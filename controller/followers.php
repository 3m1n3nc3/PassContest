<?php
 
function mainContent() {
	global $PTMPL, $LANG, $SETT, $DB, $user, $settings, $profiles, $marxTime, $premium_status, $userApp;
	$cd = new contestDelivery; 
	$bars = new barMenus;
	$side_bar = new sidebarClass;
	$social = new social;

	// Update online status
	if ($user) {
		$social->online_state($user['id'], null, 1);
	}

	if ($profiles) { 
		$ph_page = isset($_GET['followers']) ? 3 : 4;

		// Set the users header details
		$PTMPL['profile_header'] = profile_header($profiles['id'], $ph_page);

		$cd->contestant_id = $profiles['id'];
		$PTMPL['username'] = $profiles['username'];

		$realname = realName($profiles['username'], $profiles['fname'], $profiles['lname']); 

 		// Are you checking followers or following --- Page title
 		if ($profiles['username'] != $user['username']) {
	 		if (isset($_GET['followers'])) {
	 			$page_title = sprintf($LANG['they_follow'], $realname); 
	 		} elseif (isset($_GET['following'])) {
	 			$page_title = sprintf($LANG['follow_them'], $realname); 
	 		} 			 
 		} else {
	 		if (isset($_GET['followers'])) {
	 			$page_title = $LANG['you_follow']; 
	 		} elseif (isset($_GET['following'])) {
	 			$page_title = $LANG['follow_you']; 
	 		}  			
 		}

 		$PTMPL['page_title'] = $page_title;

		// Show the menus
		$PTMPL['adsbar'] = $bars->ads($settings['ads_off'], 2); 
		$PTMPL['shared_menu'] = $side_bar->user_navigation();
		$PTMPL['sidebar_menu'] = $side_bar->pre_manage_menu();
		$PTMPL['recommended'] = recomendations();  
		$PTMPL['timeline_info'] = $social->timeline_info($profiles['username']);
 		 
		$theme = new themer('social/followers'); $container = '';

 		// Are you checking followers or following --- Show cards
 		if (isset($_GET['followers'])) { 
 			$PTMPL['follow_cards'] = $social->follow_cards(0, $profiles['id']);
 		} elseif (isset($_GET['following'])) { 
 			$PTMPL['follow_cards'] = $social->follow_cards(1, $profiles['id']);
 		}		

		$img = $userApp->user_gallery($profiles['id'], 1)[0]['photo'];
		$img = getImage($img, 1);
		$PTMPL['seo_plugin'] = seo_plugin($img, $profiles['twitter'], $profiles['facebook'], $profiles['intro'], $page_title);
 
	// Show 404 error	 
	} else {
		$theme = new themer('welcome/404'); $container = '';
	}
	$container = $theme->make();
	  
	$PTMPL['container'] = $container;

	$theme = new themer('social/content');
	return $theme->make();
}
?>
