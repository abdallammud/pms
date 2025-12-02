function load_comapny() {
	var datatable = $('#companyDT').DataTable({
		// let datatable = new DataTable('#companyDT', {
	    "processing": true,
	    "serverSide": true,
	    "bDestroy": true,
	    "columnDefs": [
	        { "orderable": false, "searchable": false, "targets": [4] }  // Disable search on first and last columns
	    ],
	    "serverMethod": 'post',
	    "ajax": {
	        "url": "./app/org_controller.php?action=load&endpoint=company",
	        "method": "POST",
		    // dataFilter: function(data) {
			// 	console.log(data)
			// }
	    },
	    
	    "drawCallback": function(settings) {
	        
	    },
	    columns: [
	        { title: "Organization Name", data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.name}</span>
	                </div>`;
	        }},

	        { title: "Phone Numbers", data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.contact_phone}</span>
	                </div>`;
	        }},

	        { title: "Emails", data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.contact_email}</span>
	                </div>`;
	        }},

	        { title: "Address", data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.address}</span>
	                </div>`;
	        }},

	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
	            		<span data-recid="${row.id}" class="fa edit_companyInfo smt-5 cursor smr-10 fa-pencil"></span>
	            		<span data-recid="${row.id}" class="fa delete_company smt-5 cursor fa-trash"></span>
	                </div>`;
	        }},
	    ]
	});

	return false;
}

function handleOrg() {
	$('#addOrgForm').on('submit', (e) => {
		handle_addCompanyForm(e.target);
		return false
	})

	load_comapny();

	// edit company info popup
	$(document).on('click', '.edit_companyInfo', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    let modal = $('#edit_org');

	    let data = await get_company(id)
	    if(data) {
	    	let res = JSON.parse(data)[0]

	    	$(modal).find('#company_id').val(id)
	    	$(modal).find('#orgName4Edit').val(res.name)
	    	$(modal).find('#contactPhone4Edit').val(res.contact_phone) 
	    	$(modal).find('#contactEmail4Edit').val(res.contact_email)
	    	$(modal).find('#txtAddress4Edit').val(res.address)
	    }

	    $(modal).modal('show');
	});

	$('#editOrgForm').on('submit', (e) => {
		handle_editCompanyForm(e.target);
		return false
	})

	$(document).on('click', '.delete_company', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    swal({
	        title: "Are you sure?",
	        text: "You are going to delete this company record.",
	        icon: "warning",
	        className: 'warning-swal',
	        buttons: ["Cancel", "Yes, delete"],
	    }).then(async (confirm) => {
	        if (confirm) {
	            let data = { id: id };
	            try {
	                let response = await send_orgPost('delete company', data);

	                if (response) {
	                    let res = JSON.parse(response);
	                    if (res.error) {
	                        toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
	                    } else {
	                        toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 2000 }).then(() => {
	                            // location.reload();
	                            load_comapny();
	                        });
	                        console.log(res);
	                    }
	                } else {
	                    console.log('Failed to edit company.' + response);
	                }

	            } catch (err) {
	                console.error('Error occurred during form submission:', err);
	            }
	        }
	    });
	});
}

async function send_orgPost(str, data) {
    let [action, endpoint] = str.split(' ');

    try {
        const response = await $.post(`./app/org_controller.php?action=${action}&endpoint=${endpoint}`, data);
        return response;
    } catch (error) {
        console.error('Error occurred during the request:', error);
        return null;
    }
}

async function handle_addCompanyForm(form) {
    clearErrors();

    let name = $(form).find('#orgName').val();
    let phones = $(form).find('#contactPhone').val();
    let emails = $(form).find('#contactEmail').val();
    let address = $(form).find('#txtAddress').val();

    // Input validation
    let error = false;
    error = !validateField(name, "Company name is required", 'orgName') || error;
    error = !validateField(phones, "Company phone number is required", 'contactPhone') || error;

    if (error) return false;

    let formData = {
        name: name,
        phones: phones,
        emails: emails,
        address: address
    };

    form_loading(form);

    try {
        let response = await send_orgPost('save company', formData);

        if (response) {
            let res = JSON.parse(response)
            $('#add_org').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:2000 }).then(() => {
            		location.reload();
            	});
            	console.log(res)
            }
        } else {
            console.log('Failed to save company.' + response);
        }

    } catch (err) {
        console.error('Error occurred during form submission:', err);
    }
}

async function handle_editCompanyForm(form) {
    clearErrors();

    let id = $(form).find('#company_id').val();
    let name = $(form).find('#orgName4Edit').val();
    let phones = $(form).find('#contactPhone4Edit').val();
    let emails = $(form).find('#contactEmail4Edit').val();
    let address = $(form).find('#txtAddress4Edit').val();

    // Input validation
    let error = false;
    error = !validateField(name, "Company name is required", 'orgName4Edit') || error;
    error = !validateField(phones, "Company phone number is required", 'contactPhone4Edit') || error;

    if (error) return false;

    let formData = {
    	id:id,
        name: name,
        phones: phones,
        emails: emails,
        address: address
    };

    form_loading(form);

    try {
        let response = await send_orgPost('update company', formData);

        if (response) {
            let res = JSON.parse(response)
            $('#edit_org').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:2000 }).then(() => {
            		location.reload();
            		// load_comapny();
            	});
            	console.log(res)
            }
        } else {
            console.log('Failed to edit company.' + response);
        }

    } catch (err) {
        console.error('Error occurred during form submission:', err);
    }
}

async function get_company(id) {
	let data = {id};
	let response = await send_orgPost('get company', data);
	return response;
}


// Branches
function load_branches() {
	var datatable = $('#branchesDT').DataTable({
		// let datatable = new DataTable('#companyDT', {
	    "processing": true,
	    "serverSide": true,
	    "bDestroy": true,
	    "columnDefs": [
	        { "orderable": false, "searchable": false, "targets": [1] }  // Disable search on first and last columns
	    ],
	    "serverMethod": 'post',
	    "ajax": {
	        "url": "./app/org_controller.php?action=load&endpoint=branches",
	        "method": "POST",
		    // dataFilter: function(data) {
			// 	console.log(data)
			// }
	    },
	    
	    "drawCallback": function(settings) {
	        
	    },
	    columns: [
	        { title: `${branch_keyword.sing} Name`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.name}</span>
	                </div>`;
	        }},

	       /* { title: "Phone Numbers", data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.contact_phone}</span>
	                </div>`;
	        }},

	        { title: "Emails", data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.contact_email}</span>
	                </div>`;
	        }},

	        { title: "Address", data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.address}</span>
	                </div>`;
	        }},*/

	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
	            		<span data-recid="${row.id}" class="fa edit_branchInfo smt-5 cursor smr-10 fa-pencil"></span>
	            		<span data-recid="${row.id}" class="fa delete_branch smt-5 cursor fa-trash"></span>
	                </div>`;
	        }},
	    ]
	});

	return false;
}

function handleBranches() {
	$('#addBranchForm').on('submit', (e) => {
		handle_addBranchForm(e.target);
		return false
	})

	load_branches();

	$(document).on('click', '.edit_branchInfo', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    let modal = $('#edit_branch');

	    let data = await get_branch(id)
	    if(data) {
	    	let res = JSON.parse(data)[0]

	    	$(modal).find('#branch_id').val(id)
	    	$(modal).find('#branchName4Edit').val(res.name)
	    	$(modal).find('#contactPhone4Edit').val(res.contact_phone) 
	    	$(modal).find('#contactEmail4Edit').val(res.contact_email)
	    	$(modal).find('#txtAddress4Edit').val(res.address)
	    }

	    $(modal).modal('show');
	});

	$('#editBranchForm').on('submit', (e) => {
		handle_editBranchForm(e.target);
		return false
	})

	$(document).on('click', '.delete_branch', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    swal({
	        title: "Are you sure?",
	        text: `You are going to delete this ${branch_keyword.sing} record.`,
	        icon: "warning",
	        className: 'warning-swal',
	        buttons: ["Cancel", "Yes, delete"],
	    }).then(async (confirm) => {
	        if (confirm) {
	            let data = { id: id };
	            try {
	                let response = await send_orgPost('delete branch', data);

	                if (response) {
	                    let res = JSON.parse(response);
	                    if (res.error) {
	                        toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
	                    } else {
	                        toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 2000 }).then(() => {
	                            // location.reload();
	                            load_branches();
	                        });
	                        console.log(res);
	                    }
	                } else {
	                    console.log('Failed to edit branch.' + response);
	                }

	            } catch (err) {
	                console.error('Error occurred during form submission:', err);
	            }
	        }
	    });
	});
}

async function handle_addBranchForm(form) {
    clearErrors();

    let name = $(form).find('#branchName').val();
    /*let phones = $(form).find('#contactPhone').val();
    let emails = $(form).find('#contactEmail').val();
    let address = $(form).find('#txtAddress').val();*/

    // Input validation
    let error = false;
    /*error = !validateField(name, `${branch_keyword.sing} name is required`, 'branchName') || error;
    error = !validateField(phones, `${branch_keyword.sing} phone number is required`, 'contactPhone') || error;*/

    if (error) return false;

    let formData = {
        name: name
        /*phones: phones,
        emails: emails,
        address: address*/
    };

    form_loading(form);

    try {
        let response = await send_orgPost('save branch', formData);

        if (response) {
            let res = JSON.parse(response)
            $('#add_branch').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:2000 }).then(() => {
            		location.reload();
            		// load_branches();
            	});
            	console.log(res)
            }
        } else {
            console.log('Failed to save branch.' + response);
        }

    } catch (err) {
        console.error('Error occurred during form submission:', err);
    }
}

async function handle_editBranchForm(form) {
    clearErrors();

    let id = $(form).find('#branch_id').val();
    let name = $(form).find('#branchName4Edit').val();
    /*let phones = $(form).find('#contactPhone4Edit').val();
    let emails = $(form).find('#contactEmail4Edit').val();
    let address = $(form).find('#txtAddress4Edit').val();*/

    // Input validation
    let error = false;
    /*error = !validateField(name, `${branch_keyword.sing} name is required`, 'branchName4Edit') || error;
    error = !validateField(phones, `${branch_keyword.sing} phone number is required`, 'contactPhone4Edit') || error;*/

    if (error) return false;

    let formData = {
    	id:id,
        name: name
        /*phones: phones,
        emails: emails,
        address: address*/
    };

    form_loading(form);

    try {
        let response = await send_orgPost('update branch', formData);

        if (response) {
            let res = JSON.parse(response)
            $('#edit_branch').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:2000 }).then(() => {
            		location.reload();
            		// load_branches();
            	});
            	console.log(res)
            }
        } else {
            console.log('Failed to save branch.' + response);
        }

    } catch (err) {
        console.error('Error occurred during form submission:', err);
    }
}

async function get_branch(id) {
	let data = {id};
	let response = await send_orgPost('get branch', data);
	return response;
}

// States
function load_states() {
	var datatable = $('#statesDT').DataTable({
		// let datatable = new DataTable('#companyDT', {
	    "processing": true,
	    "serverSide": true,
	    "bDestroy": true,
	    "searching": false,  
	    "info": false,
	    "columnDefs": [
	        { "orderable": false, "searchable": false, "targets": [1] }  // Disable search on first and last columns
	    ],
	    "serverMethod": 'post',
	    "ajax": {
	        "url": "./app/org_controller.php?action=load&endpoint=states",
	        "method": "POST",
		    // dataFilter: function(data) {
			// 	console.log(data)
			// }
	    },
	    
	    "drawCallback": function(settings) {
	        
	    },
	    columns: [
	        { title: `State Name`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.name}</span>
	                </div>`;
	        }},

	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
            		<span data-recid="${row.id}" class="fa show_stateDetails smt-5 cursor smr-10 fa-eye"></span>
            		<span data-recid="${row.id}" class="fa edit_stateInfo smt-5 cursor smr-10 fa-pencil"></span>
            		<span data-recid="${row.id}" class="fa delete_state smt-5 cursor fa-trash"></span>
                </div>`;
	        }},
	    ]
	});

	return false;
}

function handleStates() {
	// States
	$(document).on('click', '.add-tax-grid-row', function(e) {
	    let prevRow = $(e.target).siblings(".row.tax-grid-row").last();
	    if(prevRow.length == 0) {
	    	prevRow = $(e.target).siblings(".tax-gridRows").find('.row.tax-grid-row').last();
	    }
	    console.log($(e.target).siblings(".row.tax-grid-row"))

	    // return false;
	    let newRow = `<div class="row tax-grid-row" style="margin-top: 5px;">
	            <div class="col-sm-4">
	            	<input type="text" onkeypress="return isNumberKey(event)"  class="form-control min-amount col-sm-4 col-lg-4">
	            </div>
	            <div class="col-sm-4">
	            	<input type="text" onkeypress="return isNumberKey(event)"  class="form-control max-amount col-sm-4 col-lg-4 validate">
	            </div>
	            <div class="col-sm-3">
	            	<input type="text" onkeypress="return isNumberKey(event)"  class="form-control rate col-sm-4 col-lg-4 validate">
	            </div>
	            <div class="col-sm-1">
	            	<i class="fa fa-trash-alt remove-tax-grid-row cursor mt-2"></i>
	            </div>
	        </div>`;

	    // Insert the new row after the current row
	    $(prevRow).after(newRow);
	});

	$(document).on('click', '.remove-tax-grid-row', function(e) {
	    e.preventDefault();
	    let prevRow = $(e.target).closest('.row');
	    $(prevRow).fadeOut(500, function() {
	        $(this).remove();
	    });
	});

	load_states();

	// Add state
	$('#addStateForm').on('submit', (e) => {
		handle_addStateForm(e.target);
		return false
	})

	// Show state
	$(document).on('click', '.show_stateDetails', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    let modal = $('#show_state');

	    let data = await get_state(id, true)
	    console.log(data)
	    if(data) {
	    	let res = JSON.parse(data);
	    	$(modal).find('#detailsTable tbody').html(res.details)
	    	$(modal).find('#tax-grid tbody').html(res.tax)
	    }
	    // return false;
	   
	    $(modal).modal('show');
	});

	// Edit state
	$(document).on('click', '.edit_stateInfo', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    let modal = $('#edit_state');

	    let data = await get_state(id, false);
	    // console.log(data)
	    if(data) {
	    	let res = JSON.parse(data)[0];
            console.log(res)
            
            let taxGridHtml = ``
            let tax_grid = res.tax_grid
            if(tax_grid && tax_grid.length > 0) {
                tax_grid.map((item) => {
                    taxGridHtml += `<div class="row tax-grid-row" style="margin-top: 2px;">
                        <div class="col-sm-4">
                            <label class="label required">Min amount</label>
                            <input type="text" onkeypress="return isNumberKey(event)" class="form-control min-amount col-sm-4 col-lg-4" value="${item.min}">
                        </div>
                        <div class="col-sm-4">
                            <label class="label required">Max amount</label>
                            <input type="text" onkeypress="return isNumberKey(event)" class="form-control max-amount col-sm-4 col-lg-4" value="${item.max}">
                        </div>
                        <div class="col-sm-3">
                            <label class="label required">Rate</label>
                            <input type="text" onkeypress="return isNumberKey(event)" class="form-control rate col-sm-4 col-lg-4" value="${item.rate}">
                            
                        </div>
                        <div class="col-sm-1">
                            <label class="label required">&nbsp;</label>
                            <i class="fa fa-trash-alt remove-tax-grid-row cursor mt-2"></i>
                        </div>
                        
                    </div>`
                })
            }

            if(taxGridHtml) {
                $('.tax-gridRows').html(taxGridHtml)
            }

	    	$(modal).find('#state_id').val(id);
	    	$(modal).find('#stateName').val(res.name);
	    	$(modal).find('#stampDuty').val(res.stamp_duty);
	    	$(modal).find('#stateCountry').val(res.country_id) ;
	    	$(modal).find('#slcStatus').val(res.status);
	    }

	    $(modal).modal('show');
	});

	// Edit state info form
	$('#editStateForm').on('submit', (e) => {
		handle_editStateForm(e.target);
		return false
	})

	// Delete state
	$(document).on('click', '.delete_state', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    swal({
	        title: "Are you sure?",
	        text: `You are going to delete this state record.`,
	        icon: "warning",
	        className: 'warning-swal',
	        buttons: ["Cancel", "Yes, delete"],
	    }).then(async (confirm) => {
	        if (confirm) {
	            let data = { id: id };
	            try {
	                let response = await send_orgPost('delete state', data);
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

async function handle_addStateForm(form) {
    clearErrors();
    let error = validateForm(form)

    let name 	= $(form).find('#stateName').val();
    let country = $(form).find('#stateCountry').val();
    let countryName = $(form).find('#stateCountry option:selected').text();
    let tax 	= [];
    let stampDuty 	= $(form).find('#stampDuty').val();

    $('.tax-grid-row').each((i, el) => {
	    let min = parseFloat($(el).find('.min-amount').val()) || 0; 
	    let max = parseFloat($(el).find('.max-amount').val());
	    let rate = parseFloat($(el).find('.rate').val());

	    // Only add valid rows to the tax array
	    if (!isNaN(max) && max > min) {
	        let obj = { "min": min, "max": max, "rate": rate || 0 }; 
	        tax.push(obj);
	    }
	});

    if (error) return false;

    let formData = {
        name: name,
        country: country,
        tax: tax,
        countryName:countryName,
        stampDuty:stampDuty
    };

    form_loading(form);

    try {
        let response = await send_orgPost('save state', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#add_state').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
            		form_loadingUndo(form);
            		location.reload();
            		// load_states();
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

async function handle_editStateForm(form) {
    clearErrors();
    let error = validateForm(form)

    let id 		= $(form).find('#state_id').val();
    let name 	= $(form).find('#stateName').val();
    let country = $(form).find('#stateCountry').val();
    let countryName = $(form).find('#stateCountry option:selected').text();
    let tax 		= [];
    let stampDuty 	= $(form).find('#stampDuty').val();
    let slcStatus 	= $(form).find('#slcStatus').val();

    $('.tax-grid-row').each((i, el) => {
	    let min = parseFloat($(el).find('.min-amount').val()) || 0; 
	    let max = parseFloat($(el).find('.max-amount').val());
	    let rate = parseFloat($(el).find('.rate').val());

	    // Only add valid rows to the tax array
	    if (!isNaN(max) && max > min) {
	        let obj = { "min": min, "max": max, "rate": rate || 0 }; 
	        tax.push(obj);
	    }
	});

    if (error) return false;

    let formData = {
    	id:id,
        name: name,
        status: slcStatus,
        country: country,
        tax: tax,
        countryName:countryName,
        stampDuty:stampDuty
    };

    form_loading(form);

    try {
        let response = await send_orgPost('update state', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#edit_state').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
            		form_loadingUndo(form);
            		location.reload();
            		// load_states();
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

async function get_state(id, show = false) {
	let data = {id, show};
	let response = await send_orgPost('get state', data);
	return response;
}


// Locations
function load_locations() {
	var datatable = $('#locationsDT').DataTable({
		// let datatable = new DataTable('#companyDT', {
	    "processing": true,
	    "serverSide": true,
	    "bDestroy": true,
	    "searching": false,  
	    "info": false,
	    "columnDefs": [
	        { "orderable": false, "searchable": false, "targets": [3] }  // Disable search on first and last columns
	    ],
	    "serverMethod": 'post',
	    "ajax": {
	        "url": "./app/org_controller.php?action=load&endpoint=locations",
	        "method": "POST",
		    // dataFilter: function(data) {
			// 	console.log(data)
			// }
	    },
	    
	    "drawCallback": function(settings) {
	        
	    },
	    columns: [
	        { title: `Duty Location`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.name}</span>
	                </div>`;
	        }},

	        { title: `City`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.city_name}</span>
	                </div>`;
	        }},

	        { title: `State`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.state_name}</span>
	                </div>`;
	        }},

	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
            		<span data-recid="${row.id}" class="fa edit_locationInfo smt-5 cursor smr-10 fa-pencil"></span>
            		<span data-recid="${row.id}" class="fa delete_location smt-5 cursor fa-trash"></span>
                </div>`;
	        }},
	    ]
	});

	return false;
}

function handleLocations() {
	$('#addLocationForm').on('submit', (e) => {
		handle_addLocationForm(e.target);
		return false
	})

	load_locations();

	// Edit location
	$(document).on('click', '.edit_locationInfo', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    let modal = $('#edit_location');

	    let data = await get_location(id);
	    console.log(data)
	    if(data) {
	    	let res = JSON.parse(data)[0];
	    	console.log(res)
	    	$(modal).find('#location_id').val(id);
	    	$(modal).find('#locationName4Edit').val(res.name);
	    	$(modal).find('#city4Edit').val(res.city_name);
	    	$(modal).find('#state4Edit').val(res.state_id) ;
	    	$(modal).find('#slcStatus').val(res.status);
	    }

	    $(modal).modal('show');
	});

	// Edit location info form
	$('#editLocationForm').on('submit', (e) => {
		handle_editLocationForm(e.target);
		return false
	})

	// Delete location
	$(document).on('click', '.delete_location', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    swal({
	        title: "Are you sure?",
	        text: `You are going to delete this duty location record.`,
	        icon: "warning",
	        className: 'warning-swal',
	        buttons: ["Cancel", "Yes, delete"],
	    }).then(async (confirm) => {
	        if (confirm) {
	            let data = { id: id };
	            try {
	                let response = await send_orgPost('delete location', data);
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

async function handle_addLocationForm(form) {
    clearErrors();
    let error = validateForm(form)

    let name 	= $(form).find('#locationName').val();
    let city 	= $(form).find('#city').val();
    let state 	= $(form).find('#state').val();
    let stateName = $(form).find('#state option:selected').text();

    if (error) return false;

    let formData = {
        name: name,
        city: city,
        state: state,
        stateName:stateName
    };

    form_loading(form);

    try {
        let response = await send_orgPost('save location', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#add_state').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
            		form_loadingUndo(form);
            		location.reload();
            		// load_states();
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

async function handle_editLocationForm(form) {
    clearErrors();
    let error = validateForm(form)

    console.log(form)

    let id 		= $(form).find('#location_id').val();
    let name 	= $(form).find('#locationName4Edit').val();
    let city 	= $(form).find('#city4Edit').val();
    let state 	= $(form).find('#state4Edit').val();
    let stateName = $(form).find('#state4Edit option:selected').text();
    let slcStatus 	= $(form).find('#slcStatus').val();

    if (error) return false;

    let formData = {
    	id:id,
        name: name,
        city: city,
        state: state,
        stateName:stateName,
        slcStatus:slcStatus
    };

    form_loading(form);

    try {
        let response = await send_orgPost('update location', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#edit_location').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
            		form_loadingUndo(form);
            		location.reload();
            		// load_states();
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

async function get_location(id) {
	let data = {id};
	let response = await send_orgPost('get location', data);
	return response;
}




// Designation
function load_designations() {
	var datatable = $('#designationsDT').DataTable({
		// let datatable = new DataTable('#companyDT', {
	    "processing": true,
	    "serverSide": true,
	    "bDestroy": true,
	    "searching": false,  
	    "info": false,
	    "columnDefs": [
	        { "orderable": false, "searchable": false, "targets": [1] }  // Disable search on first and last columns
	    ],
	    "serverMethod": 'post',
	    "ajax": {
	        "url": "./app/org_controller.php?action=load&endpoint=designations",
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
	        { title: `Name`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.name}</span>
	                </div>`;
	        }},

	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
            		<span data-recid="${row.id}" class="fa edit_designationInfo smt-5 cursor smr-10 fa-pencil"></span>
            		<span data-recid="${row.id}" class="fa delete_designation smt-5 cursor fa-trash"></span>
                </div>`;
	        }},
	    ]
	});

	return false;
}

function handleDesignations() {
	$('#addDesignationForm').on('submit', (e) => {
		handle_addDesignationForm(e.target);
		return false
	})

	load_designations();

	// Edit location
	$(document).on('click', '.edit_designationInfo', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    let modal = $('#edit_designation');

	    let data = await get_designation(id);
	    console.log(data)
	    if(data) {
	    	let res = JSON.parse(data)[0];
	    	console.log(res)
	    	$(modal).find('#designation_id').val(id);
	    	$(modal).find('#designationName4Edit').val(res.name);
	    	$(modal).find('#slcStatus').val(res.status);
	    }

	    $(modal).modal('show');
	});

	// Edit location info form
	$('#editDesignationForm').on('submit', (e) => {
		handle_editDesignationForm(e.target);
		return false
	})

	// Delete location
	$(document).on('click', '.delete_designation', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    swal({
	        title: "Are you sure?",
	        text: `You are going to delete this designation.`,
	        icon: "warning",
	        className: 'warning-swal',
	        buttons: ["Cancel", "Yes, delete"],
	    }).then(async (confirm) => {
	        if (confirm) {
	            let data = { id: id };
	            try {
	                let response = await send_orgPost('delete designation', data);
	                console.log(response)
	                if (response) {
	                    let res = JSON.parse(response);
	                    if (res.error) {
	                        toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
	                    } else {
	                        toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
								$('#add_designation').modal('hide');
	                            load_designations();
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

async function handle_addDesignationForm(form) {
    clearErrors();
    let error = validateForm(form)

    let name 	= $(form).find('#designationName').val();

    if (error) return false;

    let formData = {
        name: name
    };

    form_loading(form);

    try {
        let response = await send_orgPost('save designation', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#add_designation').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
					$('#add_designation').modal('hide');
					form_loadingUndo(form);
            		load_designations();
					form_loadingUndo(form);
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

async function handle_editDesignationForm(form) {
    clearErrors();
    let error = validateForm(form)

    console.log(form)

    let id 	= $(form).find('#designation_id').val();
   	let name 	= $(form).find('#designationName4Edit').val();
    let slcStatus 	= $(form).find('#slcStatus').val();

    if (error) return false;

    let formData = {
    	id:id,
        name: name,
        slcStatus:slcStatus
    };

    form_loading(form);

    try {
        let response = await send_orgPost('update designation', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#edit_designation').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
					$('#edit_designation').modal('hide');
					form_loadingUndo(form);
            		load_designations();
					form_loadingUndo(form);
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

async function get_designation(id) {
	let data = {id};
	let response = await send_orgPost('get designation', data);
	return response;
}

// Projects
function load_projects() {
	var datatable = $('#projectsDT').DataTable({
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
	        "url": "./app/org_controller.php?action=load&endpoint=projects",
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
	        { title: `Name`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.name}</span>
	                </div>`;
	        }},

	        { title: `Comments`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.comments}</span>
	                </div>`;
	        }},

	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
            		<span data-recid="${row.id}" class="fa edit_projectInfo smt-5 cursor smr-10 fa-pencil"></span>
            		<span data-recid="${row.id}" class="fa delete_project smt-5 cursor fa-trash"></span>
                </div>`;
	        }},
	    ]
	});

	return false;
}

