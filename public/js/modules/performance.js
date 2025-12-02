async function send_performancePost(str, data) {
    let [action, endpoint] = str.split(' ');

    try {
        const response = await $.post(`${base_url}/app/performance_controller.php?action=${action}&endpoint=${endpoint}`, data);
        return response;
    } catch (error) {
        console.error('Error occurred during the request:', error);
        return null;
    }
}

document.addEventListener("DOMContentLoaded", function() {
    load_indicators();
    // Initialize indicators functionality
    $('#addIndicatorsForm').on('submit', (e) => {
        handle_addIndicatorsForm(e.target);
        return false;
    });
    
    $('#editIndicatorsForm').on('submit', (e) => {
        handle_editIndicatorsForm(e.target);
        return false;
    });
    
    // Edit indicator
    $(document).on('click', '.edit_indicator', function(e) {
        let id = $(e.currentTarget).data('recid');
        get_indicator(id);
    });
    
    // Delete indicator
    $(document).on('click', '.delete_indicator', async (e) => {
        let id = $(e.currentTarget).data('recid');
        swal({
            title: "Are you sure?",
            text: `You are going to delete this indicator.`,
            icon: "warning",
            className: 'warning-swal',
            buttons: ["Cancel", "Yes, delete"],
        }).then(async (confirm) => {
            if (confirm) {
                let data = { id: id };
                try {
                    let response = await send_performancePost('delete indicator', data);
                    if (response) {
                        let res = JSON.parse(response);
                        if (res.error) {
                            toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
                        } else {
                            toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                                load_indicators();
                            });
                        }
                    } else {
                        console.log('Failed to delete indicator.' + response);
                    }
                } catch (err) {
                    console.error('Error occurred during delete:', err);
                }
            }
        });
    });

    // Appraisals
    handleAppraisals();
    $('.my-select').selectpicker({
	    noneResultsText: "No results found"
	});

    // Search employee
    $(document).on('keyup', '.bootstrap-select.searchEmployee input.form-control', async (e) => {
        let search = $(e.target).val();
        let searchFor = 'appraisal';
        let formData = {search:search, searchFor:searchFor}
        if(search) {
            try { 
                let response = await send_performancePost('search employee4Select', formData);
                let res = JSON.parse(response);
                if(!res.error) {
                    $('#searchEmployee').html(res.options)
                    $('.my-select').selectpicker('refresh');
                } 
            } catch (err) {
                console.error('Error occurred during employee search:', err);
            }
        }
    });

    // Goal tracking
    handleGoalTracking();
});

// Load indicators into DataTable
function load_indicators() {
    if ($.fn.DataTable.isDataTable('#indicatorsDT')) {
        $('#indicatorsDT').DataTable().destroy();
    }
    
    $('#indicatorsDT').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `${base_url}/app/performance_controller.php?action=load&endpoint=indicators`,
            type: "POST"
        },
        columns: [
            { data: "department" },
            { data: "designation" },
            { 
                data: "overall_rating",
                render: function(data, type, row) {
                    if (type === 'display') {
                        let stars = '';
                        const rating = parseFloat(data);
                        const fullStars = Math.floor(rating);
                        const hasHalfStar = rating % 1 >= 0.5;
                        
                        for (let i = 0; i < fullStars; i++) {
                            stars += '<i class="bi bi-star-fill text-warning"></i>';
                        }
                        
                        if (hasHalfStar) {
                            stars += '<i class="bi bi-star-half text-warning"></i>';
                        }
                        
                        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
                        for (let i = 0; i < emptyStars; i++) {
                            stars += '<i class="bi bi-star text-warning"></i>';
                        }
                        
                        return `<div>${stars} <span class="ms-1">${rating}</span></div>`;
                    }
                    return data;
                }
            },
            { data: "added_date" },
            { data: "actions", orderable: false }
        ],
        order: [[0, 'asc']]
    });
}

