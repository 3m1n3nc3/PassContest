<?php
 
function mainContent() {
	global $PTMPL, $LANG, $SETT, $DB, $user, $settings, $profiles, $marxTime, $premium_status, $userApp;
	$cd = new contestDelivery; 
	$bars = new barMenus;
	$side_bar = new sidebarClass;
	$social = new social;
	$messaging = new messaging;

	if ($user) {  
		// Update online status 
		$social->online_state($user['id'], null, 1); 

		$message_user = isset($_GET['u']) ? $_GET['u'] : $user['username'];
		$message_user_id = isset($_GET['id']) ? $_GET['id'] : $user['id'];

 		$page_title = 'Messenger';  
 		$PTMPL['page_title'] = $page_title;

		// Show the menus
		$adsbar = $bars->ads($settings['ads_off'], 6, 1); 
		$PTMPL['shared_menu'] = $side_bar->user_navigation();
		$PTMPL['sidebar_menu'] = $side_bar->pre_manage_menu();
		$PTMPL['recommended'] = recomendations();  
 		
 		// Fetch your friends  
		$PTMPL['gallery_cards'] = $social->follow_cards(1, $user['id']);

		// Fetch the message
		if (isset($_GET['id'])) {
			$PTMPL['messages'] = $messaging->messenger_master($message_user_id, $message_user);

			// Fetch the followers
			$social->active = $_GET['id'];		
		} else {
			// Show ads if user id is not set
			$PTMPL['messages'] = '<div class="mt-3 m-2">'.$adsbar.'</div>';
		}
		$social->onlineTime = $settings['online_time'];	
		$PTMPL['follows'] = $social->subscribers($user['id'], 0);

 
		$img = getImage($user['photo'], 1); 
		$PTMPL['seo_plugin'] = seo_plugin($img, $user['twitter'], $user['facebook'], $user['intro'], $page_title);

	// Show 404 error	 
	} else {
		$theme = new themer('welcome/404'); $container = '';
	}  

	$theme = new themer('social/messenger');
	return $theme->make();
}
?>
