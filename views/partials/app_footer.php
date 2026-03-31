<!--start overlay-->
<div class="overlay btn-toggle"></div>
<!--end overlay-->

<!--start footer-->
<footer class="page-footer">
	<p class="mb-0">Copyright © <?= date('Y') ?>. All right reserved.</p>
</footer>
<!--end footer-->

<!-- ════════════════════════════════════════════════════════════
     Global Send SMS Modal (#sendSmsModal)
     Open via: openSmsModal(tenantId, tenantName, tenantPhone)
     or:        openSmsModal()  ← free-form, no pre-fill
════════════════════════════════════════════════════════════ -->
<div class="modal fade" id="sendSmsModal" tabindex="-1" aria-labelledby="sendSmsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="sendSmsModalLabel">
                    <i class="bi bi-chat-text me-2 text-primary"></i>Send SMS
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Pre-filled recipient (locked) -->
                <div id="modal_tenant_display" class="alert alert-light border mb-3 d-none fw-semibold"></div>
                <!-- Free-form recipient selector -->
                <div id="modal_tenant_selector_wrap" class="mb-3">
                    <label class="form-label fw-semibold">Recipient</label>
                    <select id="modal_sms_tenant" class="form-select" style="width:100%">
                        <option></option>
                    </select>
                </div>
                <input type="hidden" id="modal_sms_tenant_id">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Phone Number</label>
                    <input type="text" id="modal_sms_phone" class="form-control" placeholder="+252…">
                </div>
                <div class="mb-1">
                    <label class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
                    <textarea id="modal_sms_message" class="form-control" rows="4" maxlength="640"
                              placeholder="Type your message…"></textarea>
                </div>
                <div class="d-flex justify-content-between">
                    <small class="text-muted"><span id="modal_sms_char_count">0</span> / 640</small>
                    <small class="text-muted" id="modal_sms_sms_count"></small>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="modalSendSmsBtn" onclick="submitModalSms()">
                    <i class="bi bi-send me-1"></i>Send
                </button>
            </div>
        </div>
    </div>
</div>
<!-- ════ /Send SMS Modal ════ -->






<!--start switcher-->
<!-- Global Base URL for JavaScript -->
<script>
</script>
<script src="<?= baseUri(); ?>/public/js/modules/properties.js"></script>
<?php require('to_json.php'); ?>
<!--bootstrap js-->
<script src="<?= baseUri(); ?>/public/js/bootstrap.bundle.min.js"></script>

<!--plugins-->
<script src="<?= baseUri(); ?>/public/js/jquery.min.js"></script>
<script src="<?= baseUri(); ?>/public/js/sumo_select.js"></script>
<!--plugins-->
<!-- <script src="<?= baseUri(); ?>/public/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script> -->
<script src="<?= baseUri(); ?>/public/plugins/sweetalert/sweetalert.min.js"></script>
<script src="<?= baseUri(); ?>/public/plugins/metismenu/metisMenu.min.js"></script>
<!-- <script src="<?= baseUri(); ?>/public/plugins/apexchart/apexcharts.min.js"></script> -->
<script src="<?= baseUri(); ?>/public/plugins/simplebar/js/simplebar.min.js"></script>
<script src="<?= baseUri(); ?>/public/plugins/peity/jquery.peity.min.js"></script>
<script src="<?= baseUri(); ?>/public/plugins/datatable/js/jquery.dataTables.min.js"></script>
<script src="<?= baseUri(); ?>/public/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>
<script src="<?= baseUri(); ?>/public/plugins/moment/moment.js"></script>
<script src="<?= baseUri(); ?>/public/plugins/pikaday/pikaday.js"></script>
<script src="<?= baseUri(); ?>/public/plugins/select2/js/select2.min.js"></script>
<script>

	// $(".data-attributes span").peity("donut")
</script>
<script src="<?= baseUri(); ?>/public/plugins/tinymce/tinymce.min.js"></script>
<script src="<?= baseUri(); ?>/public/js/modal_loader.js"></script>
<script src="<?= baseUri(); ?>/public/js/toaster.js"></script>
<script src="<?= baseUri(); ?>/public/js/main.js"></script>
<script src="<?= baseUri(); ?>/public/js/utilities.js"></script>
<script src="<?= baseUri(); ?>/public/js/script.js"></script>
<script src="<?= baseUri(); ?>/public/js/modules/tenants.js"></script>
<script src="<?= baseUri(); ?>/public/js/modules/communication.js"></script>
<script src="<?= baseUri(); ?>/public/js/modules/invoice.js"></script>
<script src="<?= baseUri(); ?>/public/js/modules/lease.js"></script>
<script src="<?= baseUri(); ?>/public/js/modules/properties.js"></script>
<script src="<?= baseUri(); ?>/public/js/modules/expenses.js"></script>
<script src="<?= baseUri(); ?>/public/js/modules/report.js"></script>

<!-- <script src="<?= baseUri(); ?>/public/js/dashboard1.js"></script> -->
<script>
	// new PerfectScrollbar(".user-list")
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
<?php //load_js_module(); ?>

<script src="<?= baseUri(); ?>/public/plugins/chartjs/js/chart.js"></script>
<script src="<?= baseUri(); ?>/public/js/dashboard.js"></script>

<?php if (function_exists('is_super_admin') && is_super_admin()): ?>
<script>
(function () {
    var BASE = '<?= baseUri(); ?>';

    // Populate the org switcher dropdown
    function loadOrgDropdown() {
        $.getJSON(BASE + '/app/org_controller.php?action=get_all_orgs_list', function (resp) {
            if (resp.error) return;
            var current = resp.current_org_id;
            var items = '<li><h6 class="dropdown-header">Switch Organization</h6></li>';
            items += '<li><a class="dropdown-item' + (current === 0 ? ' active fw-bold' : '') + '" href="javascript:;" onclick="switchOrg(0)">';
            items += '<i class="bi bi-globe me-2"></i> All Organizations</a></li>';
            items += '<li><hr class="dropdown-divider my-1"></li>';
            resp.data.forEach(function (org) {
                var isActive = (org.id == current);
                items += '<li><a class="dropdown-item' + (isActive ? ' active fw-bold' : '') + '" href="javascript:;" onclick="switchOrg(' + org.id + ')">';
                items += '<i class="bi bi-building me-2"></i>' + org.name + '</a></li>';
            });
            $('#orgSwitcherDropdown').html(items);
            $('#activeOrgLabel').text(current === 0 ? 'All Orgs' : resp.data.find(function(o){ return o.id == current; })?.name || 'Org #'+current);
        });
    }

    window.switchOrg = function (orgId) {
        $.post(BASE + '/app/org_controller.php?action=switch_org', { org_id: orgId }, function (resp) {
            if (resp.error) {
                alert(resp.msg);
                return;
            }
            // Reload current page with new org context
            window.location.reload();
        }, 'json');
    };

    // Load on page ready
    $(document).ready(function () {
        loadOrgDropdown();
        // Refresh dropdown list when it opens
        $('#orgSwitcherNav').on('show.bs.dropdown', loadOrgDropdown);
    });
})();
</script>
<?php endif; ?>

</body>

</html>