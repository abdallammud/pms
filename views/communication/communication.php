<?php if (!check_session('communication_manage')) {
    echo '<div class="alert alert-danger">403 — Unauthorized</div>';
    exit;
} ?>

<main class="content">
    <div class="page-header d-flex justify-content-between align-items-center mt-3 mb-3">
        <div>
            <h5 class="page-title mb-0"><i class="bi bi-chat-text me-2 text-primary"></i>Communication</h5>
            <small class="text-muted">Send SMS messages to tenants and view sent history.</small>
        </div>
    </div>

    <div class="row g-4">

        <!-- ── Compose Panel ──────────────────────────────────────── -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-send me-2 text-primary"></i>Send SMS
                </div>
                <div class="card-body">
                    <div class="mb-3 multiselect-parent">
                        <label class="form-label fw-semibold multiselect-label">Recipient(s)</label>
                        <select id="sms_tenant_select" class="form-select selectpicker" multiple data-live-search="true"
                            data-actions-box="true" title="Select tenants…">
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phone Number(s)</label>
                        <input type="text" id="sms_phone" class="form-control" placeholder="+252…" readonly>
                        <div class="form-text">Auto-filled from selected tenants. Edit if needed.</div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold">Message</label>
                        <textarea id="sms_message" class="form-control" rows="5" maxlength="640"
                            placeholder="Type your message…"></textarea>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <small class="text-muted"><span id="sms_char_count">0</span> / 640 characters</small>
                        <small class="text-muted" id="sms_sms_count"></small>
                    </div>
                    <button class="btn btn-primary w-100" id="sendSmsBtn" onclick="submitSms()">
                        <i class="bi bi-send me-2"></i>Send Message
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Log Table ─────────────────────────────────────────── -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-bold"><i class="bi bi-clock-history me-2 text-primary"></i>Sent Messages</span>
                    <button class="btn btn-outline-secondary btn-sm" onclick="reloadSmsLog()">
                        <i class="bi bi-arrow-clockwise"></i> Refresh
                    </button>
                </div>
                <div class="card-body" style="padding: 10px;">
                    <div class="table-responsive">
                        <table id="smsLogTable" class="table table-hover align-middle mb-0" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Tenant</th>
                                    <th>Phone</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Sent By</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /row -->
</main>