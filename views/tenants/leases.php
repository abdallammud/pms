<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
        <h5 class="page-title">Leases List</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaseModal">
            <i class="bi bi-plus me-2"></i> Add Lease
        </button>
    </div>
    <!-- Page Content -->
    <div class="page-content fade-in">
        <div class="card">
            <div class="card-body table">
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="leasesTable">
                        <thead>
                            <tr>
                                <th>Tenant</th>
                                <th>Unit</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once 'views/tenants/modals/add_lease.php'; ?>
<script src="public/js/modules/lease.js"></script>

