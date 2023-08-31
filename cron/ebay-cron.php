<?php
/**
 * This script is used to automate the eBay classes.
 * It only fetches orders from seller account and saves in mysql
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

$set = 'ebay';
clear_logs($set);

if(check_running($set))exit('An instance of ebay cron is already running');
$flock = lock_process($set);

do_log('===================================================');
do_log('Starting process');
do_log('===================================================');

//mysql_query("DELETE FROM ebay_orders WHERE added_at < DATE_SUB(NOW(), INTERVAL 365 DAY)");

$q = mysql_query("SELECT * FROM ebay_tokens");
while($res = mysql_fetch_assoc($q)):
	
	$api = new EbayApiMaster();
	$api->user_id = $res['user_id'];
	$api->token = $res['token'];
	
	do_log('Getting orders from user '.$res['user_id']);
		
	$api->getEbayOrders();
	
	do_log(count($api->listing).' orders found');
	
	//moved to api.class.php
	$api->processOrders($api->listing, $res['user_id']);
	
endwhile;

do_log('Updating todays profit...');
$today = date('Y-m-d');
update_profit_sales($today);

do_log('Updating yesterdays profit...');
$today = date('Y-m-d', time() - 3600*24);
update_profit_sales($today);

do_log('Updating day before yesterdays profit...');
$today = date('Y-m-d', time() - 3600*24*2);
update_profit_sales($today);

unlock_process($flock);
do_log('===================================================');
do_log('Ending process');
do_log('===================================================');

save_logs($set);

?>