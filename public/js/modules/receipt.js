/* ============================================================
   Receipt Module – with FIFO allocation preview
   ============================================================ */

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('receiptsTable')) {
        loadReceipts();
    }
    if (document.getElementById('addReceiptModal')) {
        initReceiptForm();
    }

    // Amount paid input → update FIFO preview (safe to delegate globally)
    $(document).off('input', '#amount_paid').on('input', '#amount_paid', function () {
        updateFifoPreview();
        validateReceiptAmount();
    });

    // Save receipt
    $(document).on('submit', '#saveReceiptForm', function (e) { e.preventDefault(); });
    $(document).on('click', '#saveReceiptBtn', function () {
        var form = $('#saveReceiptForm')[0];
        if (!form.checkValidity()) { form.reportValidity(); return; }
        if (!validateReceiptAmount()) return;

        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Saving…');

        var formData = new FormData(form);

        $.ajax({
            url: base_url + '/app/receipt_controller.php?action=save_receipt',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (res) {
                if (res.error) {
                    swal('Error', res.msg, 'error');
                } else {
                    toaster.success(res.msg || 'Payment saved.', 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                    $('#addReceiptModal').modal('hide');
                    if ($.fn.DataTable.isDataTable('#receiptsTable')) {
                        $('#receiptsTable').DataTable().ajax.reload();
                    }
                }
            },
            error: function () { swal('Error', 'Unexpected error.', 'error'); },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>Save Payment');
            }
        });
    });

    // ── Modal shown ─────────────────────────────────────────────────
    // Bind the invoice select change handler DIRECTLY on the element here,
    // not via document delegation — delegation doesn't reliably catch
    // bootstrap-select's custom 'changed.bs.select' event due to jQuery
    // interpreting the dot as an event namespace separator.
    $(document).on('shown.bs.modal', '#addReceiptModal', function () {
        var $sel = $('#receipt_invoice_select');

        // Bind directly to the element (not document delegation) so bootstrap-select's
        // triggered native 'change' event is reliably caught.
        $sel.off('change.receipt')
            .on('change.receipt', function () {
                var invoiceId = $sel.val();
                if (invoiceId) {
                    loadInvoiceBalance(invoiceId);
                } else {
                    clearReceiptPanel();
                }
            });

        // Apply pre-queued invoice selection (new receipt from invoice page, or edit mode).
        if (window._receiptPreSelectInvoice) {
            var id       = String(window._receiptPreSelectInvoice);
            var lockSel  = !!window._receiptEditLockInvoice;
            window._receiptPreSelectInvoice = null;
            window._receiptEditLockInvoice  = null;

            if ($.fn.selectpicker) {
                $sel.prop('disabled', lockSel)
                    .selectpicker('val', id)
                    .selectpicker('refresh');
            } else {
                $sel.prop('disabled', lockSel).val(id);
            }
            // Explicitly trigger change so our handler fires and loads the balance,
            // regardless of whether the selectpicker version does it automatically.
            $sel.trigger('change');
        }
    });

    // ── Modal hidden ────────────────────────────────────────────────
    $(document).on('hidden.bs.modal', '#addReceiptModal', function () {
        $('#receipt_invoice_select').off('change.receipt');
        $('#saveReceiptForm')[0].reset();
        $('#receipt_id').val('');
        window._receiptPreSelectInvoice = null;
        window._receiptEditLockInvoice  = null;
        if ($.fn.selectpicker) {
            $('#receipt_invoice_select').prop('disabled', false).selectpicker('val', '').selectpicker('refresh');
        }
        clearReceiptPanel();
    });
});

/* ── Invoice balance / items load ──────────────────────── */
var _receiptInvoiceCache = {};

function loadInvoiceBalance(invoiceId) {
    // Show loading state
    document.getElementById('receipt_items_placeholder').classList.remove('d-none');
    document.getElementById('receipt_items_panel').classList.add('d-none');
    document.getElementById('receipt_inv_summary').classList.add('d-none');

    if (_receiptInvoiceCache[invoiceId]) {
        _applyInvoiceBalance(_receiptInvoiceCache[invoiceId]);
        return;
    }

    $.getJSON(base_url + '/app/receipt_controller.php?action=get_invoice_balance&invoice_id=' + invoiceId, function (res) {
        if (res.error) { clearReceiptPanel(); return; }
        _receiptInvoiceCache[invoiceId] = res;
        _applyInvoiceBalance(res);
    }).fail(function () { clearReceiptPanel(); });
}

function _applyInvoiceBalance(res) {
    // Summary bar
    var $sum = $('#receipt_inv_summary');
    $('#rct_inv_total').text(fmtNum(res.total));
    $('#rct_inv_paid').text(fmtNum(res.total_paid));
    $('#rct_inv_balance').text(fmtNum(res.balance));
    $sum.removeClass('d-none');

    // Pre-fill amount_paid with remaining balance
    if (!$('#receipt_id').val()) {
        $('#amount_paid').val(res.balance > 0 ? parseFloat(res.balance).toFixed(2) : '');
    }

    // Items panel
    var rows = (res.items || []).map(function (it) {
        return '<tr data-line-total="' + it.line_total + '" data-allocated="' + it.allocated + '">'
            + '<td>' + escHtml(it.description) + '</td>'
            + '<td class="text-end">' + fmtNum(it.line_total) + '</td>'
            + '<td class="text-end text-success">' + fmtNum(it.allocated) + '</td>'
            + '<td class="text-end text-danger">' + fmtNum(it.item_balance) + '</td>'
            + '<td class="text-end text-primary fw-semibold item-this-payment">—</td>'
            + '</tr>';
    }).join('');

    $('#receipt_items_body').html(rows);
    document.getElementById('receipt_items_placeholder').classList.add('d-none');
    document.getElementById('receipt_items_panel').classList.remove('d-none');

    // Validate current amount
    validateReceiptAmount(res.balance);
    updateFifoPreview(res);
}

