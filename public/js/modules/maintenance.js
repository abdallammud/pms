document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('requestsTable')) {
        loadRequests();
        initRequestForm();
    }

    // Handle Save Request Form Submission
    $(document).on('click', '#saveRequestBtn', function (e) {
        e.preventDefault();
        var form = $('#saveRequestForm')[0];
        if (form.checkValidity()) {
            var formData = new FormData(form);
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="bi bi-save me-2"></i>Saving...');

            $.ajax({
                url: base_url + '/app/maintenance_controller.php?action=save_request',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        swal('Error', response.msg, 'error');
                    } else {
                        toaster.success(response.msg, 'Success');
                        $('#addRequestModal').modal('hide');
                        $('#requestsTable').DataTable().ajax.reload();
                    }
                },
                error: function () {
                    swal('Error', 'An unexpected error occurred.', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="bi bi-save me-2"></i>Submit Request');
                }
            });
        } else {
            form.reportValidity();
        }
    });

    // Reset Modal on close
    $(document).on('hidden.bs.modal', '#addRequestModal', function () {
        $('#saveRequestForm')[0].reset();
        $('#request_id').val('');
        $('#maintenance_status_div').addClass('d-none');
        $('#modal_title').text('New Maintenance Request');
        $('.selectpicker').selectpicker('refresh');
        $('#unitSelect').html('<option value="">Select Unit</option>').selectpicker('refresh');
    });

    // Property selection change - load units
    $(document).on('change', '#propertySelect', function () {
        var propertyId = $(this).val();
        loadAvailableUnits(propertyId);
    });
});

function loadRequests() {
    if ($.fn.DataTable.isDataTable('#requestsTable')) {
        $('#requestsTable').DataTable().destroy();
    }

    $('#requestsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": base_url + "/app/maintenance_controller.php?action=get_requests",
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "reference_number" },
            { "data": "property_name" },
            { "data": "unit_number" },
            { "data": "priority" },
            { "data": "assigned_to" },
            { "data": "status" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[0, "desc"]]
    });
}

function initRequestForm() {
    if ($('.selectpicker').length) {
        $('.selectpicker').selectpicker();
    }
}

function loadAvailableUnits(propertyId, selectedUnitId = null) {
    if (!propertyId) {
        $('#unitSelect').html('<option value="">Select Unit</option>').selectpicker('refresh');
        return;
    }

    $.ajax({
        url: base_url + '/app/maintenance_controller.php?action=get_available_units',
        type: 'POST',
        data: { property_id: propertyId },
        dataType: 'json',
        success: function (response) {
            var options = '<option value="">Select Unit</option>';
            if (response.data) {
                response.data.forEach(function (unit) {
                    var selected = (selectedUnitId == unit.id) ? 'selected' : '';
                    options += '<option value="' + unit.id + '" ' + selected + '>' + unit.unit_number + '</option>';
                });
            }
            $('#unitSelect').html(options).selectpicker('refresh');
        }
    });
}

function editRequest(id) {
    $.ajax({
        url: base_url + '/app/maintenance_controller.php?action=get_request&id=' + id,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.error) {
                swal('Error', response.msg, 'error');
            } else {
                var data = response.data;
                $('#request_id').val(data.id);
                $('#propertySelect').val(data.property_id).selectpicker('refresh');

                $('#prioritySelect').val(data.priority).selectpicker('refresh');
                $('#requester').val(data.requester);
                $('#description').val(data.description);
                $('#statusSelect').val(data.status).selectpicker('refresh');

                $('#maintenance_status_div').removeClass('d-none');
                $('#modal_title').text('Edit Maintenance Request: ' + data.reference_number);

                // Load units and pre-select
                loadAvailableUnits(data.property_id, data.unit_id);

                $('#addRequestModal').modal('show');
            }
        }
    });
}

function deleteRequest(id) {
    swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: base_url + '/app/maintenance_controller.php?action=delete_request',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        swal('Error', response.msg, 'error');
                    } else {
                        toaster.success(response.msg, 'Deleted');
                        $('#requestsTable').DataTable().ajax.reload();
                    }
                }
            });
        }
    });
}

// ============== ASSIGNMENT HANDLING ==============
document.addEventListener('DOMContentLoaded', function () {
    $(function () {
        // Handle Assign Request Form Submission
        $(document).on('click', '#saveAssignmentBtn', function (e) {
            e.preventDefault();
            var form = $('#assignRequestForm')[0];
            if (form.checkValidity()) {
                var formData = new FormData(form);
                var $btn = $(this);
                $btn.prop('disabled', true).html('<i class="bi bi-check-circle me-2"></i>Assigning...');

                $.ajax({
                    url: base_url + '/app/maintenance_controller.php?action=assign_request',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function (response) {
                        if (response.error) {
                            swal('Error', response.msg, 'error');
                        } else {
                            toaster.success(response.msg, 'Success');
                            $('#assignMaintenanceModal').modal('hide');
                            if ($('#requestsTable').length) {
                                $('#requestsTable').DataTable().ajax.reload();
                            }
                            location.reload();
                        }
                    },
                    error: function () {
                        swal('Error', 'An unexpected error occurred.', 'error');
                    },
                    complete: function () {
                        $btn.prop('disabled', false).html('<i class="bi bi-check-circle me-2"></i>Assign Request');
                    }
                });
            } else {
                form.reportValidity();
            }
        });

        // Reset Assignment Modal on close
        $(document).on('hidden.bs.modal', '#assignMaintenanceModal', function () {
            $('#assignRequestForm')[0].reset();
            $('#assignment_id').val('');
            $('#assign_modal_title').html('<i class="bi bi-person-plus me-2"></i>Assign Maintenance Request');
            $('.selectpicker').selectpicker('refresh');
        });
    });
});

// Open assignment modal for a specific request
function assignRequest(requestId) {
    $('#assign_request_id').val(requestId).selectpicker('refresh');
    $('#assignMaintenanceModal').modal('show');
}
