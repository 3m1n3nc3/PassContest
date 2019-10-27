<?php

function mainContent() {
	global $PTMPL, $LANG, $CONF, $DB, $user, $settings;

	$userApp = new userCallback;
	$rave_api = new raveAPI;

	$PTMPL['page_title'] = $LANG['up_premium']; 

	$bars = new barMenus;
	$PTMPL['adsbar'] = $bars->ads($settings['ads_off'], 2); 
	$site_icon = $CONF['url']."/".$PTMPL['template_url']."/img/notification.png"; 
	$trusted_badge = '<img height="auto" width="300px" src="'.$CONF['url'].'/'.$PTMPL['template_url'].'/img/fl_trusted.png">'; 

	$check_premium = $userApp->premiumStatus(null, 2);
	$ver_premium = $userApp->premiumStatus(null, 1);

	$plan_title = ($check_premium) ? badge(0, $check_premium['plan'], 1) : '';
	$voter_badge = badge(1); 
	$clead_badge = badge(2); 
	$cmarx_badge = badge(3); 
	$slight_badge = badge(4); 
	$lite_badge = badge(5); 
	$life_badge = badge(6); 
	$prem_val = ($check_premium) ? badge(null, $check_premium['plan'], 3) : '';

	$PTMPL_old = $PTMPL; $PTMPL = array();

	$ravemode = ($settings['rave_mode'] ? 'api.ravepay.co' : 'ravesandboxapi.flutterwave.com'); // Check if sandbox is enabled
	$currency_code 	= $settings['currency']; // Currency Code
	$successful_url	= $CONF['url'].'/connection/raveAPI.php';
	isset($_SESSION['txref']) ? $reference = $_SESSION['txref'] : $reference = '';	

    if (!$check_premium && !$ver_premium) {
        $theme = new themer('premium/upgrade'); $container = ''; 
    } elseif (!$ver_premium) {
    	$theme = new themer('premium/upgrade'); $container = ''; 
    }

 	$PTMPL['vote_point'] = $settings['premium_votes'];
 	$PTMPL['voter_badge'] = $voter_badge;
 	$PTMPL['clead_badge'] = $clead_badge;
 	$PTMPL['cmarx_badge'] = $cmarx_badge;
 	$PTMPL['slight_badge'] = $slight_badge;
 	$PTMPL['lite_badge'] = $lite_badge;
 	$PTMPL['life_badge'] = $life_badge; 
 	$PTMPL['trusted_badge'] = $trusted_badge;
 	$public_key = $settings['rave_public_key']; // Rave API Public key
	$private_key = $settings['rave_private_key']; // Rave API Private key 
 
	// Set the active class without JS
	if(isset($_POST['plan'])) {
		if ($_POST["plan"] == 'premium_plan') {
			$PTMPL['active_class'] = 'active';
			$PTMPL['show_class'] = 'show active';
		} elseif ($_POST["plan"] == 'clead_plan') {
			$PTMPL['active_class1'] = 'active';
			$PTMPL['show_class1'] = 'show active';
		} elseif ($_POST["plan"] == 'cmarx_plan') {
			$PTMPL['active_class1'] = 'active';
			$PTMPL['show_class1'] = 'show active';
		} elseif ($_POST["plan"] == 'slight_plan') {
			$PTMPL['active_class2'] = 'active';
			$PTMPL['show_class2'] = 'show active';
		} elseif ($_POST["plan"] == 'lite_plan') {
			$PTMPL['active_class2'] = 'active';
			$PTMPL['show_class2'] = 'show active';
		} elseif ($_POST["plan"] == 'life_plan') {
			$PTMPL['active_class2'] = 'active';
			$PTMPL['show_class2'] = 'show active';
		}
	} else {
		$PTMPL['active_class'] = 'active'; 
		$PTMPL['show_class'] = 'show active';
	}

	$PTMPL['premium_plan_price'] = $settings['premium_plan'].' '.$settings['currency'];
	$PTMPL['clead_plan_price'] = $settings['clead_plan'].' '.$settings['currency'];
	$PTMPL['cmarx_plan_price'] = $settings['cmarx_plan'].' '.$settings['currency'];
	$PTMPL['slight_plan_price'] = $settings['slight_plan'].' '.$settings['currency'];
	$PTMPL['lite_plan_price'] = $settings['lite_plan'].' '.$settings['currency'];
	$PTMPL['life_plan_price'] = $settings['life_plan'].' '.$settings['currency'];
	$PTMPL['no_plan_price'] = '0.00 '.$settings['currency'];

	// First check if the user is logged in 
	if ($user['username']) {
		$buyer_email = $user['email'];
		$cntr_code = countries(2, $user['country']);  

		if(isset($_POST['plan']) && !$check_premium) {
			if ($_POST["plan"] == 'premium_plan') {
				$plan_name = $LANG['premium_vp'];
				$plan_price = $settings['premium_plan'];
				$plan_desc = sprintf($LANG['premium_voter_plan'], $settings['site_name']);  
			} elseif ($_POST["plan"] == 'clead_plan') {
				$plan_name = $LANG['clead_p'];
				$plan_price = $settings['clead_plan'];
				$plan_desc = sprintf($LANG['clead_plan'], $settings['site_name']);  
			} elseif ($_POST["plan"] == 'cmarx_plan') {
				$plan_name = $LANG['cmarx_p'];
				$plan_price = $settings['cmarx_plan'];
				$plan_desc = sprintf($LANG['cmarx_plan'], $settings['site_name']);  
			} elseif ($_POST["plan"] == 'slight_plan') {
				$plan_name = $LANG['slight_p'];
				$plan_price = $settings['slight_plan'];
				$plan_desc = sprintf($LANG['slight_plan'], $settings['site_name']);  
			} elseif ($_POST["plan"] == 'lite_plan') {
				$plan_name = $LANG['lite_p'];
				$plan_price = $settings['lite_plan'];
				$plan_desc = sprintf($LANG['lite_plan'], $settings['site_name']);  
			} elseif ($_POST["plan"] == 'life_plan') {
				$plan_name = $LANG['life_p'];
				$plan_price = $settings['life_plan'];
				$plan_desc = sprintf($LANG['life_plan'], $settings['site_name']); 
			}  
				$purchase_reference = 'PC-'.mt_rand(5,99).'PRTN-'.strtoupper(uniqid(rand(19*94, true))).'-VF';

				$_SESSION['txref'] = $purchase_reference;	
				$_SESSION['amount'] = $plan_price;
				$_SESSION['currency'] = $currency_code;
				// Store the selected plan
				$_SESSION['Selected_plan'] = $_POST['plan'];

				// Parameters for Checkout, which will be sent to Rave
				$form_body = "
				  <a class=\"flwpug_getpaid\" 
				  data-PBFPubKey=\"{$public_key}\" 
				  data-txref=\"{$purchase_reference}\" 
				  data-amount=\"{$plan_price}\" 
				  data-customer_email=\"{$buyer_email}\" 
				  data-currency=\"{$currency_code}\" 
				  data-pay_button_text=\"Pay Now\" 
				  data-payment_method=\"both\"
				  data-custom_description=\"{$plan_desc}\"
				  data-custom_logo=\"{$site_icon}\"
				  data-country=\"{$cntr_code}\"
				  data-redirect_url=\"{$successful_url}\"></a>
				  
				  <script type=\"text/javascript\" src=\"https://".$ravemode."/flwv3-pug/getpaidx/api/flwpbf-inline.js\"></script>	
				";
							
				$PTMPL['form_body'] = $form_body;  


		} elseif(!$check_premium && isset($_GET['type']) && $_GET['type'] == 'canceled') {
			// If the payment has been canceled
			$PTMPL['error'] = errorMessage('Error <strong>'.$_GET['status'].'</strong>: '.$_GET['message']); 						 
		} elseif(!$check_premium && isset($_GET['type']) && $_GET['type'] == 'successful') {
			 $theme = new themer('premium/upgrade'); $container = '';
 
			// If the flutterwaveREF and OrderREF has been returned by the Return URL
			if(isset($_GET['data']["flwref"]) && isset($_GET['data']['orderref'])) {
				$token = $_GET['data']["flwref"];
				$orderref = $_GET['data']['orderref'];

				//Connect with Verification Server
		        $query = array(
		            "SECKEY" => $private_key,
		            "txref" => $reference
		        );

				$rave_api->ravemode = $ravemode;
				$rave_api->query = $query;
		        $resp = $rave_api->Validate(); 

// manual validation For testing and debugging, useless
// $resp = array("status" => 'success', "data" => array("status" => 'successful', "amount" => $_GET['data']['amount'], "paymentid" => $_GET['data']['paymentid'], "orderref" => $orderref));
// $_SESSION['Selected_plan'] = 'life_plan'; 
// For testing, DELETE

				// Check if the payment was successful
				if(strtoupper($resp['status']) == "SUCCESS") {
					// Validate payment details on server against payment details on client to Verify if the payment is Completed
					if(($resp['data']['amount'] == $_GET['data']['amount']) && ($resp['data']['paymentid'] == $_GET['data']['paymentid']) && ($resp['data']['orderref'] == $orderref)) {

						// If the payment processing was successful
						if(strtoupper($resp['data']['status']) == "SUCCESSFUL" && strtoupper($_GET['data']['status']) == "SUCCESSFUL") {
							 
							 // Set when this plan should expire
							if ($_SESSION['Selected_plan'] == 'clead_plan' || $_SESSION['Selected_plan'] == 'cmarx_plan' || $_SESSION['Selected_plan'] == 'premium_plan') {
								$date = date("Y-m-d H:m:s", strtotime("+1 year +2 days"));
							} elseif ($_SESSION['Selected_plan'] == 'slight_plan') {
								$date = date("Y-m-d H:m:s", strtotime("+1 month +2 days")); 
							} elseif ($_SESSION['Selected_plan'] == 'lite_plan') {
								$date = date("Y-m-d H:m:s", strtotime("+6 month +2 days")); 
							} elseif ($_SESSION['Selected_plan'] == 'life_plan') {
								$date = date("Y-m-d H:m:s", strtotime("+117 years")); 
							} 
							
							$rave_api->today_date = date("Y-m-d H:m:s"); // Todays date 
							$rave_api->exp_date = $date; // Expiry date

							// Variables to pass to database
							$rave_api->payer_id		= db_prepare_input($user['id']);
							$rave_api->payment_id	= db_prepare_input($orderref);
							$rave_api->price		= db_prepare_input($resp['data']['amount']);
							$rave_api->currency	 	= db_prepare_input($settings['currency']);
							$rave_api->plan		 	= db_prepare_input($_SESSION['Selected_plan']); 
							$rave_api->pfn 		 	= db_prepare_input($user['fname']);
							$rave_api->pln		 	= db_prepare_input($user['lname']);
							$rave_api->email		= db_prepare_input($user['email']);
							$rave_api->country	 	= db_prepare_input($user['country']);
							$rave_api->order_ref 	= db_prepare_input($reference);	 
 							
 							// Process this payment and approve the payment
							$response = $rave_api->promote_user(0); 

				            if ($prem_val == 1) {
				            	 $user_type ='voter';
				            } elseif ($prem_val == 2 || $prem_val == 3) {
				            	 $user_type ='contestant';
				            } elseif ($prem_val == 4 || $prem_val == 5 || $prem_val == 6) {
			 					 $user_type ='agency';
				            } 
				            // Change the user role to match the current plan
				            if (isset($user_type)) {
				            	$sql = sprintf("UPDATE " . TABLE_USERS . " SET `role` = '%s' WHERE `id` = %s", $user_type, $user['id']);
				            	dbProcessor($sql, 0, 1);
				            }

							// End all sessions
							unset($_SESSION['txref']);	
							unset($_SESSION['amount']);
							unset($_SESSION['currency']); 
							unset($_SESSION['Selected_plan']);

							// Temporarily tell the system that the user is premium
							$check_premium = 2;  
							 
						} else {
							if (strtoupper($resp['status']) == 'SUCCESS') {
								$PTMPL['error'] = errorMessage('Error: Payment Verification failed');
							} else {
								$PTMPL['error'] = errorMessage('Error '.$resp['status'].': '.$resp['message']);
							}
						} 
					} else {
						if(strtoupper($resp['status']) == 'SUCCESS') {
							$PTMPL['error'] = errorMessage('Error: Information Mismatch');
						} else {
							$PTMPL['error'] = errorMessage('Error '.$resp['status'].': '.$resp['message']);
						}
					}
				} else {
					if(strtoupper($resp['status']) == 'SUCCESS'){ 
						$PTMPL['error'] = errorMessage('Error: Unable to complete payment'); 
					} else {
						$PTMPL['error'] = errorMessage('Error '.$resp['status'].': '.$resp['message']);
					}
				}

			}			 
		} 

		if($check_premium == 2 || ($check_premium && $ver_premium)) {
			$theme = new themer('premium/success'); $container = ''; 
			$PTMPL['div'] = '<div class="card pt-2 p-3">';
			$PTMPL['div_'] = '</div>';

			$premium_link = '<a href="'.permalink($CONF['url']).'/index.php?a=premium">Click Here</a>';
			$premium_check = $userApp->premiumStatus(null, 2);
 
			// If the proAccount was just created
			if($check_premium == 2) { 
				$pt = explode('_', $premium_check['plan']);
				$PTMPL['premium_title'] = $LANG['premium_congrats'];
				$plan_title = ucfirst($pt[0]);
				$plan_title .= ($pt[0] =='premium')?' '.$LANG['p_voter']:'';
				$PTMPL['premium_success'] = sprintf($LANG['premium_success'], $plan_title);
			} else {
				$PTMPL['premium_title'] = $LANG['premium_sub']; 
				$PTMPL['premium_success'] = sprintf($LANG['premium_user_desc'], $plan_title);
			}
			$check_premium = $userApp->premiumStatus(null, 2);
			// Explode the date 
			$valid_till = explode('-', $check_premium['valid_till']);
			$valid = substr($valid_till[2], 0, 2).'-'.$valid_till[1].'-'.$valid_till[0];
			($check_premium['plan']=='life_plan') ? $renew = $LANG['non_renewable_plan'] : $renew = $LANG['renewable_plan'];

			// Days left before the premium plan expires
			$days_days = floor((strtotime($check_premium['valid_till']) - strtotime(date("Y-m-d H:i:s")))/(60*60*24));
			$PTMPL['days2go'] = ($days_days >= 1) ? sprintf($LANG['days_left'], $days_days) : sprintf($LANG['plan_expired'], $plan_title, $premium_link);
			$PTMPL['class'] = ($days_days >= 1) ? 'success' : 'danger';

			$PTMPL['allowed_or_not'] = ($days_days >= 1) ? $LANG['not allowed'] : $LANG['expired_allowed'];

			// Year the plan is expiring
			$PTMPL['expiry'] = ($days_days >= 1) ? sprintf($LANG['expiry'], $renew, $valid) : sprintf($LANG['expired_on'], $valid);

 			// The Amount paid for this premium plan
			$PTMPL['amount'] = $check_premium['amount'].' '.$settings['currency'];

			// Transaction history
			$PTMPL['history'] = $userApp->premiumHistory(0, 0);
		}

		$PTMPL['premium_action'] = 'submit_form(\'premium-voter-form\')';
		$PTMPL['contestant_action'] = 'submit_form(\'premium-contestant-form\')';
		$PTMPL['agent_action'] = 'submit_form(\'premium-agent-form\')';
	} else {
		$PTMPL['premium_action'] = 'show_connect_modal()';
		$PTMPL['contestant_action'] = 'show_connect_modal()';
		$PTMPL['agent_action'] = 'show_connect_modal()';
	}

$container = $theme->make();
 
$PTMPL = $PTMPL_old; unset($PTMPL_old);
$PTMPL['container'] = $container;

$theme = new themer('premium/content');
return $theme->make();	
}
?>