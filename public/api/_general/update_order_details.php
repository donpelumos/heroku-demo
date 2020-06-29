<?php
 /**
  * Script to update order details
  *
  * It accepts the parameter user_id,user_type,order_id,meta(this should be in json format) using GET
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
  $order_id = isset($_GET["order_id"]) ? $_GET["order_id"] : false;
  $order_meta = isset($_GET["meta"]) ? $_GET["meta"] : false;
  $user_type = isset($_GET["user_type"]) ? $user_type : 1;
  if(!($user_id && $order_meta && $order_id)){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  //check if the order meta has a correct format
 $order_meta_array = json_decode(stripslashes($order_meta),true);
  if(json_last_error() != 0){
    pekky_set_failure_state(0,"Incorrect JSON format for param 'meta'");
    exit(pekky_print_json_state());//end the program
  }
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 //check if user exists
 if(!user_exists($dbq,$user_id,$user_type) ){
    pekky_set_failure_state(0,'User doesn\'t exist');
    exit(pekky_print_json_state());
 }
//check if order exists 
if(!order_exists($dbq,$order_id)){
    pekky_set_failure_state(0,'Order doesn\'t exist');
    exit(pekky_print_json_state());
}
//check if it's not admin, so we make sure the user owns the order
if(!($user_type == 3)){//not an admin
    if(!user_owns_order($dbq,$user_id,$user_type,$order_id)){
        pekky_set_failure_state(0,'Access denied to user, not associated with order.');
        exit(pekky_print_json_state());            
    }
}
else{//its an admin
 //check if admin has priviledges to take this action
 if(!is_allowed_admin_type($dbq,$user_id)){
    pekky_set_failure_state(1,'Sorry, authorisation denied');
    exit(pekky_print_json_state());
   }
}
//carry on, just call my function, no stress :) life's good like LG
handle_order_meta($dbq,$order_id,$order_meta_array,$action = 'insert');
//we hope it all went well, can't really determine, return true
pekky_set_success_state();
pekky_print_json_state();

