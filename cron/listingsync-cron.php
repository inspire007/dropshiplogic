<?php

/**
 * This script is used to sync inventory from eBay and Amazon.
 * It only fetches listings from seller account and saves in mysql
 *
 * @package EbaySalesAutomation
 * @subpackage ListingSyncCron
 * @version 1.0
 * @author N/A
 * @copyright 2014
 */
 
set_time_limit(0);
$no_session = true;
$ebay_start_date = '2013-11-01';
$amazon_start_date = '2014-01-01';
 
include(dirname(dirname(__FILE__)).'/config.php');
include(dirname(dirname(__FILE__)).'/classes/ebay.class.php');
include(dirname(dirname(__FILE__)).'/classes/amazon.class.php');
include(dirname(dirname(__FILE__)).'/classes/api.class.php');

if(!is_dir(dirname(dirname(__FILE__)).'/logs'))mkdir(dirname(dirname(__FILE__)).'/logs');

$set = 'inventory';
clear_logs($set);

if(check_running($set))exit('An instance of listing sync cron is already running');
$flock = lock_process($set);

do_log('===================================================');
do_log('Starting process');
do_log('===================================================');

mysql_query("UPDATE ebay_items SET last_update = '0000-00-00 00:00:00'");

do_log('Syncing ebay items...');

$q = mysql_query("SELECT * FROM ebay_tokens");
while($res = mysql_fetch_assoc($q)):
	
	$api = new EbayApiMaster();
	$api->user_id = $res['user_id'];
	$api->token = $res['token'];
	
	do_log('Getting orders from user '.$res['user_id']);
	
	$kk = 0;
	do{	
		$nn = strtotime($ebay_start_date) + 30*3600*24*$kk;
		if(time() <= $nn)break;
		if($kk++ >= 100)break;
		$now = date('Y-m-d', $nn);
		do_log('Getting orders from '.$now);
		$api->getEbayListing($now, 30);
		$items = $api->listing;
		
		//var_dump($items);
		//exit;
		
		foreach($items as $item){
			if($item->SellingStatus->ListingStatus != 'Active')continue;
			$title = mysql_real_escape_string($item->Title);
			$sku = mysql_real_escape_string($item->SKU);
			$qty = mysql_real_escape_string((int)$item->Quantity - (int)$item->SellingStatus->QuantitySold);
			$item_id = mysql_real_escape_string($item->ItemID);
			$price = mysql_real_escape_string($item->SellingStatus->CurrentPrice);
			mysql_query("INSERT INTO ebay_items VALUES(0, '".mysql_real_escape_string($res['user_id'])."', '$title' , '$item_id', '$sku', 'Ebay', 'SamsClub', '$qty', '$price', NOW()) ON DUPLICATE KEY UPDATE quantity = '$qty', price = '$price', last_update = NOW()");		
		}
		sleep(10);
	}while(1);
endwhile;


do_log('Syncing amazon items...');

$q = mysql_query("SELECT * FROM amazon_tokens");
while($res = mysql_fetch_assoc($q)):
	
	$amazon = new AWSApp();
	
	$amazon->AMAZON_AWS_Access_Key_ID = $res['access_id'];
	$amazon->AMAZON_Marketplace_ID = $res['marketplace_id'];
	$amazon->AMAZON_Merchant_ID = $res['merchant_id'];
	$amazon->AMAZON_Secret_Key = $res['secret_key'];
	$amazon->username = $res['user_id'];
	$amazon->password = $res['password'];
		
	$data = $amazon->get_listings($amazon_start_date);
	
	if(empty($data)){
		do_log('No listing data received from amazon...');
		continue;	
	}
	
	if($data == 'EMPTY_REQ_ID'){
		do_log($data);
		continue;
	}
	
	$f = dirname(dirname(__FILE__)).'/logs/listings-amazon.txt';
	file_put_contents($f, $data);	
	
	$fp = fopen($f, 'r');
	$line = fgetcsv($fp, 4096, "\t");
	while($line = fgetcsv($fp, 4096, "\t")){
		$title = mysql_real_escape_string($line[0]);
		$sku = mysql_real_escape_string($line[3]);
		$qty = mysql_real_escape_string($line[5]);
		$price = mysql_real_escape_string($line[4]);
		$item_id = mysql_real_escape_string($line[16]);
		mysql_query("INSERT INTO ebay_items VALUES(0, '".mysql_real_escape_string($res['user_id'])."', '$title' , '$item_id', '$sku', 'Amazon', 'SamsClub', '$qty', '$price', NOW()) ON DUPLICATE KEY UPDATE quantity = '$qty', price = '$price', last_update = NOW()");		
	}
endwhile;

mysql_query("DELETE FROM ebay_items WHERE last_update = '0000-00-00 00:00:00'");

unlock_process($flock);
do_log('===================================================');
do_log('Ending process');
do_log('===================================================');

save_logs($set);

?>