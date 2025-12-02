async function send_payrollPost(str, data) {
	let [action, endpoint] = str.split(' ');

	try {
		const response = await $.post(`${base_url}/app/payroll_controller.php?action=${action}&endpoint=${endpoint}`, data);
		return response;
	} catch (error) {
		console.error('Error occurred during the request:', error);
		return null;
	}
}

//Transactions
function load_transactions() {
	function check_actions(row) {
		let actions = '';
		if (row.payroll_id == 0) {
			actions += `<span data-recid="${row.transaction_id}" class="fa delete_transaction smt-5 cursor fa-trash"></span>`
		}

		return actions;
	}
	var datatable = $('#transactionsDT').DataTable({
		// let datatable = new DataTable('#companyDT', {
		"processing": true,
		"serverSide": true,
		"bDestroy": true,
		// "searching": false,  
		"info": false,
		"columnDefs": [
			{ "orderable": false, "searchable": false, "targets": [7] }  // Disable search on first and last columns
		],
		"serverMethod": 'post',
		"ajax": {
			"url": "./app/payroll_controller.php?action=load&endpoint=transactions",
			"method": "POST",
			// dataFilter: function(data) {
			// 	console.log(data)
			// }
		},

		"createdRow": function (row, data, dataIndex) {
			// Add your custom class to the row 
			// $(row).addClass('table-row ' +data.status.toLowerCase());
		},
		columns: [

			{
				title: `Staff No.`, data: null, render: function (data, type, row) {
					return `<div>
	            		<span>${row.staff_no}</span>
	                </div>`;
				}
			},

			{
				title: `Full name`, data: null, render: function (data, type, row) {
					return `<div>
	            		<span>${row.full_name}</span>
	                </div>`;
				}
			},

			{
				title: `Transactions type`, data: null, render: function (data, type, row) {
					return `<div>
	            		<span>${row.transaction_type}</span>
	                </div>`;
				}
			},

			{
				title: `Transactions subtype`, data: null, render: function (data, type, row) {
					return `<div>
	            		<span>${row.transaction_subtype}</span>
	                </div>`;
				}
			},

			{
				title: `Amount`, data: null, render: function (data, type, row) {
					return `<div>
	            		<span>${formatMoney(row.amount)}</span>
	                </div>`;
				}
			},

			{
				title: `Status`, data: null, render: function (data, type, row) {
					return `<div>
	            		<span>${row.status}</span>
	                </div>`;
				}
			},


			{
				title: `Date `, data: null, render: function (data, type, row) {
					return `<div>
	            		<span>${formatDate(row.date)}</span>
	                </div>`;
				}
			},

			{
				title: "Action", data: null, render: function (data, type, row) {
					return `<div class="sflex scenter-items">
            		${user_permissions['edit_payroll_transactions'] === 'on'
							? `<span data-recid="${row.transaction_id}" 
							class="fa edit_transactionInfo smt-5 cursor smr-10 fa-pencil"></span>`
							: ''
						}

					${user_permissions['delete_payroll_transactions'] === 'on'
							? check_actions(row)
							: ''
						}
                </div>`;
				}
			},
		]
	});

	return false;
}

function handleTransactions() {
	$('#addTransactionForm').on('submit', (e) => {
		handle_addTransactionForm(e.target);
		return false
	})

	load_transactions();

	// Attendance for change
	$(document).on('change', '.slcTransFor', (e) => {
		let transFor = $(e.target).val();
		if (transFor == 'All') {
			$('.attenForDiv').html('')
		} else {
			$.post(`./app/payroll_controller.php?action=search&endpoint=trans_for`, { transFor: transFor }, function (data) {
				// console.log(data)
				let res = JSON.parse(data);
				if (!res.error) {
					$('.attenForDiv').html(res.data)

					$('.my-select').selectpicker({
						noneResultsText: "No results found"
					});


					$('.my-select').selectpicker('refresh');
				} else {
					swal('Ooops', res.msg, 'error');
					return false;
				}
			})
		}
	})

	// Edit location
	$(document).on('click', '.edit_payrollInfo', async (e) => {
		let id = $(e.currentTarget).data('recid');
		let modal = $('#edit_transaction');

		let data = await get_payroll(id);
		console.log(id)
		if (data) {
			let res = JSON.parse(data);
			console.log(res)
			/*$(modal).find('#transaction_id').val(id);
			$(modal).find('#employee_name').val(`${res.full_name}, ${res.phone_number}`);
			$(modal).find('#transType4Edit').val(res.transaction_type);
			$(modal).find('#transSubType4Edit').val(res.transaction_subtype);
			$(modal).find('#transAmount4Edit').val(res.amount);
			$(modal).find('#transStatus4Edit').val(res.status);
			$(modal).find('#transDate4Edit').val(res.date);
			$(modal).find('#txtComments4Edit').val(res.description);*/

		}

		$(modal).modal('show');
	});


	// Edit location info form
	$('#editTransactionForm').on('submit', (e) => {
		handle_editTransactionForm(e.target);
		return false
	})

	// Delete location
	$(document).on('click', '.delete_transaction', async (e) => {
		let id = $(e.currentTarget).data('recid');
		swal({
			title: "Are you sure?",
			text: `You are going to delete this transaction  record.`,
			icon: "warning",
			className: 'warning-swal',
			buttons: ["Cancel", "Yes, delete"],
		}).then(async (confirm) => {
			if (confirm) {
				let data = { id: id };
				try {
					let response = await send_payrollPost('delete transaction', data);
					console.log(response)
					if (response) {
						let res = JSON.parse(response);
						if (res.error) {
							toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 3000 }).then(() => {
								location.reload();
							});;
						} else {
							toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
								location.reload();
								// load_branches();
							});
							console.log(res);
						}
					} else {
						console.log('Failed to edit state.' + response);
					}

				} catch (err) {
					console.error('Error occurred during form submission:', err);
				}
			}
		});
	});

	// Download sample file
	$('#downloadTransactionUploadForm').on('submit', (e) => {
		handle_downloadTransactionUploadForm(e.target);
		return false
	})

	// Upload attendance
	$('#transaction_uploadForm').on('submit', (e) => {
		handle_transaction_uploadForm(e.target);
		return false
	})

	$(document).on('change', '#transType', async (e) => {
		let type = $(e.target).val();
		if (type) {
			let data = { type };
			let response = await send_payrollPost('get transSubTypes', data);
			$('#transSubType').html(response)
		}
	})

	$(document).on('change', '#transType4Edit', async (e) => {
		let type = $(e.target).val();
		if (type) {
			let data = { type };
			let response = await send_payrollPost('get transSubTypes', data);
			$('#transSubType4Edit').html(response)
		}
	})
}

