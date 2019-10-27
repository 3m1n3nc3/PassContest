<?php
function mainContent() {
	global $PTMPL, $LANG, $CONF, $DB, $user, $settings, $welcome;
	$userApp = new userCallback;

	$PTMPL['page_title'] = $LANG['welcome'];	
	
	$theme = new themer('welcome/carousel'); $carousel = '';

	$PTMPL['connect_btn'] = '<a href="#" class="btn btn-primary btn-md" data-toggle="modal" data-target="#connectModal">Connect
                <i class="fa fa-cloud ml-2"></i></a>';
    if (isset($_GET['ref'])) {
    	$_SESSION['referrer'] = $_GET['ref'];
    }
	if ($user && $user['status'] == 2) {
		header("Location: ".permalink($CONF['url']."/index.php?a=explore"));
	} elseif ($user && $user['status'] == 0) {
	    $notification ='';
	    $title = sprintf($LANG['your_acc'], $LANG['inactive']);
	    $message = sprintf($LANG['acc_inactive'], permalink($CONF['url'].'/index.php?a=welcome&activate=resend'));
		 
		if (isset($_GET['activate']) && $_GET['activate'] == 'resend') {
			$notification .= $userApp->account_activation('resend', $user['username']);
		}
 
		if(isset($_GET['activate']) && $_GET['activate'] !== 'resend'  && isset($_GET['username'])) {
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

	$PTMPL['home_featured'] = $settings['landing'] == 1 ? home_featured(0, 1) : home_featured(1);	
	
	$PTMPL['site_url'] = $CONF['url'];  

	// Show the connector card
	$PTMPL['login_connector'] = connector_card(); 

	// Set the introductory text
    $PTMPL['site_intro'] = $welcome['intro'];
    $PTMPL['site_intro_desc'] = $welcome['intro_desc'];
    
    // Set the sites usage
    $PTMPL['uses_one'] = $welcome['uses_one'];
    $PTMPL['uses_one_desc'] = $welcome['uses_one_desc'];
    $PTMPL['uses_two'] = $welcome['uses_two'];
    $PTMPL['uses_two_desc'] = $welcome['uses_two_desc'];
    $PTMPL['uses_three'] = $welcome['uses_three'];
    $PTMPL['uses_three_desc'] = $welcome['uses_three_desc']; 
    $PTMPL['uses_four'] = $welcome['uses_four'];
    $PTMPL['uses_four_desc'] = $welcome['uses_four_desc'];  

    // Show the Carousel texts
    $PTMPL['carousel_one'] = $welcome['carousel_one'];
    $PTMPL['carousel_one_sub'] = $welcome['carousel_one_sub'];
    $PTMPL['carousel_one_desc'] = $welcome['carousel_one_desc'];
    $PTMPL['carousel_two'] = $welcome['carousel_two'];
    $PTMPL['carousel_two_sub'] = $welcome['carousel_two_sub'];
    $PTMPL['carousel_two_desc'] = $welcome['carousel_two_desc'];
    $PTMPL['carousel_three'] = $welcome['carousel_three'];
    $PTMPL['carousel_three_sub'] = $welcome['carousel_three_sub'];
    $PTMPL['carousel_three_desc'] = $welcome['carousel_three_desc'];

    // Images
    $PTMPL['cover'] = $welcome['cover'];
    $PTMPL['slide_1'] = $welcome['slide_1']; 
    $PTMPL['slide_2'] = $welcome['slide_2']; 
    $PTMPL['slide_3'] = $welcome['slide_3']; 
    $rand = rand(1,3);
    $PTMPL['randomize'] = $welcome['slide_'.$rand]; 

	$carousel = $theme->make();	
	$PTMPL['carousel'] = $carousel;

    // Set the SEO info
	$PTMPL['page_title'] = $LANG['welcome'].' - '.$settings['site_name']; 
	$PTMPL['seo_plugin'] = seo_plugin(0, 0, 0, $welcome['intro_desc'], $LANG['welcome']);

	// Set the active landing page_title
	$landing = $settings['landing'] == 1 ? 'content' : 'content_login';
	$theme = new themer('welcome/'.$landing);
	return $theme->make();
}
?>