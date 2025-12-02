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

                    <!-- Property Select + Unit Number -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Property</label>
                            <select name="property_id" class="form-select" required>
                                <option value="">Select Property</option>
                                <!-- Backend will populate this with actual properties -->
                                <option value="1">Property #1</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Unit Number</label>
                            <input type="text" name="unit_number" class="form-control" placeholder="e.g. A1, B2, V3" required>
                        </div>
                    </div>

                    <!-- Unit Type + Size -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Unit Type</label>
                            <select name="unit_type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="Studio">Studio</option>
                                <option value="1-bedroom">1 Bedroom</option>
                                <option value="2-bedroom">2 Bedroom</option>
                                <option value="3-bedroom">3 Bedroom</option>
                                <option value="Office">Office</option>
                                <option value="Shop">Shop</option>
                                <option value="Villa">Villa</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Size (sq ft)</label>
                            <input type="number" name="size_sqft" class="form-control" placeholder="e.g. 750">
                        </div>
                    </div>

                    <!-- Rent + Status -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Rent Amount</label>
                            <input type="number" step="0.01" name="rent_amount" class="form-control" placeholder="e.g. 250.00">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="vacant">Vacant</option>
                                <option value="occupied">Occupied</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>

                    <!-- Tenant (Optional) -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Tenant (optional)</label>
                            <select name="tenant_id" class="form-select">
                                <option value="">No Tenant</option>
                                <!-- Later populated dynamically -->
                            </select>
                        </div>
                    </div>

                </form>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="saveUnitBtn">
                    <i class="bi bi-save me-1"></i>Save Unit
                </button>
            </div>

        </div>
    </div>
</div>
