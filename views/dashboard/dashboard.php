<!-- Dashboard -->
<main class="content">

    <!-- ── KPI Cards Row ──────────────────────────────────────────── -->
    <div class="row g-3 mb-4 mt-1" id="kpi_row">

        <!-- Properties -->
        <div class="col-xl col-lg-4 col-md-4 col-6">
            <div class="card kpi-card border-0 shadow-sm kpi-gradient-primary h-100">
                <div class="card-body d-flex flex-column justify-content-between position-relative overflow-hidden">
                    <div class="kpi-icon-bg"><i class="bi bi-buildings"></i></div>
                    <div>
                        <div class="kpi-icon-sm mb-3"><i class="bi bi-buildings"></i></div>
                        <div class="kpi-val" id="stat_total_properties">—</div>
                        <div class="kpi-label">Properties</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Units -->
        <div class="col-xl col-lg-4 col-md-4 col-6">
            <div class="card kpi-card border-0 shadow-sm kpi-gradient-info h-100">
                <div class="card-body d-flex flex-column justify-content-between position-relative overflow-hidden">
                    <div class="kpi-icon-bg"><i class="bi bi-door-open"></i></div>
                    <div>
                        <div class="kpi-icon-sm mb-3"><i class="bi bi-door-open"></i></div>
                        <div class="kpi-val" id="stat_total_units">—</div>
                        <div class="kpi-label">Total Units</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Occupancy Rate -->
        <div class="col-xl col-lg-4 col-md-4 col-6">
            <div class="card kpi-card border-0 shadow-sm kpi-gradient-success h-100">
                <div class="card-body d-flex flex-column justify-content-between position-relative overflow-hidden">
                    <div class="kpi-icon-bg"><i class="bi bi-house-check"></i></div>
                    <div class="kpi-sub-bubble text-end w-100 position-absolute pe-3" id="stat_occ_sub"
                        style="top: 15px; right: 0;"></div>
                    <div>
                        <div class="kpi-icon-sm mb-3"><i class="bi bi-house-check"></i></div>
                        <div class="kpi-val" id="stat_occupancy_pct">—</div>
                        <div class="kpi-label">Occupancy Rate</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Tenants -->
        <div class="col-xl col-lg-4 col-md-4 col-6">
            <div class="card kpi-card border-0 shadow-sm kpi-gradient-purple h-100">
                <div class="card-body d-flex flex-column justify-content-between position-relative overflow-hidden">
                    <div class="kpi-icon-bg"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="kpi-icon-sm mb-3"><i class="bi bi-people"></i></div>
                        <div class="kpi-val" id="stat_active_tenants">—</div>
                        <div class="kpi-label">Active Tenants</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- This Month Income -->
        <div class="col-xl col-lg-4 col-md-4 col-6">
            <div class="card kpi-card border-0 shadow-sm kpi-gradient-emerald h-100">
                <div class="card-body d-flex flex-column justify-content-between position-relative overflow-hidden">
                    <div class="kpi-icon-bg"><i class="bi bi-cash-stack"></i></div>
                    <div>
                        <div class="kpi-icon-sm mb-3"><i class="bi bi-cash-stack"></i></div>
                        <div class="kpi-val" id="stat_this_month_income">—</div>
                        <div class="kpi-label">This Month Income</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Outstanding -->
        <div class="col-xl col-lg-4 col-md-4 col-6">
            <div class="card kpi-card border-0 shadow-sm kpi-gradient-danger h-100">
                <div class="card-body d-flex flex-column justify-content-between position-relative overflow-hidden">
                    <div class="kpi-icon-bg"><i class="bi bi-exclamation-triangle"></i></div>
                    <div>
                        <div class="kpi-icon-sm mb-3"><i class="bi bi-exclamation-triangle"></i></div>
                        <div class="kpi-val" id="stat_outstanding_amount">—</div>
                        <div class="kpi-label">Outstanding</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ── Charts Row ─────────────────────────────────────────────── -->
    <div class="row g-3 mb-4">

        <div class="col-xl-8 col-lg-7 fade-in">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold border-0 pt-3">
                    <i class="bi bi-graph-up-arrow me-2 text-primary"></i>Income vs Expense (Last 6 months)
                </div>
                <div class="card-body pt-2">
                    <canvas id="incomeExpenseChart" style="max-height:260px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5 fade-in">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold border-0 pt-3">
                    <i class="bi bi-pie-chart me-2 text-primary"></i>Occupancy
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center pt-2">
                    <canvas id="occupancyChart" style="max-height:180px;"></canvas>
                    <div class="d-flex gap-3 mt-3 small">
                        <span><i class="bi bi-circle-fill text-primary me-1"></i>Occupied</span>
                        <span><i class="bi bi-circle-fill text-secondary me-1"></i>Vacant</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ── Lease Summary + Maintenance Queue ──────────────────────── -->
    <div class="row g-3 mb-4">

        <div class="col-md-4 fade-in">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold border-0 pt-3">
                    <i class="bi bi-file-earmark-text me-2 text-primary"></i>Lease Summary
                </div>
                <div class="card-body" id="lease_summary_body">
                    <div class="text-center text-muted py-4">
                        <div class="spinner-border spinner-border-sm"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8 fade-in">
            <div class="card border-0 shadow-sm h-100">
                <div
                    class="card-header bg-white fw-bold border-0 pt-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-tools me-2 text-primary"></i>Open Maintenance Requests</span>
                    <a href="<?= baseUri() ?>/index.php?menu=maintenance&tab=maintenance_requests"
                        class="btn btn-outline-primary btn-xs">View All</a>
                </div>
                <div class="card-body p-0" id="maintenance_queue_body">
                    <div class="text-center text-muted py-4">
                        <div class="spinner-border spinner-border-sm"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ── Receivables + Upcoming Leases ──────────────────────────── -->
    <div class="row g-3 mb-4">

        <div class="col-xl-6 fade-in">
            <div class="card border-0 shadow-sm h-100">
                <div
                    class="card-header bg-white fw-bold border-0 pt-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-receipt me-2 text-primary"></i>Rent Receivables</span>
                    <a href="<?= baseUri() ?>/index.php?menu=accounting&tab=invoices"
                        class="btn btn-outline-primary btn-xs">View All</a>
                </div>
                <div class="card-body p-0" id="receivables_body">
                    <div class="text-center text-muted py-4">
                        <div class="spinner-border spinner-border-sm"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 fade-in">
            <div class="card border-0 shadow-sm h-100">
                <div
                    class="card-header bg-white fw-bold border-0 pt-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-calendar-event me-2 text-primary"></i>Upcoming Lease Expirations</span>
                    <a href="<?= baseUri() ?>/index.php?menu=leases" class="btn btn-outline-primary btn-xs">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tenant</th>
                                    <th>Property</th>
                                    <th>Unit</th>
                                    <th>Expires</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="upcoming_leases_table">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        <div class="spinner-border spinner-border-sm"></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ── Recent Payments ────────────────────────────────────────── -->
    <div class="row g-3 mb-4">
        <div class="col-12 fade-in">
            <div class="card border-0 shadow-sm">
                <div
                    class="card-header bg-white fw-bold border-0 pt-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-credit-card me-2 text-primary"></i>Recent Payments</span>
                    <a href="<?= baseUri() ?>/index.php?menu=accounting&tab=payments_received"
                        class="btn btn-outline-primary btn-xs">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tenant</th>
                                    <th>Property</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                </tr>
                            </thead>
                            <tbody id="recent_payments_table">
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">
                                        <div class="spinner-border spinner-border-sm"></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>

