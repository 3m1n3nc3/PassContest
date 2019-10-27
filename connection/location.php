<?php
require_once(__DIR__ .'/../includes/autoload.php'); 
$sc = new siteClass; 

// Type 1: Block or Unblock user
// Type 0: View block state

if ($_POST['type'] == 1) {
	$rows = '<option disabled>Select State</option>';
	$sc->country = $_POST['country_id'];
	$states = $sc->fetch_locale(1);

	foreach ($states as $name) {
		if(mb_strtolower($user['state']) == mb_strtolower($name['name'])) {
			$selected = ' selected="selected"';
		} else {
			$selected = '';
		}
		$rows .= '<option value="'.$name['name'].'" id="'.$name['id'].'"'.$selected.'>'.$name['name'].'</option>';		
	}
} elseif ($_POST['type'] == 2) {
	$rows = '<option disabled>Select City</option>';
	$sc->state = $_POST['state_id'];
	$cities = $sc->fetch_locale(2);
	$pass = fetch_api(2);
	foreach ($cities as $city) {
		if(mb_strtolower($user['city']) == mb_strtolower($city['name'])) {
			$selected = ' selected="selected"';
		} else {
			$selected = '';
		}
		$rows .= '<option value="'.$city['name'].'" id="'.$city['id'].'"'.$selected.'>'.$city['name'].'</option>';	
	}
} 
echo $rows;