<?php
/**
  * Script to get firebase id of the user
  *
  * It accepts the parameter user_id using GET
  * Please note that this file only helps handle, determine and prevent any empty parameter
  * If a required parameter is empty, it'll return false
  * You should make sure all those are handled on your end
  * 
  * @return JSON success,firebase_id on success or Error status on failure.
  * @author Precious Omonzejele <omonze@peepsipi.com>
  */
  header("Content-Type: application/json");
  require "../inc/_config.php";
  require "../vendor/autoload.php";
  $user = isset($_GET["user_id"]) ? $_GET["user_id"] : false;
  if(!($user)){
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
  if(!$dbq->get(query_live("SELECT firebase_id from users Where id = ?"),[$user]) ){
    pekky_set_failure_state(-1,$dbq->err_msg);
    exit(pekky_print_json_state());  
  }
  pekky_set_success_state();
  pekky_print_json_state($dbq->record[0]);
  