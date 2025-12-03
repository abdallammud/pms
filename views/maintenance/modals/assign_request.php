<!-- Assign Maintenance Modal -->
 <?php $conn = $GLOBALS['conn'];?>
<div class="modal fade" id="assignMaintenanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Assign Maintenance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="app/assign_maintenance.php" method="POST">
                <div class="modal-body">
                    <div class="row g-3">

                        <!-- Request -->
                        <div class="col-md-6">
                            <label class="form-label">Maintenance Request</label>
                            <select name="request_id" class="form-select" required>
                                <option value="">Select Request</option>
                                <?php
                                $r = $conn->query("
                                  SELECT id, description 
                                  FROM maintenance_requests 
                                  WHERE status != 'completed'
                                ");
                                while($req = $r->fetch_assoc()){
                                    echo '<option value="'.$req['id'].'">#'.$req['id'].' - '.$req['description'].'</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Vendor -->
                        <div class="col-md-6">
                            <label class="form-label">Vendor</label>
                            <select name="vendor_id" class="form-select" required>
                                <option value="">Select Vendor</option>
                                <?php
                                $v = $conn->query("SELECT id, vendor_name FROM vendors ORDER BY vendor_name");
                                while($ven = $v->fetch_assoc()){
                                    echo '<option value="'.$ven['id'].'">'.$ven['vendor_name'].'</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Assigned Date -->
                        <div class="col-md-6">
                            <label class="form-label">Assigned Date</label>
                            <input type="date" name="assigned_date" class="form-control" required>
                        </div>

                        <!-- Expected Completion -->
                        <div class="col-md-6">
                            <label class="form-label">Expected Completion</label>
                            <input type="date" name="expected_completion" class="form-control" required>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Assign</button>
                </div>

            </form>
        </div>
    </div>
</div>
