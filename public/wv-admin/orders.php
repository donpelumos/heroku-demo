<?php session_start();
require_once 'vendor/autoload.php';
$admin = new Admin();
$admin->authorise();
$filter = isset($_GET['filter']) ? str_replace(' ','-',$admin->filter_order_status($_GET['filter'])) : 'all';
$correct_word = $admin->filter_order_status($filter,'past');
if(!$filter || !$correct_word){
    $filter = 'all';
    $correct_word = 'all';
}
if(isset($_POST['submitter'])){
	$admin->submit_bulk();//and you can relax, it does everything for you :)
}
$admin->header('Orders');
$admin->menu('orders');
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
									<div class="col-md-2">
									
                                <h2 class="title-1 m-b-25">Orders</h2>
                                  
									</div>
									<div class="col-md-9">
									<a href="?" title="view all orders" id="all">
									<button class="au-btn au-btn-icon au-btn--green au-btn--small">
                                        <i class=""></i>All Orders</button></a>
									<a href="?filter=process" title="view processing orders" id="process">
                                     <button class="au-btn au-btn-icon au-btn--green au-btn--small">
                                        <i class="fas fa-ellipsis-h"></i>Processing</button></a>
									<a href="?filter=hold" title="view orders on hold" id="on-hold">
                                     <button class="au-btn au-btn-icon au-btn--green au-btn--small">
                                        <i class="fas fa-minus-circle"></i>On-hold</button></a>
									<a href="?filter=complete" title="view completed orders" id="complete">
                                     <button class="au-btn au-btn-icon au-btn-- au-btn--small">
                                        <i class="fas fa-check-circle"></i>Completed</button></a>
										<a href="?filter=cancel" title="view cancelled orders" id="cancel">
                                        <button class="au-btn au-btn-icon au-btn--green au-btn--small">
                                        <i class="fas fa-window-close "></i>Cancelled</button></a>
                                     
									</div>
								</div>
                                <section class="au-breadcrumb m-t-75" style="margin-top:1px;margin-bottom:10px;">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="au-breadcrumb-content">
                                    <div class="au-breadcrumb-left">
                                        <span class="au-breadcrumb-span"></span>
                                        <ul class="list-unstyled list-inline au-breadcrumb__list">
                                            <li class="list-inline-item active">
                                                <a href="#">Orders</a>
                                            </li>
                                            <li class="list-inline-item seprate">
                                                <span>/</span>
                                            </li>
                                            <li class="list-inline-item"><?php echo $filter; ?></li>
                                        </ul>
                                    </div><span>
                                        <?php echo '<strong>'.$admin->amount($admin->get_order_total_price($filter)).'</strong>
                                         total on <strong>'.$correct_word.'</strong> orders'; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

						<div class="row">
                            <div class="col-md-12">
							<form action="<?php echo htmlentities($_SERVER["REQUEST_URI"]); ?>" method="post">
                                <!-- DATA TABLE -->
                                <h3 class="title-5 m-b-35">Data table - <?php echo $filter; ?> orders (<?php echo $admin->count_orders($filter); ?>)</h3>
                                <?php
                                if($admin->can_admin_access()){//only admin specific set of admins to access this part
                                ?>
                                <div class="table-data__tool">
                                    <div class="table-data__tool-left">
                                        <div class="rs-select2--light rs-select2--md">
                                            <select class="js-select2" name="action_type" required>
                                                <option selected="selected" value="">Bulk Action</option>
                                                <option value="delete">Delete</option>
                                                <option value="status_change_cancel">Change status to cancelled</option>
                                            </select>
                                            <div class="dropDownSelect2"></div>
                                        </div>
                                        <input type = "hidden" name="type" value="order" required>
										<button class="au-btn-filter" name="submitter" type="submit">
                                            <i class="fas fa-send"></i>Submit</button>
                                    </div>                                    
                                </div>
                                <?php
                                }
                                    //set columns
                                    $col = array(
                                        'id'=>'Order Id',
                                        'user_id'=>'Customer',
                                        'dispatcher_id'=>'Dispatcher',
                                        'price'=>'Amount',
                                        'payment_type'=>'Payment type',
                                        'status'=>'Status',
                                        'date_time'=>'Date',
                                        'action'=>'Action'
                                    );
                                    $data = $admin->get_orders($filter);
                                    $extra_data = array(
                                        'action'=>'<a href="order.php?id=[id]" class="btn btn-primary" title="view order #[id]">View</a>'
                                        );
                                    $data = $admin->add_to_data($data,$extra_data);
                                    //filter the data for the table
                                    $f_data = $admin->filter_orders($data);
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