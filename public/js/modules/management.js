// Promotions Management Functions
async function send_managementPost(str, data) {
    let [action, endpoint] = str.split(' ');
    try {
        const response = await $.post(`${base_url}/app/management_controller.php?action=${action}&endpoint=${endpoint}`, data);
        return response;
    } catch (error) {
        console.error('Error occurred during the request:', error);
        return null;
    }
}

function load_promotions() {
    if ($.fn.DataTable.isDataTable('#promotionsDT')) {
        $('#promotionsDT').DataTable().destroy();
    }
    $('#promotionsDT').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "./app/management_controller.php?action=load&endpoint=promotions",
            "type": "POST",
            // "dataFilter": function(data) {
            //    console.log(data);
            // }
        },
        "columns": [
            // { "title": "ID", "data": 0 },
            { "title": "Employee", "data": 0 },
            { "title": "From", "data": 1 },
            { "title": "To", "data": 2 },
            { "title": "Date", "data": 3 },
            { "title": "New Salary", "data": 4 },
            { "title": "Status", "data": 5 },
            { "title": "Added", "data": 6 },
            { "title": "Actions", "data": 7, "orderable": false }
        ],
        "order": [[0, "desc"]],
        "pageLength": 25,
        "responsive": true,
        "language": {
            "emptyTable": "No promotion records found",
            "zeroRecords": "No matching promotion records found"
        }
    });
}

function handlePromotions() {
    // Employee selection change handler
    $('#searchEmployee').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        console.log(selectedOption.data());
        const currentDesignation = selectedOption.data('current-designation') || '';
        const currentSalary = selectedOption.data('current-salary') || '';
        
        $('#old_designation').val(currentDesignation);
        $('#current_salary').val(currentSalary);
        
        // Set old designation ID if we can find it
        // This would need to be enhanced to get the actual designation ID
    });

    // Add form submission
    $('#addPromotionForm').on('submit', function(e) {
        e.preventDefault();
        console.log('this');
        handle_addPromotionForm(this);
    });

    // Edit form submission
    $('#editPromotionForm').on('submit', function(e) {
        e.preventDefault();
        handle_editPromotionForm(this);
    });
}

async function handle_addPromotionForm(form) {
    let data = {};
    let employee_id = $('#searchEmployee').val();
    let old_designation = $('#old_designation').val();
    let new_designation = $('#new_designation_id').val();
    let current_salary = $('#current_salary').val();
    let new_salary = $('#new_salary').val();
    let promotion_date = $('#promotion_date').val();
    let status = $('#status').val();
    let reason = $('#reason').val();

    data['employee_id'] = employee_id;
    data['old_designation'] = old_designation;
    data['new_designation'] = new_designation;
    data['current_salary'] = current_salary;
    data['new_salary'] = new_salary;
    data['promotion_date'] = promotion_date;
    data['status'] = status;
    data['reason'] = reason;

    let error = validateForm(form);
    if(error) {
        return;
    }

    console.log(data);
    // return;

    try {
        const response = await send_managementPost('save promotion', data);
        console.log(response);
        let res = JSON.parse(response);
        if (!res.error) {
            toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                $('#add_promotion').modal('hide');
                form.reset();
                $('.my-select').selectpicker('refresh');
                load_promotions();
            });
        } else {
            toaster.error(res.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
        toaster.error('An error occurred while saving the promotion', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
    }
}

async function handle_editPromotionForm(form) {
    let error = validateForm(form);
    if(error) {
        return;
    }

    let data = {};
    data['promotion_id']    = $('#edit_promotion_id').val();
    data['employee_id']     = $('#edit_employee_id').val();
    data['old_designation'] = $('#edit_old_designation').val();
    data['new_designation'] = $('#edit_new_designation').val();
    data['current_salary']  = $('#edit_current_salary').val();
    data['new_salary']      = $('#edit_new_salary').val();
    data['promotion_date']  = $('#edit_promotion_date').val();
    data['status']          = $('#edit_status').val();
    data['reason']          = $('#edit_reason').val();

    console.log(data);
    // return;

    try {
        let response = await send_managementPost('update promotion', data);
        console.log(response);
        let res = JSON.parse(response);
        if (!res.error) {
            toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                $('#edit_promotion').modal('hide');
                load_promotions();
            });
        } else {
            toaster.error(res.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
        toaster.error('An error occurred while updating the promotion', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
    }
}

function get_promotion(id) {
    $.ajax({
        url: './app/management_controller.php?action=get&endpoint=promotions',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (!response.error) {
                const data = response.data;
                console.log(data);
                $('#edit_promotion_id').val(data.promotion_id);
                $('#edit_employee_id').val(data.employee_id);
                $('#edit_employee_name').val(data.employee_name);
                $('#edit_old_designation').val(data.old_designation);
                $('#edit_new_designation').val(data.new_designation.trim());
                $('#edit_current_salary').val(data.current_salary);
                $('#edit_new_salary').val(data.new_salary);
                $('#edit_promotion_date').val(data.promotion_date);
                $('#edit_status').val(data.status);
                $('#edit_reason').val(data.reason);
                
                $('#edit_promotion').modal('show');
            } else {
                toastr.error(response.msg);
            }
        },
        error: function() {
            toastr.error('An error occurred while fetching promotion details');
        }
    });
}

function delete_promotion(id) {
    swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        buttons: ['Cancel', 'Yes, delete it!']
    }).then((result) => {
        if (result) {
            $.ajax({
                url: `${base_url}/app/management_controller.php?action=delete&endpoint=promotions`,
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    if (!response.error) {
                        toaster.success(response.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                            $('#promotionsDT').DataTable().ajax.reload();
                        });
                    } else {
                        toaster.error(response.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
                    }
                },
                error: function() {
                    toaster.error('An error occurred while deleting the promotion', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
                }
            });
        }
    });
}

