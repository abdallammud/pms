document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('tenantsTable')) {
        loadTenants();
    }

    // Initialize photo upload preview handlers
    initPhotoUploadHandlers('tenant');

    // Handle Save Tenant Form Submission
    $(document).on('click', '#saveTenantBtn', function () {
        var form = $('#addTenantForm')[0];
        if (form.checkValidity()) {
            var formData = new FormData(form);

            $.ajax({
                url: base_url + '/app/tenant_controller.php?action=save_tenant',
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
                        $('#addTenantModal').modal('hide');
                        resetTenantForm();
                        $('#tenantsTable').DataTable().ajax.reload();
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

    // Reset Tenant Modal on close
    $(document).on('hidden.bs.modal', '#addTenantModal', function () {
        resetTenantForm();
    });
});

/**
 * Initialize photo upload preview handlers
 */
function initPhotoUploadHandlers(prefix) {
    // ID Photo handler
    $(document).on('change', '#' + prefix + '_id_photo', function () {
        previewPhoto(this, prefix + '_id_photo_area');
    });

    // Work ID Photo handler
    $(document).on('change', '#' + prefix + '_work_id_photo', function () {
        previewPhoto(this, prefix + '_work_id_photo_area');
    });
}

/**
 * Preview photo after selection
 */
function previewPhoto(input, areaId) {
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
 * Remove photo preview and clear file input
 */
function removePhotoPreview(inputId) {
    var input = $('#' + inputId);
    var areaId = inputId + '_area';
    var area = $('#' + areaId);
    var placeholder = area.find('.upload-placeholder');
    var preview = area.find('.upload-preview');

    // Clear the file input
    input.val('');

    // Clear the hidden existing photo field
    var prefix = inputId.includes('work') ? 'tenant_existing_work_id_photo' : 'tenant_existing_id_photo';
    if (inputId.includes('guarantee')) {
        prefix = inputId.includes('work') ? 'guarantee_existing_work_id_photo' : 'guarantee_existing_id_photo';
    }
    $('#' + prefix).val('');

    // Show placeholder, hide preview
    placeholder.show();
    preview.hide();
    preview.find('img').attr('src', '');
}

/**
 * Reset tenant form including photo previews
 */
function resetTenantForm() {
    $('#addTenantForm')[0].reset();
    $('#tenant_id').val('');
    $('#tenant_existing_id_photo').val('');
    $('#tenant_existing_work_id_photo').val('');

    // Reset photo previews
    $('#tenant_id_photo_area .upload-placeholder').show();
    $('#tenant_id_photo_area .upload-preview').hide();
    $('#tenant_work_id_photo_area .upload-placeholder').show();
    $('#tenant_work_id_photo_area .upload-preview').hide();

    $('#addTenantLabel').html('<i class="bi bi-person-plus me-2"></i>Add Tenant');
    $('#saveTenantBtn').text('Save Tenant');
}

function loadTenants() {
    if ($.fn.DataTable.isDataTable('#tenantsTable')) {
        $('#tenantsTable').DataTable().destroy();
    }

    $('#tenantsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": base_url + "/app/tenant_controller.php?action=get_tenants",
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
 * Edit tenant - fetch and populate modal
 */
function editTenant(id) {
    $.ajax({
        url: base_url + '/app/tenant_controller.php?action=get_tenant&id=' + id,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data) {
                $('#tenant_id').val(data.id);
                $('#tenant_full_name').val(data.full_name);
                $('#tenant_phone').val(data.phone);
                $('#tenant_email').val(data.email);
                $('#tenant_id_number').val(data.id_number);
                $('#tenant_work_info').val(data.work_info);
                $('#tenant_status').val(data.status);

                // Handle existing photos
                if (data.id_photo) {
                    $('#tenant_existing_id_photo').val(data.id_photo);
                    showExistingPhoto('tenant_id_photo_area', base_url + '/public/' + data.id_photo);
                }
                if (data.work_id_photo) {
                    $('#tenant_existing_work_id_photo').val(data.work_id_photo);
                    showExistingPhoto('tenant_work_id_photo_area', base_url + '/public/' + data.work_id_photo);
                }

                $('#addTenantLabel').html('<i class="bi bi-pencil me-2"></i>Edit Tenant');
                $('#saveTenantBtn').text('Update Tenant');

                $('#addTenantModal').modal('show');
            } else {
                swal('Error', 'Could not fetch tenant data.', 'error');
            }
        },
        error: function () {
            swal('Error', 'Could not fetch tenant data.', 'error');
        }
    });
}

/**
 * Show existing photo in upload area
 */
function showExistingPhoto(areaId, photoUrl) {
    var area = $('#' + areaId);
    var placeholder = area.find('.upload-placeholder');
    var preview = area.find('.upload-preview');
    var img = preview.find('img');

    img.attr('src', photoUrl);
    placeholder.hide();
    preview.show();
}

/**
 * Delete tenant with confirmation
 */
function deleteTenant(id) {
    swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: base_url + '/app/tenant_controller.php?action=delete_tenant',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        swal('Error', response.msg, 'error');
                    } else {
                        toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                        $('#tenantsTable').DataTable().ajax.reload();
                    }
                },
                error: function () {
                    swal('Error', 'An unexpected error occurred.', 'error');
                }
            });
        }
    });
}
