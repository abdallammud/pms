<?php
// reports_page.php
$reports = [
    'rent_collection' => 'Rent Collection',
    'unit_occupancy' => 'Unit Occupancy',
    'tenant_report' => 'Tenant Report',
    'outstanding_balance' => 'Outstanding Balance',
    'income_expense' => 'Income vs Expense',
    'maintenance_report' => 'Maintenance Report',
    'maintenance_expense' => 'Maintenance Expense Report'
];
?>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar: Report List -->
        <div class="col-md-3">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Available Reports</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="report-list">
                        <?php foreach ($reports as $key => $name): ?>
                            <a href="#" class="list-group-item list-group-item-action report-item"
                                data-report="<?php echo $key; ?>">
                                <?php echo $name; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content area: Filters -->
        <div class="col-md-9">
            <div class="card shadow mb-4" id="filter-card" style="display: none;">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary" id="selected-report-title">Report Filters</h6>
                </div>
                <div class="card-body">
                    <form action="report_display" method="GET" id="report-filter-form">
                        <input type="hidden" name="report_type" id="report_type">

                        <div class="row">
                            <!-- Common Date Range Filters -->
                            <div class="col-lg-3 mb-3">
                                <label for="startDate">Start Date</label>
                                <input type="text" name="startDate" id="startDate" class="form-control datepicker"
                                    value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-lg-3 mb-3">
                                <label for="endDate">End Date</label>
                                <input type="text" name="endDate" id="endDate" class="form-control datepicker"
                                    value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <!-- Dynamic Filters Placeholder -->
                        <div id="dynamic-filters" class="row">
                            <!-- Additional filters will be injected here via JS -->
                        </div>

                        <hr>
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-file-invoice mr-1"></i> Display Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Empty State -->
            <div id="report-empty-state" class="text-center py-5">
                <i class="fas fa-chart-pie fa-4x text-gray-300 mb-3"></i>
                <h4 class="text-gray-500">Select a report from the sidebar to continue</h4>
            </div>
        </div>
    </div>
</div>
</div>