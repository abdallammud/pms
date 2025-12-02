var usersTable;

document.addEventListener('DOMContentLoaded', function () {
    load_users();
});

function load_users() {
    if ($.fn.DataTable.isDataTable('#usersTable')) {
        $('#usersTable').DataTable().destroy();
    }

    usersTable = $('#usersTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "app/user_controller.php?action=get_users",
            "type": "POST"
        },
        "columns": [
            { "data": "name" },
            { "data": "username" },
            { "data": "email" },
            { "data": "role_name" },
            { "data": "status" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[0, "asc"]]
    });
}

function saveUser() {
    var formData = $('#addUserForm').serialize();

    $.ajax({
        url: 'app/user_controller.php?action=save&endpoint=user',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function (response) {
            if (response.error) {
                swal("Error", response.msg, "error");
            } else {
                // swal("Success", response.msg, "success");
                $('#addUserModal').modal('hide');
                $('#addUserForm')[0].reset();
                $('#user_id').val('');
                usersTable.ajax.reload();
                toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 }).then(() => {

                });
            }
        },
        error: function () {
            swal("Error", "An unexpected error occurred.", "error");
        }
    });
}

function editUserModal(id) {
    $.ajax({
        url: 'app/user_controller.php?action=get_user&id=' + id,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            $('#user_id').val(data.id);
            $('#name').val(data.name);
            $('#username').val(data.username);
            $('#email').val(data.email);
            $('#role_id').val(data.role_id);
            $('#status').val(data.status);

            // Clear password field and show help text
            $('#password').val('');
            $('#passwordHelp').show();

            $('#addUserModalLabel').text('Edit User');
            $('#addUserModal').modal('show');
        },
        error: function () {
            swal("Error", "Could not fetch user data.", "error");
        }
    });
}

function deleteUser(id) {
    swal({
        title: "Are you sure?",
        text: "Once deleted, you will not be able to recover this user!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
        .then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: 'app/user_controller.php?action=delete_user',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function (response) {
                        if (response.error) {
                            swal("Error", response.msg, "error");
                        } else {
                            toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 }).then(() => {
                            });
                            usersTable.ajax.reload();
                        }
                    },
                    error: function () {
                        swal("Error", "An unexpected error occurred.", "error");
                    }
                });
            }
        });
}

document.addEventListener('DOMContentLoaded', function () {
    // Reset modal on close
    // Reset modal on close
    $(document).on('hidden.bs.modal', '#addUserModal', function () {
        $('#addUserForm')[0].reset();
        $('#user_id').val('');
        $('#addUserModalLabel').text('Add New User');
        $('#passwordHelp').hide();
    });

    // toaster.success("Good job!", 'Success', { top: '10%', right: '20px', hide: true, duration: 200000 }).then(() => {
    // });
});


// Roles
var rolesTable;

document.addEventListener('DOMContentLoaded', function () {
    // Initialize Roles Table if the element exists (it might be in a hidden tab initially, but DataTables handles that usually, or we init on tab show)
    // Actually, let's init it on load, but maybe check if tab is active? 
    // For simplicity, we can init it. But if it's in a tab, sometimes width calculation is off.
    // Let's init it when the tab is shown or just init it.
    load_roles();

    // Load permissions for the Add Role modal
    // Load permissions for the Add Role modal
    // load_permissions(); // Moved to shown.bs.modal event
});

function load_roles() {
    if ($.fn.DataTable.isDataTable('#rolesTable')) {
        $('#rolesTable').DataTable().destroy();
    }

    rolesTable = $('#rolesTable').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "app/user_controller.php?action=get_roles",
            "type": "POST"
        },
        "columns": [
            { "data": "role_name" },
            { "data": "description" },
            { "data": "actions", "orderable": false }
        ],
        "order": [[0, "asc"]]
    });
}

function load_permissions() {
    $.ajax({
        url: 'app/user_controller.php?action=get_all_permissions',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            var container = $('#permissionsContainer');
            container.empty();
            if (data.length > 0) {
                data.forEach(function (perm) {
                    var checkbox = `
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input permission-checkbox" type="checkbox" value="${perm.id}" id="perm_${perm.id}" name="permissions[]">
                                <label class="form-check-label" for="perm_${perm.id}" title="${perm.description}">
                                    ${perm.description}
                                </label>
                            </div>
                        </div>
                    `;
                    container.append(checkbox);
                });
            } else {
                container.html('<p class="text-muted">No permissions found.</p>');
            }
        }
    });
}

function saveRole() {
    var formData = $('#addRoleForm').serialize();

    $.ajax({
        url: 'app/user_controller.php?action=save&endpoint=role',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function (response) {
            if (response.error) {
                swal("Error", response.msg, "error");
            } else {
                $('#addRoleModal').modal('hide');
                $('#addRoleForm')[0].reset();
                $('#role_id').val('');
                // Uncheck all permissions
                $('.permission-checkbox').prop('checked', false);

                rolesTable.ajax.reload();
                toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
            }
        },
        error: function () {
            swal("Error", "An unexpected error occurred.", "error");
        }
    });
}

function editRole(id) {
    $.ajax({
        url: 'app/user_controller.php?action=get_role&id=' + id,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            $('#role_id').val(data.id);
            $('#role_name').val(data.role_name);
            $('#description').val(data.description);

            // Reset permissions
            $('.permission-checkbox').prop('checked', false);

            // Check assigned permissions
            if (data.permissions && data.permissions.length > 0) {
                data.permissions.forEach(function (permId) {
                    $('#perm_' + permId).prop('checked', true);
                });
            }

            $('#addRoleModalLabel').text('Edit Role');
            $('#addRoleModal').modal('show');
        },
        error: function () {
            swal("Error", "Could not fetch role data.", "error");
        }
    });
}

function deleteRole(id) {
    swal({
        title: "Are you sure?",
        text: "Once deleted, you will not be able to recover this role!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
        .then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: 'app/user_controller.php?action=delete_role',
                    type: 'POST',
                    data: { id: id },
                    dataType: 'json',
                    success: function (response) {
                        if (response.error) {
                            swal("Error", response.msg, "error");
                        } else {
                            toaster.success(response.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                            rolesTable.ajax.reload();
                        }
                    },
                    error: function () {
                        swal("Error", "An unexpected error occurred.", "error");
                    }
                });
            }
        });
}

function viewPermissions(id) {
    $.ajax({
        url: 'app/user_controller.php?action=get_role_permissions&id=' + id,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            $('#viewRoleName').text('Permissions for: ' + data.role_name);
            var list = $('#viewPermissionsList');
            list.empty();

            if (data.permissions && data.permissions.length > 0) {
                data.permissions.forEach(function (perm) {
                    list.append('<li class="list-group-item">' + perm.description + '</li>');
                });
            } else {
                list.append('<li class="list-group-item text-muted">No permissions assigned.</li>');
            }

            $('#viewPermissionsModal').modal('show');
        },
        error: function () {
            swal("Error", "Could not fetch permissions.", "error");
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    // Reset modal on close
    // Reset modal on close
    $(document).on('hidden.bs.modal', '#addRoleModal', function () {
        $('#addRoleForm')[0].reset();
        $('#role_id').val('');
        $('.permission-checkbox').prop('checked', false);
        $('#addRoleModalLabel').text('Add New Role');
    });

    // Load permissions when modal is shown if not already loaded
    $(document).on('shown.bs.modal', '#addRoleModal', function () {
        if ($('#permissionsContainer').children().length === 0) {
            load_permissions();
        }
    });
});
