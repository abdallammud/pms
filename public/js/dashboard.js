document.addEventListener('DOMContentLoaded', function () {
    // Global chart instances
    let occupancyChart;
    let incomeExpenseChart;

    // Fetch Dashboard Data
    fetchDashboardData();

    function fetchDashboardData() {
        const fetchUrl = base_url + '/app/dashboard_controller.php?action=get_stats';
        console.log('Fetching dashboard data from:', fetchUrl);

        $.ajax({
            url: fetchUrl,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                updateStats(response.stats);
                updateRecentLeases(response.recent.leases);
                updateRecentPayments(response.recent.payments);
                updateRecentMaintenance(response.recent.maintenance);
                initCharts(response.charts);
            },
            error: function (xhr, status, error) {
                console.error('Failed to fetch dashboard data');
                console.error('Status:', status);
                console.error('Error:', error);
                console.error('Response:', xhr.responseText);
            }
        });
    }

    function updateStats(stats) {
        $('#stat_total_properties').text(stats.total_properties);
        $('#stat_occupied_units').text(stats.occupied_units);
        $('#stat_vacant_units').text(stats.vacant_units);
        $('#stat_active_tenants').text(stats.active_tenants);
        $('#stat_rent_collected').text(stats.rent_collected);
        $('#stat_outstanding_amount').text(stats.outstanding_amount);
    }

    function updateRecentLeases(leases) {
        let html = '';
        if (leases.length === 0) {
            html = '<tr><td colspan="5" class="text-center">No upcoming expirations</td></tr>';
        } else {
            leases.forEach(lease => {
                html += `
                    <tr>
                        <td>${lease.tenant_name}</td>
                        <td>${lease.property_name}</td>
                        <td>${lease.unit_number}</td>
                        <td><span class="badge bg-light-warning text-warning">${lease.end_date}</span></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="window.location.href='index.php?menu=tenants&tab=edit_lease&id=${lease.id}'">
                                <i class="bi bi-arrow-repeat me-1"></i> Renew
                            </button>
                        </td>
                    </tr>`;
            });
        }
        $('#upcoming_leases_table').html(html);
    }

    function updateRecentPayments(payments) {
        let html = '';
        if (payments.length === 0) {
            html = '<tr><td colspan="5" class="text-center">No recent payments</td></tr>';
        } else {
            payments.forEach(payment => {
                html += `
                    <tr>
                        <td>${payment.tenant_name}</td>
                        <td>${payment.property_name}</td>
                        <td>$${payment.amount_paid}</td>
                        <td>${payment.received_date}</td>
                        <td><span class="badge bg-success">${payment.payment_method}</span></td>
                    </tr>`;
            });
        }
        $('#recent_payments_table').html(html);
    }

    function updateRecentMaintenance(maintenance) {
        let html = '';
        if (maintenance.length === 0) {
            html = '<tr><td colspan="5" class="text-center">No recent requests</td></tr>';
        } else {
            maintenance.forEach(req => {
                html += `
                    <tr>
                        <td>${req.property_name}</td>
                        <td>${req.unit_number}</td>
                        <td>${req.description}</td>
                        <td><span class="badge bg-${req.status_class}">${req.status}</span></td>
                        <td>
                            <button class="btn btn-sm btn-info btn-icon" onclick="window.location.href='index.php?menu=maintenance&tab=requests'">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>`;
            });
        }
        $('#recent_maintenance_table').html(html);
    }

    function initCharts(chartData) {
        // 1. Occupancy Chart
        const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
        if (occupancyChart) occupancyChart.destroy();
        occupancyChart = new Chart(occupancyCtx, {
            type: 'doughnut',
            data: {
                labels: ['Occupied', 'Vacant'],
                datasets: [{
                    data: chartData.occupancy,
                    backgroundColor: ['rgba(15, 59, 108, 0.8)', 'rgba(253, 185, 19, 0.8)'],
                    borderColor: ['rgba(15, 59, 108, 1)', 'rgba(253, 185, 19, 1)'],
                    borderWidth: 2,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 59, 108, 0.8)',
                        callbacks: {
                            label: function (context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                if (total === 0) return context.label + ': 0 (0%)';
                                const percentage = Math.round((context.parsed / total) * 100);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // 2. Income vs Expense Chart
        const incomeExpenseCtx = document.getElementById('incomeExpenseChart').getContext('2d');
        if (incomeExpenseChart) incomeExpenseChart.destroy();
        incomeExpenseChart = new Chart(incomeExpenseCtx, {
            type: 'bar',
            data: {
                labels: chartData.income_expense.labels,
                datasets: [
                    {
                        label: 'Income',
                        data: chartData.income_expense.income,
                        backgroundColor: 'rgba(15, 59, 108, 0.8)',
                        borderColor: 'rgba(15, 59, 108, 1)',
                        borderWidth: 2,
                        borderRadius: 8,
                        barPercentage: 0.7
                    },
                    {
                        label: 'Expense',
                        data: chartData.income_expense.expense,
                        backgroundColor: 'rgba(253, 185, 19, 0.8)',
                        borderColor: 'rgba(253, 185, 19, 1)',
                        borderWidth: 2,
                        borderRadius: 8,
                        barPercentage: 0.7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: value => '$' + value.toLocaleString() },
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
