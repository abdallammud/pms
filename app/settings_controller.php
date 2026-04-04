<?php
require_once 'init.php';

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'get_settings':
            get_settings();
            break;
        case 'save_profile':
            save_profile();
            break;
        case 'save_branding':
            save_branding();
            break;
        case 'save_brand_color':
            save_brand_color();
            break;
        case 'get_transaction_series':
            get_transaction_series();
            break;
        case 'save_transaction_series':
            save_transaction_series();
            break;
        case 'get_lease_conditions':
            get_lease_conditions();
            break;
        case 'save_lease_conditions':
            save_lease_conditions();
            break;
        case 'save_settings':
            save_settings();
            break;
        case 'save_sms_settings':
            save_sms_settings();
            break;
    }
}

/**
 * Get all settings as key-value pairs
 */
function get_settings()
{
    header('Content-Type: application/json');
    global $conn;

    $result = $conn->query("SELECT setting_key, setting_value FROM system_settings WHERE " . tenant_where_clause());
    $settings = [];

    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }

    // Fallback: If no org_name exists in settings, pull from organizations table
    if (empty($settings['org_name'])) {
        $org_id = current_org_id();
        if ($org_id > 0) {
            $org_res = $conn->query("SELECT name FROM organizations WHERE id = $org_id");
            if ($org_res && $org_row = $org_res->fetch_assoc()) {
                $settings['org_name'] = $org_row['name'];
            }
        }
    }

    echo json_encode(['error' => false, 'data' => $settings]);
}

/**
 * Save organization profile settings
 */
function save_profile()
{
    header('Content-Type: application/json');
    global $conn;
    $org_id = resolve_request_org_id();

    $fields = ['org_name', 'org_email', 'org_phone', 'org_street1', 'org_street2', 'org_city'];

    foreach ($fields as $field) {
        $value = $_POST[$field] ?? '';
        $value = $conn->real_escape_string($value);

        // Check if setting exists
        $check = $conn->query("SELECT id FROM system_settings WHERE setting_key = '$field' AND org_id = $org_id");

        if ($check->num_rows > 0) {
            $conn->query("UPDATE system_settings SET setting_value = '$value' WHERE setting_key = '$field' AND org_id = $org_id");
        } else {
            $conn->query("INSERT INTO system_settings (org_id, setting_key, setting_value) VALUES ($org_id, '$field', '$value')");
        }
    }

    echo json_encode(['error' => false, 'msg' => 'Organization profile saved successfully.']);
}

/**
 * Handle logo upload — supports system logo (logo_path) and document logo (doc_logo_path).
 * Pass logo_type = 'system' | 'document' in the POST body.
 */
function save_branding()
{
    header('Content-Type: application/json');
    global $conn;
    $org_id = resolve_request_org_id();

    // Determine which logo key to update
    $logo_type = $_POST['logo_type'] ?? 'system';
    $setting_key = ($logo_type === 'document') ? 'doc_logo_path' : 'logo_path';

    if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => true, 'msg' => 'Please select a valid image file.']);
        exit;
    }

    $file = $_FILES['logo'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp'];
    $max_size = 1 * 1024 * 1024; // 1 MB

    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['error' => true, 'msg' => 'Invalid file type. Allowed: jpg, png, gif, bmp']);
        exit;
    }
    if ($file['size'] > $max_size) {
        echo json_encode(['error' => true, 'msg' => 'File size exceeds 1MB limit.']);
        exit;
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'logo_' . time() . '.' . $extension;
    $upload_path = '../public/images/' . $filename;
    $relative_path = 'public/images/' . $filename;

    if (!is_dir('../public/images/')) {
        mkdir('../public/images/', 0755, true);
    }

    // Delete previous file for this logo type
    $old = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = '$setting_key' AND org_id = $org_id")->fetch_assoc();
    if ($old && !empty($old['setting_value'])) {
        $old_path = '../' . $old['setting_value'];
        if (file_exists($old_path)) {
            unlink($old_path);
        }
    }

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        $check = $conn->query("SELECT id FROM system_settings WHERE setting_key = '$setting_key' AND org_id = $org_id");
        if ($check->num_rows > 0) {
            $conn->query("UPDATE system_settings SET setting_value = '$relative_path' WHERE setting_key = '$setting_key' AND org_id = $org_id");
        } else {
            $conn->query("INSERT INTO system_settings (org_id, setting_key, setting_value) VALUES ($org_id, '$setting_key', '$relative_path')");
        }
        echo json_encode(['error' => false, 'msg' => 'Logo uploaded successfully.', 'path' => $relative_path, 'logo_type' => $logo_type]);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Failed to upload logo. Please try again.']);
    }
}

