async function send_financePost(str, data) {
    let [action, endpoint] = str.split(' ');

    try {
        const response = await $.post(`${base_url}/app/finance_controller.php?action=${action}&endpoint=${endpoint}`, data);
        return response;
    } catch (error) {
        console.error('Error occurred during the request:', error);
        return null;
    }
}

document.addEventListener("DOMContentLoaded", function() {
	handleIncome();
	handleBanks();
	handlePayrollPayments();
	handleExpenses();
	
});	

// Payroll Payments
function load_approvedPayrolls() {
	var datatable = $('#approvedPayrollsDT').DataTable({
	    "processing": true,
	    "serverSide": true,
	    "bDestroy": true,
	    "searching": true,  
	    "info": false,
	    "columnDefs": [
	        { "orderable": false, "searchable": false, "targets": [0, 7] }  // Disable search on checkbox and action columns
	    ],
	    "serverMethod": 'post',
	    "ajax": {
	        "url": "./app/finance_controller.php?action=load&endpoint=approved_payrolls",
	        "method": "POST",
	        "data": function(d) {
	            d.month = $('#monthFilter').val();
	        }
	    },
	    
	    "createdRow": function(row, data, dataIndex) { 
	    	// Add your custom class to the row 
	    	$(row).addClass('table-row approved');
	    },
	    columns: [
	    	{ title: `<input type="checkbox" class="select-all-checkbox">`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<input type="checkbox" class="row-checkbox" data-id="${row.id}" data-payroll_id="${row.payroll_id}" data-emp_id="${row.emp_id}">
	                </div>`;
	        }},

	        { title: `Staff No.`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.staff_no}</span>
	                </div>`;
	        }},

	        { title: `Full name`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.full_name}</span>
	                </div>`;
	        }},

	        { title: `Month`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.month}</span>
	                </div>`;
	        }},

	        { title: `Base Salary`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${formatMoney(row.base_salary)}</span>
	                </div>`;
	        }},

	        { title: `Net Salary`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${formatMoney(row.net_salary)}</span>
	                </div>`;
	        }},

	        { title: `Status`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span class="badge badge-success">${row.status}</span>
	                </div>`;
	        }},

	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
            		<span data-recid="${row.id}" data-payroll_id="${row.payroll_id}" class="fa pay_payrollBtn smt-5 cursor smr-10 fa-dollar-sign" title="Pay"></span>
            		<span data-recid="${row.id}" class="fa reject_payrollBtn smt-5 cursor fa-times" title="Reject"></span>
                </div>`;
	        }},
	    ]
	});

	return false;
}

