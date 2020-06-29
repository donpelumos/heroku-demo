<?php
/**
  * Script to update curent location of the dispatcher
  *
  * It accepts the parameter dispatcher_id,coord, using GET
  * Please note that this file only helps handle, determine and prevent any empty parameter
  * If a required parameter is empty, it'll return false
  * You should make sure all those are handled on your end
  * 
  * @return JSON success or Error status on failure.
  * @author Precious Omonzejele <omonze@peepsipi.com>
  */
  header("Content-Type: application/json");
  require "../inc/_config.php";
  require "../vendor/autoload.php";
  $dispatcher = isset($_GET["dispatcher_id"]) ? $_GET["dispatcher_id"] : false;
  $coord = isset($_GET["coord"]) ? trim($_GET["coord"]) : false;
  if(!($dispatcher && $coord)){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
  $dbq = new Query($con);
  $dbq->set_fetch_mode("assoc");
    if(!user_exists($dbq,$dispatcher,2)){
      pekky_set_failure_state(0,"Sorry,dispatcher doesn't exist.");
      exit(pekky_print_json_state());//end the program    
    }
    if(empty($coord) || !is_valid_coord($coord)){
      pekky_set_failure_state(0,"can't update, coordinate format incorrect.");
      exit(pekky_print_json_state());//end the program 
    }
    //seems like everything went well here, now we check every order the dispatcher is attached to
    //then we update(add) the coordinates for each of them
  
    $q = query_live("SELECT id FROM orders WHERE dispatcher_id = ? AND status = ?");
    if(!$dbq->get($q,[$dispatcher,get_order_status("process")])){
      pekky_set_failure_state(-1,$dbq->err_msg);
      exit(pekky_print_json_state());//end the program   
    }
    //check if the any row was gathered
    if($dbq->row_count < 1){//do nothing really, just hasten the code. :)
       pekky_set_success_state();
      exit(pekky_print_json_state());
    }
    //now loop and update
    $rec = $dbq->record;
    for($i = 0; $i < count($rec); $i++){//keep adding
      $order_id = $rec[$i]['id'];
     if(!$dbq->add("mapping",['order_id'=>'?','coordinates'=>'?','date_time'=>'NOW()'],[$order_id,$coord])){
        pekky_set_failure_state(-1,$dbq->err_msg);
        exit(pekky_print_json_state());//end the program 
     }
  }
 pekky_set_success_state();
  
pekky_print_json_state();