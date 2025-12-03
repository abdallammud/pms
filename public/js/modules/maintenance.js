document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('requestsTable')) {
        loadRequests();
    }
});

function loadRequests() {
    if ($.fn.DataTable.isDataTable('#requestsTable')) {
        $('#requestsTable').DataTable().destroy();
    }

    $('#requestsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "app/maintenance_controller.php?action=get_requests",
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "property_name" },
            { "data": "unit_number" },
            { "data": "priority" },
            { "data": "description" },
            { "data": "assigned_to" },
            { "data": "status" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[0, "desc"]] // Order by ID desc
    });
}

function editRequest(id) {
    // Placeholder for edit functionality
    console.log('Edit request: ' + id);
}

function deleteRequest(id) {
    // Placeholder for delete functionality
    console.log('Delete request: ' + id);
}
