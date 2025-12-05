
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
                                <div class="stats-value">24</div>
                            </div>
                        </div>

                        <!-- ITEM 2 -->
                        <div class="col-6 stats-item">
                            <div class="stats-icon success">
                                <i class="bi bi-house-check"></i>
                            </div>
                            <div class="stats-info">
                                <div class="stats-title">Occupied Units</div>
                                <div class="stats-value">162</div>
                            </div>
                        </div>

                        <!-- ITEM 3 -->
                        <div class="col-6 stats-item">
                            <div class="stats-icon warning">
                                <i class="bi bi-house-x"></i>
                            </div>
                            <div class="stats-info">
                                <div class="stats-title">Vacant Units</div>
                                <div class="stats-value">24</div>
                            </div>
                        </div>

                        <!-- ITEM 4 -->
                        <div class="col-6 stats-item">
                            <div class="stats-icon primary">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="stats-info">
                                <div class="stats-title">Active Tenants</div>
                                <div class="stats-value">162</div>
                            </div>
                        </div>

                        <!-- ITEM 5 -->
                        <div class="col-6 stats-item">
                            <div class="stats-icon success">
                                <i class="bi bi-cash-stack"></i>
                            </div>
                            <div class="stats-info">
                                <div class="stats-title">Rent Collected</div>
                                <div class="stats-value">$42,150</div>
                            </div>
                        </div>

                        <!-- ITEM 6 -->
                        <div class="col-6 stats-item">
                            <div class="stats-icon warning">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <div class="stats-info">
                                <div class="stats-title">Outstanding Amount</div>
                                <div class="stats-value">$6,450</div>
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
                            <tbody>
                                <tr>
                                    <td>John Smith</td>
                                    <td>Sunset Apartments</td>
                                    <td>A-101</td>
                                    <td>Aug 15, 2023</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary">
                                            <i class="bi bi-arrow-repeat me-1"></i> Renew
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Emily Johnson</td>
                                    <td>Green Valley</td>
                                    <td>B-205</td>
                                    <td>Aug 22, 2023</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary">
                                            <i class="bi bi-arrow-repeat me-1"></i> Renew
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Michael Brown</td>
                                    <td>Oak Residences</td>
                                    <td>C-302</td>
                                    <td>Sep 05, 2023</td>
                                    <td>
                                        <button class="btn btn-sm btn-primary">
                                            <i class="bi bi-arrow-repeat me-1"></i> Renew
                                        </button>
                                    </td>
                                </tr>
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
                            <tbody>
                                <tr>
                                    <td>Sunset Apartments</td>
                                    <td>A-101</td>
                                    <td>Leaking Faucet</td>
                                    <td><span class="badge bg-warning">In Progress</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info btn-icon">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Green Valley</td>
                                    <td>B-205</td>
                                    <td>AC Not Working</td>
                                    <td><span class="badge bg-danger">Pending</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info btn-icon">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Oak Residences</td>
                                    <td>C-302</td>
                                    <td>Broken Window</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                    <td>
                                        <button class="btn btn-sm btn-info btn-icon">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
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
                            <tbody>
                                <tr>
                                    <td>John Smith</td>
                                    <td>Sunset Apartments</td>
                                    <td>$1,200</td>
                                    <td>Aug 01, 2023</td>
                                    <td><span class="badge bg-success">Bank Transfer</span></td>
                                </tr>
                                <tr>
                                    <td>Emily Johnson</td>
                                    <td>Green Valley</td>
                                    <td>$950</td>
                                    <td>Aug 02, 2023</td>
                                    <td><span class="badge bg-info">Credit Card</span></td>
                                </tr>
                                <tr>
                                    <td>Michael Brown</td>
                                    <td>Oak Residences</td>
                                    <td>$1,500</td>
                                    <td>Aug 03, 2023</td>
                                    <td><span class="badge bg-warning">Cash</span></td>
                                </tr>
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
