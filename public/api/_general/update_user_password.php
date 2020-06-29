<?php
/**
  * Script to update password
  *
  * It accepts the parameter user_id(can also be email),user_type,password(where password is the new password) using GET
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
  $password = isset($_GET["password"]) ? hash_password($_GET["password"]) : false;
  $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : 1;
  if(!($user_id && $password)){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  $user_db = user_type($user_type);
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 $dbq->set_fetch_mode("assoc");

 //check if user exists
 if(!user_exists($dbq,$user_id,$user_type) ){
    pekky_set_failure_state(0,'User doesn\'t exist');
    exit(pekky_print_json_state());
 }

 $data = array('password'=>'?','active'=>'1','activation_link'=>'','date_time_updated'=>'NOW()');
 $binding = array($password,$user_id,$user_id);
if(!$dbq->change($user_db,$data,query_live("Where (id = ? OR email = ?)"), $binding) ){
    pekky_set_failure_state(-1,$dbq->err_msg);
    exit(pekky_print_json_state());  
}

if($dbq->rows_affected == 1){//successful
    pekky_set_success_state();
    pekky_print_json_state();
}
else{//false
    pekky_set_failure_state(0,"Couldn't update record:".$dbq->err_msg);
    pekky_print_json_state();
}