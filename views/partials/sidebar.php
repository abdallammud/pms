<?php 
$menu 	= $_GET['menu'] ?? '';
$action = $_GET['action'] ?? '';
$tab 	= $_GET['tab'] ?? '';

$menus = get_menu_config();

$sidebar = '';
foreach ($menus as $sideMenu) {
	$active = '';
	if(isset($sideMenu['sub'])) {
		$showSub = '';
		if($menu == $sideMenu['menu']) {
			$active = 'active'; // For menu-link
			$showSub = 'open'; // For submenu collapse
		}
		if(check_session($sideMenu['auth'])) {
			$sidebar .= '
				<div class="menu-item '.$showSub.'">
					<a href="javascript:;" class="menu-link '.($active ? 'collapsed' : '').'" data-bs-toggle="collapse" data-bs-target="#'.strtolower($sideMenu['menu']).'Menu">
						<i class="bi bi-'.$sideMenu['icon'].'"></i>
						<span class="menu-text">'.$sideMenu['name'].'</span>
						<i class="bi bi-chevron-right menu-arrow"></i>
					</a>
					<div class="submenu collapse '.($active ? 'show' : '').'" id="'.strtolower($sideMenu['menu']).'Menu">';
			foreach ($sideMenu['sub'] as $sub) {
				if(check_session($sub['auth'])) {
					$activeSub = '';
					$icon = 'caret-right';
					if(isset($sub['icon'])) {
						$icon = $sub['icon'];
					}
					if($action == $sub['route']) {
						$activeSub = 'active';
					}
					if(isset($sub['is_modal']) && $sub['is_modal']) {
						$sidebar .= '
						<a href="javascript:;" class="menu-link  '.$activeSub.'" onclick="loadModal(\''.$sideMenu['folder'].'\', \''.$sub['default'].'\', \''.$sub['data-bs-target'].'\')">
							<i class="bi bi-'.strtolower($icon).'"></i>
							<span class="menu-text">'.$sub['name'].'</span>
						</a>';
					} else {
						$sidebar .= '
						<a href="'.baseUri().'/'.strtolower($sub['route']).'" class="menu-link  '.$activeSub.'">
							<i class="bi bi-'.strtolower($icon).'"></i>
							<span class="menu-text">'.$sub['name'].'</span>
						</a>';
					}
				}
			}

			$sidebar .= '</div></div>';
		}
	} else {
		if($menu == $sideMenu['menu']) $active = 'active';
		if(check_session($sideMenu['auth'])) {
			$sidebar .= '<div class="menu-item">
				<a href="'.baseUri().'/'.$sideMenu['route'].'" class="menu-link '.$active.'">
					<i class="bi bi-'.$sideMenu['icon'].'"></i>
					<span class="menu-text">'.$sideMenu['name'].'</span>
				</a>
			</div>';
		}
	}
}
?>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <a href="#" class="sidebar-brand">
            <i class="bi bi-building"></i>
            <span class="menu-text">AAYATIIN</span>
        </a>
    </div>
    
    <nav class="sidebar-menu">
        <!-- Dashboard -->
        <?php echo $sidebar; ?>
    </nav>
</aside>