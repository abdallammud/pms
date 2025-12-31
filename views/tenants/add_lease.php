<?php
// Fetch tenants
$conn = $GLOBALS['conn'];
$tenants = [];
$tq = $conn->query("SELECT id, full_name FROM tenants WHERE status='active' ORDER BY full_name ASC");
while ($row = $tq->fetch_assoc()) {
    $tenants[] = $row;
}

// Fetch guarantees
$guarantees = [];
$gq = $conn->query("SELECT id, full_name, phone FROM guarantees ORDER BY full_name ASC");
while ($row = $gq->fetch_assoc()) {
    $guarantees[] = $row;
}

// Fetch lease conditions from settings
$lease_conditions_default = '';
$lcq = $conn->query("SELECT setting_value FROM system_settings WHERE setting_key = 'lease_conditions'");
if ($lcq && $row = $lcq->fetch_assoc()) {
    $lease_conditions_default = $row['setting_value'];
}
?>

<!-- Main Content -->
<main class="content">
    <div class="page-content fade-in">

        <div class="card">
            <div class="card-body">

                <!-- ✅ HEADER -->
                <div class="d-flex justify-content-between mb-4">
                    <h5 class="page-title mb-0">Add New Lease</h5>
                    <button class="btn btn-secondary" onclick="window.history.back()">
                        <i class="bi bi-arrow-left me-2"></i> Go back
                    </button>
                </div>

                <!-- ✅ FORM START -->
                <form method="post" action="" id="addLeaseForm">
                    <div class="row g-4">

                        <!-- ✅ ROW 1 (HALF): TENANT & GUARANTEE -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header fw-bold">Tenant & Guarantee</div>
                                <div class="card-body">

                                    <div class="mb-3 multiselect-parent">
                                        <label class="form-label multiselect-label">Tenant <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select selectpicker" name="tenant_id"
                                            id="lease_tenant_select" data-live-search="true" title="Choose Tenant"
                                            required>
                                            <option value="">Choose Tenant</option>
                                            <?php foreach ($tenants as $t): ?>
                                                <option value="<?= $t['id'] ?>">
                                                    <?= htmlspecialchars($t['full_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="multiselect-parent">
                                        <label class="form-label multiselect-label">Guarantee <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select selectpicker" name="guarantee_id"
                                            id="lease_guarantee_select" data-live-search="true" title="Choose Guarantee"
                                            required>
                                            <option value="">Choose Guarantee</option>
                                            <?php foreach ($guarantees as $g): ?>
                                                <option value="<?= $g['id'] ?>"><?= htmlspecialchars($g['full_name']) ?> -
                                                    <?= $g['phone'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- ✅ ROW 1 (HALF): PROPERTY & UNIT -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header fw-bold">Property & Unit</div>
                                <div class="card-body">

                                    <div class="mb-3 multiselect-parent">
                                        <label class="form-label multiselect-label">Property <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select selectpicker" name="property_id"
                                            id="lease_property_select" data-live-search="true" title="Choose Property"
                                            required>
                                            <option value="">Choose Property</option>
                                            <!-- Loaded dynamically via JavaScript -->
                                        </select>
                                    </div>

                                    <div class="multiselect-parent">
                                        <label class="form-label multiselect-label">Unit <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select selectpicker" name="unit_id" id="lease_unit_select"
                                            data-live-search="true" title="Choose Unit" required>
                                            <option value="">Choose Unit</option>
                                            <!-- Loaded dynamically based on selected property -->
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- ✅ ROW 2 (FULL): LEASE DETAILS -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header fw-bold">Lease Details</div>
                                <div class="card-body row g-3">

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                        <input type="text" value="<?= date('Y-m-d') ?>" class="form-control lease-start"
                                            name="start_date" id="lease_start_date" required>


                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                                        <input type="text" value="<?= date('Y-m-d', strtotime('+1 year')) ?>"
                                            class="form-control lease-end" name="end_date" id="lease_end_date" required>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Monthly Rent (USD) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" name="monthly_rent"
                                            id="lease_monthly_rent" required>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Deposit <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" class="form-control" name="deposit"
                                            id="lease_deposit" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Rent Cycle</label>
                                        <select class="form-select" name="rent_cycle" id="lease_rent_cycle">
                                            <option value="monthly">Monthly</option>
                                            <option value="quarterly">Quarterly</option>
                                            <option value="yearly">Yearly</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Auto Invoice</label>
                                        <select class="form-select" name="auto_invoice" id="lease_auto_invoice">
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Lease Status</label>
                                        <select class="form-select" name="status" id="lease_status">
                                            <option value="active">Active</option>
                                            <option value="pending">Pending</option>
                                            <option value="terminated">Terminated</option>
                                            <option value="expired">Expired</option>
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- ✅ ROW 3 (HALF): LEASE CONDITIONS -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header fw-bold">Lease Conditions</div>
                                <div class="card-body">
                                    <textarea class="form-control" name="lease_conditions" id="lease_conditions_editor"
                                        rows="10"><?= htmlspecialchars($lease_conditions_default) ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ ROW 3 (HALF): VEHICLE & WEAPONS (ONE SECTION) -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header fw-bold">Vehicle & Legal Weapons</div>
                                <div class="card-body">
                                    <label class="form-label">Vehicle Information</label>
                                    <textarea class="form-control mb-3" name="vehicle_info" id="lease_vehicle_info"
                                        rows="4"
                                        placeholder="Enter vehicle details (make, model, plate number, etc.)"></textarea>

                                    <label class="form-label">Legal Weapons / Guns</label>
                                    <textarea class="form-control" name="legal_weapons" id="lease_legal_weapons"
                                        rows="4" placeholder="Enter any legal weapons information"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ ROW 4 (FULL): WITNESSES -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header fw-bold">Witnesses</div>
                                <div class="card-body">
                                    <?php for ($i = 1; $i <= 3; $i++): ?>
                                        <?php $required_attr = ($i === 1) ? 'required' : ''; ?>
                                        <?php $required_span = ($i === 1) ? '<span class="text-danger">*</span>' : ''; ?>
                                        <div class="row g-2 mb-3">
                                            <div class="col-md-4">
                                                <label class="form-label">Witness <?= $i ?> Name
                                                    <?= $required_span ?></label>
                                                <input type="text" class="form-control" name="witness_name[]"
                                                    <?= $required_attr ?>>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Phone <?= $required_span ?></label>
                                                <input type="text" class="form-control" name="witness_phone[]"
                                                    <?= $required_attr ?>>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">ID Card <?= $required_span ?></label>
                                                <input type="text" class="form-control" name="witness_id[]"
                                                    <?= $required_attr ?>>
                                            </div>
                                        </div>
                                    <?php endfor; ?>

                                </div>
                            </div>
                        </div>

                        <!-- ✅ SUBMIT -->
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">
                                <i class="bi bi-x-lg me-2"></i> Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" id="saveLeaseBtn">
                                <i class="bi bi-save me-2"></i> Save Lease
                            </button>
                        </div>

                    </div>
                </form>
                <!-- ✅ FORM END -->

            </div>
        </div>

    </div>
</main>

<!-- Include lease module JS -->
<script src="<?= baseUri(); ?>/public/js/modules/lease.js"></script>