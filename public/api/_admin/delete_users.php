<?php
 /**
  * Script to delete user(s)
  *
  * It accepts the parameter user_id,ids(this should be in csv),user_type using GET
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
  $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : 1;
  if(!($user_id && $ids)){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 $dbq->set_fetch_mode("assoc");
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
 $user_db = user_type($user_type);
 //now start deleting
//get the value through csv
$delete_records = explode(',',$ids);
$total = count($delete_records);
$s = 0;//for counting success
for($i = 0; $i < $total; $i++){
  $id = (string)$delete_records[$i];
  if(!empty(trim($id))){
    if($user_id == $id)//cant change anything for same user
      continue;
    
    $dbq->get(query_live("SELECT email FROM ".$user_db." WHERE id = ?"),[$id]);
    $email = isset($dbq->record[0]['email']) ? $dbq->record[0]['email'] : '';
    //check if a user is part of a processing order,so we don't allow
    $dbq->get(query_live("SELECT id FROM orders where (user_id = ? OR dispatcher_id = ? ) AND (status = 2)"),[$id,$id]);
    $processing_order_count = $dbq->row_count;
    if(!empty($email) && $processing_order_count < 1){
      $dbq->change($user_db,['deleted'=>'1','email'=>'--'.$email.'--'],"WHERE id = ?",[$id]);//since email field is unique, add something to it to prevent future issues :)
      if($dbq->rows_affected == 1){
          $s++;
      }
    }
  }
}
pekky_set_success_state();
pekky_print_json_state(['total'=>$total,'deleted'=>$s]);
