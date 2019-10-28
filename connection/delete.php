<?php
require_once(__DIR__ .'/../includes/autoload.php');
$cd = new contestDelivery;
$save = new siteClass; 
$noti = new msgNotif;
$social = new social;

$action = 'Action';
$return_value ='';
if(isset($_POST['type']) && $_POST['type'] == 0) { /*Delete the schedule*/
	$sql = sprintf("DELETE FROM " . TABLE_SCHEDULE . " WHERE `id` = '%s'", $_POST['id']);
	$response = successMessage('Schedule Removed'); 

} elseif(isset($_POST['type']) && $_POST['type'] == 1) { /*Delete the category*/
	$sql = sprintf("DELETE FROM " . TABLE_CATEGORY . " WHERE `id` = '%s'", $_POST['id']); 
	$response = successMessage('Category Removed'); 
	   
} elseif(isset($_POST['type']) && $_POST['type'] == 2) { /*Delete Created Profiles*/
	$us = new userCallback;
	$us->deleteUser($_POST['id']); //This will delete everything about the user

} elseif(isset($_POST['type']) && $_POST['type'] == 3) { /*Delete the notification*/
	$sql = sprintf("DELETE FROM " . TABLE_NOTIFY . " WHERE `id` = '%s' AND `receiver` = '%s'", $_POST['id'], $user['id']); 
	$response = successMessage($LANG['notifications'].' Deleted'); 	

} elseif(isset($_POST['type']) && $_POST['type'] == 4) { /*Decline the contest application*/
	$return_value .= $cd->declineApplication($_POST['id'], $_POST['contest_id']);	 
	$msg = $settings['email_declined_temp'];
	$action = $LANG['declined'];
} elseif(isset($_POST['type']) && $_POST['type'] == 5) { /*Remove contestant from the contest*/ 
	$return_value .= $cd->removeContestant($_POST['id'], $_POST['contest_id']);  	   
} elseif(isset($_POST['type']) && $_POST['type'] == 6) { /*Remove contestant from the contest*/ 
	$msg = $save->support_system(2, $_POST['id']); //Delete the Ticket 
	$save->what = sprintf('reply = \'%s\'', $_POST['id']);
	$del = $save->support_system(0);
	if (count($msg) > 0) { //Check if the ticket has replies then delete them
		$save->support_system(3, $_POST['id']); 
	}
	$return_value .= ($msg == 1) ? successMessage('Ticket Closed') : $msg; 
} elseif(isset($_POST['type']) && $_POST['type'] == 7) { /*Delete the Gallery Photo*/
	$sql = sprintf("DELETE FROM " . TABLE_GALLERY . " WHERE `id` = '%s'", $_POST['id']); 
	$img = $userApp->user_gallery($user['id'], 2, $_POST['id'])[0];
	deleteFiles($img['photo'], 1);
	$response = '<div class="p-3">'.successMessage($LANG['photo_deleted']).'</div>'; 	

} elseif(isset($_POST['type']) && $_POST['type'] == 8) { /*Delete the Timline post*/
	$sql = sprintf("DELETE FROM " . TABLE_TIMELINE . " WHERE `pid` = '%s'", $_POST['id']); 
	$img = $social->timelines($_POST['id'], 1);
	$img['post_photo'] ? deleteFiles($img['post_photo'], 1) : '';
	$response = '<div class="p-3">'.successMessage($LANG['photo_deleted']).'</div>'; 	

} elseif(isset($_POST['type']) && $_POST['type'] == 9) { /*Delete the comment*/
	$cd->reply_id = $_POST['id'];
	$m = empty($_POST['master']) ? 'post' : 'poll';
    $get_replies = $cd->doComments(1, $m, 3)[0];
    if ($get_replies['reply_id'] == $_POST['id']) { /*Delete the replies*/
    	$rsql = sprintf("DELETE FROM " . TABLE_COMMENTS . " WHERE `reply_id` = '%s'", $_POST['id']);
    	dbProcessor($rsql, 0);
    }
	$sql = sprintf("DELETE FROM " . TABLE_COMMENTS . " WHERE `id` = '%s'", $_POST['id']);
	$response = '<div class="p-3">'.successMessage($LANG['comment_deleted']).'</div>'; 	
} elseif(isset($_POST['type']) && $_POST['type'] == 10) { /*Delete the Message*/
	$sql = sprintf("DELETE FROM " . TABLE_MESSAGE . " WHERE `cid` = '%s'", $_POST['id']);  	
} 

$return = (isset($sql)) ? dbProcessor($sql, 0, $response) : $return_value;
echo $return; 
$pass = fetch_api(2);
// Prepare to send out notifications
$userApp->user_id = $_POST['id'];
$userVar = $userApp->userData(null, 1)[0];

if (isset($msg) && $userVar['id'] == $_POST['id']) { 
	
	$cc = $cd->getContest(0, $_POST['contest_id']);

	// Prepare the message template
	$act = $us->collectUserName($user['username'], 0);
	$act_username = '<a href="'.$act['profile'].'">'.$act['username'].'</a>';
	$act_firstname = '<a href="'.$act['profile'].'">'.$act['firstname'].'</a>';
	$act_lastname = '<a href="'.$act['profile'].'">'.$act['lastname'].'</a>'; 

	// Prepare the message to send as email notification 
	$contest = '<a href="'.permalink($SETT['url'].'/index.php?a=contest&id='.$cc['id']).'">'.$cc['title'].'</a>';
	$params = 
	    array($contest, ucfirst($userVar['username']), $userVar['password'], $userVar['fname'], $userVar['lname'], 
	    	'Not Required', $userVar['email'], $act_username, $act_firstname, $act_lastname, $action, 'action_on'
	    );
	$message = $save->message_template($msg, $params); 

    // If the user receives site wide notifications, send him one 
    $social->subject = sprintf($LANG['approved_or_declined'], $cc['title'], $LANG['decline']);
    $social->type = 1;
    $social->message = $message;
    $social->notifier($_POST['contest_id'], $_POST['id'], 'x', 0, $settings['email_approved']);  
}

