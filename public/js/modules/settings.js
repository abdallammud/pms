/**
 * Settings Module JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    // Load settings on page load
    loadSettings();
    loadTransactionSeries();
    loadLeaseConditions();
    loadChargeTypes();
    loadAutoInvoiceSettings();

    // Setup navigation
    setupSettingsNav();

    // Setup live preview for transaction series
    setupTransactionPreview();

    // Charge Type Form Submission
    $('#chargeTypeForm').on('submit', function (e) {
        e.preventDefault();
        saveChargeType();
    });
});

/**
 * Setup settings sidebar navigation
 */
function setupSettingsNav() {
    const navLinks = document.querySelectorAll('.settings-nav-link');
    const sections = document.querySelectorAll('.settings-section');

    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();

            // Remove active from all links and sections
            navLinks.forEach(l => l.classList.remove('active'));
            sections.forEach(s => s.classList.add('d-none'));

            // Add active to clicked link
            this.classList.add('active');

            // Show corresponding section
            const target = this.getAttribute('data-section');
            document.getElementById(target).classList.remove('d-none');

            // Re-initialize TinyMCE if switching to lease conditions section
            if (target === 'section-lease-conditions') {
                const textarea = document.getElementById('lease_conditions');
                if (textarea && !tinymce.get('lease_conditions')) {
                    tinymce.init({
                        selector: '#lease_conditions',
                        height: 350,
                        menubar: true,
                        plugins: 'lists link table code',
                        toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist outdent indent | link table | code'
                    });
                }
            }

            if (target === 'section-charge-types') {
                loadChargeTypes();
            }
        });
    });
}

/**
 * Load all settings
 */
function loadSettings() {
    $.ajax({
        url: 'app/settings_controller.php?action=get_settings',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (!response.error && response.data) {
                const settings = response.data;

                // Profile fields
                $('#org_name').val(settings.org_name || '');
                $('#org_email').val(settings.org_email || '');
                $('#org_phone').val(settings.org_phone || '');
                $('#org_street1').val(settings.org_street1 || '');
                $('#org_street2').val(settings.org_street2 || '');
                $('#org_city').val(settings.org_city || '');

                // Logo preview
                if (settings.logo_path) {
                    $('#logoPreview').attr('src', settings.logo_path).removeClass('d-none');
                    $('#logoPlaceholder').addClass('d-none');
                }
            }
        },
        error: function () {
            console.error('Failed to load settings');
        }
    });
}

function saveProfile() {
    const formData = $('#profileForm').serialize();
    $.post('app/settings_controller.php?action=save_profile', formData, function (res) {
        if (!res.error) {
            toaster.success(res.msg);
        } else {
            swal("Error", res.msg, "error");
        }
    }, 'json');
}

function uploadLogo() {
    const fileInput = document.getElementById('logoFile');
    if (!fileInput.files || !fileInput.files[0]) {
        swal("Error", "Please select an image file.", "error");
        return;
    }
    const formData = new FormData();
    formData.append('logo', fileInput.files[0]);
    $.ajax({
        url: 'app/settings_controller.php?action=save_branding',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function (res) {
            if (!res.error) {
                toaster.success(res.msg);
                if (res.path) {
                    $('#logoPreview').attr('src', res.path).removeClass('d-none');
                    $('#logoPlaceholder').addClass('d-none');
                }
                fileInput.value = '';
            } else {
                swal("Error", res.msg, "error");
            }
        }
    });
}

function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            $('#logoPreview').attr('src', e.target.result).removeClass('d-none');
            $('#logoPlaceholder').addClass('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Load transaction number series
 */
function loadTransactionSeries() {
    $.ajax({
        url: 'app/settings_controller.php?action=get_transaction_series',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (!response.error && response.data) {
                const series = response.data;
                const modules = ['rent_invoice', 'other_invoice', 'payment', 'expense', 'maintenance', 'lease'];

                modules.forEach(module => {
                    const data = series[module];
                    if (data) {
                        $(`#${module}_prefix`).val(data.prefix || '');
                        $(`#${module}_starting`).val(data.starting_number || '00001');
                        $(`#${module}_suffix`).val(data.suffix || '');
                        updatePreview(module);
                    }
                });
            }
        }
    });
}

function setupTransactionPreview() {
    const modules = ['rent_invoice', 'other_invoice', 'payment', 'expense', 'maintenance', 'lease'];
    modules.forEach(module => {
        $(`#${module}_prefix, #${module}_starting, #${module}_suffix`).on('input change', function () {
            updatePreview(module);
        });
    });
}

function updatePreview(module) {
    const prefix = $(`#${module}_prefix`).val() || '';
    const starting = $(`#${module}_starting`).val() || '00001';
    const suffix = $(`#${module}_suffix`).val() || '';

    const preview = prefix + starting + suffix;
    $(`#${module}_preview`).text(preview);
}

