<?php
 /**
  * Script to set up a forgot password link
  *
  * It accepts the parameter email,user_type,dont_send_mail using GET
  * Please note that this file only helps handle, determine and prevent any empty parameter
  * If a required parameter is empty, it'll return false
  * You should make sure all those are handled on your end
  * 
  * @return JSON success,verification_code on success or Error status on failure.
  * @author Precious Omonzejele <omonze@peepsipi.com>
  */
  header("Content-Type: application/json");
  require "../inc/_config.php";
  require "../vendor/autoload.php";
 
  $email = isset($_GET["email"]) ? $_GET["email"] : false;
  $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : 1;
  $n_send_mail = isset($_GET["dont_send_mail"]) ? true : false;
  
  if(!($email && $user_type)){
      pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  $user_db = user_type($user_type);
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 $dbq->set_fetch_mode("num");
  //check if it's dispatcher, so you can remind them of the login_id
  $dispatcher_column = ',login_id';
  if($user_db != 'dispatchers')
    $dispatcher_column = '';
 if(!$dbq->get(query_live("SELECT fullname,email".$dispatcher_column." FROM ".$user_db." WHERE email = ?"),[$email] ) ){
  pekky_set_failure_state(-1,$dbq->err_msg);
  exit(pekky_print_json_state());//end the program
 }
 if($dbq->row_count != 1){//failure
  pekky_set_failure_state(1,"invalid email");
  exit(pekky_print_json_state());//end the program
 }
 $_e = $dbq->record[0][1];
 $d_log_id = isset($dbq->record[0][2]) ? $dbq->record[0][2] : '';
  $confirm_code = md5(session_id().time());
  //add activation code to db
  if(!$dbq->change($user_db,['activation_link'=>'?'],'WHERE email = ?',[$confirm_code,$email]) ){
    pekky_set_failure_state(-1,$dbq->err_msg);
    exit(pekky_print_json_state());//end the program     
  }
  if($dbq->rows_affected < 1){//not successful
    pekky_set_failure_state(-1,'Couldn\'t update activation_link');
    exit(pekky_print_json_state());//end the program     
  }
  $link = SITE_URL.'reset_password.php?mail='.$_e.'&link='.$confirm_code.'&type='.$user_db;
   if(!$n_send_mail){//means you should send default mail
    require "../inc/_mail.php";
    $to = $email;
    $from = SITE_TITLE." Support <no-reply@".SITE_DOMAIN.">";
    $subject = "Reset your Password on ".SITE_TITLE;
    //remind the dispatcher of the login id just incase
    $sep_part = "<br/> Just incase you've forgotten, your login id is:<code style='font-size:22px;'>".$d_log_id."</code>";
    if($user_db != 'dispatchers')//change the text
      $sep_part = "";

    $msg = "Hi ".$dbq->record[0][0].",<br/>We noticed you want to change your password
    Please <a href=\"".$link."\">click here</a> or use the link below to reset your password<br/>".$link."
    ".$sep_part."<br/><br/>Please ignore if you think this mail was sent by mistake.";
    
    if(email_send($from,$to,$subject,$msg)){
      pekky_add_array_to_print(['mail_sent'=>'true']);
    }
    else{
      pekky_add_array_to_print(['mail_sent'=>'false']);
    }
   }
  pekky_set_success_state();
  exit(pekky_print_json_state(['reset_code' => $confirm_code]));//end the program
