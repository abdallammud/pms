<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="page-header fade-in">
        <h1 class="page-title">Units Management</h1>
    </div>
    <!-- Page Content -->
    <div class="page-content fade-in">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="card-title">Units List</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUnitModal">
                        <i class="bi bi-plus me-2"></i> Add Unit
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="unitsTable">
                        <thead>
                            <tr>
                                <th>Unit Name</th>
                                <th>Unit Type</th>
                                <th>Unit Address</th>
                                <th>Unit Status</th>
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
<?php require_once 'views/properties/modals/add_unit.php'; ?>
<script src="public/js/modules/properties.js"></script>