// TRANSFERS MANAGEMENT FUNCTIONS
function load_transfers() {
    $('#transfersDT').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": `${base_url}/app/management_controller.php?action=load&endpoint=transfers`,
            "type": "POST"
        },
        "columns": [
            // { "title": "ID", "data": 0 },
            { "title": "Employee", "data": 0 },
            { "title": "From Department", "data": 1 },
            { "title": "To Department", "data": 2 },
            { "title": "Transfer Date", "data": 3 },
            { "title": "Status", "data": 4 },
            { "title": "Added Date", "data": 5 },
            { "title": "Actions", "data": 6, "orderable": false }
        ],
        "order": [[0, "desc"]],
        "pageLength": 25,
        "responsive": true
    });
}

function handleTransfers() {
    // Employee selection change handler
    $('#searchEmployee').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const currentBranchId = selectedOption.data('current-branch');
        const currentBranchName = selectedOption.data('current-branch-name');
        
        $('#old_department').val(currentBranchName);
        $('#old_department_id').val(currentBranchId);
    });

    // Add transfer form submission
    $('#addTransferForm').on('submit', function(e) {
        e.preventDefault();
        handle_addTransferForm(this);
    });

    // Edit transfer form submission
    $('#editTransferForm').on('submit', function(e) {
        e.preventDefault();
        handle_editTransferForm(this);
    });
}

