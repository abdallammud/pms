document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('tenantsTable')) {
        loadTenants();
    }

    // Handle Save Tenant Form Submission
    $(document).on('click', '#saveTenantBtn', function () {
        var form = $('#addTenantForm')[0];
        if (form.checkValidity()) {
            var formData = new FormData(form);

            $.ajax({
                url: 'app/tenant_controller.php?action=save_tenant',
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
                        $('#addTenantForm')[0].reset();
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
        $('#addTenantForm')[0].reset();
        $('#tenant_id').val(''); // Clear the tenant ID
        $('#addTenantLabel').html('<i class="bi bi-person-plus me-2"></i>Add Tenant');
        $('#saveTenantBtn').text('Save Tenant');
    });
});

function loadTenants() {
    if ($.fn.DataTable.isDataTable('#tenantsTable')) {
        $('#tenantsTable').DataTable().destroy();
    }

    $('#tenantsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "app/tenant_controller.php?action=get_tenants",
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
        "order": [[0, "asc"]]
    });
}

/**
 * Edit tenant - fetch and populate modal
 */
function editTenant(id) {
    $.ajax({
        url: 'app/tenant_controller.php?action=get_tenant&id=' + id,
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
                url: 'app/tenant_controller.php?action=delete_tenant',
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
