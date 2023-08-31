<?php
/**
 * This script is used to automate the Sams classes.
 * It only places orders on Sams and saves info on mysql
 *
 * @package EbaySalesAutomation
 * @subpackage PostOrderCron
 * @version 1.0
 * @author N/A
 * @copyright 2014
 */
 
/**
 * dropship_status glossary:
 * 0 -> not processed yet or queued
 * 1 -> successfully ordered
 * 2 -> order id added to ebay sales note
 * 3 -> shipping tracking info added on ebay
 */

set_time_limit(0);
$no_session = true; 
include(dirname(dirname(__FILE__)).'/config.php');
include(dirname(dirname(__FILE__)).'/classes/sams.class.php');
include(dirname(dirname(__FILE__)).'/classes/dom.class.php');
include(dirname(dirname(__FILE__)).'/classes/amazon.class.php');


/**
 * updating sales records
 */

$today = date('Y-m-d');
update_profit_sales($today);

if(!is_dir(dirname(dirname(__FILE__)).'/logs'))mkdir(dirname(dirname(__FILE__)).'/logs');

$set = 'amazon-postorder';
clear_logs($set);

if(check_running($set))exit('An instance of Amazon cron is already running');
$flock = lock_process($set);

do_log('===================================================');
do_log('Starting process');
do_log('===================================================');

$wait_time = 12;
if(!empty($settings['postorder_wait_time']))$wait_time = $settings['postorder_wait_time'];

$amazon = '';
$sams = new SamsClub();

/*
 * Set another set of cookie file for pos
 */
 
$sams->cookie = dirname(dirname(__FILE__))."/logs/pos-cookie.txt";
$sams->log_file = dirname(dirname(__FILE__))."/logs/pos-sams-log.txt";

$sams->clear_cookie();
$sams->clear_log();

/**
 * when dropship_status = 1 or 2 then we will check for the orderid and shipping tracking url
 * regular orders
 */
$q = mysql_query("SELECT id FROM ebay_orders WHERE (dropship_done = 1 OR (dropship_done = 2 AND locked_at <= DATE_SUB(NOW(), INTERVAL $wait_time HOUR))) AND locked_at != '0000-00-00 00:00:00' AND dropship_orderid != '' AND process_at != '0000-00-00 00:00:00' AND sales_channel = 'Amazon'");
process_postorder($q);


/**
 * manual orders when manually orderid or tracking no added
 */

$q = mysql_query("SELECT id FROM ebay_orders WHERE (dropship_done = 1 OR dropship_done = 2) AND (dropship_orderid != '' OR tracking_number != '') AND process_at = '0000-00-00 00:00:00' AND (locked_at <= DATE_SUB(NOW(), INTERVAL $wait_time HOUR) OR locked_at = '0000-00-00 00:00:00') AND sales_channel = 'Amazon'");
process_postorder($q);


