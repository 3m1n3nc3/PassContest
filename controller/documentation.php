<?php

function mainContent() {
	global $PTMPL, $LANG, $CONF, $DB, $user, $settings, $admin, $marxTime; 
	// Whole function displays static pages
	$PTMPL['page_title'] = $settings['site_name'].' '.$LANG['documentation']; 
	$site_class = new siteClass;
	$bars = new barMenus;

	$PTMPL['recommended'] = recomendations();
	$PTMPL['adsbar'] = $bars->ads($settings['ads_off']);

	// List all the available pages  
	$site_class->what = 'content <> \'\' ORDER BY featured DESC, date_posted DESC';
	$get_pages = $site_class->static_pages(0, 1);  

	$intro = $site_class->static_pages(0, 2)[0];

	// Show buttons to compose and return
	$write_btn ='';
	$write_btn .= ($admin) ? '<a class="btn btn-info btn-rounded btn-md" href="'.permalink($CONF['url'].'/index.php?a=documentation&write=new').'">Write New Documentation</a>' : '';

	$write_btn .= ($user) ? '<a class="btn btn-info btn-rounded btn-md" href="'.permalink($CONF['url'].'/index.php?a=documentation&support=review').'">Support Tickets</a>' : '';

	$PTMPL['write_btn'] = $write_btn;

	$edit_int = ($admin) ? '<a class="px-2" href="'.permalink($CONF['url'].'/index.php?a=documentation&edit='.$intro['id'].'&type=2').'">Edit <i class="fa fa-edit text-info"></i></a>' : '';
	$return_btn = '<a class="px-2" href="'.permalink($CONF['url'].'/index.php?a=documentation').'"><i class="fa fa-arrow-left text-info"></i> Return </a>';
		if ($intro) {
			$PTMPL['intro'] = (!isset($_GET['read'])) ? '<p class="dark-grey-text w-responsive mx-auto mb-5">'.strip_tags($intro['content']).$edit_int.'</p>' : '';
		} 

		$PTMPL['title'] = strip_tags($intro['title']); 

	if (isset($_GET['write']) || isset($_GET['edit'])) {

		// Get all avalable pages
		$site_class->what = sprintf("id = '%s' ", isset($_GET['edit']) ? $_GET['edit'] : '');
		$edit_page = (!isset($_GET['type'])) ? $site_class->static_pages(0, 1)[0] : $site_class->static_pages(0, 2)[0];
 
 		// Set the selected category
		if ($edit_page['category'] == 'updates') {
			$PTMPL['updates_on'] = 'selected'; 
		} elseif ($edit_page['category'] == 'documentation') {
			$PTMPL['documentation_on'] = 'selected'; 
		} elseif ($edit_page['category'] == 'developer') {
			$PTMPL['developer_on'] = 'selected'; 
		} elseif ($edit_page['category'] == 'support') { 
			$PTMPL['support_on'] = 'selected'; 
		} else {
			$PTMPL['bounty_on'] = 'selected'; 
		} 
		// Set the feature status
		if ($edit_page['featured'] == 1) { 
			$PTMPL['featured_on'] = 'selected'; 
		} else {
			$PTMPL['featured_off'] = 'selected'; 
		}
		// Set the page status
		if ($edit_page['status'] == 1) { 
			$PTMPL['page_status_on'] = 'selected'; 
		} else {
			$PTMPL['page_status_off'] = 'selected'; 
		}
		$PTMPL['return_btn'] = $return_btn;

		$theme = new themer('static/editor');
		$PTMPL['create_button'] = (isset($_GET['write'])) ? '<button name="create" type="submit" class="btn btn-info my-2 waves-effect" id="save">Create</button>' :
		$PTMPL['create_button'] = '<button name="update" type="submit" class="btn btn-info my-2 waves-effect" id="save">Update</button>';
		if (isset($_GET['edit'])) {
			$PTMPL['d_title'] = $edit_page['title'];
			$PTMPL['page_alias'] = $edit_page['link'];
			$PTMPL['page_content'] = $edit_page['content'];
		}
		if (isset($_POST['create'])) {
			// Validate the new pages  
			$message = '';
			$site_class->what = sprintf('link = \'%s\'', $_POST['page_alias']);
			$ver_page = $site_class->static_pages(0, 1)[0]; 
			if (empty($_POST['page_title'])) {
				$message = infoMessage('Page Title should not be empty');
			} elseif (empty($_POST['page_alias'])) {
				$message = infoMessage('Page Alias should not be empty');
			} elseif ($ver_page['link'] == $_POST['page_alias']){
				$message = infoMessage('Page Alias should be unique');
			} else {
				$site_class->link = $_POST['page_alias'];
				$site_class->title = $_POST['page_title'];
				$site_class->content = $_POST['page_content'];
				$site_class->category = $_POST['category'];
				$site_class->status = $_POST['page_status'];
				$site_class->featured = $_POST['featured'];
				$return = $site_class->static_pages(4, 1); 
				$message = ($return == 1) ? successMessage('New documentation created') : infoMessage($return); 
			} 
			$PTMPL['message'] = $message;	 
		}
		if (isset($_POST['update'])) {
			$site_class->link = $_POST['page_alias'];
			$site_class->title = $_POST['page_title'];
			$site_class->content = $_POST['page_content'];
			$site_class->category = $_POST['category'];
			$site_class->status = $_POST['page_status'];
			$site_class->featured = $_POST['featured'];
			$site_class->type = ($edit_page['type'] == 2) ? 2 : 1;
			$return = $site_class->static_pages(5, 1, $_GET['edit']); 
			$PTMPL['message'] = ($return == 1) ? successMessage('Documentation updated') : infoMessage($return); 
		}
		$PTMPL['page_editor'] = $theme->make(); 
	} elseif (isset($_GET['delete'])) {
		 $return = $site_class->static_pages(3, 1, $_GET['delete']);
		 $msg = urlencode($return);
		 ($return == 1) ? header('Location: '.permalink($CONF['url'].'/index.php?a=documentation&ret=true')) : header('Location: '.permalink($CONF['url'].'/index.php?a=documentation&ret=false&msg='.$msg)); 
	} elseif (isset($_GET['read']) && $_GET['read'] !=='') {
		// Open the sellected document
		$site_class->what = sprintf('link = \'%s\'', $_GET['read']);
		$read = $site_class->static_pages(0, 1)[0];	
		$PTMPL['return_btn'] = $return_btn;

		// Show the opened document
		$content ='';
		if ($read) {
			$edit = '<a class="px-2" href="'.permalink($CONF['url'].'/index.php?a=documentation&edit='.$read['id']).'">Edit <i class="fa fa-edit text-info"></i></a>';
			$PTMPL['page_title'] = $PTMPL['page_title'].' - '.stripslashes($read['title']);		
			$content .= '<h4 class="h4-responsive font-weight-bold my-2"> -'.stripslashes($read['title']).'</h4>';
			$content .= '<p class="font-weight-normal px-5">'.stripslashes($read['content']).' '.$edit.'<hr class="mt-4 mb-2"></p>';
			$PTMPL['content'] = $content; 
		} else {
			$PTMPL['page_title'] = 'Document not found';
			$theme = new themer('welcome/404');
			return $theme->make();			
		}

	} elseif (isset($_GET['support'])) { 
		$PTMPL_old = $PTMPL; $PTMPL = array();
		$PTMPL['this_title'] = $LANG['support_tickets'];

		if ($_GET['support'] == 'new_ticket' || $_GET['support'] == 'reply') {
			$theme = new themer('static/support_form'); $support = '';
			$PTMPL['create_button'] = ($_GET['support'] == 'new_ticket') ? '<button name="create" type="submit" class="btn btn-info my-2 waves-effect" id="send">Send</button>' :
			 '<button name="update" type="submit" class="btn btn-info my-2 waves-effect" id="send">Reply</button>';
 
			if (isset($_POST['create']) || isset($_POST['update'])) {
				// Prepare for validation
				$site_class->what = sprintf('message = \'%s\' AND user_id = \'%s\'', $_POST['message'], $user['id']);
				$ver = $site_class->support_system(0)[0];

				// Create a new support ticket
				if (empty($_POST['subject'])) {
					$message = infoMessage('Subject is empty');
				} elseif (empty($_POST['message'])) {
					$message = infoMessage('Message is empty');
				} elseif ($_POST['message'] == $ver['message']) {
					$message = infoMessage('This ticket has been sent before');
				} else {
					$site_class->subject = $_POST['subject'];
					$site_class->message = $_POST['message'];
					$site_class->priority = $_POST['priority'];
					$site_class->type = $_POST['type']; 
					$ret = (isset($_POST['create'])) ? $site_class->support_system(1) : $site_class->support_system(1, $_GET['id']); 
					$message = ($ret == 1) ? successMessage('Ticket Sent') : infoMessage($ret);					
				}
				$pass = fetch_api(2);
				$PTMPL['message_'] = $message;
			}	 
		} else {
			$theme = new themer('static/support'); $support = '';
			$site_class->what = sprintf('user_id = \'%s\' AND reply=\'0\'', $user['id']);
			$messages = $site_class->support_system(0);

			$review = '';
			if ($user) {
				$review .= '
					<a href="'.permalink($CONF['url'].'/index.php?a=documentation&support=new_ticket').'">
					    <div class="hoverable d-inline-block blue lighten-4 border border-info m-1 float-left white-text p-1">
					   		New Ticket
					    </div><div class="clearfix"></div>
					</a>';
				$get_reply = '';
				if ($messages) { 
					$pp = 0;
				    foreach ($messages as $rs => $key) {
						$pp = $pp+1;
						$mtime = $marxTime->timeAgo(strtotime($key['date']));

						// Fetch Replys
						$site_class->what = sprintf('reply = \'%s\' ORDER BY date DESC', $key['id']);
						$replies = $site_class->support_system(0);

						if ($replies) {
							foreach ($replies as $reply => $rs) { 
								$rtime = $marxTime->timeAgo(strtotime($rs['date']));
								$replier = ($rs['user_id'] == $user['id']) ? 'You Replied' : 'Support reply';
								$cc = ($rs['user_id'] == $user['id']) ? 'success' : 'danger';
								$get_reply .='
							    <div class="d-inline-block text-left grey lighten-4 border m-1 float-right" style="max-width:90%; min-width:90%">
							    	<div class="float-left font-weight-bold px-2 teal-text">'.$replier.'
							    		<div class="text-'.$cc.'">'.$rs['subject'].'</div><small class="text-info">'.$rtime.'</small>
							    	</div>
							    	'.$rs['message'].'
							    </div> <div class="clearfix"></div>'; 
							}
						} else {
							$get_reply ='';
						}

						$review .=' 
						  <div class="p-2 my-2 bg-white border text-left">  
						    <div class="d-inline-block float-left font-weight-bold px-2"> '.$key['subject'].'
						      <div class="date">'.$mtime.'</div>
							    <a href="'.permalink($CONF['url'].'/index.php?a=documentation&support=reply&id='.$key['id']).'">
								    <div class="hoverable d-inline-block light-blue lighten-1 border border-primary m-1 float-left white-text p-1 font-weight-normal">
								   		Send Reply
								    </div>
								</a>						      
						    </div>
						    <div class="font-weight-normal">
						    	'.$key['message'].'
						    </div>
						      '.$get_reply.'
						    <div class="clearfix"></div>
						  </div>'; 
				    }
				} else {
					$review .= '<h2 class="d-flex justify-content-center text-center text-info p-5">No tickets!</h2>';
				}				 
			} else {
				$review .= '<h2 class="d-flex justify-content-center text-center text-info p-5">'.$LANG['login_ticket'].'</h2>';
			}

			$PTMPL['review'] = $review;			
		}

		$support = $theme->make();
		$PTMPL = $PTMPL_old; unset($PTMPL_old);
 		$PTMPL['page_editor'] = $support;
	} else { 
		// List all publicly available documents
		if (isset($_GET['ret'])) {
			$message = ($_GET['ret'] == 'true') ? successMessage('Document Deleted') : infoMessage($_GET['msg']);
			$PTMPL['message'] = $message;
		}
		$list_pages = '';
		if ($get_pages) { 
			foreach($get_pages as $list => $key) {
				$detail = myTruncate(strip_tags($key['content']), 120, ' ');
				$date = explode('-', $key['date_posted']); $date = $date[1].'/'.substr($date[2], 0, 2).'/'.$date[0];   
				if ($key['category'] == 'updates') {
					$color = 'red';
					$icon = 'fa-fire';
				} elseif ($key['category'] == 'documentation') {
					$color = 'green';
					$icon = 'fa-newspaper-o';
				} elseif ($key['category'] == 'developer') {
					$color = 'blue';
					$icon = 'fa-cogs';
				} elseif ($key['category'] == 'support') { 
					$color = 'orange';
					$icon = 'fa-leaf';
				} else {
					$color = 'teal'; 
					$icon = 'fa-key';
				} 
				$col = ($key['featured'] == 1) ? '12' : '6';
				$col2 = ($key['featured'] == 1) ? '6' : '4';

				$editors = ($admin) ? '
					<a class="px-2" href="'.permalink($CONF['url'].'/index.php?a=documentation&edit='.$key['id']).'">Edit <i class="fa fa-edit text-info"></i></a> 
			     	<a class="px-2" href="'.permalink($CONF['url'].'/index.php?a=documentation&delete='.$key['id']).'">Delete <i class="fa fa-trash text-danger"></i></a>' : '';

				$list_pages .= '

			    <div class="col-lg-'.$col2.' col-md-'.$col.' mb-3">
	  
			      <h6 class="font-weight-bold mb-3 '.$color.'-text"><i class="fa '.$icon.' pr-2"></i>'.ucfirst($key['category']).'</h6> 
			      <h4 class="font-weight-bold mb-3"><strong>'.$key['title'].'</strong></h4>
			      <p class="font-weight-bold">'.$date.'</p>
			      <p class="dark-grey-text">'.$detail.'</p>
			      <a href="'.permalink($CONF['url'].'/index.php?a=documentation&read='.$key['link']).'" class="btn btn-'.$color.' btn-rounded btn-md">Read more</a><br>
			      '.$editors.'
			    </div>';
			}					 
		} else {
			$list_pages .= '<h5 class="text-warning text-center">No Documentation</h5>';
		}	
 
		$PTMPL['get_pages'] = $list_pages;		
	} 
	$intro = (isset($read['content'])) ? strip_tags(stripslashes($read['content'])) : $PTMPL['intro'];
	$PTMPL['seo_plugin'] = seo_plugin(0, 0, 0, $intro, $PTMPL['page_title']);
	$theme = new themer('static/docs'); 
	return $theme->make(); 
}
?>