async function handle_addTransactionForm(form) {
	clearErrors();
	let error = validateForm(form)
	let ref_id, ref_name, err_mgs = '';

	let emp_id = $(form).find('#searchEmployee').val();
	let full_name = $(form).find('#searchEmployee option:selected').text();
	full_name = full_name.split(',')[0]
	err_mgs = 'employee';


	let transType = $(form).find('#transType').val();
	let transSubType = $(form).find('#transSubType').val();
	let transAmount = $(form).find('#transAmount').val();
	let transStatus = $(form).find('#transStatus').val();
	let transDate = $(form).find('#transDate').val();
	let txtComments = $(form).find('#txtComments').val();

	if (!emp_id) {
		swal('Sorry', `Please select ${err_mgs}`, 'error')
		return false;
	}

	if (error) return false;

	let formData = {
		emp_id: emp_id,
		transaction_type: transType,
		transaction_subtype: transSubType,
		amount: transAmount,
		date: transDate,
		status: transStatus,
		description: txtComments,
	};

	form_loading(form);

	try {
		let response = await send_payrollPost('save transaction', formData);
		console.log(response)
		if (response) {
			let res = JSON.parse(response)
			if (res.error) {
				toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 1000 }).then(() => {
					location.reload();
				});
			} else {
				toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
					location.reload();
					// load_budgetCodes();
				});
				console.log(res)
			}
		} else {
			console.log('Failed to save state.' + response);
		}

	} catch (err) {
		console.error('Error occurred during form submission:', err);
	}
}

async function handle_editTransactionForm(form) {
	clearErrors();
	let error = validateForm(form)
	let transaction_id = $(form).find('#transaction_id').val();
	let transType = $(form).find('#transType4Edit').val();
	let transSubType = $(form).find('#transSubType4Edit').val();
	let transAmount = $(form).find('#transAmount4Edit').val();
	let transStatus = $(form).find('#transStatus4Edit').val();
	let transDate = $(form).find('#transDate4Edit').val();
	let txtComments = $(form).find('#txtComments4Edit').val();

	if (error) return false;

	let formData = {
		transaction_id: transaction_id,
		transaction_type: transType,
		transaction_subtype: transSubType,
		amount: transAmount,
		date: transDate,
		status: transStatus,
		description: txtComments,
	};

	form_loading(form);

	try {
		let response = await send_payrollPost('update transaction', formData);
		console.log(response)
		if (response) {
			let res = JSON.parse(response)
			if (res.error) {
				toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 1000 }).then(() => {
					location.reload();
				});
			} else {
				toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
					location.reload();
					// load_budgetCodes();
				});
				console.log(res)
			}
		} else {
			console.log('Failed to save state.' + response);
		}

	} catch (err) {
		console.error('Error occurred during form submission:', err);
	}
}

async function get_transaction(id) {
	let data = { id };
	let response = await send_payrollPost('get transaction', data);
	return response;
}

async function handle_downloadTransactionUploadForm(form) {
	clearErrors();
	let error = validateForm(form)
	let ref_id, ref_name, err_mgs = '';
	let transFor = $(form).find('#slcTransFor').val();
	console.log(transFor)

	if (transFor == 'Department') {
		ref_id = $(form).find('#searchDepartment').val();
		ref_name = $(form).find('#searchDepartment option:selected').text();
		err_mgs = 'department';
	} else if (transFor == 'Location') {
		ref_id = $(form).find('#searchLocation').val();
		ref_name = $(form).find('#searchLocation option:selected').text();
		err_mgs = 'location';
	}

	let transDate = $(form).find('#transDate4Download').val();

	/*if(!ref_id) {
		swal('Sorry', `Please select ${err_mgs}`, 'error')
		return false;
	}*/

	if (error) return false;

	let formData = {
		ref: transFor,
		ref_id: ref_id,
		ref_name: ref_name,
		date: transDate,
	};

	// form_loading(form);

	try {
		let response = await send_payrollPost('get downloadTransactionsCSV', formData);
		console.log(response)
		// return false;
		if (response) {
			let res = JSON.parse(response)
			if (res.error) {
				toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 3000 }).then(() => {
					location.reload();
				});
			} else {
				downloadCSV(res.data, filename = `Transactions upload file on ${formatDate(transDate)}.csv`);
				toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
					let modal = $('#download_transactionUploadFile');
					$(modal).modal('hide');
				});
			}
		} else {
			console.log('Failed to save state.' + response);
		}

	} catch (err) {
		console.error('Error occurred during form submission:', err);
	}

	return false;
}

async function handle_transaction_uploadForm(form) {
	let fileInput = $(form).find('#transaction_uploadInput');
	let file = fileInput[0].files[0];
	let allowedExtensions = ['csv'];

	// Validate file type
	if (!file) {
		alert('Please select a file.');
		return;
	}

	let ext = file.name.split('.').pop().toLowerCase();
	if (!allowedExtensions.includes(ext)) {
		alert('Invalid file type. Please upload a csv  file.');
		return;
	}

	let formData = new FormData();
	formData.append('file', file);

	form_loading(form);

	var ajax = new XMLHttpRequest();
	ajax.addEventListener("load", function (event) {
		console.log(event.target.response)
		let res = JSON.parse(event.target.response)
		if (res.error) {
			toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
			return false;
		} else if (res.errors) {
			swal('Sorry', `${res.errors} \n`, 'error');
			return false;
		} else {
			toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 2000 }).then(() => {
				location.reload();
			});
		}
	});

	ajax.open("POST", `${base_url}/app/payroll_controller.php?action=save&endpoint=upload_transaction`);
	ajax.send(formData);
}

// Payroll
function load_payroll() {
	var datatable = $('#payrollDT').DataTable({
		// let datatable = new DataTable('#companyDT', {
		"processing": true,
		"serverSide": true,
		"bDestroy": true,
		// "searching": false,  
		"info": false,
		"columnDefs": [
			{ "orderable": false, "searchable": false, "targets": [2, 4] }  // Disable search on first and last columns
		],
		"serverMethod": 'post',
		"ajax": {
			"url": "./app/payroll_controller.php?action=load&endpoint=payroll",
			"method": "POST",
			/*dataFilter: function(data) {
				console.log(data)
			}*/
		},

		"createdRow": function (row, data, dataIndex) {
			// Add your custom class to the row 
			// $(row).addClass('table-row ' +data.status.toLowerCase());
		},
		columns: [

			{
				title: `Payroll`, data: null, render: function (data, type, row) {
					return `<div>
	            		<span>${row.ref} - ${row.ref_name}</span>
	                </div>`;
				}
			},

			{
				title: `Month `, data: null, render: function (data, type, row) {
					return `<div>
	            		<span>${row.month}</span>
	                </div>`;
				}
			},

			{
				title: `Status `, data: null, render: function (data, type, row) {
					return `<div>
	            		<span>${row.status} </span>
	                </div>`;
				}
			},


			{
				title: `Employees `, data: null, render: function (data, type, row) {
					return `<div>
	            		<span>${row.employee_count} employees</span>
	                </div>`;
				}
			},

			{
				title: `Date added`, data: null, render: function (data, type, row) {
					return `<div>
	            		<span>${formatDate(row.added_date)}</span>
	                </div>`;
				}
			},

			{
				title: "Action", data: null, render: function (data, type, row) {
					return `<div class="sflex scenter-items">
            		<a href="${base_url}/payroll/${row.id}" data-recid="${row.id}" class="fa edit_payrollInfo smt-5 cursor smr-10 fa-eye"></a>
            		${user_permissions['delete_payroll'] === 'on'
							? `<span data-recid="${row.id}" 
                         class="fa delete_payroll smt-5 cursor fa-trash">
                   		</span>`
							: ''
						}
                </div>`;
				}
			},
		]
	});

	return false;
}

