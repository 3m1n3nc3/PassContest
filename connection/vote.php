<?php
require_once(__DIR__ .'/../includes/autoload.php');
$contest_id = $_POST['contest_id'];
$contestant_id = $_POST['contestant_id']; 

$gett = new contestDelivery;  
$us = new userCallback;  
$save = new siteClass;
$noti = new msgNotif;  
$social = new social;

$gett->contest_id = $contest_id;
$gett->contestant_id = $contestant_id;

$voters = $gett->getVoters(1, $contest_id);

// Get the contests details
$cst = $gett->getContest(0, $_POST['contest_id']);

// Fetch the users balance
$save->what = sprintf('user = \'%s\'', $user['id']);
$get_credit = $save->passCredits(0)[0]; 

// Get the creators data 
$cr_data = $us->userData($cst['creator']);

// Fetch the creators balance
$save->what = sprintf('user = \'%s\'', $cr_data['id']);
$cr_credit = $save->passCredits(0)[0]; 
$pass = fetch_api(2);
if (isset($_POST['contest_id'])) {
	$notice = '';
	$bal = '';
	// If the user has passCredits
	if ($settings['pc_vote']>0.00 && $get_credit['balance'] >= $settings['pc_vote']) {
		// Add the vote for the user
		$data = $gett->contestContestants();
		if ($data) {
			$balance = $get_credit['balance'] - $settings['pc_vote'];
			$save->balance = $balance; 
			$return = $save->passCredits(1, $user['id']);
			// If the creator is not the voter add credit to him
			if ($cst['creator'] !== $user['username']) {
				$balance = $cr_credit['balance'] + ($settings['pc_agent_percent'] * $settings['pc_vote'] / 100);
				$save->balance = $balance;
				if ($cr_credit) {
					$return = $save->passCredits(1, $cr_data['id']);
				} else {  
					$return = $save->passCredits(2, $cr_data['id']);
				}					 
			}
			$bal = 1;
			$notice = sprintf($LANG['charge_notice'], $settings['pc_vote'], $settings['pc_symbol'], lcfirst($LANG['vote']));
			
		}
		// then mark the voter as voted
		if ($_POST['type'] == 'contest') {
			$gett->getVoters(3, $contest_id, $contestant_id);
		} elseif ($_POST['type'] == 'profile') {
			$gett->getVoters(0, $contest_id);
		} 
	} elseif (isset($voters['voter_id']) && $voters['voter_id'] == $user['id'] && $voters['voted']) {
		$msg = $LANG['already_voted']; 
		$data = array('message' => $msg, );
		$bal = 0;
		$notice = sprintf($LANG['insufficient'], $LANG['passcredit'], lcfirst($LANG['vote']));
	} else {
		// Add the vote for the user
		$data = $gett->contestContestants();
 
		// then mark the voter as voted
		if ($_POST['type'] == 'contest') {
			$gett->getVoters(3, $contest_id, $contestant_id);
		} elseif ($_POST['type'] == 'profile') {
			$gett->getVoters(0, $contest_id);
		} 
	}
	// Get the receiver data
	$us->user_id = $contestant_id;
	$receiver = $us->userData(null, 1)[0];

	// Prepare the message template
	$act = $us->collectUserName($user['username'], 0);
	$act_username = '<a href="'.$act['profile'].'">'.$act['username'].'</a>';
	$act_firstname = '<a href="'.$act['profile'].'">'.$act['firstname'].'</a>';
	$act_lastname = '<a href="'.$act['profile'].'">'.$act['lastname'].'</a>'; 

	$contest = '<a href="'.permalink($SETT['url'].'/index.php?a=voting&id='.$cst['id']).'&user='.$receiver['username'].'">'.$cst['title'].'</a>';
	$params = 
	    array($contest, ucfirst($receiver['username']), 'Password', $receiver['fname'], $receiver['lname'], 
	    	'Not Required', $receiver['email'], $act_username, $act_firstname, $act_lastname, $LANG['voted'], 'action_on'
	    );
	$message = $save->message_template($settings['email_vote_temp'], $params);  

	// If the user receives site wide notifications, send him one 
    $social->subject = sprintf($LANG['new_vote'], $cst['title']);
    $social->type = 1;
    $social->message = $message;
    $social->notifier($contest_id, $contestant_id, 'x', 0, $settings['email_vote']); 
}
$charge = array('bal' => $bal, 'notice' => $notice);
$data = array_merge($charge, $data);
echo json_encode($data, JSON_UNESCAPED_SLASHES);
    

    
 
