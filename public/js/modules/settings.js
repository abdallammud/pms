async function send_settingsPost(str, data) {
    let [action, endpoint] = str.split(' ');
    try {
        const response = await $.post(`${base_url}/app/settings_controller.php?action=${action}&endpoint=${endpoint}`, data);
        return response;
    } catch (error) {
        console.error('Error occurred during the request:', error);
        return null;
    }
}

// new: send form-data (files)
async function send_settingsFormData(action, endpoint, formData) {
    try {
        const response = await $.ajax({
            url: `${base_url}/app/settings_controller.php?action=${action}&endpoint=${endpoint}`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
        });
        return response;
    } catch (error) {
        console.error('Error occurred during the request:', error);
        return null;
    }
}

async function change_settings(type, isOption = false) {
    // special case: logo
    if (type === 'system_logo' || type === 'logo') {
        $('#uploadLogoModal').modal('show');
        return false;
    }

    if (type === 'disabled_features') {
        $('#disabledFeaturesModal').modal('show');
        return false;
    }

    if (type === 'email_config') {
        $('#emailConfigModal').modal('show');
        return false;
    }

    let data = await get_setting(type);
    let modal = $('#change_setting');

    console.log(data)

    // color determines UI
    let isColor = (type.indexOf('color') !== -1) || (type === 'system_color');

    if (data) {
        let res = JSON.parse(data);
        $(modal).find('.settingType').val(type);
        $(modal).find('.settingSection').val(res.section);
        $(modal).find('.settingRemarks').val(res.remarks);
        $(modal).find('.settingDetails').val(res.details || '');
        $(modal).find('.settingValue').val(res.value);

        // if color setting, show the color input and set its value from the stored rgb
        if (isColor) {
            let rgb = res.value || 'rgb(0,0,0)';
            // convert rgb(...) -> hex for input[type=color]
            let hex = rgbToHex(rgb);
            $(modal).find('.color-row').removeClass('d-none');
            $(modal).find('.value-row').addClass('d-none');
            $(modal).find('.settingColor').val(hex);
            // make value field optional
            $(modal).find('.settingValue').removeClass('validate');
        } else {
            $(modal).find('.color-row').addClass('d-none');
            $(modal).find('.value-row').removeClass('d-none');
            if (res.remarks !== 'required') $(modal).find('.settingValue').removeClass('validate');
        }
    } else {
        // no existing setting record: prepare for new
        $(modal).find('.settingType').val(type);
        $(modal).find('.settingSection').val('');
        $(modal).find('.settingRemarks').val('');
        $(modal).find('.settingDetails').val('');
        $(modal).find('.settingValue').val('');
        if ((type.indexOf('color') !== -1) || (type === 'system_color')) {
            $(modal).find('.color-row').removeClass('d-none');
            $(modal).find('.value-row').addClass('d-none');
            $(modal).find('.settingColor').val('#000000');
        } else {
            $(modal).find('.color-row').addClass('d-none');
            $(modal).find('.value-row').removeClass('d-none');
        }
    }

    $(modal).modal('show');
}

async function get_setting(type) {
    let data = {type};
    let response = await send_settingsPost('get setting', data);
    return response;
}

// helper: convert rgb(...) to hex
function rgbToHex(rgb) {
    // rgb expected in format: "rgb(r,g,b)" or "rgba(r,g,b,a)" or hex already
    if (!rgb) return '#000000';
    rgb = rgb.trim();
    if (rgb.indexOf('#') === 0) return rgb;
    let m = rgb.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)/i);
    if (!m) return '#000000';
    let r = parseInt(m[1]), g = parseInt(m[2]), b = parseInt(m[3]);
    return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
}

// helper: convert hex to rgb string "rgb(r,g,b)"
function hexToRgbString(hex) {
    if (!hex) return 'rgb(0,0,0)';
    // normalize
    hex = hex.replace('#','');
    if (hex.length === 3) hex = hex.split('').map(c => c+c).join('');
    let bigint = parseInt(hex, 16);
    let r = (bigint >> 16) & 255;
    let g = (bigint >> 8) & 255;
    let b = bigint & 255;
    return `rgb(${r},${g},${b})`;
}

// existing submit handler (unchanged logic except color handling)
$(document).on('submit', '.changeSettingForm',  (e) => {
    let form = $(e.target);
    handleChangeSettings(form);
    return false;
});

$(document).on('submit', '#disabledFeaturesForm',  (e) => {
    let form = $(e.target);
    handleChangeDisabledFeatures(form);
    return false;
});

$(document).on('submit', '#emailConfigForm',  (e) => {
    let form = $(e.target);
    saveEmailConfig(form);
    return false;
});


