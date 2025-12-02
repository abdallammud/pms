async function send_attendancePost(str, data) {
    let [action, endpoint] = str.split(' ');

    try {
        const response = await $.post(`${base_url}/app/atten_controller.php?action=${action}&endpoint=${endpoint}`, data);
        return response;
    } catch (error) {
        console.error('Error occurred during the request:', error);
        return null;
    }
}
// Leave types
function load_leave_types() {
	var datatable = $('#leaveTypesDT').DataTable({
		// let datatable = new DataTable('#companyDT', {
	    "processing": true,
	    "serverSide": true,
	    "bDestroy": true,
	    "searching": false,  
	    "info": false,
	    "columnDefs": [
	        { "orderable": false, "searchable": false,  "targets": [2] }  // Disable search on first and last columns
	    ],
	    "serverMethod": 'post',
	    "ajax": {
	        "url": "./app/atten_controller.php?action=load&endpoint=leave_types",
	        "method": "POST",
		    /*dataFilter: function(data) {
				console.log(data)
			}*/
	    },
	    
	    "createdRow": function(row, data, dataIndex) { 
	    	// Add your custom class to the row 
	    	$(row).addClass('table-row ' +data.status.toLowerCase());
	    },
	    columns: [
	        { title: `Type Name`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.name}</span>
	                </div>`;
	        }},

	        { title: `Paid Type`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.paid_type}</span>
	                </div>`;
	        }},

	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
            		<span data-recid="${row.id}" class="fa edit_leaveTypeInfo smt-5 cursor smr-10 fa-pencil"></span>
            		<span data-recid="${row.id}" class="fa delete_leaveType smt-5 cursor fa-trash"></span>
                </div>`;
	        }},
	    ]
	});

	return false;
}

function handleLeaveMgt() {
	$('#addLeaveTypeForm').on('submit', (e) => {
		handle_addLeaveTypeForm(e.target);
		return false
	})

	load_leave_types();

	// Edit location
	$(document).on('click', '.edit_leaveTypeInfo', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    let modal = $('#edit_leaveType');

	    let data = await get_leaveType(id);
	    console.log(data)
	    if(data) {
	    	let res = JSON.parse(data)[0];
	    	console.log(res)
	    	$(modal).find('#leave_typeID').val(id);
	    	$(modal).find('#leave_typeName4Edit').val(res.name);
	    	$(modal).find('#slcPaidType4Edit').val(res.paid_type);
	    	$(modal).find('#slcStatus').val(res.status);
	    }

	    $(modal).modal('show');
	});

	// Edit location info form
	$('#editLeaveTypeForm').on('submit', (e) => {
		handle_editLeaveTypeForm(e.target);
		return false
	})

	// Delete location
	$(document).on('click', '.delete_leaveType', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    swal({
	        title: "Are you sure?",
	        text: `You are going to delete this leave type.`,
	        icon: "warning",
	        className: 'warning-swal',
	        buttons: ["Cancel", "Yes, delete"],
	    }).then(async (confirm) => {
	        if (confirm) {
	            let data = { id: id };
	            try {
	                let response = await send_attendancePost('delete leave_type', data);
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
}

async function handle_addLeaveTypeForm(form) {
    clearErrors();
    let error = validateForm(form)

    let name 	= $(form).find('#leave_typeName').val();
    let paid_type = $(form).find('#slcPaidType').val();

    if (error) return false;

    let formData = {
        name: name,
        paid_type:paid_type
    };

    form_loading(form);

    try {
        let response = await send_attendancePost('save leave_type', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#add_budgetCode').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 3000 }).then(() => {
            		location.reload();
            	});;
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
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

async function handle_editLeaveTypeForm(form) {
    clearErrors();
    let error = validateForm(form)

    console.log(form)

    let id 	= $(form).find('#leave_typeID').val();
   	let name 	= $(form).find('#leave_typeName4Edit').val();
    let paid_type = $(form).find('#slcPaidType4Edit').val();
    let slcStatus 	= $(form).find('#slcStatus').val();

    if (error) return false;

    let formData = {
    	id:id,
        name: name,
        paid_type: paid_type,
        slcStatus:slcStatus
    };

    form_loading(form);
    try {
        let response = await send_attendancePost('update leave_type', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#edit_budgetCode').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 3000 }).then(() => {
            		location.reload();
            	});;
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
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

async function get_leaveType(id) {
	let data = {id};
	let response = await send_attendancePost('get leave_type', data);
	return response;
}

// Employee leaves
function load_employeeLeave() {
	function set_actions(row) {
		let actions = '';
		if(row.status.toLowerCase() != 'approved') {
			actions += `<span data-recid="${row.id}" class="fa delete_empLeave smt-5 cursor fa-trash"></span>`
		}

		return actions;
	}
	var datatable = $('#empLeaveDT').DataTable({
		// let datatable = new DataTable('#companyDT', {
	    "processing": true,
	    "serverSide": true,
	    "bDestroy": true,
	    // "searching": false,  
	    "info": false,
	    "columnDefs": [
	        { "orderable": false, "searchable": false,  "targets": [4] }  // Disable search on first and last columns
	    ],
	    "serverMethod": 'post',
	    "ajax": {
	        "url": "./app/atten_controller.php?action=load&endpoint=emp_leaves",
	        "method": "POST",
		    /*dataFilter: function(data) {
				console.log(data)
			}*/
	    },
	    
	    "createdRow": function(row, data, dataIndex) { 
	    	// Add your custom class to the row 
	    	$(row).addClass('table-row ' +data.status.toLowerCase());
	    },
	    columns: [

	    	{ title: `Staff No. `, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.staff_no}</span>
	                </div>`;
	        }},

	        { title: `Full name `, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.full_name}</span>
	                </div>`;
	        }},

	        { title: `Leave type`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.name} - ${row.paid_type}</span>
	                </div>`;
	        }},

	        { title: `Dates`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${formatDateRange(row.date_from, row.date_to)}</span>
	                </div>`;
	        }},

	        { title: `Days`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.days_num} </span>
	                </div>`;
	        }},

	        { title: `Status`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.status}</span>
	                </div>`;
	        }},

	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
            		<span data-recid="${row.id}" class="fa edit_empLeaveInfo smt-5 cursor smr-10 fa-pencil"></span>
            		${set_actions(row)}
                </div>`;
	        }},
	    ]
	});

	return false;
}

