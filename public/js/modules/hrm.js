async function send_hrmPost(str, data) {
    let [action, endpoint] = str.split(' ');

    try {
        const response = await $.post(`${base_url}/app/hrm_controller.php?action=${action}&endpoint=${endpoint}`, data);
        return response;
    } catch (error) {
        console.error('Error occurred during the request:', error);
        return null;
    }
}
document.addEventListener("DOMContentLoaded", function () {
    // console.log('HRM is here')
    // $('#add_employee').modal('show');

    $(document).on('click', '.add-educationRow', function (e) {
        e.preventDefault();
        let prevRow = $(e.target).closest('.row');
        // Hide all "Add" buttons
        $('button.add-educationRow').css('display', 'none');
        $('button.remove-educationRow').css('display', 'block');
        let newRow = `<div class="row education-row added">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="form-group">
                    <input type="text" class="form-control degree" id="degree" name="degree">
                    <span class="form-error text-danger">This is error</span>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="form-group">
                    <input type="text" class="form-control institution" id="institution" name="institution">
                    <span class="form-error text-danger">This is error</span>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-2">
                <div class="form-group">
                    <input type="text" class="form-control startYear" onkeypress="return isNumberKey(event)" id="startYear" name="startYear">
                    <span class="form-error text-danger">This is error</span>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-2">
                <div class="form-group">
                    <input type="text" class="form-control endYear" onkeypress="return isNumberKey(event)" id="endYear" name="endYear">
                    <span class="form-error text-danger">This is error</span>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-1">
                <div class="form-group">
                    <button type="button" class="btn form-control add-educationRow btn-info cursor" style="color: #fff;">
                        <i class="fa fa-plus-square"></i>
                    </button>
                    <button type="button" class="btn form-control remove-educationRow btn-danger cursor" style="display: none;">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>`;

        // Insert the new row after the current row
        $(prevRow).after(newRow);
    });

    $(document).on('click', '.remove-educationRow', function (e) {
        e.preventDefault();
        let prevRow = $(e.target).closest('.row');
        $(prevRow).fadeOut(500, function () {
            $(this).remove();
        });
    });

    $('#addEmployeeForm').on('submit', (e) => {
        handle_addEmployeeForm(e.target);
        return false
    })

    $('#editEmployeeForm').on('submit', (e) => {
        handle_editEmployeeForm(e.target);
        return false
    })

    $('#profile-img').on('change', async (e) => {
        let employee_id = $('#employee_id').val();
        let fileInput = $('#profile-img');
        let file = fileInput[0].files[0];
        let allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        // Validate file type
        if (!file) {
            alert('Please select a file.');
            return;
        }

        let ext = file.name.split('.').pop().toLowerCase();
        if (!allowedExtensions.includes(ext)) {
            alert('Invalid file type. Please upload an image (jpg, jpeg, png, gif).');
            return;
        }

        // Check file size (e.g., 5MB max)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size exceeds the maximum limit of 5MB.');
            return;
        }

        let formData = new FormData();
        formData.append('employee_id', employee_id);
        formData.append('avatar', file);

        var ajax = new XMLHttpRequest();
        ajax.addEventListener("load", function (event) {
            console.log(event.target.response)
            let res = JSON.parse(event.target.response)
            if (res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
                return false;
            } else {
                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 2000 }).then(() => {
                    location.reload();
                });
            }
        });

        ajax.open("POST", `${base_url}/app/hrm_controller.php?action=update&endpoint=employee_avatar`);
        ajax.send(formData);


    });


    // Upload emplyees
    $('#upload_employeesInput').on('change', async (e) => {
        let fileInput = $('#upload_employeesInput');
        let file = fileInput[0].files[0];
        let allowedExtensions = ['csv'];

        // Validate file type
        if (!file) {
            $('#upload_employeesInput').val('');
            alert('Please select a file.');
            return;
        }

        let ext = file.name.split('.').pop().toLowerCase();
        if (!allowedExtensions.includes(ext)) {
            $('#upload_employeesInput').val('');
            alert('Invalid file type. Please upload a csv  file.');
            return;
        }
        return false;
    });

    $('#upload_employeesForm').on('submit', (e) => {
        handle_upload_employeesForm(e.target);
        return false
    })

    load_employees();

    // Delete employee
    $(document).on('click', '.delete_employee', async (e) => {
        let id = $(e.currentTarget).data('recid');
        swal({
            title: "Are you sure?",
            text: `You are going to delete this employee.`,
            icon: "warning",
            className: 'warning-swal',
            buttons: ["Cancel", "Yes, delete"],
        }).then(async (confirm) => {
            if (confirm) {
                let data = { id: id };
                try {
                    let response = await send_hrmPost('delete employee', data);
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

    $('.filter#slcDepartment, .filter#slcState, .filter#slcLocation, .filter#slcStatus').on('change', () => {
        let department = $('.filter#slcDepartment').val();
        let state = $('.filter#slcState').val();
        let location = $('.filter#slcLocation').val();
        let status = $('.filter#slcStatus').val();

        load_employees(department, state, location, status);
    })


    // Documents
    handleDocs();
    handleDocTypes();
    handleEmpDocs();
    $('.my-select').selectpicker({
        noneResultsText: "No results found"
    });

    handleAddAwards();
});


function load_employees(department = '', state = '', location = '', status = '') {
    const dt = $('#employeesDT').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        searching: true,
        info: true,
        serverMethod: 'POST',
        ajax: {
            url: './app/hrm_controller.php?action=load&endpoint=employees',
            data: function (d) {
                d.department = department;
                d.state = state;
                d.location = location;
                d.status = status;
            }
        },

        createdRow: function (row, data) {
            $(row).addClass('table-row ' + (data.status || '').toLowerCase());
        },

        columns: [
            { // Checkbox column
                title: `<input type="checkbox" id="select-all">`,
                data: 'employee_id',
                name: 'select',
                orderable: false,
                searchable: false,
                className: 'select-checkbox',
                render: (data) => `<input type="checkbox" class="row-select" value="${data}">`
            },
            { title: 'Staff No.', data: 'staff_no', name: 'staff_no', className: 'staff_no', defaultContent: '' },
            { title: 'Full name', data: 'full_name', name: 'full_name', className: 'full_name', defaultContent: '' },
            { title: 'Phone Number', data: 'phone_number', name: 'phone_number', className: 'phone_number', defaultContent: '' },
            { title: 'Email', data: 'email', name: 'email', className: 'email', defaultContent: '' },
            { title: 'Gender', data: 'gender', name: 'gender', className: 'gender', defaultContent: '' },
            { title: 'DOB', data: 'date_of_birth', name: 'date_of_birth', className: 'date_of_birth', render: d => formatDate(d) },
            { title: 'State', data: 'state', name: 'state', className: 'state', defaultContent: '' },
            { title: 'City', data: 'city', name: 'city', className: 'city', defaultContent: '' },
            { title: 'Address', data: 'address', name: 'address', className: 'address', defaultContent: '' },
            { title: 'Department', data: 'branch', name: 'branch', className: 'department', defaultContent: '' },
            { title: 'Duty Location', data: 'location_name', name: 'location_name', className: 'location_name', defaultContent: '' },
            { title: 'Position', data: 'position', name: 'position', className: 'position', defaultContent: '' },
            { title: 'Project', data: 'project', name: 'project', className: 'project', defaultContent: '' },
            { title: 'Designation', data: 'designation', name: 'designation', className: 'designation', defaultContent: '' },
            { title: 'Hire date', data: 'hire_date', name: 'hire_date', className: 'hire_date', render: d => formatDate(d) },
            { title: 'Contract start', data: 'contract_start', name: 'contract_start', className: 'contract_start', render: d => formatDate(d) },
            { title: 'Contract end', data: 'contract_end', name: 'contract_end', className: 'contract_end', render: d => formatDate(d) },
            { title: 'Work days', data: 'work_days', name: 'work_days', className: 'work_days', render: d => `${d} days/week` },
            { title: 'Work hours', data: 'work_hours', name: 'work_hours', className: 'work_hours', render: d => `${d} hours/day` },
            { title: 'Budget code', data: 'budget_code', name: 'budget_code', className: 'budget_code', defaultContent: '' },
            { title: 'Salary', data: 'salary', name: 'salary', className: 'salary', render: d => formatMoney(d) },
            { title: 'MoH Contract', data: 'moh_contract', name: 'moh_contract', className: 'moh_contract', defaultContent: '' },
            { title: 'Bank', data: 'payment_bank', name: 'payment_bank', className: 'bank', defaultContent: '' },
            { title: 'Account number', data: 'payment_account', name: 'payment_account', className: 'account_number', defaultContent: '' },
            { title: 'Grade', data: 'grade', name: 'grade', className: 'grade', defaultContent: '' },
            { title: 'Tax exempt', data: 'tax_exempt', name: 'tax_exempt', className: 'tax_exempt', defaultContent: '' },
            { title: 'Seniority', data: 'seniority', name: 'seniority', className: 'seniority', defaultContent: '' },
            { title: 'Status', data: 'status', name: 'status', className: 'status', defaultContent: '' },
            {
                title: 'Action',
                data: null,
                name: 'action',
                className: 'action',
                orderable: false,
                searchable: false,
                render: (data, type, row) => {
                    const canEdit = user_permissions['edit_employees'] === 'on';
                    const canDelete = user_permissions['delete_employees'] === 'on';

                    return `
                    <div class="sflex scenter-items">
                        <a href="${base_url}/employees/show/${row.employee_id}" 
                        class="fa smt-5 cursor smr-10 fa-eye" title="View"></a>

                        ${canEdit
                            ? `<a href="${base_url}/employees/edit/${row.employee_id}" 
                                class="fa smt-5 cursor smr-10 fa-pencil" title="Edit"></a>`
                            : ''}

                        ${canDelete
                            ? `<span data-recid="${row.employee_id}" 
                                class="fa delete_employee smt-5 cursor fa-trash" 
                                title="Delete"></span>`
                            : ''}
                    </div>
                `;
                }
            },
        ],

        columnDefs: [
            { targets: [0, -1], orderable: false, searchable: false }
        ],

        initComplete: function () {
            applyColumnVisibility(this.api());
            bindBulkActions();
        }
    });

    function applyColumnVisibility(api) {
        if (!Array.isArray(window.tableColumns)) return;
        const columns = api.settings()[0].aoColumns;
        const map = {};
        columns.forEach((col, idx) => { map[col.data] = idx; });

        // Hide all except checkbox & action
        columns.forEach((col, idx) => {
            if (col.data !== null) api.column(idx).visible(false, false);
        });

        window.tableColumns.forEach(key => {
            if (map[key] !== undefined) {
                api.column(map[key]).visible(true, false);
            }
        });

        // Always show checkbox and action
        api.column(0).visible(true, false);
        api.column(columns.length - 1).visible(true, false);
        api.columns.adjust().draw(false);
    }

    function bindBulkActions() {
        $(document).on('click', '#applyBulk', (e) => {
            // console.log(e)
            let value = $("#bulkAction").val();
            const ids = $('.row-select:checked').map(function () {
                return $(this).val();
            }).get();

            if (ids.length === 0) {
                swal('Sorry', 'No employees selected', 'error');
                return;
            }

            // console.log(ids)
            if (value == 'delete') {
                $(e.target).html('Please wait...')
                $(e.target).attr('disabled', true)
                swal({
                    title: "Are you sure?",
                    text: "You are going to delete all selected employees!",
                    icon: "warning",
                    className: 'warning-swal',
                    buttons: ["Cancel", "Yes, delete"],
                })
                    .then((willDelete) => {
                        if (willDelete) {
                            $.post(`${base_url}/app/hrm_controller.php?action=delete&endpoint=employeeBulk`, { ids: ids, action: value })
                                .then(response => {
                                    console.log(response)
                                    let res = JSON.parse(response);
                                    if (!res.error) {
                                        toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                                            window.location.reload();
                                        });
                                    } else {
                                        toaster.error(res.msg, 'Error');
                                    }
                                })
                        } else {
                            $(e.target).html('Apply')
                            $(e.target).attr('disabled', false)
                        }
                    });
            }
        })

        // Select/Deselect all
        $('#select-all').on('change', function () {
            $('.row-select').prop('checked', $(this).is(':checked'));
        });

    }

    return false;
}
async function handle_addEmployeeForm(form) {
    clearErrors();

    let error = validateForm(form)

    let fullName = $(form).find('#full-name').val();
    let phone = $(form).find('#phone').val();
    let email = $(form).find('#email').val();
    let staffNo = $(form).find('#staffNo').val();
    let nationalID = $(form).find('#nationalID').val();
    let gender = $(form).find('#gender').val();
    let dob = $(form).find('#dob').val();
    let address = $(form).find('#address').val();
    let state = $(form).find('#state').val();
    let stateName = state ? $(form).find('#state option:selected').text() : '';
    let city = $(form).find('#city').val();
    let bankName = $(form).find('#bankName').val();
    let accountNo = $(form).find('#accountNo').val();

    let position = $(form).find('#position').val();
    let project_id = $(form).find('#project').val();
    let project = $(form).find('#project option:selected').val();
    let dep = $(form).find('#dep').val();
    let depName = dep ? $(form).find('#dep option:selected').text() : '';
    let dutyStation = $(form).find('#dutyStation').val();
    let dutyStationName = $(form).find('#dutyStation option:selected').val();
    let designation = $(form).find('#designation').val();
    let contractType = $(form).find('#contractType').val();
    let mohContract = $(form).find('#mohContract').val();
    let grade = $(form).find('#grade').val();
    let salary = $(form).find('#salary').val();
    let budgetCode = $(form).find('#budgetCode').val();
    let taxExempt = $(form).find('#taxExempt').val();
    let hireDate = $(form).find('#hireDate').val();
    let currentContract = $(form).find('#currentContract').val();
    let contractEnd = $(form).find('#contractEnd').val();
    let seniority = $(form).find('#seniority').val();
    let workDays = $(form).find('#workDays').val();
    let workHours = $(form).find('#workHours').val();

    let degree = [];
    let institution = [];
    let startYear = [];
    let endYear = [];

    console.log(project_id, project)


    $('.row.education-row').each((i, el) => {
        if ($(el).find('.degree').val()) {
            degree.push($(el).find('.degree').val());
            institution.push($(el).find('.institution').val());
            startYear.push($(el).find('.startYear').val());
            endYear.push($(el).find('.endYear').val());
        }
    })

    console.log(degree, institution, startYear, endYear)

    if (error) return false;

    form_loading(form);

    let formData = {
        staff_no: staffNo,
        full_name: fullName,
        email: email,
        phone_number: phone,
        national_id: nationalID,
        gender: gender,
        date_of_birth: dob,
        state_id: state,
        state: stateName,
        city: city,
        address: address,
        branch_id: dep,
        branch: depName,
        location_id: dutyStation,
        location_name: dutyStationName,
        position: position,
        project_id: project_id,
        project: project,
        designation: designation,
        hire_date: hireDate,
        contract_start: currentContract,
        contract_end: contractEnd,
        work_days: workDays,
        work_hours: workHours,
        contract_type: contractType,
        salary: salary,
        budget_code: budgetCode,
        moh_contract: mohContract,
        payment_bank: bankName,
        payment_account: accountNo,
        grade: grade,
        tax_exempt: taxExempt,
        seniority: seniority,

        degree: degree,
        institution: institution,
        startYear: startYear,
        endYear: endYear

    }

    try {
        let response = await send_hrmPost('save employee', formData);
        console.log(response)
        // return false;

        if (response) {
            let res = JSON.parse(response)
            $('#add_branch').modal('hide');
            if (res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 2000 }).then(() => {
                    window.location = `${base_url}/employees`
                });
                console.log(res)
            }
        } else {
            console.log('Failed to save branch.' + response);
        }

    } catch (err) {
        console.error('Error occurred during form submission:', err);
    }

    return false;
}

