<?php
$conn = $GLOBALS['conn'];
$org_clause = tenant_where_clause('l');
$leases = $conn->query("
    SELECT l.id, t.full_name, u.unit_number 
    FROM leases l
    LEFT JOIN tenants t ON t.id = l.tenant_id
    LEFT JOIN units u ON u.id = l.unit_id
    WHERE l.status='active' AND $org_clause
    ORDER BY t.full_name ASC
");
?>

<div class="modal fade" id="addInvoiceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="addInvoiceLabel">
          <i class="bi bi-receipt me-2"></i>Create Invoice
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="saveInvoiceForm">
        <input type="hidden" name="invoice_id" id="invoice_id">

        <div class="modal-body">
          <div class="row g-3">

            <!-- Left: Invoice Meta -->
            <div class="col-lg-4">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                  <h6 class="fw-bold mb-3 text-primary"><i class="bi bi-info-circle me-1"></i>Invoice Details</h6>

                  <div class="mb-3">
                    <label class="form-label fw-semibold">Invoice Type <span class="text-danger">*</span></label>
                    <select name="invoice_type" id="invoice_type" class="form-select" required>
                      <option value="rent">Rent Invoice</option>
                      <option value="other_charge">Other Charges</option>
                    </select>
                  </div>

                  <div id="charge_type_container" class="mb-3 d-none">
                    <label class="form-label fw-semibold">Charge Type <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <select name="charge_type_id" id="charge_type_id" class="form-select selectpicker"
                        data-live-search="true" title="Select Charge Type">
                        <!-- Populated via AJAX -->
                      </select>
                      <button type="button" class="btn btn-outline-secondary" id="btnAddNewChargeType" title="Add New">
                        <i class="bi bi-plus-lg"></i>
                      </button>
                    </div>
                  </div>

                  <div class="mb-3 multiselect-parent">
                    <label class="form-label fw-semibold multiselect-label">Lease <span
                        class="text-danger">*</span></label>
                    <select name="lease_id[]" id="lease_id" class="form-select selectpicker" data-live-search="true"
                      title="Select Lease" required>
                      <?php while ($lease = $leases->fetch_assoc()): ?>
                        <option value="<?= $lease['id'] ?>"
                          data-subtext="Unit <?= htmlspecialchars($lease['unit_number']) ?>">
                          <?= htmlspecialchars($lease['full_name']) ?>
                        </option>
                      <?php endwhile; ?>
                    </select>
                    <div id="lease_help" class="form-text">For rent invoices, select exactly one lease.</div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-semibold">Invoice Date <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                      <input type="text" value="<?= date('Y-m-d') ?>" name="invoice_date" id="invoice_date"
                        class="form-control datepicker" required>
                    </div>
                  </div>

                  <div class="mb-3">
                    <label class="form-label fw-semibold">Due Date <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                      <input type="text" value="<?= date('Y-m-d') ?>" name="due_date" id="due_date"
                        class="form-control datepicker" required>
                    </div>
                  </div>

                  <div class="row g-2 mb-3">
                    <div class="col-6">
                      <label class="form-label fw-semibold">Billing Month</label>
                      <select name="billing_month" id="billing_month" class="form-select form-select-sm">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                          <option value="<?= $m ?>" <?= $m == date('n') ? 'selected' : '' ?>>
                            <?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                        <?php endfor; ?>
                      </select>
                    </div>
                    <div class="col-6">
                      <label class="form-label fw-semibold">Year</label>
                      <select name="billing_year" id="billing_year" class="form-select form-select-sm">
                        <?php $cy = date('Y');
                        for ($y = $cy - 1; $y <= $cy + 2; $y++): ?>
                          <option value="<?= $y ?>" <?= $y == $cy ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                      </select>
                    </div>
                  </div>

                  <div class="mb-0">
                    <label class="form-label fw-semibold">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="2"
                      placeholder="Optional notes..."></textarea>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right: Line Items -->
            <div class="col-lg-8">
              <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-primary"><i class="bi bi-list-ul me-1"></i>Line Items</h6>
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addInvoiceItemBtn">
                      <i class="bi bi-plus-lg me-1"></i>Add Item
                    </button>
                  </div>

                  <div class="table-responsive flex-grow-1">
                    <table class="table table-sm align-middle" id="invoiceItemsTable">
                      <thead class="table-light">
                        <tr>
                          <th style="min-width:200px">Description</th>
                          <th style="width:80px">Qty</th>
                          <th style="width:120px">Unit Price</th>
                          <th style="width:80px">Tax %</th>
                          <th style="width:110px">Line Total</th>
                          <th style="width:50px"></th>
                        </tr>
                      </thead>
                      <tbody id="invoiceItemsBody">
                        <!-- rows injected by JS -->
                      </tbody>
                    </table>
                  </div>

                  <!-- Totals summary -->
                  <div class="border-top pt-3 mt-auto">
                    <div class="row justify-content-end">
                      <div class="col-md-5">
                        <table class="table table-sm table-borderless mb-0">
                          <tr>
                            <td class="text-muted">Subtotal</td>
                            <td class="text-end fw-semibold" id="inv_subtotal">0.00</td>
                          </tr>
                          <tr>
                            <td class="text-muted">Tax</td>
                            <td class="text-end fw-semibold" id="inv_tax_total">0.00</td>
                          </tr>
                          <tr class="table-active">
                            <td class="fw-bold">Total</td>
                            <td class="text-end fw-bold text-primary fs-6" id="inv_grand_total">0.00</td>
                          </tr>
                        </table>
                      </div>
                    </div>
                  </div>

                </div>
              </div>
            </div>

          </div>
        </div><!-- /.modal-body -->

        <div class="modal-footer bg-light">
          <button type="submit" class="btn btn-primary px-4" id="saveInvoiceBtn">
            <i class="bi bi-save me-1"></i>Save Invoice
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>

      </form>
    </div>
  </div>
</div>

<style>
  #invoiceItemsTable td {
    vertical-align: middle;
  }

  #invoiceItemsBody .inv-item-row input {
    font-size: .85rem;
  }

  /* Remove the double-border that bootstrap-select adds inside the Charge Type input-group */
  #charge_type_container .bootstrap-select {
    border: 0 !important;
    padding: 0 !important;
    flex: 1 1 auto;
  }

  #charge_type_container .bootstrap-select>.dropdown-toggle {
    border-radius: 0;
  }
</style>