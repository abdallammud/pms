<?php
$conn = $GLOBALS['conn'];
$org_clause = tenant_where_clause();
$invoices = $conn->query("
    SELECT i.id, i.reference_number, i.amount, i.status,
           t.full_name, u.unit_number
    FROM invoices i
    LEFT JOIN leases l ON l.id = i.lease_id
    LEFT JOIN tenants t ON t.id = l.tenant_id
    LEFT JOIN units u ON u.id = l.unit_id
    WHERE i.status IN ('unpaid','partial') AND $org_clause
    ORDER BY i.id DESC
");
?>

<div class="modal fade" id="addReceiptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-cash-coin me-2"></i>Record Payment Received
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="saveReceiptForm">
        <input type="hidden" name="receipt_id" id="receipt_id">

        <div class="modal-body">
          <div class="row g-3">

            <!-- Left: Invoice Selection + Payment Info -->
            <div class="col-lg-4">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                  <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-receipt me-1"></i>Invoice &amp; Payment</h6>

                  <div class="mb-3 multiselect-parent">
                    <label class="form-label fw-semibold multiselect-label">Invoice <span class="text-danger">*</span></label>
                    <select name="invoice_id" id="receipt_invoice_select" class="form-select selectpicker"
                            data-live-search="true" title="Select Invoice" required>
                      <?php while ($i = $invoices->fetch_assoc()): ?>
                        <option value="<?= $i['id'] ?>"
                                data-amount="<?= $i['amount'] ?>"
                                data-subtext="Unit <?= htmlspecialchars($i['unit_number']) ?>">
                          <?= htmlspecialchars($i['reference_number']) ?> —
                          <?= htmlspecialchars($i['full_name']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                  </div>

                  <!-- Invoice balance summary (populated via JS) -->
                  <div id="receipt_inv_summary" class="d-none mb-3">
                    <div class="card bg-light border-0">
                      <div class="card-body p-3">
                        <div class="d-flex justify-content-between small mb-1">
                          <span class="text-muted">Invoice Total</span>
                          <span class="fw-semibold" id="rct_inv_total">—</span>
                        </div>
                        <div class="d-flex justify-content-between small mb-1">
                          <span class="text-muted">Already Paid</span>
                          <span class="fw-semibold text-success" id="rct_inv_paid">—</span>
                        </div>
                        <div class="d-flex justify-content-between small">
                          <span class="text-muted fw-bold">Balance Due</span>
                          <span class="fw-bold text-danger" id="rct_inv_balance">—</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-semibold">Received Date <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                      <input type="text" name="received_date" id="received_date"
                             class="form-control datepicker" value="<?= date('Y-m-d') ?>" required>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-semibold">Amount Paid <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text">$</span>
                      <input type="number" step="0.01" name="amount_paid" id="amount_paid"
                             class="form-control" required min="0.01">
                    </div>
                    <div class="form-text text-danger d-none" id="amount_warning">
                      Amount cannot exceed invoice balance.
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                    <select name="payment_method" id="payment_method" class="form-select" required>
                      <option value="cash">Cash</option>
                      <option value="mobile">Mobile Payment</option>
                      <option value="bank">Bank Transfer</option>
                    </select>
                  </div>

                  <div class="mb-0">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" id="receipt_notes" rows="2" class="form-control"
                              placeholder="Optional notes..."></textarea>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right: Invoice Items breakdown -->
            <div class="col-lg-8">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                  <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-list-check me-1"></i>Invoice Items &amp; Allocation Preview</h6>

                  <div id="receipt_items_placeholder" class="text-center text-muted py-5">
                    <i class="bi bi-receipt fs-1 opacity-25"></i>
                    <p class="mt-2">Select an invoice to see its items.</p>
                  </div>

                  <div id="receipt_items_panel" class="d-none">
                    <div class="table-responsive">
                      <table class="table table-sm align-middle">
                        <thead class="table-light">
                          <tr>
                            <th>Description</th>
                            <th class="text-end">Line Total</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Balance</th>
                            <th class="text-end">This Payment</th>
                          </tr>
                        </thead>
                        <tbody id="receipt_items_body"></tbody>
                        <tfoot>
                          <tr class="table-active fw-bold">
                            <td>Total</td>
                            <td class="text-end" id="rct_tbl_total">—</td>
                            <td class="text-end" id="rct_tbl_paid">—</td>
                            <td class="text-end" id="rct_tbl_balance">—</td>
                            <td class="text-end text-primary" id="rct_tbl_this">—</td>
                          </tr>
                        </tfoot>
                      </table>
                    </div>
                    <div class="alert alert-info small mb-0 mt-2 py-2">
                      <i class="bi bi-info-circle me-1"></i>
                      Payment is allocated to items in order (FIFO). The preview above updates as you type.
                    </div>
                  </div>

                </div>
              </div>
            </div>

          </div>
        </div><!-- /.modal-body -->

        <div class="modal-footer bg-light">
          <button type="submit" class="btn btn-primary px-4" id="saveReceiptBtn">
            <i class="bi bi-save me-1"></i>Save Payment
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>

      </form>
    </div>
  </div>
</div>
