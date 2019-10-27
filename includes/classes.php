<?php
//======================================================================\\
// Passcontest 1.0 - Contests and Voting Script                         \\
// Copyright © Newnify. All rights reserved.                            \\
//----------------------------------------------------------------------\\
// http://www.newnify.com/                                              \\
//======================================================================\\

//Fetch settings from database
function getSetting($x=0) {  
	if ($x==0) {
		$sql = "SELECT * FROM ".TABLE_SETTINGS;
	} else {
		$sql = "SELECT * FROM ".TABLE_WELCOME;	
	} 

    return dbProcessor($sql, 1)[0]; 
} 

// generate safelinks from names and emails
function safeLinks($string) {
	$gett = new contestDelivery;
	// Replace spaces and special characters with a -
    $return = strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8')), ENT_QUOTES, 'UTF-8')), '-'));

	$contest = $gett->getContest(0, $return, 'safelink');
    // If the link is not safe add a random string
    ($contest['safelink'] == $return) ? $safelink = $return.'-'.rand(100,900) : $safelink = $return; 
    
    return $safelink;
}

/**
 * Fetch and insert new user information
 */
class userCallback {
	public $username; 
	public $email;
	public $password;
	public $remember;

	public $firstname;
	public $lastname;
	public $city;
	public $state;
	public $country;
	public $phone;

	function authenticateUser($type = null) {
		global $LANG; 
		if(isset($_COOKIE['username']) && isset($_COOKIE['userToken'])) {
			$this->username = $_COOKIE['username'];
			$auth = $this->userData($this->username); 

			if($auth['username']) {
				$logged = true;
			} else {
				$logged = false;
			}
		} elseif(isset($this->username)) {
			$username = $this->username;
			$auth = $this->userData($username);
			
			if($auth['username']) {			
				if($this->remember == 1) {
					setcookie("username", $auth['username'], time() + 30 * 24 * 60 * 60, COOKIE_PATH);
					setcookie("userToken", $auth['token'], time() + 30 * 24 * 60 * 60, COOKIE_PATH);
	 
					$_SESSION['username'] = $auth['username'];
					
					$logged = true;
					session_regenerate_id(); 
				} else {
					$_SESSION['username'] = $auth['username'];
					$_SESSION['password'] = $auth['password']; 
					$logged = true;
				} 
			}			
		return $username;

		} elseif($type) {
			$auth = $this->userData($this->username);
			
			if($this->remember == 1) {
				setcookie("username", $auth['username'], time() + 30 * 24 * 60 * 60, COOKIE_PATH);
				setcookie("userToken", $auth['token'], time() + 30 * 24 * 60 * 60, COOKIE_PATH);
 
				$_SESSION['username'] = $auth['username'];
				
				$logged = true;
				session_regenerate_id();
			} else {
				return $LANG['invalid_data'];
			}
		}	
		
		if(isset($logged) && $logged == true) {
			return $auth;
		} elseif(isset($logged) && $logged == false) {
			$this->logOut();
			return $LANG['invalid_data'];
		}
		
		return false;
	}	 

	function userData($username=NULL, $type=NULL) {
		// 1 Instance = get all users e.g userData()
		// 2 instance = get a particular user by id e.g userData(NULL, 1)
		// 3 instance = get a particular user by username e.g userData(king)

	    global $DB, $settings;

	    // Limit clause to enable pagination
		if (isset($this->limit)) {
			isset($this->featured) ? $rand = 'RAND()' : $rand = 'reg_date';
			$limit = sprintf('ORDER BY %s DESC LIMIT %s, %s', $rand, $this->start, $this->limit);
		} else {$limit = '';}
 
		// Check if the user is a featured user
		if (isset($this->featured)) {
			$featured = 'featured = \'1\'';
		} else {
			$featured = '1';
		}

		$filter = (isset($this->filter)) ? $this->filter : '' ; 

	    $user_id = isset($this->user_id)?$this->user_id:'';

	    if (isset($this->search)) {			//Search instance
	    	$search = $this->search; 	
	    	$sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE username LIKE '%s' OR concat_ws(' ', `fname`, `lname`) LIKE '%s' OR country LIKE '%s' OR role LIKE '%s' LIMIT %s", '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', '%'.$search.'%', $settings['per_explore']);  
	    } elseif ($username == NULL && $type !=1) {
	    	$sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE %s %s %s", $featured, $filter, $limit); //1 instance
	    } elseif ($user_id !='' && $type == 1) {
	    	$sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE id = '%s'", $user_id);	//2 instance
	    } else {
	    	// if the username is an email address
	    	if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
	    		$sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE email = '%s'", $username); 	//3 instance
	    	} else {
	    		$sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE username = '%s'", $username); 	//3 instance
	    	}
	    }
	    try {
	        $stmt = $DB->prepare($sql); 
	        $stmt->execute();
	        $results = $stmt->fetchAll();
	    } catch (Exception $ex) {
	        return errorMessage($ex->getMessage());
	    } 
	    if (count($results)>0) {
	    	if ($username == NULL) {
	    		return $results;
	    	} else {
	    		return $results[0];
	    	}
	    }
	}

	function checkEmail($email=NULL, $type=0) {
		global $DB; 
		$sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE 1 AND email = '%s'", mb_strtolower($email));
	    try {
	        $stmt = $DB->prepare($sql); 
	        $stmt->execute();
	        $results = $stmt->fetchAll();
	    } catch (Exception $ex) {
	        return errorMessage($ex->getMessage());
	    } 
	    if (count($results)>0)  {
	    	if ($type == 1) {
	    		return $results[0];
	    	} else {
	    		return $results[0]['email'];
	    	}
	    } 		
	}

	function registrationCall($username, $email, $password, $phone=NULL) {
		// Register usage
		global $DB, $CONF, $LANG, $settings, $user;
		// Prevents bypassing the FILTER_VALIDATE_EMAIL
		$email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');

		$token = accountToken();
		$password = hash('md5', $_POST['password']);
		$status = ($settings['activation'] == 'none') ? 2 : 0;
		$sql = sprintf("INSERT INTO " . TABLE_USERS . " (`email`, `username`, `password`, `phone`, `token`, `creator`, `claimed`, `status`) VALUES 
	        ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $email, $username, $password, $phone, $token, 0, 1, $status);
		$response = dbProcessor($sql, 0, 1);
		
		if ($response == 1) {
			$_SESSION['username'] = $_POST['username'];
			$_SESSION['password'] = hash('md5', $_POST['password']);
			$process = 1;
		} 

		// Send an activation mail
		$save = new siteClass; 
		$social = new social;

		if($process == 1) {
			// Get the new user data via his email address
			$data = $this->checkEmail($email, 1);

			// Fetch the users balance
			$save->what = sprintf('user = \'%s\'', $data['id']);
			$credit = $save->passCredits(0)[0];

			// If this is a referral, add the referral data
			if (isset($_SESSION['referrer'])) {
				$ref = $save->referrals(0, $_SESSION['referrer'], $data['username']);
				if ($ref == 1) {
					unset($_SESSION['referrer']);
				} 
			}

			// Add the signup bonus
			if ($settings['signup_bonus']>0.00 && $credit['balance'] < $settings['signup_bonus']) {
				$save->balance = $settings['signup_bonus'];
				if ($credit) {
					$return = $save->passCredits(1, $data['id']);
				} else {
					$return = $save->passCredits(2, $data['id']);
				}
			} 

			// Construct the email message
			$key = '<a href="'.permalink($CONF['url'].'/index.php?a=welcome&activate='.$token.'&username='.$username)
			.'">'.permalink($CONF['url'].'/index.php?a=welcome&activate='.$token.'&username='.$username).'</a>';
			
			$params = 
				array(
					'contest', ucfirst($username), $password, $data['fname'], $data['lname'], $key, $email,
					'act_username', 'act_firstname', 'act_lastname', 'action', 'action_on'
				);

			$message = $save->message_template($settings['email_reg_temp'], $params);
			$subject = sprintf($LANG['activation_msg'], $username, $settings['site_name']);
			if ($settings['activation'] == 'email') {
				// Send the message
				$save->user_id = $data['id'];
				$save->reg = 1;
    			$save->mailerDaemon($CONF['email'], $email, $subject, $message);				 
			} elseif ($settings['activation'] == 'phone') {
				$text = sprintf($LANG['short_activation_msg'], $key, $settings['site_name']);
				$social->sendSMS($text, $phone);
			} elseif($settings['email_welcome']) { 
				// Send the message
				$save->user_id = $data['id'];
				$save->reg = 1;
    			$save->mailerDaemon($CONF['email'], $email, $subject, $message);
			}
		}
		return ($process) ? $process : 0 ;
	}

	function captchaVal($captcha) {
		global $settings;
		if($settings['captcha']) {
			if($captcha == "{$_SESSION['captcha']}" && !empty($captcha)) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	function phoneVal($number, $x=0) {
		global $settings;
		$phoneX = substr($number, 1);
		$phone = substr($number, 3);

		if($settings['activation'] == 'phone') {
			if ($x) {
				$sql = sprintf("SELECT phone FROM " .TABLE_USERS. " WHERE phone = '%s'", $number);
				$rs = dbProcessor($sql, 1)[0];
				return $rs ? false : true;
			} else {
				if (mb_strlen($phone) < 9) {
				 	return false;
				} elseif(filter_var($phone, FILTER_VALIDATE_INT)) {
					return true;
				} else {
					return false;
				}		
			}
		} else {
			return true;
		}
	}

	function use_invite($token) {
		global $LANG, $settings;
		if ($settings['invite_only']) {
			$sql = sprintf("SELECT * FROM " . TABLE_GIFT . " WHERE token = '%s' AND reged = '0'", db_prepare_input($token));
			$data = dbProcessor($sql, 1)[0];
			if ($data) {
				$ic = $data['reged'];
				if ($ic == 0) {
					$sql = sprintf("UPDATE " . TABLE_GIFT . " SET `reged` = '1' WHERE `token` = '%s'", db_prepare_input($token));
					dbProcessor($sql, 0);
					return true;
				}
			} else {
				return false;
			}
		} else {
			return true;
		}
	}	

	function account_activation($token, $username) {
		global $CONF, $settings, $user, $LANG;
		if($token == 'resend') { 
			// Check if a token has been sent before, and is not expired
			$sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE username = '%s' AND status = '0'", db_prepare_input($username));
			$data = dbProcessor($sql, 1)[0];
 
			if($user['token'] && date("Y-m-d", strtotime($data['reg_date'])) < date("Y-m-d")) {
				$date = date("Y-m-d H:i:s");
				$token = accountToken();
				$sql = sprintf("UPDATE " . TABLE_USERS . " SET `token` = '%s', `reg_date` = '%s'"
				." WHERE `username` = '%s'", $token, $date, db_prepare_input($username));
				$return = dbProcessor($sql, 0, 1);
				if($settings['activation'] == 'email') {
					$link = permalink($CONF['url'].'/index.php?a=welcome&activate='.$token.'&username='.$username);
					$msg = sprintf($LANG['welcome_msg'], $username, $settings['site_name'], $link, $link);	
					$subject = ucfirst(sprintf($LANG['activation_msg'], $username, $settings['site_name']));

					$save = new siteClass;
					$save->user_id = $data['id'];  
					$save->reg = 1;
	    			$save->mailerDaemon($CONF['email'], $data['email'], $subject, $msg);
	    			return successMessage($LANG['activation_sent']);
				}				
			} else {
				return infoMessage($LANG['activation_was_sent']);
			}   
		} else {
			$sql = sprintf("SELECT * FROM " . TABLE_USERS . " WHERE username = '%s' AND token = '%s' AND status = '0'", db_prepare_input($username), db_prepare_input($token)); 
			$return = dbProcessor($sql, 0, 1);
			if ($return == 1) {
				$sql = sprintf("UPDATE " . TABLE_USERS . " SET `status` = '2', `token` = ''"
				." WHERE `username` = '%s'", db_prepare_input($username));
				return dbProcessor($sql, 0, 1);
			} else {
				return $return;
			}
		}
	}

	function logOut($rt = null) {
		if($rt == true) {
			$this->resetToken();
		}
		setcookie("userToken", '', time()-3600, COOKIE_PATH);
		setcookie("username", '', time()-3600, COOKIE_PATH);
		unset($_SESSION['username']);
		unset($_SESSION['password']); 
	}

	function updateProfile($id) {
		// Register usage
		global $DB;
 
		if ($this->update == 1){
			// Udate users basic info
			$firstname = $this->firstname;
			$lastname = $this->lastname;
			$gender = $this->gender;
			$city = $this->city;
			$state = $this->state;
			$country = $this->country;
			$phone = $this->phone;	
			$address = $this->address;	

	        $sql = sprintf("UPDATE " . TABLE_USERS . " SET `fname` = '%s', `lname` = '%s', `gender` = '%s', "
                . " `city` =  '%s', `state` = '%s', `country` = '%s', `phone` = '%s', `address` = '%s' "  
                . " WHERE `id` = %s", $firstname, $lastname, $gender, $city, $state, $country, $phone, $address, $id);
	    } elseif ($this->update == 'admin'){
	    	// Update user data from admin panel
			$username = $this->username;
			$email = $this->email; 	
			$status = $this->status;
			$feat = $this->featured;
	        $sql = sprintf("UPDATE " . TABLE_USERS . " SET `username` = '%s', `email` = '%s', " 
	        	. " `status` = '%s', `featured` = '%s' WHERE `id` = %s", $username, $email, $status, $feat, $id);
	    } elseif ($this->update == 'password'){
	    	// Change user Password
			$password = $this->password; 
	        $sql = sprintf("UPDATE " . TABLE_USERS . " SET `password` = '%s' WHERE `id` = %s", $password, $id);
	    } else {
	    	// Update user profile information
			$profession = $this->profession;
			$facebook = $this->facebook;
			$twitter = $this->twitter;
			$instagram = $this->instagram;
			$lovesto = $this->lovesto;
			$intro = $this->intro;
	        $sql = sprintf("UPDATE " . TABLE_USERS . " SET `profession` = '%s', `intro` = '%s', `facebook` = '%s', "
        		. " `twitter` = '%s', `instagram` = '%s', `lovesto` = '%s' "
                . " WHERE `id` = %s", $profession, $intro, $facebook, $twitter, $instagram, $lovesto, $id);  
	    }                          
        $return = dbProcessor($sql, 0, 1);
        $response = ($return == 1) ? successMessage('Saved') : infoMessage($return);
		return $response;
	}

	function set_bank($type, $uid) {
		global $user, $settings, $LANG, $CONF;
		// type 0: fetch
		// type 1: Save bank
		// type 2: cashout
		// type 3: admin

		$sc = new siteClass;
		$social = new social;

		if ($type == 0) {
			// $x could be = or >
			$x = (isset($this->x)) ? $this->x : sprintf('bank.user_id = \'%s\'', $uid); 

		    // Limit clause to enable pagination
			if (isset($this->limit)) { 
				$limit = sprintf('ORDER BY cashout DESC, bank.id DESC LIMIT %s, %s', $this->start, $this->limit);
			} else {
				$limit = '';
			}

	        $sql = sprintf("SELECT * FROM " . TABLE_BANK 
	    		. " AS bank LEFT JOIN " . TABLE_USERS . " AS `users` ON `bank`.`user_id` = `users`.`id`" 
	    		. " WHERE %s %s", $x, $limit);
	        $return = dbProcessor($sql, 1);
	        if (isset($this->x)) {
	        	return $return;
	        } else {
	        	return $return[0];
	        }
	        
		} elseif ($type == 1) {
			// First check if the user has svaed thier bank details
			$sb = $this->set_bank(0, $uid);
	        if ($sb) {
				$paypal = $this->paypal;  
				$bank = $this->bank;
				$bank_address = $this->bank_address;
				$sort = $this->sort; 
				$account_name = $this->account_name;
				$account_number = $this->account_number;
				$routing = $this->routing;

				$sql = sprintf("UPDATE " . TABLE_BANK . "  SET `paypal` = '%s', `bank_name` = '%s', `bank_address` = '%s',"
					." `sort_code` = '%s', `account_name` = '%s', `account_number` = '%s', `aba` = '%s' "
					." WHERE `user_id` = %s", $paypal, $bank, $bank_address, $sort, $account_name, $account_number, $routing, $uid);
				$sb = dbProcessor($sql, 0, 1);
				return $sb;
	        } else {
				$sql = sprintf("INSERT INTO " . TABLE_BANK . " (`user_id`) VALUES ('%s')", $uid);
				$sb = dbProcessor($sql, 0, 1);
				$sb = $this->set_bank(1, $uid);
				if ($sb) {
				  	return $sb;
				}  	        	
	        }
		} elseif ($type == 2) {
			$co = $this->cashout;
			$sql = sprintf("UPDATE " . TABLE_BANK . "  SET `cashout` = '%s' WHERE `user_id` = %s", $co, $uid);	
			return dbProcessor($sql, 0, 1);		
		} elseif ($type == 3) {
			$bank = $this->set_bank(0, $uid);
			// Set the message type
			if ($this->status == 'success') {
				$status = $LANG['successful'];
				$now = $LANG['cashout_processing'];
			} elseif ($this->status == 'paid') {
				// Set the cashout amount to seesion
				$_SESSION['amount'] = $bank['cashout'].' '.$settings['pc_symbol'];
				// Prepare the message
				$status = $LANG['pay_success'];
				$now = $LANG['pay_verify'];

				// Deduct the approved amount
				$amount = $this->set_bank(0, $uid); 
				$sc->what = sprintf('user = \'%s\'', $uid);
  				$credit = $sc->passCredits(0)[0];
				$sc->balance = $credit['balance'] - $amount['cashout'];
				$sc->passCredits(1, $uid);
			} elseif ($this->status == 'declined') {
				// Set the cashout amount to seesion
				$_SESSION['amount'] = $bank['cashout'].' '.$settings['pc_symbol'];
				$status = $LANG['decline'].'d';
				$now = $LANG['ignore_prev_cashout'];
			}
			$co = $this->admin;
			// Approve or decline the request
			$sql = sprintf("UPDATE " . TABLE_BANK . "  SET %s WHERE `user_id` = %s", $co, $uid);
			$ret = dbProcessor($sql, 0, 1);

			// Send a notifications
			if ($ret == 1) {
				$this->user_id = $uid;
				$data = $this->userData(NULL, 1)[0];
				// Get the cashout amount
				$amount = isset($_SESSION['amount']) ? $_SESSION['amount'] : $bank['cashout'].' '.$settings['pc_symbol'];
				// Prepare the message
				$message = sprintf($LANG['cashout_message'], $data['username'], $amount, $settings['site_name'], $status, $now);
				$subject = sprintf($LANG['cashout_status'], $settings['site_name'], $status);

				// If the user receives site wide notifications, send him one  
    			$social->type = 4;
				$social->subject = $subject;
				$social->message = $message;
				$social->notifier(0, $data['id'], 0, 0, 1);
				// unset the cashout amount session
				if ($_SESSION['amount']) {
					unset($_SESSION['amount']);
				} 
			}
			return $ret;
		}

	}

	// create a new contestant
	function createContestant($id=NULL) {
		 
		// $this->create 1: Create the new user profile
		// $this->create 2: Update the data for this new profile
		// $this->create 3: Associate the created profile with the creator
		// $this->create 4: Update the new user profile

		global $DB, $user;
 
		if ($this->create == 1 || $this->create == 4){
			$username  = $this->username;
			$password = $this->password;
			$firstname = $this->firstname;
			$lastname = $this->lastname;
			$city = $this->city;
			$state = $this->state;
			$country = $this->country;
			$phone = $this->phone;	
			$email = $this->email;	

			if ($this->create == 1) { 
				$cl = (isset($this->claim)) ? $this->claim : 0;
				$sql = sprintf("INSERT INTO " . TABLE_USERS . " (`username`, `password`, `token`, `fname`, `lname`, `city`, `state`, `country`, `phone`, `email`, `role`, `status`, `creator`, `claimed`) VALUES 
					('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",$username, $password, accountToken(), $firstname, $lastname, $city, $state, $country, $phone, $email, 'contestant', 2, $user['id'], $cl); 
			} elseif ($this->create == 4) {
				$uid = $this->user_id;
				$sql = sprintf("UPDATE " . TABLE_USERS . "  SET `fname` = '%s', `lname` = '%s', `city` = '%s', `state` = '%s', `country` = '%s', `phone` = '%s', `email` = '%s' WHERE `id` = %s", $firstname, $lastname, $city, $state, $country, $phone, $email, $uid);
			}
	    } elseif ($this->create == 2) {
			$profession = $this->profession;
			$facebook = $this->facebook;
			$twitter = $this->twitter;
			$instagram = $this->instagram;
			$lovesto = $this->lovesto;
			$intro = $this->intro;

	        $sql = sprintf("UPDATE " . TABLE_USERS . " SET `profession` = '%s', `intro` = '%s', `facebook` = '%s', "
	        		. " `twitter` = '%s', `instagram` = '%s', `lovesto` = '%s' "
	                . " WHERE `id` = %s", $profession, $intro, $facebook, $twitter, $instagram, $lovesto, $id); 
	    } elseif ($this->create == 3) {
			$sql = sprintf("INSERT INTO " . TABLE_GENERATE . " (`user_id`, `contest_id`) VALUES 
				('%s', '%s')", $this->user_id, $this->contest_id);
	    }        
		$return = dbProcessor($sql, 0, 1);

	    // Prepare the responses for returning after successful data insertion
	    $response = '';
	    if ($return == 1) {
	    	if ($this->create == 1 || $this->create == 4) { 
	    		$response = ($this->create == 1) ? 'New Contestant created successfully' : 'Contestant data updated successfully'; 
	    	} elseif ($this->create == 2) {
	        	$response = 'Saved';
	    	}
	    	return successMessage($response);	    	 
	    } else {
	    	return infoMessage($return);
	    } 
	}

	function viewGenerated($creator) {
		global $DB;

		if (isset($this->limit)) {
			$limit = sprintf('ORDER BY date DESC LIMIT %s, %s', $this->start, $this->limit);
		} else {$limit = '';}

		$sql = sprintf("SELECT * FROM " . TABLE_GENERATE . " WHERE contest_id = '%s' AND claimed = '0' %s", $creator, $limit);
	    return dbProcessor($sql, 1);		
	}

	function updatePhoto($id, $type = null) {
		global $LANG;
		// 0 == Cover Picture  
		// 1 == profile picture
		// 2 == head shot
		// 3 == full body
		// 4 == Contest Cover
		// 5 == Gallery
 
		global $DB;
		$photo = $this->photo;
		$_apply_sql = sprintf("SELECT headshot, fullbody FROM " . TABLE_APPLY . " WHERE user_id = '%s'", $id);
		$apply_var = dbProcessor($_apply_sql, 1) ? 1 : 0;
 
		if ($type == 0) {
	        $sql = sprintf("UPDATE " . TABLE_USERS . " SET `cover` = '%s' " 
	        	. " WHERE `id` = %s", $photo, $id); 
		} elseif ($type == 1) {
	        $sql = sprintf("UPDATE " . TABLE_USERS . " SET `photo` = '%s' " 
	        	. " WHERE `id` = %s", $photo, $id); 
		} elseif ($type == 2 || $type == 3) {
			$st = $type == 2 ? 'headshot' : 'fullbody';
			if ($apply_var) {
	        	$sql = sprintf("UPDATE " . TABLE_APPLY . " SET `%s` = '%s' " 
	        	. " WHERE `user_id` = %s", $st, $photo, $id); 
			} else {
				$sql = sprintf("INSERT INTO " . TABLE_APPLY . " (`%s`, `user_id`) VALUES ('%s', '%s') ", 
					$st, $photo, $id); 
			}
		} elseif ($type == 4) {
	        $sql = sprintf("UPDATE " . TABLE_CONTEST . " SET `cover` = '%s' " 
	            . " WHERE `id` = %s", $photo, $id); 
		} elseif ($type == 5) { 
			// Check the ranking of the photo
			$sr = "SELECT id FROM " . TABLE_GALLERY . " WHERE rank = 1";
			$r = dbProcessor($sr, 1);
			if (count($r) == 2) {
				$rank = 2;
			} else {
				$rank = 1;
			}
			 
	        $sql = sprintf("INSERT INTO " . TABLE_GALLERY . " (`photo`, `uid`, `description`, `rank`) VALUES " 
	        	. "('%s', '%s', '%s', '%s')", $photo, $id, $this->description, $rank);
		} 
		$count = $this->user_gallery($id, 0)[0]['count'];
		$response = successMessage($LANG['upload_success']);

		if ($type == 5) {
			return $count<5 ? dbProcessor($sql, 0, $response) : infoMessage($LANG['upload_limit']);  
		} else {
			return dbProcessor($sql, 0, $response);
		}
		
	} 

	function user_gallery($user='', $type, $photo='') {
		// Type 0 == Count
		// Type 1 == View All
		// Type 2 == View One

		if ($type == 0) {
			$sql = sprintf("SELECT COUNT(id) as count FROM " . TABLE_GALLERY . " WHERE uid = %s", $user);
		} elseif ($type == 1) {
			$r = isset($this->rank) ? sprintf('AND rank = %s', $this->rank) : '';
			$sql = sprintf("SELECT * FROM " . TABLE_GALLERY . " WHERE uid = %s %s ORDER BY date DESC", $user, $r);
		} else {
			$sql = sprintf("SELECT * FROM " . TABLE_GALLERY . " WHERE id = %s", $photo);
		} 
		 
		return dbProcessor($sql, 1);
	}

	function generateUsername($type = null) {
		// If type is set, generate a random username
		if($type) {
			$this->username = $this->transUsername().rand(0, 999);
		} else {
			$this->username = $this->transUsername();
		}
		
		// Replace the '.' sign with '_' (allows @user_mention)
		$this->username = str_replace('.', '_', $this->username);
		
		// Check if the username exists
		$checkUser = $this->userData($this->username)['username'];
		
		if($checkUser) {
			$this->generateUsername(1);
		}  
	}

	function transUsername() {
		if(ctype_alnum($this->firstname) && ctype_alnum($this->lastname)) {
			return $this->username = $this->firstname.'.'.$this->lastname;
		} elseif(ctype_alnum($this->firstname)) {
			return $this->firstname;
		} elseif(ctype_alnum($this->lastname)) {
			return $this->lastname;
		} else {
			// Parse email address
			$email = explode('@', $this->email);
			$email = preg_replace("/[^a-z0-9]+/i", "", $email[0]);
			if(ctype_alnum($email)) {
				return $email;
			} else {
				return rand(0, 9999);
			}
		}
	}

	function generatePassword($length) {
		// Allowed characters
		$chars = str_split("abcdefghijklmnopqrstuvwxyz0123456789");
		
		// Generate password
	    $password = '';
		for($i = 1; $i <= $length; $i++) {
			// Get a random character
			$n = array_rand($chars, 1);
			
			// Store random char
			$password .= $chars[$n];
		}
		return $password;
	}

	function deleteUser($id) {
		global $DB;
		// First get the user details
		$this->user_id = $id;
		$user = $this->userData(0, 1)[0];
		// Then delete all related images from storage
		($user['cover']) ? deleteImages($user["cover"], 0) : ''; 
		($user['photo']) ? deleteImages($user["photo"], 1) : '' ;
	
		// Fetch and delete users contests	
		$gett = new contestDelivery;
		$sql = sprintf("SELECT creator,id FROM " .TABLE_CONTEST. " WHERE cid = '%s'", $id);
		$res = dbProcessor($sql, 1);//print_r($res[0]['id']);
		// while (count($res) > 0) {
		// 	$echo = $gett->deleteContest($res[0]['id']);
		// }
		$x = count($res);
		$echo = '';
		for ($i=0; $i < $x; $i++) { 
			$echo = $gett->deleteContest($res[0]['id']);
		}

		// Then remove all DB entries
		$sql = sprintf("DELETE FROM " . TABLE_GENERATE . " WHERE `user_id` = '%s'",  $id); 
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_REFER . " WHERE `user` = '%s'",  $id); 
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_CONTESTANT . " WHERE `contestant_id` = '%s'",  $id); 
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_COMMENTS . " WHERE `contestant_id` = '%s'",  $id); 
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_APPLY . " WHERE `user_id` = '%s'",  $id); 
		dbProcessor($sql, 0); 
		//If you do this send a message to the voters so they can vote again 
		$sql = sprintf("DELETE FROM " . TABLE_VOTERS . " WHERE `contestant_id` = '%s'",  $id);
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_NOTIFY . " WHERE `receiver` = '%s'",  $id);
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_BANK . " WHERE `user_id` = '%s'",  $id);
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_TIMELINE . " WHERE `user_id` = '%s'",  $id);
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_TIMELINE . " WHERE `share_id` = '%s'",  $id);
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_RELATE . " WHERE `leader_id` = '%s'",  $id);
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_RELATE . " WHERE `follower_id` = '%s'",  $id);
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_LIKE . " WHERE `user_id` = '%s'",  $id);
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_LIKE . " WHERE `owner_id` = '%s'",  $id);
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_MESSAGE . " WHERE `sender` = '%s'",  $id);
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_MESSAGE . " WHERE `receiver` = '%s'",  $id);
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_BLOCK . " WHERE `user_id` = '%s'",  $id);
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_BLOCK . " WHERE `by` = '%s'",  $id);
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_COMMENTS . " WHERE `writer_id` = '%s'",  $id);
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_COMMENTS . " WHERE `receiver_id` = '%s'",  $id);
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_PAYMENT . " WHERE `payer_id` = '%s'",  $id);
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_NOTIFY . " WHERE `sender` = '%s'",  $id);
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_NOTIFY . " WHERE `receiver` = '%s'",  $id);
		dbProcessor($sql, 0);
		$sql = sprintf("DELETE FROM " . TABLE_USERS . " WHERE `id` = '%s'",  $id);
		$msg = 1;
		$return = dbProcessor($sql, 0, $msg);
		$response = ($return == 1) ? successMessage('Profile Deleted') : infoMessage($return);
		return $echo;	
	}

	function collectUserName($username = null, $st, $id = null) { 
		global $CONF;
		// $st = user, 0: Get User details
		// $st = contest, 1 :  Get Contest details
		$type = $st == 1 ? 'contest' : 'user';  

		if ($type == 'user') {
			$this->user_id = db_prepare_input($id);
			// Check if a username or id was provided
			$_user = $id ? $this->userData(0, 1)[0] : $this->userData(db_prepare_input($username));

			// Check the user premium state and add the badge
			$premium_status = $this->premiumStatus($_user['id'], 2);
			$badge = ($premium_status) ? badge(0, $premium_status['plan'], 2) : '';

			// Set the profile address
			$location = profilesCountry($_user['username']);

			// Set the profile link
			$profile_link = permalink($CONF['url'].'/index.php?a=profile&u='.$_user['username']);

			// Timeline link
			$timeline = permalink($CONF['url'].'/index.php?a=timeline&u='.$_user['username']);

			// Set message link
			$message = permalink($CONF['url'].'/index.php?a=messenger&u='.$_user['username'].'&id='.$_user['id']);

			// Set the fullname with badge
			$realname = realName($_user['username'], $_user['fname'], $_user['lname']).' '.$badge;

			// Set the fullname with badge and suffix
			$suffixname = realName($_user['username'], $_user['fname'], $_user['lname']).'\'s '.$badge;

			// Set the fullname without badge
			$fullname = realName($_user['username'], $_user['fname'], $_user['lname']);

			$intro = completeIntro($_user['city'], $_user['state'], $_user['country'], $_user['lovesto']); 

			// Set the parameters
			$result = 
				array('profile' => $profile_link, 'message' => $message, 'address' => $location,
					'fullname' => $realname, 'fullnamex' => $suffixname, 'name' => $fullname, 
					'username' => ucfirst($_user['username']), 'firstname' => ucfirst($_user['fname']), 
					'lastname' => ucfirst($_user['lname']), 'photo' => $_user['photo'], 
					'user_id' => $_user['id'], 'cover' => $_user['cover'], 'intro' => $intro,
					'mainintro' => $_user['intro'], 'timeline' => $timeline
				);	
		} else {
			$cd = new contestDelivery;
			$contest = $cd->getContest(0, $id);
			$contest_link = permalink($CONF['url'].'/index.php?a=contest&s='.$contest['safelink']);
			$contest_id_link = permalink($CONF['url'].'/index.php?a=contest&id='.$contest['id']);
			$voting_link = permalink($CONF['url'].'/index.php?a=voting&id='.$contest['id']);
			$cover = $CONF['url'].'/uploads/cover/contest/'.$contest['cover'];
			$result = 
				array('title' => ucfirst($contest['title']), 'safelink' => $contest_link, 'voting' => $voting_link,
					'id_link' => $contest_id_link, 'photo' => $contest['cover'], 'id' => $contest['id'],
					'mainintro' => $contest['intro'], 'type' => $contest['type'], 'cover' => $cover
				); 
		} 
		return $result;		
	}

	function premiumStatus($id = null, $type = null) {
		global $DB, $LANG, $PTMPL, $CONF, $user, $contest_id, $settings;
		// Type 0: Check whether a user is a premium user or not
		// Type 1: Check if premium accounts are enabled, then check whether a user is a premium user or not
		// Type 2: Return the results from last transaction 
		$sql = sprintf("SELECT * FROM " . TABLE_PAYMENT . " WHERE payer_id = '%s' ORDER BY `id` DESC LIMIT 0, 1", ($id) ? $id : $user['id']);
		$result = dbProcessor(isset($sql)?$sql:'', 1)[0];
		
		if($type == 1) {
			if($settings['premium']) { 
				if($result['status'] && strtotime($result['valid_till']) >= time()) {
					return 1;
				} else {
					return 0;
				}
			} else { 
				// If premium is disabled provide user with premium features
				return 1;
			}
		} elseif($type == 2) {
			return $result;
		} else {
			if($result['status'] == 1 && strtotime($result['valid_till']) >= time()) {   
				return 1;
			} else {
				return 0;
			}
		}
	}

	function premiumHistory($id = null, $type) { 
		// Type 0: Return all transaction history
		// Type 1: Return inactive transactions
		global $LANG, $user, $PTMPL, $CONF;
				
		if($type) {
			$x = ' AND `valid_till` < \''.date('Y-m-d H:i:s').'\'';
		} else {
			$x = '';
		}
 
		$sql = sprintf("SELECT * FROM " . TABLE_PAYMENT . " WHERE payer_id = '%s'%s ORDER BY `id` DESC", ($id) ? $id : $user['id'], $x);
		$data = dbProcessor(isset($sql)?$sql:'', 1);

		$nb=0;
		$result = '';
		if(!empty($data)) {
			$result = '<table class="table table-borderless">'; 
				$result .= '
				<p class="text-danger pt-2 font-weight-bold">Transaction History</p>
				<thead>
				    <tr>
				      <th scope="col">#</th>
				      <th scope="col">'.$LANG['paid_on'].'</th>
				      <th scope="col">'.$LANG['expires'].'</th>
				      <th scope="col">'.$LANG['plan'].'</th>
				      <th scope="col">'.$LANG['price'].'</th> 
				    </tr>
				</thead>'; 

			foreach($data as $rs) {
				$nb = $nb+1;
				$paid_onArr = explode('-', $rs['payment_date']);
				$date_paid = $paid_onArr[0].'-'.$paid_onArr[1].'-'.substr($paid_onArr[2], 0, 2);
				$planArr = explode('_', $rs['plan']); 
				$plan_name = ucfirst($planArr[0]);
				$expiresArr = explode('-', $rs['valid_till']);
				if ($rs['status'] == 0) {
					$expires = $LANG['suspended'];
				} elseif (strtotime($rs['valid_till'])>strtotime(date("Y-m-d H:i:s"))) {
					$expires = substr($expiresArr[2], 0, 2).'-'.$expiresArr[1].'-'.$expiresArr[0]; 
				} else {
					$expires = $LANG['expired'];
				}
				
				$result .= '  
				<tbody>
				    <tr>
				      <th scope="row">'.$nb.'</th>
				      <td>'.$date_paid.'</td>
				      <td>'.$expires.'</td>
				      <td>'.$plan_name.'</td>
				      <td>'.$rs['amount'].' '.$rs['currency'].'</td>
				    </tr> 
				</tbody>';
			}
			$result .= '</table>';
		}
		
		return $result;
	}

	function premiumUsers($id = null, $type = null) { 
		// Get all premium users for admin
		if ($type == 1) {
			$extra = (isset($this->extra)) ? $this->extra : sprintf('payer_id = \'%s\'', $id);
			$sql = sprintf("SELECT * FROM " . TABLE_PAYMENT . " WHERE %s ORDER BY `id` DESC", $extra);
		} elseif ($type == 2) {
			$sql = sprintf("UPDATE " . TABLE_PAYMENT . " SET  `status` = '%s' WHERE `payer_id` = %s", $this->status, $id); 
			$t = 'Settings Saved';			 
		} elseif ($type == 3) {
			$sql = sprintf("DELETE FROM " . TABLE_PAYMENT . " WHERE `payer_id` = '%s'",  $id);
			$t = 'Deleted';			 
		} else {
			$sql = "SELECT * FROM " . TABLE_PAYMENT . " WHERE 1 ORDER BY `id` DESC";
		}
		$data = dbProcessor($sql, isset($t)?0:1, isset($t)?$t:'');
		if (isset($t)) {
			return ($t == $data) ? successMessage($t) : infoMessage($data);
		} else {
			return $data;
		}
	}

	function site_admin($type) {
		// Type 1: Login
		// Type 2: Change Password
		// Type 3: Login

		if ($type == 0) {
			$sql = "SELECT * FROM " . TABLE_ADMIN . " WHERE 1";
			$t = 1;
		} elseif ($type == 1) {
			$sql = sprintf("UPDATE " . TABLE_ADMIN . " SET `password` = '%s' WHERE `id` = %s", $this->password, $this->admin);
			$t = 0;
		} elseif ($type == 2) {
			$sql = sprintf("SELECT * FROM " . TABLE_ADMIN . " WHERE username = '%s' AND password = '%s'", $this->username, $this->password);
			$t = 1;
		}
		return dbProcessor($sql, $t, ($t==0)?1:null);
	}
}

