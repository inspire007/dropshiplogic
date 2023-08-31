<div class="container-fluid">
    <?php if(empty($_GET['amazon'])){?>
    <!-- BEGIN PAGE HEADER-->
    <div class="row-fluid">
        <div class="span12">
        	<h3 class="page-title">
                Get eBay Token				
            </h3>
            <ul class="breadcrumb">
                <li>
                    <i class="icon-home"></i>
                    <a href="index.php">Home</a> 
                    <i class="icon-angle-right"></i>
                </li>
                <li><a href="add_account.php">Add eBay Account</a></li>
            </ul>
            <!-- END PAGE TITLE & BREADCRUMB-->
        </div>
    </div>
    
    <div class="row-fluid">
        <div class="span12">
            <a class="btn purple big actoken">Get Access Token <i class="m-icon-big-swapright m-icon-white"></i></a>
        </div>
    </div>
    <br/><br/>
    <div class="row-fluid">
        <div class="span12">
            <div class="tokenizer-status">
             </div>
        </div>
    </div>
    <?php }else{?>
    <!-- BEGIN PAGE HEADER-->
    <div class="row-fluid">
        <div class="span12">
        	<h3 class="page-title">
                Add Amazon Account				
            </h3>
            <ul class="breadcrumb">
                <li>
                    <i class="icon-home"></i>
                    <a href="index.php">Home</a> 
                    <i class="icon-angle-right"></i>
                </li>
                <li><a href="add_account.php">Add Amazon Account</a></li>
            </ul>
            <!-- END PAGE TITLE & BREADCRUMB-->
        </div>
    </div>
    
    <div class="row-fluid">
        <div class="span12">
        
        	<?php
			$error = 0;
			if(!empty($_POST['add_amazon_id'])){
				$access_id = mysql_real_escape_string($_POST['access_id']);
				$merchant_id = mysql_real_escape_string($_POST['merchant_id']);
				$marketplace_id = mysql_real_escape_string($_POST['marketplace_id']);
				$secret_key = mysql_real_escape_string($_POST['secret_key']);
				$username = mysql_real_escape_string($_POST['username']);
				$password = mysql_real_escape_string($_POST['password']);
				
				if(empty($access_id) || empty($merchant_id) || empty($marketplace_id) || empty($secret_key) || empty($username) || empty($password))$error = 1;
				else{
					if(!mysql_num_rows(mysql_query("SELECT NULL FROM amazon_tokens WHERE user_id = '$username'"))){
						mysql_query("INSERT INTO amazon_tokens VALUES(0, '$username', '$password', '$merchant_id', '$marketplace_id', '$access_id', '$secret_key', NOW())");
					}
					else{
						mysql_query("UPDATE amazon_tokens SET password = '$password', merchant_id = '$merchant_id', marketplace_id = '$marketplace_id', access_id = '$access_id', secret_key = '$secret_key', added_at = NOW() WHERE user_id = '$username'");	
					}
					$error = 2;	
				}	
			}
			?>
        	
            <?php if($error == 1){?><div class="alert alert-error">Please fill up all the require fields!</div><?php }?>
            <?php if($error == 2){?><div class="alert alert-success">Record inserted successfully!</div><?php }?>
        	
            <form action="" method="post">
            	<table cellpadding="5" cellspacing="5">
                	<tr><td>API Access ID</td><td><input type="text" name="access_id" value="<?php echo $error == 1 ? htmlentities($_POST['access_id']) : '' ?>"/></td></tr>
                    <tr><td>Merchant ID</td><td><input type="text" name="merchant_id" value="<?php echo $error == 1 ? htmlentities($_POST['merchant_id']) : '' ?>"/></td></tr>
                    <tr><td>Marketplace ID</td><td><input type="text" name="marketplace_id" value="<?php echo $error == 1 ? htmlentities($_POST['marketplace_id']) : '' ?>"/></td></tr>
                    <tr><td>Secret Key</td><td><input type="text" name="secret_key" value="<?php echo $error == 1 ? htmlentities($_POST['secret_key']) : '' ?>"/></td></tr>
                    <tr><td>Amazon Login Email</td><td><input type="text" name="username" value="<?php echo $error == 1 ? htmlentities($_POST['username']) : '' ?>"/></td></tr>
                    <tr><td>Amazon Login Password</td><td><input type="password" name="password" value="<?php echo $error == 1 ? htmlentities($_POST['password']) : '' ?>"/></td></tr>
                    <tr><td></td><td><button class="btn green">Save</button></td></tr>
                </table>
                <input type="hidden" name="add_amazon_id" value="1" />
            </form>
        </div>
    </div>
    <br/><br/>
    <div class="row-fluid">
        <div class="span12">
            <div class="tokenizer-status">
             </div>
        </div>
    </div>
    <?php }?>
</div>
