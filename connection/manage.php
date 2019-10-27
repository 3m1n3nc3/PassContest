<?php 
require_once(__DIR__ .'/../includes/autoload.php');
$userid = $user['id'];

$gett = new contestDelivery; 
$userApp = new userCallback;

$contest_details = $gett->getContest($user['username'], 0, 'id','AND active = \'1\'');
$premium_status = $userApp->premiumStatus(null, 2); 
$prem_check = $userApp->premiumStatus(null, 1); 
$pass = fetch_api(2);
if(isset($_POST['active']) && $_POST['active'] == 1) {
    $active = $_POST['s']; 
    $id = $_POST['id']; 
 
    if ($active == 1) {

        // Check if premium is on
        if ($settings['premium']) {

            // Check if user has an active subscription
            if ($prem_check) {
                if ($premium_status['plan'] == 'slight_plan') {
                    if (count($contest_details)>=1) {
                        echo '<span class="text-warning">'.sprintf($LANG['active_limit'], 1).'</span>';
                    } else {
                        echo $gett->activateItem($id, TABLE_CONTEST, $active, 0);
                    }
                } elseif ($premium_status['plan'] == 'lite_plan') {
                    if (count($contest_details)>=3) {
                        echo '<span class="text-warning">'.sprintf($LANG['active_limit'], 3).'</span>';
                    } else {
                        echo $gett->activateItem($id, TABLE_CONTEST, $active, 0);
                    }
                } elseif ($premium_status['plan'] == 'life_plan') { 
                        echo $gett->activateItem($id, TABLE_CONTEST, $active, 0); 
                } else {
                    echo '<span class="text-danger">'.$LANG['upgrade_to_activate'].'</span>';
                }  
            } else {
                echo '<span class="text-info">'.$LANG['expired_sub'].'</span>';
            }

        // Activate the user if premium is off
        } else {
            echo $gett->activateItem($id, TABLE_CONTEST, $active, 0); 
        }            
    } elseif ($active == 0) {
        echo $gett->activateItem($id, TABLE_CONTEST, $active, 0);
    }      

}

if(isset($_POST['allow_vote']) && $_POST['allow_vote'] == 1) {
    $active = $_POST['s']; 
    $id = $_POST['id'];

    $gett->active = $active;
    echo $gett->activateItem($id, TABLE_CONTEST, $active, 1);
}

if(isset($_POST['social_require']) && $_POST['social_require'] == 1) {
    $active = $_POST['s']; 
    $id = $_POST['id'];

    $gett->active = $active;
    echo $gett->activateItem($id, TABLE_CONTEST, $active, 7);
}

if(isset($_POST['addactivity']) && $_POST['addactivity'] == 1) {
    $date = $_POST['date']; 
    $time = $_POST['time'];
    $activity = $_POST['activity'];
    $description = $_POST['description'];
    $id = $_POST['id'];

    if ($activity == '') {
        echo errorMessage("Activity can not be empty"); 
    } elseif ($date == '') {
        echo errorMessage("Date can not be empty"); 
    } elseif ($time == '') {
        echo errorMessage("Time can not be empty"); 
    } else {
        $gett->activity = $activity;
        $gett->date = $date;
        $gett->time = $time;
        $gett->description = $description;
        $gett->type = 0;
        echo $gett->scheduleCategory($id);
    }
}
if(isset($_POST['addcategory']) && $_POST['addcategory'] == 1) {
    $category = $_POST['category']; 
    $requirement = $_POST['requirement']; 
    $description = $_POST['description'];
    $id = $_POST['id'];

    if ($category == '') {
        echo errorMessage("Category can not be empty"); 
    } else {
        $gett->category = $category;
        $gett->requirement = $requirement; 
        $gett->description = $description;
        $gett->type = 1;
        echo $gett->scheduleCategory($id);  
    }
}