class facebook {
	function fetch_token($redirect_uri, $code, $app_id, $secret) {
		// Token URI
		$uri = 'https://graph.facebook.com/oauth/access_token?client_id='.$app_id.'&redirect_uri='.urlencode($redirect_uri).'&client_secret='.$secret.'&code='.$code;
		
		// Fetch the access token
		$response = json_decode(fetch($uri), true);

		// Return the json response as parameters
		return $response;
	}

	function fetch_picture($access_token) {
		// Build the Graph URL
		$uri = "https://graph.facebook.com/me/picture?width=500&height=500&access_token=".$access_token;
		
		// Get the picture
		$picture = fetch($uri);
		
		// Generate the file name 
		$new_image = mt_rand().'_'.mt_rand().'_'.mt_rand().'_n.jpg';
		$file_path = __DIR__ .'/../uploads/faces/';
		$cover_path = __DIR__ .'/../uploads/cover/';
		
		// Save the picture
		$rw = fopen($file_path.$new_image, 'wb');
		$crw = fopen($cover_path.$new_image, 'wb');
		
		// If the file already exist
		if(!file_exists($file_path.$new_image)) { 
			return false;
		}
		// If the cover file already exist
		if(!file_exists($cover_path.$new_image)) { 
			return false;
		}
		
		// Write the picture
		fwrite($rw, $picture);
		// Write the cover
		fwrite($crw, $picture);
		
		// Close
		fclose($rw);
		fclose($crw);
		
		// Return the filename
		return $new_image;
	}

	function facebookAccess() {

		if ($this->fbacc) {
			$fetch_token = $this->fetch_token($this->url.'/connection/connect.php?facebook=true', $this->code, $this->fb_appid, $this->fb_secret);
			$user = $this->fetch_info($fetch_token['access_token']);
			if($fetch_token == null || $_SESSION['state'] == null || ($_SESSION['state'] != $this->state) || empty($user->email)) { 
				if (isset($_SESSION['state'])) {
					unset($_SESSION['state']);
				}
				header("Location: ".$this->url);
			}

			$data = new userCallback; // fetch the user database
			if(!empty($user->email)) {

				$email = $user->email;

				$user_data = $data->userData($email);

				// If this is a returning user
				if($user_data) {
					if($user_data['status'] == 1) {
						header("Location: ".$this->url);
						return false;
					}
					
					// Set the sessions and then log-in
					$_SESSION['username'] = $user_data['username'];
					$_SESSION['password'] = $user_data['password'];

					// Redirect user
					if (isset($_SESSION['referrer'])) {
						$header = urldecode(urlReferrer($_SESSION['referrer'], 1));
						unset($_SESSION['referrer']);
					} else {
						$header = permalink($CONF['url'].'/index.php?a=account');
					} 	
					header("Location: ".$header);
				} else {
					global $settings;
					$save = new siteClass;

	 				$data->email = $user->email;
	 				$data->firstname = $user->first_name;
					$data->lastname = $user->last_name;
					$data->generateUsername();
					$data->password = hash('md5', $data->generatePassword(8));

					// Create the new user profile 
					$data->city = '';
					$data->state = '';
					$data->country = '';
					$data->phone = '';
					$data->claim = 1;
					$data->create = 1;
					$data->createContestant(); 
					
					$update = $data->userData($data->email);
					$data->photo = $this->fetch_picture($fetch_token['access_token']);
					$data->updatePhoto($update['id'], 1);
					$data->updatePhoto($update['id'], 0);

					// Add the signup bonus
					if ($settings['signup_bonus']) {
						$save->balance = $settings['signup_bonus']; 
						$save->passCredits(2, $update['id']); 
					} 		

					$_SESSION['username'] = $update['username'];
					$_SESSION['password'] = $update['password'];
					return 1;
				}
			}						
		}
	}

	function fetch_info($access_token) {
		// Build the Graph URL
		$uri = "https://graph.facebook.com/me?fields=id,email,first_name,gender,last_name,link,locale,name,timezone,updated_time,verified&access_token=".$access_token;
		
		// Get the the json response
		$user = json_decode(fetch($uri));
		
		// Return the user info
		if($user != null && isset($user->name)) {
			return $user;
		}
		return null;
	}	
}

class doRecovery { 
	public $username;	// The username to recover
	
	function verify_user() {
		// Query the database and check if the username exists
		if(filter_var(db_prepare_input($this->username), FILTER_VALIDATE_EMAIL)) {
			$sql = sprintf("SELECT `username`,`email` FROM ".TABLE_USERS." WHERE `email` = '%s'", db_prepare_input(mb_strtolower($this->username)));
		} else {
			$sql = sprintf("SELECT `username`,`email` FROM ".TABLE_USERS." WHERE `username` = '%s'", db_prepare_input(mb_strtolower($this->username)));
		}

		$result = dbProcessor($sql, 1); 
		
		// If user is verified or found
		if (count($result) > 0) {  
			// fetch the users data
			$u = new userCallback;
			$data = $u->userData(db_prepare_input(mb_strtolower($this->username)));

			// Generate the recovery key
			$key = $this->setToken($data['username']);
			
			// If the recovery key has been generated
			if($key) {
				// Return the username, email and recovery key
				return array($data['id'], $data['username'], $data['fname'], $data['lname'], $data['email'], $key);
			}
		}
	}
	
	function setToken($username) {
		// Generate the token
		$key = rand(409009, 901100);
				
		// Prepare to update the database with the token
		$date = date("Y-m-d H:i:s");
		$sql = sprintf("UPDATE ".TABLE_USERS." SET `token` = '%s', `reg_date` = '%s' WHERE `username` = '%s'", db_prepare_input($key), $date, db_prepare_input(mb_strtolower($username))); 
		 
		$result = dbProcessor($sql, 0, 1); 

		// If token was updated return token
		if($result == 1) {
			return $key;
		} else {
			return false;
		}
	}
	
	function changePassword($username, $password, $key) {
		// Check if the username and the token exists
		$sql = sprintf("SELECT `username` FROM ".TABLE_USERS." WHERE `username` = '%s' AND `token` = '%s'", db_prepare_input(mb_strtolower($username)), db_prepare_input($key));
		$result = dbProcessor($sql, 1);
		
		// If a valid match was found
		if (count($result) > 0) {
			$password = hash('md5', $password);
			
			// Change the password
			$sql = sprintf("UPDATE ".TABLE_USERS." SET `password` = '%s', `token` = '' WHERE `username` = '%s'", $password, db_prepare_input(mb_strtolower($username)));  

			$result = dbProcessor($sql, 0, 1);

			if($result == 1) {
				return true;
			} else {
				return false;
			}
		}
	}
}

class siteClass {
	function site_settings($type) {
		global $LANG, $user, $PTMPL, $CONF, $settings;

		if ($type == 0) {
			// Save Site settings
			$sql = sprintf("UPDATE " . TABLE_SETTINGS . " SET `site_name` = '%s',`site_phone` = '%s', `mode` = '%s', `activation` = '%s', `sidebar` = '%s', `direction` = '%s', `tracking` = '%s', `recommend` = '%s'", $this->sitename, $this->sitephone, $this->site_mode, $this->activation, $this->sidebar, $this->direction, $this->tracking, $this->recommend); 
			$response = 'Settings Saved'; 	 
		} elseif ($type == 1) {
			// limits settings
			$sql = sprintf("UPDATE " . TABLE_SETTINGS . " SET `per_explore` = '%s', `per_table` = '%s', `per_notification` = '%s', `per_messenger` = '%s', `per_notification_drop` = '%s', `per_featured` = '%s', `per_contest` = '%s', `per_voting` = '%s'", $this->explore, $this->table, $this->notifications, $this->messenger, $this->notifications_drop, $this->featured, $this->contest, $this->voting); 
			$response = 'Settings Saved';			 
		} elseif ($type == 2) {
			// register settings
			$sql = sprintf("UPDATE " . TABLE_SETTINGS . " SET `captcha` = '%s', `invite_only` = '%s', `fb_appid` = '%s', `fb_secret` = '%s'", $this->captcha, $this->invite, $this->fb_appid, $this->fb_secret); 
			$response = 'Settings Saved';			 
		} elseif ($type == 3) {
			// email transport settings
			$sql = sprintf("UPDATE " . TABLE_SETTINGS . " SET `sms_premium` = '%s', `sms` = '%s', `twilio_phone` = '%s', `twilio_sid` = '%s', `twilio_token` = '%s', `email_apply` = '%s', `email_approved` = '%s',`email_social` = '%s', `email_vote` = '%s', `email_comment` = '%s', `email_welcome` = '%s', `smtp` = '%s', `smtp_secure` = '%s', `smtp_auth` = '%s', `smtp_port` = '%s', `smtp_server` = '%s', `smtp_username` = '%s', `smtp_password` = '%s'", $this->premium_sms, $this->send_sms, $this->twilio_phone, $this->twilio_sid, $this->twilio_token, $this->email_apply, $this->email_approved, $this->email_social, $this->email_vote, $this->email_comment, $this->email_welcome, $this->smtp, $this->smtp_secure, $this->smtp_auth, $this->smtp_port, $this->smtp_server, $this->smtp_username, $this->smtp_password);
			$response = 'Settings Saved';			 
		} elseif ($type == 4) {
			// email template settings
			$sql = sprintf("UPDATE " . TABLE_SETTINGS . " SET `email_approved_temp` = '%s', `email_declined_temp` = '%s', `email_comment_temp` = '%s', `email_reply_temp` = '%s', `email_vote_temp` = '%s', `email_apply_temp` = '%s', `email_reg_temp` = '%s', `email_recover_temp` = '%s'", $this->approved, $this->declined, $this->comment, $this->reply, $this->vote, $this->apply, $this->register, $this->recover); 
			$response = 'Templates Settings Saved';
		} elseif ($type == 5) {
			// Rave payment settings
			$sql = sprintf("UPDATE " . TABLE_SETTINGS . " SET `rave_mode` = '%s', `rave_public_key` = '%s', `rave_private_key` = '%s', `rave_encryption_key` = '%s'", $this->mode, $this->public, $this->private, $this->encryption); 
			$response = 'Rave payment Settings Saved';			 
		} elseif ($type == 6) {
			// Payment settings
			$sql = sprintf("UPDATE " . TABLE_SETTINGS . " SET `currency` = '%s', `premium_votes` = '%s', `premium_plan` = '%s', `clead_plan` = '%s', `cmarx_plan` = '%s', `slight_plan` = '%s', `lite_plan` = '%s', `life_plan` = '%s'", $this->currency, $this->votes, $this->premium, $this->clead, $this->cmarx, $this->slight, $this->lite, $this->life); 
			$response = 'Payment Settings Saved';			 
		} elseif ($type == 7) {
			// Rave payment settings
			$sql = sprintf("UPDATE " . TABLE_SETTINGS . " SET `ads_1` = '%s', `ads_2` = '%s', `ads_3` = '%s', `ads_4` = '%s', `ads_5` = '%s', `ads_6` = '%s', `ads_off` = '%s'", $this->unit_1, $this->unit_2, $this->unit_3, $this->unit_4, $this->unit_5, $this->unit_6, $this->status); 
			$response = 'Ads Settings Saved';			 
		} elseif ($type == 8) {
			// Rave payment settings
			$sql = sprintf("UPDATE " . TABLE_SETTINGS . " SET `pc_vote` = '%s', `pc_comment` = '%s', `pc_enter` = '%s', `pc_symbol` = '%s', `cashout` = '%s', `cashout_retain` = '%s', `cashout_max` = '%s', `pc_value` = '%s', `signup_bonus` = '%s', `pc_agent_percent` = '%s', `pc_ref_percent` = '%s'", $this->vote, $this->comment, $this->enter, $this->symbol, $this->cashout, $this->cashout_retain, $this->cashout_max, $this->value, $this->bonus, $this->agency, $this->referral); 
			$response = 'Pricing Settings Saved';			 
		}

		$return = dbProcessor($sql, 0, 1);
		if ($return == 1) {
			 return successMessage($response);
		} else {
			return infoMessage($return);
		}
	}

	function mailerDaemon($sender, $receiver, $subject, $message) {
		// Load up the site settings
		global $CONF, $settings, $user, $mail;

		// show the message details if test_mode is on
		$return_response = null;
		$echo =
		'<small class="p-1"><div class="text-warning text-justify"
		Sender: '.$sender.'<br>
		Receiver: '.$receiver.'<br>
		Subject: '.$subject.'<br>
		Message: '.$message.'<br></div></small>';
		if (trueAjax() && $GLOBALS['test_mode'] == true) {
			echo $echo;
		}

		$userApp = new userCallback;
	    $userApp->user_id = isset($this->user_id) ? $this->user_id : 0;
	    $y = $userApp->userData(NULL, 1)[0]; 

	    // Send a test email message
	    if (isset($this->test)) {
	    	$sender = $CONF['email'];
	    	$receiver = $CONF['email'];
	    	$subject = 'Test EMAIL Message from '.$settings['site_name'];
	    	$message = 'Test EMAIL Message from '.$settings['site_name'];
	    	$return_response = successMessage('Test Email Sent');
	    }

		if ($y && $y['email_notifications'] == 0 && !isset($this->reg)) {
			 return false;
		} else {
			// If the SMTP emails option is enabled in the Admin Panel
			if($settings['smtp']) { 
 
				require_once(__DIR__ . '/vendor/autoload.php');
				
				//Tell PHPMailer to use SMTP
				$mail->isSMTP();

				//Enable SMTP debugging
				// 0 = off 
				// 1 = client messages
				// 2 = client and server messages
				$mail->SMTPDebug = ($settings['mode'] == 'debug') ? '2' : '0';
				
				$mail->CharSet = 'UTF-8';	//Set the CharSet encoding
				
				$mail->Debugoutput = 'html'; //Ask for HTML-friendly debug output
				
				$mail->Host = $settings['smtp_server'];	//Set the hostname of the mail server
				
				$mail->Port = $settings['smtp_port'];	//Set the SMTP port number - likely to be 25, 465 or 587
				
				$mail->SMTPAuth = $settings['smtp_auth'] ? true : false;	//Whether to use SMTP authentication
				
				$mail->Username = $settings['smtp_username'];	//Username to use for SMTP authentication
				
				$mail->Password = $settings['smtp_password'];	//Password to use for SMTP authentication
				
				$mail->setFrom($sender, $settings['site_name']);	//Set who the message is to be sent from
				
				$mail->addReplyTo($sender, $settings['site_name']);	//Set an alternative reply-to address
				if($settings['smtp_secure'] !=0) {
					$mail->SMTPSecure = $settings['smtp_secure'];
				} else {
					$mail->SMTPSecure = false;
				}
				//Set who the message is to be sent to
				if(is_array($receiver)) {
					foreach($receiver as $address) {
						$mail->addAddress($address);
					}
				} else {
					$mail->addAddress($receiver);
				}
				//Set the message subject 
				$mail->Subject = $subject;
				//convert HTML into a basic plain-text alternative body,
				//Read an HTML message body from an external file, convert referenced images to embedded
				$mail->msgHTML($message);

				//send the message, check for errors
				if(!$mail->send()) {
					// Return the error in the Browser's console
					#echo $mail->ErrorInfo;
				}
			} else {
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=utf-8' . PHP_EOL;
				$headers .= 'From: '.$settings['site_name'].' <'.$sender.'>' . PHP_EOL .
					'Reply-To: '.$settings['site_name'].' <'.$sender.'>' . PHP_EOL .
					'X-Mailer: PHP/' . phpversion();
				if(is_array($receiver)) {
					foreach($receiver as $address) {
						@mail($address, $subject, $message, $headers);
					}
				} else {
					@mail($receiver, $subject, $message, $headers);
				}
			}			
		}
		return $return_response;
	}

