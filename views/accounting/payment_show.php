<!-- Payment Show Page -->
<main class="content">

  <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
    <div class="d-flex align-items-center gap-3">
      <a href="<?= baseUri() ?>/payments_received" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back
      </a>
      <h5 class="page-title mb-0">Payment Detail</h5>
    </div>
    <div id="pmt_show_actions"></div>
  </div>

  <div class="page-content fade-in" id="payment_show_wrap">

    <div id="pmt_show_loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-2 text-muted">Loading payment…</p>
    </div>

    <div id="pmt_show_content" class="d-none"></div>

  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
  (function () {
    'use strict';

    var pathParts = window.location.pathname.replace(/\/$/, '').split('/');
    var paymentId = parseInt(pathParts[pathParts.length - 1], 10);
    if (!paymentId) {
      var params = new URLSearchParams(window.location.search);
      paymentId = parseInt(params.get('id') || params.get('payment_id'), 10);
    }

    if (!paymentId) {
      document.getElementById('pmt_show_loading').innerHTML =
        '<div class="alert alert-danger">Payment ID not found in URL.</div>';
      return;
    }

    $.getJSON(base_url + '/app/receipt_controller.php?action=get_payment_show&id=' + paymentId)
      .done(function (data) {
        if (data.error) {
          document.getElementById('pmt_show_loading').innerHTML =
            '<div class="alert alert-danger">' + (data.msg || 'Payment not found.') + '</div>';
          return;
        }
        renderPaymentShow(data);
      })
      .fail(function () {
        document.getElementById('pmt_show_loading').innerHTML =
          '<div class="alert alert-danger">Failed to load payment data.</div>';
      });

    function fmt(n) { return parseFloat(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
    function escHtml(s) {
      if (!s) return '';
      return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
    function appUrl(path) {
      return base_url + '/' + path;
    }

    function renderPaymentShow(d) {
      var methodBadge = { cash: 'success', mobile: 'info', bank: 'primary' };

      var allocRows = (d.allocations && d.allocations.length)
        ? d.allocations.map(function (a) {
            return '<tr>'
              + '<td>' + escHtml(a.description) + '</td>'
              + '<td class="text-end">' + fmt(a.line_total) + '</td>'
              + '<td class="text-end fw-semibold text-primary">' + fmt(a.amount) + '</td>'
              + '</tr>';
          }).join('')
        : '<tr><td colspan="3" class="text-center text-muted py-3">No allocation data found.</td></tr>';

      var allocTotal = (d.allocations || []).reduce(function(s, a) { return s + parseFloat(a.amount || 0); }, 0);

      var html = ''
        + '<div class="row g-3 mb-4">'

        // Payment card
        + '<div class="col-md-6">'
        + '<div class="card border-0 shadow-sm h-100">'
        + '<div class="card-body">'
        + '<div class="d-flex align-items-start justify-content-between mb-3">'
        + '<div>'
        + '<h4 class="fw-bold mb-0">' + escHtml(d.receipt_number || ('#' + d.id)) + '</h4>'
        + '<div class="text-muted small mt-1">Payment Receipt</div>'
        + '</div>'
        + '<span class="badge bg-' + (methodBadge[d.payment_method] || 'secondary') + ' fs-6">' + d.payment_method + '</span>'
        + '</div>'
        + '<table class="table table-sm table-borderless mb-0">'
        + '<tr><td class="text-muted" style="width:130px">Amount</td><td class="fw-bold fs-5 text-success">$' + fmt(d.amount_paid) + '</td></tr>'
        + '<tr><td class="text-muted">Date</td><td>' + (d.received_date || '—') + '</td></tr>'
        + '<tr><td class="text-muted">Tenant</td><td>' + escHtml(d.tenant_name || '—') + '</td></tr>'
        + '<tr><td class="text-muted">Unit</td><td>' + escHtml(d.unit_number || '—') + ' <span class="text-muted">(' + escHtml(d.property_name || '—') + ')</span></td></tr>'
        + (d.notes ? '<tr><td class="text-muted">Notes</td><td>' + escHtml(d.notes) + '</td></tr>' : '')
        + '</table>'
        + '</div></div></div>'

        // Invoice ref card
        + '<div class="col-md-6">'
        + '<div class="card border-0 shadow-sm h-100">'
        + '<div class="card-body">'
        + '<h6 class="fw-bold text-primary mb-3"><i class="bi bi-receipt me-1"></i>Invoice Reference</h6>'
        + '<p class="mb-1"><span class="text-muted me-2">Invoice</span>'
        + '<a href="' + appUrl('invoice/' + (d.invoice_id || '')) + '" class="fw-semibold">'
        + escHtml(d.invoice_ref || ('INV-' + d.invoice_id))
        + '</a></p>'
        + '<p class="mb-0"><span class="text-muted me-2">Status after payment</span>'
        + '<span class="badge bg-' + (d.invoice_status === 'paid' ? 'success' : d.invoice_status === 'partial' ? 'warning' : 'danger') + '">' + (d.invoice_status || '—') + '</span>'
        + '</p>'
        + '</div></div></div>'

        + '</div>'

        // Allocation breakdown
        + '<div class="card border-0 shadow-sm">'
        + '<div class="card-header bg-white fw-bold"><i class="bi bi-diagram-3 me-2 text-primary"></i>Allocation Breakdown</div>'
        + '<div class="card-body p-0">'
        + '<div class="table-responsive">'
        + '<table class="table table-sm align-middle mb-0">'
        + '<thead class="table-light"><tr>'
        + '<th>Item Description</th><th class="text-end">Line Total</th><th class="text-end text-primary">Allocated</th>'
        + '</tr></thead>'
        + '<tbody>' + allocRows + '</tbody>'
        + '<tfoot class="table-active fw-bold"><tr>'
        + '<td colspan="2" class="text-end">Total Allocated</td>'
        + '<td class="text-end text-primary">$' + fmt(allocTotal) + '</td>'
        + '</tr></tfoot>'
        + '</table></div></div></div>';

      document.getElementById('pmt_show_content').innerHTML = html;
      document.getElementById('pmt_show_loading').classList.add('d-none');
      document.getElementById('pmt_show_content').classList.remove('d-none');

      // Action buttons
      if (document.getElementById('pmt_show_actions') && d.tenant_phone && typeof openSmsModal === 'function') {
        document.getElementById('pmt_show_actions').innerHTML =
          '<button class="btn btn-info btn-sm text-white" onclick="openSmsModal(' + d.tenant_id + ',\'' + escHtml(d.tenant_name) + '\',\'' + escHtml(d.tenant_phone) + '\')">'
          + '<i class="bi bi-chat-text me-1"></i>Send SMS to Tenant</button>';
      }
    }
  })();
});
</script>
