<?php
function mainContent() {
	global $PTMPL, $LANG, $SETT, $DB, $user, $settings; 

	// Whole function displays static pages
	$site_class = new siteClass;
	$uc = new userCallback;
	$bars = new barMenus;
	$rave_api = new raveAPI;

	$PTMPL['page_title'] = $LANG['passcredit'];
	$PTMPL['recommended'] = recomendations();
	$PTMPL['adsbar'] = $bars->ads($settings['ads_off']);

  	// Rave payment info
  	$PTMPL['trusted_badge'] = '<img height="auto" width="250px" src="'.$SETT['url'].'/'.$PTMPL['template_url'].'/img/fl_trusted.png">';
	
  	$site_icon = $SETT['url']."/".$PTMPL['template_url']."/img/notification.png";
  	$successful_url	= $SETT['url'].'/connection/raveAPI.php';
  	isset($_SESSION['txref']) ? $reference = $_SESSION['txref'] : $reference = '';

	// Rave API Public key
 	$public_key = $settings['rave_public_key'];
	
	// Rave API Private key 	
	$private_key = $settings['rave_private_key'];
	
	//If is sandbox
	$ravemode = ($settings['rave_mode'] ? 'api.ravepay.co' : 'ravesandboxapi.flutterwave.com');

  	$site_class->what = sprintf('user = \'%s\'', $user['id']);
  	$get_credit = $site_class->passCredits(0)[0];
  	$PTMPL['pc_vote'] = $settings['pc_vote'];
  	$PTMPL['pc_comment'] = $settings['pc_comment'];
  	$PTMPL['pc_enter'] = $settings['pc_enter'];
  	$PTMPL['pc_symbol'] = $settings['pc_symbol'];
  	$PTMPL['currency'] = $settings['currency'];
  	$PTMPL['pc_value_now'] = sprintf($LANG['pc_value_now'], $settings['currency'], $settings['pc_value'], $settings['pc_symbol']); 

	if ($user) {
		$theme = new themer('passcredit/content');
		$PTMPL['balance'] = ($get_credit['balance']) ? $get_credit['balance'] : '0.00 ';
 		$cashout_min = $settings['pc_value'] * $settings['cashout'];
 		$allowed_min = $get_credit['balance'] - ($settings['cashout_retain'] * $get_credit['balance'] / 100);
 		$allowed_max = $get_credit['balance'] - ($settings['cashout_max'] * $get_credit['balance'] / 100);

		// Show the cashout button
		if ($settings['cashout'] > 0) {
			$cashout_message = '';
			$bank = $uc->set_bank(0, $user['id']); 

			if ($get_credit['balance'] >= $cashout_min) {

				// Cashout buttons
				$cashout_btn_2 = '
		        <button name="proceed" type="submit" class="btn btn-success btn-block font-weight-bold">
		          Proceed <i class="fa fa-chevron-right fa-lg px-1"></i>
		        </button>';	

				$cashout_btn_1 = '
		        <button name="request" type="submit" class="btn btn-primary btn-block font-weight-bold">
		          Request Cashout <i class="fa fa-money fa-lg px-1"></i>
		        </button>';	

		        $inpute = '
				<input name="cashout" class="p-2 form-control form-control-sm" type="text" placeholder="'
				.$LANG['cashout_amount'].'">';

				if (isset($_POST['request'])) {
					if (empty($_POST['cashout']) || $_POST['cashout'] <=0) {
						$cashout_message .= errorMessage($LANG['empty_cashout']);
						$cashout_btn = $cashout_btn_1;
						$inpute = $inpute;
					} elseif ($_POST['cashout'] < $allowed_min || $_POST['cashout'] > $allowed_max) {
						$cashout_message .= errorMessage(sprintf($LANG['cashout_lower'], $allowed_min, $allowed_max.' '.$settings['pc_symbol']));

						$cashout_btn = $cashout_btn_1;	
						$inpute = $inpute;
					} elseif (!isset($bank)) {
						$cashout_message .= errorMessage($LANG['update_bank']);
						$cashout_btn = $cashout_btn_1;	
						$inpute = $inpute;
					} else {
						$_SESSION['cashout'] = $_POST['cashout']; 
						$value = $_SESSION['cashout'] / $settings['pc_value']; 
						$value = round($value, 2).' '.$settings['currency'];
						$cashout_message .= infoMessage(sprintf($LANG['cashout_request'], $_POST['cashout'].' '.$settings['pc_symbol'], $value));
						$cashout_btn = $cashout_btn_2;
						$inpute = '';					
					}
				} else {

					// Make the cashout request
					if (isset($_POST['proceed'])) {
						if (isset($_SESSION['cashout'])) {
							$uc->cashout = $_SESSION['cashout'];
							$ret = $uc->set_bank(2, $user['id']);
							$cashout_message .= ($ret == 1) ? successMessage($LANG['cashout_success']) : infoMessage($LANG['cashout_already']);
							$cashout_btn = '';
						} else {
							$cashout_message .= errorMessage('Something went wrong');
							$cashout_btn = $cashout_btn_1;
						}
						unset($_SESSION['cashout']);
					} else {
						$cashout_btn = $cashout_btn_1;
					}				

				}

				$cashout_form = '
				  <div class="text-info p-1 text-center">'
				  .sprintf($LANG['can_cashout'], round($allowed_max, 2).' '.$settings['pc_symbol'])
				  .$cashout_message
				  .'</div>
			      <form method="post" action="">
			        '.$inpute.'
			        <br>
					'.$cashout_btn.'                        
			      </form>';		
			} else {
				$cashout_form = '<h4 class="text-danger text-center">'
				.sprintf($LANG['cashout_min'], $cashout_min.' '.$settings['pc_symbol'])
				.'</h4>';
			}

			$PTMPL['request_cashout'] = '
	        <div class="d-flex flex-column my-2 justify-content-center white border">
	        	<h3 class="mb-0 grey lighten-2 p-2 font-weight-normal text-center">'.$LANG['cashout'].'</h3>
	        	<div class="p-2">
	        		'.$cashout_form.'
	        	</div> 
	        </div>';				 
		}	

		// Process Credit Card payments
		if (isset($_POST['buy'])) {
			$buyer_email = $user['email'];
			$cntr_code = countries(2, $user['country']);

			$plan_name = $LANG['passcredit']; 
			$plan_desc = $_POST['amount'].' '.$settings['site_name'].' '.$LANG['passcredit']; 
			$_SESSION['txref'] = 'PC-'.mt_rand(5,99).'PRTN-'.strtoupper(uniqid(rand(19*94, true))).'-PC';	
			$_SESSION['amount'] = $_POST['amount'];
			$_SESSION['currency'] = $settings['currency'];
			$_SESSION['type'] = 'credit'; 

			// Parameters for Checkout, which will be sent to Rave
			$form_body = "
			  <a class=\"flwpug_getpaid\" 
			  data-PBFPubKey=\"{$public_key}\" 
			  data-txref=\"{$_SESSION['txref']}\" 
			  data-amount=\"{$_SESSION['amount']}\" 
			  data-customer_email=\"{$buyer_email}\" 
			  data-currency=\"{$_SESSION['currency']}\" 
			  data-pay_button_text=\"Pay Now\" 
			  data-payment_method=\"both\"
			  data-custom_description=\"{$plan_desc}\"
			  data-custom_logo=\"{$site_icon}\"
			  data-country=\"{$cntr_code}\"
			  data-redirect_url=\"{$successful_url}\"></a>
			  <script type=\"text/javascript\" src=\"https://".$ravemode."/flwv3-pug/getpaidx/api/flwpbf-inline.js\"></script>
			";	
			$PTMPL['form_body'] = $form_body;			 			
		} elseif(isset($_GET['type']) && $_GET['type'] == 'canceled') {

			// If the payment has been canceled
			$PTMPL['message'] = errorMessage('Error <strong>'.$_GET['status'].'</strong>: '.$_GET['message']); 
		} elseif(isset($_GET['type']) && $_GET['type'] == 'successful') {

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

				// Check if the payment was successful
				if(strtoupper($resp['status']) == "SUCCESS") {

					// Validate payment details on server against payment details on client to Verify if the payment is Completed
					if(($resp['data']['amount'] == $_GET['data']['amount']) && ($resp['data']['paymentid'] == $_GET['data']['paymentid']) && ($resp['data']['orderref'] == $orderref)) {

						// If the payment processing was successful
						if(strtoupper($resp['data']['status']) == "SUCCESSFUL" && strtoupper($_GET['data']['status']) == "SUCCESSFUL") {
							$quantity = $_SESSION['amount'] * $settings['pc_value']; 
							$balance = $quantity + $get_credit['balance']; 


							// Add the users credit value
							if ($get_credit) {
								$site_class->balance = $balance; 
								$return = $site_class->passCredits(1, $user['id']);
							} else { 
								$site_class->balance = $balance; 
								$return = $site_class->passCredits(2, $user['id']);
							}

							if ($return == 1) {
								$message = sprintf($LANG['credit_success'], $quantity, $LANG['passcredit'], 
								$_SESSION['amount'], $settings['currency'], $balance, $settings['pc_symbol']);  		

								$PTMPL['message'] = successMessage($message);					

								// End all sessions
								unset($_SESSION['txref']);	
								unset($_SESSION['amount']);
								unset($_SESSION['currency']);  							
								unset($_SESSION['type']);  							
							}
						} else {
							if (strtoupper($resp['status']) == 'SUCCESS') {
								$PTMPL['message'] = errorMessage('Error: Payment Verification failed');
							} else {
								$PTMPL['message'] = errorMessage('Error '.$resp['status'].': '.$resp['message']);
							}
						} 
					} else {
						if(strtoupper($resp['status']) == 'SUCCESS') {
							$PTMPL['message'] = errorMessage('Error: Information Mismatch');
						} else {
							$PTMPL['message'] = errorMessage('Error '.$resp['status'].': '.$resp['message']);
						}
					}
				} else {
					if(strtoupper($resp['status']) == 'SUCCESS'){ 
						$PTMPL['message'] = errorMessage('Error: Unable to complete payment'); 
					} else {
						$PTMPL['message'] = errorMessage('Error '.$resp['status'].': '.$resp['message']);
					}
				}
			}			 
		}								        
		return $theme->make();
	} else {
		header("Location: ".$SETT['url']);
	}	 
}
?>
