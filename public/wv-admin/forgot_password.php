<?php session_start();
require_once 'vendor/autoload.php';
$admin = new Admin();
if($admin->is_logged_in()){
    header("location:index.php");
    exit();
}
if(isset($_POST['submit'])){//form is submitted
    if(!$admin->set_forgot_password($_POST['email'],3))
        $e_msg = $admin->get_error().$admin->get_mail_sent_error('The mail couldn\'t be sent, try again '.$admin->contact_dev_msg());
    else{
        $g_msg = "You should soon receive an email sent to <strong>".$_POST['email']."</strong> allowing you to reset your password.
         Please make sure to check your spam and trash if you can't find the email.";
    }
}
$admin->header('Reset Password');//load html head
?>
<body class="animsition">
    <div class="page-wrapper">
        <div class="page-content--bge5">
            <div class="container">
                <div class="login-wrap">
                    <div class="login-content">
                        <div class="login-logo">
                            <a href="index.php">
                                <img src="<?php echo $admin->logo_url(); ?>" alt="CoolAdmin">
                            </a>
                        </div>
                        <div class="login-form">
                            <form action="" method="post">
                            <form action="<?php htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
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
                                <div class="form-group">
                                    <small>Enter your email and we'll send you a link to get back into your account.</small>
                                    <label>Email Address</label>
                                    <input class="au-input au-input--full" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>" type="email" name="email" placeholder="Email" required>
                                </div>
                                <button class="au-btn au-btn--block au-btn--green m-b-20" name="submit" type="submit">submit</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
<!-- Jquery JS-->
<script src="assets/jquery-3.2.1.min.js"></script>
    <!-- Bootstrap JS-->
    <script src="assets/bootstrap-4.1/bootstrap.min.js"></script>
    <script src="assets/slick/slick.min.js">
    </script>
    <script src="assets/wow/wow.min.js"></script>
    <script src="assets/animsition/animsition.min.js"></script>
    <script src="assets/perfect-scrollbar/perfect-scrollbar.js"></script>
    <!-- Main JS-->
    <script src="js/main.js"></script>
</body>
</html>