function handleEmpLeave() {
	$('#addEmpLeaveForm').on('submit', (e) => {
		handle_addEmpLeaveForm(e.target);
		return false
	})

	load_employeeLeave();

	// Edit location
	$(document).on('click', '.edit_empLeaveInfo', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    let modal = $('#edit_employeeLeave');

	    let data = await get_empLeave(id);
	    console.log(data)
	    if(data) {
	    	let res = JSON.parse(data);
	    	console.log(res)
	    	$(modal).find('#emp_leaveID').val(id);
	    	$(modal).find('#searchEmployee4Edit').val(res.full_name);
	    	$(modal).find('#slcLeaveType4Edit').val(res.leave_id);
	    	$(modal).find('#dateFrom4Edit').val(res.date_from);
	    	$(modal).find('#dateTo4Edit').val(res.date_to);
	    	$(modal).find('#slcStatus').val(res.status);
	    	if (res.status == 'Approved') {
			    $(modal).find('#slcStatus option').each(function() {
			        if ($(this).val() !== 'Approved' && $(this).val() !== 'Cancelled') {
			            $(this).css('display', 'none');
			        }
			    });
			}

	    }

	    $(modal).modal('show');
	});

	// Edit location info form
	$('#editEmpLeaveForm').on('submit', (e) => {
		handle_editEmpLeaveForm(e.target);
		return false
	})

	// Delete location
	$(document).on('click', '.delete_empLeave', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    swal({
	        title: "Are you sure?",
	        text: `You are going to delete this employee leave record.`,
	        icon: "warning",
	        className: 'warning-swal',
	        buttons: ["Cancel", "Yes, delete"],
	    }).then(async (confirm) => {
	        if (confirm) {
	            let data = { id: id };
	            try {
	                let response = await send_attendancePost('delete emp_leave', data);
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

async function handle_addEmpLeaveForm(form) {
    clearErrors();
    let error = validateForm(form)

    let employee_id = $(form).find('#searchEmployee').val();
    let leaveType = $(form).find('#slcLeaveType').val();

    let dateFrom = $(form).find('#dateFrom').val();
    let dateTo = $(form).find('#dateTo').val();

    if(!employee_id) {
    	swal('Sorry', 'Please select employee', 'error')
    	return false;
    }

    if (error) return false;

    let formData = {
        emp_id: employee_id,
        leave_id:leaveType,
        date_from:dateFrom,
        date_to:dateTo
    };

    form_loading(form);

    try {
        let response = await send_attendancePost('save employee_leave', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#add_budgetCode').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
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

async function handle_editEmpLeaveForm(form) {
    clearErrors();
    let error = validateForm(form)

    console.log(form)

    let id 	= $(form).find('#emp_leaveID').val();
   	let leaveType 	= $(form).find('#slcLeaveType4Edit').val();
    let dateFrom 	= $(form).find('#dateFrom4Edit').val();
    let dateTo 		= $(form).find('#dateTo4Edit').val();
    let slcStatus 	= $(form).find('#slcStatus').val();

    if (error) return false;

    let formData = {
    	id:id,
        leave_id:leaveType,
        date_from:dateFrom,
        date_to:dateTo,
        status:slcStatus
    };

    form_loading(form);
    try {
        let response = await send_attendancePost('update emp_leave', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#edit_budgetCode').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
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

async function get_empLeave(id) {
	let data = {id};
	let response = await send_attendancePost('get emp_leave', data);
	return response;
}




//Attendance
function load_attendance() {
	
	var datatable = $('#attendanceDT').DataTable({
		// let datatable = new DataTable('#companyDT', {
	    "processing": true,
	    "serverSide": true,
	    "bDestroy": true,
	    // "searching": false,  
	    "info": false,
	    "columnDefs": [
	        { "orderable": false, "searchable": false,  "targets": [2, 3] }  // Disable search on first and last columns
	    ],
	    "serverMethod": 'post',
	    "ajax": {
	        "url": "./app/atten_controller.php?action=load&endpoint=attendance",
	        "method": "POST",
		    /*dataFilter: function(data) {
				console.log(data)
			}*/
	    },
	    
	    "createdRow": function(row, data, dataIndex) { 
	    	// Add your custom class to the row 
	    	// $(row).addClass('table-row ' +data.status.toLowerCase());
	    },
	    columns: [

	    	/* { title: `Attendance for`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.ref} - ${row.ref_name}</span>
	                </div>`;
	        }}, */

	        { title: `Attendance date `, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${formatDate(row.atten_date)}</span>
	                </div>`;
	        }},

	        { title: `Employees `, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.employee_count} employees</span>
	                </div>`;
	        }},
	      
	        { title: `Date added`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${formatDate(row.added_date)}</span>
	                </div>`;
	        }},

	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
            		<span data-recid="${row.id}" class="fa edit_attendanceInfo smt-5 cursor smr-10 fa-pencil"></span>
            		<span data-recid="${row.id}" class="fa delete_attendance smt-5 cursor fa-trash"></span>
                </div>`;
	        }},
	    ]
	});

	return false;
}

function handleAttendance() {
	$('#addAttendanceForm').on('submit', (e) => {
		handle_addAttendanceForm(e.target);
		return false
	})

	load_attendance();

	// Attendance for change
	$(document).on('change', '.slcAttenFor', (e) => {
		let attenFor = $(e.target).val();
		$.post(`./app/atten_controller.php?action=search&endpoint=atten_for`, {attenFor:attenFor}, function(data) {
			// console.log(data)
			let res = JSON.parse(data);
			if(!res.error) {
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
	})

	// Edit location
	$(document).on('click', '.edit_attendanceInfo', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    let modal = $('#edit_attendance');

	    let data = await get_4editAttendance(id);
	    console.log(data)
	    if(data) {
	    	let res = JSON.parse(data);
	    	console.log(res)
	    	$(modal).find('#attendance_id').val(id);
	    	$(modal).find('#slcAttenFor4Edit').html('')
	    	$(modal).find('#slcAttenFor4Edit').html(`<option value="${res.ref}">${res.ref}</option>`)
	    	$(modal).find('#ref_name').val(res.ref_name);
	    	$(modal).find('#attendDate4Edit').val(res.atten_date);

	    	$('tbody.employeesData').html(res.employees);
	    	
	    	var datatable = $('#attendanceEmployee').DataTable({
				// let datatable = new DataTable('#companyDT', {
			    "bDestroy": true,
			    // "searching": false,  
			    "info": false,
			    "pageLength": 500,
			})
	    }

	    $(modal).modal('show');
	});

	// Edit location info form
	$('#editAttendanceForm').on('submit', (e) => {
		handle_editAttendanceForm(e.target);
		return false
	})

	// Delete location
	$(document).on('click', '.delete_attendance', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    swal({
	        title: "Are you sure?",
	        text: `You are going to delete this attendance  record.`,
	        icon: "warning",
	        className: 'warning-swal',
	        buttons: ["Cancel", "Yes, delete"],
	    }).then(async (confirm) => {
	        if (confirm) {
	            let data = { id: id };
	            try {
	                let response = await send_attendancePost('delete attendance', data);
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
	$('#downloadAttendanceUploadForm').on('submit', (e) => {
		handle_downloadAttendanceUploadForm(e.target);
		return false
	})

	// Upload attendance
	$('#attendance_uploadForm').on('submit', (e) => {
		handle_upload_attendanceForm(e.target);
		return false
	})
}

async function handle_addAttendanceForm(form) {
    clearErrors();
    let error = validateForm(form)
    let ref_id, ref_name, err_mgs = '';
    let attenFor = $('#slcAttenFor').val();
    if(attenFor == 'Employee') {
    	ref_id = $(form).find('#searchEmployee').val();
    	ref_name = $(form).find('#searchEmployee option:selected').text();
    	ref_name = ref_name.split(',')[0]
    	err_mgs = 'employee';
    } else if(attenFor == 'Department') {
    	ref_id = $(form).find('#searchDepartment').val();
    	ref_name = $(form).find('#searchDepartment option:selected').text();
    	err_mgs = 'department';
    } else if(attenFor == 'Location') {
    	ref_id = $(form).find('#searchLocation').val();
    	ref_name = $(form).find('#searchLocation option:selected').text();
    	err_mgs = 'location';
    }


    let attendDate = $(form).find('#attendDate').val();
    let attenStatus = $(form).find('#attenStatus').val();

    if(!ref_id) {
    	swal('Sorry', `Please select ${err_mgs}`, 'error')
    	return false;
    }

    if (error) return false;

    let formData = {
    	ref:attenFor,
        ref_id: ref_id,
        ref_name: ref_name,
        atten_date:attendDate,
        atten_status:attenStatus,
    };

    form_loading(form);

    try {
        let response = await send_attendancePost('save attendance', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#add_budgetCode').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 3000 }).then(() => {
            		location.reload();
            	});
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
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

async function handle_editAttendanceForm(form) {
    clearErrors();
    console.log(form)

    let id 	= $(form).find('#attendance_id').val();
   	let attendDate 	= $(form).find('#attendDate4Edit').val();
   	let emp_id = [];
   	let attend_status = [];

   	$(form).find('tbody.employeesData').find('tr').each((i, el) => {
   		if($(el)) {
   			let emp = $(el).find('input.employee_id').val();
   			let status = $(el).find('input.statusBTN:checked').val();

   			emp_id.push(emp)
   			attend_status.push(status)

   		}
   	})

   	if(emp_id.length < 1 ||  attend_status.length < 1 ) {
   		swal('Ooops', 'There are no employees in this record.', 'error')
   		return false;
   	}

    let formData = {
    	id:id,
        atten_date:attendDate,
        emp_id:emp_id,
        attend_status:attend_status
    };

    form_loading(form);
    try {
        let response = await send_attendancePost('update attendance', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#edit_budgetCode').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 3000 }).then(() => {
            		location.reload();
            	});;
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
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

async function get_4editAttendance(id) {
	let data = {id};
	let response = await send_attendancePost('get 4editAttendance', data);
	return response;
}

async function handle_downloadAttendanceUploadForm(form) {
    clearErrors();
    let error = validateForm(form)
    let ref_id, ref_name, err_mgs = '';
    let attenFor = $(form).find('#slcAttenFor').val();
    console.log(attenFor)

    if(attenFor == 'Department') {
    	ref_id = $(form).find('#searchDepartment').val();
    	ref_name = $(form).find('#searchDepartment option:selected').text();
    	err_mgs = 'department';
    } else if(attenFor == 'Location') {
    	ref_id = $(form).find('#searchLocation').val();
    	ref_name = $(form).find('#searchLocation option:selected').text();
    	err_mgs = 'location';
    }

    let attendDate = $(form).find('#attendDate').val();

    if(!ref_id) {
    	swal('Sorry', `Please select ${err_mgs}`, 'error')
    	return false;
    }

    if (error) return false;

    let formData = {
    	ref:attenFor,
        ref_id: ref_id,
        ref_name: ref_name,
        atten_date:attendDate,
    };

    // form_loading(form);

    try {
        let response = await send_attendancePost('get downloadAttendanceCSV', formData);
        // console.log(response)
        // return false;
        if (response) {
            let res = JSON.parse(response)
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 3000 }).then(() => {
            		location.reload();
            	});
            } else {
            	downloadCSV(res.data, filename = `Attendance upload file on ${formatDate(attendDate)}.csv`);
				toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
            		let modal = $('#download_attendanceUploadFile');
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

async function handle_upload_attendanceForm(form) {
	let fileInput = $(form).find('#attendance_uploadInput');
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
	ajax.addEventListener("load", function(event) {
		console.log(event.target.response)
		let res = JSON.parse(event.target.response)
		if(res.error) {
			toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
			return false;
		} else if(res.errors) {
			swal('Sorry', `${res.errors} \n`, 'error');
			return false;
		} else {
			toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 2000 }).then(() => {
                location.reload();
            });
		}
	});
	
	ajax.open("POST", `${base_url}/app/atten_controller.php?action=save&endpoint=upload_attendance`);
	ajax.send(formData);
}

// Bulk attendance
function handleBulkAttendance() {
	$('#add_bulkAttendanceForm').on('submit', (e) => {
		handle_add_bulkAttendanceForm(e.target);
		return false
	})

	$('#add_bulkTimesheetForm').on('submit', (e) => {
		handle_add_bulkTimesheetForm(e.target);
		return false
	})
}
function simplifyAddAttendanceTable(timesheet = '') {
	$(document).on('change', '.slcStatus4Atten', (e) => {
		let status = $(e.target).val();
		let row = $(e.target).parents('tr');
		// console.log(row)
		const $presentCheckboxes = $('.form-check-input.isPresent:not(:disabled)');
		if(status) {
			// $(row).find('.isPresent').prop('checked', true)
			// $(row).find('.isPresent').attr('checked', true)

			$presentCheckboxes.prop('checked', true);
			$presentCheckboxes.attr('checked', true);
		} else {
			// $(row).find('.isPresent').prop('checked', false)
			// $(row).find('.isPresent').attr('checked', false)

			$presentCheckboxes.prop('checked', false);
			$presentCheckboxes.attr('checked', false);
		}
	})

	$(document).on('change', '.isPresent', (e) => {
		let row = $(e.target).closest('tr'); 
		if ($(e.target).is(':checked')) { 
			row.find('.slcStatus4Atten').val('P');
		} else {
			row.find('.slcStatus4Atten').val(''); 
		}
	});

	$('.selectAll').on('change', (e) => {
		const isChecked = $(e.target).is(':checked'); // Get the checked state of the 'selectAll' checkbox
		const $presentCheckboxes = $('.form-check-input.isPresent:not(:disabled)');
		$presentCheckboxes.prop('checked', isChecked);
		$presentCheckboxes.attr('checked', isChecked);
		$presentCheckboxes.trigger('change');
	});

	$('.slcDepartment4Atten, .slcLocation4Atten, .attendDate').on('change', (e) => {
		let dep = $(e.target).val();
		get_data4Attendance(timesheet);
	})

	let table = $('#attendanceTable').DataTable({ 
		"paging": true,         
		"ordering": true,      
		"info": true,           
		"searching": true,       
		"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]], 
		"displayLength": -1,
		"language": {
			"paginate": {
				"previous": "&lt;", // Use < instead of <
				"next": "&gt;"    // Use > instead of >
			}
		}
	});
	
}

function get_data4Attendance(timesheet = '') {
	let department = $('.slcDepartment4Atten').val();
	let location = $('.slcLocation4Atten').val();
	let date = $('.attendDate').val();

	let data = {
		department:department,
		location:location,
		date:date,
		timesheet:timesheet,
	}

	// console.log(data)

	$.post(`${base_url}/app/atten_controller.php?action=get&endpoint=data4BulkAttendance`, {department:department, location:location, timesheet:timesheet, date:date}, (data) => {
		// console.log(data)
		if ( $.fn.DataTable.isDataTable('#attendanceTable') ) {
			$('#attendanceTable').DataTable().destroy();
		}
		$('#attendanceTable tbody').empty();
		$('#attendanceTable tbody').html(data)
		reInitDataTable();

		$('.slcStatus4Atten').trigger('change')
	});
}

function reInitDataTable() {
	table = $('#attendanceTable').DataTable({
		"paging": true,
		"ordering": true,
		"info": true,
		"searching": true,
		"lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
		"displayLength": -1,
		"language": {
			"paginate": {
				"previous": "&lt;",
				"next": "&gt;"
			}
		}
	});

	$(document).find('.slcStatus4Atten').trigger('change')
}
        

async function handle_add_bulkAttendanceForm(form) {
	let employees   = [];
	let statuses      = [];
	let department  = $(form).find('.slcDepartment4Atten').val();
	let location    = $(form).find('.slcLocation4Atten').val();
	let date        = $(form).find('.attendDate').val();

	$(form).find('.isPresent:checked').each((i, el) => {
		
		let row = $(el).parents('tr');
		let employee_id = $(row).find('.employee_id').val();
		let status      = $(row).find('.slcStatus4Atten').val();

		console.log(el)
		console.log(employee_id)
		console.log(status)

		if(status) {
			employees.push(employee_id);
			statuses.push(status)
		}
	});

	/* $(form).find('tbody tr').each((i, row) => {
		let employee_id = $(row).find('.employee_id').val();
		let status      = $(row).find('.slcStatus4Atten').val();
		if(status) {
			employees.push(employee_id);
			statuses.push(status)
		}
	}) */

	if(employees.length < 1 || statuses.length < 1) {
		toaster.warning("Please select attendance status.", 'Sorry', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
		});
		// swal('Ooops', 'There are no employees in this record.', 'error')
		return false;
	}

	const postData = {
		department: department,
		location: location,
		date: date,
		employees: employees,
		statuses:statuses
	};

	console.log(postData)

	try {
        let response = await send_attendancePost('save bulkAttendance', postData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#edit_budgetCode').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 3000 }).then(() => {
            		window.location.reload();
            	});;
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
            		window.location.reload();
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

async function handle_add_bulkTimesheetForm(form) {
	let employees   = [];
	let time_in      = [];
	let time_out      = [];
	let department  = $(form).find('.slcDepartment4Atten').val();
	let location    = $(form).find('.slcLocation4Atten').val();
	let date        = $(form).find('.attendDate').val();

	$(form).find('.isPresent:checked').each((i, el) => {
		// Only process if the checkbox is not disabled
		if (!$(el).is(':disabled')) {
			const $row = $(el).closest('tr'); // Use .closest() for better robustness
			const employeeId = $row.find('.employee_id').val();
			const timeIn = $row.find('.time_in').val();
			const timeOut = $row.find('.time_out').val();

			employees.push(employeeId);
			time_in.push(timeIn);
			time_out.push(timeOut);
		}
	});


	if(employees.length < 1 || time_in.length < 1) {
		toaster.warning("Please check some records.", 'Sorry', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
		});
		// swal('Ooops', 'There are no employees in this record.', 'error')
		return false;
	}

	const postData = {
		department: department,
		location: location,
		date: date,
		employees: employees,
		time_in:time_in,
		time_out:time_out
	};

	console.log(postData)

	// return false;

	try {
        let response = await send_attendancePost('save bulkTimesheet', postData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#edit_budgetCode').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 3000 }).then(() => {
            		window.location.reload();
            	});;
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
            		window.location.reload();
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



// Resource Allocation
function handleAllocation() {
	load_timesheetAllocation();
}
function simplifyResourceAllocation() {
	$('.searchEmployee').on('change', async function () {
		var emp_id = $('.searchEmployee option:selected').val();
		let month =  $('#rsaMonth').val();
		if(emp_id) {
			// console.log(emp_id);
			let postData = {
				emp_id: emp_id,
				month: month,
			};
			try {
				let response = await send_attendancePost('get employeeData4Allocation', postData);
				// console.log(response)
				$('.employeeData4Allocation').html(response)
				get_prevAllocation(emp_id, month)
			} catch (err) {
				console.error('Error occurred during form submission:', err);
			}
		}
	});

	$('#rsaMonth').on('change', async function () {
		var emp_id = $('.searchEmployee option:selected').val();
		let month =  $('#rsaMonth').val();
		if(emp_id) {
			get_prevAllocation(emp_id, month)
		}
	});

	
	console.log($('#add_allocationForm'))
	$('#add_allocationForm').on('submit', (e) => {
		handle_add_allocationForm(e.target);
		return false
	})
}

async function handle_add_allocationForm(form) {
	var employee_id = $(form).find('#searchEmployee').val();
	var supervisor_id = $(form).find('#searchSupervisor').val();
	let month 		= $(form).find('#rsaMonth').val();
	let prevMonth 	= $(form).find('#prevMonth').val();

	console.log(form)

	let budegets 		= [];
	let budegetTimes 	= [];
	let projectIds		= [];
	let projects 		= [];
	let projectTimes 	= [];
	let projectsJson 	= [];
	let budgetsJson 	= [];

	$(form).find('.budgetItem.row').each((i, el) => {
		let budgetCode = $(el).find('.slcBudget').val();
		let budgetTime = $(el).find('.budgetTime').val();
		if(budgetCode) {
			budegets.push(budgetCode);
			budegetTimes.push(budgetTime);
			budgetsJson.push({name:budgetCode, time:budgetTime})
		}
	})

	$(form).find('.projectItem.row').each((i, el) => {
		let projectId = $(el).find('.slcProject').val();
		let project = $(el).find('.slcProject option:selected').text();
		let projectTime = $(el).find('.projectTime').val();
		if(projectId) {
			projectIds.push(projectId);
			projects.push(project);
			projectTimes.push(projectTime);
			projectsJson.push({id:projectId, name:project, time:projectTime})
		}
	})

	if(!employee_id) {
		swal('Ooops', 'Please select employee', 'error')
		return false;
	}
	if(!supervisor_id) {
		swal('Ooops', 'Please select supervisor', 'error')
		return false;
	}
	

	if(budegets.length < 1 && projects.length < 1) {
		toaster.warning("Please provide budget or project.", 'Sorry', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
		});
		// swal('Ooops', 'There are no employees in this record.', 'error')
		return false;
	}

	let allocation = {
		budgets: budgetsJson,
		projects: projectsJson,
	};

	const postData = {
		employee_id: employee_id,
		supervisor_id: supervisor_id,
		allocation: allocation,
		month: month,
		prevMonth: prevMonth,
	};

	console.log(postData)

	try {
        let response = await send_attendancePost('save allocation', postData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#edit_budgetCode').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 3000 }).then(() => {
            		window.location.reload();
            	});;
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
            		window.location.href = base_url + '/allocation';
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

function load_timesheetAllocation() {
	var datatable = $('#allocationDT').DataTable({
		// let datatable = new DataTable('#companyDT', {
	    "processing": true,
	    "serverSide": true,
	    "bDestroy": true,
	    // "searching": false,  
	    "info": false,
	    "columnDefs": [
	        { "orderable": false, "searchable": false,  "targets": [6, 5] }  // Disable search on first and last columns
	    ],
	    "serverMethod": 'post',
	    "ajax": {
	        "url": "./app/atten_controller.php?action=load&endpoint=allocations",
	        "method": "POST",
		    // dataFilter: function(data) {
			// 	console.log(data)
			// }
	    },
	    
	    "createdRow": function(row, data, dataIndex) { 
	    	// Add your custom class to the row 
	    	// $(row).addClass('table-row ' +data.status.toLowerCase());
	    },
	    columns: [

	    	{ title: `Month`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${(row.month)}</span>
	                </div>`;
	        }},

			{ title: `Employee ID`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.staff_no} </span>
	                </div>`;
	        }},
	      

	        { title: `Employee `, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.full_name} </span>
	                </div>`;
	        }},

			{ title: `Supervisor`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.sup_name} </span>
	                </div>`;
	        }},

			{ title: `Time Accounted for`, data: null, render: function(data, type, row) {
	            return `<div>
					<span>${row.time}% </span>
				</div>`;
	        }},
	      
	        { title: `Date added`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${formatDate(row.added_date)}</span>
	                </div>`;
	        }},

	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
            		<a href="${base_url}/allocation/edit/${row.id}" class="fa  smt-5 cursor smr-10 fa-pencil"></a>
            		<span onclick="return delete_allocation(${row.id})" class="fa smt-5 cursor fa-trash"></span>
                </div>`;
	        }},
	    ]
	});

	return false;
}

async function get_prevAllocation(employee_id, month) {
	let data = {employee_id:employee_id, month:month};
	let response = await send_attendancePost('get prev_allocation', data);
	if(response) {
		let allocation = JSON.parse(response);

		setTimeout(() => {
			let budgetNum = 1;
			allocation.budgets.forEach((budget) => { 
				// console.log(budget)
				const trimmedBudgetName = budget.name.trim(); 
				// console.log(`Processing budget: ${trimmedBudgetName}`);
				const budgetItem = $(document).find(`.budgetItem${budgetNum}`);
				if (budgetItem.length > 0) { 
					const selectElement = budgetItem.find('.slcBudget');
					const timeInput = budgetItem.find('.budgetTime');
					selectElement.val(trimmedBudgetName); 
					timeInput.val(budget.time)
				} else {
				}

				budgetNum++;
			});

			let projectNum = 1;
			allocation.projects.forEach((project) => { 
				// console.log(project)
				const projectId = project.id; 
				// console.log(`Processing project: ${projectId}`);
				const projectItem = $(document).find(`.projectItem${projectNum}`);
				if (projectItem.length > 0) { 
					const selectElement = projectItem.find('.slcProject');
					const timeInput = projectItem.find('.projectTime');
					selectElement.val(projectId); 
					timeInput.val(project.time)
				} else {
				}

				projectNum++;
			});
		}, 500)
	} else {
		$('.slcBudget').val('')
		$('.budgetTime').val('')
		$('.slcProject').val('')
		$('.projectTime').val('')
	}
}

function delete_allocation(id) {
	swal({
        title: "Are you sure?",
        text: `You are going to delete this allocation record.`,
        icon: "warning",
        className: 'warning-swal',
        buttons: ["Cancel", "Yes, delete"],
    }).then(async (confirm) => {
        if (confirm) {
            let data = { id: id };
            try {
                let response = await send_attendancePost('delete allocation', data);
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
    })
}

















//Timesheet
function load_timesheet() {
	var datatable = $('#timesheetDT').DataTable({
		// let datatable = new DataTable('#companyDT', {
	    "processing": true,
	    "serverSide": true,
	    "bDestroy": true,
	    // "searching": false,  
	    "info": false,
	    "columnDefs": [
	        { "orderable": false, "searchable": false,  "targets": [1, 3] }  // Disable search on first and last columns
	    ],
	    "serverMethod": 'post',
	    "ajax": {
	        "url": "./app/atten_controller.php?action=load&endpoint=timesheet",
	        "method": "POST",
		    /*dataFilter: function(data) {
				console.log(data)
			}*/
	    },
	    
	    "createdRow": function(row, data, dataIndex) { 
	    	// Add your custom class to the row 
	    	// $(row).addClass('table-row ' +data.status.toLowerCase());
	    },
	    columns: [

	    	{ title: `Date`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${formatDate(row.ts_date)}</span>
	                </div>`;
	        }},

	        { title: `Employees `, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.employee_count} employees</span>
	                </div>`;
	        }},
	      
	        { title: `Date added`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${formatDate(row.added_date)}</span>
	                </div>`;
	        }},

	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
            		<span data-recid="${row.id}" class="fa edit_timesheetInfo smt-5 cursor smr-10 fa-pencil"></span>
            		<span data-recid="${row.id}" class="fa delete_timehseet smt-5 cursor fa-trash"></span>
                </div>`;
	        }},
	    ]
	});

	return false;
}

function handleTimesheet() {
	$('#addTimesheetForm').on('submit', (e) => {
		handle_addTimesheetForm(e.target);
		return false
	})

	load_timesheet();

	// Attendance for change
	$(document).on('change', '.slcAttenFor', (e) => {
		let attenFor = $(e.target).val();
		$.post(`./app/atten_controller.php?action=search&endpoint=atten_for`, {attenFor:attenFor}, function(data) {
			// console.log(data)
			let res = JSON.parse(data);
			if(!res.error) {
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
	})

	// Edit location
	$(document).on('click', '.edit_timesheetInfo', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    let modal = $('#edit_timesheet');

	    let data = await get_4editTimesheet(id);
	    console.log(data)
	    if(data) {
	    	let res = JSON.parse(data);
	    	console.log(res)
	    	$(modal).find('#ts_id').val(id);
	    	$(modal).find('#tsDate4Edit').val(res.ts_date);

	    	$('tbody.employeesData').html(res.employees);
	    }

	    $(modal).modal('show');
	});


	// Edit location info form
	$('#editTimesheetForm').on('submit', (e) => {
		handle_editTimesheetForm(e.target);
		return false
	})

	// Delete location
	$(document).on('click', '.delete_timehseet', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    swal({
	        title: "Are you sure?",
	        text: `You are going to delete this timesheet  record.`,
	        icon: "warning",
	        className: 'warning-swal',
	        buttons: ["Cancel", "Yes, delete"],
	    }).then(async (confirm) => {
	        if (confirm) {
	            let data = { id: id };
	            try {
	                let response = await send_attendancePost('delete timesheet', data);
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
	$('#downloadTimesheetUploadForm').on('submit', (e) => {
		handle_downloadTimesheetUploadForm(e.target);
		return false
	})

	// Upload attendance
	$('#timesheet_uploadForm').on('submit', (e) => {
		handle_timesheet_uploadForm(e.target);
		return false
	})
}

async function handle_addTimesheetForm(form) {
    clearErrors();
    let error = validateForm(form)
    let ref_id, ref_name, err_mgs = '';

    emp_id = $(form).find('#searchEmployee').val();
	full_name = $(form).find('#searchEmployee option:selected').text();
	full_name = full_name.split(',')[0]
	err_mgs = 'employee';


    let tsDate = $(form).find('#tsDate').val();
    let timeIn = $(form).find('#timeIn').val();
    let timeOut = $(form).find('#timeOut').val();

    if(!emp_id) {
    	swal('Sorry', `Please select ${err_mgs}`, 'error')
    	return false;
    }

    if (error) return false;

    let formData = {
        emp_id: emp_id,
        ts_date: tsDate,
        time_in:timeIn,
        time_out:timeOut,
    };

    form_loading(form);

    try {
        let response = await send_attendancePost('save timesheet', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#add_budgetCode').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 3000 }).then(() => {
            		location.reload();
            	});
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
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

async function handle_editTimesheetForm(form) {
    clearErrors();
    console.log(form)

    let id 	= $(form).find('#ts_id').val();
   	let tsDate 	= $(form).find('#tsDate4Edit').val();
   	let emp_id 	= [];
   	let tsStatus = [];
   	let delete_status = [];
   	let timeIn 	= [];
   	let timeOut = [];

   	$(form).find('tbody.employeesData').find('tr').each((i, el) => {
   		if($(el)) {
   			let emp 	= $(el).find('input.employee_id').val();
   			let del 	= $(el).find('input.statusBTN:checked').val();
   			let status 	= $(el).find('select.slcTsStatus').val();

   			let tIn 	= $(el).find('input.timeIn').val();
   			let tOut 	= $(el).find('input.timeOut').val();

   			emp_id.push(emp)
   			tsStatus.push(status)
   			delete_status.push(del)
   			timeIn.push(tIn)
   			timeOut.push(tOut)

   		}
   	})

   	if(emp_id.length < 1 ||  tsStatus.length < 1 ) {
   		swal('Ooops', 'There are no employees in this record.', 'error')
   		return false;
   	}

    let formData = {
    	id:id,
        ts_date:tsDate,
        emp_id:emp_id,
        ts_status:tsStatus,
        delete_status:delete_status,
        time_in:timeIn,
        time_out:timeOut
    };

    form_loading(form);
    try {
        let response = await send_attendancePost('update timesheet', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#edit_budgetCode').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 3000 }).then(() => {
            		location.reload();
            	});;
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
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

async function get_4editTimesheet(id) {
	let data = {id};
	let response = await send_attendancePost('get 4editTimesheet', data);
	return response;
}

async function handle_downloadTimesheetUploadForm(form) {
    clearErrors();
    let error = validateForm(form)
    let ref_id, ref_name, err_mgs = '';
    let attenFor = $(form).find('#slcAttenFor').val();
    console.log(attenFor)

    if(attenFor == 'Department') {
    	ref_id = $(form).find('#searchDepartment').val();
    	ref_name = $(form).find('#searchDepartment option:selected').text();
    	err_mgs = 'department';
    } else if(attenFor == 'Location') {
    	ref_id = $(form).find('#searchLocation').val();
    	ref_name = $(form).find('#searchLocation option:selected').text();
    	err_mgs = 'location';
    }

    let tsDate = $(form).find('#tsDate').val();

    if(!ref_id) {
    	swal('Sorry', `Please select ${err_mgs}`, 'error')
    	return false;
    }

    if (error) return false;

    let formData = {
    	ref:attenFor,
        ref_id: ref_id,
        ref_name: ref_name,
        ts_date:tsDate,
    };

    // form_loading(form);

    try {
        let response = await send_attendancePost('get downloadTimesheetCSV', formData);
        console.log(response)
        // return false;
        if (response) {
            let res = JSON.parse(response)
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 3000 }).then(() => {
            		location.reload();
            	});
            } else {
            	downloadCSV(res.data, filename = `Timesheet upload file on ${formatDate(tsDate)}.csv`);
				toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
            		let modal = $('#download_timesheetUploadFile');
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

async function handle_timesheet_uploadForm(form) {
	let fileInput = $(form).find('#timesheet_uploadInput');
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
	ajax.addEventListener("load", function(event) {
		console.log(event.target.response)
		let res = JSON.parse(event.target.response)
		if(res.error) {
			toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
			return false;
		} else if(res.errors) {
			swal('Sorry', `${res.errors} \n`, 'error');
			return false;
		} else {
			toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 2000 }).then(() => {
                location.reload();
            });
		}
	});
	
	ajax.open("POST", `${base_url}/app/atten_controller.php?action=save&endpoint=upload_timesheet`);
	ajax.send(formData);
}


document.addEventListener("DOMContentLoaded", function() {
	handleLeaveMgt();
	handleEmpLeave();
	handleAttendance();
	handleTimesheet();
	handleBulkAttendance();
	handleAllocation();
	

	$('.my-select').selectpicker({
	    noneResultsText: "No results found"
	});

	// Search employee
	$(document).on('keyup', '.bootstrap-select.searchEmployee input.form-control', async (e) => {
    	let search = $(e.target).val();
    	let searchFor = 'leave';
    	let formData = {search:search, searchFor:searchFor}
		if(search) {
			try {
		        let response = await send_attendancePost('search employee4Select', formData);
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

	// Search employee
	$(document).on('keyup', '.bootstrap-select.searchSupervisor input.form-control', async (e) => {
    	let search = $(e.target).val();
    	let searchFor = 'leave';
    	let formData = {search:search, searchFor:searchFor}
		if(search) {
			try {
		        let response = await send_attendancePost('search employee4Select', formData);
		        console.log(response)
		        let res = JSON.parse(response);
		        if(!res.error) {
					$('#searchSupervisor').html(res.options)
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
		        let response = await send_attendancePost('search department4Select', formData);
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
		        let response = await send_attendancePost('search location4Select', formData);
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
});