<?php
require_once(__DIR__ .'/../includes/autoload.php');
$user_id = $user['id'];

//error_reporting(E_ALL);
// if (isset($_POST['save']) && !isset($user['username'])) {
// 	header("Location: ")
// }

$gett = new contestDelivery;
$noti = new msgNotif;
$userApp = new userCallback;
$save = new siteClass;
$social = new social;

$s = $gett->viewApplications(0, 0, $_POST['user_id']);
$cc = $gett->getContest(0, $_POST['contest_id']);

// Prepare the data to add to approved applications table
$gett->contest_id = $_POST['contest_id'];
$gett->contestant_id = $_POST['user_id'];
$gett->contest_id = $_POST['contest_id'];
$gett->name = $s['firstname'].' '.$s['lastname'];
$gett->city = $s['city'];
$gett->state = $s['state'];
$gett->country = $s['country'];

// Fetch user data and check if user has enabled site wide notifications
$userApp->user_id = $_POST['user_id'];
$userVar = $userApp->userData(null, 1)[0];
$rsn = ($userVar['site_notifications']) ? 1 : 0 ;

// Prepare the message template
$act = $userApp->collectUserName($user['username'], 0);
$act_username = '<a href="'.$act['profile'].'">'.$act['username'].'</a>';
$act_firstname = '<a href="'.$act['profile'].'">'.$act['firstname'].'</a>';
$act_lastname = '<a href="'.$act['profile'].'">'.$act['lastname'].'</a>'; 

// Fetch the contest
$cont = $userApp->collectUserName(null, 1, $_POST['contest_id']);
$contest = '<a href="'.$cont['id_link'].'">'.$cont['title'].'</a>';

// Prepare the message to send as email notification 
$params = 
    array($contest, $userVar['username'], 'password', $userVar['fname'], $userVar['lname'], 'key', $userVar['email'],
        $act_username, $act_firstname, $act_lastname, 'action', 'action_on'
    );
$message = $save->message_template($settings['email_approved_temp'], $params);

// Start checking if the contest creator has reached his limits 
$approved = $gett->getApprovedList(); 
$count = count($approved);
$premium_status = $userApp->premiumStatus(null, 2);
$prem_check = $userApp->premiumStatus(null, 1);  

// Check if premium is on
if ($settings['premium']) {

	// Check if user has an active subscription
	if ($prem_check) {
        if ($premium_status['plan'] == 'slight_plan') {
            if ($count>=15) {
                $action = '<span class="text-dark font-weight-bold">'.sprintf($LANG['creators_limit'], 15).'</span>';
            } else {
                $action = $gett->approveApplication();
                $r = 1;
            }
        } elseif ($premium_status['plan'] == 'lite_plan') {
            if ($count>=35) {
                $action = '<span class="text-black font-weight-bold">'.sprintf($LANG['creators_limit'], 35).'</span>';
            } else {
                $action = $gett->approveApplication();
                $r = 1;	                     
            }
        } elseif ($premium_status['plan'] == 'life_plan') { 
            $action = $gett->approveApplication(); 
            $r = 1;                   
        } else {
            $action = '<span class="text-dark font-weight-bold">'.$LANG['requires_prem'].'</span>';
        }
	} else {
		$action = '<span class="text-dark font-weight-bold">'.$LANG['requires_prem'].'</span>';
	}
} else {
    $action = $gett->approveApplication();
    $r = 1;
}
echo $action;

// Send a notification to the username
if (isset($r) && $userVar['id'] == $_POST['user_id']) { 
    $social->subject = sprintf($LANG['approved_or_declined'], $cc['title'], $LANG['approve']);
    $social->type = 1;
    $social->message = $message;
    $social->notifier($_POST['contest_id'], $_POST['user_id'], 'x', 0, $settings['email_approved']);    
}   
 