<?php
function load_files()
{
    // Load menu configuration
    $menus = get_menu_config();

    // Extract parameters
    $menu = $_GET['menu'] ?? 'dashboard';
    $action = $_GET['action'] ?? null;
    $tab = $_GET['tab'] ?? null;

    // Load dashboard by default if no menu is specified or invalid
    if (!$menu || !array_key_exists($menu, $menus)) {
        $menu = 'dashboard';
    }

    // echo $menu;

    $config = $menus[$menu];
    $folder = 'views/' . $config['folder'] . '/';
    $defaultFile = $config['default'];


    // Check main menu auth if needed
    if (isset($config['auth']) && !check_auth_permission($config['auth'])) {
        load_unauthorized();
        return;
    }

    // echo $defaultFile;

    // Handle submenus and their actions
    if ($tab && isset($config['sub'][$tab])) {
        handle_sub_menu($config['sub'][$tab], $folder, $action);
    }

    // Handle top-level menu actions
    else if ($action && isset($config['actions'][$action])) {
        handle_action($config['actions'][$action], $folder);
    }

    // Load the default file for the menu
    else {
        load_file($folder . $defaultFile . '.php');
    }
}

function handle_sub_menu($subMenu, $folder, $action)
{
    if ($action && isset($subMenu['actions'][$action])) {
        handle_action($subMenu['actions'][$action], $folder);
    } else {
        if (isset($subMenu['auth']) && !check_auth_permission($subMenu['auth'])) {
            load_unauthorized();
            return;
        }
        load_file($folder . $subMenu['default'] . '.php');
    }
}

function handle_action($actionConfig, $folder)
{
    $file = $actionConfig['file'] ?? null;
    $authKey = $actionConfig['auth'] ?? null;

    if ($file) {
        if ($authKey && !check_auth_permission($authKey)) {
            load_unauthorized();
            return;
        }
        load_file($folder . $file . '.php');
    } else {
        load_unauthorized();
    }
}

function load_file($filePath)
{
    if (file_exists($filePath)) {
        require $filePath;
    } else {
        load_not_found($filePath);
    }
}

function load_unauthorized()
{
    echo "<div class='alert alert-danger'>403 Unauthorized Access</div>";
}

function load_not_found($file = '')
{
    echo "<div class='alert alert-warning'>404 Not Found: $file</div>";
}

// Wrapper for check_session to match new auth system
function check_auth_permission($permission)
{
    if (function_exists('check_session')) {
        return check_session($permission);
    }
    return true; // Fallback if auth not ready
}

