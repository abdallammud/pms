<?php 
function load_files() {
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

function handle_sub_menu($subMenu, $folder, $action) {
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

function handle_action($actionConfig, $folder) {
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

function load_file($filePath) {
    if (file_exists($filePath)) {
        require $filePath;
    } else {
        load_not_found($filePath);
    }
}

function load_unauthorized() {
    echo "<div class='alert alert-danger'>403 Unauthorized Access</div>";
}

function load_not_found($file = '') {
    echo "<div class='alert alert-warning'>404 Not Found: $file</div>";
}

// Wrapper for check_session to match new auth system
function check_auth_permission($permission) {
    if (function_exists('check_session')) {
        return check_session($permission);
    }
    return true; // Fallback if auth not ready
}

function get_menu_config() {
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
            'icon' => 'buildings',
            'auth' => 'property_manage',
            'menu' => 'properties',
            'route' => 'properties',
            'sub' => [
                'all' => ['default' => 'properties', 'route' => 'properties', 'is_modal' => false, 'name' => 'All Properties', 'auth' => 'property_manage'],
                'add' => ['default' => 'add_property', 'route' => 'add_property', 'is_modal' => true, 'data-bs-target' => '#addPropertyModal', 'name' => 'Add New Property', 'auth' => 'property_create'],
                'units' => ['default' => 'units', 'route' => 'units', 'is_modal' => false, 'name' => 'Units List', 'auth' => 'unit_manage'],
            ],
        ],
        'tenants' => [
            'folder' => 'tenants',
            'default' => 'tenants',
            'name' => 'Tenants',
            'icon' => 'people',
            'auth' => 'tenant_manage',
            'menu' => 'tenants',
            'route' => 'tenants',
            'sub' => [
                'directory' => ['default' => 'tenants', 'route' => 'tenants', 'name' => 'Tenant Directory', 'auth' => 'tenant_manage'],
                'add' => ['default' => 'add_tenant', 'is_modal' => true, 'data-bs-target' => '#addTenantModal', 'route' => 'add_tenant', 'name' => 'Add Tenant', 'auth' => 'tenant_create'],
                'leases' => ['default' => 'leases', 'route' => 'leases', 'name' => 'Lease Agreements', 'auth' => 'lease_manage'],
                'add_lease' => ['default' => 'add_lease', 'route' => 'add_lease', 'name' => 'Add Lease', 'auth' => 'lease_create'],
                'guarantees' => ['default' => 'guarantees', 'route' => 'guarantees', 'name' => 'Guarantees', 'auth' => 'tenant_manage'],
            ],
        ],
        'accounting' => [
            'folder' => 'accounting',
            'default' => 'invoices',
            'name' => 'Accounting',
            'icon' => 'wallet2',
            'auth' => 'invoice_manage',
            'menu' => 'accounting',
            'route' => 'accounting',
            'sub' => [
                'invoices' => ['default' => 'invoices', 'route' => 'invoices', 'name' => 'Rent Invoices', 'auth' => 'invoice_manage'],
                'create_invoice' => ['default' => 'create_invoice', 'route' => 'create_invoice', 'name' => 'Create Invoice', 'auth' => 'invoice_create'],
                'payments_received' => ['default' => 'payments_received', 'route' => 'payments_received', 'name' => 'Payments Received', 'auth' => 'payment_manage'],
                'expenses' => ['default' => 'expenses', 'route' => 'expenses', 'name' => 'Expenses', 'auth' => 'expense_manage'],
                'bills' => ['default' => 'bills', 'route' => 'bills', 'name' => 'Bills', 'auth' => 'bill_manage'],
                'payments_made' => ['default' => 'payments_made', 'route' => 'payments_made', 'name' => 'Payments Made', 'auth' => 'payment_manage'],
            ],
        ],
        'maintenance' => [
            'folder' => 'maintenance',
            'default' => 'requests',
            'name' => 'Maintenance',
            'icon' => 'tools',
            'auth' => 'maintenance_manage',
            'menu' => 'maintenance',
            'route' => 'maintenance',
            'sub' => [
                'requests' => ['default' => 'requests', 'route' => 'requests', 'name' => 'All Requests', 'auth' => 'maintenance_manage'],
                'create_request' => ['default' => 'create_request', 'route' => 'create_request', 'name' => 'Create Request', 'auth' => 'maintenance_create'],
                'assign_request' => ['default' => 'assign_request', 'route' => 'assign_request', 'name' => 'Assign Requests', 'auth' => 'maintenance_assign'],
                'vendors' => ['default' => 'vendors', 'route' => 'vendors', 'name' => 'Vendors / Staff', 'auth' => 'vendor_manage'],
            ],
        ],
        'reports' => [
            'folder' => 'reports',
            'default' => 'rent_collection',
            'name' => 'Reports',
            'icon' => 'bar-chart',
            'auth' => 'reports_view',
            'menu' => 'reports',
            'route' => 'reports',
            'sub' => [
                'rent_collection' => ['default' => 'rent_collection', 'route' => 'rent_collection', 'name' => 'Rent Collection Report', 'auth' => 'reports_view'],
                'occupancy' => ['default' => 'occupancy', 'route' => 'occupancy', 'name' => 'Occupancy Report', 'auth' => 'reports_view'],
                'tenant_report' => ['default' => 'tenant_report', 'route' => 'tenant_report', 'name' => 'Tenant Report', 'auth' => 'reports_view'],
                'outstanding' => ['default' => 'outstanding', 'route' => 'outstanding', 'name' => 'Outstanding Balances', 'auth' => 'reports_view'],
                'income_expense' => ['default' => 'income_expense', 'route' => 'income_expense', 'name' => 'Income vs Expense', 'auth' => 'reports_view'],
                'maintenance_cost' => ['default' => 'maintenance_cost', 'route' => 'maintenance_cost', 'name' => 'Maintenance Cost Report', 'auth' => 'reports_view'],
            ],
        ],
        'settings' => [
            'folder' => 'settings',
            'default' => 'system_settings',
            'name' => 'Settings',
            'icon' => 'gear',
            'auth' => 'settings_manage',
            'menu' => 'settings',
            'route' => 'settings',
            'sub' => [
                'users' => ['default' => 'users', 'route' => 'users', 'name' => 'User Management', 'auth' => 'user_manage'],
                'system' => ['default' => 'system_settings', 'route' => 'system_settings', 'name' => 'System Settings', 'auth' => 'system_customize'],
                'property_types' => ['default' => 'property_types', 'route' => 'property_types', 'name' => 'Property Types', 'auth' => 'property_type_manage'],
            ],
        ],
    ];
}