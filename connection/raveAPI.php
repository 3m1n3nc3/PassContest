<?php
require_once(__DIR__ .'/../includes/autoload.php');
global $PTMPL, $LANG, $SETT, $DB, $user, $settings;

$ravemode = ($settings['rave_mode'] ? 'api.ravepay.co' : 'ravesandboxapi.flutterwave.com'); // Check if sandbox is enabled
$public_key = $settings['rave_public_key']; // Rave API Public key
$private_key = $settings['rave_private_key']; // Rave API Private key 

if (isset($_SESSION['type']) && $_SESSION['type'] == 'credit') {
    $url = $SETT['url'].'/index.php?a=credit&type=successful&%s';
    $fail_url = $SETT['url'].'/index.php?a=credit&type=canceled&status=%s&message=%s';
} else {
    $url = $SETT['url'].'/index.php?a=premium&type=successful&%s';
    $fail_url = $SETT['url'].'/index.php?a=premium&type=canceled&status=%s&message=%s';    
}

(!isset($_SESSION)) ? session_start() : $echo = ''; 
// error_reporting(0); 

    $txrefer   = $_SESSION['txref'];
    $amount    = $_SESSION['amount']; //Correct Amount from Server 
    $currency = $_SESSION['currency']; //Correct Currency from Server

    $response  = $_GET['resp'];

    if (isset($txrefer)) {  

        //Connect with Verification Server
        $query = array(
            "SECKEY" => $private_key,
            "txref" => $txrefer 
        );

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
            echo '<br/> Curl Error: '.$error_msg.'<br/>';
        }
        curl_close($ch);

        $resp = json_decode($response, true);
        $return_query = http_build_query($resp);

   //      print_r($resp);

        $paymentStatus = $resp['data']['status'];
        $chargeResponsecode = $resp['data']['chargecode'];
        $chargeAmount = $resp['data']['amount'];
        $chargeCurrency = $resp['data']['currency'];
        $message = $resp['message'];

        if ($paymentStatus == 'successful' && ($chargeResponsecode == "00") && ($chargeAmount >= $amount)  && ($chargeCurrency == $currency)) { 

            header("Location: ".sprintf($url, $return_query)); 
          // transaction was successful...
             // please check other things like whether you already gave value for this ref
          // if the email matches the customer who owns the product etc
          //Give Value and return to Success page
        } else {        
            
            //Dont Give Value and return a failure message to the upgrade page
            header("Location: ".sprintf($fail_url, $paymentStatus, $message));
        }
    } else {
      die('No reference supplied');
    }

?> 
 
