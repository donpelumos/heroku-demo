<?php
 /**
  * Script to update details
  *
  * It accepts the parameter fullname,email,phone,address,user_id,user_type(optional),dont_send_mail(optional although it makes sense for mail to be sent if an email was changed :)) using GET using GET
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
  
  $email = isset($_GET["email"]) ? $_GET["email"] : "";
  $user_id = isset($_GET["user_id"]) ? $_GET["user_id"] : "";
  $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : 2;
  $fullname = isset($_GET["fullname"]) ? $_GET["fullname"] : "";
  $phone = isset($_GET["phone"]) ? $_GET["phone"] : "";
  $n_send_mail = isset($_GET["dont_send_mail"]) ? true : false;
//  $address = isset($_GET["address"]) ? $_GET["address"] : "";

  if(!($email && $user_id && $fullname && $phone /*&& $address*/)){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  $user_db = user_type($user_type);
  $db = new DBCon(CON_TYPE);
 $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 $dbq->set_fetch_mode("assoc");
 if(!user_exists($dbq,$user_id,$user_type)){
    pekky_set_failure_state(0,"User doesn't exist");
    exit(pekky_print_json_state());  
 }
 $data = array('fullname'=>'?','email'=>'?','phone'=>'?'/*,'address'=>'?'*/,'date_time_updated'=>'NOW()');
$binding = array($fullname,$email,$phone/*,$address*/);
 
 //Check if the email is changed. 
  $email_changed = false;
 if( !$dbq->get(query_live("select id,email from ".$user_db." WHERE id = ? "),[$user_id])){
    //error
    pekky_set_failure_state(-1,$dbq->err_msg);
	exit(pekky_print_json_state());
  }
  $old_email = $dbq->record[0]['email'];

  //no error, continue
  if($dbq->row_count == 1 && $old_email != $email){ // New email
	if( user_exists($dbq, $email, 1 ) ){ //new email already exists, ta!
		pekky_set_failure_state(1,"Sorry, the new E-mail Address you entered is already in use.");
		exit(pekky_print_json_state());
	}
	unset( $data['email'] );
	$confirm_code = md5(session_id().time());
	$data = array_merge( $data, array( 'new_info' => '?', 'activation_link' => '?' ) );
	$binding = array($fullname,$phone/*,$address*/,$email,$confirm_code);
 	$email_changed = true;
  }
 //always make the user id the last bind value :)
 $binding = array_merge($binding,array($user_id));

if(!$dbq->change($user_db,$data,query_live("Where id = ?"), $binding) ){
    pekky_set_failure_state(-1,$dbq->err_msg);
    exit(pekky_print_json_state());  
}

if($dbq->rows_affected == 1){//successful
	if($email_changed && !$n_send_mail){//email changed, && means you should send default mail
		require "../inc/_mail.php";
		$link = SITE_URL.'reset_email.php?mail='.$old_email.'&new='.$email.'&link='.$confirm_code.'&type='.$user_db;

		$to = $old_email;
		$from = SITE_TITLE." Support <no-reply@".SITE_DOMAIN.">";
		$subject = "Request to change E-mail Address on ".SITE_TITLE;
		$msg = "Hi ".$fullname.",<br/>We noticed you requested to change your email address to <strong>".$email."</strong>.<br/>
		Please make sure you can access that email address, and it works well.
		Please <a href=\"".$link."\">click here</a> or use the link below to confirm approval<br/>".$link."
		<br/><br/>Please if you do not remember initiating this action, do not click the link. Do well to request to change your password also. ";

		if(email_send($from,$to,$subject,$msg)){
			$mail_sent = 'true';
		}
		else{
			$mail_sent = 'false';
		}
		pekky_add_array_to_print(['email_changed'=>'true','confirm_link'=>$confirm_code,'mail_sent'=>$mail_sent]);

	}

    pekky_set_success_state();
    pekky_print_json_state();
}
else{//false
    pekky_set_failure_state(0,"Couldn't update record:".$dbq->err_msg);
    pekky_print_json_state();  
}
