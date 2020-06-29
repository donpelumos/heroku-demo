<?php
 /**
  * Script to sign up a new user(admin or dispatcher)
  *
  * It accepts the parameter user_id(the admin id),email,fullname,phone(optional),admin_type(optional: default is 2),password(optional),dont_send_mail(optional,should most likely be ignored) using GET
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

  $user_id = isset($_GET["user_id"]) ? $_GET["user_id"] : false;
  $email = isset($_GET["email"]) ? $_GET["email"] : false;
  $password = isset($_GET["password"]) ? hash_password($_GET["password"]) : hash_password('fake_password');//empty, can always be reset
  $fullname = isset($_GET["fullname"]) ? $_GET["fullname"] : false;
  $phone = isset($_GET["phone"]) ? $_GET["phone"] : ' ';
  $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : 2;
  $admin_type = isset($_GET['admin_type']) ? $_GET['admin_type'] : 2;
  /*$address = isset($_GET["address"]) ? $_GET["address"] : "";*/
  $n_send_mail = isset($_GET["dont_send_mail"]) ? true : false;

  if(!($user_id && $email && $fullname && $phone)){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  $user_db = user_type($user_type);
 $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 $dbq->set_fetch_mode("assoc");
 //check if user exists
 if(!user_exists($dbq,$user_id,3) ){
  pekky_set_failure_state(0,'User doesn\'t exist');
  exit(pekky_print_json_state());
}
 //check if email exists
 $dbq->get(query_live("select id from ".$user_db." WHERE email = ?"),[$email]);
if($dbq->row_count == 1){
pekky_set_failure_state(1,"email already exists");
exit(pekky_print_json_state());
}
//continue
//generate id
$_id = md5(session_id().time());
//generate activation link
$link = md5($_id.time());
$id_code = '';
if($user_type == 2){
//you have to reshuffle it since we're reducing it to 6 digits, to avoid repitition
$letters = new Letters();
$confirm_code = $letters->backwards($link);
 $id_code = substr($confirm_code, 0, 6);
}
$login_id_param = array('login_id'=>$id_code);//for dispatcher
$admin_type_param = array('admin_type'=>$admin_type);//for admin
if($user_type != 2)
  $login_id_param = array();
if($user_type != 3)
  $admin_type_param = array();

$data = array_merge($admin_type_param,$login_id_param,array('id'=>'?','fullname'=>'?','email'=>'?','password'=>'?','phone'=>'?','activation_link'=>'?','date_time'=> 'NOW()'));
$binding = array($_id,$fullname,$email,$password,$phone,$link);
if( !$dbq->add($user_db,$data, $binding ) ){
  pekky_set_failure_state(-1,$dbq->err_msg);
  exit(pekky_print_json_state());
}
if(!$n_send_mail){//means you should send default mail
  $cat_type = '';
  if(strpos($user_db,'dispatch') !== false)
    $cat_type = 'a dispatcher';
  else if(strpos($user_db,'admi') !== false)
    $cat_type = 'an admin';//you can add admin type priviledges detail here :)
  else if(strpos($user_db,'user') !== false)
    $cat_type = 'a user';  
//send mail to the user
require "../inc/_mail.php";
$from =  "Wave Us Support <no-reply@".SITE_DOMAIN.">";
//$mailer->username = "no-reply@".SITE_DOMAIN;
//$mailer->password = "dont.wavy.mail#$";
$subject = "Confirm your registeration as ".$cat_type." on ".SITE_TITLE;
$mail_link = SITE_URL.'reset_password.php?mail='.$email.'&link='.$link.'&type='.$user_db;
//main stuff
$sep_part = " with your login id: <code style='font-size:22px'>".$id_code."</code> and the password you're going to set. Please always keep your login id safe, if you forget it, 
just try reseting your password, a message will be sent to your email.";
if($user_db != 'dispatchers')//change the text
  $sep_part = " with this email and the password you're going to set.";

$msg = "<p style=\"line-height:23px;\">Hi <strong>".$fullname."</strong>,<br/> 
You were recently registered as ".$cat_type." on <strong>".SITE_TITLE.".</strong><br/>
Please confirm your registeration to fully activate your account by <a href=\"".$mail_link."\">clicking here</a> or 
the link below<br/>".$mail_link." <br/>Please note you'll be asked to enter a password, after you've done that, 
you can login to your account".$sep_part."
<br/><br/>Please ignore if you think this mail was sent by mistake.
</p>";
if(email_send($from,$email,$subject,$msg)){//indicate that the mail was sent
  pekky_add_array_to_print(['mail_sent'=>'true']);
}
else{
  pekky_add_array_to_print(['mail_sent'=>'false']);
}
}
pekky_set_success_state();
switch($user_type){//display based on user type
  case 1://user
    pekky_print_json_state(['id'=> $_id,'confirm_link'=>$link]);
  break;
  case 2://dispatcher
    pekky_print_json_state(['id'=> $_id,'login_id'=>$id_code,'confirm_link'=>$link]);
  break;
  case 3://admin
    pekky_print_json_state(['id'=> $_id,'admin_type'=>$admin_type,'confirm_link'=>$link]);
  break;
}