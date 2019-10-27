<?php
require_once(__DIR__ .'/../includes/autoload.php');
$us = new userCallback;
 
if ($_POST['type'] == 'contest') {
	$data = $us->collectUserName(0, 1, $_POST['id']);
	$title = ucfirst($LANG['follow']).' '.$data['title'];
} elseif ($_POST['type'] == 'contestant') {
	$data = $us->collectUserName(0, 0, $_POST['id']); 
	$title = ucfirst($LANG['follow']).' '.$data['firstname'].' '.$data['lastname'];
}

$modal = ' 
  <h5>The contest creators requires you to Like their Facebook fan page or follow them on twitter before you can vote...</h5>
  <p class="h4"><a href="#top_title" onclick="$(\'#follow-modal\').modal(\'hide\')"> Act Now</a></p> ';  

$modal = modal('follow', $modal, $title, 1);

echo $modal;
  