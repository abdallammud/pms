<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between mt-3 align-items-center  mb-3">
        <h5 class="page-title">Vendors List</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVendorModal">
            <i class="bi bi-plus me-2"></i> Add Vendor
        </button>
    </div>
    <!-- Page Content -->
    <div class="page-content fade-in">
        <div class="card">
            <div class="card-body table">
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="vendorsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Vendor Name</th>
                                <th>Service Type</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once 'views/maintenance/modals/add_vendor.php'; ?>
<script src="public/js/modules/vendors.js"></script>
