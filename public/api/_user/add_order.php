<?php
 /**
  * Script to add an order
  *
  * It accepts the parameter user_id,meta(this should be in json format),dont_send_mail using GET
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
  $order_meta = isset($_GET["meta"]) ? $_GET["meta"] : false;
  $n_send_mail = isset($_GET["dont_send_mail"]) ? true : false;
  if(!($user_id && $order_meta)){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  //check if the order meta has a correct format
 $order_meta_array = json_decode(stripslashes($order_meta),true);
  if(empty($order_meta_array)){//empty meta, not allowed
    pekky_set_failure_state(0,"empty JSON data for param 'meta'");
    exit(pekky_print_json_state());//end the program   
  }
  if(json_last_error() != 0){
    pekky_set_failure_state(0,"Incorrect JSON format for param 'meta'");
    exit(pekky_print_json_state());//end the program
  }
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 //check if user exists
 if(!user_exists($dbq,$user_id) ){
    pekky_set_failure_state(0,'User doesn\'t exist');
    exit(pekky_print_json_state());
 }
//generate auth code
$_code = md5(session_id().time());  
//you have to reshuffle it since we're reducing it to 6 digits, to avoid repitition
$letters = new Letters();
$_code = $letters->backwards($_code);
 $order_code = substr($_code, 0, 6);
//now add to the db
$data = array('user_id'=>'?','status'=>''.ORDER_HOLD_CODE.'','date_time'=>'NOW()');
$binding = array($user_id);
if(!$dbq->add('orders',$data,$binding)){//error
  pekky_set_failure_state(-1,$dbq->err_msg);
  exit(pekky_print_json_state());
}
//last_insert id
$order_id = $dbq->last_insert_id;
##############################################
//add the auth code to the order meta
$ext = array('order_auth'=>$order_code);
$order_meta_array = array_merge($order_meta_array,$ext);
//after adding orders, add the order_meta
handle_order_meta($dbq,$order_id,$order_meta_array);
pekky_set_success_state();
//set up email
if(!$n_send_mail){
require '../inc/_mail.php';
//for the user
if(order_mail_template($dbq,$order_id,1,$user_id))
  pekky_add_array_to_print(['mail_sent_to_user'=>'true']);
else
 pekky_add_array_to_print(['mail_sent_to_user'=>'false']);
//for the admin
$t_dbq = new Query($con);//did this cause caling the order_mail_template a second time, the query wasnt working, returning false for i dont know why
if(order_mail_template($t_dbq,$order_id,1,$user_id,1,true))
  pekky_add_array_to_print(['mail_sent_to_admin'=>'true']);
else
 pekky_add_array_to_print(['mail_sent_to_admin'=>'false']);

}
pekky_print_json_state(['order_id'=>$order_id,'order_auth'=>$order_code]);
