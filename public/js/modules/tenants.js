document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('tenantsTable')) {
        loadTenants();
    }
});

function loadTenants() {
    if ($.fn.DataTable.isDataTable('#tenantsTable')) {
        $('#tenantsTable').DataTable().destroy();
    }

    $('#tenantsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "app/tenant_controller.php?action=get_tenants",
            "type": "POST"
        },
        "columns": [
            { "data": "full_name" },
            { "data": "phone" },
            { "data": "email" },
            { "data": "id_number" },
            { "data": "status" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[0, "asc"]]
    });
}

function editTenant(id) {
    // Placeholder for edit functionality
    console.log('Edit tenant: ' + id);
}

function deleteTenant(id) {
    // Placeholder for delete functionality
    console.log('Delete tenant: ' + id);
}
