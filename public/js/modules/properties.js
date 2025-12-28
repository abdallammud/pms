document.addEventListener('DOMContentLoaded', function () {
    // Load property types and managers dropdowns
    loadPropertyTypes();
    loadManagers();
    loadPropertiesForUnits();

    if (document.getElementById('propertiesTable')) {
        loadProperties();
        initBulkActions();
    }
    if (document.getElementById('unitsTable')) {
        loadUnits();
        initUnitBulkActions();
    }

    // Handle Add Property Form Submission
    $(document).off('click', '#savePropertyBtn').on('click', '#savePropertyBtn', function (e) {
        e.preventDefault();
        var $btn = $(this);
        if ($btn.prop('disabled')) return; // Prevent double submission

        var form = $('#addPropertyForm')[0];
        if (form.checkValidity()) {
            var formData = new FormData(form);
            $btn.prop('disabled', true); // Disable button

            $.ajax({
                url: base_url + '/app/property_controller.php?action=save_property',
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
                        $('#addPropertyModal').modal('hide');
                        $('#addPropertyForm')[0].reset();
                        $('#propertiesTable').DataTable().ajax.reload();
                    }
                },
                error: function () {
                    swal('Error', 'An unexpected error occurred.', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false); // Re-enable button
                }
            });
        } else {
            form.reportValidity();
        }
    });

    // Handle Add Unit Form Submission
    $(document).off('click', '#saveUnitBtn').on('click', '#saveUnitBtn', function (e) {
        e.preventDefault();
        var $btn = $(this);
        if ($btn.prop('disabled')) return; // Prevent double submission

        var form = $('#addUnitForm')[0];
        if (form.checkValidity()) {
            var formData = new FormData(form);
            $btn.prop('disabled', true); // Disable button

            $.ajax({
                url: base_url + '/app/property_controller.php?action=save_unit',
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
                        $('#addUnitModal').modal('hide');
                        $('#addUnitForm')[0].reset();
                        $('#unitsTable').DataTable().ajax.reload();
                    }
                },
                error: function () {
                    swal('Error', 'An unexpected error occurred.', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false); // Re-enable button
                }
            });
        } else {
            form.reportValidity();
        }
    });

    // Reset Unit Modal on close
    $(document).on('hidden.bs.modal', '#addUnitModal', function () {
        $('#addUnitForm')[0].reset();
        $('#unit_id').val(''); // Clear the unit ID
        $('#unit_property_select').val('').selectpicker('refresh'); // Reset Bootstrap Select
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
        "pageLength": 25,
        "ajax": {
            "url": base_url + "/app/property_controller.php?action=get_properties",
            "type": "POST"
        },
        "columns": [
            {
                "data": "id",
                "orderable": false,
                "render": function (data, type, row) {
                    return '<input type="checkbox" class="property-checkbox" value="' + data + '">';
                }
            },
            { "data": "name" },
            { "data": "type" },
            { "data": "address" },
            { "data": "units" },
            { "data": "occupied_units" },
            { "data": "manager_name" },
            { "data": "owner_name" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[1, "asc"]],
        "drawCallback": function () {
            // Re-bind select all check
            $('#selectAllProperties').prop('checked', false);
        }
    });
}

/**
 * Load property types into dropdown
 */
function loadPropertyTypes() {
    $.ajax({
        url: `${base_url}/app/property_type_controller.php?action=get_active_types`,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            var select = $('#property_type_select');
            select.find('option:not(:first)').remove(); // Keep "Select Type" option

            if (data && data.length > 0) {
                data.forEach(function (type) {
                    select.append('<option value="' + type.id + '">' + type.type_name + '</option>');
                });
            }
            // Refresh Bootstrap Select
            select.selectpicker('refresh');
        },
        error: function () {
            console.error('Failed to load property types');
        }
    });
}

/**
 * Load managers (users) into dropdown
 */
function loadManagers() {
    $.ajax({
        url: `${base_url}/app/user_controller.php?action=get_managers`,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            var select = $('#manager_select');
            select.find('option:not(:first)').remove();

            if (data && data.length > 0) {
                data.forEach(function (user) {
                    select.append('<option value="' + user.id + '">' + user.name + '</option>');
                });
            }
            // Refresh Bootstrap Select
            select.selectpicker('refresh');
        },
        error: function () {
            console.error('Failed to load managers');
        }
    });
}

