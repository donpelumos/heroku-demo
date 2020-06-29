<style>
	.wv-table{
		margin:2px;
		border:1px solid rgba(0,0,0,0.2);
		box-shadow:1px 2px 2px 1px rgba(0,0,0,0.2);
		width:85%;
		margin:auto;
		border-top:4px solid #010101;
	}
	.wv-stuff{
		font-family: Arial,sans-serif;
	}
	.wv-table tbody td{
		border-bottom:1px solid rgba(0,0,0,0.1) !important;
		padding:5px 10px;
		font-size:18px;
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
</style>
<?php
//Silence is all about being the SNAKE IN THE MONKEY SHADOW ;)
require "inc/_config.php";
require "vendor/autoload.php";
 
//check if the order meta has a correct format
 $db = new DBCon(CON_TYPE);
 $con = $db->connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
 $dbq = new Query($con);
 $order_meta = array('test'=>'2','asd'=>2,'test'=>'45');
 //echo json_encode($order_meta);
 
 require "inc/_mail.php";
$order_id = 2;
$user_id = 'edf98bc42533a808cf058ada6f25fa6b';
order_mail_template($dbq,$order_id,1,$user_id);
  //pekky_add_array_to_print(['mail_sent_to_user'=>'true']);
//else
 //pekky_add_array_to_print(['mail_sent_to_user'=>'false']);
//for the admin
$t_dbq = new Query($con);//did this cause caling the order_mail_template a second time, the query wasnt working, returning false for i dont know why
order_mail_template($t_dbq,$order_id,1,$user_id,1,true);
  //pekky_add_array_to_print(['mail_sent_to_admin'=>'true']);

  //pekky_add_array_to_print(['mail_sent_to_admin'=>'false']);
