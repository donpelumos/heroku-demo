<?php
 /**
  * Script to change user status
  *
  * It accepts the parameter user_id,ids(this should be in csv),user_type,status(values are active or inactive),dont_send_mail using GET
  * Please note that this file only helps handle, determine and prevent any empty parameter
  * If a required parameter is empty, it'll return false
  * You should make sure all those are handled on your end
  * 
  * @return JSON on success or Error status on failure.
  * @author Precious Omonzejele <omonze@peepsipi.com>
  */
  header("Content-Type: application/json");
  require "../inc/_config.php";
  require "../vendor/autoload.php";
 
  $user_id = isset($_GET["user_id"]) ? $_GET["user_id"] : false;
  $ids = isset($_GET["ids"]) ? trim($_GET["ids"]) : false;
  $user_type = isset($_GET["user_type"]) ? $_GET["user_type"] : 1;
  $status = isset($_GET["status"]) ? $_GET["status"] : false;
  $n_send_mail = isset($_GET["dont_send_mail"]) ? true : false;

  if(!($user_id && $ids && $status)){
    pekky_set_failure_state(0,"empty field(s)");
    exit(pekky_print_json_state());//end the program
  }
  $db = new DBCon(CON_TYPE);
  $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 //check if user exists, to preceed with the action
 if(!user_exists($dbq,$user_id,3)){
    pekky_set_failure_state(0,'User doesn\'t exist');
    exit(pekky_print_json_state());
 }
 //check if admin has priviledges to take this action
 if(!is_allowed_admin_type($dbq,$user_id)){
  pekky_set_failure_state(1,'Sorry, authorisation denied');
  exit(pekky_print_json_state());
 }
 $dbq->set_fetch_mode("assoc");
 $user_db = user_type($user_type);
 //now start deleting
//get the value through csv
$change_records = explode(',',$ids);

//require mail for mail stuff
require '../inc/_mail.php';

//mail stuff
$subject = 'Suspension';
$status_text = 'suspended';
$suspension_text_note = 'Once your account has been <strong>re-activated</strong>, an email will be sent to you notifying you of your account
being re-activated';
$meaning_text = 'This mans that you will not be able to log in to';

if($status == 'active'){
  $status = 1;
  $subject = 'Re-activation';
  $status_text = 're-activated';
  $suspension_text_note = '';
  $meaning_text = 'This mans that you are able to log in to';
}
else{ 
  $status = 0;
}

$subject .= ' of your '.SITE_TITLE.' account';
$from =  "Wave Us Support <no-reply@".SITE_DOMAIN.">";
//
$total = count($change_records);
$s = 0;//for counting success
$user_mails_sent = 0;//for counting mails that sent
//$admin_mails_sent = 0;//for counting mails
for($i = 0; $i < $total; $i++){
  $id = (string)$change_records[$i];
  if(!empty(trim($id))){
    if($user_id == $id)//cant change anything for same user
      continue;
    
    $dbq->change($user_db,['active'=>'?'],'WHERE id = ?',[$status,$id]);
    if($dbq->rows_affected == 1){
      $s++;

      //now check if the send mail
      if(!$n_send_mail){// the mail part
        //get the user details
        $dbq->get(query_live('SELECT fullname,email from '.$user_db.' where id = ?'),[$id]);
        $to = $dbq->record[0]['email'];
        $msg = '<p>Hi <strong>'.$dbq->record[0]['fullname'].'</strong>, your account on '.SITE_TITLE.' has been <strong>'.$status_text.'</strong>.</p>
        <p>What does this mean?</p>
        <p>This means that you will not be able to log in to '.SITE_TITLE.' web applications to perform activities.<br/>'.$suspension_text_note.'
        </p><br/>
        <p>Cheers, the '.SITE_TITLE.' team.</p>';
        if(email_send($from,$to,$subject,$msg) )
          $user_mails_sent++;
      }

    }
  }
}
pekky_set_success_state();
pekky_print_json_state(['total'=>$total,'changed'=>$s,'users_mails_sent'=>$user_mails_sent]);
