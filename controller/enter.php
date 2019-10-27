<?php
function mainContent() {
	global $PTMPL, $LANG, $CONF, $DB, $user, $settings;
	$gett = new contestDelivery;
	$userApp = new userCallback;
	$save = new siteClass;

	// If the user is not activated  
	if($user && $user['status'] == 0) {  
	    header('Location: '.permalink($CONF['url'].'/index.php?a=welcome'));
	}

	// Apply Buttons
	$buttons_apply = '	
      <div class="col">
        <button name="save" class="btn btn-info my-4 btn-block" type="submit">Save Application</button>
      </div>
      <div class="col">
        <button name="enter" class="btn btn-secondary my-4 btn-block" type="submit">Submit Application</button>
      </div>';

    $buttons_update = '
      <div class="col">
        <button name="save" class="btn btn-info my-4 btn-block" type="submit">Save Data</button>
      </div>';

    $enter_serial = (isset($_GET['success'])) ? '
    <form id="card_serial">
      <div class="border rounded border-danger z-depth-1 py-4 px-2 px-md-5 my-3 deep-blue-gradient">
        <span class="text-dark lighten-5 border z-depth-1 p-2 rounded grey font-weight-bold">'.$LANG['enter_with'].' '.$LANG['contest_card'].'</span>
        <div class="md-form form-sm text-left input-group"> 
          <input onkeyup="enterSerial(0, '.$_GET['success'].')" name="gift_card" type="text" id="gift_card" class="form-control bg-light border border-danger rounded" length="13" autocomplete="off" required>                      
          <div class="input-group-append">
            <button onclick="enterSerial(1, '.$_GET['success'].')" class="btn btn-sm btn-rounded rounded-right m-0 px-3 blue-gradient" type="button">Enter <i class="fa fa-credit-card-alt px-1"></i></button>
          </div>  
          <label for="gift_card" class="mx-2 p-2 text-dark font-weight-bold">'.$LANG['contest_card'].'</label>
        </div>
        <span id="loader"></span>
        <span id="status_val"></span> 
      </div>
    </form>
    <hr class="bg-danger z-depth-4">' : '';

    $PTMPL['this_id'] = (isset($_GET['success'])) ? $_GET['success'] : ''; //get the id of the success page

	$bars = new barMenus;
	$PTMPL['adsbar'] = $bars->ads($settings['ads_off']);

	$side_bar = new sidebarClass;
	$PTMPL['sidebar_menu'] = $side_bar->manageMenu();
	$PTMPL['shared_menu'] = $side_bar->pre_manage_menu();
	$PTMPL['recommended'] = recomendations();

	// Fetch the users balance
	$save->what = sprintf('user = \'%s\'', $user['id']);
	$get_credit = $save->passCredits(0)[0]; 

	// If this user is logged out
	if (isset($_GET['required']) && $_GET['required'] == 'login_enter') {
		$PTMPL['page_title'] = 'Application';
		$PTMPL['information'] = $LANG['login_enter'];
		$PTMPL['connect_btn'] = '<a href="#" class="btn btn-primary btn-md" target="_blank" id="openModal1" data-toggle="modal" data-target="#connectModal">Connect
                <i class="fa fa-cloud ml-2"></i>
              </a>';
		$PTMPL['apologies'] = $LANG['apologies'];
		$theme = new themer('welcome/information');
		return $theme->make();

	//View the selected contest application form
	} elseif ($_GET['a'] == 'enter' && (isset($_GET['id']) ? $_GET['id'] !== '' : '' || isset($_GET['s']))) { 

		$PTMPL['page_title'] = 'Application';
		if (isset($_GET['ret'])) { 
			$PTMPL['response'] = easy_crypt($_GET['ret'], 1); 
		} elseif (isset($_GET['updt'])) { 
			$PTMPL['response'] = easy_crypt($_GET['updt'], 1); 
		}

		if (isset($_GET['id']) && $_GET['id'] == $gett->getContest(NULL, $_GET['id'])['id'] ) {
			 $contest_details = $gett->getContest(NULL, $_GET['id']); 
		} elseif (isset($_GET['s']) && $_GET['s'] == $gett->getContest(NULL, $_GET['s'], 'safelink')['safelink'] ) {
			 $contest_details = $gett->getContest(NULL, $_GET['s'], 'safelink'); 
		} else {
			$theme = new themer('welcome/404');
			return $theme->make();	
		}

		if (isset($contest_details)) { 
			$vrf = $gett->viewApplications(0, 0, $user['id']);
			if ($user['id'] == $vrf['user_id']) { 
				$response = $LANG['congratulations'];
				$msg = easy_crypt($response);
				header("Location: ".permalink($CONF['url'].'/index.php?a=enter&success='.$_GET['id'].'&ret='.$msg)); 
			}
			// Fill the application form
			$PTMPL['buttons_action'] = $buttons_apply;
			$PTMPL['countries'] = countries(1, $contest_details['country']);
	    	$PTMPL['get_user_id'] = $user['id']; 
			$PTMPL['contest_id'] = $contest_details['id'];
			$PTMPL['contest_title'] = $LANG['enter_w']. ' ' .$contest_details['title']; 
			$PTMPL['only_once'] = $LANG['only_once'];
			$theme = new themer('enter/contest');
			return $theme->make();		 	 
		} 
		
		// Return the success message
	} elseif (isset($_GET['success'])) { 
		$vrf = $gett->viewApplications($_GET['success'], 4, $user['id']);
		$contest_details = $gett->getContest(NULL, $_GET['success']);

		// Set the social icons
		$facebook = $contest_details['facebook'];
		$twitter = $contest_details['twitter'];
		$instagram = $contest_details['instagram'];  

		// Social Links
		$social_now = '';
		$social_now .='
	       <div class="card cloudy-knoxville-gradient">
	         <div class="card-body mt-1 mx-auto align-middle p-2">';
	    $social_now .= ($facebook) ? '     
	            <a class="px-3" href="https://facebook.com/'.$facebook.'"><i class="fa fa-facebook-square fa-2x fb-ic"></i></a>' : '';
	    $social_now .= ($twitter) ? '  
	            <a class="px-3" href="https://twitter.com/'.$twitter.'"><i class="fa fa-twitter-square fa-2x tw-ic"></i></a>' : '';
	    $social_now .= ($instagram) ? '
	            <a class="px-3" href="https://instagram.com/'.$instagram.'"><i class="fa fa-instagram fa-2x ins-ic"></i></a>' : '';
	    $social_now .='
	         </div>
	       </div>';

		// if the user is only updating their information
		if (isset($_GET['updt'])) {
			$PTMPL['page_title'] = 'Info Saved';
			$PTMPL['congrats'] = 'Info Saved';
			$PTMPL['welcome_msg'] = $LANG['save_msg_p1'];
			$PTMPL['enter_now_data'] = '<span class="text-success">'.easy_crypt($_GET['updt'], 1).'</span>';
		// if the user is using a coupon gift card
		} elseif (isset($_GET['process']) && $_GET['process'] == 'giftcard' && $user['id'] == $vrf['user_id']) { 
			$PTMPL['page_title'] = 'Entry Success';
			$PTMPL['welcome_msg'] = sprintf($LANG['succ_msg'], $contest_details['title']);
			$PTMPL['congrats'] = $LANG['premium_congrats'];
			// messages
			$PTMPL['for_now'] = $LANG['for_now'];
			$PTMPL['social_now'] = $social_now;
		// If the user will be charged from passcredit
		} elseif (isset($_GET['process']) && $_GET['process'] == 'passcredit' && $user['id'] !== $vrf['user_id']) {
			$balance = $get_credit['balance'] - $settings['pc_enter'];
			$save->balance = $balance; 
			if ($get_credit['balance'] > $settings['pc_enter']) {
				 $return = $save->passCredits(1, $user['id']);
				 $notice = sprintf($LANG['charge_notice'], $settings['pc_enter'], $settings['pc_symbol'], 'Entry');
			} else {
				$notice = sprintf($LANG['insufficient'], $LANG['passcredit'], $LANG['enter']);
				$return = 0;
			}
			 
			if ($return) {
				// Get the creators data 
				$cr_data = $userApp->userData($contest_details['creator']);
				// Fetch the creators balance
				$save->what = sprintf('user = \'%s\'', $cr_data['id']);
				$cr_credit = $save->passCredits(0)[0]; 

				// If the creator is not the applicant add credit to him
				if ($contest_details['creator'] !== $user['username']) {
					$balance = $cr_credit['balance'] + ($settings['pc_agent_percent'] * $settings['pc_enter'] / 100);
					$save->balance = $balance; 
					if ($cr_credit) {
						$save->passCredits(1, $cr_data['id']);
					} else {  
						$save->passCredits(2, $cr_data['id']);
					}					 
				}
				$gett->method = 'passcredit';
				$PTMPL['enter_now_data'] = successMessage($gett->enterContest($_GET['success']));
				// Enter the user to the contest
				$PTMPL['page_title'] = 'Entry Success';
				$PTMPL['welcome_msg'] = sprintf($LANG['succ_msg'], $contest_details['title']);
				$PTMPL['congrats'] = $LANG['premium_congrats'];	
				$xx = 'info';		
			} else {
				// Failed to enter
				$PTMPL['page_title'] = 'Insufficient';
				$PTMPL['welcome_msg'] = sprintf($LANG['insufficient'], $LANG['passcredit'], $LANG['enter']);
				$PTMPL['congrats'] = 'Insufficient '.$LANG['passcredit'].'s';	
				$xx = 'danger';				
			}
			// messages
			$PTMPL['for_now'] = $LANG['for_now'];
			$PTMPL['social_now'] = $social_now;	
			$PTMPL['social_now'] .= '<div class="p-4 font-weight-bold text-'.$xx.'">'.$notice.'</div>';
	
		// If the user is trying to enter a contest and is already in the contest								 
		} elseif ($user['id'] == $vrf['user_id']) {
			$PTMPL['page_title'] = $LANG['check_point'];
			$PTMPL['welcome_msg'] = $LANG['ready_msg'];
			$PTMPL['congrats'] = $LANG['premium_congrats']; 
			
			// Show the social buttons
			$PTMPL['for_now'] = $LANG['for_now'];
			$PTMPL['social_now'] = $social_now;						
		} else {
			// If the user is not yet in the contest
			$gett->contestant_id = $user['id'];
			$gett->contest_id = $_GET['success'];
			$cfm = $gett->getUsersCurrent(1);
			$user_cfm = $gett->getUsersCurrent(0); 

			$user_prem = $userApp->premiumStatus(null, 2);
			$user_prem_check = $userApp->premiumStatus(null, 1); 
			$prem_val = badge(null, $user_prem['plan'], 3); 

			if ($settings['premium']) {
	        	// Check if user has an active subscription
	        	if ($user_prem_check) {
		            if ($prem_val == 2) {
		            	//If the user has a clead_plan drop a limit to 3 contest
		            	$limiter = (count($user_cfm)>=3) ? 1 : 0 ; 
		            } elseif ($prem_val == 3) {
		            	//If the user has a cmarx_plan drop a limit to 5 contest
		            	$limiter = (count($user_cfm)>=5) ? 2 : 0 ; 
		            } elseif ($prem_val == 6) {
		            	//0 If the user has a life_plan, remove all limits
	 					$limiter = 0; 
		            } else {
		                $limiter = 3;
		            }
	        	} else {
	        		//If the user does not have a supported plan
	        		// Check if the contest allows free users
	        		if ($contest_details['allow_free']) {
	        			$limiter = (count($user_cfm)>=1) ? 4 : 0 ; 
	        		} else {
	        			$limiter = (count($user_cfm)>=0) ? 5 : 0 ; 
	        		}
	        	}
	        } else {
	            $limiter = 0; //0If premium is off
	        }

			// Charge the user's credit
			$charge = '';
			if ($settings['pc_vote']>0.00 && $get_credit['balance'] > $settings['pc_enter']) {  
				//If the user has enough balance
				$charge .= '<div class="border text-center m-1 rounded grey lighten-5 p-2">'
				.sprintf($LANG['enter_warning'], $settings['pc_enter'], $settings['pc_symbol'], $LANG['passcredit']).'... 
					<span> <a href="'.permalink($CONF['url'].'/index.php?a=enter&success='.$_GET['success']
					.'&process=passcredit').'">'.$LANG['pay_with'].' '.$LANG['passcredit'].'</a></span></div>';		
			} 			
	        $PTMPL['enter_serial'] = ($limiter !== 0) ? $charge : '';
	        $PTMPL['enter_serial'] .= ($limiter !== 0) ? $enter_serial : '';

	        // If the user has passed the premium test
	        if ($limiter == 0) {
				$PTMPL['page_title'] = 'Entry Success';

				$PTMPL['contest_title'] = $contest_details['title'];

				// Start checking if the contest creator has reached his limits
				$c_creator = $userApp->collectUserName($contest_details['creator'], 0);  
				$prem_check = $userApp->premiumStatus($c_creator['user_id'], 1); 

				// If admin has enabled premium 
				if ($settings['premium']) {

		        	// Check if contest creator has an active subscription
		        	if ($prem_check) { 
						if ($user['id'] !== $cfm[0]['contestant_id']) {
							$gett->method = 'premium';
							$PTMPL['enter_now_data'] = successMessage($gett->enterContest($_GET['success']));
							$PTMPL['welcome_msg'] = sprintf($LANG['succ_msg'], $PTMPL['contest_title']);
							$PTMPL['congrats'] = $LANG['premium_congrats'];
						} else {
							$PTMPL['welcome_msg'] = $LANG['enrolled_msg'];
							$PTMPL['share_msg'] = $LANG['share_msg'];
							$PTMPL['congrats'] = $LANG['premium_congrats'];
						} 
		        	} else {
		        		$PTMPL['welcome_msg'] = '<span class="text-warning"><i class="fa fa-info-circle fa-2x text-danger"></i><br>'.$LANG['expired_creator'].' <br><span class="text-success"></span></span>';
							$PTMPL['congrats'] = $LANG['check_point'];
		        	}

		        // if premium is off just allow the user
		        } else { 
					if ($user['id'] !== $cfm[0]['contestant_id']) {
						$gett->method = 'premium';
						$PTMPL['enter_now_data'] = successMessage($gett->enterContest($_GET['success']));
						$PTMPL['welcome_msg'] = sprintf($LANG['succ_msg'], $PTMPL['contest_title']);
						$PTMPL['congrats'] = $LANG['premium_congrats'];
					} else {
						$PTMPL['welcome_msg'] = $LANG['enrolled_msg'];
						$PTMPL['share_msg'] = $LANG['share_msg'];
						$PTMPL['congrats'] = $LANG['premium_congrats'];
					}	 
		        }  

				// messages
				$PTMPL['for_now'] = $LANG['for_now'];
				$PTMPL['social_now'] = $social_now;

	          // If the user fails the premium check	        	 
	        } elseif ($limiter == 1) {
				$PTMPL['page_title'] = $LANG['check_point'];
	        	$PTMPL['congrats'] = $LANG['check_point'];
	        	$PTMPL['welcome_msg'] = '<span class="text-warning"><i class="fa fa-info-circle fa-2x text-danger"></i><br>'.sprintf($LANG['enter_limit'], 3).' <br><span class="text-success"></span></span>';
	        } elseif ($limiter == 2) {
				$PTMPL['page_title'] = $LANG['check_point'];
	        	$PTMPL['congrats'] = $LANG['check_point'];
	        	$PTMPL['welcome_msg'] = '<span class="text-warning"><i class="fa fa-info-circle fa-2x text-danger"></i><br>'.sprintf($LANG['enter_limit'], 5).' <br><span class="text-success"></span></span>';
	        } elseif ($limiter == 3) {
				$PTMPL['page_title'] = $LANG['check_point'];
	        	$PTMPL['congrats'] = $LANG['check_point'];
	        	$PTMPL['welcome_msg'] = '<span class="text-warning"><i class="fa fa-info-circle fa-2x text-danger"></i><br>'.$LANG['cant_enter_plan'].' <br><span class="text-success"></span></span>';
	        } elseif ($limiter == 4) {
				$PTMPL['page_title'] = $LANG['check_point'];
	        	$PTMPL['congrats'] = $LANG['check_point'];
	        	$PTMPL['welcome_msg'] = '<span class="text-warning"><i class="fa fa-info-circle fa-2x text-danger"></i><br>'.sprintf($LANG['enter_limit'], 1).' <br><span class="text-success"></span></span>';
	        } elseif ($limiter == 5) {
				$PTMPL['page_title'] = $LANG['check_point'];
	        	$PTMPL['congrats'] = $LANG['check_point'];
	        	$PTMPL['welcome_msg'] = '<span class="text-warning"><i class="fa fa-info-circle fa-2x text-danger"></i><br>'.$LANG['cant_enter_plan'].' <br><span class="text-success"></span></span>';
	        }
		}
		$theme = new themer('enter/success');
		return $theme->make();

		// View user information 
	} elseif (isset($_GET['viewdata']) && $_GET['viewdata'] !=='') {
		$PTMPL['adsbar'] = $bars->ads($settings['ads_off'], 3);
	    $gett = new contestDelivery; 
	    $data = $gett->viewApplications(0, 0, $_GET['viewdata']);

	    if ($data['user_id'] == $user['id']) {
	    	$PTMPL['update'] = '<a href="'.permalink($CONF['url'].'/index.php?a=enter&update='.$user['id']).'" data-toggle="tooltip" data-placement="right" title="Update data" class="btn btn-info">Update Data</a>';
	    }
	    	// If the user has filled the profile form
	    if ($_GET['viewdata'] == $data['user_id']) {
			$PTMPL['page_title'] = sprintf($LANG['user_data'], $data['firstname'].' '.$data['lastname']); 
		    // Data definitions
		    $PTMPL['firstname'] = $data['firstname'];
		    $PTMPL['lastname'] = $data['lastname'];
		    $PTMPL['gender'] = $data['gender'];
		    $PTMPL['email'] = $data['email'];
		    $PTMPL['phone'] = $data['phone'];
		    $PTMPL['zip'] = $data['zip'];
		    $PTMPL['city'] = $data['city'];
		    $PTMPL['state'] = $data['state'];
		    $PTMPL['country'] = $data['country'];
		    $PTMPL['address1'] = $data['address1'];
		    $PTMPL['address2'] = $data['address2'];
		    $PTMPL['dob'] = $data['dob'];
		    $PTMPL['pob'] = $data['pob'];
		    $PTMPL['height'] = $data['height'];
		    $PTMPL['weight'] = $data['weight'];
		    $PTMPL['swim'] = $data['swim'];
		    $PTMPL['dress'] = $data['dress'];
		    $PTMPL['shoe'] = $data['shoe'];
		    $PTMPL['work'] = $data['work'];
		    $PTMPL['certificate'] = $data['certificate'];
		    $PTMPL['hobbies'] = $data['hobbies']; 
		    $PTMPL['activities'] = $data['activities']; 
		    $PTMPL['twitter'] = $data['twitter']; 
		    $PTMPL['instagram'] = $data['instagram']; 
		    $PTMPL['food'] = $data['food']; 
		    $PTMPL['color'] = $data['color']; 
		    $PTMPL['sport'] = $data['sport']; 
		    $PTMPL['ambition'] = $data['ambition'];  
		    $PTMPL['performing'] = $data['performing'];  
		    $PTMPL['awards'] = $data['awards'];  
		    $PTMPL['training'] = $data['training'];  
		    $PTMPL['family'] = $data['family'];  
		    $PTMPL['languages'] = $data['languages'];  
		    $PTMPL['liketomeet'] = $data['liketomeet'];  
		    $PTMPL['unusual'] = $data['unusual'];   
		    $PTMPL['moment'] = $data['moment'];   
		    $PTMPL['traveled'] = $data['traveled'];   
		    $PTMPL['statement'] = $data['statement'];   
		    $PTMPL['headshot'] = $CONF['url'].'/uploads/contest/head/'.$data['headshot'];   
		    $PTMPL['fullbody'] = $CONF['url'].'/uploads/contest/body/'.$data['fullbody'];   

			$theme = new themer('enter/viewdata');
			return $theme->make();

			// If users form is empty show the correct error page
	    } else {
	    	if ($_GET['viewdata'] == $user['id']) { 
	    		$url = permalink($CONF['url'].'/index.php?a=contest');
	    		$PTMPL['to_update'] = sprintf($LANG['find_contest_url'], $url);
	    	}
		    $PTMPL['page_title'] = $LANG['no_data'];
		    $PTMPL['information'] = $LANG['empty_data'];
			$PTMPL['apologies'] = $LANG['no_error']; 

			$theme = new themer('welcome/information');
			return $theme->make();		    	
	    }

	// Update the user information (application form)		 	 
	} elseif (isset($_GET['update']) && $_GET['update'] == $user['id']) {
		$PTMPL['page_title'] = 'Update Application Data';
		//Fetch the data
	    $gett = new contestDelivery; 
	    $data = $gett->viewApplications(0, 0, $_GET['update']);
	    $PTMPL['contest_id'] = $data['id'];
	    $PTMPL['get_user_id'] = $user['id'];

	    // Data definitions
	    $PTMPL['firstname'] = $data['firstname'];
	    $PTMPL['lastname'] = $data['lastname'];
	    $PTMPL['email'] = $data['email'];
	    $PTMPL['phone'] = $data['phone'];
	    $PTMPL['zip'] = $data['zip'];
	    $PTMPL['city'] = $data['city'];
	    $PTMPL['state'] = $data['state'];
	    $PTMPL['country'] = $data['country'];
	    $PTMPL['address1'] = $data['address1'];
	    $PTMPL['address2'] = $data['address2'];
	    $PTMPL['dob'] = $data['dob'];
	    $PTMPL['pob'] = $data['pob'];
	    $PTMPL['height'] = $data['height'];
	    $PTMPL['weight'] = $data['weight'];
	    $PTMPL['swim'] = $data['swim'];
	    $PTMPL['dress'] = $data['dress'];
	    $PTMPL['shoe'] = $data['shoe'];
	    $PTMPL['work'] = $data['work'];
	    $PTMPL['certificate'] = $data['certificate'];
	    $PTMPL['hobbies'] = $data['hobbies']; 
	    $PTMPL['activities'] = $data['activities']; 
	    $PTMPL['twitter'] = $data['twitter']; 
	    $PTMPL['instagram'] = $data['instagram']; 
	    $PTMPL['food'] = $data['food']; 
	    $PTMPL['color'] = $data['color']; 
	    $PTMPL['sport'] = $data['sport']; 
	    $PTMPL['ambition'] = $data['ambition'];  
	    $PTMPL['performing'] = $data['performing'];  
	    $PTMPL['awards'] = $data['awards'];  
	    $PTMPL['training'] = $data['training'];  
	    $PTMPL['family'] = $data['family'];  
	    $PTMPL['languages'] = $data['languages'];  
	    $PTMPL['liketomeet'] = $data['liketomeet'];  
	    $PTMPL['unusual'] = $data['unusual'];   
	    $PTMPL['moment'] = $data['moment'];   
	    $PTMPL['traveled'] = $data['traveled'];   
	    $PTMPL['statement'] = $data['statement'];   
	    $PTMPL['headshot'] = $data['headshot'];   
	    $PTMPL['fullbody'] = $data['fullbody'];
	    $PTMPL['contest_title'] = $LANG['update_app'];
	    $PTMPL['buttons_action'] = $buttons_update;

		$theme = new themer('enter/contest');
		return $theme->make();

		//Generate and manage contestant accounts
	} elseif (isset($_GET['create']) && $_GET['create'] !== '') { 
		if (isset($_GET['photo'])) {
			$PTMPL['page_title'] = 'Upload Photo';
			$PTMPL['contestant_id'] = $_GET['photo'];
			$PTMPL['header'] = 'enter&create='.$_GET['create'].'&photo='.$_GET['photo'];
			
			// Show gallery upload status
			$msg = '';
			if (isset($_GET['msg'])) {
				$PTMPL['active'] = 'show active';
				$PTMPL['active_2'] = 'active';
				$msg = urldecode($_GET['msg']);
				if ($_GET['msg'] == 'success') {
					$PTMPL['msg'] = successMessage($LANG['upload_success']);
				} else {
					$PTMPL['msg'] = $msg;
				}
			} else { 
				$PTMPL['default'] = 'show active';
				$PTMPL['default_2'] = 'active';
			}

			// Show uploaded gallery photos in chips
			$PTMPL['chips'] = gallery_chips($_GET['photo']);

			$theme = new themer('enter/picture');
			return $theme->make();
		}
		$premium_status = $userApp->premiumStatus(null, 2);
		$prem_check = $userApp->premiumStatus(null, 1); 

		$create_btn = '<a id="create1" onclick="createContestant(1, 0)" class="btn btn-info btn-rounded my-2 waves-effect">'.$LANG['create'].'</a>';
		$create_btn_2 = '<a id="create2" onclick="createContestant(2, 0)" class="btn btn-info btn-rounded my-2 waves-effect">Save</a>'; 

		$userApp->filter = 'AND creator = \''.$user['id'].'\'';
		$creations = $userApp->userData(); $count = count($creations); 
		// Check if premium is on 
        if ($settings['premium']) {

        	// Check if user has an active subscription
        	if ($prem_check) {
	            if ($premium_status['plan'] == 'slight_plan') {
	                if ($count>=20) {
	                    $PTMPL['create_btn'] = '<span class="text-info font-weight-bold">'.sprintf($LANG['create_limit'], 20, $LANG['contestant_profile']).'</span>';
	                } else {
	                    $PTMPL['create_btn'] = $create_btn;
	                    $PTMPL['create_btn_2'] = $create_btn_2;
	                }
	            } elseif ($premium_status['plan'] == 'lite_plan') {
	                if ($count>=35) {
	                    $PTMPL['create_btn'] = '<span class="text-info font-weight-bold">'.sprintf($LANG['create_limit'], 35, $LANG['contestant_profile']).'</span>';
	                } else {
	                    $PTMPL['create_btn'] = $create_btn;	
	                    $PTMPL['create_btn_2'] = $create_btn_2;                     
	                }
	            } elseif ($premium_status['plan'] == 'life_plan') { 
	                $PTMPL['create_btn'] = $create_btn;
	                $PTMPL['create_btn_2'] = $create_btn_2;	                     
	            } else {
	                $PTMPL['create_btn'] = '<span class="text-info font-weight-bold">'.sprintf($LANG['upgrade_to_create'], $LANG['p_contestant']).'</span>';
	            }
        	} else {
        		$PTMPL['create_btn'] = '<span class="text-info font-weight-bold">'.$LANG['expired_sub'].' <br><span class="text-success">'.sprintf($LANG['upgrade_to_create'], $LANG['p_contestant']).'</span></span>';
        	}
        } else {
            $PTMPL['create_btn'] = $create_btn;
	        $PTMPL['create_btn_2'] = $create_btn_2;
        } 

		$PTMPL['get_id'] = 0;
		$PTMPL['contest_id'] = $_GET['create'];

		$PTMPL['page_title'] = $LANG['c_contestant'];
		$theme = new themer('enter/create');
		return $theme->make();	

		//Manage Generated contestant accounts
	} elseif (isset($_GET['manage']) && $_GET['manage'] !== '') {  

		$PTMPL['adsbar'] = $bars->ads($settings['ads_off'], 2);

		if (isset($_POST['limit'])) {
			$PTMPL['limit'] = $_POST['limit'];
		} else {
			$PTMPL['limit'] = $settings['per_table']; 
		}
		if (isset($_GET['user'])) {
			$PTMPL['get_id'] = $_GET['user'];
			$PTMPL['contest_id'] = $_GET['manage'];

			$userApp->user_id = $_GET['user'];
			$profile = $userApp->userData(NULL, 1)[0];

			$create_btn = '<a id="create1" onclick="createContestant(1, '.$PTMPL['get_id'].')" class="btn btn-info btn-rounded my-2 waves-effect">'.$LANG['save'].'</a>';
			$create_btn_2 = '<a id="create2" onclick="createContestant(2, '.$PTMPL['get_id'].')" class="btn btn-info btn-rounded my-2 waves-effect">Save</a>';

			$PTMPL['create_btn'] = $create_btn;
			$PTMPL['create_btn_2'] = $create_btn_2;
			$PTMPL['email'] = $profile['email'];
			$PTMPL['fname'] = $profile['fname'];
			$PTMPL['lname'] = $profile['lname'];
			$PTMPL['city'] = $profile['city'];
			$PTMPL['state'] = $profile['state'];
			$PTMPL['country'] = $profile['country'];
			$PTMPL['phone'] = $profile['phone'];
			$PTMPL['prof'] = $profile['profession'];
			$PTMPL['facebook'] = $profile['facebook'];
			$PTMPL['twitter'] = $profile['twitter'];
			$PTMPL['instagram'] = $profile['instagram'];
			$PTMPL['loves'] = $profile['lovesto'];
			$PTMPL['intro'] = $profile['intro'];	
		
			$PTMPL['page_title'] = $LANG['update'];
			$theme = new themer('enter/create');
			return $theme->make();
		}  
		$PTMPL['contest_id'] = $_GET['manage'];

		$PTMPL['page_title'] = $LANG['created_prof'];
		$theme = new themer('enter/manage');
		return $theme->make();	
	}  else {
		$theme = new themer('welcome/404');
		return $theme->make();	
	} 
}
?>