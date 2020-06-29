<?php session_start();
require_once 'vendor/autoload.php';
$admin = new Admin();
$admin->authorise();
$id = isset($_GET['id']) ? $_GET['id'] : '';

if(isset($_POST['submitter'])){
	$admin->submit_bulk('order','Order #'.$id.' status updated successfully.');//and you can relax, it does everything for you :)
}
$order = $admin->get_order_data($id);
if(!($order)){
    $_404 = new _404Message();
    $_404->display();
    exit();
}
$admin->header('Order #'.$order['id']);
$admin->menu('orders');
$status_tag = '';

switch($order['status']){
    case ORDER_CANCEL_CODE:
        $status_tag = '<span class="cancel"><i class="fas fa-2x relate status--cancel fa-window-close"></i> Cancelled</span>';
    break;
    case ORDER_HOLD_CODE:
        $status_tag = '<span class="on-hold"><i class="fas fa-2x relate status--on-hold fa-minus-circle"></i> On hold</span>';
    break;
    case ORDER_PROCESS_CODE:
        $status_tag = '<span class="process"><i class="fas fa-2x relate status--process fa-ellipsis-h"></i> Processing</span>';
    break;
    case ORDER_COMPLETE_CODE:
        $status_tag = '<span class="complete"><i class="fas fa-2x relate status--complete fa-check-circle"></i> Completed</span>';
    break;
}
$user = $admin->get_user_data($order['user_id'],1);
$user_details = '';
if(!$user){
    $user_details .= 'N/A';
}
else{
    $u_email = isset($user['email']) ? '<a href="mailto:'.$user['email'].'" title="Send a mail to '.$user['fullname'].'"><i class="fas fa-envelope"></i></a>' :'';
    $u_phone = isset($user['phone']) ? '<a href="tel:'.$user['phone'].'" title="call '.$user['fullname'].'"><i class="fas fa-phone"></i></a>' :'';
    $user_details .= '<span class="call-to-action"><span class="block-email">'.$user['fullname'].'</span> '.$u_email.' '.$u_phone.'</span>';
}

$user = $admin->get_user_data($order['dispatcher_id'],2);
$dispatcher_details = '';
if(!$user){
    $dispatcher_details .= 'N/A';
}
else{
    $u_email = isset($user['email']) ? '<a href="mailto:'.$user['email'].'" title="Send a mail to '.$user['fullname'].'"><i class="fas fa-envelope"></i></a>' :'';
    $u_phone = isset($user['phone']) ? '<a href="tel:'.$user['phone'].'" title="call '.$user['fullname'].'"><i class="fas fa-phone"></i></a>' :'';
    $dispatcher_details .= '<span class="call-to-action"><span class="block-email">'.$user['fullname'].'</span> '.$u_email.' '.$u_phone.'</span>';
}

