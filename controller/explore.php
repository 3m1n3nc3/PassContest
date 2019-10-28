<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $DB, $user, $settings;
	$social = new social;
	
	if ($user) {
		// Update online status
		$social->online_state($user['id'], null, 1);

		$PTMPL['page_title'] = $LANG['explore'];

		$bars = new barMenus;
		$PTMPL['adsbar'] = $bars->ads($settings['ads_off']);

		$side_bar = new sidebarClass;
		$PTMPL['shared_menu'] = $side_bar->user_navigation();
		$PTMPL['sidebar_menu'] = $side_bar->pre_manage_menu();
		$PTMPL['recommended'] = recomendations();

		if(isset($_GET['logout'])) {
	        $u = new userCallback;
	        $u->logOut();
	        header('Location: '.permalink($SETT['url'].'/index.php?a=welcome'));
		}
		if (isset($_POST['limit'])) {
			$PTMPL['limit'] = $_POST['limit'];
		} else {
			$PTMPL['limit'] = $settings['per_explore']; 
		} 

		$PTMPL['seo_plugin'] = seo_plugin(0, 0, 0, $PTMPL['page_title'], $PTMPL['page_title']);

		$theme = new themer('explore/content');
		return $theme->make();
	} else {
		header('Location: '.permalink($SETT['url'].'/index.php?a=featured'));
	}
}
?>
