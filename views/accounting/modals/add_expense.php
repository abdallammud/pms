<!-- Add / Edit Expense Modal -->
<?php
$conn = $GLOBALS['conn'];
$org_clause = tenant_where_clause();
$properties = $conn->query("SELECT id, name FROM properties WHERE $org_clause ORDER BY name");
?>

<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="addExpenseLabel">
                    <i class="bi bi-credit-card-2-back me-2"></i>Add Expense
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="saveExpenseForm">
                <input type="hidden" name="expense_id" id="expense_id">

                <div class="modal-body">
                    <div class="row g-3">

                        <!-- Expense Type -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Expense Type <span class="text-danger">*</span></label>
                            <select name="expense_type" id="expense_type" class="form-select" required>
                                <option value="Property">Property Expense</option>
                                <option value="Aayatiin/Property Manager">Property Manager / Agency</option>
                                <option value="General">General / Other</option>
                            </select>
                        </div>

                        <!-- Property (Conditional) -->
                        <div class="col-md-6 multiselect-parent" id="property_select_container">
                            <label class="form-label fw-semibold multiselect-label">Property <span class="text-danger">*</span></label>
                            <select name="property_id" id="expense_property_select" class="form-select selectpicker"
                                data-live-search="true" title="Select Property">
                                <?php while ($row = $properties->fetch_assoc()): ?>
                                    <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Category -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                <input type="text" name="category" id="expense_category" class="form-control"
                                    placeholder="e.g. Maintenance, Cleaning, Utilities" required>
                            </div>
                        </div>

                        <!-- Amount -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0.01" name="amount" id="expense_amount"
                                    class="form-control" placeholder="0.00" required>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Expense Date <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" name="expense_date" id="expense_date" class="form-control"
                                    value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description <span class="text-muted">(Optional)</span></label>
                            <textarea name="description" id="expense_description" class="form-control" rows="3"
                                placeholder="Additional details about the expense…"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" id="saveExpenseBtn">
                        <i class="bi bi-save me-1"></i>Save Expense
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    $(document).on('change', '#expense_type', function () {
        var showProp = $(this).val() !== 'General';
        $('#property_select_container').toggle(showProp);
        $('#expense_property_select').prop('required', showProp);
    });
    $(document).on('hidden.bs.modal', '#addExpenseModal', function () {
        $('#saveExpenseForm')[0].reset();
        $('#expense_id').val('');
        $('#addExpenseLabel').html('<i class="bi bi-credit-card-2-back me-2"></i>Add Expense');
        $('#saveExpenseBtn').html('<i class="bi bi-save me-1"></i>Save Expense');
        $('#property_select_container').show();
        if ($.fn.selectpicker) $('#expense_property_select').selectpicker('refresh');
    });
});
</script>
