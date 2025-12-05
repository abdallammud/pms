<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between mt-3 align-items-center  mb-3">
        <h5 class="page-title">Requests List</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRequestModal">
            <i class="bi bi-plus me-2"></i> Create Request
        </button>
    </div>
    <!-- Page Content -->
    <div class="page-content fade-in">
        <div class="card">
            <div class="card-body table">
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="requestsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Property</th>
                                <th>Unit</th>
                                <th>Priority</th>
                                <th>Description</th>
                                <th>Assigned To</th>
                                <th>Status</th>
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

<script src="public/js/modules/maintenance.js"></script>
