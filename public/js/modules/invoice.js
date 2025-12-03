document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('invoicesTable')) {
        loadInvoices();
    }
});

function loadInvoices() {
    if ($.fn.DataTable.isDataTable('#invoicesTable')) {
        $('#invoicesTable').DataTable().destroy();
    }

    $('#invoicesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "app/invoice_controller.php?action=get_invoices",
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "tenant_name" },
            { "data": "unit_number" },
            { "data": "amount" },
            { "data": "due_date" },
            { "data": "status" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[4, "asc"]] // Order by due_date asc
    });
}

function editInvoice(id) {
    // Placeholder for edit functionality
    console.log('Edit invoice: ' + id);
}

function deleteInvoice(id) {
    // Placeholder for delete functionality
    console.log('Delete invoice: ' + id);
}
