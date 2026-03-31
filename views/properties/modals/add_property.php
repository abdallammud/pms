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

                    <!-- Name & Type -->
                    <div class="row mb-3">
                        <div class="col-md-7">
                            <label class="form-label">Property Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="property_name" class="form-control" required
                                placeholder="e.g. Sunrise Apartments">
                        </div>
                        <div class="col-md-5 multiselect-parent">
                            <label class="form-label multiselect-label">Property Type</label>
                            <select name="type_id" id="property_type_select" class="form-select selectpicker"
                                data-live-search="true" title="Select Type">
                                <option value="">Select Type</option>
                            </select>
                        </div>
                    </div>

                    <!-- Address & City -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Street Address</label>
                            <input type="text" name="address" id="property_address" class="form-control"
                                placeholder="Street / plot number">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" name="city" id="property_city" class="form-control" required
                                placeholder="City">
                        </div>
                    </div>

                    <!-- Region & District -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Region</label>
                            <input type="text" name="region" id="property_region" class="form-control"
                                placeholder="e.g. Dar es Salaam">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">District</label>
                            <input type="text" name="district" id="property_district" class="form-control"
                                placeholder="e.g. Kinondoni">
                        </div>
                    </div>

                    <!-- Owner & Manager -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Owner Name</label>
                            <input type="text" name="owner_name" id="property_owner" class="form-control"
                                placeholder="Property owner">
                        </div>
                        <div class="col-md-6 multiselect-parent">
                            <label class="form-label multiselect-label">Manager</label>
                            <select name="manager_id" id="manager_select" class="form-select selectpicker"
                                data-live-search="true" title="Select Manager">
                                <option value="">Select Manager</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="property_description" class="form-control" rows="3"
                            placeholder="Brief description of the property..."></textarea>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="savePropertyBtn">
                    <i class="bi bi-save me-1"></i> Save Property
                </button>
            </div>

        </div>
    </div>
</div>