async function handle_editEmployeeForm(form) {
    clearErrors();

    let error = validateForm(form)

    let employee_id = $(form).find('#employee_id').val();
    let fullName = $(form).find('#full-name').val();
    let phone = $(form).find('#phone').val();
    let email = $(form).find('#email').val();
    let slcStatus = $(form).find('#slcStatus').val();
    let staffNo = $(form).find('#staffNo').val();
    let nationalID = $(form).find('#nationalID').val();
    let gender = $(form).find('#gender').val();
    let dob = $(form).find('#dob').val();
    let address = $(form).find('#address').val();
    let state = $(form).find('#state').val();
    let stateName = state ? $(form).find('#state option:selected').text() : '';
    let city = $(form).find('#city').val();
    let bankName = $(form).find('#bankName').val();
    let accountNo = $(form).find('#accountNo').val();

    let position = $(form).find('#position').val();
    let project_id = $(form).find('#project').val();
    let project = $(form).find('#project option:selected').text();
    let dep = $(form).find('#dep').val();
    let depName = dep ? $(form).find('#dep option:selected').text() : '';
    let dutyStation = $(form).find('#dutyStation').val();
    let dutyStationName = $(form).find('#dutyStation option:selected').text();
    let designation = $(form).find('#designation').val();
    let contractType = $(form).find('#contractType').val();
    let mohContract = $(form).find('#mohContract').val();
    let grade = $(form).find('#grade').val();
    let salary = $(form).find('#salary').val();
    let budgetCode = $(form).find('#budgetCode').val();
    let taxExempt = $(form).find('#taxExempt').val();
    let hireDate = $(form).find('#hireDate').val();
    let currentContract = $(form).find('#currentContract').val();
    let contractEnd = $(form).find('#contractEnd').val();
    let seniority = $(form).find('#seniority').val();
    let workDays = $(form).find('#workDays').val();
    let workHours = $(form).find('#workHours').val();

    let degree = [];
    let institution = [];
    let startYear = [];
    let endYear = [];

    $('.row.education-row').each((i, el) => {
        if ($(el).find('.degree').val()) {
            degree.push($(el).find('.degree').val());
            institution.push($(el).find('.institution').val());
            startYear.push($(el).find('.startYear').val());
            endYear.push($(el).find('.endYear').val());
        }
    })

    console.log(degree, institution, startYear, endYear)

    // return false;

    if (error) return false;

    form_loading(form);

    let formData = {
        employee_id: employee_id,
        staff_no: staffNo,
        full_name: fullName,
        email: email,
        status: slcStatus,
        phone_number: phone,
        national_id: nationalID,
        gender: gender,
        date_of_birth: dob,
        state_id: state,
        state: stateName,
        city: city,
        address: address,
        branch_id: dep,
        branch: depName,
        location_id: dutyStation,
        location_name: dutyStationName,
        position: position,
        project_id: project_id,
        project: project,
        designation: designation,
        hire_date: hireDate,
        contract_start: currentContract,
        contract_end: contractEnd,
        work_days: workDays,
        work_hours: workHours,
        contract_type: contractType,
        salary: salary,
        budget_code: budgetCode,
        moh_contract: mohContract,
        payment_bank: bankName,
        payment_account: accountNo,
        grade: grade,
        tax_exempt: taxExempt,
        seniority: seniority,

        degree: degree,
        institution: institution,
        startYear: startYear,
        endYear: endYear

    }

    try {
        let response = await send_hrmPost('update employee', formData);
        console.log(response)
        // return false;

        if (response) {
            let res = JSON.parse(response)
            $('#add_branch').modal('hide');
            if (res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 2000 }).then(() => {
                    window.location = `${base_url}/employees`
                });
                console.log(res)
            }
        } else {
            console.log('Failed to save branch.' + response);
        }

    } catch (err) {
        console.error('Error occurred during form submission:', err);
    }

    return false;
}

