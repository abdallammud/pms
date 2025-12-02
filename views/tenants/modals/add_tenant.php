<!-- Add Tenant Modal -->
<div class="modal fade" id="addTenantModal" tabindex="-1" aria-labelledby="addTenantLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="addTenantLabel">
                    <i class="bi bi-person-plus me-2"></i>Add Tenant
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <form id="addTenantForm">

                    <!-- Basic Info -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" placeholder="e.g. Mohamed Abdi Farah" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone" class="form-control" placeholder="e.g. 252612345678" required>
                        </div>
                    </div>

                    <!-- Email + ID Number -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. mohamed@example.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">ID Number</label>
                            <input type="text" name="id_number" class="form-control" placeholder="e.g. A1234567">
                        </div>
                    </div>

                    <!-- Work / Employment Info -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Work Information</label>
                            <input type="text" name="work_info" class="form-control" placeholder="e.g. Accountant at Premier Bank">
                        </div>
                    </div>

                    <!-- Emergency Contact -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Emergency Contact</label>
                            <input type="text" name="emergency_contact" class="form-control" placeholder="e.g. Amina Farah - 252615443322">
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                </form>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="saveTenantBtn">
                    Save Tenant
                </button>
            </div>

        </div>
    </div>
</div>
