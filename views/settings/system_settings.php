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
                <i class="bi bi-gear"></i> Customization
            </div>
            <ul class="settings-nav">
                <li class="settings-nav-item">
                    <a href="#" class="settings-nav-link" data-section="section-transaction">Transaction Number Series</a>
                </li>
                <li class="settings-nav-item">
                    <a href="#" class="settings-nav-link" data-section="section-lease-conditions">Lease Conditions Template</a>
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
                            <label for="org_name" class="form-label">Organization Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="org_name" name="org_name" placeholder="Enter organization name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="org_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="org_email" name="org_email" placeholder="contact@example.com">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="org_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="org_phone" name="org_phone" placeholder="+1 234 567 8900">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="org_city" class="form-label">City</label>
                            <input type="text" class="form-control" id="org_city" name="org_city" placeholder="City">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="org_street1" class="form-label">Street Address 1</label>
                            <input type="text" class="form-control" id="org_street1" name="org_street1" placeholder="Street 1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="org_street2" class="form-label">Street Address 2</label>
                            <input type="text" class="form-control" id="org_street2" name="org_street2" placeholder="Street 2 (optional)">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" onclick="saveProfile()">
                            <i class="bi bi-check-lg me-1"></i> Save Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Branding Section -->
        <div id="section-branding" class="settings-section d-none">
            <h2 class="settings-section-title">Branding</h2>
            <p class="settings-section-desc">Upload your organization logo. This will be displayed on invoices and reports.</p>

            <div class="settings-card">
                <h5 class="settings-card-title">Organization Logo</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="logo-upload-area" onclick="document.getElementById('logoFile').click()">
                            <img id="logoPreview" src="" alt="Logo Preview" class="logo-preview d-none">
                            <div id="logoPlaceholder" class="logo-placeholder">
                                <i class="bi bi-cloud-upload"></i>
                                <p class="mb-1">Click to upload your organization logo</p>
                                <small class="text-muted">Recommended: 240×240 pixels @ 72 DPI</small>
                            </div>
                            <input type="file" id="logoFile" accept=".jpg,.jpeg,.png,.gif,.bmp" class="d-none" onchange="previewLogo(this)">
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Supported: JPG, PNG, GIF, BMP. Max size: 1MB
                            </small>
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-primary" onclick="uploadLogo()">
                        <i class="bi bi-upload me-1"></i> Upload Logo
                    </button>
                </div>
            </div>
        </div>

        <!-- Transaction Number Series Section -->
        <div id="section-transaction" class="settings-section d-none">
            <h2 class="settings-section-title">Transaction Number Series</h2>
            <p class="settings-section-desc">Configure prefix, suffix, and starting numbers for different transaction types.</p>

            <div class="settings-card">
                <div class="table-responsive">
                    <table class="transaction-table">
                        <thead>
                            <tr>
                                <th style="width: 20%">Module</th>
                                <th style="width: 18%">Prefix</th>
                                <th style="width: 18%">Starting Number</th>
                                <th style="width: 18%">Suffix</th>
                                <th style="width: 26%">Preview</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Invoice</strong></td>
                                <td><input type="text" id="invoice_prefix" placeholder="INV-"></td>
                                <td><input type="text" id="invoice_starting" placeholder="00001"></td>
                                <td><input type="text" id="invoice_suffix" placeholder="Optional"></td>
                                <td><span id="invoice_preview" class="transaction-preview">INV-00001</span></td>
                            </tr>
                            <tr>
                                <td><strong>Payment Receipt</strong></td>
                                <td><input type="text" id="payment_prefix" placeholder="RCT-"></td>
                                <td><input type="text" id="payment_starting" placeholder="00001"></td>
                                <td><input type="text" id="payment_suffix" placeholder="Optional"></td>
                                <td><span id="payment_preview" class="transaction-preview">RCT-00001</span></td>
                            </tr>
                            <tr>
                                <td><strong>Expense</strong></td>
                                <td><input type="text" id="expense_prefix" placeholder="EXP-"></td>
                                <td><input type="text" id="expense_starting" placeholder="00001"></td>
                                <td><input type="text" id="expense_suffix" placeholder="Optional"></td>
                                <td><span id="expense_preview" class="transaction-preview">EXP-00001</span></td>
                            </tr>
                            <tr>
                                <td><strong>Maintenance Request</strong></td>
                                <td><input type="text" id="maintenance_prefix" placeholder="MR-"></td>
                                <td><input type="text" id="maintenance_starting" placeholder="00001"></td>
                                <td><input type="text" id="maintenance_suffix" placeholder="Optional"></td>
                                <td><span id="maintenance_preview" class="transaction-preview">MR-00001</span></td>
                            </tr>
                            <tr>
                                <td><strong>Lease</strong></td>
                                <td><input type="text" id="lease_prefix" placeholder="LS-"></td>
                                <td><input type="text" id="lease_starting" placeholder="00001"></td>
                                <td><input type="text" id="lease_suffix" placeholder="Optional"></td>
                                <td><span id="lease_preview" class="transaction-preview">LS-00001</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button type="button" class="btn btn-primary" onclick="saveTransactionSeries()">
                        <i class="bi bi-check-lg me-1"></i> Save Transaction Series
                    </button>
                </div>
            </div>
        </div>

        <!-- Lease Conditions Template Section -->
        <div id="section-lease-conditions" class="settings-section d-none">
            <h2 class="settings-section-title">Lease Conditions Template</h2>
            <p class="settings-section-desc">Define the default terms and conditions that will appear on lease agreements.</p>

            <div class="settings-card">
                <h5 class="settings-card-title">Lease Terms & Conditions</h5>
                <form id="leaseConditionsForm">
                    <div class="mb-3">
                        <textarea name="lease_conditions" id="lease_conditions" class="tinymce" rows="10"></textarea>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary" onclick="saveLeaseConditions()">
                            <i class="bi bi-check-lg me-1"></i> Save Lease Conditions
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="public/js/modules/settings.js"></script>
