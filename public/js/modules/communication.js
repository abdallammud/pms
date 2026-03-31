/* ============================================================
   Communication Module — SMS Compose + Log
   ============================================================ */

var BASE = (typeof base_url !== 'undefined') ? base_url : '';

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('sms_tenant_select')) {
        initCommunicationPage();
    }
    if (document.getElementById('sendSmsModal')) {
        initSendSmsModal();
    }
});

/* ─────────────────────────────────────────────────────────────
   COMMUNICATION PAGE
───────────────────────────────────────────────────────────── */
function initCommunicationPage() {
    // Load tenant options into the bootstrap-select picker
    $.getJSON(BASE + '/app/communication_controller.php?action=get_contact_list', function (resp) {
        if (resp.error || !resp.data) return;
        var $sel = $('#sms_tenant_select');
        $sel.empty();
        resp.data.forEach(function (c) {
            $sel.append('<option value="' + c.id + '" data-phone="' + (c.phone || '') + '">'
                + c.name + ' (' + c.phone + ')</option>');
        });
        $sel.selectpicker('refresh');
    });

    // On tenant selection change → update phone field with comma-separated numbers
    $('#sms_tenant_select').on('changed.bs.select', function () {
        var phones = [];
        $(this).find('option:selected').each(function () {
            var p = $(this).data('phone');
            if (p) phones.push(p);
        });
        $('#sms_phone').val(phones.join(', '));
    });

    // Character counter
    $('#sms_message').on('input', updateCharCount);

    // DataTable for sent log — guard against reinitialisation
    if (!$.fn.DataTable.isDataTable('#smsLogTable')) {
        $('#smsLogTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE + '/app/communication_controller.php?action=get_sms_log',
                type: 'POST',
            },
            columns: [
                { data: 'tenant_name' },
                { data: 'recipient_phone' },
                { data: 'message' },
                { data: 'status', orderable: false },
                { data: 'sent_by' },
                { data: 'created_at' },
            ],
            order: [[5, 'desc']],
            pageLength: 25,
        });
    }
}

/* Helper to safely reload sms log table */
function reloadSmsLog() {
    if ($.fn.DataTable.isDataTable('#smsLogTable')) {
        $('#smsLogTable').DataTable().ajax.reload(null, false);
    }
}

function updateCharCount() {
    var len = ($('#sms_message').val() || '').length;
    var smss = Math.ceil(len / 160) || 1;
    $('#sms_char_count').text(len);
    $('#sms_sms_count').text(smss > 1 ? smss + ' SMS parts' : '1 SMS');
}

function submitSms() {
    var phone = $.trim($('#sms_phone').val());
    var message = $.trim($('#sms_message').val());
    var tids = $('#sms_tenant_select').val() || [];

    if (!phone) { swal('Error', 'Please select at least one recipient.', 'error'); return; }
    if (!message) { swal('Error', 'Message body is required.', 'error'); return; }

    var $btn = $('#sendSmsBtn');
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Sending…');

    // Send one SMS per selected tenant (each phone)
    var phoneList = phone.split(',').map(function (p) { return p.trim(); }).filter(Boolean);
    var tidArr = Array.isArray(tids) ? tids : [tids];
    var completed = 0;
    var errors = [];

    phoneList.forEach(function (ph, idx) {
        $.post(BASE + '/app/communication_controller.php?action=send_sms', {
            recipient_phone: ph,
            message: message,
            tenant_id: tidArr[idx] || '',
        }, function (res) {
            if (res.error) errors.push(ph + ': ' + res.msg);
        }, 'json').always(function () {
            completed++;
            if (completed >= phoneList.length) {
                $btn.prop('disabled', false).html('<i class="bi bi-send me-2"></i>Send Message');
                if (errors.length === 0) {
                    toaster.success('SMS sent to ' + phoneList.length + ' recipient(s).');
                    $('#sms_message').val('');
                    $('#sms_tenant_select').selectpicker('deselectAll');
                    $('#sms_phone').val('');
                    updateCharCount();
                } else {
                    swal('Partial Failure', errors.join('\n'), 'warning');
                }
                reloadSmsLog();
            }
        });
    });
}

/* ─────────────────────────────────────────────────────────────
   GLOBAL SEND SMS MODAL
   openSmsModal(tenantId, tenantName, tenantPhone)
   Call with no args to get a free-form modal.
───────────────────────────────────────────────────────────── */
window.openSmsModal = function (tenantId, tenantName, tenantPhone) {
    var modal = document.getElementById('sendSmsModal');
    if (!modal) { console.warn('sendSmsModal not found in DOM'); return; }

    var prefilled = tenantId && tenantPhone;

    // Reset
    $('#modal_sms_message').val('');
    $('#modal_sms_char_count').text('0');
    $('#modal_sms_sms_count').text('');

    if (prefilled) {
        // Lock recipient fields
        $('#modal_tenant_display').text(tenantName + ' — ' + tenantPhone).removeClass('d-none').show();
        $('#modal_tenant_selector_wrap').hide();
        $('#modal_sms_phone').val(tenantPhone);
        $('#modal_sms_tenant_id').val(tenantId);
    } else {
        $('#modal_tenant_display').hide();
        $('#modal_tenant_selector_wrap').show();
        $('#modal_sms_phone').val('');
        $('#modal_sms_tenant_id').val('');
        if (!$('#modal_sms_tenant').data('select2')) {
            $('#modal_sms_tenant').select2({
                dropdownParent: $('#sendSmsModal'),
                placeholder: 'Search tenant…',
                allowClear: true,
                ajax: {
                    url: BASE + '/app/communication_controller.php?action=get_contact_list',
                    dataType: 'json',
                    delay: 250,
                    processResults: function (resp) { return { results: resp.error ? [] : resp.data }; },
                    cache: true,
                },
            });
            $('#modal_sms_tenant').on('select2:select', function (e) {
                $('#modal_sms_phone').val(e.params.data.phone || '');
                $('#modal_sms_tenant_id').val(e.params.data.id || '');
            });
        }
    }

    $(modal).modal('show');
};

function initSendSmsModal() {
    $('#modal_sms_message').on('input', function () {
        var len = $(this).val().length;
        var smss = Math.ceil(len / 160) || 1;
        $('#modal_sms_char_count').text(len);
        $('#modal_sms_sms_count').text(smss > 1 ? smss + ' SMS parts' : '');
    });
}

window.submitModalSms = function () {
    var phone = $.trim($('#modal_sms_phone').val());
    var message = $.trim($('#modal_sms_message').val());
    var tid = $('#modal_sms_tenant_id').val() || '';

    if (!phone) { swal('Error', 'Phone number is required.', 'error'); return; }
    if (!message) { swal('Error', 'Message body is required.', 'error'); return; }

    var $btn = $('#modalSendSmsBtn');
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Sending…');

    $.post(BASE + '/app/communication_controller.php?action=send_sms', {
        recipient_phone: phone,
        message: message,
        tenant_id: tid,
    }, function (res) {
        if (!res.error) {
            toaster.success(res.msg);
            $('#sendSmsModal').modal('hide');
            reloadSmsLog();
        } else {
            swal('Failed', res.msg, 'error');
        }
    }, 'json').always(function () {
        $btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i>Send');
    });
};
