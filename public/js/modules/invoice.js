document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('invoicesTable')) {
        loadInvoices();
        initBulkActions();

        // Select All Checkbox
        $(document).off('click', '#selectAllInvoices').on('click', '#selectAllInvoices', function () {
            var isChecked = this.checked;
            var table = $('#invoicesTable').DataTable();
            $(table.rows().nodes()).find('.invoice-checkbox').prop('checked', isChecked).trigger('change');
        });

        // Individual checkbox click
        $(document).off('click', '.invoice-checkbox').on('click', '.invoice-checkbox', function () {
            var table = $('#invoicesTable').DataTable();
            var total = table.rows().nodes().length;
            var checked = $(table.rows().nodes()).find('.invoice-checkbox:checked').length;
            $('#selectAllInvoices').prop('checked', total > 0 && total === checked);
        });

        // Apply Bulk Action
        $('#applyBulkActionBtnInvoices').on('click', function () {
            var action = $('#bulkActionSelectInvoices').val();
            var selectedIds = [];

            $('.invoice-checkbox:checked').each(function () {
                selectedIds.push($(this).val());
            });

            if (!action) {
                swal('Warning', 'Please select an action.', 'warning');
                return;
            }

            if (selectedIds.length === 0) {
                swal('Warning', 'Please select at least one invoice.', 'warning');
                return;
            }

            if (action === 'delete') {
                swal({
                    title: 'Are you sure?',
                    text: "You are about to delete " + selectedIds.length + " invoice(s). You won't be able to revert this!",
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        performBulkInvoiceAction(action, selectedIds);
                    }
                });
            }
        });
    }

    // Modal Global Initialization (Works even if dynamically loaded)
    $(document).on('show.bs.modal', '#addInvoiceModal', function () {
        initInvoiceForm();
    });

    // Delegated Listeners for the Invoice Modal (Always active)
    $(document).off('change', '#invoice_type').on('change', '#invoice_type', function () {
        toggleInvoiceTypeFields($(this).val());
    });

    $(document).off('change', '#lease_id').on('change', '#lease_id', function () {
        if ($('#invoice_type').val() === 'rent') {
            var leaseId = $(this).val();
            if (Array.isArray(leaseId)) leaseId = leaseId[0];
            if (leaseId) fetchLeaseRent(leaseId);
        }
    });

    $(document).off('change', '#charge_type_id').on('change', '#charge_type_id', function () {
        var selectedOption = $(this).find('option:selected');
        var defaultAmount = selectedOption.data('amount');
        if (defaultAmount) {
            $('#amount').val(defaultAmount);
        }
    });

    // Handle Save Invoice Form Submission
    $(document).on('click', '#saveInvoiceBtn', function (e) {
        e.preventDefault();
        var form = $('#saveInvoiceForm')[0];
        if (form.checkValidity()) {
            var formData = new FormData(form);
            var $leaseSelect = $('#lease_id');
            var selectedLeases = $leaseSelect.val();

            // Selectpicker multi-select handling
            if (!Array.isArray(selectedLeases)) {
                selectedLeases = [selectedLeases];
            }

            // Re-append leases if disabled (edit mode)
            if ($leaseSelect.prop('disabled')) {
                selectedLeases.forEach(val => {
                    if (val) formData.append('lease_id[]', val);
                });
            }

            // Validation: Rent must be exactly one
            if ($('#invoice_type').val() === 'rent' && selectedLeases.length > 1) {
                swal('Error', 'Rent invoices can only be created for one lease at a time.', 'error');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: base_url + '/app/invoice_controller.php?action=save_invoice',
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
                        $('#addInvoiceModal').modal('hide');
                        $('#saveInvoiceForm')[0].reset();
                        $('#lease_id').selectpicker('refresh');
                        $('#charge_type_id').selectpicker('refresh');
                        $('#invoicesTable').DataTable().ajax.reload();
                    }
                },
                error: function () {
                    swal('Error', 'An unexpected error occurred.', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false).text('Save Invoice');
                }
            });
        } else {
            form.reportValidity();
        }
    });

    // Reset Modal on close
    $(document).on('hidden.bs.modal', '#addInvoiceModal', function () {
        $('#saveInvoiceForm')[0].reset();
        $('#invoice_id').val('');
        $('#lease_id').prop('disabled', false).selectpicker('val', []).selectpicker('refresh');
        $('#charge_type_id').selectpicker('val', []).selectpicker('refresh');
        $('#invoice_type').val('rent').trigger('change');
        $('#addInvoiceLabel').text('Add Invoice');
        $('#saveInvoiceBtn').text('Save Invoice');
    });

    // Inline Add Charge Type
    $(document).on('click', '#btnAddNewChargeType', function () {
        // Here we could show a mini-modal or just prompt
        swal({
            title: "New Charge Type",
            text: "Enter charge type name:",
            content: "input",
            buttons: true,
        }).then((value) => {
            if (value) {
                $.post(base_url + '/app/charge_type_controller.php?action=save_charge_type', { name: value, status: 'active' }, function (res) {
                    if (!res.error) {
                        loadActiveChargeTypes(res.id);
                        toaster.success('Charge type created');
                    } else {
                        swal('Error', res.msg, 'error');
                    }
                }, 'json');
            }
        });
    });
});

