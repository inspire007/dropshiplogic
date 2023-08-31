<?php
/**
 * Index file
 *
 * @package EbaySalesAutomation
 * @subpackage Home
 * @version 1.0
 * @author N/A
 * @copyright 2014
 */
 
/**
  login required to access this page
 */
$login_required = 1;
/**
  then include config file
  this will check login
 */
include(dirname(__FILE__).'/config.php');
include(dirname(__FILE__).'/classes/dom.class.php');
include(dirname(__FILE__).'/classes/amazon.class.php');
include(dirname(__FILE__).'/classes/ebay.class.php');
include(dirname(__FILE__).'/classes/api.class.php');

$response = array();
$response['error'] = '';

function print_response()
{
	global $response;
	echo json_encode($response);
	exit();
}


if(!empty($_POST['initEbayTokenSession'])){
	include(dirname(__FILE__).'/classes/ebay.class.php');

	$ebay = new EbayApp();
	$ebay->eBay('GetSessionID');
	if(empty($ebay->data->SessionID)){
		$response['error'] = 'Failed to get session id'; 
		print_response();
	}
	
	$ebay->SessionID = $ebay->data->SessionID;
	$url = EBAY_SANDBOX ?  'https://signin.sandbox.ebay.com' : 'https://signin.ebay.com';
	$url .= '/ws/eBayISAPI.dll?SignIn&RuName='.EBAY_RU_NAME.'&SessID='.$ebay->SessionID;
	$response['url'] = $url;
	$response['SessionID'] = $ebay->SessionID;
	print_response();	
}

else if(!empty($_POST['authEbaySession'])){
	include(dirname(__FILE__).'/classes/ebay.class.php');
	
	$SessionID = $_POST['authEbaySession'];
	$ebay = new EbayApp();
	$ebay->SessionID = $SessionID;
	$ebay->eBay('FetchToken');
	
	if(empty($ebay->data->eBayAuthToken)){
		$response['error'] = 'EBAY_ERROR: No token received';	
		print_response();	
	}
	
	$token = mysql_real_escape_string($ebay->data->eBayAuthToken);
	$ebay->eBay('ConfirmIdentity');
	
	if(empty($ebay->data->UserID)){
		$response['error'] = 'EBAY_ERROR: No userid received';	
		print_response();	
	}
	
	$user_id = mysql_real_escape_string($ebay->data->UserID);
	
	if(mysql_num_rows(mysql_query("SELECT NULL FROM ebay_tokens WHERE user_id = '$user_id'"))){
		mysql_query("UPDATE ebay_tokens SET token = '$token', added_at = NOW() WHERE user_id = '$user_id'");	
	}
	else mysql_query("INSERT INTO ebay_tokens VALUES(0, '$user_id', '$token', NOW())");
	
	$response['error'] = mysql_error();
	$response['data'] = 'EBAY_UID: <b>'.$user_id.'</b> saved successfully';
	print_response();
}

else if(!empty($_POST['delEbayID'])){
	$delEbayID = mysql_real_escape_string($_POST['delEbayID']);
	mysql_query("DELETE FROM ebay_tokens WHERE id = '$delEbayID'");
	$response['error'] = mysql_error();
	print_response();
}

else if(!empty($_POST['delAmID'])){
	$delAmID = mysql_real_escape_string($_POST['delAmID']);
	mysql_query("DELETE FROM amazon_tokens WHERE id = '$delAmID'");
	$response['error'] = mysql_error();
	print_response();
}

else if(!empty($_POST['queueOrder'])){
	$orders = explode(',', $_POST['queueOrder']);
	foreach($orders as $order_no){
		if(empty($order_no))continue;
		$order_no = mysql_real_escape_string($order_no);
		mysql_query("UPDATE ebay_orders SET process_at = NOW(), dropship_locked = 0 WHERE id = '$order_no' AND dropship_done = 0");
		$response['error'] = mysql_error();
	}
	print_response();
}

else if(!empty($_POST['suspendOrder'])){
	$orders = explode(',', $_POST['suspendOrder']);
	foreach($orders as $order_no){
		if(empty($order_no))continue;
		$order_no = mysql_real_escape_string($order_no);
		mysql_query("UPDATE ebay_orders SET process_at =  '0000-00-00 00:00:00' WHERE id = '$order_no' AND dropship_done = 0");
		$response['error'] = mysql_error();
	}
	print_response();
}

