<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
        <h5 class="page-title">Leases List</h5>
        <a href="add_lease" class="btn btn-primary">
            <i class="bi bi-plus me-2"></i> Add Lease
        </a>
    </div>
    <!-- Page Content -->
    <div class="page-content fade-in">
        <div class="card">
            <div class="card-body table">
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="leasesTable">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Tenant</th>
                                <th>Property / Unit</th>
                                <th>Monthly Rent</th>
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

<script src="<?=baseUri();?>/public/js/modules/lease.js"></script>
