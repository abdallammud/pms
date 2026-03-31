/* ============================================================
   Invoice Module – with Line Items support
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('invoicesTable')) {
        loadInvoices();
    }

    // Modal show
    $(document).on('show.bs.modal', '#addInvoiceModal', function () {
        initInvoiceForm();
        // Add a default item row if none exist
        if ($('#invoiceItemsBody').children().length === 0) {
            addInvoiceItemRow();
        }
    });

    // Invoice type change
    $(document).off('change', '#invoice_type').on('change', '#invoice_type', function () {
        toggleInvoiceTypeFields($(this).val());
    });

    // Lease change – auto-fill rent item when rent type
    $(document).off('change', '#lease_id').on('change', '#lease_id', function () {
        if ($('#invoice_type').val() === 'rent') {
            var leaseId = $(this).val();
            if (Array.isArray(leaseId)) leaseId = leaseId[0];
            if (leaseId) fetchLeaseRentForItems(leaseId);
        }
    });

    // Charge type default amount
    $(document).off('change', '#charge_type_id').on('change', '#charge_type_id', function () {
        var def = $(this).find('option:selected').data('amount');
        if (def && $('#invoiceItemsBody tr').length === 1) {
            $('#invoiceItemsBody tr:first .inv-unit-price').val(parseFloat(def).toFixed(2));
            recalcInvoiceItems();
        }
    });

    // Add item row button
    $(document).off('click', '#addInvoiceItemBtn').on('click', '#addInvoiceItemBtn', function () {
        addInvoiceItemRow();
    });

    // Item field live recalc (delegated)
    $(document).off('input change', '#invoiceItemsBody .inv-qty, #invoiceItemsBody .inv-unit-price, #invoiceItemsBody .inv-tax-rate')
               .on('input change', '#invoiceItemsBody .inv-qty, #invoiceItemsBody .inv-unit-price, #invoiceItemsBody .inv-tax-rate', function () {
        recalcRow($(this).closest('tr'));
        recalcInvoiceTotals();
    });

    // Remove item row (delegated)
    $(document).off('click', '.inv-remove-row').on('click', '.inv-remove-row', function () {
        var $row = $(this).closest('tr');
        if ($('#invoiceItemsBody tr').length > 1) {
            $row.remove();
            recalcInvoiceTotals();
        } else {
            swal('', 'An invoice must have at least one item.', 'warning');
        }
    });

    // Save Invoice
    $(document).on('submit', '#saveInvoiceForm', function (e) { e.preventDefault(); });
    $(document).on('click', '#saveInvoiceBtn', function (e) {
        e.preventDefault();
        var form = $('#saveInvoiceForm')[0];

        // Validate at least one non-empty item
        var hasItem = false;
        $('#invoiceItemsBody tr').each(function () {
            if ($.trim($(this).find('.inv-description').val())) { hasItem = true; return false; }
        });
        if (!hasItem) {
            swal('Validation', 'Please add at least one invoice item.', 'warning');
            return;
        }

        if (!form.checkValidity()) { form.reportValidity(); return; }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving…');

        var formData = new FormData(form);

        // Append lease_id properly
        var $leaseSelect = $('#lease_id');
        var selLeases = $leaseSelect.val();
        if (!Array.isArray(selLeases)) selLeases = [selLeases];
        if ($leaseSelect.prop('disabled')) {
            selLeases.forEach(function (v) { if (v) formData.append('lease_id[]', v); });
        }

        // Collect item rows
        formData.delete('item_description[]');
        formData.delete('item_qty[]');
        formData.delete('item_unit_price[]');
        formData.delete('item_tax_rate[]');
        $('#invoiceItemsBody tr').each(function () {
            var desc = $.trim($(this).find('.inv-description').val());
            if (!desc) return;
            formData.append('item_description[]', desc);
            formData.append('item_qty[]',         $(this).find('.inv-qty').val()        || '1');
            formData.append('item_unit_price[]',  $(this).find('.inv-unit-price').val() || '0');
            formData.append('item_tax_rate[]',    $(this).find('.inv-tax-rate').val()   || '0');
        });

        $.ajax({
            url: base_url + '/app/invoice_controller.php?action=save_invoice',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (res.error) {
                    swal('Error', res.msg, 'error');
                } else {
                    toaster.success(res.msg || 'Invoice saved.', 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                    $('#addInvoiceModal').modal('hide');
                    if ($.fn.DataTable.isDataTable('#invoicesTable')) {
                        $('#invoicesTable').DataTable().ajax.reload();
                    }
                }
            },
            error: function () { swal('Error', 'Unexpected error.', 'error'); },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Save Invoice');
            }
        });
    });

    // Reset modal on close
    $(document).on('hidden.bs.modal', '#addInvoiceModal', function () {
        $('#saveInvoiceForm')[0].reset();
        $('#invoice_id').val('');
        $('#invoiceItemsBody').empty();
        recalcInvoiceTotals();
        $('#lease_id').prop('disabled', false).selectpicker('val', []).selectpicker('refresh');
        $('#charge_type_id').selectpicker('val', []).selectpicker('refresh');
        $('#invoice_type').val('rent').trigger('change');
        $('#addInvoiceLabel').html('<i class="bi bi-receipt me-2"></i>Create Invoice');
        $('#saveInvoiceBtn').html('<i class="bi bi-save me-1"></i>Save Invoice');
    });

    // Inline add charge type
    $(document).on('click', '#btnAddNewChargeType', function () {
        swal({ title: 'New Charge Type', text: 'Enter charge type name:', content: 'input', buttons: true })
            .then(function (value) {
                if (value) {
                    $.post(base_url + '/app/charge_type_controller.php?action=save_charge_type',
                        { name: value, status: 'active' }, function (res) {
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

/* ── Init ─────────────────────────────────────────────── */
function initInvoiceForm() {
    if ($.fn.selectpicker) {
        $('#addInvoiceModal .selectpicker').selectpicker();
    }
    loadActiveChargeTypes();
    toggleInvoiceTypeFields('rent');
}

