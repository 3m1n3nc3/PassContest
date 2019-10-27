<?php
require_once(__DIR__ .'/../includes/autoload.php'); 
$save = new userCallback;
$gett = new contestDelivery;

// prepare input for db
	if (isset($_POST['create']) && $_POST['create'] == 1 && $_POST['user_id']>0) {
	    $firstname = db_prepare_input($_POST['firstname']); 
	    $lastname = db_prepare_input($_POST['lastname']); 
		$city = db_prepare_input($_POST['city']); 
		$state = db_prepare_input($_POST['state']); 
		$country = db_prepare_input($_POST['country']); 
		$phone = db_prepare_input($_POST['phone']);
		$email = db_prepare_input($_POST['email']);
		$user_id = $_POST['user_id'];

		//Update the new user profile
		$save->firstname = $firstname;
		$save->lastname = $lastname;
		$save->city = $city;
		$save->state = $state;
		$save->country = $country;
		$save->phone = $phone;
		$save->email = $email;
		$save->username = null;
		$save->password = null;
		$save->user_id = $user_id;
		$save->create = 4; // createComtestant method type 4
		echo $save->createContestant();

	} elseif (isset($_POST['create']) && $_POST['create'] == 1) {
	    $firstname = db_prepare_input($_POST['firstname']); 
	    $lastname = db_prepare_input($_POST['lastname']); 
		$city = db_prepare_input($_POST['city']); 
		$state = db_prepare_input($_POST['state']); 
		$country = db_prepare_input($_POST['country']); 
		$phone = db_prepare_input($_POST['phone']);
		$email = db_prepare_input($_POST['email']);

	// authenticate usage
	if ($firstname == '') { 
		echo infoMessage('First Name can not be empty');
	} elseif ($lastname == '') {  
		echo infoMessage('Last Name can not be empty');  
	} elseif ($email == '') {  
		echo infoMessage('Email can not be empty');  
	} elseif ($email == $save->checkEmail($email)) {  
		echo infoMessage('This email has been taken');
	} else {
		// Create the new user profile
		$save->firstname = $firstname;
		$save->lastname = $lastname;
		$save->city = $city;
		$save->state = $state;
		$save->country = $country;
		$save->phone = $phone;
		$save->email = $email;
		$save->generateUsername();
		$save->password = hash('md5', $save->generatePassword(8));
		$save->create = 1; // createComtestant method type 1
		echo $save->createContestant(); 

		// Extract the newly created user_id
		$user_id = $save->userData($save->username)['id'];

		// Associate the created profile with the creator
		$save->create = 3; // createComtestant method type 3
		$save->user_id = $user_id;
		$save->contest_id = $_POST['contest_id'];
		$save->createContestant(); 
		echo '<input type="hidden" name="get_user_id" value="'.$save->user_id.'">';

		// Add the newly created user profile to this contests table
		$gett->contestant_id = $user_id;
		$gett->contest_id = $_POST['contest_id'];
		$gett->name = $firstname.' '.$lastname;
		$gett->city = $city;
		$gett->state = $state;
		$gett->country = $country;
 		$gett->approveApplication();

	} 
//Update the data for this new profile
} elseif (isset($_POST['create']) && $_POST['create'] == 2) {
	$profession = db_prepare_input($_POST['profession']); 
	$facebook = db_prepare_input($_POST['facebook']);
	$twitter = db_prepare_input($_POST['twitter']);
	$instagram = db_prepare_input($_POST['instagram']);
	$lovesto = db_prepare_input($_POST['lovesto']);
	$intro = db_prepare_input($_POST['intro']);
	$user_id = db_prepare_input($_POST['contestant_id']); 

	$save->profession = $profession;
	$save->facebook = $facebook;
	$save->twitter = $twitter;
	$save->instagram= $instagram;
	$save->lovesto= $lovesto;
	$save->intro = $intro;	
	$save->create = 2; // createComtestant method type 2
	echo $save->createContestant($user_id); 
}