else if(!empty($_POST['retryOrder'])){
	$orders = explode(',', $_POST['retryOrder']);
	foreach($orders as $order_no){
		if(empty($order_no))continue;
		$order_no = mysql_real_escape_string($order_no);
		mysql_query("UPDATE ebay_orders SET process_at =  NOW(), dropship_done = 0, dropship_locked = 0, dropship_status = 'ORDER_RETRY' WHERE id = '$order_no' AND dropship_done = 100");
		mysql_query("UPDATE ebay_orders SET process_at =  NOW(), dropship_done = 1, dropship_locked = 0, dropship_status = 'TRACKING_RETRY' WHERE id = '$order_no' AND dropship_done = 200");
		$response['error'] = mysql_error();
	}
	print_response();
}

else if(!empty($_POST['delOrder'])){
	$orders = explode(',', $_POST['delOrder']);
	foreach($orders as $order_no){
		if(empty($order_no))continue;
		$order_no = mysql_real_escape_string($order_no);
		mysql_query("UPDATE ebay_orders SET dropship_done = 500 WHERE id = '$order_no'");
		$response['error'] = mysql_error();
	}
	print_response();
}

else if(!empty($_POST['editSKU'])){
	$sku = mysql_real_escape_string($_POST['editSKU']);
	$order_no = mysql_real_escape_string($_POST['orderNo']);
	mysql_query("UPDATE ebay_orders SET item_sku = '$sku' WHERE id = '$order_no'");
	$response['error'] = mysql_error();
	print_response();
}

else if(!empty($_POST['markOrdered'])){
	$orders = explode(',', $_POST['markOrdered']);
	foreach($orders as $order_no){
		if(empty($order_no))continue;
		$order_no = mysql_real_escape_string($order_no);
		/*if(!mysql_num_rows(mysql_query("SELECT NULL FROM ebay_orders WHERE id = '$order_no' AND dropship_orderid != ''"))){
			$response['error'] = 'ERROR: Add a vendor orderid first';
			print_response();	
		}*/
		mysql_query("UPDATE ebay_orders SET dropship_done = 1, dropship_status = 'ITEM_ORDERED_M', process_at = '0000-00-00 00:00:00' WHERE id = '$order_no'");
		$response['error'] = mysql_error();
	}
	print_response();
}

else if(!empty($_POST['markShipped'])){
	$orders = explode(',', $_POST['markShipped']);
	foreach($orders as $order_no){
		if(empty($order_no))continue;
		$order_no = mysql_real_escape_string($order_no);
		/*if(!mysql_num_rows(mysql_query("SELECT NULL FROM ebay_orders WHERE id = '$order_no' AND tracking_number != '' AND tracking_carrier != ''"))){
			$response['error'] = 'ERROR: Add a tracking number first';
			print_response();	
		}*/
		mysql_query("UPDATE ebay_orders SET dropship_done = 2, dropship_status = 'ITEM_SHIPPED_M', process_at = '0000-00-00 00:00:00' WHERE id = '$order_no'");
		$response['error'] = mysql_error();
	}
	print_response();
}

else if(!empty($_POST['markCancelled'])){
	$orders = explode(',', $_POST['markCancelled']);
	foreach($orders as $order_no){
		if(empty($order_no))continue;
		$order_no = mysql_real_escape_string($order_no);
		/*if(!mysql_num_rows(mysql_query("SELECT NULL FROM ebay_orders WHERE id = '$order_no' AND tracking_number != '' AND tracking_carrier != ''"))){
			$response['error'] = 'ERROR: Add a tracking number first';
			print_response();	
		}*/
		mysql_query("UPDATE ebay_orders SET dropship_done = 100, dropship_status = 'CANCELLED', process_at = '0000-00-00 00:00:00' WHERE id = '$order_no'");
		$response['error'] = mysql_error();
	}
	print_response();
}

