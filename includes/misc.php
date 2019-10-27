<?php
$settings = getSetting();
$welcome = getSetting(1);

// Store the theme path and theme name into the CONF and TMPL
$PTMPL['template_path'] = $CONF['template_path'];
$PTMPL['template_name'] = $CONF['template_name'] = $settings['template'];
$PTMPL['template_url'] = $CONF['template_url'] = $CONF['template_path'].'/'.$CONF['template_name'];
$PTMPL['full_template_url'] = $CONF['full_template_url'] = $CONF['url'].'/'.$PTMPL['template_url'];



// Site general image
$site_image = getImage($welcome['cover']);

$userApp = new userCallback;
if (isset($_SESSION['username'])) {
	$user = $userApp->userData($_SESSION['username']);
} else {
	$user = $userApp->authenticateUser();
}
$admin = (isset($_SESSION['admin_username']) && isset($_SESSION['admin_password'])?$_SESSION['admin_username']:'');
$admin_access = ($admin) ? true : false; 

// Get the users public data
if (isset($_GET['a'])) {
	if ($_GET['a'] == 'profile' || $_GET['a'] == 'gallery' || $_GET['a'] == 'timeline' || $_GET['a'] == 'followers') {		
		if (isset($_GET['u']) && $_GET['u'] !== '') {
			// Fetch profiles from get u
			$profiles = $userApp->userData($_GET['u']);
		} elseif (isset($_GET['followers']) && $_GET['followers'] !== '') {
			// Fetch user from followers
			$userApp->user_id = $_GET['followers'];
			$profiles = $userApp->userData(NULL, 1)[0];
		} elseif (isset($_GET['following']) && $_GET['following'] !== '') {
			// fetch profiles from get following
			$userApp->user_id = $_GET['following'];
			$profiles = $userApp->userData(NULL, 1)[0];
		} elseif (isset($_GET['user']) && $_GET['user'] !== '') {
			// fetch profiles from get user
			$userApp->user_id = $_GET['user'];
			$profiles = $userApp->userData(NULL, 1)[0];
		} else {
			// fetch profiles from current user
			$profiles = $userApp->userData($user['username']);
		}
	} 
}
fetch_api(1);   

// If the user is suspended log him out
// User[status] 0: unverified 
// User[status] 1: Suspended 
// User[status] 2: Active
if($user['status'] == 1) { 
    $userApp->logOut();
    header('Location: '.permalink($CONF['url'].'/index.php?a=welcome'));
}

// Set the site's offline mode
if (isset($_GET['a']) && $_GET['a'] !== 'offline' && $_GET['a'] !== 'admin' && !$admin_access) {
    if ($settings['mode'] == 'offline') { 
        header("Location: ".permalink($CONF['url'].'/index.php?a=offline'));
    }
}

// Set debug mode to session
if ($settings['mode'] == 'debug' || $settings['mode'] == 'offline') { 
    $_SESSION['site_mode'] = $settings['mode'];
} else {
	if (isset($_SESSION['site_mode'])) {
		unset($_SESSION['site_mode']);
	}
} 

// Set the defult timezone
date_default_timezone_set("Africa/Lagos");

if (isset($_GET['referrer'])) {
	$_SESSION['referrer'] = $_GET['referrer'];
	$referrer = $_GET['referrer'];
} else {
	$referrer = null;
}

// Content odering
$sidebar = $settings['sidebar'];
$order = $settings['direction'];

// Navigation comes first
if ($order == 1) {
	$PTMPL['margin_rl'] = '10';
	$PTMPL['margin_lr'] = '5';
	$rl = '';
	$lr = ' order-md-first';
// Navigation comes next
} else {
	$PTMPL['margin_rl'] = '5';
	$PTMPL['margin_lr'] = '10';
	$rl = ' order-md-first';
	$lr = '';
}

// Change status and position of sidebar //adm_col only affects the admin dashboard
if ($sidebar == 1) {
	$PTMPL['col_1'] = 'col-md-8'.$rl; 
	$PTMPL['col_2'] = 'col-md-4'.$lr; 
	$PTMPL['row'] = 'row';
} elseif ($sidebar == 2)  {
	$PTMPL['col_1'] = 'col-md-12'.$lr;
	$PTMPL['col_2'] = 'col-md-12'.$rl; 
	$PTMPL['row'] = 'row'; 
} else {
	$PTMPL['col_1'] = 'col-md-12'.$lr;
	$PTMPL['col_2'] = 'col-md-12 d-none';

	$PTMPL['adm_col_1'] = 'col-md-12'.$lr;
	$PTMPL['adm_col_2'] = 'col-md-12';
	$PTMPL['row'] = 'row'; 	
}

if ($sidebar == 0) {
	$PTMPL['col_1'] = 'col-md-8'.$rl; 
	$PTMPL['col_2'] = 'col-md-4'.$lr;	 
} else {
	$PTMPL['adm_col_1'] = $PTMPL['col_1']; 
	$PTMPL['adm_col_2'] = $PTMPL['col_2'];
} 
