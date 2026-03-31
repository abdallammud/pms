<!-- Create / Edit Maintenance Request Modal -->
<?php
$conn = $GLOBALS['conn'];
$org_clause = tenant_where_clause();
$properties = $conn->query("SELECT id, name FROM properties WHERE $org_clause ORDER BY name");
?>

<div class="modal fade" id="addRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content shadow border-0">

            <div class="modal-header">
                <h5 class="modal-title" id="modal_title">
                    <i class="bi bi-tools me-2"></i>New Maintenance Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="saveRequestForm">
                <input type="hidden" name="request_id" id="request_id">

                <div class="modal-body">
                    <div class="row g-4">

                        <!-- Left column: Location -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-geo-alt me-1"></i>Location
                                    </h6>

                                    <div class="mb-3 multiselect-parent">
                                        <label class="form-label fw-semibold multiselect-label">Property <span class="text-danger">*</span></label>
                                        <select name="property_id" id="propertySelect" class="form-select selectpicker"
                                            data-live-search="true" title="Select Property" required>
                                            <?php while ($p = $properties->fetch_assoc()): ?>
                                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3 multiselect-parent">
                                        <label class="form-label fw-semibold multiselect-label">Unit <span class="text-muted">(Optional)</span></label>
                                        <select name="unit_id" id="unitSelect" class="form-select selectpicker"
                                            data-live-search="true" title="Select Unit (optional)">
                                        </select>
                                        <div class="form-text mt-1 d-flex align-items-center gap-1">
                                            <span id="unit_status_badge" title="Unit status"></span>
                                            <span id="unit_tenant_hint"></span>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Right column: Request Info -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-info-circle me-1"></i>Request Details
                                    </h6>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                                        <div class="d-flex gap-2" id="priority_buttons">
                                            <input type="hidden" name="priority" id="priority_val" value="medium">
                                            <button type="button" class="btn btn-outline-success btn-sm flex-fill priority-btn" data-val="low">
                                                <i class="bi bi-arrow-down-circle me-1"></i>Low
                                            </button>
                                            <button type="button" class="btn btn-warning btn-sm flex-fill priority-btn active" data-val="medium">
                                                <i class="bi bi-dash-circle me-1"></i>Medium
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm flex-fill priority-btn" data-val="high">
                                                <i class="bi bi-arrow-up-circle me-1"></i>High
                                            </button>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Requester Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" name="requester" id="requester" class="form-control"
                                                placeholder="Tenant / staff name" required>
                                            <button type="button" class="btn btn-outline-secondary" id="clearRequesterBtn"
                                                title="Clear auto-fill"><i class="bi bi-pencil"></i></button>
                                        </div>
                                        <div class="form-text text-info d-none" id="requester_auto_hint">
                                            <i class="bi bi-magic me-1"></i>Auto-filled from occupied unit tenant.
                                        </div>
                                    </div>

                                    <div class="mb-3 d-none" id="maintenance_status_div">
                                        <label class="form-label fw-semibold">Status</label>
                                        <select name="status" id="statusSelect" class="form-select">
                                            <option value="new">New</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Full-width: Description -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description of Issue <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" rows="4" class="form-control"
                                placeholder="Describe the maintenance issue in detail…" required></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button class="btn btn-primary" type="submit" id="saveRequestBtn">
                        <i class="bi bi-save me-1"></i>Submit Request
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    /* ── Priority button toggle ───────────────────────────── */
    $(document).on('click', '.priority-btn', function () {
        var val = $(this).data('val');
        $('#priority_val').val(val);
        $('#priority_buttons .priority-btn')
            .removeClass('btn-success btn-warning btn-danger active')
            .each(function () {
                var v = $(this).data('val');
                $(this).addClass(v === 'low' ? 'btn-outline-success' : v === 'medium' ? 'btn-outline-warning' : 'btn-outline-danger');
            });
        $(this).removeClass('btn-outline-success btn-outline-warning btn-outline-danger').addClass(
            val === 'low' ? 'btn-success active' : val === 'medium' ? 'btn-warning active' : 'btn-danger active'
        );
    });

    /* ── Property → Units cascade ─────────────────────────── */
    $(document).on('change', '#propertySelect', function () {
        var propId = $(this).val();
        var $unit = $('#unitSelect');
        $unit.html('<option value="">Loading…</option>').selectpicker('refresh');
        clearRequesterAuto();
        if (!propId) { $unit.html('<option value="">Select Unit (optional)</option>').selectpicker('refresh'); return; }

        $.getJSON(base_url + '/app/maintenance_controller.php?action=get_available_units&property_id=' + propId, function (res) {
            var opts = '<option value="">— No specific unit —</option>';
            (res || []).forEach(function (u) {
                opts += '<option value="' + u.id + '" data-status="' + u.status + '">'
                    + u.unit_number + (u.status === 'occupied' ? ' (Occupied)' : '') + '</option>';
            });
            $unit.html(opts).selectpicker('refresh');
        });
    });

    /* ── Unit change → auto-fill requester if occupied ───── */
    $(document).on('change', '#unitSelect', function () {
        var unitId   = $(this).val();
        var status   = $(this).find(':selected').data('status');
        var $badge   = $('#unit_status_badge');
        var $hint    = $('#unit_tenant_hint');

        clearRequesterAuto();

        if (!unitId) { $badge.html(''); $hint.text(''); return; }

        if (status === 'occupied') {
            $badge.html('<i class="bi bi-person-fill text-success" title="Occupied"></i>');
            // Auto-fetch tenant
            $.getJSON(base_url + '/app/maintenance_controller.php?action=get_unit_tenant&unit_id=' + unitId, function (res) {
                if (!res.error) {
                    $('#requester').val(res.tenant_name).attr('data-autofill', '1');
                    $('#requester_auto_hint').removeClass('d-none');
                    $hint.html('<i class="bi bi-person-check me-1 text-success"></i>Tenant: <strong>' + res.tenant_name + '</strong>');
                }
            });
        } else {
            $badge.html('<i class="bi bi-house text-muted" title="Vacant"></i>');
            $hint.text('');
        }
    });

    /* ── Clear auto-fill when user edits manually ─────────── */
    $(document).on('input', '#requester', function () {
        if (!$(this).attr('data-autofill')) return;
        $(this).removeAttr('data-autofill');
        $('#requester_auto_hint').addClass('d-none');
    });

    $(document).on('click', '#clearRequesterBtn', function () {
        clearRequesterAuto();
        $('#requester').val('').focus();
    });

    function clearRequesterAuto() {
        $('#requester').removeAttr('data-autofill');
        $('#requester_auto_hint').addClass('d-none');
        $('#unit_tenant_hint').text('');
        $('#unit_status_badge').html('');
    }

    /* ── Reset modal on close ──────────────────────────────── */
    $(document).on('hidden.bs.modal', '#addRequestModal', function () {
        $('#saveRequestForm')[0].reset();
        $('#request_id').val('');
        $('#maintenance_status_div').addClass('d-none');
        $('#modal_title').html('<i class="bi bi-tools me-2"></i>New Maintenance Request');
        $('#saveRequestBtn').html('<i class="bi bi-save me-1"></i>Submit Request');
        $('#unitSelect').html('<option value="">Select Unit (optional)</option>').selectpicker('refresh');
        clearRequesterAuto();
        // Reset priority to medium
        $('#priority_buttons .priority-btn[data-val="medium"]').trigger('click');
    });
});
</script>
