<?php 
require_once 'app/init.php';
require_once 'views/partials/app_header.php';
if (function_exists('authenticate')) {
    authenticate();
}
?>
    <!--start header-->
    <?php //require_once 'views/partials/topnav.php'; ?>
    <!--end top header-->
    <?php require('./views/partials/app_topbar.php');?>

    <!--start sidebar-->
    <?php require_once 'views/partials/app_sidebar.php'; ?>
    <!--end sidebar-->

    <!--start main wrapper-->
    <main class="main-wrapper py-3">
        <div class="main-content">
            <?php load_files(); ?>
            <!-- Main content loader function here -->
        </div>
    </main>
    <!--end main wrapper-->

    <!-- Modals -->
    <?php require_once 'views/properties/modals/add_property.php'; ?>
    <?php //require_once 'views/properties/modals/add_tenant.php'; ?>
<?php require_once 'views/partials/app_footer.php'; ?>
