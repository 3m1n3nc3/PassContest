<?php 
require_once(__DIR__ .'/../includes/autoload.php');
$user_id = $user['id'];
 
$userApp = new userCallback;

// Pagination Navigation settings
$perpage = $settings['per_explore']; 


if(isset($_POST['page']) & !empty($_POST['page'])){
    $curpage = $_POST['page'];
} else{
    $curpage = 1;
}
$start = ($curpage * $perpage) - $perpage;

$count = count($userApp->userData());
$userApp->limit = $perpage;
$userApp->start = $start; 
$results = $userApp->userData();

// Pagination Logic
$endpage = ceil($count/$perpage);
$startpage = 1;
$nextpage = $curpage + 1;
$previouspage = $curpage - 1;

if ($results) {
  echo '<div class="row">';
  foreach ($results as $rs => $key) {

    if ($key['role'] == 'agency') {
        $c = 'badge-success';
        $d = 'Agency';
    } elseif ($key['role'] == 'contestant') {
        $c = 'badge-info';
        $d = 'Contestant';
    } else {
        $c = 'badge-warning';
        $d = 'Voter';
    }
    if ($key['photo'] == '') {
      $photo = 'default.jpg';
    } else {
      $photo = $key['photo'];
    } 

    $user_data = $userApp->collectUserName($key['username'], 0);
    $fullname = $user_data['fullname'];

    echo '
    <div class="col-md-4 mt-2">
      <div class="card m-1 aqua-gradient">

        <div class="view overlay h-100"> 
          <img class="card-img-top" src="'.$CONF['url'].'/uploads/faces/'.$photo.'" alt="'.$key['username'].'"  style="display: block; object-position: 50% 50%; width: 100%; height: 100%;   object-fit: cover;" id="photo_'.$key['id'].'">
          <a onclick="profileModal('.$key['id'].', '.$key['id'].', 0)">
            <div class="mask rgba-white-light flex-center font-weight-bold">Quick Preview</div>
          </a>
        </div>

        <div class="card-body">
          <a onclick="shareModal(2, '.$key['id'].')" class="activator waves-effect waves-light mr-2"><i class="fa fa-share-alt"></i></a> 
          <a href="'.permalink($CONF['url'].'/index.php?a=profile&u='.$key['username']).'" class="black-text text-left" id="profile-url'.$key['id'].'"><h4>'.$fullname.' <i class="fa fa-angle-double-right"></i></h4></a> 
        </div>
        <div class="card-footer cloudy-knoxville-gradient"> 
            <span class="badge badge-pill '.$c.'">'.$d.'</span></div>
      </div>                
    </div>';
  }
    echo '</div> '; 
} else {
  echo '<h1 class="container text-info p-4">No '.$LANG['profile'].'s</h1> ';
}

$navigation = '';
if ($endpage > 1) {
  if ($curpage != $startpage) {
    $navigation .= '<a class="mx-2" href="#" onclick="loadExplorer('.$start.', '.$perpage.', '.$startpage.', 2)" class="text-black mx-1"><i class="fa fa-angle-double-left"></i></a>';
  }

  if ($curpage >= 2) {
    $navigation .= '<a class="mx-2" href="#" onclick="loadExplorer('.$start.', '.$perpage.', '.$previouspage.', 2)" class="text-black mx-1"><i class="fa fa-angle-left"></i></a>';
  }
    $navigation .= '<a class="mx-2" href="#" onclick="loadExplorer('.$start.', '.$perpage.', '.$curpage.', 2)" class="text-black mx-1"><i class="fa fa-th-large"></i></a>';

  if($curpage != $endpage){
    $navigation .= '<a class="mx-2" href="#" onclick="loadExplorer('.$start.', '.$perpage.', '.$nextpage.', 2)" class="text-black mx-1"><i class="fa fa-angle-right"></i></a>';

    $navigation .= '<a class="mx-2" href="#" onclick="loadExplorer('.$start.', '.$perpage.', '.$endpage.', 2)" class="text-black mx-1"><i class="fa fa-angle-double-right"></i></a>';
  }

  $navigation .= '<p class="px-4">Page '.$curpage.' of '.$endpage.'</p>';

} else {$navigation .= '';}

echo '<div class="mt-5 text-center"><hr class="bg-warning">' .$navigation. '</div>';