<?php session_start();
require_once 'vendor/autoload.php';
$admin = new Admin();
$admin->authorise();
$admin->header('Dashboard');
$admin->menu('dashboard');
$user_count = $admin->count_users(1);
$dispatcher_count = $admin->count_users(2);
$order_count = $admin->count_orders();
?>
                       <div class="row">
                            <div class="col-md-12">
                                <div class="overview-wrap">
                                    <h2 class="title-1">overview</h2>
                                </div>
                            </div>
                        </div>
                        <div class="row m-t-25">
                            <div class="col-sm-6 col-lg-3">
                                <div class="overview-item overview-item--c1">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-account-o"></i>
                                            </div>
                                            <div class="text">
                                                <h2><?php echo $user_count; ?></h2>
                                                <span><?php echo ($user_count != 1 ? 'Users': 'User'); ?></span>
                                            </div>
                                        </div>
                                        <div class="overview-chart">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="overview-item overview-item--c2">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-shopping-cart"></i>
                                            </div>
                                            <div class="text">
                                                <h2><?php echo $order_count; ?></h2>
                                                <span>total <?php echo ($order_count != 1 ? 'orders': 'order'); ?></span>
                                            </div>
                                        </div>
                                        <div class="overview-chart">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="overview-item overview-item--c3">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <div class="icon">
                                                <i class="zmdi zmdi-account-o"></i>
                                            </div>
                                            <div class="text">
                                                <h2><?php echo $dispatcher_count; ?></h2>
                                                <span><?php echo ($dispatcher_count != 1 ? 'Dispatchers': 'Dispatcher'); ?></span>
                                            </div>
                                        </div>
                                        <div class="overview-chart">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="overview-item overview-item--c4">
                                    <div class="overview__inner">
                                        <div class="overview-box clearfix">
                                            <!--<div class="icon">
                                                <i class="zmdi zmdi-money"></i>
                                            </div>-->
                                            <div class="text">
                                                <h2><?php echo $admin->amount($admin->get_order_total_price('complete')); ?></h2>
                                                <span>total earnings</span>
                                            </div>
                                        </div>
                                        <div class="overview-chart">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <h2 class="title-2 m-b-25">Order Summary</h2>
                                    <?php
                                    //set columns
                                    $col = array(
                                        'id'=>'Order Id',
                                        'user_id'=>'User',
                                        'dispatcher_id'=>'Dispatcher',
                                        'price'=>'Amount',
                                        'status'=>'Status',
                                        'date_time'=>'Date'
                                    );
                                    $data = $admin->get_orders();
                                    //filter the data for the table
                                    $f_data = $admin->filter_orders($data,15);
                                    $admin->minimal_table($col,$f_data); ?>
                                 <a href="orders.php" class="btn btn-secondary btn-lg btn-block">View all orders</a>  
                            </div>
                           
                        </div>
                        
<?php $admin->footer(); ?>