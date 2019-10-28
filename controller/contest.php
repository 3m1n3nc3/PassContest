<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $DB, $user, $settings;
	$gett = new contestDelivery;
	$userApp = new userCallback;
	$social = new social;

	$premium_status = $userApp->premiumStatus(null, 2);
	$prem_check = $userApp->premiumStatus(null, 1); 

	// Update online status
	if ($user) {
		$social->online_state($user['id'], null, 1);
	}

	if (isset($_GET['u'])) {
		if (isset($_GET['id'])) { 
			$contest = $gett->getContest($_GET['u'], $_GET['id']);		 
		} elseif (isset($_GET['s'])) { 
            $contest = $gett->getContest($_GET['u'], $_GET['s'], 'safelink');  
		} else { 
			$contest = $gett->getContest($_GET['u']);		
		}
	} else {
		if (isset($_GET['id'])) { 
			$contest = $gett->getContest($user['username'], $_GET['id']);		 
		} elseif (isset($_GET['s'])) { 
            $contest = $gett->getContest($user['username'], $_GET['s'], 'safelink');  
		} else { 
			$contest = $gett->getContest();		
		}		
	}
 	if (isset($_GET['id'])) {	
		$PTMPL['contest_types'] = contestTypes(1, $contest['type']);	 
 	} else {
 		$PTMPL['contest_types'] = contestTypes(1, NULL);	
 	} 

	$PTMPL['page_title'] = $LANG['contests']; 
	$bars = new barMenus;
	$PTMPL['adsbar'] = $bars->ads($settings['ads_off']);
	$PTMPL['recommended'] = recomendations();

	$side_bar = new sidebarClass; 
	$PTMPL['sidebar_menu'] = $side_bar->manageMenu();
	$PTMPL['sidebar_menu_pre'] = $side_bar->pre_manage_menu();
	$PTMPL['shared_menu'] = $side_bar->user_navigation();

	if (!isset($_GET['d']) && isset($_GET['id']) ? $_GET['id'] !== '' : '' || isset($_GET['s']) ) { 
		//View the selected contest

		$PTMPL['adsbar'] = $bars->ads($settings['ads_off'], 2);

		$contest_by_id = $gett->getContest(NULL, isset($_GET['id']) ? $_GET['id'] : '');
		$contest_by_s = $gett->getContest(NULL, isset($_GET['s']) ? $_GET['s'] : '', 'safelink');
		$contest_id_cr = $gett->getContest($user['username'], isset($_GET['id']) ? $_GET['id'] : '');
		$contest_s_cr = $gett->getContest($user['username'], isset($_GET['s']) ? $_GET['s'] : '', 'safelink');		

		if (isset($_GET['id']) && $_GET['id'] == $contest_by_id['id']) { 
			 $contest_details = $contest_by_id; 
		} elseif (isset($_GET['s']) && $_GET['s'] == $contest_by_s['safelink']) {
			 $contest_details = $contest_by_s; 
		} elseif (isset($contest_id_cr['creator']) ? $contest_id_cr['creator'] : '' == $user['username'] && isset($_GET['id']) && $_GET['id'] == $contest_id_cr['id']) {
			 $contest_details = $contest_id_cr; 
		} elseif (isset($contest_s_cr['creator']) ? $contest_s_cr['creator'] : '' == $user['username'] && isset($_GET['s']) && $_GET['s'] == $contest_s_cr['safelink']) {
			 $contest_details = $contest_s_cr; 
		} else {
			$theme = new themer('welcome/404');
			return $theme->make();	
		}

		if (isset($contest_details) && $contest_details['creator'] == $user['username']) {
			$PTMPL['top_title'] = $LANG['ongoing'];
			$PTMPL['contest_edit'] = '<a href="'.permalink($SETT['url'].'/index.php?a=contest&d=create&id='.$contest_details['id']).'" data-toggle="tooltip" data-placement="right" title="'.$LANG['edit_contest'].'"><i class="fa fa-edit"></i>'.$LANG['edit_contest'].'</a>';
		} else {
			$PTMPL['top_title'] = $LANG['my_own_contest'];
		}
		if (isset($contest_details)) {
			if ($contest_details['status']) {

				// If entry is open show the enter now button
				if ($contest_details['entry']) {
					if ($user['username']) {
				 		$PTMPL['enter_btn'] = '<a href="'.permalink($SETT['url'].'/index.php?a=enter&id='.$contest_details['id']).'" data-toggle="tooltip" data-placement="right" title="'.$LANG['enter'].'" class="btn btn-info border border-white btn-rounded btn-lg">'.$LANG['enter'].' <i class="fa fa-sign-in text-white"></i></a>';
					} else {
				 		$PTMPL['enter_btn'] = '<a href="'.permalink($SETT['url'].'/index.php?a=enter&id='.$contest_details['id'].'&required=login_enter&referrer='.urlencode(urlReferrer(permalink($SETT['url'].'/index.php?a=enter&id='.$contest_details['id']), 0))).'" data-toggle="tooltip" data-placement="right" title="'.$LANG['enter'].'" class="btn btn-warning border border-white btn-rounded btn-lg">'.$LANG['enter'].' <i class="fa fa-sign-in text-white"></i></a>';

					}
				} else {
					$PTMPL['entry_closed'] = '<span class="d-flex justify-content-center border border-info rounded p-2 text-info z-depth-1 bg-white">'.$LANG['entry_closed'].'</span>';
				}

			 	$PTMPL['view_btn'] = '<a href="'.permalink($SETT['url'].'/index.php?a=voting&id='.$contest_details['id']).'" data-toggle="tooltip" data-placement="right" title="'.$LANG['view_contest'].'" class="btn btn-success border border-white btn-rounded btn-lg">'.$LANG['view_contest'].' <i class="fa fa-thumbs-up text-white"></i></a>';				 
			} else {
					$PTMPL['entry_closed'] = '<span class="d-flex  justify-content-center border border-danger rounded p-2 text-danger z-depth-1 bg-white">'.$LANG['contest_blocked'].'</span>';
			}

		 	$PTMPL['details_cards'] = detailsCards();

			$PTMPL['cover'] = getImage($contest_details['cover'], 2);
			$PTMPL['contest_title'] = stripslashes($contest_details['title']);
			$PTMPL['contest_slug'] = stripslashes($contest_details['slogan']); 
			$PTMPL['contest_intro'] = '<h4 class="black-text">'.stripslashes($contest_details['intro']).'</h4>';  

	        $PTMPL['contest_fb'] = isset($contest_details['facebook'])?'<a href="http://facebook.com/'.$contest_details['facebook'].'" class="p-2 m-2 fa-lg"> <i class="fa fa-facebook fb-ic"> </i> </a>':'';

	        $PTMPL['contest_tw'] = isset($contest_details['twitter'])?'<a href="http://twitter.com/'.$contest_details['twitter'].'" class="p-2 m-2 fa-lg"> <i class="fa fa-twitter tw-ic"> </i> </a>':'';

	        $PTMPL['contest_ins'] = isset($contest_details['instagram'])?'<a href="http://instagram.com/'.$contest_details['instagram'].'" class="p-2 m-2 fa-lg"> <i class="fa fa-instagram ins-ic"> </i> </a>':'';
	        $tw = (isset($contest_details['twitter'])) ? $contest_details['twitter'] : '';
	        $fb = (isset($contest_details['facebook'])) ? $contest_details['facebook'] : '';

			$PTMPL['page_title'] = $PTMPL['contest_title'];
			$PTMPL['seo_plugin'] = seo_plugin($PTMPL['cover'], $tw, $fb, $PTMPL['contest_intro'], $PTMPL['page_title']);
			$theme = new themer('contest/details');
			return $theme->make();		 	 
		 } 
	// Update an existing contest
	} elseif (isset($_GET['d']) && $_GET['d'] == "create" && isset($_GET['id']) && $_GET['id'] !== 'new') {

		$PTMPL['adsbar'] = $bars->ads($settings['ads_off'], 3);
		//If updating the contest
		$contest_details = $gett->getContest($user['username'], $_GET['id']);
		$PTMPL['contest_id'] = $contest_details['id'];
		$PTMPL['title'] = stripslashes($contest_details['title']);
		$PTMPL['cover'] = getImage($contest_details['cover'], 2);
		$PTMPL['type'] = $contest_details['type'];
		$PTMPL['slogan'] = stripslashes($contest_details['slogan']);
		$PTMPL['facebook'] = $contest_details['facebook'];
		$PTMPL['twitter'] = $contest_details['twitter'];
		$PTMPL['instagram'] = $contest_details['instagram'];
		$PTMPL['email'] = stripslashes($contest_details['email']);
		$PTMPL['phone'] = stripslashes($contest_details['phone']);
		$PTMPL['intro'] = stripslashes($contest_details['intro']);
		$PTMPL['eligibility'] = stripslashes($contest_details['eligibility']);
		$PTMPL['prize'] = stripslashes($contest_details['prize']);
		$PTMPL['venue'] = stripslashes($contest_details['venue']);
		$PTMPL['contest_types'] = contestTypes($contest_details['type']);
		$PTMPL['country'] = $contest_details['country'];
		$PTMPL['country2partake'] = countries(1, $contest_details['country']); 
		$PTMPL['cover_direction'] = '&id='.$PTMPL['contest_id'].'&cover='.$PTMPL['cover'];
		$theme = new themer('contest/update');
		return $theme->make();	
	} elseif (isset($_GET['manage'])) { 

		$PTMPL['adsbar'] = $bars->ads($settings['ads_off'], 3);
		//If managing the contest
		$contest_details = $gett->getContest($user['username'], $_GET['manage']);
		$PTMPL['contest_id'] = $contest_details['id'];

		// First check if logged user is the creator of the selected contest
		if ($user['username'] == $contest_details['creator']) {
			// Check the status on or off
			$status = $contest_details['active']; 
			if ($status == '1') {
				$PTMPL['status_check'] = '<span class="text-success">'.$LANG['the_contest_is'].' '.$LANG['active'].'</span>';
				$PTMPL['checked'] = 'checked';
			} else {
				$PTMPL['status_check'] = '<span class="text-danger">'.$LANG['the_contest_is'].' '.$LANG['inactive'].'</span>';
			}

			// Check if voting is allowed
			$allow_vote = $contest_details['allow_vote'];
			if ($allow_vote == '1') {
				$PTMPL['voting_check'] = '<span class="text-success">'.$LANG['voting_is'].' '.$LANG['active'].'</span>';
				$PTMPL['v_checked'] = 'checked';
			} else {
				$PTMPL['voting_check'] = '<span class="text-danger">'.$LANG['voting_is'].' '.$LANG['inactive'].'</span>';
			}

			// Check if social is required
			$require_social = $contest_details['require_social'];
			if ($require_social == '1') {
				$PTMPL['social_required'] = '<span class="text-success">'.$LANG['social_required'].' '.$LANG['active'].'</span>';
				$PTMPL['social_checked'] = 'checked';
			} else {
				$PTMPL['social_required'] = '<span class="text-danger">'.$LANG['social_required'].' '.$LANG['inactive'].'</span>';
			}	

			// Select the entry allowed status
			$entry = array('Not Allowed', 'Allowed');
			$entry_status = '';
			foreach ($entry as $key => $value) {
				if ($key == $contest_details['entry']) {
					$selected = ' selected="selected"';
				} else {
					$selected = '';
				} 
			    $entry_status .= '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
			}
			if (isset($_POST['entry_submit'])) {
				$sql = sprintf("UPDATE " . TABLE_CONTEST . " SET  `entry` =  '%s' " 
	            . " WHERE `id` = %s", $_POST['entry'], $contest_details['id']);  
	            $response = dbProcessor($sql, 0, 1);
	            $PTMPL['response'] = ($response == 1) ? successMessage('Settings Updated') : infoMessage('No changes made');
			}	

			// Select the allow free entry
			$allow_free = array('Not Allowed', 'Allowed');
			$allow_free_status = '';
			foreach ($allow_free as $key => $value) {
				if ($key == $contest_details['allow_free']) {
					$selected = ' selected="selected"';
				} else {
					$selected = '';
				} 
			    $allow_free_status .= '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
			}
			if (isset($_POST['free_entry_submit'])) {
				$sql = sprintf("UPDATE " . TABLE_CONTEST . " SET  `allow_free` =  '%s' " 
	            . " WHERE `id` = %s", $_POST['free_entry'], $contest_details['id']);  
	            $response = dbProcessor($sql, 0, 1);
	            $PTMPL['response'] = ($response == 1) ? successMessage('Settings Updated') : infoMessage('No changes made');
			}
			$PTMPL['entry_status'] = $entry_status;
			$PTMPL['free_entry_status'] = $allow_free_status;

			$PTMPL['get_schedule'] = scheduleList();
			$PTMPL['title'] = stripslashes($contest_details['title']);
			$PTMPL['cover'] = getImage($contest_details['cover'], 2);
			$PTMPL['type'] = $contest_details['type'];
			$PTMPL['slogan'] = stripslashes($contest_details['slogan']);
			$PTMPL['facebook'] = $contest_details['facebook'];
			$PTMPL['twitter'] = $contest_details['twitter'];
			$PTMPL['instagram'] = $contest_details['instagram'];
			$PTMPL['phone'] = stripslashes($contest_details['phone']);
			$PTMPL['intro'] = stripslashes($contest_details['intro']);
			$PTMPL['eligibility'] = stripslashes($contest_details['eligibility']);
			$PTMPL['prize'] = stripslashes($contest_details['prize']);
			$PTMPL['venue'] = stripslashes($contest_details['venue']);
			$PTMPL['contest_types'] = contestTypes($contest_details['type']);
			$PTMPL['country'] = $contest_details['country'];
			$PTMPL['country2partake'] = countries(1, $contest_details['country']); 
			$PTMPL['cover_direction'] = '&id='.$PTMPL['contest_id'].'&cover='.$PTMPL['cover'];
	                             
			$PTMPL['page_title'] = $LANG['manage'].' '.$PTMPL['title'];   
			$theme = new themer('contest/manage');
			return $theme->make();	

			// if the logged in user did not create the contest, redirect to the 'contests created by you' page		
		} else { 
			header("Location: ".permalink($SETT['url'].'/index.php?a=contest&u='.$user['username']));
		}	
	//If creating a new contest
	} elseif (isset($_GET['d']) && $_GET['d'] == "create" && isset($_GET['id']) && $_GET['id'] == 'new') { 
		$PTMPL['adsbar'] = $bars->ads($settings['ads_off'], 3);

		$contest = $gett->getContest($user['username'], 0, 'id', 'ORDER BY `id` DESC LIMIT 0, 1')[0];
		$contest_details = $gett->getContest($user['username'], 0);  

		$PTMPL['new_location'] = permalink($SETT['url'].'/index.php?a=contest&u='.$user['username']);

		$create_btn = '<a id="save1" onclick="addContest()" class="btn btn-info btn-rounded my-2 waves-effect font-weight-bold">Create</a>';
		// Check if premium is on
        if ($settings['premium']) {

        	// Check if user has an active subscription
        	if ($prem_check) {
	            if ($premium_status['plan'] == 'slight_plan') {
	                if (count($contest_details)>=2) {
	                    $PTMPL['create_btn'] = '<span class="text-info font-weight-bold">'.sprintf($LANG['create_limit'], 2, $LANG['contest'].'s').'</span>';
	                } else {
	                    $PTMPL['create_btn'] = $create_btn;
	                }
	            } elseif ($premium_status['plan'] == 'lite_plan') {
	                if (count($contest_details)>=5) {
	                    $PTMPL['create_btn'] = '<span class="text-info font-weight-bold">'.sprintf($LANG['create_limit'], 5, $LANG['contest'].'s').'</span>';
	                } else {
	                    $PTMPL['create_btn'] = $create_btn;	                     
	                }
	            } elseif ($premium_status['plan'] == 'life_plan') { 
	                $PTMPL['create_btn'] = $create_btn;	                     
	            } else {
	                $PTMPL['create_btn'] = '<span class="text-info font-weight-bold">'.sprintf($LANG['upgrade_to_create'], $LANG['contest']).'</span>';
	            }
        	} else {
        		$PTMPL['create_btn'] = '<span class="text-info font-weight-bold">'.$LANG['expired_sub'].' <br><span class="text-success">'.sprintf($LANG['upgrade_to_create'], $LANG['contest']).'</span></span>';
        	}
        } else {
            $PTMPL['create_btn'] = $create_btn;
        }

		$theme = new themer('contest/create'); 
		return $theme->make();	
	} elseif (isset($_GET['applications']) && $_GET['applications'] !== 'create') {
		//View applications to this contest contest
		$PTMPL['adsbar'] = $bars->ads($settings['ads_off'], 2);
		$contest = $gett->getContest($user['username'], $_GET['applications']);

		// First check if logged user is the creator of the selected contest
		if ($user['username'] == $contest['creator']) {
			$PTMPL['app_tables'] = applicationList();
			$PTMPL['contest_id'] = $contest['id'];
			$PTMPL['title'] = $contest['title'];
	 
			$PTMPL['page_title'] = $LANG['applications_to'].' '.$PTMPL['title'];
			$theme = new themer('contest/applications');
			return $theme->make();	

			// if the logged in user did not create the contest, redirect to the 'contests created by you' page		
		} else { 
			header("Location: ".permalink($SETT['url'].'/index.php?a=contest&u='.$user['username']));
		}

		// View all approved contestant to this contest
	}  elseif (isset($_GET['approved'])) {

		$contest = $gett->getContest($user['username'], $_GET['approved']);

		// First check if logged user is the creator of the selected contest
		if ($user['username'] == $contest['creator']) {

			$PTMPL['approved_tables'] = approvedList();
	 		$PTMPL['title'] = $contest['title'];
	 
			$PTMPL['page_title'] = $PTMPL['title'].' '.$LANG['approved_c']; 
			$theme = new themer('contest/approved');
			return $theme->make();	 	

			// if the logged in user did not create the contest, redirect to the 'contests created by you' page		
		} else { 
			header("Location: ".permalink($SETT['url'].'/index.php?a=contest&u='.$user['username']));
		}
	} else { 

		//View all available contests
		if (isset($_GET['u']) && $_GET['u'] == $user['username']) {
			$PTMPL['top_title'] = $LANG['my_own_contest'];
		} else {
			$PTMPL['top_title'] = $LANG['ongoing']; 
		}

		$PTMPL['contest_cards'] = contestCards();
		$theme = new themer('contest/content');
		return $theme->make();	
	}
}
?>
