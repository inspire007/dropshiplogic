<?php
/**
 * This script is used to get orders from ebay and set OSC.
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
include(dirname(dirname(__FILE__)).'/classes/ebay.class.php');
include(dirname(dirname(__FILE__)).'/classes/api.class.php');

if(!is_dir(dirname(dirname(__FILE__)).'/logs'))mkdir(dirname(dirname(__FILE__)).'/logs');

$set = 'ebayosc';
clear_logs($set);

if(check_running($set))exit('An instance of ebay cron is already running');
$flock = lock_process($set);

do_log('===================================================');
do_log('Starting process');
do_log('===================================================');

mysql_query("DELETE FROM ebay_orders WHERE added_at < DATE_SUB(NOW(), INTERVAL 365 DAY)");

$q = mysql_query("SELECT * FROM ebay_tokens");
while($res = mysql_fetch_assoc($q)):
	
	$api = new EbayApiMaster();
	$api->user_id = $res['user_id'];
	$api->token = $res['token'];
	
	do_log('Getting items from user '.$res['user_id']);
		
	$api->getEbayListing();
	
	do_log(count($api->listing).' items found');
	
	foreach($api->listing as $item):
		$item_id = $item->ItemID;
		if(empty($item_id))continue;
		
		if(get_osc($item_id)){
			do_log('Item '.$item_id.' is already processed');		
			continue;
		}
		//set OSC
		if($api->setOSC($item_id)){
			do_log('OutofStockControl successfully set for itemid '.$item_id);	
			write_osc($item_id);
		}
		else do_log('OutofStockControl could not be set for itemid '.$item_id.' | '.@$api->data->Errors->LongMessage);	
	endforeach;
	
endwhile;

unlock_process($flock);
do_log('===================================================');
do_log('Ending process');
do_log('===================================================');

save_logs($set);

function write_osc($item_id)
{
	$fp = fopen(dirname(dirname(__FILE__)).'/logs/osc.txt', 'a');
	fwrite($fp, $item_id."\r\n");
	fclose($fp);
}

function get_osc($item_id)
{
	if(!file_exists(dirname(dirname(__FILE__)).'/logs/osc.txt'))return false;
	$fp = fopen(dirname(dirname(__FILE__)).'/logs/osc.txt', 'r');
	while($line = fgets($fp, 4096)){
		$line = trim($line);
		if($line == $item_id)return true;	
	}
	return false;
}

?>