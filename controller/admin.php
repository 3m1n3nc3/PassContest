<?php

function mainContent() {
	global $PTMPL, $LANG, $CONF, $DB, $user, $settings, $admin, $admin_access; 
	$PTMPL['page_title'] = $LANG['admin_login']; 
 	$userApp = new userCallback;

	if (isset($_POST['admin_login'])) {
		$userApp->username = db_prepare_input($_POST['admin_username']);
		$userApp->password = hash('md5', $_POST['admin_password']);
		$check = $userApp->site_admin(2)[0];   

		// Validate the provided details
		if ($userApp->username !== $check['username']) {
			$message = 'Invalid username or password';
		} elseif ($userApp->password !== $check['password']) { 
			$message = 'Invalid username or password';
		} else {
			// Login if provided details are correct
			$_SESSION['admin_username'] = $check['username'];
			$_SESSION['admin_password'] = $check['password']; 
			$logged = true;	 
			if ($logged) {
				header('Location: '.permalink($CONF['url'].'/index.php?a=settings'));
			}
		}
		$PTMPL['message'] = (isset($message)) ? infoMessage($message) : '';
	} 
 
	if ($admin_access) {
		header('Location: '.permalink($CONF['url'].'/index.php?a=settings'));
	}

	$theme = new themer('admin/admin');
	return $theme->make();
}
?>