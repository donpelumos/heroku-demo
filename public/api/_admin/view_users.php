<?php
/**
 * Script to view all user(s)
 *
 * It accepts the parameter user_id,user_type,order_by(optional, this should be a valid sql statement stating a valid column in db,e.g fullname ASC) using GET
 * Please note that this file only helps handle, determine and prevent any empty parameter
 * If a required parameter is empty, it'll return false
 * You should make sure all those are handled on your end
 * 
 * @return JSON on success or Error status on failure.
 * @author Precious Omonzejele <omonze@peepsipi.com>
 */
header("Content-Type: application/json");
 require "../inc/_config.php";
 require "../vendor/autoload.php";
 $user_id = isset($_GET["user_id"]) ? $_GET["user_id"] : false;
 $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : 1;
 $deleted = isset($_GET["deleted"]) ? true : false;
 $order_by = isset($_GET["order_by"]) ? $_GET["order_by"] : "fullname ASC";
 if(!($user_id)){
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

$user_db = user_type($user_type);
$columns = '';
  switch($user_type){
      case 2:
        $columns = 'id,login_id,fullname,email,phone,active,date_time,date_time_updated';
      break;
      case 3:
        $columns = 'id,fullname,email,phone,active,admin_type,date_time,date_time_updated';
      break;
      default:
        $columns = 'id,fullname,email,phone,address,active,date_time,date_time_updated,coordinates';

  } 
$query = "SELECT ".$columns." FROM ".$user_db." ORDER BY ".$order_by;
if(!$deleted)
  $query = query_live($query);
//now get the all the user's details
if(!$dbq->get($query)){
  pekky_set_failure_state(-1,$dbq->err_msg);
  exit(pekky_print_json_state());
} 
//size key
pekky_add_array_to_print(['size'=>$dbq->row_count]);
pekky_set_success_state();
pekky_print_json_state($dbq->record,'data',false);
