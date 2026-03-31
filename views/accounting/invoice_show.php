<?php
/* ── Org settings for print header ───────────────────────────── */
$_conn    = $GLOBALS['conn'];
$_orgId   = function_exists('current_org_id') ? current_org_id() : 0;
$_clause  = $_orgId > 0 ? "org_id = $_orgId" : "1=1";
$_sRes    = $_conn->query("SELECT setting_key, setting_value FROM system_settings WHERE $_clause");
$_cfg     = [];
if ($_sRes) { while ($_sr = $_sRes->fetch_assoc()) $_cfg[$_sr['setting_key']] = $_sr['setting_value']; }
$_orgName  = htmlspecialchars($_cfg['org_name']  ?? 'Property Management');
$_orgPhone = htmlspecialchars($_cfg['org_phone'] ?? '');
$_orgEmail = htmlspecialchars($_cfg['org_email'] ?? '');
$_logoUrl  = $GLOBALS['logoPath'] ?? (baseUri() . '/public/images/logo.png');
$_baseUri  = baseUri();
?>
<!-- Invoice Show Page -->
<main class="content">

  <!-- Back + title row -->
  <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
    <div class="d-flex align-items-center gap-3">
      <a href="<?= baseUri() ?>/invoices" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back
      </a>
      <h5 class="page-title mb-0">Invoice Detail</h5>
    </div>
    <div id="inv_show_actions" class="d-flex gap-2"></div>
  </div>

  <div class="page-content fade-in" id="invoice_show_wrap">
    <div id="inv_show_loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2 text-muted">Loading invoice…</p>
    </div>
    <div id="inv_show_content" class="d-none"></div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
  (function () {
    'use strict';

    /* ── Config passed from PHP ──────────────────────────────── */
    var ORG_NAME  = <?= json_encode($_orgName) ?>;
    var ORG_PHONE = <?= json_encode($_orgPhone) ?>;
    var ORG_EMAIL = <?= json_encode($_orgEmail) ?>;
    var LOGO_URL  = <?= json_encode($_logoUrl) ?>;
    var BASE      = <?= json_encode($_baseUri) ?>;

    /* ── Resolve invoice ID ──────────────────────────────────── */
    var pathParts = window.location.pathname.replace(/\/$/, '').split('/');
    var invoiceId = parseInt(pathParts[pathParts.length - 1], 10);
    if (!invoiceId) {
      var params = new URLSearchParams(window.location.search);
      invoiceId = parseInt(params.get('id') || params.get('invoice_id'), 10);
    }
    if (!invoiceId) {
      document.getElementById('inv_show_loading').innerHTML =
        '<div class="alert alert-danger">Invoice ID not found in URL.</div>';
      return;
    }

    /* ── Fetch data ──────────────────────────────────────────── */
    $.getJSON(BASE + '/app/invoice_controller.php?action=get_invoice_show&id=' + invoiceId)
      .done(function (data) {
        if (data.error) {
          document.getElementById('inv_show_loading').innerHTML =
            '<div class="alert alert-danger">' + (data.msg || 'Invoice not found.') + '</div>';
          return;
        }
        renderInvoiceShow(data);
      })
      .fail(function () {
        document.getElementById('inv_show_loading').innerHTML =
          '<div class="alert alert-danger">Failed to load invoice data.</div>';
      });

    /* ── Helpers ─────────────────────────────────────────────── */
    function escHtml(s) {
      if (!s) return '';
      return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function fmt(n) {
      return parseFloat(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function monthName(m) {
      return ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][parseInt(m,10)] || m;
    }
    function statusBadge(s) {
      var map = { paid: 'success', partial: 'warning', unpaid: 'danger' };
      return '<span class="badge bg-' + (map[s]||'secondary') + ' text-uppercase">' + (s||'—') + '</span>';
    }
    function summaryCard(label, val, color) {
      return '<div class="col-6 col-md-4"><div class="text-center p-2 rounded bg-light">'
        + '<div class="small text-muted">' + label + '</div>'
        + '<div class="fw-bold text-' + color + '">' + val + '</div></div></div>';
    }

    /* ── Render show page ────────────────────────────────────── */
    function renderInvoiceShow(d) {
      var subtotal = 0, taxTotal = 0, grandTotal = 0, paidTotal = 0;
      d.items.forEach(function (it) {
        subtotal   += parseFloat(it.unit_price) * parseFloat(it.qty);
        taxTotal   += parseFloat(it.tax_amount  || 0);
        grandTotal += parseFloat(it.line_total  || 0);
        paidTotal  += parseFloat(it.allocated   || 0);
      });
      var balance = grandTotal - paidTotal;

      var itemRows = d.items.map(function (it) {
        return '<tr>'
          + '<td>' + escHtml(it.description) + '</td>'
          + '<td class="text-end">' + fmt(it.qty) + '</td>'
          + '<td class="text-end">' + fmt(it.unit_price) + '</td>'
          + '<td class="text-end">' + fmt(it.tax_rate) + '%</td>'
          + '<td class="text-end">' + fmt(it.tax_amount) + '</td>'
          + '<td class="text-end fw-semibold">' + fmt(it.line_total) + '</td>'
          + '<td class="text-end text-success">' + fmt(it.allocated) + '</td>'
          + '<td class="text-end text-danger">' + fmt(it.item_balance) + '</td>'
          + '</tr>';
      }).join('');

      var pmtRows = d.payments.length
        ? d.payments.map(function (p) {
            var mBadge = { cash:'success', mobile:'info', bank:'primary' };
            return '<tr>'
              + '<td>' + escHtml(p.receipt_number || ('RCT-' + p.id)) + '</td>'
              + '<td>' + p.received_date + '</td>'
              + '<td><span class="badge bg-' + (mBadge[p.payment_method]||'secondary') + '">' + p.payment_method + '</span></td>'
              + '<td class="text-end fw-semibold">' + fmt(p.amount_paid) + '</td>'
              + '<td>' + (escHtml(p.notes) || '—') + '</td>'
              + '<td><a href="' + BASE + '/payment/' + p.id + '" class="btn btn-outline-primary btn-xs"><i class="bi bi-eye"></i></a></td>'
              + '</tr>';
          }).join('')
        : '<tr><td colspan="6" class="text-center text-muted py-3">No payments recorded yet.</td></tr>';

      var invoiceType = d.invoice_type === 'rent' ? 'Rent Invoice' : ('Other: ' + (d.charge_type_name || ''));

      var html = ''
        + '<div class="row g-3 mb-4">'
        + '<div class="col-md-6"><div class="card border-0 shadow-sm h-100"><div class="card-body">'
        + '<div class="d-flex align-items-start justify-content-between mb-3">'
        + '<div><h4 class="fw-bold mb-0">' + escHtml(d.reference_number || ('#' + d.id)) + '</h4>'
        + '<div class="text-muted small mt-1">' + invoiceType + '</div></div>'
        + statusBadge(d.status) + '</div>'
        + '<table class="table table-sm table-borderless mb-0">'
        + '<tr><td class="text-muted" style="width:120px">Tenant</td><td class="fw-semibold">' + escHtml(d.tenant_name || '—') + '</td></tr>'
        + '<tr><td class="text-muted">Unit</td><td>' + escHtml(d.unit_number || '—') + ' <span class="text-muted">(' + escHtml(d.property_name || '—') + ')</span></td></tr>'
        + '<tr><td class="text-muted">Invoice Date</td><td>' + (d.invoice_date || '—') + '</td></tr>'
        + '<tr><td class="text-muted">Due Date</td><td>' + (d.due_date || '—') + '</td></tr>'
        + (d.billing_month ? '<tr><td class="text-muted">Billing Period</td><td>' + monthName(d.billing_month) + ' ' + d.billing_year + '</td></tr>' : '')
        + (d.notes ? '<tr><td class="text-muted">Notes</td><td>' + escHtml(d.notes) + '</td></tr>' : '')
        + '</table></div></div></div>'

        + '<div class="col-md-6"><div class="card border-0 shadow-sm h-100"><div class="card-body">'
        + '<h6 class="fw-bold text-primary mb-3"><i class="bi bi-cash-stack me-1"></i>Payment Summary</h6>'
        + '<div class="row g-3">'
        + summaryCard('Subtotal', fmt(subtotal), 'primary')
        + summaryCard('Tax',      fmt(taxTotal),  'secondary')
        + summaryCard('Total',    fmt(grandTotal), 'dark')
        + summaryCard('Paid',     fmt(paidTotal),  'success')
        + summaryCard('Balance',  fmt(balance),    balance > 0 ? 'danger' : 'success')
        + '</div></div></div></div></div>'

        + '<div class="card border-0 shadow-sm mb-4">'
        + '<div class="card-header bg-white fw-bold"><i class="bi bi-list-ul me-2 text-primary"></i>Invoice Items</div>'
        + '<div class="card-body p-0"><div class="table-responsive">'
        + '<table class="table table-sm align-middle mb-0"><thead class="table-light"><tr>'
        + '<th>Description</th><th class="text-end">Qty</th><th class="text-end">Unit Price</th>'
        + '<th class="text-end">Tax %</th><th class="text-end">Tax Amt</th>'
        + '<th class="text-end">Line Total</th><th class="text-end text-success">Allocated</th>'
        + '<th class="text-end text-danger">Balance</th>'
        + '</tr></thead><tbody>' + itemRows + '</tbody>'
        + '<tfoot class="table-active fw-bold"><tr>'
        + '<td colspan="5" class="text-end">Totals</td>'
        + '<td class="text-end">' + fmt(grandTotal) + '</td>'
        + '<td class="text-end text-success">' + fmt(paidTotal) + '</td>'
        + '<td class="text-end text-danger">' + fmt(balance) + '</td>'
        + '</tr></tfoot></table></div></div></div>'

        + '<div class="card border-0 shadow-sm">'
        + '<div class="card-header bg-white fw-bold"><i class="bi bi-credit-card me-2 text-primary"></i>Payments Received</div>'
        + '<div class="card-body p-0"><div class="table-responsive">'
        + '<table class="table table-sm align-middle mb-0"><thead class="table-light"><tr>'
        + '<th>Receipt #</th><th>Date</th><th>Method</th><th class="text-end">Amount</th><th>Notes</th><th></th>'
        + '</tr></thead><tbody>' + pmtRows + '</tbody>'
        + '</table></div></div></div>';

      document.getElementById('inv_show_content').innerHTML = html;
      document.getElementById('inv_show_loading').classList.add('d-none');
      document.getElementById('inv_show_content').classList.remove('d-none');

      /* ── Action buttons ──────────────────────────────────── */
      /* Store data globally so onclick handlers can reference it safely */
      window._currentInvoiceData = d;

      var actionsHtml = '<button class="btn btn-outline-secondary btn-sm" onclick="openPrintWindow(window._currentInvoiceData)">'
        + '<i class="bi bi-printer me-1"></i>Print</button>'
        + ' <a href="' + BASE + '/pdf.php?print=invoice&id=' + d.id + '" class="btn btn-danger btn-sm" target="_blank">'
        + '<i class="bi bi-file-earmark-pdf me-1"></i>Download PDF</a>';
      if (d.status !== 'paid') {
        actionsHtml += ' <button class="btn btn-success btn-sm" onclick="window._receiptPreSelectInvoice=' + d.id + '; $(\'#addReceiptModal\').modal(\'show\');">'
          + '<i class="bi bi-cash-coin me-1"></i>Record Payment</button>';
      }
      if (typeof openSmsModal === 'function' && d.tenant_phone) {
        actionsHtml += ' <button class="btn btn-info btn-sm text-white" onclick="openSmsModal(' + d.tenant_id + ', \'' + escHtml(d.tenant_name) + '\', \'' + escHtml(d.tenant_phone) + '\')">'
          + '<i class="bi bi-chat-text me-1"></i>Remind Tenant</button>';
      }
      document.getElementById('inv_show_actions').innerHTML = actionsHtml;
    }

    /* ══════════════════════════════════════════════════════════
       PRINT WINDOW
    ══════════════════════════════════════════════════════════ */
    window.openPrintWindow = function (d) {
      var subtotal = 0, taxTotal = 0, grandTotal = 0, paidTotal = 0;
      d.items.forEach(function (it) {
        subtotal   += parseFloat(it.unit_price) * parseFloat(it.qty);
        taxTotal   += parseFloat(it.tax_amount  || 0);
        grandTotal += parseFloat(it.line_total  || 0);
        paidTotal  += parseFloat(it.allocated   || 0);
      });
      var balance     = grandTotal - paidTotal;
      var invoiceType = d.invoice_type === 'rent' ? 'Rent Invoice' : ('Other Charges' + (d.charge_type_name ? ': ' + d.charge_type_name : ''));
      var billingPeriod = d.billing_month
        ? (monthName(d.billing_month) + ' ' + d.billing_year) : '—';

      /* Status label + colour */
      var statusColor = { paid: '#16a34a', partial: '#d97706', unpaid: '#dc2626' };
      var statusLabel = { paid: 'PAID', partial: 'PARTIAL', unpaid: 'UNPAID' };
      var sColor = statusColor[d.status] || '#64748b';
      var sLabel = statusLabel[d.status] || (d.status || '').toUpperCase();

      /* Item rows */
      var itemsHtml = d.items.length ? d.items.map(function (it) {
        return '<tr>'
          + '<td style="padding:8px 10px">' + escHtml(it.description) + '</td>'
          + '<td style="padding:8px 10px;text-align:right">' + fmt(it.qty) + '</td>'
          + '<td style="padding:8px 10px;text-align:right">' + fmt(it.unit_price) + '</td>'
          + '<td style="padding:8px 10px;text-align:right">' + fmt(it.tax_rate) + '%</td>'
          + '<td style="padding:8px 10px;text-align:right">' + fmt(it.tax_amount) + '</td>'
          + '<td style="padding:8px 10px;text-align:right;font-weight:600">' + fmt(it.line_total) + '</td>'
          + '</tr>';
      }).join('') : '<tr><td colspan="6" style="text-align:center;color:#94a3b8;padding:16px">No items.</td></tr>';

      /* Payment rows */
      var pmtHtml = d.payments.length ? d.payments.map(function (p) {
        return '<tr>'
          + '<td style="padding:7px 10px">' + escHtml(p.receipt_number || ('RCT-' + p.id)) + '</td>'
          + '<td style="padding:7px 10px">' + p.received_date + '</td>'
          + '<td style="padding:7px 10px;text-transform:capitalize">' + (p.payment_method || '—') + '</td>'
          + '<td style="padding:7px 10px;text-align:right;font-weight:600;color:#16a34a">' + fmt(p.amount_paid) + '</td>'
          + '<td style="padding:7px 10px;color:#64748b">' + (escHtml(p.notes) || '—') + '</td>'
          + '</tr>';
      }).join('') : '<tr><td colspan="5" style="text-align:center;color:#94a3b8;padding:12px">No payments recorded.</td></tr>';

      var doc = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">'
        + '<meta name="viewport" content="width=device-width,initial-scale=1">'
        + '<title>Invoice ' + escHtml(d.reference_number || d.id) + '</title>'
        + '<style>'
        + '*{box-sizing:border-box;margin:0;padding:0}'
        + 'body{font-family:"Segoe UI",Arial,sans-serif;font-size:13px;color:#1e293b;background:#f1f5f9;}'
        + '.page{background:#fff;max-width:820px;margin:24px auto;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.10);}'
        /* Header band */
        + '.inv-header{background:#1d3354;color:#fff;padding:28px 36px;display:flex;align-items:center;justify-content:space-between;}'
        + '.inv-header-left{display:flex;align-items:center;gap:16px;}'
        + '.inv-logo{height:52px;width:auto;border-radius:6px;background:#fff;padding:4px 6px;}'
        + '.inv-org-name{font-size:1.2rem;font-weight:700;letter-spacing:.02em;}'
        + '.inv-org-sub{font-size:.78rem;opacity:.72;margin-top:2px;}'
        + '.inv-title-block{text-align:right;}'
        + '.inv-title{font-size:2rem;font-weight:800;letter-spacing:.06em;opacity:.18;line-height:1;}'
        + '.inv-ref{font-size:1.05rem;font-weight:700;margin-top:4px;}'
        + '.inv-status{display:inline-block;padding:3px 14px;border-radius:20px;font-size:.72rem;font-weight:700;letter-spacing:.06em;margin-top:6px;}'
        /* Body padding */
        + '.inv-body{padding:28px 36px;}'
        /* Info grid */
        + '.inv-meta{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px;}'
        + '.meta-box{background:#f8fafc;border-radius:8px;padding:16px 18px;border-left:4px solid #1d3354;}'
        + '.meta-box.green{border-left-color:#16a34a;}'
        + '.meta-title{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;margin-bottom:10px;}'
        + '.meta-row{display:flex;justify-content:space-between;font-size:.84rem;padding:3px 0;border-bottom:1px solid #f1f5f9;}'
        + '.meta-row:last-child{border-bottom:none;}'
        + '.meta-label{color:#64748b;}'
        + '.meta-val{font-weight:600;color:#1e293b;text-align:right;max-width:60%;}'
        /* Tables */
        + '.inv-section-title{font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#1d3354;padding:0 0 8px;border-bottom:2px solid #1d3354;margin-bottom:0;}'
        + '.inv-table{width:100%;border-collapse:collapse;font-size:.84rem;margin-bottom:24px;}'
        + '.inv-table thead tr{background:#f8fafc;}'
        + '.inv-table th{padding:9px 10px;text-align:left;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;border-bottom:2px solid #e2e8f0;}'
        + '.inv-table th.r,.inv-table td.r{text-align:right;}'
        + '.inv-table tbody tr{border-bottom:1px solid #f1f5f9;}'
        + '.inv-table tbody tr:last-child{border-bottom:none;}'
        + '.inv-table tfoot tr{background:#f8fafc;font-weight:700;border-top:2px solid #e2e8f0;}'
        + '.inv-table tfoot td{padding:9px 10px;}'
        /* Totals box */
        + '.totals-box{display:flex;justify-content:flex-end;margin-bottom:28px;}'
        + '.totals-inner{min-width:260px;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;}'
        + '.tot-row{display:flex;justify-content:space-between;padding:8px 16px;border-bottom:1px solid #f1f5f9;font-size:.88rem;}'
        + '.tot-row:last-child{border-bottom:none;}'
        + '.tot-row.grand{background:#1d3354;color:#fff;font-weight:700;font-size:.95rem;}'
        + '.tot-row.balance-due{font-weight:700;font-size:.92rem;}'
        + '.tot-label{color:#64748b;}'
        + '.tot-row.grand .tot-label,.tot-row.grand .tot-val{color:#fff;}'
        /* Balance due banner */
        + '.balance-banner{border-radius:8px;padding:14px 20px;display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;}'
        + '.balance-banner.paid{background:#dcfce7;border:1px solid #86efac;}'
        + '.balance-banner.unpaid{background:#fef2f2;border:1px solid #fca5a5;}'
        + '.balance-banner.partial{background:#fffbeb;border:1px solid #fcd34d;}'
        /* Footer */
        + '.inv-footer{background:#f8fafc;border-top:1px solid #e2e8f0;padding:16px 36px;display:flex;justify-content:space-between;align-items:center;font-size:.78rem;color:#94a3b8;}'
        /* Print button */
        + '.print-bar{text-align:center;padding:18px;background:#f8fafc;border-top:1px solid #e2e8f0;}'
        + '.btn-print{background:#1d3354;color:#fff;border:none;padding:10px 32px;border-radius:8px;font-size:.9rem;font-weight:600;cursor:pointer;margin:0 6px;}'
        + '.btn-close-win{background:#e2e8f0;color:#475569;border:none;padding:10px 24px;border-radius:8px;font-size:.9rem;cursor:pointer;margin:0 6px;}'
        + '@media print{'
        + '.print-bar{display:none!important}'
        + 'body{background:#fff}'
        + '.page{box-shadow:none;margin:0;border-radius:0}'
        + '}'
        + '</style></head><body>'

        /* ── Page ─────────────────────────────────────────────── */
        + '<div class="page">'

        /* Header */
        + '<div class="inv-header">'
        + '<div class="inv-header-left">'
        + '<img src="' + LOGO_URL + '" class="inv-logo" onerror="this.style.display=\'none\'">'
        + '<div>'
        + '<div class="inv-org-name">' + escHtml(ORG_NAME) + '</div>'
        + (ORG_PHONE || ORG_EMAIL ? '<div class="inv-org-sub">'
            + (ORG_PHONE ? escHtml(ORG_PHONE) : '')
            + (ORG_PHONE && ORG_EMAIL ? ' &nbsp;·&nbsp; ' : '')
            + (ORG_EMAIL ? escHtml(ORG_EMAIL) : '')
            + '</div>' : '')
        + '</div></div>'
        + '<div class="inv-title-block">'
        + '<div class="inv-title">INVOICE</div>'
        + '<div class="inv-ref">' + escHtml(d.reference_number || ('#' + d.id)) + '</div>'
        + '<div><span class="inv-status" style="background:' + sColor + '33;color:' + sColor + ';border:1px solid ' + sColor + '55">' + sLabel + '</span></div>'
        + '</div></div>'

        /* Body */
        + '<div class="inv-body">'

        /* Meta grid */
        + '<div class="inv-meta">'
        + '<div class="meta-box">'
        + '<div class="meta-title">Billed To</div>'
        + '<div class="meta-row"><span class="meta-label">Tenant</span><span class="meta-val">' + escHtml(d.tenant_name || '—') + '</span></div>'
        + '<div class="meta-row"><span class="meta-label">Unit</span><span class="meta-val">' + escHtml(d.unit_number || '—') + '</span></div>'
        + '<div class="meta-row"><span class="meta-label">Property</span><span class="meta-val">' + escHtml(d.property_name || '—') + '</span></div>'
        + '</div>'
        + '<div class="meta-box green">'
        + '<div class="meta-title">Invoice Details</div>'
        + '<div class="meta-row"><span class="meta-label">Type</span><span class="meta-val">' + escHtml(invoiceType) + '</span></div>'
        + '<div class="meta-row"><span class="meta-label">Invoice Date</span><span class="meta-val">' + escHtml(d.invoice_date || '—') + '</span></div>'
        + '<div class="meta-row"><span class="meta-label">Due Date</span><span class="meta-val">' + escHtml(d.due_date || '—') + '</span></div>'
        + (d.billing_month ? '<div class="meta-row"><span class="meta-label">Billing Period</span><span class="meta-val">' + billingPeriod + '</span></div>' : '')
        + '</div></div>'

        /* Items table */
        + '<div class="inv-section-title">Invoice Items</div>'
        + '<table class="inv-table"><thead><tr>'
        + '<th>Description</th><th class="r">Qty</th><th class="r">Unit Price</th>'
        + '<th class="r">Tax %</th><th class="r">Tax Amt</th><th class="r">Line Total</th>'
        + '</tr></thead><tbody>' + itemsHtml + '</tbody></table>'

        /* Totals */
        + '<div class="totals-box"><div class="totals-inner">'
        + '<div class="tot-row"><span class="tot-label">Subtotal</span><span class="tot-val">' + fmt(subtotal) + '</span></div>'
        + '<div class="tot-row"><span class="tot-label">Tax</span><span class="tot-val">' + fmt(taxTotal) + '</span></div>'
        + '<div class="tot-row grand"><span class="tot-label">Total</span><span class="tot-val">' + fmt(grandTotal) + '</span></div>'
        + '<div class="tot-row"><span class="tot-label">Paid</span><span class="tot-val" style="color:#16a34a">- ' + fmt(paidTotal) + '</span></div>'
        + '<div class="tot-row balance-due" style="background:' + (balance > 0 ? '#fef2f2' : '#f0fdf4') + '">'
        + '<span class="tot-label" style="color:' + (balance > 0 ? '#dc2626' : '#16a34a') + ';font-weight:700">Balance Due</span>'
        + '<span class="tot-val" style="color:' + (balance > 0 ? '#dc2626' : '#16a34a') + '">' + fmt(balance) + '</span></div>'
        + '</div></div>'

        /* Balance banner */
        + (d.status === 'paid'
            ? '<div class="balance-banner paid"><span style="color:#16a34a;font-weight:700">✓ This invoice is fully paid.</span><span style="color:#16a34a;font-size:.85rem">Thank you!</span></div>'
            : (balance > 0
                ? '<div class="balance-banner unpaid"><span style="color:#dc2626;font-weight:700">Balance of ' + fmt(balance) + ' is outstanding.</span><span style="color:#dc2626;font-size:.85rem">Payment due: ' + escHtml(d.due_date || '') + '</span></div>'
                : ''))

        /* Payments section */
        + '<div class="inv-section-title">Payments Received</div>'
        + '<table class="inv-table" style="margin-top:0"><thead><tr>'
        + '<th>Receipt #</th><th>Date</th><th>Method</th><th class="r">Amount</th><th>Notes</th>'
        + '</tr></thead><tbody>' + pmtHtml + '</tbody></table>'

        /* Notes */
        + (d.notes ? '<div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:12px 16px;margin-bottom:24px;font-size:.84rem"><strong>Notes:</strong> ' + escHtml(d.notes) + '</div>' : '')

        + '</div>'/* /inv-body */

        /* Footer */
        + '<div class="inv-footer">'
        + '<span>' + escHtml(ORG_NAME) + '</span>'
        + '<span>Generated on ' + new Date().toLocaleDateString('en-GB', { day:'numeric', month:'short', year:'numeric' }) + '</span>'
        + '</div>'

        /* Print bar */
        + '<div class="print-bar">'
        + '<button class="btn-print" onclick="window.print()"><svg style="vertical-align:middle;margin-right:6px" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/><path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/></svg>Print / Save PDF</button>'
        + '<button class="btn-close-win" onclick="window.close()">Close</button>'
        + '</div>'

        + '</div>'/* /page */
        + '</body></html>';

      var pw = window.open('', '_blank', 'width=900,height=700,scrollbars=yes');
      pw.document.open();
      pw.document.write(doc);
      pw.document.close();
    };

  })();
});
</script>
<?php require 'views/accounting/modals/add_receipt.php'; ?>
<script src="<?= baseUri(); ?>/public/js/modules/receipt.js"></script>
