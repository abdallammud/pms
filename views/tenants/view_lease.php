<?php
// Get lease ID from URL
$lease_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($lease_id <= 0) {
    echo '<script>alert("Invalid lease ID"); window.location.href = "leases";</script>';
    exit;
}

$conn = $GLOBALS['conn'];

// Fetch the lease data with all related information
$sql = "SELECT l.*, 
               t.full_name as tenant_name, t.phone as tenant_phone, t.email as tenant_email, t.id_number as tenant_id_number,
               g.full_name as guarantee_name, g.phone as guarantee_phone, g.email as guarantee_email, g.id_number as guarantee_id_number,
               p.name as property_name, p.address as property_address, p.city as property_city,
               u.unit_number, u.unit_type, u.size_sqft
        FROM leases l 
        LEFT JOIN tenants t ON l.tenant_id = t.id
        LEFT JOIN guarantees g ON l.guarantee_id = g.id
        LEFT JOIN properties p ON l.property_id = p.id
        LEFT JOIN units u ON l.unit_id = u.id
        WHERE l.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lease_id);
$stmt->execute();
$result = $stmt->get_result();
$lease = $result->fetch_assoc();

if (!$lease) {
    echo '<script>alert("Lease not found"); window.location.href = "leases";</script>';
    exit;
}

// Decode witnesses JSON
$witnesses = json_decode($lease['witnesses'] ?? '[]', true) ?: [];

// Status badge class
$statusClass = 'bg-secondary';
$statusText = ucfirst($lease['status']);
switch($lease['status']) {
    case 'active': $statusClass = 'bg-success'; break;
    case 'pending': $statusClass = 'bg-warning'; break;
    case 'expired': $statusClass = 'bg-danger'; break;
    case 'terminated': $statusClass = 'bg-secondary'; break;
}

// Format dates
$startDate = date('F d, Y', strtotime($lease['start_date']));
$endDate = date('F d, Y', strtotime($lease['end_date']));
$createdAt = $lease['created_at'] ? date('F d, Y H:i', strtotime($lease['created_at'])) : 'N/A';
?>

