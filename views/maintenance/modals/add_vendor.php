<!-- Add Vendor Modal -->
<div class="modal fade" id="addVendorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg border-0">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modal_title"><i class="bi bi-person-badge me-2"></i>Add New Vendor</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="saveVendorForm">
                <input type="hidden" name="vendor_id" id="vendor_id">
                <div class="modal-body">
                    <div class="row g-3">

                        <!-- Vendor Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Vendor / Staff Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="vendor_name" id="vendor_name" class="form-control"
                                    placeholder="e.g. ABC Plumbing" required>
                            </div>
                        </div>

                        <!-- Service Type -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Service Type</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-gear"></i></span>
                                <input type="text" name="service_type" id="service_type" class="form-control"
                                    placeholder="e.g. Plumbing, Electrical" required>
                            </div>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Phone</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="phone" id="phone" class="form-control"
                                    placeholder="e.g. 0612345678" required>
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Email (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" id="email" class="form-control"
                                    placeholder="e.g. vendor@email.com">
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button class="btn btn-primary" type="submit" id="saveVendorBtn">
                        <i class="bi bi-save me-1"></i> Save Vendor
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>