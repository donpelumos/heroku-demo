<?php
/**
 * Script to send firebase notification to the user device
 *
 * It accepts the parameter user_firebase_id,notification_message,notification_title using GET
 * Note: I like to resist the urge to shalaye, but its not Precious that wrote this script, edited some parts sha :)
 *
 * @return JSON
 * @author Pelumi Oyefeso, Precious Omonzejele
 */

header("Content-Type: application/json");
require "../inc/_config.php";

$firebase_id = isset($_GET['user_firebase_id']) ? strip_tags(trim($_GET['user_firebase_id'])) : false;
$notification_message = isset($_GET['notification_message']) ? strip_tags(trim($_GET['notification_message'])) : false;
$notification_title = isset($_GET['notification_title']) ? strip_tags(trim($_GET['notification_title'])) : false;

if( !( $firebase_id && $notification_message && $notification_title ) ){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
}

$curl = curl_init();
$auth_token = "AAAAtxTOoFU:APA91bHb-hwdoexhvK0MQVYUt7Rsnu9DBGUuebLViOn2o5VR7v1La_aJ4DZooeAj4Jvm3V34CGYz6DCkUeZMrotL1Cla_GOM8hAzVhxv5svbrbQecheMJPLy69bU0HBN4UxFow9RR17X";
$post_data = array(
    'to' => $firebase_id,
    'priority' => 'high',
    'notification' => array('sound' => 'enabled', 'title' => $notification_title, 'body' => $notification_message, 'tag' => '1')
);

// Set some options - we are passing in a useragent too here
curl_setopt_array($curl, array(
    CURLOPT_HTTPHEADER => array(
        'Authorization:key='. $auth_token,
        'Content-Type: application/json'
    ),
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_URL => 'https://fcm.googleapis.com/fcm/send',
    CURLOPT_POST => TRUE,
    CURLOPT_POSTFIELDS => json_encode($post_data)
));

// Send the request & save response to $resp
$response = curl_exec($curl);

if( $response === FALSE ){
    pekky_set_failure_state( -1, curl_error($curl) );
    exit(pekky_print_json_state());//end the program
}

$response_data = json_decode($response, TRUE);

pekky_set_success_state();
pekky_print_json_state( ['data' => $response_data] );

// Close request to clear up some resources
curl_close($curl);
