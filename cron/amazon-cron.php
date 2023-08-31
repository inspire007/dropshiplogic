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
include(dirname(dirname(__FILE__)).'/classes/dom.class.php');
include(dirname(dirname(__FILE__)).'/classes/amazon.class.php');

$set = 'amazon';
clear_logs($set);

if(check_running($set))exit('An instance of amazon cron is already running');
$flock = lock_process($set);

do_log('===================================================');
do_log('Starting process');
do_log('===================================================');

$next_token = '';

$q = mysql_query("SELECT * FROM amazon_tokens");
while($res = mysql_fetch_assoc($q)):
	$kkk = 0;
	do{
		$amazon = new AWSApp();
		
		$amazon->AMAZON_AWS_Access_Key_ID = $res['access_id'];
		$amazon->AMAZON_Marketplace_ID = $res['marketplace_id'];
		$amazon->AMAZON_Merchant_ID = $res['merchant_id'];
		$amazon->AMAZON_Secret_Key = $res['secret_key'];
		$amazon->username = $res['user_id'];
		$amazon->password = $res['password'];
		if(!$amazon->login()){
			exit('Amazon login failed');	
		}
		
		if(empty($next_token))$orders = $amazon->get_orders();
		else $orders = $amazon->get_next_orders($next_token);
		
		if(!empty($amazon->data->ListOrdersResult->NextToken))$next_token = $amazon->data->ListOrdersResult->NextToken;
		else if(!empty($amazon->data->ListOrdersByNextTokenResult->NextToken))$next_token = $amazon->data->ListOrdersByNextTokenResult->NextToken;
		else $next_token = '';
		
		//$next_token = '';
		
		//moved to amazon.class.php
		if(!empty($orders))$amazon->process_orders($orders, $res['user_id']);
		
		if($kkk++ >= 10)break;
		
		if(!empty($next_token)){
			do_log('Getting next orders with token '.$next_token.'...');
			sleep(15);
			continue;	
		}
		
		$amazon->process_commission();
		break;
		
	}while(1);
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