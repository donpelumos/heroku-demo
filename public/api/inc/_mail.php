<?php 
/**
 * For mail stuff
 * @author Precious Omonzejele <omonze@peepsipi.com>
 */

/**
 * Mail method to send
 * 
 * @param string $from sets who the mail is from
 * @param string $to a valid email to send to
 * @param string $subject subject of the mail
 * @param string $msg message of the mail
 * @param string $reply_to(optional) mail to reply to
 * @return bool true if sent, false otherwise
 */
 function email_send($from,$to,$subject,$msg,$reply_to = ''){
	$headers = "From: ".$from." \r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	if(!empty(trim($reply_to)))
		$headers .= "Reply-To: ".$reply_to."\r\n";
	$message = "<p style=\"line-height:32px;\">".$msg."</p>";
	if(strpos($msg,'<html') !== false || strpos($msg,'<body') !== false)//the message contains a full html ish
		$message = $msg;
	$send = mail($to, $subject, $message,$headers);
	return $send;
 }
/**
 * Order mail template
 * 
 * Helps construct a default email for the order
 * 
 * @param object $query the query object
 * @param int $order_id the order id
 * @param string $order_type the type of order
 * @param mixed $user_id the user id, to get the details
 * @param int $user_type (optional) the type of user, default is 1
 * @param bool $for_admin (optional) if it's for admin, default is false
 * @param string $msg (optional) in case you want to change the default message
 * @return bool, true if message sent, false otherwise
 */
 function order_mail_template($query,$order_id,$order_type,$user_id,$user_type = 1,$for_admin = false,$msg = ''){
	$msg = (isset($msg) ? trim($msg) : '');
	$order_type = (isset($order_type) ? $order_type : 1);
	$user_type = (isset($user_type) ? $user_type : 1);
	$subject = '';
	$message = '';
	//get user info
	$user_db = user_type($user_type);
	$query->set_fetch_mode("assoc");
	$query->get(query_live("select fullname,email FROM ".$user_db." WHERE id = ?"),[$user_id]);
	$user_record = for_single_array($query->record);
	$name = $user_record['fullname'];
	if($for_admin == true)
		$user_type = 3;
	$ini_msg = order_msg_template($query,$order_id,$order_type,$user_type,$name);
	//get order_time
	$query->get(query_live("select date_time FROM orders WHERE id = ?"),[$order_id]);
	$order_record = for_single_array($query->record);
	
	$order_stuff = array(
        ORDER_COMPLETE_CODE => array('user'=> array(),'dispatcher'=> array(),'admin'=> array()),//completed
        ORDER_PROCESS_CODE => array('user'=> array(),'dispatcher'=> array(),'admin'=> array()),//processing
        ORDER_HOLD_CODE => array('user'=> array(),'dispatcher'=> array(),'admin'=> array()),//on hold
        ORDER_CANCEL_CODE => array('user'=> array(),'dispatcher'=> array(),'admin'=> array())//cancelled
	);
	//completed
	$order_stuff[ORDER_COMPLETE_CODE]['user'] = ['sub'=> '','msg'=>$ini_msg];
	$order_stuff[ORDER_COMPLETE_CODE]['dispatcher'] = ['sub'=> '', 'msg'=>$ini_msg];
	$order_stuff[ORDER_COMPLETE_CODE]['admin'] = ['sub'=> '', 'msg'=>$ini_msg];
	//processing
	$order_stuff[ORDER_PROCESS_CODE]['user'] = ['sub'=> '', 'msg'=>$ini_msg];
	$order_stuff[ORDER_PROCESS_CODE]['dispatcher'] = ['sub'=> '', 'msg'=>$ini_msg];
	$order_stuff[ORDER_PROCESS_CODE]['admin'] = ['sub'=> '', 'msg'=>$ini_msg];
	//on hold
	$order_stuff[ORDER_HOLD_CODE]['user'] = ['sub'=> '', 'msg'=>$ini_msg];
	$order_stuff[ORDER_HOLD_CODE]['dispatcher'] = ['sub'=> '', 'msg'=>$ini_msg];
	$order_stuff[ORDER_HOLD_CODE]['admin'] = ['sub'=> '', 'msg'=>$ini_msg];
	//cancelled
	$order_stuff[ORDER_CANCEL_CODE]['user'] = ['sub'=> '', 'msg'=>$ini_msg];
	$order_stuff[ORDER_CANCEL_CODE]['dispatcher'] = ['sub'=> '', 'msg'=>$ini_msg];
	$order_stuff[ORDER_CANCEL_CODE]['admin'] = ['sub'=> '', 'msg'=>$ini_msg];
  
	$user_stuff = '';
	switch($user_type){
		 case 2://dispatcher 
			$user_stuff = 'dispatcher'; 
			$subject = SITE_TITLE.' order receipt from '.date('M, d Y',strtotime($order_record['date_time']));
		break;
		case 3://admin
			$user_stuff = 'admin'; 
			$subject = ''.SITE_TITLE.' order(#'.$order_id.') receipt from '.date('M, d Y',strtotime($order_record['date_time']));
		break;
		default://user
			$user_stuff = 'user';
			$subject = 'Your '.SITE_TITLE.' order(#'.$order_id.') on '.date('M, d Y',strtotime($order_record['date_time']));
	}
	//$subject = $order_stuff[$order_type][$user_stuff]['sub'];
	$message = !empty($msg) ? $msg : $order_stuff[$order_type][$user_stuff]['msg'];
	//send
	$f_email = get_site_option($query,'order_email');
	$from = SITE_TITLE.' order emails <'.$f_email.'>';
	$to = $user_record['email'];
	if($for_admin){
		$to = $f_email;
	}
	//echo '<br><br>'.$to.'<br>'.$from.'<br>'.$subject.'<br/>'.$message;
	$r_t = get_site_option($query,'order_email');//reply to this mail
	if(email_send($from,$to,$subject,$message,$r_t)){return true;}
	else{return false;}
 }

