<?php
require_once(__DIR__ .'/../includes/autoload.php');
$user_id = $_POST['contestant_id'];
$contest_id = $_POST['contest_id'];
 
$gett = new contestDelivery;
$userApp = new userCallback;

$gett->contestant_id = $user_id;
$gett->contest_id = $contest_id;
$contestant = $gett->getUsersCurrent(1)[0];

$userApp->user_id = $user_id;
$c_user = $userApp->userData(NULL, 1)[0];

$gallery_photo = '';

// If this is a contests
$navi = '';
if ($_POST['view'] == 'contest') {
  $c_contest = $gett->getContest(null, $contest_id);

  // Get the contest cover or display the default photo
  $c_photo = getImage($c_contest['cover'], 2); 
  $get_user = $c_contest;
  $name = $c_contest['title'];
  $intro = $c_contest['intro']; 
  $link = '<a href="'.permalink($SETT['url'].'/index.php?a=contest&s='.$c_contest['safelink']).'" class="text-left">Contest Details</a>';
  $sharer = '<a class="px-3 text-info" onclick="shareModal(1, '.$contest_id.')"><i class="fa fa-share"></i> '.$LANG['share'].'</a>';

  // Else show user details
} else {

  // Get the users photo or display the default photo
  $c_photo = getImage($c_user['photo'], 1); 

  if (count($contestant) > 0) {
    $get_user = $gett->getUsersCurrent(1)[0];
    $name = $get_user['name'];
  } else {
    $get_user = $c_user;
    $name = $get_user['fname'].' '.$get_user['lname'];
  }  
  $from = 'From '.$get_user['city'].', '.$get_user['state'].', '.$get_user['country'].'.';
  $intro = $c_user['intro'];
  $link = ($user['role'] == 'agency' || $user_id == $user['id']) ? '<a href="'.permalink($SETT['url'].'/index.php?a=enter&viewdata='.$user_id).'" class="text-left">View full bio</a>.' : '';
  $sharer = '<a class="px-3 text-info" onclick="shareModal(2, '.$user_id.')"><i class="fa fa-share"></i> '.$LANG['share'].'</a>';

  // Get the users gallery images
  $photos_cards = $userApp->user_gallery($user_id, 1); 

  if ($photos_cards) {
    foreach ($photos_cards as $photo) {
      $g_photo = getImage($photo, 1);
      $gallery_photo .='
      <div class="carousel-item">
        <img class="d-block w-100" src="'.$g_photo.'" alt="'.$c_user['username'].' gallery photo '.$photo['id'].'">
      </div>';   
    }
    $navi = '
    <a class="carousel-control-prev" href="#carousel-thumb" role="button" data-slide="prev">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#carousel-thumb" role="button" data-slide="next">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="sr-only">Next</span>
    </a>';

  } else {
    $gallery_photo = '';
  }

}

// You can add new carousel items by adding duplicating or replicating  the carousel item and removing the active class from the others, you can use a foreach loop to achieve a gallery

$details = '   
  <div class="row">  
		<div class="col-md-7">

      <div id="carousel-thumb" class="carousel slide carousel-fade carousel-thumbnails" data-ride="carousel">
        <div class="carousel-inner" role="listbox">

          <div class="carousel-item active">
            <img class="d-block w-100" src="'.$c_photo.'" alt="'.$c_user['username'].'\'s '.$LANG['profile'].' photo">
          </div> 
          '.$gallery_photo.'
        </div>
        '.$navi.'
      </div> 
    </div> 
    
    <div class="col-md-5">
      <h2 class="h2-responsive product-name">
        <strong>'.$name.'</strong>
      </h2>  

      <h5 class="mb-0 pb-2">
        '.$from.'
      </h5> 

     <div>
       '.$intro.'.
     </div> 
        '.$link.' '.$sharer.'        
    </div>
    
  </div> ';
        
$data = array(
     'view_content' => $details, 
  ); 
echo json_encode($data, JSON_UNESCAPED_SLASHES);
