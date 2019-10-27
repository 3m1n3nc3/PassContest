<?php
require_once(__DIR__ .'/../includes/autoload.php'); 

$menu = new menuHandler;
$upd = new msgNotif;

$return_menu = '';
if ($user) {
	if (isset($_POST['type'])) {
		$n = $menu->messageNotifications();

		$return_menu .= 
		$n['notifications']. 
        '<div class="dropdown-divider"></div>'.
        $n['all_notifications'];

		$menu = array(
	   	'notification' => $return_menu, 
	   	'count'  => $n['count'],
	   	'notice' => $n['notice']); 
	} else {
		if (isset($_POST['view']) && $_POST['view'] == 1) {
			$upd->notificationState(1, $user['id']); 
		}
	 
		$n = $menu->notificationsMenu();

		$return_menu .= 
		$n['notifications']. 
        '<div class="dropdown-divider"></div>'.
        $n['all_notifications'];

		$menu = array(
	   	'notification' => $return_menu, 
	   	'count'  => $n['count'],
	   	'notice' => $n['notice']); 
	}
	echo json_encode($menu, JSON_UNESCAPED_SLASHES);
}

// <div style="display:none" class="notification_list">
//   <span class="d-flex justify-content-center blue-gradient p-1">You have 4/6 New Notifications</span>
//   <div class="unstyled">
//     <div data-notification_id="1" class="notification_li p-2"><a href="#" class="notification_message">Messaggio di notifica un po più lungo del normale</a> </div>
//     <div data-notification_id="2" class="notification_li p-2"><a href="#">Messaggio di notifica 2 un po più lungo del normale</a> </div>
//     <div data-notification_id="3" class="notification_li p-2"><a href="#">Messaggio di notifica un po più lungo del normale</a> </div>
//   </div>
//   <span class="d-flex justify-content-center grey lighten-4 p-1"><a class="red-text" href="#" class="turn_off_notification">View All</a></span>
// </div>