/** 
 * Helps construct a message based on order type
 * 
 * @param object $query the query object
 * @param int $order_id the order id
 * @param string $order_type the type of order
 * @param int $user_type the type of user
 * @return string the msg
 */
function order_msg_template($query,$order_id,$order_type,$user_type,$user_name){
	global $meta_backend;
	$user_name = isset($user_name) ? trim($user_name) : '';
	$msg = '<html><head><style type="text/css">
	.wv-stuff{
		font-family: Arial,sans-serif;
		color:#333;
	}
	.wv-table{
		margin:2px;
		border:1px solid rgba(0,0,0,0.2);
		box-shadow:1px 2px 2px 1px rgba(0,0,0,0.2);
		width:85%;
		margin:auto;
		border-top:4px solid #010101;
	}
	.wv-table tbody td{
		border-bottom:1px solid rgba(0,0,0,0.1) !important;
		padding:5px 10px;
		font-size:17px;
		margin:0;
	}
	.wv-table tr{
		margin:0;
	}
	.wv-table tbody tr:last-child td{
		border-bottom:none;
	}
	.wv-table .left{
		text-align:left;
		font-weight:600;
	}
	.wv-table .right{
		text-align:right;
	}
	.main-footer{
		padding:25px 10px;
		background-color:#010101;
		color:#ffffff;
	}
		</style></head><body class="wv-stuff">';
	$msg_1 = '';
	$msg_state = '';
	$msg_2 = '';
	$msg_footer = '';
	$footer_style_1 = ' style="border:1px solid rgba(0,0,0,0.1);padding: 5px 15px;font-size: 18px;color: #0c5460;background-color: #d1ecf1;border-color: #bee5eb;" ';
	$footer_style_2 = ' style="border:1px solid rgba(0,0,0,0.1);padding: 5px 15px;font-size: 18px;color: #0c5460;background-color: #d1ecf1;border-color: #bee5eb;" ';
	switch($user_type){
		case 3://admin
		$msg_1 .='<h2>Order <strong>#'.$order_id.'</strong> by <strong>'.$user_name.'</strong> ';
		$msg_2 .= '</h2><h3>Order details are shown below for your reference</h3>';
		$msg_footer .='<p'.$footer_style_1.'>Please keep this email for reference
		purpose.<br/> <a href="'.SITE_URL.'wv-admin/order.php?id='.$order_id.'">click here to view the order</a></p>
		<p'.$footer_style_2.'> <i>Delivery could take 24 to 72 hours.</i>
		</p><p class="main-footer" style="text-align:center;">'.date('Y').'. '.SITE_TITLE.' team.</p>';
		break;
		default://user
		$msg_1 .='<h2>Your order <strong>#'.$order_id.'</strong> ';
		$msg_2 .= '</h2><h3>Your order details are shown below for your reference</h3>';
		$msg_footer .='<p'.$footer_style_1.'>Please keep this email for invoice/reference
		purpose.<br/>Thank you for your order.</p>
		<p'.$footer_style_2.'> <i>Delivery could take 24 to 72 hours.</i>
		</p><p class="main-footer" style="text-align:center;">'.date('Y').'. '.SITE_TITLE.' team.</p>';
	}
	switch($order_type){
		case ORDER_COMPLETE_CODE:
		$msg_state .= 'has been completed';//completed
		break;
		case ORDER_PROCESS_CODE:
		$msg_state .= 'is now being processed';//processing
		break;
		case ORDER_HOLD_CODE:
		$msg_state .= 'is on-hold until a dispatcher picks up '.(($user_type == 1)?'your':'the').' order';//on hold
		break;
		default:
		$msg_state .= 'has been cancelled';//cancelled
	}
	$msg .= $msg_1.' '.$msg_state.' '.$msg_2;
	$table_ish = '<table class="wv-table"><thead><tr><td> </td><td> </td></tr></thead><tbody>';
	//get the price
	$query->set_fetch_mode("assoc");
	$query->get("SELECT price,payment_type from orders WHERE id = ?",[$order_id]);
	$price = trim($query->record[0]['price']);
	$payment_type = trim($query->record[0]['payment_type']);
	$darken_style = ' style="background-color:#f0f0f0" ';
	$count = 0;
	if(!empty($price)){//add price
		$table_ish .= '<tr><td class="left">price</td><td class="right">'.amount($price).'</td></tr>';
		$count++;
	}
	if(!empty($payment_type)){//add payment type
		$table_ish .= '<tr'.($count==1 ? $darken_style : '').'><td class="left">payment mode</td><td class="right">'.strtoupper($payment_type).'</td></tr>';
		$count++;
	}
	//get proper modulus answer to use
	$module_ans = 1;
	if(!empty($price) && !empty($payment_type))
		$module_ans = 0;
	else if( (empty($price) && !empty($payment_type)) || (!empty($price) && empty($payment_type)) )
		$module_ans = 0;
	//
	//get the order meta.
	$order_meta = get_order_meta($query,$order_id,'','shown');
	foreach($order_meta as $key => $value){
		$darken_style = ' style="background-color:#f0f0f0;"';
		if(($count%2) == $module_ans)
			$darken_style = '';
		//check if payment_mode meta already exists
		if(!empty($payment_type) && strpos($key,'yment_mode') !== false)//there's already payment type, so ignore showing thisi
			continue;
		if(!in_array($key,$meta_backend)){
			$table_ish .= '<tr'.$darken_style.'><td class="left">'.str_replace('_',' ',$key).'</td><td class="right">'.$value.'</td></tr>';
			$count++;
		}
	}
	$table_ish .='</tbody></table></body></html>';
	$msg .= $table_ish.$msg_footer;
	//echo $msg;
	return $msg;
}