async function handle_upload_employeesForm(form) {
    let fileInput = $(form).find('#upload_employeesInput');
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

    ajax.open("POST", `${base_url}/app/hrm_controller.php?action=save&endpoint=upload_employees`);
    ajax.send(formData);
}

// Documents
function handleDocs() {
    load_folders();
    $('#addFolderForm').on('submit', (e) => {
        handle_addFolderForm(e.target);
        return false;
    });
    $('#editFolderForm').on('submit', (e) => {
        handle_editFolderForm(e.target);
        return false;
    });

    // Search
    $('#searchFolder').on('input', () => {
        load_folders();
    });
}

async function handle_addFolderForm(form) {
    clearErrors();
    let error = validateForm(form);

    let name = $(form).find('#folderName').val();

    if (error) return false;

    let formData = {
        name: name
    };

    form_loading(form);

    try {
        let response = await send_hrmPost('save folder', formData);
        console.log(response);
        if (response) {
            let res = JSON.parse(response);
            if (res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    form_loadingUndo(form);
                    $('#add_folder').modal('hide');
                    load_folders();
                });
            }
        } else {
            console.log('Failed to save folder.' + response);
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
    }
}

function editFolder(id, name) {
    $('#editFolderId').val(id);
    $('#editFolderName').val(name);
    $('#edit_folder').modal('show');
}