<!-- Main Content -->
<main class="content view_lease">
    <div class="page-content fade-in">

        <div class="card shadow-sm">
            <div class="card-body">

                <!-- ✅ HEADER -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                    <div>
                        <h4 class="page-title mb-1">
                            <i class="bi bi-file-earmark-text me-2 text-primary"></i>Lease Agreement
                        </h4>
                        <span class="badge <?= $statusClass ?> fs-6"><?= $statusText ?></span>
                        <span class="text-muted ms-2">Reference: <strong><?= htmlspecialchars($lease['reference_number'] ?? 'N/A') ?></strong></span>
                    </div>
                    <div>
                        <a class="btn btn-outline-secondary me-2" href="<?=baseUri();?>/pdf.php?print=lease&lease_id=<?= $lease_id ?>" title="Print Lease">
                            <i class="bi bi-printer me-1"></i> Print
                        </a>
                        <a href="<?=baseUri();?>/edit_lease/<?= $lease_id ?>" class="btn btn-primary me-2">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                        <a href="<?=baseUri();?>/leases" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>

                <div class="row g-4">

                    <!-- ✅ TENANT INFORMATION -->
                    <div class="col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-header bg-primary text-white fw-bold border-0">
                                <i class="bi bi-person me-2"></i>Tenant Information
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-5 text-muted">Full Name:</div>
                                    <div class="col-7 fw-semibold"><?= htmlspecialchars($lease['tenant_name'] ?? 'N/A') ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 text-muted">Phone:</div>
                                    <div class="col-7"><?= htmlspecialchars($lease['tenant_phone'] ?? 'N/A') ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 text-muted">Email:</div>
                                    <div class="col-7"><?= htmlspecialchars($lease['tenant_email'] ?? 'N/A') ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-5 text-muted">ID Number:</div>
                                    <div class="col-7"><?= htmlspecialchars($lease['tenant_id_number'] ?? 'N/A') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ GUARANTEE INFORMATION -->
                    <div class="col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-header bg-success text-white fw-bold border-0">
                                <i class="bi bi-shield-check me-2"></i>Guarantee Information
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-5 text-muted">Full Name:</div>
                                    <div class="col-7 fw-semibold"><?= htmlspecialchars($lease['guarantee_name'] ?? 'N/A') ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 text-muted">Phone:</div>
                                    <div class="col-7"><?= htmlspecialchars($lease['guarantee_phone'] ?? 'N/A') ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 text-muted">Email:</div>
                                    <div class="col-7"><?= htmlspecialchars($lease['guarantee_email'] ?? 'N/A') ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-5 text-muted">ID Number:</div>
                                    <div class="col-7"><?= htmlspecialchars($lease['guarantee_id_number'] ?? 'N/A') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ PROPERTY & UNIT INFORMATION -->
                    <div class="col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-header bg-info text-white fw-bold border-0">
                                <i class="bi bi-building me-2"></i>Property & Unit
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-5 text-muted">Property:</div>
                                    <div class="col-7 fw-semibold"><?= htmlspecialchars($lease['property_name'] ?? 'N/A') ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 text-muted">Address:</div>
                                    <div class="col-7"><?= htmlspecialchars(($lease['property_address'] ?? '') . ', ' . ($lease['property_city'] ?? '')) ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 text-muted">Unit Number:</div>
                                    <div class="col-7 fw-semibold"><?= htmlspecialchars($lease['unit_number'] ?? 'N/A') ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 text-muted">Unit Type:</div>
                                    <div class="col-7"><?= htmlspecialchars($lease['unit_type'] ?? 'N/A') ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-5 text-muted">Size (sq ft):</div>
                                    <div class="col-7"><?= number_format($lease['size_sqft'] ?? 0) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ LEASE FINANCIAL DETAILS -->
                    <div class="col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-header bg-warning text-dark fw-bold border-0">
                                <i class="bi bi-cash-stack me-2"></i>Financial Details
                            </div>
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-5 text-muted">Monthly Rent:</div>
                                    <div class="col-7 fw-bold text-success fs-5">$<?= number_format($lease['monthly_rent'], 2) ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 text-muted">Deposit:</div>
                                    <div class="col-7 fw-semibold">$<?= number_format($lease['deposit'], 2) ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-5 text-muted">Payment Cycle:</div>
                                    <div class="col-7"><?= ucfirst($lease['payment_cycle'] ?? 'Monthly') ?></div>
                                </div>
                                <div class="row">
                                    <div class="col-5 text-muted">Auto Invoice:</div>
                                    <div class="col-7">
                                        <?php if($lease['auto_invoice']): ?>
                                            <span class="badge bg-success">Yes</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">No</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ LEASE DATES -->
                    <div class="col-12">
                        <div class="card border-0 bg-light">
                            <div class="card-header bg-dark text-white fw-bold border-0">
                                <i class="bi bi-calendar-range me-2"></i>Lease Period
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-4">
                                        <div class="p-3 rounded bg-white shadow-sm">
                                            <div class="text-muted small">Start Date</div>
                                            <div class="fs-5 fw-bold text-primary"><?= $startDate ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded bg-white shadow-sm">
                                            <div class="text-muted small">End Date</div>
                                            <div class="fs-5 fw-bold text-danger"><?= $endDate ?></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 rounded bg-white shadow-sm">
                                            <div class="text-muted small">Created</div>
                                            <div class="fs-6 fw-semibold text-secondary"><?= $createdAt ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ LEASE CONDITIONS -->
                    <?php if(!empty($lease['lease_conditions'])): ?>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-header bg-secondary text-white fw-bold border-0">
                                <i class="bi bi-file-text me-2"></i>Lease Conditions
                            </div>
                            <div class="card-body">
                                <div class="bg-white p-3 rounded" style="max-height: 300px; overflow-y: auto;">
                                    <?= $lease['lease_conditions'] ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- ✅ VEHICLE & WEAPONS -->
                    <div class="col-md-6">
                        <div class="card border-0 bg-light h-100">
                            <div class="card-header bg-secondary text-white fw-bold border-0">
                                <i class="bi bi-car-front me-2"></i>Vehicle & Legal Weapons
                            </div>
                            <div class="card-body">
                                <?php if(!empty($lease['vehicle_info'])): ?>
                                <div class="mb-3">
                                    <label class="text-muted small">Vehicle Information</label>
                                    <div class="bg-white p-2 rounded"><?= nl2br(htmlspecialchars($lease['vehicle_info'])) ?></div>
                                </div>
                                <?php else: ?>
                                <div class="mb-3 text-muted fst-italic">No vehicle information</div>
                                <?php endif; ?>

                                <?php if(!empty($lease['legal_weapons'])): ?>
                                <div>
                                    <label class="text-muted small">Legal Weapons</label>
                                    <div class="bg-white p-2 rounded"><?= nl2br(htmlspecialchars($lease['legal_weapons'])) ?></div>
                                </div>
                                <?php else: ?>
                                <div class="text-muted fst-italic">No legal weapons registered</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- ✅ WITNESSES -->
                    <?php if(!empty($witnesses)): ?>
                    <div class="col-12">
                        <div class="card border-0 bg-light">
                            <div class="card-header bg-secondary text-white fw-bold border-0">
                                <i class="bi bi-people me-2"></i>Witnesses
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped mb-0 bg-white">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Phone</th>
                                                <th>ID Card</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($witnesses as $i => $w): ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td><?= htmlspecialchars($w['name'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($w['phone'] ?? '') ?></td>
                                                <td><?= htmlspecialchars($w['id_card'] ?? '') ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>

            </div>
        </div>

    </div>
</main>

<!-- Print Styles -->
<style>
@media print {
    .btn, .page-footer, .sidebar-wrapper, .topbar, .overlay {
        display: none !important;
    }
    .content {
        margin: 0 !important;
        padding: 20px !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
    .card-header {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
