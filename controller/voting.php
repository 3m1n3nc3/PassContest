<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $DB, $user, $settings, $profiles;
	$cd = new contestDelivery;
	$social = new social;

	$contest = isset($_GET['id']) ? $cd->getContest(0, $_GET['id']) : ''; 

	$cd->contest_id = (isset($_GET['id'])) ? $_GET['id'] : '';
	$voters = $cd->getVoters(1, $_GET['id']);

	// If the user is not activated  
	if($user && $user['status'] == 0) {  
	    header('Location: '.permalink($SETT['url'].'/index.php?a=welcome'));
	}
	// show the facebook snd twitter buttons
	if ($contest['require_social']) {
		if ($user) {
			// Update online status 
			$social->online_state($user['id'], null, 1); 

			$theme = new themer('voting/social_require'); 
		
			if (!$voters['social']) { 
			  	$PTMPL['facebook'] = (isset($_POST['facebook'])) ? '<span class=\'text-success\'>Like our Facebook fan page to vote!</span>
			  	<p><div class="fb-like" data-href="https://facebook.com/'.$contest['facebook'].'" data-layout="box_count" data-action="like" data-size="large" data-show-faces="true" data-share="true"></div></p>' : 
			  	'<form method="post" action="">
			  		<button type="submit" name="facebook" class="btn btn btn-fb" id="fb_follow"><i class="fa fa-2x fa-facebook"></i></button>
			   	</form>';  

			  	$PTMPL['twitter'] = (isset($_POST['twitter'])) ? "<span class='text-success'>Follow us on Twitter to vote!</span>
			  	<p><a onclick='checker' href='https://twitter.com/".$contest['twitter']."?ref_src=twsrc%5Etfw' class='twitter-follow-button' data-size='large' data-show-count='true'>Follow @".$contest['twitter']."</a><script async src='https://platform.twitter.com/widgets.js' charset='utf-8'></script></p>" : 
			  	'<form method="post" action="">
			  		<button type="submit" name="twitter" class="btn btn btn-tw" id="tw_follow"><i class="fa fa-2x fa-twitter"></i></button>
			   	</form>';
				(isset($_POST['facebook']) || isset($_POST['twitter'])) ? $cd->getVoters(2, $_GET['id']) : '';
			} 
			$PTMPL['require_social'] = (!$voters['social']) ? $theme->make() : ''; 
		}
	}  	

	$bars = new barMenus;
	$userApp = new userCallback;

	$PTMPL['adsbar'] = $bars->ads($settings['ads_off'], 2);
	$PTMPL['d_block'] = (isset($_GET['user'])) ? 'd-none d-md-block' : 'd-block';
	$PTMPL['recommended'] =  recomendations();

	$side_bar = new sidebarClass; 
	$PTMPL['sidebar_menu'] = $side_bar->manageMenu();
	$PTMPL['shared_menu'] = $side_bar->pre_manage_menu();
	$PTMPL['user_menu'] = $side_bar->user_navigation(); 

	if (!isset($_GET['id']) || $_GET['id'] == '' || $_GET['id'] !== $contest['id']) {
		$theme = new themer('welcome/404');
		return $theme->make();
	}
	// Determine if this is the contests voting page or the contestants voting page
	$cd->contestant_id = $profiles['id']; 
	if (isset($_GET['user']) && $_GET['user'] !== '') {
		// Get the contestants data
		$data = $userApp->userData($_GET['user']);
		$fullname = realName($data['username'], $data['fname'], $data['lname']);
		$fullnamefor = $fullname.' for ';
		$PTMPL['contestant_cards'] = vote_user_card();
	} else {
		$fullnamefor = '';
		$PTMPL['contestant_cards'] = votingCards();
	}

	// Set the contest title
	if ($contest['status']) {
		$PTMPL['c_title'] = strtoupper($LANG['vote_v'].' '.$fullnamefor.$contest['title']);
	}else { 
		$PTMPL['c_title'] = $LANG['contest_blocked'];
	}
	$PTMPL['page_title'] = ucfirst(strtolower($PTMPL['c_title']));
	if ($contest['allow_vote'] != 1) {
		$PTMPL['c_over'] = $LANG['is_over'];
	}

	if (isset($data)) {
		$PTMPL['pphoto'] = $SETT['url'].'/uploads/faces/'.$data['photo'];
		$intro = $data['intro'];
		$facebook = $data['facebook'];
		$twitter = $data['twitter'];
	} else {
		$PTMPL['pphoto'] = $SETT['url'].'/uploads/cover/contest/'.$contest['cover'];
		$intro = $contest['intro'];
		$facebook = $contest['facebook'];
		$twitter = $contest['twitter'];		
	}
 
	$PTMPL['intro'] = ($profiles['intro']) ? '<h4 class="black-text">'.$profiles['intro'].'</h4>' : ''; 

	$PTMPL['seo_plugin'] = seo_plugin($PTMPL['pphoto'], $twitter, $facebook, $intro, $PTMPL['c_title']); 
	
	$PTMPL['loves'] = $profiles['lovesto'];
 
	if (isset($_GET['required']) && $_GET['required'] == 'login_vote') {
		$PTMPL['apologies'] = $LANG['apologies'];
		$PTMPL['information'] = $LANG['login_vote'];
		$PTMPL['connect_btn'] = '<a href="#" class="btn btn-primary btn-md" target="_blank" id="openModal1" data-toggle="modal" data-target="#connectModal">Connect
                <i class="fa fa-cloud ml-2"></i>
              </a>';		
		$theme = new themer('welcome/information');
		return $theme->make();
	}
	$theme = new themer('voting/content');
	return $theme->make();
}
?>
