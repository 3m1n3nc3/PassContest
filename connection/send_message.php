<?php
require_once(__DIR__ .'/../includes/autoload.php');
$messaging = new messaging;

echo $messaging->send_message($_POST['id'], $_POST['message']); 
  