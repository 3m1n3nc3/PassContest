<?php
require_once(__DIR__ .'/../includes/autoload.php');

$msg = '';

// fetch user data
$userApp->user_id = isset($_GET['id']) ? $_GET['id'] : $user['id'];
$data = $userApp->userData(NULL, 1)[0]; 

// Check if this upload is ajax
if (isset($_POST['ajax_image'])) {
  $ajax_image_ = explode(';',$_POST['ajax_image']);
  $ajax_image_ = isset($ajax_image_[1]) ? $ajax_image_[1] : null; 
}

if (!empty($_FILES)) { 

  // Connect to db to select contest records
  if (isset($_GET['id'])) {
    $cn = new contestDelivery;
    $getcont = $cn->viewApplications(0, 0, $_GET['id']);    
  } 

  // File arguments
  $errors= array();
  $file_name = $_FILES['file']['name'];
  $file_size = $_FILES['file']['size'];
  $file_tmp = $_FILES['file']['tmp_name'];
  $file_type= $_FILES['file']['type'];
  $var_string2lower = explode('.',$_FILES['file']['name']);
  $file_ext = strtolower(end($var_string2lower));
  
  $expensions= array("jpeg","jpg","png");

  $new_image = mt_rand().'_'.mt_rand().'_'.mt_rand().'_n.'.$file_ext; 
  
  if(in_array($file_ext,$expensions)=== false){
     $errors[]="File not allowed, use a JPEG, JPG or PNG file";
  }
  
  if($file_size > 5097152){
    $errors[].='Image is larger than 10 MB';
  }

  // Crop and compress the image
  if (in_array($file_ext,$expensions) && empty($errors)==true) {            
      // Create a new ImageResize object
    $image = new \Gumlet\ImageResize($file_tmp);

    $upload_dir = 'photos/';   
    if ($_GET['d'] == 'headshot') {
      // Manipulate it
      $image->crop(2000, 3000);     
      $imgexist = $getcont["headshot"]; 
      $t=1;
    } elseif ($_GET['d'] == 'fullshot') {
      // Manipulate it
      $image->crop(2000, 3000);     
      $imgexist = $getcont["fullbody"]; 
      $t=1;
    } elseif ($_GET['d'] == 'gallery') {
      // Manipulate it 
      $userApp->description = $_POST['desc'];
      $image->resizeToHeight(800);    
      $imgexist = null; 
      $t=1;    
    }

    // Set the user id
    $d_id = $data['id'];

    // Check the upload type and set limit
    $count = $userApp->user_gallery($d_id, 0)[0]['count'];

    // Save the new image to the upload directory
    if ($_GET['d'] == 'gallery') {
      $count<5 ? $image->save($SETT['working_dir'].'/uploads/'.$upload_dir.$new_image) : $errors[] .= messageNotice($LANG['upload_limit']);
    } else {
      // delete the old image
      deleteFiles($imgexist, $t); 

      // And upload a new one
      if (is_writable($SETT['working_dir'].'/uploads/'.$upload_dir)) {
        $image->save($SETT['working_dir'].'/uploads/'.$upload_dir.$new_image);
      } else {
        $msg .= messageNotice($SETT['working_dir'].'/uploads/'.$upload_dir.' is not writable', 3);
      }
    }
    
    $userApp->photo = $new_image; 
    $msg .= $userApp->updatePhoto($d_id, $t);       
          
  } else {                                  
    $msg =. messageNotice($errors[0], 3);    
  } 			 
  if (!empty($errors)) {
    $msg .= messageNotice($errors[0], 3);  
  }

	//End of Crop or resample uploaded image -- 
	//===============================================================================	
  if ($_GET['d'] == 'gallery') {
    if (empty($errors) && strtolower($msg) == 'saved') {
      $msg .= 'success';
    } else {
      $msg .= $msg;
    }
    if (isset($_GET['ref']) && isset($_GET['create'])) {
      $header = permalink($SETT['url'].'/index.php?a='.$_GET['ref'].'&create='.$_GET['create'].'&photo='.$_GET['photo'].'&msg='.urlencode($msg).'#gallery');
    } else {
      $header = permalink($SETT['url'].'/index.php?a=gallery&u='.$user['username'].'&msg='.urlencode($msg));
    }

    header("Location: ".$header);
  }	
  echo $msg;

  // If this upload is from ajax
} elseif (!empty($ajax_image_)) {
  $image = $_POST['ajax_image'];
  list($type, $image) = explode(';',$image);
  list(, $image) = explode(',',$image);
  $image = base64_decode($image);

  $new_image = mt_rand().'_'.mt_rand().'_'.mt_rand().'_n.png';

  // Check what type of photo is being uploaded
  if (isset($_GET['d'])) { 
    if ($_GET['d'] == 'profile') {
      // Upload the profile photo
      $upload_dir = 'photos/';
      $imgexist = $data["photo"]; 
      $t=1;      

    } elseif ($_GET['d'] == 'cover') {
      // Upload the profile photo
      $upload_dir = 'covers/';
      $imgexist = $data["cover"]; 
      $t=2;     

    } elseif ($_GET['d'] == 'contest') {
      // Upload the contest Cover  
      $upload_dir = 'covers/';    
      $imgexist = $_GET["cover"]; 
      $t=2;    
    }
  }

  // Save the new image to the upload directory  
  if (is_writable($SETT['working_dir'].'/uploads/'.$upload_dir)) {
    file_put_contents($SETT['working_dir'].'/uploads/'.$upload_dir.$new_image, $image); 
  } else {
    $msg .= messageNotice($SETT['working_dir'].'/uploads/'.$upload_dir.' is not writable', 3);
  }


  $d_id = $data['id'];

  // delete the old image
  if (isset($imgexist)) {
    deleteFiles($imgexist, $t);
  }

  // Link image to DB
  $userApp->photo = $new_image; 
  $msg .= $userApp->updatePhoto($d_id, $t); 
  echo $msg; 
} elseif (empty($ajax_image_)) {
  echo messageNotice('Please choose a valid image file');
}