async function handle_addTransferForm(form) {
    let error = validateForm(form);
    // if(error) {
    //     return;
    // }

    let data = {};
    data['employee_id'] = $('#searchEmployee').val();
    data['new_department_id'] = $('#new_department_id').val();
    data['transfer_date'] = $('#transfer_date').val();
    data['status'] = $('#status').val();

    console.log(data);
    // return;
    
    try {
        const response = await send_managementPost('save transfer', data);
        console.log(response);
        const result = JSON.parse(response);
        
        if (result.error) {
            toaster.error(result.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        } else {
            toaster.success(result.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                $('#add_transfer').modal('hide');
                form.reset();
                $('#transfersDT').DataTable().ajax.reload();
            });
        }
    } catch (error) {
        toaster.error('An error occurred while saving the transfer', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        console.error('Error:', error);
    }
}

async function handle_editTransferForm(form) {
    let error = validateForm(form);
    if(error) {
        return;
    }
    
    let data = {};
    data['transfer_id'] = $('#edit_transfer_id').val();
    data['employee_id'] = $('#edit_employee_id').val();
    data['old_department_id'] = $('#edit_old_department_id').val();
    data['old_department'] = $('#edit_old_department').val();
    data['new_department_id'] = $('#edit_new_department_id').val();
    data['transfer_date'] = $('#edit_transfer_date').val();
    data['status'] = $('#edit_status').val();
    data['reason'] = $('#edit_reason').val();

    console.log(data);
    // return;
    
    try {
        const response = await send_managementPost('update transfer', data);
        const result = JSON.parse(response);
        console.log(result);
        
        if (!result.error) {
            toaster.success(result.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 });
            $('#edit_transfer').modal('hide');
            $('#transfersDT').DataTable().ajax.reload();
        }
    } catch (error) {
        toaster.error('An error occurred while updating the transfer', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        console.error('Error:', error);
    }
}

async function get_transfer(transfer_id) {
    try {
        const response = await send_managementPost('get transfer', { id: transfer_id });
        console.log(response);
        const result = JSON.parse(response);
        
        if (result.error) {
            toaster.error(result.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        } else {
            const data = result.data;
            
            // Populate edit form
            $('#edit_transfer_id').val(data.transfer_id);
            $('#edit_employee_id').val(data.employee_id);
            $('#edit_employee_name').val(data.employee_name);
            $('#edit_old_department_id').val(data.old_department_id);
            $('#edit_old_department').val(data.old_department_name || '');
            $('#edit_new_department_id').val(data.new_department_id);
            $('#edit_transfer_date').val(data.transfer_date);
            $('#edit_status').val(data.status);
            $('#edit_reason').val(data.reason || '');
            
            $('#edit_transfer').modal('show');
        }
    } catch (error) {
        toaster.error('An error occurred while fetching transfer data', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        console.error('Error:', error);
    }
}

function delete_transfer(transfer_id) {
    swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        buttons: ['Cancel', 'Yes, delete it!']
    }).then(async (result) => {
        if (result) {
            try {
                let data = {};
                data['transfer_id'] = transfer_id;
                
                const response = await send_managementPost('delete transfer', data);
                console.log(response);
                const deleteResult = JSON.parse(response);
                
                if (deleteResult.error) {
                    toaster.error(deleteResult.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
                } else {
                    toaster.success(deleteResult.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 });
                    $('#transfersDT').DataTable().ajax.reload();
                }
            } catch (error) {
                toaster.error('An error occurred while deleting the transfer', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
                console.error('Error:', error);
            }
        }
    });
}

// RESIGNATIONS MANAGEMENT FUNCTIONS
function load_resignations() {
    if ($.fn.DataTable.isDataTable('#resignationsDT')) {
        $('#resignationsDT').DataTable().destroy();
    }
    $('#resignationsDT').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "./app/management_controller.php?action=load&endpoint=resignations",
            "type": "POST",
        },
        "columns": [
            { "title": "Employee", "data": 0 },
            { "title": "Resignation Date", "data": 1 },
            { "title": "Last Working Day", "data": 2 },
            { "title": "Status", "data": 3 },
            { "title": "Added Date", "data": 4 },
            { "title": "Actions", "data": 5, "orderable": false }
        ],
        "order": [[0, "desc"]],
        "pageLength": 25,
        "responsive": true,
        "language": {
            "emptyTable": "No resignation records found",
            "zeroRecords": "No matching resignation records found"
        }
    });
}

function handleResignations() {
    // Add form submission
    $('#addResignationForm').on('submit', function(e) {
        e.preventDefault();
        handle_addResignationForm(this);
    });

    // Edit form submission
    $('#editResignationForm').on('submit', function(e) {
        e.preventDefault();
        handle_editResignationForm(this);
    });
}