// Handle add indicator form submission
async function handle_addIndicatorsForm(form) {
    let error = validateForm(form)
    let department_id = $('#slcDepartment').val();
    let designation_id = $('#slcDesignation').val();
    let department = $('#slcDepartment option:selected').text();
    let designation = $('#slcDesignation option:selected').text();
    
    if (error) return false;
    // Get form data
    let formData = {
        department_id: department_id,
        designation_id: designation_id,
        department: department,
        designation: designation,
        business_pro: $('input[name="business_pro"]:checked').val() || 0,
        oral_com: $('input[name="oral_com"]:checked').val() || 0,
        leadership: $('input[name="leadership"]:checked').val() || 0,
        project_mgt: $('input[name="project_mgt"]:checked').val() || 0,
        res_allocating: $('input[name="res_allocating"]:checked').val() || 0
    };

    console.log(formData);
    
    try {
        let response = await send_performancePost('save indicators', formData);
        // console.log(response);
        if (response) {
            let res = JSON.parse(response);
            if (res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    // Reset form
                    $('#addIndicatorsForm')[0].reset();
                    $('#add_indicators').modal('hide');
                    load_indicators();
                });
            }
        } else {
            console.log('Failed to add indicator.');
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
    }
}

// Get indicator details for editing
async function get_indicator(id) {
    let data = { id: id };
    try {
        let response = await send_performancePost('get indicator', data);
        if (response) {
            let res = JSON.parse(response);
            if (res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                let indicator = res.data;
                let attributes = JSON.parse(indicator.attributes);
                
                if(indicator.department_id == 0) indicator.department_id = "All";
                if(indicator.designation_id == 0) indicator.designation_id = "All";
                
                // Set form values
                $('#edit_indicator_id').val(indicator.id);
                $('#edit_slcDepartment').val(indicator.department_id);
                $('#edit_slcDesignation').val(indicator.designation_id);
                
                // Set ratings for each competency
                // Behavioural Competencies
                let businessProcess = attributes['Behavioural Competencies'].find(item => item.name === 'Business Process');
                if (businessProcess && businessProcess.rating > 0) {
                    $(`#edit_business_pro_${businessProcess.rating}`).prop('checked', true);
                }
                
                let oralCommunication = attributes['Behavioural Competencies'].find(item => item.name === 'Oral Communication');
                if (oralCommunication && oralCommunication.rating > 0) {
                    $(`#edit_oral_com_${oralCommunication.rating}`).prop('checked', true);
                }
                
                // Organizational Competencies
                let leadership = attributes['Organizational Competencies'].find(item => item.name === 'Leadership');
                if (leadership && leadership.rating > 0) {
                    $(`#edit_leadership_${leadership.rating}`).prop('checked', true);
                }
                
                let projectManagement = attributes['Organizational Competencies'].find(item => item.name === 'Project Management');
                if (projectManagement && projectManagement.rating > 0) {
                    $(`#edit_project_mgt_${projectManagement.rating}`).prop('checked', true);
                }
                
                // Technical Competencies
                let allocatingResources = attributes['Technical Competencies'].find(item => item.name === 'Allocating Resources');
                if (allocatingResources && allocatingResources.rating > 0) {
                    $(`#edit_res_allocating_${allocatingResources.rating}`).prop('checked', true);
                }
                
                // Show edit modal
                $('#edit_indicators').modal('show');
            }
        } else {
            console.log('Failed to get indicator details.');
        }
    } catch (err) {
        console.error('Error occurred while getting indicator details:', err);
    }
}

// Handle edit indicator form submission
async function handle_editIndicatorsForm(form) {
    let error = validateForm(form)
    if (error) return false;
    
    let id = $('#edit_indicator_id').val();
    let department_id = $('#edit_slcDepartment').val();
    let designation_id = $('#edit_slcDesignation').val();
    let department = $('#edit_slcDepartment option:selected').text();
    let designation = $('#edit_slcDesignation option:selected').text();
    
    // Get form data
    let formData = {
        id: id,
        department_id: department_id,
        designation_id: designation_id,
        department: department,
        designation: designation,
        business_pro: $('input[name="business_pro"]:checked').val() || 0,
        oral_com: $('input[name="oral_com"]:checked').val() || 0,
        leadership: $('input[name="leadership"]:checked').val() || 0,
        project_mgt: $('input[name="project_mgt"]:checked').val() || 0,
        res_allocating: $('input[name="res_allocating"]:checked').val() || 0
    };
    
    try {
        let response = await send_performancePost('update indicators', formData);
        if (response) {
            let res = JSON.parse(response);
            if (res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    // Reset form and close modal
                    $('#editIndicatorsForm')[0].reset();
                    $('#edit_indicators').modal('hide');
                    load_indicators();
                });
            }
        } else {
            console.log('Failed to update indicator.');
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
    }
}

