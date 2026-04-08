<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="d-flex justify-content-between mt-3 align-items-center  mb-3">
        <h5 class="page-title">Maintenance Requests</h5>
        <div>
            <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#assignMaintenanceModal">
                <i class="bi bi-person-plus me-2"></i> Assign Request
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRequestModal">
                <i class="bi bi-plus-circle me-2"></i> Create Request
            </button>
        </div>
    </div>
    <!-- Page Content -->
    <div class="page-content fade-in">
        <?php
        $summary_cards = [
            ['label' => 'Total Requests', 'value' => '...', 'icon' => 'bi-tools', 'color' => 'primary'],
            ['label' => 'Pending/Work', 'value' => '...', 'icon' => 'bi-clock-history', 'color' => 'warning'],
            ['label' => 'High Priority', 'value' => '...', 'icon' => 'bi-exclamation-triangle', 'color' => 'danger'],
        ];
        include 'views/partials/summary_cards.php';
        ?>
        <div class="card shadow-sm border-0">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-hover w-100" id="requestsTable">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Reference #</th>
                                <th>Property</th>
                                <th>Unit</th>
                                <th>Priority</th>
                                <th>Assigned To</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
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

<!-- Modals -->
<?php include 'modals/create_request.php'; ?>
<?php include 'modals/assign_request.php'; ?>

<script src="public/js/summary_cards.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        loadSummaryStats('app/maintenance_controller.php?action=get_maintenance_stats', '.card-stats-row');
    });
</script>
<script src="<?= baseUri(); ?>/public/js/modules/maintenance.js"></script>