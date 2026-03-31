<?php
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'send_sms':
            require_permission('communication_manage');
            send_sms();
            break;
        case 'get_sms_log':
            require_permission('communication_manage');
            get_sms_log();
            break;
        case 'get_contact_list':
            require_permission('communication_manage');
            get_contact_list();
            break;
    }
}

/**
 * Send an SMS via the 1s2u / Hormuud API
 * POST: tenant_id (optional), recipient_phone, message
 */
function send_sms() {
    header('Content-Type: application/json');
    global $conn;

    $org_id    = resolve_request_org_id();
    $user_id   = $_SESSION['user_id'] ?? 0;
    $phone     = trim($_POST['recipient_phone'] ?? '');
    $message   = trim($_POST['message']         ?? '');
    $tenant_id = intval($_POST['tenant_id']     ?? 0) ?: null;

    if (empty($phone)) {
        echo json_encode(['error' => true, 'msg' => 'Recipient phone number is required.']);
        exit;
    }
    if (empty($message)) {
        echo json_encode(['error' => true, 'msg' => 'Message body is required.']);
        exit;
    }
    if (strlen($message) > 640) {
        echo json_encode(['error' => true, 'msg' => 'Message exceeds maximum length (640 chars).']);
        exit;
    }

    // Load org-specific credentials, fall back to global defaults
    $cfg = $GLOBALS['SMS'];
    $sRes = $conn->query("SELECT setting_key, setting_value FROM system_settings WHERE org_id = $org_id AND setting_key IN ('sms_username','sms_password','sms_sender_name','sms_enabled')");
    if ($sRes) {
        while ($sr = $sRes->fetch_assoc()) {
            switch ($sr['setting_key']) {
                case 'sms_username':    $cfg['sms_username'] = $sr['setting_value']; break;
                case 'sms_password':    $cfg['sms_password'] = $sr['setting_value']; break;
                case 'sms_sender_name': $cfg['sender_name']  = $sr['setting_value']; break;
                case 'sms_enabled':
                    if ($sr['setting_value'] !== 'yes') {
                        echo json_encode(['error' => true, 'msg' => 'SMS is disabled. Enable it in Settings → Communication.']);
                        exit;
                    }
                    break;
            }
        }
    }

    // ── Call 1s2u API ─────────────────────────────────────────────
    $payload = http_build_query([
        'UserName'   => $cfg['sms_username'],
        'Password'   => $cfg['sms_password'],
        'SMSText'    => $message,
        'GSM'        => $phone,
        'sid'        => $cfg['sms_sid'],
        'mno'        => $cfg['msgProvider'],
    ]);

    $ch = curl_init($cfg['api_url']);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Determine success — 1s2u returns "OK" or similar in the body
    $success = (!$curlErr && $httpCode === 200 && stripos($response, 'OK') !== false);
    $status  = $success ? 'sent' : 'failed';

    // ── Log to sms_log ────────────────────────────────────────────
    $phone_esc    = $conn->real_escape_string($phone);
    $message_esc  = $conn->real_escape_string($message);
    $response_esc = $conn->real_escape_string($response ?: $curlErr);
    $tenant_sql   = $tenant_id ? $tenant_id : 'NULL';

    $conn->query(
        "INSERT INTO sms_log (org_id, tenant_id, recipient_phone, message, status, provider_response, sent_by_user_id)
         VALUES ($org_id, $tenant_sql, '$phone_esc', '$message_esc', '$status', '$response_esc', $user_id)"
    );

    if ($success) {
        echo json_encode(['error' => false, 'msg' => 'SMS sent successfully.']);
    } else {
        echo json_encode(['error' => true, 'msg' => 'SMS delivery failed. The message has been logged.', 'provider_response' => $response]);
    }
}

/**
 * Get sent SMS log (DataTables server-side)
 */
function get_sms_log() {
    header('Content-Type: application/json');
    global $conn;

    $org_id = resolve_request_org_id();
    $limit  = intval($_POST['length']  ?? 25);
    $offset = intval($_POST['start']   ?? 0);
    $search = $conn->real_escape_string($_POST['search']['value'] ?? '');

    $where = "sl.org_id = $org_id";
    if ($search) {
        $where .= " AND (sl.recipient_phone LIKE '%$search%' OR sl.message LIKE '%$search%' OR t.full_name LIKE '%$search%')";
    }

    $total = $conn->query("SELECT COUNT(*) c FROM sms_log sl LEFT JOIN tenants t ON t.id = sl.tenant_id WHERE $where")->fetch_assoc()['c'];

    $res = $conn->query(
        "SELECT sl.id, sl.recipient_phone, sl.message, sl.status, sl.created_at,
                COALESCE(t.full_name, 'Manual Entry') AS tenant_name,
                COALESCE(u.name, '') AS sent_by
         FROM sms_log sl
         LEFT JOIN tenants t ON t.id = sl.tenant_id
         LEFT JOIN users   u ON u.id = sl.sent_by_user_id
         WHERE $where
         ORDER BY sl.created_at DESC
         LIMIT $limit OFFSET $offset"
    );

    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $statusClass = ['sent' => 'success', 'failed' => 'danger', 'pending' => 'secondary'][$r['status']] ?? 'secondary';
        $rows[] = [
            'tenant_name'      => htmlspecialchars($r['tenant_name']),
            'recipient_phone'  => htmlspecialchars($r['recipient_phone']),
            'message'          => htmlspecialchars(mb_strimwidth($r['message'], 0, 80, '…')),
            'status'           => '<span class="badge bg-' . $statusClass . '">' . $r['status'] . '</span>',
            'sent_by'          => htmlspecialchars($r['sent_by']),
            'created_at'       => $r['created_at'],
        ];
    }

    echo json_encode([
        'draw'            => intval($_POST['draw'] ?? 1),
        'recordsTotal'    => $total,
        'recordsFiltered' => $total,
        'data'            => $rows,
    ]);
}

/**
 * Get tenants list for recipient selector (scoped to current org)
 */
function get_contact_list() {
    header('Content-Type: application/json');
    global $conn;

    $res = $conn->query(
        "SELECT t.id, t.full_name, t.phone, t.email
         FROM tenants t
         WHERE t.phone IS NOT NULL AND t.phone <> '' AND " . tenant_where_clause('t') . "
         ORDER BY t.full_name ASC"
    );
    $contacts = [];
    while ($r = $res->fetch_assoc()) {
        $contacts[] = [
            'id'    => $r['id'],
            'text'  => $r['full_name'] . ' (' . $r['phone'] . ')',
            'phone' => $r['phone'],
            'name'  => $r['full_name'],
        ];
    }
    echo json_encode(['error' => false, 'data' => $contacts]);
}

/**
 * Guard: redirect with 403 if permission not met
 */
function require_permission($perm) {
    if (!check_session($perm)) {
        header('Content-Type: application/json');
        http_response_code(403);
        echo json_encode(['error' => true, 'msg' => '403 Unauthorized']);
        exit;
    }
}
?>
