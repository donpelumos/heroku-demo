<?php
/**
  * Script to get user details
  *
  * It accepts the parameter user,user_type using GET, where user can be the id or email
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
 
  $user = isset($_GET["user"]) ? $_GET["user"] : false;
  $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : 1;
  if(!($user && $user_type)){
      pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }

  $user_db = user_type($user_type);
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 $dbq->set_fetch_mode("assoc");
  $columns = '';
  switch($user_type){
      case 2:
        $columns = 'id,login_id,fullname,email,phone,active,date_time,date_time_updated';
      break;
      case 3:
        $columns = 'id,fullname,email,phone,active,admin_type,date_time,date_time_updated';
      break;
      default:
        $columns = 'id,fullname,email,phone,active,address,date_time,date_time_updated,coordinates';

  }
  $query = query_live("SELECT ".$columns." FROM ".$user_db." WHERE (email = ? OR id = ?)");
 if(!$dbq->get($query,[$user,$user])){
     pekky_set_failure_state(-1,$dbq->err_msg);
     exit(pekky_print_json_state());
 }
 if($user_type == 1){
 //now do the coordinates proper
 $coord = json_coord($dbq->record[0]['coordinates']);
 unset($dbq->record[0]['coordinates']);
 pekky_add_array_to_print($coord);
 }
 if($dbq->row_count == 1){//valid
  pekky_set_success_state();
  pekky_print_json_state(for_single_array($dbq->record));
 }
 else{
  pekky_set_failure_state(1,'user doesn\'t exist');
  pekky_print_json_state(); 
 }