function handlePayrollPayments() {
	// Load approved payrolls on page load
	load_approvedPayrolls();

	// Month filter change
	$(document).on('change', '#monthFilter', function() {
		$('#approvedPayrollsDT').DataTable().ajax.reload();
	});

	// Select all checkbox functionality
	$(document).on('change', '.select-all-checkbox', function() {
		$('.row-checkbox').prop('checked', $(this).prop('checked'));
		updateBulkActionButtons();
	});

	// Individual checkbox change
	$(document).on('change', '.row-checkbox', function() {
		updateBulkActionButtons();
		
		// Update select all checkbox
		var totalCheckboxes = $('.row-checkbox').length;
		var checkedCheckboxes = $('.row-checkbox:checked').length;
		$('.select-all-checkbox').prop('checked', totalCheckboxes === checkedCheckboxes);
	});

	// Bulk pay button
	$(document).on('click', '#bulkPayBtn', function() {
		var selectedIds = [];
		var payrollId = null;
		
		$('.row-checkbox:checked').each(function() {
			selectedIds.push($(this).data('id'));
			if (!payrollId) {
				payrollId = $(this).data('payroll_id');
			}
		});

		if (selectedIds.length > 0) {
			showPaymentModal(payrollId, selectedIds.join(','), null);
		}
	});

	// Bulk reject button
	$(document).on('click', '#bulkRejectBtn', function() {
		var selectedIds = [];
		
		$('.row-checkbox:checked').each(function() {
			selectedIds.push($(this).data('id'));
		});

		if (selectedIds.length > 0) {
			swal({
				title: "Are you sure?",
				text: `You are going to reject the selected payroll entries.`,
				icon: "warning",
				className: 'warning-swal',
				buttons: ["Cancel", "Yes, reject"],
			}).then(async (confirm) => {
				if (confirm) {
					rejectPayroll(null, selectedIds.join(','));
				}
			})
		}
	});

	// Individual pay button
	$(document).on('click', '.pay_payrollBtn', function() {
		var payrollDetailId = $(this).data('recid');
		var payrollId = $(this).data('payroll_id');
		showPaymentModal(payrollId, null, payrollDetailId);
	});

	// Individual reject button
	$(document).on('click', '.reject_payrollBtn', function() {
		var payrollDetailId = $(this).data('recid');
		swal({
			title: "Are you sure?",
			text: `You are going to reject this payroll entry.`,
			icon: "warning",
			className: 'warning-swal',
			buttons: ["Cancel", "Yes, reject"],
		}).then(async (confirm) => {
			if (confirm) {
				rejectPayroll(payrollDetailId, null);
			}
		})
	});

	// Payment form submission
	$('#paymentForm').on('submit', function(e) {
		e.preventDefault();
		handle_paymentForm(this);
	});

	// Load bank accounts when payment modal opens
	$(document).on('show.bs.modal', '#paymentModal', function() {
		loadBankAccountsForPayment();
	});
}

function updateBulkActionButtons() {
	var checkedCount = $('.row-checkbox:checked').length;
	
	if (checkedCount > 0) {
		$('#bulkActions').show();
		$('#bulkPayBtn, #bulkRejectBtn').prop('disabled', false);
	} else {
		$('#bulkActions').hide();
		$('#bulkPayBtn, #bulkRejectBtn').prop('disabled', true);
	}
}

function showPaymentModal(payrollId, payrollDetailIds, payrollDetailId) {
	$('#paymentModal').find('#payroll_id').val(payrollId);
	$('#paymentModal').find('#payroll_detIds').val(payrollDetailIds);
	$('#paymentModal').find('#payroll_detId').val(payrollDetailId);
	$('#paymentModal').find('#payDate').val(new Date().toISOString().split('T')[0]);
	$('#paymentModal').modal('show');
}

async function loadBankAccountsForPayment() {
	try {
		const response = await send_financePost('get bank_accounts_for_payment', {});
		$('#slcBank').html(response);
	} catch (error) {
		console.error('Error loading bank accounts:', error);
		toastr.error('Error loading bank accounts');
	}
}

