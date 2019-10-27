<?php
require_once(__DIR__ .'/../includes/autoload.php');

$site_class = new siteClass;
$userApp = new userCallback;
$cd = new contestDelivery;
$social = new social;

$leader = $_POST['leader'];

// Fetch user relationships
$follower = $social->follow($leader, 1);
$followers = $social->follow($leader, 2); 
 
// fetch user likes
$social->content_type = $_POST['type']; 
$social->content = $_POST['leader'];
$liked = $social->like(0, 1);
$all_likes = $social->like(0, 2);

$process = '';
// Follow this User
if ($_POST['action'] == 'follow' || $_POST['action'] == 'unfollow') {
	if ($_POST['action'] == 'follow') {
		// Follow the user
		if ($follower['leader_id'] == $leader) {
			$return = "Already following";
			$status = 1;
			$new = '<i class="fa fa-user-times"></i> '.$LANG['unfollow'];
			$new_ = count($followers); 
			$new_followers = $new_>1 ? $new_.' '.$LANG['followers'] : $new_.' '.$LANG['follower'];
		} else {
			$return = $social->follow($leader, 0);
			$status = 1;
			$new = '<i class="fa fa-user-times"></i> '.$LANG['unfollow'];
			$new_ = count($followers)+1; 
			$new_followers = $new_>1 ? $new_.' '.$LANG['followers'] : $new_.' '.$LANG['follower'];
		}
	} elseif ($_POST['action'] == 'unfollow') {
		// Unfollow the user
		$sql = sprintf("DELETE FROM " . TABLE_RELATE . " WHERE `leader_id` = '%s' AND `follower_id` = '%s'", $leader, $user['id']);
		$ret = dbProcessor($sql, 0, 1);
		$return = $ret == 1 ? 'No longer following' : $ret;
		$status = $ret == 1 ? 0 : 1;
		$new = '<i class="fa fa-user-plus"></i> '.$LANG['follow'];
		$new_ = count($followers)-1; 
		$new_followers = $new_>1 ? $new_.' '.$LANG['followers'] : $new_.' '.$LANG['follower'];
	}	
	$process = array('response' => $return, 'status' => $status, 'new_action' => $new, 'count' => $new_followers );
// Like the content
} elseif ($_POST['action'] == 'like' || $_POST['action'] == 'unlike') {
	// Like the content
	if ($_POST['action'] == 'like') {
		$social->content = $leader;
		$social->content_type = $_POST['type']; 
		$new_likes = count($all_likes)+1;
		$lang = $new_likes>1 ? $LANG['likes'] : $LANG['like'];
		$status = 1;
		$return = $liked['user_id'] !== $user['id'] ? $social->like($_POST['user_id'], 0) : 'Already Liked';
		$process = array('response' => $return, 'status' => $status, 'count' => $new_likes, 'like' => $lang);
		// UnLike the content
	} elseif ($_POST['action'] == 'unlike') { 
		$sql = sprintf("DELETE FROM " . TABLE_LIKE . " WHERE `content_id` = '%s' AND `user_id` = '%s' AND `content_type` = '%s'", $leader, $user['id'], $_POST['type']);
		$ret = $liked['user_id'] == $user['id'] ? dbProcessor($sql, 0, 1) : 'Not Liked';
		$return = $ret == 1 ? 'Unliked' : $ret;
		$status = $ret == 1 ? 0 : 1;
		$new_likes = count($all_likes)-1;
		$lang = $new_likes>1 ? $LANG['likes'] : $LANG['like']; 
		$process = array('response' => $return, 'status' => $status, 'count' => $new_likes, 'like' => $lang);
	}
// Show likes
} elseif ($_POST['action'] == 'showlike') {
	$likers = '';
	if ($all_likes) {
		foreach ($all_likes as $key) {
			$userApp->user_id = $key['user_id'];
			$u = $userApp->userData(null, 1)[0];

			// Check users premium status to add premium badge
			$premium_status = $userApp->premiumStatus($u['id'], 2);
			$badge = ($premium_status) ? badge(0, $premium_status['plan'], 2) : '';
			$fullname = realName($u['username'], $u['fname'], $u['lname']).' '.$badge;

			$photo = '
			<a href="'.permalink($CONF['url'].'/index.php?a=profile&u='.$u['username']).'">
				<div class="chip">
				  <img src="'.$CONF['url'].'/uploads/faces/'.$u['photo'].'" alt="'.$u['username'].'"> '.$fullname.'
				</div>
			</a>';

			$likers .= 
			'<span>'.$photo.'</span> '.$social->follow_link($u['id'], 1);
		}		
	} else {
		$likers .= 
			'<h3 class="p-3 text-info text-center">'.$LANG['no_likes'].'</h3> ';
	}

	$modal = modal('showLikes', $likers, $LANG['likes'], 1);
	$process = array('modal' => $modal);
// Quick info Cards
} elseif ($_POST['action'] == 'quickinfo') {
	$card = $social->info_cards(0, 1, $leader);
	$process = array('infoCard' => $card);
}

$process = json_encode($process, JSON_UNESCAPED_SLASHES);
echo $process;
 



