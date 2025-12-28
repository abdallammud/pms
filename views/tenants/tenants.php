<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
        <h5 class="page-title">Tenants List</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTenantModal">
            <i class="bi bi-plus me-2"></i> Add Tenant
        </button>
    </div>
    <!-- Page Content -->
    <div class="page-content fade-in">
        <div class="card">
            <div class="card-body table">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-gear"></i></span>
                            <select class="form-select" id="bulkActionSelectTenants">
                                <option value="">Bulk Actions</option>
                                <option value="delete">Delete Selected</option>
                            </select>
                            <button class="btn btn-secondary" id="applyBulkActionBtnTenants" type="button">Apply</button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="tenantsTable">
                        <thead>
                            <tr>
                                <th width="40"><input type="checkbox" id="selectAllTenants"></th>
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

