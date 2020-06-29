<?php
 /**
  * Script to carry out the sign up
  *
  * It accepts the parameter email,fullname,phone,password using GET
  * Please note that this file only helps handle, determine and prevent any empty parameter
  * If a required parameter is empty, it'll return false
  * You should make sure all those are handled on your end
 * 
  * @return JSON | Array user_id on success or Error status on failure.
  * @author Precious Omonzejele <omonze@peepsipi.com>
  */
  header("Content-Type: application/json");
  require "../inc/_config.php";
  require "../vendor/autoload.php";

  $email = isset($_GET["email"]) ? $_GET["email"] : false;
  $password = isset($_GET["password"]) ? hash_password($_GET["password"]) : false;
  $fullname = isset($_GET["fullname"]) ? $_GET["fullname"] : false;
  $phone = isset($_GET["phone"]) ? $_GET["phone"] : false;

  if(!($email && $password && $fullname && $phone)){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
$db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 $dbq->set_fetch_mode("assoc");
//check if email exists
 $dbq->get(query_live("select id from dispatchers WHERE email = ?"),[$email]);
if($dbq->row_count == 1){
pekky_set_failure_state(1,"email already exists");
exit(pekky_print_json_state());
}

//continue
//generate id
$_id = md5(session_id().time());
//generate activation link
$link = md5($_id.time());
$nk = md5($_id.$link.time());
//you have to reshuffle it since we're reducing it to 6 digits, to avoid repitition
$letters = new Letters();
$i_code = $letters->backwards($link);
$id_code = substr($i_code, 0, 6);
$c_code = $letters->backwards($nk);
$confirm_code = substr($c_code, 0, 6);

$data = array('id'=>'?','login_id'=>$id_code,'fullname'=>'?','email'=>'?','password'=>'?','phone'=>'?','activation_link'=>'?','date_time'=> 'NOW()');
if( !$dbq->add("dispatchers",$data, array($_id,$fullname,$email,$password,$phone,$link) ) ){
  pekky_set_failure_state(-1,$dbq->err_msg);
  exit(pekky_print_json_state());
}
pekky_set_success_state();
//set mail
//send mail to the user
require "../inc/_mail.php";
$from =  "Wave Us Support <no-reply@".SITE_DOMAIN.">";
//$mailer->username = "no-reply@".SITE_DOMAIN;
//$mailer->password = "dont.wavy.mail#$";
$subject = "Confirm your registeration on Wave Us";
$mail_link = '<code>'.$link.'</code>';//SITE_URL."activate.php?ref=".$link."&email=".$email;
//main stuff
$msg = "<p style=\"line-height:23px;\">Hi <strong>".$fullname."</strong>,<br/> 
Please confirm your registeration to fully activate your account by using the code below<br/>".$mail_link."
<br/>Please ignore if you think this mail was sent by mistake.
</p>";
if(email_send($from,$email,$subject,$msg)){//indicate that the mail was sent
  pekky_print_json_state(['user_id'=> $_id,'confirm_link'=>$confirm_code,'mail_sent' => 'true']);
}
else{
  pekky_print_json_state(['user_id'=>$_id,'login_id'=>$id_code,'confirm_link'=>$confirm_code,'mail_sent' => 'false']);
}
