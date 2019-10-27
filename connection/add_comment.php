<?php
require_once(__DIR__ .'/../includes/autoload.php');
$gett = new contestDelivery; 
$us = new userCallback;
$save = new siteClass;
$noti = new msgNotif;
$action = new actions;
$social = new social;

$message = '';

/**
 * [Type 0: Reply]
 * [1: Poll Reply]
 * [2: Comment]
 * [3: Poll Comment]
 * [4: Others]
 */

$sender = db_prepare_input($_POST['sender']);
$receiver = db_prepare_input($_POST['receiver']);
$master = db_prepare_input($_POST['master']);
$reply_id = db_prepare_input($_POST['reply']);
$comment = db_prepare_input($_POST['comment']);
$type = db_prepare_input($_POST['type']);

$mst = $type == 0 || $type == 2 ? 'post': 'poll';
$cid = $mst == 'poll' ? $master : 0;

/**
 * Set the types to either comments or replies
 */ 
$comment_type = $type == 0 || $type == 1 ? 0 : 1;

/**
 * Sender info
 */ 
$us->user_id = $sender;
$sender_d = $us->userData(NULL, 1)[0];
$sender_data = $us->collectUserName(null, 0, $sender_d['id']); 
$s_profile = '<a href="'.$sender_data['profile'].'">%s</a>';

/**
 * Receiver info
 */ 
$us->user_id = $receiver;
$receiver_d = $us->userData(NULL, 1)[0];
$receiver_data = $us->collectUserName(null, 0, $receiver_d['id']);

$tlink = $CONF['url'].'/index.php?a=timeline&u='.$sender_d['username'].'&read='.$master.'#comment';
$vlink = $CONF['url'].'/index.php?a=voting&id='.$master.'&user='.$receiver_d['username'];
$pass = fetch_api(2);

// Set the template
if ($type == 0 || $type == 1) {
	$msg_template = $settings['email_reply_temp'];
	$action = $LANG['replied'];
	$action_on = $LANG['poll'].' or '.$LANG['post'];
} elseif ($type == 2 || $type == 3) {
	$msg_template = $settings['email_comment_temp'];
	$action = $LANG['comment'];
	$action_on = $type == 3 ? '<a href="'.$vlink.'">'.$LANG['poll'].'</a>' : '<a href="'.$tlink.'">'.$LANG['post'].'</a>';
} 

/**
 * Is this a comment or a reply
 */ 
if ($type == 2 || $type == 3) {   
	$commentArray = array($sender_d['id'], $receiver_d['id'], 0, $master, $cid, $comment, $comment_type, $mst); 
} else {
	$commentArray = array($sender_d['id'], $receiver_d['id'], $reply_id, $master, $cid, $comment, $comment_type, $mst);
}

// Message template
$params = 
	array($settings['site_name'], ucfirst($receiver_d['username']), 'no password', $receiver_d['fname'], 
		$receiver_d['lname'], 'no key', $receiver_d['email'], sprintf($s_profile, ucfirst($sender_d['username'])),
		sprintf($s_profile, $sender_d['fname']), sprintf($s_profile, $sender_d['lname']), $action, $action_on
	);

/**
 * The message to send
 */
$message = $save->message_template($msg_template, $params);

/**
 * Determine the email subject
 */
$action_on = $type == 1 || $type == 3 ? $LANG['poll'] : $LANG['post'];
$subject = ($type == 0 || $type == 1) ? sprintf($LANG['new_reply'], $action_on, $settings['site_name']) : sprintf($LANG['new_comment'], $action_on, $settings['site_name']);

/**
 * Send the user a notification and an email
 */
if ($sender_d['id'] !== $receiver_d['id']) {
	$social->subject = $subject;
	$social->message = $message;
	$social->notifier($sender_d['id'], $receiver_d['id'], 0, $master, $settings['email_comment']);
}

$t = '<small><span><i class="fa fa-clock-o "></i> '.$marxTime->timeAgo(strtotime('Now')).'</span></small>';
$l = '<small><span><i class="fa fa-clock-o "></i> '.$sender_data['address'].'</span></small>';

/**
 * If this is a post comment or reply, append the new comment
 */
if ($type == 0 || $type == 2) {
	$gett->type = $type;
	$gett->sender = $sender_data;
	$gett->comment = $comment;
	$append = $gett->timelineComments($receiver_d['id'], $master, 1);
	$message = !empty($comment) ? $append : '';
}

/**
 * If this is a poll comment prepare to add credit to the creator
 */
if ($type == 1 || $type == 3) {
	$x = $LANG['comment'];

	// Get the contest and creators data 
	$contest = $gett->getContest(0, $master);
	$creator = $us->userData($contest['creator']);

	// Fetch the creators balance
	$save->what = sprintf('user = \'%s\'', $creator['id']);
	$cr_credit = $save->passCredits(0)[0];

}

/**
 * Fetch the senders balance
 */
$save->what = sprintf('user = \'%s\'', $user['id']);
$get_credit = $save->passCredits(0)[0]; 

/**
 * Check senders premium status
 */
$prem_check = $userApp->premiumStatus(null, 1);	 

$contest_id = $master;

/**
 * If the user has passCredits
 */
$charge = '... ';
$gett->array = $commentArray;
if ($settings['pc_vote']) {
	if ($type == 3 && $get_credit['balance'] > $settings['pc_comment']) { 
		$data = $gett->doComments($comment_type, $master, 0);
		if ($data) {
			$balance = $get_credit['balance'] - $settings['pc_comment'];
			$save->balance = $balance;
			$return = $save->passCredits(1, $user['id']);
			$charge .= sprintf($LANG['charge_notice'], $settings['pc_comment'], $settings['pc_symbol'], $x);

			// If the creator is not the commenter add credit to him
			if ($contest['creator'] !== $user['username']) {
				$balance = $cr_credit['balance'] + ($settings['pc_agent_percent'] * $settings['pc_vote'] / 100);
				$save->balance = $balance;
				if ($cr_credit) {
					$return = $save->passCredits(1, $cr_data['id']);
				} else {  
					$return = $save->passCredits(2, $cr_data['id']);
				}					 
			}			
		}
	} elseif ($settings['premium']) {

		// Check if the user is a premium user
		if ($prem_check) {
			$data = $gett->doComments($comment_type, $master, 0);
		}
	} else {
		$data = sprintf($LANG['insufficient'], $LANG['passcredit'], $x);
	}
} elseif ($settings['premium']) { 

	// Check if the user is a premium user
	if ($prem_check) {
		$data = $gett->doComments($comment_type, $master, 0);
	}
} else {
	$data = $gett->doComments($comment_type, $master, 0);
}
$data .= $charge;

echo $type == 1 || $type == 3 ? $data : $message;