function handleAppraisals() {
    $('#slcDepartment, #slcDesignation').on('change', function() {
        let department_id = $('#slcDepartment').val();
        let designation_id = $('#slcDesignation').val();
        let formData = { department_id: department_id, designation_id: designation_id };

        // Clear previous indicator selections when department/designation changes
        $('.indicator-stars input[type="radio"]').prop('checked', false);

        // Only proceed if both department and designation are selected
        if (department_id && designation_id) {
            try {
                // Assuming send_performancePost returns a Promise that resolves with the parsed JSON response
                send_performancePost('get indicator4Appraisals', formData).then(response => { // Corrected endpoint name if it's 'indicator4Appraisals'
                    console.log('API Response:', response);

                    if (response.status === 200 && !response.error && response.data) {
                        const indicatorsData = response.data;
                        console.log('Indicators data to set:', indicatorsData);

                        // Function to set the indicator rating based on the provided data and HTML structure
                        function setIndicatorRating(competencyKey, rating) {
                            // Map the simplified_attributes keys from PHP to your HTML input names
                            let inputName;
                            if (competencyKey === 'business_pro') {
                                inputName = 'indicator_business_pro';
                            } else if (competencyKey === 'oral_com') {
                                inputName = 'indicator_oral_com';
                            } else if (competencyKey === 'leadership') {
                                inputName = 'indicator_leadership';
                            } else if (competencyKey === 'project_mgt') {
                                inputName = 'indicator_project_mgt';
                            } else if (competencyKey === 'res_allocating') {
                                inputName = 'indicator_res_allocating';
                            } else {
                                console.warn("Unknown competency key in data for setting indicator:", competencyKey);
                                return;
                            }

                            // Check the radio button with the corresponding name and value
                            $(`input[name="${inputName}"][value="${rating}"]`).prop('checked', true);

                            // You might need to manually trigger a change or update the display
                            // if your star rating library doesn't automatically react to prop('checked', true)
                            // For some custom star rating components, you might need to re-initialize them
                            // or trigger a custom event. If it's pure CSS, `checked` is usually enough.
                        }

                        // Iterate through the received indicatorsData and set the ratings
                        for (const key in indicatorsData) {
                            if (indicatorsData.hasOwnProperty(key)) {
                                setIndicatorRating(key, indicatorsData[key]);
                            }
                        }

                    } else {
                        console.error('Error or no data in response for indicators:', response.msg || 'No data');
                        // Clear all indicator selections if there's an error or no data
                        $('.indicator-stars input[type="radio"]').prop('checked', false);
                    }
                }).catch(err => {
                    console.error('Error occurred during indicator search:', err);
                    // Clear all indicator selections on network/promise error
                    $('.indicator-stars input[type="radio"]').prop('checked', false);
                });
            } catch (err) {
                console.error('Synchronous error caught during indicator search:', err);
                // Clear all indicator selections on synchronous error
                $('.indicator-stars input[type="radio"]').prop('checked', false);
            }
        } else {
            console.log('Department or Designation not selected. Clearing indicators.');
            // This case is handled by the initial clear above, but good to keep for clarity.
        }
    });
    
    // Initialize the appraisals form submission
    $('#addAppraisalsForm').on('submit', (e) => {
        handle_addAppraisalsForm(e.target);
        return false;
    });
    
    // Edit appraisal
    $(document).on('click', '.edit_appraisal', function(e) {
        let id = $(e.currentTarget).data('recid');
        get_appraisal(id);
    });
    
    // Delete appraisal
    $(document).on('click', '.delete_appraisal', async (e) => {
        let id = $(e.currentTarget).data('recid');
        swal({
            title: "Are you sure?",
            text: `You are going to delete this appraisal.`,
            icon: "warning",
            className: 'warning-swal',
            buttons: ["Cancel", "Yes, delete"],
        }).then(async (confirm) => {
            if (confirm) {
                let data = { id: id };
                try {
                    let response = await send_performancePost('delete appraisal', data);
                    console.log(response)
                    if (response) {
                        let res = JSON.parse(response);
                        if (res.error) {
                            toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
                        } else {
                            toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                                load_appraisals();
                            });
                        }
                    } else {
                        console.log('Failed to delete appraisal.' + response);
                    }
                } catch (err) {
                    console.error('Error occurred during delete:', err);
                }
            }
        });
    });

    load_appraisals()
}