async function handle_addResignationForm(form) {
    let data = {};
    data['employee_id'] = $('#add_resignation_employee_id').val();
    data['resignation_date'] = $('#add_resignation_date').val();
    data['last_working_day'] = $('#add_last_working_day').val();
    data['reason'] = $('#add_resignation_reason').val();
    data['status'] = $('#add_resignation_status').val();

    let error = validateForm(form);
    if(error) {
        return;
    }

    try {
        const response = await send_managementPost('save resignation', data);
        const res = JSON.parse(response);
        if (!res.error) {
            toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                $('#add_resignation').modal('hide');
                form.reset();
                $('.my-select').selectpicker('refresh');
                load_resignations();
            });
        } else {
            toaster.error(res.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
        toaster.error('An error occurred while saving the resignation', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
    }
}

async function handle_editResignationForm(form) {
    let error = validateForm(form);
    if(error) {
        return;
    }

    let data = {};
    data['resignation_id'] = $('#edit_resignation_id').val();
    data['employee_id'] = $('#edit_resignation_employee_id').val();
    data['resignation_date'] = $('#edit_resignation_date').val();
    data['last_working_day'] = $('#edit_last_working_day').val();
    data['reason'] = $('#edit_resignation_reason').val();
    data['status'] = $('#edit_resignation_status').val();

    try {
        const response = await send_managementPost('update resignation', data);
        const res = JSON.parse(response);
        if (!res.error) {
            toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                $('#edit_resignation').modal('hide');
                load_resignations();
            });
        } else {
            toaster.error(res.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
        toaster.error('An error occurred while updating the resignation', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
    }
}

function get_resignation(id) {
    $.ajax({
        url: `${base_url}/app/management_controller.php?action=get&endpoint=resignation`,
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (!response.error) {
                const data = response.data;
                $('#edit_resignation_id').val(data.resignation_id);
                $('#edit_resignation_employee_id').val(data.employee_id);
                $('#edit_resignation_employee_name').val(data.employee_name);
                $('#edit_resignation_date').val(data.resignation_date);
                $('#edit_last_working_day').val(data.last_working_day);
                $('#edit_resignation_reason').val(data.reason);
                $('#edit_resignation_status').val(data.status);
                
                $('.my-select').selectpicker('refresh');
                $('#edit_resignation').modal('show');
            } else {
                toaster.error(response.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
            }
        },
        error: function() {
            toaster.error('An error occurred while fetching resignation details', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        }
    });
}

function delete_resignation(id) {
    swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        buttons: ['Cancel', 'Yes, delete it!']
    }).then(async (result) => {
        if (result) {
            try {
               let data = {};
               data['resignation_id'] = id;
                const response = await send_managementPost('delete resignation', data);
                const deleteResult = JSON.parse(response);

                if (!deleteResult.error) {
                    toaster.success(deleteResult.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 });
                    $('#resignationsDT').DataTable().ajax.reload();
                } else {
                    toaster.error(deleteResult.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
                }
            } catch (error) {
                toaster.error('An error occurred while deleting the resignation', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
                console.error('Error:', error);
            }
        }
    });
}

// TERMINATIONS MANAGEMENT FUNCTIONS
function load_terminations() {
    if ($.fn.DataTable.isDataTable('#terminationsDT')) {
        $('#terminationsDT').DataTable().destroy();
    }
    $('#terminationsDT').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "./app/management_controller.php?action=load&endpoint=terminations",
            "type": "POST",
        },
        "columns": [
            { "title": "Employee", "data": 0 },
            { "title": "Termination Date", "data": 1 },
            { "title": "Reason", "data": 2 },
            { "title": "Type", "data": 3 },
            { "title": "Status", "data": 4 },
            { "title": "Added Date", "data": 5 },
            { "title": "Actions", "data": 6, "orderable": false }
        ],
        "order": [[0, "desc"]],
        "pageLength": 25,
        "responsive": true,
        "language": {
            "emptyTable": "No termination records found",
            "zeroRecords": "No matching termination records found"
        }
    });
}

function handleTerminations() {
    // Add form submission
    $('#addTerminationForm').on('submit', function(e) {
        e.preventDefault();
        handle_addTerminationForm(this);
    });

    // Edit form submission
    $('#editTerminationForm').on('submit', function(e) {
        e.preventDefault();
        handle_editTerminationForm(this);
    });

    load_terminations();
}

