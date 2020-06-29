<?php
/**
  * Script to activate account
  *
  * It accepts the parameter user(email or id),link using GET
  * It transfers the user from the temp user table to the main table
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
 
  $user_id = isset($_GET["user"]) ? $_GET["user"] : false;
  $link = isset($_GET["link"]) ? $_GET["link"] : false;
  if(!($user_id && $link)){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 $dbq->set_fetch_mode("assoc");

 //check db
 if(!$dbq->get("SELECT id,fullname,phone,address,email,password,date_time FROM temp_users WHERE (email = ? OR id = ?) AND activation_link = ?",[$user_id,$user_id,$link] ) ){//error
  pekky_set_failure_state(-1,$dbq->err_msg);
  exit(pekky_print_json_state());
 }

 if($dbq->row_count != 1){//invalid
  pekky_set_failure_state(1,"doesn't match");
  exit(pekky_print_json_state());
 }
 else{//valid
  //transfer to another table
  $record = for_single_array($dbq->record);
  $data = array('id' => '?','fullname' => '?','email' => '?','password'=> '?', 'phone'=>'?','address'=>'?','date_time'=>'?','date_time_updated'=>'NOW()');
  $data_binding = array($record['id'],$record['fullname'],$record['email'],$record['password'],$record['phone'],$record['address'],$record['date_time']);

  if(!$dbq->add('users',$data,$data_binding)){//couldnt work
    pekky_set_failure_state(-1,$dbq->err_msg);
    exit(pekky_print_json_state());
  }

  if($dbq->rows_affected != 1){
    pekky_set_failure_state(-1,"Unknown error occured");
    pekky_print_json_state();
  }
  else{   
    pekky_set_success_state();
    pekky_print_json_state();  
    //delete from the temp_table
    $dbq->remove("temp_users","WHERE (email = ? or id = ?)",[$user_id,$user_id]);
   // echo $dbq->err_msg;
  }

}
?>