/**
 * Save brand primary color
 */
function save_brand_color()
{
    header('Content-Type: application/json');
    global $conn;
    $org_id = resolve_request_org_id();

    $color = trim($_POST['brand_primary_color'] ?? '');
    if (empty($color)) {
        echo json_encode(['error' => true, 'msg' => 'Color value is required.']);
        exit;
    }
    // Strip leading # so we store a clean 6-char hex
    $color = ltrim($color, '#');
    if (!preg_match('/^[0-9a-fA-F]{6}$/', $color)) {
        echo json_encode(['error' => true, 'msg' => 'Invalid color format. Use a 6-digit hex code.']);
        exit;
    }
    $color = $conn->real_escape_string($color);

    $check = $conn->query("SELECT id FROM system_settings WHERE setting_key = 'brand_primary_color' AND org_id = $org_id");
    if ($check->num_rows > 0) {
        $conn->query("UPDATE system_settings SET setting_value = '$color' WHERE setting_key = 'brand_primary_color' AND org_id = $org_id");
    } else {
        $conn->query("INSERT INTO system_settings (org_id, setting_key, setting_value) VALUES ($org_id, 'brand_primary_color', '$color')");
    }

    echo json_encode(['error' => false, 'msg' => 'Brand color saved successfully.', 'color' => '#' . $color]);
}

/**
 * Get transaction number series
 */
function get_transaction_series()
{
    header('Content-Type: application/json');
    global $conn;
    $org_id = resolve_request_org_id();

    $result = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'transaction_series' AND org_id = $org_id");
    $row = $result->fetch_assoc();

    if ($row && !empty($row['setting_value'])) {
        $series = json_decode($row['setting_value'], true);
        echo json_encode(['error' => false, 'data' => $series]);
    } else {
        // Return default if not set
        $default = [
            'invoice' => ['prefix' => 'INV-', 'suffix' => '', 'starting_number' => '00001', 'current_number' => 0],
            'payment' => ['prefix' => 'RCT-', 'suffix' => '', 'starting_number' => '00001', 'current_number' => 0],
            'expense' => ['prefix' => 'EXP-', 'suffix' => '', 'starting_number' => '00001', 'current_number' => 0],
            'maintenance' => ['prefix' => 'MR-', 'suffix' => '', 'starting_number' => '00001', 'current_number' => 0],
            'lease' => ['prefix' => 'LS-', 'suffix' => '', 'starting_number' => '00001', 'current_number' => 0]
        ];
        echo json_encode(['error' => false, 'data' => $default]);
    }
}

/**
 * Save transaction number series
 */
function save_transaction_series()
{
    header('Content-Type: application/json');
    global $conn;
    $org_id = resolve_request_org_id();

    $series_data = $_POST['series'] ?? '';

    if (empty($series_data)) {
        echo json_encode(['error' => true, 'msg' => 'No data provided.']);
        exit;
    }

    // Validate JSON structure
    $decoded = json_decode($series_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['error' => true, 'msg' => 'Invalid data format.']);
        exit;
    }

    $series_data = $conn->real_escape_string($series_data);

    // Check if setting exists
    $check = $conn->query("SELECT id FROM system_settings WHERE setting_key = 'transaction_series' AND org_id = $org_id");

    if ($check->num_rows > 0) {
        $conn->query("UPDATE system_settings SET setting_value = '$series_data' WHERE setting_key = 'transaction_series' AND org_id = $org_id");
    } else {
        $conn->query("INSERT INTO system_settings (org_id, setting_key, setting_value) VALUES ($org_id, 'transaction_series', '$series_data')");
    }

    echo json_encode(['error' => false, 'msg' => 'Transaction number series saved successfully.']);
}

