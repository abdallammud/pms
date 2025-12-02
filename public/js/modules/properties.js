document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('propertiesTable')) {
        loadProperties();
    }
    if (document.getElementById('unitsTable')) {
        loadUnits();
    }

    // Handle Add Property Form Submission
    $(document).on('click', '#savePropertyBtn', function () {
        var form = $('#addPropertyForm')[0];
        if (form.checkValidity()) {
            var formData = new FormData(form);
            // Add ID if editing (you might need a hidden input for property_id in the form)

            $.ajax({
                url: 'app/property_controller.php?action=save_property',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.error) {
                        Swal.fire('Error', response.msg, 'error');
                    } else {
                        Swal.fire('Success', response.msg, 'success');
                        $('#addPropertyModal').modal('hide');
                        $('#addPropertyForm')[0].reset();
                        $('#propertiesTable').DataTable().ajax.reload();
                    }
                },
                error: function () {
                    Swal.fire('Error', 'An unexpected error occurred.', 'error');
                }
            });
        } else {
            form.reportValidity();
        }
    });

    // Handle Add Unit Form Submission
    $(document).on('click', '#saveUnitBtn', function () {
        var form = $('#addUnitForm')[0];
        if (form.checkValidity()) {
            var formData = new FormData(form);
            // Add ID if editing

            $.ajax({
                url: 'app/property_controller.php?action=save_unit', // Need to implement this in controller
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.error) {
                        Swal.fire('Error', response.msg, 'error');
                    } else {
                        Swal.fire('Success', response.msg, 'success');
                        $('#addUnitModal').modal('hide');
                        $('#addUnitForm')[0].reset();
                        $('#unitsTable').DataTable().ajax.reload();
                    }
                },
                error: function () {
                    Swal.fire('Error', 'An unexpected error occurred.', 'error');
                }
            });
        } else {
            form.reportValidity();
        }
    });

    // Reset Unit Modal on close
    $(document).on('hidden.bs.modal', '#addUnitModal', function () {
        $('#addUnitForm')[0].reset();
        $('input[name="unit_id"]').remove();
        $('#addUnitLabel').html('<i class="bi bi-door-open me-2"></i>Add Unit');
        $('#saveUnitBtn').html('<i class="bi bi-save me-1"></i>Save Unit');
    });
});

function loadProperties() {
    if ($.fn.DataTable.isDataTable('#propertiesTable')) {
        $('#propertiesTable').DataTable().destroy();
    }

    $('#propertiesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "app/property_controller.php?action=get_properties",
            "type": "POST"
        },
        "columns": [
            { "data": "name" },
            { "data": "type" },
            { "data": "address" },
            { "data": "units" },
            { "data": "occupied_units" },
            { "data": "manager_name" },
            { "data": "owner_name" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[0, "asc"]]
    });
}

function editProperty(id) {
    // Implementation for editing property
    // Fetch data and populate modal
    $.ajax({
        url: 'app/property_controller.php?action=get_property&id=' + id,
        type: 'GET',
        success: function (data) {
            // Populate form fields
            $('input[name="name"]').val(data.name);
            $('select[name="type"]').val(data.type);
            $('input[name="address"]').val(data.address);
            $('input[name="city"]').val(data.city);
            $('input[name="owner_name"]').val(data.owner_name);
            $('select[name="manager_id"]').val(data.manager_id);
            $('textarea[name="description"]').val(data.description);

            // Add hidden input for ID if not exists
            if ($('input[name="property_id"]').length === 0) {
                $('#addPropertyForm').append('<input type="hidden" name="property_id" value="' + data.id + '">');
            } else {
                $('input[name="property_id"]').val(data.id);
            }

            // Change modal title and button text
            $('#addPropertyLabel').html('<i class="bi bi-pencil me-2"></i>Edit Property');
            $('#savePropertyBtn').text('Update Property');

            $('#addPropertyModal').modal('show');
        }
    });
}

function deleteProperty(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'app/property_controller.php?action=delete_property',
                type: 'POST',
                data: { id: id },
                success: function (response) {
                    if (response.error) {
                        Swal.fire('Error', response.msg, 'error');
                    } else {
                        Swal.fire('Deleted!', response.msg, 'success');
                        $('#propertiesTable').DataTable().ajax.reload();
                    }
                }
            });
        }
    });
}

// Reset modal on close
document.addEventListener('DOMContentLoaded', function () {
    $(document).on('hidden.bs.modal', '#addPropertyModal', function () {
        $('#addPropertyForm')[0].reset();
        $('input[name="property_id"]').remove();
        $('#addPropertyLabel').html('<i class="bi bi-building-add me-2"></i>Add Property');
        $('#savePropertyBtn').text('Save Property');
    });
});

function loadUnits() {
    if ($.fn.DataTable.isDataTable('#unitsTable')) {
        $('#unitsTable').DataTable().destroy();
    }

    $('#unitsTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "app/property_controller.php?action=get_units",
            "type": "POST"
        },
        "columns": [
            { "data": "unit_number" },
            { "data": "unit_type" },
            { "data": "property_name" },
            { "data": "status" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[0, "asc"]]
    });
}
