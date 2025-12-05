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

<!-- Main Content -->
<main class="content">
    <div class="page-content fade-in">

        <div class="card">
            <div class="card-body">

                <!-- ✅ HEADER -->
                <div class="d-flex justify-content-between mb-4">
                    <h5 class="page-title mb-0">Leases List</h5>
                    <button class="btn btn-secondary" onclick="window.history.back()">
                        <i class="bi bi-arrow-left me-2"></i> Go back
                    </button>
                </div>

                <!-- ✅ FORM START -->
                <form method="post" action="">
                    <div class="row g-4">

                        <!-- ✅ ROW 1 (HALF): TENANT & GUARANTEE -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header fw-bold">Tenant & Guarantee</div>
                                <div class="card-body">

                                    <div class="mb-3">
                                        <label class="form-label">Tenant</label>
                                        <select class="form-select" name="tenant_id" required>
                                            <option selected disabled>Choose Tenant</option>
                                            <?php foreach ($tenants as $t): ?>
                                                <option value="<?= $t['id'] ?>"><?= $t['full_name'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="form-label">Guarantee</label>
                                        <select class="form-select" name="guarantee_id" required>
                                            <option selected disabled>Choose Guarantee</option>
                                            <?php foreach ($tenants as $t): ?>
                                                <option value="<?= $t['id'] ?>"><?= $t['full_name'] ?></option>
                                            <?php endforeach; ?>
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
                                        <label class="form-label">Unit</label>
                                        <select class="form-select" name="unit_id" required>
                                            <option selected disabled>Choose Unit</option>
                                            <?php foreach ($units as $u): ?>
                                                <option value="<?= $u['id'] ?>"><?= $u['unit_number'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">Start Date</label>
                                        <input type="text" readonly value="<?= date('Y-m-d') ?>" class="form-control datepicker" name="start_date" required>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label class="form-label">End Date</label>
                                        <input type="text" readonly value="<?= date('Y-m-d') ?>" class="form-control datepicker" name="end_date" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Monthly Rent (USD)</label>
                                        <input type="number" class="form-control" name="monthly_rent" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Deposit</label>
                                        <input type="number" class="form-control" name="deposit" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Rent Cycle</label>
                                        <select class="form-select" name="rent_cycle">
                                            <option value="monthly">Monthly</option>
                                            <option value="quarterly">Quarterly</option>
                                            <option value="yearly">Yearly</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Auto Invoice</label>
                                        <select class="form-select" name="auto_invoice">
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
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
                                    <textarea class="form-control tinymce" name="lease_conditions"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ ROW 3 (HALF): VEHICLE & WEAPONS (ONE SECTION) -->
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header fw-bold">Vehicle & Legal Weapons</div>
                                <div class="card-body">
                                    <textarea class="form-control mb-3" name="vehicle_info" rows="4" placeholder="Vehicle Information"></textarea>
                                    <textarea class="form-control" name="legal_weapons" rows="4" placeholder="Legal Weapons / Guns"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- ✅ ROW 4 (FULL): WITNESSES -->
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header fw-bold">Witnesses</div>
                                <div class="card-body">

                                    <?php for($i=1; $i<=3; $i++): ?>
                                    <div class="row g-2 mb-3">
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="witness_name[]" placeholder="Witness <?= $i ?> Name" required>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="witness_phone[]" placeholder="Phone" required>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="text" class="form-control" name="witness_id[]" placeholder="ID Card" required>
                                        </div>
                                    </div>
                                    <?php endfor; ?>

                                </div>
                            </div>
                        </div>

                        <!-- ✅ SUBMIT -->
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">
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

<!-- ✅ TinyMCE Activation -->
<script>

</script>