	function message_template($temp, $array) {
	    global $LANG, $PTMPL, $CONF, $user, $settings, $EM; 
	    $action = new actions;

	    // $params = array('contest', 'username', 'password', 'firstname', 'lastname', 'key', 'email',
    	// 	'act_username', 'act_firstname', 'act_lastname', 'action', 'action_on');

		list($contest, $username, $password, $firstname, $lastname, $key, $email, $act_username, $act_firstname, $act_lastname, $action, $action_on) = $array;

	    // Message receivers details
	    $EM['contest'] = $contest;
	    $EM['username'] = $username;
	    $EM['password'] = $password;
	    $EM['firstname'] = $firstname;
	    $EM['lastname'] = $lastname;
	    $EM['key'] = $key;
	    $EM['email'] = $email;

	    // Details of who is performing an action
	    $EM['act_username'] = $act_username;
	    $EM['act_firstname'] = $act_firstname;
	    $EM['act_lastname'] = $act_lastname;

	    // The action triggering this Message
	    $EM['action'] = $action;

	    // action on poll ot post
	    $EM['action_on'] = $action_on;

		$msg = preg_replace_callback('/{\$em->(.+?)}/i', function($matches) {
			global $EM;
			return (isset($EM[$matches[1]])?$EM[$matches[1]]:"");
		}, $temp);

	    return $msg; 
	} 

	function manage_gift_cards($type = null, $contest = null) {
		global $user;
		// type 0: get cards for a contest
		// type 1: get all the cards
		// type 2: check if the token exist
		// type 3: check if the token belongs to the set contest
		// type 4: create new tokens
		// type 5: Set the status of the token to be used by this user
		// type 6: Add contest to list of contests with tokens
		// type 7: View all contests with a token

		if (isset($this->limit)) {
			$limit = sprintf('ORDER BY invalid_by ASC LIMIT %s, %s', $this->start, $this->limit);
		} else {
			$limit = 'ORDER BY invalid_by ASC';
		}

		if ($type == 0) {
			$sql = sprintf("SELECT * FROM " . TABLE_GIFT . " WHERE contest = '%s' %s ", $contest, $limit);
			$t = 1;
		} elseif ($type == 1) { 
			$sql = sprintf("SELECT * FROM " . TABLE_GIFT . " %s ", $limit);
			$t = 1;
		} elseif ($type == 2) {
			$sql = sprintf("SELECT * FROM " . TABLE_GIFT . " WHERE token = '%s'", $this->token);
			$t = 1;
		} elseif ($type == 3) {
			$sql = sprintf("SELECT * FROM " . TABLE_GIFT . " WHERE token = '%s' AND contest = '%s'", $this->token, $contest);
			$t = 1;  
		} elseif ($type == 4) {
			$sql = sprintf("INSERT INTO " . TABLE_GIFT . " (`contest`, `token`, `valid_till`, `invalid_by`, `value`)"
				." VALUES ('%s', '%s', '%s', '%s', '%s')", $this->contest, $this->token, $this->valid, $this->expires, $this->value);
			$t = 0;
		} elseif ($type == 5) {
			$sql = sprintf("UPDATE " . TABLE_GIFT . " SET  `used_by` = '%s' WHERE `id` = %s", $user['id'], $this->token_id);
			$t = 0;
		} elseif ($type == 6) {
			$sql = sprintf("INSERT INTO " . TABLE_GIFTED . " (`contest_id`, `contest`) VALUES ('%s', '%s')", $this->contest, 
				$this->contest_title);
			$t = 0;
		} elseif ($type == 7) {
			$drt = ($contest) ? ' WHERE contest_id = \''.$contest.'\'' : ' WHERE 1';
			$sql = sprintf("SELECT * FROM " . TABLE_GIFTED . "%s", $drt); 
			$t = 1;
		} elseif ($type == 8) {
			$drt = ($contest) ? 'WHERE contest_id = \''.$contest.'\'' : '';
			$wht = (isset($this->what)) ? $this->what : '1';
			$sql = sprintf("SELECT * FROM " . TABLE_GIFT . " WHERE %s %s", $wht, $drt); 
			$t = 1;
		}
		return dbProcessor($sql, $t); 
	}

	function passCredits($type, $user_id=null) {
		// type 0: get credit
		// type 1: add credit if user already has
		// type 1: insert new credit balance
		global $user, $settings; 
		$save = new siteClass;
		$us = new userCallback; 

		$balance = (isset($this->balance)) ? $this->balance : '';
		$date = date("Y-m-d H:i:s");
		$what = (isset($this->what)) ? $this->what : '1';
		if ($type == 0) {
			$sql = sprintf("SELECT * FROM " . TABLE_CREDIT . " WHERE %s", $what); 
			$t = 1;
		} elseif ($type == 1) { 
			$sql = sprintf("UPDATE " . TABLE_CREDIT . " SET `balance` = '%s', `date` = '%s'"
				." WHERE `user` = '%s'", db_prepare_input($balance), $date, $user_id); 
			$t = 0;
		} elseif ($type == 2) {
			$sql = sprintf("INSERT INTO " . TABLE_CREDIT . " (`balance`, `user`, date) VALUES ('%s', '%s', '%s')", $balance, $user_id, $date);
			$t = 0;
		}

		// Check if this is not a select operation
		if ($type != 0) {
			// Get the users data
			$us->user_id = $user_id;
			$data = $us->userData(NULL, 1)[0];

			// Check the users referrer
			$ref = $save->referrals(2, 0, $data['username']);
			if ($ref) {
				// Fetch the referrers balance
				$this->what = sprintf('user = \'%s\'', $ref['referrer']);
				$credit = $this->passCredits(0)[0];
				// Sum the new balance
				$balance = $credit['balance'] + ($settings['pc_ref_percent'] * $balance / 100);
				$this->balance = $balance;
				// Add the balance as bounty to the referrer
				if ($credit) { 
					$this->passCredits(1, $ref['referrer']);
				} else {  
					$this->passCredits(2, $ref['referrer']);
				}				 
			}			 
		}		
		return isset($t) ? dbProcessor($sql, $t, ($t==0)?1:0) : ''; 
	}

	function coupon_generator() {
		global $settings, $user;
		
		// Generate a random 13 digit numeric string
	  	$key = rand(4000000000000,9000000000000);
	  	// Fetch already created tokens
	  	$this->token = $key;
	  	$get_token = $this->manage_gift_cards(2)[0];
	  	$token = $get_token['token'];
	  	// Generate a new key if it has already been Generated
	  	if ($token == $key) {
	  		$coupon = rand(4000000000000,9000000000000);
	  	} else {
	  		$coupon = $key;
	  	}
	  	return $coupon;
	}

	function static_pages($type, $x=null, $id=null) {
		// Type 0: Fetch all
		// Type 1: Create a new page
		// Type 2: Update the selected page
		// Type 3: delete the selected page

		// x 0: Static pages
		// x 1: Documentation pages

		global $settings, $user;
 
		if ($type == 0) {
			$wht = (isset($this->what)) ? sprintf("type = '%s' AND %s ", $x, $this->what) : sprintf('type = \'%s\'', $x); 
			$sql = sprintf("SELECT * FROM " . TABLE_PAGES . " WHERE %s", $wht); 
			$t = 1;
		} elseif ($type == 1) {
			$sql = sprintf("INSERT INTO " . TABLE_PAGES . " (`link`, `title`, `content`, `status`, `type`) VALUES ('%s', '%s', '%s', '%s', '%s')", db_prepare_input($this->link), db_prepare_input($this->title), db_prepare_input($this->content), $this->status, $x);
			$t = 0;
		} elseif ($type == 2) {
			$sql = sprintf("UPDATE " . TABLE_PAGES . " SET  `link` = '%s', `title` = '%s', `content` = '%s', `status` = '%s' WHERE `id` = %s", db_prepare_input($this->link), db_prepare_input($this->title), db_prepare_input($this->content), $this->status, $id); 
			$t = 0;
		} elseif ($type == 3) {
			$sql = sprintf("DELETE FROM " . TABLE_PAGES . " WHERE `id` = '%s'", $id); 
			$t = 0;
		} elseif ($type == 4) {
			$x = (isset($this->type)) ? $this->type : $x;
			$sql = sprintf("INSERT INTO " . TABLE_PAGES . " (`link`, `title`, `content`, `status`, `category`, `featured`, `type`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s')", db_prepare_input($this->link), db_prepare_input($this->title), db_prepare_input($this->content), $this->status, $this->category, $this->featured, $x);
			$t = 0;
		} elseif ($type == 5) {
			$x = (isset($this->type)) ? $this->type : $x;
			$sql = sprintf("UPDATE " . TABLE_PAGES . " SET `link` = '%s', `title` = '%s', `content` = '%s', `status` = '%s', `category` = '%s', `featured` = '%s', `type` = '%s' WHERE id = %s", db_prepare_input($this->link), db_prepare_input($this->title), db_prepare_input($this->content), $this->status, $this->category, $this->featured, $x, $id);
			$t = 0; $r = 1;
		}
		return dbProcessor($sql, $t, ($t==0)?1:'');
	}

	/**
	* Create and Manage support tickets
	*/
	function support_system($type, $id=null) {
		// Type 0: Select Support ticket
		// Type 1: Insert Support ticket
		// Type 2: Delete Support ticket
		// Type 3: Delete all reply threads
		global $settings, $user;

		if ($type == 0) {
			$wht = (isset($this->what)) ? sprintf("%s ", $this->what) : '1';
			 $sql = sprintf("SELECT * FROM " . TABLE_SUPPORT . " WHERE %s", $wht);
			 $t = 1; 
		} elseif ($type == 1) { 
			$sql = sprintf("INSERT INTO " . TABLE_SUPPORT . " (`user_id`, `subject`, `message`, `priority`, `type`, `reply`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s')", $user['id'], db_prepare_input($this->subject), db_prepare_input($this->message), $this->priority, $this->type, $id);
			$t = 0;
		} elseif ($type == 2 || $type == 3) {  
			$del = ($type == 2) ? 'id' : 'reply';
			$sql = sprintf("DELETE FROM " . TABLE_SUPPORT . " WHERE `%s` = '%s'", $del, $id);
			$t = 0;
		} 
		return dbProcessor($sql, $t, ($t==0)?1:'');
	}

	/**
	* Manage the sites templating system
	*/
	function fetch_templates($type) {
		global $CONF, $LANG;
		
		if ($type == 0) {
			$templates = scandir('./'.$CONF['template_path'].'/');

			$sort = '';
			foreach($templates as $template) {
				if($template != '.' && $template != '..' && $template != 'index.html' && file_exists('./'.$CONF['template_path'].'/'.$template.'/property.php')) {
					$system_templates[] = $template;
					include('./'.$CONF['template_path'].'/'.$template.'/property.php');
						
					if($CONF['template_name'] == $template) {
						$state = '<a class="btn btn-primary btn-sm waves-effect active">Active</a>';
					} else {
						$state = '<a class="btn btn-primary btn-sm waves-effect" href="'.$CONF['url'].'/index.php?a=settings&b=site_templates&template='.$template.'">Activate</a>';
					}
					
					if(file_exists('./'.$CONF['template_path'].'/'.$template.'/thumb.png')) {
						$image = '<img src="'.$CONF['url'].'/'.$CONF['template_path'].'/'.$template.'/thumb.png" class="img-fluid rounded-circle" height="30%" width="30%"/>';
					}  else {
						$image = '';
					}
					
					$sort .= '
					<div class="p-2"> 
						<a href="'.$url.'" target="_blank" title="'.$LANG['template_author_home'].'">'.$image.'</a>
						<strong><a href="'.$url.'" target="_blank" title="'.$LANG['template_author_home'].'">'.$name.'</a></strong> '.$version.'<br />'.$LANG['by'].': <a href="'.$url.'" target="_blank" title="'.$LANG['template_author_home'].'">'.$author.'</a>
						<div class="manage-users-buttons">
							'.$state.'
						</div>
					</div>';
				}
			}
			return array($system_templates, $sort);
		} else {
			$sql = sprintf("UPDATE " . TABLE_SETTINGS . " SET `template` = '%s'", $this->template); 
        	return dbProcessor($sql, 0, 1);
		}
	}

	/**
	* Set the sites active skin and landing page
	*/
	function set_skin($type) {
		if ($type == 0) {
			global $settings;
			// Select skins
			$skins = 
			array('Default' => 'mdb-skin', 'White' => 'white-skin', 'Black' => 'black-skin', 
				'Cyan' => 'cyan-skin', 'Deep Purple' => 'deep-purple-skin', 'Navy Blue' => 'navy-blue-skin',
				'Pink' => 'pink-skin', 'Indigo' => 'indigo-skin', 'Light Blue' => 'light-blue-skin',
				'Grey' => 'grey-skin');

			$set = '';
			foreach ($skins as $skin => $value) {
				$selected = $value == $settings['skin'] ? ' selected="selected"' : ''; 
				$set .= '<option value="'.$value.'"'.$selected.'>'.$skin.'</option>';
			}
			return $set;
		} elseif ($type == 1) {
			// Update skin
			$sql = sprintf("UPDATE ".TABLE_SETTINGS." SET `skin` = '%s', `landing` = '%s'", $this->skin, $this->landing);
			$return = dbProcessor($sql, 0, 1);
			return $return;
		}
	}

	/**
	* Upload images for site use
	*/
	function site_uploader($type) {
		global $CONF;
	  	$errors= array();
	  	$file_name = $_FILES['file']['name'];
	  	$file_size = $_FILES['file']['size'];
	  	$file_tmp = $_FILES['file']['tmp_name'];
	  	$file_type= $_FILES['file']['type'];
	  	$var_string2lower = explode('.',$_FILES['file']['name']);
	  	$file_ext = strtolower(end($var_string2lower));

	  	if ($type == 2 || $type == 3 || $type == 4 || $type == 5 || $type == 6) {
	  		$expensions= array("jpeg","jpg","png");
	  		$xtn = 'JPEG, JPG or PNG';
	  	} else {
	  		$expensions= array("jpg");
	  		$xtn = 'JPG';
	  	}
	  	
  	  	if(in_array($file_ext,$expensions)=== false) {
	     	$errors[]="File not allowed, use a ".$xtn." file";
	  	}
		if($file_size > 10000000){
		    $errors[].='Image should not be larger than 10 MB, Current file is '.round($file_size/1000000).' MB';
		}
		// Crop and compress the image
		if (in_array($file_ext,$expensions) && empty($errors)==true) {        
			// Create a new ImageResize object
     		$image = new \Gumlet\ImageResize($file_tmp);
     		$t = null;
			if ($type == 1) {
			  	// Upload The site cover
			  	$new_image = 'profile_city.'.$file_ext; 
		        $image->crop(1600, 1066);
		        $xy = 'cover';
		        $t = 1;
			} elseif ($type == 2) {
			  	// Upload The site logo
			  	$new_image = 'logos.'.$file_ext; 
		        $image->crop(700, 132);
		        $xy = 'logo';
		        $t = 1;
			} elseif ($type == 3) {
			  	// Upload The site favicon
			  	$new_image = 'favicons.'.$file_ext; 
		        $image->crop(32, 32);
		        $xy = 'favicon';
		        $t = 1;
			} elseif ($type == 4 || $type == 5 || $type == 6) {
				$slide = $type == 4 ? 1 : ($type == 5 ? 2 : 3);
			  	// Upload The site Slides
			  	$new_image = 'slide-'.$slide.'.'.$file_ext; 
		        $image->crop(1450, 750);
		        $image->save(__DIR__."../../uploads/sites/slides/".$new_image);
		        $sql = sprintf("UPDATE ".TABLE_WELCOME." SET `slide_%s` = '%s'", $slide, $new_image);
		        dbProcessor($sql, 0, 1);
		        return successMessage('Successfully updated');
			}
			if ($t == 1) {
				$image->save(__DIR__."../../".$CONF['template_url']."/img/".$new_image);
		        $sql = sprintf("UPDATE ".TABLE_WELCOME." SET `%s` = '%s'", $xy, $new_image);
		        dbProcessor($sql, 0, 1);
		        return successMessage('Successfully updated');
			} 
		} else {                                  
	        return errorMessage($errors[0]);  
	    } 
	} 

	/**
	* Update the site introductory texts
	*/
	function update_welcome() {
		$sql = sprintf("UPDATE ".TABLE_WELCOME." SET `intro` = '%s', `intro_desc` = '%s', `uses_one` = '%s',
			`uses_one_desc` = '%s', `uses_two` = '%s', `uses_two_desc` = '%s', `uses_three` = '%s',
			`uses_three_desc` = '%s', `uses_four` = '%s', `uses_four_desc` = '%s', `carousel_one` = '%s',
			`carousel_one_sub` = '%s', `carousel_one_desc` = '%s', `carousel_two` = '%s',
			`carousel_two_sub` = '%s', `carousel_two_desc` = '%s', `carousel_three` = '%s',
			`carousel_three_sub` = '%s', `carousel_three_desc` = '%s'", db_prepare_input($_POST['intro_title']),
			db_prepare_input($_POST['intro_desc']), db_prepare_input($_POST['uses_one']),
			db_prepare_input($_POST['uses_one_desc']), db_prepare_input($_POST['uses_two']), 
			db_prepare_input($_POST['uses_two_desc']), db_prepare_input($_POST['uses_three']),
			db_prepare_input($_POST['uses_three_desc']), db_prepare_input($_POST['uses_four']),
			db_prepare_input($_POST['uses_four_desc']), db_prepare_input($_POST['carousel_one']), 
			db_prepare_input($_POST['carousel_one_sub']), db_prepare_input($_POST['carousel_one_desc']),
			db_prepare_input($_POST['carousel_two']), db_prepare_input($_POST['carousel_two_sub']), 
			db_prepare_input($_POST['carousel_two_desc']), db_prepare_input($_POST['carousel_three']),
			db_prepare_input($_POST['carousel_three_sub']), db_prepare_input($_POST['carousel_three_desc']));
		$result = dbProcessor($sql, 0, 1);
		if ($result == 1) {
			return successMessage('Successfully updated');
		} else {
			return infoMessage($result);
		}
	}

	/**
	* List all available languages
	*/
	function list_languages($type) {
		global $CONF, $LANG, $settings;
		
		if ($type == 0) {
			$languages = scandir('./languages/');
			
			$LANGS = $LANG;
			$by = $LANG['by'];
			$default = $LANG['default'];
			$make = $LANG['make_default'];

			$sort = '';
			foreach($languages as $language) {
				if($language != '.' && $language != '..' && substr($language, -4, 4) == '.php') {
					$language = substr($language, 0, -4);
					$system_languages[] = $language;
					
					include('./languages/'.$language.'.php');
					
					if($settings['language'] == $language) {
						$state = '<a class="btn btn-primary btn-sm waves-effect active">'.$default.'</a>';
					} else {
						$state = '<a class="btn btn-primary btn-sm waves-effect" href="'.$CONF['url'].'/index.php?a=settings&b=languages&language='.$language.'">'.$make.'</a>';
					}
					
					$sort .= '<div class="p-1">
								'.$state.'
								<div>
									<div>
										<strong><a href="'.$url.'" target="_blank">'.$name.'</a></strong>
									</div>
									<div>
										'.$by.': <a href="'.$url.'" target="_blank">'.$author.'</a>
									</div>
								</div>
							</div>';
				}
			}
			
			$LANG = $LANGS;
			return array($system_languages, $sort);
		} else {
			$sql = sprintf("UPDATE " . TABLE_SETTINGS . " SET `language` = '%s'", $this->language); 
        	return dbProcessor($sql, 0, 1);
		}
	}

	/**
	* Manage the sites language settings
	*/
	function getLanguage($url, $ln = null, $type = null) {
		global $settings;
		// Type 1: Output the available languages
		
		// Define the languages folder
		$lang_folder = __DIR__ .'/../languages/';
		
		// Open the languages folder
		if($handle = opendir($lang_folder)) {
			// Read the files (this is the correct way of reading the folder)
			while(false !== ($entry = readdir($handle))) {
				// Excluse the . and .. paths and select only .php files
				if($entry != '.' && $entry != '..' && substr($entry, -4, 4) == '.php') {
					$name = pathinfo($entry);
					$languages[] = $name['filename'];
				}
			}
			closedir($handle);
		}
		
		if($type == 1) {
			// Add to array the available languages
	        $available = '';
			foreach($languages as $lang) {
				// The path to be parsed
				$path = pathinfo($lang);
				
				// Add the filename into $available array
				$available .= '<span><a href="'.$url.'/index.php?lang='.$path['filename'].'">'.ucfirst(mb_strtolower($path['filename'])).'</a></span>';
			}
			return $available;
		} else {
			// If get is set, set the cookie and stuff
			$lang = $settings['language']; // Default Language
			
			if(isset($_GET['lang'])) {
				if(in_array($_GET['lang'], $languages)) {
					$lang = $_GET['lang'];
					setcookie('lang', $lang, time() + (10 * 365 * 24 * 60 * 60), COOKIE_PATH); // Expire in one month
				} else {
					setcookie('lang', $lang, time() + (10 * 365 * 24 * 60 * 60), COOKIE_PATH); // Expire in one month
				}
			} elseif(isset($_COOKIE['lang'])) {
				if(in_array($_COOKIE['lang'], $languages)) {
					$lang = $_COOKIE['lang'];
				}
			} else {
				setcookie('lang', $lang, time() + (10 * 365 * 24 * 60 * 60), COOKIE_PATH); // Expire in one month
			}

			// If the language file doens't exist, fall back to an existent language file
			if(!file_exists($lang_folder.$lang.'.php')) {
				$lang = $languages[0];
			}
			return $lang_folder.$lang.'.php';
		}
	} 

	/**
	* Get and show the referrals system
	*/
	function referrals($type, $referrer='', $referral='') {
		// type 0: Create referrals
		// type 1: Fetch referrals
		// type 2: Fetch referrer
		global $CONF, $LANG, $settings, $user;
		$us = new userCallback;

		// Fetch data for referrer and referred user
		$r_data = $us->userData(db_prepare_input($referrer)); //Referrers Data
		$data = $us->userData(db_prepare_input($referral)); //Users data

		$date = date("Y-m-d H:i:s");
		if ($type == 0) {
			$sql = sprintf("INSERT INTO " . TABLE_REFER . " (`referrer`, `user`, `date`) VALUES ('%s', '%s', '%s')", 
			$r_data['id'], $data['id'], $date); 
			$return = dbProcessor($sql, 0, 1);
		} elseif ($type == 1) {
        	$sql = sprintf("SELECT `referrer`, `user`, `username`, `fname`, `lname`, `role` FROM " . TABLE_USERS 
        		. " AS users LEFT JOIN " . TABLE_REFER . " AS `referral` ON `users`.`id` = `referral`.`user`" 
        		. " WHERE referrer = '%s'", $user['id']);
        	$return = dbProcessor($sql, 1);
		} elseif ($type == 2 && isset($data['id'])) {
        	$sql = sprintf("SELECT * FROM " . TABLE_REFER . " WHERE user = '%s'", $data['id']);
        	$return = dbProcessor($sql, 1);
		} 
		// Return single data if fetching referrer
		if ($type == 2) {
			return isset($return) ? $return[0] : '';
		} else {
			return isset($return) ? $return : '';
		}
	}

