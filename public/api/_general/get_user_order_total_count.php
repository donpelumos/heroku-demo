<?php 
 /** 
  * Script to get user total order and price of order based on status
  * 
  * It accepts the parameter user_id,user_type,status, using GET 
  * Please note that this file only helps handle, determine and prevent any empty parameter 
  * If a required parameter is empty, it'll return false 
  * You should make sure all those are handled on your end 
  *  
  * @return JSON success, on success or Error status on failure. 
  * @author Precious Omonzejele <omonze@peepsipi.com> 
  */
  header("Content-Type: application/json");
  require "../inc/_config.php"; 
  require "../vendor/autoload.php"; 
  
  $user_id = isset($_GET["user_id"]) ? $_GET["user_id"] : false; 
  $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : 1;
  $status = isset($_GET["status"]) ? (empty(trim($_GET["status"])) ? "all" : trim($_GET["status"])) : "all"; 
  if(!($user_id)){ 
    pekky_set_failure_state(0,"empty field(s)"); 
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
  //process the order
  if($status == "all"){
    $o_status = "all";
   }
   else{
    $o_status = get_order_status($status);
   }
   $extra_statement = " AND status = '".$o_status."'";
   if($o_status == "all")
      $extra_statement = '';
 $user_statement = "WHERE ".($user_type == 2 ? "dispatcher_id" : "user_id")." = ?";
if($o_status == false){
  pekky_set_failure_state(0,"Couldn't process status value,please check properly");
  exit(pekky_print_json_state());//end the program   
}
 $q = query_live("SELECT COUNT(id) as total_orders, ROUND(SUM(price),2) as total_amount FROM orders ".$user_statement.$extra_statement);
 $dbq->set_fetch_mode("assoc");
//get order info
if(!$dbq->get($q,[$user_id])){
    pekky_set_failure_state(-1,$dbq->err_msg);
    exit(pekky_print_json_state());            
}
$dbq->record[0]['total_amount'] = is_null($dbq->record[0]['total_amount']) ? 0 : $dbq->record[0]['total_amount'];
$dbq->record[0]['total_orders'] = is_null($dbq->record[0]['total_orders']) ? 0 : $dbq->record[0]['total_orders'];
pekky_set_success_state();
pekky_print_json_state($dbq->record[0]);

