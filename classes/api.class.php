<?php
/**
 * This class is used to handle Ebay class.
 *
 * @package EbaySalesAutomation
 * @subpackage EbayHelper
 * @version 1.0
 * @author N/A
 * @copyright 2014
 * @link http://developer.ebay.com/common/api/
 */

/**
 * This the Main Ebay class
 * Work flow :
 * Initialize the class
 * set token and user_id
 * class respective functions
 * getEbayListing() to get ebay listings
 * getEbayTransactions() to get ebay transactions
 * getEbayOrders() to get ebay orders
 * setEbayTrackingNumber() to set tracking number
 * setEbaySalesNote() to set ebay sales note
 */
class EbayApiMaster
{
	/**
 	 * class variables
 	 */
	public $token;
	public $user_id;
	public $listing;
	public $data;
	
	/**
	 * Class constructor
	 *
	 * @param n/a
	 * @return n/a
	 */
	public function __construct()
	{
		//nothing to do	
	}
	
	/**
 	 * Function to get ebay listings of the user
	 *
	 * @param $date datetime
	 * @return n/a
	 * saves the data to $this->listing
 	 */
	public function getEbayListing($date = '', $range = 0)
	{
		$this->listing = array();
		$ebay = new EbayApp($this->token, $this->user_id);
		$ebay->resultsPerPage = 200;
		if($date){
			$ebay->fromDate = $date.date("\T00:00:00.000\Z");
			if(!$range)$ebay->toDate = $date.date("\T23:59:59.000\Z");
			else $ebay->toDate = date('Y-m-d', strtotime($date) + $range*60*60*24).date("\T23:59:59.000\Z");	
		}
		
		$HasMoreItems = true;
		while($HasMoreItems){
			$ebay->eBay('GetSellerList');
			//echo $ebay->response;
			if(!empty($ebay->data->ItemArray->Item)){
				foreach($ebay->data->ItemArray->Item as $item)$this->listing[] = $item;
			}
			if($ebay->pageNo++ >= 20)break;
			$HasMoreItems = @$ebay->data->HasMoreItems;
			if($HasMoreItems)sleep(2);
		}
	}
	
	/**
 	 * Function to get ebay inventory of the user
	 *
	 * @param $type string
	 * @return n/a
	 * saves the data to $this->listing
 	 */
	public function setEbayInventory($items)
	{
		$success = 0;
		$this->listing = array();
		$ebay = new EbayApp($this->token, $this->user_id);
		$data = array_chunk($items, 1, true);
		foreach($data as $d){
			$ebay->eBay('ReviseItemStock', array('items' => $d));
			if($ebay->data->Ack == 'Success' || $ebay->data->Ack == 'Warning')$success++;
		}
		return $success;
	}
	
	/**
 	 * Function to get ebay Transactions of the user
	 *
	 * @param $date datetime
	 * @return n/a
	 * saves the data to $this->listing
	 * date is not used here. Last 1 days items returned
 	 */
	public function getEbayTransactions($date = '')
	{
		$this->listing = array();
		$ebay = new EbayApp($this->token, $this->user_id);
		if($date){
			$ebay->fromDate = $date.date("\T00:00:00.000\Z");
			$ebay->toDate = $date.date("\T23:59:59.000\Z");	
		}
		
		$HasMoreTransactions = true;
		while($HasMoreTransactions){
			$ebay->eBay('GetSellerTransactions');
			if(!empty($ebay->data->TransactionArray->Transaction)){
				foreach($ebay->data->TransactionArray->Transaction as $order)$this->listing[] = $order;
			}
			if($ebay->pageNo++ >= 20)break;
			$HasMoreTransactions = $ebay->data->HasMoreTransactions == 'false' ? 0:1;
		}
	}
	
	/**
 	 * Function to get ebay Orders of the user
	 *
	 * @param $date datetime
	 * @return n/a
	 * saves the data to $this->listing
	 * date is not used here. Last 1 days items returned
 	 */
	public function getEbayOrders($date = '')
	{
		$this->listing = array();
		$ebay = new EbayApp($this->token, $this->user_id);
		if($date){
			$ebay->fromDate = $date.date("\T00:00:00.000\Z");
			$ebay->toDate = $date.date("\T23:59:59.000\Z");	
		}
		
		$HasMoreOrders = true;
		
		while($HasMoreOrders){
			$ebay->eBay('GetOrders');
			//var_dump($ebay->data);
			if(!empty($ebay->data->OrderArray->Order)){
				foreach($ebay->data->OrderArray->Order as $order)$this->listing[] = $order;
			}
			if($ebay->pageNo++ >= 20)break;
			$HasMoreOrders = $ebay->data->HasMoreOrders == 'false' ? 0:1;
		}
	}
	
