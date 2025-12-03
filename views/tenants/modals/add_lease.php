<?php
// Fetch tenants
$conn = $GLOBALS['conn'];
$tenants = [];
$tq = $conn->query("SELECT id, full_name FROM tenants WHERE status='active' ORDER BY full_name ASC");
while ($row = $tq->fetch_assoc()) {
    $tenants[] = $row;
}

// Fetch vacant units
$units = [];
$uq = $conn->query("SELECT id, unit_number FROM units WHERE status='vacant' ORDER BY unit_number ASC");
while ($row = $uq->fetch_assoc()) {
    $units[] = $row;
}
?>
<!-- Add Lease Modal -->
<div class="modal fade" id="addLeaseModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl"> <!-- Wider modal for columns -->
    <div class="modal-content">

      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold">Add New Lease</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form action="app/leases.php?action=add" method="POST" id="addLeaseForm">

        <div class="modal-body">

          <div class="row g-3">

            <!-- LEFT COLUMN -->
            <div class="col-md-6">

              <div class="card border-0 shadow-sm p-3">
                <h6 class="fw-bold mb-3">Tenant & Unit</h6>

                <div class="mb-3">
                  <label class="form-label">Tenant</label>
                  <select name="tenant_id" class="form-select" required>
                    <option value="">Select Tenant</option>
                    <?php foreach ($tenants as $t): ?>
                      <option value="<?= $t['id']; ?>">
                        <?= htmlspecialchars($t['full_name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label">Unit</label>
                  <select name="unit_id" class="form-select" required>
                    <option value="">Select Unit</option>
                    <?php foreach ($units as $u): ?>
                      <option value="<?= $u['id']; ?>">
                        Unit <?= htmlspecialchars($u['unit_number']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>

              </div>

            </div>

            <!-- RIGHT COLUMN -->
            <div class="col-md-6">

              <div class="card border-0 shadow-sm p-3">
                <h6 class="fw-bold mb-3">Lease Details</h6>

                <div class="row g-3">

                  <div class="col-md-6">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Monthly Rent (USD)</label>
                    <input type="number" step="0.01" name="monthly_rent" class="form-control" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Deposit</label>
                    <input type="number" step="0.01" name="deposit" class="form-control" required>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Payment Cycle</label>
                    <select name="payment_cycle" class="form-select" required>
                      <option value="monthly">Monthly</option>
                      <option value="quarterly">Quarterly</option>
                      <option value="yearly">Yearly</option>
                    </select>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label">Auto Invoice</label>
                    <select name="auto_invoice" class="form-select">
                      <option value="1">Yes</option>
                      <option value="0">No</option>
                    </select>
                  </div>

                </div>

              </div>

            </div>

          </div>

        </div>

        <div class="modal-footer bg-light">
          <button type="submit" class="btn btn-primary px-4">Save Lease</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>

      </form>

    </div>
  </div>
</div>