// Load appraisals into DataTable
function load_appraisals() {
    if ($.fn.DataTable.isDataTable('#appraisalsDT')) {
        $('#appraisalsDT').DataTable().destroy();
    }
    
    $('#appraisalsDT').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `${base_url}/app/performance_controller.php?action=load&endpoint=appraisals`,
            type: "POST"
        },
        columns: [
            { data: "staff_no" },
            { data: "full_name" },
            { 
                data: "indicator_rating",
                render: function(data, type, row) {
                    if (type === 'display') {
                        let stars = '';
                        let ratings = JSON.parse(data);
                        const rating = parseFloat(calculateAverageRating(ratings));
                        const fullStars = Math.floor(rating);
                        const hasHalfStar = rating % 1 >= 0.5;
                        
                        for (let i = 0; i < fullStars; i++) {
                            stars += '<i class="bi bi-star-fill text-warning"></i>';
                        }
                        
                        if (hasHalfStar) {
                            stars += '<i class="bi bi-star-half text-warning"></i>';
                        }
                        
                        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
                        for (let i = 0; i < emptyStars; i++) {
                            stars += '<i class="bi bi-star text-warning"></i>';
                        }
                        
                        return `<div>${stars} <span class="ms-1">${rating}</span></div>`;
                    }
                    return data;
                }
            },
            { 
                data: "appraisal_rating",
                render: function(data, type, row) {
                    if (type === 'display') {
                        let stars = '';
                        let ratings = JSON.parse(data);
                        const rating = parseFloat(calculateAverageRating(ratings));
                        const fullStars = Math.floor(rating);
                        const hasHalfStar = rating % 1 >= 0.5;
                        
                        for (let i = 0; i < fullStars; i++) {
                            stars += '<i class="bi bi-star-fill text-warning"></i>';
                        }
                        
                        if (hasHalfStar) {
                            stars += '<i class="bi bi-star-half text-warning"></i>';
                        }
                        
                        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
                        for (let i = 0; i < emptyStars; i++) {
                            stars += '<i class="bi bi-star text-warning"></i>';
                        }
                        
                        return `<div>${stars} <span class="ms-1">${rating}</span></div>`;
                    }
                    return data;
                }
            },
            { data: "month" },
            { data: "added_date" },
            { data: "actions", orderable: false }
        ],
        order: [[5, 'desc']]
    });
}

