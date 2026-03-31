<!-- Edit Property Modal (Tabbed) -->
<div class="modal fade" id="editPropertyModal" tabindex="-1" aria-labelledby="editPropertyLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="editPropertyLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Property
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" id="editPropertyTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="ep-info-tab" data-bs-toggle="tab"
                            data-bs-target="#ep-info" type="button" role="tab">
                            <i class="bi bi-info-circle me-1"></i> Basic Info
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ep-images-tab" data-bs-toggle="tab"
                            data-bs-target="#ep-images" type="button" role="tab"
                            onclick="loadPropertyImages(epCurrentId)">
                            <i class="bi bi-images me-1"></i> Images
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="editPropertyTabContent">
                    <!-- Basic Info Tab -->
                    <div class="tab-pane fade show active" id="ep-info" role="tabpanel">
                        <form id="editPropertyForm">
                            <input type="hidden" name="property_id" id="ep_property_id" value="">

                            <div class="row mb-3">
                                <div class="col-md-7">
                                    <label class="form-label">Property Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="ep_name" class="form-control" required>
                                </div>
                                <div class="col-md-5 multiselect-parent">
                                    <label class="form-label multiselect-label">Property Type</label>
                                    <select name="type_id" id="ep_type_id" class="form-select selectpicker"
                                        data-live-search="true" title="Select Type">
                                        <option value="">Select Type</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label class="form-label">Street Address</label>
                                    <input type="text" name="address" id="ep_address" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <input type="text" name="city" id="ep_city" class="form-control" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Region</label>
                                    <input type="text" name="region" id="ep_region" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">District</label>
                                    <input type="text" name="district" id="ep_district" class="form-control">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Owner Name</label>
                                    <input type="text" name="owner_name" id="ep_owner" class="form-control">
                                </div>
                                <div class="col-md-6 multiselect-parent">
                                    <label class="form-label multiselect-label">Manager</label>
                                    <select name="manager_id" id="ep_manager" class="form-select selectpicker"
                                        data-live-search="true" title="Select Manager">
                                        <option value="">Select Manager</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="ep_description" class="form-control" rows="3"></textarea>
                            </div>
                        </form>
                    </div>

                    <!-- Images Tab -->
                    <div class="tab-pane fade" id="ep-images" role="tabpanel">
                        <!-- Upload zone -->
                        <div class="ep-upload-zone mb-4" id="epUploadZone">
                            <input type="file" id="epImageInput" accept="image/*" multiple class="d-none">
                            <div class="ep-drop-area text-center py-4 rounded border border-dashed" id="epDropArea"
                                onclick="document.getElementById('epImageInput').click()" style="cursor:pointer;">
                                <i class="bi bi-cloud-upload display-5 text-primary opacity-50"></i>
                                <p class="mt-2 mb-0 text-muted">Click or drag &amp; drop images here</p>
                                <small class="text-muted">JPG, PNG, WebP · Max 8 MB each</small>
                            </div>
                        </div>

                        <!-- Upload progress -->
                        <div id="epUploadProgress" class="d-none mb-3">
                            <div class="progress" style="height:6px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" id="epProgressBar"
                                    role="progressbar" style="width:0%"></div>
                            </div>
                            <small class="text-muted mt-1 d-block" id="epProgressLabel">Uploading...</small>
                        </div>

                        <!-- Image gallery -->
                        <div id="epImagesGallery" class="row g-2">
                            <div class="col-12 text-center text-muted py-3" id="epImagesPlaceholder">
                                <i class="bi bi-images opacity-50"></i> No images yet
                            </div>
                        </div>
                    </div>
                </div><!-- /tab-content -->
            </div><!-- /modal-body -->

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="updatePropertyBtn">
                    <i class="bi bi-save me-1"></i> Save Changes
                </button>
            </div>

        </div>
    </div>
</div>

<style>
.border-dashed { border-style: dashed !important; }
.ep-drop-area { transition: background .2s, border-color .2s; }
.ep-drop-area.drag-over { background: #eef3fc; border-color: var(--primary-accent) !important; }
.img-thumb-wrap {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid transparent;
    transition: border-color .2s;
}
.img-thumb-wrap.is-cover {
    border-color: var(--primary-accent);
}
.img-thumb-wrap img {
    width: 100%;
    height: 120px;
    object-fit: cover;
    display: block;
}
.img-thumb-actions {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,.55);
    padding: 4px 6px;
    display: flex;
    gap: 4px;
    justify-content: flex-end;
}
.img-cover-badge {
    position: absolute;
    top: 5px;
    left: 5px;
    font-size: .65rem;
    padding: 2px 7px;
    background: var(--primary-accent);
    color: #fff;
    border-radius: 20px;
    font-weight: 700;
}
</style>
