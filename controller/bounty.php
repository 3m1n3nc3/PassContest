<?php

function mainContent() {
	global $PTMPL, $LANG, $CONF, $DB, $user, $settings;

	$uc = new userCallback;
	$save = new siteClass;
	$social = new social;

    // Check if user is a premium user
    $prem_status = $uc->premiumStatus(null, 2);
    $prem_check = $uc->premiumStatus(null, 1); 
    if ($settings['premium']) {
        if ($prem_check) {
            if ($prem_status['plan'] == 'slight_plan' || $prem_status['plan'] == 'lite_plan') {
                $st = 0;
            } else {
                $st = 1;
            }
        } else {
            $st = 0;
        }
    } else {
        $st = 1;
    }  
	$bars = new barMenus;
	$PTMPL['adsbar'] = $bars->ads($settings['ads_off']);

	$side_bar = new sidebarClass;
	$PTMPL['shared_menu'] = $side_bar->user_navigation();
	$PTMPL['sidebar_menu'] = $side_bar->pre_manage_menu();
	$PTMPL['recommended'] = recomendations();

	$PTMPL['page_title'] = $LANG['bounty'];

	$PTMPL_old = $PTMPL; $PTMPL = array();

	if ($user) {
	    // Update online status
	    $social->online_state($user['id'], null, 1);

		$theme = new themer('bounty/bounty'); $container = '';

		$link = permalink($CONF['url'].'/index.php?a=welcome&ref='.$user['username']);
		$ref_link = '
		<div class="d-flex p-2 border m-2 justify-content-around blue-grey lighten-5 rounded">
			<div class="font-weight-bold w-75">'.$LANG['your_ref_link'].':</div> 
			<input type="text" id="referal-link" class="form-control" value="'.$link.'">  
		</div>';
		$PTMPL['note'] = sprintf($LANG['ref_note'], $settings['pc_ref_percent']);

		// Check if this user has a supported plan
			$content = '';
		if ($st == 1) {
			$PTMPL['ref_link'] = $ref_link; 
			
			$nb = 1;
			// Get the referrals for this user
			$refs = $save->referrals(1);
			if ($refs) {
				foreach ($refs as $key => $data) {
				  	$username = '<a href="'.permalink($CONF['url'].'/index.php?a=profile&u='.$data['username']).'">'.$data['username'].'</a>';
				    $content .=
				    '<tr>
				      <th scope="row">'.$nb++.'</th>
				      <td>'.$username.'</td>
				      <td>'.$data['fname'].'</td>
				      <td>'.$data['lname'].'</td>
				      <td>'.$data['role'].'</td>
				    </tr>'; 
				}				 
			} else {
				$content .='<tr><td colspan="5" class="text-center h3">'.$LANG['no_ref'].'<td></tr>';				
			}
 

		} else {
			$content .='<tr><td colspan="5" class="text-center h3">'.$LANG['not_refable'].'<td></tr>';
		}
		$PTMPL['tbody'] = $content;		 
	} else {
		header("Location:".permalink($CONF['url'].'/index.php?a=welcome'));
	}

	$container = $theme->make();
	$PTMPL = $PTMPL_old; unset($PTMPL_old);
	$PTMPL['container'] = $container;

	$theme = new themer('bounty/content');
	return $theme->make();	
}
?>