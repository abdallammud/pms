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
                <div class="row mb-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-gear"></i></span>
                            <select class="form-select" id="bulkActionSelect">
                                <option value="">Bulk Actions</option>
                                <option value="delete">Delete Selected</option>
                                <option value="terminate">Terminate Selected</option>
                                <option value="invoice">Create Invoice (Other Charges)</option>
                                <option value="auto_rent_invoice">Auto Generate Rent Invoice</option>
                            </select>
                            <button class="btn btn-secondary" id="applyBulkActionBtn" type="button">Apply</button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="leasesTable">
                        <thead>
                            <tr>
<<<<<<< HEAD
                                <th width="40"><input type="checkbox" id="selectAllLeasesCheckBox"></th>
=======
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
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

<<<<<<< HEAD
<!-- Auto-Rent Progress Modal -->
<div class="modal fade" id="autoRentProgressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Generating Rent Invoices</h5>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <p id="progressText">Processing leases, please wait...</p>
                    <div class="progress" style="height: 25px;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;">0%</div>
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
=======
<script src="<?=baseUri();?>/public/js/modules/lease.js"></script>
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