	/**
	 * Do a Search query
	 */
	function searchEngine($q, $x=0) {
		// Search users and contests
		$filter = isset($this->filters) ? $this->filters : null;
		$results = array();
		$users = $contest = $post = array();
		// Show the regular results
 
		$limit = isset($this->limit) ? sprintf("LIMIT %s, %s", $this->offset, $this->limit) : '';

		if (empty($filter)) {
			if (isset($this->tags)) {
				$u_tags = sprintf("(`lovesto` LIKE '%s')", '%'.db_prepare_input($q).'%');
				$c_tags = sprintf("(`tags` LIKE '%s')", '%'.db_prepare_input($q).'%');
			} else {
				$u_tags = sprintf("(`username` LIKE '%s' OR concat_ws(' ', `fname`, `lname`) LIKE '%s')", '%'.db_prepare_input($q).'%', '%'.db_prepare_input($q).'%');
				$c_tags = sprintf("(`title` LIKE '%s' OR `creator` LIKE '%s')", '%'.db_prepare_input($q).'%', '%'.db_prepare_input($q).'%');
			}
	 
			if ($x == 0) {
				$sql = sprintf("SELECT id AS uid,username,fname,lname,photo,intro,cover,role FROM ".TABLE_USERS." WHERE %s %s", $u_tags, $limit); 
				$users = dbProcessor($sql, 1);
				$sql = sprintf("SELECT id AS cid,title,safelink,cover,creator,intro FROM ".TABLE_CONTEST." WHERE %s %s", $c_tags, $limit); 
				$contest = dbProcessor($sql, 1);
			}
		// If a filter is set change the query
		} elseif ($filter == 'users' || $filter == 'gender') {
			// Find only users
			if (isset($this->tags)) {echo $filter;
				$u_tags = sprintf("(`lovesto` LIKE '%s')", '%'.db_prepare_input($q).'%'); 
			} elseif ($filter == 'gender') {
				$u_tags = sprintf("(`gender` LIKE '%s')", '%'.db_prepare_input($q).'%'); 
			} else {
				$u_tags = sprintf("(`username` LIKE '%s' OR concat_ws(' ', `fname`, `lname`) LIKE '%s')", '%'.db_prepare_input($q).'%', '%'.db_prepare_input($q).'%'); 
			}
			$sql = sprintf("SELECT id AS uid,username,fname,lname,photo,intro,cover,role FROM ".TABLE_CONTEST." WHERE %s %s", $u_tags, $limit);
			$users = dbProcessor($sql, 1);
		} elseif ($filter == 'contests') {
			// Find only contests
			if (isset($this->tags)) {
				$c_tags = sprintf("(`tags` LIKE '%s')", '%'.db_prepare_input($q).'%'); 
			} else {
				$c_tags = sprintf("(`title` LIKE '%s' OR `creator` LIKE '%s')", '%'.db_prepare_input($q).'%', '%'.db_prepare_input($q).'%'); 
			}
			$sql = sprintf("SELECT id AS cid,title,safelink,cover,creator,intro FROM ".TABLE_CONTEST." WHERE %s %s", $c_tags, $limit); 
			$contest = dbProcessor($sql, 1);
		} elseif ($filter == 'contest_type') {
			// Find only contests by type 
			$sql = sprintf("SELECT id AS cid,title,safelink,cover,creator,intro FROM ".TABLE_CONTEST." WHERE (`type` LIKE '%s') %s", '%'.db_prepare_input($q).'%', $limit); 
			$contest = dbProcessor($sql, 1);
		} elseif ($filter == 'country') {
			// Filter the results by country 	 
			$sql = sprintf("SELECT id AS uid,username,fname,lname,photo,intro,cover,role FROM ".TABLE_USERS." WHERE (`country` LIKE '%s') %s", '%'.db_prepare_input($q).'%', $limit); 
			$users = dbProcessor($sql, 1);
			$sql = sprintf("SELECT id AS cid,title,safelink,cover,creator,intro FROM ".TABLE_CONTEST." WHERE (`country` LIKE '%s') %s", '%'.db_prepare_input($q).'%', $limit); 
			$contest = dbProcessor($sql, 1);  
		} elseif ($filter == 'posts') {
			$sql = sprintf("SELECT user_id,text,share_id,pid FROM ".TABLE_TIMELINE." WHERE (`text` LIKE '%s') %s", '%'.db_prepare_input($q).'%', $limit);
			$post = dbProcessor($sql, 1);
		}

		$results['users'] = $users;
		$results['contests'] = $contest;
		$results['posts'] = $post;

		return $results;
	}

	/**
	 * Fetch cities, State and countries
	 */
	function fetch_locale($type=0) {
		if ($type == 0) {
			$sql = "SELECT * FROM ".TABLE_COUNTRIES; 
		} elseif ($type == 1) {
			$country = isset($this->country) ? $this->country : '';
			$sql = sprintf("SELECT * FROM ".TABLE_STATES." WHERE `country_id`=%s", db_prepare_input($country)); 
		} elseif ($type == 2) {
			$state = isset($this->state) ? $this->state : '';
			$sql = sprintf("SELECT * FROM ".TABLE_CITIES." WHERE `state_id`=%s", db_prepare_input($state)); 
		} elseif ($type == 3) {
			$state = isset($this->state) ? $this->state : '';
			$sql = sprintf("SELECT id FROM ".TABLE_STATES." WHERE `name`= '%s'", db_prepare_input($state));
		}
		$list = dbProcessor($sql, 1);
		return $list;
	}
}


/**
 * This class contains frequent action functions
 */
class actions {
	
	/**
	 * Manage blocking actions and blocked users
	 */
	function manageBlock($id, $type = null, $x = 0, $y=0) {
		// Type 0: Show the block button
		// Type 1: Block or Unblock a user
		global $LANG, $user, $userApp;

		$userApp->user_id = $id;
		$u = $userApp->userData(NULL, 1)[0];
		$p = $userApp->collectUserName(null, 0, $id);
		
		// If the username does not exist, return nothing
		if(empty($u)) {
			return false;
		} else {
			// Check if this user was blocked
			if ($y) {
				$sql = sprintf("SELECT * FROM ".TABLE_BLOCK." WHERE `user_id` = '%s' AND `by` = '%s'", $user['id'], db_prepare_input($u['id']));
			} else {
				$sql = sprintf("SELECT * FROM ".TABLE_BLOCK." WHERE `user_id` = '%s' AND `by` = '%s'", db_prepare_input($u['id']), $user['id']);
			}
			
			$st = dbProcessor($sql, 1);
			$st = count($st) > 0 ? 1 : 0;
			
			// Block or unblock
			if($type) {
				// If there is a block, unblock
				if($st) {
					// Remove the block
					$sql = sprintf("DELETE FROM ".TABLE_BLOCK." WHERE `user_id` = '%s' AND `by` = '%s'", db_prepare_input($u['id']), $user['id']); 
					$status = 0; 
				} else {
					// unblock
					$sql = sprintf("INSERT INTO ".TABLE_BLOCK." (`user_id`, `by`) VALUES ('%s', '%s')", db_prepare_input($u['id']), $user['id']); 
					$status = 1;
				}
				$action = dbProcessor($sql, 0, 1);
				$status = $status;
			} else {
				$status = $st; 
			}
		} 

		// Set the icon
		$ban_i = '<i class="fa fa-ban text-danger px-1 hoverable rounded-circle"></i>';
		$unban_i = '<i class="fa fa-ban text-success px-1 hoverable rounded-circle"></i>'; 

		if(!$status) {
			// Show only icon
			$icon = '<a onclick="blockAction('.$id.', 1, '.$x.')" data-toggle="tooltip" data-placement="right" title="'.$LANG['block'].' '.$p['name'].'" id="block_">'.$ban_i.'</a>';
			// Show only the link
			$link = '<a class="text-info" onclick="blockAction('.$id.', 1, '.$x.')" data-toggle="tooltip" data-placement="right" title="'.$LANG['block'].' '.$p['name'].'" id="block_">'.$LANG['block'].'</a>';
			// Show link and icon
			$link_icon = '<a class="text-info" onclick="blockAction('.$id.', 1, '.$x.')" data-toggle="tooltip" data-placement="right" title="'.$LANG['block'].' '.$p['name'].'" id="block_">'.$ban_i.$LANG['block'].'</a>';
		} else {
			// Show only icon
			$icon = '<a onclick="blockAction('.$id.', 1 '.$x.')" data-toggle="tooltip" data-placement="right" title="'.$LANG['unblock'].' '.$p['name'].'" id="block_">'.$unban_i.'</a>';
			// Show only the link
			$link = '<a class="text-info" onclick="blockAction('.$id.', 1, '.$x.')" data-toggle="tooltip" data-placement="right" title="'.$LANG['unblock'].' '.$p['name'].'" id="block_">'.$LANG['unblock'].'</a>';
			// Show link and icon
			$link_icon = '<a class="text-info" onclick="blockAction('.$id.', 1, '.$x.')" data-toggle="tooltip" data-placement="right" title="'.$LANG['unblock'].' '.$p['name'].'" id="block_">'.$unban_i.$LANG['unblock'].'</a>';
		}
		return array('icon' => $icon, 'link' => $link, 'link_icon' => $link_icon, 'status' => $status);		
	}

	/**
	 * Create click-able links from message
	 */	
	function decodeMessage($message, $x=0) { 
		global $LANG, $CONF;

		// Decode the links
		$extractUrl = preg_replace_callback('/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))/', "decodeLink", $message);
		
		$y = $x==1 ? 'warning' : 'info';
		// Decode link from #hashtags and @mentions
		$extractMessage = preg_replace(array('/(^|[^a-z0-9_\/])@([a-z0-9_]+)/i', '/(^|[^a-z0-9_\/])#(\w+)/u'), array('$1<a class="text-'.$y.'" href="'.permalink($CONF['url'].'/index.php?a=profile&u=$2').'" rel="loadpage">@$2</a>', '$1<a class="text-'.$y.'" href="'.permalink($CONF['url'].'/index.php?a=search&query='.urlencode('#').'$2').'" rel="loadpage">#$2</a>'), $extractUrl);

		return $extractMessage;
	} 


}


/**
 * Social interaction class
 */
class social {
	function timelines($this_id, $type, $fetch_users=false) {
		global $CONF, $LANG, $settings, $user, $userApp;

		$cu = $this_id ? $userApp->collectUserName(null, 0, $this_id) : '';

		// Fetch all timeline posts
		if ($type == 0) {
			if ($user['id'] == $this_id) {
				// Fetch the shared post
				$fetch_users = ($fetch_users == true) ? sprintf("WHERE (timelines.user_id = '%s') OR (timelines.share_id = '%s')", $this_id, $this_id) : '';
			} else {
				// Fetch the users post
				$fetch_users = ($fetch_users == true) ? sprintf("WHERE timelines.user_id = '%s' AND timelines.share_id = 0", $this_id) : '';				
			}

			$y = isset($this->last) ? 'LIMIT 1' : '';
    		$sql = sprintf("SELECT * FROM " . TABLE_TIMELINE . " AS timelines LEFT JOIN " . TABLE_USERS . " AS `users` ON `timelines`.`user_id` = `users`.`id` %s ORDER BY date DESC %s", $fetch_users, $y);
    		return dbProcessor($sql, 1);

    	// see a particular post
		} elseif ($type == 1) {
    		$sql = sprintf("SELECT * FROM " . TABLE_TIMELINE . " AS timelines LEFT JOIN " . TABLE_USERS . " AS `users` ON `timelines`.`user_id` = `users`.`id` WHERE pid = '%s'", $this_id);  
    		return dbProcessor($sql, 1)[0];

    	// Create a new post
		} elseif ($type == 2) {
			$array = $this->array;
			extract($array);
			$post = empty($post_2) ? $post : $post_2;
			$location = $user['city'].', '.$user['state'].', '.$user['country'];
			$sql = sprintf("INSERT INTO " . TABLE_TIMELINE . " (`user_id`, `share_id`, `post_id`, `text`, `post_photo`, `location`, `privacy`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s')", $user_id, $share_id, $post_id, db_prepare_input($post), db_prepare_input($photo), $location, db_prepare_input($privacy)); 
			$r = dbProcessor($sql, 0, 1); 
			return $r==1 ? 'Success' : $r;
		}
	}


	/**
	* Create a follow and unfollow href link
	*/
	function follow_link($leader, $t=0) {
		// t 0: normal follow link
		// t 1: Modal follow link
		global $user, $LANG;
		$social = new social;
		$follower = $social->follow($leader, 1);

		// Is the link to show in a modal?
		$type = $t == 1 ? 'modal_' : '';

		if ($follower['leader_id'] == $leader) {
			$follow_link = '<a class="text-info" onclick="relate('.$leader.', 1)" id="'.$type.'follow_link_'.$leader.'"><i class="fa fa-user-times"></i> '.$LANG['unfollow'].'</a>';
		} else {
			$follow_link = '<a class="text-info" onclick="relate('.$leader.', 0)" id="'.$type.'follow_link_'.$leader.'"><i class="fa fa-user-plus"></i> '.$LANG['follow'].'</a>';
		}
		return $leader !== $user['id'] ? $follow_link : null;
	}


	/**
	* Timeline info on the side bar
	*/
	function timeline_info($username, $user_id='') {
		global $LANG, $user, $userApp, $CONF; 
		$data = $userApp->userData($username); 
		$collect = $userApp->collectUserName(null, 0, $data['id']);

		// Prepare the data
		$name = realName($data['username'], $data['fname'], $data['lname']);
		$user_profile = permalink($CONF['url'].'/index.php?a=profile&u='.$data['username']);
		$intro = rip_tags(stripslashes(myTruncate($data['intro'], 120)));
		$intro = isset($intro) ? '<div class="h7 mt-3">'.$intro.'</div>' : '';

		// Link to the follows page
		$followers_link = permalink($CONF['url'].'/index.php?a=followers&followers='.$data['id']);
		$following_link = permalink($CONF['url'].'/index.php?a=followers&following='.$data['id']);

		// Fetch user relationships
		$follower = $this->follow($data['id'], 1);
		$followers = $this->follow($data['id'], 2);
		$following = $this->follow($data['id'], 3);
		$count_followers = count($followers) > 0 ? count($followers) : 0;
		$count_following = count($following) > 0 ? count($following) : 0;

		// Online Status
		$online = $this->online_state($data['id']);

		// <a class="text-info" href="'.$collect['message'].'">

		// Show the follow link
		$follow_link = $this->follow_link($data['id'], 1);

		// Show followers thumbnail
		$followers_cap = $this->follow($data['id'], 2, 6);
		$following_cap = $this->follow($data['id'], 3, 6);
		$ftn = '';
		if ($followers_cap) {
			foreach ($followers_cap as $key) { 
				$_user = $userApp->collectUserName(null, 0, $key['follower_id']); 
				$onl = $this->online_state($_user['user_id']);   
				$ftn .= ' 
				<a href="'.$_user['profile'].'" data-toggle="tooltip" data-placement="bottom" title="@'.$_user['username'].'" class="follower_link">
					<img class="followers-thumbs rounded" id="profile-image" src="'.$CONF['url'].'/uploads/faces/'.$_user['photo'].'" alt="'.$_user['username'].'">'.$onl['icon'].'
				</a>';
			}
		}

		// Show following thumbnail
		$fntn = '';
		if ($following_cap) {
			foreach ($following_cap as $key) { 
				$_flwn = $userApp->collectUserName(null, 0, $key['leader_id']); 
				$onl = $this->online_state($_flwn['user_id']);   
				$fntn .= ' 
				<a href="'.$_flwn['profile'].'" data-toggle="tooltip" data-placement="bottom" title="@'.$_flwn['username'].'" class="following_link">
					<img class="followers-thumbs rounded" id="profile-image" src="'.$CONF['url'].'/uploads/faces/'.$_flwn['photo'].'" alt="'.$_flwn['username'].'">'.$onl['icon'].'
				</a>';
			}
		}

		// Show the users information
		$info = '
        <div class="card mb-2">
            <div class="card-body profile-card">
                <img class="float-right rounded w-25" id="profile-image" src="'.$CONF['url'].'/uploads/faces/'.$data['photo'].'" alt="'.$data['username'].'">
                <div class="h5"><a href="'.$user_profile.'">@'.ucfirst($data['username']).'</a>'.$online['icon'].'</div>
                <div class="h7 text-muted">'.$name.'</div>
                '.$follow_link.'
                '.$intro.'
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">
                    <div class="h6 text-muted"><a href="'.$followers_link.'">'.$LANG['followers'].'</a></div>
                    <div class="h5" id="followers_count_'.$data['id'].'">'.$count_followers.'</div>
                    '.$ftn.'
                </li>
                <li class="list-group-item">
                    <div class="h6 text-muted"><a href="'.$following_link.'">'.$LANG['following'].'</a></div>
                    <div class="h5" id="following_count_'.$data['id'].'">'.$count_following.'</div>
                    '.$fntn.' 
                </li>
                <li class="list-group-item">
                	'.ucfirst($user['role']).'
                	<span class="float-right">
                		<a class="text-info" data-toggle="tooltip" data-placement="left" title="'.$LANG['send_message'].'" href="'.$collect['message'].'"> <i class="fa fa-envelope"></i></a>
                	</span>
                </li>
            </ul>
        </div>'; 
        return $_GET['a'] == 'timeline' || $_GET['a'] == 'followers'  || $_GET['a'] == 'gallery' ? $info : null;
	}


	/**
	* Process follow actions and requests
	*/
	function follow($user_id, $type='', $limit=null) {		
		// type 0: New Follow
		// type 1: Fetch this follower
		// type 2: Fetch followers
		// type 3: Fetch following
		// type 4: Followers List
		global $LANG, $user;

		$limit = $limit ? sprintf("ORDER BY date DESC LIMIT %s", $limit) : "ORDER BY date DESC";
		if ($type == 0) {
			$sql = sprintf("INSERT INTO " . TABLE_RELATE . " (`leader_id`, `follower_id`) VALUES ('%s', '%s')", $user_id, $user['id']); 
			$t = 0;
			$r = 'Success'; 
		} elseif ($type == 1) {
    		$sql = sprintf("SELECT * FROM " . TABLE_RELATE . " WHERE leader_id = '%s' AND follower_id = '%s'", $user_id, $user['id']);  
    		$t = 1; 
		} elseif ($type == 2 || $type == 3) {
			$x = $type == 2 ? 'leader_id' : 'follower_id';
    		$sql = sprintf("SELECT * FROM " . TABLE_RELATE . " WHERE %s = '%s' %s", $x, $user_id, $limit);  
    		$t = 1; 
		} elseif ($type == 4) {
			$sql = sprintf("SELECT leader_id FROM " . TABLE_RELATE . " WHERE follower_id = '%s'", $user_id);  
    		$t = dbProcessor($sql, 1); 
    		return $t;
		}
		$response = dbProcessor($sql, $t, $t==0?1:null); 
		return $t == 0 ? ($response == 1 ? $r : $response) : ($type == 1 ? $response[0] : $response);
	}


	/**
	* Fetch follows and followers
	*/
	function subscribers($user_id, $type=null, $string=null) {
		global $CONF, $LANG, $user, $userApp, $marxTime;
		$messaging = new messaging;
		// Type 0: All Followers
		// Type 1: Search Followers
		$timeNow = time();
		$user_id = $user_id == 0 ? $user['id'] : $user_id;
		$array = $this->follow($user_id, 4);
		$r = '';
		if (isset($array)) {
			foreach ($array as $key) {
				$r .= $key['leader_id'].',';
			}
		}

		preg_match('[\W]', substr($r,-1), $q); 
		$subs = $r ? rtrim($r, $q[0]) : '';
		if ($type == 0) {
			$sql = sprintf("SELECT * FROM ".TABLE_USERS." WHERE `id` IN (%s) ORDER BY `online` DESC", db_prepare_input($subs));
		} elseif ($type == 1) {
			if($string) {
				// Search followers
				$sql = sprintf("SELECT * FROM ".TABLE_USERS." WHERE (`username` LIKE '%s' OR concat_ws(' ', `fname`, `lname`) LIKE '%s') AND `id` IN (%s) ORDER BY `online` DESC", '%'.db_prepare_input($string).'%', '%'.db_prepare_input($string).'%', db_prepare_input($subs));
			} else {
				// Display current friends
				$sql = sprintf("SELECT * FROM ".TABLE_USERS." WHERE `id` IN (%s) ORDER BY `online` DESC", db_prepare_input($subs));
			}
		} else {
			// Show online followers
			$sql = sprintf("SELECT * FROM ".TABLE_USERS." WHERE `id` IN (%s) AND `online` > '%s'-'%s' ORDER BY `online` DESC", db_prepare_input($subs), $timeNow, $this->online_time);
		}
		
		$process = $subs ? dbProcessor($sql, 1) : array();

		$follow_list = '';
		if ($type == 0 || $type == 1) {
			if (isset($process)) {
				foreach ($process as $key) {
					$data = $userApp->collectUserName(null, 0, $key['id']);
					$last = $messaging->fetchMessages(5, $key['id'])[0];
					$active = isset($this->active) && $this->active == $key['id'] ? 'active_chat' : '';
 
					// Set icon to show online status
					if(($timeNow - $key['online']) > $this->onlineTime) {
						$icon = 'warning';
					} else {
						$icon = 'success';
					}
					$online = $this->online_state($key['id'], $this->onlineTime);

					$bold = $last['seen'] ? '' : ' class="font-weight-bold"';

					$follow_list .= '
					<a href="'.$data['message'].'" id="hoverable">
					  <div class="chat_list '.$active.'">
					    <div class="chat_people">
					      <div class="chat_img"> 
					        <img class="rounded-circle" src="'.$CONF['url'].'/uploads/faces/'.$data['photo'].'" alt="sunil"> 
					      </div>
					      <div class="chat_ib">
					        <h5>'.$data['fullname'].' 
					        	'.$online['icon'].'
					        	<span class="chat_date">'.$marxTime->timeAgo(strtotime($last['date']), 2).'</span></h5>
					        <p'.$bold.'>'.myTruncate($last['message'], 80, ' ', '').'</p>
					      </div>
					    </div>
					  </div>
					</a>';
				} 				
			} else {
					$follow_list = '
					<div class="chat_list">
						<div class="chat_people">
							<div class="chat_ib"><h5>'.$LANG['nobody_here'].'</h5></div>
						</div>
					</div>';
				}

		}
		return $follow_list;
	}


	/**
	* Check if the user is online
	*/
	function online_state($user, $gettime=null, $type=0) {
		global $LANG, $settings, $userApp, $marxTime;

		$timeNow = time();
		$userApp->user_id = $user;
		$data = $userApp->userData(NULL, 1)[0];
		$online_time = $gettime ? $gettime : $settings['online_time'];

		// Set icon to show online status
		if(($timeNow - $data['online']) > $online_time) {
			$info = $LANG['offline'];
			$icon = '<i class="small-icon fa fa-circle text-warning" data-toggle="tooltip" data-placement="right" data-title="'.$info.'"></i>';
			$last = $LANG['last_seen'].' '.$marxTime->timeAgo($data['online'], 1);
			$status = 0;
		} else {
			$info = $LANG['online'];
			$icon = '<i class="small-icon fa fa-circle text-success" data-toggle="tooltip" data-placement="right" data-title="'.$info.'"></i>';
			$last = $LANG['last_seen'].' '.$LANG['just_now'];
			$status = 1;
		}
		if ($type == 0) {
			return array('icon' => $icon, 'text' => $info, 'last_seen' => $last);
		} else {
			if ($status) {
				return $status;
			} else {
				$sql = sprintf("UPDATE " .TABLE_USERS. " SET `online` = '%s' WHERE `id` = '%s'", $timeNow, db_prepare_input($user));
				dbProcessor($sql, 0, 1);				
			}			
		}
	}


	/**
	* Process Like action and request
	*/
	function like($owner_id, $type='') {	
		// type 0: New Like
		// type 1: Fetch this Likes
		// type 2: Fetch likes
		// type 3: Fetch following
		// type 4: Unfollow
		global $LANG, $user;

		if ($type == 0) {
			$sql = sprintf("INSERT INTO " . TABLE_LIKE . " (`user_id`, `owner_id`, `content_id`, `content_type`) VALUES ('%s', '%s', '%s', '%s')", $user['id'], $owner_id, $this->content, $this->content_type); 
			$t = 0;
			$r = 'Success'; 
		} elseif ($type == 1) {
			$ctype = isset($this->content_type) ? sprintf("AND content_type = '%s'", $this->content_type) : '';
    		$sql = sprintf("SELECT * FROM " . TABLE_LIKE . " WHERE content_id = '%s' AND user_id = '%s' %s", $this->content, $user['id'], $ctype);
    		$t = 1; 
		} elseif ($type == 2) {
			$limit = isset($this->limit) ? sprintf("ORDER BY date DESC LIMIT %s", $this->limit) : 'ORDER BY date DESC';
			$ctype = isset($this->content_type) ? sprintf("AND content_type = '%s'", $this->content_type) : '';
    		$sql = sprintf("SELECT * FROM " . TABLE_LIKE . " WHERE content_id = '%s' %s %s", $this->content, $ctype, $limit);  
    		$t = 1; 
		} elseif ($type == 3) {
    		$sql = sprintf("SELECT * FROM " . TABLE_RELATE . " WHERE follower_id = '%s'", $user_id);  
    		$t = 1; 
		}
		$response = dbProcessor($sql, $t, $t==0?1:null);
		return $t == 0 ? ($response == 1 ? $r : $response) : ($type == 1 ? $response[0] : $response);
	}


	/**
	* Small profile cards
	*/
	function profile_card($type, $id='', $username='') {
		global $LANG, $userApp;
		$social = new social;
		// Type 0: Profile
		// Type 1: Contest
		$follower = $this->follow($id, 1);
		$followers = $this->follow($id, 2);
		$following = $this->follow($id, 3);
		$follow_link = $this->follow_link($id, 1); 

		if ($type == 0) {
			$_user = $userApp->collectUserName(null, 0, $id);
		}
		$card = ' 
		<div class="card news-card pass-info-card rounded"> 
		  <div class="card-body pass-card-header heavy-rain-gradient">
		    <div class="content light-blue-text"> 
		      <img src="'.$CONF['url'].'/uploads/faces/'.$_user['photo'].'" class="rounded avatar-img z-depth-1-half" id="info-card-image">
		      <a href="'.$_user['profile'].'">'.$_user['fullname'].'</a>
		    </div>
		  </div>  
		  <div class="card-body pass-card-body"> 
		    <div class="social-meta px-3">
		      <p>'.$LANG['from'].' '.$_user['address'].' </p> 
		      <span class="blue-grey-text font-weight-bold" id="modal_following_count_'.$id.'">'.count($following).' Following</span>
		      <p class="blue-grey-text font-weight-bold" id="modal_followers_count_'.$id.'">'.count($followers).' Followers</p> 
		    </div>
		    <hr> 
		    <div class="pass-card-body text-center font-weight-bold text-info">
		      '.$follow_link.'
		    </div>
		  </div> 
		</div>';
		return $card;
	}


