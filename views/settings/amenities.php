<!-- Main Content -->
<main class="content">
    <div class="d-flex mt-3 align-items-center justify-content-between mb-3">
        <h5 class="page-title">Amenities</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#amenityModal">
            <i class="bi bi-plus me-1"></i> Add Amenity
        </button>
    </div>
    <div class="page-content fade-in">
        <div class="card">
            <div class="card-body table">
                <div class="table-responsive">
                    <table id="amenitiesTable" class="table table-striped table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Icon</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th style="width:120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Amenity Modal -->
<div class="modal fade" id="amenityModal" tabindex="-1" aria-labelledby="amenityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="amenityModalLabel">Add Amenity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="amenityForm">
                    <input type="hidden" id="amenity_id" name="amenity_id" value="">

                    <div class="mb-3">
                        <label class="form-label">Amenity Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="am_name" name="name"
                            placeholder="e.g. WiFi, Parking, Swimming Pool" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bootstrap Icon Class</label>
                        <div class="input-group">
                            <span class="input-group-text" id="amenityIconPreview"><i class="bi bi-tag"></i></span>
                            <input type="text" class="form-control" id="am_icon" name="icon"
                                placeholder="e.g. bi-wifi, bi-car-front, bi-droplet"
                                oninput="previewAmenityIcon(this.value)">
                        </div>
                        <div class="form-text">
                            Browse icons at
                            <a href="https://icons.getbootstrap.com/" target="_blank">icons.getbootstrap.com</a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="am_status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveAmenity()">
                    <i class="bi bi-save me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= baseUri(); ?>/public/js/modules/amenities.js"></script>

<script>
    function previewAmenityIcon(cls) {
        var el = document.getElementById('amenityIconPreview');
        el.innerHTML = cls ? '<i class="bi ' + cls + '"></i>' : '<i class="bi bi-tag"></i>';
    }
</script>