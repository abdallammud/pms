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

                    <!-- Property Info -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Property Name</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Banadir Plaza" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Property Type</label>
                            <select name="type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="Apartment">Apartment</option>
                                <option value="Commercial">Commercial</option>
                                <option value="Villa">Villa</option>
                                <option value="Compound">Compound</option>
                                <option value="Mixed-use">Mixed-use</option>
                            </select>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-control" placeholder="Street / Road Name">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-control" placeholder="e.g. Mogadishu" required>
                        </div>
                    </div>

                    <!-- Ownership / Manager -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Owner Name</label>
                            <input type="text" name="owner_name" class="form-control" placeholder="e.g. Mohamed Ali">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Manager</label>
                            <select name="manager_id" class="form-select">
                                <option value="1">Manager #1</option>
                                <!-- You will populate this dynamically later -->
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Additional details about this property..."></textarea>
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
