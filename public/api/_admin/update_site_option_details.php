<?php
 /**
  * Script to update site option details
  *
  * It accepts the parameter user_id,meta(this should be in json format) using GET
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
  $meta = isset($_GET["meta"]) ? $_GET["meta"] : false;
  if(!($user_id && $meta)){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  //check if the order meta has a correct format
 $meta_array = json_decode(stripslashes($meta),true);
  if(json_last_error() != 0){
    pekky_set_failure_state(0,"Incorrect JSON format for param 'meta'");
    exit(pekky_print_json_state());//end the program
  }
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 //check if user exists
 if(!user_exists($dbq,$user_id,3) ){
    pekky_set_failure_state(0,'User doesn\'t exist');
    exit(pekky_print_json_state());
 }
//carry on, just call my function, no stress :) life's good like LG
handle_site_option_meta($dbq,$meta_array,$action = 'insert');
//we hope it all went well, can't really determine, return true
pekky_set_success_state();
pekky_print_json_state();
