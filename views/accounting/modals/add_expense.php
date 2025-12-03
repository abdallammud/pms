<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="app/add_expense.php" method="POST">
                <div class="modal-body">

                    <div class="row g-3">

                        <!-- Property -->
                        <div class="col-md-6">
                            <label class="form-label">Property</label>
                            <select name="property_id" class="form-select" required>
                                <option value="">Select Property</option>
                                <?php
                                $conn = $GLOBALS['conn'];
                                $sql = "SELECT id, name FROM properties ORDER BY name";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    echo '<option value="'.$row['id'].'">'.$row['name'].'</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Category -->
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control" placeholder="e.g. Maintenance, Cleaning" required>
                        </div>

                        <!-- Amount -->
                        <div class="col-md-6">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" name="amount" class="form-control" required>
                        </div>

                        <!-- Date -->
                        <div class="col-md-6">
                            <label class="form-label">Expense Date</label>
                            <input type="date" name="expense_date" class="form-control" required>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter description..."></textarea>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Expense</button>
                </div>

            </form>
        </div>
    </div>
</div>
