<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
     <div class="d-flex mt-3 align-items-center justify-content-between mb-3">
        <h5 class="page-title">Properties List</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPropertyModal">
            <i class="bi bi-plus me-2"></i> Add Property
        </button>
    </div>
    <!-- Page Content -->
    <div class="page-content fade-in">
        <div class="card">
            <div class="card-body table">
               
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="propertiesTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Address</th>
                                <th>Units</th>
                                <th>Occupied Units</th>
                                <th>Manager</th>
                                <th>Owner</th>
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