function deleteFolder(id) {
    swal({
        title: "Are you sure?",
        text: "Once deleted, you will not be able to recover this folder!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
        .then((willDelete) => {
            if (willDelete) {
                $.post(`${base_url}/app/hrm_controller.php?action=delete&endpoint=folder`, { id })
                    .then(response => {
                        let res = JSON.parse(response);
                        if (!res.error) {
                            toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 });
                            load_folders();
                        } else {
                            toaster.error(res.msg, 'Error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        toaster.error('Failed to delete folder', 'Error');
                    });
            }
        });
}

async function handle_editFolderForm(form) {
    clearErrors();
    let error = validateForm(form);

    if (error) return false;

    let formData = {
        id: $(form).find('#editFolderId').val(),
        name: $(form).find('#editFolderName').val()
    };

    form_loading(form);

    try {
        let response = await send_hrmPost('update folder', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response);
            if (res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    form_loadingUndo(form);
                    $('#edit_folder').modal('hide');
                    load_folders();
                });
            }
        } else {
            console.log('Failed to update folder.' + response);
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
    }
}

async function load_folders() {
    let search = $('#searchFolder').val() || '';
    const response = await $.post(`${base_url}/app/hrm_controller.php?action=load&endpoint=folders`, { search });
    // console.log(response)
    let res = JSON.parse(response)
    if (!res.error) {
        let folders = '';
        res.data.map((folder) => {
            folders += `<div class="col-sm-6 col-md-4 col-lg-2 mb-4">
                <div class="card text-center shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Folder</span>
                        <div class="dropdown">
                            <button class="btn btn-sm" type="button" id="dropdownMenuButton${folder.id}" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton${folder.id}">
                                <li><a class="dropdown-item" href="#" onclick="editFolder(${folder.id}, '${folder.name}'); return false;">Edit</a></li>
                                <li><a class="dropdown-item" href="#" onclick="deleteFolder(${folder.id}); return false;">Delete</a></li>
                            </ul>
                        </div>
                    </div>
                    <a href="${base_url}/documents/${folder.id}" class="card-body text-decoration-none">
                        <i class="bi bi-folder fs-1 text-primary"></i>
                        <h6 class="card-title mt-2 ">${folder.name}</h6>
                    </a>
                </div>
            </div>`;
        });

        // Add the "Add Folder" button only if user_type != employee
        if (user_type != 'employee') {
            folders += `<a href="#" data-bs-toggle="modal" data-bs-target="#add_folder" class="col-sm-6 col-md-4 col-lg-2 mb-4 text-decoration-none">
                <div class="card text-center shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>New Folder</span>
                </div>
                <div class="card-body">
                    <i class="bi bi-plus-square fs-1 text-primary"></i>
                    <h6 class="card-title mt-2 ">Add Folder</h6>
                </div>
            </div>
            </a>`;
        }

        // console.log(folders)

        $('#folders').html(folders);
    }
    return response;
}

