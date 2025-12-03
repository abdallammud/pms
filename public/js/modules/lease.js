document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('leasesTable')) {
        loadLeases();
    }
});

function loadLeases() {
    if ($.fn.DataTable.isDataTable('#leasesTable')) {
        $('#leasesTable').DataTable().destroy();
    }

    $('#leasesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "app/lease_controller.php?action=get_leases",
            "type": "POST"
        },
        "columns": [
            { "data": "tenant_name" },
            { "data": "unit_number" },
            { "data": "start_date" },
            { "data": "end_date" },
            { "data": "status" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[2, "desc"]] // Order by start_date desc
    });
}

function editLease(id) {
    // Placeholder for edit functionality
    console.log('Edit lease: ' + id);
}

function deleteLease(id) {
    // Placeholder for delete functionality
    console.log('Delete lease: ' + id);
}
