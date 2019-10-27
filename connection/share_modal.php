<?php
require_once(__DIR__ .'/../includes/autoload.php');
$us = new userCallback;
$social = new social;

if ($_POST['type'] == 'contest') {
	$data = $us->collectUserName(0, 1, $_POST['id']);
	$title = $data['title'];
} elseif ($_POST['type'] == 'contestant') {
  $data = $us->collectUserName(0, 0, $_POST['id']); 
  $title = $data['firstname'].' '.$data['lastname'];
} elseif ($_POST['type'] == 'post') {
  $data = $social->timelines($_POST['id'], 1); 
  $title = ucfirst($data['username']).'s '.$LANG['post'];
}

$content = '
<div class="card"> 
  <div class="card-body text-center">
     <button class="btn btn-sm btn-fb" id="fb_sharer"><i class="fa fa-2x fa-facebook"></i></button>
     <button class="btn btn-sm btn-tw" id="tw_sharer"><i class="fa fa-2x fa-twitter"></i></button> 
     <button class="btn btn-sm btn-pin" id="pin_sharer"><i class="fa fa-2x fa-pinterest-p"></i></button> 
     <button class="btn btn-sm btn-gplus" id="gplus_sharer"><i class="fa fa-2x fa-google-plus"></i></button> 
  </div>
</div>';
 
$modal = modal('sharing', $content, ucfirst($LANG['share']).' '.$title, '1');
echo $modal;
  