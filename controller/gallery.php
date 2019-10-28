<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $DB, $user, $settings, $profiles;
	$cd = new contestDelivery;
	$userApp = new userCallback;
	$bars = new barMenus;
	$side_bar = new sidebarClass;
	$social = new social;

	// Update online status
	if ($user) {
		$social->online_state($user['id'], null, 1);
	}
	 
	if ($profiles) { 
		$realname = realName($profiles['username'], $profiles['fname'], $profiles['lname']);
		$page_title = $realname.'\' Photo Gallery';
		$PTMPL['page_title'] = $page_title;
		$cd->contestant_id = $profiles['id'];
		$PTMPL['username'] = $profiles['username'];
		// Show the menus
		$PTMPL['adsbar'] = $bars->ads($settings['ads_off'], 2); 
		$PTMPL['shared_menu'] = $side_bar->user_navigation();
		$PTMPL['sidebar_menu'] = $side_bar->pre_manage_menu();
		$PTMPL['recommended'] = recomendations();  
		$PTMPL['timeline_info'] = $social->timeline_info($profiles['username']);
 
		$theme = new themer('social/gallery'); $container = '';
		
		// Set the users header details
		$PTMPL['profile_header'] = profile_header($profiles['id'], 2);

		// Get and manage the users photos
		// Upload form
		$msg = '';
		if (isset($_GET['msg'])) {
			$msg = urldecode($_GET['msg']);
			if ($_GET['msg'] == 'success') {
				$msg = successMessage($LANG['upload_success']);
			} else {
				$msg = $msg;
			}
		}

		$upload_form ='
          <div class="border border-light bg-white mb-2 m-3">
            <span class="bg-light p-2 d-flex font-weight-bold text-info">'.$LANG['upload_new'].'</span>

            <div class="p-3">
            '.$msg.'
              <form method="post" action="'.$SETT['url'].'/connection/upload.php?d=gallery" enctype="multipart/form-data">
                <div class="form-group">
                  <label for="Photo-desc">'.$LANG['description'].'</label>
                  <textarea name="desc" class="form-control rounded-0" id="Photo-desc" rows="3"></textarea>
                </div>
                <label for="gallery_image" class="btn btn-outline-info waves-effect">'.$LANG['choose_image'].' 
                  <i class="fa fa-photo"></i>
                </label>
                <input type="file" name="file" id="gallery_image" style="display: none;">
                <button type="submit" class="btn btn-info waves-effect">'.$LANG['upload'].' <i class="fa fa-upload"> </i> </button>
              </form>              
            </div>
          </div>';

		if ($user['id'] == $profiles['id']) {
			$PTMPL['upload_form'] = $upload_form;
		} 

		// Show the Photos
		$PTMPL['gallery_cards'] = gallery_cards(); 	

		$img = $userApp->user_gallery($profiles['id'], 1)[0]['photo'];
		$img = $SETT['url'].'/uploads/gallery/'.$img;
		$PTMPL['seo_plugin'] = seo_plugin($img, $profiles['twitter'], $profiles['facebook'], $profiles['intro'], $page_title);
	// Show 404 errow	 
	} else {
		$theme = new themer('welcome/404'); $container = ''; 
	}
	$container = $theme->make();
	  
	$PTMPL['container'] = $container;

	$theme = new themer('social/content');
	return $theme->make();	
}
?>