// Handle add appraisal form submission
async function handle_addAppraisalsForm(form) {
    let error = validateForm(form)
    let department_id = $('#slcDepartment').val();
    let designation_id = $('#slcDesignation').val();
    let department = $('#slcDepartment option:selected').text();
    let designation = $('#slcDesignation option:selected').text();
    
    if (error) return false;

    // Build indicator ratings JSON
    let indicatorRatings = {
        'Behavioural Competencies': [
            { name: 'Business Process', rating: parseInt($('input[name="indicator_business_pro"]:checked').val() || 0) },
            { name: 'Oral Communication', rating: parseInt($('input[name="indicator_oral_com"]:checked').val() || 0) }
        ],
        'Organizational Competencies': [
            { name: 'Leadership', rating: parseInt($('input[name="indicator_leadership"]:checked').val() || 0) },
            { name: 'Project Management', rating: parseInt($('input[name="indicator_project_mgt"]:checked').val() || 0) }
        ],
        'Technical Competencies': [
            { name: 'Allocating Resources', rating: parseInt($('input[name="indicator_res_allocating"]:checked').val() || 0) }
        ]
    };

    // Build appraisal ratings JSON
    let appraisalRatings = {
        'Behavioural Competencies': [
            { name: 'Business Process', rating: parseInt($('input[name="appraisal_business_pro"]:checked').val() || 0) },
            { name: 'Oral Communication', rating: parseInt($('input[name="appraisal_oral_com"]:checked').val() || 0) }
        ],
        'Organizational Competencies': [
            { name: 'Leadership', rating: parseInt($('input[name="appraisal_leadership"]:checked').val() || 0) },
            { name: 'Project Management', rating: parseInt($('input[name="appraisal_project_mgt"]:checked').val() || 0) }
        ],
        'Technical Competencies': [
            { name: 'Allocating Resources', rating: parseInt($('input[name="appraisal_res_allocating"]:checked').val() || 0) }
        ]
    };
    
    // Get form data
    let formData = {
        emp_id: $('#searchEmployee').val(),
        department_id: department_id,
        designation_id: designation_id,
        department: department,
        designation: designation,
        indicator_rating: JSON.stringify(indicatorRatings),
        appraisal_rating: JSON.stringify(appraisalRatings),
        month: $('#txtMonth').val(),
        remarks: $('#txtRemarks').val()
    };
    
    try {
        let response = await send_performancePost('save appraisal', formData);
        if (response) {
            let res = JSON.parse(response);
            if (res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    // Reset form
                    $('#addAppraisalsForm')[0].reset();
                    $('#add_appraisals').modal('hide');
                    load_appraisals();
                });
            }
        } else {
            console.log('Failed to add appraisal.');
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
    }
}

// Get appraisal details for editing
async function get_appraisal(id) {
    let data = { id: id };
    try {
        let response = await send_performancePost('get appraisal', data);
        if (response) {
            let res = JSON.parse(response);
            if (res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                let appraisal = res.data;
                let indicatorRatings = JSON.parse(appraisal.indicator_rating);
                let appraisalRatings = JSON.parse(appraisal.appraisal_rating);
                
                // Set form values
                $('#edit_appraisal_id').val(appraisal.id);
                $('#edit_slcDepartment').val(appraisal.department_id);
                $('#edit_slcDesignation').val(appraisal.designation_id);
                $('#edit_txtMonth').val(appraisal.month);
                $('#edit_txtRemarks').val(appraisal.remarks);
                
                // Set indicator ratings
                setIndicatorRating('Business Process', indicatorRatings['Behavioural Competencies'][0].rating, 'indicator');
                setIndicatorRating('Oral Communication', indicatorRatings['Behavioural Competencies'][1].rating, 'indicator');
                setIndicatorRating('Leadership', indicatorRatings['Organizational Competencies'][0].rating, 'indicator');
                setIndicatorRating('Project Management', indicatorRatings['Organizational Competencies'][1].rating, 'indicator');
                setIndicatorRating('Allocating Resources', indicatorRatings['Technical Competencies'][0].rating, 'indicator');
                
                // Set appraisal ratings
                setIndicatorRating('Business Process', appraisalRatings['Behavioural Competencies'][0].rating, 'appraisal');
                setIndicatorRating('Oral Communication', appraisalRatings['Behavioural Competencies'][1].rating, 'appraisal');
                setIndicatorRating('Leadership', appraisalRatings['Organizational Competencies'][0].rating, 'appraisal');
                setIndicatorRating('Project Management', appraisalRatings['Organizational Competencies'][1].rating, 'appraisal');
                setIndicatorRating('Allocating Resources', appraisalRatings['Technical Competencies'][0].rating, 'appraisal');
                
                // Show edit modal
                $('#edit_appraisals').modal('show');
            }
        } else {
            console.log('Failed to get appraisal details.');
        }
    } catch (err) {
        console.error('Error occurred while getting appraisal details:', err);
    }
}