function initInvoiceForm() {
    if ($('.selectpicker').length) {
        $('.selectpicker').selectpicker();
    }
    loadActiveChargeTypes();
    toggleInvoiceTypeFields('rent'); // Default
}

function toggleInvoiceTypeFields(type) {
    const $chargeContainer = $('#charge_type_container');
    const $leaseSelect = $('#lease_id');
    const $amount = $('#amount');
    const $amountHelp = $('#amount_help');
    const $leaseLabel = $('#lease_label');
    const $leaseHelp = $('#lease_help');

    if (type === 'rent') {
        $chargeContainer.addClass('d-none').find('select').prop('required', false);
        $amount.prop('readonly', true);
        $amountHelp.text('Rent amount auto-fills from lease.');
        $leaseLabel.text('Lease');
        $leaseHelp.text('For rent invoices, select exactly one lease.');

        // Rent mode: Single select
        if ($leaseSelect.prop('multiple')) {
            $leaseSelect.prop('multiple', false).selectpicker('destroy').selectpicker({
                maxOptions: 1,
                liveSearch: true,
                title: 'Select one lease'
            });
        }
    } else {
        $chargeContainer.removeClass('d-none').find('select').prop('required', true);
        $amount.prop('readonly', false);
        $amountHelp.text('Enter the amount for this charge.');
        $leaseLabel.text('Lease(s)');
        $leaseHelp.text('Select one or more leases for this charge.');

        // Other Charges mode: Multi select
        if (!$leaseSelect.prop('multiple')) {
            $leaseSelect.prop('multiple', true).selectpicker('destroy').selectpicker({
                maxOptions: false,
                liveSearch: true,
                title: 'Select one or more leases'
            });
        }
    }

    $leaseSelect.selectpicker('refresh');
}

function loadActiveChargeTypes(selectedId = null) {
    $.get(base_url + '/app/charge_type_controller.php?action=get_active_charge_types', function (res) {
        if (!res.error) {
            let options = '<option value="">Select Charge Type</option>';
            res.data.forEach(item => {
                options += `<option value="${item.id}" data-amount="${item.default_amount || ''}">${item.name}</option>`;
            });
            $('#charge_type_id').html(options).selectpicker('refresh');
            if (selectedId) {
                $('#charge_type_id').selectpicker('val', selectedId);
            }
        }
    }, 'json');
}

function fetchLeaseRent(leaseId) {
    if (!leaseId) return;
    $.get(base_url + '/app/invoice_controller.php?action=get_lease_rent&lease_id=' + leaseId, function (res) {
        if (!res.error) {
            $('#amount').val(res.rent);
        }
    }, 'json');
}

function loadInvoices() {
    if ($.fn.DataTable.isDataTable('#invoicesTable')) {
        $('#invoicesTable').DataTable().destroy();
    }

    $('#invoicesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": base_url + "/app/invoice_controller.php?action=get_invoices",
            "type": "POST"
        },
        "columns": [
            {
                "data": "id",
                "orderable": false,
                "render": function (data, type, row) {
                    return '<input type="checkbox" class="invoice-checkbox" value="' + data + '">';
                }
            },
            { "data": "reference_number" },
            { "data": "invoice_type" },
            { "data": "charge_type" },
            { "data": "tenant_name" },
            { "data": "amount" },
            { "data": "billing_period" },
            { "data": "status" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[1, "desc"]],
        "drawCallback": function () {
            $('#selectAllInvoices').prop('checked', false);
        }
    });
}

