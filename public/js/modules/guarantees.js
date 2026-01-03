document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('guaranteesTable')) {
        loadGuarantees();
    }

    // Initialize photo upload preview handlers
    initGuaranteePhotoUploadHandlers();

    // Handle Save Guarantee Form Submission
    $(document).on('click', '#saveGuaranteeBtn', function () {
        var form = $('#addGuaranteeForm')[0];
        if (form.checkValidity()) {
            var formData = new FormData(form);

            $.ajax({
                url: base_url + '/app/guarantee_controller.php?action=save_guarantee',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        swal('Error', response.msg, 'error');
                    } else {
                        toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                        $('#addGuaranteeModal').modal('hide');
                        resetGuaranteeForm();
                        $('#guaranteesTable').DataTable().ajax.reload();
                    }
                },
                error: function () {
                    swal('Error', 'An unexpected error occurred.', 'error');
                }
            });
        } else {
            form.reportValidity();
        }
    });

    // Reset Guarantee Modal on close
    $(document).on('hidden.bs.modal', '#addGuaranteeModal', function () {
        resetGuaranteeForm();
    });
});

/**
 * Initialize photo upload preview handlers for guarantees
 */
function initGuaranteePhotoUploadHandlers() {
    // ID Photo handler
    $(document).on('change', '#guarantee_id_photo', function () {
        previewGuaranteePhoto(this, 'guarantee_id_photo_area');
    });

    // Work ID Photo handler
    $(document).on('change', '#guarantee_work_id_photo', function () {
        previewGuaranteePhoto(this, 'guarantee_work_id_photo_area');
    });
}

/**
 * Preview photo after selection
 */
function previewGuaranteePhoto(input, areaId) {
    var area = $('#' + areaId);
    var placeholder = area.find('.upload-placeholder');
    var preview = area.find('.upload-preview');
    var img = preview.find('img');

    if (input.files && input.files[0]) {
        // Validate file size (5MB max)
        if (input.files[0].size > 5 * 1024 * 1024) {
            swal('Error', 'File size must be less than 5MB', 'error');
            input.value = '';
            return;
        }

        var reader = new FileReader();
        reader.onload = function (e) {
            img.attr('src', e.target.result);
            placeholder.hide();
            preview.show();
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Reset guarantee form including photo previews
 */
function resetGuaranteeForm() {
    $('#addGuaranteeForm')[0].reset();
    $('#guarantee_id').val('');
    $('#guarantee_existing_id_photo').val('');
    $('#guarantee_existing_work_id_photo').val('');

    // Reset photo previews
    $('#guarantee_id_photo_area .upload-placeholder').show();
    $('#guarantee_id_photo_area .upload-preview').hide();
    $('#guarantee_work_id_photo_area .upload-placeholder').show();
    $('#guarantee_work_id_photo_area .upload-preview').hide();

    $('#addGuaranteeLabel').html('<i class="bi bi-person-plus me-2"></i>Add Guarantor');
    $('#saveGuaranteeBtn').text('Save Guarantor');
}

function loadGuarantees() {
    if ($.fn.DataTable.isDataTable('#guaranteesTable')) {
        $('#guaranteesTable').DataTable().destroy();
    }

    $('#guaranteesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": base_url + "/app/guarantee_controller.php?action=get_guarantees",
            "type": "POST"
        },
        "columns": [
            { "data": "full_name" },
            { "data": "phone" },
            { "data": "email" },
            { "data": "id_number" },
            { "data": "status" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[0, "asc"]],
        "drawCallback": function () { }
    });
}

/**
 * Edit guarantee - fetch and populate modal
 */
function editGuarantee(id) {
    $.ajax({
        url: base_url + '/app/guarantee_controller.php?action=get_guarantee&id=' + id,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data) {
                $('#guarantee_id').val(data.id);
                $('#guarantee_full_name').val(data.full_name);
                $('#guarantee_phone').val(data.phone);
                $('#guarantee_email').val(data.email);
                $('#guarantee_id_number').val(data.id_number);
                $('#guarantee_work_info').val(data.work_info);
                $('#guarantee_status').val(data.status);

                // Handle existing photos
                if (data.id_photo) {
                    $('#guarantee_existing_id_photo').val(data.id_photo);
                    showGuaranteeExistingPhoto('guarantee_id_photo_area', base_url + '/public/' + data.id_photo);
                }
                if (data.work_id_photo) {
                    $('#guarantee_existing_work_id_photo').val(data.work_id_photo);
                    showGuaranteeExistingPhoto('guarantee_work_id_photo_area', base_url + '/public/' + data.work_id_photo);
                }

                $('#addGuaranteeLabel').html('<i class="bi bi-pencil me-2"></i>Edit Guarantor');
                $('#saveGuaranteeBtn').text('Update Guarantor');

                $('#addGuaranteeModal').modal('show');
            } else {
                swal('Error', 'Could not fetch guarantor data.', 'error');
            }
        },
        error: function () {
            swal('Error', 'Could not fetch guarantor data.', 'error');
        }
    });
}

/**
 * Show existing photo in upload area
 */
function showGuaranteeExistingPhoto(areaId, photoUrl) {
    var area = $('#' + areaId);
    var placeholder = area.find('.upload-placeholder');
    var preview = area.find('.upload-preview');
    var img = preview.find('img');

    img.attr('src', photoUrl);
    placeholder.hide();
    preview.show();
}

/**
 * Delete guarantee with confirmation
 */
function deleteGuarantee(id) {
    swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: base_url + '/app/guarantee_controller.php?action=delete_guarantee',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        swal('Error', response.msg, 'error');
                    } else {
                        toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                        $('#guaranteesTable').DataTable().ajax.reload();
                    }
                },
                error: function () {
                    swal('Error', 'An unexpected error occurred.', 'error');
                }
            });
        }
    });
}
