<?php
require_once(__DIR__ .'/../includes/autoload.php');
$fb = new facebook();
$save = new siteClass;
$social = new social;

$header = 0; 
$message = ''; 
 
if(isset($_GET['facebook']) && $settings['fb_appid']) { 
	$fb->url = $CONF['url'];
	$fb->fb_appid = $settings['fb_appid'];
	$fb->fb_secret = $settings['fb_secret'];
	$fb->fbacc = $settings['fbacc'];
	$fb->code = $_GET['code'];
	$fb->state = $_GET['state'];
	$process = $fb->facebookAccess();

	if($process == 1) {
		// Construct the email message
		$email = $user['email'];
		$phone = $user['phone']; 
		$params = 
		    array('contest', ucfirst($user['username']), $user['password'], $user['fname'], $user['lname'], 
		    	'Not Required', $email, 'act_username', 'act_firstname', 'act_lastname', 'action', 'action_on'
		    );
		$message = $save->message_template($settings['email_reg_temp'], $params);
		$subject = $LANG['welcome'].' to '.$settings['site_name'];

		if ($settings['activation'] == 'email') {
			// Send the message
			$save->user_id = $user['id'];
			$save->reg = 1;
			$save->mailerDaemon($CONF['email'], $email, $subject, $message);				 
		} elseif ($settings['activation'] == 'phone') {
			$social->sendSMS($subject.'! '.$message, $phone);
		} elseif($settings['email_welcome']) { 
			// Send the message
			$save->user_id = $user['id'];
			$save->reg = 1;
			$save->mailerDaemon($CONF['email'], $email, $subject, $message);
		}

		if (isset($_SESSION['referrer'])) {
			$header = urldecode(urlReferrer($_SESSION['referrer'], 1));
			unset($_SESSION['referrer']);
		} else {
			$header = permalink($CONF['url'].'/index.php?a=account');
		} 	
		header("Location: ".$header);	
	}
}

$client_id = isset($_SESSION['client_id']) ? '&client_id='.$_SESSION['client_id'] : '';

if(isset($_POST['login'])) {
    $dbusername = db_prepare_input(mb_strtolower($_POST['username'])); 
    $dbpassword = hash('md5', $_POST['password']);
	$PTMPL['postuser'] = $_POST['username'];  

	// Check user
	$check_user = $userApp->userData($dbusername);

	$verify_username = filter_var($dbusername, FILTER_VALIDATE_EMAIL) && $dbusername == $check_user['email'] ? true : ($dbusername == $check_user['username'] ? true : false);
	// Log-in usage
	if ($dbusername == '' || $dbpassword == '') {
     	$message = errorMessage($LANG['not_empty']);  
    } elseif (!$verify_username) {
	    $message = errorMessage($LANG['user_unrecognized']); 
	} elseif ($check_user['claimed'] !== '1') {
	    $message = infoMessage($LANG['not_claimed']);   
	} elseif ($dbpassword !==$check_user['password']) {
	    $message = errorMessage($LANG['invalid_password']); 
	} else {
		$userApp->username = $_POST['username'];
		$userApp->password = $_POST['password'];
		if (isset($_POST['remember'])) {
			$userApp->remember = 1;  
		}  $auth = true;
		$auth = $userApp->authenticateUser();
		if ($auth) {
			$message = successMessage($LANG['login_success']); 
			if ($_POST['referrer']) {
				$header = urldecode(urlReferrer($_POST['referrer'], 1)).$client_id;
			} else {
				$header = permalink($CONF['url'].'/index.php?a=account');
			} 	
			if (isset($_SESSION['referrer'])) {
				unset($_SESSION['referrer']);
			}	
		}  
	} 
}
if(isset($_POST['signup'])) {
	$inv_only = $settings['invite_only'];
	$re_cap = $settings['captcha'];
	$phone_var = $settings['activation'] == 'phone' ? 1 : 0;

	$dbphone = $phone_var ? db_prepare_input($_POST['phone']) : '';
    $dbrecaptcha = $re_cap ? db_prepare_input($_POST['recaptcha']) : '';
    $dbtoken = $inv_only ? db_prepare_input($_POST['invite_code']) : '';

    $dbusername = db_prepare_input(mb_strtolower($_POST['username']));
    $dbemail = db_prepare_input(mb_strtolower($_POST['email'])); 
    $dbpassword = db_prepare_input($_POST['password']);
	$PTMPL['postuser'] = $_POST['username'];
	$PTMPL['postemail'] = $_POST['email']; 

	if ($inv_only && $dbtoken == '' || $dbusername == '' || $dbemail == '' || $dbpassword == '') {
     	$message = errorMessage($LANG['not_empty']);  
    } elseif ($dbusername == $userApp->userData($dbusername)['username']) { 
        $message = infoMessage($LANG['username_taken']); 
    } elseif ($dbemail == $userApp->checkEmail($dbemail)) {
        $message = infoMessage($LANG['email_used']);     
    } elseif (!filter_var($dbemail, FILTER_VALIDATE_EMAIL)) {
    	 $message = errorMessage($LANG['invalid_email']);   
    } elseif (mb_strlen($dbpassword)<6) {
    	 $message = errorMessage($LANG['password_short']);   
    } elseif ($userApp->captchaVal($dbrecaptcha) == false) {
    	 $message = errorMessage($LANG['fail_recaptcha']);   
    } elseif ($userApp->use_invite($dbtoken) == false) {
		$message = errorMessage($LANG['gift_card_registered']); 
	} elseif ($userApp->phoneVal($dbphone) == false) {
		$message = errorMessage($LANG['invalid_phone_number']);
	} elseif ($userApp->phoneVal($dbphone, 1) == false) {
		$message = infoMessage($LANG['phone_number_taken']);
	} else { 
		$phone = $phone_var ? filter_var($dbphone, FILTER_SANITIZE_NUMBER_INT) : NULL;
        $userApp->registrationCall($dbusername, $dbemail, $dbpassword, $phone);
        $message = successMessage($LANG['signup_success']); 
		if ($_POST['referrer']) {
			$header = urldecode(urlReferrer($_POST['referrer'], 1)).$client_id;
		} else {
			$header = permalink($CONF['url'].'/index.php?a=account');
		}
		if (isset($_SESSION['referrer'])) {
			unset($_SESSION['referrer']);
		}
    }   
}     
$message = array('message' => $message, 'header' => $header);
echo json_encode($message, JSON_UNESCAPED_SLASHES);  
?>
