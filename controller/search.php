<?php
 
function mainContent() {
	global $PTMPL, $LANG, $SETT, $DB, $user, $settings, $userApp;
	$social = new social;
	$bars = new barMenus;
	$side_bar = new sidebarClass;
	$site_class = new siteClass;

	if ($user) {
		// Update online status
		$social->online_state($user['id'], null, 1);
		$tt = isset($_GET['query']) ? ' :: '.$_GET['query'] : '';
		$PTMPL['page_title'] = $LANG['search'].$tt;

		$PTMPL['adsbar'] = $bars->ads($settings['ads_off']);
		$PTMPL['shared_menu'] = $side_bar->user_navigation();
		$PTMPL['sidebar_menu'] = $side_bar->pre_manage_menu();
		$PTMPL['recommended'] = recomendations(); 

		// Prepare the search query
		if (isset($_POST['query'])) {
			if (isset($_GET['filters'])) {
				redirect('search&filters='.$_GET['filters'].'&query='.urlencode($_POST['query']));
			} else {
				redirect('search&query='.urlencode($_POST['query']));
			}
		}

		$unset = isset($_GET['filters']) && $_GET['filters'] == 'unset' ? redirect('search') : '';
		$PTMPL['filters'] = filters(isset($_GET['filters']) ? $_GET['filters'] : '', isset($_GET['query']) ? $_GET['query'] : null); 

		// Start a search operation
		if (isset($_GET['query'])) {
			$PTMPL['query'] = $_GET['query'];

			$search_query = urlencode($_GET['query']);

			// Set the results limit 
		    $perpage = $settings['per_contest'];  //Results to show per page

		    if(isset($_GET['offset'])) {
		        $curpage = $_GET['offset'];
		    } else{
		        $curpage = 1;
		    }
	    	$start = ($curpage * $perpage) - $perpage;


			// find the # tags 
			if (tag_finder($_GET['query']) == 1) {
				$site_class->tags = true;
				$q =substr($_GET['query'],1);
				$site_class->hash_amp = tag_finder($_GET['query']);

				// Count the results
				$count = $site_class->searchEngine($q);
				$count = count($count['posts']) + count($count['users']) + count($count['contests']);

				// Set the current page and limit
	 			$site_class->offset = $start;
	 			$site_class->limit = $perpage;

	 			// Fetch the results
				$results = $site_class->searchEngine($q);
			} else { 
				$site_class->filters = isset($_GET['filters']) ? $_GET['filters'] : null;

				// Count the results
				$count = $site_class->searchEngine($_GET['query']);
				$count = count($count['posts']) + count($count['users']) + count($count['contests']) / 3;
				

				// Set the current page and limit
	 			$site_class->offset = $start;
	 			$site_class->limit = $perpage;

	 			// Fetch the results
				$site_class->filters = isset($_GET['filters']) ? $_GET['filters'] : null;
				$results = $site_class->searchEngine($_GET['query']);
			} 			
			// Enable pagination
		    $endpage = ceil($count/$perpage);
		    $startpage = 1;
		    $nextpage = $curpage + 1;
		    $previouspage = $curpage - 1;
		    $nb = 0;
		    $filt = isset($_GET['filters']) ? $_GET['filters'] : '';
		    $quer = isset($_GET['query']) ? $_GET['query'] : ''; 
		    $url = permalink($SETT['url'].'/index.php?a=search&filters='.$filt.'&query='.$quer);
		    $PTMPL['page_navigator'] = page_navigator($url, $startpage, $previouspage, $nextpage, $curpage, $endpage, 'offset');

			$c_data = $u_data = [];
			$main = '';
			if (mb_strlen($_GET['query'])<=0 || (!$results['users'] && !$results['contests'] && !$results['posts'])) {
				$main = empty_results();
			} else {
				if ($results['users']) {
					foreach ($results['users'] as $rs) { 
						$u_data = $userApp->collectUserName(null, 0, $rs['uid']); 
						$intro = myTruncate($u_data['mainintro'], 100, ' ');
						$main .= 
						'<div class="col-md-12 col-lg-6 border-bottom">
						  <div class="media mt-4 px-1 text-left">
						    <img class="no-borders card-img-100 d-flex z-depth-1 mr-3" src="'.getImage($u_data['photo'], 1).'"
						          alt="'.$u_data['name'].'">
						    <div class="media-body">
						      <h5 class="font-weight-bold mt-0">
						        <a href="'.$u_data['profile'].'">'.$u_data['fullname'].'</a>
						        <br><small class="text-success">'.ucfirst($rs['role']).'</small>
						      </h5>
						      '.$intro.'
						    </div>
						  </div>
						</div><hr>';
					} 
				}
				if ($results['posts']) {
					foreach ($results['posts'] as $rs) {
						$u_data = $userApp->collectUserName(null, 0, $rs['user_id']);
						if (!$u_data) {
							$u_data = $userApp->collectUserName(null, 0, $rs['share_id']);
						}
						$photo = isset($rs['post_photo']) ? getImage($rs['post_photo'], 1) : getImage($u_data['photo'], 1); 
						$intro = myTruncate($rs['text'], 100, ' ');
						$title = myTruncate($rs['text'], 25, ' '); 
						$lnk = permalink($SETT['url'].'/index.php?a=timeline&u='.$u_data['username'].'&read='.$rs['pid']);
						$main .= 
						'<div class="col-md-12 col-lg-6 border-bottom">
						  <div class="media mt-4 px-1 text-left">
						    <img class="no-borders card-img-100 d-flex z-depth-1 mr-3" src="'.$photo.'"
						          alt="'.$title.'">
						    <div class="media-body">
						      <h5 class="font-weight-bold mt-0">
						        <a href="'.$lnk.'">'.$title.'</a>
						        <br><small class="text-success">'.$LANG['post'].' by '.$u_data['name'].'</small>
						      </h5>
						      '.$intro.'
						    </div>
						  </div>
						</div>';					
					} 
				}				
				if ($results['contests']) { 
					foreach ($results['contests'] as $rs) {
						$c_data = $userApp->collectUserName(null, 1, $rs['cid']); 
						$intro = myTruncate($c_data['mainintro'], 100, ' ');
						$type = $c_data['type'] == 'popularity' || $c_data['type'] == 'photo' ? $c_data['type'].' '.$LANG['contest'] : $c_data['type'];
						$main .= 
						'<div class="col-md-12 col-lg-6 border-bottom">
						  <div class="media mt-4 px-1 text-left">
						    <img class="no-borders card-img-100 d-flex z-depth-1 mr-3" src="'.getImage($c_data['photo'], 1).'"
						          alt="'.$c_data['title'].'">
						    <div class="media-body">
						      <h5 class="font-weight-bold mt-0">
						        <a href="'.$c_data['safelink'].'">'.$c_data['title'].'</a>
						        <br><small class="text-success">'.ucfirst($type).'</small>
						      </h5>
						      '.$intro.'
						    </div>
						  </div>
						</div>';					
					} 
				} 								
			}
		} else {
			$main = empty_results(2);
		}	

		$PTMPL['cards'] = $main;

		// Process the query
		$PTMPL['action'] = permalink($SETT['url'].'/index.php?a=search&q=');

		$PTMPL['seo_plugin'] = seo_plugin(0, 0, 0, $PTMPL['page_title'], $PTMPL['page_title']);

		$theme = new themer('explore/search');
		return $theme->make();
	} else {
		header('Location: '.permalink($SETT['url'].'/index.php?a=featured'));
	}
}
?>
