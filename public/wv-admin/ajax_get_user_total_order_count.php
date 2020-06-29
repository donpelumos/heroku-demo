<?php session_start();
require_once 'vendor/autoload.php';
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$request = isset($_GET['request']) ? trim($_GET['request']) : '';
$user_type = isset($_GET['user_type']) ? trim($_GET['user_type']) : '';
$order_status = 'all';
if(strpos($request,'rde') !== false)
    $request = 'total_orders';
else if(strpos($request,'moun') !== false){
    $request = 'total_amount';
    $order_status = 'complete';//set status to complete, cause we only calculate total amount for complete orders
}
$admin = new Admin();
/** returns proper error value based on request */
function error_val(){
  global $admin,$request;
  if($request == 'total_amount')
    return $admin->amount(0);
  else
    return '0';
}
if(!$admin->is_logged_in()){
    exit(error_val());
}
$type_no = $admin->get_user_type($user_type);
if($type_no == 0){
    exit(error_val());
}
$result = $admin->get_user_order_total_count($user_id,$type_no,$order_status);
if(!$result)
    exit(error_val());
if($request == 'total_amount')
    echo $admin->amount($result[$request]);
else
    echo $result[$request];