else if(!empty($_POST['ignoreLoss'])){
	$orders = explode(',', $_POST['ignoreLoss']);
	foreach($orders as $order_no){
		if(empty($order_no))continue;
		$order_no = mysql_real_escape_string($order_no);
		mysql_query("UPDATE ebay_orders SET ignore_loss = 1 WHERE id = '$order_no'");
		$response['error'] = mysql_error();
		mysql_query("UPDATE ebay_orders SET process_at =  NOW(), dropship_done = 0, dropship_locked = 0, dropship_status = 'ORDER_RETRY' WHERE id = '$order_no' AND dropship_done = 100");
		mysql_query("UPDATE ebay_orders SET process_at =  NOW(), dropship_done = 1, dropship_locked = 0, dropship_status = 'TRACKING_RETRY' WHERE id = '$order_no' AND dropship_done = 200");
	}
	print_response();
}

else if(!empty($_POST['ignoreTax'])){
	$orders = explode(',', $_POST['ignoreTax']);
	foreach($orders as $order_no){
		if(empty($order_no))continue;
		$order_no = mysql_real_escape_string($order_no);
		mysql_query("UPDATE ebay_orders SET ignore_tax = 1 WHERE id = '$order_no'");
		$response['error'] = mysql_error();
		mysql_query("UPDATE ebay_orders SET process_at =  NOW(), dropship_done = 0, dropship_locked = 0, dropship_status = 'ORDER_RETRY' WHERE id = '$order_no' AND dropship_done = 100");
		mysql_query("UPDATE ebay_orders SET process_at =  NOW(), dropship_done = 1, dropship_locked = 0, dropship_status = 'TRACKING_RETRY' WHERE id = '$order_no' AND dropship_done = 200");
	}
	print_response();
}


else if(!empty($_POST['ignorePrice'])){
	$orders = explode(',', $_POST['ignorePrice']);
	foreach($orders as $order_no){
		if(empty($order_no))continue;
		$order_no = mysql_real_escape_string($order_no);
		mysql_query("UPDATE ebay_orders SET ignore_price = 1 WHERE id = '$order_no'");
		$response['error'] = mysql_error();
		mysql_query("UPDATE ebay_orders SET process_at =  NOW(), dropship_done = 0, dropship_locked = 0, dropship_status = 'ORDER_RETRY' WHERE id = '$order_no' AND dropship_done = 100");
		mysql_query("UPDATE ebay_orders SET process_at =  NOW(), dropship_done = 1, dropship_locked = 0, dropship_status = 'TRACKING_RETRY' WHERE id = '$order_no' AND dropship_done = 200");
	}
	print_response();
}


else if(!empty($_POST['UndoIgnoreLoss'])){
	$orders = explode(',', $_POST['UndoIgnoreLoss']);
	foreach($orders as $order_no){
		if(empty($order_no))continue;
		$order_no = mysql_real_escape_string($order_no);
		mysql_query("UPDATE ebay_orders SET ignore_loss = 0 WHERE id = '$order_no'");
		$response['error'] = mysql_error();
	}
	print_response();
}

else if(!empty($_POST['UndoIgnoreTax'])){
	$orders = explode(',', $_POST['UndoIgnoreTax']);
	foreach($orders as $order_no){
		if(empty($order_no))continue;
		$order_no = mysql_real_escape_string($order_no);
		mysql_query("UPDATE ebay_orders SET ignore_tax = 0 WHERE id = '$order_no'");
		$response['error'] = mysql_error();
	}
	print_response();
}

else if(!empty($_POST['UndoIgnorePrice'])){
	$orders = explode(',', $_POST['UndoIgnorePrice']);
	foreach($orders as $order_no){
		if(empty($order_no))continue;
		$order_no = mysql_real_escape_string($order_no);
		mysql_query("UPDATE ebay_orders SET ignore_price = 0 WHERE id = '$order_no'");
		$response['error'] = mysql_error();
	}
	print_response();
}

else if(!empty($_POST['editVendor'])){
	$vendor = mysql_real_escape_string($_POST['editVendor']);
	$order_no = mysql_real_escape_string($_POST['orderNo']);
	mysql_query("UPDATE ebay_orders SET vendor = '$vendor' WHERE id = '$order_no'");
	$response['error'] = mysql_error();
	print_response();
}

