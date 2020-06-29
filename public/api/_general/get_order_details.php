<?php
 /**
  * Script to get order details
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
if(!$dbq->get('SELECT id,user_id,status,dispatcher_id,price,payment_type,order_type,date_time,date_time_updated FROM orders WHERE id = ?',[$order_id])){
    pekky_set_failure_state(-1,$dbq->err_msg);
    exit(pekky_print_json_state());            
}
$order_details = for_single_array($dbq->record);
//carry on, 
$order_meta = get_order_meta($dbq,$order_id);

$p_coord_key = find_coord($order_meta,1);//find the possible coordinate key name,pickup
if(!empty($p_coord_key)){
$new_coord = json_coord($order_meta[$p_coord_key],'pickup_coord');
//unset the normal coord
unset($order_meta[$p_coord_key]);
$order_meta = array_merge($order_meta,$new_coord);//merge new coord to array
}
$d_coord_key = find_coord($order_meta,2);//find the possible coordinate key name,delivery
if(!empty($d_coord_key)){
$new_coord = json_coord($order_meta[$d_coord_key],'delivery_coord');
//unset the normal coord
unset($order_meta[$d_coord_key]);
$order_meta = array_merge($order_meta,$new_coord);//merge new coord to array
}
//merge
$whole_order = array_merge($order_details,array('meta'=>$order_meta));
pekky_set_success_state();
pekky_print_json_state($whole_order);
