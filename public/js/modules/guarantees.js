document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('guaranteesTable')) {
        loadGuarantees();
    }
});

function loadGuarantees() {
    if ($.fn.DataTable.isDataTable('#guaranteesTable')) {
        $('#guaranteesTable').DataTable().destroy();
    }

    $('#guaranteesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "app/guarantee_controller.php?action=get_guarantees",
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

function editGuarantee(id) {
    // Placeholder for edit functionality
    console.log('Edit guarantee: ' + id);
}

function deleteGuarantee(id) {
    // Placeholder for delete functionality
    console.log('Delete guarantee: ' + id);
}
