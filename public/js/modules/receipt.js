document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('receiptsTable')) {
        loadReceipts();
        initReceiptForm();
        initBulkActions();

        // Select All Checkbox
        $(document).off('click', '#selectAllReceipts').on('click', '#selectAllReceipts', function () {
            var isChecked = this.checked;
            var table = $('#receiptsTable').DataTable();
            $(table.rows().nodes()).find('.receipt-checkbox').prop('checked', isChecked).trigger('change');
        });

        // Individual checkbox click to update Select All
        $(document).off('click', '.receipt-checkbox').on('click', '.receipt-checkbox', function () {
            var table = $('#receiptsTable').DataTable();
            var total = table.rows().nodes().length;
            var checked = $(table.rows().nodes()).find('.receipt-checkbox:checked').length;
            $('#selectAllReceipts').prop('checked', total > 0 && total === checked);
        });

        // Apply Bulk Action
        $('#applyBulkActionBtnReceipts').on('click', function () {
            var action = $('#bulkActionSelectReceipts').val();
            var selectedIds = [];

            $('.receipt-checkbox:checked').each(function () {
                selectedIds.push($(this).val());
            });

            if (!action) {
                swal('Warning', 'Please select an action.', 'warning');
                return;
            }

            if (selectedIds.length === 0) {
                swal('Warning', 'Please select at least one receipt.', 'warning');
                return;
            }

            swal({
                title: 'Are you sure?',
                text: "You are about to delete " + selectedIds.length + " receipt(s).",
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    performBulkReceiptAction(action, selectedIds);
                }
            });
        });
    }

    // Handle Save Receipt Form Submission
    $(document).on('click', '#saveReceiptBtn', function () {
        var form = $('#saveReceiptForm')[0];
        if (form.checkValidity()) {
            // Additional check for amount capping
            var selectedInvoice = $('#receipt_invoice_select option:selected');
            var maxAmount = parseFloat(selectedInvoice.data('amount')) || 0;
            var inputAmount = parseFloat($('#amount_paid').val()) || 0;

            if (inputAmount > maxAmount) {
                swal('Error', 'Amount paid cannot exceed the invoice amount of ' + maxAmount.toFixed(2), 'error');
                return;
            }

            var formData = new FormData(form);
            var $btn = $(this);
            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: base_url + '/app/receipt_controller.php?action=save_receipt',
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
                        $('#addReceiptModal').modal('hide');
                        $('#saveReceiptForm')[0].reset();
                        $('#receiptsTable').DataTable().ajax.reload();
                        // Refresh invoices in the modal if needed (reloading window might be easier or just refresh the select)
                        location.reload(); // To refresh the invoice list in the PHP modal
                    }
                },
                error: function () {
                    swal('Error', 'An unexpected error occurred.', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false).text('Save Payment');
                }
            });
        } else {
            form.reportValidity();
        }
    });

    // Reset Modal on close
    $(document).on('hidden.bs.modal', '#addReceiptModal', function () {
        $('#saveReceiptForm')[0].reset();
        $('#receipt_id').val('');
        $('#receipt_invoice_select').selectpicker('val', '');
        $('#receipt_invoice_select').prop('disabled', false).selectpicker('refresh');
        $('#amount_warning').addClass('d-none');
    });

    // Invoice selection change
    $(document).on('change', '#receipt_invoice_select', function () {
        var amount = $(this).find(':selected').data('amount');
        if (amount) {
            $('#amount_paid').val(amount);
        } else {
            $('#amount_paid').val('');
        }
        validateAmount();
    });

    // Amount paid validation
    $(document).on('input', '#amount_paid', function () {
        validateAmount();
    });
});

function validateAmount() {
    var selectedInvoice = $('#receipt_invoice_select option:selected');
    var maxAmount = parseFloat(selectedInvoice.data('amount')) || 0;
    var inputAmount = parseFloat($('#amount_paid').val()) || 0;

    if (inputAmount > maxAmount && maxAmount > 0) {
        $('#amount_warning').removeClass('d-none');
        $('#saveReceiptBtn').prop('disabled', true);
    } else {
        $('#amount_warning').addClass('d-none');
        $('#saveReceiptBtn').prop('disabled', false);
    }
}

function initReceiptForm() {
    if ($('.selectpicker').length) {
        $('.selectpicker').selectpicker();
    }
}

function loadReceipts() {
    if ($.fn.DataTable.isDataTable('#receiptsTable')) {
        $('#receiptsTable').DataTable().destroy();
    }

    $('#receiptsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": base_url + "/app/receipt_controller.php?action=get_receipts",
            "type": "POST"
        },
        "columns": [
            { "data": "id_check", "orderable": false },
            { "data": "receipt_number" },
            { "data": "invoice_number" },
            { "data": "tenant_name" },
            { "data": "amount_paid" },
            { "data": "payment_method" },
            { "data": "received_date" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[6, "desc"]], // Order by received_date desc (shifted)
        "drawCallback": function () {
            $('#selectAllReceipts').prop('checked', false);
        }
    });
}

/**
 * Initialize Bulk Actions
 */
function initBulkActions() {

}

/**
 * Perform Bulk Action for Receipts
 */
function performBulkReceiptAction(action, ids) {
    var $btn = $('#applyBulkActionBtnReceipts');
    $btn.prop('disabled', true).text('Processing...');

    $.ajax({
        url: base_url + '/app/receipt_controller.php?action=bulk_action',
        type: 'POST',
        data: {
            action_type: action,
            ids: ids
        },
        dataType: 'json',
        success: function (response) {
            if (response.error) {
                swal('Error', response.msg, 'error');
            } else {
                toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                $('#receiptsTable').DataTable().ajax.reload();
                $('#selectAllReceipts').prop('checked', false);
                $('#bulkActionSelectReceipts').val('');
                location.reload();
            }
        },
        error: function () {
            swal('Error', 'An unexpected error occurred.', 'error');
        },
        complete: function () {
            $btn.prop('disabled', false).text('Apply');
        }
    });
}

function editReceipt(id) {
    $.ajax({
        url: base_url + '/app/receipt_controller.php?action=get_receipt&id=' + id,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data) {
                $('#receipt_id').val(data.id);
                $('#receipt_invoice_select').selectpicker('val', data.invoice_id);
                $('#receipt_invoice_select').prop('disabled', true).selectpicker('refresh');

                $('#amount_paid').val(data.amount_paid);
                $('#received_date').val(data.received_date);
                $('#payment_method').val(data.payment_method);
                $('#receipt_notes').val(data.notes);

                $('#addReceiptModal').modal('show');
            }
        }
    });
}

function deleteReceipt(id) {
    swal({
        title: 'Are you sure?',
        text: "This will also revert the invoice status if needed!",
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: base_url + '/app/receipt_controller.php?action=delete_receipt',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        swal('Error', response.msg, 'error');
                    } else {
                        toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                        $('#receiptsTable').DataTable().ajax.reload();
                        location.reload();
                    }
                }
            });
        }
    });
}
