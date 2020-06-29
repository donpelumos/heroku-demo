<?php
/**
  * Script to update firebase id of the user
  *
  * It accepts the parameter user_id,firebase_id, using GET
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
  $user = isset($_GET["user_id"]) ? $_GET["user_id"] : false;
  $firebase = isset($_GET["firebase_id"]) ? trim($_GET["firebase_id"]) : false;
  if(!($user && $firebase)){
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
    $binding = array($firebase,$user);
    //check if the value is the same, so as not to confuse lumos when he updates with same value
    $dbq->get(query_live("SELECT firebase_id FROM users WHERE firebase_id = ? AND id = ?"),$binding);
    if($dbq->row_count > 0){
      pekky_set_failure_state(0,"Same value");
      exit(pekky_print_json_state());      
    }
    if(!$dbq->change("users",['firebase_id'=>'?'],query_live("Where id = ?"), $binding) ){
      pekky_set_failure_state(-1,$dbq->err_msg);
      exit(pekky_print_json_state());  
    }
  
  if($dbq->rows_affected == 1){//successful
      pekky_set_success_state();
  }
  else{//false
      pekky_set_failure_state(0,"Couldn't update record:".$dbq->err_msg);
  }
  pekky_print_json_state();  
  