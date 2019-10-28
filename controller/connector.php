<?php
function mainContent() {
	global $PTMPL, $LANG, $SETT, $DB, $user, $settings, $welcome, $referrer;
	$userApp = new userCallback;

	$PTMPL['page_title'] = $LANG['welcome'];
	
	$theme = new themer('welcome/carousel'); $carousel = '';
    if (isset($_GET['ref'])) {
    	$_SESSION['referrer'] = $_GET['ref'];
    }
	if ($user && $user['status'] == 2) {
		header("Location: ".permalink($SETT['url']."/index.php?a=explore"));
	} elseif ($user && $user['status'] == 0) {
	    $notification ='';
	    $title = sprintf($LANG['your_acc'], $LANG['inactive']);
	    $message = sprintf($LANG['acc_inactive'], permalink($SETT['url'].'/index.php?a=connector&activate=resend'));
		 
		if (isset($_GET['activate']) && $_GET['activate'] == 'resend') {
			$notification .= $userApp->account_activation('resend', $user['username']);
		}
 
		if(isset($_GET['activate']) && $_GET['activate'] !== 'resend' && isset($_GET['username'])) {
			$return = $userApp->account_activation($_GET['activate'], $_GET['username']);
			if($return == 1) {
				$title = sprintf($LANG['your_acc'], $LANG['active']);
				$message = sprintf($LANG['acc_active'], $settings['site_name']);
			} else {
				$title = $title;
				$message = $message;
				$notification .= errorMessage($LANG['invalid_act_token']);
			}
		} 

	    $PTMPL['activate_account'] ='
		 <div class="text-center border border-primary rounded z-depth-1 bg-white py-2 px-2 m-3 mt-5">
		  <h1 class="flex-fill">'.$title.'</h1>
		  <p class="font-weight-normal text-info p-1">'.$message.'</p>
		  '.$notification.'
		 </div>';				
	} 
	$PTMPL['cover'] = $welcome['cover'];
	$PTMPL['home_featured'] = $settings['landing'] == 1 ? home_featured(0, 1) : home_featured(1, 1);	

	// Show the connector card
	$PTMPL['login_connector'] = connector_card(1, $referrer);

	$PTMPL['site_url'] = $SETT['url'];  

    // Set the SEO info
	$PTMPL['page_title'] = $LANG['welcome'].' - '.$settings['site_name']; 
	$PTMPL['seo_plugin'] = seo_plugin(0, 0, 0, 'Quick Connector', $LANG['welcome']);

	// Set the active landing page_title
	$landing = $settings['landing'] == 1 ? 'content' : 'content_login';
	$theme = new themer('user/connector');
	return $theme->make();
}
?>