	/**
	* Follow cards shown on follow page
	*/
	function follow_cards($type, $user_id='') {
		global $userApp, $CONF;
		// type 0: followers
		// type 1: following
		global $LANG, $user, $userApp;
		$social = new social;

		$follower = $this->follow($user_id, 1);
		$follows = $type==0 ? $followers = $this->follow($user_id, 2) : $following = $this->follow($user_id, 3);
		
		$card = '';
		if ($follows) {
			foreach ($follows as $key) {
				if ($type==0) {
					$_user = $userApp->collectUserName(null, 0, $key['follower_id']);
					$follow_link = $this->follow_link($key['follower_id'], 1); 
				} else {
					$_user = $userApp->collectUserName(null, 0, $key['leader_id']);
					$follow_link = $this->follow_link($key['leader_id'], 1); 
				}

				$u = $userApp->userData($_user['username']);

				$collect = $userApp->collectUserName(null, 0, $u['id']);
				$message_link = $u['id'] == $user['id'] ? '' : '<a class="text-info px-2" data-toggle="tooltip" data-placement="left" title="'.$LANG['send_message'].'" href="'.$collect['message'].'"> <i class="fa fa-envelope"></i></a>';
				
				$online = $this->online_state($u['id']);

				$class = $u['role'] == 'agency' ? 'blue' : ($u['role'] == 'contestant' ? 'young-passion' : 'aqua');

				$card .= ' 
				<div class="col-6 col-md-4">
				<div class="card news-card follow-cards rounded"> 
				  <div class="card-body follow-cards-header '.$class.'-gradient">
				    <div class="content text-white">  
				      <a class="white-text" href="'.$_user['profile'].'">'.$_user['fullname'].'</a>
				    </div>
				  </div>  
				  <a href="'.$_user['profile'].'">
					<img class="card-img-top" id="follow-cards-image" src="'.$CONF['url'].'/uploads/faces/'.$_user['photo'].'" alt="'.$_user['username'].'">
				  </a>
				  
				  <div class="card-body follow-cards-body"> 
				    <div class="social-meta px-3">
				      <p>'.$_user['address'].' '.$online['icon'].'</p> 
				       
				    </div>
				    <hr> 
				    <div class="follow-cards-body text-center font-weight-bold text-info">
				      '.$follow_link.'
				      '.$message_link.'
				    </div>
				  </div> 
				</div></div>'; 
			}
		} else {
			// if there is nothinf to show
		    $card = '
		    <div class="col-lg-12" id="photo">
		      <div class="cardbox shadow-md bg-light"> 
		        <div class="cardbox-item h1 peach-gradient text-center text-white p-4">
		          '.$LANG['nothing_to_show'].'
		        </div> 
		      </div>
		    </div>'; 
		}


		return $card;
	}

	/**
	 * send sms text with twillio
	 */
	function sendSMS($text, $phone, $test=0) {
	    global $settings;
	    $success = true;
	    $fail = false;
	    if ($test==1) {
	    	$phone = $settings['site_phone'];
	    	$text = 'Test SMS from '.$settings['site_name'];
	    	$return = 'Test SMS successfully sent';
	    	$fail = 'Failed to send Test SMS';
	    }
	    $client = new Twilio\Rest\Client($settings['twilio_sid'], $settings['twilio_token']);
	    $message = $client->account->messages->create(
	        $phone,
	        array(
	            'from' => $settings['twilio_phone'],
	            'body' => $text
	        )
	    );
	    if($message->sid) {
	    	return $success;
	    }
	    return $fail;
	}

	/**
	* Send notifications to users
	*/
	function notifier($sender, $receiver, $x, $master=0, $mail=0) {
		//notif_array $type: 0,2 = User activity; 1,3 = Contest Activity; 4 = System Notifications
		global $LANG, $CONF, $settings, $userApp;
		$noti = new msgNotif;
		$site_class = new siteClass;

		$sender = db_prepare_input($sender);
		$receiver = db_prepare_input($receiver);
		$prem_check = $userApp->premiumStatus($receiver, 1);
		$notification = false;

		// Sender info
		if (isset($this->type)) {
			$sender_data = $userApp->collectUserName(null, 1, $sender);
			$profile = '<a href="'.$sender_data['safelink'].'">%s</a>';	
		} else {
			$userApp->user_id = $sender;
			$s = $userApp->userData(NULL, 1)[0];
			$sender_data = $userApp->collectUserName(null, 0, $s['id']); 
			$profile = '<a href="'.$sender_data['profile'].'">%s</a>';			
		}

		// Receiver info
		$userApp->user_id = $receiver;
		$r = $userApp->userData(NULL, 1)[0];

		/* x 0: Repost or Share Notification; 
		 * x 1: Follow Notification; 
		 * x 2: Like Notification
		 */ 

		if ($x === 'x') {
			$x = 10;
		}

		if ($x == 0) {
			$post = '<a href="'.permalink($CONF['url'].'/index.php?a=timeline&u='.$s['username'].'&read='.$master).'" class="blue-grey-text">'.lcfirst($LANG['post']).'</a>';
			$message = sprintf($LANG['user_shared'], $sender_data['fullname'], $post);
			$type = 0;
			$mail = $settings['email_social'];
		} elseif ($x == 1) {
			$message = sprintf($LANG['user_followed'], $sender_data['fullname']);
			$type = 0;
			$mail = $settings['email_social'];
		} elseif ($x == 2) {
			$post = '<a href="'.permalink($CONF['url'].'/index.php?a=timeline&u='.$s['username'].'&read='.$master).'" class="blue-grey-text">'.lcfirst($LANG['post']).'</a>';
			$message = sprintf($LANG['user_likes'], $sender_data['fullname'], $post);
			$type = 0;
			$mail = $settings['email_social'];
		}
		// Check if a message was supplied
		$message = isset($this->message) ? $this->message : $message;
		$type = isset($this->type) ? $this->type : $type;

		// Send the site notification
		if (!isset($this->no_notify)) {
			// if ($sender === $receiver) {
				$notif_array = array($sender, $receiver, $message, $type, $master);
				$notification = $r['site_notifications'] ? $noti->notification(4, $notif_array) : '';
			// }
		}

		// Send an email if the user has enabled it and the system enables it too if needed
		if ($mail) {
			// Determine the email subject 
			$subject = rip_tags($message);
			$subject = isset($this->subject) ? $this->subject : $subject;

			// Restructure the message
			$message = isset($this->message) ? $message : $message.' on '.$settings['site_name'];

			// Determine the email content
		    $site_class->user_id = $r['id']; 
			$site_class->mailerDaemon($CONF['email'], $r['email'], $subject, $message); 
		}

		// Check if SMS are allowed then send and SMS
		if ($settings['sms']) {
			// Check if user has a valid phone number
			if ($r['phone']) {
				// Check if sms are reserved for premium
				if ($settings['sms_premium']) {
					// Check if premium is on to send sms
					if ($settings['premium']) {
						// Check if receiver has an active subscription
						if ($prem_check) {
							$this->sendSMS(rip_tags($message), $r['phone']);
						}
					} else {
						$this->sendSMS(rip_tags($message), $r['phone']);
					}
				} else {
					$this->sendSMS(rip_tags($message), $r['phone']);
				}
			}
		}
		
		return $notification;		
	}	
}


/**
 * Manage viewing and sending of messages
 */
class messaging {

	/**
	* Write or read messages from db
	*/
	function fetchMessages($type, $user_id, $chat_id=null, $start=null) {
		// type 0: Read
		// type 1: Check for new messages
		// type 2: Fetch new message
		// type 3: Fetch newly posted message
		// type 4: set the message status as read
		// type 5: fetch the last message
		//---------------------- 
		global $user, $settings;

		if($start == 0) {
			$start = '';
		} else { 
			$start = 'AND `messenger`.`cid` < \''.db_prepare_input($chat_id).'\'';
		}

		if ($type == 0) { 
			$sql = sprintf("SELECT * FROM " . TABLE_MESSAGE . " AS messenger, " . TABLE_USERS . " AS users WHERE (`messenger`.`sender` = '%s' AND `messenger`.`receiver` = '%s' AND `messenger`.`sender` = `users`.`id`) %s OR (`messenger`.`sender` = '%s' AND `messenger`.`receiver` = '%s' AND `messenger`.`sender` = `users`.`id`) %s ORDER BY `messenger`.`cid` DESC LIMIT %s", $user['id'], db_prepare_input($user_id), $start, db_prepare_input($user_id), $user['id'], $start, ($settings['per_messenger'] + 1));
			return dbProcessor($sql, 1); 
		} elseif ($type == 1) {
			$sql = sprintf("SELECT * FROM " . TABLE_MESSAGE . " AS messenger WHERE `sender` = '%s' AND `receiver` = '%s' AND `seen` = '0'", db_prepare_input($user_id), $user['id']);
			$process = dbProcessor($sql, 1);
			if ($process) {
				return $this->fetchMessages(2, $user_id);
			}
			return false;
		} elseif ($type == 2) {
			$sql = sprintf("SELECT * FROM " . TABLE_MESSAGE . " AS messenger, " . TABLE_USERS . " AS users WHERE `sender` = '%s' AND `receiver` = '%s' AND `seen` = '0' AND `messenger`.`sender` = `users`.`id` ORDER BY `messenger`.`cid` DESC", db_prepare_input($user_id), $user['id']); 
			return dbProcessor($sql, 1); 
		} elseif ($type == 3) {
			$sql = sprintf("SELECT * FROM " . TABLE_MESSAGE . " AS messenger, " . TABLE_USERS . " AS users WHERE (`messenger`.`sender` = '%s' AND `messenger`.`receiver` = '%s' AND `messenger`.`sender` = `users`.`id`) ORDER BY `messenger`.`cid` DESC LIMIT 1", $user['id'], db_prepare_input($user_id));
			return dbProcessor($sql, 1);
		} elseif($type == 4) { 
			$sql = sprintf("UPDATE " . TABLE_MESSAGE . " SET `seen` = '1', `date` = `date` WHERE `sender` = '%s' AND `receiver` = '%s' AND `seen` = '0'", db_prepare_input($user_id), $user['id']);
			dbProcessor($sql, 0);
		} elseif ($type == 5) { 
			$sql = sprintf("SELECT * FROM " . TABLE_MESSAGE . " AS messenger, " . TABLE_USERS . " AS users WHERE (`messenger`.`sender` = '%s' AND `messenger`.`receiver` = '%s' AND `messenger`.`sender` = `users`.`id`) OR (`messenger`.`sender` = '%s' AND `messenger`.`receiver` = '%s' AND `messenger`.`sender` = `users`.`id`) ORDER BY `messenger`.`cid` DESC LIMIT 1", $user['id'], db_prepare_input($user_id), db_prepare_input($user_id), $user['id']);
			return dbProcessor($sql, 1); 
		}		
	}

	/**
	* Display the messages
	*/
	function messenger($type, $user_id) {
		global $CONF, $LANG, $user, $settings, $marxTime, $userApp;
		// Type 0: all messages
		// Type 1: Fetch just posted message
		// Type 2: Fetch new message
		// Type 3: Fetch more messages

		$action = new actions;

		$more = $readmore = ''; 
		$read_msg = '';

		if ($type == 0) {
			$messages = $this->fetchMessages(0, $user_id);
		} elseif ($type == 1) {
			$messages = $this->fetchMessages(3, $user_id);
		} elseif ($type == 2) {
			$messages = $this->fetchMessages(1, $user_id);
		} elseif ($type == 3) {
			$messages = $this->fetchMessages(0, $user_id, $this->chat_id, $this->start);
		} 
		
		if (empty($messages)) {
			return false;
		}
		// Update the message status to seen
		if($type !== 1) {
			$this->fetchMessages(4, $user_id);
		}

		$messages = array_reverse($messages);

		if(array_key_exists($settings['per_messenger'], $messages)) {
			$readmore = 1;
			
			// Unset the first array element used to predict if the Load More Messages should be shown
			unset($messages[0]);
		}
  
		foreach ($messages as $cmsg) {
			// Get the user's profile data
			$profile = $userApp->collectUserName(null, 0, $cmsg['id']);
			$clean_message = rip_tags($cmsg['message']);
			$clean_message = $action->decodeMessage($clean_message);

			if ($cmsg['username'] == $user['username']) {
				$delete = '<a onclick="delete_the('.$cmsg['cid'].', 10)" data-toggle="tooltip" data-placement="left" title="'.$LANG['delete'].'"><i class="fa fa-trash text-danger px-1 hoverable rounded-circle"></i></a>';
				$seen = $cmsg['seen'] == 1 ? $LANG['seen'] : '';
				$read_msg .= '
			    <div class="outgoing_msg" id="message_'.$cmsg['cid'].'">
			      <div class="sent_msg">
			        <p>'.$clean_message.'</p>
			        <span class="time_date">
			        	'.$marxTime->timeAgo(strtotime($cmsg['date']), 1).'
			        	<span class="teal-text">'.$seen.'</span>
			        	'.$delete.'
			        </span>
			      </div>
			    </div>';				
			} else { 
				$read_msg .= '
			    <div class="incoming_msg">
			      <a href="'.$profile['profile'].'" data-toggle="tooltip" data-placement="bottom" title="@'.$profile['fullname'].'">
			        <div class="incoming_msg_img"> 
			      	  <img class="rounded-circle" src="'.$CONF['url'].'/uploads/faces/'.$cmsg['photo'].'" alt="'.$cmsg['username'].'"> 
			        </div>
			      </a>
			      <div class="received_msg">
			        <div class="received_withd_msg">
			          <p>'.$clean_message.'</p>
			          <span class="time_date">
			          	'.$marxTime->timeAgo(strtotime($cmsg['date']), 1).'
			          </span>
			        </div>
			      </div>
			    </div>';				
			}
		}
		if($readmore) {
			$more = '<div class="more-messages text-center grey-text"><a onclick="loadMessages('.htmlentities($user_id, ENT_QUOTES).', \'\', '.$messages[1]['cid'].', 1)">'.$LANG['show_more'].'...</a></div>';
		}	 

		return $more.$read_msg;
	}

	/**
	* Display the messages and show send new message input
	*/
	function messenger_master($user_id, $user) {
		global $CONF, $LANG, $userApp;
		$social = new social;
		$act = new actions;

		$fetch_msg = $this->messenger(0, $user_id);
		// Collect user data
		$profile = $userApp->collectUserName(null, 0, $user_id);
		// Show the user's online status
		$online = $social->online_state($user_id);
		// Show the follow link
		$follow = $social->follow_link($user_id);
		// Check and block chat follower
		$blocked = $act->manageBlock($user_id, 0, 2);
		// Check if logged user is bloked
		$you_blocked = $act->manageBlock($user_id, 0, 2, 1);
		// Fetch messages
		$fetch_messages = $this->messenger(0, $user_id);
		$fetch_messages = $fetch_messages ? $fetch_messages : $this->messageError($LANG['too_quiet']);

		// Show the input if the user is not blocked
		if ($you_blocked['status']) {
			$input = '
			<h5 class="border blue-grey-border rounded m-2 p-3 blue-grey-text text-center grey lighten-5"> 
			  '.$LANG['cant_reply'].'
			</h5>';
		} else {
			$input = '
			<div class="form-group"> 
			  <textarea class="form-control" id="write_msg" rows="3" placeholder="'.$LANG['type_message'].'..."></textarea>
			</div>';
		}

        $messages = '
        <div class="msg_history p-2" id="messages_read">
          '.$fetch_messages.'
        </div>
        <div id="loader"></div>
        <div class="p-2 border-top border-light chat-profile">
        	<img class="rounded" src="'.$CONF['url'].'/uploads/faces/'.$profile['photo'].'" alt="'.$profile['username'].'">
        	'.$online['icon'].'
        	<a href="'.$profile['profile'].'" class="px-1">'.$profile['fullname'].'</a>
        	'.$follow.'
        	'.$blocked['link_icon'].'
        	<span class="float-right">'.$online['last_seen'].'</span>
        </div>
        <div class="type_msg">
          <div class="input_msg_write">
          	<input type="hidden" value="'.$user_id.'" id="message-receiver" /> 
          	'.$input.' 
          </div>
        </div>';
        return $messages;	
	}


	/**
	* Add the message to DB
	*/
	function send_message($user_id, $message) {
		global $LANG, $user, $userApp;

		$userApp->user_id = $user_id;
		$data = $userApp->userData(NULL, 1)[0];

		if(strlen($message) > 300) {
			return $this->messageError($LANG['message_too_long']);
		} elseif($user_id == $user['id']) {
			return $this->messageError($LANG['message_self']);
		} elseif(!$data['username']) {
			return $this->messageError($LANG['user_not_exist']);
		}

		$message = rip_tags($message);
		$sql = sprintf("INSERT INTO " . TABLE_MESSAGE . " (`sender`, `receiver`, `message`, `seen`, `date`) VALUES ('%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)", $user['id'], db_prepare_input($user_id), db_prepare_input($message), 0);
		$process = dbProcessor($sql, 0, 1);
		if ($process == 1) {
			return $this->messenger(1, $user_id);
		}
	}	

	function messageError($error) {
		return '<div class="message-error">'.$error.'</div>';
	}	
}


/**
 * Rave Payment processing and validation class 
 */
class raveAPI {
	function Validate() {
		$ravemode = $this->ravemode;
		$query = $this->query;

		$data_string = json_encode($query);

	    $ch = curl_init('https://'.$ravemode.'/flwv3-pug/getpaidx/api/v2/verify');                                                                      
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                              
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	    $response = curl_exec($ch);

	    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	    $header = substr($response, 0, $header_size);
	    $body = substr($response, $header_size);

	    if (curl_error($ch)) {
			$error_msg = curl_error($ch);
		}
		if(isset($error_msg)) {
	    	return $error_msg;
		}
	    curl_close($ch);

	    return json_decode($response, true);	
	}

	/**
	* Change the user's premium plan
	*/
	function promote_user() {
		$userApp = new userCallback;
		// Process this payment
		$today_date = $this->today_date; // Todays date 
		$exp_date 	= $this->exp_date; // Expiry date
		$payer_id	= $this->payer_id;
		$payment_id	= $this->payment_id;
		$price		= $this->price;
		$currency	= $this->currency;
		$plan		= $this->plan; 
		$pfn 		= $this->pfn;
		$pln		= $this->pln;
		$email		= $this->email;
		$country	= $this->country;
		$order_ref	= $this->order_ref;

		// Try to add the user to the payments table
		$sql = sprintf("INSERT INTO " . TABLE_PAYMENT . " (`payer_id`, `payment_id`, `payer_firstname`, `payer_lastname`, `payer_email`, `payer_country`, `trx_id`, `amount`, `currency`, `status`, `plan`, `valid_till`, `payment_date`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $payer_id, $payment_id, $pfn, $pln, $email, $country, $order_ref, $price, $currency, 1, $plan, $exp_date, $today_date);
		$return = dbProcessor($sql, 0, 1);

		// If the user was successfully promoted, set the new role in users table
		if ($return) {
			$premium_c = $userApp->premiumStatus($_GET['promote'], 2);
			$plan_code = badge(null, $plan, 3);
			if ($plan_code == 1) {
				$role = 'voter';
			} elseif ($plan_code == 2 || $plan_code == 3) {
				$role = 'contestant';
			} else {
				$role = 'agency';
			}
			$sql = sprintf("UPDATE " . TABLE_USERS . " SET `role` = '%s' WHERE id = '%s'", $role, $payer_id); 
			dbProcessor($sql, 0, 1);	
		}
		return ($return) ? 1 : 0;
	}
}

/**
 * Notification and messaging class 
 */
class msgNotif {
	public $settings;
	public $user;

	// View all notifications available to the current user level
	function notification($type=null, $array=null){
		global $DB, $USER, $CONF, $LANG, $user;

		// $notif_array = array(sender, type, receiver, content);
		if (isset($this->limit)) {
			$limit = sprintf('ORDER BY time DESC LIMIT %s, %s', $this->start, $this->limit);
		} else {$limit = 'ORDER BY time DESC';}

		// Show notifications
		if ($type == 0) {
			// See New notifications for contestant
			$receiver = $this->receiver;
			$sql = sprintf("SELECT * FROM " . TABLE_NOTIFY . " WHERE receiver = '%s' %s ", $receiver, $limit);
			$t = 1;
		} elseif ($type == 1) {
			// See notification sent by this user
			$sender = $this->sender;
			$sql = sprintf("SELECT * FROM " . TABLE_NOTIFY . " WHERE sender = '%s' ORDER BY time DESC, seen ASC ", $sender);
		} elseif ($type == 2) {
			// Show all notifications
			$sql = sprintf("SELECT * FROM " . TABLE_NOTIFY . " WHERE receiver = '%s' ORDER BY time DESC, seen ASC ", $receiver);
			$t = 1;
		} elseif ($type == 3) {
			// Show the selected notification
			$receiver = $this->receiver;
			$sql = sprintf("SELECT * FROM " . TABLE_NOTIFY . " WHERE receiver = '%s' and id = '%s' ", $receiver, $this->id);
			$t = 1;
		} else {
			// Send notification
			list($sender, $receiver, $content, $type, $master) = $array;
			$sql = sprintf("INSERT INTO " . TABLE_NOTIFY . " (`sender`, `receiver`, `content`, `type`, `master`, `seen`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s')", $sender, $receiver, $content, $type, $master, 0);
			$t = 0;
			$this->notificationState(0, $receiver);
		}
		return dbProcessor($sql, $t); 
	}

	// Global notifications for logged in user shown on Navbar
	function instantNotification($type = 0, $lim = null) {
		// Type 0: Site Notifications, Type 1: message Notifications

		global $DB, $USER, $CONF, $LANG, $user, $settings;

		$limit = ($lim) ? 'LIMIT 0, '.$lim : '';
		if ($type == 0) {
			// See New notifications for contestant
			$receiver = $this->receiver;
			$sql = sprintf("SELECT * FROM " . TABLE_NOTIFY . " WHERE receiver = '%s' AND seen = '0' ORDER BY time DESC %s", $receiver, $limit);
			$t = 1; 
		} elseif ($type == 1) {
			$y = isset($this->all) ? '' : 'AND  `seen` = \'0\'';
			$sql = sprintf("SELECT * FROM " . TABLE_MESSAGE . " AS messenger, " . TABLE_USERS . " AS users WHERE `receiver` = '%s' %s AND `messenger`.`sender` = `users`.`id` ORDER BY `messenger`.`cid` DESC %s", $user['id'], $y, $limit); 
			$t = 1; 
		}
		return dbProcessor($sql, $t); 
	}

