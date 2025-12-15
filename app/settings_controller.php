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
    }
}

/**
 * Get all settings as key-value pairs
 */
function get_settings() {
    header('Content-Type: application/json');
    global $conn;

    $result = $conn->query("SELECT setting_key, setting_value FROM system_settings");
    $settings = [];
    
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    echo json_encode(['error' => false, 'data' => $settings]);
}

/**
 * Save organization profile settings
 */
function save_profile() {
    header('Content-Type: application/json');
    global $conn;

    $fields = ['org_name', 'org_email', 'org_phone', 'org_street1', 'org_street2', 'org_city'];
    
    foreach ($fields as $field) {
        $value = $_POST[$field] ?? '';
        $value = $conn->real_escape_string($value);
        
        // Check if setting exists
        $check = $conn->query("SELECT id FROM system_settings WHERE setting_key = '$field'");
        
        if ($check->num_rows > 0) {
            $conn->query("UPDATE system_settings SET setting_value = '$value' WHERE setting_key = '$field'");
        } else {
            $conn->query("INSERT INTO system_settings (setting_key, setting_value) VALUES ('$field', '$value')");
        }
    }
    
    echo json_encode(['error' => false, 'msg' => 'Organization profile saved successfully.']);
}

/**
 * Handle logo upload and save branding settings
 */
function save_branding() {
    header('Content-Type: application/json');
    global $conn;

    if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['error' => true, 'msg' => 'Please select a valid image file.']);
        exit;
    }

    $file = $_FILES['logo'];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp'];
    $max_size = 1 * 1024 * 1024; // 1MB

    // Validate file type
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['error' => true, 'msg' => 'Invalid file type. Allowed: jpg, png, gif, bmp']);
        exit;
    }

    // Validate file size
    if ($file['size'] > $max_size) {
        echo json_encode(['error' => true, 'msg' => 'File size exceeds 1MB limit.']);
        exit;
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'logo_' . time() . '.' . $extension;
    $upload_path = '../public/images/' . $filename;
    $relative_path = 'public/images/' . $filename;

    // Create directory if not exists
    if (!is_dir('../public/images/')) {
        mkdir('../public/images/', 0755, true);
    }

    // Delete old logo if exists
    $old_logo = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'logo_path'")->fetch_assoc();
    if ($old_logo && !empty($old_logo['setting_value'])) {
        $old_path = '../' . $old_logo['setting_value'];
        if (file_exists($old_path)) {
            unlink($old_path);
        }
    }

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Save path to database
        $check = $conn->query("SELECT id FROM system_settings WHERE setting_key = 'logo_path'");
        
        if ($check->num_rows > 0) {
            $conn->query("UPDATE system_settings SET setting_value = '$relative_path' WHERE setting_key = 'logo_path'");
        } else {
            $conn->query("INSERT INTO system_settings (setting_key, setting_value) VALUES ('logo_path', '$relative_path')");
        }
        
        echo json_encode(['error' => false, 'msg' => 'Logo uploaded successfully.', 'path' => $relative_path]);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Failed to upload logo. Please try again.']);
    }
}

/**
 * Get transaction number series
 */
function get_transaction_series() {
    header('Content-Type: application/json');
    global $conn;

    $result = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'transaction_series'");
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
function save_transaction_series() {
    header('Content-Type: application/json');
    global $conn;

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
    $check = $conn->query("SELECT id FROM system_settings WHERE setting_key = 'transaction_series'");
    
    if ($check->num_rows > 0) {
        $conn->query("UPDATE system_settings SET setting_value = '$series_data' WHERE setting_key = 'transaction_series'");
    } else {
        $conn->query("INSERT INTO system_settings (setting_key, setting_value) VALUES ('transaction_series', '$series_data')");
    }
    
    echo json_encode(['error' => false, 'msg' => 'Transaction number series saved successfully.']);
}

/**
 * Get lease conditions template
 */
function get_lease_conditions() {
    header('Content-Type: application/json');
    global $conn;

    $result = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'lease_conditions'");
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
function save_lease_conditions() {
    header('Content-Type: application/json');
    global $conn;

    $lease_conditions = $_POST['lease_conditions'] ?? '';
    $lease_conditions = $conn->real_escape_string($lease_conditions);
    
    // Check if setting exists
    $check = $conn->query("SELECT id FROM system_settings WHERE setting_key = 'lease_conditions'");
    
    if ($check->num_rows > 0) {
        $conn->query("UPDATE system_settings SET setting_value = '$lease_conditions' WHERE setting_key = 'lease_conditions'");
    } else {
        $conn->query("INSERT INTO system_settings (setting_key, setting_value) VALUES ('lease_conditions', '$lease_conditions')");
    }
    
    echo json_encode(['error' => false, 'msg' => 'Lease conditions template saved successfully.']);
}
?>
