<?php 
require_once(__DIR__ .'/../includes/autoload.php');
$user_id = $user['id'];
 
$userApp = new userCallback;
$gett = new contestDelivery;

// Pagination Navigation settings
$perpage = $settings['per_table'];  


if(isset($_POST['page']) & !empty($_POST['page'])){
    $curpage = $_POST['page'];
} else{
    $curpage = 1;
}
$start = ($curpage * $perpage) - $perpage;

$gett = new contestDelivery;

$count = count($userApp->viewGenerated($_POST['contest']));
$userApp->limit = $perpage;
$userApp->start = $start; 
$results = $userApp->viewGenerated($_POST['contest']);

// Pagination Logic
$endpage = ceil($count/$perpage);
$startpage = 1;
$nextpage = $curpage + 1;
$previouspage = $curpage - 1;
$nb = 0;

      $table_content=''; 

  if ($results) {
    foreach ($results as $rs => $key) {
      $nb = $nb+1; 

      $userApp->user_id = $key['user_id'];
      $data = $userApp->userData(NULL, 1)[0];

      $gett->contest_id = $key['contest_id'];
      $gett->contestant_id = $key['user_id'];
      $contest_data = $gett->getUsersCurrent(1)[0];

      $fullname = realName($data['username'], $data['fname'], $data['lname']);

      $table_content .= '  
                          <tr id="user_'.$key['user_id'].'">
                            <th scope="row">'.$nb.'</th> 
                            <td>
                              <a data-toggle="tooltip" data-placement="top" title="View Public Profile" href="'.permalink($CONF['url'].'/index.php?a=profile&u='.$data['username']).'">'.$fullname.'</a>
                            </td>
                            <td>'.$data['city'].'</td>
                            <td>'.$data['state'].'</td>  
                            <td>'.$data['country'].'</td> 
                            <td>'.$contest_data['votes'].'</td> 
                            <td>
                              <a id="delete-button" href="#" onclick="delete_the('.$key['user_id'].', 2)"><i class="fa fa-trash text-danger px-1"></i></a> 
                              <a id="edit-button" href="'.permalink($CONF['url'].'/index.php?a=enter&manage='.$key['contest_id'].'&user='.$key['user_id']).'"><i class="fa fa-edit text-primary px-1"></i></a>
                            </td>  
                          </tr> '; 
    }
  } else {
    $table_content .= '<tr><td colspan="7" class="text-info h3">You have not created any profiles.</td></tr>';
  } 

  $loader = '<div class="mt-2 text-center"><div class="saving-load mr-auto"></div>';

  $navigation = '';

  if ($endpage > 1) {
    if ($curpage != $startpage) {
      $navigation .= '<a href="#" onclick="loadTable('.$start.', '.$perpage.', '.$startpage.', 1, '.$_POST['contest'].')" class="text-black mx-1"><i class="fa fa-angle-double-left"></i></a>';
    }

    if ($curpage >= 2) {
      $navigation .= '<a href="#" onclick="loadTable('.$start.', '.$perpage.', '.$previouspage.', 1, '.$_POST['contest'].')" class="text-black mx-1"><i class="fa fa-angle-left"></i></a>';
    }
      $navigation .= '<a href="#" onclick="loadTable('.$start.', '.$perpage.', '.$curpage.', 1, '.$_POST['contest'].')" class="text-black mx-1"><i class="fa fa-th-large"></i></a>';

    if($curpage != $endpage){
      $navigation .= '<a href="#" onclick="loadTable('.$start.', '.$perpage.', '.$nextpage.', 1, '.$_POST['contest'].')" class="text-black mx-1"><i class="fa fa-angle-right"></i></a>';

      $navigation .= '<a href="#" onclick="loadTable('.$start.', '.$perpage.', '.$endpage.', 1, '.$_POST['contest'].')" class="text-black mx-1"><i class="fa fa-angle-double-right"></i></a>';
    }

    $navigation .= '<p class="px-4">Page '.$curpage.' of '.$endpage.'</p>';

  } else {$navigation .= '';}
  $data = array('table_content' => $table_content, 'loader' => $loader, 'navigation' => $navigation);
  echo json_encode($data, JSON_UNESCAPED_SLASHES); 