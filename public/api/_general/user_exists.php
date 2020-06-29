<?php
/**
  * Script to Verify if a user exists
  *
  * It accepts the parameter user_id(can be email or user id),user_type using GET
  * Please note that this file only helps handle, determine and prevent any empty parameter
  * If a required parameter is empty, it'll return false
  * You should make sure all those are handled on your end
  * 
  * @return JSON true on success or Error status on failure.
  * @author Precious Omonzejele <omonze@peepsipi.com>
  */
  header("Content-Type: application/json");
  require "../inc/_config.php";
  require "../vendor/autoload.php";
 
  $user_id = isset($_GET["user_id"]) ? $_GET["user_id"] : false;
  $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : 1;
  if(!($user_id)){
      pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  $user_db = user_type($user_type);
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 $dbq->set_fetch_mode("assoc");

if(!$dbq->get(query_live("SELECT id from ".$user_db." Where (id = ? OR email = ?)"), [$user_id,$user_id]) ){
    pekky_set_failure_state(-1,$dbq->err_msg);
    exit(pekky_print_json_state());  
}

if($dbq->row_count == 1){//correct
    pekky_set_success_state();
    pekky_print_json_state();
}
else{//false
    pekky_set_failure_state(1,"");
pekky_print_json_state();  

}