<?php
require_once(__DIR__ .'/../includes/autoload.php');
$userid = $user['id'];

$gett = new contestDelivery;
$save = new siteClass;

// Controls for the notification switches
if (isset($_POST['action'])) {
	echo ($_POST['type'] == 1) ? $gett->activateItem($userid, 0, $_POST['s'], 4) : '';
	echo ($_POST['type'] == 2) ? $gett->activateItem($userid, 0, $_POST['s'], 5) : ''; 
	echo ($_POST['type'] == 3) ? $gett->activateItem($userid, 0, $_POST['s'], 6) : ''; 
} else {
	if(isset($_POST['save']) && $_POST['save'] == 1) {
	    $sitename = db_prepare_input($_POST['sitename']); 
	    $sitephone = db_prepare_input($_POST['sitephone']); 
	    $site_mode = db_prepare_input($_POST['site_mode']); 
		$activation = db_prepare_input($_POST['activation']);
		$sidebar = db_prepare_input($_POST['sidebar']); 
		$direction = db_prepare_input($_POST['direction']); 
		$tracking = db_prepare_input($_POST['tracking']);
		$recommend = db_prepare_input($_POST['recommend']);   

		if ($sitename == '') { 
			echo infoMessage('Site Name can\'t be empty');  
		} else {
			$save->sitename = $sitename;
			$save->sitephone = $sitephone;
			$save->site_mode = $site_mode;
			$save->activation = $activation;
			$save->sidebar = $sidebar;
			$save->direction = $direction;
			$save->tracking = $tracking; 
			$save->recommend = $recommend; 
			$save->settings = 1;
			echo $save->site_settings(0);  
		} 
	} elseif($_POST['save'] == 2) {
		$save->explore = db_prepare_input($_POST['explore']); 
		$save->featured = db_prepare_input($_POST['featured']);
		$save->notifications = db_prepare_input($_POST['notifications']);
		$save->messenger = db_prepare_input($_POST['messenger']);
		$save->notifications_drop = db_prepare_input($_POST['notifications_drop']);
		$save->table = db_prepare_input($_POST['table']); 
		$save->contest = db_prepare_input($_POST['contest']); 
		$save->voting = db_prepare_input($_POST['voting']);  
		echo $save->site_settings(1); 
	} elseif($_POST['save'] == 3) {
		$save->captcha = db_prepare_input($_POST['captcha']); 
		$save->invite = db_prepare_input($_POST['invite']); 
		$save->fb_appid = db_prepare_input($_POST['fb_appid']);
		$save->fb_secret = db_prepare_input($_POST['fb_secret']); 
		echo $save->site_settings(2); 
	} elseif($_POST['save'] == 4) {
		$save->twilio_phone = db_prepare_input($_POST['twilio_phone']); 
		$save->twilio_sid = db_prepare_input($_POST['twilio_sid']); 
		$save->twilio_token = db_prepare_input($_POST['twilio_token']); 

		$save->email_apply = db_prepare_input($_POST['email_apply']); 
		$save->email_approved = db_prepare_input($_POST['email_approved']); 
		$save->email_social = db_prepare_input($_POST['email_social']); 
		$save->email_vote = db_prepare_input($_POST['email_vote']); 
		$save->email_comment = db_prepare_input($_POST['email_comment']); 
		$save->email_welcome = db_prepare_input($_POST['email_welcome']); 

		$save->premium_sms = db_prepare_input($_POST['premium_sms']);
		$save->send_sms = db_prepare_input($_POST['send_sms']);
		$save->smtp = db_prepare_input($_POST['smtp']);
		$save->smtp_secure = db_prepare_input($_POST['smtp_secure']);  
		$save->smtp_auth = db_prepare_input($_POST['smtp_auth']);  
		$save->smtp_port = db_prepare_input($_POST['smtp_port']); 
		$save->smtp_server = db_prepare_input($_POST['smtp_server']);  
		$save->smtp_username = db_prepare_input($_POST['smtp_username']);  
		$save->smtp_password = db_prepare_input($_POST['smtp_password']); 
		echo $save->site_settings(3); 
	} elseif($_POST['save'] == 5) {
		$save->approved = db_prepare_input($_POST['approved']); 
		$save->declined = db_prepare_input($_POST['declined']); 
		$save->comment = db_prepare_input($_POST['comment']);  
		$save->reply = db_prepare_input($_POST['reply']); 
		$save->vote = db_prepare_input($_POST['vote']);  
		$save->apply = db_prepare_input($_POST['apply']);  
		$save->register = db_prepare_input($_POST['register']);  
		$save->recover = db_prepare_input($_POST['recover']);   
		echo $save->site_settings(4); 
	} elseif($_POST['save'] == 6) {
		$save->unit_1 = db_prepare_input($_POST['unit_1']);    
		$save->unit_2 = db_prepare_input($_POST['unit_2']);    
		$save->unit_3 = db_prepare_input($_POST['unit_3']);    
		$save->unit_4 = db_prepare_input($_POST['unit_4']);    
		$save->unit_5 = db_prepare_input($_POST['unit_5']);   
		$save->unit_6 = db_prepare_input($_POST['unit_6']);   
		$save->status = db_prepare_input($_POST['status']);    
		echo $save->site_settings(7); 
	}	 
}

