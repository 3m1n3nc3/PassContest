<?php 
require_once(__DIR__ .'/../includes/autoload.php');
$user_id = $user['id'];
 
$userApp = new userCallback;

// Pagination Navigation settings
$perpage = $settings['per_featured']; 

if(isset($_POST['page']) & !empty($_POST['page'])){
    $curpage = $_POST['page'];
} else{
    $curpage = 1;
}
$start = ($curpage * $perpage) - $perpage;

$userApp->featured = true;
$cud = $userApp->userData();
$count = $cud ? count($cud) : '';
$userApp->limit = $perpage;
$userApp->start = $start; 
$results = $userApp->userData();

// Pagination Logic
$endpage = 0;
if ($count) {
  $endpage = ceil($count/$perpage);
}
$startpage = 1;
$nextpage = $curpage + 1;
$previouspage = $curpage - 1;

if ($results) {
  echo '<div class="row">';
  foreach ($results as $rs => $key) {

    if ($key['role'] == 'agency') {
        $c = 'success-color';
        $d = 'Agency';
    } elseif ($key['role'] == 'contestant') {
        $c = 'info-color';
        $d = 'Contestant';
    } else {
        $c = 'danger-color';
        $d = 'Voter';
    }
    if ($key['photo'] == '') {
      $photo = 'default.jpg';
    } else {
      $photo = $key['photo'];
    } 
    
    $user_data = $userApp->collectUserName($key['username'], 0);
    $fullname = $user_data['fullname'];
    $intro = myTruncate($key['intro'], 100, ' ');

      echo '
        <div class="col-md-6 mt-2">
          <div class="card m-1 aqua-gradient h-100">

            <div class="view overlay"> 
              <img class="card-img-top" src="'.$CONF['url'].'/uploads/faces/'.$photo.'" alt="'.$key['username'].'"  style="display: block; object-position: 50% 50%; width: 100%; height: 100%; object-fit: cover;" id="photo_'.$key['id'].'">
              <a onclick="profileModal('.$key['id'].', '.$key['id'].', 0)">
                <div class="mask flex-center rgba-blue-light">
                  <p class="white-text">Quick Preview</p> 
                </div>
              </a>
            </div>

            <div class="card-body">

              <a onclick="shareModal(2, '.$key['id'].')" class="activator waves-effect waves-light mr-2"><i class="fa fa-share-alt"></i></a> 
              <a href="'.permalink($CONF['url'].'/index.php?a=profile&u='.$key['username']).'" class="black-text text-left" id="profile-url'.$key['id'].'"><h4>'.$fullname.' <i class="fa fa-angle-double-right"></i></h4></a> 
            </div>
            <div class="card-footer cloudy-knoxville-gradient"> 
                <span class="badge badge-pill '.$c.'">'.$d.'</span>
                <div class="text-justify">'.$intro.'</div>
            </div>
          </div>                
        </div> 
      ';
  }    
} else {
  echo '<h1 class="container text-info p-4">No '.$LANG['featured'].' '.$LANG['profile'].'s</h1>';
} 

            echo '</div> ';
        $navigation = '';
        if ($endpage > 1) {
          if ($curpage != $startpage) {
            $navigation .= '<a class="mx-2" href="#" onclick="loadFeature('.$start.', '.$perpage.', '.$startpage.', 2)" class="mx-3"><i class="fa fa-angle-double-left"></i></a>';
          }

          if ($curpage >= 2) {
            $navigation .= '<a class="mx-2" href="#" onclick="loadFeature('.$start.', '.$perpage.', '.$previouspage.', 2)" class="mx-3"><i class="fa fa-angle-left"></i></a>';
          }
            $navigation .= '<a class="mx-2" href="#" onclick="loadFeature('.$start.', '.$perpage.', '.$curpage.', 2)" class="mx-3"><i class="fa fa-th-large"></i></a>';

          if($curpage != $endpage){
            $navigation .= '<a class="mx-2" href="#" onclick="loadFeature('.$start.', '.$perpage.', '.$nextpage.', 2)" class="mx-3"><i class="fa fa-angle-right"></i></a>';

            $navigation .= '<a class="mx-2" href="#" onclick="loadFeature('.$start.', '.$perpage.', '.$endpage.', 2)" class="mx-3"><i class="fa fa-angle-double-right"></i></a>';
          }

          $navigation .= '<p class="px-4">Page '.$curpage.' of '.$endpage.'</p>';

        } else {$navigation .= '';}

        echo '<div class="mt-5 text-center"><hr class="bg-warning">' .$navigation. '</div>';
