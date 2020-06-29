<?php
 /**
  * Script to delete order(s)
  *
  * It accepts the parameter user_id,ids(this should be in csv)
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
 //now start deleting
//get the value through csv
$delete_records = explode(',',$ids);
//$dbq->prepare("UPDATE orders SET deleted = '0' WHERE id = ?");
$total = count($delete_records);
$s = 0;//for counting success
for($i = 0; $i < $total; $i++){
  $id = (string)$delete_records[$i];
  if(!empty(trim($id))){
    //delete only when order is complete or cancelled
    $dbq->get("SELECT id from orders WHERE id = ? AND (status = '2' OR status = '1' )",[$id]);
    if($dbq->row_count > 0)
        continue;//skip
    $dbq->change('orders',['deleted'=>'1'],'WHERE id = ?',[$id]);
    if($dbq->rows_affected == 1){
      $s++;
    }
  }
}
pekky_set_success_state();
pekky_print_json_state(['total'=>$total,'deleted'=>$s]);
