<?php session_start();
require_once 'vendor/autoload.php';
$admin = new Admin();
$admin->authorise();
if(isset($_POST['edit_account'])){//edit account has been submitted
    //set stuff for the function call
	$phone = (!empty(trim($_POST['phone']))) ? $_POST['phone'] : ' ';//cause in the api, phone field cant be empty, so put space
    $args = array('user_id'=>$admin->get_session(),'fullname'=>$_POST['fullname'],'email'=>$_POST['email'],'phone'=>$phone);
    if(!empty($_POST['password'])){//password wants to be changed
        $args['password'] = $_POST['password'];
        $args['new_password'] = $_POST['new_password'];
        $args['confirm_new_password'] = $_POST['c_new_password'];
    }
    if(!$admin->update_user_details($args,3))
        $e_msg = $admin->get_error();
    else
        $g_msg = 'Updated successfully';
}
if(isset($_POST['submit_settings'])){
   if(empty(trim($_POST['order_email'])))
        $s_e_msg = 'Order email field is empty';
    else{
        /**
         * Submits the settings form
         * 
         * @return bool
         */
        function submit_settings(){
            global $admin;
			// Advantage of using same name attr as option_name :), less stress!
            $meta = json_encode($_POST['options_data']);
            $url = 'update_site_option_details.php?user_id='.$admin->get_session().'&meta='.$meta;
            if(!$admin->get_endpoint($url))
                return false;
            if(!$admin->get_success_state()){
                $admin->sort_reason();
                return false;
            }
            return true;
        }
        if(submit_settings())
            $s_g_msg = 'Updated successfully';
        else
            $s_e_msg = $admin->get_error();
    }
}
$user = $admin->get_user_data($admin->get_session());
$admin->header('Account settings');
$admin->menu('account');
?>
                       <div class="row">
                            <div class="col-md-12">
                                <div class="overview-wrap">
                                    <h2 class="title-2">Account Settings</h2>
                                </div>
                            </div>
                        </div>
						<br/><br/>
                        <div class="row">
                                <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">Your details</div>
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
                                        <form action="" method="post" class="">
                                            <div class="form-group">
                                                <label>Fullname(first + last) <span class="required">*</span></label>
												<div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-user"></i>
                                                    </div>
                                                    <input value="<?php echo $user['fullname']; ?>" type="text" name="fullname" placeholder="Fullname" required class="form-control">
                                                </div>
                                            </div>
                                            <div class="form-group">
												<label>Email address <span class="required">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-envelope"></i>
                                                    </div>
                                                    <input value="<?php echo $user['email']; ?>" type="email" name="email" placeholder="Email" required class="form-control">
                                                </div>
                                            </div>
                                            
                                            <div class="form-group">
												<label>Admin type <span class="required"></span></label>
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-"></i>
                                                    </div>
                                                    <select id="admin_type_input" disabled name="admin_type" placeholder="Select admin type" class="form-control">
                                                        <option value="">Select admin type</option>
                                                        <option value="<?php echo $admin->get_admin_type(1,false); ?>"><?php echo $admin->get_admin_type(1,true); ?></option>
                                                        <option value="<?php echo $admin->get_admin_type(2,false); ?>"><?php echo $admin->get_admin_type(2,true); ?>(can only view orders,users,etc)</option>
                                                    </select>
                                                    <script type="text/javascript">
                                                        $("#admin_type_input").val("<?php echo $admin->get_admin_type($admin->get_current_admin_type()); ?>");
                                                    </script>
                                                </div>
                                                <small></small>
                                            </div>

                                            <div class="form-group">
												<label>Phone number</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-phone"></i>
                                                    </div>
                                                    <input value="<?php echo $user['phone']; ?>" type="number" name="phone" placeholder="Phone number" class="form-control">
                                                </div>
                                            </div>
											<fieldset><legend>Change Password</legend>
											<small>Please leave blank if you do not want to change your password</small>
                                            <div class="form-group">
												<label>Current Password</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-asterisk"></i>
                                                    </div>
                                                    <input type="password" name="password" placeholder="Current Password" class="form-control">
                                                </div>
                                            </div>  
											<div class="form-group">
												<label>New Password</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-asterisk"></i>
                                                    </div>
                                                    <input type="password" name="new_password" placeholder="New Password" class="form-control">
                                                </div>
                                            </div>  
											<div class="form-group">
												<label>Confirm New Password</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-asterisk"></i>
                                                    </div>
                                                    <input type="password" name="c_new_password" placeholder="Re-enter new Password" class="form-control">
                                                </div>
                                            </div>
											</fieldset>
                                            <div class="form-actions form-group">
                                                <button type="submit" name="edit_account" class="btn btn-secondary btn-sm">Submit</button>
                                            </div>
											<small>Make sure you use a strong password</small>
                                        </form>
                                    </div>
                                </div>
                            </div>
                           
						   <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">Site settings</div>
                                    <div class="card-body card-block">
									<div class="adjust-alert-msg">
									<?php 
									if(isset($s_e_msg)){
										echo "<div class=\"alert alert-danger\" role=\"alert\">".$s_e_msg."</div>";
									}
									else if(isset($s_g_msg)){
										echo "<div class=\"alert alert-success\" role=\"alert\">".$s_g_msg."</div>";
									}
									/**
									 * Rave little info
									 *
									 * @param string $key_type public or secret
									 * @return string
									 */
									function rave_little_info($key_type){
										return 'This is the '.$key_type.' key gotten from your <a href="https://dashboard.flutterwave.com/dashboard/settings/apis" target="_blank">Rave dashboard</a>.';
									}
									//get site options
									$options = $admin->get_site_options();
									// Yup, same values as name attr, e get why.
									$options_data = array(
										'order_email' => '',
										'flw_live_secret_key' => '',
										'flw_live_public_key' => '',
										'flw_test_secret_key' => '',
										'flw_test_public_key' => '',
									);
									if( $options ){ // Best way i could think of, so tired :)
										foreach( $options as $option ){
											foreach( $options_data as $key => $value ){
												if( $option['option_name'] == $key ){ // Match, set
													$options_data[$key] = $option;
													continue;
												}
											}
										}
									}
									?>
									</div>
                                        <form action="<?php echo htmlentities($_SERVER["PHP_SELF"]); ?>" method="post">
                                            <div class="form-group">
												<label>Order email</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-envelope"></i>
                                                    </div>
                                                    <input type="email" name="options_data[order_email]" value="<?php echo $options_data['order_email']['option_value']; ?>" placeholder="Order Email" class="form-control">
                                                </div>
												<small>This is the email address customers can reply to when they receive order emails.</small>
                                            </div>
											<div class="form-group"> <!-- Live Keys -->
												<label>Rave Payment Public Keys</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-key"></i>
                                                    </div>
                                                    <input type="text" name="options_data[flw_live_public_key]" value="<?php echo $options_data['flw_live_public_key']['value']; ?>" placeholder="Rave Live Public Key" class="form-control">
                                                </div>
												<small><?php echo rave_little_info("Live Public"); ?></small>
                                            
												<label>Rave Payment Secret Key</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-key"></i>
                                                    </div>
                                                    <input type="text" name="options_data[flw_live_secret_key]" value="<?php echo $options_data['flw_live_secret_key']['value']; ?>" placeholder="Rave Live Secret Key" class="form-control">
                                                </div>
												<small><?php echo rave_little_info("Live Secret"); ?></small>

											</div>
											
											<div class="form-group"> <!-- Test Keys -->
												<label>Rave Payment Test Public Key</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-key"></i>
                                                    </div>
                                                    <input type="text" name="options_data[flw_test_public_key]" value="<?php echo $options_data['flw_test_public_key']['value']; ?>" placeholder="Rave Test Public Key" class="form-control">
                                                </div>
												<small><?php echo rave_little_info("Test Public"); ?></small>
                                            
												<label>Rave Payment Test Secret Key</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-key"></i>
                                                    </div>
                                                    <input type="text" name="options_data[flw_test_secret_key]" value="<?php echo $options_data['flw_test_secret_key']['value']; ?>" name="flw_test_secret_key" placeholder="Rave Test Secret Key" class="form-control">
                                                </div>
												<small><?php echo rave_little_info("Test Secret"); ?></small>

											</div>
                                            <div class="form-actions form-group">
                                                <button type="submit" name="submit_settings" class="btn btn-success btn-sm">Submit</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                         
                        </div>
                        
<?php $admin->footer(); ?>