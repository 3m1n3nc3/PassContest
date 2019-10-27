<?php
require_once(__DIR__ .'/../includes/autoload.php'); 
$actions = new actions;
$social = new social;

// Type 1: Block or Unblock user
// Type 0: View block state

if ($_POST['style'] == 0) {
	echo $actions->manageBlock($_POST['id'], $_POST['type'], $_POST['style'])['icon'];
} elseif ($_POST['style'] == 1) {
	echo $actions->manageBlock($_POST['id'], $_POST['type'], $_POST['style'])['link'];
} elseif ($_POST['style'] == 2) {
	echo $actions->manageBlock($_POST['id'], $_POST['type'], $_POST['style'])['link_icon'];
}
 