	function notificationState($state, $id, $seen = null) {
		// $id could either be a user_id or a notification_id
		// State = 0, New notification exist
		// state = 1, No new notification

		if ($seen) { 
			// Change the status of the selected notification to seen
			$sql = sprintf("UPDATE " . TABLE_NOTIFY . " SET  `seen` = '%s' " 
            . " WHERE `id` = %s", $state, $id);
		} else { 
			// Remove the notifications counter badge
			$sql = sprintf("UPDATE " . TABLE_USERS . " SET `new_notification` = '%s' " 
            . " WHERE `id` = %s", $state, $id);
        }
        dbProcessor($sql, 0);
	}
}

//Get users real name
function realName($username, $first = null, $last = null, $fullname = null) {
	if($fullname) {
		if($first && $last) {
			return ucfirst($first).' '.ucfirst($last);
		} else {
			return ucfirst($username);
		}
	}
	if($first && $last) {
		return ucfirst($first).' '.ucfirst($last);
	} elseif($first) {
		return ucfirst($first);
	} elseif($last) {
		return ucfirst($last);
	} elseif($username) { // If username is not set, return empty (example: the real-name under the subscriptions)
		return ucfirst($username);
	}
}

//Get users real name
function completeIntro($city = null, $state = null, $country = null, $lovesto = null) {
	global $LANG;

	$dedot = str_replace('.','',strtolower($lovesto));
	preg_match('[\W]', substr($dedot,-1), $q);
	if ($q) {
		$user_loves = rtrim($dedot, $q[0]);
	} else {
		$user_loves = $lovesto;
	}
	if (substr($user_loves,-3) == strtolower('ing')) {
		$repl = ' Loves ';
	} else {
		$repl = ' Loves to ';
	}
	
	if($lovesto) {
		if($city && $state && $country && $user_loves) {
			return $city.', '.$state.', '.$country.$repl.$user_loves;
		} elseif($city && $state) {
			return $city.', '.$state.', '.$repl.$user_loves;
		} elseif($city) {
			return $city.', '.$repl.$user_loves;
		} elseif($state) {
			return $state.', '.$repl.$user_loves;
		} else {
			return $country;
		}
	} else {
		if($city && $state && $country) {
			return $city.', '.$state.', '.$country;
		} elseif($city && $country) {
			return $city.', '.$country;
		} elseif($state && $country) {
			return $state.', '.$country;
		} elseif($country) { // If username is not set, return empty (example: the real-name under the subscriptions)
			return $country;
		} elseif($city) {
			return $city;
		} elseif ($city == null && $state == null && $country == null && $lovesto == null) {
			return $LANG['new_user_intro'];
		} elseif($state) {
			return $state;
		}		
	}
  
}

function profilesCountry($username) {
    // Set the users location
    global $userApp;

    $prof = $userApp->userData($username);
    if (!empty($prof['id'])) {
	    if ($prof['state'] && $prof['country']) {
	        $location = $prof['state'].', '.$prof['country'];
	    } elseif ($prof['state']) {
	        $location = $prof['state'];
	    } else {
	        $location = $prof['country'];
	    }	
	    return $location;
    } else {
    	return false;
    }

} 

function permalink($url) {
	// url: the URL to be rewritten
	global $settings;

	if($settings['permalinks']) {
		$path['profile'] 			= 'index.php?a=profile';
		$path['gallery'] 			= 'index.php?a=gallery';
		$path['contest'] 			= 'index.php?a=contest';
		$path['account']			= 'index.php?a=account';
		$path['explore']			= 'index.php?a=explore';
		$path['featured']			= 'index.php?a=featured'; 
		$path['settings'] 			= 'index.php?a=settings';
		$path['messenger']			= 'index.php?a=messenger';
		$path['timeline']			= 'index.php?a=timeline';
		$path['followers']			= 'index.php?a=followers';
		$path['premium']			= 'index.php?a=premium';
		$path['credit']				= 'index.php?a=credit';
		$path['welcome']			= 'index.php?a=welcome';
		$path['recovery']			= 'index.php?a=recovery';
		$path['update']				= 'index.php?a=update';
		$path['voting']				= 'index.php?a=voting'; 
		$path['enter']				= 'index.php?a=enter';
		$path['static']				= 'index.php?a=static';
		$path['search']				= 'index.php?a=search';
		$path['documentation']		= 'index.php?a=documentation';
		$path['offline']			= 'index.php?a=offline';
 	
		if(strpos($url, $path['profile'])) {
			$url = str_replace(array($path['profile'], '&u=', '&r=', '&filter='), array('profile', '/', '/', '/filter/'), $url);
		} elseif(strpos($url, $path['gallery'])) {
			$url = str_replace(array($path['gallery'], '&u=', '&r=', '&filter='), array('gallery', '/', '/', '/filter/'), $url);
		} elseif(strpos($url, $path['contest'])) {
			$url = str_replace(array($path['contest'], '&id=', '&s=', '&d=', '&u=', '&applications=', '&manage=', '&approved='), array('contest', '/id/', '/', '/', '/owner/', '/applications/', '/manage/', '/approved/'), $url);
		} elseif(strpos($url, $path['account'])) {
			$url = str_replace(array($path['account'], '&votes=', '&notifications=', '&notifications'), array('account', '/votes/', '/notifications/', '/notifications/'), $url);
		} elseif(strpos($url, $path['explore'])) {
			$url = str_replace(array($path['explore'], '&logout'), array('explore', '/logout/'), $url);
		} elseif(strpos($url, $path['featured'])) {
			$url = str_replace(array($path['featured']), array('featured'), $url);
		} elseif(strpos($url, $path['premium'])) {
			$url = str_replace(array($path['premium']), array('premium'), $url);
		}  elseif(strpos($url, $path['settings'])) {
			$url = str_replace(array($path['settings'], '&b=', '&edit=', '&delete=', '&promote='), array('settings', '/', '/edit/', '/delete/', '/promote/'), $url);
		} elseif(strpos($url, $path['messenger'])) {
			$url = str_replace(array($path['messenger'], '&u=', '&id='), array('messenger', '/', '/'), $url);
		} elseif(strpos($url, $path['timeline'])) {
			$url = str_replace(array($path['timeline'], '&u=', '&id=', '&read=', '&share=', '&sort='), array('timeline', '/', '/', '/read/', '/share/', '/'), $url);
		} elseif(strpos($url, $path['followers'])) {
			$url = str_replace(array($path['followers'], '&followers=', '&following='), array('followers', '/followers/', '/following/'), $url);
		} elseif(strpos($url, $path['voting'])) {
			$url = str_replace(array($path['voting'], '&id=', '&user='), array('voting', '/', '/vote/'), $url);
		} elseif(strpos($url, $path['search'])) {
			$url = str_replace(array($path['search'], '&filters=', '&query='), array('search', '/filters/', '/query/'), $url);
		} elseif(strpos($url, $path['welcome'])) {
			$url = str_replace(array($path['welcome'], '&ref='), array('welcome', '/referrer/'), $url);
		} elseif(strpos($url, $path['recovery'])) {
			$url = str_replace(array($path['recovery'], '&account=', '&ready=1'), array('recovery', '/account/', '/set/'), $url);
		} elseif(strpos($url, $path['enter'])) {
			$url = str_replace(array($path['enter'], '&id=', '&viewdata=', '&success=', '&process=', '&create=', '&manage=', '&user=', '&update='), array('enter', '/', '/viewdata/', '/success/', '/process/', '/create/', '/manage/', '/profile/', '/update/'), $url);
		} elseif(strpos($url, $path['update'])) {
			$url = str_replace(array($path['update'], '&id=', '&edit=true', '&name='), array('account/settings', '/', '/edit/', '/'), $url);
		} elseif(strpos($url, $path['credit'])) {
			$url = str_replace(array($path['credit']), array('credit'), $url);
		} elseif(strpos($url, $path['static'])) {
			$url = str_replace(array($path['static'], '&page='), array('read', '/'), $url);
		} elseif(strpos($url, $path['documentation'])) {
			$url = str_replace(array($path['documentation'], '&read=', '&edit=', '&delete=', '&type=', '&write=', '&support='), array('docs/', '', 'edit/', 'delete/', '/type/', 'write/', 'support/'), $url);
		} elseif(strpos($url, $path['offline'])) {
			$url = str_replace(array($path['offline']), array('offline'), $url);
		}
	}
	return $url;
}

function cleanUrl($url) {
	$url = str_replace(' ', '-', $url);
	$url = preg_replace('/[^\w-+]/u', '', $url);
	$url = preg_replace('/\-+/u', '-', $url);
	return mb_strtolower($url);
}
 
class menuHandler {
	public $user;
	public $settings;

	/**
	 * Navigation bar user dropdown menu
	 */  
	function droplMenu(){
		global $LANG, $PTMPL, $CONF, $user; 
		$userApp = new userCallback;
		$premium_status = $userApp->premiumStatus(0, 1); 
		if ($user !== FALSE) {
			$theme = new themer('user/dropmenu'); $dropmenu = '';
			$PTMPL_old = $PTMPL; $PTMPL = array(); 

				$PTMPL['droplinks'] = $divider = '';
				$links = array(	array('account', ucfirst(realName($user['username'], $user['fname'], $user['lname'])), 1, 0), 
					(!$premium_status ? array('premium', $LANG['up_premium'], 1, 0) : ''),
					array('enter&viewdata='.$user['id'], $LANG['my_data'], 1, 2),
					array('explore', $LANG['explore'], 1, 2),
					array('update', $LANG['user_settings'], 1, 0), 
					array('explore&logout', $LANG['log_out'], 0, 0));

				//print_r($links);
				foreach ($links as $rs => $key) {
					if($key) {
						$PTMPL['droplinks'] .= $divider.'<a class="dropdown-item" href="'.permalink($CONF['url'].'/index.php?a='.$key[0]).'">'.$key[1].'</a>';
					} 
				}

			$dropmenu = $theme->make();	
			$PTMPL = $PTMPL_old; unset($PTMPL_old);
			return $dropmenu;
		}	
	}

	/**
	 * Navigation bar notifications dropdown menu
	 */   
	function notificationsMenu() {
		// Request 1 = notifications count
		// Request 2 = All notifications link
		// Request 3 = Return notifications

		global $LANG, $PTMPL, $CONF, $user, $settings;
		$us = new userCallback;

		$us->user_id = $user['id'];
		$user = $us->userData(null, 1)[0];  
 

		if ($user) {				
			$cd = new contestDelivery;
			$t = new marxTime;
			
			$noti = new msgNotif;
			$noti->receiver = $user['id'];
			$limit = $settings['per_notification_drop'];
			$notification = $noti->instantNotification(0, $limit);
			$noti_count = count($noti->instantNotification(0)); 

	 		// Get and show a notice of number of seen and unseen notifications 
			$count_all = count($noti->notification(0)); 
			if ($noti_count < 1) {
				$class = 'aqua-gradient';
				$msgs = $LANG['no_ping'];
			} else {
				$noti_count > 1 ?$s = 's':$s='';
				$class = 'peach-gradient';
				$msgs = sprintf($LANG['ping'], ' <span class="font-weight-bold">'.$noti_count.'</span>/'.$count_all, $s);
			}
			// Notification new and seen banner
			$count_notice = '<span class="d-flex inline-flex justify-content-center text-center '.$class.' text-white">'.$msgs.'</span>';

			// Notification count badge
			if ($noti_count>0 && $user['new_notification']==0) {
				$counter = '<span class="badge badge-pill badge-danger">'.$noti_count.'</span>';
			} else {
				$counter = '';
			}

			// All notifications button
			$all_btn = '<a class="text-center dropdown-item" href="'.permalink($CONF['url'].'/index.php?a=account&notifications').'">'.$LANG['view_all_pings'].'</a>';

				$notifications = $divider = '';
				if ($notification) {
					foreach ($notification as $rs => $key) {
						$br = ' ';
						$content = myTruncate(rip_tags($key['content']), 50, $br);
						$time = '<i class="fa fa-clock-o" aria-hidden="true"></i> '.$t->timeAgo(strtotime($key['time']));

						if ($key['type'] == 4) {
							$sender = 'System'; 
						} elseif ($key['type'] == 1 || $key['type'] == 3) {
							$sd = $us->collectUserName(0, 1, $key['sender']); 
							$sender = $sd['title']; 
						} else {
							$sd = $us->collectUserName(0, 0, $key['sender']);
							$sender = $sd['username'];
						}
						if($key) {
							$notifications .= $divider.'  
			                  <a class="dropdown-item waves-effect waves-light" href="'.permalink($CONF['url'].'/index.php?a=account&notifications='.$key['id']).'">
			                      <i class="fa fa-bullhorn mr-2" aria-hidden="true"></i>
			                      <span>'.$LANG['new_ping'].'</span>
			                      <div>'.$LANG['from'].' '.$sender.'</div>
			                      <div>'.$content.'</div>
			                      <div>'.$time.'</div>
			                  </a> ';
						} 
					}
				} else {
					$notifications = '
					<a class="dropdown-item disabled text-center" href="#">
						<span class="text-danger">
							<i class="fa fa-bell-slash mr-2" aria-hidden="true"></i>'.$LANG['no_new_ping'].'
						</span>
					</a>';
				}
			$notifs = array('notifications' => $notifications, 'all_notifications' => $all_btn, 'count' => $counter, 'notice' => $count_notice);
			return $notifs;		
		}
	}

	/**
	 * Navigation bar messages notifications dropdown menu
	 */   
	function messageNotifications() {
		// Request 1 = notifications count
		// Request 2 = All notifications link
		// Request 3 = Return notifications

		global $LANG, $PTMPL, $CONF, $user, $settings;
		$us = new userCallback;

		$us->user_id = $user['id'];
		$user = $us->userData(null, 1)[0];  
 

		if ($user) {				
			$cd = new contestDelivery;
			$t = new marxTime;
			
			$noti = new msgNotif;
			$noti->receiver = $user['id'];
			$limit = $settings['per_notification_drop'];
			$notification = $noti->instantNotification(1, $limit);
			$noti_count = count($noti->instantNotification(1));

	 		// Get and show a notice of number of seen and unseen notifications 
			$noti->all = 1;
			$count_all = count($noti->instantNotification(1)); 
			if ($noti_count < 1) {
				$class = 'aqua-gradient';
				$msgs = $LANG['no_message'];
			} else {
				$noti_count > 1 ?$s = 's':$s='';
				$class = 'peach-gradient';
				$msgs = sprintf($LANG['new_message_count'], ' <span class="font-weight-bold"> '.$noti_count.'</span>/'.$count_all, $s);
			}
			// Notification new and seen banner
			$count_notice = '<span class="d-flex inline-flex justify-content-center text-center '.$class.' text-white">'.$msgs.'</span>';

			// Notification count badge
			if ($noti_count>0) {
				$counter = '<span class="badge badge-pill badge-danger">'.$noti_count.'</span>';
			} else {
				$counter = '';
			}

			// All notifications button
			$all_btn = '<a class="text-center dropdown-item" href="'.permalink($CONF['url'].'/index.php?a=messenger').'">'.$LANG['view_all_message'].'</a>';

				$notifications = $divider = '';
				if ($notification) {
					foreach ($notification as $rs => $key) {
						$br = ' ';
						$content = myTruncate(rip_tags($key['message']), 50, $br);
						$time = '<i class="fa fa-clock-o" aria-hidden="true"></i> '.$t->timeAgo(strtotime($key['date']), 1);

						if ($key['sender'] == 0) { 
							$sender = 'System';
						} else {  
							$sd = $us->collectUserName(0, 0, $key['sender']);
							$sender = $sd['fullname'];
						}
						if($key) {
							$notifications .= $divider.'  
			                  <a class="dropdown-item waves-effect waves-light" href="'.permalink($CONF['url'].'/index.php?a=messenger&u='.$sd['username'].'&id='.$sd['user_id']).'">
			                      <i class="fa fa-envelope mr-2" aria-hidden="true"></i>
			                      <span>'.$LANG['new_message'].'</span>
			                      <div>'.$LANG['from'].' '.$sender.'</div>
			                      <div>'.$content.'</div>
			                      <div>'.$time.'</div>
			                  </a> ';
						} 
					}
				} else {
					$notifications = '
					<a class="dropdown-item disabled text-center" href="#">
						<span class="text-danger">
							<i class="fa fa-envelope-open mr-2" aria-hidden="true"></i>'.$LANG['no_new_message'].'
						</span>
					</a>';
				}
			$notifs = array('notifications' => $notifications, 'all_notifications' => $all_btn, 'count' => $counter, 'notice' => $count_notice);
			return $notifs;		
		}
	}

	/**
	 * Show notifications in notifications page
	 */   
	function viewNotifications($request) {
		global $LANG, $PTMPL, $CONF, $user, $settings;
		// Request 1 = notifications count
		// Request 2 = All notifications link
		// Request 3 = Return notifications

		$us = new userCallback;
		$noti = new msgNotif;

		$us->user_id = $user['id'];
		$user = $us->userData(null, 1)[0];  
 		
 		// Get and show a notice of number of seen and unseen notifications
		$noti->receiver = $user['id']; 
		$count_it = count($noti->instantNotification(0)); 
		$count_all = count($noti->notification(0)); 
		if ($count_it < 1) {
			$class = 'aqua-gradient';
			$msgs = 'You don\'t have any new Ping';
		} else {
			$count_it > 1 ?$s = 's':$s='';
			$class = 'peach-gradient';
			$msgs = sprintf($LANG['ping'], ' <span class="font-weight-bold">'.$count_it.'</span>/'.$count_all, $s);
		}
		$count_notice = '<span class="d-flex inline-flex justify-content-center text-center '.$class.' text-white">'.$msgs.'</span>';

			// Show the notifications
			if ($user) {				
				$cd = new contestDelivery;

				if ($request == 2)  {
					$noti->id = $this->id;
					$noti->receiver = $user['id'];
					$notfn = $noti->notification(3)[0];

						$dd = strtotime($notfn['time']);
						$date = date("D M d - h:i:s A", $dd);

						// Check if this notification was sent from user activity
						if ($notfn['type'] == 0 || $notfn['type'] == 2) {
							$sd = $us->collectUserName(0, 0, $notfn['sender']);
							$sender = $sd['username'];
							$img = $CONF['url'].'/uploads/faces/'.$sd['photo']; 
							$url = '<a class="text-white" href="'.permalink($CONF['url'].'/index.php?a=profile&u='.$sender).'">'.$sender.'</a>'; 
						// Check if this notification was sent from contest activity
						} elseif ($notfn['type'] == 1 || $notfn['type'] == 3) {
							$sd = $us->collectUserName(0, 1, $notfn['sender']); 
							$sender = $sd['title'];
							$img = $CONF['url'].'/uploads/cover/contest/'.$sd['photo']; 
							$url = '<a class="text-white" href="'.permalink($CONF['url'].'/index.php?a=contest&id='.$notfn['sender']).'">'.$sender.'</a>'; 
						// elseif this notification was sent from system activity
						} elseif ($notfn['type'] == 4) {
							$sender = $settings['site_name'];
							$img = $CONF['url'].'/'.$PTMPL['template_url'].'/img/notification.png'; 
							$url = '<a class="text-white" href="'.permalink($CONF['url'].'/index.php?a=featured').'">'.$sender.'</a>';
						}
						// Set a different gradient for system notifications
						$grnt = ($notfn['type'] == 'system') ? 'young-passion' : 'aqua';

						$view = 
						'<div id="set-messddage"></div>   
		                  	<div class="col-md-12 text-left" id="notification_'.$notfn['id'].'">  
				                <div class="card border-light">
				                  <div class="chip chip-lg '.$grnt.'-gradient white-text m-1" style="margin-bottom: -1rem;"> 
				                    <img src="'.$img.'" alt="name"> '.$url.'<span class="float-right px-2"><a onclick="delete_the('.$notfn['id'].', 3)"><i class="fa fa-times text-danger"></i></a></span>  
				                  </div> 
				                  <div class="card-text text-muted px-2 text-info">'.$date.'</div>
				                    <p class="card-text px-3">'.$notfn['content'].'</p> 
				            </div>
				        	<hr>
				        </div>';	
					return array('notifications' => $view, 'notice' => $count_notice, 'page' => '');

				} elseif ($request == 1) {
					// Enable pagination on the notification page
				    $perpage = $settings['per_notification'];
				    if(isset($this->page) & !empty($this->page)){
				        $curpage = $this->page;
				    } else{
				        $curpage = 1;
				    }
				    $start = ($curpage * $perpage) - $perpage;

					$noti->receiver = $user['id'];
				    $noti_count = count($noti->notification(0)); 

				    $noti->limit = $perpage;
				    $noti->start = $start;
				    $notification = $noti->notification(0); 

				    $endpage = ceil($noti_count/$perpage);
				    $startpage = 1;
				    $nextpage = $curpage + 1;
				    $previouspage = $curpage - 1; 	 
				}

			    $page = '';
	        if ($endpage > 1) {
	            if($curpage != $startpage){	
					$page .= '<a class="px-3" href="#" onclick="viewAllNotifications(1, '.$_POST['start'].', '.$_POST['limit'].', '.$startpage.')" class="text-black"><i class="fa fa-angle-double-left"></i></a>';	            
	            }		    
			    if($curpage >= 2){ 
					$page .= '<a class="px-3" href="#" onclick="viewAllNotifications(1, '.$_POST['start'].', '.$_POST['limit'].', '.$previouspage.')" class="text-black"><i class="fa fa-angle-left"></i></a>'; 
			    } 

					$page .= '<a class="px-3" href="#" onclick="viewAllNotifications(1, '.$_POST['start'].', '.$_POST['limit'].', '.$curpage.')" class="text-black"><i class="fa fa-circle"> PAGE '.$curpage.'</i></a>';	

			    if ($curpage != $endpage) {
					$page .= '<a class="px-3" href="#" onclick="viewAllNotifications(1, '.$_POST['start'].', '.$_POST['limit'].', '.$nextpage.')" class="text-black"><i class="fa fa-angle-right"></i></a>'; 
					
					$page .= '<a class="px-3" href="#" onclick="viewAllNotifications(1, '.$_POST['start'].', '.$_POST['limit'].', '.$endpage.')" class="text-black"><i class="fa fa-angle-double-right"></i></a>';	 								     
			    }
			}

			$notifications = $divider = '';
			if ($notification) {
				foreach ($notification as $rs => $key) { 

					$dd = strtotime($key['time']);
					$date = date("D M d - h:i:s A", $dd);

					// elseif this notification was sent from user activity
					if ($key['type'] == 0 || $key['type'] == 2) { 
						$sd = $us->collectUserName(0, 0, $key['sender']);
						$sender = $sd['username'];
						$img = $CONF['url'].'/uploads/faces/'.$sd['photo']; 
						$url = '<a class="text-white" href="'.permalink($CONF['url'].'/index.php?a=profile&u='.$sender).'">'.$sender.'</a>'; 
					// elseif this notification was sent from contest activity
					} elseif ($key['type'] == 1 || $key['type'] == 3) {
						$sd = $us->collectUserName(0, 1, $key['sender']); 
						$sender = $sd['title'];
						$img = $CONF['url'].'/uploads/cover/contest/'.$sd['photo']; 
						$url = '<a class="text-white" href="'.permalink($CONF['url'].'/index.php?a=contest&id='.$key['sender']).'">'.$sender.'</a>'; 
					// elseif this notification was sent from system activity
					} elseif ($key['type'] == 4) {  
						$sender = $settings['site_name'];
						$img = $CONF['url'].'/'.$PTMPL['template_url'].'/img/notification.png'; 
						$url = '<a class="text-white" href="'.permalink($CONF['url'].'/index.php?a=featured').'">'.$sender.'</a>';
					}
					// Set a different gradient for system notifications
					$grnt = ($key['type'] == 'system') ? 'young-passion' : 'aqua';
					if ($key['seen'] == 1) {
						$sts = 'Seen';
						$cc = 'success';
					} else {
						$sts = 'New';
						$cc = 'danger';
					}
					if($key) {
						$notifications .= $divider.'
					  	<div id="set-message_'.$key['id'].'"></div>   
	                  	<div class="col-md-12 text-left" id="notification_'.$key['id'].'">  
			                <div class="card border-light">
			                  	<div class="chip chip-lg '.$grnt.'-gradient white-text m-1" style="margin-bottom: -1rem;"> 
			                    	<img src="'.$img.'" alt="name"> '.$url.'<span class="float-right px-2"><a onclick="delete_the('.$key['id'].', 3)"><i class="fa fa-times text-danger"></i></a></span>
			                  	</div> 
			                  	<div class="card-text text-muted px-2 text-info">'.$date.'</div>
			                  	<p class="card-text px-3">
			                  		<span class="float-right badge badge-pill badge-'.$cc.'">'.$sts.'</span>
			                  		'.$key['content'].'
			                  	</p> 
			                </div> 
			          		<hr>
			          	</div>';				                  
					} 
				}
			} else {
				$notifications = ' 
					<h2 class="text-danger text-center">
						<i class="fa fa-bell-slash mr-2" aria-hidden="true"></i>No Pings today!
					</h2> ';
			}
			if ($request == 1)  {
				return array('notifications' => $notifications, 'notice' => $count_notice, 'page' => $page,);
			}
		}
	
	}
 	
 	// Side navigation contest management dropdown menu
	function sideMenuContestUL(){
		global $LANG, $PTMPL, $CONF, $settings;
		$user = $this->user; 

		if ($user) {
			$theme = new themer('user/menu_contest_ul'); $dropmenu = '';
			$PTMPL_old = $PTMPL; $PTMPL = array(); 

			$PTMPL['droplinks'] = $divider = '';
			if ($user['role'] == 'agency') {
				$links = array(	array('contest&d=create&id=new', $LANG['create_contest']),
				 	array('contest&u='.$user['username'], $LANG['my_contests']),
					array('featured', $LANG['featured']),
					array('explore', $LANG['explore'])); 
			} else {
				$links = array(	array('contest&d=create&id=new', $LANG['create_contest']), 
					array('featured', $LANG['featured']),
					array('explore', $LANG['explore'])); 					
			}
   
			//print_r($links);
			foreach ($links as $rs => $key) {
				if($key) {
					$PTMPL['droplinks'] .= $divider.'<li><a class="waves-effect" href="'.permalink($CONF['url'].'/index.php?a='.$key[0]).'">'.$key[1].'</a></li>';
				} 
			}
			$dropmenu = $theme->make();	
			$PTMPL = $PTMPL_old; unset($PTMPL_old);
			return $dropmenu;
		}	
	}		

	/**
	 * Side navigation menu 
	 */  
	function menu($user) {
		global $PTMPL, $LANG, $CONF, $DB, $settings;
		
		$admin_url = (isset($_SESSION['admin']) ? '<li class="nav-item" title="'.$LANG['admin_panel'].'"><a href="'.$CONF['url'].'/index.php?a=admin"><i class="fa fa-gear"><i></a></li>' : '');

		if($user) {
			$theme = new themer('user/menu'); $menus = '';
			$PTMPL_old = $PTMPL; $PTMPL = array();

			$PTMPL['menuUldrop'] = $this->sideMenuContestUL();
			
			$PTMPL['fullname'] = realName($user['username'], $user['fname'], $user['lname']);
			if ($user['photo']) {
				$PTMPL['pphoto'] = $CONF['url'].'/uploads/faces/'.$user['photo'];
			} else {
				$PTMPL['pphoto'] = $CONF['url'].'/uploads/faces/default.jpg';
			} 
			$PTMPL['username'] = $user['username'];
			$PTMPL['site_url'] = $CONF['url'];
			$PTMPL['template_path'] = $CONF['template_path']; 
			$PTMPL['template_name'] = $CONF['template_name']; 
			
			$PTMPL['admin_url'] = $admin_url; 
			
			// Documentation menu
			$site_class = new siteClass;
			$site_class->what = 'featured = \'1\' AND status = \'1\' ORDER BY date_posted DESC';
			$get_pages = $site_class->static_pages(0, 1);
			
			$list_pages = '';
			if ($get_pages) {
				foreach ($get_pages as $key) {
					$list_pages .= '	
	                <ul>
	                  <li><a href="'.permalink($CONF['url'].'/index.php?a=documentation&read='
	                  	.$key['link'])
	                  .'" class="waves-effect ">'.$key['title'].'</a> </li> 
	                </ul>';
				}
			}
			$PTMPL['documentation'] = $list_pages;
			$PTMPL['contact'] = '<a href="'.permalink($CONF['url'].'/index.php?a=documentation&support=review')
	                  .'" class="collapsible-header waves-effect"><i class="fa fa-envelope-o"></i>Contact and Support Tickets</a>';
			$menus = $theme->make();
			$PTMPL = $PTMPL_old; unset($PTMPL_old);
			return $menus;
		} 
	}
}

class barMenus {
	// Status could be 'on' or 'off'

	function ads($status, $unit=1, $x=0) {
		global $PTMPL, $LANG, $CONF, $DB, $settings;
		
		$theme = new themer('contest/menubars'); $menubars = '';
		$random = rand(1, 10);
		$new_unit = ($random <= 2 && $random != 6) ? 1 : 0;
		if ($unit == 1) {
			$ads_unit = $settings['ads_1'];
		} elseif ($unit == 2) {
			$ads_unit = $settings['ads_2'];
		} elseif ($unit == 3) {
			$ads_unit = $settings['ads_3'];
		} elseif ($unit == 4) {
			$ads_unit = $settings['ads_4'];
		} elseif ($unit == 5) {
			$ads_unit = $settings['ads_5'];
		}

		if ($unit == 6) {
			$ads_unit = $settings['ads_6'];
		} else {
			if ($new_unit == 1) {
				$ads_unit = $ads_unit;
			} else {
				$ads_unit = ($unit == 2) ? $settings['ads_5'] : $settings['ads_4'];
			}			
		}

		if ($status == 'on' || $status == 1) {
			if ($x==1) {
				$PTMPL['adsbar'] = stripslashes($ads_unit);
			} else {
				$PTMPL['adsbar'] = ' 
			    <div class="card mb-3" style="min-width:100%;"> 
			      <div class="card-body" style="min-width:100%;">
			        <h6 class="card-title">'.$LANG['sponsored'].'</h6>
			        <p class="card-text" style="min-width:100%;">'.stripslashes($ads_unit).'</p> 
			      </div>
			    </div>';				
			}
		}
		$menubars = $theme->make();
		return $menubars;
	}
}

