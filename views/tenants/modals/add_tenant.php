<!-- Add Tenant Modal -->
<div class="modal fade" id="addTenantModal" tabindex="-1" aria-labelledby="addTenantLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
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

                    <!-- Basic Info -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" id="tenant_full_name" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="tenant_phone" class="form-control" required>
                        </div>
                    </div>

                    <!-- Email + ID Number -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" id="tenant_email" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">ID Number <span class="text-danger">*</span></label>
                            <input type="text" name="id_number" id="tenant_id_number" class="form-control" required>
                        </div>
                    </div>

                    <!-- ID Photo Uploads -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">ID Photo <span class="text-danger">*</span></label>
                            <div class="id-photo-upload-area" id="tenant_id_photo_area">
                                <input type="file" name="id_photo" id="tenant_id_photo" class="form-control"
                                    accept="image/*" style="display: none;">
                                <div class="upload-placeholder"
                                    onclick="document.getElementById('tenant_id_photo').click()">
                                    <i class="bi bi-credit-card-2-front fs-1 text-muted"></i>
                                    <p class="mb-0 mt-2 text-muted">Click to upload ID Photo</p>
                                    <small class="text-muted">JPG, PNG (Max 5MB)</small>
                                </div>
                                <div class="upload-preview" style="display: none;">
                                    <img src="" alt="ID Preview" class="img-fluid rounded">
                                    <button type="button" class="btn btn-sm btn-danger remove-photo"
                                        onclick="removePhotoPreview('tenant_id_photo')">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="existing_id_photo" id="tenant_existing_id_photo" value="">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Work ID Photo <span class="text-muted">(Optional)</span></label>
                            <div class="id-photo-upload-area" id="tenant_work_id_photo_area">
                                <input type="file" name="work_id_photo" id="tenant_work_id_photo" class="form-control"
                                    accept="image/*" style="display: none;">
                                <div class="upload-placeholder"
                                    onclick="document.getElementById('tenant_work_id_photo').click()">
                                    <i class="bi bi-briefcase fs-1 text-muted"></i>
                                    <p class="mb-0 mt-2 text-muted">Click to upload Work ID</p>
                                    <small class="text-muted">JPG, PNG (Max 5MB)</small>
                                </div>
                                <div class="upload-preview" style="display: none;">
                                    <img src="" alt="Work ID Preview" class="img-fluid rounded">
                                    <button type="button" class="btn btn-sm btn-danger remove-photo"
                                        onclick="removePhotoPreview('tenant_work_id_photo')">
                                        <i class="bi bi-x"></i>
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="existing_work_id_photo" id="tenant_existing_work_id_photo"
                                value="">
                        </div>
                    </div>

                    <!-- Work / Employment Info -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label">Work Information</label>
                            <input type="text" name="work_info" id="tenant_work_info" class="form-control">
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="row mb-3">
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
                    Save Tenant
                </button>
            </div>

        </div>
    </div>
</div>

<style>
    .id-photo-upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f8f9fa;
        position: relative;
        min-height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .id-photo-upload-area:hover {
        border-color: var(--bs-primary, #0d6efd);
        background: #f0f7ff;
    }

    .id-photo-upload-area .upload-placeholder {
        cursor: pointer;
    }

    .id-photo-upload-area .upload-preview {
        position: relative;
        width: 100%;
    }

    .id-photo-upload-area .upload-preview img {
        max-height: 120px;
        object-fit: contain;
    }

    .id-photo-upload-area .remove-photo {
        position: absolute;
        top: -8px;
        right: -8px;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>