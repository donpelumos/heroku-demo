<?php 
 /** 
  * Script to get order history of an order 
  * 
  * It accepts the parameter user_id,user_type,order_id, using GET 
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
  $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : 1; 
  if(!($user_id && $order_id)){ 
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
$dbq->set_fetch_mode("assoc"); 
//get order info 
if(!$dbq->get('SELECT order_status,user_id,date_time FROM order_history WHERE order_id = ? ORDER BY date_time DESC',[$order_id])){ 
    pekky_set_failure_state(-1,$dbq->err_msg); 
    exit(pekky_print_json_state());             
}
//size key
pekky_add_array_to_print(['size'=>$dbq->row_count]);
if($dbq->row_count < 1){
    pekky_set_success_state(); 
    exit(pekky_print_json_state(["No history record for order #{$order_id}"]));             
}
 $history = $dbq->record;
 for($i = 0;$i<count($history);$i++){
     $user_detail = explode(',',$history[$i]['user_id']);//break to an array
     $user = array('user'=>array('id'=>$user_detail[0],'type'=>$user_detail[1]));
     $history[$i] = array_merge($history[$i],$user);
     unset($history[$i]['user_id']);
 }

pekky_set_success_state(); 
pekky_print_json_state($history,false); 