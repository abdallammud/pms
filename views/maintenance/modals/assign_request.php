<!-- Assign Maintenance Request Modal -->
<?php
$conn = $GLOBALS['conn'];
$org_clause = tenant_where_clause();
$pending_requests = $conn->query("
    SELECT m.id, m.reference_number, m.description, m.priority,
           p.name AS property_name, u.unit_number
    FROM maintenance_requests m
    LEFT JOIN properties p ON m.property_id = p.id
    LEFT JOIN units u      ON m.unit_id = u.id
    WHERE m.status != 'completed' AND $org_clause
    ORDER BY m.created_at DESC
");
$vendors = $conn->query("
    SELECT id, vendor_name, service_type
    FROM vendors
    WHERE $org_clause
    ORDER BY vendor_name
");
?>

<div class="modal fade" id="assignMaintenanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content shadow border-0">

            <div class="modal-header">
                <h5 class="modal-title" id="assign_modal_title">
                    <i class="bi bi-person-plus me-2"></i>Assign Maintenance Request
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="assignRequestForm">
                <input type="hidden" name="assignment_id" id="assignment_id">

                <div class="modal-body">
                    <div class="row g-4">

                        <!-- Left: Request selection -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-list-task me-1"></i>Request
                                    </h6>

                                    <div class="mb-3 multiselect-parent">
                                        <label class="form-label fw-semibold multiselect-label">Maintenance Request <span class="text-danger">*</span></label>
                                        <select name="request_id" id="assign_request_id" class="form-select selectpicker"
                                            data-live-search="true" title="Select Request" required>
                                            <?php while ($req = $pending_requests->fetch_assoc()):
                                                $label = $req['reference_number'] . ' — ' . $req['property_name'];
                                                if ($req['unit_number']) $label .= ' / ' . $req['unit_number'];
                                                $label .= ' · ' . substr($req['description'], 0, 45);
                                            ?>
                                                <option value="<?= $req['id'] ?>"
                                                        data-subtext="<?= ucfirst($req['priority']) ?> priority">
                                                    <?= htmlspecialchars($label) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <!-- Request summary card (populated via JS) -->
                                    <div id="assign_request_preview" class="d-none">
                                        <div class="card bg-light border-0">
                                            <div class="card-body p-3 small">
                                                <div class="row g-1">
                                                    <div class="col-5 text-muted">Reference</div>
                                                    <div class="col-7 fw-semibold" id="preview_ref">—</div>
                                                    <div class="col-5 text-muted">Property</div>
                                                    <div class="col-7" id="preview_prop">—</div>
                                                    <div class="col-5 text-muted">Unit</div>
                                                    <div class="col-7" id="preview_unit">—</div>
                                                    <div class="col-5 text-muted">Priority</div>
                                                    <div class="col-7" id="preview_priority">—</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Right: Assignment details -->
                        <div class="col-md-6">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3">
                                        <i class="bi bi-person-badge me-1"></i>Assignment Details
                                    </h6>

                                    <div class="mb-3 multiselect-parent">
                                        <label class="form-label fw-semibold multiselect-label">Assign To (Vendor / Staff) <span class="text-danger">*</span></label>
                                        <select name="vendor_id" id="assign_vendor_id" class="form-select selectpicker"
                                            data-live-search="true" title="Select Vendor" required>
                                            <?php while ($v = $vendors->fetch_assoc()): ?>
                                                <option value="<?= $v['id'] ?>"
                                                        data-subtext="<?= htmlspecialchars($v['service_type']) ?>">
                                                    <?= htmlspecialchars($v['vendor_name']) ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Assigned Date <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                            <input type="date" name="assigned_date" id="assigned_date"
                                                class="form-control" value="<?= date('Y-m-d') ?>" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Expected Completion</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
                                            <input type="date" name="expected_completion" id="expected_completion"
                                                class="form-control">
                                        </div>
                                    </div>

                                    <div class="mb-0">
                                        <label class="form-label fw-semibold">Notes</label>
                                        <textarea name="notes" id="assign_notes" class="form-control" rows="3"
                                            placeholder="Special instructions or notes…"></textarea>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Cancel
                    </button>
                    <button class="btn btn-primary" type="submit" id="saveAssignmentBtn">
                        <i class="bi bi-check-circle me-1"></i>Assign Request
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
/* ── Request preview on selection ─────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    $(document).on('change', '#assign_request_id', function () {
        var $opt = $(this).find(':selected');
        if (!$(this).val()) {
            $('#assign_request_preview').addClass('d-none');
            return;
        }
        var label = $opt.text().split('·');
        var refProp = (label[0] || '').trim().split('—');
        $('#preview_ref').text((refProp[0] || '').trim());
        $('#preview_prop').text((refProp[1] || '').trim());
        $('#preview_unit').text('—');
        $('#preview_priority').text($opt.data('subtext') || '—');
        $('#assign_request_preview').removeClass('d-none');
    });

    /* ── Reset on close ──────────────────────────────────── */
    $(document).on('hidden.bs.modal', '#assignMaintenanceModal', function () {
        $('#assignRequestForm')[0].reset();
        $('#assignment_id').val('');
        $('#assign_request_id').selectpicker('val', '').selectpicker('refresh');
        $('#assign_vendor_id').selectpicker('val', '').selectpicker('refresh');
        $('#assign_request_preview').addClass('d-none');
        $('#saveAssignmentBtn').html('<i class="bi bi-check-circle me-1"></i>Assign Request');
    });
});
</script>
