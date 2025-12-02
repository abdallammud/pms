async function send_reportPost(str, data) {
    let [action, endpoint] = str.split(' ');

    try {
        const response = await $.post(`./app/report_controller.php?action=${action}&endpoint=${endpoint}`, data);
        return response;
    } catch (error) {
        console.error('Error occurred during the request:', error);
        return null;
    }
}

document.addEventListener("DOMContentLoaded", function() {
	$('.my-select').selectpicker({
	    noneResultsText: "No results found"
	});k

	// Search employee
	$(document).on('keyup', '.bootstrap-select.searchEmployee input.form-control', async (e) => {
    	let search = $(e.target).val();
    	let searchFor = 'leave';
    	let formData = {search:search, searchFor:searchFor}
		if(search) {
			try {
		        let response = await send_reportPost('search employee4Select', formData);
		        console.log(response)
		        let res = JSON.parse(response);
		        if(!res.error) {
					$('#searchEmployee').html(res.options)
					$('.my-select').selectpicker('refresh');
				} 
		    } catch (err) {
		        console.error('Error occurred during form submission:', err);
		    }
		}
    })

    // Search department
	$(document).on('keyup', '.bootstrap-select.searchDepartment input.form-control', async (e) => {
    	let search = $(e.target).val();
    	let searchFor = 'leave';
    	let formData = {search:search, searchFor:searchFor}
		if(search) {
			try {
		        let response = await send_reportPost('search department4Select', formData);
		        console.log(response)
		        let res = JSON.parse(response);
		        if(!res.error) {
					$('#searchDepartment').html(res.options)
					$('.my-select').selectpicker('refresh');
				} 
		    } catch (err) {
		        console.error('Error occurred during form submission:', err);
		    }
		}
    })

    // Search location
    $(document).on('keyup', '.bootstrap-select.searchLocation input.form-control', async (e) => {
    	let search = $(e.target).val();
    	let searchFor = 'leave';
    	let formData = {search:search, searchFor:searchFor}
		if(search) {
			try {
		        let response = await send_reportPost('search location4Select', formData);
		        console.log(response)
		        let res = JSON.parse(response);
		        if(!res.error) {
					$('#searchLocation').html(res.options)
					$('.my-select').selectpicker('refresh');
				} 
		    } catch (err) {
		        console.error('Error occurred during form submission:', err);
		    }
		}
    })

    $('#slcReport').on('change', (e) => {
    	let report = $(e.target).val();
    	console.log(report)
    	if(report == 'attendance') {
    		$('.slcMonthFilter').removeClass('hidden');
    	} else {
    		$('.slcMonthFilter').addClass('hidden');
    	}
    })
});

// async function show_report(e) {
// 	e.preventDefault();
// 	let form = $(e.target);
// 	let report = $(form).find('#slcReport').val();
// 	if(!report) {
// 		swal('Sorry', 'Please select valid report', 'error');
// 		return false;
// 	}
// 	let month = $(form).find('#slcMonth').val();

// 	let formData = {report:report}

// 	$('#reportDataTable').html('')
// 	$('#reportDataTable thead').remove()

// 	if(report == 'allEmployees') {
// 		var datatable = $('#reportDataTable').DataTable({
// 			"processing": true,
// 			"serverSide": true,
// 			"bDestroy": true,
// 			// "paging": false,
// 			"serverMethod": 'post',
// 			"ajax": {
// 				"url": `${base_url}/app/report_controller.php?action=report&report=allEmployees`,
// 				"method":"POST",
// 				"data": {
// 		            "report": report
// 	            },
// 				/*dataFilter: function(data) {
// 					console.log(data)
// 				}*/
// 			}, 
			
// 			columns: [
// 				{title: "Staff No.", data: null, render: function(data, type, row) {
// 		            return `<div class="flex center-items">
// 			            	<span>${row.staff_no}</span>
// 			            </div>`;
// 		        }},

// 		        {title: "Full name", data: null, render: function(data, type, row) {
// 		            return `<div>${row.full_name}</div>`;
// 		        }},

// 		        {title: "Phone number", data: null, render: function(data, type, row) {
// 		            return `<div>${row.phone_number}</div>`;
// 		        }},

