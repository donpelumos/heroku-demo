<?php session_start();
require_once 'vendor/autoload.php';
$admin = new Admin();
$admin->authorise();
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

if(isset($_POST['submitter'])){
	$admin->submit_bulk($_POST['type']);//and you can relax, it does everything for you :)
}
$admin->header('Dispatchers');
$admin->menu('dispatchers');
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
											<h2 class="title-1 m-b-25">Dispatchers</h2>
										</div>
									</div>
									<div class="col-md-9">
									<a href="?" title="view all users" id="all">
									<button class="au-btn au-btn-icon au-btn--green au-btn--small">
                                        <i class=""></i>All Dispatchers</button></a>
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
                                <h3 class="title-5 m-b-35">Data table - <?php echo $filter; ?> dispatchers (<?php echo $admin->count_users(2,$filter); ?>)</h3>
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
                                        <input type = "hidden" name="type" value="dispatcher" required>
										<button class="au-btn-filter" name="submitter" type="submit">
                                            <i class="fas fa-send"></i>Submit</button>
                                    </div>
                                    <div class="table-data__tool-right">
                                    <a href="add_user.php?type=dispatcher" class="au-btn au-btn-icon au-btn--green au-btn--small">
                                        <i class="fa fa-user-plus"></i>add dispatcher</a>    
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
                                        'order_count'=>'Total orders',
                                        'date_time'=>'Date Registered',
                                        'action'=>'Action'
                                    );
                                    $data = $admin->get_users(2,$filter);
                                    $add = array(
                                        'order_count'=>'<span id="o_c-[id]" class="total-order-count-h"><i class="fas fa-spin fa-loader"></span>',
                                        'action'=>'<a href="user.php?id=[id]&type=dispatcher" class="btn btn-primary" title="view [fullname]">View</a>'
                                    );
                                    $data = $admin->add_to_data($data,$add);
                                    //filter the data for the table
                                    $f_data = $admin->filter_users($data);
                                    $admin->cool_checkbox_table($col,$f_data); ?>
								</form>
                            </div>
                        </div>
								  
                            <script type="text/javascript">
                                /**
                                 * function to sort the ajax
                                 * @param element, the 'this' should be parsed here i think
                                 * @param string type, either order or amount, any other thing won't work, dont say i didnt tell you
                                 */
                                function sortCount(element,type){
                                    var _id = $(element).attr('id');
                                    var realId = _id.split("-");
                                    realId = realId[1];
                                    var _url = "ajax_get_user_total_order_count.php?user_id="+realId+"&user_type=dispatcher&request="+type;
                                    $.get(_url,function(){
                                    })
                                    .done(function(data){
                                        $('#'+_id).html(data);
                                    })
                                    .fail(function(data){
                                        var _val = '';
                                        if(type == 'order')
                                            _val = 0;
                                        else
                                            _val = "<?php echo $admin->amount(0); ?>";
                                        $('#'+_id).html(_val);
                                    });
                                }
                                //stores as [id_attr_val=>'value']
								$('#<?php echo $filter; ?>').addClass('active');
                                $('.total-order-count-h').each(function(index,obj){
                                   sortCount(this,"order");
                                });
							</script>
                            </div>
                           
                        </div>
                        
<?php $admin->footer(); ?>