<?php session_start();
require_once 'vendor/autoload.php';
$admin = new Admin();
$admin->authorise();
$id = isset($_GET['id']) ? $_GET['id'] : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$action = isset($_GET['action']) ? trim($_GET['action']) : '';
$type_no =  $admin->get_user_type($type);
$type_txt = $admin->get_user_type($type,true);

if(isset($_POST['submit'])){//edit account has been submitted
	if($action == 'edit'){
    //set stuff for the function call
	/*
	$phone = (!empty(trim($_POST['phone']))) ? $_POST['phone'] : ' ';//cause in the api, phone field cant be empty, so put space
    $args = array('fullname'=>$_POST['fullname'],'email'=>$_POST['email'],'phone'=>$phone);

    if(!$admin->update_user_details($args,$type_no))
        $e_msg = $admin->get_error();
    else
        $g_msg = $type_txt.' details updated successfully';  
    */
	}
}
$user = $admin->get_user_data($id,$type_no);
if(!($user) || empty($type_txt)){
    $_404 = new _404Message();
    $_404->display();
    exit();
}
$user_c = $admin->get_user_order_total_count($id,$type_no);
$total_orders = $user_c['total_orders'];
$total_order_amount = $admin->amount($user_c['total_amount']);
$email_icon = !empty(trim($user['email'])) ? '<a href="mailto:'.$user['email'].'" title="send an email"><i class="fas fa-envelope"></i></a>' : '<i class="fa fa-envelope"></i>';
$phone_icon = !empty(trim($user['phone'])) ? '<a href="tel:'.$user['phone'].'" title="call"><i class="fas fa-phone"></i></a>' : '<i class="fas fa-phone"></i>';
$submit_btn = '<div class="form-actions form-group">
            <button type="submit" name="submit" class="btn btn-secondary btn-sm">Submit</button>
            </div>';
$no_edit_attr = '';
if($action !== 'edit'){
    $submit_btn = '';
    $no_edit_attr = ' disabled read-only ';
}
$admin->header($user['fullname'].' - '.$type_txt);
$admin->menu($type_txt.'s');
?>
                       <div class="row">
                            <div class="col-md-12">
                                <div class="overview-wrap">
                                    <h2 class="title-2"><?php echo $type_txt.' - '.$user['fullname']; ?><br/><br/>
                                    <small title="time of registeration"><i class="fas fa-clock-o"></i>
                                     <?php echo $admin->date_time_display($user['date_time']); ?></small>
                                    </h2>
                                </div>
                            </div>
                        </div>
						<br/><br/>
                        <div class="row">
                                <div class="col-md-9">
                                <div class="card">
                                    <div class="card-header">
                                            <div class="row">
                                                <div class="col col-md-8">
                                                    <label class="form-control-label">Details</label>
                                                </div>
                                                <div class="col-12 col-sm-4 float-right" style="text-align:right;">
                                                    <p class="form-control-static">
                                                    <strong>Status:
												<?php echo ($user['active'] == 1) ? '<span class="btn btn-success">active</span>' : '<span class="btn btn-danger">Suspended</span>'; ?></strong>
												</p>
                                                </div>
                                            </div>
                                            <?php 
                                            if($type_no == 3){//only for admins
                                            ?>
                                            <div class="row admin-type">
                                                <div class="col-md-8">
                                                    <p class="form-control-static">
                                                        <strong>Admin type:
												        <span class="btn btn-secondary"><?php echo $admin->get_admin_type($user['admin_type'],true); ?></span></strong>
											    	</p>
                                                </div>
                                            </div>
                                            <?php } ?>
                                    </div>
                                    <div class="card-body card-block">
									<div class="adjust-alert-msg">
									<?php 
									if(isset($e_msg)){
										echo "<div class=\"alert alert-danger\" role=\"alert\">".$e_msg."</div>";
									}
									else if(isset($g_msg)){
										echo "<div class=\"alert alert-success\" role=\"alert\">".$g_msg."</div>";
									}
									?>
									</div>
                                       <?php if($action == 'type'){ ?>
                                        <form action="" method="post" class="">
                                       <?php } ?>
                                            <div class="form-group">
                                                <label>Fullname(first + last) </label>
												<div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-user"></i>
                                                    </div>
                                                    <input <?php echo $no_edit_attr; ?> value="<?php echo $user['fullname']; ?>" type="text" name="fullname" placeholder="Fullname" required class="form-control">
                                                </div>
                                            </div>
                                            <div class="form-group">
												<label>Email address</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <?php echo $email_icon; ?>
                                                    </div>
                                                    <input <?php echo $no_edit_attr; ?> value="<?php echo $user['email']; ?>" type="email" name="email" placeholder="Email" required class="form-control">
                                                </div>
                                            </div>
                                            <div class="form-group">
												<label>Phone number</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                    <?php echo $phone_icon; ?>
                                                    </div>
                                                    <input <?php echo $no_edit_attr; ?> value="<?php echo $user['phone']; ?>" type="number" name="phone" placeholder="Phone number" class="form-control">
                                                </div>
                                            </div><hr/>
                                            <?php 
                                            if($type_no != 3){//show if its not admin
                                            ?>
                                            <div class="row form-group">
                                                <div class="col col-md-5">
                                                    <label class=" form-control-label">Total Orders Made</label>
                                                </div>
                                                <div class="col-12 col-md-7">
                                                    <p class="form-control-static"><?php echo $total_orders; ?></p>
                                                </div>
                                            </div><hr/>
                                            <div class="row form-group">
                                                <div class="col col-md-5">
                                                    <label class=" form-control-label">Total Amount on orders</label>
                                                </div>
                                                <div class="col-12 col-md-7">
                                                    <p class="form-control-static"><?php echo $total_order_amount; ?></p>
                                                </div>
                                            </div><hr/>
                                            <?php } ?>
                                            <?php echo $submit_btn;
                                        if($action == 'edit'){ ?>
                                        </form>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                           
                        </div>
                        
<?php $admin->footer(); ?>