/**
 * Lease Module JavaScript
 */

// Get base URL or fallback to empty string

document.addEventListener('DOMContentLoaded', function () {
    // Initialize leases table if on leases page
    if (document.getElementById('leasesTable')) {
        loadLeases();
    }

    // Initialize add lease form if on add lease page
    if (document.getElementById('addLeaseForm')) {
        initAddLeaseForm();
    }
});

/**
 * Initialize the Add Lease Form
 */
function initAddLeaseForm() {
    // Initialize Bootstrap Select for all selectpickers
    if (typeof $.fn.selectpicker !== 'undefined') {
        $('#lease_tenant_select, #lease_guarantee_select, #lease_property_select, #lease_unit_select').selectpicker();
    }

    // Initialize TinyMCE for lease conditions
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#lease_conditions_editor',
            height: 300,
            menubar: true,
            plugins: 'lists link table code',
            toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist outdent indent | link table | code'
        });
    }

    // Load properties only if select is empty (for add form)
    var propertySelect = $('#lease_property_select');
    if (propertySelect.find('option').length <= 1) {
        loadLeaseProperties();
    }

    // Handle property change to load units
    $('#lease_property_select').on('change', function () {
        var propertyId = $(this).val();
        loadUnitsByProperty(propertyId);
    });

    // Handle form submission
    $('#addLeaseForm').on('submit', function (e) {
        e.preventDefault();
        saveLease();
    });
}

/**
 * Load all properties into the select dropdown
 */
function loadLeaseProperties() {
    $.ajax({
        url: base_url + '/app/property_controller.php?action=get_all_properties',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            var select = $('#lease_property_select');
            select.find('option:not(:first)').remove();

            if (data && data.length > 0) {
                data.forEach(function (property) {
                    select.append('<option value="' + property.id + '">' + property.name + '</option>');
                });
            }
            select.selectpicker('refresh');
        },
        error: function () {
            console.error('Failed to load properties');
        }
    });
}

/**
 * Load units based on selected property
 */
function loadUnitsByProperty(propertyId) {
    var select = $('#lease_unit_select');
    select.find('option:not(:first)').remove();

    if (!propertyId) {
        select.selectpicker('refresh');
        return;
    }

    $.ajax({
        url: base_url + '/app/property_controller.php?action=get_units_by_property&property_id=' + propertyId,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (data && data.length > 0) {
                data.forEach(function (unit) {
                    var statusBadge = unit.status === 'vacant' ? ' (Vacant)' : ' (' + unit.status + ')';
                    select.append('<option value="' + unit.id + '" data-rent="' + unit.rent_amount + '">' + unit.unit_number + statusBadge + '</option>');
                });
            } else {
                select.append('<option value="" disabled>No units available</option>');
            }
            select.selectpicker('refresh');
        },
        error: function () {
            console.error('Failed to load units');
            select.selectpicker('refresh');
        }
    });
}

/**
 * Save Lease
 */
function saveLease() {
    var $btn = $('#saveLeaseBtn');
    if ($btn.prop('disabled')) return;

    var form = $('#addLeaseForm')[0];
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Get TinyMCE content
    var leaseConditions = '';
    if (tinymce.get('lease_conditions_editor')) {
        leaseConditions = tinymce.get('lease_conditions_editor').getContent();
    }

    var formData = new FormData(form);
    formData.set('lease_conditions', leaseConditions);

    $btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-2"></i> Saving...');

    $.ajax({
        url: base_url + '/app/lease_controller.php?action=save_lease',
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
                // Redirect to leases list after 1.5 seconds
                setTimeout(function () {
                    window.location.href = base_url + '/leases';
                }, 1500);
            }
        },
        error: function () {
            swal('Error', 'An unexpected error occurred.', 'error');
        },
        complete: function () {
            $btn.prop('disabled', false).html('<i class="bi bi-save me-2"></i> Save Lease');
        }
    });
}

/**
 * Load Leases DataTable
 */
function loadLeases() {
    if ($.fn.DataTable.isDataTable('#leasesTable')) {
        $('#leasesTable').DataTable().destroy();
    }

    $('#leasesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "pageLength": 25,
        "ajax": {
            "url": base_url + "/app/lease_controller.php?action=get_leases",
            "type": "POST"
        },
        "columns": [
            { "data": "reference_number" },
            { "data": "tenant_name" },
            { "data": "property_unit" },
            { "data": "monthly_rent" },
            { "data": "start_date" },
            { "data": "end_date" },
            { "data": "status" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[4, "desc"]] // Order by start_date desc
    });
}

/**
 * View Lease Details
 */
function viewLease(id) {
    window.location.href = base_url + '/view_lease/' + id;
}

/**
 * Edit Lease
 */
function editLease(id) {
    window.location.href = base_url + '/edit_lease/' + id;
}

/**
 * Delete Lease
 */
function deleteLease(id) {
    swal({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: base_url + '/app/lease_controller.php?action=delete_lease',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        swal('Error', response.msg, 'error');
                    } else {
                        toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                        $('#leasesTable').DataTable().ajax.reload();
                    }
                },
                error: function () {
                    swal('Error', 'An unexpected error occurred.', 'error');
                }
            });
        }
    });
}
