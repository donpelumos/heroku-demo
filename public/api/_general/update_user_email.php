<?php
/**
  * Script to update User email
  *
  * It accepts the parameter user_id(can also be email),user_type,new_email,dont_send_mail(optional) using GET
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
  $new_email = isset($_GET["new_email"]) ? trim($_GET["new_email"]) : false;
  $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : 1;
  $n_send_mail = isset($_GET["dont_send_mail"]) ? true : false;

  if( !( $user_id && $new_email ) ){
	pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }

  $user_db = user_type($user_type);
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
  $dbq = new Query($con);
  $dbq->set_fetch_mode("assoc");

 //check if user exists.
 if( !user_exists($dbq,$user_id,$user_type) ){
    pekky_set_failure_state(0,'User doesn\'t exist');
    exit(pekky_print_json_state());
 }

 // Check if the new email tallies with what is sent
 $binding = array($user_id,$user_id,$new_email);
if(!$dbq->get(query_live("SELECT id,fullname,email,new_info from ".$user_db." Where (id = ? OR email = ?) AND new_info = ?"), $binding) ){
    pekky_set_failure_state(-1,$dbq->err_msg);
    exit(pekky_print_json_state());  
}

if($dbq->row_count < 1){ // Doesnt exist
	// Could be developer error, but can mostly be user error, its better to show to user, so they can easily report, than otherwise :)
    pekky_set_failure_state(1,"Sorry, the new email requested '".$new_email."' could not be confirmed, try again. If this persists, contact support.");
    exit(pekky_print_json_state());
}

 // Use the new email from the db, trust issues :)
 $records = $dbq->record;
 $new_email = $records[0]['new_info'];


 // Check if new email already exists.
 if( user_exists($dbq,$new_email,$user_type) ){
    pekky_set_failure_state(1,'Sorry, the new E-mail Address you\'re trying to change to is already in use. Please try another email.');
    exit(pekky_print_json_state());
 }

 $data = array('email'=>'?','new_info'=>'','activation_link'=>'','date_time_updated'=>'NOW()');
 $binding = array($new_email,$user_id,$user_id);
 if(!$dbq->change($user_db,$data,query_live("Where (id = ? OR email = ?)"), $binding) ){
    pekky_set_failure_state(-1,$dbq->err_msg);
    exit(pekky_print_json_state());  
 }

 if($dbq->rows_affected == 1){//successful
	$array_data = array( 'user_id'=>$records[0]['id'],'old_email'=>$records[0]['email'], 'new_email'=>$new_email );
	if(!$n_send_mail){// You should send default mail
		require "../inc/_mail.php";
		$fullname = $records[0]['fullname'];
		$to = $new_email;
		$from = SITE_TITLE." Support <no-reply@".SITE_DOMAIN.">";
		$subject = "Approved Request to change E-mail Address on ".SITE_TITLE;
		$msg = "Hi ".$fullname.",<br/>Your request to change your email address has been approved.<br/>
		When next you're to Log In to our App, this is the email you will use.<br/>
		Take care and stay safe!";

		if(email_send($from,$to,$subject,$msg)){
			$mail_sent = 'true';
		}
		else{
			$mail_sent = 'false';
		}
		$mail_data = array( 'mail_sent'=>$mail_sent );
		// Merge
		$array_data = array_merge( $array_data, $mail_data );
	}
	
	pekky_add_array_to_print( $array_data );
    pekky_set_success_state();
    pekky_print_json_state();
 }
 else{//false
    pekky_set_failure_state(0,"Couldn't update record:".$dbq->err_msg);
    pekky_print_json_state();
 }
 