async function handleChangeDisabledFeatures (form) {
    clearErrors();
    let disabled_features = [];
    // Get all unchecked checkboxes
    $(form).find('input[type=checkbox]:not(:checked)').each(function() {
        disabled_features.push($(this).val());
    });
    console.log(disabled_features)

    let data = {type: 'disabled_features', value: disabled_features};
    try {
        let response = await send_settingsPost('update setting', data);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#change_setting').modal('hide');
            if(res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:2000 }).then(() => {
                    location.reload();
                });
            }
        } else {
            console.log('Failed to save state.' + response);
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
    }
    return false;
}

async function handleChangeSettings(form) {
    clearErrors();
    let error = validateForm(form);
    // if (error) return false;

    console.log(form)

    let settingType = $(form).find('.settingType').val();
    let settingSection = $(form).find('.settingSection').val();
    let settingRemarks = $(form).find('.settingRemarks').val();
    let settingDetails = $(form).find('.settingDetails').val();
    let settingValue = $(form).find('.settingValue').val();

    // If color UI is visible, get color and convert hex -> rgb string
    let modal = $('#change_setting');
    if (!modal.find('.color-row').hasClass('d-none') && settingType.indexOf('color') !== -1) {
        let hex = modal.find('.settingColor').val();
        settingValue = hexToRgbString(hex);
    }

    let formData = {
        type: settingType,
        details: settingDetails,
        value: settingValue,
        section: settingSection,
        remarks: settingRemarks
    };

    console.log(formData)
    // return false;

    try {
        let response = await send_settingsPost('update setting', formData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response)
            $('#change_setting').modal('hide');
            if(res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:2000 }).then(() => {
                    location.reload();
                });
            }
        } else {
            console.log('Failed to save state.' + response);
        }
    } catch (err) {
        console.error('Error occurred during form submission:', err);
    }
    return false;
}

// logo upload form submit
$('#logoUploadForm').on('submit', async (e) => {
    e.preventDefault();
    clearErrors();
    let form = document.getElementById('logoUploadForm');
    let fileInput = $('#logoFile');
    if (!fileInput.val()) {
        toaster.warning('Please choose a file', 'Validation', { top: '30%', right: '20px', hide: true, duration: 3000 });
        return false;
    }
    let fd = new FormData(form);
    // include required fields
    fd.append('type', 'system_logo');

    console.log(fd)

    try {
        let response = await send_settingsFormData('update', 'setting', fd);
        if (response) {
            let res = JSON.parse(response);
            $('#uploadLogoModal').modal('hide');
            if (res.error) {
                toaster.warning(res.msg, 'Sorry', { top: '30%', right: '20px', hide: true, duration: 5000 });
            } else {
                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration:2000 }).then(() => {
                    location.reload();
                });
            }
        } else {
            console.log('Failed to save state.' + response);
        }
    } catch (err) {
        console.error('Error occurred during logo upload:', err);
    }
    return false;
});

// Email config form submit
async function saveEmailConfig(form) {
    clearErrors();
    let data = {};

    // formData.forEach(item => data[item.name] = item.value);
    let host = $(form).find('input[name=host]').val();
    let port = $(form).find('input[name=port]').val();
    let username = $(form).find('input[name=username]').val();
    let password = $(form).find('input[name=password]').val();
    let from = $(form).find('input[name=from]').val();
    let from_name = $(form).find('input[name=fromName]').val();
    let secure = $(form).find('select[name=secure]').val();
    let replyTo = $(form).find('input[name=replyTo]').val();

   
    let finalData = {
        type: 'email_config',
        details: 'Email configuration settings',
        value: {
            host:host,
            port:port,
            username:username,
            password:password,
            from:from,
            fromName:from_name,
            secure:secure,
            replyTo:replyTo
        },
        section: 'email',
        remarks: 'required'
    };

    console.log(finalData)
    // return false;

    try {
        let response = await send_settingsPost('update setting', finalData);
        console.log(response)
        if (response) {
            let res = JSON.parse(response);
            $('#emailConfigModal').modal('hide');
            if (res.error) {
                toaster.warning(res.msg, 'Error', { top: '30%', right: '20px', hide: true, duration: 4000 });
            } else {
                toaster.success(res.msg, 'Success', { top: '20%', right: '20px', hide: true, duration: 2000 }).then(() => {
                    location.reload();
                });
            }
        }
    } catch (err) {
        console.error('Error saving email config:', err);
        toaster.warning('Unexpected error occurred', 'Error', { top: '30%', right: '20px', hide: true, duration: 4000 });
    }

    return false;
}