// Document Types
function load_docTypes() {
    var datatable = $('#docTypesDT').DataTable({
        "processing": true,
        "serverSide": true,
        "bDestroy": true,
        "columnDefs": [
            { "orderable": false, "searchable": false, "targets": [2] }
        ],
        "serverMethod": 'post',
        "ajax": {
            "url": `${base_url}/app/hrm_controller.php?action=load&endpoint=doc_types`,
            "method": "POST",
            // dataFilter: function(data) {
            // 	console.log(data)
            // }
        },
        columns: [
            {
                title: `Document Type Name`, data: null, render: function (data, type, row) {
                    return `<div>
	            		<span>${row.name}</span>
	                </div>`;
                }
            },

            {
                title: `Created At`, data: null, render: function (data, type, row) {
                    return `<div>
	            		<span>${row.created_at}</span>
	                </div>`;
                }
            },

            {
                title: "Action", data: null, render: function (data, type, row) {
                    return `<div class="sflex scenter-items">
            		<span data-recid="${row.id}" class="fa edit_docType smt-5 cursor smr-10 fa-pencil"></span>
            		<span data-recid="${row.id}" class="fa delete_docType smt-5 cursor fa-trash"></span>
                </div>`;
                }
            },
        ]
    });
    return false;
}

function handleDocTypes() {
    $('#addDocTypeForm').on('submit', (e) => {
        handle_addDocTypeForm(e.target);
        return false;
    });

    load_docTypes();

    $(document).on('click', '.edit_docType', async (e) => {
        let id = $(e.currentTarget).data('recid');
        let modal = $('#edit_docType');
        let data = await get_docType(id);

        if (data) {
            let res = JSON.parse(data)[0];
            $(modal).find('#docType_id').val(id);
            $(modal).find('#typeName4Edit').val(res.name);
        }
        $(modal).modal('show');
    });

    $('#editDocTypeForm').on('submit', (e) => {
        handle_editDocTypeForm(e.target);
        return false;
    });

    $(document).on('click', '.delete_docType', async (e) => {
        let id = $(e.currentTarget).data('recid');
        swal({
            title: "Are you sure?",
            text: "You are going to delete this document type.",
            icon: "warning",
            className: 'warning-swal',
            buttons: ["Cancel", "Yes, delete"],
        }).then(async (confirm) => {
            if (confirm) {
                let data = { id: id };
                try {
                    let response = await send_hrmPost('delete doc_types', data);
                    if (response) {
                        let res = JSON.parse(response);
                        if (res.error) {
                            toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
                        } else {
                            toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 2000 }).then(() => {
                                load_docTypes();
                            });
                        }
                    }
                } catch (err) {
                    console.error('Error occurred:', err);
                }
            }
        });
    });
}

async function handle_addDocTypeForm(form) {
    clearErrors();
    let name = $(form).find('#typeName').val();

    let error = false;
    error = !validateField(name, "Type name is required", 'typeName') || error;

    if (error) return false;

    let formData = {
        name: name
    };

    form_loading(form);

    try {
        let response = await send_hrmPost('save doc_types', formData);
        if (response) {
            let res = JSON.parse(response);
            $('#add_docType').modal('hide');
            if (res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 2000 }).then(() => {
                    load_docTypes();
                    form_loadingUndo(form);
                });
            }
        }
    } catch (err) {
        console.error('Error occurred:', err);
    }
}

async function handle_editDocTypeForm(form) {
    clearErrors();
    let id = $(form).find('#docType_id').val();
    let name = $(form).find('#typeName4Edit').val();

    let error = false;
    error = !validateField(name, "Type name is required", 'typeName4Edit') || error;

    if (error) return false;

    let formData = {
        id: id,
        name: name
    };

    form_loading(form);

    try {
        let response = await send_hrmPost('update doc_types', formData);
        if (response) {
            let res = JSON.parse(response);
            $('#edit_docType').modal('hide');
            if (res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 2000 }).then(() => {
                    load_docTypes();
                });
            }
        }
    } catch (err) {
        console.error('Error occurred:', err);
    }
}

async function get_docType(id) {
    try {
        return await send_hrmPost('get doc_types', { id: id });
    } catch (error) {
        console.error('Error:', error);
        return null;
    }
}

