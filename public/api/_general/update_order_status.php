<?php
/**
  * Script to update order status
  *
  * It accepts the parameter order_id,status(can be int, or valid phrase),user_id,user_type,order_auth(Optional, only for dispatcher changing order to complete),dont_send_mail using GET,
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
 
  $order_id = isset($_GET["order_id"]) ? $_GET["order_id"] : false;
  $status = isset($_GET["status"]) ? $_GET["status"] : false;
  $user_id = isset($_GET["user_id"]) ? $_GET["user_id"] : false;
  $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : 1;
  $order_auth = isset($_GET["order_auth"]) ? $_GET["order_auth"] : false;
  $n_send_mail = isset($_GET["dont_send_mail"]) ? true : false;
 
  if(!($order_id && $status && $user_id && $user_type)){
      pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }

  $user_db = user_type($user_type);
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 $dbq->set_fetch_mode("assoc");

  if(!$dbq->get(query_live("SELECT id FROM orders WHERE id = ? "),[$order_id])){
    pekky_set_failure_state(-1,$dbq->err_msg);
    exit(pekky_print_json_state());//end the program   
  }
  //does order exist
  if($dbq->row_count != 1){
    pekky_set_failure_state(0,"Order with Id ".$order_id." doesn't exist");
    exit(pekky_print_json_state());//end the program   
  }
//everything went well, carry on
//convert status , just in case
$o_status = get_order_status($status);
if($o_status == false){
  pekky_set_failure_state(0,"Couldn't process status value,please check properly");
  exit(pekky_print_json_state());//end the program   
}
$dbq->get(query_live("SELECT id FROM orders where id = ? AND status = ?"),[$order_id,$o_status]);
if($dbq->row_count == 1){//status being changed to the old status, same thing, no need
  pekky_set_failure_state(1,"New status is the same as old status,no need");
  exit(pekky_print_json_state());//end the program   
}
//check if it's an admin, then  make sure the admin has priviledges 
if($user_type == 3){
  if(!is_allowed_admin_type($dbq,$user_id)){
    pekky_set_failure_state(0,"Access denied, this admin cannot change an order status");
    exit(pekky_print_json_state());//end the program   
  }  
}
//check if the order want's to be changed to cancelled
if($user_type == 1){
//check if it's a normal user, and he's the owner of the order,
if(!user_owns_order($dbq,$user_id,$user_type,$order_id)){
  pekky_set_failure_state(0,"Access denied, this user doesn't own this order");
  exit(pekky_print_json_state());//end the program   
}
}
$change_action = array('status'=>'?');
$change_action_data = array($o_status,$order_id);
if($user_type == 2){
  //prevent dispatcher from cancelling order
  if($o_status == ORDER_CANCEL_CODE){
    pekky_set_failure_state(0,"Dispatcher can't cancel an order");
    exit(pekky_print_json_state());//end the program  
  }

if($o_status == ORDER_COMPLETE_CODE){//check if the order status is changing to complete
//check if it's dispatcher, and they're assigned to the order,
if(!user_owns_order($dbq,$user_id,$user_type,$order_id)){
  pekky_set_failure_state(0,"Access denied, this dispatcher isn't assigned to this order");
  exit(pekky_print_json_state());//end the program
}
//get the auth key
if($order_auth != get_order_meta($dbq,$order_id,'order_auth')){//deny
  pekky_set_failure_state(1,"Access denied, invalid order auth code");
  exit(pekky_print_json_state());//end the program   
}
}
$change_action = array('status'=>'?','dispatcher_id'=>'?');
$change_action_data = array($o_status,$user_id,$order_id);
}
//now change things
$user_tory = $user_id.','.$user_type;
$add_bind = array($order_id,$o_status,$user_tory);
if(!$dbq->add('order_history',['order_id'=>'?','order_status'=>'?','user_id'=>'?','date_time'=>'now()'],$add_bind) ){
  pekky_set_failure_state(-1,$dbq->err_msg);
  exit(pekky_print_json_state());//end the program   
}
$dbq->change('orders',$change_action,'WHERE id = ?',$change_action_data);
if($dbq->rows_affected != 1){
  pekky_set_failure_state(-1,"Couldn't be updated, dont know why");
  exit(pekky_print_json_state());//end the program   

}
//now check if the send mail
if(!$n_send_mail){// the mail part
  require '../inc/_mail.php';
//for the user
//get the user id of the order 
$dbq->get("select user_id FROM orders WHERE id = ?",[$order_id]);
$user_own = $dbq->record[0]['user_id'];
if(order_mail_template($dbq,$order_id,$o_status,$user_own))
  pekky_add_array_to_print(['mail_sent_to_user'=>'true']);
else
 pekky_add_array_to_print(['mail_sent_to_user'=>'false']);
//for the admin
$t_dbq = new Query($con);//did this cause calling the order_mail_template a second time, the query wasnt working, returning false for i dont know why
if(order_mail_template($t_dbq,$order_id,$o_status,$user_id,1,true))
   pekky_add_array_to_print(['mail_sent_to_admin'=>'true']);
else
 pekky_add_array_to_print(['mail_sent_to_admin'=>'false']);

}
pekky_set_success_state();
pekky_print_json_state();
