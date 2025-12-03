<?php
$conn = $GLOBALS['conn'];
$leases = $conn->query("
    SELECT l.id, t.full_name, u.unit_number 
    FROM leases l
    LEFT JOIN tenants t ON t.id = l.tenant_id
    LEFT JOIN units u ON u.id = l.unit_id
    WHERE l.status='active'
    ORDER BY t.full_name ASC
");
?>

<div class="modal fade" id="addInvoiceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold">Add Rent Invoice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form action="app/invoices.php?action=add" method="POST">

        <div class="modal-body">
          <div class="row g-3">

            <!-- Left column -->
            <div class="col-md-6">
              <div class="card border-0 shadow-sm p-3">
                <h6 class="fw-bold mb-3">Lease Information</h6>

                <div class="mb-3">
                  <label class="form-label">Lease</label>
                  <select name="lease_id" class="form-select" required>
                    <option value="">Select Lease</option>
                    <?php while($lease = $leases->fetch_assoc()): ?>
                      <option value="<?= $lease['id']; ?>">
                        <?= htmlspecialchars($lease['full_name']); ?> — Unit <?= htmlspecialchars($lease['unit_number']); ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">Invoice Date</label>
                  <input type="date" name="invoice_date" class="form-control" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Due Date</label>
                  <input type="date" name="due_date" class="form-control" required>
                </div>
              </div>
            </div>

            <!-- Right column -->
            <div class="col-md-6">
              <div class="card border-0 shadow-sm p-3">
                <h6 class="fw-bold mb-3">Payment Details</h6>

                <div class="mb-3">
                  <label class="form-label">Amount</label>
                  <input type="number" step="0.01" name="amount" class="form-control" required>
                </div>

                <div class="mb-3">
                  <label class="form-label">Status</label>
                  <select name="status" class="form-select" required>
                    <option value="unpaid">Unpaid</option>
                    <option value="paid">Paid</option>
                    <option value="partial">Partial</option>
                  </select>
                </div>

              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer bg-light">
          <button type="submit" class="btn btn-primary px-4">Save Invoice</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>

      </form>

    </div>
  </div>
</div>
