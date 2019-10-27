<?php
require_once(__DIR__ .'/../includes/autoload.php'); 
$site_class = new siteClass;
$gett = new contestDelivery;

$contest = db_prepare_input($_POST['contest']);
$coupon = db_prepare_input($_POST['coupon']);

// Get the coupons for this Contest
$site_class->token = $coupon;
$giftcard = $site_class->manage_gift_cards(3, $contest)[0]; 
$days_days = floor((strtotime($giftcard['invalid_by']) - strtotime(date("Y-m-d H:i:s")))/(60*60*24));
$pass = fetch_api(2);
$balance = 0;
if ($_POST['action'] == 'validate') {
	// Validate this coupon
	if ($coupon && $coupon == $giftcard['token']) {
		// Check if the card has already been used
		if ($giftcard['used_by']) {
			if ($giftcard['used_by'] == $user['id']) {
				echo '<span class="z-depth-1 p-1 m-2 border blue lighten-5 rounded border-info text-info d-flex justify-content-center">'.sprintf($LANG['token_used'], lcfirst($LANG['you'])).'</span>'; 
			} else {
				echo '<span class="z-depth-1 p-1 m-2 border blue lighten-5 rounded border-info text-info d-flex justify-content-center">'.sprintf($LANG['token_used'], lcfirst($LANG['another_user'])).'</span>'; 
			}
		// Check if the gift card has expired
		} elseif ($days_days < 1) {
			echo '<span class="z-depth-1 p-1 m-2 border red lighten-5 rounded border-danger text-danger d-flex justify-content-center">'.$LANG['expired_gift'].'</span>'; 
		} else {
			echo '<span class="z-depth-1 p-1 m-2 border green lighten-5 rounded border-success text-success d-flex justify-content-center">'.$LANG['looks_good'].'</span>';
		} 
	} else {
		echo '<span class="z-depth-1 p-1 m-2 border red lighten-5 rounded border-danger text-danger d-flex justify-content-center">'.$LANG['invalid_token'].'</span>';
	}
// Add the user to the contest
} elseif ($_POST['action'] == 'enter') {
	// Validate this coupon again
	if ($coupon && $coupon == $giftcard['token']) {
		// Check if the card has already been used
		if ($giftcard['used_by']) {
			if ($giftcard['used_by'] == $user['id']) {
				echo '<span class="z-depth-1 p-1 m-2 border blue lighten-5 rounded border-info text-info d-flex justify-content-center">'.sprintf($LANG['token_used'], lcfirst($LANG['you'])).'</span>'; 
			} else {
				echo '<span class="z-depth-1 p-1 m-2 border blue lighten-5 rounded border-info text-info d-flex justify-content-center">'.sprintf($LANG['token_used'], lcfirst($LANG['another_user'])).'</span>'; 
			}
		// Check if the gift card has expired
		} elseif ($days_days < 1) {
			echo '<span class="z-depth-1 p-1 m-2 border red lighten-5 rounded border-danger text-danger d-flex justify-content-center">'.$LANG['expired_gift'].'</span>'; 
		} else {
			// Send the application request
			$gett->method = 'giftcard';
			$echo = $gett->enterContest($contest);
			// Set the coupon status to used
			$site_class->token_id = $giftcard['id'];
			$site_class->manage_gift_cards(5);
			echo '<span class="z-depth-1 p-1 m-2 border green lighten-5 rounded border-success text-success d-flex justify-content-center">'.$echo.'&nbsp; <span id="waiter" class="font-weight-bold"></span>';
		} 
	} else {
		echo '<span class="z-depth-1 p-1 m-2 border red lighten-5 rounded border-danger text-danger d-flex justify-content-center">'.$LANG['invalid_token'].'</span>';
	}
} elseif ($_POST['action'] == 'credit') {

	// Fetch the users balance
	$site_class->what = sprintf('user = \'%s\'', $user['id']);
  	$get_credit = $site_class->passCredits(0)[0]; 

	// Validate this coupon again
	if ($coupon && $coupon == $giftcard['token']) {
		// Check if the card has already been used
		if ($giftcard['used_by']) {
			if ($giftcard['used_by'] == $user['id']) {
				$echo = '<span class="z-depth-1 p-1 m-2 border blue lighten-5 rounded border-info text-info d-flex justify-content-center">'.sprintf($LANG['token_used'], lcfirst($LANG['you'])).'</span>'; 
			} else {
				$echo = '<span class="z-depth-1 p-1 m-2 border blue lighten-5 rounded border-info text-info d-flex justify-content-center">'.sprintf($LANG['token_used'], lcfirst($LANG['another_user'])).'</span>'; 
			}
		// Check if the gift card has expired
		} elseif ($days_days < 1) {
			$echo = '<span class="z-depth-1 p-1 m-2 border red lighten-5 rounded border-danger text-danger d-flex justify-content-center">'.$LANG['expired_gift'].'</span>'; 
		} else {
			// Send the application request
			$gett->method = 'giftcard'; 
			$quantity = $giftcard['value'] * $settings['pc_value'];
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
				$message = sprintf($LANG['credit_success'], $quantity, $LANG['passcredit'], $giftcard['value'], $settings['currency'], $balance, $settings['pc_symbol']);  							
			}					
			// Set the coupon status to used
			$site_class->token_id = $giftcard['id'];
			$site_class->manage_gift_cards(5);
			$echo = '<span class="z-depth-1 p-1 m-2 border green lighten-5 rounded border-success text-success d-flex justify-content-center">'.$message.'&nbsp; <span id="waiter" class="font-weight-bold"></span>';
		} 
	} else {
		$echo = '<span class="z-depth-1 p-1 m-2 border red lighten-5 rounded border-danger text-danger d-flex justify-content-center">'.$LANG['invalid_token'].'</span>';
	}
	$response = array('message' => $echo, 'balance' => $balance);
	echo json_encode($response, JSON_UNESCAPED_SLASHES); 
}