<!-- Add / Edit Tenant Modal -->
<div class="modal fade" id="addTenantModal" tabindex="-1" aria-labelledby="addTenantLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="addTenantLabel">
                    <i class="bi bi-person-plus me-2"></i>Add Tenant
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="addTenantForm" enctype="multipart/form-data">
                    <input type="hidden" name="tenant_id" id="tenant_id" value="">

                    <!-- Section: Personal Info -->
                    <div class="form-section-title">
                        <i class="bi bi-person-badge me-2 text-primary"></i>Personal Information
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="full_name" id="tenant_full_name" class="form-control"
                                    placeholder="Enter full name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="phone" id="tenant_phone" class="form-control"
                                    placeholder="+255 7XX XXX XXX" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" name="email" id="tenant_email" class="form-control"
                                    placeholder="email@example.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">National ID Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-credit-card"></i></span>
                                <input type="text" name="id_number" id="tenant_id_number" class="form-control"
                                    placeholder="ID / Passport number" required>
                            </div>
                        </div>
                    </div>

                    <!-- Section: ID Photos -->
                    <div class="form-section-title">
                        <i class="bi bi-images me-2 text-primary"></i>Identity Documents
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">ID Photo <span class="text-danger">*</span></label>
                            <div class="tenant-photo-zone" id="tenant_id_photo_area"
                                onclick="document.getElementById('tenant_id_photo').click()">
                                <input type="file" name="id_photo" id="tenant_id_photo"
                                    accept="image/*" class="d-none"
                                    onchange="previewTenantPhoto(this, 'tenant_id_photo_area')">
                                <div class="photo-zone-placeholder" id="tenant_id_photo_placeholder">
                                    <i class="bi bi-credit-card-2-front fs-2 text-muted"></i>
                                    <p class="mb-0 mt-1 small text-muted">Click to upload ID Photo</p>
                                    <small class="text-muted opacity-75">JPG, PNG · Max 5 MB</small>
                                </div>
                                <div class="photo-zone-preview d-none" id="tenant_id_photo_preview">
                                    <img src="" alt="ID Photo" class="photo-preview-img">
                                    <button type="button" class="photo-zone-remove"
                                        onclick="event.stopPropagation(); removeTenantPhoto('tenant_id_photo', 'tenant_id_photo_area')">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="existing_id_photo" id="tenant_existing_id_photo" value="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Work ID Photo <span class="text-muted">(Optional)</span></label>
                            <div class="tenant-photo-zone" id="tenant_work_id_photo_area"
                                onclick="document.getElementById('tenant_work_id_photo').click()">
                                <input type="file" name="work_id_photo" id="tenant_work_id_photo"
                                    accept="image/*" class="d-none"
                                    onchange="previewTenantPhoto(this, 'tenant_work_id_photo_area')">
                                <div class="photo-zone-placeholder" id="tenant_work_id_photo_placeholder">
                                    <i class="bi bi-briefcase fs-2 text-muted"></i>
                                    <p class="mb-0 mt-1 small text-muted">Click to upload Work ID</p>
                                    <small class="text-muted opacity-75">JPG, PNG · Max 5 MB</small>
                                </div>
                                <div class="photo-zone-preview d-none" id="tenant_work_id_photo_preview">
                                    <img src="" alt="Work ID" class="photo-preview-img">
                                    <button type="button" class="photo-zone-remove"
                                        onclick="event.stopPropagation(); removeTenantPhoto('tenant_work_id_photo', 'tenant_work_id_photo_area')">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="existing_work_id_photo" id="tenant_existing_work_id_photo" value="">
                        </div>
                    </div>

                    <!-- Section: Employment -->
                    <div class="form-section-title">
                        <i class="bi bi-briefcase me-2 text-primary"></i>Employment / Work Information
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label">Work / Employer Information</label>
                            <input type="text" name="work_info" id="tenant_work_info" class="form-control"
                                placeholder="e.g. Company name, job title, self-employed">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" id="tenant_status" class="form-select">
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
                    <i class="bi bi-save me-1"></i> Save Tenant
                </button>
            </div>

        </div>
    </div>
</div>

<style>
.form-section-title {
    font-size: .8rem;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: var(--primary-accent, #2e62a8);
    border-bottom: 2px solid rgba(29,51,84,.08);
    padding-bottom: 6px;
    margin-bottom: 16px;
}

.tenant-photo-zone {
    border: 2px dashed rgba(29,51,84,.2);
    border-radius: 10px;
    padding: 20px 16px;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    background: #fafbfd;
    min-height: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}
.tenant-photo-zone:hover {
    border-color: var(--primary-accent, #2e62a8);
    background: #eef3fc;
}
.photo-zone-placeholder { pointer-events: none; }
.photo-zone-preview { width: 100%; position: relative; }
.photo-preview-img {
    max-height: 120px;
    max-width: 100%;
    object-fit: contain;
    border-radius: 6px;
    display: block;
    margin: 0 auto;
}
.photo-zone-remove {
    position: absolute;
    top: -6px;
    right: -6px;
    background: #dc3545;
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 26px;
    height: 26px;
    font-size: .75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 5;
}
</style>

<script>
function previewTenantPhoto(input, areaId) {
    var area = document.getElementById(areaId);
    var placeholder = area.querySelector('.photo-zone-placeholder');
    var preview = area.querySelector('.photo-zone-preview');
    var img = preview.querySelector('.photo-preview-img');

    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            placeholder.classList.add('d-none');
            preview.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeTenantPhoto(inputId, areaId) {
    var input = document.getElementById(inputId);
    var area = document.getElementById(areaId);
    input.value = '';
    area.querySelector('.photo-zone-placeholder').classList.remove('d-none');
    area.querySelector('.photo-zone-preview').classList.add('d-none');
    area.querySelector('.photo-preview-img').src = '';
}

// Legacy compat shim for old code that calls removePhotoPreview()
function removePhotoPreview(inputId) {
    var map = {
        'tenant_id_photo': 'tenant_id_photo_area',
        'tenant_work_id_photo': 'tenant_work_id_photo_area'
    };
    if (map[inputId]) removeTenantPhoto(inputId, map[inputId]);
}
</script>
