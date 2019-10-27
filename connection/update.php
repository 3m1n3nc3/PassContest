<?php
require_once(__DIR__ .'/../includes/autoload.php');
$userid = $user['id'];

$gett = new contestDelivery;

// Check the users premium status
$user_prem = $userApp->premiumStatus(null, 2);
$user_prem_check = $userApp->premiumStatus(null, 1); 
$prem_val = badge(null, $user_prem['plan'], 3); 

if ($settings['premium']) {
	// Check if user has an active subscription
	if ($user_prem_check) {
        if ($prem_val == 7) {
        	$limiter = 1 ; //If the user has a free_plan drop a limit 
        } else {
            $limiter = 0;
        }
	} else {
		$limiter = 1 ; //If the user has an expired plan 
	}
} else {
    $limiter = 0; //If premium is off
}
$err = '<span class="text-warning">'.$LANG['not_supported'].'</span>';

// Controls for the notification switches
if (isset($_POST['action'])) {
	echo ($_POST['type'] == 1) ? ($limiter) ? $err : $gett->activateItem($userid, 0, $_POST['s'], 3) : $gett->activateItem($userid, 0, $_POST['s'], 2); 
} else {
	$save = new userCallback;

	if(isset($_POST['save']) && $_POST['save'] == 1) {
	    $firstname = db_prepare_input($_POST['firstname']); 
	    $lastname = db_prepare_input($_POST['lastname']); 
	    $gender = db_prepare_input($_POST['gender']); 
		$city = db_prepare_input($_POST['city']); 
		$state = db_prepare_input($_POST['state']); 
		$country = db_prepare_input($_POST['country']); 
		$phone = db_prepare_input($_POST['phone']); 
		$address = db_prepare_input($_POST['address']);

		// Validate
		if ($firstname == '') { 
			echo infoMessage('First Name can not be empty');
		} elseif ($lastname == '') {  
			echo infoMessage('Last Name can not be empty');  
		} else {
			$save->firstname = $firstname;
			$save->lastname = $lastname;
			$save->gender = $gender;
			$save->city = $city;
			$save->state = $state;
			$save->country = $country;
			$save->phone = $phone;
			$save->address = $address;
			$save->update = 1;
			echo $save->updateProfile($userid); 
		} 
	} elseif(isset($_POST['save']) && $_POST['save'] == 2) {
		$profession = db_prepare_input($_POST['profession']); 
		$facebook = db_prepare_input($_POST['facebook']);
		$twitter = db_prepare_input($_POST['twitter']);
		$instagram = db_prepare_input($_POST['instagram']);
		$lovesto = db_prepare_input($_POST['lovesto']);
		$intro = db_prepare_input($_POST['intro']); 

		$save->profession = $profession;
		$save->facebook = $facebook;
		$save->twitter = $twitter;
		$save->instagram= $instagram;
		$save->lovesto= $lovesto;
		$save->intro = $intro;	
		$save->update = 2;
		echo $save->updateProfile($userid); 
	} else {
		$save->paypal = db_prepare_input($_POST['paypal']);  
		$save->bank = db_prepare_input($_POST['bank']); 
		$save->bank_address = db_prepare_input($_POST['bank_address']); 
		$save->sort = db_prepare_input($_POST['sort']);  
		$save->account_name = db_prepare_input($_POST['account_name']); 
		$save->account_number = db_prepare_input($_POST['account_number']); 
		$save->routing = db_prepare_input($_POST['routing']); 
		$b = $save->set_bank(1, $user['id']);
		if ($b == 1) {
			echo successMessage('Bank info saved');
		} else {
			echo infoMessage($b);
		}
	}	 
}

