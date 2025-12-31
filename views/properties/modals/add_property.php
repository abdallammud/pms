<!-- Add Property Modal -->
<div class="modal fade" id="addPropertyModal" tabindex="-1" aria-labelledby="addPropertyLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="addPropertyLabel">
                    <i class="bi bi-building-add me-2"></i>Add Property
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">

                <form id="addPropertyForm">
                    <input type="hidden" name="property_id" id="property_id" value="">

                    <!-- Property Info -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Property Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="property_name" class="form-control" required>
                        </div>

                        <div class="col-md-6 multiselect-parent">
                            <label class="form-label multiselect-label">Property Type</label>
                            <select name="type_id" id="property_type_select" class="form-select selectpicker"
                                data-live-search="true" title="Select Type">
                                <option value="">Select Type</option>
                                <!-- Populated dynamically via AJAX -->
                            </select>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" id="property_address" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" id="property_city" class="form-control" required>
                        </div>
                    </div>

                    <!-- Ownership / Manager -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Owner Name</label>
                            <input type="text" name="owner_name" id="property_owner" class="form-control">
                        </div>

                        <div class="col-md-6 multiselect-parent">
                            <label class="form-label multiselect-label">Manager</label>
                            <select name="manager_id" id="manager_select" class="form-select selectpicker"
                                data-live-search="true" title="Select Manager">
                                <option value="">Select Manager</option>
                                <!-- Populated dynamically via AJAX -->
                            </select>
                        </div>
                    </div>

                    <!-- Description & Logo -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Property Logo</label>
                            <input type="file" name="logo" id="property_logo" class="form-control" accept="image/*">
                            <small class="text-muted">Optional (Images only)</small>
                        </div>
                    </div>

                </form>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="savePropertyBtn">
                    Save Property
                </button>
            </div>

        </div>
    </div>
</div>

<style>

</style>