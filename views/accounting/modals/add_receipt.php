<?php
$conn = $GLOBALS['conn'];
$invoices = $conn->query("
    SELECT r.id, r.amount, r.due_date, t.full_name, u.unit_number
    FROM rent_invoices r
    LEFT JOIN leases l ON l.id = r.lease_id
    LEFT JOIN tenants t ON t.id = l.tenant_id
    LEFT JOIN units u ON u.id = l.unit_id
    ORDER BY r.id DESC
");
?>

<div class="modal fade" id="addReceiptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold">Record Payment Received</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form action="app/payments.php?action=add" method="POST">

        <div class="modal-body">
          <div class="row g-3">

            <!-- Left Column -->
            <div class="col-md-6">
              <div class="card border-0 shadow-sm p-3">
                <h6 class="fw-bold mb-3">Invoice Details</h6>

                <!-- Invoice -->
                <div class="mb-3">
                  <label class="form-label">Invoice</label>
                  <select name="invoice_id" class="form-select" required>
                    <option value="">Select Invoice</option>
                    <?php while($i = $invoices->fetch_assoc()): ?>
                      <option value="<?= $i['id']; ?>">
                        Invoice #<?= $i['id']; ?> — 
                        <?= htmlspecialchars($i['full_name']); ?> (Unit <?= htmlspecialchars($i['unit_number']); ?>)
                        — Amount <?= number_format($i['amount'],2); ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">Received Date</label>
                  <input type="date" name="received_date" class="form-control" required>
                </div>
              </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-6">
              <div class="card border-0 shadow-sm p-3">
                <h6 class="fw-bold mb-3">Payment Info</h6>

                <div class="mb-3">
                  <label class="form-label">Amount Paid</label>
                  <input type="number" step="0.01" name="amount_paid" class="form-control" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Payment Method</label>
                  <select name="payment_method" class="form-select" required>
                    <option value="cash">Cash</option>
                    <option value="mobile">Mobile Payment</option>
                    <option value="bank">Bank Transfer</option>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">Notes</label>
                  <textarea name="notes" rows="3" class="form-control"></textarea>
                </div>
              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer bg-light">
          <button type="submit" class="btn btn-primary px-4">Save Payment</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>

      </form>

    </div>
  </div>
</div>
