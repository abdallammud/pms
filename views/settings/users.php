<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="page-header fade-in">
        <h1 class="page-title">User Management</h1>
    </div>
    <!-- Page Content -->
    <div class="page-content fade-in">
        <div class="card">
            <div class="card-body">
                <!-- Tabs Nav -->
                <ul class="nav nav-tabs mb-3" id="userTabs" role="tablist">
                    <li class="nav-item mb-2 " role="presentation">
                        <button class="nav-link mr-2 px-5 active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users-pane" type="button" role="tab" aria-controls="users-pane" aria-selected="true">Users</button>
                    </li>
                    <li class="nav-item mb-2 ms-2" role="presentation">
                        <button class="nav-link  px-5" id="roles-tab" data-bs-toggle="tab" data-bs-target="#roles-pane" type="button" role="tab" aria-controls="roles-pane" aria-selected="false">Roles</button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content" id="userTabsContent">
                    <!-- Users Tab -->
                    <div class="tab-pane fade show active" id="users-pane" role="tabpanel" aria-labelledby="users-tab">
                        <div class="d-flex justify-content-between mb-3">
                            <h5 class="card-title">Users List</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                <i class="bi bi-plus me-2"></i> Add User
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover w-100" id="usersTable">
                                <thead>
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Roles Tab -->
                    <div class="tab-pane fade" id="roles-pane" role="tabpanel" aria-labelledby="roles-tab">
                        <div class="d-flex justify-content-between mb-3">
                            <h5 class="card-title">Roles List</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                                <i class="bi bi-plus me-2"></i> Add Role
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover w-100" id="rolesTable">
                                <thead>
                                    <tr>
                                        <th>Role Name</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main> 

<?php require_once 'views/settings/modals/add_user.php'; ?>
<?php require_once 'views/settings/modals/add_role.php'; ?>
<?php require_once 'views/settings/modals/view_role_permissions.php'; ?>
<script src="public/js/modules/users.js"></script>