function saveTransactionSeries() {
    const modules = ['rent_invoice', 'other_invoice', 'payment', 'expense', 'maintenance', 'lease'];
    const series = {};

    modules.forEach(module => {
        series[module] = {
            prefix: $(`#${module}_prefix`).val() || '',
            starting_number: $(`#${module}_starting`).val() || '00001',
            suffix: $(`#${module}_suffix`).val() || '',
            current_number: 0 // Will be preserved by controller if already exists
        };
    });

    $.post('app/settings_controller.php?action=save_transaction_series', { series: JSON.stringify(series) }, function (res) {
        if (!res.error) {
            toaster.success(res.msg);
        } else {
            swal("Error", res.msg, "error");
        }
    }, 'json');
}

/**
 * Charge Types Logic
 */
function loadChargeTypes() {
    if ($.fn.DataTable.isDataTable('#chargeTypesTable')) {
        $('#chargeTypesTable').DataTable().ajax.reload();
        return;
    }

    $('#chargeTypesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "app/charge_type_controller.php?action=get_charge_types",
            "type": "POST"
        },
        "columns": [
            { "data": "name" },
            { "data": "description" },
            { "data": "default_amount" },
            { "data": "status" },
            { "data": "actions", "orderable": false }
        ]
    });
}

function openAddChargeTypeModal() {
    $('#chargeTypeForm')[0].reset();
    $('#charge_type_id_hidden').val('');
    $('#chargeTypeModalLabel').text('Add Charge Type');
    $('#chargeTypeModal').modal('show');
}

function editChargeType(id) {
    $.get('app/charge_type_controller.php?action=get_charge_type&id=' + id, function (res) {
        if (!res.error) {
            const data = res.data;
            $('#charge_type_id_hidden').val(data.id);
            $('#charge_type_name').val(data.name);
            $('#charge_type_description').val(data.description);
            $('#charge_type_amount').val(data.default_amount);
            $('#charge_type_status').val(data.status);

            $('#chargeTypeModalLabel').text('Edit Charge Type');
            $('#chargeTypeModal').modal('show');
        }
    }, 'json');
}

function saveChargeType() {
    const $btn = $('#saveChargeTypeBtn');
    $btn.prop('disabled', true).text('Saving...');

    const formData = $('#chargeTypeForm').serialize();
    $.post('app/charge_type_controller.php?action=save_charge_type', formData, function (res) {
        if (!res.error) {
            toaster.success(res.msg);
            $('#chargeTypeModal').modal('hide');
            $('#chargeTypesTable').DataTable().ajax.reload();
        } else {
            swal("Error", res.msg, "error");
        }
    }, 'json').always(() => {
        $btn.prop('disabled', false).text('Save Charge Type');
    });
}

function deleteChargeType(id) {
    swal({
        title: 'Are you sure?',
        text: "In-use charge types will be set to inactive instead of deleted.",
        icon: 'warning',
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.post('app/charge_type_controller.php?action=delete_charge_type', { id: id }, function (res) {
                if (!res.error) {
                    toaster.success(res.msg);
                    $('#chargeTypesTable').DataTable().ajax.reload();
                } else {
                    swal("Error", res.msg, "error");
                }
            }, 'json');
        }
    });
}

/**
 * Auto-Invoice Logic
 */
function loadAutoInvoiceSettings() {
    $.get('app/settings_controller.php?action=get_settings', function (res) {
        if (!res.error && res.data) {
            $('#auto_invoice_enabled').prop('checked', res.data.auto_invoice_enabled === 'yes');
            $('#auto_invoice_day').val(res.data.auto_invoice_day || '1');
        }
    }, 'json');
}

function saveAutoInvoiceSettings() {
    const enabled = $('#auto_invoice_enabled').is(':checked') ? 'yes' : 'no';
    const day = $('#auto_invoice_day').val();

    $.post('app/settings_controller.php?action=save_settings', {
        auto_invoice_enabled: enabled,
        auto_invoice_day: day
    }, function (res) {
        if (!res.error) {
            toaster.success(res.msg);
        } else {
            swal("Error", res.msg, "error");
        }
    }, 'json');
}

/**
 * Lease Conditions Template
 */
function loadLeaseConditions() {
    $.get('app/settings_controller.php?action=get_lease_conditions', function (response) {
        if (!response.error && response.data) {
            $('#lease_conditions').val(response.data);
            if (tinymce.get('lease_conditions')) {
                tinymce.get('lease_conditions').setContent(response.data);
            }
        }
    }, 'json');
}

function saveLeaseConditions() {
    let content = tinymce.get('lease_conditions') ? tinymce.get('lease_conditions').getContent() : $('#lease_conditions').val();
    $.post('app/settings_controller.php?action=save_lease_conditions', { lease_conditions: content }, function (res) {
        if (!res.error) {
            toaster.success(res.msg);
        } else {
            swal("Error", res.msg, "error");
        }
    }, 'json');
}
