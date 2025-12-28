<!-- Add Guarantor Modal -->
<div class="modal fade" id="addGuaranteeModal" tabindex="-1" aria-labelledby="addGuaranteeLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="addGuaranteeLabel">
                    <i class="bi bi-person-plus me-2"></i>Add Guarantor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <form id="addGuaranteeForm">
                    <input type="hidden" name="guarantee_id" id="guarantee_id" value="">

                    <!-- Basic Info -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" id="guarantee_full_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="guarantee_phone" class="form-control" required>
                        </div>
                    </div>

                    <!-- Email + ID Number -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" id="guarantee_email" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">ID Number</label>
                            <input type="text" name="id_number" id="guarantee_id_number" class="form-control">
                        </div>
                    </div>

                    <!-- Work Info -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Work Information</label>
                            <input type="text" name="work_info" id="guarantee_work_info" class="form-control">
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" id="guarantee_status" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                </form>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="saveGuaranteeBtn">
                    Save Guarantor
                </button>
            </div>

        </div>
    </div>
</div>
