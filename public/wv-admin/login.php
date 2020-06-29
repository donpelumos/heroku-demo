<?php session_start();
require_once 'vendor/autoload.php';
$admin = new Admin();
if($admin->is_logged_in()){
    header("location:index.php");
    exit();
}
if(isset($_POST['submit'])){//form is submitted
    $email = $_POST['email'];
    $pass = $_POST['password'];
    if(!$admin->login($email,$pass))
        $e_msg = $admin->get_error();
}
$admin->header('Login');//load html head
?>
<body class="animsition">
    <div class="page-wrapper">
        <div class="page-content--bge5">
            <div class="container">
                <div class="login-wrap">
                    <div class="login-content">
                        <div class="login-logo">
                            <a href="">
                                <img src="<?php echo $admin->logo_url(); ?>" alt="Wave Us transit Logo">
                            </a>
                        </div>
                        <div class="login-form">
                            <form action="<?php htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
                            <div class="adjust-alert-msg">
						 <?php if(isset($e_msg)){
							 echo "<div class=\"alert alert-danger\" role=\"alert\">".$e_msg."</div>";
								} ?>
                         </div>
                              <div class="form-group">
                                    <label>Email Address</label>
                                    <input class="au-input au-input--full" type="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : '' ?>" name="email" placeholder="Email" required>
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input class="au-input au-input--full" type="password" name="password" placeholder="Password" required>
                                </div>
                                <div class="login-checkbox">
                                    <label>
                                        <a href="forgot_password.php">Forgotten Password?</a>
                                    </label>
                                </div>
                                <button class="au-btn au-btn--block au-btn--green m-b-20" name="submit" type="submit">sign in</button>
                                </form>
                            <div class="register-link">
                                <p>
                                </p>
                            </div>
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
    <!-- assets
     JS       -->
    <script src="assets/slick/slick.min.js">
    </script>
    <script src="assets/wow/wow.min.js"></script>
    <script src="assets/animsition/animsition.min.js"></script>
    <script src="assets/perfect-scrollbar/perfect-scrollbar.js"></script>
    <!-- Main JS-->
    <script src="js/main.js"></script>
</body>
</html>