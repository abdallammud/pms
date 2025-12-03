<!-- Add Maintenance Request Modal -->
<?php $conn = $GLOBALS['conn'];?>
<div class="modal fade" id="addRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">New Maintenance Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="app/add_request.php" method="POST">
                <div class="modal-body">
                    <div class="row g-3">

                        <!-- Property -->
                        <div class="col-md-6">
                            <label class="form-label">Property</label>
                            <select name="property_id" id="propertySelect" class="form-select" required>
                                <option value="">Select Property</option>
                                <?php
                                $q = $conn->query("SELECT id, name FROM properties ORDER BY name");
                                while($p = $q->fetch_assoc()){
                                    echo '<option value="'.$p['id'].'">'.$p['name'].'</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Unit -->
                        <div class="col-md-6">
                            <label class="form-label">Unit</label>
                            <select name="unit_id" id="unitSelect" class="form-select" required>
                                <option value="">Select Unit</option>
                            </select>
                        </div>

                        <!-- Priority -->
                        <div class="col-md-6">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>

                        <!-- Requester -->
                        <div class="col-md-6">
                            <label class="form-label">Requester</label>
                            <input type="text" name="requester" class="form-control" required>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" class="form-control" required></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Submit Request</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
// Dynamic Units Loader
document.addEventListener('DOMContentLoaded', function () {
    let propertyId = $(this).val();

    // $('#unitSelect').html('<option>Loading...</option>');

    // $.post("app/get_units.php", { property_id: propertyId }, function (data) {
    //     $('#unitSelect').html(data);
    // });
});
</script>