// 		       	{title: "Email", data: null, render: function(data, type, row) {
// 		            return `<div>
// 		            		${row.email}
// 		            	</div>`;
// 		        }},

// 		        {title: "Department", data: null, render: function(data, type, row) {
// 		            return `<div>${row.branch}</div>`;
// 		        }},

// 		        {title: "Location", data: null, render: function(data, type, row) {
// 		            return `<div>${row.location_name}</div>`;
// 		        }},

// 			]
// 		})

// 		let printHref = `${base_url}/pdf.php?print=employees`;
// 		$('#printTag').attr('href', printHref)
// 	} else if(report == 'attendance') {
// 		var datatable = $('#reportDataTable').DataTable({
// 			"processing": true,
// 			"serverSide": true,
// 			"bDestroy": true,
// 			// "paging": false,
// 			"serverMethod": 'post',
// 			"ajax": {
// 				"url": `${base_url}/app/report_controller.php?action=report&report=attendance`,
// 				"method":"POST",
// 				"data": {
// 		            "report": report,
// 		            "month": month,
// 	            },
// 				/*dataFilter: function(data) {
// 					console.log(data)
// 				}*/
// 			}, 
			
// 			columns: [
// 				{title: "Staff No.", data: null, render: function(data, type, row) {
// 		            return `<div class="flex center-items">
// 			            	<span>${row.staff_no}</span>
// 			            </div>`;
// 		        }},

// 		        {title: "Full name", data: null, render: function(data, type, row) {
// 		            return `<div>${row.full_name}</div>`;
// 		        }},

// 		        {title: "Days worked", data: null, render: function(data, type, row) {
// 		            return `<div>${row.worked_days}/${row.required_days}</div>`;
// 		        }},

// 		        {title: "Paid leave", data: null, render: function(data, type, row) {
// 		            return `<div>${row.paid_leave_count}</div>`;
// 		        }},

// 		       	{title: "Un-paid leave", data: null, render: function(data, type, row) {
// 		            return `<div>
// 		            		${row.unpaid_leave_count}
// 		            	</div>`;
// 		        }},

// 		        {title: "Not hired days", data: null, render: function(data, type, row) {
// 		            return `<div>${row.not_hired_count}</div>`;
// 		        }},

// 		        {title: "Holidays", data: null, render: function(data, type, row) {
// 		            return `<div>${row.holiday_count}</div>`;
// 		        }},

// 			]
// 		})

// 		let printHref = `${base_url}/pdf.php?print=attendance&month=${month}`;
// 		$('#printTag').attr('href', printHref)
// 	}

	
	

// 	return false;
// }