/* ── Invoice type UI toggle ───────────────────────────── */
function toggleInvoiceTypeFields(type) {
    var $chargeContainer = $('#charge_type_container');
    var $leaseSelect     = $('#lease_id');
    var $leaseHelp       = $('#lease_help');

    if (type === 'rent') {
        $chargeContainer.addClass('d-none').find('select').prop('required', false);
        $leaseHelp.text('For rent invoices, select exactly one lease.');
        if ($leaseSelect.prop('multiple')) {
            $leaseSelect.prop('multiple', false).selectpicker('destroy').selectpicker({ liveSearch: true, title: 'Select one lease' });
        }
    } else {
        $chargeContainer.removeClass('d-none').find('select').prop('required', true);
        $leaseHelp.text('Select one or more leases for this charge.');
        if (!$leaseSelect.prop('multiple')) {
            $leaseSelect.prop('multiple', true).selectpicker('destroy').selectpicker({ liveSearch: true, title: 'Select one or more leases' });
        }
    }
    $leaseSelect.selectpicker('refresh');
}

/* ── Line items ───────────────────────────────────────── */
var _itemSeq = 0;

function addInvoiceItemRow(desc, qty, uprc, taxr) {
    _itemSeq++;
    var d    = desc || '';
    var q    = qty  || 1;
    var u    = uprc || '';
    var t    = taxr || 0;
    var ltot = (parseFloat(q) * parseFloat(u || 0) * (1 + parseFloat(t) / 100)).toFixed(2);

    var row = '<tr id="inv_row_' + _itemSeq + '">'
        + '<td><input type="text" class="form-control form-control-sm inv-description" placeholder="Description" value="' + escHtml(d) + '" required></td>'
        + '<td><input type="number" step="0.01" min="0.01" class="form-control form-control-sm inv-qty" value="' + q + '" style="width:70px"></td>'
        + '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm inv-unit-price" placeholder="0.00" value="' + (u || '') + '" style="width:110px"></td>'
        + '<td><input type="number" step="0.01" min="0" max="100" class="form-control form-control-sm inv-tax-rate" value="' + t + '" style="width:70px"></td>'
        + '<td class="text-end fw-semibold inv-line-total">' + (uprc ? ltot : '0.00') + '</td>'
        + '<td><button type="button" class="btn btn-outline-danger btn-sm inv-remove-row"><i class="bi bi-x"></i></button></td>'
        + '</tr>';

    $('#invoiceItemsBody').append(row);
    recalcInvoiceTotals();
}

