/**
 * Lease Module JavaScript
 */

// Get base URL or fallback to empty string

document.addEventListener('DOMContentLoaded', function () {
    // Initialize leases table if on leases page
    if (document.getElementById('leasesTable')) {
        loadLeases();
        initBulkActions();

        $(document).off('click', '#selectAllLeasesCheckBox').on('click', '#selectAllLeasesCheckBox', function () {
            var isChecked = this.checked;
            var table = $('#leasesTable').DataTable();


            // Use DataTables API to find all row nodes and update checkboxes
            $(table.rows().nodes()).find('.lease-checkbox').prop('checked', isChecked).trigger('change');
        });

        // Individual checkbox click to update Select All
        $(document).off('click', '.lease-checkbox').on('click', '.lease-checkbox', function () {
            var table = $('#leasesTable').DataTable();
            var total = table.rows().nodes().length;
            var checked = $(table.rows().nodes()).find('.lease-checkbox:checked').length;

            $('#selectAllLeasesCheckBox').prop('checked', total > 0 && total === checked);
        });

        // Apply Bulk Action
        $('#applyBulkActionBtn').on('click', function () {
            var action = $('#bulkActionSelect').val();
            var selectedIds = [];

            $('.lease-checkbox:checked').each(function () {
                selectedIds.push($(this).val());
            });

            if (!action) {
                swal('Warning', 'Please select an action.', 'warning');
                return;
            }

            if (selectedIds.length === 0) {
                swal('Warning', 'Please select at least one lease.', 'warning');
                return;
            }

            if (action === 'invoice') {
                if (typeof openBatchInvoiceModal === 'function') {
                    // For multi-select, we force "Other Charges" type
                    if (selectedIds.length > 1) {
                        openBatchInvoiceModal(selectedIds, 'other_charge');
                    } else {
                        openBatchInvoiceModal(selectedIds, 'rent');
                    }
                } else {
                    swal('Error', 'Invoice module not loaded correctly.', 'error');
                }
                return;
            }

            if (action === 'auto_rent_invoice') {
                startAutoRentInvoicing(selectedIds);
                return;
            }

            var actionText = action === 'delete' ? 'delete' : 'terminate';
            var confirmText = "You won't be able to revert this!";

            swal({
                title: 'Are you sure?',
                text: "You are about to " + actionText + " " + selectedIds.length + " lease(s). " + confirmText,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    performBulkLeaseAction(action, selectedIds);
                }
            });
        });

        // Add invoice bulk action handler explicitly if needed, 
        // but it's handled above if we add it to the 'actionText' logic.
        // Actually, let's inject it into the click handler logic.


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
    if (propertySelect.find('option').length) {
        loadLeaseProperties4Lease();
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

function loadLeaseProperties4Lease() {
    $.ajax({
        url: base_url + '/app/property_controller.php?action=get_all_properties',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            console.log(data);
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
            {
                "data": "id",
                "orderable": false,
                "render": function (data, type, row) {
                    return '<input type="checkbox" class="lease-checkbox" value="' + data + '">';
                }
            },
            { "data": "reference_number" },
            { "data": "tenant_name" },
            { "data": "property_unit" },
            { "data": "monthly_rent" },
            { "data": "start_date" },
            { "data": "end_date" },
            { "data": "status" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[5, "desc"]], // Order by start_date desc (index shifted by 1)
        "drawCallback": function () {
            // Re-bind select all check
            $('#selectAllLeasesCheckBox').prop('checked', false);
        }
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

/**
 * Initialize Bulk Actions
 */
function initBulkActions() {

}

/**
 * Perform Bulk Action
 */
function performBulkLeaseAction(action, ids) {
    var $btn = $('#applyBulkActionBtn');
    $btn.prop('disabled', true).text('Processing...');

    console.log(ids);

    $.ajax({
        url: base_url + '/app/lease_controller.php?action=bulk_action',
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
                $('#leasesTable').DataTable().ajax.reload();
                $('#selectAllLeasesCheckBox').prop('checked', false);
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
 * Start Auto Rent Invoicing with Progress UI
 */
function startAutoRentInvoicing(ids) {
    const $modal = $('#autoRentProgressModal');
    const $progressBar = $('#progressBar');
    const $progressText = $('#progressText');
    const $progressList = $('#progressList');
    const $footer = $('#progressFooter');

    // Reset UI
    $progressList.empty();
    $progressBar.css('width', '0%').text('0%');
    $progressText.text('Generating invoices for ' + ids.length + ' leases...');
    $footer.addClass('d-none');
    $modal.modal('show');

    // Make AJAX call for batch generation
    $.ajax({
        url: base_url + '/app/invoice_controller.php?action=generate_rent_invoices_bulk',
        type: 'POST',
        data: {
            lease_ids: ids,
            billing_month: new Date().getMonth() + 1,
            billing_year: new Date().getFullYear()
        },
        dataType: 'json',
        success: function (response) {
            if (response.error) {
                swal('Error', response.msg, 'error');
                $modal.modal('hide');
                return;
            }

            // Process results
            let current = 0;
            const total = response.results.length;

            response.results.forEach((res, index) => {
                setTimeout(() => {
                    let icon = '';
                    let badgeClass = '';
                    if (res.status === 'success') {
                        icon = '<i class="bi bi-check-circle-fill text-success"></i>';
                        badgeClass = 'bg-success';
                    } else if (res.status === 'skipped') {
                        icon = '<i class="bi bi-exclamation-circle-fill text-warning"></i>';
                        badgeClass = 'bg-warning';
                    } else {
                        icon = '<i class="bi bi-x-circle-fill text-danger"></i>';
                        badgeClass = 'bg-danger';
                    }

                    const item = `
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                ${icon} <span class="ms-2 fw-bold">${res.tenant_name || 'Lease #' + res.lease_id}</span>
                                <div class="small text-muted ms-4">${res.message}</div>
                            </div>
                            <span class="badge ${badgeClass} rounded-pill">${res.status}</span>
                        </li>
                    `;
                    $progressList.prepend(item);

                    // Update progress bar
                    current++;
                    let percent = Math.round((current / total) * 100);
                    $progressBar.css('width', percent + '%').text(percent + '%');

                    if (current === total) {
                        $progressText.html(`<strong>Generation Complete!</strong> <br> ${response.msg}`);
                        $footer.removeClass('d-none');
                        $('#leasesTable').DataTable().ajax.reload();
                        $('#selectAllLeasesCheckBox').prop('checked', false);
                        $('#bulkActionSelect').val('');
                    }
                }, index * 100); // Small delay for visual effect
            });
        },
        error: function () {
            swal('Error', 'An error occurred during generation.', 'error');
            $modal.modal('hide');
        }
    });
}