function get_menu_config()
{
    return [
        'dashboard' => [
            'folder' => 'dashboard',
            'default' => 'dashboard',
            'name' => 'Dashboard',
            'icon' => 'speedometer2',
            'auth' => 'dashboard_view',
            'menu' => 'dashboard',
            'route' => 'dashboard',
        ],
        'properties' => [
            'folder' => 'properties',
            'default' => 'properties',
            'name' => 'Properties',
            'icon' => 'building',
            'auth' => 'property_manage',
            'menu' => 'properties',
            'route' => 'properties',
            'sub' => [
                'all'   => ['default' => 'properties', 'route' => 'properties', 'icon' => 'building', 'is_modal' => false, 'name' => 'Properties', 'auth' => 'property_manage'],
                'units' => ['default' => 'units', 'route' => 'units', 'icon' => 'grid-1x2', 'is_modal' => false, 'name' => 'Units', 'auth' => 'unit_manage'],
            ],
            'actions' => [
                'property_show' => ['file' => 'property_show', 'auth' => 'property_manage'],
            ],
        ],
        'tenants' => [
            'folder' => 'tenants',
            'default' => 'tenants',
            'name' => 'Tenants',
            'icon' => 'person-lines-fill',
            'auth' => 'tenant_manage',
            'menu' => 'tenants',
            'route' => 'tenants',
            'sub' => [
                'directory'  => ['default' => 'tenants',    'route' => 'tenants',    'icon' => 'person-lines-fill', 'name' => 'Tenants',    'auth' => 'tenant_manage'],
                'guarantees' => ['default' => 'guarantees', 'route' => 'guarantees', 'icon' => 'shield-check',      'name' => 'Guarantors', 'auth' => 'tenant_manage'],
            ],
        ],
        'leases' => [
            'folder'  => 'tenants',
            'default' => 'leases',
            'name'    => 'Lease',
            'icon'    => 'file-earmark-text',
            'auth'    => 'lease_manage',
            'menu'    => 'leases',
            'route'   => 'leases',
            'actions' => [
                'add_lease'  => ['file' => 'add_lease',  'auth' => 'lease_create'],
                'edit_lease' => ['file' => 'edit_lease', 'auth' => 'lease_update'],
                'view_lease' => ['file' => 'view_lease', 'auth' => 'lease_manage'],
            ],
        ],
        'accounting' => [
            'folder' => 'accounting',
            'default' => 'invoices',
            'name' => 'Finance',
            'icon' => 'cash-coin',
            'auth' => 'invoice_manage',
            'menu' => 'accounting',
            'route' => 'accounting',
            'actions' => [
                'invoice_show' => ['file' => 'invoice_show', 'auth' => 'invoice_manage'],
                'payment_show' => ['file' => 'payment_show', 'auth' => 'payment_manage'],
            ],
            'sub' => [
                'invoices'           => ['default' => 'invoices',           'route' => 'invoices',           'icon' => 'receipt',              'name' => 'Invoices',           'auth' => 'invoice_manage'],
                'payments_received'  => ['default' => 'payments_received',  'route' => 'payments_received',  'icon' => 'cash-stack',           'name' => 'Payments Received',  'auth' => 'payment_manage'],
                'expenses'           => ['default' => 'expenses',           'route' => 'expenses',           'icon' => 'credit-card-2-back',   'name' => 'Expenses',           'auth' => 'expense_manage'],
            ],
        ],
        'maintenance' => [
            'folder' => 'maintenance',
            'default' => 'requests',
            'name' => 'Maintenance',
            'icon' => 'hammer',
            'auth' => 'maintenance_manage',
            'menu' => 'maintenance',
            'route' => 'maintenance',
            'sub' => [
                'requests' => ['default' => 'requests', 'route' => 'maintenance_requests', 'icon' => 'list-task',    'name' => 'All Requests',  'auth' => 'maintenance_manage'],
                'vendors'  => ['default' => 'vendors',  'route' => 'vendors',              'icon' => 'person-gear',  'name' => 'Vendors / Staff', 'auth' => 'vendor_manage'],
            ],
        ],
        'reports' => [
            'folder' => 'reports',
            'default' => 'reports_page',
            'name' => 'Reports',
            'icon' => 'graph-up-arrow',
            'auth' => 'reports_view',
            'menu' => 'reports',
            'route' => 'reports',
            'actions' => [
                'report_display' => ['file' => 'report_display', 'auth' => 'reports_view'],
            ],
        ],
        'communication' => [
            'folder'  => 'communication',
            'default' => 'communication',
            'name'    => 'Communication',
            'icon'    => 'chat-text',
            'auth'    => 'communication_manage',
            'menu'    => 'communication',
            'route'   => 'communication',
        ],
        'settings' => [
            'folder' => 'settings',
            'default' => 'system_settings',
            'name' => 'Settings',
            'icon' => 'sliders',
            'auth' => 'settings_manage',
            'menu' => 'settings',
            'route' => 'settings',
            'sub' => [
                'users'          => ['default' => 'users',           'route' => 'users',           'icon' => 'people',         'name' => 'User Management',  'auth' => 'user_manage'],
                'system'         => ['default' => 'system_settings', 'route' => 'system_settings', 'icon' => 'gear',           'name' => 'System Settings',  'auth' => 'system_customize'],
                'property_types' => ['default' => 'property_types',  'route' => 'property_types',  'icon' => 'tag',            'name' => 'Property Types',   'auth' => 'property_type_manage'],
                'unit_types'     => ['default' => 'unit_types',      'route' => 'unit_types',      'icon' => 'door-open',      'name' => 'Unit Types',       'auth' => 'property_type_manage'],
                'amenities'      => ['default' => 'amenities',       'route' => 'amenities',       'icon' => 'stars',          'name' => 'Amenities',        'auth' => 'property_type_manage'],
                'organizations'  => ['default' => 'organizations',   'route' => 'organizations',   'icon' => 'buildings',      'name' => 'Organizations',    'auth' => 'super_admin'],
            ],
        ],
    ];
}