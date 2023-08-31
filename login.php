<?php
/**
 * Login script
 * Authenticates users to the system
 *
 * @package EbaySalesAutomation
 * @subpackage Login
 * @version 1.0
 * @author N/A
 * @copyright 2014
 */
 
include(dirname(__FILE__).'/config.php');
$page_title = 'Login | '.PAGE_TITLE;

/**
	if the user is already logged in
 */
if(check_login()){
	header('location: index.php');
	exit();	
}	

/**
	if login submitted
 */
$error = 0;
if(!empty($_POST['username']) && !empty($_POST['password'])){
	$username = mysql_real_escape_string($_POST['username']);
	$password = mysql_real_escape_string($_POST['password']);
	if(!mysql_num_rows(mysql_query("SELECT * FROM users WHERE email = '$username' AND password = SHA1('$password')")))$error = 1;
	else{
		$_SESSION['user_logged_in'] = $username;
		header('location: index.php');	
	}	
}
?>
<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!--> <html lang="en"> <!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
  <meta charset="utf-8" />
  <title><?php echo $page_title?></title>
  <meta content="width=device-width, initial-scale=1.0" name="viewport" />
  <meta content="" name="description" />
  <meta content="" name="author" />
  <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
  <link href="assets/css/metro.css" rel="stylesheet" />
  <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
  <link href="assets/css/style.css" rel="stylesheet" />
  <link href="assets/css/style_responsive.css" rel="stylesheet" />
  <link href="assets/css/style_default.css" rel="stylesheet" id="style_color" />
  <link rel="stylesheet" type="text/css" href="assets/uniform/css/uniform.default.css" />
  <link rel="shortcut icon" href="favicon.ico" />
</head>
<!-- END HEAD -->
<!-- BEGIN BODY -->
<body class="login">
  <!-- BEGIN LOGO -->
  <div class="logo">
    <img src="assets/img/logo-big.png" alt="" /> 
  </div>
  <!-- END LOGO -->
  <!-- BEGIN LOGIN -->
  <div class="content">
    <!-- BEGIN LOGIN FORM -->
    <form class="form-vertical login-form" action="" method="post">
      <h3 class="form-title">Login to your account</h3>
      <?php if($error){?>
      <div class="alert alert-error">
        <button class="close" data-dismiss="alert"></button>
        <span>Invalid username and password.</span>
      </div>
      <?php }?>
      <div class="control-group">
        <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
        <label class="control-label visible-ie8 visible-ie9">Username</label>
        <div class="controls">
          <div class="input-icon left">
            <i class="icon-user"></i>
            <input class="m-wrap placeholder-no-fix" type="text" placeholder="Username" name="username"/>
          </div>
        </div>
      </div>
      <div class="control-group">
        <label class="control-label visible-ie8 visible-ie9">Password</label>
        <div class="controls">
          <div class="input-icon left">
            <i class="icon-lock"></i>
            <input class="m-wrap placeholder-no-fix" type="password" placeholder="Password" name="password"/>
          </div>
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn green pull-right">
        Login <i class="m-icon-swapright m-icon-white"></i>
        </button>            
      </div>
    </form>
    <!-- END LOGIN FORM --> 
  </div>
  <!-- END LOGIN -->
  <!-- BEGIN COPYRIGHT -->
  <div class="copyright">
    <?php echo date('Y')?> &copy; <?php echo PAGE_TITLE?>
  </div>
  <!-- END COPYRIGHT -->
  <!-- BEGIN JAVASCRIPTS -->
  <script src="assets/js/jquery-1.8.3.min.js"></script>
  <script src="assets/bootstrap/js/bootstrap.min.js"></script>  
  <script src="assets/uniform/jquery.uniform.min.js"></script> 
  <script src="assets/js/jquery.blockui.js"></script>
  <!-- END JAVASCRIPTS -->
</body>
<!-- END BODY -->
</html>