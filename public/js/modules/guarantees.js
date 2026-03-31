document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('guaranteesTable')) {
        loadGuarantees();
    }

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
 * Reset guarantee form including photo previews
 */
function resetGuaranteeForm() {
    $('#addGuaranteeForm')[0].reset();
    $('#guarantee_id').val('');
    $('#guarantee_existing_id_photo').val('');
    $('#guarantee_existing_work_id_photo').val('');

    // Reset photo zones to placeholder state
    ['guarantee_id_photo_area', 'guarantee_work_id_photo_area'].forEach(function (areaId) {
        var area = document.getElementById(areaId);
        if (!area) return;
        area.querySelector('.photo-zone-placeholder').classList.remove('d-none');
        area.querySelector('.photo-zone-preview').classList.add('d-none');
        area.querySelector('.photo-preview-img').src = '';
    });

    $('#addGuaranteeLabel').html('<i class="bi bi-shield-check me-2"></i>Add Guarantor');
    $('#saveGuaranteeBtn').html('<i class="bi bi-save me-1"></i> Save Guarantor');
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
                $('#saveGuaranteeBtn').html('<i class="bi bi-save me-1"></i> Update Guarantor');

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
 * Show existing photo in upload area (new photo-zone structure)
 */
function showGuaranteeExistingPhoto(areaId, photoUrl) {
    var area = document.getElementById(areaId);
    if (!area) return;
    area.querySelector('.photo-zone-placeholder').classList.add('d-none');
    var preview = area.querySelector('.photo-zone-preview');
    preview.querySelector('.photo-preview-img').src = photoUrl;
    preview.classList.remove('d-none');
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