class sidebarClass {
	public $user;
	public $settings;

	/**
	 * Contest management menu 
	 */    
	function manageMenu($page = null) {
		global $LANG, $PTMPL, $CONF, $user; 

		if (isset($_GET['d']) && $_GET['d'] == 'create' && $_GET['id'] == 'new') {
			$manage = $_GET['id'];
			$c = 'new_contest';
		} elseif (isset($_GET['d']) && $_GET['d'] == 'create' && isset($_GET['id']) && $_GET['id'] != '') {
			$manage = $_GET['id'];
			$c = 'edit';
		} elseif ($_GET['a'] != 'enter' && isset($_GET['manage']) && $_GET['manage'] !='') {
			$manage = $_GET['manage'];
			$c = 'manage';
		} elseif (isset($_GET['approved']) && $_GET['approved'] !='') {
			$manage = $_GET['approved'];
			$c = 'approved';
		} elseif (isset($_GET['applications']) && $_GET['applications'] !='') {
			$manage = $_GET['applications'];
			$c = 'applications';
		} elseif (isset($_GET['create']) && $_GET['create'] !='') {
			$manage = $_GET['create'];
			$c = 'create';
		} elseif ($_GET['a'] == 'enter' && isset($_GET['manage']) && $_GET['manage'] != '') {
			$manage = $_GET['manage'];
			$c = 'created';
		} 
		$gett = new contestDelivery;

		if ($user && $user['role'] == 'agency') {
			if (isset($_GET['manage']) || isset($_GET['approved']) || isset($_GET['applications']) || isset($_GET['create']) 
				|| isset($_GET['d']) && $_GET['d'] == 'create' && isset($_GET['id']) && $_GET['id'] != 'new') {
				$theme = new themer('contest/sidebar'); $manageMenu = '';
				$PTMPL_old = $PTMPL; $PTMPL = array();

			    $gett->contest_id = $manage;
			    $count_apprv = count($gett->getApprovedList(/*$start, $perpage*/));
			    $count_appl = count($gett->viewApplications($manage, 1));			 

				$PTMPL['manage_menu'] = $divider = '';
				if (isset($manage)) {
					$links = array(	array('contest&d=create&id=new', $LANG['create_contest'], 'new_contest'),
					 	array('contest&u='.$user['username'], $LANG['my_contests'], 'my_contests'),
						array('contest&approved='.$manage, $LANG['approved_c'].' <span class="badge badge-pill badge-info">'
							.$count_apprv.'</span>', 'approved'),
						array('contest&applications='.$manage, $LANG['applications'].' <span class="badge badge-pill badge-info">'
							.$count_appl.'</span>', 'applications'),
						array('contest&manage='.$manage, $LANG['manage'].' '.$LANG['contest'], 'manage'),
						array('contest&d=create&id='.$manage, $LANG['edit_contest'], 'edit'),
						array('enter&create='.$manage, $LANG['c_contestant'], 'create'),
						array('enter&manage='.$manage, $LANG['created_prof'], 'created'),
						array('contest&id='.$manage, $LANG['view_details'], 'id'));
				} 
	              
				//print_r($links);
				$PTMPL['manage_menu'] .= $divider.'<div class="mb-3 z-depth-1"> <div class="bg-info py-2 px-4 text-white">Manage Contests</div>';
				foreach ($links as $rs => $key) {
					if ($key[2] == $c) {
						$class = 'bg-info text-white font-weight-bold';
					} else {
						$class = '';
					}
					if($key) {
						$PTMPL['manage_menu'] .= $divider.'
							<div class="list-group"> 
							  	<a href="'.permalink($CONF['url'].'/index.php?a='.$key[0]).'" class="list-group-item list-group-item-action '.$class.'">'.$key[1].'</a> 
							</div>';
					} 
				}
				$PTMPL['manage_menu'] .= $divider.'</div>';

				$manageMenu = $theme->make();	
				$PTMPL = $PTMPL_old; unset($PTMPL_old);
				return $manageMenu;
			}
		}	 
	}

	/**
	 * Create and view contests you created menu 
	 * Show this menu if no contest is selected
	 */    
	function pre_manage_menu($page = null) { 
		global $LANG, $PTMPL, $CONF, $user; 

		if (isset($_GET['contest']) && isset($_GET['u']) && $_GET['u'] !='') {
			$pre_manage = $_GET['u'];
			$c = 'my_contests';
		} elseif (isset($_GET['d']) && $_GET['d'] !='') {
			$pre_manage = $_GET['d'];
			$c = 'create_contest';
		} elseif (isset($_GET['viewdata']) && $_GET['viewdata'] !='') {
			$pre_manage = $_GET['viewdata'];
			$c = 'data';
		} else {
			$pre_manage = 'explore';
			$c = 'explore';
		}  
		$gett = new contestDelivery; 

		if ($user && $user['role'] == 'agency') {
			if (isset($_GET['u']) || isset($_GET['d']) || isset($_GET['viewdata']) || $_GET['a'] == 'explore' || isset($_GET['notifications']) || $_GET['a'] == 'update' || $_GET['a'] == 'voting' || $_GET['a'] == 'messenger') {
				$theme = new themer('contest/sidebar'); $pre_manageMenu = '';
				$PTMPL_old = $PTMPL; $PTMPL = array();		 

				$PTMPL['pre_manage_menu'] = $divider = '';
				if (isset($pre_manage)) {
					$links = array(array('contest&d=create&id=new', $LANG['create_contest'], 'create_contest'),
					 	array('contest&u='.$user['username'], $LANG['my_contests'], 'my_contests'),
					 	array('enter&viewdata='.$user['id'], $LANG['my_data'], 'data'));
				} 
	              
				//print_r($links);
				$PTMPL['pre_manage_menu'] .= $divider.'<div class="mb-3 z-depth-1"> <div class="bg-info py-2 px-4 text-white">My Content</div>';
				foreach ($links as $rs => $key) {
					if ($key[2] == $c) {
						$class = 'bg-info text-white font-weight-bold';
					} else {
						$class = '';
					}
					if($key) {
						$PTMPL['pre_manage_menu'] .= $divider.'
							<div class="list-group"> 
							  	<a href="'.permalink($CONF['url'].'/index.php?a='.$key[0]).'" class="list-group-item list-group-item-action '.$class.'">'.$key[1].'</a> 
							</div>';
					} 
				}
				$PTMPL['pre_manage_menu'] .= $divider.'</div>';

				$pre_manageMenu = $theme->make();	
				$PTMPL = $PTMPL_old; unset($PTMPL_old);
				return $pre_manageMenu;
			}
		}	 
	}

	// Show this menu to regular users is selected
	function user_navigation($page = null) { 
		global $LANG, $PTMPL, $CONF, $user, $settings;  
 
		$gett = new contestDelivery; 

		if ($user && $user['role'] != 'agency') { 
				$theme = new themer('contest/sidebar'); $userMenu = '';
				$PTMPL_old = $PTMPL; $PTMPL = array();		 
 
				$PTMPL['user_navigation_menu'] = $divider = ''; 
				$links = array(array('contest&d=create&id=new', $LANG['create_contest'], 'create_contest'),
				 	array('contest', $LANG['contests'], 'contests'),
					array('enter&viewdata='.$user['id'], $LANG['my_data'], 'data')); 
	               
	            $PTMPL['user_navigation_menu'] .= $divider.'<div class="mb-3 z-depth-1"> <div class="bg-info py-2 px-4 text-white">User Navigation</div>';
				foreach ($links as $rs => $key) { 
					$class = '';	
					if($key) {
						$PTMPL['user_navigation_menu'] .= $divider.'
							<div class="list-group"> 
							  	<a href="'.permalink($CONF['url'].'/index.php?a='.$key[0]).'" class="list-group-item list-group-item-action '.$class.'">'.$key[1].'</a> 
							</div>';
					} 
				}
				$PTMPL['user_navigation_menu'] .= $divider.'</div>';

				$userMenu = $theme->make();	
				$PTMPL = $PTMPL_old; unset($PTMPL_old);
				return $userMenu; 
		}	 
	}

	/**
	 * Administrative menu
	 * Show this menu to the administrator 
	 */    
	function admin_menu($page = null) { 
		global $LANG, $PTMPL, $CONF, $user, $admin;  
 
		$gett = new contestDelivery; 
		$uc = new userCallback;

		// Show cashout request count
		$uc->x = 'cashout != \'0\' AND approved < \'1\'';
		$s_bank = $uc->set_bank(0, 0);
		$coc = $s_bank ? count($s_bank) : '';
		$cb = ($coc) ? '<span class="badge badge-pill badge-danger mx-2">'.$coc.'</span>' : '';
		$cashout = $LANG['cashout'].' '.$LANG['request'].'s'.$cb; 

		if ($admin) { 
			$theme = new themer('contest/sidebar'); $userMenu = '';
			$PTMPL_old = $PTMPL; $PTMPL = array();		 
 
			$PTMPL['admin_navigation_menu'] = $divider = ''; 
			$links = array(	array('settings', $LANG['site_settings'], 'settings'),  
			 	array('settings&b=templates', $LANG['email_templates'], 'templates'),
			 	array('settings&b=giftcards', $LANG['gift_cards'], 'giftcards'),
			 	array('settings&b=cashout', $cashout,' Requests', 'cashout'),
			 	array('settings&b=premium', $LANG['premium'], 'premium'),
			 	array('settings&b=static', 'Static Pages', 'static'),
			 	array('settings&b=payments', $LANG['manage'].' Payments', 'payments'),
			 	array('settings&b=users', $LANG['manage'].' '.$LANG['users'], 'users'),
			 	array('settings&b=contests', $LANG['manage'].' '.$LANG['contest'].'s', 'contests'),
			 	array('settings&b=ads', $LANG['manage'].' Ads', 'ads'),
			 	array('settings&b=tickets', $LANG['support_tickets'], 'tickets'),
			 	array('settings&b=site_templates', 'Templates', 'site_templates'),
			 	array('settings&b=languages', 'Languages', 'languages'),
			 	array('settings&b=password', 'Admin Password', 'password'),
			 	array('contest', $LANG['contest'], 'contest'),
				array('enter&viewdata='.$user['id'], $LANG['my_data'], 'data'),
				array('settings&logout=true', $LANG['log_out'], 'logout')); 
	               
	        $PTMPL['admin_navigation_menu'] .= $divider.'<div class="mb-3 z-depth-1"> <div class="bg-info py-2 px-4 text-white">Admin Menu</div>';
			foreach ($links as $rs => $key) { 
				$class = '';	
				if (isset($_GET['b']) && $key[2] == $_GET['b']) {
					$class = 'list-group-item-primary text-white';
				} elseif (!isset($_GET['b']) && $key[2] == $_GET['a']) {
					$class = 'bg-info text-white font-weight-bold';
				} else {
					$class = '';
				}				
				if($key) {
					$PTMPL['admin_navigation_menu'] .= $divider.'
					<div class="list-group"> 
					  	<a href="'.permalink($CONF['url'].'/index.php?a='.$key[0]).'" class="list-group-item list-group-item-action '.$class.'">'.$key[1].'</a> 
					</div>';
				} 
			}
			$PTMPL['admin_navigation_menu'] .= $divider.'</div>';

			$userMenu = $theme->make();	
			$PTMPL = $PTMPL_old; unset($PTMPL_old);
			return $userMenu; 
		}	 
	}	
}

class contestDelivery {

	function validateContest($contest) {
	    global $PTMPL, $LANG, $CONF, $DB, $settings;
	    $sql = sprintf("SELECT * FROM " . TABLE_CONTEST . " WHERE 1 AND title = '%s'", mb_strtolower($contest));
	    try {
	        $stmt = $DB->prepare($sql); $stmt->execute(); $results = $stmt->fetchAll();
	    } catch (Exception $ex) {
	        return errorMessage($ex->getMessage());
	    } return $results;
	}

	// Search instance gets results by search request
	// 
	// Fetch the contest data from db
	// First instance will get all active results(useful for public display), 
	// declare creator as 0 EG. getContest()
	//
	// Second instance will get the selected active contests, declare $creator as 0,   
	// $contest_id as id or safe_link then $idby as 'safelink' to override the 
	// default 'id' e.g getContest(0, yahoo, 'safelink') or getContest(0, 55)
	//
	// Third instance will get all results from a creator with an optional 
	// filter (useful for management) e.g 
	// getContest(king, 0, AND active = \'1\' LIMIT 6)
	//
	// Last instance will get the selected contest from a 
	// creator (useful for management) with 'safelink' to override the default id 
	// getContest(king, yahoo, 'safelink') or getContest(king, 55) 
	//
	function getContest($creator=NULL, $contest=NULL, $idby='id', $filter= '') {
	    global $PTMPL, $LANG, $CONF, $DB, $settings;

	    // Limit clause to enable pagination
		if (isset($this->limit)) {
			isset($this->featured) ? $rand = 'RAND()' : $rand = 'votes';
			$limit = sprintf('ORDER BY %s DESC LIMIT %s, %s', $rand, $this->start, $this->limit);
		} else {$limit = '';}

		//Check if the contest is a featured contest
		if (isset($this->featured)) {
			$featured = 'AND status = "1" AND featured = "1"';
		} else {
			$featured = '';
		}

	    if (isset($this->search)) {
	    	$search = $this->search;			//Search instance
	    	$sql = sprintf("SELECT * FROM " . TABLE_CONTEST . " WHERE active = '1' AND title LIKE '%s'", '%'.$search.'%');  
	    } elseif ($creator == NULL) {
	    	if ($contest == NULL) {				// First instance
	    		$sql = sprintf("SELECT * FROM " . TABLE_CONTEST . " WHERE active = '1' %s %s", $featured, $limit); 
	    	} else {							// Second instance
	    		$sql = sprintf("SELECT * FROM " . TABLE_CONTEST . " WHERE %s = '%s' AND active = '1'", $idby, $contest);
	    	}
	    } else {
	    	if ($contest == NULL) {				// Third instance
	    		$sql = sprintf("SELECT * FROM " . TABLE_CONTEST . " WHERE 1 AND creator = '%s' %s %s", mb_strtolower($creator), $filter, $limit);
	    	} else {							// Last instance
	    		$sql = sprintf("SELECT * FROM " . TABLE_CONTEST . " WHERE 1 AND %s = '%s' AND creator = '%s'", $idby, $contest, mb_strtolower($creator));
	    	}
	    }
	    $results = dbProcessor(isset($sql)?$sql:'', 1); 
  
    	if ($contest == NULL) {
    		 return $results;
    	} else {
    		return $results[0];
    	} 
	}

	// View all available contests with no restrictions, usefull in administration mode
	function get_all_Contest($id=null) {
	    global $PTMPL, $LANG, $CONF, $DB, $settings;
 		
	    // Limit clause to enable pagination
		if (isset($this->limit)) { 
			$limit = sprintf('ORDER BY votes DESC, id DESC LIMIT %s, %s', $this->start, $this->limit);
		} else {$limit = '';}

	    $cid = ($id) ?  'id = '.$id : '1'; // if $id is not provided all contests will be fetched
		$sql = sprintf("SELECT * FROM " . TABLE_CONTEST . " WHERE %s %s", $cid, $limit); 
	 
	    $results = dbProcessor($sql, 1); 
 
    	if ($id) {
    		 return $results[0];
    	} else {
    		return $results;
    	}  
	}

	// Toggles and switches controls
	function activateItem($id, $table = 0, $active = 0, $type = 0) {
		global $DB, $LANG;
		if ($type == 0) { 
			// enable the contest
			$sql = sprintf("UPDATE " . '%s' . " SET  `active` =  '%s' " 
		            . " WHERE `id` = %s", $table, $active, $id);
			if ($active == '1') {
				$response = '<span class="text-success">'.$LANG['the_contest_is'].' '.$LANG['active'].'</span>'; 
			} else {
				$response = '<span class="text-danger">'.$LANG['the_contest_is'].' '.$LANG['inactive'].'</span>'; 
			}			
		} elseif ($type == 1) {
			// enable voting
			$sql = sprintf("UPDATE " . '%s' . " SET  `allow_vote` = '%s' " 
		            . " WHERE `id` = %s", $table, $active, $id);
			if ($active == '1') {
				$response = '<span class="text-success">'.$LANG['voting_is'].' '.$LANG['active'].'</span>'; 
			} else {
				$response = '<span class="text-danger">'.$LANG['voting_is'].' '.$LANG['inactive'].'</span>'; 
			}			
		} elseif ($type == 2) {
			// enable site wide notifications
			$sql = sprintf("UPDATE " . TABLE_USERS . " SET  `site_notifications` = '%s' " 
		            . " WHERE `id` = %s", $active, $id);
			if ($active == '1') {
				$response = '<span class="text-success">'.$LANG['site_notifications'].' '.$LANG['lang_on'].'</span>'; 
			} else {
				$response = '<span class="text-danger">'.$LANG['site_notifications'].' '.$LANG['lang_off'].'</span>'; 
			}			
		} elseif ($type == 3) {
			// enable email notifications
			$sql = sprintf("UPDATE " . TABLE_USERS . " SET  `email_notifications` = '%s' " 
		            . " WHERE `id` = %s", $active, $id);
			if ($active == '1') {
				$response = '<span class="text-success">'.$LANG['email_notifications'].' '.$LANG['lang_on'].'</span>'; 
			} else {
				$response = '<span class="text-danger">'.$LANG['email_notifications'].' '.$LANG['lang_off'].'</span>'; 
			}			
		} elseif ($type == 4) {
			// Turn premium on or off
			$sql = sprintf("UPDATE " . TABLE_SETTINGS . " SET  `premium` = '%s'", $active);
			if ($active == '1') {
				$response = '<span class="text-success">'.$LANG['premium'].' '.$LANG['lang_on'].'</span>'; 
			} else {
				$response = '<span class="text-danger">'.$LANG['premium'].' '.$LANG['lang_off'].'</span>'; 
			}			
		} elseif ($type == 5) {
			// enable enable facebook login
			$sql = sprintf("UPDATE " . TABLE_SETTINGS . " SET  `fbacc` = '%s'", $active);
			if ($active == '1') {
				$response = '<span class="text-success">'.$LANG['facebook_login'].' '.$LANG['lang_on'].'</span>'; 
			} else {
				$response = '<span class="text-danger">'.$LANG['facebook_login'].' '.$LANG['lang_off'].'</span>'; 
			}			
		} elseif ($type == 6) {
			// enable enable permalinks
			$sql = sprintf("UPDATE " . TABLE_SETTINGS . " SET  `permalinks` = '%s'", $active);
			if ($active == '1') {
				$response = '<span class="text-success">'.$LANG['permalinks'].' '.$LANG['lang_on'].'</span>'; 
			} else {
				$response = '<span class="text-danger">'.$LANG['permalinks'].' '.$LANG['lang_off'].'</span>'; 
			}			
		} elseif ($type == 7) {
			// enable voting
			$sql = sprintf("UPDATE " . '%s' . " SET  `require_social` = '%s' " 
		            . " WHERE `id` = %s", $table, $active, $id);
			if ($active == '1') {
				$response = '<span class="text-success">'.$LANG['social_required'].' '.$LANG['active'].'</span>'; 
			} else {
				$response = '<span class="text-danger">'.$LANG['social_required'].' '.$LANG['inactive'].'</span>'; 
			}			
		}
		$return_val = dbProcessor($sql, 0, $response); 
		if ($return_val == 'No changes were made') {
			return '<span class="text-info">'.$LANG['reverted'].'</span>';;
		} else {
			return $return_val;
		}
  	   
		return $response;
	}

