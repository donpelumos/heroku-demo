<?php
/**
  * Script to get notifications type of the user, read or unread for now, to use both, use get_notifications endpoint
  *
  * It accepts the parameter user_id,type(read,unread) using GET
  * Please note that this file only helps handle, determine and prevent any empty parameter
  * If a required parameter is empty, it'll return false
  * You should make sure all those are handled on your end
  * 
  * @return JSON success,some data on success or Error status on failure.
  * @author Precious Omonzejele <omonze@peepsipi.com>
  */
  header("Content-Type: application/json");
  require "../inc/_config.php";
  require "../vendor/autoload.php";
  $user = isset($_GET["user_id"]) ? $_GET["user_id"] : false;
  $type = isset($_GET["type"]) ? trim(strtolower($_GET["type"])) : false;
  if(!($user&& $type)){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
  $dbq = new Query($con);
  $dbq->set_fetch_mode("assoc");
  if(!user_exists($dbq,$user,1)){
    pekky_set_failure_state(0,"Sorry,user doesn't exist.");
    exit(pekky_print_json_state());//end the program    
  }
  //check the type
  $view_val = 0;
  switch($type){
    case "read":
      $view_val = 1;
    break;
    case "unread":
      $view_val = 0;
    break;
    case "un-read":
      $view_val = 0;
    break;
    default:
    pekky_set_failure_state(0,"Wrong type param value");
    exit(pekky_print_json_state());//end the program        
  }

  if(!$dbq->get(query_live("SELECT id,user_id,dispatcher_id,title,message,device,viewed,date_time FROM notifications WHERE user_id = ? AND viewed = ? ORDER BY date_time DESC"),[$user,$view_val]) ){
    pekky_set_failure_state(-1,$dbq->err_msg);
    exit(pekky_print_json_state());  
  }
//size key
pekky_add_array_to_print(['size'=>$dbq->row_count]);
pekky_set_success_state();
pekky_print_json_state($dbq->record,'data',false);
