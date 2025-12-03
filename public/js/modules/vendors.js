document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('vendorsTable')) {
        loadVendors();
    }
});

function loadVendors() {
    if ($.fn.DataTable.isDataTable('#vendorsTable')) {
        $('#vendorsTable').DataTable().destroy();
    }

    $('#vendorsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "app/vendor_controller.php?action=get_vendors",
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "vendor_name" },
            { "data": "service_type" },
            { "data": "phone" },
            { "data": "email" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[1, "asc"]] // Order by vendor_name asc
    });
}

function editVendor(id) {
    // Placeholder for edit functionality
    console.log('Edit vendor: ' + id);
}

function deleteVendor(id) {
    // Placeholder for delete functionality
    console.log('Delete vendor: ' + id);
}
