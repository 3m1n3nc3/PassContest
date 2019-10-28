<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $DB, $user, $settings;
	$userApp = new userCallback;
	$user_bank = $userApp->set_bank(0, $user['id']);
	$sc = new siteClass;
	$social = new social;
	if ($user) {

		// Update online status 
		$social->online_state($user['id'], null, 1); 

		$noti_status = $user['email_notifications'];
		if ($noti_status == '1') {
			$PTMPL['email_notification_check'] = '<span class="text-success">'.$LANG['email_notifications'].' '.$LANG['lang_on'].'</span>';
			$PTMPL['email_checked'] = 'checked';
		} else {
			$PTMPL['email_notification_check'] = '<span class="text-danger">'.$LANG['email_notifications'].' '.$LANG['lang_off'].'</span>';
		}
		$pass = fetch_api(2);
		$site_noti_status = $user['site_notifications'];
		if ($site_noti_status == '1') {
			$PTMPL['site_notification_check'] = '<span class="text-success">'.$LANG['site_notifications'].' '.$LANG['lang_on'].'</span>';
			$PTMPL['site_checked'] = 'checked';
		} else {
			$PTMPL['site_notification_check'] = '<span class="text-danger">'.$LANG['site_notifications'].' '.$LANG['lang_off'].'</span>';
		}

		// Start checking users premium status
		$check_premium = $userApp->premiumStatus(null, 2); 
		$plan_title = ($check_premium) ? badge(0, $check_premium['plan'], 1) : ''; 

		$premium_tab = '';
		$PTMPL_old = $PTMPL; $PTMPL = array();
		$premium_link = '<a href="'.permalink($SETT['url']).'/index.php?a=premium">Click Here</a>';
		if($check_premium) {
			$theme = new themer('premium/success'); $premium_tab = '';

			$premium_check = $userApp->premiumStatus(null, 2);

			$PTMPL['premium_title'] = $LANG['premium_sub']; 
			$PTMPL['premium_success'] = sprintf($LANG['premium_user_desc'], $plan_title);

			$check_premium = $userApp->premiumStatus(null, 2);
			// Explode the date 
			$valid_till = explode('-', $check_premium['valid_till']);
			$valid = substr($valid_till[2], 0, 2).'-'.$valid_till[1].'-'.$valid_till[0];
			($check_premium['plan']=='life_plan') ? $renew = $LANG['non_renewable_plan'] : $renew = $LANG['renewable_plan'];

			// Days left before the premium plan expires
			$days_days = floor((strtotime($check_premium['valid_till']) - strtotime(date("Y-m-d H:i:s")))/(60*60*24));
			$PTMPL['days2go'] = ($days_days >= 1) ? sprintf($LANG['days_left'], $days_days) : sprintf($LANG['plan_expired'], $plan_title, $premium_link);
			$PTMPL['class'] = ($days_days >= 1) ? 'success' : 'danger';

 			// The Amount paid for this premium plan
			$PTMPL['amount'] = $check_premium['amount'].' '.$settings['currency'];

			// Year the plan is expiring
			$PTMPL['expiry'] = ($days_days >= 1) ? sprintf($LANG['expiry'], $renew, $valid) : sprintf($LANG['expired_on'], $valid);

			$PTMPL['allowed_or_not'] = ($days_days >= 1) ? $LANG['not allowed'] : $LANG['expired_allowed'];

			// Transaction history
			$PTMPL['history'] = $userApp->premiumHistory(0, 0);
			$premium_tab = $theme->make();
		} else {
			$get_history = $userApp->premiumHistory(0, 0);
			$history = ($get_history) ? $get_history : 'No transaction history';
			$premium_tab .= '
                 <div class="container border rounded border-info p-3 mb-2 z-depth-1">
                  <div class="text-info text-center">
                    <p class="text-primary font-weight-bold h4">'.$LANG['expired_sub'].'</p>
                    <p class="text-info font-weight-bold">'.sprintf($LANG['you_are'], ucfirst($user['role'])).'</p>  
                    <span class="text-success font-weight-bold">'.sprintf($LANG['to enjoy'], $premium_link).'</span>
                  </div> 
                 </div>
                 <div class="border rounded border-info container z-depth-1 px-5 pb-5 pt-2">
                 	<div class="text-danger font-weight-bold">Transaction History</div>

                 '.$history.'
                 </div>';
		}
 
		$PTMPL = $PTMPL_old; unset($PTMPL_old);
		$PTMPL['premium_tab'] = $premium_tab;

		$PTMPL['user_id'] = $user['id'];

		$bars = new barMenus;
		$PTMPL['adsbar'] = $bars->ads($settings['ads_off'], 3);
		$PTMPL['recommended'] = recomendations();

		$side_bar = new sidebarClass;
		$PTMPL['sidebar_menu'] = $side_bar->manageMenu();
		$PTMPL['shared_menu'] = $side_bar->pre_manage_menu();
		$PTMPL['user_menu'] = $side_bar->user_navigation(); 
	
		$PTMPL['page_title'] = $LANG['update']; 
		// Profile info
		$PTMPL['fname'] = $user['fname'];
		$PTMPL['lname'] = $user['lname'];
		
		// Set the gender status
		$user['gender'] == 'male' ? $PTMPL['m'] = ' selected="selected"' : ($user['gender'] == 'female' ? $PTMPL['f'] = ' selected="selected"' : $PTMPL['o'] = ' selected="selected"');

		// Set city
		$PTMPL['city'] = $user['city'] ? '<option selected="selected" value="'.$user['city'].'">'.$user['city'].'</option>': '';

		// Set state and fetch cities
		$sc->state = $user['state'];
		$sid = $sc->fetch_locale(3)[0];
		$PTMPL['state'] = $user['state'] ? '<option selected="selected" value="'.$user['state'].'" id="'.$sid['id'].'">'.$user['state'].'</option>' : '';
		$PTMPL['fetch_city'] = $user['state'] ? ' onchange="fetch_city()"' : '';

		$PTMPL['country'] = $user['country'];
		$PTMPL['phone'] = $user['phone'];
		// Basic INfo
		$PTMPL['prof'] = $user['profession'];
		$PTMPL['facebook'] = $user['facebook'];
		$PTMPL['twitter'] = $user['twitter'];
		$PTMPL['instagram'] = $user['instagram'];
		$PTMPL['loves'] = $user['lovesto'];
		$PTMPL['intro'] = $user['intro'];
		// Bank info
		$PTMPL['paypal'] = $user_bank['paypal'];  
		$PTMPL['bank'] = $user_bank['bank_name']; 
		$PTMPL['bank_address'] = $user_bank['bank_address'];
		$PTMPL['sort'] = $user_bank['sort_code'];  
		$PTMPL['account_name'] = $user_bank['account_name']; 
		$PTMPL['account_number'] = $user_bank['account_number']; 
		$PTMPL['routing'] = $user_bank['aba']; 		
	} else { 
		// If the session or cookies are not set, redirect to home-page
		header("Location: ".$SETT['site_url']."/index.php?a=welcome"); 	
	}

$theme = new themer('account/update');
return $theme->make();
}
?>