else if(!empty($_POST['editVendorOrderID'])){
	$editVendorOrderID = mysql_real_escape_string($_POST['editVendorOrderID']);
	$order_no = mysql_real_escape_string($_POST['orderNo']);
	list($status) = mysql_fetch_row(mysql_query("SELECT dropship_done FROM ebay_orders WHERE id = '$order_no'"));
	if(empty($status))$status = 1;
	mysql_query("UPDATE ebay_orders SET dropship_orderid = '$editVendorOrderID', dropship_done = '$status', process_at = '0000-00-00 00:00:00' WHERE id = '$order_no'");
	$response['error'] = mysql_error();
	print_response();
}

else if(!empty($_POST['editTrackingID'])){
	$tid = mysql_real_escape_string($_POST['editTrackingID']);
	$tc = mysql_real_escape_string($_POST['editTrackingC']);
	$tcu = mysql_real_escape_string($_POST['editTrackingCU']);
	if(mysql_num_rows(mysql_query("SELECT NULL FROM ebay_orders WHERE tracking_number = '$tid'"))){
		$response['error'] = 'Tracking number already used';
		print_response();	
	}
	if(empty($tc)){
		$response['error'] = 'Empty carrier';
		print_response();	
	}
	$order_no = mysql_real_escape_string($_POST['orderNo']);
	list($status) = mysql_fetch_row(mysql_query("SELECT dropship_done FROM ebay_orders WHERE id = '$order_no'"));
	if(empty($status))$status = 1;
	mysql_query("UPDATE ebay_orders SET tracking_number = '$tid', tracking_url = '$tcu', tracking_carrier = '$tc', dropship_done = '$status', process_at = '0000-00-00 00:00:00' WHERE id = '$order_no'");
	$response['error'] = mysql_error();
	print_response();
}

else if(!empty($_POST['editPaidPrice'])){
	$price = mysql_real_escape_string($_POST['editPaidPrice']);
	$order_no = mysql_real_escape_string($_POST['orderNo']);
	mysql_query("UPDATE ebay_orders SET paid_amount = '$price' WHERE id = '$order_no'");
	$response['error'] = mysql_error();
	print_response();
}

else if(!empty($_POST['editPurchasePrice'])){
	$price = mysql_real_escape_string($_POST['editPurchasePrice']);
	$order_no = mysql_real_escape_string($_POST['orderNo']);
	mysql_query("UPDATE ebay_orders SET purchase_price = '$price' WHERE id = '$order_no'");
	$response['error'] = mysql_error();
	print_response();
}

else if(!empty($_POST['editExpensePrice'])){
	$price = mysql_real_escape_string($_POST['editExpensePrice']);
	$order_no = mysql_real_escape_string($_POST['orderNo']);
	mysql_query("UPDATE ebay_orders SET expense_fee = '$price' WHERE id = '$order_no'");
	$response['error'] = mysql_error();
	print_response();
}

else if(!empty($_POST['editProfitPrice'])){
	$price = mysql_real_escape_string($_POST['editProfitPrice']);
	$order_no = mysql_real_escape_string($_POST['orderNo']);
	mysql_query("UPDATE ebay_orders SET amount_profit = '$price' WHERE id = '$order_no'");
	$response['error'] = mysql_error();
	list($added_at) = mysql_fetch_row(mysql_query("SELECT added_at FROM ebay_orders WHERE id = '$order_no'"));
	if(!empty($added_at)){
		list($today) = explode(' ', $added_at);
		update_profit_sales($today);	
	}
	print_response();
}

else if(!empty($_POST['editQty'])){
	$qty = mysql_real_escape_string($_POST['editQty']);
	$order_no = mysql_real_escape_string($_POST['orderNo']);
	mysql_query("UPDATE ebay_orders SET item_quantity = '$qty' WHERE id = '$order_no'");
	$response['error'] = mysql_error();
	print_response();
}

else if(!empty($_POST['editAddr'])){
	$addr = mysql_real_escape_string($_POST['editAddr']);
	$order_no = mysql_real_escape_string($_POST['orderNo']);
	mysql_query("UPDATE ebay_orders SET street1 = '$addr' WHERE id = '$order_no'");
	$response['error'] = mysql_error();
	print_response();
}

