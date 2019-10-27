<?php
require_once(__DIR__ .'/../includes/autoload.php'); 
$messaging = new messaging;
$social = new social;

if (!empty($_POST['type'])) {
	// Fetch the new message
  	echo $messaging->messenger(2, $_POST['user_id']);
} elseif (!empty($_POST['search'])) {
	// Search your subscribers
	$social->onlineTime = $settings['online_time'];
	echo $social->subscribers(0, 1, $_POST['q']);
} else {
	// Load more messages
  	$messaging->chat_id = $_POST['chat_id'];
  	$messaging->start = $_POST['start'];
  	echo $messaging->messenger(3, $_POST['user_id']);
} 