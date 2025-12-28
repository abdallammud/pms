<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between mt-3 align-items-center  mb-3">
        <h5 class="page-title">Vendors / Staff Management</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVendorModal">
            <i class="bi bi-plus-circle me-2"></i> Add Vendor
        </button>
    </div>
    <!-- Page Content -->
    <div class="page-content fade-in">
        <div class="card shadow-sm border-0">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover w-100" id="vendorsTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Vendor Name</th>
                                <th>Service Type</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th class="text-end">Actions</th>
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

<!-- Modals -->
<?php include 'modals/add_vendor.php'; ?>

<script src="<?= baseUri(); ?>/public/js/modules/vendors.js"></script>