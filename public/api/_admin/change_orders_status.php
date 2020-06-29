<?php
 /**
  * Script to change status of order(s)
  *
  * It accepts the parameter user_id,ids(this should be in csv),status,dont_send_mail using GET
  * Please note that this file only helps handle, determine and prevent any empty parameter
  * If a required parameter is empty, it'll return false
  * You should make sure all those are handled on your end
  * 
  * @return JSON success,total,deleted on success or Error status on failure.
  * @author Precious Omonzejele <omonze@peepsipi.com>
  */  
  header("Content-Type: application/json");
  require "../inc/_config.php";
  require "../vendor/autoload.php";
 
  $user_id = isset($_GET["user_id"]) ? $_GET["user_id"] : false;
  $ids = isset($_GET["ids"]) ? trim($_GET["ids"]) : false;
  $status = isset($_GET['status']) ? trim($_GET['status']) : false;
  $n_send_mail = isset($_GET["dont_send_mail"]) ? true : false;
 
  if(!($user_id && $ids)){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 //check if user exists, to preceed with the action
 if(!user_exists($dbq,$user_id,3)){
    pekky_set_failure_state(0,'User doesn\'t exist');
    exit(pekky_print_json_state());
 }
 //check if admin has priviledges to take this action
 if(!is_allowed_admin_type($dbq,$user_id)){
  pekky_set_failure_state(1,'Sorry, authorisation denied');
  exit(pekky_print_json_state());
 }
$dbq->set_fetch_mode("assoc");

//convert status , just in case
$o_status = get_order_status($status);
if($o_status == false){
  pekky_set_failure_state(0,"Couldn't process status value,please check properly");
  exit(pekky_print_json_state());//end the program
}
if($o_status != -1){//for now, user can only cancel orders
  pekky_set_failure_state(0,"Admin is only allowed to set an order status to cancel.");
  exit(pekky_print_json_state());//end the program
}
//require mail for mail stuff
require '../inc/_mail.php';

$o_status = (string)$o_status;
 //now start deleting
//get the value through csv
$change_records = explode(',',$ids);
$total = count($change_records);
$s = 0;//for counting success
$user_mails_sent = 0;//for counting mails that sent
$admin_mails_sent = 0;//for counting mails sent to admin
//user nail initialise query
$u_m_dbq = new Query($con);//did this cause calling the order_mail_template a second time, the query wasnt working, returning false for i dont know why
//admin nail initialise query
$a_m_dbq = new Query($con);//did this cause calling the order_mail_template a second time, the query wasnt working, returning false for i dont know why

for($i = 0; $i < $total; $i++){
  $order_id = (string)$change_records[$i];
  if(!empty(trim($order_id))){
    //change status only when order is  or cancelled
   /* $dbq->get("SELECT id from orders WHERE id = ? AND (status = '2' OR status = '1' )",[$id]);
    if($dbq->row_count > 0)
        continue;//skip
    */
    $dbq->change('orders',['status'=>$o_status],'WHERE id = ?',[$order_id]);
    if($dbq->rows_affected == 1){
      $s++;
      //now check if the send mail
      if(!$n_send_mail){// the mail part
        //for the user
        //get the user id of the order 
        $dbq->get("select user_id FROM orders WHERE id = ?",[$order_id]);
        $user_own = $dbq->record[0]['user_id'];
        if(order_mail_template($u_m_dbq,$order_id,$o_status,$user_own))
          $user_mails_sent++;
        //for the admin
        if(order_mail_template($a_m_dbq,$order_id,$o_status,$user_own,1,true))
          $admin_mails_sent++;
      }

    }
  }
}
pekky_set_success_state();
pekky_print_json_state(['total'=>$total,'status_updated'=>$s,'user_mails_sent'=>$user_mails_sent,'admin_mails_sent'=>$admin_mails_sent]);
