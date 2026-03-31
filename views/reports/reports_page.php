<!-- Reports Hub -->
<main class="content">
    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
        <h5 class="page-title">Reports</h5>
    </div>

    <div class="page-content fade-in">

        <!-- Quick Date Range Strip -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body py-3">
                <form id="quick-report-form" class="row g-3 align-items-end" action="report_display" method="GET">
                    <input type="hidden" name="report_type" id="report_type_hidden">

                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Start Date</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                            <input type="text" name="startDate" id="startDate" class="form-control datepicker"
                                value="<?= date('Y-m-01') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">End Date</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                            <input type="text" name="endDate" id="endDate" class="form-control datepicker"
                                value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Quick Range</label>
                        <select class="form-select form-select-sm" id="quick_range">
                            <option value="">Custom</option>
                            <option value="this_month" selected>This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_quarter">This Quarter</option>
                            <option value="this_year">This Year</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Cards Grid -->
        <div class="row g-3">

            <?php
            $report_cards = [
                [
                    'key' => 'rent_collection',
                    'title' => 'Rent Collection',
                    'desc' => 'Track rent payments received, outstanding balances, and collection rates per period.',
                    'icon' => 'cash-stack',
                    'color' => 'primary',
                ],
                [
                    'key' => 'unit_occupancy',
                    'title' => 'Unit Occupancy',
                    'desc' => 'Analyse occupied vs vacant units across properties and time ranges.',
                    'icon' => 'houses',
                    'color' => 'info',
                ],
                [
                    'key' => 'tenant_report',
                    'title' => 'Tenant Report',
                    'desc' => 'List of tenants with lease dates, payment history, and status.',
                    'icon' => 'people',
                    'color' => 'success',
                ],
                [
                    'key' => 'outstanding_balance',
                    'title' => 'Outstanding Balance',
                    'desc' => 'View all unpaid and partially paid invoices with aged balances.',
                    'icon' => 'exclamation-triangle',
                    'color' => 'danger',
                ],
                [
                    'key' => 'income_expense',
                    'title' => 'Income vs Expense',
                    'desc' => 'Compare total income from rent against recorded expenses.',
                    'icon' => 'graph-up-arrow',
                    'color' => 'primary',
                ],
                [
                    'key' => 'maintenance_report',
                    'title' => 'Maintenance Report',
                    'desc' => 'Overview of maintenance requests by status, priority, and property.',
                    'icon' => 'tools',
                    'color' => 'warning',
                ],
                [
                    'key' => 'maintenance_expense',
                    'title' => 'Maintenance Expenses',
                    'desc' => 'Breakdown of expenses linked to maintenance work across properties.',
                    'icon' => 'receipt-cutoff',
                    'color' => 'secondary',
                ],
            ];
            ?>

            <?php foreach ($report_cards as $card): ?>
                <div class="col-xl-4 col-lg-6 col-md-6" style="margin-bottom: 10px;">
                    <div class="card border-0 shadow-sm report-hub-card h-100" onclick="runReport('<?= $card['key'] ?>')"
                        style="cursor: pointer; transition: transform .18s, box-shadow .18s;">
                        <div class="card-body d-flex gap-3 align-items-start p-4">
                            <div class="report-hub-icon bg-<?= $card['color'] ?> bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width:52px; height:52px;">
                                <i class="bi bi-<?= $card['icon'] ?> fs-4 text-<?= $card['color'] ?>"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1"><?= $card['title'] ?></h6>
                                <p class="text-muted small mb-2"><?= $card['desc'] ?></p>
                                <span class="btn btn-<?= $card['color'] ?> btn-sm px-3">
                                    <i class="bi bi-arrow-right me-1"></i>View Report
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>

    </div>
</main>

<style>
    .report-hub-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 24px rgba(29, 51, 84, .12) !important;
    }
</style>

<script>
    function runReport(type) {
        var form = document.getElementById('quick-report-form');
        document.getElementById('report_type_hidden').value = type;
        form.submit();
    }

    // Quick date range
    $(document).on('change', '#quick_range', function () {
        var val = $(this).val();
        var now = new Date();
        var y = now.getFullYear(), m = now.getMonth();
        var start, end;

        function pad(n) { return n < 10 ? '0' + n : n; }
        function fmt(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }

        if (val === 'this_month') {
            start = new Date(y, m, 1);
            end = new Date(y, m + 1, 0);
        } else if (val === 'last_month') {
            start = new Date(y, m - 1, 1);
            end = new Date(y, m, 0);
        } else if (val === 'this_quarter') {
            var q = Math.floor(m / 3);
            start = new Date(y, q * 3, 1);
            end = new Date(y, q * 3 + 3, 0);
        } else if (val === 'this_year') {
            start = new Date(y, 0, 1);
            end = new Date(y, 11, 31);
        } else {
            return;
        }
        $('#startDate').val(fmt(start));
        $('#endDate').val(fmt(end));
    });
</script>