else if(isset($_POST['editAddr2'])){
	$addr = mysql_real_escape_string($_POST['editAddr2']);
	$order_no = mysql_real_escape_string($_POST['orderNo']);
	mysql_query("UPDATE ebay_orders SET street2 = '$addr' WHERE id = '$order_no'");
	$response['error'] = mysql_error();
	print_response();
}

else if(!empty($_POST['editNote'])){
	$note = mysql_real_escape_string($_POST['editNote']);
	$order_no = mysql_real_escape_string($_POST['orderNo']);
	mysql_query("UPDATE ebay_orders SET custom_note = '$note' WHERE id = '$order_no'");
	$response['error'] = mysql_error();
	print_response();
}

else if(!empty($_POST['editName'])){
	$name = mysql_real_escape_string($_POST['editName']);
	$order_no = mysql_real_escape_string($_POST['orderNo']);
	mysql_query("UPDATE ebay_orders SET name = '$name' WHERE id = '$order_no'");
	$response['error'] = mysql_error();
	print_response();
}

else if(!empty($_POST['editCity'])){
	$city = mysql_real_escape_string($_POST['editCity']);
	$order_no = mysql_real_escape_string($_POST['orderNo']);
	mysql_query("UPDATE ebay_orders SET city = '$city' WHERE id = '$order_no'");
	$response['error'] = mysql_error();
	print_response();
}

else if(!empty($_POST['editState'])){
	$state = mysql_real_escape_string($_POST['editState']);
	$order_no = mysql_real_escape_string($_POST['orderNo']);
	mysql_query("UPDATE ebay_orders SET state_province = '$state' WHERE id = '$order_no'");
	$response['error'] = mysql_error();
	print_response();
}

else if(!empty($_POST['editPS'])){
	$ps = mysql_real_escape_string($_POST['editPS']);
	$order_no = mysql_real_escape_string($_POST['orderNo']);
	mysql_query("UPDATE ebay_orders SET postal_code = '$ps' WHERE id = '$order_no'");
	$response['error'] = mysql_error();
	print_response();
}

else if(!empty($_POST['editPhone'])){
	$phone = mysql_real_escape_string($_POST['editPhone']);
	$order_no = mysql_real_escape_string($_POST['orderNo']);
	mysql_query("UPDATE ebay_orders SET phone = '$phone' WHERE id = '$order_no'");
	$response['error'] = mysql_error();
	print_response();
}

else if(!empty($_POST['addOrder'])){
	$orderId = mysql_real_escape_string($_POST['OrderID']);
	$Vendor = mysql_real_escape_string($_POST['Vendor']);
	$VendorOrderID = mysql_real_escape_string($_POST['VendorOrderID']);
	$Sales = mysql_real_escape_string($_POST['Sales']);
	$SKU = mysql_real_escape_string($_POST['SKU']);
	$Qty = mysql_real_escape_string($_POST['Qty']);
	$Name = mysql_real_escape_string($_POST['Name']);
	$Street = mysql_real_escape_string($_POST['Street']);
	$City = mysql_real_escape_string($_POST['City']);
	$State = mysql_real_escape_string($_POST['State']);
	$Zip = mysql_real_escape_string($_POST['Zip']);
	$Phone = mysql_real_escape_string($_POST['Phone']);
	
	if(empty($SKU) || empty($Vendor) || empty($Qty)){
		$response['error'] = 'Sku, vendor name and qty required';
		print_response();	
	}
	
	mysql_query("INSERT INTO ebay_orders (sales_channel, order_id, vendor, added_at, item_sku, item_quantity, name, street1, city, state_province, postal_code, phone, dropship_done, dropship_status, country, country_name, dropship_orderid, paid_amount) VALUES('Manual','$orderId' , '$Vendor', NOW(), '$SKU', '$Qty', '$Name', '$Street', '$City', '$State', '$Zip', '$Phone', 3, 'MANUAL', 'US', 'United States', '$VendorOrderID', '$Sales')");
	$id = mysql_insert_id();
	if(!empty($id)){
		list($title, $cost, $qq) = mysql_fetch_row(mysql_query("SELECT item_title, purchase_price, item_quantity FROM ebay_orders WHERE item_sku = '$SKU' AND vendor = '$Vendor' ORDER BY purchase_price DESC"));	
		mysql_query("UPDATE ebay_orders SET item_title = '$title' WHERE id = '$id'");
		if(!empty($cost)){
			$cost = (float)($cost/$qq);
			$cost = $cost*$Qty;
			$profit = $Sales - $cost;
			mysql_query("UPDATE ebay_orders SET purchase_price = '$cost', amount_profit = '$profit' WHERE id = '$id'");	
		}
	}
	
	$response['error'] = mysql_error();
	print_response();
}

