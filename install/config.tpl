<?php
//error_reporting(E_ALL);
/*
 * turn off magic-quotes support, for runtime e, as it will cause problems if enabled
 */
if (version_compare(PHP_VERSION, 5.3, '<') && function_exists('set_magic_quotes_runtime')) set_magic_quotes_runtime(0);


$CONF = $PTMPL = array();

/* 
* set currentPage in the local scope
*/
$CONF['current_page'] = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);

/* 
* The MySQL credentials
*/	
define('DB_PREFIX', '<DB_PREFIX>');	
$CONF['dbdriver'] = 'mysql'; 
$CONF['dbhost'] = '<DB_HOST>'; 
$CONF['dbuser'] = '<DB_USER>'; 
$CONF['dbpass'] = '<DB_PASSWORD>'; 
$CONF['dbname'] = '<DB_NAME>';

/* 
* The Installation URL 
* https is enforced in .HTACCESS, to use the auto protocol feature remove the .HTACCESS https enforcement
*/
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https' : 'http';
$CONF['url'] = $protocol.'://'.$_SERVER['HTTP_HOST'];

/* 
* The Notifications e-mail
*/
$CONF['email'] = '<SITE_EMAIL>';

$CONF['site_image'] = 'profile_city.jpg';

/* 
* Copyrights owner
*/
$CONF['copyrights'] = '<SITE_COPY_OWNER>';
$CONF['copyrights_url'] = '<SITE_COPY_OWNER_LINK>';

/* 
* The templates directory
*/
$CONF['template_path'] = 'templates';

$action = array('admin'						=> 'admin',
				'contest'					=> 'contest',
				'documentation'				=> 'documentation',
				'featured'					=> 'featured',
				'enter'						=> 'enter', 
				'voting'					=> 'voting',
				'settings'					=> 'settings',
				'messenger'					=> 'messenger', 
				'upload'					=> 'upload',
				'recovery'					=> 'recovery',
				'profile'					=> 'profile',
				'stats'						=> 'stats',
				'premium'					=> 'premium',
				'bounty'					=> 'bounty',
				'credit'					=> 'credit',
				'static'					=> 'static',
				'welcome'					=> 'welcome',
				'explore'					=> 'explore',
				'account'					=> 'account',
				'offline'					=> 'offline',
				'update'					=> 'update',
				'gallery'					=> 'gallery',
				'timeline'					=> 'timeline',
				'followers'					=> 'followers',
				'search'					=> 'search'
				);
$enc_key = 'aHR0cHM6Ly9hcGkucGFzc2NvbnRlc3QuY2Yv';
/* 
* Define the cookies path
*/				
define('COOKIE_PATH', preg_replace('|'.$protocol.'?://[^/]+|i', '', $CONF['url']).'/');

?>