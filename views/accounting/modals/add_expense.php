<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="addExpenseLabel">Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form id="saveExpenseForm">
                <input type="hidden" name="expense_id" id="expense_id">
                <div class="modal-body">

                    <div class="row g-3">

                        <!-- Expense Type -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Expense Type <span class="text-danger">*</span></label>
                            <select name="expense_type" id="expense_type" class="form-select" required>
                                <option value="Property">Property</option>
                                <option value="Aayatiin/Property Manager">Aayatiin/Property Manager</option>
                            </select>
                        </div>

                        <!-- Property (Conditional) -->
                        <div class="col-md-6 multiselect-parent" id="property_select_container">
                            <label class="form-label fw-bold multiselect-label">Property <span
                                    class="text-danger">*</span></label>
                            <select name="property_id" id="expense_property_select" class="form-select selectpicker"
                                data-live-search="true" title="Select Property">
                                <?php
                                $conn = $GLOBALS['conn'];
                                $sql = "SELECT id, name FROM properties ORDER BY name";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['name']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Category -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                            <input type="text" name="category" id="expense_category" class="form-control"
                                placeholder="e.g. Maintenance, Cleaning" required>
                        </div>

                        <!-- Amount -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" name="amount" id="expense_amount" class="form-control"
                                    required>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Expense Date <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
                                <input type="date" name="expense_date" id="expense_date" class="form-control"
                                    value="<?= date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" id="expense_description" class="form-control" rows="3"
                                placeholder="Enter description..."></textarea>
                        </div>

                    </div>

                </div>

                <div class="modal-footer bg-light">
                    <button type="submit" class="btn btn-primary px-4" id="saveExpenseBtn">Save Expense</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>