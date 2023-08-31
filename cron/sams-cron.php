<?php
/**
 * This script is used to automate the Sams classes.
 * It only places orders on Sams and saves info on mysql
 *
 * @package EbaySalesAutomation
 * @subpackage Cron
 * @version 1.0
 * @author N/A
 * @copyright 2014
 */

set_time_limit(0);
$no_session = true; 
include(dirname(dirname(__FILE__)).'/config.php');
include(dirname(dirname(__FILE__)).'/classes/sams.class.php');
include(dirname(dirname(__FILE__)).'/classes/dom.class.php');


if(!is_dir(dirname(dirname(__FILE__)).'/logs'))mkdir(dirname(dirname(__FILE__)).'/logs');

$set = 'sams';
clear_logs($set);

if(check_running($set))exit('An instance of ebay cron is already running');
$flock = lock_process($set);

do_log('===================================================');
do_log('Starting process');
do_log('===================================================');

$sams = new SamsClub();
$sams->clear_log();

//unlock all locked task older than 20 minute
mysql_query("UPDATE ebay_orders SET dropship_locked = 0 WHERE dropship_locked = 1 AND dropship_done = 0 AND locked_at != '0000-00-00 00:00:00' AND locked_at < DATE_SUB(NOW(), INTERVAL 20 MINUTE)");

$q = mysql_query("SELECT id FROM ebay_orders WHERE dropship_done = 0 AND process_at <= NOW() AND process_at != '0000-00-00 00:00:00'");
while($data = mysql_fetch_assoc($q)){
	
	$sams->clear_cookie();
	
	$id = $data['id'];
	do_log('================');
	do_log('Processing order no '.$id);
	$res = mysql_fetch_assoc(mysql_query("SELECT *,UNIX_TIMESTAMP('locked_at') AS lock_time FROM ebay_orders WHERE id = '$id'"));
	if($res['dropship_locked']){
		/*if((abs(time() - $res['lock_time']) > 3600*1) && $res['lock_time'] != 0){
			do_log('Reactivating locked process '.$id);
		}
		else{
			do_log('Order is already being processed by another process');	
			continue;
		}*/
		do_log('Order is already being processed by another process');	
		continue;
	}
	
	if($res['dropship_done']){
		do_log('Order status changed');	
		continue;
	}
	
	mysql_query("UPDATE ebay_orders SET dropship_locked = 1, locked_at = NOW() WHERE id = '$id'");
	
	if(!empty($res['item_sku']))$sku = $res['item_sku'];
	else{
		do_log('Item sku empty');
		mysql_query("UPDATE ebay_orders SET dropship_done = 100, dropship_status = 'EMPTY_ITEM_SKU' WHERE id = '$id'");
		continue;	
	}
	
	if(!empty($res['item_quantity']) && is_numeric($res['item_quantity']))$quantity = $res['item_quantity'];
	else{
		do_log('Item item_quantity empty');
		mysql_query("UPDATE ebay_orders SET dropship_done = 100, dropship_status = 'EMPTY_ITEM_QTY' WHERE id = '$id'");
		continue;	
	}
	
	if(!empty($res['dropship_orderid']) || !empty($res['tracking_number'])){
		do_log('Order '.$id.' has a tracking number or vendor orderid already set');
		mysql_query("UPDATE ebay_orders SET dropship_done = 1, dropship_status = 'ITEM_ORDERED_DUP' WHERE id = '$id'");
		continue;	
	}
	
	$shipping = array();
	if(!empty($res['name']))$shipping['name'] = $res['name'];
	else{
		do_log('Shipping address name empty');
		mysql_query("UPDATE ebay_orders SET dropship_done = 100, dropship_status = 'EMPTY_SHIPPING_NAME' WHERE id = '$id'");
		continue;	
	}
	
	if(!empty($res['street1']))$shipping['street1'] = $res['street1'];
	else{
		do_log('Shipping address street1 empty');
		mysql_query("UPDATE ebay_orders SET dropship_done = 100, dropship_status = 'EMPTY_SHIPPING_STREET1' WHERE id = '$id'");
		continue;	
	}
	
	if(!empty($res['street2']))$shipping['street2'] = $res['street2'];
	else $shipping['street2'] = '';
	
	if(!empty($res['city']))$shipping['city'] = $res['city'];
	else{
		do_log('Shipping address city empty');
		mysql_query("UPDATE ebay_orders SET dropship_done = 100, dropship_status = 'EMPTY_SHIPPING_CITY' WHERE id = '$id'");
		continue;	
	}
	
	if(!empty($res['state_province']))$shipping['state_province'] = $res['state_province'];
	else{
		do_log('Shipping address state_province empty');
		mysql_query("UPDATE ebay_orders SET dropship_done = 100, dropship_status = 'EMPTY_SHIPPING_STATE' WHERE id = '$id'");
		continue;	
	}
	
	if(!empty($res['postal_code']))$shipping['postal_code'] = $res['postal_code'];
	else{
		do_log('Shipping address postal_code empty');
		mysql_query("UPDATE ebay_orders SET dropship_done = 100, dropship_status = 'EMPTY_SHIPPING_PSC' WHERE id = '$id'");
		continue;	
	}
	
	if(!empty($res['phone']))$shipping['phone'] = $res['phone'];
	else $shipping['phone'] = '';
	
	$shipping['paid_price'] = ($res['paid_amount'] - $res['expense_fee'])/$quantity;
	$shipping['ignore_loss'] = $res['ignore_loss'];
	$shipping['ignore_price'] = $res['ignore_price'];
	$shipping['ignore_tax'] = $res['ignore_tax'];
	
	$sams->init_order($sku, $quantity, $shipping);
	if(!empty($sams->error)){
		//retry
		do_log('Error making order on sams '.$sams->error);
		do_log('Retrying order...');
		$sams->error = '';
		$sams->clear_cookie();
		$sams->init_order($sku, $quantity, $shipping);
		//if error again then abort
		if(!empty($sams->error)){
			do_log('Error making order on sams '.$sams->error);
			mysql_query("UPDATE ebay_orders SET dropship_done = 100, dropship_status = '".mysql_real_escape_string($sams->error)."' WHERE id = '$id'");
			continue;
		}
	}
	
	if(empty($sams->sales_order_id)){
		do_log('Failed to get order id from sams');
		mysql_query("UPDATE ebay_orders SET dropship_done = 100, dropship_status = 'FAIL_GETTING_ORDER_ID' WHERE id = '$id'");
		continue;	
	}
	
	if(!empty($sams->total_cost))$price = mysql_real_escape_string($sams->total_cost);
	else $price = mysql_real_escape_string($sams->product_unit_price*$quantity);
	
	$expense_fee = $res['expense_fee'];
	$profit = mysql_real_escape_string(round($res['paid_amount'] - $price - $expense_fee, 2));
	
	do_log('Successfully ordered item #'.$sku.' order_no '.$id);
	mysql_query("UPDATE ebay_orders SET dropship_done = 1, dropship_status = 'ITEM_ORDERED', dropship_orderid = '".mysql_real_escape_string($sams->sales_order_id)."', purchase_price = '$price' , amount_profit = '$profit' WHERE id = '$id'");
	
	list($today) = explode(' ',$res['added_at']);
	update_profit_sales($today);
	
	do_log('================');
}

unlock_process($flock);
do_log('===================================================');
do_log('Ending process');
do_log('===================================================');

save_logs($set);
?>