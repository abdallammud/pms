document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('expensesTable')) {
        loadExpenses();
    }

    // Modal Global Initialization
    $(document).on('show.bs.modal', '#addExpenseModal', function () {
        initExpenseForm();
    });

    // Handle Expense Type Change
    $(document).off('change', '#expense_type').on('change', '#expense_type', function () {
        toggleExpenseTypeFields($(this).val());
    });

    // Handle Save Expense Form Submission
    $(document).off('click', '#saveExpenseBtn').on('click', '#saveExpenseBtn', function (e) {
        e.preventDefault();
        var form = $('#saveExpenseForm')[0];
        if (form.checkValidity()) {
            var formData = new FormData(form);
            var $btn = $(this);
            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: base_url + '/app/expense_controller.php?action=save_expense',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        swal('Error', response.msg, 'error');
                    } else {
                        toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                        $('#addExpenseModal').modal('hide');
                        $('#saveExpenseForm')[0].reset();
                        $('#expense_property_select').selectpicker('refresh');
                        $('#expensesTable').DataTable().ajax.reload();
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error: " + status + " - " + error);
                    console.error(xhr.responseText);
                    swal('Error', 'An unexpected error occurred. Please check the console for details.', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false).text('Save Expense');
                }
            });
        } else {
            form.reportValidity();
        }
    });

    // Reset Modal on close
    $(document).on('hidden.bs.modal', '#addExpenseModal', function () {
        $('#saveExpenseForm')[0].reset();
        $('#expense_id').val('');
        $('#expense_property_select').selectpicker('val', '').selectpicker('refresh');
        $('#expense_type').val('Property').trigger('change');
        $('#addExpenseLabel').text('Add Expense');
    });
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
            { "data": "reference_number" },
            { "data": "expense_type" },
            { "data": "property_name" },
            { "data": "category" },
            { "data": "amount" },
            { "data": "expense_date" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[5, "desc"]] // Order by expense_date desc
    });
}

function toggleExpenseTypeFields(type) {
    if (type === 'Property') {
        $('#property_select_container').show();
        $('#expense_property_select').prop('required', true);
    } else {
        $('#property_select_container').hide();
        $('#expense_property_select').prop('required', false).selectpicker('val', '');
    }
    $('#expense_property_select').selectpicker('refresh');
}

function initExpenseForm() {
    // Re-initialize plugins if needed
    $('.selectpicker').selectpicker('refresh');
}

function editExpense(id) {
    $.getJSON(base_url + '/app/expense_controller.php?action=get_expense&id=' + id, function (response) {
        if (!response.error) {
            var data = response.data;
            $('#expense_id').val(data.id);
            $('#expense_type').val(data.expense_type).trigger('change');
            $('#expense_property_select').selectpicker('val', data.property_id).selectpicker('refresh');
            $('#expense_category').val(data.category);
            $('#expense_amount').val(data.amount);
            $('#expense_date').val(data.expense_date);
            $('#expense_description').val(data.description);

            $('#addExpenseLabel').text('Edit Expense');
            $('#addExpenseModal').modal('show');
        } else {
            swal('Error', response.msg, 'error');
        }
    });
}

function deleteExpense(id) {
    swal({
        title: "Are you sure?",
        text: "Once deleted, you will not be able to recover this expense record!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: base_url + '/app/expense_controller.php?action=delete_expense',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        swal('Error', response.msg, 'error');
                    } else {
                        toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                        $('#expensesTable').DataTable().ajax.reload();
                    }
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error: " + status + " - " + error);
                    console.error(xhr.responseText);
                    swal('Error', 'An unexpected error occurred.', 'error');
                }
            });
        }
    });
}
