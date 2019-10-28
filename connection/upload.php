<?php
require_once(__DIR__ .'/../includes/autoload.php');

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

      if ($_GET['d'] == 'headshot') {
        // Manipulate it
        $image->crop(2000, 3000);   
        $upload_dir = 'contest/head';     
        $imgexist = $getcont["headshot"]; 
        $t=2;
      } elseif ($_GET['d'] == 'fullshot') {
        // Manipulate it
        $image->crop(2000, 3000);    
        $upload_dir = 'contest/body';    
        $imgexist = $getcont["fullbody"]; 
        $t=3;
      } elseif ($_GET['d'] == 'gallery') {
        // Manipulate it 
        $userApp->description = $_POST['desc'];
        $image->resizeToHeight(800);  
        $upload_dir = 'gallery';     
        $imgexist = null; 
        $t=5;    
      }

      // Set the user id
      $d_id = $data['id'];

      // Check the upload type and set limit
      $count = $userApp->user_gallery($d_id, 0)[0]['count'];

      // Save the new image to the upload directory
      if ($_GET['d'] == 'gallery') {
        $count<5 ? $image->save('../uploads/'.$upload_dir.'/'.$new_image) : infoMessage($LANG['upload_limit']);
      } else {
        $image->save('../uploads/'.$upload_dir.'/'.$new_image);
      }

      // delete the old image if not gallery
      if (isset($imgexist)) {
        deleteImages($imgexist, $t);
      }
      
      $userApp->photo = $new_image; 
      $msg = $userApp->updatePhoto($d_id, $t);       
          
    } else {                                  
        $msg = errorMessage($errors[0]);    
    } 																			
	//End of Crop or resample uploaded image -- 
	//===============================================================================	
  if ($_GET['d'] == 'gallery') {
    if (empty($errors) && strtolower($msg) == 'saved') {
      $msg = 'success';
    } else {
      $msg = $msg;
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
      $upload_dir = 'faces';
      $imgexist = $data["photo"]; 
      $t=1;      

    } elseif ($_GET['d'] == 'cover') {
      // Upload the profile photo
      $upload_dir = 'cover';
      $imgexist = $data["cover"]; 
      $t=0;     

    } elseif ($_GET['d'] == 'contest') {
      // Upload the contest Cover  
      $upload_dir = 'cover/contest';    
      $imgexist = $_GET["cover"]; 
      $t=4;    
    }
  }

  // Save the new image to the upload directory  
  file_put_contents('../uploads/'.$upload_dir.'/'.$new_image, $image); 


  $d_id = $data['id'];

  // delete the old image if not gallery
  if (isset($imgexist)) {
    deleteImages($imgexist, $t);
  }

  // Link image to DB
  $userApp->photo = $new_image; 
  $msg = $userApp->updatePhoto($d_id, $t); 
  echo $msg; 
} elseif (empty($ajax_image_)) {
  echo $msg = infoMessage('Please choose a valid image file');
}


