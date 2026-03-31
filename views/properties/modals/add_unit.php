<!-- Add Unit Modal -->
<div class="modal fade" id="addUnitModal" tabindex="-1" aria-labelledby="addUnitLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="addUnitLabel">
                    <i class="bi bi-door-open me-2"></i>Add Unit
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="addUnitForm">
                    <input type="hidden" name="unit_id" id="unit_id" value="">

                    <!-- Property + Unit Number -->
                    <div class="row mb-3">
                        <div class="col-md-6 multiselect-parent">
                            <label class="form-label multiselect-label">Property <span class="text-danger">*</span></label>
                            <select name="property_id" id="unit_property_select" class="form-select selectpicker"
                                data-live-search="true" title="Select Property" required>
                                <option value="">Select Property</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit Number <span class="text-danger">*</span></label>
                            <input type="text" name="unit_number" id="unit_number" class="form-control"
                                placeholder="e.g. A1, 101, GF-02" required>
                        </div>
                    </div>

                    <!-- Unit Type + Size -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Unit Type <span class="text-danger">*</span></label>
                            <select name="unit_type_id" id="unit_type_id" class="form-select" required>
                                <option value="">Select Type</option>
                                <!-- Populated via AJAX -->
                            </select>
                            <input type="hidden" name="unit_type" id="unit_type_hidden">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Size (sq ft)</label>
                            <input type="number" name="size_sqft" id="unit_size" class="form-control"
                                placeholder="0" min="0">
                        </div>
                    </div>

                    <!-- Floor + Rooms -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Floor Number</label>
                            <input type="number" name="floor_number" id="unit_floor" class="form-control"
                                placeholder="e.g. 1, 2, G">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Number of Rooms</label>
                            <input type="number" name="room_count" id="unit_rooms" class="form-control"
                                placeholder="e.g. 3" min="0">
                        </div>
                    </div>

                    <!-- Rent + Status -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Rent Amount</label>
                            <input type="number" step="0.01" name="rent_amount" id="unit_rent" class="form-control"
                                placeholder="0.00" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" id="unit_status" class="form-select">
                                <option value="vacant">Vacant</option>
                                <option value="occupied">Occupied</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>

                    <!-- List on Website -->
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_listed" id="unit_is_listed"
                                value="1" onchange="validateUnitListedStatus()">
                            <label class="form-check-label" for="unit_is_listed">
                                <i class="bi bi-globe me-1 text-primary"></i>
                                List on website <span class="text-muted small">(unit cannot be occupied if listed)</span>
                            </label>
                        </div>
                        <div id="unit_listed_warning" class="alert alert-warning py-1 mt-2 small d-none">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            An occupied unit cannot be listed on the website.
                        </div>
                    </div>

                    <!-- Amenities Checklist -->
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-stars me-1 text-primary"></i>Amenities</label>
                        <div id="amenitiesChecklist" class="row g-2">
                            <div class="col-12 text-muted small">Loading amenities...</div>
                        </div>
                    </div>

                    <input type="hidden" name="tenant_id" id="unit_tenant_select" value="">

                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="saveUnitBtn">
                    <i class="bi bi-save me-1"></i> Save Unit
                </button>
            </div>

        </div>
    </div>
</div>