async function handle_paymentForm(form) {
	let data = {};
	const formData = new FormData(form);
	formData.forEach((value, key) => {
		data[key] = value;
	});

	try {
		const response = await send_financePost('update payPayroll', data);
		const result = JSON.parse(response);
		
		if (!result.error) {
			toaster.success(result.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
				$('#paymentModal').modal('hide');
				$('#approvedPayrollsDT').DataTable().ajax.reload();
				// Clear checkboxes
				$('.row-checkbox, .select-all-checkbox').prop('checked', false);
				updateBulkActionButtons();
			});
		} else {
			toaster.error(result.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
		}
	} catch (error) {
		console.error('Error processing payment:', error);
		toaster.error('Error processing payment', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
	}
}

async function rejectPayroll(payrollDetailId, payrollDetailIds) {
	const data = {};
	if (payrollDetailId) {
		data['payroll_detId'] = payrollDetailId;
	}
	if (payrollDetailIds) {
		data['payroll_detIds'] = payrollDetailIds;
	}
	
	try {
		const response = await send_financePost('update rejectPayroll', data);
		const result = JSON.parse(response);
		
		if (!result.error) {
			toaster.success(result.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
				$('#approvedPayrollsDT').DataTable().ajax.reload();
				// Clear checkboxes
				$('.row-checkbox, .select-all-checkbox').prop('checked', false);
				updateBulkActionButtons();
			});
		} else {
			toaster.error(result.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
		}
	} catch (error) {
		console.error('Error rejecting payroll:', error);
		toaster.error('Error rejecting payroll', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
	}
}

// Income
function load_income() {
	var datatable = $('#incomeDT').DataTable({
	    "processing": true,
	    "serverSide": true,
	    "bDestroy": true,
	    "searching": true,  
	    "info": false,
	    "columnDefs": [
	        { "orderable": false, "searchable": false, "targets": [7] }
	    ],
	    "serverMethod": 'post',
	    "ajax": {
	        "url": "./app/finance_controller.php?action=load&endpoint=income",
	        "method": "POST"
	    },
	    "createdRow": function(row, data, dataIndex) {
	    	$(row).addClass('table-row ' + (data.status ? data.status.toLowerCase() : ''));
	    },
	    columns: [
	        { title: `Date`, data: null, render: function(data, type, row) {
	            return `<div><span>${formatDate(row.added_date)}</span></div>`;
	        }},
	        { title: `Financial Account`, data: null, render: function(data, type, row) {
	            return `<div><span>${row.fn_account_name}</span></div>`;
	        }},
	        { title: `Amount`, data: null, render: function(data, type, row) {
	            return `<div><span>${formatMoney(row.amount)}</span></div>`;
	        }},
	        { title: `Received From`, data: null, render: function(data, type, row) {
	            return `<div><span>${row.payee_payer}</span></div>`;
	        }},
	        { title: `Bank`, data: null, render: function(data, type, row) {
	            return `<div><span>${row.bank_name}</span></div>`;
	        }},
	        { title: `Ref Number`, data: null, render: function(data, type, row) {
	            return `<div><span>${row.ref_number || '-'}</span></div>`;
	        }},
	        { title: `Status`, data: null, render: function(data, type, row) {
	            return `<div><span class="">${row.status}</span></div>`;
	        }},
			// <span data-recid="${row.id}" class="fa edit_incomeInfo smt-5 cursor smr-10 fa-pencil" title="Edit"></span>
	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
	                <span data-recid="${row.id}" class="fa delete_income smt-5 cursor fa-trash" title="Delete"></span>
	            </div>`;
	        }},
	    ]
	});
	return false;
}

function handleIncome() {
	$('#addIncomeForm').on('submit', (e) => {
		handle_addIncomeForm(e.target);
		return false;
	})

	load_income();
	load_financial_accounts_income();
	load_banks_income();

	$(document).on('click', '.edit_incomeInfo', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    let modal = $('#edit_income');
	    let data = await get_income(id);
	    if(data) {
	    	let res = JSON.parse(data).data;
	    	$(modal).find('#edit_income_id').val(id);
	    	$(modal).find('#edit_slcFinancialAccountIncome').val(res.fn_account_id);
	    	$(modal).find('#edit_slcBankIncome').val(res.bank_id);
	    	$(modal).find('#edit_amountIncome').val(res.amount);
	    	$(modal).find('#edit_receivedFrom').val(res.payee_payer);
	    	$(modal).find('#edit_receivedDate').val(res.added_date ? res.added_date.split(' ')[0] : '');
	    	$(modal).find('#edit_refNumberIncome').val(res.ref_number);
	    	$(modal).find('#edit_descriptionIncome').val(res.description);
	    	$(modal).find('#edit_slcStatusIncome').val(res.status);
	    }
	    $(modal).modal('show');
	});

	$('#editIncomeForm').on('submit', (e) => {
		handle_editIncomeForm(e.target);
		return false;
	});

	$(document).on('click', '.delete_income', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    swal({
	        title: "Are you sure?",
	        text: `You are going to delete this income record.`,
	        icon: "warning",
	        className: 'warning-swal',
	        buttons: ["Cancel", "Yes, delete"],
	    }).then(async (confirm) => {
	        if (confirm) {
	            let result = await send_financePost('delete income', { id });
				console.log(result);
	            let res = JSON.parse(result);
	            if (!res.error) {
	                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
	                    $('#incomeDT').DataTable().ajax.reload();
	                });
	            } else {
	                toaster.error(res.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
	            }
	        }
	    });
	});
}

async function handle_addIncomeForm(form) {
	let data = {};
	// const formData = new FormData(form);
	let bank_id = $('#slcBankIncome').val();
	let fn_account_id = $('#slcFinancialAccountIncome').val();
	let amount = $('#amountIncome').val();
	let receivedFrom = $('#receivedFrom').val();
	let receivedDate = $('#receivedDate').val();
	let refNumberIncome = $('#refNumberIncome').val();
	let descriptionIncome = $('#descriptionIncome').val();
	
	// Map fields to backend
	data['bank_id'] = bank_id;
	data['fn_account_id'] = fn_account_id;
	data['payee_payer'] = receivedFrom;
	data['paid_date'] = receivedDate;
	data['refNumber'] = refNumberIncome;
	data['description'] = descriptionIncome;
	data['amount'] = amount;
	console.log(data);
	// return false;
	try {
		const response = await send_financePost('save income', data);
		console.log(response);
		const result = JSON.parse(response);
		if (!result.error) {
			toaster.success(result.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
				$('#add_income').modal('hide');
				$('#incomeDT').DataTable().ajax.reload();
				form.reset();
			});
		} else {
			toaster.error(result.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
		}
	} catch (error) {
		console.error('Error adding income:', error);
		toaster.error('Error adding income', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
	}
}

async function handle_editIncomeForm(form) {
	let data = {};
	const formData = new FormData(form);
	formData.forEach((value, key) => {
		data[key] = value;
	});
	data['id'] = data['edit_income_id'];
	data['slcBankIncome'] = data['edit_slcBankIncome'];
	data['slcFinancialAccountIncome'] = data['edit_slcFinancialAccountIncome'];
	data['amountIncome'] = data['edit_amountIncome'];
	data['receivedFrom'] = data['edit_receivedFrom'];
	data['receivedDate'] = data['edit_receivedDate'];
	data['refNumberIncome'] = data['edit_refNumberIncome'];
	data['descriptionIncome'] = data['edit_descriptionIncome'];
	data['slcStatusIncome'] = data['edit_slcStatusIncome'];
	try {
		const response = await send_financePost('update income', data);
		const result = JSON.parse(response);
		if (!result.error) {
			toaster.success(result.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
				$('#edit_income').modal('hide');
				$('#incomeDT').DataTable().ajax.reload();
			});
		} else {
			toaster.error(result.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
		}
	} catch (error) {
		console.error('Error editing income:', error);
		toaster.error('Error editing income', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
	}
}

async function get_income(id) {
	return await send_financePost('get income', { id });
}

async function load_financial_accounts_income() {
	// let dropdowns = [$('#slcFinancialAccountIncome'), $('#edit_slcFinancialAccountIncome')];
	// try {
	// 	let accounts = await $.post('./app/finance_controller.php?action=load&endpoint=financial_accounts', { type: 'Income', status: 'Active' });
	// 	accounts = JSON.parse(accounts).data || [];
	// 	dropdowns.forEach($dd => {
	// 		$dd.html('<option value="">Select Financial Account</option>');
	// 		accounts.forEach(acc => {
	// 			$dd.append(`<option value="${acc.id}">${acc.name}</option>`);
	// 		});
	// 	});
	// } catch (error) {
	// 	console.error('Error loading financial accounts:', error);
	// }
}

async function load_banks_income() {
	// let dropdowns = [$('#slcBankIncome'), $('#edit_slcBankIncome')];
	// try {
	// 	let banks = await $.post('./app/finance_controller.php?action=load&endpoint=bank_accounts', { status: 'Active' });
	// 	banks = JSON.parse(banks).data || [];
	// 	dropdowns.forEach($dd => {
	// 		$dd.html('<option value="">Select Bank Account</option>');
	// 		banks.forEach(bank => {
	// 			$dd.append(`<option value="${bank.id}">${bank.bank_name}, ${bank.account} (Balance: ${formatMoney(bank.balance)})</option>`);
	// 		});
	// 	});
	// } catch (error) {
	// 	console.error('Error loading banks:', error);
	// }
}

// Expenses
function load_expenses() {
	var datatable = $('#expensesDT').DataTable({
	    "processing": true,
	    "serverSide": true,
	    "bDestroy": true,
	    "searching": true,  
	    "info": false,
	    "columnDefs": [
	        { "orderable": false, "searchable": false, "targets": [7] }  // Disable search on action column
	    ],
	    "serverMethod": 'post',
	    "ajax": {
	        "url": "./app/finance_controller.php?action=load&endpoint=expenses",
	        "method": "POST"
	    },
	    
	    "createdRow": function(row, data, dataIndex) { 
	    	// Add your custom class to the row 
	    	$(row).addClass('table-row ' + data.status.toLowerCase());
	    },
	    columns: [
	        { title: `Date`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${formatDate(row.added_date)}</span>
	                </div>`;
	        }},

	        { title: `Financial Account`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.fn_account_name}</span>
	                </div>`;
	        }},

	        { title: `Amount`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${formatMoney(row.amount)}</span>
	                </div>`;
	        }},

	        { title: `Paid To`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.payee_payer}</span>
	                </div>`;
	        }},

	        { title: `Bank`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.bank_name}</span>
	                </div>`;
	        }},

	        { title: `Ref Number`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.ref_number || '-'}</span>
	                </div>`;
	        }},

	        { title: `Status`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span class="">${row.status}</span>
	                </div>`;
	        }},

			// <span data-recid="${row.id}" class="fa edit_expenseInfo smt-5 cursor smr-10 fa-pencil" title="Edit"></span>
	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
            		
            		<span data-recid="${row.id}" class="fa delete_expense smt-5 cursor fa-trash" title="Delete"></span>
                </div>`;
	        }},
	    ]
	});

	return false;
}

function handleExpenses() {
	$('#addExpenseForm').on('submit', (e) => {
		handle_addExpenseForm(e.target);
		return false
	})

	load_expenses();

	// Load financial accounts for expense type dropdown
	load_financial_accounts_expense();

	// Edit expense
	$(document).on('click', '.edit_expenseInfo', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    let modal = $('#edit_expense');

	    let data = await get_expense(id);
	    console.log(data)
	    if(data) {
	    	let res = JSON.parse(data)[0];
	    	console.log(res)
	    	$(modal).find('#expense_id').val(id);
	    	$(modal).find('#fn_account_id_edit').val(res.fn_account_id);
	    	$(modal).find('#bank_id_edit').val(res.bank_id);
	    	$(modal).find('#amount_edit').val(res.amount);
	    	$(modal).find('#payee_payer_edit').val(res.payee_payer);
	    	$(modal).find('#paid_date_edit').val(res.paid_date);
	    	$(modal).find('#ref_number_edit').val(res.ref_number);
	    	$(modal).find('#description_edit').val(res.description);
	    	$(modal).find('#status_edit').val(res.status);
	    }

	    $(modal).modal('show');
	});

	// Edit expense info form
	$('#editExpenseForm').on('submit', (e) => {
		handle_editExpenseForm(e.target);
		return false
	})

	// Delete expense
	$(document).on('click', '.delete_expense', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    swal({
	        title: "Are you sure?",
	        text: `You are going to delete this expense.`,
	        icon: "warning",
	        className: 'warning-swal',
	        buttons: ["Cancel", "Yes, delete"],
	    }).then(async (confirm) => {
	        if (confirm) {
	            let data = { id: id };
	            try {
	                let response = await send_financePost('delete expense', data);
	                console.log(response)
	                if (response) {
	                    let res = JSON.parse(response);
	                    if (res.error) {
	                        toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
	                    } else {
	                        toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
	                            location.reload();
	                            // load_expenses();
	                        });
	                        console.log(res);
	                    }
	                } else {
	                    console.log('Failed to delete expense.' + response);
	                }

	            } catch (err) {
	                console.error('Error occurred during form submission:', err);
	            }
	        }
	    });
	});
}

async function handle_addExpenseForm(form) {
    clearErrors();
    let error = validateForm(form)

    let fn_account_id 	= $(form).find('#slcFinancialAccount').val();
    let bank_id 		= $(form).find('#slcBank').val();
    let amount 			= $(form).find('#amount').val();
    let payee_payer 	= $(form).find('#paidTo').val();
    let paid_date 		= $(form).find('#paidDate').val();
    let ref_number 		= $(form).find('#refNumber').val();
    let description 	= $(form).find('#description').val();

    if (error) return false;

    let formData = {
        fn_account_id: fn_account_id,
        bank_id: bank_id,
        amount: amount,
        payee_payer: payee_payer,
        paid_date: paid_date,
        ref_number: ref_number,
        description: description,
    };

    form_loading(form);
	console.log(formData)
	// return false;

    try {
        let response = await send_financePost('save expense', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#add_expense').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
            		form_loadingUndo(form);
            		location.reload();
            		// load_expenses();
            	});
            	console.log(res)
            }
        } else {
            console.log('Failed to save expense.' + response);
        }

    } catch (err) {
        console.error('Error occurred during form submission:', err);
    }
}

async function handle_editExpenseForm(form) {
    clearErrors();
    let error = validateForm(form)

    console.log(form)

    let id 				= $(form).find('#expense_id').val();
   	let fn_account_id 	= $(form).find('#fn_account_id_edit').val();
    let bank_id 		= $(form).find('#bank_id_edit').val();
    let amount 			= $(form).find('#amount_edit').val();
    let payee_payer 	= $(form).find('#payee_payer_edit').val();
    let paid_date 		= $(form).find('#paid_date_edit').val();
    let ref_number 		= $(form).find('#ref_number_edit').val();
    let description 	= $(form).find('#description_edit').val();
    let status 			= $(form).find('#status_edit').val();

    if (error) return false;

    let formData = {
    	id: id,
        fn_account_id: fn_account_id,
        bank_id: bank_id,
        amount: amount,
        payee_payer: payee_payer,
        paid_date: paid_date,
        ref_number: ref_number,
        description: description,
        status: status,
    };

    form_loading(form);

    try {
        let response = await send_financePost('update expense', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#edit_expense').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
            		form_loadingUndo(form);
            		location.reload();
            		// load_expenses();
            	});
            	console.log(res)
            }
        } else {
            console.log('Failed to update expense.' + response);
        }

    } catch (err) {
        console.error('Error occurred during form submission:', err);
    }
}

async function load_financial_accounts_expense() {
    try {
        let response = await send_financePost('get financial_accounts_expense', {});
        if (response) {
            $('#fn_account_id').html(response);
            $('#fn_account_id_edit').html(response);
        }
    } catch (err) {
        console.error('Error loading financial accounts:', err);
    }
}

async function get_expense(id) {
	let data = {id};
	let response = await send_financePost('get expense', data);
	return response;
}

// Banks
function load_banks() {
	var datatable = $('#banksDT').DataTable({
		// let datatable = new DataTable('#companyDT', {
	    "processing": true,
	    "serverSide": true,
	    "bDestroy": true,
	    "searching": false,  
	    "info": false,
	    "columnDefs": [
	        { "orderable": false, "searchable": false, "targets": [4] }  // Disable search on first and last columns
	    ],
	    "serverMethod": 'post',
	    "ajax": {
	        "url": "./app/finance_controller.php?action=load&endpoint=bank_accounts",
	        "method": "POST",
		    // dataFilter: function(data) {
			// 	console.log(data)
			// }
	    },
	    
	    "createdRow": function(row, data, dataIndex) { 
	    	// Add your custom class to the row 
	    	$(row).addClass('table-row ' +data.status.toLowerCase());
	    },
	    columns: [
	        { title: `Bank name`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.bank_name}</span>
	                </div>`;
	        }},

	        { title: `Account number`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.account}</span>
	                </div>`;
	        }},

	        { title: `Current balance`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${formatMoney(row.balance)}</span>
	                </div>`;
	        }},

	        { title: `Status`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.status}</span>
	                </div>`;
	        }},

	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
            		<span data-recid="${row.id}" class="fa edit_bankInfo smt-5 cursor smr-10 fa-pencil"></span>
            		<span data-recid="${row.id}" class="fa delete_bank smt-5 cursor fa-trash"></span>
                </div>`;
	        }},
	    ]
	});

	return false;
}