function display_report(filter = false) {
	let reportPage = $('.page.reportShow');
	let report = reportPage.find('#report').val();

	console.log(report);
	
	// Filters
	let gender = reportPage.find('#slcGender').val();
	let state = reportPage.find('#slcState').val();
	let department = reportPage.find('#slcDepartment').val();
	let location = reportPage.find('#slcLocation').val();
	let salary = reportPage.find('#salary_range_start').val() || 0;
	let salary_up = reportPage.find('#salary_range_end').val() || 0;
	let age = reportPage.find('#slcAge').val();

	let date_start = reportPage.find('#date_range_start').val();
	let date_end = reportPage.find('#date_range_end').val();
	let month = reportPage.find('#month').val();

	if(report == 'employees') report = 'allEmployees';
	if(report == 'employees') report = 'allEmployees';

	let filterData = {
		gender,
		state,
		department,
		location,
		salary,
		salary_up,
		age,
		date_start,
		date_end,
		month,
		report
	}

	if(!filter) {
		filterData = {report}
	}

	if(report == 'allEmployees') {
		var datatable = $('#reportDataTable').DataTable({
			"processing": true,
			"serverSide": true,
			"bDestroy": true,
			// "paging": false,
			"serverMethod": 'post',
			"ajax": {
				"url": `${base_url}/app/report_controller.php?action=report&report=allEmployees`,
				"method":"POST",
				"data": filterData,
				// dataFilter: function(data) {
				// 	console.log(data)
				// }
			}, 
			
			columns: [
				{title: "Staff No.", data: null, render: function(data, type, row) {
		            return `<div class="flex center-items">
			            	<span>${row.staff_no}</span>
			            </div>`;
		        }},

		        {title: "Full name", data: null, render: function(data, type, row) {
		            return `<div>${row.full_name}</div>`;
		        }},

		        {title: "Phone number", data: null, render: function(data, type, row) {
		            return `<div>${row.phone_number}</div>`;
		        }},

		       	{title: "Email", data: null, render: function(data, type, row) {
		            return `<div>
		            		${row.email}
		            	</div>`;
		        }},

		        {title: "Department", data: null, render: function(data, type, row) {
		            return `<div>${row.branch}</div>`;
		        }},

		        {title: "Location", data: null, render: function(data, type, row) {
		            return `<div>${row.location_name}</div>`;
		        }},

			]
		})

		let printHref = `${base_url}/pdf.php?print=employees&gender=${gender}&state=${state}&department=${department}&location=${location}&salary=${salary}&salary_up=${salary_up}`;
		if(!filter) {
			printHref = `${base_url}/pdf.php?print=employees`;
		}
		$('#printTag').attr('href', printHref)
	} else if(report == 'absence') {
		var datatable = $('#reportDataTable').DataTable({
			"processing": true,
			"serverSide": true,
			"bDestroy": true,
			// "paging": false,
			"serverMethod": 'post',
			"ajax": {
				"url": `${base_url}/app/report_controller.php?action=report&report=absence`,
				"method":"POST",
				"data": filterData,
				// dataFilter: function(data) {
				// 	console.log(data)
				// }
			}, 
			
			columns: [
				{title: "Staff No.", data: null, render: function(data, type, row) {
		            return `<div class="flex center-items">
			            	<span>${row.staff_no}</span>
			            </div>`;
		        }},

		        {title: "Full name", data: null, render: function(data, type, row) {
		            return `<div>${row.full_name}</div>`;
		        }},

		        {title: "Paid leave", data: null, render: function(data, type, row) {
		            return `<div>${row.paid_leave_count}</div>`;
		        }},

		       	{title: "Un-paid leave", data: null, render: function(data, type, row) {
		            return `<div>
		            		${row.unpaid_leave_count}
		            	</div>`;
		        }},

		        {title: "Not hired days", data: null, render: function(data, type, row) {
		            return `<div>${row.not_hired_count}</div>`;
		        }},

		        {title: "Absent days", data: null, render: function(data, type, row) {
		            return `<div>${row.no_show_count}</div>`;
		        }},


				{title: "Total absence", data: null, render: function(data, type, row) {
		            return `<div>${row.total_absence}</div>`;
		        }},

			]
		})

		let printHref = `${base_url}/pdf.php?print=absence&month=${month}`;
		if(!filter) {
			printHref = `${base_url}/pdf.php?print=absence`;
		}
		$('#printTag').attr('href', printHref)
	} else if(report == 'componsation') {
		var datatable = $('#reportDataTable').DataTable({
			"processing": true,
			"serverSide": true,
			"bDestroy": true,
			// "paging": false,
			"serverMethod": 'post',
			"ajax": {
				"url": `${base_url}/app/report_controller.php?action=report&report=componsation`,
				"method":"POST",
				"data": filterData,
				// dataFilter: function(data) {
				// 	console.log(data)
				// }
			}, 
			
			columns: [
				{title: "Staff No.", data: null, render: function(data, type, row) {
		            return `<div class="flex center-items">
			            	<span>${row.staff_no}</span>
			            </div>`;
		        }},

		        {title: "Full name", data: null, render: function(data, type, row) {
		            return `<div>${row.full_name}</div>`;
		        }},

		        {title: "Base salary", data: null, render: function(data, type, row) {
		            return `<div>${row.base_salary}</div>`;
		        }},

		       	{title: "Allowance", data: null, render: function(data, type, row) {
		            return `<div>${row.allowance}</div>`;
		        }},

		        {title: "Bonuses", data: null, render: function(data, type, row) {
		            return `<div>${row.bonus}</div>`;
		        }},

		        {title: "Deductions", data: null, render: function(data, type, row) {
		            return `<div>${row.deduction}</div>`;
		        }},


				{title: "Net compensation", data: null, render: function(data, type, row) {
		            return `<div>${row.net_salary}</div>`;
		        }},

			]
		})

		let printHref = `${base_url}/pdf.php?print=componsation&month=${month}`;
		if(!filter) {
			printHref = `${base_url}/pdf.php?print=componsation`;
		}
		$('#printTag').attr('href', printHref)
	} else if(report == 'deductions') {
		var datatable = $('#reportDataTable').DataTable({
			"processing": true,
			"serverSide": true,
			"bDestroy": true,
			"serverMethod": 'post',
			"ajax": {
				"url": `${base_url}/app/report_controller.php?action=report&report=deductions`,
				"method":"POST",
				"data": {
					"report": report,
					"month": month,
				},
			},
			columns: [
				{title: "Staff No.", data: null, render: function(data, type, row) {
					return `<div class='flex center-items'><span>${row.staff_no}</span></div>`;
				}},
				{title: "Full name", data: null, render: function(data, type, row) {
					return `<div>${row.full_name}</div>`;
				}},
				{title: "Earnings", data: null, render: function(data, type, row) {
					return `<div>${row.earnings}</div>`;
				}},
				{title: "Deductions", data: null, render: function(data, type, row) {
					return `<div>${row.total_deductions}</div>`;
				}},
				{title: "Net Pay", data: null, render: function(data, type, row) {
					return `<div>${row.net_salary}</div>`;
				}},
			]
		});
		let printHref = `${base_url}/pdf.php?print=deductions&month=${month}`;
		$('#printTag').attr('href', printHref)
	} else if(report == 'payroll') {
		var datatable = $('#reportDataTable').DataTable({
			"processing": true,
			"serverSide": true,
			"bDestroy": true,
			"serverMethod": 'post',
			"ajax": {
				"url": `${base_url}/app/report_controller.php?action=report&report=payroll`,
				"method":"POST",
				"data": filterData,
			},
			columns: [
				{title: "Staff No.", data: null, render: function(data, type, row) {
					return `<div class='flex center-items'><span>${row.staff_no}</span></div>`;
				}},
				{title: "Full name", data: null, render: function(data, type, row) {
					return `<div>${row.full_name}</div>`;
				}},
				{title: "Gross Salary", data: null, render: function(data, type, row) {
					return `<div>${formatMoney(row.base_salary)}</div>`;
				}},
				{title: "Earnings", data: null, render: function(data, type, row) {
					return `<div>${formatMoney(row.earnings)}</div>`;
				}},
				{title: "Deductions", data: null, render: function(data, type, row) {
					return `<div>${formatMoney(row.total_deductions)}</div>`;
				}},
				{title: "Net Pay", data: null, render: function(data, type, row) {
					return `<div>${formatMoney(row.net_salary)}</div>`;
				}},
			]
		});
		let printHref = `${base_url}/pdf.php?report=reports&print=payroll&month=${month}&state=${state}&department=${department}&location=${location}&salary=${salary}&salary_up=${salary_up}`;
		$('#printTag').attr('href', printHref)
	} else if(report == 'taxation') {
		var datatable = $('#reportDataTable').DataTable({
			"processing": true,
			"serverSide": true,
			"bDestroy": true,
			"serverMethod": 'post',
			"ajax": {
				"url": `${base_url}/app/report_controller.php?action=report&report=taxation`,
				"method":"POST",
				"data": filterData,
			},
			columns: [
				{title: "Staff No.", data: null, render: function(data, type, row) {
					return `<div class='flex center-items'><span>${row.staff_no}</span></div>`;
				}},
				{title: "Full name", data: null, render: function(data, type, row) {
					return `<div>${row.full_name}</div>`;
				}},
				{title: "Net Pay", data: null, render: function(data, type, row) {
					return `<div>${formatMoney(row.net_salary)}</div>`;
				}},

				{title: "Tax", data: null, render: function(data, type, row) {
					return `<div>${formatMoney(row.tax)}</div>`;
				}}
			]
		});
		let printHref = `${base_url}/pdf.php?report=reports&print=taxation&month=${month}&state=${state}&department=${department}&location=${location}&salary=${salary}&salary_up=${salary_up}`;
		$('#printTag').attr('href', printHref)
	}
}