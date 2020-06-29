<?php
/**
  * Script to send email
  *
  * It accepts the parameter from_name,from_email,to,subject,message,reply_to(optional,the email the receiver can reply to) using GET
  * if from_name is empty, default is Wave us transit Support, if from_email is empty default is no-reply@waveustransit.com
  * It transfers the user from the temp user table to the main table
  * Please note that this file only helps handle, determine and prevent any empty parameter
  * If a required parameter is empty, it'll return false
  * You should make sure all those are handled on your end
  * 
  * @return JSON true on success or Error status on failure.
  * @author Precious Omonzejele <omonze@peepsipi.com>
  */
  header("Content-Type: application/json");
//send mail to the user
require "../inc/_mail.php";
$to = isset($_GET["to"]) ? $_GET["to"] : false;
$from_name = isset($_GET["from_name"]) ? $_GET["from_name"] : SITE_TITLE." Support";
$from_email = isset($_GET["from_email"]) ? $_GET["from_email"] : "no-reply@".SITE_DOMAIN;
$subject = isset($_GET["subject"]) ? $_GET["subject"] : false;
$msg = isset($_GET["message"]) ? $_GET["message"] : false;
$reply_to = isset($_GET["reply_to"]) ? $_GET["reply_to"] : "";
if(!($to && $from_name && $from_email && $subject && $msg)){
    pekky_set_failure_state(0,"Empty field(s)");
    exit(pekky_print_json_state());
}

$from = "".$from_name." <".$from_email.">";
if(email_send($from,$to,$subject,$msg,$reply_to)){
    pekky_set_success_state();
    pekky_print_json_state();
}
else{
    pekky_set_failure_state(-1,"Couldnt send");
    pekky_print_json_state();
}
?>