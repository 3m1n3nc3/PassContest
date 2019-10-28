<?php 
require_once(__DIR__ .'/../includes/autoload.php');
$user_id = $user['id'];
 
$gett = new contestDelivery; 

// Pagination Navigation settings
$perpage = $settings['per_table']; 

if(isset($_POST['page']) & !empty($_POST['page'])){
    $curpage = $_POST['page'];
} else{
    $curpage = 1;
}
$start = ($curpage * $perpage) - $perpage;

$count = count($gett->myVotes());
$gett->limit = $perpage;
$gett->start = $start; 
$results = $gett->myVotes();  

// Pagination Logic
$endpage = ceil($count/$perpage);
$startpage = 1;
$nextpage = $curpage + 1;
$previouspage = $curpage - 1;
$nb = 0;

  $table_content=''; 
  $no_content = '';
  if ($results) {
    foreach ($results as $rs => $key) {
      $nb = $nb+1; 

      $contest_info = $gett->getContest(0, $key['contest_id']);

      $userApp->user_id = $key['contestant_id'];
      $data = $userApp->userData(NULL, 1)[0];

      $gett->contest_id = $key['contest_id']; 
      $gett->contestant_id = $key['contestant_id']; 
      $contest_data = $gett->getUsersCurrent(1)[0];

      $fullname = realName($data['username'], $data['fname'], $data['lname']);

      $table_content .= '  
                          <tr id="vote_'.$key['id'].'">
                            <th scope="row">'.$nb.'</th> 
                            <td>
                              <a data-toggle="tooltip" data-placement="top" title="View Public Profile" href="'.permalink($SETT['url'].'/index.php?a=profile&u='.$data['username']).'">'.$fullname.'</a>
                            </td>
                            <td>'.$contest_info['title'].'</td>
                            <td>'.$contest_data['votes'].'</td> 
                            <td>'.$marxTime	->dateFormat($key['date'], 1).'</td>    
                          </tr> '; 
    }
  } else {
    $url = permalink($SETT['url'].'/index.php?a=contest');
    $no_content = 'You have not voted for anybody!<br> '.sprintf($LANG['click_here'], $url);
  } 

  $loader = '<div class="mt-2 text-center"><div class="saving-load mr-auto"></div>';

  $navigation = '';

  if ($endpage > 1) {
    if ($curpage != $startpage) {
      $navigation .= '<a href="#" onclick="loadAccountsTable('.$start.', '.$perpage.', '.$startpage.', 1)" class="mx-3"><i class="fa fa-angle-double-left"></i></a>';
    }

    if ($curpage >= 2) {
      $navigation .= '<a href="#" onclick="loadAccountsTable('.$start.', '.$perpage.', '.$previouspage.', 1)" class="mx-3"><i class="fa fa-angle-left"></i></a>';
    }
      $navigation .= '<a href="#" onclick="loadAccountsTable('.$start.', '.$perpage.', '.$curpage.', 1)" class="mx-3"><i class="fa fa-th-large"></i></a>';

    if($curpage != $endpage){
      $navigation .= '<a href="#" onclick="loadAccountsTable('.$start.', '.$perpage.', '.$nextpage.', 1)" class="mx-3"><i class="fa fa-angle-right"></i></a>';

      $navigation .= '<a href="#" onclick="loadAccountsTable('.$start.', '.$perpage.', '.$endpage.', 1)" class="mx-3"><i class="fa fa-angle-double-right"></i></a>';
    }

    $navigation .= '<p class="px-4">Page '.$curpage.' of '.$endpage.'</p>';

  } else {$navigation .= '';}
  $data = array('table_content' => $table_content, 'loader' => $loader, 'navigation' => $navigation, 'no_content' => $no_content);
  echo json_encode($data, JSON_UNESCAPED_SLASHES); 
