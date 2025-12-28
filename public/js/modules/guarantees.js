document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('guaranteesTable')) {
        loadGuarantees();
        initGuaranteeBulkActions();
    }

    // Handle Save Guarantee Form Submission
    $(document).on('click', '#saveGuaranteeBtn', function () {
        var form = $('#addGuaranteeForm')[0];
        if (form.checkValidity()) {
            var formData = new FormData(form);

            $.ajax({
                url: 'app/guarantee_controller.php?action=save_guarantee',
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
                        $('#addGuaranteeForm')[0].reset();
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
        $('#addGuaranteeForm')[0].reset();
        $('#guarantee_id').val(''); // Clear the guarantee ID
        $('#addGuaranteeLabel').html('<i class="bi bi-person-plus me-2"></i>Add Guarantor');
        $('#saveGuaranteeBtn').text('Save Guarantor');
    });
});

function loadGuarantees() {
    if ($.fn.DataTable.isDataTable('#guaranteesTable')) {
        $('#guaranteesTable').DataTable().destroy();
    }

    $('#guaranteesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "app/guarantee_controller.php?action=get_guarantees",
            "type": "POST"
        },
        "columns": [
            {
                "data": "id",
                "orderable": false,
                "render": function (data, type, row) {
                    return '<input type="checkbox" class="guarantee-checkbox" value="' + data + '">';
                }
            },
            { "data": "full_name" },
            { "data": "phone" },
            { "data": "email" },
            { "data": "id_number" },
            { "data": "status" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[1, "asc"]],
        "drawCallback": function () {
            // Re-bind select all check
            $('#selectAllGuarantees').prop('checked', false);
        }
    });
}

/**
 * Edit guarantee - fetch and populate modal
 */
function editGuarantee(id) {
    $.ajax({
        url: 'app/guarantee_controller.php?action=get_guarantee&id=' + id,
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
                url: 'app/guarantee_controller.php?action=delete_guarantee',
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

/**
 * Initialize Guarantee Bulk Actions
 */
function initGuaranteeBulkActions() {
    // Select All Checkbox - Use delegated click for reliability
    $(document).off('click', '#selectAllGuarantees').on('click', '#selectAllGuarantees', function () {
        var isChecked = this.checked;
        var table = $('#guaranteesTable').DataTable();
        $(table.rows().nodes()).find('.guarantee-checkbox').prop('checked', isChecked).trigger('change');
    });

    // Individual checkbox click to update Select All
    $(document).off('click', '.guarantee-checkbox').on('click', '.guarantee-checkbox', function () {
        var table = $('#guaranteesTable').DataTable();
        var total = table.rows().nodes().length;
        var checked = $(table.rows().nodes()).find('.guarantee-checkbox:checked').length;
        $('#selectAllGuarantees').prop('checked', total > 0 && total === checked);
    });

    // Apply Bulk Action
    $('#applyBulkActionBtnGuarantees').on('click', function () {
        var action = $('#bulkActionSelectGuarantees').val();
        var selectedIds = [];

        $('.guarantee-checkbox:checked').each(function () {
            selectedIds.push($(this).val());
        });

        if (!action) {
            swal('Warning', 'Please select an action.', 'warning');
            return;
        }

        if (selectedIds.length === 0) {
            swal('Warning', 'Please select at least one guarantor.', 'warning');
            return;
        }

        var confirmText = "You won't be able to revert this!";

        swal({
            title: 'Are you sure?',
            text: "You are about to delete " + selectedIds.length + " guarantor(s). " + confirmText,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                performGuaranteeBulkAction(action, selectedIds);
            }
        });
    });
}

/**
 * Perform Guarantee Bulk Action
 */
function performGuaranteeBulkAction(action, ids) {
    var $btn = $('#applyBulkActionBtnGuarantees');
    $btn.prop('disabled', true).text('Processing...');

    $.ajax({
        url: 'app/guarantee_controller.php?action=bulk_action',
        type: 'POST',
        data: {
            action_type: action,
            ids: ids
        },
        dataType: 'json',
        success: function (response) {
            if (response.error) {
                swal('Error', response.msg, 'error');
            } else {
                toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                $('#guaranteesTable').DataTable().ajax.reload();
                $('#selectAllGuarantees').prop('checked', false);
                $('#bulkActionSelectGuarantees').val('');
            }
        },
        error: function () {
            swal('Error', 'An unexpected error occurred.', 'error');
        },
        complete: function () {
            $btn.prop('disabled', false).text('Apply');
        }
    });
}
