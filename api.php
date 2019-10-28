<?php
header('Content-Type: text/plain; charset=utf-8;');
require_once(__DIR__ .'/includes/autoload.php');
$us = new userCallback;
$cd = new contestDelivery;

// If this is a post request, switch query
if (isset($_POST)) {
	$_GET = $_POST;
}
// Check if this is a public request
$public = $_GET['a'] == 'profile' || $_GET['a'] == 'contest' || $_GET['a'] == 'connect' ? true : false;

// Fetch the token
$token = isset($_GET['token']) ? db_prepare_input($_GET['token']) : '';

$error = array();
if ($public) {
	// Handle public API requests
	if (isset($_GET['client_id'])) {
		$sql = sprintf("SELECT * FROM `api` WHERE `client_id` = '%s'", db_prepare_input($_GET['client_id']));
		$api = dbProcessor($sql, 1)[0];
		$state = true;
	} else {
		$error['error']['message'] = 'Client ID is required to gain access';
		$error['error']['code'] = '900';
		$state = false;
	}

	if ($state) {
		if ($_GET['client_id'] == $api['client_id']) {
			if ($_GET['a'] == 'profile') {
				// Fetch the users profile
				if (isset($_GET['list']) && $_GET['list'] == 'true') {
					$dataset = array('username', 'firstname', 'lastname', 'email', 'country' ,'state', 'city', 'photo', 
						'cover', 'profile', 'error' => '', 'code' => 200);
					echo json_encode($dataset);				
					return;
				}
				if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
					$us->user_id = db_prepare_input($_GET['id']);
					$_user = $us->userData(NULL, 1)[0];
					$photo = getImage($_user['photo'], 1); 
					$cover = getImage($_user['cover'], 2); 
					$profile = permalink($SETT['url'].'/index.php?a=profile&u='.$_user['username']);
					$dataset = 
						array('username' => $_user['username'], 'firstname' => $_user['fname'], 'lastname' => $_user['lname'],
							'email' => $_user['email'], 'country' => $_user['country'], 'state' => $_user['state'],
							'city' => $_user['city'], 'photo' => $photo, 'cover' => $cover, 'profile' => $profile,
							'status' => 'success', 'error' => '', 'code' => 200
							);	
					echo $response = json_encode($dataset);				
				} else {
					$error['error']['message'] = 'Invalid id';
					$error['error']['code'] = '800';
				}
			} elseif ($_GET['a'] == 'contest') {
				// Fetch the contest
				if (isset($_GET['list']) && $_GET['list'] == 'true') {
					$dataset = array('title', 'creator', 'type', 'eligibility', 'phone' ,'email', 'country', 'votes',
						'cover','link', 'cover', 'status' => 'success', 'error' => '', 'code' => 200);
					echo json_encode($dataset);				
					return;
				}
				if (isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
					$contest = $cd->getContest(0, db_prepare_input($_GET['id']));
					$link = permalink($SETT['url'].'/index.php?a=contest&s='.$contest['safelink']);
					$cover = getImage($contest['cover'], 2);
					$dataset = array('title' => $contest['title'], 'creator' => $contest['creator'], 'type' => $contest['type'], 
						'eligibility' => $contest['eligibility'], 'phone' => $contest['phone'],'email' => $contest['email'],
						'country' => $contest['country'], 'votes' => $contest['votes'], 'cover' => $contest['cover'],
						'link' => $link, 'cover' => $cover, 'status' => 'success', 'error' => '', 'code' => 200);
					echo json_encode($dataset);	
				} else {
					$error['error']['message'] = 'Invalid id';
					$error['error']['code'] = '800';
				}
			} elseif ($_GET['a'] == 'connect') {
				$redi = 'connector&required=login_api&token='.$token.'&referrer='.urlencode(urlReferrer(permalink($SETT['url'].'/api.php?a='.$_GET['a'].'&client_id='.$api['client_id']), 0));		
				$redirect_uri = isset($_GET['redirect_uri']) ? $_GET['redirect_uri'] : '';
				if (filter_var($redirect_uri, FILTER_VALIDATE_URL)) {
					if (!$user) {
						$ret = '&token='.$token.'&redirect_uri='.urlencode($_GET['$redirect_uri']);
						$_SESSION['client_id'] = $api['client_id'].$ret;
						redirect($redi);
					} else {
						if (isset($_SESSION['client_id'])) {
							 unset($_SESSION['client_id']);
						}
						$dataset = 
							array('username' => $user['username'], 'firstname' => $user['fname'], 'lastname' => $user['lname'],
								'email' => $user['email'], 'status' => 'success', 'code' => 200, 'message' => 'success',
								'token' => $token);
						$response = $_GET['redirect_uri'].'&response='.urlencode(json_encode($dataset));
						redirect($response, 1);
					}
				} else {
					$error['error']['message'] = 'Invalid redirect uri';
					$error['error']['code'] = '700';
				}
			}
		} else {
			$error['error']['message'] = 'Client ID is invalid';
			$error['error']['code'] = '901';
		}
	}
}
echo $error ? json_encode($error) : '';

?>