function recalcRow($tr) {
    var qty   = parseFloat($tr.find('.inv-qty').val())        || 0;
    var uprc  = parseFloat($tr.find('.inv-unit-price').val()) || 0;
    var taxr  = parseFloat($tr.find('.inv-tax-rate').val())   || 0;
    var ltax  = qty * uprc * (taxr / 100);
    var ltot  = qty * uprc + ltax;
    $tr.find('.inv-line-total').text(ltot.toFixed(2));
}

function recalcInvoiceTotals() {
    var subtotal = 0, taxTotal = 0, grand = 0;
    $('#invoiceItemsBody tr').each(function () {
        var qty   = parseFloat($(this).find('.inv-qty').val())        || 0;
        var uprc  = parseFloat($(this).find('.inv-unit-price').val()) || 0;
        var taxr  = parseFloat($(this).find('.inv-tax-rate').val())   || 0;
        var ltax  = qty * uprc * (taxr / 100);
        subtotal += qty * uprc;
        taxTotal += ltax;
        grand    += qty * uprc + ltax;
    });
    $('#inv_subtotal').text(subtotal.toFixed(2));
    $('#inv_tax_total').text(taxTotal.toFixed(2));
    $('#inv_grand_total').text(grand.toFixed(2));
}

function recalcInvoiceItems() {
    $('#invoiceItemsBody tr').each(function () { recalcRow($(this)); });
    recalcInvoiceTotals();
}

/* ── Helpers ──────────────────────────────────────────── */
function loadActiveChargeTypes(selectedId) {
    $.get(base_url + '/app/charge_type_controller.php?action=get_active_charge_types', function (res) {
        if (!res.error) {
            var opts = '<option value="">Select Charge Type</option>';
            (res.data || []).forEach(function (item) {
                opts += '<option value="' + item.id + '" data-amount="' + (item.default_amount || '') + '">' + escHtml(item.name) + '</option>';
            });
            $('#charge_type_id').html(opts).selectpicker('refresh');
            if (selectedId) $('#charge_type_id').selectpicker('val', selectedId);
        }
    }, 'json');
}

function fetchLeaseRentForItems(leaseId) {
    if (!leaseId) return;
    $.get(base_url + '/app/invoice_controller.php?action=get_lease_rent&lease_id=' + leaseId, function (res) {
        if (!res.error && res.rent > 0) {
            var $body = $('#invoiceItemsBody');
            // Update first item or add one
            if ($body.find('tr').length === 0) { addInvoiceItemRow(); }
            var $firstRow = $body.find('tr:first');
            if (!$.trim($firstRow.find('.inv-description').val())) {
                $firstRow.find('.inv-description').val('Rent Charge');
            }
            $firstRow.find('.inv-unit-price').val(parseFloat(res.rent).toFixed(2));
            recalcRow($firstRow);
            recalcInvoiceTotals();
        }
    }, 'json');
}

function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

/* ── DataTable ────────────────────────────────────────── */
function loadInvoices() {
    if ($.fn.DataTable.isDataTable('#invoicesTable')) {
        $('#invoicesTable').DataTable().destroy();
    }

    $('#invoicesTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: base_url + '/app/invoice_controller.php?action=get_invoices', type: 'POST' },
        columns: [
            { data: 'reference_number' },
            { data: 'invoice_type' },
            { data: 'charge_type' },
            { data: 'tenant_name' },
            { data: 'amount' },
            { data: 'billing_period' },
            { data: 'status' },
            { data: 'actions', orderable: false }
        ],
        order: [[0, 'desc']]
    });
}

