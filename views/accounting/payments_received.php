<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between mt-3 align-items-center  mb-3">
        <h5 class="page-title">Receipts List</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReceiptModal">
            <i class="bi bi-plus me-2"></i> Add Receipt
        </button>
    </div>
    <!-- Page Content -->
    <div class="page-content fade-in">
        <?php
        $summary_cards = [
            ['label' => 'Total Received', 'value' => '...', 'icon' => 'bi-cash-coin', 'color' => 'success'],
            ['label' => 'Receipt Count', 'value' => '...', 'icon' => 'bi-receipt', 'color' => 'primary'],
            ['label' => 'Received Today', 'value' => '...', 'icon' => 'bi-calendar-check', 'color' => 'info'],
        ];
        include 'views/partials/summary_cards.php';
        ?>
        <div class="card">
            <div class="card-body table">
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="receiptsTable">
                        <thead>
                            <tr>
                                <th>Receipt #</th>
                                <th>Invoice #</th>
                                <th>Tenant</th>
                                <th>Amount Paid</th>
                                <th>Method</th>
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
<script src="public/js/summary_cards.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        loadSummaryStats('app/receipt_controller.php?action=get_payment_stats', '.card-stats-row');
    });
</script>
<script src="<?= baseUri(); ?>/public/js/modules/receipt.js"></script>