<?php 
 /** 
  * Script to get orders based on status, can get all orders 
  * 
  * It accepts the parameter user_id,user_type, using GET 
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
  $status = isset($_GET["status"]) ? (empty(trim($_GET["status"])) ? "all" : trim($_GET["status"])) : "all"; 
  $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : 1; 
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
 else if(strpos($status,"disp") !== false){//to be able to show both completed and processing order of a dispatcher
   $o_status = "disp_all";
 }
 else{
  $o_status = get_order_status($status);
 }
if($o_status == false){
  pekky_set_failure_state(0,"Couldn't process status value,please check properly");
  exit(pekky_print_json_state());//end the program   
}
 //store some things for flexible use
 $only_user = (($user_type == 2) ? 'dispatcher_id' : 'user_id')."= ? ";
 $status_statement = "status = ? ";
 $s_a = array($o_status);
 $u_a = array($user_id);
 //now check if the user type is an admin
 if($user_type == 3){
   $only_user = '';//it's an admin, so unset
   $u_a = array();
  }//before i forget, make sure when the order is on hold, any dispatcher can view it
  else if($user_type == 2 && $o_status == ORDER_HOLD_CODE){
    $only_user = '';
    $u_a = array();
  }
  
 if($o_status == "all"){
   $status_statement = '';
   $s_a = array();
  }
  else if($o_status == "disp_all" && $user_type == 2){//means its to show complete and processing for the dispatcher at once 
 $status_statement = "(status = ? OR status = ?) ";
 $s_a = array(ORDER_PROCESS_CODE,ORDER_COMPLETE_CODE);
  }
  $order_bind = array_merge($s_a,$u_a);//merge
  //on the line, check if status_statement and only_user are empty, so we add the where clause,and check if status_statement isnt empty, so we know when to add and clause
 $extra_cond = ( (!(empty($status_statement) && empty($only_user) ) ) ? 'WHERE ':'' ).$status_statement.( ( !empty($status_statement) && !empty($only_user)) ? ' AND ':'' ).$only_user;
 $q = query_live("SELECT id,user_id,status,dispatcher_id,price,payment_type,order_type,date_time,date_time_updated FROM orders ".$extra_cond." ORDER by date_time DESC");
 $dbq->set_fetch_mode("assoc");
//get order info
if(!$dbq->get($q,$order_bind)){
    pekky_set_failure_state(-1,$dbq->err_msg);
    exit(pekky_print_json_state());            
}
//size key
pekky_add_array_to_print(['size'=>$dbq->row_count]);
if($dbq->row_count < 1){//no value
  pekky_set_success_state(); 
  exit(pekky_print_json_state([],'order_list',false));             
}

$orders = $dbq->record;
//carry on,loop through
$whole_order = array();
for($i=0; $i<count($orders);$i++){
  $order_id = $orders[$i]['id'];
$order_meta = get_order_meta($dbq,$order_id);
$p_coord_key = find_coord($order_meta,1);//find the possible coordinate key name,pickup
if(!empty($p_coord_key)){//there's a coordinate, do the necessary
$new_coord = json_coord($order_meta[$p_coord_key],'pickup_coord');
//unset the normal coord
unset($order_meta[$p_coord_key]);
$order_meta = array_merge($order_meta,$new_coord);//merge new coord to array
}
$d_coord_key = find_coord($order_meta,2);//find the possible coordinate key name,delivery
if(!empty($d_coord_key)){//there's a coordinate, do the necessary
$new_coord = json_coord($order_meta[$d_coord_key],'delivery_coord');
//unset the normal coord
unset($order_meta[$d_coord_key]);
$order_meta = array_merge($order_meta,$new_coord);//merge new coord to array
}
//merge
$whole_order[] = array_merge($orders[$i],array('meta'=>$order_meta));
}
pekky_set_success_state();
pekky_print_json_state($whole_order,'order_list',false);