/* ── Edit Invoice ─────────────────────────────────────── */
function editInvoice(id) {
    $.getJSON(base_url + '/app/invoice_controller.php?action=get_invoice&id=' + id, function (res) {
        if (res.error) { swal('Error', res.msg, 'error'); return; }
        var data = res.data;

        $('#invoice_id').val(data.id);
        $('#invoice_type').val(data.invoice_type).trigger('change');
        $('#invoice_type').prop('disabled', true);

        setTimeout(function () {
            $('#lease_id').selectpicker('val', data.lease_id).prop('disabled', true).selectpicker('refresh');
            if (data.invoice_type === 'other_charge') {
                $('#charge_type_id').selectpicker('val', data.charge_type_id).selectpicker('refresh');
            }
        }, 150);

        $('#invoice_date').val(data.invoice_date);
        $('#due_date').val(data.due_date);
        $('#billing_month').val(data.billing_month);
        $('#billing_year').val(data.billing_year);
        $('#notes').val(data.notes);

        $('#addInvoiceLabel').html('<i class="bi bi-pencil me-2"></i>Edit Invoice #' + (data.reference_number || data.id));
        $('#saveInvoiceBtn').html('<i class="bi bi-save me-1"></i>Update Invoice');

        // Load existing items
        $('#invoiceItemsBody').empty();
        $.getJSON(base_url + '/app/invoice_controller.php?action=get_invoice_items&invoice_id=' + id, function (items) {
            if (items && items.length) {
                items.forEach(function (it) {
                    addInvoiceItemRow(it.description, it.qty, it.unit_price, it.tax_rate);
                });
            } else {
                addInvoiceItemRow();
            }
            $('#addInvoiceModal').modal('show');
        }).fail(function () {
            addInvoiceItemRow();
            $('#addInvoiceModal').modal('show');
        });
    }).fail(function () { swal('Error', 'Could not fetch invoice data.', 'error'); });
}

function viewInvoice(id) {
    window.location.href = base_url + '/invoice/' + id;
}

/* ── Delete Invoice ───────────────────────────────────── */
function deleteInvoice(id) {
    swal({ title: 'Delete Invoice?', text: "This cannot be undone!", icon: 'warning', buttons: true, dangerMode: true })
        .then(function (ok) {
            if (!ok) return;
            $.post(base_url + '/app/invoice_controller.php?action=delete_invoice', { id: id }, function (res) {
                if (res.error) {
                    swal('Error', res.msg, 'error');
                } else {
                    toaster.success(res.msg, 'Deleted', { top: '10%', right: '20px', hide: true, duration: 1500 });
                    if ($.fn.DataTable.isDataTable('#invoicesTable')) {
                        $('#invoicesTable').DataTable().ajax.reload();
                    }
                }
            }, 'json');
        });
}

/* ── Batch invoice opener (from lease table) ──────────── */
function openBatchInvoiceModal(leaseIds, forceType) {
    if (!leaseIds || leaseIds.length === 0) return;
    var $modal = $('#addInvoiceModal');
    var $form  = $('#saveInvoiceForm');
    $form[0].reset();
    $('#invoice_id').val('');
    $('#invoiceItemsBody').empty();

    var type = forceType || (leaseIds.length > 1 ? 'other_charge' : 'rent');
    $('#invoice_type').val(type).trigger('change');

    setTimeout(function () {
        $('#invoice_type').val(type);
        if (forceType) $('#invoice_type').prop('disabled', true);
        $('#lease_id').selectpicker('val', leaseIds).prop('disabled', true).selectpicker('refresh');
        $('#addInvoiceLabel').html('Batch Invoice (' + leaseIds.length + ' leases)');
        $('#saveInvoiceBtn').html('<i class="bi bi-save me-1"></i>Generate Invoices');
        addInvoiceItemRow();
        $modal.modal('show');
    }, 250);
}
