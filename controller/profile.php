<?php

function mainContent() {
	global $PTMPL, $LANG, $CONF, $DB, $user, $settings, $profiles;
	$cd = new contestDelivery;
	$userApp = new userCallback;
	$bars = new barMenus;
	$social = new social;
	$pass = fetch_api(2);
	// Update online status
	if ($user) {
		$social->online_state($user['id'], null, 1);
	}
	
	if ($profiles['username']) { 
		$online = $social->online_state($profiles['id']);

		$PTMPL['page_title'] = realName($profiles['username'], $profiles['fname'], $profiles['lname']);
		$cd->contestant_id = $profiles['id'];
		$PTMPL['mastercraft'] = masterCraft();
		$PTMPL['username'] = $profiles['username'];
		$PTMPL['adsbar'] = $bars->ads($settings['ads_off'], 2); // Show the adverts

		$premium_status = $userApp->premiumStatus($profiles['id'], 2);
		($premium_status) ? $badge = badge(0, $premium_status['plan'], 2) : $badge = '';

		$collect = $userApp->collectUserName(null, 0, $profiles['id']);

		$PTMPL['fullname'] = $collect['fullname'];
		$PTMPL['introname'] = $collect['name'];
		$PTMPL['role'] = ($profiles['role'] == 'agency') ? '<p><small>('.$LANG['p_agency'].')</small></p>' : '';

		$PTMPL['introShort'] = completeIntro($profiles['city'], $profiles['state'], $profiles['country'], $profiles['lovesto']);
		$quickinfo = $cd->viewApplications(0, 0, $profiles['id']);
 		
 		// Get users followers
 		$followers = $social->follow($profiles['id'], 2);
		$following = $social->follow($profiles['id'], 3);
		$follow_link = $social->follow_link($profiles['id'], 0) ? '<span class="mx-3">'.$social->follow_link($profiles['id'], 0).'</span>' : '';

		// Link to the follows page
		$followers_link = permalink($CONF['url'].'/index.php?a=followers&followers='.$profiles['id']);
		$following_link = permalink($CONF['url'].'/index.php?a=followers&following='.$profiles['id']);
		// Show the claim button on profiles created by an agency that are not yet claimed
		//$PTMPL['lay_claim'] = ($profiles['claimed'] == 0) ? '<a href="'.permalink($CONF['url'].'/index.php?a=profile&u='.$profiles['username']).'&claim='.$profiles['id'].'" class="btn btn-info border border-white">Claim this profile<i class="fa fa-user-add text-white"></i></a>' : '';

		$PTMPL['pphoto'] = getImage($profiles['photo'], 1);
		$PTMPL['cphoto'] = getImage($profiles['cover'], 2); 

		if ($profiles['profession']) {
			$PTMPL['prof'] = $profiles['profession'];
		}
		if ($profiles['facebook']) {
			$PTMPL['facebook'] = '<a href="https://facebook.com/'.$profiles['facebook'].'" class="p-2 m-2 fa-lg fb-ic"> <i class="fa fa-facebook fb-ic"> </i></a>';         
		}
		if ($profiles['twitter']) {
			$PTMPL['twitter'] = '<a href="https://twitter.com/'.$profiles['twitter'].'" class="p-2 m-2 fa-lg tw-ic"> <i class="fa fa-twitter tw-ic"> </i> </a>';
		} 
		if ($profiles['instagram']) {
			$PTMPL['instagram'] = '<a href="https://instagram.com/'.$profiles['instagram'].'" class="p-2 m-2 fa-lg ins-ic"> <i class="fa fa-instagram ins-ic"> </i> </a>';
		}

		// Show the full bio link to agencies
		$fullbio = '';
		if ($quickinfo) {
			$fullbio = ($user['role'] == 'agency' || $profiles['id'] == $user['id']) ? '<a href="'.permalink($CONF['url'].'/index.php?a=enter&viewdata='.$profiles['id']).'" data-toggle="tooltip" data-placement="right" title="View user data" class="btn btn-info border border-white">View Full Bio</a>' : '';
		}

		// Show the Gallery link
		$gallery = '<a href="'.permalink($CONF['url'].'/index.php?a=gallery&u='.$profiles['username']).'" data-toggle="tooltip" data-placement="right" title="'.$LANG['goto'].' '.$LANG['gallery'].'" class="btn btn-info border border-white">'.$LANG['gallery'].'</a>';

		// Timeline button
		$timeline = '<a href="'.permalink($CONF['url'].'/index.php?a=timeline&u='.$profiles['username']).'" data-toggle="tooltip" data-placement="right" title="'.$LANG['timeline'].'" class="btn btn-info border border-white">'.$LANG['timeline'].'</a>';

		$related = '
            <div class="d-flex justify-content-center p-3 m-4 text-info font-weight-bold rounded border border-info bg-white">
              <a class="text-info" href="'.$followers_link.'">
              	<span class="px-3" id="followers_count_'.$profiles['id'].'">'.count($followers).' '.$LANG['followers'].'</span>
              </a>
              <a class="text-info" href="'.$following_link.'">
              	<span class="px-3">'.count($following).' '.$LANG['following'].'</span>
              </a> 
              '.$follow_link.'
              <a class="text-info" href="'.$collect['message'].'">
              	<span class="px-3">'.$LANG['message'].' '.$online['icon'].'</span>
              </a>
            </div>'; 

		// Arrange all the buttons in an array
		$all_buttons = array($gallery, $timeline, $fullbio, $related);
		$list_btn = '';
		foreach ($all_buttons as $key => $value) {
			$list_btn .= $value;
		}
		$PTMPL['user_buttons'] = $list_btn;

		if ($profiles['intro']) {
			$PTMPL['intro'] = '<h4 class="black-text">'.$profiles['intro'].'</h4>';
		} else {
			$PTMPL['intro'] = '<h4 class="black-text">'.$LANG['new_user_intro'].'</h4>';
		}   
		
		$PTMPL['loves'] = $profiles['lovesto'];

		// Show the contact info if user is and agency

		$PTMPL['extra_data'] = ($profiles['role'] == 'agency') ? extra_userData($profiles['username']) : '';

		// Is the user viewing or claiming this profile	
		if (isset($_GET['claim'])) {

		} else {
			$theme = new themer('profile/content');		
		}

		$PTMPL['seo_plugin'] = seo_plugin($PTMPL['pphoto'], $profiles['twitter'], $profiles['facebook'], $profiles['intro'], $PTMPL['page_title']);
	// Show 404 errow	 
	} else {
		$theme = new themer('welcome/404');
		
	}
	return $theme->make();
}
?>
