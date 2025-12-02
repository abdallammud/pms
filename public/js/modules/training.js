// Utility for AJAX
async function send_trainingPost(str, data) {
    let [action, endpoint] = str.split(' ');
    try {
        const response = await $.post(`${base_url}/app/training_controller.php?action=${action}&endpoint=${endpoint}`, data);
        return response;
    } catch (error) {
        console.error('Error occurred during the request:', error);
        return null;
    }
}

document.addEventListener("DOMContentLoaded", function() {
    handleTrainers();
    handleTraining();
});

function load_trainers() {
    var datatable = $('#trainersDT').DataTable({
        "processing": true,
        "serverSide": true,
        "bDestroy": true,
        "searching": true,
        "info": false,
        "columnDefs": [
            { "orderable": false, "searchable": false, "targets": [5] }
        ],
        "serverMethod": 'post',
        "ajax": {
            "url": "./app/training_controller.php?action=load&endpoint=trainers",
            "method": "POST"
        },
        columns: [
            { title: `Full Name`, data: null, render: function(data, type, row) {
                return `<div><span>${row.full_name}</span></div>`;
            }},
            { title: `Phone`, data: null, render: function(data, type, row) {
                return `<div><span>${row.phone || '-'}</span></div>`;
            }},
            { title: `Email`, data: null, render: function(data, type, row) {
                return `<div><span>${row.email}</span></div>`;
            }},
            { title: `Status`, data: null, render: function(data, type, row) {
                return `<div><span>${row.status}</span></div>`;
            }},
            { title: `Added Date`, data: null, render: function(data, type, row) {
                return `<div><span>${row.added_date}</span></div>`;
            }},
            { title: "Action", data: null, render: function(data, type, row) {
                return `<div class="sflex scenter-items">
                    <span data-recid="${row.id}" class="fa edit_trainerInfo smt-5 cursor smr-10 fa-pencil" title="Edit"></span>
                    <span data-recid="${row.id}" class="fa delete_trainer smt-5 cursor fa-trash" title="Delete"></span>
                </div>`;
            }}
        ]
    });
    return false;
}

function handleTrainers() {
    $('#addTrainerForm').on('submit', (e) => {
        handle_addTrainerForm(e.target);
        return false;
    });
    load_trainers();
    $(document).on('click', '.edit_trainerInfo', async (e) => {
        let id = $(e.currentTarget).data('recid');
        let modal = $('#edit_trainer');
        let data = await get_trainer(id);
        console.log(data);
        if(data) {
            let res = JSON.parse(data).data;
            $(modal).find('#edit_trainer_id').val(id);
            $(modal).find('#edit_trainer_full_name').val(res.full_name);
            $(modal).find('#edit_trainer_phone').val(res.phone);
            $(modal).find('#edit_trainer_email').val(res.email);
            $(modal).find('#edit_trainer_status').val(res.status);
        }
        $(modal).modal('show');
    });
    $('#editTrainerForm').on('submit', (e) => {
        handle_editTrainerForm(e.target);
        return false;
    });
    $(document).on('click', '.delete_trainer', async (e) => {
        let id = $(e.currentTarget).data('recid');
        swal({
            title: "Are you sure?",
            text: `You are going to delete this trainer record.`,
            icon: "warning",
            className: 'warning-swal',
            buttons: ["Cancel", "Yes, delete"],
        }).then(async (confirm) => {
            if (confirm) {
                let result = await send_trainingPost('delete trainers', { id });
                let res = JSON.parse(result);
                if (!res.error) {
                    toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                        $('#trainersDT').DataTable().ajax.reload();
                    });
                } else {
                    toaster.error(res.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
                }
            }
        });
    });
}

