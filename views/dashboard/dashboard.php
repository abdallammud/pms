
<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="page-header fade-in">
        <h1 class="page-title">Dashboard</h1>
        <div>
            <!-- <button class="btn btn-primary">
                <i class="bi bi-download me-2"></i> Export Report
            </button> -->
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3 fade-in">
            <div class="card dashboard-card stats-card h-100">
                <div class="card-body">
                    <div class="stats-icon primary">
                        <i class="bi bi-buildings"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-title">Total Properties</div>
                        <div class="stats-value">24</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3 fade-in">
            <div class="card dashboard-card stats-card h-100">
                <div class="card-body">
                    <div class="stats-icon secondary">
                        <i class="bi bi-door-open"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-title">Total Units</div>
                        <div class="stats-value">186</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3 fade-in">
            <div class="card dashboard-card stats-card h-100">
                <div class="card-body">
                    <div class="stats-icon success">
                        <i class="bi bi-house-check"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-title">Occupied Units</div>
                        <div class="stats-value">162</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3 fade-in">
            <div class="card dashboard-card stats-card h-100">
                <div class="card-body">
                    <div class="stats-icon warning">
                        <i class="bi bi-house-x"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-title">Vacant Units</div>
                        <div class="stats-value">24</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3 fade-in">
            <div class="card dashboard-card stats-card h-100">
                <div class="card-body">
                    <div class="stats-icon primary">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-title">Active Tenants</div>
                        <div class="stats-value">162</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3 fade-in">
            <div class="card dashboard-card stats-card h-100">
                <div class="card-body">
                    <div class="stats-icon danger">
                        <i class="bi bi-calendar-x"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-title">This Month's Rent Due</div>
                        <div class="stats-value">$48,600</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3 fade-in">
            <div class="card dashboard-card stats-card h-100">
                <div class="card-body">
                    <div class="stats-icon success">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <div class="stats-info">
                        <div class="stats-title">Rent Collected</div>
                        <div class="stats-value">$42,150</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3 fade-in">
            <div class="card dashboard-card stats-card h-100">
                <div class="card-body">
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

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-xl-8 col-lg-7 mb-3 fade-in">
            <div class="card dashboard-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Monthly Rent Collection Trend</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                            id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item" href="#">Last 6 months</a></li>
                            <li><a class="dropdown-item" href="#">Last year</a></li>
                            <li><a class="dropdown-item" href="#">All time</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="rentCollectionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5 mb-3 fade-in">
            <div class="card dashboard-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Occupancy Rate</h6>
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

    <!-- Income vs Expense Chart -->
    <div class="row mb-4">
        <div class="col-xl-12 col-lg-12 mb-3 fade-in">
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Income vs Expense</h6>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                            id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                            <li><a class="dropdown-item" href="#">Last 6 months</a></li>
                            <li><a class="dropdown-item" href="#">Last year</a></li>
                            <li><a class="dropdown-item" href="#">All time</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="incomeExpenseChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="row mb-4">
        <div class="col-xl-6 col-lg-6 mb-3 fade-in">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">Recent Maintenance Requests</h6>
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
            <div class="card dashboard-card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">Upcoming Lease Expirations</h6>
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
    </div>

    <!-- More Tables Row -->
    <div class="row mb-4">
        <div class="col-xl-6 col-lg-6 mb-3 fade-in">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">Tenants in Arrears</h6>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tenant</th>
                                    <th>Property</th>
                                    <th>Unit</th>
                                    <th>Amount Due</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Robert Wilson</td>
                                    <td>Sunset Apartments</td>
                                    <td>D-104</td>
                                    <td>$1,200</td>
                                    <td>
                                        <button class="btn btn-sm btn-success">
                                            <i class="bi bi-cash me-1"></i> Collect
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Sarah Davis</td>
                                    <td>Green Valley</td>
                                    <td>E-207</td>
                                    <td>$850</td>
                                    <td>
                                        <button class="btn btn-sm btn-success">
                                            <i class="bi bi-cash me-1"></i> Collect
                                        </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>David Martinez</td>
                                    <td>Oak Residences</td>
                                    <td>F-303</td>
                                    <td>$1,500</td>
                                    <td>
                                        <button class="btn btn-sm btn-success">
                                            <i class="bi bi-cash me-1"></i> Collect
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
            <div class="card dashboard-card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">Recent Payments</h6>
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

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12 fade-in">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="quick-actions">
                        <a href="#" class="quick-action-btn">
                            <i class="bi bi-person-plus-fill"></i>
                            <span>Add Tenant</span>
                        </a>
                        <a href="#" class="quick-action-btn">
                            <i class="bi bi-building"></i>
                            <span>Add Property</span>
                        </a>
                        <a href="#" class="quick-action-btn">
                            <i class="bi bi-receipt-cutoff"></i>
                            <span>Create Invoice</span>
                        </a>
                        <a href="#" class="quick-action-btn">
                            <i class="bi bi-tools"></i>
                            <span>Log Maintenance</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="./public/plugins/chartjs/js/chart.js"></script>
<script src="./public/js/dashboard.js"></script>
