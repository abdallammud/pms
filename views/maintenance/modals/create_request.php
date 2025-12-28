<!-- Add Maintenance Request Modal -->
<?php $conn = $GLOBALS['conn']; ?>
<div class="modal fade" id="addRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg border-0">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modal_title"><i class="bi bi-tools me-2"></i>New Maintenance Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="saveRequestForm">
                <input type="hidden" name="request_id" id="request_id">
                <div class="modal-body">
                    <div class="row g-3">

                        <!-- Property -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Property</label>
                            <select name="property_id" id="propertySelect" class="form-control selectpicker"
                                data-live-search="true" required>
                                <option value="">Select Property</option>
                                <?php
                                $q = $conn->query("SELECT id, name FROM properties ORDER BY name");
                                while ($p = $q->fetch_assoc()) {
                                    echo '<option value="' . $p['id'] . '">' . $p['name'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Unit -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Unit (Optional)</label>
                            <select name="unit_id" id="unitSelect" class="form-control selectpicker"
                                data-live-search="true">
                                <option value="">Select Unit</option>
                            </select>
                        </div>

                        <!-- Priority -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Priority</label>
                            <select name="priority" id="prioritySelect" class="form-control selectpicker">
                                <option value="low" data-icon="bi-arrow-down-circle text-success">Low</option>
                                <option value="medium" data-icon="bi-dash-circle text-warning" selected>Medium</option>
                                <option value="high" data-icon="bi-arrow-up-circle text-danger">High</option>
                            </select>
                        </div>

                        <!-- Requester -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Requester Name</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="requester" id="requester" class="form-control"
                                    placeholder="e.g. John Doe / Tenant Name" required>
                            </div>
                        </div>

                        <!-- Status (Visible on Edit) -->
                        <div class="col-md-12 d-none" id="maintenance_status_div">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" id="statusSelect" class="form-control selectpicker">
                                <option value="new">New</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label fw-bold">Description of Issue</label>
                            <textarea name="description" id="description" rows="3" class="form-control"
                                placeholder="Describe the maintenance issue in detail..." required></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button class="btn btn-primary" type="submit" id="saveRequestBtn">
                        <i class="bi bi-save me-1"></i> Submit Request
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>