function clearReceiptPanel() {
    $('#receipt_inv_summary').addClass('d-none');
    $('#receipt_items_panel').addClass('d-none');
    $('#receipt_items_placeholder').removeClass('d-none');
    $('#amount_warning').addClass('d-none');
    $('#rct_tbl_total, #rct_tbl_paid, #rct_tbl_balance, #rct_tbl_this').text('—');
}

/* ── FIFO preview ──────────────────────────────────────── */
function updateFifoPreview(balanceData) {
    var amount = parseFloat($('#amount_paid').val()) || 0;
    var $rows  = $('#receipt_items_body tr');

    if (!$rows.length) return;

    // Collect current balances from DOM
    var items = [];
    $rows.each(function () {
        items.push({
            lineTot:   parseFloat($(this).data('line-total')) || 0,
            allocated: parseFloat($(this).data('allocated'))  || 0,
            $el:       $(this)
        });
    });

    var remaining = amount;
    var tTotal = 0, tPaid = 0, tBalance = 0, tThis = 0;

    items.forEach(function (it) {
        var balance = Math.max(0, it.lineTot - it.allocated);
        var alloc   = Math.min(remaining, balance);
        alloc       = Math.round(alloc * 100) / 100;
        remaining   = Math.round((remaining - alloc) * 100) / 100;

        it.$el.find('.item-this-payment').text(alloc > 0 ? fmtNum(alloc) : '—');
        tTotal   += it.lineTot;
        tPaid    += it.allocated;
        tBalance += balance;
        tThis    += alloc;
    });

    $('#rct_tbl_total').text(fmtNum(tTotal));
    $('#rct_tbl_paid').text(fmtNum(tPaid));
    $('#rct_tbl_balance').text(fmtNum(tBalance));
    $('#rct_tbl_this').text(fmtNum(tThis));
}

function validateReceiptAmount(maxBalance) {
    if (!maxBalance) {
        var invoiceId = $('#receipt_invoice_select').val();
        if (invoiceId && _receiptInvoiceCache[invoiceId]) {
            maxBalance = parseFloat(_receiptInvoiceCache[invoiceId].balance);
        }
    }
    if (!maxBalance && maxBalance !== 0) return true;

    var input = parseFloat($('#amount_paid').val()) || 0;
    if (input > maxBalance + 0.01) {
        $('#amount_warning').removeClass('d-none');
        $('#saveReceiptBtn').prop('disabled', true);
        return false;
    } else {
        $('#amount_warning').addClass('d-none');
        $('#saveReceiptBtn').prop('disabled', false);
        return true;
    }
}

/* ── Helpers ────────────────────────────────────────────── */
function initReceiptForm() {
    if ($.fn.selectpicker) {
        $('#addReceiptModal .selectpicker').selectpicker();
    }
}

function fmtNum(n) {
    return parseFloat(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function escHtml(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

/* ── DataTable ──────────────────────────────────────────── */
function loadReceipts() {
    if ($.fn.DataTable.isDataTable('#receiptsTable')) {
        $('#receiptsTable').DataTable().destroy();
    }

    $('#receiptsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: { url: base_url + '/app/receipt_controller.php?action=get_receipts', type: 'POST' },
        columns: [
            { data: 'receipt_number' },
            { data: 'invoice_number' },
            { data: 'tenant_name' },
            { data: 'amount_paid' },
            { data: 'payment_method' },
            { data: 'received_date' },
            { data: 'actions', orderable: false }
        ],
        order: [[5, 'desc']]
    });
}

/* ── Edit ────────────────────────────────────────────────── */
function editReceipt(id) {
    $.getJSON(base_url + '/app/receipt_controller.php?action=get_receipt&id=' + id, function (data) {
        if (!data) { swal('Error', 'Receipt not found.', 'error'); return; }
        $('#receipt_id').val(data.id);
        $('#amount_paid').val(data.amount_paid);
        $('#received_date').val(data.received_date);
        $('#payment_method').val(data.payment_method);
        $('#receipt_notes').val(data.notes);
        // Queue the invoice pre-selection; shown.bs.modal will apply it
        // and also load the balance. Disable flag tells the handler to lock the select.
        window._receiptPreSelectInvoice  = data.invoice_id;
        window._receiptEditLockInvoice   = true;
        $('#addReceiptModal').modal('show');
    });
}

function viewPayment(id) {
    window.location.href = base_url + '/payment/' + id;
}

/* ── Delete ─────────────────────────────────────────────── */
function deleteReceipt(id) {
    swal({ title: 'Delete Payment?', text: 'Invoice status will be recalculated.', icon: 'warning', buttons: true, dangerMode: true })
        .then(function (ok) {
            if (!ok) return;
            $.post(base_url + '/app/receipt_controller.php?action=delete_receipt', { id: id }, function (res) {
                if (res.error) {
                    swal('Error', res.msg, 'error');
                } else {
                    toaster.success(res.msg, 'Deleted', { top: '10%', right: '20px', hide: true, duration: 1500 });
                    if ($.fn.DataTable.isDataTable('#receiptsTable')) {
                        $('#receiptsTable').DataTable().ajax.reload();
                    }
                    // Invalidate cache
                    _receiptInvoiceCache = {};
                }
            }, 'json');
        });
}
