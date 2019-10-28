<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $DB, $user, $settings, $welcome, $admin, $marxTime;
	
	$PTMPL['page_title'] = $LANG['settings'];
	if ($admin) {

		// Fetch all available contests
		$gett = new contestDelivery; 
		$site_class = new siteClass;
		$userApp = new userCallback;
		$bars = new barMenus;
		$side_bar = new sidebarClass;
		$rave_api = new raveAPI; 
		$social = new social;

		$contests = $gett->getContest();


		$PTMPL['adsbar'] = $bars->ads($settings['ads_off'], 3);

		$PTMPL['sidebar_menu'] = $side_bar->admin_menu();

		$PTMPL_old = $PTMPL; $PTMPL = array();
		
		$fb_access = $settings['fbacc']; 
		$permalinks = $settings['permalinks'];

		$PTMPL['site_name'] = $settings['site_name'];
		$PTMPL['site_phone'] = $settings['site_phone'];
		$PTMPL['site_mode'] = $settings['mode'];
		$PTMPL['activation'] = $settings['activation'];
		$PTMPL['perpage_explore'] = $settings['per_explore'];
		$PTMPL['perpage_table'] = $settings['per_table'];
		$PTMPL['perpage_notifications'] = $settings['per_notification'];
		$PTMPL['perpage_messenger'] = $settings['per_messenger'];
		$PTMPL['perpage_notifications_drop'] = $settings['per_notification_drop'];
		$PTMPL['perpage_featured'] = $settings['per_featured'];
		$PTMPL['perpage_contest'] = $settings['per_contest'];
		$PTMPL['perpage_voting'] = $settings['per_voting'];
		$PTMPL['tracking'] = $settings['tracking'];
		$PTMPL['fb_appid'] = $settings['fb_appid'];
		$PTMPL['fb_secret'] = $settings['fb_secret'];
		$PTMPL['captcha'] = $settings['captcha'];
		$PTMPL['approved_temp'] = $settings['email_approved_temp'];
		$PTMPL['declined_temp'] = $settings['email_declined_temp'];
		$PTMPL['comment_temp'] = $settings['email_comment_temp'];
		$PTMPL['reply_temp'] = $settings['email_reply_temp'];
		$PTMPL['vote_temp'] = $settings['email_vote_temp'];
		$PTMPL['apply_temp'] = $settings['email_apply_temp'];
		$PTMPL['reg_temp'] = $settings['email_reg_temp'];
		$PTMPL['recover_temp'] = $settings['email_recover_temp'];
		$PTMPL['smtp_port']  = $settings['smtp_port'];
		$PTMPL['smtp_server']  = $settings['smtp_server'];
		$PTMPL['smtp_username']  = $settings['smtp_username'];
		$PTMPL['smtp_password']  = $settings['smtp_password'];
		$PTMPL['twilio_phone']  = $settings['twilio_phone'];
		$PTMPL['twilio_sid'] = $settings['twilio_sid'];
		$PTMPL['twilio_token'] = $settings['twilio_token'];

		isset($_POST['test_sms']) || isset($_POST['test_email']) ? $PTMPL['active_emailer'] = 'active show' : $PTMPL['active_general'] = 'active show';
 
		// Site settings Page
		if ($_GET['a'] == 'settings' && !isset($_GET['b'])) {
			$theme = new themer('admin/settings'); $container = '';

			// Send a test SMS
			$debug = 'Turn on Debug or Offline Mode to see detailed error reports<br>';
			if (isset($_POST['test_sms'])) {
				if (empty($settings['twilio_sid']) || empty($settings['twilio_token']) || empty($settings['twilio_phone'])) {
					$PTMPL['status_message'] = $debug.errorMessage('Check your SMS settings are all completed');
				} else {
					$test_sms = $social->sendSMS(null, null, 1);
					$PTMPL['status_message'] = $test_sms == 1 ? $debug.successMessage('Test SMS Sent') : '';
				}
			} elseif (isset($_POST['test_email'])) {
				$site_class->test = true;
				$PTMPL['status_message'] = $debug.$site_class->mailerDaemon(null, null, null, null);
			}

			// Switches and toggles
			$premium_site = $settings['premium'];
			// Premium website toggle
			if ($premium_site == '1') {
				$PTMPL['premium_site'] = '<span class="text-success">'.$LANG['premium'].' '.$LANG['lang_on'].'</span>';
				$PTMPL['premium_site_on'] = 'checked';
			} else {
				$PTMPL['premium_site'] = '<span class="text-danger">'.$LANG['premium'].' '.$LANG['lang_off'].'</span>';
			}
			// permalink toggle
			if ($permalinks == '1') {
				$PTMPL['permalinks'] = '<span class="text-success">'.$LANG['permalinks'].' '.$LANG['lang_on'].'</span>';
				$PTMPL['permalinks_on'] = 'checked';
			} else {
				$PTMPL['permalinks'] = '<span class="text-danger">'.$LANG['permalinks'].' '.$LANG['lang_off'].'</span>';
			}
			// facebook_login toggle
			if ($fb_access == '1') {
				$PTMPL['fb_access'] = '<span class="text-success">'.$LANG['facebook_login'].' '.$LANG['lang_on'].'</span>';
				$PTMPL['fb_access_on'] = 'checked';
			} else {
				$PTMPL['fb_access'] = '<span class="text-danger">'.$LANG['facebook_login'].' '.$LANG['lang_off'].'</span>';
			}

			// Set the site mode
			if ($PTMPL['site_mode']== 'live') {
				$PTMPL['mode_live'] = 'selected';
			} elseif ($PTMPL['site_mode']== 'offline') {
				$PTMPL['mode_off'] = 'selected';
			} elseif ($PTMPL['site_mode']== 'debug') {
				$PTMPL['mode_deb'] = 'selected';
			}
			// Set email activation
			if ($PTMPL['activation']== 'none') {
				$PTMPL['activation_no'] = 'selected';
			} elseif ($PTMPL['activation']== 'email') {
				$PTMPL['activation_em'] = 'selected';
			} elseif ($PTMPL['activation']== 'phone') {
				$PTMPL['activation_ph'] = 'selected';
			}
			// Enable or desable the captcha  
			($settings['captcha']) ? $PTMPL['captcha_on'] = 'selected' : $PTMPL['captcha_off'] = 'selected';

			// Set site as invite only
			($settings['invite_only']) ? $PTMPL['invite_on'] = 'selected' : $PTMPL['invite_off'] = 'selected';

			// Enable or disable smtp
			($settings['smtp'] == 1) ? $PTMPL['smtp_on']  = 'selected' : $PTMPL['smtp_off'] = 'selected';

			// Enable or disable sms
			($settings['sms'] == 1) ? $PTMPL['sms_on']  = 'selected' : $PTMPL['sms_off'] = 'selected';
			
			// Enable or disable sms for only premium users
			($settings['sms_premium'] == 1) ? $PTMPL['sms_premium_on']  = 'selected' : $PTMPL['sms_premium_off'] = 'selected';

			// Set the smtp secure mode
			if ($settings['smtp_secure'] == '0') {
				$PTMPL['smtp_secure_off']  = 'selected';
			} elseif ($settings['smtp_secure'] == 'ssl') {
				$PTMPL['smtp_secure_ssl']  = 'selected';
			} elseif ($settings['smtp_secure'] == 'tls') {
				$PTMPL['smtp_secure_tls']  = 'selected';
			}								
			// Set the sidebar status and position
			if ($settings['sidebar'] == '0') {
				$PTMPL['sidebar_off']  = 'selected';
			} elseif ($settings['sidebar'] == '1') {
				$PTMPL['sidebar_on']  = 'selected';
			} elseif ($settings['sidebar'] == '2') {
				$PTMPL['sidebar_full']  = 'selected';
			} 								
			// Set who will see recommendations
			if ($settings['recommend'] == '0') {
				$PTMPL['recommend_0']  = 'selected';
			} elseif ($settings['recommend'] == '1') {
				$PTMPL['recommend_1']  = 'selected';
			} elseif ($settings['recommend'] == '2') {
				$PTMPL['recommend_2']  = 'selected';
			} elseif ($settings['recommend'] == '3') {
				$PTMPL['recommend_3']  = 'selected';
			} 
			// Set the direction of the website, from left to right or reverse
			if ($settings['direction'] == 0) {
				$PTMPL['direction_right'] = 'selected';
			} elseif ($settings['direction'] == 1) {
				$PTMPL['direction_left'] = 'selected';
			}	
			// Set smtp authentication
			($settings['smtp_auth'] == 1) ? $PTMPL['smtp_auth_on']  = 'selected' : $PTMPL['smtp_auth_off'] = 'selected';

			// Settings for email delivery
			($settings['email_approved'] == 1) ? $PTMPL['email_approved_on']  = 'selected' : $PTMPL['email_approved_off'] = 'selected'; 
			($settings['email_social'] == 1) ? $PTMPL['email_social_on']  = 'selected' : $PTMPL['email_social_off'] = 'selected'; 
			($settings['email_vote'] == 1) ? $PTMPL['email_vote_on']  = 'selected' : $PTMPL['email_vote_off'] = 'selected'; 
			($settings['email_comment'] == 1) ? $PTMPL['email_comment_on']  = 'selected' : $PTMPL['email_comment_off'] = 'selected'; 
			($settings['email_apply'] == 1) ? $PTMPL['email_apply_on']  = 'selected' : $PTMPL['email_apply_off'] = 'selected';
			($settings['email_welcome'] == 1) ? $PTMPL['email_welcome_on']  = 'selected' : $PTMPL['email_welcome_off'] = 'selected'; 									 
		} elseif (isset($_GET['b'])) {
			if ($_GET['b'] == 'templates') {
				$theme = new themer('admin/templates'); $container = '';
				$PTMPL['this_title'] = $LANG['email_templates'];

			// Manage gift cards and Passcredits
			} elseif ($_GET['b'] == 'giftcards') {

				$PTMPL['get_quantity'] = (isset($_POST['quantity'])) ? $_POST['quantity'] : '5'; 
				$PTMPL['pc_vote'] = $settings['pc_vote']; 
				$PTMPL['pc_comment'] = $settings['pc_comment']; 
				$PTMPL['pc_symbol'] = $settings['pc_symbol']; 
				$PTMPL['pc_enter'] = $settings['pc_enter'];  
				$PTMPL['pc_value'] = $settings['pc_value']; 
				$PTMPL['pc_agent_percent'] = $settings['pc_agent_percent'];  
				$PTMPL['pc_ref_percent'] = $settings['pc_ref_percent'];  
				$PTMPL['currency'] = $settings['currency'];  
				$PTMPL['bonus'] = $settings['signup_bonus'];  
				$PTMPL['cashout'] = $settings['cashout'];
				$PTMPL['cashout_max'] = $settings['cashout_max'];
				$PTMPL['cashout_retain'] = $settings['cashout_retain'];

				$get_buttons=''; 
				$get_buttons.='
			      <button id="fetch" name="fetch" type="submit" class="mr-auto btn btn-sm btn-success my-2 waves-effect">Fetch Tokens</button>';
				$get_buttons.='
			      <button id="voucher" name="voucher" type="submit" class="mr-auto btn btn-sm btn-secondary my-2 waves-effect">Fetch '
			      .$LANG['passcredit'].'</button>';

			    if (isset($_POST['voucher']) || isset($_POST['save_credit'])) {
				    $get_buttons.='
				      <button id="save_credit" name="save_credit" type="submit" class="mr-auto btn btn-sm btn-info my-2 waves-effect">Save '
			      .$LANG['passcredit'].'s</button>';	    	 
			    } else {
				    $get_buttons.='
				      <button id="save_tokens" name="save_tokens" type="submit" class="mr-auto btn btn-sm btn-info my-2 waves-effect">Save</button>';	    	
			    }

			    if (isset($_POST['fetch'])) {
			    	$get_buttons.='
			      		<button id="delete_tokens" name="delete_tokens" type="submit" class="mr-auto btn btn-sm btn-danger my-2 waves-effect">Delete</button>'; 
			    }

				$count_all = $site_class->manage_gift_cards(1);
			    $PTMPL['get_buttons'] = $count_all ? (count($count_all) >=1) ? $get_buttons : '' : '';
				//Generate a gift card token
				$quantity = (isset($_POST['quantity']) && $_POST['quantity']>0) ? $_POST['quantity'] : '5';
				if (isset($_POST['generate'])) {
					$site_class->repeat = $quantity;
					$generate_coupon = $site_class->coupon_generator(); 
				}
				// Get all generated gift cards
				$manage_list = $site_class->manage_gift_cards(7); 
				// Set the select options from gift list
				$listed = '';
				$sql = '';
				if ($manage_list) {
					foreach($manage_list as $list => $key) {
						$not_exist = $site_class->manage_gift_cards(0, $key['contest_id'])[0];
						// Check If a token trace only exist in the gifted table 
						if ($key['contest_id'] !== $not_exist['contest']) {
							$sql .= sprintf("DELETE FROM " . TABLE_GIFTED . " WHERE `contest_id` = '%s'", $key['contest_id']); 
						} 
						$disd = $gett->getContest(0, $key['id']);
						$listed .= '<option value="'.$key['contest_id'].'">'.$key['contest'].'</option>';
						
						// If a token trace only exist in the gifted table, delete it
						if (isset($sql)) {
						 	dbProcessor($sql, 0);
						}
					}  
				}
				$PTMPL['gift_list'] =  $listed;
				// Veiw generated tokens
				if (isset($_POST['fetch']) || isset($_POST['save_tokens']) || isset($_POST['voucher']) || isset($_POST['save_credit'])) {
					$processor = 1;
				}
				if (isset($processor)) {
					$quantity = ($_POST['quantity']>0) ? $_POST['quantity'] : '5';
					$PTMPL['tab1'] = 'active show';
					if (isset($_POST['voucher']) || isset($_POST['save_credit'])) {
						$count_this = count($site_class->manage_gift_cards(0, 0)); 
						$contest_title = $LANG['passcredit'];
						if (isset($quantity)) {
							$site_class->limit = $quantity;
							$site_class->start = '1';
						}				
						$get_cards = $site_class->manage_gift_cards(0, 0);
						$title = 'Generated '.$LANG['passcredit'].'s';
					} else {
						$cntst = isset($_POST['get_contest']) ? $_POST['get_contest'] : $_POST['save_tokens'];

						$view_contest = (isset($cntst)) ? $gett->getContest(0, $cntst) : null; 

						// Count the tokens for this contest
			 			$count_this = count($site_class->manage_gift_cards(0, $cntst)); 
			 			$contest_title = $view_contest['title'];

						if (isset($quantity)) {
							$site_class->limit = $quantity;
							$site_class->start = '1';
						}
						$get_cards = $site_class->manage_gift_cards(0, $cntst);
						$title = ($view_contest) ? 'Tokens Generated for '.$view_contest['title'] : '';
					}
					//Show count of tokens for selection
					$PTMPL['count_this'] = ($count_this >= 0) ? $contest_title.': <span class="text-success">'.$count_this
					.' Tokens</span>' : '<span class="text-danger">No Tokens</span>';	
					
					// Fetch list of tokens	 
					$list_tokens = '';
					if ($get_cards) {
						foreach ($get_cards as $gift => $token) {
							$list_tokens .= '<p><span class="px-3">'.$token['token'].'</span><span class="px-3">'.$token['value'].' '.$settings['currency'].'</span></p>';
						}
					}

					// View list of tokens
					$PTMPL['generated_tokens'] = '
			          <div class="card">
			            <div class="card-header text-white h6">'.$title.'</div>
			            <div class="card-body p-3">
			              '.$list_tokens.'
			            </div>
			          </div> ';

			       	// Save available tokens to file and show a download link    
					if(isset($_POST['save_tokens']) || isset($_POST['save_credit'])) {
						$save_token = '';
						$save_token .= (isset($_POST['save_credit'])) ? $LANG['passcredit']." Tokens \r\n" : $view_contest['title']
						." Tokens \r\n"; 		

						foreach ($get_cards as $token) {
							$save_token .= $token['token']." \r\n";
							unlink(__DIR__."/../uploads/sites/tokens.txt");
							file_put_contents(__DIR__."/../uploads/sites/tokens.txt", $save_token . " \r\n", FILE_APPEND | LOCK_EX);
							$PTMPL['save_link'] = '<a data-toggle="tooltip" title="Download Token File" data-placement="right" href="'.$SETT['url'].'/uploads/sites/tokens.txt" download><i class="fa fa-download fa-3x"></i></a>';
						}
					} 

				} elseif (isset($_POST['save_pricing'])) { 
					$site_class->vote = $_POST['pc_vote'];
					$site_class->comment = $_POST['pc_comment'];
					$site_class->enter = $_POST['pc_enter'];
					$site_class->symbol = $_POST['symbol'];
					$site_class->cashout = $_POST['cashout'];
					$site_class->cashout_max = $_POST['cashout_max'];
					$site_class->cashout_retain = $_POST['cashout_retain'];
					$site_class->value = $_POST['pc_value'];
					$site_class->referral = $_POST['referral'];
					$site_class->agency = $_POST['agency'];
					$site_class->bonus = $_POST['bonus'];
					$PTMPL['message5'] = $site_class->site_settings(8);
					$PTMPL['tab3'] = 'active show'; 
				// Save premium settings	
				} elseif (isset($_POST['value'])) {
					$PTMPL['tab2'] = 'active show';
				// Delete expired tokens 
				} elseif (isset($_POST['del_exp'])) {
					$PTMPL['tab1'] = 'active show';
					$sql = "DELETE FROM " . TABLE_GIFT . " WHERE `invalid_by` < NOW()"; 
					$ret_resp = dbProcessor($sql, 0, 1);
					$PTMPL['ret_resp'] = ($ret_resp == 1) ? successMessage('All expired tokens deleted') : errorMessage($ret_resp);
				// Delete used tokens		
				} elseif (isset($_POST['del_used'])) {
					$PTMPL['tab1'] = 'active show';
					$sql = "DELETE FROM " . TABLE_GIFT . " WHERE `used_by` != ''"; 
					$ret_resp = dbProcessor($sql, 0, 1);
					$PTMPL['ret_resp'] = ($ret_resp == 1) ? successMessage('All used tokens deleted') : errorMessage($ret_resp);
				// Delete tokens for the selected contes		
				} elseif (isset($_POST['delete_tokens']) || isset($_POST['del_voucher'])) {
					$PTMPL['tab1'] = 'active show'; 
					$cntst = (isset($_POST['delete_tokens'])) ? $_POST['get_contest'] : '0';
					$sql = sprintf("DELETE FROM " . TABLE_GIFT . " WHERE `contest` = %s", $cntst); 
					$ret_resp = dbProcessor($sql, 0, 1);  
					$PTMPL['ret_resp'] = ($ret_resp == 1) ? successMessage('Tokens deleted') : errorMessage($ret_resp); 
				// Delete all available tokens
				} elseif (isset($_POST['del_all'])) {
					$PTMPL['tab1'] = 'active show';
					$sql = "DELETE FROM " . TABLE_GIFT . " WHERE 1"; 
					$ret_resp = dbProcessor($sql, 0, 1);
					$PTMPL['ret_resp'] = ($ret_resp == 1) ? successMessage('All expired tokens deleted') : errorMessage($ret_resp);	
					$sql = "DELETE FROM " . TABLE_GIFTED . " WHERE 1"; 
					$ret_resp = dbProcessor($sql, 0, 1);	
				// If no action is selected set the default active tab
				} else {
					$PTMPL['tab'] = 'active show'; 		
				}

				$theme = new themer('admin/giftcards'); $container = '';
				$PTMPL['this_title'] = $LANG['gift_cards']; 

				// Set the select options from contest list
				$return = '';
				if ($contests) {
					foreach($contests as $list => $key) { 
						$return .= '<option value="'.$key['id'].'">'.$key['title'].'</option>';
					}
					$PTMPL['generate_token'] = '<button name="generate" id="save2" class="btn btn-info btn-rounded my-2 waves-effect" type="submit" >Generate</button>';
				}

				$PTMPL['contest_list'] =  $return;

				// Manage the cards
				$card_list = $site_class->manage_gift_cards(1);
				$today = date("Y-m-d H:i:s");
				$site_class->what = '`invalid_by` < NOW()';
				$invalid_list = $site_class->manage_gift_cards(8); 
				$site_class->what = '`used_by` != \'\'';
				$used_list = $site_class->manage_gift_cards(8);
				 
				// Count all tokens
				$PTMPL['count_all'] = $count_all ? (count($count_all) >= 1) ? ' Total tokens: <span class="text-success">'.count($count_all).'</span>' : '<span class="text-danger">No Tokens</span>' : '';
				// Cont expired tokens
				$PTMPL['count_invalid'] = $invalid_list ? (count($invalid_list) >= 1) ? ' Expired tokens: <span class="text-danger">'.count($invalid_list).'</span>' : '<span class="text-success">No Expired Tokens</span>' : '';
				// Cont contests with tokens
				$PTMPL['count_cwt'] = $manage_list ? (count($manage_list) >= 1) ? ' Contests with tokens: <span class="text-success">'.count($manage_list).'</span>' : '<span class="text-danger">No Contests with Tokens</span>' : '';
				// Cont used tokens
				$PTMPL['count_used'] = $used_list ? (count($used_list) >= 1) ? ' Used tokens: <span class="text-success">'.count($used_list).'</span>' : '<span class="text-danger">No used Tokens</span>' : '';

				 
				// $days_days = floor((strtotime($invalid_list['invalid_by']) - strtotime(date("Y-m-d H:i:s")))/(60*60*24)); 
 				// How long will the generated token grant access
 				if (isset($_POST['generate'])) { 
					if ($_POST['valid'] == 1) {
						$date = date("Y-m-d H:m:s", strtotime("+1 day"));
					} elseif ($_POST['valid'] == 2) {
						$date = date("Y-m-d H:m:s", strtotime("+1 week")); 
					} elseif ($_POST['valid'] == 3) {
						$date = date("Y-m-d H:m:s", strtotime("+1 month +2 days")); 
					} elseif ($_POST['valid'] == 4) {
						$date = date("Y-m-d H:m:s", strtotime("+3 months +1 day")); 
					} elseif ($_POST['valid'] == 5) {
						$date = date("Y-m-d H:m:s", strtotime("+6 months +12 hours")); 
					} elseif ($_POST['valid'] == 6) {
						$date = date("Y-m-d H:m:s", strtotime("+1 year +6 hours")); 
					} 
					// When will the generated tokens expire
					if ($_POST['invalid'] == 1) {
						$expires = date("Y-m-d H:m:s", strtotime("+1 day"));
					} elseif ($_POST['invalid'] == 2) {
						$expires = date("Y-m-d H:m:s", strtotime("+1 week")); 
					} elseif ($_POST['invalid'] == 3) {
						$expires = date("Y-m-d H:m:s", strtotime("+1 month +2 days")); 
					} elseif ($_POST['invalid'] == 4) {
						$expires = date("Y-m-d H:m:s", strtotime("+3 months +1 day")); 
					} elseif ($_POST['invalid'] == 5) {
						$expires = date("Y-m-d H:m:s", strtotime("+6 months +12 hours")); 
					} elseif ($_POST['invalid'] == 6) {
						$expires = date("Y-m-d H:m:s", strtotime("+1 year +6 hours")); 
					}  
					$value = (isset($_POST['value'])) ? $_POST['value'] : '0.00';
 
					// Set the quantity of tokens to generate
					$quantity = ($_POST['quantity']>0) ? $_POST['quantity'] : '5';
					for ($i=0; $i <= $quantity ; $i++) { 
						$site_class->contest = $_POST['contest'];
						$site_class->token = $site_class->coupon_generator(); 
						$site_class->expires = $expires;
						$site_class->valid = $date;
						$site_class->value = $value;
						echo $site_class->manage_gift_cards(4);
					}
					// Manage the generated tokens
					$card = $site_class->manage_gift_cards(7, $_POST['contest'])[0];
					$cont = $gett->getContest(0, $_POST['contest']); 
					if ($card['contest_id'] !== $cont['id']) {
						$site_class->contest = $_POST['contest'];
						$site_class->contest_title = $cont['title'];
						$site_class->manage_gift_cards(6);
					}
					(isset($_POST['value'])) ? $PTMPL['message1'] = successMessage('You successfully generated '.$quantity
					.' '.$LANG['passcredit'].'s') : $PTMPL['message'] = successMessage('You successfully generated '.$quantity.' Gift card coupons');
 				}	
 			// Premium settings for site
			} elseif ($_GET['b'] == 'premium') { 
				$theme = new themer('admin/premium'); $container = '';
				$PTMPL['this_title'] = $LANG['premium']; 
				// Toggle sandbox and live
				if ($settings['rave_mode'] == 0) {
					$PTMPL['rave_sand'] = 'selected';
				} else {
					$PTMPL['rave_live'] = 'selected';
				}
				// Fetch the premium settings from db
				$PTMPL['rave_public'] = $settings['rave_public_key'];
				$PTMPL['rave_private'] = $settings['rave_private_key'];
				$PTMPL['rave_enc'] = $settings['rave_encryption_key'];
				$PTMPL['currency'] = $settings['currency'];
				$PTMPL['votes'] = $settings['premium_votes'];
				$PTMPL['premium_vp'] = $settings['premium_plan'];
				$PTMPL['clead_p'] = $settings['clead_plan'];
				$PTMPL['cmarx_p'] = $settings['cmarx_plan'];
				$PTMPL['slight_p'] = $settings['slight_plan'];
				$PTMPL['lite_p'] = $settings['lite_plan'];
				$PTMPL['life_p'] = $settings['life_plan'];
				// Save Ravepay settings
				if (isset($_POST['save_settings'])) {
					$site_class->mode = $_POST['mode'];
					$site_class->public = $_POST['public'];
					$site_class->private = $_POST['private'];
					$site_class->encryption = $_POST['encryption'];
					$PTMPL['message'] = $site_class->site_settings(5);
					$PTMPL['tabs'] = 'active show'; 
				// Save premium settings	
				} elseif (isset($_POST['save'])) {
					$site_class->currency = $_POST['currency'];
					$site_class->votes = $_POST['votes'];
					$site_class->premium = $_POST['premium'];
					$site_class->clead = $_POST['clead'];
					$site_class->cmarx = $_POST['cmarx'];
					$site_class->slight = $_POST['slight'];
					$site_class->lite = $_POST['lite'];
					$site_class->life = $_POST['life'];
					$PTMPL['ret_resp'] = $site_class->site_settings(6);
					$PTMPL['tabs1'] = 'active show'; 	
				} else {
					$PTMPL['tabs'] = 'active show'; 
				}
			} elseif ($_GET['b'] == 'static') {
				// Get all static pages
				$theme = new themer('admin/static_pages'); $container = '';
				$PTMPL['this_title'] = 'Static Pages'; 
				$get_pages = $site_class->static_pages(0, 0);

				// Edit the selected static page
				if (isset($_GET['edit']) || isset($_GET['delete'])) {
					$the_id = (isset($_GET['edit'])) ? $_GET['edit'] : $_GET['delete'];
					$site_class->what = sprintf("id = '%s'", $the_id);
					$manage_page = $site_class->static_pages(0, 0)[0];
					if ($manage_page['status'] == 1) {
						$PTMPL['page_status_on'] = 'selected';
					} else {
						$PTMPL['page_status_off'] = 'selected';
					} 
					$PTMPL['page_title'] = stripslashes($manage_page['title']);
					$PTMPL['page_alias'] = stripslashes($manage_page['link']);
					$PTMPL['page_content'] = stripslashes($manage_page['content']);
					$PTMPL['button'] = '<button name="edit" type="submit" class="btn btn-info my-2 waves-effect" id="save">Save</button>';
					$PTMPL['create_button'] = '<< <a class="px-2" href="'.permalink($SETT['url'].'/index.php?a=settings&b=static').'">Create New Page</a> >>';
				} else {
					$PTMPL['button'] = '<button name="save" type="submit" class="btn btn-info my-2 waves-effect" id="create">Create</button>';
				}
				$pages = '';
				if ($get_pages) {
					foreach($get_pages as $list => $key) { 
						$pages .= '<div class="p-1">' .$key['title'].' <a class="px-2" href="'.permalink($SETT['url'].'/index.php?a=settings&b=static&edit='.$key['id']).'">Edit <i class="fa fa-edit text-info"></i></a> <a class="px-2" href="'.permalink($SETT['url'].'/index.php?a=settings&b=static&delete='.$key['id']).'">Delete <i class="fa fa-trash text-danger"></i></a><br></div>';
					}					 
				} else {
					$pages = '<h5 class="text-warning text-center">No static pages to show</h5>';
				}	
				$PTMPL['get_pages'] = $pages;

				// Delete an static page
				if (isset($_GET['delete'])) { 
					$return = $site_class->static_pages(3, 0, $_GET['delete']);
					$message1 = ($return == 1) ? successMessage('Page deleted successfully') : (($return == 'No changes were made') ? infoMessage('The page no longer exist') : infoMessage($return));
					$PTMPL['message1'] = $message1;
				}
				// Create an new page
				if (isset($_POST['save']) || isset($_POST['edit'])) {
					$site_class->what = sprintf("link = '%s'", $_POST['page_alias']);
					$ver_page = $site_class->static_pages(0, 0)[0];  

					if (empty($_POST['page_title'])) {
						$message = infoMessage('Page Title should not be empty');
					} elseif (empty($_POST['page_alias'])) {
						$message = infoMessage('Page Alias should not be empty');
					} elseif (!isset($_POST['edit']) && $ver_page['link'] == $_POST['page_alias']){
						$message = infoMessage('Page Alias should be unique');
					} else {
						$site_class->link = $_POST['page_alias'];
						$site_class->title = $_POST['page_title'];
						$site_class->content = $_POST['page_content'];
						$site_class->status = $_POST['page_status'];
						if (isset($_POST['save'])) {
							$return = $site_class->static_pages(1, 0);
							$message = ($return == 1) ? successMessage('Page created successfully, Please refresh') : infoMessage($return);
						} elseif (isset($_POST['edit'])) { 
							$return = $site_class->static_pages(2, 1, $manage_page['id']);
							$message = ($return == 1) ? successMessage('Page updated successfully, Please refresh') : infoMessage($return);
						}
					}
					$PTMPL['message'] = $message;
				}
			// Manage users
			} elseif ($_GET['b'] == 'users') {
				// Get all users
				$theme = new themer('admin/users'); $container = '';
				$PTMPL['this_title'] = 'Manage Users';
				$PTMPL['button'] = (isset($_GET['edit'])) ? '<button name="edit_user" type="submit" class="btn btn-info my-2 waves-effect" id="edit_user">Edit User</button>' : ''; 
				$userApp->user_id = (isset($_POST['fetch_id'])) ? $_POST['enter_id'] : (isset($_GET['edit']) ? $_GET['edit'] : null);
				$userdata = $userApp->userData(NULL, 1)[0];
				$PTMPL['username'] = $userdata['username']; 
				$PTMPL['email'] = $userdata['email'];

				// Get the selected users status
				if ($userdata['status'] == 0) {
					$PTMPL['status_unv'] = 'selected';
				} elseif ($userdata['status'] == 1) {
					$PTMPL['status_sus'] = 'selected';
				} elseif ($userdata['status'] == 2) {
					$PTMPL['status_act'] = 'selected';
				}
				($userdata['featured'] == 0) ? $PTMPL['featured_off'] = 'selected' : $PTMPL['featured_on'] = 'selected';

				// Update the selected user data
				if (isset($_POST['edit_user'])) {
					$userApp->username = $_POST['username'];
					$userApp->email = $_POST['email'];
					$userApp->status = $_POST['status'];
					$userApp->featured = $_POST['featured'];
					$userApp->update = 'admin';
					$msg = $userApp->updateProfile($_GET['edit']);
					$PTMPL['message'] = $msg;
				}
				// Change the password
				if (isset($_POST['password'])) {
					$userApp->password = hash('md5', $_POST['password']);
					$userApp->update = 'password';
					$userApp->updateProfile($_GET['edit']);
				}	
				// Delete user data
				if (isset($_GET['delete'])) {
					$return = $userApp->deleteUser($_GET['delete']); 
					$PTMPL['message1'] = $return;	 
				}
				// Promote the selected user to premium
				if (isset($_GET['promote'])) {
					$premium_check = $userApp->premiumStatus($_GET['promote'], 1);
					
					$promtion_form = '
					  <small id="possible_helper" class="border border-info p-2 form-text text-muted mb-2 text-justify"> 
					  	<strong> 
					      <form style="color: #757575;" action="" method="post" class="form-inline"> 
					        <div class="col"> 
				             <select name="plan" id="plan" class="text-left mdb-select md-form colorful-select dropdown-primary"> 
				                <option disabled>Promote User</option>
				                <option value="premium_plan">'.$LANG['premium_vp'].'</option>
				                <option value="clead_plan">'.$LANG['clead_p'].'</option>
				                <option value="cmarx_plan">'.$LANG['cmarx_p'].'</option>
				                <option value="slight_plan">'.$LANG['slight_p'].'</option>
				                <option value="lite_plan">'.$LANG['lite_p'].'</option>
				                <option value="life_plan">'.$LANG['life_p'].'</option>
				              </select> 
				              <label for="plan">Promote User</label>
					          <button name="promote" type="submit" class="btn btn-primary btn-sm mb-0">Promote</button>
					        </div>  
					      </form>
					    </strong>
				      </small>';

				    $PTMPL['promtion_form'] = $promtion_form; 
				    if (isset($_POST['promote']) && !$premium_check) {
				    	$plan = $_POST['plan'];
				    	if ($settings['premium']) { 
							$rave_api->today_date = date("Y-m-d H:m:s"); // Todays date 
							$rave_api->exp_date = date("Y-m-d H:m:s", strtotime("+6 month")); // Expiry date
							// Variables to pass to database
							$rave_api->payer_id		= $_GET['promote'];
							$rave_api->payment_id	= 'Promoted';
							$rave_api->price		= 0;
							$rave_api->currency	 	= 'NGN';
							$rave_api->plan		 	= $plan; 
							$rave_api->pfn 		 	= 'Promoted';
							$rave_api->pln		 	= 'Promoted';
							$rave_api->email		= 'Promoted';
							$rave_api->country	 	= 'Promoted';
							$rave_api->order_ref 	= 'Promoted';
							$response = $rave_api->promote_user(0);	
							if ($response == 1) {
								//header("Location: ".$SETT['url']."/index.php?a=settings&b=users&promote=".$_GET['promote']."&ret=true");
							}	    		 
				    	} else {
				    		$PTMPL['promtion_form'] = infoMessage('Please enable premium accounts first');
				    	}									    	 
				    }
				    $PTMPL['message1'] = (isset($_GET['ret'])) ? successMessage('User Promoted successfully') : '';
				}	
			// Cashout Requests	 
			} elseif ($_GET['b'] == 'cashout') {
				$theme = new themer('admin/cashout'); $container = '';
				// Aprrove this request
				if (isset($_GET['approve'])) {
					$userApp->admin = '`approved` = \'1\'';
					$userApp->status = 'success';
					$ret = $userApp->set_bank(3, $_GET['approve']); 
					$PTMPL['message1'] = ($ret == 1) ? successMessage('Cashout request was approved successfully') : infoMessage($ret);	
				} elseif (isset($_GET['paid'])) {
					$userApp->admin = '`approved` = \'2\', `cashout` = \'0\'';
					$userApp->status = 'paid';
					$ret = $userApp->set_bank(3, $_GET['paid']);
					$PTMPL['message1'] = ($ret == 1) ? successMessage('Cashout request marked as paid') : infoMessage($ret);
				} elseif (isset($_GET['decline'])) {
					$userApp->admin = '`approved` = \'0\', `cashout` = \'0\'';
					$userApp->status = 'declined';
					$ret = $userApp->set_bank(3, $_GET['decline']); 
					$PTMPL['message1'] = ($ret == 1) ? successMessage('Cashout request was declined') : infoMessage($ret);
				}				
			// Manage conests
			} elseif ($_GET['b'] == 'contests') {
				$theme = new themer('admin/contests'); $container = '';
				$PTMPL['this_title'] = 'Manage Contests';
				$PTMPL['button'] = (isset($_GET['edit'])) ? '<button name="edit_contest" type="submit" class="btn btn-info my-2 waves-effect" id="edit_contest">Edit Contest</button>' : '';
				// Get the selected contest info
				$contest_id = (isset($_POST['fetch_id'])) ? $_POST['enter_id'] : (isset($_GET['edit']) ? $_GET['edit'] : null);
				if (isset($contest_id)) {
					$contest = $gett->get_all_Contest($contest_id);
					$PTMPL['title'] = $contest['title'];
					$PTMPL['alias'] = $contest['safelink'];
					($contest['status'] == 0) ? $PTMPL['blocked'] = 'selected' : $PTMPL['block_off'] = 'selected';
					($contest['featured'] == 0) ? $PTMPL['featured_off'] = 'selected' : $PTMPL['featured'] = 'selected';
					($contest['recommend'] == 0) ? $PTMPL['recommend_off'] = 'selected' : $PTMPL['recommend'] = 'selected';

					// Update the contest details 
					if (isset($_POST['edit_contest'])) {
						$gett->title = $_POST['title'];
						$gett->safelink = $_POST['alias'];
						$gett->status = $_POST['status'];
						$gett->featured = $_POST['featured'];
						$gett->recommend = $_POST['recommend'];
						$gett->update = 'admin';
						$return = $gett->addContest($contest_id); 
						$r = urlencode($return);
						if($return) {
							header("Location: ".$SETT['url']."/index.php?a=settings&b=contests&ret=true&resp=".$r);
						} 						
					}				 
				}
				$pass = fetch_api(2);
				if (isset($_GET['ret']) && $_GET['ret']==true) {
					$PTMPL['message'] = urldecode($_GET['resp']);
				} elseif (isset($_GET['ret']) && $_GET['ret']==false) {
					$PTMPL['message'] = infoMessage($_GET['resp']);
				}
				if (isset($_GET['delete'])) {
					$PTMPL['message'] = $gett->deleteContest($_GET['delete']);
				}
			} elseif ($_GET['b'] == 'payments') {
				$theme = new themer('admin/payments'); $container = '';
				$PTMPL['this_title'] = 'Manage Payments';
				$PTMPL['button'] = (isset($_GET['edit']) || isset($_POST['fetch_id'])) ? '<button name="edit_payment" type="submit" class="btn btn-info my-2 waves-effect" id="edit_contest">Update</button>' : '';
				// Get the selected payment info
				$pay_id = (isset($_POST['fetch_id'])) ? $_POST['enter_id'] : (isset($_GET['edit']) ? $_GET['edit'] : null);
				if (isset($pay_id)) {
					(isset($_POST['fetch_id'])) ? $userApp->extra = sprintf('trx_id = \'%s\'', $pay_id) : null;
					$pmnt = $userApp->premiumUsers($pay_id, 1)[0];  
					(isset($pmnt['status']) && $pmnt['status'] == 0) ? $PTMPL['status_sus'] = 'selected' : $PTMPL['status_act'] = 'selected'; 
					$PTMPL['detail'] = '<span class="border border-info p-2 bg-light">Now Editing '.$pmnt['payer_firstname'].' '.$pmnt['payer_lastname'].'</span>';	
				}				
				// Update the payment status
				if (isset($_POST['edit_payment'])) {
					$userApp->status = $_POST['status']; 
					$PTMPL['message'] = $userApp->premiumUsers($pmnt['payer_id'], 2);
				}
				if (isset($_GET['delete'])) {
					 $PTMPL['message1'] = $userApp->premiumUsers($_GET['delete'], 3);
				}
			} elseif ($_GET['b'] == 'site_templates') { 
				$theme = new themer('admin/site_templates'); $container = '';
				$PTMPL['this_title'] = 'Site Templates';
				$PTMPL['other_title'] = 'Other Site Templates Settings';
				isset($_POST['save']) || isset($_POST['upload']) ? $PTMPL['active_2'] = ' active show' : $PTMPL['active_1'] = ' active show'; 

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

				$get_templates = $site_class->fetch_templates(0);
				// Set the active site template
				$PTMPL['templates'] = $get_templates[1];  
				if (isset($_GET['template'])) {
					if (in_array($_GET['template'], $get_templates[0])) {
						$site_class->template = $_GET['template'];  
						$return = $site_class->fetch_templates(1);

						if($return == 1) {
							header("Location: ".$SETT['url']."/index.php?a=settings&b=site_templates&ret=true");
						} else {
							header("Location: ".$SETT['url']."/index.php?a=settings&b=site_templates&ret=false");
						}
					}
				}
				if (isset($_GET['ret']) && $_GET['ret']==true) {
					$PTMPL['message1'] = successMessage($LANG['looks_good']);
				} elseif (isset($_GET['ret']) && $_GET['ret']==false) {
					$PTMPL['message1'] = infoMessage($LANG['update_failed']);
				}
				// Fetch the list of all available skins
				$PTMPL['skins'] = $site_class->set_skin(0);
				// Update the current skin and landing page settings
				if (isset($_POST['set_skinner'])) {
					$site_class->skin = $_POST['skin'];
					$site_class->landing = $_POST['landing'];
					$result = $site_class->set_skin(1);
					if ($result == 1) {
						$PTMPL['message'] = successMessage('Skin and landing page updated');
					} else {
						$PTMPL['message'] = infoMessage($result);
					} 
				}
				// Set the selected template
				$settings['landing'] == 1 ? $PTMPL['l_1'] = ' selected="selected"' : $PTMPL['l_2'] = ' selected="selected"'; 

				// Upload the file image
				if (isset($_POST['upload'])) { 
					$PTMPL['messageX'] = $site_class->site_uploader($_POST['type']);
				}

				// Save the welcome texts
				if (isset($_POST['save'])) {
					$PTMPL['messageXY'] = $site_class->update_welcome();
				}

			} elseif ($_GET['b'] == 'languages') { 
				$theme = new themer('admin/languages'); $container = '';
				$PTMPL['this_title'] = 'Site Languages';
				$get_lang = $site_class->list_languages(0);
				$PTMPL['languages'] = $get_lang[1];  
				if (isset($_GET['language'])) {
					if (in_array($_GET['language'], $get_lang[0])) {
						$site_class->language = $_GET['language'];  
						$return = $site_class->list_languages(1);

						if($return == 1) {
							header("Location: ".$SETT['url']."/index.php?a=settings&b=languages&ret=true");
						} else {
							header("Location: ".$SETT['url']."/index.php?a=settings&b=languages&ret=false");
						}
					}
				}
				if (isset($_GET['ret']) && $_GET['ret']==true) {
					$PTMPL['message1'] = successMessage($LANG['looks_good']);
				} elseif (isset($_GET['ret']) && $_GET['ret']==false) {
					$PTMPL['message1'] = infoMessage($LANG['update_failed']);
				}
			} elseif ($_GET['b'] == 'password') { 
				$theme = new themer('admin/password'); $container = '';
				$PTMPL['this_title'] = 'Admin Password';
				$admin = $userApp->site_admin(0)[0];
				if (isset($_POST['change'])) {
					if ($_POST['old_password'] == $admin['password']) {
						$message = 'Old password is incorrect';
					} elseif ($_POST['repeat_password'] !== $_POST['new_password']) {
						$message = 'Passwords do not match';
					} elseif (mb_strlen($_POST['new_password']) < 6) {
						$message = 'New password is too short';
					} else {
						$userApp->admin = '1';
						$userApp->password = hash('md5', $_POST['new_password']);
						$ret = $userApp->site_admin(1);
						if($ret == 1) {
							header("Location: ".$SETT['url']."/index.php?a=settings&b=password&ret=true");
						}						 
					}
				}
				if (isset($message)) {
					$PTMPL['message'] = infoMessage($message);
				} elseif (isset($ret)) {
					$PTMPL['message'] = infoMessage($ret);
				} elseif (isset($_GET['ret'])) {
					$PTMPL['message'] = successMessage($LANG['pass_changed']);
				} 

			// Manage ads
			} elseif ($_GET['b'] == 'ads') {
				$theme = new themer('admin/ads'); $container = '';
				$PTMPL['this_title'] = 'Ads Settings';
				($settings['ads_off']) ? $PTMPL['all_ads_on'] = 'selected' : $PTMPL['all_ads_off'] = 'selected';
				$PTMPL['ads_unit_1'] = $settings['ads_1'];
				$PTMPL['ads_unit_2'] = $settings['ads_2'];
				$PTMPL['ads_unit_3'] = $settings['ads_3'];
				$PTMPL['ads_unit_4'] = $settings['ads_4'];
				$PTMPL['ads_unit_5'] = $settings['ads_5'];
				$PTMPL['ads_unit_6'] = $settings['ads_6'];

			// Manage support tickets
			} elseif ($_GET['b'] == 'tickets') {
				$PTMPL['this_title'] = $LANG['support_tickets']; 

				if (isset($_GET['reply'])) {
					$theme = new themer('static/support_form'); $support = '';
					$PTMPL['create_button'] = '<button name="reply" type="submit" class="btn btn-info my-2 waves-effect" id="send">Reply</button>';
					$PTMPL['create_button'] .= '<a href="'.permalink($SETT['url'].'/index.php?a=settings&b=tickets').'" class="btn btn-info my-2 waves-effect" id="ret">Return</a>';
		 
					if (isset($_POST['reply'])) {
						// Prepare for validation
						$site_class->what = sprintf('message = \'%s\' AND user_id = \'%s\'', $_POST['message'], $_GET['sender']);
						$ver = $site_class->support_system(0)[0];

						// Create a new support ticket
						if (empty($_POST['subject'])) {
							$message = infoMessage('Subject is empty');
						} elseif (empty($_POST['message'])) {
							$message = infoMessage('Message is empty');
						} elseif ($_POST['message'] == $ver['message']) {
							$message = infoMessage('This ticket has been sent before');
						} else {
							$site_class->subject = $_POST['subject'];
							$site_class->message = $_POST['message'];
							$site_class->priority = $_POST['priority'];
							$site_class->type = $_POST['type']; 
							$ret = $site_class->support_system(1, $_GET['reply']); 
							$message = ($ret == 1) ? successMessage('Ticket Sent') : infoMessage($ret);					
						}
						$PTMPL['message_'] = $message;
					}	 
				} else {
					$theme = new themer('static/support'); $container = '';
					$site_class->what = sprintf('1 AND reply=\'0\'', $user['id']);
					$messages = $site_class->support_system(0);

					$review = ''; 
					$get_reply = '';
					if ($messages) { 
						$pp = 0;
					    foreach ($messages as $rs => $key) {
							$pp = $pp+1;
							$mtime = $marxTime->timeAgo(strtotime($key['date']));

							// Get user data
							$userApp->user_id = $key['user_id'];
							$data = $userApp->userData(NULL, 1)[0];

							// Fetch Replys
							$site_class->what = sprintf('reply = \'%s\' ORDER BY priority DESC, date DESC', $key['id']);
							$replies = $site_class->support_system(0);

							if ($replies) {
								foreach ($replies as $reply => $rs) { 
									$rtime = $marxTime->timeAgo(strtotime($rs['date']));
									// Get user data
									$userApp->user_id = $rs['user_id'];
									$d = $userApp->userData(NULL, 1)[0];

									$replier = ($rs['user_id'] == $user['id'] && $admin) ? 'You Replied' : $d['username'].' replied';
									$cc = ($rs['user_id'] == $user['id'] && !$admin) ? 'success' : 'danger';
									$get_reply .='
								    <div class="d-inline-block text-left grey lighten-4 border m-1 float-right" style="max-width:90%; min-width:90%" id="ticket_'.$rs['id'].'">
								    	<div class="float-left font-weight-bold px-2 teal-text">'.$replier.'
								    		<div class="text-'.$cc.'">'.$rs['subject'].'</div>
								    		<a onclick="delete_the('.$rs['id'].', 6)"><i class="fa fa-times text-danger"></i></a>
								    		<small class="text-info">'.$rtime.'</small>
								    	</div>
								    	'.$rs['message'].' 
								    </div> <div class="clearfix"></div>
								    <span id="set-message_'.$rs['id'].'"></span>'; 
								}
							} else {
								$get_reply ='';
							}

							$review .=' 
							  <div class="p-2 my-2 bg-white border text-left" id="ticket_'.$key['id'].'">  
							    <div class="d-inline-block float-left font-weight-bold px-2"> '.$key['subject'].'
							    	<br><span class="teal-text p-0 m-0">'.$data['username'].'</span>
							      	<br><small class="text-info">'.$mtime.'</small><br>
							      	<small>Priority: '.$key['priority'].'</small><br>
								    <a href="'.permalink($SETT['url'].'/index.php?a=settings&b=tickets&reply='.$key['id']).'&sender='.$key['user_id'].'">
									    <div class="hoverable d-inline-block light-blue lighten-1 border border-primary rounded m-1 float-left white-text p-1 font-weight-normal">
									   		Reply
									    </div>
									</a>
								    <a onclick="delete_the('.$key['id'].', 6)">
									    <div class="hoverable d-inline-block red lighten-2 border border-danger rounded m-1 float-left white-text p-1 font-weight-normal">
									   		Close
									    </div>
									</a>								      
							    </div>
							    <div class="font-weight-normal">
							    	'.$key['message'].'
							    </div>
							      '.$get_reply.'
							    <div class="clearfix"></div>
							  </div>
							  <span id="set-message_'.$key['id'].'"></span>'; ; 
					    }
					} else {
						$review .= '<h2 class="d-flex justify-content-center text-center text-info p-5">No tickets!</h2>';
					} 

					$PTMPL['review'] = $review;			
				}
 
			}
			
		} 
		 
		if(isset($_GET['logout'])) { 
			unset($_SESSION['admin_username']);
			unset($_SESSION['admin_password']);
	        header('Location: '.permalink($SETT['url'].'/index.php?a=admin'));
		}  
		
		$container = $theme->make();

		$PTMPL = $PTMPL_old; unset($PTMPL_old);
		$PTMPL['container'] = $container;

		$theme = new themer('admin/container');
		return $theme->make();	
	} else {
		header('Location: '.permalink($SETT['url'].'/index.php?a=admin'));
	}
}
?>
