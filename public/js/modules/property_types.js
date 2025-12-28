/**
 * Property Types Module JavaScript
 */

var propertyTypesTable;

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('propertyTypesTable')) {
<<<<<<< HEAD
        loadPropertyTypesDatatable();
=======
        loadPropertyTypes();
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
    }
});

/**
 * Load Property Types DataTable
 */
<<<<<<< HEAD
function loadPropertyTypesDatatable() {
=======
function loadPropertyTypes() {
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
    if ($.fn.DataTable.isDataTable('#propertyTypesTable')) {
        $('#propertyTypesTable').DataTable().destroy();
    }

    propertyTypesTable = $('#propertyTypesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
<<<<<<< HEAD
            "url": base_url + "/app/property_type_controller.php?action=get_property_types",
            "type": "POST",
            // dataFilter: function (data) {
            //     console.log(data)
            // }
=======
            "url": "app/property_type_controller.php?action=get_property_types",
            "type": "POST"
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
        },
        "columns": [
            { "data": "type_name" },
            { "data": "description" },
            { "data": "status" },
            { "data": "created_at" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[0, "asc"]]
    });
}

/**
 * Save property type (create/update)
 */
function savePropertyType() {
    var formData = $('#propertyTypeForm').serialize();

    $.ajax({
<<<<<<< HEAD
        url: base_url + '/app/property_type_controller.php?action=save',
=======
        url: 'app/property_type_controller.php?action=save',
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function (response) {
            if (response.error) {
                swal("Error", response.msg, "error");
            } else {
                $('#propertyTypeModal').modal('hide');
                $('#propertyTypeForm')[0].reset();
                $('#property_type_id').val('');
                propertyTypesTable.ajax.reload();
                toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
            }
        },
        error: function () {
            swal("Error", "An unexpected error occurred.", "error");
        }
    });
}

/**
 * Edit property type - fetch and populate modal
 */
function editPropertyType(id) {
    $.ajax({
<<<<<<< HEAD
        url: base_url + '/app/property_type_controller.php?action=get_property_type&id=' + id,
=======
        url: 'app/property_type_controller.php?action=get_property_type&id=' + id,
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data) {
                $('#property_type_id').val(data.id);
                $('#type_name').val(data.type_name);
                $('#description').val(data.description);
                $('#status').val(data.status);

                $('#propertyTypeModalLabel').text('Edit Property Type');
                $('#propertyTypeModal').modal('show');
            } else {
                swal("Error", "Could not fetch property type data.", "error");
            }
        },
        error: function () {
            swal("Error", "Could not fetch property type data.", "error");
        }
    });
}

/**
 * Delete property type with confirmation
 */
function deletePropertyType(id) {
    swal({
        title: "Are you sure?",
        text: "Once deleted, you will not be able to recover this property type!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
        .then((willDelete) => {
            if (willDelete) {
                $.ajax({
<<<<<<< HEAD
                    url: base_url + '/app/property_type_controller.php?action=delete',
=======
                    url: 'app/property_type_controller.php?action=delete',
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function (response) {
                        if (response.error) {
                            swal("Error", response.msg, "error");
                        } else {
                            toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                            propertyTypesTable.ajax.reload();
                        }
                    },
                    error: function () {
                        swal("Error", "An unexpected error occurred.", "error");
                    }
                });
            }
        });
}

/**
 * Reset modal on close
 */
document.addEventListener('DOMContentLoaded', function () {
    $(document).on('hidden.bs.modal', '#propertyTypeModal', function () {
        $('#propertyTypeForm')[0].reset();
        $('#property_type_id').val('');
        $('#propertyTypeModalLabel').text('Add Property Type');
    });
});
