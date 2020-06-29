<?php session_start();
require_once 'vendor/autoload.php';
$admin = new Admin();
$admin->authorise();
$type = isset($_GET['type']) ? $_GET['type'] : '';
$type_no = ($admin->get_user_type($type) == 0) ? 2 : $admin->get_user_type($type);//set dispatcher to be the default if nothing is found
$type_txt = empty($admin->get_user_type($type,true)) ? 'dispatcher' : $admin->get_user_type($type,true);

if(isset($_POST['submit'])){//edit account has been submitted
    //set stuff for the function call
    $phone = (!empty(trim($_POST['phone']))) ? $_POST['phone'] : ' ';//cause in the api, phone field cant be empty, so put space
    $admin_args = array();
    if($type_no == 3)//add admin args
        $admin_args = array('admin_type'=>$_POST['admin_type']);
    
    $user_args = array('fullname'=>$_POST['fullname'],'email'=>$_POST['email'],'phone'=>$phone);
    $args = array_merge($user_args,$admin_args);
    
    if(!$admin->add_user($args,$type_no))
        $e_msg = $admin->get_error();
    else{
        $g_msg = '<strong>'.$_POST['fullname'].'</strong>\'s '.$type_txt.' account created successfully '.$admin->get_mail_sent_error('but their details couldn\'t be sent to their email');
        $_POST['fullname'] = '';
        $_POST['email'] = '';
        $_POST['phone'] = '';
        
    }
}
$admin->header('Add new '.$type_txt);
$admin->menu($type_txt.'s');
//only some admins can access this page
$admin->admin_access_content();
?>
                       <div class="row">
                            <div class="col-md-12">
                                <div class="overview-wrap">
                                    <h2 class="title-2">Add new <?php echo $type_txt; ?></h2>
                                </div>
                            </div>
                        </div>
						<br/><br/>
                        <div class="row">
                                <div class="col-md-9">
                                <div class="card">
                                    <div class="card-header">New details</div>
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
                                                    <input value="<?php echo isset($_POST['fullname']) ? $_POST['fullname'] : '' ?>" type="text" name="fullname" placeholder="Fullname" required class="form-control">
                                                </div>
                                            </div>
                                            <div class="form-group">
												<label>Email address <span class="required">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-envelope"></i>
                                                    </div>
                                                    <input value="<?php echo isset($_POST['email']) ? $_POST['email'] : '' ?>" type="email" name="email" placeholder="Email" required class="form-control">
                                                </div>
                                                <small>The <?php echo $type_txt; ?> details will be sent to this email</small>
                                            </div>
                                            <?php
                                            if($type_no == 3){ 
                                            ?>
                                            <div class="form-group">
												<label>Admin type <span class="required">*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-"></i>
                                                    </div>
                                                    <select name="admin_type" placeholder="Select admin type" required class="form-control">
                                                        <option value="">Select admin type</option>
                                                        <option value="<?php echo $admin->get_admin_type(1,false); ?>"><?php echo $admin->get_admin_type(1,true); ?></option>
                                                        <option value="<?php echo $admin->get_admin_type(2,false); ?>"><?php echo $admin->get_admin_type(2,true); ?>(can only view orders,users,etc)</option>
                                                    </select>
                                                </div>
                                                <small></small>
                                            </div>
                                            <?php } ?>
                                            <div class="form-group">
												<label>Phone number</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-phone"></i>
                                                    </div>
                                                    <input value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : '' ?>" type="number" name="phone" placeholder="Phone number" class="form-control">
                                                </div>
                                            </div>
											<div class="form-actions form-group">
                                                <button type="submit" name="submit" class="btn btn-secondary btn-sm">Submit</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                           
                        </div>
                        
<?php $admin->footer(); ?>