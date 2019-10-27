<?php
require_once(__DIR__ .'/../includes/autoload.php');

$site_class = new siteClass;
$site_class->what = sprintf('user_id = \'%s\'', $user['id']);
$messages = $site_class->support_system(0);

$review = '';
$get_reply = '';
if ($messages) { 
	$pp = 0;
    foreach ($messages as $rs => $key) {
			$pp = $pp+1;

		// Fetch Replys
		$site_class->what = sprintf('reply = \'%s\'', $key['id']);
		$replies = $site_class->support_system(0);

		if ($replies) {
			foreach ($replies as $reply => $rs) {
				$dd=strtotime($key['date']); 

				$get_reply .='
			    <div class="d-inline-block bg-light border m-1 float-right" style="max-width:85%;">
			    	<div class="float-left font-weight-bold px-2 teal-text">Support Reply
			    		<div class="date">'.$rs['subject'].'</div><div class="date">'.$marxTime->timeAgo($dd).'</div>
			    	</div>
			    	'.$rs['message'].'
			    </div> <div class="clearfix"></div>'; 
			}
		} else {
			$get_reply ='';
		}

		$review .=' 
		  <div class="p-2 my-2 bg-white">  
		    <div class="d-inline-block float-left font-weight-bold px-2"> '.$key['subject'].'
		      <div class="date">'.$marxTime->timeAgo($dd).'</div>
		    </div>
		    <div class="">
		    	'.$key['message'].'
		    </div>
		      '.$get_reply.'
		  </div>'; 
    }
} else {
	$review .= '<h2 class="d-flex justify-content-center text-center text-info p-5">No tickets!</h2>';
}  
 echo $review;


