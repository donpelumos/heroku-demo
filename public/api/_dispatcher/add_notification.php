<?php
 /**
  * Script to add notification
  *
  * It accepts the parameter user_id,dispatcher_id,title,msg,device(optional) using GET
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
  $dispatcher = isset($_GET["dispatcher_id"]) ? $_GET["dispatcher_id"] : false;
  $title = isset($_GET["title"]) ? $_GET["title"] : false;
  $msg = isset($_GET["msg"]) ? $_GET["msg"] : false;
  $device = isset($_GET["device"]) ? $_GET["device"] : "";
  if(!($user && $dispatcher && $title && $msg)){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 //check if user exists
 if(!user_exists($dbq,$user) ){
    pekky_set_failure_state(0,'User doesn\'t exist');
    exit(pekky_print_json_state());
 }
 //check if dispatcher exists
 if(!user_exists($dbq,$dispatcher,2) ){
    pekky_set_failure_state(0,'Dispatcher doesn\'t exist');
    exit(pekky_print_json_state());
 }
 //insert stuff
 $data = array('user_id'=>'?','dispatcher_id'=>'?','title'=>'?','message'=>'?','device'=>'','date_time'=>'NOW()');
 $binding = array($user,$dispatcher,$title,$msg,$device);
 if(!$dbq->add("notifications",$data,$binding)){
     pekky_set_failure_state(-1,$dbq->err_msg);
     exit(pekky_print_json_state());
 }
 //worked
 pekky_set_success_state();
 pekky_print_json_state(['id'=>$dbq->last_insert_id]);