async function handle_addTerminationForm(form) {
    let data = {};
    data['employee_id'] = $('#add_termination_employee_id').val();
    data['termination_date'] = $('#add_termination_date').val();
    data['reason'] = $('#add_termination_reason').val();
    data['termination_type'] = $('#add_termination_type').val();
    data['status'] = $('#add_termination_status').val();

    let error = validateForm(form);
    if(error) {
        return;
    }

    try {
        const response = await send_managementPost('save termination', data);
        const res = JSON.parse(response);
        if (!res.error) {
            toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                $('#add_termination').modal('hide');
                form.reset();
                $('.my-select').selectpicker('refresh');
                load_terminations();
            });
        } else {
            toaster.error(res.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
        toaster.error('An error occurred while saving the termination', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
    }
}

async function handle_editTerminationForm(form) {
    let error = validateForm(form);
    if(error) {
        return;
    }

    let data = {};
    data['termination_id'] = $('#edit_termination_id').val();
    data['employee_id'] = $('#edit_termination_employee_id').val();
    data['termination_date'] = $('#edit_termination_date').val();
    data['reason'] = $('#edit_termination_reason').val();
    data['termination_type'] = $('#edit_termination_type').val();
    data['status'] = $('#edit_termination_status').val();

    try {
        const response = await send_managementPost('update termination', data);
        const res = JSON.parse(response);
        if (!res.error) {
            toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                $('#edit_termination').modal('hide');
                load_terminations();
            });
        } else {
            toaster.error(res.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
        toaster.error('An error occurred while updating the termination', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
    }
}

function get_termination(id) {
    $.ajax({
        url: './app/management_controller.php?action=get&endpoint=termination',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (!response.error) {
                const data = response.data;
                $('#edit_termination_id').val(data.termination_id);
                $('#edit_termination_employee_id').val(data.employee_id);
                $('#edit_termination_employee_name').val(data.employee_name);
                $('#edit_termination_date').val(data.termination_date);
                $('#edit_termination_reason').val(data.reason);
                $('#edit_termination_type').val(data.termination_type);
                $('#edit_termination_status').val(data.status);
                
                $('.my-select').selectpicker('refresh');
                $('#edit_termination').modal('show');
            } else {
                toaster.error(response.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
            }
        },
        error: function() {
            toaster.error('An error occurred while fetching termination details', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        }
    });
}

function delete_termination(id) {
    swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        buttons: ['Cancel', 'Yes, delete it!']
    }).then(async (result) => {
        if (result) {
            try {
               let data = {};
               data['id'] = id;
                const response = await send_managementPost('delete termination', data);
                const deleteResult = JSON.parse(response);

                if (!deleteResult.error) {
                    toaster.success(deleteResult.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 });
                    $('#terminationsDT').DataTable().ajax.reload();
                } else {
                    toaster.error(deleteResult.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
                }
            } catch (error) {
                toaster.error('An error occurred while deleting the termination', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
                console.error('Error:', error);
            }
        }
    });
}

// WARNINGS MANAGEMENT FUNCTIONS
function load_warnings() {
    if ($.fn.DataTable.isDataTable('#warningsDT')) {
        $('#warningsDT').DataTable().destroy();
    }
    $('#warningsDT').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "./app/management_controller.php?action=load&endpoint=warnings",
            "type": "POST",
            // "dataFilter": function(data) {
            //     console.log(data);
            // }
        },
        "columns": [
            // { "title": "ID", "data": 0 },
            { "title": "Employee", "data": 0 },
            { "title": "Warning Date", "data": 1 },
            { "title": "Reason", "data": 2 },
            { "title": "Issued By", "data": 3 },
            { "title": "Severity", "data": 4 },
            { "title": "Added Date", "data": 5 },
            { "title": "Actions", "data": 6, "orderable": false }
        ],
        "order": [[0, "desc"]],
        "pageLength": 25,
        "responsive": true,
        "language": {
            "emptyTable": "No warning records found",
            "zeroRecords": "No matching warning records found"
        }
    });
}

function handleWarnings() {
    // Add form submission
    $('#addWarningForm').on('submit', function(e) {
        e.preventDefault();
        handle_addWarningForm(this);
    });

    // Edit form submission
    $('#editWarningForm').on('submit', function(e) {
        e.preventDefault();
        handle_editWarningForm(this);
    });
}

