<?php
require_once(__DIR__ .'/../includes/autoload.php');

$userid = $user['id'];
$gett = new contestDelivery;

if (isset($_POST['id'])) {
	$contest = $gett->getContest($user['username'], $_POST['id']);
	$contest_id = $contest['id'];

	if (count($contest_id) > 0) {
		$update = TRUE;
		if (isset($_POST['title']) && $_POST['title'] == $contest['title']) {
			$duplicate = FALSE;
		}	
	}	 
} else {
	$update = FALSE;
	$duplicate = TRUE;
}

if(isset($_POST['save']) && $_POST['save'] == 1) {
	$validate = $gett->validateContest($_POST['title']);
    $title = db_prepare_input($_POST['title']); 
    $type = db_prepare_input($_POST['type']); 
	$slogan = db_prepare_input($_POST['slogan']); 
	$facebook = db_prepare_input($_POST['facebook']);
	$twitter = db_prepare_input($_POST['twitter']);
	$instagram = db_prepare_input($_POST['instagram']);
	$email = db_prepare_input($_POST['email']); 
	$phone = db_prepare_input($_POST['phone']);

	if ($update == TRUE) {
		$id = $contest_id;
	}

	// Create a new contest
	if ($title == '') { 
		echo infoMessage('title can not be empty');
	} elseif ($update !== TRUE && $duplicate !== FALSE && $title == isset($validate[0]['title'])) { 
		echo infoMessage('A contest with the same name already exists'); 
	} else {
		$save = new contestDelivery;
		$save->title = $title;
		$save->type = $type;
		$save->slogan = $slogan;
		$save->facebook = $facebook;
		$save->twitter = $twitter;
		$save->instagram= $instagram;
		$save->email = $email;
		$save->phone = $phone;

		if ($update == TRUE) {
			$save->id = $id;
			$save->update = 1;
		}		
		echo $save->addContest(isset($id) ? $id : NULL); 
	} 
} elseif (isset($_POST['save']) && $_POST['save'] == 2) {
	$country = db_prepare_input($_POST['country']);
	$intro = db_prepare_input($_POST['intro']); 
	$eligibility = db_prepare_input($_POST['eligibility']); 
	$prize = db_prepare_input($_POST['prize']); 
	$address = db_prepare_input($_POST['address']);

	$update = new contestDelivery;
	$update->country = $country;
	$update->intro = $intro;
	$update->eligibility = $eligibility;
	$update->prize = $prize;
	$update->address = $address;
	$update->id = $contest_id;
	$update->update = 2; 
	echo $update->updateContestinfo($contest_id);
}	