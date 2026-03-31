<!-- Main Content -->
<main class="content">
    <div class="d-flex mt-3 align-items-center justify-content-between mb-3">
        <h5 class="page-title">Unit Types</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#unitTypeModal">
            <i class="bi bi-plus me-1"></i> Add Unit Type
        </button>
    </div>
    <div class="page-content fade-in">
        <div class="card">
            <div class="card-body table">
                <div class="table-responsive">
                    <table id="unitTypesTable" class="table table-striped table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th>Type Name</th>
                                <th>Description</th>
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

<!-- Unit Type Modal -->
<div class="modal fade" id="unitTypeModal" tabindex="-1" aria-labelledby="unitTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="unitTypeModalLabel">Add Unit Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="unitTypeForm">
                    <input type="hidden" id="unit_type_id" name="unit_type_id" value="">

                    <div class="mb-3">
                        <label class="form-label">Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ut_type_name" name="type_name"
                            placeholder="e.g. Studio, 2-Bedroom, Penthouse" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="ut_description" name="description" rows="3"
                            placeholder="Optional description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="ut_status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveUnitType()">
                    <i class="bi bi-save me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<script src="<?= baseUri(); ?>/public/js/modules/unit_types.js"></script>