function handleProjects() {
	$('#addProjectForm').on('submit', (e) => {
		handle_addProjectForm(e.target);
		return false
	})

	load_projects();

	// Edit location
	$(document).on('click', '.edit_projectInfo', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    let modal = $('#edit_project');

	    let data = await get_project(id);
	    console.log(data)
	    if(data) {
	    	let res = JSON.parse(data)[0];
	    	console.log(res)
	    	$(modal).find('#project_id').val(id);
	    	$(modal).find('#projectName4Edit').val(res.name);
	    	$(modal).find('#comments4Edit').val(res.comments);
	    	$(modal).find('#slcStatus').val(res.status);
	    }

	    $(modal).modal('show');
	});

	// Edit location info form
	$('#editProjectForm').on('submit', (e) => {
		handle_editProjectForm(e.target);
		return false
	})

	// Delete location
	$(document).on('click', '.delete_project', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    swal({
	        title: "Are you sure?",
	        text: `You are going to delete this project.`,
	        icon: "warning",
	        className: 'warning-swal',
	        buttons: ["Cancel", "Yes, delete"],
	    }).then(async (confirm) => {
	        if (confirm) {
	            let data = { id: id };
	            try {
	                let response = await send_orgPost('delete project', data);
	                console.log(response)
	                if (response) {
	                    let res = JSON.parse(response);
	                    if (res.error) {
	                        toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
	                    } else {
	                        toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
	                            load_projects();
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

async function handle_addProjectForm(form) {
    clearErrors();
    let error = validateForm(form)

    let name 	= $(form).find('#projectName').val();
    let comments = $(form).find('#comments').val();

    if (error) return false;

    let formData = {
        name: name,
        comments:comments
    };

    form_loading(form);

    try {
        let response = await send_orgPost('save project', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#add_project').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
					$('#add_project').modal('hide');
					form_loadingUndo(form);
            		load_projects();
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

async function handle_editProjectForm(form) {
    clearErrors();
    let error = validateForm(form)

    console.log(form)

    let id 	= $(form).find('#project_id').val();
   	let name 	= $(form).find('#projectName4Edit').val();
   	let comments 	= $(form).find('#comments4Edit').val();
    let slcStatus 	= $(form).find('#slcStatus').val();

    if (error) return false;

    let formData = {
    	id:id,
        name: name,
        comments: comments,
        slcStatus:slcStatus
    };

    form_loading(form);

    try {
        let response = await send_orgPost('update project', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#edit_project').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
					$('#edit_project').modal('hide');
					form_loadingUndo(form);
            		load_projects();
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

async function get_project(id) {
	let data = {id};
	let response = await send_orgPost('get project', data);
	return response;
}


// Contract types
function load_contractTypes() {
	var datatable = $('#contractTypesDT').DataTable({
		// let datatable = new DataTable('#companyDT', {
	    "processing": true,
	    "serverSide": true,
	    "bDestroy": true,
	    "searching": false,  
	    "info": false,
	    "columnDefs": [
	        { "orderable": false, "searchable": false,  "targets": [1] }  // Disable search on first and last columns
	    ],
	    "serverMethod": 'post',
	    "ajax": {
	        "url": "./app/org_controller.php?action=load&endpoint=contract_types",
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
	        { title: `Contract Type`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.name}</span>
	                </div>`;
	        }},

	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
            		<span data-recid="${row.id}" class="fa edit_contractTypeInfo smt-5 cursor smr-10 fa-pencil"></span>
            		<span data-recid="${row.id}" class="fa delete_contractType smt-5 cursor fa-trash"></span>
                </div>`;
	        }},
	    ]
	});

	return false;
}

function handleContractTypes() {
	$('#add_contractType').on('submit', (e) => {
		handle_addContractTypeForm(e.target);
		return false
	})

	load_contractTypes();

	// Edit location
	$(document).on('click', '.edit_contractTypeInfo', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    let modal = $('#edit_contractType');

	    let data = await get_contractType(id);
	    console.log(data)
	    if(data) {
	    	let res = JSON.parse(data)[0];
	    	console.log(res)
	    	$(modal).find('#contractType_id').val(id);
	    	$(modal).find('#contractTypeName4Edit').val(res.name);
	    	$(modal).find('#slcStatus').val(res.status);
	    }

	    $(modal).modal('show');
	});

	// Edit location info form
	$('#editContractTypeForm').on('submit', (e) => {
		handle_editContractTypeForm(e.target);
		return false
	})

	// Delete location
	$(document).on('click', '.delete_contractType', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    swal({
	        title: "Are you sure?",
	        text: `You are going to delete this contract type.`,
	        icon: "warning",
	        className: 'warning-swal',
	        buttons: ["Cancel", "Yes, delete"],
	    }).then(async (confirm) => {
	        if (confirm) {
	            let data = { id: id };
	            try {
	                let response = await send_orgPost('delete contract_type', data);
	                console.log(response)
	                if (response) {
	                    let res = JSON.parse(response);
	                    if (res.error) {
	                        toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
	                    } else {
	                        toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
	                            load_contractTypes();
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

async function handle_addContractTypeForm(form) {
    clearErrors();
    let error = validateForm(form)

    let name 	= $(form).find('#contractTypeName').val();

    if (error) return false;

    let formData = {
        name: name,
    };

    form_loading(form);

    try {
        let response = await send_orgPost('save contract_type', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#add_contractType').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
					$('#add_contractType').modal('hide');
					form_loadingUndo(form);
            		load_contractTypes();
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

async function handle_editContractTypeForm(form) {
    clearErrors();
    let error = validateForm(form)

    console.log(form)

    let id 	= $(form).find('#contractType_id').val();
   	let name 	= $(form).find('#contractTypeName4Edit').val();
    let slcStatus 	= $(form).find('#slcStatus').val();

    if (error) return false;

    let formData = {
    	id:id,
        name: name,
        slcStatus:slcStatus
    };

    form_loading(form);

    try {
        let response = await send_orgPost('update contract_type', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#edit_contractType').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
					$('#edit_contractType').modal('hide');
					form_loadingUndo(form);
            		load_contractTypes();
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

async function get_contractType(id) {
	let data = {id};
	let response = await send_orgPost('get contract_type', data);
	return response;
}


// Budget codes
function load_budgetCodes() {
	var datatable = $('#budgetCodesDT').DataTable({
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
	        "url": "./app/org_controller.php?action=load&endpoint=budget_codes",
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
	        { title: `Name`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.name}</span>
	                </div>`;
	        }},

	        { title: `Comments`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.comments}</span>
	                </div>`;
	        }},

            { title: `Grant code`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.grant_code}</span>
	                </div>`;
	        }},

	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
            		<span data-recid="${row.id}" class="fa edit_budgetCodeInfo smt-5 cursor smr-10 fa-pencil"></span>
            		<span data-recid="${row.id}" class="fa delete_budgetCode smt-5 cursor fa-trash"></span>
                </div>`;
	        }},
	    ]
	});

	return false;
}

function handleBudgetCodes() {
	$('#addBudgetCodeForm').on('submit', (e) => {
		handle_addBudgetCodeForm(e.target);
		return false
	})

	load_budgetCodes();

	// Edit location
	$(document).on('click', '.edit_budgetCodeInfo', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    let modal = $('#edit_budgetCode');

	    let data = await get_budgetCode(id);
	    // console.log(data)
	    if(data) {
	    	let res = JSON.parse(data)[0];
	    	$(modal).find('#budget_codeID').val(id);
            $(modal).find('#slcGrantCode4Edit').val(res.grant_code_id);
	    	$(modal).find('#budgetCode4Edit').val(res.name);
	    	$(modal).find('#comments4Edit').val(res.comments);
	    	$(modal).find('#slcStatus').val(res.status);
	    }

	    $(modal).modal('show');
	});

	// Edit location info form
	$('#editBudgetCodeForm').on('submit', (e) => {
		handle_editBudgetCodeForm(e.target);
		return false
	})

	// Delete location
	$(document).on('click', '.delete_budgetCode', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    swal({
	        title: "Are you sure?",
	        text: `You are going to delete this budget code.`,
	        icon: "warning",
	        className: 'warning-swal',
	        buttons: ["Cancel", "Yes, delete"],
	    }).then(async (confirm) => {
	        if (confirm) {
	            let data = { id: id };
	            try {
	                let response = await send_orgPost('delete budget_code', data);
	                console.log(response)
	                if (response) {
	                    let res = JSON.parse(response);
	                    if (res.error) {
	                        toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
	                    } else {
	                        toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
	                            load_budgetCodes();
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

async function handle_addBudgetCodeForm(form) {
    clearErrors();
    let error = validateForm(form)

    let name 	= $(form).find('#budgetCode').val();
    let comments = $(form).find('#comments').val();
    let grantCode = $(form).find('#slcGrantCode').val();

    if (error) return false;

    let formData = {
        name: name,
        comments:comments,
        grant_code_id:grantCode
    };

    form_loading(form);

    try {
        let response = await send_orgPost('save budget_code', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#add_budgetCode').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
					$('#add_budgetCode').modal('hide');
					form_loadingUndo(form);
            		load_budgetCodes();
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

async function handle_editBudgetCodeForm(form) {
    clearErrors();
    let error = validateForm(form)

    console.log(form)

    let id 	= $(form).find('#budget_codeID').val();
    let grantCode = $(form).find('#slcGrantCode4Edit').val();
   	let name 	= $(form).find('#budgetCode4Edit').val();
   	let comments 	= $(form).find('#comments4Edit').val();
    let slcStatus 	= $(form).find('#slcStatus').val();

    if (error) return false;

    let formData = {
    	id:id,
        grant_code_id:grantCode,
        name: name,
        comments: comments,
        slcStatus:slcStatus
    };

    form_loading(form);
    try {
        let response = await send_orgPost('update budget_code', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#edit_budgetCode').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
					$('#edit_budgetCode').modal('hide');
					form_loadingUndo(form);
            		load_budgetCodes();
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

async function get_budgetCode(id) {
	let data = {id};
	let response = await send_orgPost('get budget_code', data);
	return response;
}

// Grant codes
function load_grantCodes() {
    var datatable = $('#grantCodesDT').DataTable({
        "processing": true,
        "serverSide": true,
        "bDestroy": true,
        "searching": false,  
        "info": false,
        "columnDefs": [
            { "orderable": false, "searchable": false, "targets": [2] }
        ],
        "serverMethod": 'post',
        "ajax": {
            "url": "./app/org_controller.php?action=load&endpoint=grant_codes",
            "method": "POST",
            // dataFilter: function(data) {
			// 	console.log(data)
			// }
        },
        "createdRow": function(row, data, dataIndex) {
            $(row).addClass('table-row ' + data.status.toLowerCase());
        },
        columns: [
            { title: `Name`, data: null, render: function(data, type, row) {
                return `<div><span>${row.name}</span></div>`;
            }},
            { title: `Status`, data: null, render: function(data, type, row) {
                return `<div><span>${row.status}</span></div>`;
            }},
            { title: "Action", data: null, render: function(data, type, row) {
                return `<div class="sflex scenter-items">
                    <span data-recid="${row.id}" class="fa edit_grantCodeInfo smt-5 cursor smr-10 fa-pencil"></span>
                    <span data-recid="${row.id}" class="fa delete_grantCode smt-5 cursor fa-trash"></span>
                </div>`;
            }},
        ]
    });

    return false;
}

function handleGrantCodes() {
    $('#addGrantCodeForm').on('submit', (e) => {
        handle_addGrantCodeForm(e.target);
        return false;
    });

    load_grantCodes();

    // Edit Grant Code
    $(document).on('click', '.edit_grantCodeInfo', async (e) => {
        let id = $(e.currentTarget).data('recid');
        let modal = $('#edit_grantCode');

        let data = await get_grantCode(id);
        if (data) {
            let res = JSON.parse(data)[0];
            $(modal).find('#grantCode_id').val(id);
            $(modal).find('#grantCode4Edit').val(res.name);
            $(modal).find('#slcStatus').val(res.status);
        }

        $(modal).modal('show');
    });

    // Edit form submit
    $('#editGrantCodeForm').on('submit', (e) => {
        handle_editGrantCodeForm(e.target);
        return false;
    });

    // Delete Grant Code
    $(document).on('click', '.delete_grantCode', async (e) => {
        let id = $(e.currentTarget).data('recid');
        swal({
            title: "Are you sure?",
            text: `You are going to delete this grant code.`,
            icon: "warning",
            className: 'warning-swal',
            buttons: ["Cancel", "Yes, delete"],
        }).then(async (confirm) => {
            if (confirm) {
                let data = { id: id };
                try {
                    let response = await send_orgPost('delete grant_code', data);
                    console.log(response)
                    if (response) {
                        let res = JSON.parse(response);
                        if (res.error) {
                            toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
                        } else {
                            toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                                $('#grantCodesDT').DataTable().ajax.reload(null, false);
                            });
                        }
                    }
                } catch (err) {
                    console.error('Error occurred during deletion:', err);
                }
            }
        });
    });

    $('#slcGrantCode, #slcGrantCode4Edit').on('change', function() {
        // Find text of the selected option
        let grantCode = $(this).find('option:selected').text();
        if (grantCode) {
            console.log(grantCode)
            let firstThree = grantCode.substring(0, 3); 
            console.log(firstThree)
            
            $('#budgetCode').val(firstThree);
            // $('#budgetCode4Edit').val(firstThree);
        } else {
            $('#budgetCode').val('');
            // $('#budgetCode4Edit').val('');
        }
    });
}

async function handle_addGrantCodeForm(form) {
    clearErrors();
    let error = validateForm(form);

    let name = $(form).find('#grantCode').val();

    if (error) return false;

    let formData = {
        name: name
    };

    form_loading(form);

    try {
        let response = await send_orgPost('save grant_code', formData);
        if (response) {
            let res = JSON.parse(response);
            $('#add_grantCode').modal('hide');
            if (res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    $('#add_grantCode').modal('hide');
                    form_loadingUndo(form);
                    $('#grantCodesDT').DataTable().ajax.reload(null, false);
                });
            }
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
    }
}

async function handle_editGrantCodeForm(form) {
    clearErrors();
    let error = validateForm(form);

    let id = $(form).find('#grantCode_id').val();
    let name = $(form).find('#grantCode4Edit').val();
    let slcStatus = $(form).find('#slcStatus').val();

    if (error) return false;

    let formData = {
        id: id,
        name: name,
        slcStatus: slcStatus
    };

    form_loading(form);
    try {
        let response = await send_orgPost('update grant_code', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response);
            $('#edit_grantCode').modal('hide');
            if (res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    $('#edit_grantCode').modal('hide');
                    form_loadingUndo(form);
                    $('#grantCodesDT').DataTable().ajax.reload(null, false);
                });
            }
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
    }
}

async function get_grantCode(id) {
    let data = { id };
    let response = await send_orgPost('get grant_code', data);
    return response;
}

// banks
function load_allBanks() {
	var datatable = $('#allBanksDT').DataTable({
		// let datatable = new DataTable('#companyDT', {
	    "processing": true,
	    "serverSide": true,
	    "bDestroy": true,
	    "searching": false,  
	    "info": false,
	    "columnDefs": [
	        { "orderable": false, "searchable": false,  "targets": [1] }  // Disable search on first and last columns
	    ],
	    "serverMethod": 'post',
	    "ajax": {
	        "url": "./app/org_controller.php?action=load&endpoint=all_banks",
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
	        { title: `Name`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.name}</span>
	                </div>`;
	        }},

	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
            		<span data-recid="${row.id}" class="fa edit_allBanksInfo smt-5 cursor smr-10 fa-pencil"></span>
            		<span data-recid="${row.id}" class="fa delete_allBanks smt-5 cursor fa-trash"></span>
                </div>`;
	        }},
	    ]
	});

	return false;
}

function handleAllBanks() {
	$('#addbankForm').on('submit', (e) => {
		handle_addbankForm(e.target);
		return false
	})

	load_allBanks();

	// Edit location
	$(document).on('click', '.edit_allBanksInfo', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    let modal = $('#edit_bank');

	    let data = await get_bank(id);
	    console.log(data)
	    if(data) {
	    	let res = JSON.parse(data)[0];
	    	console.log(res)
	    	$(modal).find('#bank_id').val(id);
	    	$(modal).find('#bankName4Edit').val(res.name);
	    	$(modal).find('#slcStatus').val(res.status);
	    }

	    $(modal).modal('show');
	});

	// Edit location info form
	$('#editAllBankForm').on('submit', (e) => {
		handle_editAllBankForm(e.target);
		return false
	})

	// Delete location
	$(document).on('click', '.delete_allBanks', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    swal({
	        title: "Are you sure?",
	        text: `You are going to delete this bank.`,
	        icon: "warning",
	        className: 'warning-swal',
	        buttons: ["Cancel", "Yes, delete"],
	    }).then(async (confirm) => {
	        if (confirm) {
	            let data = { id: id };
	            try {
	                let response = await send_orgPost('delete bank', data);
	                console.log(response)
	                if (response) {
	                    let res = JSON.parse(response);
	                    if (res.error) {
	                        toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
	                    } else {
	                        toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
	                            load_allBanks();
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

async function handle_addbankForm(form) {
    clearErrors();
    let error = validateForm(form)

    let name 	= $(form).find('#bankName').val();

    if (error) return false;

    let formData = {
        name: name,
    };

    form_loading(form);

    try {
        let response = await send_orgPost('save bank', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#add_bank').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
					$('#add_bank').modal('hide');
					form_loadingUndo(form);
            		load_allBanks();
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

async function handle_editAllBankForm(form) {
    clearErrors();
    let error = validateForm(form)

    console.log(form)

    let id 	= $(form).find('#bank_id').val();
   	let name 	= $(form).find('#bankName4Edit').val();
    let slcStatus 	= $(form).find('#slcStatus').val();

    if (error) return false;

    let formData = {
    	id:id,
        name: name,
        slcStatus:slcStatus
    };

    form_loading(form);
    try {
        let response = await send_orgPost('update bank', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#edit_bank').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
					$('#edit_bank').modal('hide');
					form_loadingUndo(form);
            		load_allBanks();
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

async function get_bank(id) {
	let data = {id};
	let response = await send_orgPost('get bank', data);
	return response;
}


// Transaction subtypes
function load_transSubTypes() {
	var datatable = $('#subTypesDT').DataTable({
		// let datatable = new DataTable('#companyDT', {
	    "processing": true,
	    "serverSide": true,
	    "bDestroy": true,
	    "searching": false,  
	    "info": false,
	    "columnDefs": [
	        { "orderable": false, "searchable": false,  "targets": [1] }  // Disable search on first and last columns
	    ],
	    "serverMethod": 'post',
	    "ajax": {
	        "url": "./app/org_controller.php?action=load&endpoint=subtypes",
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
	        { title: `Type`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.type}</span>
	                </div>`;
	        }},

	        { title: `Name`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.name}</span>
	                </div>`;
	        }},

	        { title: "Action", data: null, render: function(data, type, row) {
	            return `<div class="sflex scenter-items">
            		<span data-recid="${row.id}" class="fa edit_subTypesInfo smt-5 cursor smr-10 fa-pencil"></span>
            		<span data-recid="${row.id}" class="fa delete_subType smt-5 cursor fa-trash"></span>
                </div>`;
	        }},
	    ]
	});

	return false;
}

function handleSubTypes() {
	$('#addSubtype').on('submit', (e) => {
		handle_addSubtype(e.target);
		return false
	})

	load_transSubTypes();

	// Edit location
	$(document).on('click', '.edit_subTypesInfo', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    let modal = $('#edit_subtype');

	    let data = await get_subtype(id);
	    console.log(data)
	    if(data) {
	    	let res = JSON.parse(data)[0];
	    	console.log(res)
	    	$(modal).find('#subtype_id').val(id);
	    	$(modal).find('#subtypeName4Edit').val(res.name);
	    	$(modal).find('#transType4Edit').val(res.type);
	    	$(modal).find('#slcStatus').val(res.status);
	    }

	    $(modal).modal('show');
	});

	// Edit location info form
	$('#editSubtype').on('submit', (e) => {
		handle_editSubtype(e.target);
		return false
	})

	// Delete location
	$(document).on('click', '.delete_subType', async (e) => {
	    let id = $(e.currentTarget).data('recid');
	    swal({
	        title: "Are you sure?",
	        text: `You are going to delete this record.`,
	        icon: "warning",
	        className: 'warning-swal',
	        buttons: ["Cancel", "Yes, delete"],
	    }).then(async (confirm) => {
	        if (confirm) {
	            let data = { id: id };
	            try {
	                let response = await send_orgPost('delete subtype', data);
	                console.log(response)
	                if (response) {
	                    let res = JSON.parse(response);
	                    if (res.error) {
	                        toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
	                    } else {
	                        toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
	                            load_transSubTypes();
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

async function handle_addSubtype(form) {
    clearErrors();
    let error = validateForm(form)

    let name 	= $(form).find('#subtypeName').val();
    let type 	= $(form).find('#transType').val();

    if (error) return false;

    let formData = {
        name: name,
        type:type
    };

    form_loading(form);

    try {
        let response = await send_orgPost('save subtype', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#add_subtype').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
					$('#add_subtype').modal('hide');
					form_loadingUndo(form);
            		load_transSubTypes();
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

async function handle_editSubtype(form) {
    clearErrors();
    let error = validateForm(form)

    console.log(form)

    let id 	= $(form).find('#subtype_id').val();
   	let name 	= $(form).find('#subtypeName4Edit').val();
   	let type 	= $(form).find('#transType4Edit').val();
    let slcStatus 	= $(form).find('#slcStatus').val();

    if (error) return false;

    let formData = {
    	id:id,
        name: name,
        type:type,
        slcStatus:slcStatus
    };

    form_loading(form);
    try {
        let response = await send_orgPost('update subtype', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#edit_subtype').modal('hide');
            if(res.error) {
            	toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
            	toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:1000 }).then(() => {
					$('#edit_subtype').modal('hide');
					form_loadingUndo(form);
            		load_transSubTypes();
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

async function get_subtype(id) {
	let data = {id};
	let response = await send_orgPost('get subtype', data);
	return response;
}

// Goal types
function load_goalTypes() {
	var datatable = $('#goalTypesDT').DataTable({
		// let datatable = new DataTable('#companyDT', {
	    "processing": true,
	    "serverSide": true,
	    "bDestroy": true,
	    "searching": false,  
	    "info": false,
	    "columnDefs": [
	        { "orderable": false, "searchable": false,  "targets": [1] }  // Disable search on first and last columns
	    ],
	    "serverMethod": 'post',
	    "ajax": {
        	"url": "./app/org_controller.php?action=load&endpoint=goal_types",
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
	        { title: `Goal Type`, data: null, render: function(data, type, row) {
	            return `<div>
	            		<span>${row.name}</span>
	                </div>`;
	        }},

	        { title: "Action", data: null, render: function(data, type, row) {
            		return `<div class="sflex scenter-items">
            			<span data-recid="${row.id}" class="fa edit_goalTypeInfo smt-5 cursor smr-10 fa-pencil"></span>
            			<span data-recid="${row.id}" class="fa delete_goalType smt-5 cursor fa-trash"></span>
                </div>`;
	        }},
	    ]
	});

	return false;
}

function handleGoalTypes() {
    // Ensure jQuery is loaded
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded. Cannot initialize handleGoalTypes.');
        return;
    }

    // Handle Add Goal Type form submission
    // Note: The form ID is 'addGoalTypeForm' from your HTML
    $('#addGoalTypeForm').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        handle_addGoalTypeForm(this); // 'this' refers to the form element
        return false;
    });

    load_goalTypes();

    // Handle Edit Goal Type action
    $(document).on('click', '.edit_goalTypeInfo', async function(e) {
        let id = $(this).data('recid');
        if (!id) {
            console.error("Edit action: Goal Type ID is missing.");
            // Consider showing a user-friendly error message
            return;
        }
        let modal = $('#edit_goalType'); // Assumes an edit modal with this ID exists

        if (modal.length === 0) {
            console.error("Edit Goal Type modal (#edit_goalType) not found.");
            alert("Edit functionality is currently unavailable. Modal not found."); // Replace with non-blocking UI
            return;
        }

        try {
            let data = await get_goalType(id);
            if (data) {
                let res;
                try {
                    res = JSON.parse(data)[0];
                } catch (parseError) {
                    console.error("Failed to parse goal type data for editing:", parseError, data);
                    toaster.warning('Could not load goal type details for editing.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                    return;
                }

                if(res) {
                    $(modal).find('#goalType_id').val(id); // Assumes an input with this ID exists in edit modal
                    $(modal).find('#goalTypeName4Edit').val(res.name); // Assumes an input with this ID
                    $(modal).find('#slcStatus').val(res.status); // Assumes a select/input with this ID
                    $(modal).modal('show');
                } else {
                     toaster.warning('Goal type details not found.', 'Not Found', { top: '30%', right: '20px', hide: true, duration: 5000 });
                }
            } else {
                 toaster.warning('Failed to retrieve goal type details.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            }
        } catch (err) {
            console.error('Error occurred while getting goal type for edit:', err);
            toaster.error('An unexpected error occurred. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        }
    });

    // Handle Edit Goal Type form submission
    // Assumes an edit form with ID #editGoalTypeForm exists
    $('#editGoalTypeForm').on('submit', function(e) {
        e.preventDefault();
        handle_editGoalTypeForm(this);
        return false;
    });

    // Handle Delete Goal Type action
    $(document).on('click', '.delete_goalType', async function(e) {
        let id = $(this).data('recid');
        if (!id) {
            console.error("Delete action: Goal Type ID is missing.");
            // Consider showing a user-friendly error message
            return;
        }

        // Ensure swal is available
        if (typeof swal === 'undefined') {
            console.error('SweetAlert (swal) is not loaded. Cannot show delete confirmation.');
            if (confirm(`Are you sure you want to delete this goal type? This action cannot be undone.`)) { // Fallback to native confirm
                 await processDeleteGoalType(id);
            }
            return;
        }

        swal({
            title: "Are you sure?",
            text: `You are about to delete this goal type. This action cannot be undone.`,
            icon: "warning",
            className: 'warning-swal',
            buttons: ["Cancel", "Yes, delete it"],
            dangerMode: true, // Emphasizes the destructive nature
        }).then(async (willDelete) => {
            if (willDelete) {
                await processDeleteGoalType(id);
            }
        });
    });
}

async function processDeleteGoalType(id) {
    let data = { id: id };
    try {
        // Assume send_orgPost is a global function for making POST requests
        if (typeof send_orgPost !== 'function') {
            console.error('send_orgPost function is not defined.');
            toaster.error('Cannot delete goal type. System error.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            return;
        }
        let response = await send_orgPost('delete goal_type', data); // Updated action
        if (response) {
            let res;
            try {
                res = JSON.parse(response);
            } catch (parseError) {
                console.error("Failed to parse delete response:", parseError, response);
                toaster.warning('Received an invalid response from the server after delete attempt.', 'Server Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                return;
            }

            if (res.error) {
                toaster.warning(res.msg || 'Could not delete goal type.', 'Deletion Failed', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg || 'Goal type deleted successfully.', 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    load_goalTypes(); // Reload the DataTable
                });
                console.log(res);
            }
        } else {
            console.log('Failed to delete goal type. Empty response from server.');
            toaster.error('Failed to delete goal type. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        }
    } catch (err) {
        console.error('Error occurred during goal type deletion:', err);
        toaster.error('An unexpected error occurred. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
    }
}

async function handle_addGoalTypeForm(form) {
    // Ensure necessary helper functions are available
    if (typeof clearErrors !== 'function' || typeof validateForm !== 'function' || typeof form_loading !== 'function' || typeof send_orgPost !== 'function' || typeof form_loadingUndo !== 'function' || typeof toaster === 'undefined') {
        console.error('One or more helper functions (clearErrors, validateForm, form_loading, send_orgPost, form_loadingUndo, toaster) are not defined.');
        alert('Cannot process form. A system error occurred.'); // Replace with non-blocking UI
        return;
    }

    clearErrors(); // Assumes this function clears previous form errors
    let error = validateForm(form); // Assumes this function validates the form and returns true if errors

    let goalTypeName = $(form).find('#goalTypeName').val(); // ID from your Add Goal Type form

    if (error) {
        console.log("Validation errors found in add goal type form.");
        return false;
    }
    if (!goalTypeName || goalTypeName.trim() === "") {
        console.log("Goal Type name is required.");
        // This should ideally be handled by validateForm, but as a fallback:
        $(form).find('#goalTypeName').addClass('is-invalid'); // Example: Bootstrap class
        $(form).find('#goalTypeName').next('.form-error').text('Goal Type name is required.').show();
        return false;
    }


    let formData = {
        name: goalTypeName,
    };

    form_loading(form); // Show loading indicator on the form

    try {
        let response = await send_orgPost('save goal_type', formData); // Updated action
        if (response) {
            let res;
            try {
                res = JSON.parse(response);
            } catch (parseError) {
                console.error("Failed to parse add response:", parseError, response);
                toaster.warning('Received an invalid response from the server.', 'Server Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                form_loadingUndo(form);
                return;
            }

            // Use Bootstrap's modal method if available, otherwise fallback
            if ($.fn.modal) {
                 $('#add_goalType').modal('hide'); // Hide the Add Goal Type modal
            } else {
                document.getElementById('add_goalType').style.display = 'none'; // Fallback for hiding
            }


            if (res.error) {
                toaster.warning(res.msg || 'Could not save goal type.', 'Save Failed', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg || 'Goal type saved successfully.', 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    form_loadingUndo(form);
                    load_goalTypes(); // Reload the DataTable
                    $(form)[0].reset(); // Reset the form
                });
            }
        } else {
            console.log('Failed to save goal type. Empty response from server.');
            toaster.error('Failed to save goal type. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            form_loadingUndo(form);
        }
    } catch (err) {
        console.error('Error occurred during add goal type form submission:', err);
        toaster.error('An unexpected error occurred while adding. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        form_loadingUndo(form);
    }
}

async function handle_editGoalTypeForm(form) {
     if (typeof clearErrors !== 'function' || typeof validateForm !== 'function' || typeof form_loading !== 'function' || typeof send_orgPost !== 'function' || typeof form_loadingUndo !== 'function' || typeof toaster === 'undefined') {
        console.error('One or more helper functions are not defined for edit form.');
        alert('Cannot process edit form. A system error occurred.');
        return;
    }

    clearErrors();
    let error = validateForm(form);

    let id = $(form).find('#goalType_id').val(); // Assumed ID for hidden input in edit form
    let name = $(form).find('#goalTypeName4Edit').val(); // Assumed ID for name input in edit form
    let slcStatus = $(form).find('#slcStatus').val(); // Assumed ID for status input/select in edit form

    if (error) {
        console.log("Validation errors found in edit goal type form.");
        return false;
    }
     if (!name || name.trim() === "") {
        console.log("Goal Type name is required for editing.");
        $(form).find('#goalTypeName4Edit').addClass('is-invalid').next('.form-error').text('Goal Type name is required.').show();
        return false;
    }


    let formData = {
        id: id,
        name: name,
        status: slcStatus // Changed from slcStatus to status to be more generic
    };

    form_loading(form);

    try {
        let response = await send_orgPost('update goal_type', formData); // Updated action
        if (response) {
            let res;
             try {
                res = JSON.parse(response);
            } catch (parseError) {
                console.error("Failed to parse edit response:", parseError, response);
                toaster.warning('Received an invalid response from the server.', 'Server Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                form_loadingUndo(form);
                return;
            }

            if (res.error) {
                toaster.warning(res.msg || 'Could not update goal type.', 'Update Failed', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg || 'Goal type updated successfully.', 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    form_loadingUndo(form);
                    load_goalTypes(); // Reload the DataTable
                });
            }
        } else {
            console.log('Failed to update goal type. Empty response from server.');
            toaster.error('Failed to update goal type. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            form_loadingUndo(form);
        }
    } catch (err) {
        console.error('Error occurred during edit goal type form submission:', err);
        toaster.error('An unexpected error occurred while updating. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        form_loadingUndo(form);
    }
}

async function get_goalType(id) {
    if (typeof send_orgPost !== 'function') {
        console.error('send_orgPost function is not defined. Cannot get goal type.');
        return null;
    }
    let data = { id: id };
    try {
        let response = await send_orgPost('get goal_type', data); // Updated action
        return response;
    } catch (err) {
        console.error('Error occurred while fetching goal type:', err);
        return null; // Return null or throw error as per desired error handling strategy
    }
}

// Award types
function load_awardTypes() {
    var datatable = $('#awardTypesDT').DataTable({
        "processing": true,
        "serverSide": true,
        "bDestroy": true,
        "searching": false,  
        "info": false,
        "columnDefs": [
            { "orderable": false, "searchable": false,  "targets": [1] }  // Disable search on first and last columns
        ],
        "serverMethod": 'post',
        "ajax": {
            "url": "./app/org_controller.php?action=load&endpoint=award_types",
            "method": "POST",
        },
        
        "createdRow": function(row, data, dataIndex) { 
            // Add your custom class to the row 
            $(row).addClass('table-row ' +data.status.toLowerCase());
        },
        columns: [
            { title: `Award Type`, data: null, render: function(data, type, row) {
                return `<div>
                        <span>${row.name}</span>
                    </div>`;
            }},

            { title: "Action", data: null, render: function(data, type, row) {
                return `<div class="sflex scenter-items">
                    <span data-recid="${row.id}" class="fa edit_awardTypeInfo smt-5 cursor smr-10 fa-pencil"></span>
                    <span data-recid="${row.id}" class="fa delete_awardType smt-5 cursor fa-trash"></span>
                </div>`;
            }},
        ]
    });

    return false;
}

function handleAwardTypes() {
    // Ensure jQuery is loaded
    if (typeof $ === 'undefined') {
        console.error('jQuery is not loaded. Cannot initialize handleAwardTypes.');
        return;
    }

    // Handle Add Award Type form submission
    $('#addAwardTypeForm').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission
        handle_addAwardTypeForm(this); // 'this' refers to the form element
        return false;
    });

    load_awardTypes();

    // Handle Edit Award Type action
    $(document).on('click', '.edit_awardTypeInfo', async function(e) {
        let id = $(this).data('recid');
        if (!id) {
            console.error("Edit action: Award Type ID is missing.");
            return;
        }
        let modal = $('#edit_awardType'); 

        if (modal.length === 0) {
            console.error("Edit Award Type modal (#edit_awardType) not found.");
            alert("Edit functionality is currently unavailable. Modal not found.");
            return;
        }

        try {
            let data = await get_awardType(id);
            if (data) {
                let res;
                try {
                    res = JSON.parse(data)[0];
                } catch (parseError) {
                    console.error("Failed to parse award type data for editing:", parseError, data);
                    toaster.warning('Could not load award type details for editing.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                    return;
                }

                if(res) {
                    $(modal).find('#awardType_id').val(id);
                    $(modal).find('#awardTypeName4Edit').val(res.name);
                    $(modal).find('#slcStatus').val(res.status);
                    $(modal).modal('show');
                } else {
                    toaster.warning('Award type details not found.', 'Not Found', { top: '30%', right: '20px', hide: true, duration: 5000 });
                }
            } else {
                toaster.warning('Failed to retrieve award type details.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            }
        } catch (err) {
            console.error('Error occurred while getting award type for edit:', err);
            toaster.error('An unexpected error occurred. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        }
    });

    // Handle Edit Award Type form submission
    $('#editAwardTypeForm').on('submit', function(e) {
        e.preventDefault();
        handle_editAwardTypeForm(this);
        return false;
    });

    // Handle Delete Award Type action
    $(document).on('click', '.delete_awardType', async function(e) {
        let id = $(e.currentTarget).data('recid');
        if (!id) {
            console.error("Delete action: Award Type ID is missing.");
            return;
        }

        // Ensure swal is available
        if (typeof swal === 'undefined') {
            console.error('SweetAlert (swal) is not loaded. Cannot show delete confirmation.');
            if (confirm(`Are you sure you want to delete this award type? This action cannot be undone.`)) { // Fallback to native confirm
                 await processDeleteAwardType(id);
            }
            return;
        }

        swal({
            title: "Are you sure?",
            text: `You are about to delete this award type. This action cannot be undone.`,
            icon: "warning",
            className: 'warning-swal',
            buttons: ["Cancel", "Yes, delete it"],
            dangerMode: true, // Emphasizes the destructive nature
        }).then(async (willDelete) => {
            if (willDelete) {
                await processDeleteAwardType(id);
            }
        });
    });
}

async function processDeleteAwardType(id) {
    let data = { id: id };
    try {
        let response = await send_orgPost('delete award_type', data);
        console.log(response)
        if (response) {
            let res;
            try {
                res = JSON.parse(response);
            } catch (parseError) {
                console.error("Failed to parse delete response:", parseError, response);
                toaster.warning('Received an invalid response from the server after delete attempt.', 'Server Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                return;
            }

            if (res.error) {
                toaster.warning(res.msg || 'Failed to delete award type.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                if (res.sql_error) {
                    console.error('SQL Error:', res.sql_error);
                }
            } else {
                toaster.success(res.msg || 'Award type deleted successfully.', 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    load_awardTypes(); // Reload the DataTable
                });
                console.log(res);
            }
        } else {
            console.log('Failed to delete award type. Empty response from server.');
            toaster.error('Failed to delete award type. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        }
    } catch (err) {
        console.error('Error occurred during delete award type operation:', err);
        toaster.error('An unexpected error occurred while deleting. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
    }
}

async function handle_addAwardTypeForm(form) {
    clearErrors();

    let awardTypeName = $(form).find('#awardTypeName').val();

    // Input validation
    let error = false;
    error = !validateField(awardTypeName, "Award Type name is required", 'awardTypeName') || error;

    if (error) return false;

    let formData = {
        awardTypeName: awardTypeName
    };

    form_loading(form);

    try {
        let response = await send_orgPost('save award_type', formData);

        if (response) {
            let res;
            try {
                res = JSON.parse(response);
            } catch (parseError) {
                console.error("Failed to parse add response:", parseError, response);
                toaster.warning('Received an invalid response from the server.', 'Server Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                form_loadingUndo(form);
                return;
            }

            if (res.error) {
                toaster.warning(res.msg || 'Failed to add award type.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                if (res.sql_error) {
                    console.error('SQL Error:', res.sql_error);
                }
                form_loadingUndo(form);
            } else {
                toaster.success(res.msg || 'Award type added successfully.', 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    $('#add_awardType').modal('hide');
                    form_loadingUndo(form);
                    $(form)[0].reset(); // Reset the form
                    load_awardTypes(); // Reload the DataTable
                });
            }
        } else {
            console.log('Failed to add award type. Empty response from server.');
            toaster.error('Failed to add award type. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            form_loadingUndo(form);
        }
    } catch (err) {
        console.error('Error occurred during add award type form submission:', err);
        toaster.error('An unexpected error occurred while adding. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        form_loadingUndo(form);
    }
}

async function handle_editAwardTypeForm(form) {
    clearErrors();

    let id = $(form).find('#awardType_id').val();
    let name = $(form).find('#awardTypeName4Edit').val();
    let status = $(form).find('#slcStatus').val();

    // Input validation
    let error = false;
    error = !validateField(name, "Award Type name is required", 'awardTypeName4Edit') || error;

    if (error) return false;

    let formData = {
        id: id,
        name: name,
        status: status
    };

    form_loading(form);

    try {
        let response = await send_orgPost('update award_type', formData);

        if (response) {
            let res;
            try {
                res = JSON.parse(response);
            } catch (parseError) {
                console.error("Failed to parse response:", parseError, response);
                toaster.warning('Received an invalid response from the server.', 'Server Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                form_loadingUndo(form);
                return;
            }

            if (res.error) {
                toaster.warning(res.msg || 'Failed to update award type.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                if (res.sql_error) {
                    console.error('SQL Error:', res.sql_error);
                }
                form_loadingUndo(form);
            } else {
                toaster.success(res.msg || 'Award type updated successfully.', 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    $('#edit_awardType').modal('hide');
                    form_loadingUndo(form);
                    load_awardTypes(); // Reload the DataTable
                });
            }
        } else {
            console.log('Failed to update award type. Empty response from server.');
            toaster.error('Failed to update award type. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            form_loadingUndo(form);
        }
    } catch (err) {
        console.error('Error occurred during edit award type form submission:', err);
        toaster.error('An unexpected error occurred while updating. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        form_loadingUndo(form);
    }
}

async function get_awardType(id) {
    if (typeof send_orgPost !== 'function') {
        console.error('send_orgPost function is not defined. Cannot get award type.');
        return null;
    }
    let data = { id: id };
    try {
        let response = await send_orgPost('get award_type', data);
        return response;
    } catch (err) {
        console.error('Error occurred while fetching award type:', err);
        return null;
    }
}

// Financial Accounts Management Functions
function load_financialAccounts() {
    $('#financialAccountsDT').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: 'app/org_controller.php?action=load&endpoint=financial_accounts',
            type: 'POST',
            error: function(xhr, error, code) {
                console.log('Error:', error, code);
                toaster.error('Failed to load financial accounts. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            }
        },
        columns: [
            { data: 'name', title: 'Account Name' },
            { data: 'type', title: 'Type' },
            { data: 'status', title: 'Status' },
            {
                data: null,
                title: 'Actions',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-primary me-1" onclick="get_financialAccount(${row.id})" title="Edit">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="delete_financialAccount(${row.id})" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true
    });
}

function handleFinancialAccounts() {
    load_financialAccounts();

    // Handle add form submission
    $('#addFinancialAccountForm').on('submit', function(e) {
        e.preventDefault();
        handle_addFinancialAccountForm(this);
    });

    // Handle edit form submission
    $('#editFinancialAccountForm').on('submit', function(e) {
        e.preventDefault();
        handle_editFinancialAccountForm(this);
    });
}

async function delete_financialAccount(id) {
    const result = await swal({
        title: "Are you sure?",
        text: "Once deleted, you will not be able to recover this financial account!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    });

    if (!result) return;

    let data = { id: id };

    try {
        let response = await send_orgPost('delete financial_account', data);

        if (response) {
            let res;
            try {
                res = JSON.parse(response);
            } catch (parseError) {
                console.error("Failed to parse delete response:", parseError, response);
                toaster.warning('Received an invalid response from the server after delete attempt.', 'Server Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                return;
            }

            if (res.error) {
                toaster.warning(res.msg || 'Failed to delete financial account.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                if (res.sql_error) {
                    console.error('SQL Error:', res.sql_error);
                }
            } else {
                toaster.success(res.msg || 'Financial account deleted successfully.', 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    load_financialAccounts(); // Reload the DataTable
                });
            }
        } else {
            console.log('Failed to delete financial account. Empty response from server.');
            toaster.error('Failed to delete financial account. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        }
    } catch (err) {
        console.error('Error occurred during delete financial account operation:', err);
        toaster.error('An unexpected error occurred while deleting. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
    }
}

async function handle_addFinancialAccountForm(form) {
    clearErrors();

    let financialAccountName = $(form).find('#financialAccountName').val();
    let accountType = $(form).find('#accountType').val();

    // Input validation
    let error = false;
    error = !validateField(financialAccountName, "Account name is required", 'financialAccountName') || error;
    error = !validateField(accountType, "Account type is required", 'accountType') || error;

    if (error) return false;

    let formData = {
        financialAccountName: financialAccountName,
        accountType: accountType
    };

    form_loading(form);

    try {
        let response = await send_orgPost('save financial_account', formData);

        if (response) {
            let res;
            try {
                res = JSON.parse(response);
            } catch (parseError) {
                console.error("Failed to parse response:", parseError, response);
                toaster.warning('Received an invalid response from the server.', 'Server Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                form_loadingUndo(form);
                return;
            }

            if (res.error) {
                toaster.warning(res.msg || 'Failed to add financial account.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                if (res.sql_error) {
                    console.error('SQL Error:', res.sql_error);
                }
                form_loadingUndo(form);
            } else {
                toaster.success(res.msg || 'Financial account added successfully.', 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    $('#add_financialAccount').modal('hide');
                    form_loadingUndo(form);
                    $(form)[0].reset(); // Reset the form
                    load_financialAccounts(); // Reload the DataTable
                });
            }
        } else {
            console.log('Failed to add financial account. Empty response from server.');
            toaster.error('Failed to add financial account. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            form_loadingUndo(form);
        }
    } catch (err) {
        console.error('Error occurred during add financial account form submission:', err);
        toaster.error('An unexpected error occurred while adding. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        form_loadingUndo(form);
    }
}

async function handle_editFinancialAccountForm(form) {
    clearErrors();

    let id = $(form).find('#financialAccount_id').val();
    let name = $(form).find('#financialAccountName4Edit').val();
    let type = $(form).find('#accountType4Edit').val();
    let status = $(form).find('#slcStatus4Edit').val();

    // Input validation
    let error = false;
    error = !validateField(name, "Account name is required", 'financialAccountName4Edit') || error;
    error = !validateField(type, "Account type is required", 'accountType4Edit') || error;

    if (error) return false;

    let formData = {
        financialAccount_id: id,
        financialAccountName4Edit: name,
        accountType4Edit: type,
        slcStatus4Edit: status
    };

    form_loading(form);

    try {
        let response = await send_orgPost('update financial_account', formData);

        if (response) {
            let res;
            try {
                res = JSON.parse(response);
            } catch (parseError) {
                console.error("Failed to parse response:", parseError, response);
                toaster.warning('Received an invalid response from the server.', 'Server Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                form_loadingUndo(form);
                return;
            }

            if (res.error) {
                toaster.warning(res.msg || 'Failed to update financial account.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                if (res.sql_error) {
                    console.error('SQL Error:', res.sql_error);
                }
                form_loadingUndo(form);
            } else {
                toaster.success(res.msg || 'Financial account updated successfully.', 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    $('#edit_financialAccount').modal('hide');
                    form_loadingUndo(form);
                    load_financialAccounts(); // Reload the DataTable
                });
            }
        } else {
            console.log('Failed to update financial account. Empty response from server.');
            toaster.error('Failed to update financial account. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            form_loadingUndo(form);
        }
    } catch (err) {
        console.error('Error occurred during edit financial account form submission:', err);
        toaster.error('An unexpected error occurred while updating. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        form_loadingUndo(form);
    }
}

async function get_financialAccount(id) {
    let data = { id: id };

    try {
        let response = await send_orgPost('get financial_account', data);

        if (response) {
            let res;
            try {
                res = JSON.parse(response);
            } catch (parseError) {
                console.error("Failed to parse response:", parseError, response);
                toaster.warning('Received an invalid response from the server.', 'Server Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                return;
            }

            if (res.length > 0) {
                let financialAccount = res[0];
                $('#financialAccount_id').val(financialAccount.id);
                $('#financialAccountName4Edit').val(financialAccount.name);
                $('#accountType4Edit').val(financialAccount.type);
                $('#slcStatus4Edit').val(financialAccount.status);
                $('#edit_financialAccount').modal('show');
            } else {
                toaster.warning('Financial account not found.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            }
        } else {
            console.log('Failed to get financial account. Empty response from server.');
            toaster.error('Failed to get financial account. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        }
    } catch (err) {
        console.error('Error occurred while fetching financial account:', err);
        toaster.error('An unexpected error occurred while fetching. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
    }
}

// Training Options Management Functions
function load_trainingOptions() {
    $('#trainingOptionsDT').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: 'app/org_controller.php?action=load&endpoint=training_options',
            type: 'POST',
            error: function(xhr, error, code) {
                console.log('Error:', error, code);
                toaster.error('Failed to load training options. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            }
        },
        columns: [
            { data: 'name', title: 'Training Option Name' },
            { data: 'status', title: 'Status' },
            {
                data: null,
                title: 'Actions',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-primary me-1" onclick="get_trainingOption(${row.id})" title="Edit">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="delete_trainingOption(${row.id})" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true
    });
}

function handleTrainingOptions() {
    load_trainingOptions();

    // Handle add form submission
    $('#addTrainingOptionForm').on('submit', function(e) {
        e.preventDefault();
        handle_addTrainingOptionForm(this);
    });

    // Handle edit form submission
    $('#editTrainingOptionForm').on('submit', function(e) {
        e.preventDefault();
        handle_editTrainingOptionForm(this);
    });
}

async function delete_trainingOption(id) {
    const result = await swal({
        title: "Are you sure?",
        text: "Once deleted, you will not be able to recover this training option!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    });

    if (!result) return;

    let data = { id: id };

    try {
        let response = await send_orgPost('delete training_options', data);

        if (response) {
            let res;
            try {
                res = JSON.parse(response);
            } catch (parseError) {
                console.error("Failed to parse delete response:", parseError, response);
                toaster.warning('Received an invalid response from the server after delete attempt.', 'Server Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                return;
            }

            if (res.error) {
                toaster.warning(res.msg || 'Failed to delete training option.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                if (res.sql_error) {
                    console.error('SQL Error:', res.sql_error);
                }
            } else {
                toaster.success(res.msg || 'Training option deleted successfully.', 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    load_trainingOptions(); // Reload the DataTable
                });
            }
        } else {
            console.log('Failed to delete training option. Empty response from server.');
            toaster.error('Failed to delete training option. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        }
    } catch (err) {
        console.error('Error occurred during delete training option operation:', err);
        toaster.error('An unexpected error occurred while deleting. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
    }
}

async function handle_addTrainingOptionForm(form) {
    clearErrors();

    let trainingOptionName = $(form).find('#trainingOptionName').val();

    // Input validation
    let error = false;
    error = !validateField(trainingOptionName, "Training option name is required", 'trainingOptionName') || error;

    if (error) return false;

    let formData = {
        name: trainingOptionName
    };

    form_loading(form);

    try {
        let response = await send_orgPost('save training_options', formData);

        if (response) {
            let res;
            try {
                res = JSON.parse(response);
            } catch (parseError) {
                console.error("Failed to parse response:", parseError, response);
                toaster.warning('Received an invalid response from the server.', 'Server Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                form_loadingUndo(form);
                return;
            }

            if (res.error) {
                toaster.warning(res.msg || 'Failed to add training option.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                if (res.sql_error) {
                    console.error('SQL Error:', res.sql_error);
                }
                form_loadingUndo(form);
            } else {
                toaster.success(res.msg || 'Training option added successfully.', 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    $('#add_trainingOption').modal('hide');
                    form_loadingUndo(form);
                    $(form)[0].reset(); // Reset the form
                    load_trainingOptions(); // Reload the DataTable
                });
            }
        } else {
            console.log('Failed to add training option. Empty response from server.');
            toaster.error('Failed to add training option. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            form_loadingUndo(form);
        }
    } catch (err) {
        console.error('Error occurred during add training option form submission:', err);
        toaster.error('An unexpected error occurred while adding. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        form_loadingUndo(form);
    }
}

async function handle_editTrainingOptionForm(form) {
    clearErrors();

    let id = $(form).find('#trainingOption_id').val();
    let name = $(form).find('#trainingOptionName4Edit').val();
    let status = $(form).find('#slcStatus4EditTrainingOption').val();

    // Input validation
    let error = false;
    error = !validateField(name, "Training option name is required", 'trainingOptionName4Edit') || error;

    if (error) return false;

    let formData = {
        id: id,
        name: name,
        status: status
    };

    form_loading(form);

    try {
        let response = await send_orgPost('update training_options', formData);

        if (response) {
            let res;
            try {
                res = JSON.parse(response);
            } catch (parseError) {
                console.error("Failed to parse response:", parseError, response);
                toaster.warning('Received an invalid response from the server.', 'Server Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                form_loadingUndo(form);
                return;
            }

            if (res.error) {
                toaster.warning(res.msg || 'Failed to update training option.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                if (res.sql_error) {
                    console.error('SQL Error:', res.sql_error);
                }
                form_loadingUndo(form);
            } else {
                toaster.success(res.msg || 'Training option updated successfully.', 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    $('#edit_trainingOption').modal('hide');
                    form_loadingUndo(form);
                    load_trainingOptions(); // Reload the DataTable
                });
            }
        } else {
            console.log('Failed to update training option. Empty response from server.');
            toaster.error('Failed to update training option. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            form_loadingUndo(form);
        }
    } catch (err) {
        console.error('Error occurred during edit training option form submission:', err);
        toaster.error('An unexpected error occurred while updating. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        form_loadingUndo(form);
    }
}

async function get_trainingOption(id) {
    let data = { id: id };

    try {
        let response = await send_orgPost('get training_options', data);

        if (response) {
            let res;
            try {
                res = JSON.parse(response);
            } catch (parseError) {
                console.error("Failed to parse response:", parseError, response);
                toaster.warning('Received an invalid response from the server.', 'Server Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                return;
            }

            if (res.length > 0) {
                let trainingOption = res[0];
                $('#trainingOption_id').val(trainingOption.id);
                $('#trainingOptionName4Edit').val(trainingOption.name);
                $('#slcStatus4EditTrainingOption').val(trainingOption.status);
                $('#edit_trainingOption').modal('show');
            } else {
                toaster.warning('Training option not found.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            }
        } else {
            console.log('Failed to get training option. Empty response from server.');
            toaster.error('Failed to get training option. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        }
    } catch (err) {
        console.error('Error occurred while fetching training option:', err);
        toaster.error('An unexpected error occurred while fetching. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
    }
}

// Training Types Management Functions
function load_trainingTypes() {
    $('#trainingTypesDT').DataTable({
        destroy: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: 'app/org_controller.php?action=load&endpoint=training_types',
            type: 'POST',
            error: function(xhr, error, code) {
                console.log('Error:', error, code);
                toaster.error('Failed to load training types. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            }
        },
        columns: [
            { data: 'name', title: 'Training Type Name' },
            { data: 'status', title: 'Status' },
            {
                data: null,
                title: 'Actions',
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-sm btn-primary me-1" onclick="get_trainingType(${row.id})" title="Edit">
                            <i class="fa fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="delete_trainingType(${row.id})" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    `;
                }
            }
        ],
        order: [[0, 'asc']],
        pageLength: 25,
        responsive: true
    });
}

function handleTrainingTypes() {
    load_trainingTypes();

    // Handle add form submission
    $('#addTrainingTypeForm').on('submit', function(e) {
        e.preventDefault();
        handle_addTrainingTypeForm(this);
    });

    // Handle edit form submission
    $('#editTrainingTypeForm').on('submit', function(e) {
        e.preventDefault();
        handle_editTrainingTypeForm(this);
    });
}

async function delete_trainingType(id) {
    const result = await swal({
        title: "Are you sure?",
        text: "Once deleted, you will not be able to recover this training type!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    });

    if (!result) return;

    let data = { id: id };

    try {
        let response = await send_orgPost('delete training_types', data);

        if (response) {
            let res;
            try {
                res = JSON.parse(response);
            } catch (parseError) {
                console.error("Failed to parse delete response:", parseError, response);
                toaster.warning('Received an invalid response from the server after delete attempt.', 'Server Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                return;
            }

            if (res.error) {
                toaster.warning(res.msg || 'Failed to delete training type.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                if (res.sql_error) {
                    console.error('SQL Error:', res.sql_error);
                }
            } else {
                toaster.success(res.msg || 'Training type deleted successfully.', 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    load_trainingTypes(); // Reload the DataTable
                });
            }
        } else {
            console.log('Failed to delete training type. Empty response from server.');
            toaster.error('Failed to delete training type. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        }
    } catch (err) {
        console.error('Error occurred during delete training type operation:', err);
        toaster.error('An unexpected error occurred while deleting. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
    }
}

async function handle_addTrainingTypeForm(form) {
    clearErrors();

    let trainingTypeName = $(form).find('#trainingTypeName').val();

    // Input validation
    let error = false;
    error = !validateField(trainingTypeName, "Training type name is required", 'trainingTypeName') || error;

    if (error) return false;

    let formData = {
        name: trainingTypeName
    };

    form_loading(form);

    try {
        let response = await send_orgPost('save training_types', formData);

        if (response) {
            let res;
            try {
                res = JSON.parse(response);
            } catch (parseError) {
                console.error("Failed to parse response:", parseError, response);
                toaster.warning('Received an invalid response from the server.', 'Server Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                form_loadingUndo(form);
                return;
            }

            if (res.error) {
                toaster.warning(res.msg || 'Failed to add training type.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                if (res.sql_error) {
                    console.error('SQL Error:', res.sql_error);
                }
                form_loadingUndo(form);
            } else {
                toaster.success(res.msg || 'Training type added successfully.', 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    $('#add_trainingType').modal('hide');
                    form_loadingUndo(form);
                    $(form)[0].reset(); // Reset the form
                    load_trainingTypes(); // Reload the DataTable
                });
            }
        } else {
            console.log('Failed to add training type. Empty response from server.');
            toaster.error('Failed to add training type. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            form_loadingUndo(form);
        }
    } catch (err) {
        console.error('Error occurred during add training type form submission:', err);
        toaster.error('An unexpected error occurred while adding. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        form_loadingUndo(form);
    }
}

async function handle_editTrainingTypeForm(form) {
    clearErrors();

    let id = $(form).find('#trainingType_id').val();
    let name = $(form).find('#trainingTypeName4Edit').val();
    let status = $(form).find('#slcStatus4EditTrainingType').val();

    // Input validation
    let error = false;
    error = !validateField(name, "Training type name is required", 'trainingTypeName4Edit') || error;

    if (error) return false;

    let formData = {
        id: id,
        name: name,
        status: status
    };

    form_loading(form);

    try {
        let response = await send_orgPost('update training_types', formData);

        if (response) {
            let res;
            try {
                res = JSON.parse(response);
            } catch (parseError) {
                console.error("Failed to parse response:", parseError, response);
                toaster.warning('Received an invalid response from the server.', 'Server Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                form_loadingUndo(form);
                return;
            }

            if (res.error) {
                toaster.warning(res.msg || 'Failed to update training type.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                if (res.sql_error) {
                    console.error('SQL Error:', res.sql_error);
                }
                form_loadingUndo(form);
            } else {
                toaster.success(res.msg || 'Training type updated successfully.', 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    $('#edit_trainingType').modal('hide');
                    form_loadingUndo(form);
                    load_trainingTypes(); // Reload the DataTable
                });
            }
        } else {
            console.log('Failed to update training type. Empty response from server.');
            toaster.error('Failed to update training type. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            form_loadingUndo(form);
        }
    } catch (err) {
        console.error('Error occurred during edit training type form submission:', err);
        toaster.error('An unexpected error occurred while updating. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        form_loadingUndo(form);
    }
}

async function get_trainingType(id) {
    let data = { id: id };

    try {
        let response = await send_orgPost('get training_types', data);

        if (response) {
            let res;
            try {
                res = JSON.parse(response);
            } catch (parseError) {
                console.error("Failed to parse response:", parseError, response);
                toaster.warning('Received an invalid response from the server.', 'Server Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
                return;
            }

            if (res.length > 0) {
                let trainingType = res[0];
                $('#trainingType_id').val(trainingType.id);
                $('#trainingTypeName4Edit').val(trainingType.name);
                $('#slcStatus4EditTrainingType').val(trainingType.status);
                $('#edit_trainingType').modal('show');
            } else {
                toaster.warning('Training type not found.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            }
        } else {
            console.log('Failed to get training type. Empty response from server.');
            toaster.error('Failed to get training type. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        }
    } catch (err) {
        console.error('Error occurred while fetching training type:', err);
        toaster.error('An unexpected error occurred while fetching. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
    }
}

document.addEventListener("DOMContentLoaded", function() {
    handleOrg();
    handleBranches();
    handleStates();
    handleLocations();
    // handleBanks();
    handleDesignations();
    handleProjects();
    handleContractTypes();
    handleBudgetCodes();
    handleAllBanks();
    handleSubTypes();
    handleGoalTypes();
    handleAwardTypes();
    handleFinancialAccounts();
    handleTrainingOptions();
    handleTrainingTypes();
    handleGrantCodes();
});
