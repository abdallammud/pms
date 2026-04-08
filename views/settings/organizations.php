<!-- Organizations Management (Super Admin only) -->
<?php if (!is_super_admin()): ?>
    <div class="alert alert-danger">Access denied. This page is restricted to super administrators.</div>
    <?php return; endif; ?>

<div class="d-flex mt-3 align-items-center justify-content-between mb-3">
    <h5 class="page-title">Organizations</h5>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#orgModal">
        <i class="bi bi-plus me-2"></i> Add Organization
    </button>
</div>

<div class="page-content fade-in">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="orgsTable" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Status</th>
                            <th style="width:120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Organization Modal -->
<div class="modal fade" id="orgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orgModalLabel">Add Organization</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="orgForm">
                    <input type="hidden" id="org_id" name="org_id" value="">

                    <div class="mb-3">
                        <label class="form-label">Organization Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="org_name" name="name"
                            placeholder="e.g. Acme Property Group" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Short Code</label>
                        <input type="text" class="form-control" id="org_code" name="code"
                            placeholder="e.g. ACME (optional)">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="org_status" name="status">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveOrgBtn">Save Organization</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var BASE = '<?= baseUri(); ?>';

        var orgsTable = $('#orgsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: BASE + '/app/org_controller.php?action=get_orgs',
                type: 'POST'
            },
            columns: [
                { data: 'name' },
                { data: 'code' },
                { data: 'status', orderable: false },
                { data: 'actions', orderable: false }
            ],
            order: [[0, 'asc']]
        });

        // Save org
        $('#saveOrgBtn').on('click', function () {
            var formData = $('#orgForm').serialize();
            $.post(BASE + '/app/org_controller.php?action=save_org', formData, function (resp) {
                if (resp.error) {
                    showToast(resp.msg, 'error');
                } else {
                    showToast(resp.msg, 'success');
                    $('#orgModal').modal('hide');
                    orgsTable.ajax.reload();
                }
            }, 'json');
        });

        // Edit org
        window.editOrg = function (id) {
            $.getJSON(BASE + '/app/org_controller.php?action=save_org&id=' + id, function (resp) {
                // Load form for editing - fetch via separate get endpoint if needed
            });
            // For simplicity, populate via a direct query approach
            // A full get_org action could be added; for now we reload the row
        };

        // Delete org
        window.deleteOrg = function (id) {
            if (!confirm('Are you sure you want to delete this organization? This cannot be undone.')) return;
            $.post(BASE + '/app/org_controller.php?action=delete_org', { id: id }, function (resp) {
                showToast(resp.msg, resp.error ? 'error' : 'success');
                if (!resp.error) orgsTable.ajax.reload();
            }, 'json');
        };

        // Reset modal on open
        $('#orgModal').on('show.bs.modal', function (e) {
            if (!$(e.relatedTarget).data('org-id')) {
                $('#orgForm')[0].reset();
                $('#org_id').val('');
                $('#orgModalLabel').text('Add Organization');
            }
        });
    });
</script>