// Documents in folder
function loadDocumentsTable(tableId, folder_id = '', employee_id = '') {
    $('#' + tableId).DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        searching: true,
        info: true,
        serverMethod: 'post',
        ajax: {
            url: `${base_url}/app/hrm_controller.php?action=load&endpoint=documents`,
            method: "POST",
            data: {
                folder_id: folder_id,
                employee_id: employee_id
            },
            // dataFilter: function(data) {
            // 	console.log(data)
            // }
        },
        columnDefs: [
            { orderable: false, searchable: false, targets: [7] } // Action column index
        ],
        columns: [
            { title: "Document Name", data: "doc_name" },
            { title: "Employee", data: "full_name" },
            { title: "Phone Number", data: "phone_number" },
            { title: "Document Type", data: "type_name" },
            { title: "Folder", data: "folder_name" },
            { title: "Expiration Date", data: "expiration_date", render: formatDate },
            { title: "Created At", data: "created_at", render: formatDate },
            {
                title: "Action",
                data: null,
                render: function (data, type, row) {
                    return `
                        <div class="sflex scenter-items">
                            <a href="${base_url}/assets/docs/employee/${row.document}" 
                            download="${row.document}" 
                            class="fa smt-5 cursor smr-10 fa-download"></a>

                            ${user_type !== 'employee'
                            ? `<span onclick="handleDeleteDocument(${row.id})" 
                                            class="fa delete_document smt-5 cursor fa-trash"></span>`
                            : ''
                        }
                        </div>
                    `;
                }
            }
        ]
    });
}


function handleFolderDocs() {
    // Load document types for select
    $.get('./app/hrm_controller.php?action=get&endpoint=doc_types_list', function (response) {
        let types = JSON.parse(response);
        let options = '<option value="">Select Document Type</option>';
        types.forEach(type => {
            options += `<option value="${type.id}">${type.name}</option>`;
        });
        $('#docType, #docType4Edit').html(options);
    });

    // Load employees for select
    $.get('./app/hrm_controller.php?action=get&endpoint=employees_list', function (response) {
        let employees = JSON.parse(response);
        console.log(employees)
        let options = '<option value="">Select Employee</option>';
        employees.forEach(emp => {
            options += `<option value="${emp.id}" data-phone="${emp.phone}" data-email="${emp.email}">${emp.full_name}</option>`;
        });
        $('#employee, #employee4Edit').html(options);
    });

    $('#addDocumentForm').on('submit', (e) => {
        handle_addDocumentForm(e.target);
        return false;
    });

    loadDocumentsTable("empDocumentsDT", $('#folder_id').val())
    // loadDocumentsTable("empDocumentsDT", $('#folder_id').val())


    $(document).on('click', '.edit_document', async function () {
        let id = $(this).data('recid');
        let response = await get_document(id);

        if (data) {
            let res = JSON.parse(data)[0];
            $(modal).find('#document_id').val(id);
            $(modal).find('#docName4Edit').val(res.name);
            $(modal).find('#docType4Edit').val(res.type_id);
            $(modal).find('#employee4Edit').val(res.emp_id);
            $(modal).find('#tags4Edit').val(res.tags);
            $(modal).find('#expirationDate4Edit').val(res.expiration_date);
            $('.my-select').selectpicker('refresh');
            $('#edit_emp_document').modal('show');
        }
    });

    $('#editDocumentForm').on('submit', (e) => {
        handle_editDocumentForm(e.target);
        return false;
    });

    $(document).on('click', '.delete_document', async function () {
        let id = $(this).data('recid');
        swal({
            title: "Are you sure?",
            text: "You are going to delete this document.",
            icon: "warning",
            className: 'warning-swal',
            buttons: ["Cancel", "Yes, delete"],
        }).then(async (confirm) => {
            if (confirm) {
                let data = { id: id };
                try {
                    let response = await send_hrmPost('delete folder_docs', data);
                    if (response) {
                        let res = JSON.parse(response);
                        if (res.error) {
                            toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
                        } else {
                            toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 2000 }).then(() => {
                                loadDocumentsTable("empDocumentsDT", $('#folder_id').val())
                            });
                        }
                    }
                } catch (err) {
                    console.error('Error occurred:', err);
                }
            }
        });
    });

    $(document).on('click', '.download_document', function () {
        let id = $(this).data('recid');
        window.location.href = `./app/hrm_controller.php?action=download&endpoint=folder_docs&id=${id}`;
    });

    $(document).on('click', '.view_document', function () {
        let id = $(this).data('recid');
        window.open(`./app/hrm_controller.php?action=view&endpoint=folder_docs&id=${id}`, '_blank');
    });

    // Search and select employee for ducment upload
    $(document).on('keyup', '.bootstrap-select.searchEmployee input.form-control', async (e) => {
        let search = $(e.target).val();
        let searchFor = 'award';
        let formData = { search: search, searchFor: searchFor }
        if (search) {
            try {
                let response = await send_hrmPost('search employee4Select', formData);
                let res = JSON.parse(response);
                if (!res.error) {
                    $('#searchEmployee').html(res.options)
                    $('.my-select').selectpicker('refresh');
                }
            } catch (err) {
                console.error('Error occurred during employee search:', err);
            }
        }
    });

}

function handleEmpDocs() {
    loadDocumentsTable("empDocumentsDT", $('#folder_id').val())
    loadDocumentsTable("employeeDocsDT", '', $('#employee_id').val())

    // console.log('handleEmpDocs');

    // Handle document form submission
    $('#addEmpDocumentForm').on('submit', async function (e) {
        e.preventDefault();
        clearErrors();
        handle_addEmpDocumentForm(this);
    });
}

