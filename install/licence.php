<?php 
    session_start(); 
	require_once('include/shared.inc.php');    
    require_once('include/settings.inc.php');
	require_once('include/functions.inc.php');
	require_once('include/languages.inc.php');
 
	function activator($code) {
	    $url = base64_decode('aHR0cHM6Ly9hcGkucGFzc2NvbnRlc3QuY2Yv');
	    $data = "a=licence&token=".$code."&server=".$_SERVER['HTTP_HOST'];
	    $curl = curl_init();
	    curl_setopt($curl, CURLOPT_URL, $url);
	    curl_setopt($curl, CURLOPT_POST, 1);
	    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 5.1; rv:5.0) Gecko/20100101 Firefox/5.0 Firefox/5.0');
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	    curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
	    $contents = curl_exec($curl);
	    $status = curl_getinfo($curl); 
	    curl_close($curl);
	    if($status['http_code'] == 200) { 
	        $contents = json_decode($contents, true);
	        if($contents['error']) {
	            return $contents['error']['message'].'! (Error Code #'.$contents['error']['code'].')';
	        }
	        return true;
	    } else {
	        return "Error Processing Request";
	    }
	}

	$message = '';
	$button = '						
	<div class="username">
		<span class="username">Licence Key</span>
		<input type="text" name="key" class="name" placeholder="" required="">
		<div class="clearfix"></div>
	</div> 
	<button type="submit" name="activate" class="btn btn-block btn-light rounded border border-danger shadow">'.lang_key('activate').'</button>';

	if (isset($_POST['activate'])) { 
		$_SESSION['licence'] = $_POST['key'];
		$e = activator($_POST['key']); 
		$message = '	
		<p><div class="w3-rest w3-center"> <div class="alert alert-%s text-center" role="alert"> <strong>%s: </strong>%s </div></div></p>';
		$message = $e == 1 ? sprintf($message, 'success', 'Success', lang_key('validated_successfully')) : sprintf($message, 'danger', lang_key('error'), $e);
	} elseif (isset($_POST['install'])) {
		$_SESSION['verified'] = true;
		header('Location: /install');
	}
	if (isset($e) && $e == 1) {
		$button = '<button type="submit" name="install" class="btn btn-block btn-success rounded border shadow">'.lang_key('install').'</button>';
	}
?>
<!DOCTYPE HTML>
<html>
	<head>
		<title><?php echo lang_key('licence_screen'); ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />  
		<!-- Bootstrap Core CSS -->
		<link href="templates/<?php echo EI_TEMPLATE; ?>/css/bootstrap.css" rel='stylesheet' type='text/css' />
		<link href="templates/<?php echo EI_TEMPLATE; ?>/css/moriss.css" rel='stylesheet' type='text/css' />
		<link href="templates/<?php echo EI_TEMPLATE; ?>/css/adino.css" rel='stylesheet' type='text/css' />   
	</head> 
	<body>
		<div class="main-wthree align-middle">
			<div class="container">
				<div class="sin-w3-agile">
					<h2><?php echo EI_APPLICATION_NAME.' '.EI_APPLICATION_VERSION.' '.lang_key('licence_screen'); ?></h2>
					<?php echo $message;?>
					<form action="" method="post">
						<?php echo $button;?>
					</form> 
				</div>
			</div>
		</div>
	</body>
</html>