/**
 * Get lease conditions template
 */
function get_lease_conditions()
{
    header('Content-Type: application/json');
    global $conn;
    $org_id = resolve_request_org_id();

    $result = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'lease_conditions' AND org_id = $org_id");
    $row = $result->fetch_assoc();

    if ($row && !empty($row['setting_value'])) {
        echo json_encode(['error' => false, 'data' => $row['setting_value']]);
    } else {
        // Return empty string if not set
        echo json_encode(['error' => false, 'data' => '']);
    }
}

/**
 * Save lease conditions template
 */
function save_lease_conditions()
{
    header('Content-Type: application/json');
    global $conn;
    $org_id = resolve_request_org_id();

    $lease_conditions = $_POST['lease_conditions'] ?? '';
    $lease_conditions = $conn->real_escape_string($lease_conditions);

    // Check if setting exists
    $check = $conn->query("SELECT id FROM system_settings WHERE setting_key = 'lease_conditions' AND org_id = $org_id");

    if ($check->num_rows > 0) {
        $conn->query("UPDATE system_settings SET setting_value = '$lease_conditions' WHERE setting_key = 'lease_conditions' AND org_id = $org_id");
    } else {
        $conn->query("INSERT INTO system_settings (org_id, setting_key, setting_value) VALUES ($org_id, 'lease_conditions', '$lease_conditions')");
    }

    echo json_encode(['error' => false, 'msg' => 'Lease conditions template saved successfully.']);
}

/**
 * Save auto-invoice settings (B1 fix — previously had no handler)
 */
function save_settings()
{
    header('Content-Type: application/json');
    global $conn;
    $org_id = resolve_request_org_id();

    $fields = ['auto_invoice_enabled', 'auto_invoice_day'];

    foreach ($fields as $field) {
        if (!isset($_POST[$field])) {
            continue;
        }
        $value = $conn->real_escape_string($_POST[$field]);
        $check = $conn->query("SELECT id FROM system_settings WHERE setting_key = '$field' AND org_id = $org_id");
        if ($check->num_rows > 0) {
            $conn->query("UPDATE system_settings SET setting_value = '$value' WHERE setting_key = '$field' AND org_id = $org_id");
        } else {
            $conn->query("INSERT INTO system_settings (org_id, setting_key, setting_value) VALUES ($org_id, '$field', '$value')");
        }
    }

    echo json_encode(['error' => false, 'msg' => 'Auto-invoice settings saved successfully.']);
}

/**
 * Save SMS / communication settings
 * Only sms_username, sms_sender_name, and sms_password are dynamic per org.
 * Static provider constants live in $GLOBALS['SMS'] (helpers.php).
 */
function save_sms_settings()
{
    header('Content-Type: application/json');
    global $conn;
    $org_id = resolve_request_org_id();

    $fields = ['sms_username', 'sms_sender_name', 'sms_password', 'sms_enabled'];

    foreach ($fields as $field) {
        if (!isset($_POST[$field])) {
            continue;
        }
        $value = $conn->real_escape_string($_POST[$field]);
        $check = $conn->query("SELECT id FROM system_settings WHERE setting_key = '$field' AND org_id = $org_id");
        if ($check->num_rows > 0) {
            $conn->query("UPDATE system_settings SET setting_value = '$value' WHERE setting_key = '$field' AND org_id = $org_id");
        } else {
            $conn->query("INSERT INTO system_settings (org_id, setting_key, setting_value) VALUES ($org_id, '$field', '$value')");
        }
    }

    echo json_encode(['error' => false, 'msg' => 'SMS settings saved successfully.']);
}
?>