	function scheduleCategory($id) {
		global $DB;
		if ($this->type == 0) {
			$sql = sprintf("INSERT INTO " . TABLE_SCHEDULE . " (`date`, `time`, `activity`, `description`, `contest`) VALUES 
				('%s', '%s', '%s', '%s', '%s')", $this->date, $this->time, $this->activity, $this->description, $id);			 
		} else {
			$sql = sprintf("INSERT INTO " . TABLE_CATEGORY . " (`category`, `requirements`, `description`, `contest`) VALUES 
				('%s', '%s', '%s', '%s')", $this->category, $this->requirement, $this->description, $id);				
		}

		try {
			$stmt = $DB->prepare($sql);	 	
			$stmt->execute();
		} catch (Exception $ex) {
		   $error = errorMessage($ex->getMessage());
		}
		if ($stmt->rowCount() > 0) { 
			if ($this->type == 0) {
				$response = successMessage('Your schedule was added'); 
			} else {
				$response = successMessage('Your Category has been added'); 
			} 
        } elseif (isset($error)) {
        	$response = $error;
        } else {
        	$response = infoMessage('No changes were made');
        }		   
		return $response;
	}

	function getScheduleCategory($id, $type) {
	    global $PTMPL, $LANG, $CONF, $DB, $settings;
 		if ($type == 0) {
 			 $sql = sprintf("SELECT * FROM " . TABLE_SCHEDULE . " WHERE contest = '%s'", $id);
 		} else {
 			$sql = sprintf("SELECT * FROM " . TABLE_CATEGORY . " WHERE contest = '%s'", $id);
 		}
 
	    try {
	        $stmt = $DB->prepare($sql); 
	        $stmt->execute();
	        $results = $stmt->fetchAll();
	    } catch (Exception $ex) {
	        return errorMessage($ex->getMessage());
	    } 
	    if (count($results)>0) { 
	    	return $results; 
	    }
	}

	function updateContestCover($id, $type = null) {
		// 1 == Gallery picture
		// 2 == Cover Picture

		global $DB;
		$photo = $this->photo; 

		// Prevents bypassing the FILTER_VALIDATE_EMAIL
		if ($type == 1) {
	        $sql = "UPDATE " . TABLE_CONTEST . " SET  `cover` =  :cp " 
	                . " WHERE `id` = :id"; 
		} else {
	        $sql = "UPDATE " . TABLE_CONTEST . " SET  `cover` =  :cp " 
	                . " WHERE `id` = :id"; 
		} 
		try {
			$stmt = $DB->prepare($sql);	
	        $stmt->bindValue("cp", $photo); 
	        $stmt->bindValue("id", $id);	
			$stmt->execute();
		} catch (Exception $ex) {
		   $error = errorMessage($ex->getMessage());
		}
        if ($stmt->rowCount() > 0) {
            $response = successMessage('Saved'); 
        } elseif (isset($error)) {
        	$response = $error;
        } else {
        	$response = infoMessage('No changes were made');
        }		   
	return $response;
	}

	function addContest($id) {
		// Register usage
		global $DB, $user;

		// Check if the contest is being updated from admin panel
		$title = $this->title;
		if (!isset($this->update)) {
			$admin_up = 0;
		} elseif (isset($this->update) && $this->update != 'admin') {
			$admin_up = 0;
		} else {
			$admin_up = 1;
		}

		if (!$admin_up) {
			$type = $this->type;
			$slogan = $this->slogan;
			$facebook = $this->facebook;
			$twitter = $this->twitter;
			$instagram = $this->instagram;
			$email = $this->email;
			$phone = $this->phone;	
			$safelink = safeLinks($this->title);
		}

		if (isset($this->update) && $this->update == 1) {
    		$sql = sprintf("UPDATE " . TABLE_CONTEST . " SET `safelink` = '%s', `title` =  '%s', `type` = '%s', "
           	 	. " `slogan` =  '%s', `facebook` = '%s', `twitter` = '%s', `instagram` = '%s', " 
           	 	. " `email` =  '%s', `phone` = '%s', `creator` = '%s' "  
           	 	. " WHERE `id` = %s", $safelink, $title, $type, $slogan, $facebook, $twitter, $instagram, $email, $phone, $user['username'], $this->id);
			$ver = 1;
			$response = successMessage('Contest has been updated'); 
		} elseif (isset($this->update) && $this->update == 'admin') {
    		$sql = sprintf("UPDATE " . TABLE_CONTEST . " SET `safelink` = '%s', `title` =  '%s', `status` = '%s', `featured` = '%s', `recommend` = '%s'"  
           	 	. " WHERE `id` = %s", $this->safelink, $title, $this->status, $this->featured, $this->recommend, $id);
			$ver = 2; 
			$response = successMessage('Contest has been updated'); 
		} else {
			$sql = sprintf("INSERT INTO " . TABLE_CONTEST . " (`safelink`, `title`, `cid`, `creator`, `type`, `slogan`, `facebook`, `twitter`, `instagram`, `email`, `phone`) VALUES 
				('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $safelink, $title, $user['id'], $user['username'], $type, $slogan, $facebook, $twitter, $instagram, $email, $phone); 	
			$ver = 3;
			$response = successMessage('New Contest created'); 		
		}
        $return = dbProcessor($sql, 0, $ver);
        ($return == 1 || $return == 2 || $return == 3) ? $response = $response : $response = infoMessage($return);

		return $response;
	}

	// Update the contest information
	function updateContestinfo($id, $type=0) { 
		//Type 1 == Update vote count
		global $DB, $user, $settings;
		if (isset($this->update) && $this->update == 2) {
			$country = $this->country;
			$intro = $this->intro;
			$eligibility = $this->eligibility;
			$prize = $this->prize;
			$address = $this->address;  
		}

		$userApp = new userCallback;
 		$premium_status = $userApp->premiumStatus(null, 2);
		$prem_check = $userApp->premiumStatus(null, 1); 

		// Check how many votes this contest now has
		$current_votes = ($this->getContest(0, $id)['votes']) ? $this->getContest(0, $id)['votes'] : '';
		$current_votes = ($current_votes<=0) ? 0 : $current_votes; 
		// Check if premium is on 
        if ($settings['premium']) {

        	// Check if user has an active subscription
        	if ($prem_check) {
        		// If user has any of the super premium plans
	            if ($premium_status['plan'] == 'life_plan') {
					$vote = $current_votes + $settings['premium_votes']; // premium votes (default = 5)
	            } elseif ($premium_status['plan'] == 'clead_plan') {
	            	$vote = $current_votes + $settings['premium_votes']; // premium votes (default = 5)
	            }  elseif ($premium_status['plan'] == 'cmarx_plan') {
	            	$vote = $current_votes + $settings['premium_votes']; // premium votes (default = 5)
	            } elseif ($premium_status['plan'] == 'premium_plan') {
	            	$vote = $current_votes + $settings['premium_votes']; // premium votes (default = 5)
	            } else {
	            	$vote = $current_votes+1; // regular vote  (default = 1)
	            } 
        	} else {
        		$vote = $current_votes+1; // regular vote  (default = 1)
        	}
        } else {
            $vote = $current_votes+1; // regular vote  (default = 1)
        }  

		if (isset($this->update) && $this->update == 2) {
    		$sql = sprintf("UPDATE " . TABLE_CONTEST . " SET `country` =  '%s', `intro` =  '%s', `eligibility` = '%s', "
           	 	. " `prize` = '%s', `venue` =  '%s' "  
           	 	. " WHERE `id` = %s", $country, $intro, $eligibility, $prize, $address, $this->id);
		} elseif ($type == 1 && !isset($this->update)) { 
    		$sql = sprintf("UPDATE " . TABLE_CONTEST . " SET  `votes` =  '%s' " 
	            . " WHERE `id` = %s", $vote, $id); 
		} 
		$response = successMessage('Contest has been updated'); 
        $ret = dbProcessor($sql, 0, 1); 
        return ($ret == 1) ? $response : infoMessage($ret); 
	}

	// Insert user into the contest
	function enterContest($contest_id) {
		global $DB, $CONF, $LANG, $user, $settings;
 
		$us = new userCallback;  
		$save = new siteClass;
		$noti = new msgNotif;
		$social = new social;

		// Get the contests details
		$cst = $this->getContest(0, $contest_id);

		// Get the contest creators data 
		$c_creator = $us->userData($cst['creator']); 

		// Prepare the message to send as email notification 
		$act = $us->collectUserName($user['username'], 0);
		$act_username = '<a href="'.$act['profile'].'">'.$act['username'].'</a>';
		$act_firstname = '<a href="'.$act['profile'].'">'.$act['firstname'].'</a>';
		$act_lastname = '<a href="'.$act['profile'].'">'.$act['lastname'].'</a>'; 

		$save->username = $c_creator['username'];
		$save->firstname = $c_creator['fname'];
		$save->lastname = $c_creator['lname'];
		$contest = '<a href="'.permalink($CONF['url'].'/index.php?a=contest&applications='.$cst['id']).'">'.$cst['title'].'</a>';

		// Message template
		$params = 
		    array($contest, ucfirst($c_creator['username']), $c_creator['password'], $c_creator['fname'], 
		    	$c_creator['lname'], 'Not Required', $c_creator['email'], $act_username, $act_firstname, 
		    	$act_lastname, 'action', 'action_on'
		    );

		/**
		 * The message to send
		 */		    
		$message = $save->message_template($settings['email_apply_temp'], $params); 

		/**
		 * If the user receives site wide notifications, send him one 
		 */	 
	    $social->subject = sprintf($LANG['new_application'], $cst['title']);
	    $social->type = 4;
	    $social->message = $message;
	    $social->notifier($c_creator['id'], $c_creator['id'], 'x', 0, $settings['email_apply']); 

		/**
		 * Get the applicants full details from the application form
		 */	 
		$userinfo = $this->viewApplications($contest_id, 0, $user['id']);
 
		$sql = sprintf("INSERT INTO " . TABLE_ENTER . " (`user_id`, `contest_id`, `firstname`, `lastname`, `city`, `state`, `country`, `method`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $user['id'], $contest_id, $userinfo['firstname'], $userinfo['lastname'], $userinfo['city'], $userinfo['state'], $userinfo['country'], $this->method); 
		$response = $LANG['request_successful'];
		return dbProcessor($sql, 0, $response);  			
	}

	/**
	 * Insert user into the contest
	 */	 
	function doComments($type, $master, $x) {
		// type 1 = Comment
		// type 0 = Reply

		// x 0 = Write
		// x 1 = Read Comment
		// x 2 = Read reply
		// x 3 = View post comment

		//Master: poll, post, gallery
 
		global $DB, $user; 
 		if ($x == 0) {
 			$array = $this->array;
 			list($writer_id, $receiver_id, $reply_id, $post_id, $contest_id, $comment, $type, $master) = $array;
			$sql = sprintf("INSERT INTO " . TABLE_COMMENTS . " (`writer_id`, `receiver_id`, `reply_id`, `post_id`, `contest_id`,  `comment`, `type`, `master`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')", $writer_id, $receiver_id, $reply_id, $post_id, $contest_id, $comment, $type, $master); 
			$response = 'Comment Added';
			$r_type = 0;
 		} elseif ($x == 1) {
 			$sql = sprintf("SELECT * FROM " . TABLE_COMMENTS . " WHERE receiver_id = '%s' AND contest_id = '%s' AND type = '1' ORDER by date DESC", $this->contestant_id, $this->contest_id); 
 			$r_type = 1;
 		} elseif ($x == 2) {
 			$sql = sprintf("SELECT * FROM " . TABLE_COMMENTS . " WHERE reply_id = '%s' ORDER by date DESC", $this->comment_id); 
 			$r_type = 1;
 		} elseif ($x == 3) {
 			$y = isset($this->reply_id) ? sprintf("reply_id = '%s'", $this->reply_id) : sprintf("post_id = '%s' AND !reply_id", $this->post_id);
 			$limit = isset($this->sort) && $this->sort == 'newest' ? 'DESC' : 'ASC';
 			isset($this->all) ? list($post, $reply) = $this->all : '';

 			$sql = sprintf("SELECT * FROM " . TABLE_COMMENTS . " WHERE %s AND master = '%s' ORDER by date %s", $y, $master, $limit); 
 			$r_type = 1;
 		}

		$return = dbProcessor($sql, $r_type, isset($response) ? $response : null);
		return $return;
	}

	// Load all posted comments
	function timelineComments($user_id, $post_id, $type=null) { 
		global $CONF, $LANG, $userApp, $marxTime, $user;

		$action = new actions;

		$this->post_id = $post_id;
		isset($_GET['sort']) && $_GET['sort'] == 'newest' ? $this->sort = 'newest' : $this->sort = 'oldest';
		$get_comments = $this->doComments(1, 'post', 3);

	    // Show Comments
		$read_ = '';

		// Sort the posts
		$slink = !trueAjax() ? permalink($CONF['url'].'/index.php?a='.$_GET['a'].'&u='.$_GET['u'].'&read='.$_GET['read'].'&sort=%s#comment') : '';
		if (isset($_GET['sort']) && $_GET['sort'] == 'newest') {
			$sort = '<a href="'.sprintf($slink, 'oldest').'" class="dropdown-item">'.$LANG['sort_oldest'].' <i class="fa fa-sort-desc p-1"></i> </a>';
		} else {
			$sort = '<a href="'.sprintf($slink, 'newest').'" class="dropdown-item">'.$LANG['sort_newest'].' <i class="fa fa-sort-asc p-1"></i> </a>';
		}
		// Show old comments
	    if (empty($type)) {
	        if ($get_comments) {
	        	foreach ($get_comments as $key) {
	        		$userApp->user_id = $key['writer_id'];
	                $us = $userApp->collectUserName(null, 0, $key['writer_id']);

		            if ($user['id'] == $key['writer_id']) {
		                $delete = '
		                <a class="dropdown-item" onclick="delete_the('.$key['id'].', 9)">'
		                .$LANG['delete'].' <i class="fa fa-trash p-1"></i> </a>';
		            } else {
		                $delete = '<div class="px-3">'.$LANG['hello'].'</div>';
		            }
		            $time = '<small><span><i class="fa fa-clock-o "></i> '.$marxTime->timeAgo(strtotime($key['date'])).'</span></small>';
		            $loctn = '<small><span><i class="fa fa-clock-o "></i> '.$us['address'].'</span></small>';
	 				
	 				// Show replies
					$this->reply_id = $key['id'];
					$reply_block = '';
					$get_reply = $this->doComments(1, 'post', 3);
					if ($get_reply) {
					    foreach ($get_reply as $k) {
					    	$u = $userApp->collectUserName(null, 0, $k['writer_id']);
					    	$t = '<small><span><i class="fa fa-clock-o "></i> '.$marxTime->timeAgo(strtotime($k['date'])).'</span></small>';
		            		$l = '<small><span><i class="fa fa-clock-o "></i> '.$u['address'].'</span></small>';

		            		$reply_block .= '
					    	<div class="p-1 px-4 reply-comment">
				      			<div class="text-info mx-2">
							      	<span class="commentors-avatar">
							        	<img class="rounded-circle" src="'.$CONF['url'].'/uploads/faces/'.$u['photo'].'" alt="'.$u['username'].'_Photo">
							        </span>
							        <span class="comment-user">
							        	<p><a href="'.$us['profile'].'" class="blue-grey-text">'.$u['fullname'].'</a><p>
					        			'.$l.' &nbsp; '.$t.'
					        		</span>
					      		</div>
					    	 	'.$action->decodeMessage($k['comment'], 1).'
					    	</div>';
					    }					
					}			    

				    if (!$key['reply_id']) {
		        		$comment_block = '
					    <div class="p-2 px-4">'.$action->decodeMessage($key['comment'], 1).'						       
						  <a class="text-info" onclick="hidden_form('.$key['id'].')">'.$LANG['reply'].'</a>

							  <div class="form-inline md-form" id="form_'.$key['id'].'" style="display: none;"> 
							    <input id="reply_'.$key['id'].'" class="form-control form-control-sm m-2 col-sm-9" type="text" placeholder="Reply">
							    <button onclick="write_real_comment('.$user['id'].', '.$user_id.', '.$post_id.', 0, '.$key['id'].')" class="btn btn-sm btn-info">Reply</button>
							  </div>
							  <div id="new-reply_'.$key['id'].'"></div> 
					    </div>';			    	
				    }
	            	
		            $read_ .= '
				    <div class="m-2 flex" id="comment_'.$key['id'].'">
				      <div class="text-info mx-2">
	                    <button class="btn btn-flat btn-flat-icon float-right" type="button" data-toggle="dropdown" aria-expanded="false">
	                      <em class="fa fa-ellipsis-h"></em>
	                    </button>	
	                    <div class="dropdown-menu dropdown-scale dropdown-menu-right" role="menu" style="position: absolute; transform: translate3d(-136px, 28px, 0px); top: 0px; left: 0px; will-change: transform;">
	                      '.$delete.'
	                      '.$sort.'
	                    </div>
				      	<span class="commentors-avatar">
				        	<img class="rounded-circle" src="'.$CONF['url'].'/uploads/faces/'.$us['photo'].'" alt="'.$us['username'].'_Photo">
				        </span>
				        <span class="comment-user">	
				        	<p><a href="'.$us['profile'].'" class="blue-grey-text">'.$us['fullname'].'</a></p>
				        	'.$loctn.' &nbsp; '.$time.'
				        </span>
				    </div>
		            <div id="comment"></div>
					<div class="border-bottom p-2">
					      '.$comment_block.'
					      '.$reply_block.'
					    </div>
					</div>';	            		 
	        	}
	        }
	        $read_ .= '<div id="new-comment"></div>';

	    // If you just posted a new comment
	    } else {
	    	$t = '<small><span><i class="fa fa-clock-o "></i> '.$marxTime->timeAgo(strtotime('Now')).'</span></small>';
			$l = '<small><span><i class="fa fa-clock-o "></i> '.$this->sender['address'].'</span></small>';

			if ($this->type == 0) { 
				$read_ = '
		    	<div class="p-1 px-4 reply-comment">
		  			<div class="text-info mx-2">
				      	<span class="commentors-avatar">
				        	<img class="rounded-circle" src="'.$CONF['url'].'/uploads/faces/'.$this->sender['photo'].'" alt="'.$this->sender['username'].'_Photo">
				        </span>
				        <span class="comment-user">
				        	<p><a href="'.$this->sender['profile'].'" class="blue-grey-text">'.$this->sender['fullname'].'</a><p>
		        			'.$l.' &nbsp; '.$t.'
		        		</span> 
		      		</div>
		    	 	'.$action->decodeMessage($this->comment, 1).'
		    	</div>';	 	
			} else {
				$read_ = ' 
				<div class="border-bottom p-2" id="comments">
				    <div class="m-2 flex">
				      <div class="text-info mx-2">
				      	<span class="commentors-avatar">
				        	<img class="rounded-circle" src="'.$CONF['url'].'/uploads/faces/'.$this->sender['photo'].'" alt="'.$this->sender['username'].'_Photo">
				        </span>
				        <span class="comment-user">	
				        	<p><a href="'.$this->sender['profile'].'" class="blue-grey-text">'.$this->sender['fullname'].'</a></p>
				        	'.$l.' &nbsp; '.$t.'
				        </span>	 
				      </div>
				      <div class="p-2 px-4">'.$action->decodeMessage($this->comment, 1).'</div> 
				    </div>
				</div>'; 	
			}
	    }

        return $read_;		
	}


	// Approve the users application into the contest
	function approveApplication() {
		global $user;
		$userApp = new userCallback;

 		$contest_id = $this->contest_id;
 		$contestant_id = $this->contestant_id;
 		$name = $this->name;
 		$city = $this->city;
 		$state = $this->state;
 		$country = $this->country;

		$sql = sprintf("INSERT INTO " . TABLE_CONTESTANT . " (`contestant_id`, `contest_id`, `name`, `city`, `state`, `country`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s')", $contestant_id, $contest_id, $name, $city, $state, $country); 
		
		$return = dbProcessor(isset($sql)?$sql:'', 0, 1);
		if ($return == 1) { 
			//Remove user from enter table after adding to contestants table
			$this->declineApplication($contestant_id, $contest_id); 
			
			// Check if the user role is set as contestant if not set it
			$userApp->user_id = $contestant_id;
			$data = $userApp->userData(NULL, 1)[0];
			if ($data['role'] != 'contestant' && $data['role'] != 'agency') {
				$sql = sprintf("UPDATE " . TABLE_USERS . " SET `role` = 'contestant' WHERE id = '%s'", $contestant_id); 
				dbProcessor($sql, 0);
			}
			$response = 'You successfully approved '.$name;
		}  
		return $response;			
	}

	// See all approved contestants
	function getApprovedList($start=NULL, $perpage=NULL) {
		global $user;

		$extra = ($perpage) ? sprintf('ORDER BY id DESC LIMIT %s, %s', $start, $perpage) : '';
 
 		$contest_id = $this->contest_id; 

		$sql = sprintf("SELECT * FROM " . TABLE_CONTESTANT . " WHERE contest_id = '%s' %s", $contest_id, $extra);
		return dbProcessor(isset($sql)?$sql:'', 1);			
	}

	// view or update the contestants table
	function contestContestants($type=null) {
		global $user, $settings, $userApp;

 		$premium_status = $userApp->premiumStatus(null, 2);
		$prem_check = $userApp->premiumStatus(null, 1);


 		$contest_id = $this->contest_id;
 		$contestant_id = $this->contestant_id;

		$this->contest_id = $contest_id;
		$this->contestant_id = $contestant_id;
		$contestant = $this->getUsersCurrent(1)[0];

 		$current_votes = $contestant['votes'];  

		// Check if premium is on 
        if ($settings['premium']) {

        	// Check if user has an active subscription
        	if ($prem_check) {
        		// If user has any of the super premium plans
	            if ($premium_status['plan'] == 'life_plan') {
					$vote = $current_votes + $settings['premium_votes']; // premium votes (default = 5)
	            } elseif ($premium_status['plan'] == 'clead_plan') {
	            	$vote = $current_votes + $settings['premium_votes']; // premium votes (default = 5)
	            }  elseif ($premium_status['plan'] == 'cmarx_plan') {
	            	$vote = $current_votes + $settings['premium_votes']; // premium votes (default = 5)
	            } elseif ($premium_status['plan'] == 'premium_plan') {
	            	$vote = $current_votes + $settings['premium_votes']; // premium votes (default = 5)
	            } else {
	            	$vote = $current_votes+1; // regular vote  (default = 1)
	            } 
        	} else {
        		$vote = $current_votes+1; // regular vote  (default = 1)
        	}
        } else {
            $vote = $current_votes+1; // regular vote  (default = 1)
        }

		$sql = sprintf("UPDATE " . TABLE_CONTESTANT . " SET  `votes` =  '%s' " 
	            . " WHERE `contest_id` = %s AND `contestant_id` = %s", $vote, $contest_id, $contestant_id); 

		$msg = 'Thanks for your support, '.$contestant['name'].' now has '.$vote.' votes.'; 
		$response = array('message' => $msg, 'count' => $vote);
		$return = dbProcessor(isset($sql)?$sql:'', 0, 1);
		$return == 1 ? $this->updateContestinfo($contest_id, 1) : '';
		return $return == 1 ? $response : $return;  
		
	}

	// See all who have already voted
	function getVoters($type, $contest_id='', $contestant_id='') {
		// type = '1' : Fetch voters data
		// type = '2' : Approve the users vote from social
		// type = '3' : Update the voter info
		// type = '4' : Select all votes for the contest
		// type = '0' : Add to the voters data
		global $user; 
 		 
 		if ($type == 1) {
 			$sql = sprintf("SELECT * FROM " . TABLE_VOTERS . " WHERE contest_id = '%s' AND voter_id = '%s'", $contest_id, $user['id']); 
 			$t = 1;
 		} elseif ($type == 2) {
 			$sql = sprintf("INSERT INTO " . TABLE_VOTERS . " (`contest_id`, `voter_id`, `social`) VALUES ('%s', '%s', '%s')", $contest_id, $user['id'], 1);
 			$t = 0;
 		} elseif ($type == 3) {
 			$sql = sprintf("UPDATE " . TABLE_VOTERS . " SET  `voted` = '%s', `contestant_id` = '%s'" 
	            . " WHERE `voter_id` = %s", 1, $contestant_id, $user['id']); 
 			$t = 0;
 		} elseif ($type == 0) {
 			// check if user has voted before
 			$vb = $this->getVoters(1, $contest_id);
 			if ($vb) {
 				$c = $vb['count']+1;
 				$sql = sprintf("UPDATE " . TABLE_VOTERS . " SET  `count` = '%s'"
 				." WHERE `voter_id` = %s", $c, $user['id']); 
 			} else {
 				$sql = sprintf("INSERT INTO " . TABLE_VOTERS . " (`contestant_id`, `contest_id`, `voter_id`, `voted`, `count`) VALUES ('%s', '%s', '%s', '%s', '%s')", $contestant_id, $contest_id, $user['id'], 1, 1);		
 			}
 			$t = 0;
 		} 
 		$return = $type == 1 ? dbProcessor($sql, $t)[0] : dbProcessor($sql, $t);
 		$type == 3 ? $this->getVoters(0, $contest_id) : '';			
 		return $return;
	}

	function myVotes() {
		// Type = '2' : Fetch all votes from this voters
	    global $PTMPL, $LANG, $CONF, $settings, $user;

	    // Limit clause to enable pagination
		if (isset($this->limit)) {
			$limit = sprintf('ORDER BY date DESC LIMIT %s, %s', $this->start, $this->limit);
		} else {$limit = '';}

	    $sql = sprintf("SELECT * FROM " . TABLE_VOTERS . " WHERE voter_id = '%s' %s", $user['id'], $limit); 
	    $results = dbProcessor($sql, 1);
  
    	return $results;  
	}

	// // See all all contest entered by this contestant
	function getUsersCurrent($type = 0, $n=null) {
		// Type = 0 : Get all Contestant information
		// Type = 1 : Get contestant data from id
		// Type = 2 : Get contestant data from username

		//n 1: No limit

		global $user;

		$limit = ($n == 1) ? '' : 'LIMIT 6' ; 
 		$contestant_id = (isset($this->contestant_id)) ? $this->contestant_id : '';
 		$userApp = new userCallback;

 		if ($type == 0) {
 			$sql = sprintf("SELECT * FROM " . TABLE_CONTESTANT . " WHERE contestant_id = '%s' ORDER BY votes DESC, date ASC %s", $contestant_id, $limit);
 		} elseif ($type == 1) {
 			$contest_id = $this->contest_id; 
 			$sql = sprintf("SELECT * FROM " . TABLE_CONTESTANT . " WHERE 1 AND contestant_id = '%s' AND contest_id = '%s'", $contestant_id, $contest_id);
 		} elseif ($type == 2) { 
 			$contest_id = $this->contest_id; 
 			$data = $userApp->userData($this->username);
 			$sql = sprintf("SELECT * FROM " . TABLE_CONTESTANT . " WHERE 1 AND contestant_id = '%s' AND contest_id = '%s'", $data['id'], $contest_id);
 		}
		return dbProcessor($sql, 1, 1);		
	}	

	// Decline user application or Remove user from the contest
	function declineApplication($id, $contest_id, $response=null) { 
		$sql = sprintf("DELETE FROM " . TABLE_ENTER . " WHERE `contest_id` = '%s' AND `user_id` = '%s'", $contest_id, $id);
		$response = 'Application request was declined!'; 
		return dbProcessor($sql, 0, $response); 	 
	}	

	// Remove user from the contest
	function removeContestant($id, $contest_id, $response=null) { 
		$sql = sprintf("DELETE FROM " . TABLE_CONTESTANT . " WHERE `contest_id` = '%s' AND `contestant_id` = '%s'", $contest_id, $id);
		$response = 'Contestant was removed from the contest!'; 
		return dbProcessor($sql, 0, $response); 	 
	}

	function viewApplications($contest_id, $type, $user_id = '') {
	// Type = 4, Check if logged user is signed up to the contest id
	// Type = 3, View all contests entered by logged user	
	// Type = 2, View all applications for (useful in admin mode)
	// Type = 1, View applications for this contest id
	// Type = 0, View Application

		global $DB, $user; 

		if (isset($this->limit)) {
			$limit = sprintf('ORDER BY timestamp DESC LIMIT %s, %s', $this->start, $this->limit);
		} else {$limit = '';}

		if ($type == 0) {
			$sql = sprintf("SELECT * FROM " . TABLE_APPLY . " WHERE user_id = '%s'", $user_id);
		} elseif ($type == 1) {
			$sql = sprintf("SELECT * FROM " . TABLE_ENTER . " WHERE contest_id = '%s' %s", $contest_id, $limit);
		} elseif ($type == 2) {
			$sql = sprintf("SELECT * FROM " . TABLE_APPLY . " WHERE 1 ORDER BY id DESC");
		} elseif ($type == 3) {
			$sql = sprintf("SELECT * FROM " . TABLE_ENTER . " WHERE user_id = '%s' %s", $user['id'], $limit);
		} else {
			$sql = sprintf("SELECT * FROM " . TABLE_ENTER . " WHERE contest_id = '%s' AND user_id = '%s'",$contest_id , $user_id);
		}
		$results = dbProcessor($sql, 1); 

    	if ($user_id == NULL) {
    		 return $results;
    	} else {
    		return $results[0]; 
	    }
	}	

	// Decline user application or Remove user from the contest
	function deleteContest($id) { 
		// First get the contest details	 
		$contest = $this->getContest(0, $id);
		// Then delete all related images from storage
		($contest['cover']) ? deleteImages($contest["cover"], 4) : '';  

		$sql = sprintf("DELETE FROM " . TABLE_CATEGORY . " WHERE `contest` = '%s'", $id);
		dbProcessor($sql, 0); 
		$sql = sprintf("DELETE FROM " . TABLE_COMMENTS . " WHERE `contest_id` = '%s'", $id);
		dbProcessor($sql, 0); 
		$sql = sprintf("DELETE FROM " . TABLE_CONTESTANT . " WHERE `contest_id` = '%s'", $id);
		dbProcessor($sql, 0); 
		$sql = sprintf("DELETE FROM " . TABLE_ENTER . " WHERE `contest_id` = '%s'", $id);
		dbProcessor($sql, 0); 
		$sql = sprintf("DELETE FROM " . TABLE_GENERATE . " WHERE `contest_id` = '%s' AND `claimed` = '0'", $id);
		dbProcessor($sql, 0); 
		$sql = sprintf("DELETE FROM " . TABLE_GIFT . " WHERE `contest` = '%s'", $id);
		dbProcessor($sql, 0); 
		$sql = sprintf("DELETE FROM " . TABLE_GIFTED . " WHERE `contest_id` = '%s'", $id);
		dbProcessor($sql, 0); 
		$sql = sprintf("DELETE FROM " . TABLE_SCHEDULE . " WHERE `contest` = '%s'", $id);
		dbProcessor($sql, 0); 
		$sql = sprintf("DELETE FROM " . TABLE_VOTERS . " WHERE `contest_id` = '%s'", $id);
		dbProcessor($sql, 0); 
		$sql = sprintf("DELETE FROM " . TABLE_CONTEST . " WHERE `id` = '%s'", $id);
		$response = 'Contest was deleted'; 
		$return = dbProcessor($sql, 0, 1);
		return ($return == 1) ? successMessage($response) : infoMessage($return);
	}	
}

// Function to process all database calls (Was added later during production, 
// all functions will be migrated in future versions)
function dbProcessor($sql=0, $type=0, $response='') {
	// Type 0 = Insert, Update, Delete
	// Type 1 = Select 
	// Type 100 = Just return the response

	global $DB;
	if ($type == 100) {
		$response = $response;
	} else {
		try {
			$stmt = $DB->prepare($sql);	 	
			$stmt->execute();
		} catch (Exception $ex) {
		   $error = errorMessage($ex->getMessage());
		}
		if ($type == 0) {
			if ($stmt->rowCount() > 0) {  
				return $response;
			} elseif (isset($error)) {
				return $error;
			} else {
				return 'No changes were made';
			}		 
		} elseif ($type == 1) {
			$results = $stmt->fetchAll();
		    if (count($results)>0) { 
		    	return $results; 
		    } elseif (isset($error)) {
		    	return $error;
		    }
		}		
	} 
}
