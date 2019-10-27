<?php  

function mainContent() {
	global $PTMPL, $LANG, $CONF, $DB, $user, $settings;

	$userApp = new userCallback;
	$bars = new barMenus; 
	$side_bar = new sidebarClass;
	$social = new social;
	$pass = fetch_api(2);
	if ($user) {
	    // Update online status
	    $social->online_state($user['id'], null, 1);

    	// If the user is not activated  
    	if($user['status'] == 0) {  
    	    header('Location: '.permalink($CONF['url'].'/index.php?a=welcome'));
    	}
    	
		// Checkk if user is premium
		$premium_status = $userApp->premiumStatus(0, 2);
		$badge = ($premium_status) ? badge(0, $premium_status['plan'], 2) : '';

		$PTMPL['adsbar'] = $bars->ads($settings['ads_off'], 2);
		$PTMPL['recommended'] = recomendations();

		$PTMPL['sidebar_menu'] = $side_bar->manageMenu();
		$PTMPL['shared_menu'] = $side_bar->pre_manage_menu();
		$PTMPL['user_menu'] = $side_bar->user_navigation(); 

		$PTMPL['page_title'] = ucfirst($user['username']).' '.$LANG['account']; 
		$PTMPL['get_id'] = $user['id'];  

		$PTMPL['linksetting'] = barMenu(); 

		$theme = new themer('account/up_image');
		$PTMPL['upload'] = $theme->make();

		$PTMPL['fullname'] = realName($user['username'], $user['fname'], $user['lname']).' '.$badge;
		$PTMPL['introname'] = realName($user['username'], $user['fname'], $user['lname']);
		$PTMPL['introShort'] = completeIntro($user['city'], $user['state'], $user['country'], $user['lovesto']);

		if ($user['photo']) {
			$PTMPL['pphoto'] = $CONF['url'].'/uploads/faces/'.$user['photo'];
		} else {
			$PTMPL['pphoto'] = $CONF['url'].'/uploads/faces/default.jpg';
		}

		if ($user['profession']) {
			$PTMPL['prof'] = $user['profession'];
		}
		if ($user['facebook']) {
			$PTMPL['facebook'] = '<a href="https://facebook.com/'.$user['facebook'].'" class="p-2 m-2 fa-lg fb-ic"> <i class="fa fa-facebook fb-ic"> </i></a>';         
		}
		if ($user['twitter']) {
			$PTMPL['twitter'] = '<a href="https://twitter.com/'.$user['twitter'].'" class="p-2 m-2 fa-lg tw-ic"> <i class="fa fa-twitter tw-ic"> </i> </a>';
		}
		if ($user['instagram']) {
			$PTMPL['instagram'] = '<a href="https://instagram.com/'.$user['instagram'].'" class="p-2 m-2 fa-lg ins-ic"> <i class="fa fa-instagram ins-ic"> </i> </a>';
		}
		if ($user['intro']) {
			$PTMPL['intro'] = '<h4 class="black-text">'.$user['intro'].'</h4>';
		} else {
			$PTMPL['intro'] = '<h4 class="black-text">'.$LANG['no_intro'].'</h4>';
		} 

		if ($user['cover']) {
			$PTMPL['cphoto'] = $CONF['url'].'/uploads/cover/'.$user['cover'];
		} else {
			$PTMPL['cphoto'] = $CONF['url'].'/uploads/cover/default.jpg';
		}
		$PTMPL['extra_data'] = extra_userData($user['username']);
		// Show all notifications
		if (isset($_GET['notifications'])) {
			$PTMPL['limit'] = $settings['per_notification'];
			$PTMPL['page_title'] = $LANG['notifications']; 
			if ($_GET['notifications'] !== '') {
				$PTMPL['notification_id'] = $_GET['notifications']; 

        		$PTMPL['all_notifications'] = '<div class="d-inline-flex justify-content-center p-2 blue-gradient text-white font-weight-bold"><a href="'.permalink($CONF['url'].'/index.php?a=account&notifications').'" class="white-text text-left">View All '.$LANG['notifications'].'</a></div>';
			} else {
				$PTMPL['notification_id'] = '';
			}
			$theme = new themer('account/notifications');
			return $theme->make();
		// Get all users votes
		} elseif (isset($_GET['votes'])) {
			$PTMPL['adsbar'] = $bars->ads($settings['ads_off'], 1);
			$PTMPL['page_title'] = $LANG['my_votes']; 
			$PTMPL['limit'] = $settings['per_table']; 
			$theme = new themer('account/myvotes');
			return $theme->make();			
		}
	} else {
		header("Location: ".$CONF['site_url']."/index.php?a=welcome"); 
	} 

	$theme = new themer('account/content');
	return $theme->make();
}
?>