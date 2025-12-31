<!-- Main Content -->
<main class="content">
    <!-- Page Header -->


    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-6 col-lg-6 col-md-12 fade-in">
            <div class="card dashboard-summary-card h-100">

                <!-- TITLE -->
                <div class="card-header bg-transparent fw-bold">
                    Summary
                </div>

                <!-- CONTENT -->
                <div class="card-body">
                    <div class="row g-3">

                        <!-- ITEM 1 -->
                        <div class="col-6 stats-item">
                            <div class="stats-icon primary">
                                <i class="bi bi-buildings"></i>
                            </div>
                            <div class="stats-info">
                                <div class="stats-title">Total Properties</div>
                                <div class="stats-value" id="stat_total_properties">0</div>
                            </div>
                        </div>

                        <!-- ITEM 2 -->
                        <div class="col-6 stats-item">
                            <div class="stats-icon success">
                                <i class="bi bi-house-check"></i>
                            </div>
                            <div class="stats-info">
                                <div class="stats-title">Occupied Units</div>
                                <div class="stats-value" id="stat_occupied_units">0</div>
                            </div>
                        </div>

                        <!-- ITEM 3 -->
                        <div class="col-6 stats-item">
                            <div class="stats-icon warning">
                                <i class="bi bi-house-x"></i>
                            </div>
                            <div class="stats-info">
                                <div class="stats-title">Vacant Units</div>
                                <div class="stats-value" id="stat_vacant_units">0</div>
                            </div>
                        </div>

                        <!-- ITEM 4 -->
                        <div class="col-6 stats-item">
                            <div class="stats-icon primary">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="stats-info">
                                <div class="stats-title">Active Tenants</div>
                                <div class="stats-value" id="stat_active_tenants">0</div>
                            </div>
                        </div>

                        <!-- ITEM 5 -->
                        <div class="col-6 stats-item">
                            <div class="stats-icon success">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <div class="stats-info">
                                <div class="stats-title">Rent Collected</div>
                                <div class="stats-value" id="stat_rent_collected">$0</div>
                            </div>
                        </div>

                        <!-- ITEM 6 -->
                        <div class="col-6 stats-item">
                            <div class="stats-icon warning">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <div class="stats-info">
                                <div class="stats-title">Outstanding Amount</div>
                                <div class="stats-value" id="stat_outstanding_amount">$0</div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
        <div class="col-xl-6 col-lg-6 col-md-12 fade-in">
            <div class="card dashboard-summary-card h-100">
                <div class="card-header bg-transparent fw-bold">
                    Income vs Expense
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="incomeExpenseChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7 mb-3 fade-in">
            <div class="card dashboard-summary-card h-100">
                <div class="card-header bg-transparent fw-bold">
                    Upcoming Lease Expirations
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Property</th>
                                    <th>Unit</th>
                                    <th>Expiry Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="upcoming_leases_table">
                                <!-- Dynamic -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5 mb-3 fade-in">
            <div class="card dashboard-summary-card h-100 h-100">
                <div class="card-header bg-transparent fw-bold">
                    Occupancy Rate
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="occupancyChart"></canvas>
                    </div>
                    <div class="mt-3 text-center small">
                        <span class="mr-2">
                            <i class="bi bi-circle-fill text-primary"></i> Occupied
                        </span>
                        <span class="mr-2">
                            <i class="bi bi-circle-fill text-secondary"></i> Vacant
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Tables Row -->
    <div class="row mb-4">
        <div class="col-xl-6 col-lg-6 mb-3 fade-in">
            <div class="card dashboard-summary-card h-100 h-100">
                <div class="card-header bg-transparent fw-bold">
                    Recent Maintenance Requests
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Property</th>
                                    <th>Unit</th>
                                    <th>Issue</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="recent_maintenance_table">
                                <!-- Dynamic -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6 mb-3 fade-in">
            <div class="card dashboard-summary-card h-100 h-100">
                <div class="card-header bg-transparent fw-bold">
                    Recent Payments
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Property</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                </tr>
                            </thead>
                            <tbody id="recent_payments_table">
                                <!-- Dynamic -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>




</main>
<script src="./public/plugins/chartjs/js/chart.js"></script>
<script src="./public/js/dashboard.js"></script>