// Handle edit appraisal form submission
async function handle_editAppraisalsForm(form) {
    let error = validateForm(form)
    let department_id = $('#edit_slcDepartment').val();
    let designation_id = $('#edit_slcDesignation').val();
    let department = $('#edit_slcDepartment option:selected').text();
    let designation = $('#edit_slcDesignation option:selected').text();
    
    if (error) return false;

    // Build indicator ratings JSON
    let indicatorRatings = {
        'Behavioural Competencies': [
            { name: 'Business Process', rating: parseInt($('input[name="edit_indicator_business_pro"]:checked').val() || 0) },
            { name: 'Oral Communication', rating: parseInt($('input[name="edit_indicator_oral_com"]:checked').val() || 0) }
        ],
        'Organizational Competencies': [
            { name: 'Leadership', rating: parseInt($('input[name="edit_indicator_leadership"]:checked').val() || 0) },
            { name: 'Project Management', rating: parseInt($('input[name="edit_indicator_project_mgt"]:checked').val() || 0) }
        ],
        'Technical Competencies': [
            { name: 'Allocating Resources', rating: parseInt($('input[name="edit_indicator_res_allocating"]:checked').val() || 0) }
        ]
    };

    // Build appraisal ratings JSON
    let appraisalRatings = {
        'Behavioural Competencies': [
            { name: 'Business Process', rating: parseInt($('input[name="edit_appraisal_business_pro"]:checked').val() || 0) },
            { name: 'Oral Communication', rating: parseInt($('input[name="edit_appraisal_oral_com"]:checked').val() || 0) }
        ],
        'Organizational Competencies': [
            { name: 'Leadership', rating: parseInt($('input[name="edit_appraisal_leadership"]:checked').val() || 0) },
            { name: 'Project Management', rating: parseInt($('input[name="edit_appraisal_project_mgt"]:checked').val() || 0) }
        ],
        'Technical Competencies': [
            { name: 'Allocating Resources', rating: parseInt($('input[name="edit_appraisal_res_allocating"]:checked').val() || 0) }
        ]
    };
    
    // Get form data
    let formData = {
        id: $('#edit_appraisal_id').val(),
        department_id: department_id,
        designation_id: designation_id,
        department: department,
        designation: designation,
        indicator_rating: JSON.stringify(indicatorRatings),
        appraisal_rating: JSON.stringify(appraisalRatings),
        month: $('#edit_txtMonth').val(),
        remarks: $('#edit_txtRemarks').val()
    };
    
    try {
        let response = await send_performancePost('update appraisal', formData);
        if (response) {
            let res = JSON.parse(response);
            if (res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                    $('#edit_appraisals').modal('hide');
                    load_appraisals();
                });
            }
        } else {
            console.log('Failed to update appraisal.');
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
    }
}

// Calculate average rating from ratings object
function calculateAverageRating(ratings) {
    let totalRating = 0;
    let ratingCount = 0;
    
    for(let category in ratings) {
        for(let item of ratings[category]) {
            totalRating += item.rating;
            ratingCount++;
        }
    }
    
    return ratingCount > 0 ? (totalRating / ratingCount).toFixed(1) : 0;
}

// Set indicator rating in edit form
function setIndicatorRating(name, rating, type) {
    if(rating > 0) {
        $(`#edit_${type}_${name.toLowerCase().replace(' ', '_')}_${rating}`).prop('checked', true);
    }
}

// Load goal tracking into DataTable
function load_goal_tracking() {
    if ($.fn.DataTable.isDataTable('#goalTrackingDT')) {
        $('#goalTrackingDT').DataTable().destroy();
    }
    
    $('#goalTrackingDT').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: `${base_url}/app/performance_controller.php?action=load&endpoint=goal_tracking`,
            type: "POST"
        },
        columns: [
            { data: "type" },
            { data: "subject" },
            { data: "department" },
            { data: "start_date" },
            { data: "end_date" },
            { data: "progress", render: function(data, type, row) {
                return `${data}%`;
            } },
            { data: "added_date" },
            { data: "actions", orderable: false }
        ],
        order: [[0, 'asc']]
    });
}

