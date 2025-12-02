<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="page-header fade-in">
        <h1 class="page-title">Tenants Management</h1>
    </div>
    <!-- Page Content -->
    <div class="page-content fade-in">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="card-title">Tenants List</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTenantModal">
                        <i class="bi bi-plus me-2"></i> Add Tenant
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="tenantsTable">
                        <thead>
                            <tr>
                                <th>Full Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>ID Number</th>
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
<script src="public/js/modules/tenants.js"></script>
