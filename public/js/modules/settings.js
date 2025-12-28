/**
 * Settings Module JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    // Load settings on page load
    loadSettings();
    loadTransactionSeries();
    loadLeaseConditions();
<<<<<<< HEAD
    loadChargeTypes();
    loadAutoInvoiceSettings();
=======
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6

    // Setup navigation
    setupSettingsNav();

    // Setup live preview for transaction series
    setupTransactionPreview();
<<<<<<< HEAD

    // Charge Type Form Submission
    $('#chargeTypeForm').on('submit', function (e) {
        e.preventDefault();
        saveChargeType();
    });
=======
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
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
<<<<<<< HEAD

            if (target === 'section-charge-types') {
                loadChargeTypes();
            }
=======
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
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

<<<<<<< HEAD
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
=======
/**
 * Save organization profile
 */
function saveProfile() {
    const formData = $('#profileForm').serialize();

    $.ajax({
        url: 'app/settings_controller.php?action=save_profile',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function (response) {
            if (response.error) {
                swal("Error", response.msg, "error");
            } else {
                toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
            }
        },
        error: function () {
            swal("Error", "An unexpected error occurred.", "error");
        }
    });
}

/**
 * Handle logo upload
 */
function uploadLogo() {
    const fileInput = document.getElementById('logoFile');

>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
    if (!fileInput.files || !fileInput.files[0]) {
        swal("Error", "Please select an image file.", "error");
        return;
    }
<<<<<<< HEAD
    const formData = new FormData();
    formData.append('logo', fileInput.files[0]);
=======

    const formData = new FormData();
    formData.append('logo', fileInput.files[0]);

>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
    $.ajax({
        url: 'app/settings_controller.php?action=save_branding',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
<<<<<<< HEAD
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
=======
        success: function (response) {
            if (response.error) {
                swal("Error", response.msg, "error");
            } else {
                toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });

                // Update logo preview
                if (response.path) {
                    $('#logoPreview').attr('src', response.path).removeClass('d-none');
                    $('#logoPlaceholder').addClass('d-none');
                }

                // Clear file input
                fileInput.value = '';
            }
        },
        error: function () {
            swal("Error", "An unexpected error occurred.", "error");
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
        }
    });
}

<<<<<<< HEAD
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
=======
/**
 * Preview logo before upload
 */
function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();

>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
        reader.onload = function (e) {
            $('#logoPreview').attr('src', e.target.result).removeClass('d-none');
            $('#logoPlaceholder').addClass('d-none');
        };
<<<<<<< HEAD
=======

>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
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
<<<<<<< HEAD
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
=======

                Object.keys(series).forEach(module => {
                    const data = series[module];
                    $(`#${module}_prefix`).val(data.prefix || '');
                    $(`#${module}_suffix`).val(data.suffix || '');
                    $(`#${module}_starting`).val(data.starting_number || '00001');

                    // Update preview
                    updatePreview(module);
                });
            }
        },
        error: function () {
            console.error('Failed to load transaction series');
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
        }
    });
}

<<<<<<< HEAD
function setupTransactionPreview() {
    const modules = ['rent_invoice', 'other_invoice', 'payment', 'expense', 'maintenance', 'lease'];
    modules.forEach(module => {
        $(`#${module}_prefix, #${module}_starting, #${module}_suffix`).on('input change', function () {
=======
/**
 * Setup live preview for transaction inputs
 */
function setupTransactionPreview() {
    const modules = ['invoice', 'payment', 'expense', 'maintenance', 'lease'];

    modules.forEach(module => {
        $(`#${module}_prefix, #${module}_suffix, #${module}_starting`).on('input', function () {
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
            updatePreview(module);
        });
    });
}

<<<<<<< HEAD
function updatePreview(module) {
    const prefix = $(`#${module}_prefix`).val() || '';
    const starting = $(`#${module}_starting`).val() || '00001';
    const suffix = $(`#${module}_suffix`).val() || '';
=======
/**
 * Update preview for a specific module
 */
function updatePreview(module) {
    const prefix = $(`#${module}_prefix`).val() || '';
    const suffix = $(`#${module}_suffix`).val() || '';
    const starting = $(`#${module}_starting`).val() || '00001';
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6

    const preview = prefix + starting + suffix;
    $(`#${module}_preview`).text(preview);
}

<<<<<<< HEAD
function saveTransactionSeries() {
    const modules = ['rent_invoice', 'other_invoice', 'payment', 'expense', 'maintenance', 'lease'];
=======
/**
 * Save transaction number series
 */
function saveTransactionSeries() {
    const modules = ['invoice', 'payment', 'expense', 'maintenance', 'lease'];
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
    const series = {};

    modules.forEach(module => {
        series[module] = {
            prefix: $(`#${module}_prefix`).val() || '',
<<<<<<< HEAD
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
=======
            suffix: $(`#${module}_suffix`).val() || '',
            starting_number: $(`#${module}_starting`).val() || '00001',
            current_number: 0 // This will be managed by the system
        };
    });

    $.ajax({
        url: 'app/settings_controller.php?action=save_transaction_series',
        type: 'POST',
        data: { series: JSON.stringify(series) },
        dataType: 'json',
        success: function (response) {
            if (response.error) {
                swal("Error", response.msg, "error");
            } else {
                toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
            }
        },
        error: function () {
            swal("Error", "An unexpected error occurred.", "error");
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
        }
    });
}

/**
<<<<<<< HEAD
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
=======
 * Load lease conditions template
 */
function loadLeaseConditions() {
    $.ajax({
        url: 'app/settings_controller.php?action=get_lease_conditions',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (!response.error && response.data) {
                // Set the value to textarea (TinyMCE will pick it up when initialized)
                $('#lease_conditions').val(response.data);

                // If TinyMCE is already initialized, update it
                if (tinymce.get('lease_conditions')) {
                    tinymce.get('lease_conditions').setContent(response.data);
                }
            }
        },
        error: function () {
            console.error('Failed to load lease conditions');
        }
    });
}

/**
 * Save lease conditions template
 */
function saveLeaseConditions() {
    // Get content from TinyMCE
    let content = '';
    if (tinymce.get('lease_conditions')) {
        content = tinymce.get('lease_conditions').getContent();
    } else {
        content = $('#lease_conditions').val();
    }

    $.ajax({
        url: 'app/settings_controller.php?action=save_lease_conditions',
        type: 'POST',
        data: { lease_conditions: content },
        dataType: 'json',
        success: function (response) {
            if (response.error) {
                swal("Error", response.msg, "error");
            } else {
                toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
            }
        },
        error: function () {
            swal("Error", "An unexpected error occurred.", "error");
        }
    });
>>>>>>> 2d4dd43dfe288e642e8e324d993a9813a8d533d6
}
