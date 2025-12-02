document.addEventListener('DOMContentLoaded', function () {
    // Theme Switcher
    const themeSwitcher = document.getElementById('themeSwitcher');
    const themeIcon = document.getElementById('themeIcon');
    const themeText = document.getElementById('themeText');
    const body = document.body;
    // Initialize Charts
    // Rent Collection Chart
    const rentCollectionCtx = document.getElementById('rentCollectionChart').getContext('2d');
    const rentCollectionChart = new Chart(rentCollectionCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [{
                label: 'Rent Collected',
                data: [42000, 45000, 43500, 46000, 47500, 48500, 42150],
                backgroundColor: 'rgba(15, 59, 108, 0.1)',
                borderColor: 'rgba(15, 59, 108, 1)',
                borderWidth: 3,
                pointBackgroundColor: 'rgba(15, 59, 108, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(15, 59, 108, 1)',
                pointRadius: 5,
                pointHoverRadius: 7,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return '$' + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 59, 108, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function (context) {
                            return 'Rent Collected: $' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Occupancy Chart
    const occupancyCtx = document.getElementById('occupancyChart').getContext('2d');
    const occupancyChart = new Chart(occupancyCtx, {
        type: 'doughnut',
        data: {
            labels: ['Occupied', 'Vacant'],
            datasets: [{
                data: [162, 24],
                backgroundColor: [
                    'rgba(15, 59, 108, 0.8)',
                    'rgba(253, 185, 19, 0.8)'
                ],
                borderColor: [
                    'rgba(15, 59, 108, 1)',
                    'rgba(253, 185, 19, 1)'
                ],
                borderWidth: 2,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 59, 108, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function (context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Income vs Expense Chart
    const incomeExpenseCtx = document.getElementById('incomeExpenseChart').getContext('2d');
    const incomeExpenseChart = new Chart(incomeExpenseCtx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [
                {
                    label: 'Income',
                    data: [42000, 45000, 43500, 46000, 47500, 48500, 42150],
                    backgroundColor: 'rgba(15, 59, 108, 0.8)',
                    borderColor: 'rgba(15, 59, 108, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    barPercentage: 0.7
                },
                {
                    label: 'Expense',
                    data: [18000, 19500, 17500, 20000, 21000, 22000, 18500],
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
                    ticks: {
                        callback: function (value) {
                            return '$' + value.toLocaleString();
                        }
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 12,
                            family: "'Poppins', sans-serif"
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15, 59, 108, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += '$' + context.parsed.y.toLocaleString();
                            return label;
                        }
                    }
                }
            }
        }
    });

    // Function to update chart colors based on theme
    function updateChartColors(theme) {
        const isDark = theme === 'dark';

        // Update Rent Collection Chart
        rentCollectionChart.options.scales.y.grid.color = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';
        rentCollectionChart.options.plugins.tooltip.backgroundColor = isDark ? 'rgba(30, 30, 45, 0.8)' : 'rgba(15, 59, 108, 0.8)';
        rentCollectionChart.update();

        // Update Occupancy Chart
        occupancyChart.options.plugins.tooltip.backgroundColor = isDark ? 'rgba(30, 30, 45, 0.8)' : 'rgba(15, 59, 108, 0.8)';
        occupancyChart.update();

        // Update Income vs Expense Chart
        incomeExpenseChart.options.scales.y.grid.color = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)';
        incomeExpenseChart.options.plugins.tooltip.backgroundColor = isDark ? 'rgba(30, 30, 45, 0.8)' : 'rgba(15, 59, 108, 0.8)';
        incomeExpenseChart.update();
    }


});