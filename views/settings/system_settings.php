<style>
    /* Settings Page Styles */
    .settings-container {
        display: flex;
        min-height: calc(100vh - 150px);
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .settings-sidebar {
        width: 250px;
        border-right: 1px solid #e5e7eb;
        padding: 1.5rem 0;
        background: #f9fafb;
    }

    .settings-sidebar-title {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #6b7280;
        padding: 0 1.5rem;
        margin-bottom: 0.5rem;
    }

    .settings-nav {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .settings-nav-group {
        margin-bottom: 1.5rem;
    }

    .settings-nav-group-title {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #6b7280;
        padding: 0.5rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .settings-nav-group-title i {
        font-size: 0.875rem;
    }

    .settings-nav-item {
        padding: 0;
    }

    .settings-nav-link {
        display: block;
        padding: 0.625rem 1.5rem 0.625rem 2.5rem;
        color: #374151;
        text-decoration: none;
        font-size: 0.875rem;
        transition: all 0.15s;
        border-left: 3px solid transparent;
    }

    .settings-nav-link:hover {
        background: #e5e7eb;
        color: #111827;
    }

    .settings-nav-link.active {
        background: #dbeafe;
        color: #1d4ed8;
        border-left-color: #1d4ed8;
        font-weight: 500;
    }

    .settings-content {
        flex: 1;
        padding: 2rem;
    }

    .settings-section-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #111827;
        margin-bottom: 0.5rem;
    }

    .settings-section-desc {
        color: #6b7280;
        margin-bottom: 1.5rem;
    }

    .settings-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .settings-card-title {
        font-size: 1rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 1rem;
    }

    /* Logo Upload Styles */
    .logo-upload-area {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 2rem;
        text-align: center;
        transition: all 0.2s;
        cursor: pointer;
        background-color: #009a2d26;
    }

    .logo-upload-area:hover {
        border-color: #3b82f6;
        background: #f8fafc;
    }

    .logo-preview {
        max-width: 240px;
        max-height: 240px;
        border-radius: 8px;
    }

    .logo-placeholder {
        color: #9ca3af;
    }

    .logo-placeholder i {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    /* Transaction Series Table */
    .transaction-table {
        width: 100%;
    }

    .transaction-table th {
        background: #f9fafb;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #6b7280;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .transaction-table td {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    .transaction-table input {
        border: 1px solid #d1d5db;
        border-radius: 4px;
        padding: 0.5rem;
        font-size: 0.875rem;
        width: 100%;
    }

    .transaction-preview {
        font-family: monospace;
        background: #f3f4f6;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.875rem;
    }
</style>

<div class="settings-container">
    <!-- Settings Sidebar -->
    <div class="settings-sidebar">
        <div class="settings-nav-group">
            <div class="settings-nav-group-title">
                <i class="bi bi-building"></i> Organization
            </div>
            <ul class="settings-nav">
                <li class="settings-nav-item">
                    <a href="#" class="settings-nav-link active" data-section="section-profile">Profile</a>
                </li>
                <li class="settings-nav-item">
                    <a href="#" class="settings-nav-link" data-section="section-branding">Branding</a>
                </li>
            </ul>
        </div>
        <div class="settings-nav-group">
            <div class="settings-nav-group-title">
                <i class="bi bi-cash-coin"></i> Accounting
            </div>
            <ul class="settings-nav">
                <li class="settings-nav-item">
                    <a href="#" class="settings-nav-link" data-section="section-transaction">Number Series</a>
                </li>
                <li class="settings-nav-item">
                    <a href="#" class="settings-nav-link" data-section="section-charge-types">Charge Types</a>
                </li>
            </ul>
        </div>
        <div class="settings-nav-group">
            <div class="settings-nav-group-title">
                <i class="bi bi-lightning-charge"></i> Automation
            </div>
            <ul class="settings-nav">
                <li class="settings-nav-item">
                    <a href="#" class="settings-nav-link" data-section="section-auto-invoice">Monthly Auto-Invoicing</a>
                </li>
            </ul>
        </div>
        <div class="settings-nav-group">
            <div class="settings-nav-group-title">
                <i class="bi bi-file-earmark-text"></i> Templates
            </div>
            <ul class="settings-nav">
                <li class="settings-nav-item">
                    <a href="#" class="settings-nav-link" data-section="section-lease-conditions">Lease Conditions</a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="settings-content">
        <!-- Profile Section -->
        <div id="section-profile" class="settings-section">
            <h2 class="settings-section-title">Organization Profile</h2>
            <p class="settings-section-desc">Manage your organization's basic information.</p>

            <div class="settings-card">
                <form id="profileForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="org_name" class="form-label fw-bold">Organization Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="org_name" name="org_name"
                                placeholder="Enter organization name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="org_email" class="form-label fw-bold">Email <span
                                    class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="org_email" name="org_email"
                                placeholder="contact@example.com">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="org_phone" class="form-label fw-bold">Phone</label>
                            <input type="text" class="form-control" id="org_phone" name="org_phone"
                                placeholder="+1 234 567 8900">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="org_city" class="form-label fw-bold">City</label>
                            <input type="text" class="form-control" id="org_city" name="org_city" placeholder="City">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="org_street1" class="form-label fw-bold">Street Address 1</label>
                            <input type="text" class="form-control" id="org_street1" name="org_street1"
                                placeholder="Street 1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="org_street2" class="form-label fw-bold">Street Address 2</label>
                            <input type="text" class="form-control" id="org_street2" name="org_street2"
                                placeholder="Street 2 (optional)">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary px-4" onclick="saveProfile()">
                            <i class="bi bi-save me-2"></i> Save Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Branding Section -->
        <div id="section-branding" class="settings-section d-none">
            <h2 class="settings-section-title">Branding</h2>
            <p class="settings-section-desc">Upload your organization logo for invoices and reports.</p>

            <div class="settings-card">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <div class="logo-upload-area" onclick="document.getElementById('logoFile').click()">
                            <img id="logoPreview" src="" alt="Logo Preview" class="logo-preview d-none">
                            <div id="logoPlaceholder" class="logo-placeholder">
                                <i class="bi bi-cloud-upload"></i>
                                <p class="mb-1">Click to upload logo</p>
                                <small class="text-muted">Recommended: 240×240px</small>
                            </div>
                            <input type="file" id="logoFile" accept=".jpg,.jpeg,.png,.gif,.bmp" class="d-none"
                                onchange="previewLogo(this)">
                        </div>
                    </div>
                    <div class="col-md-7 ps-md-4">
                        <h6>Logo Guidelines</h6>
                        <ul class="small text-muted mb-4">
                            <li>Supported formats: JPG, PNG, GIF, BMP</li>
                            <li>Maximum file size: 1MB</li>
                            <li>Transparent background (PNG) is recommended for best results on invoices</li>
                        </ul>
                        <button type="button" class="btn btn-primary" onclick="uploadLogo()">
                            <i class="bi bi-upload me-2"></i> Upload Logo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Number Series Section -->
        <div id="section-transaction" class="settings-section d-none">
            <h2 class="settings-section-title">Transaction Number Series</h2>
            <p class="settings-section-desc">Configure identifiers for various modules. Includes support for yearly
                resets.</p>

            <div class="settings-card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table align-middle transaction-table mb-0">
                        <thead class="table-light text-muted small">
                            <tr>
                                <th style="width: 20%">Module</th>
                                <th style="width: 15%">Prefix</th>
                                <th style="width: 15%">Start #</th>
                                <th style="width: 15%">Suffix</th>
                                <th style="width: 35%">Preview</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Rent Invoice</strong></td>
                                <td><input type="text" id="rent_invoice_prefix" class="form-control form-control-sm"
                                        placeholder="RNT-"></td>
                                <td><input type="text" id="rent_invoice_starting" class="form-control form-control-sm"
                                        placeholder="00001"></td>
                                <td><input type="text" id="rent_invoice_suffix" class="form-control form-control-sm"
                                        placeholder=""></td>
                                <td><span id="rent_invoice_preview"
                                        class="transaction-preview text-primary fw-bold small"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Other Invoice</strong></td>
                                <td><input type="text" id="other_invoice_prefix" class="form-control form-control-sm"
                                        placeholder="CHR-"></td>
                                <td><input type="text" id="other_invoice_starting" class="form-control form-control-sm"
                                        placeholder="00001"></td>
                                <td><input type="text" id="other_invoice_suffix" class="form-control form-control-sm"
                                        placeholder=""></td>
                                <td><span id="other_invoice_preview"
                                        class="transaction-preview text-primary fw-bold small"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Payment Receipt</strong></td>
                                <td><input type="text" id="payment_prefix" class="form-control form-control-sm"
                                        placeholder="RCT-"></td>
                                <td><input type="text" id="payment_starting" class="form-control form-control-sm"
                                        placeholder="00001"></td>
                                <td><input type="text" id="payment_suffix" class="form-control form-control-sm"
                                        placeholder=""></td>
                                <td><span id="payment_preview"
                                        class="transaction-preview text-primary fw-bold small"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Expense</strong></td>
                                <td><input type="text" id="expense_prefix" class="form-control form-control-sm"
                                        placeholder="EXP-"></td>
                                <td><input type="text" id="expense_starting" class="form-control form-control-sm"
                                        placeholder="00001"></td>
                                <td><input type="text" id="expense_suffix" class="form-control form-control-sm"
                                        placeholder=""></td>
                                <td><span id="expense_preview"
                                        class="transaction-preview text-primary fw-bold small"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Maintenance</strong></td>
                                <td><input type="text" id="maintenance_prefix" class="form-control form-control-sm"
                                        placeholder="MR-"></td>
                                <td><input type="text" id="maintenance_starting" class="form-control form-control-sm"
                                        placeholder="00001"></td>
                                <td><input type="text" id="maintenance_suffix" class="form-control form-control-sm"
                                        placeholder=""></td>
                                <td><span id="maintenance_preview"
                                        class="transaction-preview text-primary fw-bold small"></span></td>
                            </tr>
                            <tr>
                                <td><strong>Lease</strong></td>
                                <td><input type="text" id="lease_prefix" class="form-control form-control-sm"
                                        placeholder="LS-"></td>
                                <td><input type="text" id="lease_starting" class="form-control form-control-sm"
                                        placeholder="00001"></td>
                                <td><input type="text" id="lease_suffix" class="form-control form-control-sm"
                                        placeholder=""></td>
                                <td><span id="lease_preview"
                                        class="transaction-preview text-primary fw-bold small"></span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <button type="button" class="btn btn-primary px-4" onclick="saveTransactionSeries()">
                        <i class="bi bi-check-lg me-2"></i> Save Configuration
                    </button>
                    <p class="small text-muted mt-2 mb-0"><i class="bi bi-info-circle me-1"></i> Changes will apply to
                        all future transactions.</p>
                </div>
            </div>
        </div>

        <!-- Charge Types Section -->
        <div id="section-charge-types" class="settings-section d-none">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <div>
                    <h2 class="settings-section-title">Charge Types</h2>
                    <p class="settings-section-desc">Manage categories for "Other Charges Invoices".</p>
                </div>
                <button class="btn btn-primary" onclick="openAddChargeTypeModal()">
                    <i class="bi bi-plus-lg me-1"></i> Add New
                </button>
            </div>

            <div class="settings-card shadow-sm border-0">
                <div class="table-responsive">
                    <table style="width: 100%;" class="table table-hover align-middle mb-0" id="chargeTypesTable">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Default Amount</th>
                                <th>Status</th>
                                <th width="120">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data populated via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Auto-Invoice Section -->
        <div id="section-auto-invoice" class="settings-section d-none">
            <h2 class="settings-section-title">Monthly Auto-Invoicing</h2>
            <p class="settings-section-desc">Automate the generation of rent invoices every month.</p>

            <div class="settings-card border-left-primary">
                <form id="autoInvoiceForm">
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto_invoice_enabled"
                                name="auto_invoice_enabled">
                            <label class="form-check-label fw-bold" for="auto_invoice_enabled">Enable Automated Monthly
                                Rent Invoicing</label>
                        </div>
                        <div class="small text-muted ps-4">When enabled, the system will automatically generate rent
                            invoices for all active leases with "Auto-Invoice" enabled in their settings.</div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Generation Day of Month <span
                                    class="text-danger">*</span></label>
                            <select name="auto_invoice_day" id="auto_invoice_day" class="form-select">
                                <?php for ($i = 1; $i <= 28; $i++): ?>
                                    <option value="<?= $i; ?>">
                                        <?= $i . (in_array($i, [1, 21]) ? 'st' : (in_array($i, [2, 22]) ? 'nd' : (in_array($i, [3, 23]) ? 'rd' : 'th'))); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                            <div class="form-text small">Invoices will be generated on this day every month.
                                Recommended: 1st or 25th.</div>
                        </div>
                    </div>

                    <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
                        <i class="bi bi-exclamation-triangle-fill fs-4 me-3"></i>
                        <div class="small">
                            <strong>Note:</strong> Auto-invoicing requires a cron job to be configured on your server
                            pointing to <code>cron/auto_invoice.php</code>.
                            Contact support if you need help setting this up.
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary px-4" onclick="saveAutoInvoiceSettings()">
                        <i class="bi bi-save me-2"></i> Save Automation Settings
                    </button>
                </form>
            </div>
        </div>

        <!-- Lease Conditions Template Section -->
        <div id="section-lease-conditions" class="settings-section d-none">
            <h2 class="settings-section-title">Lease Conditions Template</h2>
            <p class="settings-section-desc">Default terms and conditions for new lease agreements.</p>

            <div class="settings-card">
                <h5 class="settings-card-title">Default Terms & Conditions</h5>
                <form id="leaseConditionsForm">
                    <div class="mb-3">
                        <textarea name="lease_conditions" id="lease_conditions" class="tinymce" rows="10"></textarea>
                    </div>
                    <div class="mt-3 text-end">
                        <button type="button" class="btn btn-primary px-4" onclick="saveLeaseConditions()">
                            <i class="bi bi-check-lg me-2"></i> Save Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit Charge Type -->
<div class="modal fade" id="chargeTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="chargeTypeModalLabel">Add Charge Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="chargeTypeForm">
                <input type="hidden" name="charge_type_id" id="charge_type_id_hidden">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="charge_type_name" class="form-control" required
                            placeholder="e.g., Late Fee, Water Bill">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" id="charge_type_description" class="form-control"
                            rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Default Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" name="default_amount" id="charge_type_amount"
                                class="form-control">
                        </div>
                        <div class="form-text small">Optional. Will be pre-filled when this charge is selected.</div>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" id="charge_type_status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4" id="saveChargeTypeBtn">Save Charge Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="public/js/modules/settings.js"></script>