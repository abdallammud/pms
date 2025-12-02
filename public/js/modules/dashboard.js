

addEventListener("DOMContentLoaded", (event) => {
    get_dashboadCards() ;
});

function get_dashboadCards() {
	$.post(`${base_url}/app/dashboard_data.php?action=get&endpoint=cards`, {}, function (data) {
		console.log(data)
		let res = JSON.parse(data);
        console.log(res)

        let employees = res.employees ?? 0;
        let new_employees = res.new_employees ?? 0;
        let approved_leave = res.approved_leave ?? 0;
        let expenses     = res.expenses ?? 0;
        let this_month_salary = res.thisMonthSalary ?? 0;
        let company_balance = res.company_balance ?? 0;

		    $('.total_employees').html(addPrefixToNumber(employees, 3))
		    $('.total_new_employees').html(addPrefixToNumber(new_employees, 3))
        $('.on_leave').html(addPrefixToNumber(approved_leave, 3))
        $('.operational_funds').html(formatMoney(company_balance))
        $('.this_month_salary').html(formatMoney(this_month_salary))
		    $('.expenses').html(formatMoney(expenses))

        let genderData = res.gender ?? [];
        const formattedData = transformGenderData(genderData);
        console.log(formattedData)
       genderChart(formattedData);

       departmentChart(res.employeeByDepartment);

       last5MonthsPayrollChart(res.last5Months);
		
	});

    function transformGenderData(inputArray) {
        // Find the male count and ensure it is treated as a number
        const maleItem = inputArray.find(item => item.gender === 'Male');
        const maleCount = maleItem ? parseInt(maleItem.count, 10) : 0;
        
        // Find the female count and ensure it is treated as a number
        const femaleItem = inputArray.find(item => item.gender === 'Female');
        const femaleCount = femaleItem ? parseInt(femaleItem.count, 10) : 0;
        
        // Return the new array in the specified order [Male, Female]
        return [maleCount, femaleCount];
    }



    
    

   
}


function genderChart(genderData) {
    const ctx = document.getElementById('employeeGenderChart');

  const data = {
    labels: ['Male', 'Female'],
    datasets: [{
      data: genderData, // 👈 Replace with your actual gender data
      backgroundColor: [
        'rgba(54, 162, 235, 0.9)',  // Blue for male
        'rgba(255, 99, 132, 0.9)'   // Pink for female
      ],
      borderColor: [
        'rgba(54, 162, 235, 1)',
        'rgba(255, 99, 132, 1)'
      ],
      borderWidth: 2,
      hoverOffset: 12,
      hoverBorderWidth: 3,
      hoverBorderColor: '#fff',
    }]
  };

  const config = {
    type: 'pie',
    data: data,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            color: '#444',
            font: { size: 14, weight: '500' },
            padding: 16,
          }
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleFont: { size: 13 },
          bodyFont: { size: 13 },
          padding: 10,
          cornerRadius: 6,
          displayColors: false,
          callbacks: {
            label: function(context) {
              const label = context.label || '';
              const value = context.raw || 0;
              const total = context.chart._metasets[0].total;
              const percentage = ((value / total) * 100).toFixed(1);
              return `${label}: ${value} (${percentage}%)`;
            }
          }
        }
      },
      animation: {
        animateScale: true,
        animateRotate: true,
        duration: 1800,
        easing: 'easeOutBounce'
      },
    },
  };

  new Chart(ctx, config);
}

function departmentChart(departmentData) {
  const ctx = document.getElementById('employeeDepartmentChart');

  // Extract department names and employee counts
  const labels = departmentData.map(item => item.department);
  const dataValues = departmentData.map(item => parseInt(item.employee_count));

  const data = {
    labels: labels,
    datasets: [{
      data: dataValues,
      backgroundColor: [
        '#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f',
        '#edc949', '#af7aa1', '#ff9da7', '#9c755f', '#bab0ab'
      ], // colors auto-rotated
      borderColor: '#fff',
      borderWidth: 2,
      hoverOffset: 12,
      hoverBorderWidth: 3,
      hoverBorderColor: '#fff',
      cutout: '60%' // 👈 creates the "empty center" donut effect
    }]
  };

  const config = {
    type: 'doughnut', // 👈 change from pie to doughnut
    data: data,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            color: '#444',
            font: { size: 14, weight: '500' },
            padding: 16,
          }
        },
        tooltip: {
          backgroundColor: 'rgba(0, 0, 0, 0.8)',
          titleFont: { size: 13 },
          bodyFont: { size: 13 },
          padding: 10,
          cornerRadius: 6,
          displayColors: false,
          callbacks: {
            label: function(context) {
              const label = context.label || '';
              const value = context.raw || 0;
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = ((value / total) * 100).toFixed(1);
              return `${label}: ${value} (${percentage}%)`;
            }
          }
        }
      },
      animation: {
        animateScale: true,
        animateRotate: true,
        duration: 1800,
        easing: 'easeOutBounce'
      }
    }
  };

  new Chart(ctx, config);
}

function last5MonthsPayrollChart(payrollData) {
  const ctx = document.getElementById('last5MonthsPayrollChart');

  const labels = payrollData.map(item => item.month);
  const dataValues = payrollData.map(item => item.total_salary);

  const data = {
    labels: labels,
    datasets: [{
      label: 'Total Payroll (Last 5 Months)',
      data: dataValues,
      backgroundColor: 'rgba(54, 162, 235, 0.7)',
      borderColor: 'rgba(54, 162, 235, 1)',
      borderWidth: 2,
      borderRadius: 8,
      barPercentage: 0.6,
      hoverBackgroundColor: 'rgba(54, 162, 235, 0.9)'
    }]
  };

  const config = {
    type: 'bar',
    data: data,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function(context) {
              const value = context.raw.toLocaleString();
              return `Total Salary: ${value} USD`;
            }
          }
        },
        title: {
          display: false,
          text: '',
          color: '#333',
          font: { size: 16, weight: 'bold' }
        }
      },
      scales: {
        x: {
          ticks: { color: '#555', font: { size: 13 } },
          grid: { display: false }
        },
        y: {
          beginAtZero: true,
          ticks: { color: '#555', font: { size: 13 }, callback: v => v.toLocaleString() },
          grid: { color: '#eee' }
        }
      },
      animation: {
        duration: 1500,
        easing: 'easeOutElastic'
      }
    }
  };

  new Chart(ctx, config);
}