function editProperty(id) {
    $.ajax({
        url: `${base_url}/app/property_controller.php?action=get_property&id=${id}`,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            // Populate form fields
            $('#property_id').val(data.id);
            $('#property_name').val(data.name);
            $('#property_type_select').val(data.type_id).selectpicker('refresh');
            $('#property_address').val(data.address);
            $('#property_city').val(data.city);
            $('#property_owner').val(data.owner_name);
            $('#manager_select').val(data.manager_id).selectpicker('refresh');
            $('textarea[name="description"]').val(data.description);

            // Change modal title and button text
            $('#addPropertyLabel').html('<i class="bi bi-pencil me-2"></i>Edit Property');
            $('#savePropertyBtn').text('Update Property');

            $('#addPropertyModal').modal('show');
        },
        error: function () {
            swal('Error', 'Could not fetch property data.', 'error');
        }
    });
}

function deleteProperty(id) {
    swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: base_url + '/app/property_controller.php?action=delete_property',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        swal('Error', response.msg, 'error');
                    } else {
                        toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                        $('#propertiesTable').DataTable().ajax.reload();
                    }
                },
                error: function () {
                    swal('Error', 'An unexpected error occurred.', 'error');
                }
            });
        }
    });
}

// Reset modal on close
document.addEventListener('DOMContentLoaded', function () {
    $(document).on('hidden.bs.modal', '#addPropertyModal', function () {
        $('#addPropertyForm')[0].reset();
        $('#property_id').val(''); // Clear the property ID
        $('#property_type_select').val('').selectpicker('refresh'); // Reset Bootstrap Select
        $('#manager_select').val('').selectpicker('refresh'); // Reset Bootstrap Select
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
        "pageLength": 25,
        "ajax": {
            "url": base_url + "/app/property_controller.php?action=get_units",
            "type": "POST"
        },
        "columns": [
            {
                "data": "id",
                "orderable": false,
                "render": function (data, type, row) {
                    return '<input type="checkbox" class="unit-checkbox" value="' + data + '">';
                }
            },
            { "data": "unit_number" },
            { "data": "unit_type" },
            { "data": "property_name" },
            { "data": "status" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[1, "asc"]],
        "drawCallback": function () {
            // Re-bind select all check
            $('#selectAllUnits').prop('checked', false);
        }
    });
}

/**
 * Load properties for unit dropdown
 */
function loadPropertiesForUnits() {
    $.ajax({
        url: `${base_url}/app/property_controller.php?action=get_all_properties`,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            var select = $('#unit_property_select');
            select.find('option:not(:first)').remove();

            if (data && data.length > 0) {
                data.forEach(function (property) {
                    select.append('<option value="' + property.id + '">' + property.name + '</option>');
                });
            }
            // Refresh Bootstrap Select
            select.selectpicker('refresh');
        },
        error: function () {
            console.error('Failed to load properties for units');
        }
    });
}

/**
 * Edit unit - fetch and populate modal
 */
function editUnit(id) {
    $.ajax({
        url: base_url + '/app/property_controller.php?action=get_unit&id=' + id,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data) {
                $('#unit_id').val(data.id);
                $('#unit_property_select').val(data.property_id).selectpicker('refresh');
                $('#unit_number').val(data.unit_number);
                $('#unit_type').val(data.unit_type);
                $('#unit_size').val(data.size_sqft);
                $('#unit_rent').val(data.rent_amount);
                $('#unit_status').val(data.status);
                $('#unit_tenant_select').val(data.tenant_id);

                $('#addUnitLabel').html('<i class="bi bi-pencil me-2"></i>Edit Unit');
                $('#saveUnitBtn').html('<i class="bi bi-save me-1"></i>Update Unit');

                $('#addUnitModal').modal('show');
            } else {
                swal('Error', 'Could not fetch unit data.', 'error');
            }
        },
        error: function () {
            swal('Error', 'Could not fetch unit data.', 'error');
        }
    });
}

/**
 * Delete unit with confirmation
 */
