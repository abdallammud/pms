var amenitiesTable;

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('amenitiesTable')) {
        loadAmenitiesDatatable();
    }

    $(document).on('hidden.bs.modal', '#amenityModal', function () {
        document.getElementById('amenityForm').reset();
        $('#amenity_id').val('');
        $('#amenityModalLabel').text('Add Amenity');
        document.getElementById('amenityIconPreview').innerHTML = '<i class="bi bi-tag"></i>';
    });
});

function loadAmenitiesDatatable() {
    if ($.fn.DataTable.isDataTable('#amenitiesTable')) {
        $('#amenitiesTable').DataTable().destroy();
    }
    amenitiesTable = $('#amenitiesTable').DataTable({
        processing: true, serverSide: true,
        ajax: { url: base_url + '/app/amenity_controller.php?action=get_amenities', type: 'POST' },
        columns: [
            { data: 'name' },
            { data: 'icon' },
            { data: 'status' },
            { data: 'created_at' },
            { data: 'actions', orderable: false }
        ],
        order: [[0, 'asc']]
    });
}

function saveAmenity() {
    var formData = $('#amenityForm').serialize();
    $.ajax({
        url: base_url + '/app/amenity_controller.php?action=save',
        type: 'POST', data: formData, dataType: 'json',
        success: function (r) {
            if (r.error) { swal('Error', r.msg, 'error'); return; }
            $('#amenityModal').modal('hide');
            amenitiesTable.ajax.reload();
            toaster.success(r.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
        },
        error: function () { swal('Error', 'An unexpected error occurred.', 'error'); }
    });
}

function editAmenity(id) {
    $.ajax({
        url: base_url + '/app/amenity_controller.php?action=get_amenity&id=' + id,
        type: 'GET', dataType: 'json',
        success: function (data) {
            $('#amenity_id').val(data.id);
            $('#am_name').val(data.name);
            $('#am_icon').val(data.icon || '');
            $('#am_status').val(data.status);
            if (typeof previewAmenityIcon === 'function') previewAmenityIcon(data.icon || '');
            $('#amenityModalLabel').text('Edit Amenity');
            $('#amenityModal').modal('show');
        },
        error: function () { swal('Error', 'Could not fetch amenity data.', 'error'); }
    });
}

function deleteAmenity(id) {
    swal({ title: 'Delete Amenity?', icon: 'warning', buttons: true, dangerMode: true })
    .then(function (ok) {
        if (!ok) return;
        $.ajax({
            url: base_url + '/app/amenity_controller.php?action=delete',
            type: 'POST', data: { id: id }, dataType: 'json',
            success: function (r) {
                if (r.error) { swal('Error', r.msg, 'error'); return; }
                toaster.success(r.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                amenitiesTable.ajax.reload();
            },
            error: function () { swal('Error', 'An unexpected error occurred.', 'error'); }
        });
    });
}
