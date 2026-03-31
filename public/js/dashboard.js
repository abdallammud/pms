/* ============================================================
   Dashboard JS – KPI + Charts + Widgets
   ============================================================ */

(function () {
    if (window.__dashboardInitialized) return;
    window.__dashboardInitialized = true;

    $(document).ready(function () {
        var occupancyChart, incomeExpenseChart;

        // Guard: exit if dashboard elements not on page
        if (!document.getElementById('kpi_row')) return;

        // Load all dashboard data in parallel
        $.getJSON(base_url + '/app/dashboard_controller.php?action=get_stats')
            .done(function (r) {
                updateKpis(r.stats);
                initCharts(r.charts);
                renderUpcomingLeases(r.recent.leases);
                renderRecentPayments(r.recent.payments);
            })
            .fail(function () { console.error('Dashboard stats failed'); });

        $.getJSON(base_url + '/app/dashboard_controller.php?action=get_receivables')
            .done(renderReceivables)
            .fail(function () { $('#receivables_body').html('<p class="text-center text-muted py-3">Failed to load.</p>'); });

        $.getJSON(base_url + '/app/dashboard_controller.php?action=get_lease_summary')
            .done(renderLeaseSummary)
            .fail(function () { $('#lease_summary_body').html('<p class="text-center text-muted py-3">Failed to load.</p>'); });

        $.getJSON(base_url + '/app/dashboard_controller.php?action=get_maintenance_queue')
            .done(renderMaintenanceQueue)
            .fail(function () { $('#maintenance_queue_body').html('<p class="text-center text-muted py-3">Failed to load.</p>'); });

        /* ── KPI ──────────────────────────────────────────── */
        function updateKpis(s) {
            set('stat_total_properties', s.total_properties);
            set('stat_total_units', s.total_units);
            set('stat_occupancy_pct', s.occupancy_pct + '%');
            set('stat_occ_sub', s.occupied_units + ' occ · ' + s.vacant_units + ' vac');
            set('stat_active_tenants', s.active_tenants);
            set('stat_this_month_income', s.this_month_income);
            set('stat_outstanding_amount', s.outstanding_amount);
        }

        function set(id, val) {
            var el = document.getElementById(id);
            if (el) el.textContent = val;
        }

        /* ── Charts ───────────────────────────────────────── */
        function initCharts(charts) {
            // Income vs Expense
            var ieCtx = document.getElementById('incomeExpenseChart');
            if (ieCtx) {
                if (incomeExpenseChart) incomeExpenseChart.destroy();
                incomeExpenseChart = new Chart(ieCtx, {
                    type: 'bar',
                    data: {
                        labels: charts.income_expense.labels,
                        datasets: [
                            {
                                label: 'Income',
                                data: charts.income_expense.income,
                                backgroundColor: 'rgba(29,51,84,.75)',
                                borderRadius: 5
                            },
                            {
                                label: 'Expense',
                                data: charts.income_expense.expense,
                                backgroundColor: 'rgba(220,53,69,.55)',
                                borderRadius: 5
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { position: 'bottom' } },
                        scales: { y: { beginAtZero: true, ticks: { callback: function (v) { return '$' + v.toLocaleString(); } } } }
                    }
                });
            }

            // Occupancy Doughnut
            var occCtx = document.getElementById('occupancyChart');
            if (occCtx) {
                if (occupancyChart) occupancyChart.destroy();
                occupancyChart = new Chart(occCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Occupied', 'Vacant'],
                        datasets: [{ data: charts.occupancy, backgroundColor: ['#1d3354', '#dee2e6'], borderWidth: 2 }]
                    },
                    options: {
                        responsive: true,
                        cutout: '70%',
                        plugins: { legend: { display: false } }
                    }
                });
            }
        }

        /* ── Lease Summary ────────────────────────────────── */
        function renderLeaseSummary(d) {
            var html = '<div class="row g-3">'
                + leaseStatBox('Active', d.active, 'success', 'check-circle')
                + leaseStatBox('Expiring Soon', d.expiring, 'warning', 'calendar-event')
                + leaseStatBox('Expired', d.expired, 'danger', 'calendar-x')
                + leaseStatBox('Terminated', d.terminated, 'secondary', 'x-circle')
                + '</div>';
            $('#lease_summary_body').html(html);
        }

        function leaseStatBox(label, val, color, icon) {
            return '<div class="col-6" style="margin-bottom: 10px;"><div class="d-flex align-items-center gap-2 p-2 rounded bg-' + color + ' bg-opacity-10">'
                + '<i class="bi bi-' + icon + ' text-' + color + ' fs-5"></i>'
                + '<div><div class="fw-bold fs-5 text-' + color + '">' + val + '</div>'
                + '<div class="small text-muted">' + label + '</div></div>'
                + '</div></div>';
        }

        /* ── Maintenance Queue ────────────────────────────── */
        function renderMaintenanceQueue(rows) {
            if (!rows || !rows.length) {
                $('#maintenance_queue_body').html('<p class="text-center text-muted py-3">No open requests.</p>');
                return;
            }
            var priorityMap = { high: 'danger', medium: 'warning', low: 'success' };
            var html = '<div class="table-responsive"><table class="table table-sm align-middle mb-0">'
                + '<thead class="table-light"><tr><th>Ref</th><th>Location</th><th>Issue</th><th>Priority</th><th>Assigned</th></tr></thead><tbody>';
            rows.forEach(function (r) {
                html += '<tr>'
                    + '<td class="small text-muted">' + esc(r.reference_number) + '</td>'
                    + '<td class="small">' + esc(r.property_name) + (r.unit_number ? ' / ' + esc(r.unit_number) : '') + '</td>'
                    + '<td class="small">' + esc(r.description.substring(0, 40)) + (r.description.length > 40 ? '…' : '') + '</td>'
                    + '<td><span class="badge bg-' + (priorityMap[r.priority] || 'secondary') + '">' + esc(r.priority) + '</span></td>'
                    + '<td class="small">' + (r.assigned_to ? esc(r.assigned_to) : '<span class="text-muted">Unassigned</span>') + '</td>'
                    + '</tr>';
            });
            html += '</tbody></table></div>';
            $('#maintenance_queue_body').html(html);
        }

        /* ── Receivables ──────────────────────────────────── */
        function renderReceivables(rows) {
            if (!rows || !rows.length) {
                $('#receivables_body').html('<p class="text-center text-muted py-3">No outstanding invoices.</p>');
                return;
            }
            var html = '<div class="table-responsive"><table class="table table-sm align-middle mb-0">'
                + '<thead class="table-light"><tr><th>Invoice</th><th>Tenant</th><th>Unit</th><th>Due</th><th class="text-end">Balance</th><th>Status</th></tr></thead><tbody>';
            rows.forEach(function (r) {
                var overdue = new Date(r.due_date) < new Date() && r.status !== 'paid';
                var dueTd = overdue
                    ? '<td class="small text-danger fw-semibold">' + r.due_date + '</td>'
                    : '<td class="small">' + r.due_date + '</td>';
                var statusBadge = r.status === 'partial'
                    ? '<span class="badge bg-warning">Partial</span>'
                    : '<span class="badge bg-danger">Unpaid</span>';

                html += '<tr>'
                    + '<td><a href="' + base_url + '/invoice/' + r.id + '" class="small fw-semibold">' + esc(r.reference_number) + '</a></td>'
                    + '<td class="small">' + esc(r.tenant_name || '—') + '</td>'
                    + '<td class="small">' + esc(r.unit_number || '—') + '</td>'
                    + dueTd
                    + '<td class="text-end fw-semibold small text-danger">$' + parseFloat(r.balance).toFixed(2) + '</td>'
                    + '<td>' + statusBadge + '</td>'
                    + '</tr>';
            });
            html += '</tbody></table></div>';
            $('#receivables_body').html(html);
        }

        /* ── Upcoming Lease Expirations ───────────────────── */
        function renderUpcomingLeases(leases) {
            var html = '';
            if (!leases || !leases.length) {
                html = '<tr><td colspan="5" class="text-center text-muted py-3">No expirations in the next 30 days.</td></tr>';
            } else {
                leases.forEach(function (l) {
                    html += '<tr>'
                        + '<td class="small">' + esc(l.tenant_name) + '</td>'
                        + '<td class="small">' + esc(l.property_name) + '</td>'
                        + '<td class="small">' + esc(l.unit_number) + '</td>'
                        + '<td class="small text-warning fw-semibold">' + esc(l.end_date) + '</td>'
                        + '<td><a href="' + base_url + '/leases" class="btn btn-outline-primary btn-xs">Renew</a></td>'
                        + '</tr>';
                });
            }
            $('#upcoming_leases_table').html(html);
        }

        /* ── Recent Payments ──────────────────────────────── */
        function renderRecentPayments(payments) {
            var methodMap = { cash: 'success', mobile: 'info', bank: 'primary' };
            var html = '';
            if (!payments || !payments.length) {
                html = '<tr><td colspan="5" class="text-center text-muted py-3">No recent payments.</td></tr>';
            } else {
                payments.forEach(function (p) {
                    var method = (p.payment_method || '').toLowerCase();
                    html += '<tr>'
                        + '<td class="small">' + esc(p.tenant_name) + '</td>'
                        + '<td class="small">' + esc(p.property_name) + '</td>'
                        + '<td class="small fw-semibold text-success">$' + p.amount_paid + '</td>'
                        + '<td class="small">' + esc(p.received_date) + '</td>'
                        + '<td><span class="badge bg-' + (methodMap[method] || 'secondary') + '">' + esc(p.payment_method) + '</span></td>'
                        + '</tr>';
                });
            }
            $('#recent_payments_table').html(html);
        }

        function esc(s) {
            if (!s) return '';
            return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }
    });
})();