$extras = '<div class="card-header order-extras">';
if(isset($order['meta']) && !empty($order['meta'])){
    $ignore_meta = array('pickup_coord','delivery_coord','payment_mode');//meta details to ignore
    $extras .= '<h4>Extra details</h4></div><div class="card-body card-block">';
    foreach ($order['meta'] as $key=>$val){
        if(in_array($key,$ignore_meta))
            continue;
        $label = '';
        //try to detect some things
        if(strpos($key,'phone') !== false){
            $label = '<i class="fa fa-phone"></i>';
            $p =  '<span class="call-to-action"><a href="tel:'.$val.'" title="call"><i class="fas fa-phone"></i></a></span>';
            $val = $p;
        }
        else if(strpos($key,'address') !== false){
            $label = '<i class="fa fa-map-marker-alt"></i>';
        }
        else if(strpos($key,'duratio') !== false){
            $label = '<i class="fa fa-clock-o"></i>';
        }
        else if(strpos($key,'name') !== false){
            $label = '<i class="fas fa-address-card"></i>';
            $val = '<span class="block-email">'.$val.'</span>';
        }
        else if(strpos($key,'descript') !== false){
            $label = '<i class="fa fa-paperclip"></i>';
        }
        else if(strpos($key,'auth') !== false){/////added part, check later :)
            $label = '<i class="fa fa-id-badge"></i>';
			$key = 'order authentication key';
        }
        $key = str_replace('_',' ',$key);
        $extras .='<div class="row form-group"><div class="col col-md-3">
                  <label class=" form-control-label">'.$label.' '.$key.'</label>
                </div> <div class="col-12 col-md-9">
            <p class="form-control-static">'.$val.'</p>
             </div></div><hr>';		
    }
    $extras .= '</div>';
}
?>
                       <div class="row">
                            <div class="col-md-12">
                                <div class="overview-wrap">
                                    <h2 class="title-2"><?php echo 'Order #'.$order['id']; ?><br/><br/>
                                    <small title="time of order"><i class="fas fa-clock-o"></i>
                                     <?php echo $admin->date_time_display($order['date_time']); ?></small>
                                    </h2>
                                </div>
                            </div>
                        </div>
						<br/><br/>
                        <div class="row order-details">
                            <div class="col-md-10">
                            <div class="card">
                                    <div class="card-header">
                                            <div class="row">
                                                <div class="col col-md-8">
                                                    <label class="form-control-label">Details</label>
                                                </div>
												<?php 
												if($admin->can_admin_access()){
                                                    if($order['status'] != ORDER_CANCEL_CODE){
												?>
                                                <div class="col-12 col-sm-4 float-right" style="text-align:right;">
                                                    <p class="form-control-static">
                                                    <form action="<?php echo htmlentities($_SERVER["REQUEST_URI"]); ?>" method="post">
                                                        <input type="hidden" name="type" value="order" required>
                                                        <input type="hidden" name="action_type" value="status_change_cancel" required>
										                <input type="hidden" name="values[]" value="<?php echo $id; ?>" required>
										                <input type="submit" value="Cancel Order" name="submitter" class="btn btn-danger">
                                                    </form>
												</p>
                                                </div>
                                                <?php 
                                                    }
                                                } ?>
                                            </div>
                                    </div>
                                    
                                    <div class="card-body card-block">
                                    <div class="row form-group">
                                                <div class="col col-md-3">
                                                    <label class=" form-control-label"><i class="fa fa-ellipsis-v"></i> Status</label>
                                                </div>
                                                <div class="col-12 col-md-9">
                                                    <p class="form-control-static"><?php echo $status_tag; ?></p>
                                                </div>
                                            </div><hr>
                                            <div class="row form-group">
                                                <div class="col col-md-3">
                                                    <label class=" form-control-label"><i class="fas fa-user"></i> Customer</label>
                                                </div>
                                                <div class="col-12 col-md-9">
                                                    <p class="form-control-static"><?php echo $user_details; ?></p>
                                                </div>
                                            </div><hr>
                                            <div class="row form-group">
                                                <div class="col col-md-3">
                                                    <label class=" form-control-label"><i class="fa fa-user"></i> Dispatcher</label>
                                                </div>
                                                <div class="col-12 col-md-9">
                                                    <p class="form-control-static"><?php echo $dispatcher_details; ?></p>
                                                </div>
                                            </div><hr>
                                            <div class="row form-group">
                                                <div class="col col-md-3">
                                                    <label class=" form-control-label"><i class="fa fa-money-bill-alt"></i> Price</label>
                                                </div>
                                                <div class="col-12 col-md-9">
                                                    <p class="form-control-static"><?php echo $admin->amount($order['price']); ?></p>
                                                </div>
                                            </div><hr>
                                            <div class="row form-group">
                                                <div class="col col-md-3">
                                                    <label class=" form-control-label"><i class="fas fa-credit-card "></i> Payment type</label>
                                                </div>
                                                <div class="col-12 col-md-9">
                                                    <p class="form-control-static"><?php echo (!empty($order['payment_type']) ? $order['payment_type'] : 'N/A'); ?></p>
                                                </div>
                                            </div>
                                    </div>
                                    
                                    <?php echo $extras; ?>
                                    <div class="card-footer">
									<!--
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fa fa-dot-circle-o"></i> Submit
                                        </button>
                                        <button type="reset" class="btn btn-danger btn-sm">
                                            <i class="fa fa-ban"></i> Reset
                                        </button>-->
                                    </div>
                                </div>


                            </div>
                        </div>
                        
<?php $admin->footer(); ?>