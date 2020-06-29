<?php
/**
  * Script to update notifications of the user, can be more than 1
  *
  * It accepts the parameter ids(notifications ids this should be in csv),user_id,user_type, read(yes or no) using GET
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
  $user_id = isset($_GET["user_id"]) ? $_GET["user_id"] : false;
  $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : 1;
  $ids = isset($_GET["ids"]) ? trim($_GET["ids"]) : false;
  $read = isset($_GET["read"]) ? trim(strtolower($_GET["read"])) : "yes";

  if(!($user_id && $ids)){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
  $dbq = new Query($con);
  $dbq->set_fetch_mode("assoc");
  if(!user_exists($dbq,$user,$user_type)){
    pekky_set_failure_state(0,"Sorry,user doesn't exist.");
    exit(pekky_print_json_state());//end the program    
  }
  
  switch($read){
	case "yes":
		$read = 1;
	break;
	case: "no":
		$read = 0;
	break;
	default:
		pekky_set_failure_state(0,"'read' parameter has invalid value, either 'yes' or 'no', if you don't add this parameter, default is 'yes'");
		exit(pekky_print_json_state());//end the program
  }

//now start updating
//get the value through csv
$update_records = explode(',',$ids);
$total = count($update_records);
$s = 0;//for counting success
for($i = 0; $i < $total; $i++){
  $id = (string)$update_records[$i];
  if(!empty(trim($id))){
    $dbq->change('notifications',['viewed'=>'1'],'WHERE id = ?',[$id]);
    if($dbq->rows_affected == 1){
      $s++;
    }
  }
}
pekky_set_success_state();
pekky_print_json_state(['total'=>$total,'updated'=>$s]);
