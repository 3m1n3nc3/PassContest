<?php
require_once(__DIR__ .'/../includes/autoload.php');
$user_id = $user['id'];

$gett = new contestDelivery; 
$data = $gett->viewApplications(0, 0, $user['id']);

if(isset($_POST['enter']) || isset($_POST['save'])) {
    $firstname = db_prepare_input($_POST['firstname']); 
    $lastname = db_prepare_input($_POST['lastname']); 
    $gender = db_prepare_input($_POST['gender']); 
	$email = db_prepare_input($_POST['email']); 
	$phone = db_prepare_input($_POST['phone']); 
	$city = db_prepare_input($_POST['city']);
	$zip = db_prepare_input($_POST['zip']);
	$state = db_prepare_input($_POST['state']);
	$country = db_prepare_input($_POST['country']); 
	$address1 = db_prepare_input($_POST['address1']);
	$address2 = db_prepare_input($_POST['address2']);
	$dob = db_prepare_input($_POST['dob']);
	$pob = db_prepare_input($_POST['pob']);
	$height = db_prepare_input($_POST['height']);
	$weight = db_prepare_input($_POST['weight']);
	$swim = db_prepare_input($_POST['swim']);
	$dress = db_prepare_input($_POST['dress']);
	$shoe = db_prepare_input($_POST['shoe']);
	$work = db_prepare_input($_POST['work']);
	$certificate = db_prepare_input($_POST['certificate']);
	$family = db_prepare_input($_POST['family']);

	$twitter = db_prepare_input($_POST['twitter']);
	$instagram = db_prepare_input($_POST['instagram']);
	$food = db_prepare_input($_POST['food']);
	$color = db_prepare_input($_POST['color']);
	$sport = db_prepare_input($_POST['sport']);

	$hobbies = db_prepare_input($_POST['hobbies']);
	$activities = db_prepare_input($_POST['activities']);
	$performing = db_prepare_input($_POST['performing']);
	$ambition = db_prepare_input($_POST['ambition']);
	$awards = db_prepare_input($_POST['awards']);
	$training = db_prepare_input($_POST['training']);
	$languages = db_prepare_input($_POST['languages']);
	$liketomeet = db_prepare_input($_POST['liketomeet']);
	$unusual = db_prepare_input($_POST['unusual']);
	$moment = db_prepare_input($_POST['moment']);
	$traveled = db_prepare_input($_POST['traveled']);

	$statement = db_prepare_input($_POST['statement']);
 
	if ($_POST['agree'] == 'on') {
		 $agree = '1';
	} else {
		$agree = '0';
	}  
	if ($_POST['agree2'] == 'on') {
		 $agree2 = '1';
	} else {
		$agree = '0';
	}
	if ($_POST['agree3'] == 'on') {
		 $agree3 = '1';
	} else {
		$agree = '0';
	}	
 
	$contest_id = isset($_POST['contest_id']) ? $_POST['contest_id'] : '';
}

// Use this query if you are updating the user data
$savesql = sprintf("UPDATE " . TABLE_APPLY . " SET  `firstname` = '%s', `lastname` = '%s', `gender` = '%s', `email` = '%s', 
	`phone` = '%s', `city` = '%s', `zip` = '%s', `state` = '%s', `country` = '%s', `address1` = '%s', `address2` = '%s', 
	`dob` = '%s', `pob` = '%s', `height` = '%s', `weight` = '%s', `swim` = '%s', `dress` = '%s', `shoe` = '%s', `work` = '%s', 
	`certificate` = '%s', `family` = '%s', `twitter` = '%s', `instagram` = '%s', `food` = '%s', `color` = '%s', 
	`sport` = '%s', `hobbies` = '%s', `activities` = '%s', `performing` = '%s', `ambition` = '%s', `awards` = '%s', 
	`training` = '%s', `languages` = '%s', `liketomeet` = '%s', `unusual` = '%s', `moment` = '%s', `traveled` = '%s', 
	`statement` = '%s', `agree` = '%s', `agree2` = '%s', `agree3` = '%s' " 
    . " WHERE `user_id` = '%s'", $firstname, $lastname, $gender, $email, $phone, $city, $zip, $state, $country, $address1, 
    $address2, $dob, $pob, $height, $weight, $swim, $dress, $shoe, $work, $certificate, $family, $twitter, $instagram, $food, 
    $color, $sport, $hobbies, $activities, $performing, $ambition, $awards, $training, $languages, $liketomeet, $unusual, $moment, 
    $traveled, $statement, $agree, $agree2, $agree3, $user_id);

// Use this query if you are inserting new user data
$createsql = sprintf("INSERT INTO " . TABLE_APPLY . " (`user_id`, `firstname`, `lastname`, `gender`, `email`, `phone`, `city`, 
	`zip`, `state`, `country`, `address1`, `address2`, `dob`, `pob`, `height`, `weight`, `swim`, `dress`, `shoe`, `work`,
	 `certificate`, `family`, `twitter`, `instagram`, `food`, `color`, `sport`, `hobbies`, `activities`, `performing`,
	  `ambition`, `awards`, `training`, `languages`, `liketomeet`, `unusual`, `moment`, `traveled`, `statement`, `agree`,
	   `agree2`, `agree3`) VALUES 
			('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
			 '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
			  '%s', '%s', '%s', '%s')", $user_id, $firstname, $lastname, $gender, $email, $phone, $city, $zip, $state, $country, 
			  $address1, $address2, $dob, $pob, $height, $weight, $swim, $dress, $shoe, $work, $certificate, $family, $twitter, 
			  $instagram, $food, $color, $sport, $hobbies, $activities, $performing, $ambition, $awards, $training, 
			  $languages, $liketomeet, $unusual, $moment, $traveled, $statement, $agree, $agree2, $agree3); 

if (isset($_POST['save'])) {
	if ($data['user_id'] == $user['id']) {
		$sql = $savesql;
		$response = $LANG['congrats_saved'];
	}else {
		$sql = $createsql;
		$response = $LANG['congratulations'];
	}
} else {
	if ($data['user_id'] == $user['id']) {
		$sql = $savesql;
		$response = $LANG['congrats_saved'];
	}else {
		$sql = $createsql;
		$response = $LANG['congratulations'];
	}
}
$return = dbProcessor($sql, 0, $response);
$msg = easy_crypt($return); 

if (isset($_POST['save'])) {
	header("Location: ".permalink($SETT['url'].'/index.php?a=enter&success='.$user['id'].'&updt='.$msg));
} else { 
	header("Location: ".permalink($SETT['url'].'/index.php?a=enter&success='.$contest_id.'&ret='.$msg));
}