function initBulkActions() { }

function performBulkInvoiceAction(action, ids) {
    var $btn = $('#applyBulkActionBtnInvoices');
    $btn.prop('disabled', true).text('Processing...');

    $.ajax({
        url: base_url + '/app/invoice_controller.php?action=bulk_action',
        type: 'POST',
        data: { action_type: action, ids: ids },
        dataType: 'json',
        success: function (response) {
            if (response.error) {
                swal('Error', response.msg, 'error');
            } else {
                toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                $('#invoicesTable').DataTable().ajax.reload();
                $('#selectAllInvoices').prop('checked', false);
                $('#bulkActionSelectInvoices').val('');
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

function editInvoice(id) {
    $.ajax({
        url: base_url + '/app/invoice_controller.php?action=get_invoice&id=' + id,
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            if (!res.error) {
                const data = res.data;
                $('#invoice_id').val(data.id);
                $('#invoice_type').val(data.invoice_type).trigger('change');

                // For edit, we lock the lease and type
                $('#invoice_type').prop('disabled', true);

                setTimeout(() => {
                    $('#lease_id').selectpicker('val', data.lease_id).prop('disabled', true).selectpicker('refresh');
                    if (data.invoice_type === 'other_charge') {
                        $('#charge_type_id').selectpicker('val', data.charge_type_id).selectpicker('refresh');
                    }
                }, 150);

                $('#invoice_date').val(data.invoice_date);
                $('#due_date').val(data.due_date);
                $('#amount').val(data.amount);
                $('#billing_month').val(data.billing_month);
                $('#billing_year').val(data.billing_year);
                $('#notes').val(data.notes);

                $('#addInvoiceLabel').text('Edit Invoice #' + (data.reference_number || data.id));
                $('#saveInvoiceBtn').text('Update Invoice');

                $('#addInvoiceModal').modal('show');
            } else {
                swal('Error', res.msg, 'error');
            }
        },
        error: function () {
            swal('Error', 'Could not fetch invoice data.', 'error');
        }
    });
}

function deleteInvoice(id) {
    swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.post(base_url + '/app/invoice_controller.php?action=delete_invoice', { id: id }, function (response) {
                if (response.error) {
                    swal('Error', response.msg, 'error');
                } else {
                    toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                    $('#invoicesTable').DataTable().ajax.reload();
                }
            }, 'json');
        }
    });
}

/**
 * Open Invoice Modal for Batch Generation from Lease Table
 */
function openBatchInvoiceModal(leaseIds, forceType = null) {
    if (!leaseIds || leaseIds.length === 0) return;

    var $modal = $('#addInvoiceModal');
    var $form = $('#saveInvoiceForm');
    var $leaseSelect = $('#lease_id');
    var $typeSelect = $('#invoice_type');

    // Reset form and UI state
    $form[0].reset();
    $typeSelect.prop('disabled', false);
    $leaseSelect.prop('disabled', false);

    // Determine type
    let type = forceType;
    if (!type) {
        type = (leaseIds.length > 1) ? 'other_charge' : 'rent';
    }

    // Set type and trigger UI update IMMEDIATELY
    $typeSelect.val(type).trigger('change');

    // Note: If type is other_charge and leaseIds > 1, toggleInvoiceTypeFields was triggered.
    // We must wait for the selectpicker to be reconstructed before setting values.

    setTimeout(() => {
        // Double check the val is set correctly
        $typeSelect.val(type);

        if (forceType) {
            $typeSelect.prop('disabled', true);
        }

        // Set lease values
        $leaseSelect.selectpicker('val', leaseIds);

        // Lock lease selection for bulk action
        $leaseSelect.prop('disabled', true).selectpicker('refresh');

        $('#addInvoiceLabel').text('Batch Invoice Generation (' + leaseIds.length + ')');
        $('#saveInvoiceBtn').text('Generate Invoices');
        $modal.modal('show');
    }, 250); // Increased delay slightly for safer execution
}
