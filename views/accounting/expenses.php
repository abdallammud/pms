<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between mt-3 align-items-center  mb-3">
        <h5 class="page-title">Expenses List</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
            <i class="bi bi-plus me-2"></i> Add Expense
        </button>
    </div>
    <!-- Page Content -->
    <div class="page-content fade-in">
        <?php
        $summary_cards = [
            ['label' => 'Total Expenses', 'value' => '...', 'icon' => 'bi-cart-dash', 'color' => 'danger'],
            ['label' => 'This Month', 'value' => '...', 'icon' => 'bi-calendar-month', 'color' => 'warning'],
            ['label' => 'Expense Count', 'value' => '...', 'icon' => 'bi-list-ol', 'color' => 'primary'],
        ];
        include 'views/partials/summary_cards.php';
        ?>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="expensesTable">
                        <thead>
                            <tr>
                                <th>Ref #</th>
                                <th>Type</th>
                                <th>Property</th>
                                <th>Category</th>
                                <th>Amount</th>
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
<script src="public/js/summary_cards.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        loadSummaryStats('app/expense_controller.php?action=get_expense_stats', '.card-stats-row');
    });
</script>
<script src="public/js/modules/expenses.js"></script>