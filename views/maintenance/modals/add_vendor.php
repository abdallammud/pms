<!-- Add Vendor Modal -->
<div class="modal fade" id="addVendorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add New Vendor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="app/add_vendor.php" method="POST">
                <div class="modal-body">
                    <div class="row g-3">

                        <!-- Vendor Name -->
                        <div class="col-md-6">
                            <label class="form-label">Vendor Name</label>
                            <input type="text" name="vendor_name" class="form-control" required>
                        </div>

                        <!-- Service Type -->
                        <div class="col-md-6">
                            <label class="form-label">Service Type</label>
                            <input type="text" name="service_type" class="form-control" required>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Add Vendor</button>
                </div>

            </form>

        </div>
    </div>
</div>