else if(!empty($_POST['ebayAcc']) && !empty($_POST['orderIds'])){
	$debug_off = true;
	$accId = mysql_real_escape_string($_POST['ebayAcc']);
	$orders = array();
	foreach(preg_split("/((\r?\n)|(\r\n?))/", $_POST['orderIds']) as $line){
		$orders[] = trim($line);
	}	
	
	$res = mysql_fetch_assoc(mysql_query("SELECT * FROM ebay_tokens WHERE id = '$accId'"));
	$api = new EbayApiMaster();
	$api->user_id = $res['user_id'];
	$api->token = $res['token'];
	
	if(!empty($orders)){
		$api->getEbayOrdersById($orders);
		$odata = $api->listing;
		if(!empty($odata)){
			$api->processOrders($api->listing, $res['user_id']);
		
			$today = date('Y-m-d');
			update_profit_sales($today);
			
			$today = date('Y-m-d', time() - 3600*24);
			update_profit_sales($today);
			
			$today = date('Y-m-d', time() - 3600*24*2);
			update_profit_sales($today);			
		}
		else{
			$response['error'] = 'No order found';
			print_response();	
		}
	}
	else{
		$response['error'] = 'No order id received';
		print_response();	
	}
	
	$response['error'] = '';
	print_response();
}

else if(!empty($_POST['amazonAcc']) && !empty($_POST['orderIds'])){
	$debug_off = true;
	$accId = mysql_real_escape_string($_POST['amazonAcc']);
	$orders = array();
	foreach(preg_split("/((\r?\n)|(\r\n?))/", $_POST['orderIds']) as $line){
		$orders[] = trim($line);
	}	
	$res = mysql_fetch_assoc(mysql_query("SELECT * FROM amazon_tokens WHERE id = '$accId'"));
	
	$amazon = new AWSApp();
	$amazon->AMAZON_AWS_Access_Key_ID = $res['access_id'];
	$amazon->AMAZON_Marketplace_ID = $res['marketplace_id'];
	$amazon->AMAZON_Merchant_ID = $res['merchant_id'];
	$amazon->AMAZON_Secret_Key = $res['secret_key'];
	$amazon->username = $res['user_id'];
	$amazon->password = $res['password'];
	if(!$amazon->login()){
		$response['error'] = 'Amazon login failed';
		print_response();	
	}
	
	if(!empty($orders)){
		$amazon->Amazon('GetOrder', $orders);
		$odata = array();
		foreach($amazon->data->GetOrderResult->Orders->Order as $order)$odata[] = $order;
		if(!empty($odata)){
			$amazon->process_orders($odata, $res['user_id']);	
			/*$amazon->process_commission();
			
			$today = date('Y-m-d');
			update_profit_sales($today);
			
			$today = date('Y-m-d', time() - 3600*24);
			update_profit_sales($today);
			
			$today = date('Y-m-d', time() - 3600*24*2);
			update_profit_sales($today);*/	
		}
		else{
			$response['error'] = 'No order found';
			print_response();	
		}
	}
	else{
		$response['error'] = 'No order id received';
		print_response();	
	}
	
	$response['error'] = '';
	print_response();
		
}

else if(!empty($_POST['TrackingRetry'])){
	$orders = explode(',', $_POST['TrackingRetry']);
	foreach($orders as $order_no){
		if(empty($order_no))continue;
		$order_no = mysql_real_escape_string($order_no);
		mysql_query("UPDATE ebay_orders SET dropship_done = 1, dropship_status = 'TRACKING_RETRY' WHERE id = '$order_no'");
		$response['error'] = mysql_error();
	}
	print_response();
}

?>