<?php

if(!empty($_POST)){
	$new = !mysql_num_rows(mysql_query('SELECT * FROM global_config'));
	if(!$new){
		if(!empty($_POST['postorder_wait_time'])){
			mysql_query("UPDATE global_config SET postorder_wait_time = '".((int)$_POST['postorder_wait_time'])."'");		
		}
		if(isset($_POST['paypal_fee_flat'])){
			mysql_query("UPDATE global_config SET paypal_fee_flat = '".((float)$_POST['paypal_fee_flat'])."'");		
		}
		if(isset($_POST['paypal_fee_percent'])){
			mysql_query("UPDATE global_config SET paypal_fee_percent = '".((float)$_POST['paypal_fee_percent'])."'");		
		}
		if(!empty($_POST['auto_order'])){
			mysql_query("UPDATE global_config SET auto_order = 1");		
		}
		else{
			mysql_query("UPDATE global_config SET auto_order = 0");		
		}
		if(!empty($_POST['show_all_orders'])){
			mysql_query("UPDATE global_config SET show_all_orders = 1");		
		}
		else mysql_query("UPDATE global_config SET show_all_orders = 0");
		
		if(!empty($_POST['order_safety_200'])){
			mysql_query("UPDATE global_config SET order_safety_200 = 1");		
		}
		else mysql_query("UPDATE global_config SET order_safety_200 = 0");
	}
	else{
		$sql = "INSERT INTO global_config VALUES(";
		if(!empty($_POST['postorder_wait_time'])){
			$sql .= "'".((int)$_POST['postorder_wait_time'])."',";		
		}
		else $sql .= '0,';
		
		if(!empty($_POST['paypal_fee'])){
			$sql .= "'".((float)$_POST['paypal_fee'])."',";	
		}
		else $sql .= '0,';
		
		if(!empty($_POST['auto_order'])){
			$sql .= '1,';
		}	
		else $sql .= '0,';
		
		if(!empty($_POST['show_all_orders'])){
			$sql .= '1,';
		}	
		else $sql .= '0,';
		
		if(!empty($_POST['order_safety_200'])){
			$sql .= '1)';
		}	
		else $sql .= '0)';
		
		mysql_query($sql);
	}
	$settings = load_settings();	
}

if(!empty($_POST['password'])){
	$user = mysql_real_escape_string($_SESSION['user_logged_in']);
	$pass = mysql_real_escape_string(sha1($_POST['password']));
	mysql_query("UPDATE users SET password = '$pass' WHERE email = '$user'");	
}

if(!empty($_POST['clear_sales_stats']))mysql_query("TRUNCATE TABLE sales_stats");

