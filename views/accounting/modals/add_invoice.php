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
        <h5 class="modal-title fw-bold" id="addInvoiceLabel">Add Rent Invoice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="saveInvoiceForm">
        <input type="hidden" name="invoice_id" id="invoice_id">

        <div class="modal-body">
          <div class="row g-3">

            <!-- Left column: Type & Lease -->
            <div class="col-md-6">
              <div class="card border-0 shadow-sm p-3 h-100">
                <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-info-circle me-2"></i>Invoice Type & Lease</h6>

                <div class="mb-3">
                  <label class="form-label fw-bold">Invoice Type <span class="text-danger">*</span></label>
                  <select name="invoice_type" id="invoice_type" class="form-select" required>
                    <option value="rent">Rent Invoice</option>
                    <option value="other_charge">Other Charges Invoice</option>
                  </select>
                </div>

                <div id="charge_type_container" class="mb-3 d-none multiselect-parent">
                  <label class="form-label fw-bold multiselect-label">Charge Type <span class="text-danger">*</span></label>
                  <div class="input-group d-flex">
                    <select name="charge_type_id" id="charge_type_id" class="form-select selectpicker" data-live-search="true" title="Select Charge Type">
                      <!-- Populated via AJAX -->
                    </select>
                    <button type="button" class="btn btn-outline-secondary" id="btnAddNewChargeType" title="Add New Charge Type">
                      <i class="bi bi-plus-lg"></i>
                    </button>
                  </div>
                </div>

                <div class="mb-3 multiselect-parent">
                  <label class="form-label fw-bold multiselect-label" id="lease_label">Lease <span class="text-danger">*</span></label>
                  <select name="lease_id[]" id="lease_id" class="form-select selectpicker" data-live-search="true" title="Select Lease" required>
                    <?php while($lease = $leases->fetch_assoc()): ?>
                      <option value="<?= $lease['id']; ?>" data-subtext="Unit <?= htmlspecialchars($lease['unit_number']); ?>">
                        <?= htmlspecialchars($lease['full_name']); ?>
                      </option>
                    <?php endwhile; ?>
                  </select>
                  <div id="lease_help" class="form-text">For rent invoices, select exactly one lease.</div>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-bold">Invoice Date <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                    <input type="text" value="<?=date('Y-m-d');?>" name="invoice_date" id="invoice_date" class="form-control datepicker" required>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right column: Amount & Period -->
            <div class="col-md-6">
              <div class="card border-0 shadow-sm p-3 h-100">
                <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-cash-stack me-2"></i>Invoice Details</h6>

                <div class="mb-3">
                  <label class="form-label fw-bold">Amount <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
                  </div>
                  <div id="amount_help" class="form-text">Rent amount will be auto-filled.</div>
                </div>

                <div class="mb-3">
                  <label class="form-label fw-bold">Due Date <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                    <input type="text" value="<?=date('Y-m-d');?>" name="due_date" id="due_date" class="form-control datepicker" required>
                  </div>
                </div>

                <div class="row g-2">
                  <div class="col-6">
                    <div class="mb-3">
                      <label class="form-label fw-bold text-truncate">Billing Month</label>
                      <select name="billing_month" id="billing_month" class="form-select">
                        <?php for($m=1; $m<=12; $m++): ?>
                          <option value="<?= $m; ?>" <?= $m == date('n') ? 'selected' : ''; ?>>
                            <?= date('F', mktime(0, 0, 0, $m, 1)); ?>
                          </option>
                        <?php endfor; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="mb-3">
                      <label class="form-label fw-bold text-truncate">Billing Year</label>
                      <select name="billing_year" id="billing_year" class="form-select">
                        <?php 
                        $currentYear = date('Y');
                        for($y=$currentYear-1; $y<=$currentYear+2; $y++): ?>
                          <option value="<?= $y; ?>" <?= $y == $currentYear ? 'selected' : ''; ?>><?= $y; ?></option>
                        <?php endfor; ?>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="mb-0">
                  <label class="form-label fw-bold">Notes</label>
                  <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Optional internal notes..."></textarea>
                </div>

              </div>
            </div>

          </div>
        </div>

        <div class="modal-footer bg-light">
          <button type="submit" class="btn btn-primary px-4" id="saveInvoiceBtn">Save Invoice</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>

      </form>

    </div>
  </div>
</div>

<style>
  #charge_type_container .bootstrap-select:not([class*=col-]):not([class*=form-control]):not(.input-group-btn) {
    flex-basis: 70%;;
  }
</style>