async function handle_addEmpDocumentForm(form) {
    clearErrors();
    let error = validateForm(form);
    let employee_id = $(form).find('#searchEmployee').val();
    let docName = $(form).find('#docName').val();
    let docType = $(form).find('#docType').val();
    let docTypeName = $(form).find('#docType option:selected').text();
    let docFolder = $(form).find('#docFolder').val();
    let docFolderName = $(form).find('#docFolder option:selected').text();
    let expirationDate = $(form).find('#expirationDate').val();
    let employee_name = $(form).find('#searchEmployee option:selected').text();

    console.log(employee_id, employee_name);

    // return false;

    if (!employee_id) {
        error = true;
        swal('Sorry', 'Please select an employee', 'error');
        return false;
    }

    let fileInput = $(form).find('#docFile');
    let file = fileInput[0].files[0];
    let allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'txt'];

    // Validate file type
    if (!file) {
        swal('Sorry', 'Please select a document file', 'error');
        return;
    }

    let fileName = file.name;
    let fileExtension = fileName.split('.').pop().toLowerCase();
    if (!allowedExtensions.includes(fileExtension)) {
        swal('Sorry', 'Invalid file type. Only PDF, DOC, DOCX, JPG, JPEG, PNG, GIF, BMP, WEBP, SVG, TXT are allowed', 'error');
        return;
    }

    form_loading(form);

    let formData = new FormData(form);
    formData.append('employee_id', employee_id);
    formData.append('employee_name', employee_name);
    formData.append('docFile', file);
    formData.append('docName', docName);
    formData.append('docType', docType);
    formData.append('docTypeName', docTypeName);
    formData.append('docFolder', docFolder);
    formData.append('docFolderName', docFolderName);
    formData.append('expirationDate', expirationDate);

    // Log FormData entries for debugging
    for (let [key, value] of formData.entries()) {
        console.log(key, value);
    }

    try {
        let response = await $.ajax({
            url: `${base_url}/app/hrm_controller.php?action=save&endpoint=emp_docs`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            cache: false,
            timeout: 60000 // Set a timeout for the request
        });

        console.log(response);

        if (response) {
            let res = JSON.parse(response);
            $('#add_emp_document').modal('hide');
            if (res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 2000 }).then(() => {
                    $('.my-select').selectpicker('refresh');
                    $('#add_document').modal('hide');
                    form_loadingUndo(form)
                    loadDocumentsTable("empDocumentsDT", $('#folder_id').val())
                    loadDocumentsTable("employeeDocsDT", '', $('#employee_id').val())
                    // location.reload();
                });
            }
        }
    } catch (err) {
        console.error('Error occurred:', err);
        toaster.error('An error occurred while saving the document', 'Error');
    }
    return false;
}

async function editDocument(id) {
    try {
        let response = await $.ajax({
            url: './app/hrm_controller.php?action=get&endpoint=emp_docs',
            type: 'POST',
            data: { id: id }
        });

        if (response) {
            let data = JSON.parse(response)[0];
            $('#document_id').val(data.id);
            $('#docName4Edit').val(data.name);
            $('#docType4Edit').val(data.type_id);
            $('#employee4Edit').val(data.emp_id);
            $('#tags4Edit').val(data.tags);
            $('#expirationDate4Edit').val(data.expiration_date);
            $('.my-select').selectpicker('refresh');
            $('#edit_emp_document').modal('show');
        }
    } catch (err) {
        console.error('Error occurred:', err);
        toaster.error('An error occurred while loading the document', 'Error');
    }
}

async function handleDeleteDocument(id) {
    swal({
        title: "Are you sure?",
        text: `You are going to delete this document.`,
        icon: "warning",
        className: 'warning-swal',
        buttons: ["Cancel", "Yes, delete"],
    }).then(async (confirm) => {
        if (confirm) {
            let data = { id: id };
            try {
                let response = await send_hrmPost('delete document', data);
                console.log(response)
                if (response) {
                    let res = JSON.parse(response);
                    if (res.error) {
                        toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
                    } else {
                        toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                            loadDocumentsTable("empDocumentsDT", $('#folder_id').val())
                        });
                        console.log(res);
                    }
                } else {
                    console.log('Failed to delete document.' + response);
                }

            } catch (err) {
                console.error('Error occurred during form submission:', err);
            }
        }
    });
}


// Awards
function handleAddAwards() {
    // Initialize DataTable for awards
    loadAwards();

    // Search employee
    $(document).on('keyup', '.bootstrap-select.searchEmployee input.form-control', async (e) => {
        let search = $(e.target).val();
        let searchFor = 'award';
        let formData = { search: search, searchFor: searchFor }
        if (search) {
            try {
                let response = await send_hrmPost('search employee4Select', formData);
                let res = JSON.parse(response);
                if (!res.error) {
                    $('#searchEmployee').html(res.options)
                    $('.my-select').selectpicker('refresh');
                }
            } catch (err) {
                console.error('Error occurred during employee search:', err);
            }
        }
    });

    // Handle Add Award Form Submission
    $('#addAwardForm').on('submit', function (e) {
        e.preventDefault();
        handleAddAwardForm(this);
    });

    // Handle Edit Award Form Submission
    $('#editAwardForm').on('submit', function (e) {
        e.preventDefault();
        handleEditAwardForm(this);
    });

    // Handle Edit Award Button Click
    $(document).on('click', '.edit-award', function () {
        const awardId = $(this).data('id');
        getAwardDetails(awardId);
    });

    // Handle Delete Award Button Click
    $(document).on('click', '.delete-award', function () {
        const awardId = $(this).data('id');
        deleteAward(awardId);
    });
}

