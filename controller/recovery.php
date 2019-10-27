<?php

function mainContent() {
	global $PTMPL, $LANG, $CONF, $DB, $user, $settings; 
	$PTMPL['page_title'] = $LANG['recovery_pass']; 

	$recovery = new doRecovery;
	$bars = new barMenus;
	$save = new siteClass;
	$social = new social;

	$PTMPL['adsbar'] = $bars->ads($settings['ads_off']);

	$side_bar = new sidebarClass;
	$PTMPL['shared_menu'] = $side_bar->user_navigation();
	$PTMPL['recommended'] = recomendations();
	
	$PTMPL_old = $PTMPL; $PTMPL = array();

	$theme = new themer('recovery/recover'); $container = ''; 

	$PTMPL['forgot_link'] = permalink($CONF['url'].'/index.php?a=recovery');

	if(isset($_POST['username']) && empty($_POST['username'])) {
		$_SESSION['error'] = errorMessage($LANG['username_not_found']); 
	} elseif(isset($_POST['username']) && !empty($_POST['username'])) {
		$recovery->username = $_POST['username'];

		list($uid, $username, $firstname, $lastname, $email, $key) = $recovery->verify_user();

		// If the requested username is the the result
		if(mb_strtolower($_POST['username']) == $username || mb_strtolower($_POST['username']) == $email) {	
			
			// Prepare the message template 
			$params = 
			    array('contest', ucfirst($username), 'Password', ucfirst($firstname), ucfirst($lastname), 
			    	$key, $email, 'act_username', 'act_firstname', 'act_lastname', 'action', 'action_on'
			    ); 
			$message = $save->message_template($settings['email_recover_temp']);
			 
			// Send the recovery email	 
			$social->no_notify = true;
			$social->subject = $LANG['recovery_subject'];
			$social->message = $message;
		    $social->notifier(null, $uid, null, null, 1);
			header("Location: ".permalink($CONF['url'].'/index.php?a=recovery&account='.$username.'&ready=1')); 		
		} 
	} elseif (isset($_GET['ready'])) {
		$theme = new themer('recovery/password'); $container = '';
		// Fetch the users data
		$u = new userCallback;
		$data = $u->userData(db_prepare_input($_GET['account']));	

		// Show the notification	
		$_SESSION['error'] = infoMessage(sprintf($LANG['key_sent'], $data['email'])); 

		// Set the variables
		$PTMPL['key'] = isset($_POST['key']) ? $_POST['key'] : '';
		$PTMPL['new_password'] = isset($_POST['new_password']) ? $_POST['new_password'] : '';
		$PTMPL['repeat_password'] = isset($_POST['repeat_password']) ? $_POST['repeat_password'] : ''; 

		if (isset($_POST['change'])) {
			// Validate the key
			if(isset($_POST['key']) && $_POST['key'] !== $data['token']) {
				$_SESSION['error'] = errorMessage($LANG['invalid_key']); 
			} elseif (date("Y-m-d", strtotime($data['reg_date'])) < date("Y-m-d")) {
				$_SESSION['error'] = infoMessage($LANG['expired_key']);
			} elseif (mb_strlen($_POST['new_password']) < 6) {
				$_SESSION['error'] = infoMessage($LANG['password_short']);
			} elseif ($_POST['new_password'] !== $_POST['repeat_password']) {
				$_SESSION['error'] = infoMessage($LANG['password_not_match']);
			} else {
				$ret = $recovery->changePassword($data['username'], $_POST['new_password'], $_POST['key']); 
				if ($ret == true) {
					$PTMPL['login'] = '<a href="#" class="btn btn-info my-2 waves-effect" target="_blank" id="openModal1" data-toggle="modal" data-target="#connectModal"></i>'.$LANG['login'].' </a>';
					$_SESSION['error'] = successMessage($LANG['reset_success']);
				}
			}			 
		}
		 
	}

    if(!empty($_SESSION['error'])) {
        $PTMPL['message'] = $_SESSION['error'];
        $_SESSION['error'] = '';
    }

	$container = $theme->make();
	$PTMPL = $PTMPL_old; unset($PTMPL_old);
	$PTMPL['container'] = $container;

	$theme = new themer('recovery/content');
	return $theme->make();
}
?>