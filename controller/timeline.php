<?php

function mainContent() {
	global $PTMPL, $LANG, $SETT, $DB, $user, $settings, $profiles, $marxTime, $premium_status, $userApp, $welcome;
	$cd = new contestDelivery; 
	$bars = new barMenus;
	$side_bar = new sidebarClass;
	$social = new social;
	$action = new actions;
 
	// Update online status
	if ($user) {
		$social->online_state($user['id'], null, 1);
	}
	
	if ($profiles) { 
		$realname = realName($profiles['username'], $profiles['fname'], $profiles['lname']);
		$page_title = $profiles['username'] != $user['username'] ? sprintf($LANG['their_timeline'], $realname) : $LANG['your_timeline']; 
		$PTMPL['page_title'] = $page_title;
		$cd->contestant_id = $profiles['id'];
		$PTMPL['username'] = $profiles['username']; 

		// Show the menus
		$PTMPL['adsbar'] = $bars->ads($settings['ads_off'], 2); 
		$PTMPL['shared_menu'] = $side_bar->user_navigation();
		$PTMPL['sidebar_menu'] = $side_bar->pre_manage_menu();
		$PTMPL['recommended'] = recomendations();  
		$PTMPL['timeline_info'] = $social->timeline_info($profiles['username']);

		$theme = new themer('social/gallery'); $container = '';

		// Set the users header details
		$PTMPL['profile_header'] = profile_header($profiles['id'], 1);

		// Get and manage the users photos
		// Upload form
		$msg = '';
		if (isset($_GET['msg'])) {
			$msg = urldecode($_GET['msg']);
			if ($_GET['msg'] == 'success') {
				$msg = successMessage($LANG['upload_success']);
			} else {
				$msg = $msg;
			}
		}

		$public_priv = permalink($SETT['url'].'/index.php?a=timeline&u='.$profiles['username'].'&privacy=public');
		$follow_priv = permalink($SETT['url'].'/index.php?a=timeline&u='.$profiles['username'].'&privacy=followers');
		$private_priv = permalink($SETT['url'].'/index.php?a=timeline&u='.$profiles['username'].'&privacy=private');
		$privacy_icon = isset($_GET['privacy']) && $_GET['privacy']=='followers' ? 'users' : (isset($_GET['privacy']) && $_GET['privacy']=='private' ? 'user' : 'globe');

		// Set the privacy of the post - 0=private, 1=followers, 2=public
		$post_privacy = isset($_GET['privacy']) && $_GET['privacy']=='followers' ? 1 : (isset($_GET['privacy']) && $_GET['privacy']=='private' ? 0 : 2);

		$public = isset($_GET['privacy']) && $_GET['privacy']=='public' ? 'active' : '';
		$followers = isset($_GET['privacy']) && $_GET['privacy']=='followers' ? 'active' : '';
		$private = isset($_GET['privacy']) && $_GET['privacy']=='private' ? 'active' : ''; 

		// Submit the form
		if (isset($_POST['share'])) { 
			if (!empty($_FILES['photo']['tmp_name'])) { 
				// File arguments
				$errors= array();
				$file_name = $_FILES['photo']['name'];
				$file_size = $_FILES['photo']['size'];
				$file_tmp = $_FILES['photo']['tmp_name'];
				$file_type= $_FILES['photo']['type'];
				$var_string2lower = explode('.',$_FILES['photo']['name']);
				$file_ext = strtolower(end($var_string2lower));
				$expensions= array("jpeg","jpg","png");
				$new_image = mt_rand().'_'.mt_rand().'_'.mt_rand().'_n.'.$file_ext; 
				
				// Check if the file ext is allowed
				if(in_array($file_ext,$expensions)=== false){
				  $errors[]="File not allowed, use a JPEG, JPG or PNG file";
				}
				
				// Check the file size
				if($file_size > 5097152){
				   $errors[].='Image is larger than 10 MB';
				} 

				// Crop and compress the image
				if (in_array($file_ext,$expensions) && empty($errors)==true) {  
			      // Create a new ImageResize object
			      $image = new \Gumlet\ImageResize($file_tmp);	
			      $image->resizeToHeight(800);
			      $image->save('uploads/gallery/'.$new_image);			
				}			
				$new_image = $new_image;
			} else {
				$new_image = null;
			}
			$array = array('photo' => $new_image, 'user_id' => $user['id'], 'share_id' => 0, 'post_id' => 0);
			$array = array_merge($_POST, $array);

			$social->array = $array;
			$post_it = $social->timelines($user['id'], 2); 
		}
 
		$upload_form ='
	      <div class="card wider mb-3"> 
	        <div class="card-body"> 
	          <div class="border border-light bg-white mb-2 m-3">
	            <div class="card-header bg-light">
	              <ul class="nav nav-tabs card-header-tabs" id="myTab" role="tablist">
	                <li class="nav-item">
	                  <a class="nav-link active" id="posts-tab" data-toggle="tab" href="#posts" role="tab" aria-controls="posts" aria-selected="true">'.$LANG['just_write'].'</a>
	                </li>
	                <li class="nav-item">
	                  <a class="nav-link" id="images-tab" data-toggle="tab" role="tab" aria-controls="images" aria-selected="false" href="#images">'.$LANG['add_photo'].'</a>
	                </li>
	              </ul>
	            </div>

	            <div class="p-3">
	              <form action="" method="post" enctype="multipart/form-data">
	                <input type="hidden" name="privacy" value="'.$post_privacy.'">
	                <div class="tab-content" id="myTabContent">
	                  <div class="tab-pane fade show active" id="posts" role="tabpanel" aria-labelledby="posts-tab">
	                    <div class="form-group"> 
	                        <textarea name="post" class="form-control post-text" id="message" rows="3" placeholder="What are you thinking?"></textarea>
	                    </div>
	                  </div>
	                  <div class="tab-pane fade" id="images" role="tabpanel" aria-labelledby="images-tab">
	                    <div class="form-group cam"> 
	                      <textarea name="post_2" class="form-control post-text" id="message" rows="3" placeholder="'.$LANG['what_are_you_thinking'].'"></textarea>
	                      <label for="gallery_image"><i class="fa fa-camera"></i></label>
	                    </div> 
	                    <input type="file" name="photo" id="gallery_image" style="display: none;">                
	                  </div>
	                </div>
	                <div class="btn-toolbar justify-content-between">
	                  <div class="btn-group">
	                    <button type="submit" name="share" class="btn btn-primary">'.$LANG['share'].'</button>
	                  </div>
	                  <div class="btn-group">
	                    <button id="btnGroupDrop1" type="button" class="btn btn-link dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
	                      aria-expanded="false">
	                      <i class="fa fa-'.$privacy_icon.'"></i>
	                    </button>
	                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="btnGroupDrop1">
	                      <a class="dropdown-item '.$public.'" href="'.$public_priv.'"><i class="fa fa-globe"></i> Public</a>
	                      <a class="dropdown-item '.$followers.'" href="'.$follow_priv.'"><i class="fa fa-users"></i> Followers</a>
	                      <a class="dropdown-item '.$private.'" href="'.$private_priv.'"><i class="fa fa-user"></i> Private</a>
	                    </div>
	                  </div>
	                </div> 
	              </form> 
	            </div>
	          </div>
	        </div>
	      </div>
       '; 

        // Prepare to show the post
		$location = profilesCountry($profiles['username']);
		$location = '<small><span><i class="fa fa-map-pin"></i> '.$location.'</span></small>';
		$photos_cards = isset($_GET['read']) ? $social->timelines($_GET['read'], 1) : null;

		// if there is nothing to show
	    $nothing_here = '
	    <div class="col-lg-12" id="photo">
	      <div class="cardbox shadow-md bg-light"> 
	        <div class="cardbox-item h1 peach-gradient text-center text-white p-4">
	          '.$LANG['nothing_to_show'].'
	        </div> 
	      </div>
	    </div>';  

	    if ($photos_cards) {
	    	$post = $photos_cards;

	    	$post_content = $action->decodeMessage($post['text'], 1);

			$PTMPL['seo_plugin'] = seo_plugin($post['post_photo'] ? $SETT['url'].'/uploads/gallery/'.$post['post_photo'] : $welcome['cover'], $profiles['twitter'], $profiles['facebook'], $post['text'], $page_title);

            // Check if you follow username
            $follower = $social->follow($post['user_id'], 1); 

            $post_class = !$post['post_photo'] ? 'aqua-gradient h1-responsive text-white p-3 text-center' : 'm-2';
            $desc = '';
            if ($post['text']) {  
                $desc .= '<div class="p-2 '.$post_class.'" id="description_'.$post['pid'].'">'.$post_content.'</div>'; 
            }

            // Decide if this is an original or shared post
            $author_link = '<a href="%s" class="blue-grey-text">%s</a>';
            if ($post['share_id']) {
                $s = $userApp->collectUserName(null, 0, $post['share_id']);
                $u = $userApp->collectUserName(null, 0, $post['user_id']);
                $sharer = $s['user_id'] == $user['id'] ? 'You' : $s['fullname'];
                $poster = $u['user_id'] == $user['id'] ? 'your' : $s['fullnamex'];
                $post_link = '<a href="'.permalink($SETT['url'].'/index.php?a=timeline&u='.$u['username'].'&read='.$post['pid']).'" class="blue-grey-text">'.lcfirst($LANG['post']).'</a>';
                $author = sprintf($author_link, $s['profile'], $sharer).' '.lcfirst($LANG['shared']).' '.sprintf($author_link, $u['profile'], $poster).' '.$post_link;
                $auto_photo = $s['photo'];
            } else {
                $u = $userApp->collectUserName(null, 0, $post['user_id']); 
                $author = sprintf($author_link, $u['profile'], $u['fullname']);
                $auto_photo = $post['photo'];
            }

		    // Show count comments
		    $cd->post_id = $post['pid'];
		    $get_comments = $cd->doComments(1, 'post', 3);

            // Set the new page title
            $PTMPL['page_title'] = myTruncate($post['text'], 35);

            // Set the photo
            if ($profiles['photo']) {
                $pphoto = $SETT['url'].'/uploads/faces/'.$auto_photo;
            } else {
                $pphoto = $SETT['url'].'/uploads/faces/default.jpg';
            } 

            $post_photo = $post['post_photo'] ? '<img class="img-fluid" src="'.$SETT['url'].'/uploads/gallery/'.$post['post_photo'].'" alt="post_photo" id="post_photo_'.$post['pid'].'">' : ''; 

            if ($user['id'] == $post['user_id'] || $user['id'] == $post['share_id']) {
                $delete = ' 
                <a class="dropdown-item" onclick="delete_the('.$post['pid'].', 8)">'
                .$LANG['delete'].' <i class="fa fa-trash p-1"></i> </a>';
            } else {
                $delete = '<div class="px-3">Hello!</div>';
            }
            $stop_follow = $follower['follower_id']==$user['id'] ? '<a class="dropdown-item" onclick="relate('.$post['user_id'].', 1)">'.$LANG['stop_following'].'</a>' : '';

            $user_profile = permalink($SETT['url'].'/index.php?a=profile&u='.$post['username']);

            // privacy icon
            $privacy_icon = $post['privacy']=='1' ? 'users' : ($post['privacy']=='0' ? 'user' : 'globe');

            // Check Likes
            $social->content_type = 'post'; 
            $social->content = $post['pid'];
            $liked = $social->like(0, 1);
            $all_likes = $social->like(0, 2);

            $social->limit = 5;
            $limit_likes = $social->like(0, 2);

            // See if user liked this post
            $t_class = $liked['user_id'] == $user['id'] ? 'text-info' : '';
            $likes_count = count($all_likes)>1 ? count($all_likes). ' '.$LANG['likes'] : count($all_likes). ' '.$LANG['likes'];

            // Show Comments
            $read_comments = $cd->timelineComments($post['user_id'], $post['pid']);

            //Show users who liked
            if ($limit_likes) {
                $liking = '';
                foreach ($limit_likes as $key) {
                    $userApp->user_id = $key['user_id'];
                    $lk_user = $userApp->userData(NULL, 1)[0];
                    $pp = $lk_user['photo'] ? $lk_user['photo'] : 'default.jpg';
                    $lk_profile = permalink($SETT['url'].'/index.php?a=profile&u='.$lk_user['username']);
                    $liking .= '<li><a href="'.$lk_profile.'"><img src="'.$SETT['url'].'/uploads/faces/'.$pp.'" class="img-fluid rounded-circle" alt="User'.$lk_user['username'].'"></a></li>';
                }
            }
            $liker = count($all_likes)>0 ? $liking : '';

            //Like Button
            $like_action = $liked['user_id'] == $user['id'] ? 3 : 2;  

            // Post comments link
            $post_comments = permalink($SETT['url'].'/index.php?a=timeline&u='.$user['username'].'&read='.$post['pid']);

            // Share post on timeline
            $share_post = '<a href="'.permalink($SETT['url'].'/index.php?a=timeline&u='.$user['username'].'&share='.$post['pid']).'"  class="dropdown-item">'.$LANG['share_post'].'</a>';

            $cards = '<div id="set-messagez_'.$post['pid'].'"></div>
            <div class="col-lg-12" id="photo_'.$post['pid'].'"> 
              <div class="cardbox shadow bg-white"> 
                <div class="cardbox-heading"> 
                  <div class="dropdown float-right">
                    <button class="btn btn-flat btn-flat-icon" type="button" data-toggle="dropdown" aria-expanded="false">
                      <em class="fa fa-ellipsis-h"></em>
                    </button>
                    <div class="dropdown-menu dropdown-scale dropdown-menu-right" role="menu" style="position: absolute; transform: translate3d(-136px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
                      '.$delete.'
                      '.$stop_follow.'
                      '.$share_post.'
                    </div>
                  </div> 
                  <div class="media m-0">
                   <div class="d-flex mr-3">
                  	<a href="'.$user_profile.'"><img class="img-fluid rounded-circle" src="'.$pphoto.'" alt="User"></a>
                   </div>
                   <div class="media-body" id="post-writer">
                    <p class="m-0">'.$author.'</p>
                  '.$location.' 
                  <small><span><i class="fa fa-clock-o "></i> '.$marxTime->timeAgo(strtotime($post['date']), 1).'</span><i class="fa fa-'.$privacy_icon.'"></i></small>
                   </div>
                  </div> 
                </div> 

                '.$desc.'  
                <div class="cardbox-item d-flex justify-content-center">
                    '.$post_photo.'
                </div> 
                    
                <input type="hidden" value="'.$post_comments.'" id="post_share_url_'.$post['pid'].'">
                <input type="hidden" value="'.$PTMPL['page_title'].'" id="post_share_title_'.$post['pid'].'">

                <div class="cardbox-base">
                  <ul class="float-right">
                    <li><a href="'.$post_comments.'#comment"><i class="fa fa-comments"></i></a></li>
                    <li><a href="'.$post_comments.'#comment"><em class="mr-5">'.count($get_comments).'</em></a></li>
                    <li><a onclick="shareModal(4, '.$post['pid'].')"><i class="fa fa-share-alt"></i></a></li> 
                  </ul>
                   
                  <ul>
                    <li><a onclick="relate('.$post['pid'].', '.$like_action.', '.$post['user_id'].', 1)" id="like_btn_'.$post['pid'].'"><i class="fa fa-thumbs-up '.$t_class.'" id="thumb_'.$post['pid'].'"></i></a></li>
                    '.$liker.'
                    <li>
                    	<a data-toggle="modal" onclick="relate('.$post['pid'].', 4, '.$post['user_id'].', 1)" id="modal_btn_'.$post['pid'].'" data-target="#showLikesModal">
                    		<span id="like_count_'.$post['pid'].'">'.$likes_count.'</span>
                    	</a>
                    </li>
                  </ul>        
                </div> 	                	

                '.$read_comments.'

              	<div class="text-center border-bottom" id="comment_block_'.$post['pid'].'"></div> 
                <div class="cardbox-comments">
                  <span class="comment-avatar float-left">
                    <a href="'.$user_profile.'"><img class="rounded-circle" src="'.$pphoto.'" alt="..."></a>                
                  </span>
                  <div class="search">
                    <input name="comment" id="comment_'.$post['pid'].'" placeholder="Write a comment" type="text">
                	<button type="button" onclick="write_real_comment('.$user['id'].', '.$post['user_id'].', '.$post['pid'].', 2)"><i class="fa fa-paper-plane"></i></button>
                  </div> 
                </div>    
              </div>
            </div>';

            // Show the post by its privacy setting
            if ($post['privacy'] == 2) {
                $cards = $cards;
            } elseif ($post['privacy'] == 1) {
                if ($follower['leader_id'] == $post['user_id']) {
                    $cards = $cards;
                } else {
                    $cards = '';
                }  
            } elseif ($post['privacy'] == 0) {
                if ($user['id'] == $post['user_id']) {
                    $cards = $cards;
                } else {
                    $cards = '';
                } 
            }   
	        $photo_rows = $cards;
	    } else {
	           $photo_rows = $nothing_here;       
	    }

	    $return = $photo_rows; 

		// Show the Timeline
    	if (isset($_GET['read'])) {
    		$PTMPL['timeline_cards'] = $return;
    	} else {
			if ($user['id'] == $profiles['id']) {
				$PTMPL['new_post'] = $upload_form;
			}
    		$PTMPL['timeline_cards'] = timeline_cards(0);
    	}

		$theme = new themer('social/timeline');		

	// Show 404 errow	 
	} else {
		$theme = new themer('welcome/404'); $container = '';
	}

	$container = $theme->make();
	  
	$PTMPL['container'] = $container;

	$theme = new themer('social/content');
	return $theme->make();
}
?>