// Load Awards DataTable
function loadAwards() {
    $('#awardsDT').DataTable({
        "processing": true,
        "serverSide": true,
        "bDestroy": true,
        "ajax": {
            "url": `${base_url}/app/hrm_controller.php?action=load&endpoint=awards`,
            "type": "POST",
            dataFilter: function (data) {
                console.log(data)
                return data;
            }
        },
        "columns": [
            {
                title: "Employee", data: "full_name", render: function (data, type, row) {
                    return `<div>
                    <span class="d-block">${row.full_name}</span>
                    <small class="text-muted">${row.staff_no || ''}</small>
                </div>`;
                }
            },
            { title: "Award Type", data: "award_type" },
            { title: "Gift/Prize", data: "gift", defaultContent: "-" },
            {
                title: "Date", data: "award_date", render: function (data) {
                    return formatDate(data);
                }
            },



            // <span data-id="${row.id}" class="fa edit-award smt-5 cursor smr-10 fa-pencil"></span>
            {
                title: "Actions", data: null, render: function (data, type, row) {
                    return `<div class="btn-group sflex scenter-items">
            		${user_permissions['delete_awards'] === 'on'
                            ? `<span data-id="${row.id}" 
                         class="fa delete-award smt-5 cursor fa-trash"></span>`
                            : ''
                        }   
                </div>`;
                }
            }
        ],
        "order": [[3, "desc"]]
    });
}

// Handle Add Award Form Submission
async function handleAddAwardForm(form) {
    clearErrors();

    // Get form data
    const employeeId = $(form).find('#searchEmployee').val();
    const awardType = $(form).find('#awardType').val();
    const gift = $(form).find('#gift').val();
    const awardDate = $(form).find('#awardDate').val();
    const description = $(form).find('#description').val();

    // Validate form fields
    let error = false;
    if (!employeeId) {
        showError('#searchEmployee', 'Please select an employee');
        error = true;
    }
    if (!awardType) {
        showError('#awardType', 'Please select an award type');
        error = true;
    }
    if (!awardDate) {
        showError('#awardDate', 'Please select a date');
        error = true;
    }

    if (error) return false;

    // Prepare form data
    const formData = {
        employee_id: employeeId,
        award_type: awardType,
        gift: gift,
        award_date: awardDate,
        description: description
    };

    form_loading(form);

    try {
        const response = await send_hrmPost('save award', formData);
        const res = JSON.parse(response);

        if (res.error) {
            toaster.warning(res.msg || 'Failed to save award', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            form_loadingUndo(form);
        } else {
            toaster.success(res.msg || 'Award saved successfully', 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                $('#add_award').modal('hide');
                form_loadingUndo(form);
                $(form)[0].reset();
                loadAwards();
            });
        }
    } catch (err) {
        console.error('Error occurred during award submission:', err);
        toaster.error('An unexpected error occurred. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        form_loadingUndo(form);
    }
}

// Get Award Details for Editing
async function getAwardDetails(awardId) {
    try {
        const response = await send_hrmPost('get award', { id: awardId });
        const res = JSON.parse(response);

        if (res.error) {
            toaster.warning(res.msg || 'Failed to get award details', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        } else {
            const award = res.data;

            // Populate form fields
            $('#award_id').val(award.id);
            $('#employee_name').val(award.full_name);
            $('#employee_id').val(award.emp_id);
            $('#edit_award_type').val(award.type_id);
            $('#edit_gift').val(award.gift);
            $('#edit_award_date').val(award.award_date);
            $('#edit_status').val(award.status);
        }
    } catch (err) {
        console.error('Error occurred while getting award details:', err);
        toaster.error('An unexpected error occurred. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
    }
}

// Handle Edit Award Form Submission
async function handleEditAwardForm(form) {
    clearErrors();

    // Get form data
    const awardId = $(form).find('#award_id').val();
    const employeeId = $(form).find('#employee_id').val();
    const awardType = $(form).find('#edit_award_type').val();
    const gift = $(form).find('#edit_gift').val();
    const awardDate = $(form).find('#edit_award_date').val();
    const status = $(form).find('#edit_status').val();

    // Validate form fields
    let error = false;
    if (!awardType) {
        showError('#edit_award_type', 'Please select an award type');
        error = true;
    }
    if (!awardDate) {
        showError('#edit_award_date', 'Please select a date');
        error = true;
    }

    if (error) return false;

    // Prepare form data
    const formData = {
        id: awardId,
        employee_id: employeeId,
        award_type: awardType,
        gift: gift,
        award_date: awardDate,
        status: status
    };

    form_loading(form);

    try {
        const response = await send_hrmPost('update award', formData);
        const res = JSON.parse(response);

        if (res.error) {
            toaster.warning(res.msg || 'Failed to update award', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
            form_loadingUndo(form);
        } else {
            toaster.success(res.msg || 'Award updated successfully', 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                $('#edit_award').modal('hide');
                form_loadingUndo(form);
                loadAwards();
            });
        }
    } catch (err) {
        console.error('Error occurred during award update:', err);
        toaster.error('An unexpected error occurred. Please try again.', 'Error', { top: '30%', right: '20px', hide: true, duration: 5000 });
        form_loadingUndo(form);
    }
}

// Delete Award
async function deleteAward(awardId) {
    // Use swall for confirmation
    swal({
        title: "Are you sure?",
        text: "You are going to delete this award.",
        icon: "warning",
        className: 'warning-swal',
        buttons: ["Cancel", "Yes, delete"],
    }).then(async (confirm) => {
        if (confirm) {
            let data = { id: awardId };
            try {
                let response = await send_hrmPost('delete award', data);
                if (response) {
                    let res = JSON.parse(response);
                    if (res.error) {
                        toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
                    } else {
                        toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 2000 }).then(() => {
                            loadAwards();
                        });
                    }
                }
            } catch (err) {
                console.error('Error occurred:', err);
            }
        }
    });

    return;


}



