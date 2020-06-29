<?php session_start();
require_once 'vendor/autoload.php';
$admin = new Admin();
$admin->authorise();
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

if(isset($_POST['submitter'])){
	$admin->submit_bulk($_POST['type']);//and you can relax, it does everything for you :)
}
$admin->header('Admins');
$admin->menu('admins');
?>
                       <div class="row">
                            <div class="col-md-12">
                               <!-- <div class="overview-wrap">
                                    <h2 class="title-1">overview</h2>
                                </div>-->
                            </div>
                        </div>
                       
                        <div class="row">
                            <div class="col-lg-12">
								<div class="row wv-table-actions">
									<div class="col-md-3">
										<div class="inner-title">
											<h2 class="title-1 m-b-25">Admins</h2>
										</div>
									</div>
									<div class="col-md-9">
									<a href="?" title="view all users" id="all">
									<button class="au-btn au-btn-icon au-btn--green au-btn--small">
                                        <i class=""></i>All Admins</button></a>
									<a href="?filter=active" title="view only active users" id="active">
                                     <button class="au-btn au-btn-icon au-btn-- au-btn--small">
                                        <i class="fas fa-check-circle"></i>Active</button></a>
                                        <a href="?filter=suspended" title="view only suspended users" id="suspended">
                                     <button class="au-btn au-btn-icon au-btn--green au-btn--small">
                                        <i class="fas fa-minus-circle"></i>Suspended</button></a>
									
									</div>
								</div>
						<div class="row">
                            <div class="col-md-12">
							<form action="<?php echo htmlentities($_SERVER["REQUEST_URI"]); ?>" method="post">
                                <!-- DATA TABLE -->
                                <h3 class="title-5 m-b-35">Data table - <?php echo $filter; ?> admins (<?php echo $admin->count_users(3,$filter); ?>)</h3>
                                <?php 
								if($admin->can_admin_access()){//only admin specific set of admins to access this part
								?>
								<div class="table-data__tool">
                                    <div class="table-data__tool-left">
                                        <div class="rs-select2--light rs-select2--md">
                                            <select class="js-select2" name="action_type">
                                                <option selected="selected">Bulk Action</option>
                                                <option value="activate">Activate</option>
                                                <option value="suspend">Suspend</option>
                                                <option value="delete">Delete</option>
                                            </select>
                                            <div class="dropDownSelect2"></div>
                                        </div>
                                        <input type = "hidden" name="type" value="admin" required>
										<button class="au-btn-filter" name="submitter" type="submit">
                                            <i class="fas fa-send"></i>Submit</button>
                                    </div>
                                    <div class="table-data__tool-right">
                                    <a href="add_user.php?type=admin" class="au-btn au-btn-icon au-btn--green au-btn--small">
                                        <i class="fa fa-user-plus"></i>add admin</a>    
                                    </div>
                                </div>
                                <?php 
								}
                                    //set columns
                                    $col = array(
                                        'fullname'=>'Name',
                                        'email'=>'Email',
                                        'phone'=>'Phone',
                                        'active'=>'Status',
                                        'admin_type'=>'Admin Type',
                                        'date_time'=>'Date Registered',
                                        'action'=>'Action'
                                    );
                                    $data = $admin->get_users(3,$filter);
                                    $add = array(
                                        'action'=>'<a href="user.php?id=[id]&type=admin" class="btn btn-primary" title="view [fullname]">View</a>'
                                    );
                                    $data = $admin->add_to_data($data,$add);
                                    //filter the data for the table
                                    $f_data = $admin->filter_users($data);
                                    $admin->cool_checkbox_table($col,$f_data); ?>
								</form>
                            </div>
                        </div>
								  
                            <script type="text/javascript">
                                $('#<?php echo $filter; ?>').addClass('active');
							</script>
                            </div>
                           
                        </div>
                        
<?php $admin->footer(); ?>