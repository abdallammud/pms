<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between mt-3 align-items-center mb-3">
        <h5 class="page-title">Invoices List</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInvoiceModal">
            <i class="bi bi-plus me-2"></i> Create Invoice
        </button>
    </div>
    <!-- Page Content -->
    <div class="page-content fade-in">
        <div class="card">
            <div class="card-body table">
                <div class="row mb-3 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-gear"></i></span>
                            <select class="form-select" id="bulkActionSelectInvoices">
                                <option value="">Bulk Actions</option>
                                <option value="delete">Delete Selected</option>
                            </select>
                            <button class="btn btn-secondary" id="applyBulkActionBtnInvoices"
                                type="button">Apply</button>
                        </div>
                    </div>
                    <!-- <div class="col-md-3">
                        <select class="form-select" id="typeFilter">
                            <option value="">All Types</option>
                            <option value="rent">Rent Invoices</option>
                            <option value="other_charge">Other Charges</option>
                        </select>
                    </div> -->
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="invoicesTable">
                        <thead>
                            <tr>
                                <th width="40"><input type="checkbox" id="selectAllInvoices"></th>
                                <th>Invoice #</th>
                                <th>Type</th>
                                <th>Charge</th>
                                <th>Tenant</th>
                                <th>Amount</th>
                                <th>Period</th>
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