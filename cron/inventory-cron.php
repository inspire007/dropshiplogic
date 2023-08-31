<?php
/**
 * This script is used to adjust inventory in Amazon and Ebay with Sams.
 * It checks inventory in SamsClub and Adjust into Ebay and Amazon
 *
 * @package EbaySalesAutomation
 * @subpackage InventoryCron
 * @version 1.0
 * @author N/A
 * @copyright 2014
 */
 
set_time_limit(0);
$no_session = true; 
include(dirname(dirname(__FILE__)).'/config.php');
include(dirname(dirname(__FILE__)).'/classes/ebay.class.php');
include(dirname(dirname(__FILE__)).'/classes/amazon.class.php');
include(dirname(dirname(__FILE__)).'/classes/api.class.php');

if(!is_dir(dirname(dirname(__FILE__)).'/logs'))mkdir(dirname(dirname(__FILE__)).'/logs');

$set = 'inventory';
clear_logs($set);

if(check_running($set))exit('An instance of inventory cron is already running');
$flock = lock_process($set);

do_log('===================================================');
do_log('Starting process');
do_log('===================================================');

list($start) = mysql_fetch_row(mysql_query("SELECT NOW()"));

$url = array();
$q = mysql_query("SELECT * FROM ebay_items WHERE vendor = 'SamsClub'");
while($res = mysql_fetch_assoc($q)):
	$iii = $res['id'];
	//if a sku is already check skip it
	if(!mysql_num_rows(mysql_query("SELECT NULL FROM ebay_items WHERE id = '$iii' AND last_update < '$start'")))continue;
	$url[] = 'http://www.samsclub.com/sams/search/searchResults.jsp?searchTerm='.$res['sku'].'&searchCategoryId=all';
	if(count($url) >= CURL_LIMIT){
		make_request($url);
		$url = array();	
	}
	//exit;
endwhile;

if(!empty($url)){
	make_request($url);
	$url = array();	
}

//exit;
//now update

do_log('Adjusting inventory into ebay...');

$qqq = mysql_query("SELECT * FROM ebay_tokens");
while($rres = mysql_fetch_assoc($qqq)):
	
	$api = new EbayApiMaster();
	$api->user_id = $rres['user_id'];
	$api->token = $rres['token'];
	
	$u = mysql_real_escape_string($rres['user_id']);
	$inv = array();
	$q = mysql_query("SELECT * FROM ebay_items WHERE (quantity = 0 OR quantity = -1) AND last_update > '$start' AND sales_channel = 'Ebay' AND vendor = 'SamsClub' AND user_id = '$u'");
	while($res = mysql_fetch_assoc($q)):
		if($res['quantity'] == 0)$inv[$res['item_id']] = 0;
		else{
			$inv[$res['item_id']] = 10;
			mysql_query("UPDATE ebay_items SET quantity = 10 WHERE id = '".$res['id']."'");
		}
		
		do_log('Changing inventory of SKU '.$res['sku'].' and item id '.$res['item_id'].' to '.$inv[$res['item_id']]);
		
		if(count($inv) >= 100){
			if($api->setEbayInventory($inv))do_log('Stock update successful...');
			else do_log('Stock Update failed...');
			$inv = array();			
		}
		 
	endwhile;
	
	if(!empty($inv)){
		if($api->setEbayInventory($inv))do_log('Stock update successful...');
		else do_log('Stock update failed...');
		$inv = array();			
	}
	
endwhile;

do_log('Adjusting inventory into amazon...');

$qqq = mysql_query("SELECT * FROM amazon_tokens");
while($rres = mysql_fetch_assoc($qqq)):
	
	$amazon = new AWSApp();
	
	$amazon->AMAZON_AWS_Access_Key_ID = $rres['access_id'];
	$amazon->AMAZON_Marketplace_ID = $rres['marketplace_id'];
	$amazon->AMAZON_Merchant_ID = $rres['merchant_id'];
	$amazon->AMAZON_Secret_Key = $rres['secret_key'];
	$amazon->username = $rres['user_id'];
	$amazon->password = $rres['password'];
	
	$u = mysql_real_escape_string($rres['user_id']);
	$inv = array();
	$q = mysql_query("SELECT * FROM ebay_items WHERE (quantity = 0 OR quantity = -1) AND last_update > '$start' AND sales_channel = 'Amazon' AND vendor = 'SamsClub' AND user_id = '$u'");
	while($res = mysql_fetch_assoc($q)):
		if($res['quantity'] == 0)$inv[$res['sku']] = 0;
		else{
			$inv[$res['sku']] = 10;
			mysql_query("UPDATE ebay_items SET quantity = 10 WHERE id = '".$res['id']."'");
		}
		
		do_log('Changing inventory of SKU '.$res['sku'].' and item id '.$res['item_id'].' to '.$inv[$res['sku']]);
		
		if(count($inv) >= 500){
			$m = $amazon->UpdateInventory($inv);
			if($m != 'SUCCESS')do_log($m);
			else do_log('Stock updated...');
			$inv = array();			
		}
		 
	endwhile;
	
	if(!empty($inv)){
		$m = $amazon->UpdateInventory($inv);
		if($m != 'SUCCESS')do_log($m);
		else do_log('Stock updated...');
		$inv = array();			
	}
	
endwhile;

unlock_process($flock);
do_log('===================================================');
do_log('Ending process');
do_log('===================================================');

save_logs($set);


function make_request($url)
{
	$app = new AWSApp();
	$app->curl_multi = 1;
	$app->doPost = 0;
	$app->url = $url;
	$app->cookie = '';
	$app->get_source();
	
	for($i = 0; $i < count($app->url); $i++){
		$sku = '';
		$qty = -1;
		$response = $app->response[$i];
		//echo htmlentities($response);
		if(preg_match("/'skuID' : '([0-9]+)',/siU", $response, $m)){
			do_log('SkuID '.$m[1].' processing...');
			$sku = mysql_real_escape_string($m[1]);
			if(preg_match('/Out of stock online/siU', $response)){
				$qty = 0;
				do_log($sku.' is out of stock');
			}
			else if(preg_match('/Buy Online/siU', $response)){
				$qty = 1;
				do_log($sku.' is in stock');
			}
			else{ 
				do_log($sku.' could not be checked');
				$qty = -1;
			}
		}
		else if(preg_match('/<h2>We found <span>0 results <\/span> for  "([0-9]+)"\.<\/h2>/siU', $response, $m) 
				|| preg_match("/nullSearchPage\.jsp\?searchTerm=([0-9]+)'/siU", $response, $m)){
			do_log('SKU '.$m[1].' returned zero result...');
			$sku = mysql_real_escape_string($m[1]);
			$qty = 0;	
		}
		
		if(!empty($sku)){
			if($qty == 0)mysql_query("UPDATE ebay_items SET quantity = 0, last_update = NOW() WHERE sku = '$sku' AND quantity != 0");	
			else if($qty == 1)mysql_query("UPDATE ebay_items SET quantity = -1, last_update = NOW() WHERE sku = '$sku' AND quantity = 0");
		}	
	}
		
}

?>