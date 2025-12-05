	<!--start overlay-->
	<div class="overlay btn-toggle"></div>
	<!--end overlay-->

	<!--start footer-->
	<footer class="page-footer">
		<p class="mb-0">Copyright © <?=date('Y')?>. All right reserved.</p>
	</footer>
	<!--end footer-->





	
	<!--start switcher-->

	<?php require('to_json.php'); ?>
	<!--bootstrap js-->
	<script src="<?=baseUri();?>/public/js/bootstrap.bundle.min.js"></script>

	<!--plugins-->
	<script src="<?=baseUri();?>/public/js/jquery.min.js"></script>
	<script src="<?=baseUri();?>/public/js/sumo_select.js"></script>
	<!--plugins-->
	<!-- <script src="<?=baseUri();?>/public/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script> -->
	<script src="<?=baseUri();?>/public/plugins/sweetalert/sweetalert.min.js"></script>
	<script src="<?=baseUri();?>/public/plugins/metismenu/metisMenu.min.js"></script>
	<!-- <script src="<?=baseUri();?>/public/plugins/apexchart/apexcharts.min.js"></script> -->
	<script src="<?=baseUri();?>/public/plugins/simplebar/js/simplebar.min.js"></script>
	<script src="<?=baseUri();?>/public/plugins/peity/jquery.peity.min.js"></script>
	<script src="<?=baseUri();?>/public/plugins/datatable/js/jquery.dataTables.min.js"></script>
	<script src="<?=baseUri();?>/public/plugins/datatable/js/dataTables.bootstrap5.min.js"></script>
	<script src="<?=baseUri();?>/public/plugins/moment/moment.js"></script>
	<script src="<?=baseUri();?>/public/plugins/pikaday/pikaday.js"></script>
	<script>

	// $(".data-attributes span").peity("donut")
	</script>
	<script src="<?=baseUri();?>/public/plugins/tinymce/tinymce.min.js"></script>
	<script src="<?=baseUri();?>/public/js/toaster.js"></script>
	<script src="<?=baseUri();?>/public/js/main.js"></script>
	<script src="<?=baseUri();?>/public/js/utilities.js"></script>
	<script src="<?=baseUri();?>/public/js/script.js"></script>
	
	<!-- <script src="<?=baseUri();?>/public/js/dashboard1.js"></script> -->
	<script>
	// new PerfectScrollbar(".user-list")
	</script>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js"></script>
	<?php //load_js_module(); ?>

</body>

</html>