?>
<div class="container-fluid">
    <!-- BEGIN PAGE HEADER-->
    <div class="row-fluid">
        <div class="span12">
        	<h3 class="page-title">
                General Settings				
            </h3>
            <ul class="breadcrumb">
                <li>
                    <i class="icon-home"></i>
                    <a href="index.php">Home</a> 
                    <i class="icon-angle-right"></i>
                </li>
                <li><a href="settings.php">Settings</a></li>
            </ul>
            <!-- END PAGE TITLE & BREADCRUMB-->
        </div>
    </div>
    
    <div class="row-fluid">
        <div class="span6">
            <!-- BEGIN Portlet PORTLET-->
            <div class="portlet box grey">
                <div class="portlet-title">
                    <h4><i class="icon-reorder"></i>Added eBay Accounts</h4>
                    <div class="actions">
                    	<a href="add_account.php" class="btn green mini"><i class="icon-plus"></i> Add</a>
                    </div>
                </div>
                <div class="portlet-body">
                    <div>
                       <table cellpadding="5" cellspacing="5">
                            <tr align="left"><th style="width:250px">User ID</th><th style="width:250px">Added at</th><th>Actions</th></tr>
                        <?php
                        $q = mysql_query("SELECT * FROM ebay_tokens LIMIT 100");
                        if(!mysql_num_rows($q)){
                            echo '<tr><td colspan="5"><div class="alert alert-error">No user found on database</div></td></tr>';
                        }else
                        while($res = mysql_fetch_assoc($q)){
                            echo '<tr id="user-'.$res['id'].'">
                                    <td>'.$res['user_id'].'</td>
                                    <td>'.$res['added_at'].'</td>
                                    <td><a href="javascript:void(0)" class="btn mini red del_ebay_id" rel="'.$res['id'].'"><i class="icon-trash"></i> Delete</a></td>
                                 </tr>';	
                        }
                        ?>
                        </table> 
                    </div>
                </div>
            </div>
            <!-- END Portlet PORTLET-->
       </div>
       
       
       <div class="span6">
            <!-- BEGIN Portlet PORTLET-->
            <div class="portlet box grey">
                <div class="portlet-title">
                    <h4><i class="icon-reorder"></i>Added Amazon Accounts</h4>
                    <div class="actions">
                    	<a href="add_account.php?amazon=1" class="btn green mini"><i class="icon-plus"></i> Add</a>
                    </div>
                </div>
                <div class="portlet-body">
                    <div>
                       <table cellpadding="5" cellspacing="5">
                            <tr align="left"><th style="width:250px">User ID</th><th style="width:250px">Added at</th><th>Actions</th></tr>
                        <?php
                        $q = mysql_query("SELECT * FROM amazon_tokens LIMIT 100");
                        if(!mysql_num_rows($q)){
                            echo '<tr><td colspan="5"><div class="alert alert-error">No user found on database</div></td></tr>';
                        }else
                        while($res = mysql_fetch_assoc($q)){
                            echo '<tr id="user-'.$res['id'].'">
                                    <td>'.$res['user_id'].'</td>
                                    <td>'.$res['added_at'].'</td>
                                    <td><a href="javascript:void(0)" class="btn mini red del_am_id" rel="'.$res['id'].'"><i class="icon-trash"></i> Delete</a></td>
                                 </tr>';	
                        }
                        ?>
                        </table> 
                    </div>
                </div>
            </div>
            <!-- END Portlet PORTLET-->
       </div>
       
    </div>      
    <form action="" method="post">
    <div class="row-fluid">        
        <div class="span3"><label>Check tracking number every</label></div>
        <div class="span9"><input type="text" name="postorder_wait_time" style="width: 50px" value="<?php echo $settings['postorder_wait_time']?>"/> &nbsp;&nbsp;hours</div>  
   </div>
   <div class="row-fluid">        
        <div class="span3"><label>Paypal fee flat</label></div>
        <div class="span9"><input type="text" name="paypal_fee_flat" style="width: 50px" value="<?php echo $settings['paypal_fee_flat']?>"/> &nbsp;&nbsp;$</div>  
   </div>
   <div class="row-fluid">        
        <div class="span3"><label>Paypal fee %</label></div>
        <div class="span9"><input type="text" name="paypal_fee_percent" style="width: 50px" value="<?php echo $settings['paypal_fee_percent']?>"/> &nbsp;&nbsp;%</div>  
   </div>
   <div class="row-fluid">        
        <div class="span3"><label>Auto order products</label></div>
        <div class="span9"><input type="checkbox" name="auto_order" <?php echo $settings['auto_order'] ? 'checked="checked"' : ''?>/></div>  
   </div>
   <div class="row-fluid">        
        <div class="span3"><label>Enable safety for orders over $200</label></div>
        <div class="span9"><input type="checkbox" name="order_safety_200" <?php echo $settings['order_safety_200'] ? 'checked="checked"' : ''?>/></div>  
   </div>
   <div class="row-fluid">        
        <div class="span3"><label>Show all orders in homepage</label></div>
        <div class="span9"><input type="checkbox" name="show_all_orders" <?php echo $settings['show_all_orders'] ? 'checked="checked"' : ''?>/></div>  
   </div>
   <div class="row-fluid">        
        <div class="span3"><label>Change login password</label></div>
        <div class="span9"><input type="password" name="password"/></div>  
   </div>
   <div class="row-fluid">        
        <div class="span3"><input type="submit" class="btn purple" value="Save" /></div>
   </div>
   </form> 
   <form action="" method="post" id="stats_clear">
   <input type="hidden" name="clear_sales_stats" value="1" />
   <a href="javascript:void(0)" class="btn red" onclick="if(!confirm('Are you sure?'))return false;$('#stats_clear').submit()"><i class="icon-black icon-trash"></i>&nbsp;&nbsp; Clear all sales statistics</a>
   </form>
</div>
</div>
