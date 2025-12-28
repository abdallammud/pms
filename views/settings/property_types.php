<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="d-flex mt-3 align-items-center justify-content-between mb-3">
        <h5 class="page-title">Property Types </h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#propertyTypeModal">
            <i class="bi bi-plus me-2"></i> Add Property Type
        </button>
    </div>
    <!-- Page Content -->
    <div class="page-content fade-in">
        <div class="card">
            <div class="card-body table">

                <div class="table-responsive">
                    <table id="propertyTypesTable" class="table table-striped table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Type Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Property Type Modal -->
<div class="modal fade" id="propertyTypeModal" tabindex="-1" aria-labelledby="propertyTypeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="propertyTypeModalLabel">Add Property Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="propertyTypeForm">
                    <input type="hidden" id="property_type_id" name="property_type_id" value="">

                    <div class="mb-3">
                        <label for="type_name" class="form-label">Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="type_name" name="type_name"
                            placeholder="e.g. Apartment, House, Commercial" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                            placeholder="Optional description for this property type"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class=" me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" onclick="savePropertyType()">
                    <i class=" me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= baseUri(); ?>/public/js/modules/property_types.js"></script>