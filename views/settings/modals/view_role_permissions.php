<!-- View Permissions Modal -->
<div class="modal fade" id="viewPermissionsModal" tabindex="-1" aria-labelledby="viewPermissionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"> <!-- widened -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewPermissionsModalLabel">Role Permissions</h5>
                <button type="button" class="btn-close bg-danger" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 id="viewRoleName" class="mb-3 fw-bold"></h6>

                <!-- We will inject bootstrap row/cols here -->
                <div id="viewPermissionsList"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