function handleBanks() {
	$('#addBankForm').on('submit', (e) => {
		handle_addBankForm(e.target);
		return false
	})

	load_banks();

	// Edit location
	$(document).on('click', '.edit_bankInfo', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    let modal = $('#edit_bank');

	    let data = await get_bank_account(id);
	    console.log(data)
	    if(data) {
	    	let res = JSON.parse(data)[0];
	    	console.log(res)
	    	$(modal).find('#bank_account_id').val(id);
	    	$(modal).find('#bankName4Edit').val(res.bank_name);
	    	$(modal).find('#account4Edit').val(res.account);
	    	$(modal).find('#balance4Edit').val(res.balance) ;
	    	$(modal).find('#slcStatus').val(res.status);
	    }

	    $(modal).modal('show');
	});

	// Edit location info form
	$('#editBankForm').on('submit', (e) => {
		handle_editBankForm(e.target);
		return false
	})

	// Delete location
	$(document).on('click', '.delete_bank', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    swal({
	        title: "Are you sure?",
	        text: `You are going to delete this bank account.`,
	        icon: "warning",
	        className: 'warning-swal',
	        buttons: ["Cancel", "Yes, delete"],
	    }).then(async (confirm) => {
	        if (confirm) {
	            let data = { id: id };
	            try {
	                let response = await send_financePost('delete bank_account', data);
	                console.log(response)
	                if (response) {
	                    let res = JSON.parse(response);
	                    if (res.error) {
	                        toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
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
}

async function handle_addBankForm(form) {
    clearErrors();
    let error = validateForm(form)

    let name 	= $(form).find('#bankName').val();
    let account 	= $(form).find('#account').val();
    let balance 	= $(form).find('#balance').val();

    if (error) return false;

    let formData = {
        name: name,
        account: account,
        balance: balance,
    };

    form_loading(form);

    try {
        let response = await send_financePost('save bank_account', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#add_bank').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
            		form_loadingUndo(form);
            		location.reload();
            		// load_banks();
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

async function handle_editBankForm(form) {
    clearErrors();
    let error = validateForm(form)

    console.log(form)

    let id 	= $(form).find('#bank_account_id').val();
   	let name 	= $(form).find('#bankName4Edit').val();
    let account 	= $(form).find('#account4Edit').val();
    let balance 	= $(form).find('#balance4Edit').val();
    let slcStatus 	= $(form).find('#slcStatus').val();

    if (error) return false;

    let formData = {
    	id:id,
        name: name,
        account: account,
        balance: balance,
        slcStatus:slcStatus
    };

    form_loading(form);

    try {
        let response = await send_financePost('update bank_account', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#edit_bank').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
            		form_loadingUndo(form);
            		location.reload();
            		// load_banks();
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

async function get_bank_account(id) {
	let data = {id};
	let response = await send_financePost('get bank_account', data);
	return response;
}