function handleGoalTracking() {
    load_goal_tracking();

    
    // Add goal tracking
    $('#addGoalTrackingForm').on('submit', async function(e) {
        e.preventDefault();

        console.log('Form submitted');
        let error = validateForm(this);
        console.log(error);
        if (error) {
            return false;
        }
        
        let formData = {
            department_id: $('#slcDepartment').val(),
            type_id: $('#slcGoalType').val(),
            department: $('#slcDepartment option:selected').text(),
            type: $('#slcGoalType option:selected').text(),
            subject: $('#subject').val(),
            target: $('#target').val(),
            description: $('#description').val(),
            start_date: $('#slcStartDate').val(),
            end_date: $('#slcEndDate').val(),
            progress: '0',
            status: 'Active'
        };

        console.log(formData);

        try {
            let response = await send_performancePost('save goal_tracking', formData);
            console.log(response);
            if (response) {
                let res = JSON.parse(response);
                if (res.error) {
                    toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
                } else {
                    toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                        // Reset form
                        $('#addGoalTrackingForm')[0].reset();
                        $('#add_goal_tracking').modal('hide');
                        load_goal_tracking();
                    });
                }
            } else {
                console.log('Failed to add goal_tracking.');
            }
        } catch (err) {
            console.error('Error occurred during form submission:', err);
        }
    });

    // Edit goal tracking
    $(document).on('click', '.edit_goal_tracking', function() {
        let id = $(this).data('recid');
        
        send_performancePost('get goal_tracking', { id: id })
            .then(response => {
                console.log(response);
                if (!response.error) {
                    let res = JSON.parse(response);
                    let data = res.data;
                    let modal = $('#edit_goal_tracking');
                    $(modal).find('#id').val(data.id);
                    $(modal).find('#slcDepartment').val(data.department_id);
                    $(modal).find('#slcGoalType').val(data.type_id);
                    $(modal).find('#slcStartDate').val(data.start_date);
                    $(modal).find('#slcEndDate').val(data.end_date);
                    $(modal).find('#subject').val(data.subject);
                    $(modal).find('#target').val(data.target);
                    $(modal).find('#description').val(data.description);
                    $(modal).find('#progressRange').val(data.progress);
                    $(modal).find('#progressValue').val(data.progress);

                    const progressValueEl = document.getElementById("progressValue");
                    const rangeEl = document.getElementById("progressRange");
            
                    progressValueEl.textContent = data.progress;
                    rangeEl.style.setProperty('--val', data.progress + '%');
                    
                    $(modal).modal('show');
                } else {
                    toastr.error(response.msg);
                }
            });
    });

    // Update goal tracking
    $('#editGoalTrackingForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!validateForm(this)) {
            return false;
        }

        let modal = $('#edit_goal_tracking');
        
        let formData = {
            id: $('#id').val(),
            department_id: modal.find('#slcDepartment').val(),
            type_id: modal.find('#slcGoalType').val(),
            department: modal.find('#slcDepartment option:selected').text(),
            type: modal.find('#slcGoalType option:selected').text(),
            subject: modal.find('#subject').val(),
            target: modal.find('#target').val(),
            description: modal.find('#description').val(),
            start_date: modal.find('#slcStartDate').val(),
            end_date: modal.find('#slcEndDate').val(),
            progress: modal.find('#progressValue').text()
        };

        send_performancePost('update goal_tracking', formData)
            .then(response => {
                let res = JSON.parse(response);
                if (res.error) {
                    toaster.error(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
                } else {
                    toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                        modal.modal('hide');
                        load_goal_tracking();
                    });
                }
            });
    });

    // Delete goal tracking
    $(document).on('click', '.delete_goal_tracking', function() {
        let id = $(this).data('recid');
        swal({
            title: 'Are you sure?',
            text: 'You will not be able to recover this goal tracking!',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                send_performancePost('delete goal_tracking', { id: id })
                .then(response => {
                    let res = JSON.parse(response);
                    console.log(res);
                    if (res.error) {
                        toaster.error(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
                    } else {
                        toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 1000 }).then(() => {
                            load_goal_tracking();
                        });
                    }
                });
            }
        });
    });
}
