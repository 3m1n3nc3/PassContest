<?php
require_once(__DIR__ .'/../includes/autoload.php');
$contestant_id = $_POST['contestant_id'];
$contest_id = $_POST['contest_id'];
 
$gett = new contestDelivery;
$userApp = new userCallback;

$gett->contestant_id = $contestant_id;
$gett->contest_id = $contest_id;
$contestant = $gett->getUsersCurrent(1)[0];

// Get the Commments
$gett->contestant_id = $contestant_id; 
$gett->contest_id = $contest_id;
$comments = $gett->doComments(1, 'post', 1);

$comments_data = '<div class="row">';

$reply_comment = '';
if ($comments) { 
	$pp = 0;
    foreach ($comments as $rs => $key) {
		$pp = $pp+1;
        
        $gett->comment_id = $key['id'];
        $gett->reply_id = $key['reply_id'];
		$replies = $gett->doComments(0, 'post', 2);

		if ($replies) {
			foreach ($replies as $comment => $rs) {
				$dd=strtotime($key['date']);

            	$userApp->user_id = $rs['writer_id'];
				$replyer = $userApp->userData(NULL, 1)[0];
				$r_photo = $SETT['url'].'/uploads/faces/'.$replyer['photo'];
				$real_replyer_name = realName($replyer['username'], $replyer['fname'], $replyer['lname']);
				$repurl = '<a class="text-white" href="'.permalink($SETT['url'].'/index.php?a=profile&u='.$replyer['username']).'">'.$real_replyer_name.'</a>';

				$reply_comment .='
				<span class="pb-1 d-flex justify-content-end">
					<div class="card border-light" style="width: 70%;">
						<div class="chip chip-lg aqua-gradient white-text m-1" style="margin-bottom: -1rem;"> 
							<img src="'.$r_photo.'" alt="'.$real_replyer_name.'"> '.$repurl.' 	
						</div> 
					  
					    <div class="card-text text-muted px-2 text-info">'.date("D M d - h:i:s A", $dd).'</div>
					    <p class="card-text px-3">'.$rs['comment'].'</p> 
					</div>
				</span>'; 
			}
		} else {
			$reply_comment ='';
		}

		$userApp->user_id = $key['writer_id'];
		$writer = $userApp->userData(NULL, 1)[0];
		$w_photo = $SETT['url'].'/uploads/faces/'.$writer['photo'];
		$real_writer_name = realName($writer['username'], $writer['fname'], $writer['lname']); 
		$comurl = '<a class="text-white" href="'.permalink($SETT['url'].'/index.php?a=profile&u='.$writer['username']).'">'.$real_writer_name.'</a>';
		 $d=strtotime($key['date']);

		$comments_data .= '<span id="notice_'.$key['id'].'"></span>
		<div class="col-md-12" id="comment_'.$key['id'].'">
			<div class="card border-light mb-3 p-2" style="max-width: 100%;">
			  <div class="m-1">
			  	<div class="chip chip-lg aqua-gradient white-text" style="margin-bottom: -1rem;"> 
			  		<img src="'.$w_photo.'" alt="'.$real_writer_name.'"> '.$comurl.' 
			  		<i class="close fa fa-times" onclick="delete_the('.$key['id'].', 9, null, 1)"></i>
			  	</div> 
			  		<div class="card-text text-muted pt-2 text-info">'.date("D M d - h:i:s A", $d).'</div>
			  </div>

			    <span class="p-1">'.$key['comment'].'</span>
			    <hr><span>Replies...</span>
			    '.$reply_comment.'
	 
			  <div class="card-footer">
				<form>
				  <div class="form-row align-items-center">
				    <div class="col-auto" style="min-width: 70%;"> 
				      <input type="text" name="reply" class="form-control form-control-sm mb-sm-0" id="reply_'.$key['writer_id'].'_'.$pp.'" placeholder="Reply...">  
				    </div> 
				    <div class="col-auto">
				      <span id="r_popover" class="d-inline-block" data-toggle="popover" data-content=""> </span>
				      <button type="button" class="btn btn-light btn-info btn-sm" onclick="addComment('.$user['id'].', '.$contestant_id.', '.$contest_id.', 3, '.$key['id'].')">Post</button>
				    </div>
				  </div>
				</form>
			  </div>
			</div> 
	      </div>';
    }
} else {
	$comments_data .= '
	<h2 class="d-flex justify-content-center text-center text-info p-5">No comments available!</h2>';
}  

$comments_data .='	
</div>
	<div id="return-message"></div>';

$footer ='
<form>
    <div class="form-row">
      <textarea name="comment" class="form-control z-depth-1" class="form-control" id="comment-box" rows="3" placeholder="Whats on your mind..."></textarea>   
      <button id="comment-btn" type="button" class="btn btn-info btn-sm" onclick="addComment('.$user['id'].', '.$contestant_id.', '.$contest_id.', 3)">Comment</button>
      <span id="c_popover" class="d-inline-block" data-toggle="popover" data-content=""> </span> 
    </div>
</form>'; 

$data = array(
	   'view_content' => $comments_data,
	   'footer' => $footer,
	   'contestant_n' => $contestant['name'], 
	); 
echo json_encode($data, JSON_UNESCAPED_SLASHES);