// Payroll
// function load_showPayroll(payroll_id, month) {
// 	function set_actions(row) {
// 		let actions = `<a title="View payslip" data-recid="${row.id}" data-emp_id="${row.emp_id}" class="fa show_payslip smt-5 cursor smr-10 fa-eye"></a>`;
// 		if (row.status == 'Pending') {
// 			actions += `<a title="Approve payroll" data-recid="${row.payroll_id}" data-emp_id="${row.emp_id}" class="fa approve_payrollBtn smt-5 cursor smr-10 fa-check"></a>`;
// 		} else if (row.status == 'Approved') {
// 			actions += `<a title="Pay payroll" data-recid="${row.payroll_id}" data-detail_id="${row.id}" class="fa pay_payrollBtn smt-5 cursor smr-10 fa-dollar-sign"></a>`;
// 		}
// 		actions += `<a data-recid="${row.id}" data-emp_id="${row.emp_id}" class="fa delete_payrollDetail smt-5 cursor smr-10 fa-trash"></a>`;
// 		return actions;
// 	}

// 	let department = $('#slcDepartment').val();
// 	let location = $('#slcDutyLocation').val();
// 	let state = $('#slcState').val();
// 	let project = $('#slcProject').val();
// 	let budget_code = $('#slcBudgetCode').val();

// 	var datatable = $('#showpayrollDT').DataTable({
// 		processing: true,
// 		serverSide: true,
// 		bDestroy: true,
// 		info: false,
// 		columnDefs: [
// 			{ orderable: false, searchable: false, targets: [0, 19] }  // checkbox and action not orderable
// 		],
// 		serverMethod: 'post',
// 		ajax: {
// 			url: `${base_url}/app/payroll_controller.php?action=load&endpoint=payroll_details`,
// 			method: "POST",
// 			data: function (d) {
// 				// d contains start, length, search, order, etc.
// 				d.payroll_id = payroll_id;
// 				d.month = month;
// 				// optional: send additional filters here
// 			},
// 			dataFilter: function (data) {
// 				try {
// 					JSON.parse(data);
// 				} catch (e) {
// 					console.error("Invalid JSON from server:", data);
// 				}
// 				return data;
// 			}
// 		},
// 		createdRow: function (row, data, dataIndex) {
// 			// Add your custom class to the row 
// 			// $(row).addClass('table-row ' +data.status.toLowerCase());
// 			// $('#showpayrollDT_wrapper').find('td').css('display', 'none')
// 			//
// 			tableColumns.map((column) => {
// 				// $('#showpayrollDT_wrapper').find('td.'+column).css('display', 'flex')
// 				// $('#showpayrollDT_wrapper').find('th.'+column).css('display', 'flex')
// 			})
// 		},
// 		drawCallback: function (settings) {
// 			$('#showpayrollDT_wrapper').find('td').css('display', 'none');
// 			$('#showpayrollDT_wrapper').find('th').css('display', 'none');
// 			tableColumns.map((column) => {
// 				$('#showpayrollDT_wrapper').find('td.' + column).css('display', 'table-cell');
// 				$('#showpayrollDT_wrapper').find('th.' + column).css('display', 'table-cell');
// 			});
// 			// Always show the checkbox column
// 			$('#showpayrollDT_wrapper').find('td.bulk-checkbox').css('display', 'table-cell');
// 			$('#showpayrollDT_wrapper').find('th:first-child').css('display', 'table-cell');
// 		},
// 		columns: [

