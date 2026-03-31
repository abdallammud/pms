<?php
/**
 * Organization Controller
 * Super-admin only: manage organizations and switch active org context
 */
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    switch ($action) {
        case 'get_orgs':
            get_orgs();
            break;
        case 'save_org':
            save_org();
            break;
        case 'delete_org':
            delete_org();
            break;
        case 'switch_org':
            switch_org();
            break;
        case 'get_all_orgs_list':
            get_all_orgs_list();
            break;
    }
}

function require_super_admin()
{
    if (!is_super_admin()) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['error' => true, 'msg' => 'Access denied. Super admin only.']);
        exit;
    }
}

function get_orgs()
{
    require_super_admin();
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $draw = $_POST['draw'] ?? 1;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $search_value = $_POST['search']['value'] ?? '';

    $sql = "SELECT * FROM organizations WHERE 1=1";

    if (!empty($search_value)) {
        $search_value = $conn->real_escape_string($search_value);
        $sql .= " AND (name LIKE '%$search_value%' OR code LIKE '%$search_value%')";
    }

    $total_records = $conn->query("SELECT COUNT(*) as count FROM organizations")->fetch_assoc()['count'] ?? 0;

    $filtered_sql = preg_replace('/SELECT\b.*?\bFROM/is', 'SELECT COUNT(*) as count FROM', $sql, 1);
    $filtered_records = $conn->query($filtered_sql)->fetch_assoc()['count'] ?? 0;

    $sql .= " ORDER BY name ASC LIMIT $start, $length";
    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $statusBadge = $row['status'] == 'active'
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';

        $actionBtn  = '<button class="btn btn-sm btn-primary me-1" onclick="editOrg(' . $row['id'] . ')"><i class="bi bi-pencil"></i></button>';
        $actionBtn .= '<button class="btn btn-sm btn-danger" onclick="deleteOrg(' . $row['id'] . ')"><i class="bi bi-trash"></i></button>';

        $data[] = [
            'id'      => $row['id'],
            'name'    => htmlspecialchars($row['name']),
            'code'    => htmlspecialchars($row['code'] ?? ''),
            'status'  => $statusBadge,
            'actions' => $actionBtn,
        ];
    }

    echo json_encode([
        'draw'            => intval($draw),
        'recordsTotal'    => intval($total_records),
        'recordsFiltered' => intval($filtered_records),
        'data'            => $data,
    ]);
}

function save_org()
{
    require_super_admin();
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $id     = isset($_POST['org_id']) && is_numeric($_POST['org_id']) ? intval($_POST['org_id']) : 0;
    $name   = trim($_POST['name'] ?? '');
    $code   = trim($_POST['code'] ?? '');
    $status = $_POST['status'] ?? 'active';

    if (empty($name)) {
        echo json_encode(['error' => true, 'msg' => 'Organization name is required.']);
        exit;
    }

    if ($id === 0) {
        $stmt = $conn->prepare("INSERT INTO organizations (name, code, status) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $code, $status);
        if ($stmt->execute()) {
            echo json_encode(['error' => false, 'msg' => 'Organization created.', 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error: ' . $conn->error]);
        }
    } else {
        $stmt = $conn->prepare("UPDATE organizations SET name=?, code=?, status=? WHERE id=?");
        $stmt->bind_param("sssi", $name, $code, $status, $id);
        if ($stmt->execute()) {
            echo json_encode(['error' => false, 'msg' => 'Organization updated.']);
        } else {
            echo json_encode(['error' => true, 'msg' => 'Error: ' . $conn->error]);
        }
    }
}

function delete_org()
{
    require_super_admin();
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

    if ($id === 1) {
        echo json_encode(['error' => true, 'msg' => 'Cannot delete the default organization.']);
        exit;
    }

    // Check for linked users
    $check = $conn->query("SELECT COUNT(*) as c FROM users WHERE org_id = $id")->fetch_assoc()['c'] ?? 0;
    if ($check > 0) {
        echo json_encode(['error' => true, 'msg' => "Cannot delete. $check user(s) belong to this organization."]);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM organizations WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['error' => false, 'msg' => 'Organization deleted.']);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Error: ' . $conn->error]);
    }
}

/**
 * Switch the active org context for the current super-admin session
 */
function switch_org()
{
    require_super_admin();
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $org_id = isset($_POST['org_id']) ? intval($_POST['org_id']) : 0;

    if ($org_id < 0) {
        echo json_encode(['error' => true, 'msg' => 'Invalid org ID.']);
        exit;
    }

    if ($org_id === 0) {
        // 0 means "all orgs" view
        $_SESSION['active_org_id'] = 0;
        echo json_encode(['error' => false, 'msg' => 'Viewing all organizations.', 'org_id' => 0]);
        exit;
    }

    $res = $conn->query("SELECT id, name FROM organizations WHERE id = $org_id AND status = 'active'");
    if (!$res || $res->num_rows === 0) {
        echo json_encode(['error' => true, 'msg' => 'Organization not found or inactive.']);
        exit;
    }

    $org = $res->fetch_assoc();
    $_SESSION['active_org_id'] = $org_id;

    echo json_encode([
        'error'    => false,
        'msg'      => 'Context switched to: ' . $org['name'],
        'org_id'   => $org_id,
        'org_name' => $org['name'],
    ]);
}

/**
 * Get minimal list of orgs for the context switcher dropdown
 */
function get_all_orgs_list()
{
    require_super_admin();
    ob_clean();
    header('Content-Type: application/json');
    $conn = $GLOBALS['conn'];

    $result = $conn->query("SELECT id, name FROM organizations WHERE status = 'active' ORDER BY name");
    $orgs = [];
    while ($row = $result->fetch_assoc()) {
        $orgs[] = $row;
    }

    echo json_encode(['error' => false, 'data' => $orgs, 'current_org_id' => current_org_id()]);
}
