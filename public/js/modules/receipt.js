document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('receiptsTable')) {
        loadReceipts();
    }
});

function loadReceipts() {
    if ($.fn.DataTable.isDataTable('#receiptsTable')) {
        $('#receiptsTable').DataTable().destroy();
    }

    $('#receiptsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "app/receipt_controller.php?action=get_receipts",
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "invoice_number" },
            { "data": "tenant_name" },
            { "data": "amount_paid" },
            { "data": "payment_method" },
            { "data": "received_date" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[5, "desc"]] // Order by received_date desc
    });
}

function editReceipt(id) {
    // Placeholder for edit functionality
    console.log('Edit receipt: ' + id);
}

function deleteReceipt(id) {
    // Placeholder for delete functionality
    console.log('Delete receipt: ' + id);
}
