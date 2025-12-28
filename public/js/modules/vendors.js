document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('vendorsTable')) {
        loadVendors();
    }

    // Handle Save Vendor Form Submission
    $(document).on('click', '#saveVendorBtn', function (e) {
        e.preventDefault();
        var form = $('#saveVendorForm')[0];
        if (form.checkValidity()) {
            var formData = new FormData(form);
            var $btn = $(this);
            $btn.prop('disabled', true).html('<i class="bi bi-save me-2"></i>Saving...');

            $.ajax({
                url: base_url + '/app/vendor_controller.php?action=save_vendor',
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
                        $('#addVendorModal').modal('hide');
                        $('#vendorsTable').DataTable().ajax.reload();
                    }
                },
                error: function () {
                    swal('Error', 'An unexpected error occurred.', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false).html('<i class="bi bi-save me-2"></i>Save Vendor');
                }
            });
        } else {
            form.reportValidity();
        }
    });

    // Reset Modal on close
    $(document).on('hidden.bs.modal', '#addVendorModal', function () {
        $('#saveVendorForm')[0].reset();
        $('#vendor_id').val('');
        $('#modal_title').text('Add New Vendor');
    });
});

function loadVendors() {
    if ($.fn.DataTable.isDataTable('#vendorsTable')) {
        $('#vendorsTable').DataTable().destroy();
    }

    $('#vendorsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": base_url + "/app/vendor_controller.php?action=get_vendors",
            "type": "POST"
        },
        "columns": [
            { "data": "id" },
            { "data": "vendor_name" },
            { "data": "service_type" },
            { "data": "phone" },
            { "data": "email" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[1, "asc"]]
    });
}

function editVendor(id) {
    $.ajax({
        url: base_url + '/app/vendor_controller.php?action=get_vendor&id=' + id,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.error) {
                swal('Error', response.msg, 'error');
            } else {
                var data = response.data;
                $('#vendor_id').val(data.id);
                $('#vendor_name').val(data.vendor_name);
                $('#service_type').val(data.service_type);
                $('#phone').val(data.phone);
                $('#email').val(data.email);

                $('#modal_title').text('Edit Vendor: ' + data.vendor_name);
                $('#addVendorModal').modal('show');
            }
        }
    });
}

function deleteVendor(id) {
    swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: base_url + '/app/vendor_controller.php?action=delete_vendor',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        swal('Error', response.msg, 'error');
                    } else {
                        toaster.success(response.msg, 'Deleted');
                        $('#vendorsTable').DataTable().ajax.reload();
                    }
                }
            });
        }
    });
}
