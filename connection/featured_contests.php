<?php

require_once(__DIR__ .'/../includes/autoload.php');

$user_id = $user['id'];

$gett = new contestDelivery; 

// Pagination Navigation settings
$perpage = $settings['per_featured']; 
if(isset($_POST['page']) & !empty($_POST['page'])){
    $curpage = $_POST['page'];
} else{
    $curpage = 1;
}
$start = ($curpage * $perpage) - $perpage;

$gett->featured = true;
$gtc = $gett->getContest();
$count = $gtc ? count($gtc) : '';
$gett->limit = $perpage;
$gett->start = $start; 
$c_results = $gett->getContest(); 

// Pagination Logic
$endpage = 0;
if ($count) {
  $endpage = ceil($count/$perpage);
}
$startpage = 1;
$nextpage = $curpage + 1;
$previouspage = $curpage - 1;

echo '<div class="row">';

if ($c_results) { 
  foreach ($c_results as $rs => $key) {
    $gett->contest_id = $key['id']; 
    $data = $gett->getApprovedList(); 

    // How many votes
    if ($key['active'] == 1) {
        if ($key['votes']>0) {
            $d = 'Voted '.$key['votes'].' times'; $c = 'success-color';
        } else {
            $d = 'No Votes'; $c = 'danger-color';
        }
    } else {
        $d = 'Inactive'; $c = 'danger-color';
    }

    // How many Contestants
    $cc = (count($data)>0) ? count($data) : 0;
    $ccc = (count($data)>0) ? 'success-color' : 'danger-color';

    // Entry is open
    $ce = ($key['entry']) ? 'Entry is open' : 'Entry is closed';
    $cec = ($key['entry']) ? 'success-color' : 'danger-color';

    // If voting is on
    $cv = ($key['allow_vote']) ? 'Voting is on' : 'Voting is closed';
    $cvc = ($key['allow_vote']) ? 'success-color' : 'danger-color';

    // Covers
    if ($key['cover'] == '') {
      $photo = 'default.jpg';
    } else {
      $photo = $key['cover'];
    }
    $intro = myTruncate($key['intro'], 300, ' ');
    echo '
      <div class="col-12 mt-3">
        <div class="card mb-1 h-75">
          <div class="view overlay"> 
            <img class="card-img-top" src="'.$CONF['url'].'/uploads/cover/contest/'.$photo.'" alt="'.$key['title'].'"  style="display: block; object-position: 50% 50%; width: 100%; height: 100%;   object-fit: cover;" id="photo_'.$key['id'].'">
            <a onclick="profileModal('.$key['id'].', '.$key['id'].', 2)">
              <div class="mask flex-center rgba-blue-light">
                <p class="white-text">Quick Preview</p> 
              </div>
            </a>
          </div>

          <div class="card-body aqua-gradient">
            <a onclick="shareModal(1, '.$key['id'].')" class="activator waves-effect waves-light mr-2"><i class="fa fa-share-alt"></i></a> 
            <a href="'.permalink($CONF['url'].'/index.php?a=contest&s='.$key['safelink']).'" class="black-text" id="contest-url'.$key['id'].'"><h4>'.$key['title'].' <i class="fa fa-angle-double-right"></i></h4></a>
          </div>
          <div class="card-body bg-white text-justify">
            '.$intro.'
          </div> 

          <div class="card-footer cloudy-knoxville-gradient"> 
            <div class="chip '.$c.' white-text lighten-2">'.$d.'</div> 
            <div class="chip '.$ccc.' white-text lighten-2">'.$cc.' Contestants</div> 
            <div class="chip '.$cec.' white-text lighten-2">'.$ce.'</div> 
            <div class="chip '.$cvc.' white-text lighten-2">'.$cv.'</div>  
          </div>
        </div>                
      </div>  
    ';
  }
} else {
  echo '<h1 class="container text-info p-4">No '.$LANG['featured'].' '.$LANG['contest'].'s</h1>';
} 

echo '</div> ';
$navigation = '';
if ($endpage > 1) {
  if ($curpage != $startpage) {
    $navigation .= '<a class="mx-2" href="#" onclick="loadFeature('.$start.', '.$perpage.', '.$startpage.', 1)" class="mx-3"><i class="fa fa-angle-double-left"></i></a>';
  }

  if ($curpage >= 2) {
    $navigation .= '<a class="mx-2" href="#" onclick="loadFeature('.$start.', '.$perpage.', '.$previouspage.', 1)" class="mx-3"><i class="fa fa-angle-left"></i></a>';
  }
    $navigation .= '<a class="mx-2" href="#" onclick="loadFeature('.$start.', '.$perpage.', '.$curpage.', 1)" class="mx-3"><i class="fa fa-th-large"></i></a>';

  if($curpage != $endpage){
    $navigation .= '<a class="mx-2" href="#" onclick="loadFeature('.$start.', '.$perpage.', '.$nextpage.', 1)" class="mx-3"><i class="fa fa-angle-right"></i></a>';

    $navigation .= '<a class="mx-2" href="#" onclick="loadFeature('.$start.', '.$perpage.', '.$endpage.', 1)" class="mx-3"><i class="fa fa-angle-double-right"></i></a>';
  }
  $navigation .= '<p class="px-4">Page '.$curpage.' of '.$endpage.'</p>';
} else {
  $navigation .= '';
}
echo '<div class="mt-5 text-center"><hr class="bg-warning">' .$navigation. '</div>';
