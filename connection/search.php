<?php
require_once(__DIR__ .'/../includes/autoload.php');
$user_id = $user['id'];
 
$gett = new contestDelivery;  
$userApp = new userCallback;


$info = '';
$search_resin = '';
if (strlen($_POST['search']) !='') {
  // Make a search query for contests
  (isset($_POST['search'])) ? $gett->search = $_POST['search'] : '';
  $c_results = $gett->getContest(); 

  // Make a search query for profiles
  (isset($_POST['search'])) ? $userApp->search = $_POST['search'] : '';

  $userApp->search_by = 'username';
  $results = $userApp->userData();
} else {
  $info .= '<div class="col-md-12 justify-content-center d-flex col-md-4 mt-2 text-info h6">Search query too short</div><hr class="bg-danger"> '; 
  $search_resin .= '<div class="col-md-12 justify-content-center d-flex col-md-4 mt-3 text-warning h6">Search query too short</div>';
  $results = '';
  $c_results = '';
}

        $echo =''; 
        $echo .= '<div class="row">';
        if ($c_results) { 
          shuffle($c_results);
          $echo .= '<div class="col-md-12 deep-blue-gradient text-dark p-1 h6">Found Contests</div>';
          foreach ($c_results as $rs => $key) {

            if ($key['active'] == 1) {
                if ($key['votes']>0) {
                    $d = 'Voted '.$key['votes'].' times'; $c = 'badge-success';
                } else {
                    $d = 'No Votes'; $c = 'badge-warning';
                }
            } else {
                $d = 'Inactive'; $c = 'badge-danger';
            }
            if ($key['cover'] == '') {
              $photo = 'default.jpg';
            } else {
              $photo = $key['cover'];
            }

            $echo .= ' 
                <div class="col-md-4 mt-2">
                  <div class="card mb-1 aqua-gradient h-100">
   
                    <div class="view overlay">
                      <img class="card-img-top" src="'.$CONF['url'].'/uploads/cover/contest/'.$photo.'" alt="'.$key['title'].'"  style="display: block; object-position: 50% 50%; width: 100%; height: 100%;   object-fit: cover;" id="photo_'.$key['id'].'">
                      <a onclick="profileModal('.$key['id'].', '.$key['id'].', 2)">
                        <div class="mask rgba-white-light flex-center font-weight-bold">Quick Preview</div>
                      </a>
                    </div>

                    <div class="card-body">
                      <a onclick="shareModal(1, '.$key['id'].')" class="activator waves-effect waves-light mr-2"><i class="fa fa-share-alt"></i></a> 
                      <a href="'.permalink($CONF['url'].'/index.php?a=contest&s='.$key['safelink']).'" class="black-text" id="contest-url'.$key['id'].'"><h4>'.$key['title'].' <i class="fa fa-angle-double-right"></i></h4></a> 
                    </div>
                    <div class="card-footer cloudy-knoxville-gradient"> 
                        <span class="badge badge-pill '.$c.'">'.$d.'</span></div>
                  </div>
                </div>  
              ';
          }
        } else {
          $info .= '<div class="col-md-12 justify-content-center d-flex col-md-4 mt-3 text-warning h5">No Contests found</div> ';
        }
        $echo .= '</div> '; 

        // Search for profiles
        if ($results) { 
          shuffle($results);
          $echo .= '<div class="row mt-3">';
          $echo .= '<div class="col-md-12 winter-neva-gradient text-dark p-1 mt-3 h6">Found Users</div>';
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

            $premium_status = $userApp->premiumStatus($key['id'], 2);
            $badge = ($premium_status) ? badge(0, $premium_status['plan'], 2) : '';

            $fullname = $badge.' '.realName($key['username'], $key['fname'], $key['lname']);

            $echo .= '
                <div class="col-md-4 mt-2">
                  <div class="card m-1 aqua-gradient h-100">
   
                    <div class="view overlay">
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
                </div> 
              ';
          }
          $echo .= '</div> ';
        } else {
          $info .= '<div class="col-md-12 justify-content-center d-flex col-md-4 mt-3 text-warning h5">No Profiles found</div> ';
        }

        // Show contests search results in sidebar search
        $search_res ='';  
        if ($c_results) { 
          $search_res .= '<div class="col-md-12 deep-blue-gradient text-dark h6">Found Contests</div>';
          foreach ($c_results as $rs => $key) {

            if ($key['active'] == 1) {
                if ($key['votes']>0) {
                    $d = $key['votes'];
                    $c = 'badge-success';
                } else {
                    $d = '0'; 
                    $c = 'badge-warning';
                }
            } else {
                $d = 'x'; $c = 'badge-danger';
            }
            if ($key['cover'] == '') {
              $photo = 'default.jpg';
            } else {
              $photo = $key['cover'];
            }

            $search_res .= ' 
                <div class="bg-light text-dark px-2 my-1">
                  <a href="'.permalink($CONF['url'].'/index.php?a=contest&s='.$key['safelink']).'" class="black-text h6" id="contest-url'.$key['id'].'">'.$key['title'].'  
                  <span class="badge badge-pill '.$c.'">'.$d.'</span></a>
                  <img class="card-img-top" src="'.$CONF['url'].'/uploads/cover/contest/'.$photo.'" alt="'.$key['title'].'"  style="display: block; object-position: 10% 10%; width: 100%; height: 5vh;   object-fit: cover;" id="photo_'.$key['id'].'"> 
                </div>    
              ';
          }
        } else {
          $search_resin .= '<div class="col-md-12 justify-content-center d-flex col-md-4 mt-3 text-warning h6">No Contests found</div> ';
        }  

        // Show profiles search results in sidebar search
        if ($results) { 
          $search_res .= '<div class="col-md-12 deep-blue-gradient text-dark h6">Found Profiles</div>';
          foreach ($results as $rs => $key) {

            if ($key['role'] == 'agency') {
                $c = 'badge-success';
                $d = 'A';
            } elseif ($key['role'] == 'contestant') {
                $c = 'badge-info';
                $d = 'C';
            } else {
                $c = 'badge-warning';
                $d = 'V';
            }
            if ($key['photo'] == '') {
              $photo = 'default.jpg';
            } else {
              $photo = $key['photo'];
            }

            $premium_status = $userApp->premiumStatus($key['id'], 2);
            $badge = ($premium_status) ? badge(0, $premium_status['plan'], 2) : '';

            $fullname = $badge.' '.realName($key['username'], $key['fname'], $key['lname']);

            $search_res .= ' 
                <div class="bg-light text-dark px-2 my-1">
                  <a href="'.permalink($CONF['url'].'/index.php?a=profile&u='.$key['username']).'" class="black-text h6" id="profile-url'.$key['id'].'">'.$fullname.' </a>
                  <img class="card-img-top" src="'.$CONF['url'].'/uploads/faces/'.$photo.'" alt="'.$key['username'].'"  style="display: block; object-position: 10% 10%; width: 100%; height: 5vh;   object-fit: cover;" id="pphoto_'.$key['id'].'"> 
                </div>    
              '; 
          } 
        } else {
          $search_resin .= '<div class="col-md-12 justify-content-center d-flex col-md-4 mt-3 text-warning h6">No Contests found</div>';
        }

        if ($_POST['type'] == 1) {
          echo $echo;
          echo $info;
        } else {
          echo $search_res;
          echo $search_resin;
        }