<!-- Edit Unit Modal (Tabbed) -->
<div class="modal fade" id="editUnitModal" tabindex="-1" aria-labelledby="editUnitLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="editUnitLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Unit
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <ul class="nav nav-tabs mb-4" id="editUnitTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="eu-info-tab" data-bs-toggle="tab"
                            data-bs-target="#eu-info" type="button" role="tab">
                            <i class="bi bi-info-circle me-1"></i> Info Edit
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="eu-images-tab" data-bs-toggle="tab"
                            data-bs-target="#eu-images" type="button" role="tab"
                            onclick="loadUnitImages(euCurrentId)">
                            <i class="bi bi-images me-1"></i> Images Upload
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="editUnitTabContent">
                    <!-- Info Tab -->
                    <div class="tab-pane fade show active" id="eu-info" role="tabpanel">
                        <form id="editUnitForm">
                            <input type="hidden" name="unit_id" id="eu_unit_id" value="">

                            <div class="row mb-3">
                                <div class="col-md-6 multiselect-parent">
                                    <label class="form-label multiselect-label">Property <span class="text-danger">*</span></label>
                                    <select name="property_id" id="eu_property_id" class="form-select selectpicker"
                                        data-live-search="true" title="Select Property" required>
                                        <option value="">Select Property</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Unit Number <span class="text-danger">*</span></label>
                                    <input type="text" name="unit_number" id="eu_unit_number" class="form-control" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Unit Type <span class="text-danger">*</span></label>
                                    <select name="unit_type_id" id="eu_unit_type_id" class="form-select" required>
                                        <option value="">Select Type</option>
                                    </select>
                                    <input type="hidden" name="unit_type" id="eu_unit_type_hidden">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Size (sq ft)</label>
                                    <input type="number" name="size_sqft" id="eu_size" class="form-control" min="0">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Floor Number</label>
                                    <input type="number" name="floor_number" id="eu_floor" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Number of Rooms</label>
                                    <input type="number" name="room_count" id="eu_rooms" class="form-control" min="0">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Rent Amount</label>
                                    <input type="number" step="0.01" name="rent_amount" id="eu_rent" class="form-control" min="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select name="status" id="eu_status" class="form-select">
                                        <option value="vacant">Vacant</option>
                                        <option value="occupied">Occupied</option>
                                        <option value="maintenance">Maintenance</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_listed" id="eu_is_listed"
                                        value="1" onchange="validateEuListedStatus()">
                                    <label class="form-check-label" for="eu_is_listed">
                                        <i class="bi bi-globe me-1 text-primary"></i>
                                        List on website
                                    </label>
                                </div>
                                <div id="eu_listed_warning" class="alert alert-warning py-1 mt-2 small d-none">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    An occupied unit cannot be listed on the website.
                                </div>
                            </div>

                            <!-- Amenities Checklist -->
                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-stars me-1 text-primary"></i>Amenities</label>
                                <div id="euAmenitiesChecklist" class="row g-2">
                                    <div class="col-12 text-muted small">Loading amenities...</div>
                                </div>
                            </div>

                        </form>
                    </div>

                    <!-- Images Tab -->
                    <div class="tab-pane fade" id="eu-images" role="tabpanel">
                        <div class="mb-4">
                            <input type="file" id="euImageInput" accept="image/*" multiple class="d-none">
                            <div class="text-center py-4 rounded border border-dashed eu-drop-area" id="euDropArea"
                                onclick="document.getElementById('euImageInput').click()" style="cursor:pointer;">
                                <i class="bi bi-cloud-upload display-5 text-primary opacity-50"></i>
                                <p class="mt-2 mb-0 text-muted">Click or drag &amp; drop images here</p>
                                <small class="text-muted">JPG, PNG, WebP · Max 8 MB each</small>
                            </div>
                        </div>
                        <div id="euUploadProgress" class="d-none mb-3">
                            <div class="progress" style="height:6px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated"
                                    id="euProgressBar" role="progressbar" style="width:0%"></div>
                            </div>
                            <small class="text-muted mt-1 d-block" id="euProgressLabel">Uploading...</small>
                        </div>
                        <div id="euImagesGallery" class="row g-2">
                            <div class="col-12 text-center text-muted py-3" id="euImagesPlaceholder">
                                <i class="bi bi-images opacity-50"></i> No images yet
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="updateUnitBtn">
                    <i class="bi bi-save me-1"></i> Save Changes
                </button>
            </div>

        </div>
    </div>
</div>

<style>
.border-dashed { border-style: dashed !important; }
.eu-drop-area { transition: background .2s, border-color .2s; }
.eu-drop-area.drag-over { background: #eef3fc; border-color: var(--primary-accent) !important; }
</style>
