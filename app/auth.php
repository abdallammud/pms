<?php
// session_start(); // Session is started in init.php or index.php usually, but let's be safe
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once('db.php');
require_once('UserClass.php');

// Handle AJAX Login
if (isset($_GET['action']) && $_GET['action'] == 'login') {
    header('Content-Type: application/json');
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['error' => true, 'msg' => 'Please fill in all fields.']);
        exit;
    }

    $result = $GLOBALS['userClass']->login($email, $password);

    if (isset($result['error'])) {
        echo json_encode(['error' => true, 'msg' => $result['error']]);
        exit;
    }

    if (set_sessions($result['id'])) {
        echo json_encode(['error' => false, 'msg' => 'Successfully logged in.']);
    } else {
        echo json_encode(['error' => true, 'msg' => 'Failed to set sessions.']);
    }
    exit;
}

function set_sessions($user_id)
{
    $user = $GLOBALS['userClass']->get($user_id);

    if (!$user) {
        return false;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['is_logged_in'] = true;

    // Get Role Info
    $roleInfo = $GLOBALS['userClass']->getRoleInfo($user['id']);
    if ($roleInfo) {
        $_SESSION['user_role_id'] = $roleInfo['role_id'];
        $_SESSION['user_role'] = $roleInfo['role_name'];
    } else {
        $_SESSION['user_role_id'] = null;
        $_SESSION['user_role'] = null;
    }

    // Load permissions into session
    $permissions = $GLOBALS['userClass']->getPermissions($user['id']);
    $_SESSION['permissions'] = $permissions;

    return true;
}

function authenticate()
{
    if (!isset($_SESSION['user_id'])) {
        // return false;
        header("Location: ./login");
    }

    $user_id = $_SESSION['user_id'];
    $user = $GLOBALS['userClass']->get($user_id);

    if (!$user || $user['status'] !== 'active') {
        // User not found or inactive, destroy session
        session_destroy();
        return false;
    }

    // Refresh sessions
    set_sessions($user_id);

    return true;
}

function check_session($permission = null)
{
    if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
        return false;
    }

    if ($permission) {
        // Check if user has the required permission
        if (isset($_SESSION['permissions']) && in_array($permission, $_SESSION['permissions'])) {
            return true;
        }
        return false;
    }

    return true;
}

function check_auth($permission = null)
{
    if (!check_session($permission)) {
        header("Location: logout.php");
        exit;
    }
}

function current_user_id()
{
    return $_SESSION['user_id'] ?? null;
}

function current_user_name()
{
    return $_SESSION['user_name'] ?? 'Guest';
}


?>