	/**
 	 * Function to get ebay Orders by id
	 *
	 * @param $date datetime
	 * @return n/a
	 * saves the data to $this->listing
	 * date is not used here. Last 1 days items returned
 	 */
	public function getEbayOrdersById($orders)
	{
		$this->listing = array();
		$ebay = new EbayApp($this->token, $this->user_id);
		$ebay->eBay('GetOrdersById', $orders);
		//var_dump($ebay->data);
		if(!empty($ebay->data->OrderArray->Order)){
			foreach($ebay->data->OrderArray->Order as $order)$this->listing[] = $order;
		}
	}
	
	public function processOrders($listing, $user_id)
	{
		foreach($listing as $order):
		
			//echo '<pre>';
			//print_r($order);
			$buyer = mysql_real_escape_string($order->BuyerUserID);
			
			do_log('Processing order '.$order->OrderID);
			
			$ship = array();
			$ship_to = $order->ShippingAddress;
			$ship['buyer_user_id'] = $order->BuyerUserID;	
			$ship['name'] = $ship_to->Name;
			$ship['street1'] = trim($ship_to->Street1);
			$ship['street2'] = @trim($ship_to->Street2);
			if(empty($ship['street1']) && !empty($ship['street2'])){
				$ship['street1'] = $ship['street2'];
				$ship['street2'] = '';
			}
			$ship['city'] = $ship_to->CityName;
			$ship['state_province'] = $ship_to->StateOrProvince;
			$ship['country'] = $ship_to->Country;
			$ship['country_name'] = $ship_to->CountryName;
			$ship['postal_code'] = substr($ship_to->PostalCode, 0, 5);
			$ship['phone'] = preg_replace('/[^0-9\s]/', '', $ship_to->Phone);
			$ship['sales_tax'] = $order->ShippingDetails->SalesTax->SalesTaxAmount;
			
			$buyer = mysql_real_escape_string($order->Buyer->UserID);
			
			if(empty($order->TransactionArray->Transaction[0]))$order->TransactionArray->Transaction[0] = $order->TransactionArray->Transaction;
			
			$order_num = count($order->TransactionArray->Transaction);
			
			foreach($order->TransactionArray->Transaction as $transaction):
				$item = array();
				$orderID = mysql_real_escape_string($transaction->OrderLineItemID);
				
				/**
				 * used to save api calls
				 */
				$m_done = 0;
				$tmp = mysql_fetch_assoc(mysql_query("SELECT dropship_done FROM ebay_orders WHERE order_id = '$orderID'"));
				if(!empty($tmp['dropship_done']))$m_done = $tmp['dropship_done'];
				
				//fee calculation
				$fees = 0;
				if(!empty($settings['paypal_fee_flat']))$fees = (float)($settings['paypal_fee_flat']/$order_num);
				
				//ebay sales fee
				if(!empty($transaction->FinalValueFee))$fees += (float)$transaction->FinalValueFee;
				
				$item['item_id'] = $transaction->Item->ItemID;
				$item['item_title'] = $transaction->Item->Title;
				$item['item_condition'] = $transaction->Item->ConditionDisplayName;
				$item['item_sku'] = $transaction->Item->SKU;
				$item['item_quantity'] = $transaction->QuantityPurchased;
				
				$amountPaid = mysql_real_escape_string(round(((int)$transaction->QuantityPurchased)*((float)$transaction->TransactionPrice), 2));
				if(!empty($settings['paypal_fee_percent']))$fees += ((float)$amountPaid*(float)$settings['paypal_fee_percent'])/100;
				$fees = round($fees, 2);
				
				$ship['buyer_email'] = @$transaction->Buyer->Email;
				
				$salesTime = mysql_real_escape_string($order->CreatedTime);
				$modTime = mysql_real_escape_string($order->CheckoutStatus->LastModifiedTime);
				$paymentMethod = mysql_real_escape_string($order->CheckoutStatus->PaymentMethod);
				
				$status = 0;
				$dropship_status = 'QUEUED';
				$ebay_sales_notes = '';
				$tracking_number = '';
				$tracking_carrier = '';
				
				if(!empty($order->ShippedTime) && $m_done <= 2){
					$status = 3;
					$dropship_status = 'ITEM_SHIPPED_M';
					$tracking_carrier =	mysql_real_escape_string($transaction->ShippingDetails->ShipmentTrackingDetails->ShippingCarrierUsed);
					$tracking_number = mysql_real_escape_string($transaction->ShippingDetails->ShipmentTrackingDetails->ShipmentTrackingNumber);
				}
				
				if($status != 3 && $m_done <= 1){
					$record = $this->getEbaySalesNote($orderID, $item['item_id']);
					if(!empty($record->NotesToSeller)){
						$ebay_sales_notes = mysql_real_escape_string($record->NotesToSeller);
						$dropship_status = 'ITEM_ORDERED_M';
						$status = 2;
					}
				}
				
				$new = 0;
				$sql = mysql_query("SELECT id,mod_time,tracking_number,dropship_done FROM ebay_orders WHERE order_id = '$orderID'");
				if(!mysql_num_rows($sql)){
					$new = 1;
					mysql_query("INSERT INTO ebay_orders (sales_channel, order_id, seller_id, vendor, paid_amount, expense_fee ,sales_time, mod_time, payment_method, added_at, tracking_number, tracking_carrier) VALUES('Ebay', '$orderID', '".mysql_real_escape_string($user_id)."', 'SamsClub' ,'$amountPaid', '$fees', '$salesTime', '$modTime', '$paymentMethod', NOW(), '$tracking_number', '$tracking_carrier')");
					$id = mysql_insert_id();
				}
				else list($id, $mod_time, $tc, $m_done) = mysql_fetch_row($sql); 	
				
				if(!empty($id)){
					
					//if modification time is old
					/*if(!$new)if($modTime == $mod_time){
						do_log('Mod time is same for '.$orderID);
						continue;
					}*/
					
					mysql_query("UPDATE ebay_orders SET mod_time = '$modTime', ebay_sales_notes = '$ebay_sales_notes' WHERE id = '$id'");
					
					if($m_done != 500){
						if(!empty($m_done)){
							if($status)mysql_query("UPDATE ebay_orders SET dropship_done = '$status', dropship_status = '$dropship_status' WHERE id = '$id'");
							//else no update	
						}
						else mysql_query("UPDATE ebay_orders SET dropship_done = '$status', dropship_status = '$dropship_status' WHERE id = '$id'");
					}
					
					foreach($ship as $k => $v){
						$v = mysql_real_escape_string($v);
						mysql_query("UPDATE ebay_orders SET `$k` = '$v' WHERE id = '$id'");	
					}
					
					foreach($item as $k => $v){
						$v = mysql_real_escape_string($v);
						mysql_query("UPDATE ebay_orders SET `$k` = '$v' WHERE id = '$id'");
					}
					
					if(empty($tc) && !empty($tracking_number)){	
						mysql_query("UPDATE ebay_orders SET tracking_number = '$tracking_number', tracking_carrier = '$tracking_carrier' WHERE id = '$id'");
					}
					
					/**
					 * Set a time only if the listing is pending
					 */
					if(AUTO_ORDER){
						if(ORDER_SAFETY_200 && $amountPaid >= 200){
							do_log("Paid amount ".$amountPaid." is above safety limit to auto order");
						}
						else mysql_query("UPDATE ebay_orders SET process_at = NOW() WHERE id = '$id' AND dropship_done = 0");
					}
				}
				
				mysql_query("UPDATE ebay_orders SET dropship_done = 1, dropship_status = 'ITEM_ORDERED_M' 
								WHERE dropship_orderid != '' AND dropship_done = 0 AND tracking_number = ''");
								
				mysql_query("UPDATE ebay_orders SET dropship_done = 3, dropship_status = 'ITEM_SHIPPED_M' 
								WHERE dropship_done != 3 AND tracking_number != ''");
			endforeach;
		endforeach;
		
		$sales = $this->getMyEbaySales();
		if(!empty($sales))
		foreach($sales as $sale):
			$transid = $sale->Transaction->TransactionID;
			$orderid = $sale->Transaction->OrderLineItemID;
			if(!empty($sale->Transaction->Item->PrivateNotes)){
				mysql_query("UPDATE ebay_orders SET dropship_done = 2 AND dropship_status = 'ITEM_ORDERED_M' WHERE (order_id = '$transid' OR order_id = '$orderid') AND (dropship_done = 0 OR dropship_done = 1)");	
			}
		endforeach;	
	}
	
	/**
 	 * Function to set ebay tracking number for an order
	 *
	 * @param $order_id string ebay order id
	 * @param $item_id string ebay item id
	 * @param $tracking_number string tracking number of that item
	 * @param $tracking_carrier string tracking carrier name
	 * @return bool true on success and false on failure 
 	 */
	public function setEbayTrackingNumber($order_id, $item_id, $tracking_number, $tracking_carrier)
	{
		$ebay = new EbayApp($this->token, $this->user_id);
		if(!preg_match('/-/', $order_id))$order_id = $item_id.'-'.$order_id;
		$ebay->order_id = $order_id;
		$ebay->item_id = $item_id;
		$ebay->eBay('CompleteSale', array('tracking_number' => $tracking_number, 'tracking_carrier' => $tracking_carrier));
		$this->data = $ebay->data;
		if($ebay->data->Ack == 'Success' || $ebay->data->Ack == 'Warning')return true;
		return false;
	}
	
	/**
 	 * Function to set ebay sales note for an order
	 *
	 * @param $order_id string ebay order id
	 * @param $item_id string ebay item id
	 * @param $seller_note string note for the seller
	 * @param $buyer_note string note for the buyer
	 * @param $mini_note string mini note for the seller
	 * @return bool true on success and false on failure 
 	 */
	public function setEbaySalesNote($order_id, $item_id, $seller_note, $buyer_note, $user_note)
	{
		$ebay = new EbayApp($this->token, $this->user_id);
		if(!preg_match('/-/', $order_id)){
			$transid = $order_id;
		}
		else list(,$transid) = explode('-', $order_id);
		$ebay->order_id = $transid;
		$ebay->item_id = $item_id;
		if(!empty($user_note))$ebay->eBay('SetUserNotes', array('seller_note' => $user_note));
		$ebay->order_id = $order_id;
		$ebay->item_id = $item_id;	
		$ebay->eBay('ReviseSellingManagerSaleRecord', array('seller_note' => $seller_note, 'buyer_note' => $buyer_note));
		$this->data = $ebay->data;
		if($ebay->data->Ack == 'Success' || $ebay->data->Ack == 'Warning')return true;
		return false;
	}
	
	/**
 	 * Function to get ebay sales note for an order
	 *
	 * @param $order_id string ebay order id
	 * @param $item_id string ebay item id
	 * @return string note on success and false on failure 
 	 */
	public function getEbaySalesNote($order_id, $item_id)
	{
		$ebay = new EbayApp($this->token, $this->user_id);
		if(!preg_match('/-/', $order_id))$order_id = $item_id.'-'.$order_id;
		$ebay->order_id = $order_id;
		$ebay->item_id = $item_id;
		$ebay->eBay('GetSellingManagerSaleRecord');
		$this->data = $ebay->data;
		if(($ebay->data->Ack == 'Success' || $ebay->data->Ack == 'Warning') && !empty($ebay->data->SellingManagerSoldOrder)){
			return $ebay->data->SellingManagerSoldOrder;
		}
		return false;
	}
	
	/**
 	 * Function to get ebay sales with notes for an order
	 *
	 * @return array of orders on success and false on failure
 	 */
	public function getMyEbaySales()
	{
		$ebay = new EbayApp($this->token, $this->user_id);
		$ebay->eBay('GetMyeBaySelling');
		if(!empty($ebay->data->SoldList->OrderTransactionArray->OrderTransaction)){
			return $ebay->data->SoldList->OrderTransactionArray->OrderTransaction;
		}
		return false;
	}
	
	/**
 	 * Function to set outofstockcontrol to an item
	 *
	 * @param $item_id string ebay item id
	 * @return bool true on success and false on failure 
 	 */
	public function setOSC($item_id)
	{
		$ebay = new EbayApp($this->token, $this->user_id);
		$ebay->item_id = $item_id;
		$ebay->eBay('ReviseItemOSC');
		$this->data = $ebay->data;
		if($ebay->data->Ack == 'Success' || $ebay->data->Ack == 'Warning')return true;
		return false;
	}
	
}

?>