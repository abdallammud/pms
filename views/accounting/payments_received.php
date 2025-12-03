<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="page-header fade-in">
        <h1 class="page-title">Payments Received</h1>
    </div>
    <!-- Page Content -->
    <div class="page-content fade-in">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="card-title">Receipts List</h5>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReceiptModal">
                        <i class="bi bi-plus me-2"></i> Add Receipt
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="receiptsTable">
                        <thead>
                            <tr>
                                <th>Receipt ID</th>
                                <th>Invoice #</th>
                                <th>Tenant</th>
                                <th>Amount Paid</th>
                                <th>Payment Method</th>
                                <th>Date</th>
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
<?php require 'views/accounting/modals/add_receipt.php'; ?>
<script src="public/js/modules/receipt.js"></script>
