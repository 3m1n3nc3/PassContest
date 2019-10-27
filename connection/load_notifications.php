<?php
require_once(__DIR__ .'/../includes/autoload.php'); 

$menu = new menuHandler;
$upd = new msgNotif;

if (isset($_POST['view']) && $_POST['view'] == 1) {
	$upd->notificationState(1, $user['id']); // Remove the notifications counter badge
}

if (isset($_POST['page'])) {
 	$menu->page = $_POST['page'];
}

if (isset($_POST['notification_id']) && $_POST['notification_id'] != 0) {
	$menu->id = $_POST['notification_id'];
	$view_notifs = $menu->viewNotifications(2);
	$upd->notificationState(1, $_POST['notification_id'], true); // Set the state of the currently open notification to seen
} else {
	$view_notifs = $menu->viewNotifications(1);
}

$menu = array(
		'n_content' => $view_notifs['notifications'],
		'page' => $view_notifs['page'], 
		'notice' => $view_notifs['notice'], 
	); 
echo json_encode($menu, JSON_UNESCAPED_SLASHES);
 