function deleteUnit(id) {
    swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: base_url + '/app/property_controller.php?action=delete_unit',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        swal('Error', response.msg, 'error');
                    } else {
                        toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                        $('#unitsTable').DataTable().ajax.reload();
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
 * Initialize Bulk Actions
 */
function initBulkActions() {
    // Select All Checkbox - Use delegated click for reliability
    $(document).off('click', '#selectAllProperties').on('click', '#selectAllProperties', function () {
        var isChecked = this.checked;
        var table = $('#propertiesTable').DataTable();
        $(table.rows().nodes()).find('.property-checkbox').prop('checked', isChecked).trigger('change');
    });

    // Individual checkbox click to update Select All
    $(document).off('click', '.property-checkbox').on('click', '.property-checkbox', function () {
        var table = $('#propertiesTable').DataTable();
        var total = table.rows().nodes().length;
        var checked = $(table.rows().nodes()).find('.property-checkbox:checked').length;
        $('#selectAllProperties').prop('checked', total > 0 && total === checked);
    });

    // Apply Bulk Action
    $('#applyBulkActionBtn').on('click', function () {
        var action = $('#bulkActionSelect').val();
        var selectedIds = [];

        $('.property-checkbox:checked').each(function () {
            selectedIds.push($(this).val());
        });

        if (!action) {
            swal('Warning', 'Please select an action.', 'warning');
            return;
        }

        if (selectedIds.length === 0) {
            swal('Warning', 'Please select at least one property.', 'warning');
            return;
        }

        var confirmText = "You won't be able to revert this!";

        swal({
            title: 'Are you sure?',
            text: "You are about to delete " + selectedIds.length + " property(s). " + confirmText,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                performBulkAction(action, selectedIds);
            }
        });
    });
}

/**
 * Perform Bulk Action
 */
function performBulkAction(action, ids) {
    var $btn = $('#applyBulkActionBtn');
    $btn.prop('disabled', true).text('Processing...');

    $.ajax({
        url: base_url + '/app/property_controller.php?action=bulk_action',
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
                $('#propertiesTable').DataTable().ajax.reload();
                $('#selectAllProperties').prop('checked', false);
                $('#bulkActionSelect').val('');
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

/**
 * Initialize Unit Bulk Actions
 */
function initUnitBulkActions() {
    // Select All Checkbox - Use delegated click for reliability
    $(document).off('click', '#selectAllUnits').on('click', '#selectAllUnits', function () {
        var isChecked = this.checked;
        var table = $('#unitsTable').DataTable();
        $(table.rows().nodes()).find('.unit-checkbox').prop('checked', isChecked).trigger('change');
    });

    // Individual checkbox click to update Select All
    $(document).off('click', '.unit-checkbox').on('click', '.unit-checkbox', function () {
        var table = $('#unitsTable').DataTable();
        var total = table.rows().nodes().length;
        var checked = $(table.rows().nodes()).find('.unit-checkbox:checked').length;
        $('#selectAllUnits').prop('checked', total > 0 && total === checked);
    });

    // Apply Bulk Action
    $('#applyBulkActionBtnUnits').on('click', function () {
        var action = $('#bulkActionSelectUnits').val();
        var selectedIds = [];

        $('.unit-checkbox:checked').each(function () {
            selectedIds.push($(this).val());
        });

        if (!action) {
            swal('Warning', 'Please select an action.', 'warning');
            return;
        }

        if (selectedIds.length === 0) {
            swal('Warning', 'Please select at least one unit.', 'warning');
            return;
        }

        var confirmText = "You won't be able to revert this!";

        swal({
            title: 'Are you sure?',
            text: "You are about to delete " + selectedIds.length + " unit(s). " + confirmText,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                performUnitBulkAction(action, selectedIds);
            }
        });
    });
}

/**
 * Perform Unit Bulk Action
 */
function performUnitBulkAction(action, ids) {
    var $btn = $('#applyBulkActionBtnUnits');
    $btn.prop('disabled', true).text('Processing...');

    $.ajax({
        url: base_url + '/app/property_controller.php?action=bulk_action_unit',
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
                $('#unitsTable').DataTable().ajax.reload();
                $('#selectAllUnits').prop('checked', false);
                $('#bulkActionSelectUnits').val('');
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