// 			{
// 				title: `<input type="checkbox" class="select-all-checkbox">`, data: null, className: "bulk-checkbox", render: function (data, type, row) {
// 					return `<div> 
// 	            		<input type="checkbox" class="row-checkbox" data-id="${row.id}" data-emp_id="${row.emp_id}" data-payroll_id="${row.payroll_id}" >
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: `Staff No. `, data: null, className: "staff_no", render: function (data, type, row) {
// 					return `<div> 
// 	            		<span>${row.staff_no} </span>
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: `Full name `, data: null, className: "full_name", render: function (data, type, row) {
// 					return `<div>
// 	            		<span>${row.full_name} </span>
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: `Email `, data: null, className: "email", render: function (data, type, row) {
// 					return `<div> 
// 	            		<span>${row.email} </span>
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: `Contract type `, data: null, className: "contract_type", render: function (data, type, row) {
// 					return `<div> 
// 	            		<span>${row.contract_type} </span>
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: `Job title `, data: null, className: "job_title", render: function (data, type, row) {
// 					return `<div> 
// 	            		<span>${row.job_title} </span>
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: `Month `, data: null, className: "month", render: function (data, type, row) {
// 					return `<div> 
// 	            		<span>${row.month} </span>
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: `Required days `, data: null, className: "required_days", render: function (data, type, row) {
// 					return `<div> 
// 	            		<span>${row.required_days} </span>
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: `Days worked `, data: null, className: "days_worked", render: function (data, type, row) {
// 					return `<div> 
// 	            		<span>${row.days_worked} </span>
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: `Unpaid days `, data: null, className: "unpaid_days", render: function (data, type, row) {
// 					return `<div> 
// 	            		<span>${row.unpaid_days} </span>
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: `Unpaid hours `, data: null, className: "unpaid_hours", render: function (data, type, row) {
// 					return `<div> 
// 	            		<span>${row.unpaid_hours} </span>
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: `Gross salary `, data: null, className: "gross_salary", render: function (data, type, row) {
// 					return `<div>
// 	            		<span>${formatMoney(row.base_salary)} </span>
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: `Earnings `, data: null, className: "earnings", render: function (data, type, row) {
// 					return `<div>
// 	            		<span>${formatMoney(row.earnings)} </span>
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: `Deductions `, data: null, className: "total_deductions", render: function (data, type, row) {
// 					return `<div>
// 	            		<span>${formatMoney(row.total_deductions)} </span>
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: `Tax `, data: null, className: "tax", render: function (data, type, row) {
// 					return `<div>
// 	            		<span>${formatMoney(row.tax)} -  (${row.taxRate}%)</span>
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: `Net salary `, data: null, className: "net_salary", render: function (data, type, row) {
// 					return `<div>
// 	            		<span>${formatMoney(row.net_salary)} </span>
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: `Status `, data: null, className: "status", render: function (data, type, row) {
// 					return `<div>
// 	            		<span>${row.txtStatus} </span>
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: `Bank name `, data: null, className: "bank_name", render: function (data, type, row) {
// 					return `<div> 
// 	            		<span>${row.bank_name} </span>
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: `Account number `, data: null, className: "bank_number", render: function (data, type, row) {
// 					return `<div> 
// 	            		<span>${row.bank_number} </span>
// 	                </div>`;
// 				}
// 			},

// 			{
// 				title: "Action", data: null, className: "action", render: function (data, type, row) {
// 					return `<div class="sflex scenter-items">
//             		${set_actions(row)}
//                 </div>`;
// 				}
// 			},
// 		]
// 	});

// 	// bulk checkbox handlers (retain)
// 	$(document).on('change', '.select-all-checkbox', function () {
// 		const isChecked = $(this).prop('checked');
// 		$('.row-checkbox').prop('checked', isChecked);
// 	});

// 	return false;
// }
// function load_showPayroll(payroll_id, month) {
// 	function set_actions(row) {
// 		let actions = `<a title="View payslip" data-recid="${row.id}" data-emp_id="${row.emp_id}" class="fa show_payslip smt-5 cursor smr-10 fa-eye"></a>`;
// 		if (row.status == 'Pending') {
// 			actions += `<a title="Approve payroll" data-recid="${row.payroll_id}" data-emp_id="${row.emp_id}" class="fa approve_payrollBtn smt-5 cursor smr-10 fa-check"></a>`;
// 		} else if (row.status == 'Approved') {
// 			actions += `<a title="Pay payroll" data-recid="${row.payroll_id}" data-detail_id="${row.id}" class="fa pay_payrollBtn smt-5 cursor smr-10 fa-dollar-sign"></a>`;
// 		}
// 		actions += `<a data-recid="${row.id}" data-emp_id="${row.emp_id}" class="fa delete_payrollDetail smt-5 cursor smr-10 fa-trash"></a>`;
// 		return actions;
// 	}

// 	const table = $('#showpayrollDT').DataTable({
// 		processing: true,
// 		serverSide: true,
// 		bDestroy: true,
// 		info: false,
// 		order: [],
// 		columnDefs: [
// 			{ orderable: false, searchable: false, targets: [0, 19] }
// 		],
// 		serverMethod: 'post',
// 		ajax: {
// 			url: `${base_url}/app/payroll_controller.php?action=load&endpoint=payroll_details`,
// 			method: "POST",
// 			data: function (d) {
// 				// Core identifiers
// 				d.payroll_id = payroll_id;
// 				d.month = month;

// 				// Attach current filter values (use 0 if blank)
// 				d.branch_id = $('#slcDepartment').val() || 0;
// 				d.location_id = $('#slcDutyLocation').val() || 0;
// 				d.state_id = $('#slcState').val() || 0;
// 				d.project_id = $('#slcProject').val() || 0;
// 				d.budget_code_id = $('#slcBudgetCode').val() || 0;
// 			},
// 			dataFilter: function (data) {
// 				try {
// 					const json = JSON.parse(data);

// 					if (json.filters) {
// 						// Hide options not in filters arrays
// 						updateFilterVisibility('#slcDepartment', json.filters.branch_ids);
// 						updateFilterVisibility('#slcDutyLocation', json.filters.location_ids);
// 						updateFilterVisibility('#slcState', json.filters.state_ids);
// 						updateFilterVisibility('#slcProject', json.filters.project_ids);
// 						updateFilterVisibility('#slcBudgetCode', json.filters.budget_code_ids);
// 					}

// 					return JSON.stringify(json);
// 				} catch (e) {
// 					console.error("Invalid JSON from server:", data);
// 					return data;
// 				}
// 			}
// 		},
// 		createdRow: function (row, data) {
// 			// Add class to row if out of contract
// 			if (data.out_of_contract == 1) {
// 				$(row).addClass('out-of-contract');
// 				// Set bg for this row
// 				$(row).css('background-color', '#f0ad4e');
// 			}
// 		},
// 		drawCallback: function () {
// 			$('#showpayrollDT_wrapper').find('td, th').hide();
// 			tableColumns.forEach(column => {
// 				$('#showpayrollDT_wrapper').find('td.' + column + ', th.' + column).css('display', 'table-cell');
// 			});
// 			$('#showpayrollDT_wrapper').find('td.bulk-checkbox, th:first-child').css('display', 'table-cell');
// 		},
// 		columns: [
// 			{
// 				title: `<input type="checkbox" class="select-all-checkbox">`, data: null, className: "bulk-checkbox",
// 				render: row => `<div><input type="checkbox" class="row-checkbox" data-id="${row.id}" data-emp_id="${row.emp_id}" data-payroll_id="${row.payroll_id}"></div>`
// 			},
// 			{ title: "Staff No.", data: "staff_no", className: "staff_no" },
// 			{ title: "Full name", data: "full_name", className: "full_name" },
// 			{ title: "Email", data: "email", className: "email" },
// 			{ title: "Contract type", data: "contract_type", className: "contract_type" },
// 			{ title: "Job title", data: "job_title", className: "job_title" },
// 			{ title: "Month", data: "month", className: "month" },
// 			{ title: "Required days", data: "required_days", className: "required_days" },
// 			{ title: "Days worked", data: "days_worked", className: "days_worked" },
// 			{ title: "Unpaid days", data: "unpaid_days", className: "unpaid_days" },
// 			{ title: "Unpaid hours", data: "unpaid_hours", className: "unpaid_hours" },
// 			{ title: "Gross salary", data: "base_salary", className: "gross_salary", render: d => formatMoney(d) },
// 			{ title: "Earnings", data: "earnings", className: "earnings", render: d => formatMoney(d) },
// 			{ title: "Deductions", data: "total_deductions", className: "total_deductions", render: d => formatMoney(d) },
// 			{ title: "Tax", data: null, className: "tax", render: row => `${formatMoney(row.tax)} - (${row.taxRate}%)` },
// 			{ title: "Net salary", data: "net_salary", className: "net_salary", render: d => formatMoney(d) },
// 			{ title: "Status", data: "txtStatus", className: "status" },
// 			{ title: "Bank name", data: "bank_name", className: "bank_name" },
// 			{ title: "Account number", data: "bank_number", className: "bank_number" },
// 			{ title: "Action", data: null, className: "action", render: row => `<div class="sflex scenter-items">${set_actions(row)}</div>` }
// 		]
// 	});

// 	// Bulk select
// 	$(document).on('change', '.select-all-checkbox', function () {
// 		const isChecked = $(this).prop('checked');
// 		$('.row-checkbox').prop('checked', isChecked);
// 	});

// 	// Helper to hide irrelevant options
// 	function updateFilterVisibility(selector, validIDs) {
// 		const $select = $(selector);
// 		$select.find('option').each(function () {
// 			const val = $(this).val();
// 			if (val === "" || validIDs.includes(parseInt(val))) {
// 				$(this).show();
// 			} else {
// 				$(this).hide();
// 			}
// 		});
// 	}

// 	// Auto reload when any filter changes
// 	$('#slcDepartment, #slcDutyLocation, #slcState, #slcProject, #slcBudgetCode').off('change').on('change', function () {
// 		$('#showpayrollDT').DataTable().page(0).draw(false);
// 	});
// }

function load_showPayroll(payroll_id, month) {

	// Columns visible for employees only
	const employeeColumns = [
		"staff_no",
		"gross_salary",
		"earnings",
		"total_deductions",
		"tax",
		"net_salary",
		"status",
		"action"
	];

	// Determine visible columns based on user type
	const visibleColumns = (user_type === "employee")
		? employeeColumns
		: tableColumns; // your system-configured columns

	function set_actions(row) {
		let actions = `
            <a title="View payslip" data-recid="${row.id}" data-emp_id="${row.emp_id}"
               class="fa show_payslip smt-5 cursor smr-10 fa-eye"></a>`;

		if (row.status === 'Pending' && user_permissions['approve_payroll'] === 'on') {
			actions += `
                <a title="Approve payroll" data-recid="${row.payroll_id}" data-emp_id="${row.emp_id}"
                   class="fa approve_payrollBtn smt-5 cursor smr-10 fa-check"></a>`;
		}

		if (row.status === 'Approved' && user_permissions['pay_payroll'] === 'on') {
			actions += `
                <a title="Pay payroll" data-recid="${row.payroll_id}" data-detail_id="${row.id}"
                   class="fa pay_payrollBtn smt-5 cursor smr-10 fa-dollar-sign"></a>`;
		}

		if (user_permissions['delete_payroll_details'] === 'on') {
			actions += `
                <a data-recid="${row.id}" data-emp_id="${row.emp_id}"
                   class="fa delete_payrollDetail smt-5 cursor smr-10 fa-trash"></a>`;
		}

		return actions;
	}

	var datatable = $('#showpayrollDT').DataTable({
		processing: true,
		serverSide: true,
		bDestroy: true,
		info: false,
		columnDefs: [
			{ orderable: false, searchable: false, targets: [0, 19] }
		],
		serverMethod: 'post',
		ajax: {
			url: `${base_url}/app/payroll_controller.php?action=load&endpoint=payroll_details`,
			method: "POST",
			data: function (d) {
				d.payroll_id = payroll_id;
				d.month = month;
			}
		},

		drawCallback: function () {
			$('#showpayrollDT_wrapper').find('td, th').hide();

			visibleColumns.forEach((column) => {
				$('#showpayrollDT_wrapper').find('td.' + column).show();
				$('#showpayrollDT_wrapper').find('th.' + column).show();
			});

			// Always show select checkbox
			$('#showpayrollDT_wrapper').find('td.bulk-checkbox, th:first-child').show();
		},

		columns: [
			{
				title: `<input type="checkbox" class="select-all-checkbox">`,
				data: null,
				className: "bulk-checkbox",
				render: (data, type, row) => `
                    <div>
                        <input type="checkbox" class="row-checkbox"
                               data-id="${row.id}" data-emp_id="${row.emp_id}" data-payroll_id="${row.payroll_id}">
                    </div>`
			},

			{ title: `Staff No.`, className: "staff_no", data: row => row.staff_no },
			{ title: `Full name`, className: "full_name", data: row => row.full_name },
			{ title: `Email`, className: "email", data: row => row.email },
			{ title: `Contract type`, className: "contract_type", data: row => row.contract_type },
			{ title: `Job title`, className: "job_title", data: row => row.job_title },
			{ title: `Month`, className: "month", data: row => row.month },
			{ title: `Required days`, className: "required_days", data: row => row.required_days },
			{ title: `Days worked`, className: "days_worked", data: row => row.days_worked },
			{ title: `Unpaid days`, className: "unpaid_days", data: row => row.unpaid_days },
			{ title: `Unpaid hours`, className: "unpaid_hours", data: row => row.unpaid_hours },
			{ title: `Gross salary`, className: "gross_salary", data: row => formatMoney(row.base_salary) },
			{ title: `Earnings`, className: "earnings", data: row => formatMoney(row.earnings) },
			{ title: `Deductions`, className: "total_deductions", data: row => formatMoney(row.total_deductions) },
			{ title: `Tax`, className: "tax", data: row => `${formatMoney(row.tax)} (${row.taxRate}%)` },
			{ title: `Net salary`, className: "net_salary", data: row => formatMoney(row.net_salary) },
			{ title: `Status`, className: "status", data: row => row.txtStatus },
			{ title: `Bank name`, className: "bank_name", data: row => row.bank_name },
			{ title: `Account number`, className: "bank_number", data: row => row.bank_number },

			{
				title: "Action",
				className: "action",
				data: null,
				render: (data, type, row) => `<div class="sflex scenter-items">${set_actions(row)}</div>`
			},
		]
	});

	return false;
}




function handlePayroll() {
	$('#generatePayrollForm').on('submit', (e) => {
		handle_generatePayrollForm(e.target);
		return false
	})

	load_payroll();

	// Attendance for change
	$(document).on('change', '.slcTransFor', (e) => {
		let transFor = $(e.target).val();
		if (transFor == 'All') {
			$('.attenForDiv').html('')
		} else {
			$.post(`./app/payroll_controller.php?action=search&endpoint=trans_for`, { transFor: transFor }, function (data) {
				// console.log(data)
				let res = JSON.parse(data);
				if (!res.error) {
					$('.attenForDiv').html(res.data)

					$('.my-select').selectpicker({
						noneResultsText: "No results found"
					});


					$('.my-select').selectpicker('refresh');
				} else {
					swal('Ooops', res.msg, 'error');
					return false;
				}
			})
		}
	})

	// Edit payroll
	$(document).on('click', '.edit_transactionInfo', async (e) => {
		let id = $(e.currentTarget).data('recid');
		let modal = $('#edit_transaction');

		let data = await get_transaction(id);
		console.log(id)
		if (data) {
			let res = JSON.parse(data);
			console.log(res)
			/*$(modal).find('#transaction_id').val(id);
			$(modal).find('#employee_name').val(`${res.full_name}, ${res.phone_number}`);
			$(modal).find('#transType4Edit').val(res.transaction_type);
			$(modal).find('#transSubType4Edit').val(res.transaction_subtype);
			$(modal).find('#transAmount4Edit').val(res.amount);
			$(modal).find('#transStatus4Edit').val(res.status);
			$(modal).find('#transDate4Edit').val(res.date);
			$(modal).find('#txtComments4Edit').val(res.description);*/

			$(modal).find('#transSubType4Edit').html(`<option value="">None</option>`)
			$(modal).find('#transaction_id').val(id);
			$(modal).find('#employee_name').val(`${res.full_name}, ${res.phone_number}`);
			$(modal).find('#transType4Edit').val(res.transaction_type);
			$(modal).find('#transType4Edit').trigger('change')
			setTimeout(() => {
				$(modal).find('#transSubType4Edit').val(res.transaction_subtype);
			}, 200)
			// $(modal).find('#transSubType4Edit').val(res.transaction_subtype);
			$(modal).find('#transAmount4Edit').val(res.amount);
			$(modal).find('#transStatus4Edit').val(res.status);
			$(modal).find('#transDate4Edit').val(res.date);
			$(modal).find('#txtComments4Edit').val(res.description);

		}

		$(modal).modal('show');
	});

	// Edit payroll info form
	/*$('#editTransactionForm').on('submit', (e) => {
		handle_editTransactionForm(e.target);
		return false
	})*/

	// Delete payroll
	$(document).on('click', '.delete_payroll', async (e) => {
		let id = $(e.currentTarget).data('recid');
		swal({
			title: "Are you sure?",
			text: `You are going to delete this payroll  record.`,
			icon: "warning",
			className: 'warning-swal',
			buttons: ["Cancel", "Yes, delete"],
		}).then(async (confirm) => {
			if (confirm) {
				let data = { id: id };
				try {
					let response = await send_payrollPost('delete payroll', data);
					console.log(response)
					if (response) {
						let res = JSON.parse(response);
						if (res.error) {
							toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 3000 }).then(() => {
								location.reload();
							});;
						} else {
							toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
								location.reload();
								// load_branches();
							});
							console.log(res);
						}
					} else {
						console.log('Failed to edit state.' + response);
					}

				} catch (err) {
					console.error('Error occurred during form submission:', err);
				}
			}
		});
	});

	// Approve payroll
	$(document).on('click', '.approve_payrollBtn', async (e) => {
		let id = $(e.currentTarget).data('recid');
		let emp_id = $(e.currentTarget).data('emp_id');
		swal({
			title: "Are you sure?",
			text: `You are going to approve this payroll  record.`,
			icon: "warning",
			// className: 'warning-swal',
			buttons: ["Cancel", "Yes, approve"],
		}).then(async (confirm) => {
			if (confirm) {
				approve_payroll(id, emp_id)
			}
		})
	});

	// Pay payroll
	$(document).on('click', '.pay_payrollBtn', async (e) => {
		let id = $(e.currentTarget).data('recid');
		let detailId = $(e.currentTarget).data('detail_id');
		let modal = $('#pay_payroll');

		$(modal).find('#payroll_id').val(id);
		$(modal).find('#payroll_detId').val(`${detailId}`);

		$(modal).modal('show');
	});

	// Pay payroll
	$('#payPayrollForm').on('submit', async (e) => {
		e.preventDefault(); // Prevent the default form submission immediately.
		clearErrors();
		let error = validateForm(e.target)
		let payroll_id = $(e.target).find('#payroll_id').val();
		let payroll_detId = $(e.target).find('#payroll_detId').val();
		let slcBank = $(e.target).find('#slcBank').val();
		let payDate = $(e.target).find('#payDate').val();

		if (payroll_detId == 'undefined' || !payroll_detId) payroll_detId = '';

		console.log(payroll_id, payroll_detId, slcBank, payDate);

		if (error) return false;

		swal({
			title: "Are you sure?",
			text: `You are going to pay this payroll record.`,
			icon: "warning",
			buttons: ["Cancel", "Yes, pay"],
		}).then(async (confirm) => {
			if (confirm) {
				let data = { payroll_id, payroll_detId, slcBank, payDate }; // Using shorthand object property.
				try {
					let response = await send_payrollPost('update payPayroll', data);
					console.log(response);
					if (response) {
						let res = JSON.parse(response);
						if (res.error) {
							toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 3000 }).then(() => {
								location.reload();
							});
						} else {
							toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
								location.reload();
							});
							console.log(res);
						}
					} else {
						console.log('Failed to edit state.' + response);
					}
				} catch (err) {
					console.error('Error occurred during form submission:', err);
				}
			}
		});
	});


	// Delete payroll detail
	$(document).on('click', '.delete_payrollDetail', async (e) => {
		let id = $(e.currentTarget).data('recid');
		swal({
			title: "Are you sure?",
			text: `You are going to delete this payroll detail  record.`,
			icon: "warning",
			className: 'warning-swal',
			buttons: ["Cancel", "Yes, delete"],
		}).then(async (confirm) => {
			if (confirm) {
				delete_payrollDetail(id)
			}
		});
	});

	// Show payrol details
	$(document).on('click', '.show_payslip', async (e) => {
		let id = $(e.currentTarget).data('recid');
		let emp_id = $(e.currentTarget).data('emp_id');
		let modal = $('#show_payslip');
		let data = await get_4payslipShow(id);
		console.log(data)
		if (data) {
			$(modal).find('.modal-dialog').html(data);
			$(document).find('.print-payslip').attr('href', `${base_url}/pdf.php?rec_id=${id}&print=payslip`);
			$(document).find('.print-payslip').attr('target', '_blank');
		}


		$(modal).modal('show');
	});


	// Payroll year change 
	$('#slcYear').on('change', (e) => {
		let year = $(e.target).val();
		const months = {
			'01': 'January',
			'02': 'February',
			'03': 'March',
			'04': 'April',
			'05': 'May',
			'06': 'June',
			'07': 'July',
			'08': 'August',
			'09': 'September',
			'10': 'October',
			'11': 'November',
			'12': 'December'
		};

		const selectElement = $('#slctedMonths');

		// Loop through months and create options
		Object.entries(months).forEach(([monthNum, monthName]) => {
			const optionValue = `${year}-${monthNum}`;
			const optionText = `${monthName} ${year}`;

			// Check if option already exists before appending
			if (!selectElement.find(`option[value="${optionValue}"]`).length) {
				const newOption = $('<option></option>')
					.val(optionValue)
					.text(optionText);
				selectElement.append(newOption);

				$("#slctedMonths").SumoSelect().sumo.reload();
			}
		});
	});

	// 	Bulk actions
	$('#bulk_actions').on('change', (e) => {
		let action = $(e.target).val();
		if (action) {
			let ids = [];
			let emp_ids = [];
			let payroll_ids = [];
			$('.row-checkbox:checked').each(function () {
				ids.push($(this).data('id'));
				emp_ids.push($(this).data('emp_id'));
				payroll_ids.push($(this).data('payroll_id'));
			});
			if (ids.length > 0) {
				if (action == 'approve') {
					swal({
						title: "Are you sure?",
						text: `You are going to approve selected payroll  records.`,
						icon: "warning",
						// className: 'warning-swal',
						buttons: ["Cancel", "Yes, approve"],
					}).then(async (confirm) => {
						if (confirm) {
							let autoreload = false;
							ids.map((id, index) => {
								if (id == ids[ids.length - 1]) autoreload = true;
								approve_payroll(payroll_ids[index], emp_ids[index], 'Approved', autoreload);
							})
						}
					})
				} else if (action == 'request') {
					swal({
						title: "Are you sure?",
						text: `You are going to change status request to selected payroll  records.`,
						icon: "warning",
						// className: 'warning-swal',
						buttons: ["Cancel", "Yes, change status to request"],
					}).then(async (confirm) => {
						if (confirm) {
							let autoreload = false;
							ids.map((id, index) => {
								if (id == ids[ids.length - 1]) autoreload = true;
								approve_payroll(payroll_ids[index], emp_ids[index], 'Pending', autoreload);
							})
						}
					})
				} else if (action == 'reject') {
					swal({
						title: "Are you sure?",
						text: `You are going to reject selected payroll  records.`,
						icon: "warning",
						// className: 'warning-swal',
						buttons: ["Cancel", "Yes, reject"],
					}).then(async (confirm) => {
						if (confirm) {
							let autoreload = false;
							ids.map((id, index) => {
								if (id == ids[ids.length - 1]) autoreload = true;
								approve_payroll(payroll_ids[index], emp_ids[index], 'Rejected', autoreload);
							})
						}
					})
				} else if (action == 'delete') {
					swal({
						title: "Are you sure?",
						text: `You are going to delete selected payroll  records.`,
						icon: "warning",
						// className: 'warning-swal',
						buttons: ["Cancel", "Yes, delete"],
					}).then(async (confirm) => {
						if (confirm) {
							let autoreload = false;
							ids.map((id, index) => {
								if (id == ids[ids.length - 1]) autoreload = true;
								delete_payrollDetail(id, autoreload);
							})
						}
					})
				}
			}
		}
	})

}

async function approve_payroll(id, emp_id, status = 'Approved', autoreload = true) {
	let data = { id: id, emp_id: emp_id, status: status };
	try {
		let response = await send_payrollPost('update approvePayroll', data);
		console.log(response)
		if (response) {
			let res = JSON.parse(response);
			if (res.error) {
				toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 3000 }).then(() => {
					if (autoreload) location.reload();
				});;
			} else {
				toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
					if (autoreload) location.reload();
					// load_branches();
				});
				console.log(res);
			}
		} else {
			console.log('Failed to edit state.' + response);
		}

	} catch (err) {
		console.error('Error occurred during form submission:', err);
	}

}

async function delete_payrollDetail(id, autoreload = true) {
	let data = { id: id };
	try {
		let response = await send_payrollPost('delete payrollDetail', data);
		console.log(response)
		if (response) {
			let res = JSON.parse(response);
			if (res.error) {
				toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 3000 }).then(() => {
					if (autoreload) location.reload();
				});;
			} else {
				toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
					if (autoreload) location.reload();
					// load_branches();
				});
				console.log(res);
			}
		} else {
			console.log('Failed to edit state.' + response);
		}

	} catch (err) {
		console.error('Error occurred during form submission:', err);
	}
}

async function handle_generatePayrollForm(form) {
	clearErrors();
	let error = validateForm(form)
	let ref_id, ref_name, err_mgs = '';
	let transFor = $(form).find('#slcTransFor').val();

	if (transFor != 'All') {
		if (transFor == 'Department') {
			ref_id = $(form).find('#searchDepartment').val();
			ref_name = $(form).find('#searchDepartment option:selected').text();
			err_mgs = 'department';
		} else if (transFor == 'Location') {
			ref_id = $(form).find('#searchLocation').val();
			ref_name = $(form).find('#searchLocation option:selected').text();
			err_mgs = 'location';
		} else if (transFor == 'Employee') {
			ref_id = $(form).find('#searchEmployee').val();
			ref_name = $(form).find('#searchEmployee option:selected').text();
			err_mgs = 'employee';
		} else if (transFor == 'State') {
			ref_id = $(form).find('#searchState').val();
			ref_name = $(form).find('#searchState option:selected').text();
			err_mgs = 'state';
		}
	}

	let month = $(form).find('#slctedMonths').val();

	if (error) return false;

	let formData = {
		ref: transFor,
		ref_id: ref_id,
		ref_name: ref_name,
		month: month,
	};

	form_loading(form)

	console.log(formData)
	// return false;

	try {
		let response = await send_payrollPost('save payroll', formData);
		console.log(response)
		// return false;
		if (response) {
			let res = JSON.parse(response)
			if (res.error) {
				toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: false, duration: 3000 }).then(() => {
					// location.reload();
				});
			} else {
				toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
					location.reload();
				});
			}
		} else {
			console.log('Failed to save state.' + response);
		}

	} catch (err) {
		console.error('Error occurred during form submission:', err);
	}

	return false;
}