<style>
    .kpi-card {
        border-radius: 16px;
        color: #fff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1) !important;
    }

    .kpi-card .card-body {
        padding: 20px;
        z-index: 2;
    }

    .kpi-icon-bg {
        position: absolute;
        right: -10px;
        bottom: -15px;
        font-size: 6rem;
        opacity: 0.15;
        z-index: 1;
        line-height: 1;
    }

    .kpi-icon-sm {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        font-size: 1.2rem;
        color: #fff;
    }

    .kpi-val {
        font-size: 1.7rem;
        font-weight: 700;
        line-height: 1.1;
        color: #fff;
        margin-bottom: 2px;
    }

    .kpi-label {
        font-size: .8rem;
        color: rgba(255, 255, 255, 0.85);
        font-weight: 500;
    }

    .kpi-sub-bubble {
        font-size: .75rem;
        color: rgba(255, 255, 255, 0.9);
        font-weight: 600;
        z-index: 3;
    }

    /* Beautiful Gradients */
    .kpi-gradient-primary { background: linear-gradient(135deg, #3a7bd5 0%, #3a6073 100%); }
    .kpi-gradient-info { background: linear-gradient(135deg, #00b4db 0%, #0083b0 100%); }
    .kpi-gradient-success { background: linear-gradient(135deg, #56ab2f 0%, #a8e063 100%); }
    .kpi-gradient-purple { background: linear-gradient(135deg, #8e2de2 0%, #4a00e0 100%); }
    .kpi-gradient-emerald { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .kpi-gradient-danger { background: linear-gradient(135deg, #ed213a 0%, #93291e 100%); }

    .btn-xs {
        font-size: .72rem;
        padding: 2px 10px;
    }
</style>

<!-- chart.js and dashboard.js are loaded from app_footer.php -->