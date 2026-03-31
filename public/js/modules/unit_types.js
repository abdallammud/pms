var unitTypesTable;

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('unitTypesTable')) {
        loadUnitTypesDatatable();
    }

    $(document).on('hidden.bs.modal', '#unitTypeModal', function () {
        document.getElementById('unitTypeForm').reset();
        $('#unit_type_id').val('');
        $('#unitTypeModalLabel').text('Add Unit Type');
    });
});

function loadUnitTypesDatatable() {
    if ($.fn.DataTable.isDataTable('#unitTypesTable')) {
        $('#unitTypesTable').DataTable().destroy();
    }
    unitTypesTable = $('#unitTypesTable').DataTable({
        processing: true, serverSide: true,
        ajax: { url: base_url + '/app/unit_type_controller.php?action=get_unit_types', type: 'POST' },
        columns: [
            { data: 'type_name' },
            { data: 'description' },
            { data: 'status' },
            { data: 'created_at' },
            { data: 'actions', orderable: false }
        ],
        order: [[0, 'asc']]
    });
}

function saveUnitType() {
    var formData = $('#unitTypeForm').serialize();
    $.ajax({
        url: base_url + '/app/unit_type_controller.php?action=save',
        type: 'POST', data: formData, dataType: 'json',
        success: function (r) {
            if (r.error) { swal('Error', r.msg, 'error'); return; }
            $('#unitTypeModal').modal('hide');
            unitTypesTable.ajax.reload();
            toaster.success(r.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
        },
        error: function () { swal('Error', 'An unexpected error occurred.', 'error'); }
    });
}

function editUnitType(id) {
    $.ajax({
        url: base_url + '/app/unit_type_controller.php?action=get_unit_type&id=' + id,
        type: 'GET', dataType: 'json',
        success: function (data) {
            $('#unit_type_id').val(data.id);
            $('#ut_type_name').val(data.type_name);
            $('#ut_description').val(data.description);
            $('#ut_status').val(data.status);
            $('#unitTypeModalLabel').text('Edit Unit Type');
            $('#unitTypeModal').modal('show');
        },
        error: function () { swal('Error', 'Could not fetch unit type data.', 'error'); }
    });
}

function deleteUnitType(id) {
    swal({ title: 'Are you sure?', text: 'This unit type will be deleted.', icon: 'warning', buttons: true, dangerMode: true })
    .then(function (ok) {
        if (!ok) return;
        $.ajax({
            url: base_url + '/app/unit_type_controller.php?action=delete',
            type: 'POST', data: { id: id }, dataType: 'json',
            success: function (r) {
                if (r.error) { swal('Error', r.msg, 'error'); return; }
                toaster.success(r.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                unitTypesTable.ajax.reload();
            },
            error: function () { swal('Error', 'An unexpected error occurred.', 'error'); }
        });
    });
}
