<?php
/**
  * Script to get curent location of the order
  *
  * It accepts the parameter order_id,dispatcher_id,event,coord(useful if the event is to update), using GET
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
  $order_id = isset($_GET["order_id"]) ? $_GET["order_id"] : false;
  $event = isset($_GET["event"]) ? $_GET["event"] : "all";
  $coord = isset($_GET["coord"]) ? trim($_GET["coord"]) : "";
  if(!($dispatcher && $order_id)){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
  $dbq = new Query($con);
  $dbq->set_fetch_mode("assoc");
  //check if dispatcher is part of an order
  if(!user_owns_order($dbq,$dispatcher,2,$order_id)){
    pekky_set_failure_state(0,"Access denied, this dispatcher isn't associated with this order");
    exit(pekky_print_json_state());//end the program     
  }
  //now check what type of event was used
  if(strpos($event,"retr") !== false){//retrieve
    if(!$dbq->get("SELECT coordinates,date_time from mapping where order_id = ? ORDER BY date_time desc LIMIT 1",[$order_id])){
      pekky_set_failure_state(-1,$dbq->err_msg);
      exit(pekky_print_json_state());//end the program 
    }
    if($dbq->row_count < 1 ){
      pekky_set_success_state();
      pekky_print_json_state(array('order_id'=>$order_id,'data'=>[]));
      exit();
    }
    //now get the values
    //now do the coordinates proper
    $coord = json_coord($dbq->record[0]['coordinates']);
    unset($dbq->record[0]['coordinates']);
    pekky_add_array_to_print(array('order_id'=>$order_id,'data'=>[array_merge($coord,for_single_array($dbq->record))]));
    pekky_set_success_state();
  }
  else if(strpos($event,"upda") !== false){//update
    if(empty($coord) || !is_valid_coord($coord)){
      pekky_set_failure_state(0,"can't update, coordinate format incorrect.");
      exit(pekky_print_json_state());//end the program 
    }
    if(!$dbq->add("mapping",['order_id'=>'?','coordinates'=>'?','date_time'=>'NOW()'],[$order_id,$coord])){
      pekky_set_failure_state(-1,$dbq->err_msg);
      exit(pekky_print_json_state());//end the program 
    }
    pekky_set_success_state();
  }
  else{//show all
    if(!$dbq->get("SELECT coordinates,date_time from mapping where order_id = ? ORDER BY date_time desc",[$order_id])){
      pekky_set_failure_state(-1,$dbq->err_msg);
      exit(pekky_print_json_state());//end the program 
    }
    
    if($dbq->row_count < 1 ){
      pekky_set_success_state();
      pekky_print_json_state(array('order_id'=>$order_id,'data'=>[]));
      exit();
    }
    $record = $dbq->record;
    $new_records = array();
    //now get the values,loop
    for($i=0; $i < count($record); $i++){
      //now do the coordinates proper
      $coord = json_coord($record[$i]['coordinates']);
      $single_record = for_single_array($record[$i]);
      unset($single_record['coordinates']);
      $new_record[] = array_merge($coord,for_single_array($single_record));
    }
    pekky_add_array_to_print(array('order_id'=>$order_id,'data'=>$new_record));
    
    pekky_set_success_state();
  }
pekky_print_json_state();