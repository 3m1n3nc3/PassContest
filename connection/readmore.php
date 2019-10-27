<?php
require_once(__DIR__ .'/../includes/autoload.php');

$site_class = new siteClass;
$userApp = new userCallback;
$cd = new contestDelivery;

$id = $_POST['id'];

$gallery = $userApp->user_gallery(0, 2, $id)[0];

$type = $_POST['type'];
// Type 0: Gallery Post

$less = ' <a onclick="readmore('.$id.', 0)" class="text-info"> Less</a></div>';
$more = ' <a onclick="readmore('.$id.', 1)" class="text-info"> Read More</a></div>';

if ($type == 0) {
	echo myTruncate($gallery['description'], 120).$more;
} elseif ($type == 1) {
	echo $gallery['description'].$less;
}



