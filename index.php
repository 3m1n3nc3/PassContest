<?php
require_once(__DIR__ . '/includes/autoload.php');

$site_class = new siteClass; 

if(isset($_GET['a']) && isset($action[$_GET['a']])) {
	$page_name = $action[$_GET['a']];
} else {
	$page_name = 'welcome';
}
if ($page_name == 'welcome' || $page_name == 'connector') { 
	$hide_nav = true;
}

// Extra class for the content [main and sidebar]
$PTMPL['content_class'] = ' content-'.$page_name;
$PTMPL['referrer'] = $referrer;
 
require_once("controller/{$page_name}.php");
 
$PTMPL['skinner'] = $settings['skin'];
$PTMPL['site_title'] = $settings['site_name'];
$PTMPL['seo_plugin'] = seo_plugin(0, 0, 0, $PTMPL['site_title'], 0);
$PTMPL['site_url'] = $SETT['url']; 
$PTMPL['cur_page'] = $page_name;
$PTMPL['country'] = $user['country']; 
$PTMPL['countries'] = set_local(1, $user['country']); 
$PTMPL['favicon'] = $welcome['favicon']; 

// Link to read the documentation
$PTMPL['docs_link'] = permalink($SETT['url'].'/index.php?a=documentation');

$PTMPL['copyrights'] = '&copy;'.date('Y').' <a href="'.$SETT['copyrights_url'].'" target="_blank">'.$SETT['copyrights']
.'</a>, made with <i class="fa fa-heart text-danger"></i>';

$PTMPL['username'] = $user['username'];

$PTMPL['forgot_link'] = permalink($SETT['url'].'/index.php?a=recovery');

$extra_ = extra_fields();


$PTMPL['fb_appid'] = $settings['fb_appid'];

// Facebook Login Url
$PTMPL['fbconnect'] = $extra_['fbconnect'];
// Captcha
$PTMPL['recaptcha'] = $extra_['recaptcha'];
// Site is invite invite_only
$PTMPL['invite_code'] = $extra_['invite_code'];
// Set phone number
$PTMPL['phone_number'] = $extra_['phone_number'];

$captcha_url = '/includes/vendor/goCaptcha/goCaptcha.php?gocache='.strtotime('now');
$PTMPL['captcha_url'] = $SETT['url'].$captcha_url;

//$PTMPL['token'] = $_SESSION['token_id'];  

$theme = new themer('user/menu'); $shared_menu = '';
$shared_menu = $theme->make();	

$theme = new themer('user/slidebtn'); $slidebtn = '';
$slidebtn = $theme->make();	
 
$dm = new menuHandler; 
$PTMPL['dropmenu'] = $dm->droplmenu();

$PTMPL['featured_link'] = permalink($SETT['url'].'/index.php?a=featured');

if ($admin_access) {
	if ($settings['mode'] == 'offline') {
		$PTMPL['offline_notice'] = 'Site is offline';
	} elseif ($settings['mode'] == 'debug') {
		$PTMPL['offline_notice'] = 'Debug Mode';
	}	
}

if(!empty($user['username'])) {
	$PTMPL['slidebtn'] = $slidebtn;	
	$PTMPL['menu'] = $dm->menu($user); 

	$PTMPL['shared_menu'] = $shared_menu;

	$PTMPL['explore_link'] = '
	<li class="nav-item active">
		<a class="nav-link font-weight-bold" href="'.permalink($SETT['url'].'/index.php?a=explore').'" target="_self">'.$LANG['explore'].'</a>
	</li>';	 
} else {
	$PTMPL['login'] = '<a href="#" class="btn btn-primary btn-sm rounded" target="_blank" id="openModal1" data-toggle="modal" data-target="#connectModal"></i>'.$LANG['login'].' </a>';
	$PTMPL['logohere'] = '<a class="navbar-brand pl-3" href="'.$SETT['url'].'"> <img src="'.getImage($welcome['logo']).'" height="45" alt="passcontest logo"> </a>';
}

if ($admin) {
	$PTMPL['admin_menu'] = '
    <ul class="navbar-nav nav-flex-icons">  
      <li class="nav-item">
        <a class="nav-link text-white" href="'.permalink($SETT['url'].'/index.php?a=admin').'"><i class="fa fa-tachometer"></i></a>
      </li>          
    </ul>';
}

$PTMPL['tracking_code'] = $settings['tracking']; 
$PTMPL['language'] = $_COOKIE['lang'];

// If the user is not logged in set the connect modal and buttons
$theme = new themer('welcome/connect') ; $connector = '';
if (isset($hide_nav)) {
	// If this is a loggin landing page hide the connect modal and buttons
	if (!$user && $settings['landing'] == 1) {
		$PTMPL['connector'] = $theme->make();
	}
} else { 
	$PTMPL['connector'] = $theme->make(); 
}

// Render the page
$PTMPL['content'] = mainContent();  
 
// Show or hide the navigation bar menu

$theme = new themer('welcome/navigation'); $double_navigation = '';
if (isset($hide_nav)) {
	$PTMPL['double_navigation'] = $settings['landing'] == 1 ? $theme->make() : ''; 
} else {
	$PTMPL['double_navigation'] = $theme->make(); 
}

// Footer Links
$site_class->what = 'status = \'1\'';
$get_pages = $site_class->static_pages(0, 0);

$pages = '<a class="px-1" href="'.$PTMPL['docs_link'].'"> <small>'.strtoupper($LANG['documentation']).'</small> </a>';
if ($get_pages) {
	foreach($get_pages as $list => $key) { 
		$pages .= '<a class="px-1" href="'.permalink($SETT['url'].'/index.php?a=static&page='.$key['link']).'"> <small>'.strtoupper($key['title']).'</small> </a>';
	}					 
} else {
	$pages = '<a href="#" class="text-danger">No extra pages to show</a>';
}	
$PTMPL['footer_links'] = $pages;

$theme = new themer('container');
echo $theme->make();
 
?>