function process_postorder($mysql_handle)
{
	global $sams, $amazon;
	$ii = 0;
	$stack = array();
	
	while($data = mysql_fetch_assoc($mysql_handle)):
		
		if($ii > 10){
			//$ii = 0;
			//do_log('Sleeping for two minute....');
			//sleep(120);
		}
		
		$id = $data['id'];
		
		$res = mysql_fetch_assoc(mysql_query("SELECT *,UNIX_TIMESTAMP(added_at) AS added_time FROM ebay_orders WHERE id = '$id'"));
		mysql_query("UPDATE ebay_orders SET locked_at = NOW() WHERE id = '$id'");
		
		if(abs(time() - $res['added_time']) > 3600*24*10){
			do_log('Item pending for 10 days. Marking as error | '.time().'|'.$res['added_time']);
			mysql_query("UPDATE ebay_orders SET dropship_done = 200, dropship_status = 'TIMEOUT_SHIPPING_STATS' WHERE id = '$id'");
			continue;	
		}
		
		$seller_id = $res['seller_id'];
		$order_id = $res['order_id'];
		$item_id = $res['item_id'];
		$status = $res['dropship_done'];
		$vendor = $res['vendor'];
		$quantity = $res['item_quantity'];
		
		do_log('Processing post order no '.$id.', order id '.$order_id);
		
		$udata = mysql_fetch_assoc(mysql_query("SELECT * FROM amazon_tokens WHERE user_id = '".mysql_real_escape_string($seller_id)."'"));
		
		if(empty($udata['user_id'])){
			do_log('Item owner amazon id not found');
			mysql_query("UPDATE ebay_orders SET dropship_done = 200, dropship_status = 'EMPTY_AM_SELLER' WHERE id = '$id'");
			continue;	
		}
		
		if(!empty($res['dropship_orderid']))$sams_order_id = $res['dropship_orderid'];
		else{
			do_log('Item orderid empty');
			mysql_query("UPDATE ebay_orders SET dropship_done = 200, dropship_status = 'EMPTY_ORDER_ID' WHERE id = '$id'");
			continue;	
		}	
		
		$sams->sales_order_id = trim($sams_order_id);
		$sams->collect_order_info();
		//exit;
		$txt = '';
		if(!empty($sams->order_status))$txt = $sams->order_status."\r\n";
		if(!empty($sams->shipment_status))$txt .= $sams->shipment_status;
		mysql_query("UPDATE ebay_orders SET dropship_notes = '".mysql_real_escape_string($txt)."' WHERE id = '$id'");
		
		$note_update = 0;
		$tracking_update = 0;
		
		if(!empty($sams->priv_note)){
			$note_update = 1;
			mysql_query("UPDATE ebay_orders SET ebay_sales_notes = '".mysql_real_escape_string($sams->priv_note)."' WHERE id = '$id'");
			do_log('Updating seller note in ebay');
		}
		
		if(!empty($sams->tracking_no)){
			$tracking_update = 1;
			do_log('Updating tracking number for order no. '.$sams_order_id.' | '.$id);		
			mysql_query("UPDATE ebay_orders SET tracking_number = '".mysql_real_escape_string($sams->tracking_no)."', tracking_url = '".mysql_real_escape_string($sams->tracking_url)."', tracking_carrier = '".mysql_real_escape_string($sams->tracking_carrier)."' WHERE id = '$id'");
		}
		
		if(($note_update && $status == 1) || $tracking_update){
		
			if(empty($amazon)){
				$amazon = new AWSApp();
			
				$amazon->AMAZON_AWS_Access_Key_ID = $udata['access_id'];
				$amazon->AMAZON_Marketplace_ID = $udata['marketplace_id'];
				$amazon->AMAZON_Merchant_ID = $udata['merchant_id'];
				$amazon->AMAZON_Secret_Key = $udata['secret_key'];
				$amazon->username = $udata['user_id'];
				$amazon->password = $udata['password'];
				
				if(!$amazon->login()){
					do_log('Amazon login failed');
					exit();
				}
			}
			
			if($note_update && $status == 1){
				$mini_note = '';
				if(!empty($sams_order_id))$mini_note = $vendor.' - Order Number : '.$sams_order_id;
				if($amazon->update_sales_note($order_id, $mini_note)){
					do_log('Seller note successfully added');	
				}	
				else do_log('Failed to add seller note');
			}
			
			if($tracking_update){
				$ii++;
				$stack[] = array(
								'order_id' => $order_id, 
								'no' => $sams->tracking_no, 
								'carrier' => $sams->tracking_carrier, 
								'quantity' => $quantity, 
								'item_id' => $item_id, 
								'ref' => $id
								);
			}
			else if($status == 1){
				$ii++;
				$stack[] = array(
								'order_id' => $order_id, 
								'no' => '', 
								'carrier' => 'Fedex', 
								'quantity' => $quantity, 
								'item_id' => $item_id, 
								'ref' => $id
								);
			}
		}
		
		if(count($stack) >= 100){
			process_bulk_update($stack);
			$stack = array();	
		}
		
	endwhile;
	
	if(!empty($stack)){
		process_bulk_update($stack);
		$stack = array();	
	}
}

function process_bulk_update($stack)
{
	global $amazon;
	if(!empty($stack)){
		do_log('Adding bulk tracking number');
		$r = $amazon->set_bulk_tracking_number($stack);		
		if($r != 'SUCCESS'){
			do_log('Failed to add tracking number. Reason : '.$r);
			do_log(print_r($stack), true);	
			if($r == 'REQUEST_THROTTOLED'){
				do_log('Request throttoled. Sleeping for two minutes');
				sleep(120);
				//do_log('Request throttoled. Exit...');
				//exit();	
			}
		}	
		else{	
			$err = $amazon->errors;
			foreach($stack as $ss){
				if(array_key_exists($ss['ref'], $err)){
					do_log('Failed to add tracking number to #'.$ss['ref'].' | Reason: '.$err[$ss['ref']].' | '.print_r($ss, true));
					continue;
				}
				
				if(empty($ss['no'])){
					$status = 'ITEM_SHIPPED';
					$code = 2;
				}
				else{
					$status = 'ITEM_TRACKED';
					$code = 3;	
				}
				
				mysql_query("UPDATE ebay_orders SET dropship_done = '$code', dropship_status = '$status' WHERE id = '".$ss['ref']."'");	
				do_log('Tracking number successfully added to #'.$ss['ref'].' | '.mysql_affected_rows().' | '.print_r($ss, true));
			}
		}		
	}
}

unlock_process($flock);
do_log('===================================================');
do_log('Ending process');
do_log('===================================================');

save_logs($set);
?>