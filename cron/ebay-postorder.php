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
include(dirname(dirname(__FILE__)).'/classes/ebay.class.php');
include(dirname(dirname(__FILE__)).'/classes/api.class.php');


/**
 * updating sales records
 */

$today = date('Y-m-d');
update_profit_sales($today);

if(!is_dir(dirname(dirname(__FILE__)).'/logs'))mkdir(dirname(dirname(__FILE__)).'/logs');

$set = 'ebay-postorder';
clear_logs($set);

if(check_running($set))exit('An instance of ebay cron is already running');
$flock = lock_process($set);

do_log('===================================================');
do_log('Starting process');
do_log('===================================================');

$wait_time = 12;
if(!empty($settings['postorder_wait_time']))$wait_time = $settings['postorder_wait_time'];

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
$q = mysql_query("SELECT id FROM ebay_orders WHERE (dropship_done = 1 OR (dropship_done = 2 AND locked_at <= DATE_SUB(NOW(), INTERVAL $wait_time HOUR))) AND locked_at != '0000-00-00 00:00:00' AND dropship_orderid != '' AND process_at != '0000-00-00 00:00:00' AND sales_channel = 'Ebay'");

while($data = mysql_fetch_assoc($q)){
	$id = $data['id'];
	
	$res = mysql_fetch_assoc(mysql_query("SELECT *,UNIX_TIMESTAMP(added_at) AS added_time FROM ebay_orders WHERE id = '$id'"));
	mysql_query("UPDATE ebay_orders SET locked_at = NOW() WHERE id = '$id'");
	
	if(abs(time() - $res['added_time']) > 3600*24*10){
		do_log('Item pending for 10 days. Marking as error | '.time().'|'.$res['added_time']);
		mysql_query("UPDATE ebay_orders SET dropship_done = 200, dropship_status = 'TIMEOUT_SHIPPING_STATS' WHERE id = '$id'");
		continue;	
	}
	
	$seller_id = $res['seller_id'];
	$ebay_order_id = $res['order_id'];
	$item_id = $res['item_id'];
	$status = $res['dropship_done'];
	
	do_log('Processing post order no '.$id.', order id '.$ebay_order_id);
	
	$udata = mysql_fetch_assoc(mysql_query("SELECT * FROM ebay_tokens WHERE user_id = '".mysql_real_escape_string($seller_id)."'"));
	
	if(empty($udata['user_id'])){
		do_log('Item owner ebay id not found');
		mysql_query("UPDATE ebay_orders SET dropship_done = 200, dropship_status = 'EMPTY_EBAY_SELLER' WHERE id = '$id'");
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
		$api = new EbayApiMaster();
		$api->user_id = $udata['user_id'];
		$api->token = $udata['token'];
		
		if($note_update){
			$mini_note = '';
			if(!empty($sams_order_id))$mini_note = 'Sams - Order Number : '.$sams_order_id;
			if($api->setEbaySalesNote($ebay_order_id, $item_id, $sams->priv_note, '', $mini_note)){
				do_log('Seller note successfully added');	
			}	
			else do_log('Failed to add seller note');
			
			mysql_query("UPDATE ebay_orders SET dropship_done = 2, dropship_status = 'ITEM_NOTED' WHERE id = '$id'");
		}
		
		if($tracking_update){
			if($api->setEbayTrackingNumber($ebay_order_id, $item_id, $sams->tracking_no, $sams->tracking_carrier)){
				do_log('Tracking number successfully added');	
				mysql_query("UPDATE ebay_orders SET dropship_done = 3, dropship_status = 'ITEM_TRACKED' WHERE id = '$id'");
			}
			else do_log('Failed to add tracking number');
			
			if(!empty($sams->tracking_url)){
				if($api->setEbaySalesNote($ebay_order_id, $item_id, '', 'Tracking url # '.$sams->tracking_url, '')){
					do_log('Buyer note successfully added');	
				}	
				else do_log('Failed to add buyer note');
			}	
		}
	}
}

/**
 * manual orders when manually orderid or tracking no added
 */

$q = mysql_query("SELECT id FROM ebay_orders WHERE (dropship_done = 1 OR dropship_done = 2) AND (dropship_orderid != '' OR tracking_number != '') AND process_at = '0000-00-00 00:00:00' AND (locked_at <= DATE_SUB(NOW(), INTERVAL $wait_time HOUR) OR locked_at = '0000-00-00 00:00:00') AND sales_channel = 'Ebay'");

while($data = mysql_fetch_assoc($q)){
	$id = $data['id'];
	
	$res = mysql_fetch_assoc(mysql_query("SELECT *,UNIX_TIMESTAMP(added_at) AS added_time FROM ebay_orders WHERE id = '$id'"));
	mysql_query("UPDATE ebay_orders SET locked_at = NOW() WHERE id = '$id'");
	
	$seller_id = $res['seller_id'];
	$ebay_order_id = $res['order_id'];
	$item_id = $res['item_id'];
	$status = $res['dropship_done'];
	$vendor = $res['vendor'];
	
	do_log('Processing manual post order no '.$id.', order id '.$ebay_order_id);
	
	$udata = mysql_fetch_assoc(mysql_query("SELECT * FROM ebay_tokens WHERE user_id = '".mysql_real_escape_string($seller_id)."'"));
	
	if(empty($udata['user_id'])){
		do_log('Item owner ebay id not found');
		mysql_query("UPDATE ebay_orders SET dropship_done = 200, dropship_status = 'EMPTY_EBAY_SELLER' WHERE id = '$id'");
		continue;	
	}
	
	if(!empty($res['dropship_orderid']) && $status < 2){
		$sams_order_id = $res['dropship_orderid'];
		do_log('Updating seller note in ebay');
		
		$mini_note = $priv_note = $vendor.' order id : '.$sams_order_id;
		$api = new EbayApiMaster();
		$api->user_id = $udata['user_id'];
		$api->token = $udata['token'];
		
		if($api->setEbaySalesNote($ebay_order_id, $item_id, $priv_note, '', $mini_note)){
			do_log('Seller note successfully added');	
		}	
		else do_log('Failed to add seller note | '.@$api->data->Errors->LongMessage);
			
		mysql_query("UPDATE ebay_orders SET dropship_done = 2, dropship_status = 'ITEM_ORDERED_M' WHERE id = '$id'");
	}
	
	if(!empty($res['tracking_number'])){
		if($api->setEbayTrackingNumber($ebay_order_id, $item_id, $res['tracking_number'], $res['tracking_carrier'])){
			do_log('Tracking number successfully added');	
			mysql_query("UPDATE ebay_orders SET dropship_done = 3, dropship_status = 'ITEM_SHIPPED_M' WHERE id = '$id'");
		}
		else do_log('Failed to add tracking number | '.@$api->data->Errors->LongMessage);
		
		if(!empty($res['tracking_url'])){
			if($api->setEbaySalesNote($ebay_order_id, $item_id, '', 'Tracking url # '.$res['tracking_url'], '')){
				do_log('Buyer note successfully added');	
			}	
			else do_log('Failed to add buyer note | '.@$api->data->Errors->LongMessage);
		}	
	}
}


unlock_process($flock);
do_log('===================================================');
do_log('Ending process');
do_log('===================================================');

save_logs($set);
?>