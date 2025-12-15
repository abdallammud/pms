/**
 * Settings Module JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    // Load settings on page load
    loadSettings();
    loadTransactionSeries();
    loadLeaseConditions();

    // Setup navigation
    setupSettingsNav();

    // Setup live preview for transaction series
    setupTransactionPreview();
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
        }
    });
}

/**
 * Preview logo before upload
 */
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
        }
    });
}

/**
 * Setup live preview for transaction inputs
 */
function setupTransactionPreview() {
    const modules = ['invoice', 'payment', 'expense', 'maintenance', 'lease'];

    modules.forEach(module => {
        $(`#${module}_prefix, #${module}_suffix, #${module}_starting`).on('input', function () {
            updatePreview(module);
        });
    });
}

/**
 * Update preview for a specific module
 */
function updatePreview(module) {
    const prefix = $(`#${module}_prefix`).val() || '';
    const suffix = $(`#${module}_suffix`).val() || '';
    const starting = $(`#${module}_starting`).val() || '00001';

    const preview = prefix + starting + suffix;
    $(`#${module}_preview`).text(preview);
}

/**
 * Save transaction number series
 */
function saveTransactionSeries() {
    const modules = ['invoice', 'payment', 'expense', 'maintenance', 'lease'];
    const series = {};

    modules.forEach(module => {
        series[module] = {
            prefix: $(`#${module}_prefix`).val() || '',
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
        }
    });
}

/**
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
}
