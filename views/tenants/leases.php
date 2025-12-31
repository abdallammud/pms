<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
        <h5 class="page-title">Leases List</h5>
        <a href="add_lease" class="btn btn-primary">
            <i class="bi bi-plus me-2"></i> Add Lease
        </a>
    </div>
    <!-- Page Content -->
    <div class="page-content fade-in">
        <div class="card">
            <div class="card-body table">
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="leasesTable">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Tenant</th>
                                <th>Property / Unit</th>
                                <th>Monthly Rent</th>
                                <th>Start Date</th>
                                <th>End Date</th>
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

<!-- Auto-Rent Progress Modal -->
<div class="modal fade" id="autoRentProgressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Generating Rent Invoices</h5>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <p id="progressText">Processing leases, please wait...</p>
                    <div class="progress" style="height: 25px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                            role="progressbar" style="width: 0%;">0%</div>
                    </div>
                </div>
                <div id="progressResults" class="overflow-auto" style="max-height: 300px;">
                    <ul class="list-group list-group-flush" id="progressList">
                        <!-- Per-lease status items will be appended here -->
                    </ul>
                </div>
            </div>
            <div class="modal-footer d-none" id="progressFooter">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>