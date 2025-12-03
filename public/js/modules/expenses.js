document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('expensesTable')) {
        loadExpenses();
    }
});

function loadExpenses() {
    if ($.fn.DataTable.isDataTable('#expensesTable')) {
        $('#expensesTable').DataTable().destroy();
    }

    $('#expensesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "app/expense_controller.php?action=get_expenses",
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "property_name" },
            { "data": "category" },
            { "data": "amount" },
            { "data": "expense_date" },
            { "data": "description" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[4, "desc"]] // Order by expense_date desc
    });
}

function editExpense(id) {
    // Placeholder for edit functionality
    console.log('Edit expense: ' + id);
}

function deleteExpense(id) {
    // Placeholder for delete functionality
    console.log('Delete expense: ' + id);
}
