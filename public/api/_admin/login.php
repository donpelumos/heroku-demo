<?php
 /**
  * 
  * Script to carry out the login work
  *
  * It accepts the parameter email address and password using GET
  * Please note that this file just helps handle if an email is empty or password is empty
  * You should make sure all other errors are handled on your end
  * It checks the value of the email address and password in the respective table
  * 
  * @return JSON | Array user_id,fullname,email,phone number on success or Error status on failure.
  * @author Precious Omonzejele <omonze@peepsipi.com>
  */
  header("Content-Type: application/json");
  //use vendor\pp\DB as dbq;
  require "../inc/_config.php";
  require "../vendor/autoload.php";
 //require "vendor/pp/DB/DBCon.class.php";
  $email = isset($_GET["email"]) ? $_GET["email"] : false;
  $password = isset($_GET["password"]) ? hash_password($_GET["password"]) : false;

  if(!($email && $password)){
      pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 $dbq->set_fetch_mode("assoc");

 //check if the user is inactive
 
 if( !$dbq->get(query_live("select id from admins WHERE active = 0 and email = ? and password = ? "),[$email,$password]) ){
  //error
  pekky_set_failure_state(-1,$dbq->err_msg);
exit(pekky_print_json_state());
}
//no error, continue
if($dbq->row_count == 1){
  //suspended
pekky_set_failure_state(1,"user suspended");
exit(pekky_print_json_state());
}

 if( !$dbq->get(query_live("select id,fullname,email,phone,admin_type from admins WHERE email = ? AND password = ? "),[$email,$password,true])){
    //error
    pekky_set_failure_state(-1,$dbq->err_msg);
  exit(pekky_print_json_state());
  }
  //var_dump($dbq);
  //no error, continue
  if($dbq->row_count == 1){
    pekky_set_success_state();
    pekky_print_json_state(for_single_array($dbq->record));
  }
  else{
  //error
  pekky_set_failure_state(1,"user doesn't exist");
  exit(pekky_print_json_state());
  }
  