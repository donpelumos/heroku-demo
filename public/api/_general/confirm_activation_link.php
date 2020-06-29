<?php
/**
  * Script to Verify activativation link of a user
  *
  * It accepts the parameter email,link,user_type using GET
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
 
  $email = isset($_GET["email"]) ? $_GET["email"] : false;
  $link = isset($_GET["link"]) ? $_GET["link"] : false;
  $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : 1;
  if(!($email && $link)){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  $user_db = user_type($user_type);
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 $dbq->set_fetch_mode("assoc");

$binding = array($email,$link);
if(!$dbq->get(query_live("SELECT id from ".$user_db." Where email = ? AND activation_link = ?"), $binding) ){
    pekky_set_failure_state(-1,$dbq->err_msg);
    exit(pekky_print_json_state());  
}

if($dbq->row_count == 1){//correct
    pekky_set_success_state();
    $values = array('id'=>$dbq->record[0]['id'],'user_type'=>(int)$user_type);
    pekky_print_json_state($values,'data',false);
}
else{//false
    pekky_set_failure_state(1,"Doesnt match");
    pekky_print_json_state();
}