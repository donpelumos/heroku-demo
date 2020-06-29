<?php
 /**
  * Script to get site option details,all
  *
  * It accepts the parameter user_id,user_type(optional, default is 3),key_value_format(optional, if set, the data returned will be {key:value} format),get_payment_gateway_creds(optional, if added, means user_type is 1(or 3) and request is to get gateway keys) using GET
  * Please note that this file only helps handle, determine and prevent any empty parameter
  * If a required parameter is empty, it'll return false
  * You should make sure all those are handled on your end
  * 
  * @return JSON success on success or Error status on failure.
  * @author Precious Omonzejele <omonze@peepsipi.com>
  */
  header("Content-Type: application/json");
  require "../inc/_config.php";
  require "../vendor/autoload.php";
 
  $user_id = isset($_GET["user_id"]) ? $_GET["user_id"] : false;
  $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : 3;
  $get_payment_gateway_creds = isset($_GET["get_payment_gateway_creds"]) ? true : false;
  $key_value_format = isset($_GET["key_value_format"]) ? true : false;

  if( !($user_id) ){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
  $dbq = new Query($con);
  $dbq->set_fetch_mode("assoc");
 
 // Make sure when gatewaycreds is needed, user type is 1 or 3(since admin na egbon), if not, user type is 3.
 if( ( $get_payment_gateway_creds && ( $user_type != 1 || $user_type != 3 ) ) || ( !$get_payment_gateway_creds && $user_type != 3 ) ){
    pekky_set_failure_state( 0, 'Access denied, invalid user type.' );
    exit(pekky_print_json_state());
 }

 //check if user exists
 if( !user_exists($dbq,$user_id,$user_type) ){
    pekky_set_failure_state( 0, 'User doesn\'t exist' );
    exit(pekky_print_json_state());
 }
//carry on, just call my function, no stress :) life's good like LG
$condition = '';
if( $get_payment_gateway_creds ){// Add condition to get only rave stuff, they should start with flw :)
	// Getting all the sorting from the db direct is faster than having to loop via script.
	$condition = " WHERE option_name LIKE '%flw_%' ";
}

if( !$dbq->get( "SELECT id,option_name,option_value FROM site_options".$condition ) ){
  pekky_set_failure_state( -1, $dbq->err_msg );
  exit(pekky_print_json_state());//end the program
}

$records = $dbq->record;

if( $key_value_format ){ // Produce in {key:value} format.
	$new_records = array();
	foreach( $records as $record ){
	$new_records[ $record['option_name'] ] = $record['option_value'];
	}
	$records = $new_records; 
}

// We hope it all went well, can't really determine, return true.
pekky_set_success_state();
pekky_print_json_state($records,'data',false);
