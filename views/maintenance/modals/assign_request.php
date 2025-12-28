<!-- Assign Maintenance Modal -->
<?php $conn = $GLOBALS['conn']; ?>
<div class="modal fade" id="assignMaintenanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg border-0">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="assign_modal_title"><i class="bi bi-person-plus me-2"></i>Assign Maintenance
                    Request</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="assignRequestForm">
                <input type="hidden" name="assignment_id" id="assignment_id">
                <div class="modal-body">
                    <div class="row g-3">

                        <!-- Request -->
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Maintenance Request</label>
                            <select name="request_id" id="assign_request_id" class="form-control selectpicker"
                                data-live-search="true" required>
                                <option value="">Select Request</option>
                                <?php
                                $r = $conn->query("
                                  SELECT m.id, m.reference_number, m.description, p.name as property_name
                                  FROM maintenance_requests m
                                  LEFT JOIN properties p ON m.property_id = p.id
                                  WHERE m.status != 'completed'
                                  ORDER BY m.created_at DESC
                                ");
                                while ($req = $r->fetch_assoc()) {
                                    $label = $req['reference_number'] . ' - ' . $req['property_name'] . ' - ' . substr($req['description'], 0, 40);
                                    echo '<option value="' . $req['id'] . '">' . $label . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Vendor -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Assign To (Vendor/Staff)</label>
                            <select name="vendor_id" id="assign_vendor_id" class="form-control selectpicker"
                                data-live-search="true" required>
                                <option value="">Select Vendor</option>
                                <?php
                                $v = $conn->query("SELECT id, vendor_name, service_type FROM vendors ORDER BY vendor_name");
                                while ($ven = $v->fetch_assoc()) {
                                    echo '<option value="' . $ven['id'] . '" data-subtext="' . $ven['service_type'] . '">' . $ven['vendor_name'] . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Assigned Date -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Assigned Date</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" name="assigned_date" id="assigned_date" class="form-control"
                                    value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <!-- Expected Completion -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Expected Completion</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                <input type="date" name="expected_completion" id="expected_completion"
                                    class="form-control">
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Notes (Optional)</label>
                            <textarea name="notes" id="assign_notes" class="form-control" rows="2"
                                placeholder="Any special instructions..."></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                    <button class="btn btn-success" type="submit" id="saveAssignmentBtn">
                        <i class="bi bi-check-circle me-1"></i> Assign Request
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>