async function get_4payslipShow(id) {
	let data = { id };
	let response = await send_payrollPost('get 4payslipShow', data);
	return response;
}

document.addEventListener("DOMContentLoaded", function () {
	handleTransactions();
	handlePayroll();

	$('.my-select').selectpicker({
		noneResultsText: "No results found"
	});

	// Search employee
	$(document).on('keyup', '.bootstrap-select.searchEmployee input.form-control', async (e) => {
		let search = $(e.target).val();
		let searchFor = 'leave';
		let formData = { search: search, searchFor: searchFor }
		if (search) {
			try {
				let response = await send_payrollPost('search employee4Select', formData);
				console.log(response)
				let res = JSON.parse(response);
				if (!res.error) {
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
		let formData = { search: search, searchFor: searchFor }
		if (search) {
			try {
				let response = await send_payrollPost('search department4Select', formData);
				console.log(response)
				let res = JSON.parse(response);
				if (!res.error) {
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
		let formData = { search: search, searchFor: searchFor }
		if (search) {
			try {
				let response = await send_payrollPost('search location4Select', formData);
				console.log(response)
				let res = JSON.parse(response);
				if (!res.error) {
					$('#searchLocation').html(res.options)
					$('.my-select').selectpicker('refresh');
				}
			} catch (err) {
				console.error('Error occurred during form submission:', err);
			}
		}
	})

	// Search location
	$(document).on('keyup', '.bootstrap-select.searchState input.form-control', async (e) => {
		let search = $(e.target).val();
		let searchFor = 'leave';
		let formData = { search: search, searchFor: searchFor }
		if (search) {
			try {
				let response = await send_payrollPost('search state4Select', formData);
				console.log(response)
				let res = JSON.parse(response);
				if (!res.error) {
					$('#searchState').html(res.options)
					$('.my-select').selectpicker('refresh');
				}
			} catch (err) {
				console.error('Error occurred during form submission:', err);
			}
		}
	})



	// Payroll - Next Action Change Handler
	$(document).on('change', '#next_action', async function (e) {
		const status = $(e.target).val();
		const payroll_id = $('.payroll_id').val();
		if (!status) return;

		let reason = '';

		// If Rejected, ask for reason
		if (status === 'Rejected') {
			reason = await swal({
				title: 'Provide Rejection Reason',
				text: 'Please enter a reason for rejecting this payroll:',
				content: "input",
				button: {
					text: "Submit",
					closeModal: false
				}
			});

			// User canceled or left it empty
			if (!reason || reason.trim() === '') {
				swal.close();
				toaster.warning('Rejection reason is required.', 'Warning', {
					top: '30%',
					right: '20px',
					hide: true,
					duration: 2000
				});
				$(e.target).val(''); // Reset the dropdown
				return;
			}

			swal.close();
		}

		// Show confirmation dialog before updating
		const result = await swal({
			title: 'Are you sure?',
			text: `You want to update the payroll status to "${status}"?`,
			icon: 'warning',
			buttons: {
				cancel: {
					text: 'No',
					visible: true
				},
				confirm: {
					text: 'Yes, update',
					closeModal: false
				}
			}
		});

		if (!result) return;

		const data = {
			status: status,
			id: payroll_id
		};

		if (status === 'Rejected') data.reason = reason; // Include reason only for rejection

		console.log(data)
		// return false;

		try {
			const response = await send_payrollPost('update payroll_status', data);
			console.log(response);

			if (!response) {
				console.error('Empty response from server.');
				swal("Error", "No response received from the server.", "error");
				return;
			}

			const res = JSON.parse(response);

			if (res.error) {
				toaster.warning(res.msg || 'Something went wrong.', 'Sorry', {
					top: '30%',
					right: '20px',
					hide: true,
					duration: 3000
				}).then(() => {
					location.reload();
				});
			} else {
				toaster.success(res.msg || 'Payroll status updated successfully.', 'Success', {
					top: '20%',
					right: '20px',
					hide: true,
					duration: 1000
				}).then(() => {
					location.reload();
				});
			}

		} catch (err) {
			console.error('Error occurred during payroll status update:', err);
			swal("Error", "Failed to update payroll. Please try again.", "error");
		}
	});



	// Notify Next Person Form
	$("#notifyNextPersonForm").on('submit', async (e) => {
		e.preventDefault();
		let form = e.target;
		handleNotificationSubmit(form);
	});




	// Handle "Add Employees to Payroll" modal
	$(document).on('click', '.add-employees-btn', function () {
		const payrollId = $(this).data('id');
		const modal = $('#addEmployeesToPayrollModal');
		modal.find('#payroll_id_for_add').val(payrollId);

		// Initialize selectpicker
		const select = modal.find('#search_employee_for_payroll');
		select.selectpicker();

		// Clear previous options
		select.html('').selectpicker('refresh');

		modal.modal('show');
	});




	// Live search for employees to add to payroll
	let employeeSearchTimeout;
	$(document).on('keyup', '.bootstrap-select.searchEmployeePayroll input.form-control', async (e) => {
		// clearTimeout(employeeSearchTimeout);
		console.log(e)
		const searchTerm = $(this).val();
		const payrollId = $('#payroll_id_for_add').val();
		const select = $('#search_employee_for_payroll');
		const formData = { search: searchTerm, payroll_id: payrollId }
		if (payrollId) {
			try {
				let response = await send_payrollPost('search search_employees_for_payroll', formData);
				console.log(response)
				let res = JSON.parse(response);
				if (!res.error) {
					$('#searchEmployeePayroll').html(res.options)
					$('.my-select').selectpicker('refresh');
				}
			} catch (err) {
				console.error('Error occurred during form submission:', err);
			}
		}

	});


	// Handle form submission for adding employees to payroll
	$('#addEmployeeToPayrollForm').on('submit', function (e) {
		e.preventDefault();
		const form = $(this);
		add_employeeToPayroll(form);
		return false
	});

});

async function handleNotificationSubmit(form) {
	clearErrors();

	let error = validateForm(form);
	if (error) return false;

	let payroll_id = $(form).find('.payroll_id').val();
	let current_status = $(form).find('.selected_status').val();
	let next_user = $(form).find('#nextUserSelect').val();
	let message = $(form).find('#notificationMessage').val();
	let status = $(form).find('.selected_status').val(); // explicit status field

	let formData = {
		payroll_id: payroll_id,
		current_status: current_status,
		next_user: next_user,
		message: message,
		status: status // server now handles status + workflow + payroll_details
	};

	// Add form button loading 
	form_loading(form)

	console.log(formData)
	// return false;

	// Confirm with the user before submitting
	const result = await swal({
		title: 'Are you sure?',
		text: "You're about to finalize this payroll. No further actions can be performed after finishing.",
		icon: 'warning',
		buttons: {
			cancel: {
				text: 'Cancel',
				visible: true,
				className: 'btn btn-secondary'
			},
			confirm: {
				text: 'Yes, I am sure',
				className: 'btn btn-danger'
			}
		},
		dangerMode: true
	});

	if (!result) {
		return false; // User cancelled
	}

	try {
		let response = await send_payrollPost('update notify_next_person', formData);
		console.log(response);

		if (response) {
			let res = JSON.parse(response);

			if (res.error) {
				toaster.warning(res.msg || 'Something went wrong.', 'Sorry', {
					top: '30%',
					right: '20px',
					hide: true,
					duration: 3000
				}).then(() => {
					location.reload();
				});
			} else {
				toaster.success(res.msg || 'Notification sent successfully.', 'Success', {
					top: '20%',
					right: '20px',
					hide: true,
					duration: 1000
				}).then(() => {
					location.reload();
				});
			}
		} else {
			console.error('Failed to update payroll next action.', response);
		}
	} catch (err) {
		console.error('Error occurred during form submission:', err);
		swal("Error", "An error occurred while submitting the form. Please try again.", "error");
	}

	return false;
}

async function add_employeeToPayroll(form) {
	const submitBtn = form.find('button[type="submit"]');
	const payrollId = $(form).find('#payroll_id_for_add').val();

	// Get selected employee IDs
	const selectedEmployees = $(form).find('#searchEmployeePayroll').val();
	console.log(selectedEmployees)
	if (!selectedEmployees || selectedEmployees.length === 0) {
		swal("Error", "Please select at least one employee.", "error");
		return;
	}

	let formData = {
		payroll_id: payrollId,
		employee_ids: selectedEmployees
	}

	console.log(formData)
	// return false;

	try {
		const response = await send_payrollPost('save add_employees_to_payroll', formData);
		console.log(response);

		if (response) {
			let res = JSON.parse(response);

			if (res.error) {
				toaster.warning(res.msg || 'Something went wrong.', 'Sorry', {
					top: '30%',
					right: '20px',
					hide: true,
					duration: 3000
				}).then(() => {
					location.reload();
				});
			} else {
				toaster.success(res.msg || 'Notification sent successfully.', 'Success', {
					top: '20%',
					right: '20px',
					hide: true,
					duration: 1000
				}).then(() => {
					location.reload();
				});
			}
		} else {
			console.error('Failed to update payroll next action.', response);
		}
	} catch (err) {
		console.error('Error occurred during form submission:', err);
		swal("Error", "An error occurred while submitting the form. Please try again.", "error");
	}

	return false;
}