async function handle_addTrainerForm(form) {
    let data = {};
    data['full_name'] = $('#trainer_full_name').val();
    data['phone'] = $('#trainer_phone').val();
    data['email'] = $('#trainer_email').val();
    data['status'] = $('#trainer_status').val();
    try {
        const response = await send_trainingPost('save trainers', data);
        const result = JSON.parse(response);
        if (!result.error) {
            toaster.success(result.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                $('#add_trainer').modal('hide');
                $('#trainersDT').DataTable().ajax.reload();
                form.reset();
            });
        } else {
            toaster.error(result.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        }
    } catch (error) {
        console.error('Error adding trainer:', error);
        toaster.error('Error adding trainer', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
    }
}

async function handle_editTrainerForm(form) {
    let data = {};
    data['id'] = $('#edit_trainer_id').val();
    data['full_name'] = $('#edit_trainer_full_name').val();
    data['phone'] = $('#edit_trainer_phone').val();
    data['email'] = $('#edit_trainer_email').val();
    data['status'] = $('#edit_trainer_status').val();
    try {
        const response = await send_trainingPost('update trainers', data);
        const result = JSON.parse(response);
        if (!result.error) {
            toaster.success(result.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                $('#edit_trainer').modal('hide');
                $('#trainersDT').DataTable().ajax.reload();
            });
        } else {
            toaster.error(result.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        }
    } catch (error) {
        console.error('Error editing trainer:', error);
        toaster.error('Error editing trainer', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
    }
}

async function get_trainer(id) {
    return await send_trainingPost('get trainers', { id });
}

// Training
function handleTraining() {
    $('.my-select').selectpicker({
        noneResultsText: "No results found"
    });

    $(document).on('keyup', '.bootstrap-select.searchEmployee input.form-control', async (e) => {
    	let search = $(e.target).val();
    	let searchFor = 'training';
    	let formData = {search:search, searchFor:searchFor}
		if(search) {
			try {
		        let response = await send_trainingPost('search employee4Training', formData);
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
    });

    // Load training list
    load_training();

    // Add training form handler
    $('#addTrainingForm').on('submit', function(e) {
        e.preventDefault();
        handle_addTrainingForm(this);
    });

    // Edit training form handler
    $('#editTrainingForm').on('submit', function(e) {
        e.preventDefault();
        handle_editTrainingForm(this);
    });

    // Edit training button click
    $(document).on('click', '.edit-training', function() {
        let id = $(this).data('id');
        get_training(id);
    });

    // Delete training button click
    $(document).on('click', '.delete-training', function() {
        let id = $(this).data('id');
        delete_training(id);
    });
}

function load_training() {
    var datatable = $('#trainingsDT').DataTable({
        "processing": true,
        "serverSide": true,
        "bDestroy": true,
        "searching": true,
        "info": false,
        "columnDefs": [
            { "orderable": false, "searchable": false, "targets": [8] }
        ],
        "serverMethod": 'post',
        "ajax": {
            "url": `${base_url}/app/training_controller.php?action=load&endpoint=training`,
            "method": "POST",
            // dataFilter: function(data) {
			// 	console.log(data)
			// }
        },
        columns: [
            { title: `Employee`, data: null, render: function(data, type, row) {
                return `<div><span>${row.full_name}</span><br><small class="text-muted">${row.staff_no}</small></div>`;
            }},
            { title: `Training Type`, data: null, render: function(data, type, row) {
                return `<div><span>${row.type_name}</span></div>`;
            }},
            { title: `Training Option`, data: null, render: function(data, type, row) {
                return `<div><span>${row.option_name}</span></div>`;
            }},
            { title: `Trainer`, data: null, render: function(data, type, row) {
                return `<div><span>${row.trainer_name}</span><br><small class="text-muted">${row.trainer_phone}</small></div>`;
            }},
            { title: `Cost`, data: null, render: function(data, type, row) {
                return `<div><span>${formatMoney(row.cost || '0')}</span></div>`;
            }},
            { title: `Start Date`, data: null, render: function(data, type, row) {
                return `<div><span>${formatDate(row.start_date)}</span></div>`;
            }},
            { title: `End Date`, data: null, render: function(data, type, row) {
                return `<div><span>${formatDate(row.end_date)}</span></div>`;
            }},
            { title: `Status`, data: null, render: function(data, type, row) {
                let statusClass = row.status === 'Active' ? 'success' : 
                                 row.status === 'Completed' ? 'info' : 
                                 row.status === 'Cancelled' ? 'danger' : 'warning';
                return `<div><span class="badge bg-${statusClass}">${row.status}</span></div>`;
            }},
            { title: "Action", data: null, render: function(data, type, row) {
                return `<div class="sflex scenter-items">
                    <span class="cursor smr-10 btn-outline-primary edit-training" data-id="${row.id}" data-bs-toggle="modal" data-bs-target="#edit_training">
                        <i class="fa fa-pencil"></i>
                    </span>
                    <span class="cursor btn-outline-danger delete-training" data-id="${row.id}">
                        <i class="fa fa-trash"></i>
                    </span>
                </div>`;
            }}
        ]
    });
}

async function handle_addTrainingForm(form) {
    let data = {};
    let employee_id = $('#searchEmployee').val();
    data['employee_id'] = employee_id;
    data['type_id'] = $('#training_type').val();
    data['option_id'] = $('#training_options').val();
    data['trainer_id'] = $('#trainer').val();
    data['cost'] = $('#cost').val();
    data['start_date'] = $('#start_date').val();
    data['end_date'] = $('#end_date').val();
    data['description'] = $('#description').val();

    

    if(employee_id.length == 0) {
        toaster.error('Please select at least one employee', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        return;
    }

    // employee id is array
    // data['employee_id'] = data['employee_id'].split(',');
    console.log(data);
    // return;
    
    try {
        const response = await send_trainingPost('save training', data);
        console.log(response);
        let res = JSON.parse(response);
        if (!res.error) {
            toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                $('#add_training').modal('hide');
                form.reset();
                $('.my-select').selectpicker('refresh');
                load_training();
            });
        } else {
            toaster.error(res.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
        toaster.error('An error occurred while saving the training', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
    }
}

async function handle_editTrainingForm(form) {
    let data = {};
    data['id'] = $('#edit_training_id').val();
    data['type_id'] = $('#edit_type_id').val();
    data['option_id'] = $('#edit_option_id').val();
    data['trainer_id'] = $('#edit_trainer_id').val();
    data['cost'] = $('#edit_cost').val();
    data['start_date'] = $('#edit_start_date').val();
    data['end_date'] = $('#edit_end_date').val();
    data['description'] = $('#edit_description').val();
    data['status'] = $('#edit_training_status').val();

    console.log(data);
    // return;
    
    try {
        let response = await send_trainingPost('update training', data);
        let res = JSON.parse(response);
        
        if (!res.error) {
            toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                $('#edit_training').modal('hide');
                load_training();
            });
        } else {
            toaster.error(res.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
        toaster.error('An error occurred while updating the training', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
    }
}

async function get_training(id) {
    try {
        let response = await send_trainingPost('get training', {id: id});
        console.log(response)
        let res = JSON.parse(response);
        
        if (!res.error && res.data) {
            let training = res.data;
            
            // Populate form fields
            $('#edit_training_id').val(training.id);
            $('#edit_employee_info').val(training.full_name + ' - ' + training.staff_no);
            $('#edit_type_id').val(training.type_id);
            $('#edit_option_id').val(training.option_id);
            $('#edit_trainer_id').val(training.trainer_id);
            $('#edit_cost').val(training.cost);
            $('#edit_start_date').val(training.start_date.split(' ')[0]); // Extract date part
            $('#edit_end_date').val(training.end_date.split(' ')[0]); // Extract date part
            $('#edit_description').val(training.description);
            $('#edit_training_status').val(training.status);
            
        } else {
            toaster.error(res.msg || 'Failed to load training data', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
        }
    } catch (err) {
        console.error('Error occurred while fetching training:', err);
        toaster.error('An error occurred while loading the training data', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
    }
}

function delete_training(id) {
    swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        buttons: ['Cancel', 'Yes, delete it!']
    }).then(async (result) => {
        if (result) {
            try {
                let response = await send_trainingPost('delete training', {id: id});
                let res = JSON.parse(response);
                
                if (!res.error) {
                    toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                        load_training();
                    });
                } else {
                    toaster.error(res.msg, 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
                }
            } catch (err) {
                console.error('Error occurred during deletion:', err);
                toaster.error('An error occurred while deleting the training', 'Error', { top: '20%', right: '20px', hide: true, duration: 1000 });
            }
        }
    });
}