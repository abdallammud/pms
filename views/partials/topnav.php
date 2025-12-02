<!-- Top Navigation -->
<nav class="top-nav">
    <div class="nav-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
    </div>

    <div class="nav-right">
        <button class="theme-switcher" id="themeSwitcher">
            <i class="bi bi-sun-fill" id="themeIcon"></i>
            <span id="themeText">Light</span>
        </button>

        <div class="nav-item notifications-dropdown">
            <a href="#" class="nav-link position-relative" id="notificationDropdown" role="button"
                data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell"></i>
                <span class="notification-badge">3</span>
            </a>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
                <div class="notifications-header">Notifications</div>
                <div class="notification-item">
                    <div class="notification-title">New Lease Agreement</div>
                    <div class="notification-message">John Smith has signed a new lease agreement for Unit
                        A-101.</div>
                    <div class="notification-time">2 hours ago</div>
                </div>
                <div class="notification-item">
                    <div class="notification-title">Maintenance Request</div>
                    <div class="notification-message">A new maintenance request has been submitted for Unit
                        B-205.</div>
                    <div class="notification-time">5 hours ago</div>
                </div>
                <div class="notification-item">
                    <div class="notification-title">Payment Received</div>
                    <div class="notification-message">Emily Johnson has paid this month's rent.</div>
                    <div class="notification-time">1 day ago</div>
                </div>
                <div class="notifications-footer">
                    <a href="#">View All Notifications</a>
                </div>
            </div>
        </div>

        <div class="nav-item quick-actions-dropdown">
            <a href="#" class="nav-link" id="quickActionsDropdown" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                <i class="bi bi-grid-3x3-gap-fill"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="quickActionsDropdown">
                <a class="dropdown-item" href="#">
                    <i class="bi bi-person-plus-fill me-2"></i> Add Tenant
                </a>
                <a class="dropdown-item" href="#">
                    <i class="bi bi-building me-2"></i> Add Property
                </a>
                <a class="dropdown-item" href="#">
                    <i class="bi bi-receipt-cutoff me-2"></i> Create Invoice
                </a>
                <a class="dropdown-item" href="#">
                    <i class="bi bi-tools me-2"></i> Log Maintenance
                </a>
            </div>
        </div>

        <div class="nav-item user-dropdown">
            <a href="#" class="nav-link" id="userDropdown" role="button" data-bs-toggle="dropdown"
                aria-expanded="false">
                <img src="https://picsum.photos/seed/user123/40/40.jpg" alt="User" class="user-avatar">
            </a>
            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <div class="dropdown-header">
                    <div class="user-info">
                        <img src="https://picsum.photos/seed/user123/50/50.jpg" alt="User">
                        <div class="user-details">
                            <h6><?php echo $_SESSION['user_name']; ?></h6>
                            <p><?php echo $_SESSION['user_email']; ?></p>
                            <p><?php echo $_SESSION['user_role']; ?></p>
                        </div>
                    </div>
                </div>
                <a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> Profile</a>
                <a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Settings</a>
                <a class="dropdown-item" href="#"><i class="bi bi-question-circle me-2"></i> Help</a>
                <hr class="dropdown-divider">
                <a class="dropdown-item" href="./logout"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
            </div>
        </div>
    </div>
</nav>