async function handle_addWarningForm(form) {
    let data = {};
    data['employee_id'] = $('#searchEmployee').val();
    data['warning_date'] = $('#add_warning_date').val();
    data['reason'] = $('#add_warning_reason').val();
    data['issued_by'] = $('#add_warning_issued_by').val();
    data['severity'] = $('#add_warning_severity').val();

    let error = validateForm(form);
    if(error) {
        return;
    }

    try {
        const response = await send_managementPost('save warning', data);
        console.log(response);
        const res = JSON.parse(response);
        if (!res.error) {
            toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                $('#add_warning').modal('hide');
                form.reset();
                $('.my-select').selectpicker('refresh'); // Refresh selectpickers if any
                load_warnings();
            });
        } else {
            toaster.error(res.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
        toaster.error('An error occurred while saving the warning', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
    }
}

async function handle_editWarningForm(form) {
    let error = validateForm(form);
    if(error) {
        return;
    }

    let data = {};
    data['warning_id'] = $('#edit_warning_id').val();
    data['employee_id'] = $('#edit_warning_employee_id').val();
    data['warning_date'] = $('#edit_warning_date').val();
    data['reason'] = $('#edit_warning_reason').val();
    data['issued_by'] = $('#edit_warning_issued_by').val();
    data['severity'] = $('#edit_warning_severity').val();

    try {
        const response = await send_managementPost('update warning', data);
        const res = JSON.parse(response);
        if (!res.error) {
            toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                $('#edit_warning').modal('hide');
                load_warnings();
            });
        } else {
            toaster.error(res.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
        toaster.error('An error occurred while updating the warning', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
    }
}

function get_warning(id) {
    $.ajax({
        url: './app/management_controller.php?action=get&endpoint=warnings',
        type: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (!response.error) {
                const data = response.data;
                console.log(data);
                $('#edit_warning_id').val(data.warning_id);
                $('#edit_warning_employee_id').val(data.employee_id);
                $('#edit_warning_employee_name').val(data.employee_name); // Display employee name
                $('#edit_warning_date').val(data.warning_date);
                $('#edit_warning_reason').val(data.reason);
                $('#edit_warning_issued_by').val(data.issued_by);
                $('#edit_warning_issued_by_name').val(data.issued_by_name); // Display issued by name
                $('#edit_warning_severity').val(data.severity);

                // Refresh selectpickers if these are select elements
                $('.my-select').selectpicker('refresh');

                $('#edit_warning').modal('show');
            } else {
                toastr.error(response.msg);
            }
        },
        error: function() {
            toastr.error('An error occurred while fetching warning details');
        }
    });
}

function delete_warning(id) {
    swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        buttons: ['Cancel', 'Yes, delete it!']
    }).then(async (result) => {
        if (result) {
            try {
               let data = {};
               data['warning_id'] = id;
                const response = await send_managementPost('delete warning', data);
                console.log(response);
                const deleteResult = JSON.parse(response);

                if (!deleteResult.error) {
                    toaster.success(deleteResult.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 });
                    $('#warningsDT').DataTable().ajax.reload();
                } else {
                    toaster.error(deleteResult.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
                }
            } catch (error) {
                toaster.error('An error occurred while deleting the warning', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
                console.error('Error:', error);
            }
        }
    });
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof load_promotions === 'function') {
        load_promotions();
    }
    if (typeof handlePromotions === 'function') {
        handlePromotions();
    }
    if (typeof load_transfers === 'function') {
        load_transfers();
    }
    if (typeof handleTransfers === 'function') {
        handleTransfers();
    }
    if (typeof load_resignations === 'function') {
        load_resignations();
    }
    if (typeof handleResignations === 'function') {
        handleResignations();
    }
    if (typeof load_warnings === 'function') {
        load_warnings();
    }
    if (typeof handleWarnings === 'function') {
        handleWarnings();
    }
    if (typeof handleTerminations === 'function') {
        handleTerminations();
    }
    $('.my-select').selectpicker({
        noneResultsText: "No results found"
    });
});