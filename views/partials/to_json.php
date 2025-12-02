<script type="text/javascript">
	let base_url = '<?=baseUri();?>';

	// Make all these varaibles into json
	let userSession = {};
	<?php
	$user = [];
	$permissions = [];
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}
	foreach ($_SESSION as $key => $value) {
		if (str_starts_with($key, 'user__') || str_starts_with($key, 'user_')) {
			$user[$key] = $value;
		} elseif ($key === 'permissions') {
			$permissions = $value;
		}
	}
	?>
	userSession.user = <?= json_encode($user); ?>;
	userSession.permissions = <?= json_encode($permissions); ?>;
	

</script>