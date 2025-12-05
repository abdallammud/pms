<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between mt-3 align-items-center  mb-3">
        <h5 class="page-title">Bills List</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBillModal">
            <i class="bi bi-plus me-2"></i> Add Bill
        </button>
    </div>
    <!-- Page Content -->
    <div class="page-content fade-in">
        <div class="card">
            <div class="card-body table">
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="expensesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Property</th>
                                <th>Category</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Description</th>
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
<?php require 'views/accounting/modals/add_expense.php'; ?>
<script src="public/js/modules/expenses.js"></script>
