<?php
/**
 * This class is used to fetch orders from Ebay.
 *
 * @package EbaySalesAutomation
 * @subpackage EbaySales
 * @version 1.0
 * @author N/A
 * @copyright 2014
 * @link http://developer.ebay.com/common/api/
 */

/**
 * This the Main Ebay class
 * Work flow :
 * Initialize it using a token and userid
 * You can set them to null if no userid or token required
 * call eBay() with your opcode
 */
class EbayApp
{
	/**
 	 * class variables
 	 */
	public $error;
	public $url;
	public $curl_info;
	public $doPost;
	public $postData;
	public $response;
	public $token;
	public $user_id;
	public $data;
	public $SessionID;
	public $fromDate;
	public $toDate;
	public $resultsPerPage;
	public $pageNo;
	public $order_id;
	public $item_id;
	
	/**
	 * Class constructor
	 *
	 * @param $token string the ebay token
	 * @param $user_id string the user id of the current user
	 * @return n/a
	 */
	function __construct($token = '', $user_id = '')
	{
		if(EBAY_SANDBOX)$this->url = "https://api.sandbox.ebay.com/ws/api.dll";
		else $this->url = "https://api.ebay.com/ws/api.dll";
		$this->token = trim($token);
		$this->user_id = trim($user_id);
		$this->resultsPerPage = 200;
		$this->pageNo = 1;
		$yesterday = date("Y-m-d", time() - 3600*24);
		$this->fromDate = $yesterday.date("\T00:00:00.000\Z");
		$this->toDate = date("Y-m-d\T23:59:59.000\Z");
	}
	
	/**
	 * Function to get SOAP from opcode
	 *
	 * @param $opcode string the operation code of ebay
	 * @param $params array additional params
	 * @return n/a
	 * saves the response to $this->data
	 */
	function eBay($opcode, $params = array())
	{
		$this->postData = $this->getSOAP($opcode, $params);
		$this->doPost = 1;
		if($opcode == "ReviseItemStock" || $opcode == "ReviseItemOSC")$opcode = "ReviseItem";
		else if($opcode == 'GetOrdersById')$opcode = 'GetOrders';
		$this->get_source($opcode);
		$this->parse_xml($this->response);
	}

	/**
	 * Function to get SOAP from opcode
	 *
	 * @param $opcode string the operation code of ebay
	 * @param $params array containing the optional parameters for the operation i.e. tracking number, seller note
	 * @return $xml string soap string
	 * saves the response to $this->data
	 * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/GetSessionID.html
	 * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/ConfirmIdentity.html
	 * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/FetchToken.html
	 * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/GeteBayOfficialTime.html
	 * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/GetSellerList.html
	 * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/GetSellerTransactions.html
	 * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/GetOrders.html
	 * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/CompleteSale.html	 
	 * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/ReviseSellingManagerSaleRecord.html
	 * @link http://developer.ebay.com/devzone/xml/docs/reference/ebay/GetSellingManagerSaleRecord.html
	 * @link http://developer.ebay.com/devzone/xml/docs/reference/ebay/SetUserNotes.html
	 * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/GetMyeBaySelling.html
	 * @link http://developer.ebay.com/devzone/xml/docs/reference/ebay/ReviseItem.html
	 */
	 
	/**
		@timerange request - 
		<ModTimeFrom>'.$this->fromDate.'</ModTimeFrom>
		<ModTimeTo>'.$this->toDate.'</ModTimeTo>
	 */
	function getSOAP($opcode, $params = array())
	{
		$xml = '';
		
		switch($opcode):
			/**
			  * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/GetSessionID.html
			 */
			case "GetSessionID":
				$xml = '<?xml version="1.0" encoding="utf-8"?>
					      <GetSessionIDRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  						  <RuName>'.EBAY_RU_NAME.'</RuName>
					   </GetSessionIDRequest>';
			break;
			
			/**
			  * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/ConfirmIdentity.html
			 */
			case "ConfirmIdentity":
				$xml = '<?xml version="1.0" encoding="utf-8"?>
						  <ConfirmIdentityRequest xmlns="urn:ebay:apis:eBLBaseComponents">
  						  <SessionID>'.$this->SessionID.'</SessionID>
						</ConfirmIdentityRequest>';
			break;
			
			/**
			  * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/FetchToken.html
			 */
			case "FetchToken":
				$xml = '<?xml version="1.0" encoding="utf-8"?>
						<FetchTokenRequest xmlns="urn:ebay:apis:eBLBaseComponents">
						  <Version>799</Version>
						   <RequesterCredentials>
							 <DevId>'.EBAY_DEV_TOKEN.'</DevId>
							 <AppId>'.EBAY_APP_ID.'</AppId>
							 <AuthCert>'.EBAY_CERT_ID.'</AuthCert>
						   </RequesterCredentials>
						   <SessionID>'.$this->SessionID.'</SessionID>
						</FetchTokenRequest>';
			break;
			
			/**
			  * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/GeteBayOfficialTime.html
			 */
			case "GeteBayOfficialTime":
				$xml = '<?xml version="1.0" encoding="utf-8"?>
						<GeteBayOfficialTimeRequest xmlns="urn:ebay:apis:eBLBaseComponents">
						  <RequesterCredentials>
							<eBayAuthToken>'.$this->token.'</eBayAuthToken>
						  </RequesterCredentials>
						</GeteBayOfficialTimeRequest>';
			break;
			
			/**
			  * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/GetSellerList.html
			 */
			case "GetSellerList":
				$xml = '<?xml version="1.0" encoding="utf-8"?>
						<GetSellerListRequest xmlns="urn:ebay:apis:eBLBaseComponents">
						  <RequesterCredentials>
							<eBayAuthToken>'.$this->token.'</eBayAuthToken>
						  </RequesterCredentials>
						  <ErrorLanguage>en_US</ErrorLanguage>
						  <UserID>'.$this->user_id.'</UserID>
						  <WarningLevel>High</WarningLevel>
							<GranularityLevel>Fine</GranularityLevel> 
							<OutputSelector>ItemArray.Item.SKU</OutputSelector>
							<OutputSelector>ItemArray.Item.Title</OutputSelector>
							<OutputSelector>ItemArray.Item.ItemID</OutputSelector>
							<OutputSelector>ItemArray.Item.Quantity</OutputSelector>
							<OutputSelector>ItemArray.Item.SellingStatus.QuantitySold</OutputSelector>
							<OutputSelector>ItemArray.Item.SellingStatus.CurrentPrice</OutputSelector>
							<OutputSelector>ItemArray.Item.SellingStatus.ListingStatus</OutputSelector>
							<OutputSelector>HasMoreItems</OutputSelector>
							<OutputSelector>PaginationResult.TotalNumberOfPages</OutputSelector>
							<OutputSelector>PaginationResult.TotalNumberOfEntries</OutputSelector>
							<StartTimeFrom>'.$this->fromDate.'</StartTimeFrom> 
							<StartTimeTo>'.$this->toDate.'</StartTimeTo>  
							<Pagination> 
								<EntriesPerPage>'.$this->resultsPerPage.'</EntriesPerPage> 
								<PageNumber>'.$this->pageNo.'</PageNumber>
							</Pagination> 
						</GetSellerListRequest>';
			break;
			
			/**
			  * @link http://developer.ebay.com/devzone/xml/docs/reference/ebay/ReviseItem.html
			  * @used to revise item stock 
			 */
			case "ReviseItemStock":
				$xml = '<?xml version="1.0" encoding="utf-8"?>
						<ReviseItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
						  <RequesterCredentials>
							<eBayAuthToken>'.$this->token.'</eBayAuthToken>
						  </RequesterCredentials>';
						  
				foreach($params['items'] as $item => $stock)
					$xml .=
						' <Item>
							<ItemID>'.$item.'</ItemID>
							<Quantity>'.$stock.'</Quantity>
							<OutOfStockControl>true</OutOfStockControl>
						  </Item>';
						  
				$xml .= '</ReviseItemRequest>';
			
			
			break;
			
			/**
			  * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/GetSellingManagerInventory.html
			 */
			
			case "GetSellingManagerInventory":
				$xml = '<?xml version="1.0" encoding="utf-8"?>
						<GetSellingManagerInventoryRequest xmlns="urn:ebay:apis:eBLBaseComponents">
						  <RequesterCredentials>
							<eBayAuthToken>'.$this->token.'</eBayAuthToken>
						  </RequesterCredentials>
						  <Pagination>
						  	<EntriesPerPage>200</EntriesPerPage>
							<EntriesPerPage>'.$this->resultsPerPage.'</EntriesPerPage> 
							<PageNumber>'.$this->pageNo.'</PageNumber>
						  </Pagination>
						</GetSellingManagerInventoryRequest>';
			break;
			
			/**
			  * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/ReviseInventoryStatus.html
			 */
			
			case "ReviseInventoryStatus":
				$xml = '<?xml version="1.0" encoding="utf-8"?>
						<ReviseInventoryStatusRequest xmlns="urn:ebay:apis:eBLBaseComponents">
						  <RequesterCredentials>
							<eBayAuthToken>'.$this->token.'</eBayAuthToken>
						  </RequesterCredentials>';
				foreach($params['items'] as $item => $stock)
					$xml .= '<InventoryStatus>
								<ItemID>'.$item.'</ItemID>
								<Quantity>'.$stock.'</Quantity>
							  </InventoryStatus>';
				$xml .= '</ReviseInventoryStatusRequest>';
			break;
			
			/**
			  * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/GetSellerTransactions.html
			 */
			case "GetSellerTransactions":
				$xml = '<?xml version="1.0" encoding="utf-8"?>
						<GetSellerTransactionsRequest xmlns="urn:ebay:apis:eBLBaseComponents">
						  <RequesterCredentials>
							<eBayAuthToken>'.$this->token.'</eBayAuthToken>
						  </RequesterCredentials>
						  <IncludeFinalValueFee>true</IncludeFinalValueFee>
						  <DetailLevel>ReturnAll</DetailLevel>
						  <NumberOfDays>1</NumberOfDays>
						  <Pagination>
							<EntriesPerPage>'.$this->resultsPerPage.'</EntriesPerPage>
							<PageNumber>'.$this->pageNo.'</PageNumber>
						  </Pagination>
						</GetSellerTransactionsRequest>';
			break;
			
			/**
			  * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/GetOrders.html
			 */
			case "GetOrders":
				$xml = '<?xml version="1.0" encoding="utf-8"?>
						<GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">
						  <RequesterCredentials>
							<eBayAuthToken>'.$this->token.'</eBayAuthToken>
						  </RequesterCredentials>
						  <IncludeFinalValueFee>true</IncludeFinalValueFee>
						  <DetailLevel>ReturnAll</DetailLevel>
						  <NumberOfDays>1</NumberOfDays>
						  <OrderRole>Seller</OrderRole>
						  <OrderStatus>Completed</OrderStatus>
						  <Pagination>
							<EntriesPerPage>'.$this->resultsPerPage.'</EntriesPerPage>
							<PageNumber>'.$this->pageNo.'</PageNumber>
						  </Pagination>
						</GetOrdersRequest>';
						
			break;
			
			/**
			  * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/GetOrders.html
			 */
			case "GetOrdersById":
				$orders = '';
				foreach($params as $pp)$orders .= '<OrderID>'.$pp.'</OrderID>';
				$xml = '<?xml version="1.0" encoding="utf-8"?>
						<GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">
						  <RequesterCredentials>
							<eBayAuthToken>'.$this->token.'</eBayAuthToken>
						  </RequesterCredentials>
						  <IncludeFinalValueFee>true</IncludeFinalValueFee>
						  <DetailLevel>ReturnAll</DetailLevel>
						  <OrderIDArray>
						  	'.$orders.'
						  </OrderIDArray>
						</GetOrdersRequest>';
						
			break;	
			
			/**
			  * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/CompleteSale.html
			 */		
			case "CompleteSale":
				$xml = '<?xml version="1.0" encoding="utf-8"?>
						<CompleteSaleRequest xmlns="urn:ebay:apis:eBLBaseComponents">
						 <RequesterCredentials>
							<eBayAuthToken>'.$this->token.'</eBayAuthToken>
						  </RequesterCredentials>
						  <ItemID>'.$this->item_id.'</ItemID>
						  <OrderID>'.$this->order_id.'</OrderID>
						  <OrderLineItemID>'.$this->order_id.'</OrderLineItemID>
						   <Shipment>
							<ShipmentTrackingDetails>
							  <ShipmentTrackingNumber>'.$params['tracking_number'].'</ShipmentTrackingNumber>
							  <ShippingCarrierUsed>'.$params['tracking_carrier'].'</ShippingCarrierUsed>
							</ShipmentTrackingDetails>
						  </Shipment>
						  <Shipped>true</Shipped> 
						</CompleteSaleRequest>';
			break; 
			
			/**
			  * @link http://developer.ebay.com/DevZone/XML/docs/reference/ebay/ReviseSellingManagerSaleRecord.html
			 */	
			case "ReviseSellingManagerSaleRecord":
				$xml = '<?xml version="1.0" encoding="utf-8"?>
						<ReviseSellingManagerSaleRecordRequest xmlns="urn:ebay:apis:eBLBaseComponents">
						  <RequesterCredentials>
							<eBayAuthToken>'.$this->token.'</eBayAuthToken>
						  </RequesterCredentials>
						  <ItemID>'.$this->item_id.'</ItemID>
						  <OrderID>'.$this->order_id.'</OrderID>
						  <OrderLineItemID>'.$this->order_id.'</OrderLineItemID>
						  <SellingManagerSoldOrder>
							'.(!empty($params['seller_note']) ? '<NotesToSeller>'.$params['seller_note'].'</NotesToSeller>' : '').'
							'.(!empty($params['buyer_note']) ? '<NotesToBuyer>'.$params['buyer_note'].'</NotesToBuyer>' : '').'
						  </SellingManagerSoldOrder>
						</ReviseSellingManagerSaleRecordRequest>';
			break;
			
			/**
			  * @link http://developer.ebay.com/devzone/xml/docs/reference/ebay/GetSellingManagerSaleRecord.html
			 */
			case "GetSellingManagerSaleRecord":
				$xml = '<?xml version="1.0" encoding="utf-8"?>
						<GetSellingManagerSaleRecordRequest xmlns="urn:ebay:apis:eBLBaseComponents">
						  <RequesterCredentials>
							<eBayAuthToken>'.$this->token.'</eBayAuthToken>
						  </RequesterCredentials>
						 <DetailLevel>ReturnAll</DetailLevel>
						 <ItemID>'.$this->item_id.'</ItemID>
						 <OrderID>'.$this->order_id.'</OrderID>
						 <OrderLineItemID>'.$this->order_id.'</OrderLineItemID>
						</GetSellingManagerSaleRecordRequest>';
			break;
			
			/**
			  * @link http://developer.ebay.com/devzone/xml/docs/reference/ebay/SetUserNotes.html
			 */
			case "SetUserNotes":
				$xml = '<?xml version="1.0" encoding="utf-8"?>
						<SetUserNotesRequest xmlns="urn:ebay:apis:eBLBaseComponents">
						 <RequesterCredentials>
							<eBayAuthToken>'.$this->token.'</eBayAuthToken>
						  </RequesterCredentials>
						  <Action>AddOrUpdate</Action>
						  <NoteText>'.$params['seller_note'].'</NoteText>
						  <ItemID>'.$this->item_id.'</ItemID>
						  <TransactionID>'.$this->order_id.'</TransactionID>
						</SetUserNotesRequest>';
			break;
			
			/**
			  * @link http://developer.ebay.com/devzone/xml/docs/reference/ebay/GetMyeBaySelling.html
			 */
			case "GetMyeBaySelling":
				$xml = '<?xml version="1.0" encoding="utf-8"?>
						<GetMyeBaySellingRequest xmlns="urn:ebay:apis:eBLBaseComponents">
						  <RequesterCredentials>
							<eBayAuthToken>'.$this->token.'</eBayAuthToken>
						  </RequesterCredentials>
						   <SoldList>
						    <DurationInDays>7</DurationInDays>
							<IncludeNotes>true</IncludeNotes>
							<OrderStatusFilter>AwaitingShipment</OrderStatusFilter>
						  </SoldList>
						</GetMyeBaySellingRequest>';
			break;
			
			/**
			  * @link http://developer.ebay.com/devzone/xml/docs/reference/ebay/ReviseItem.html
			  * @used to revise OSC
			 */
			case "ReviseItemOSC":
				$xml = '<?xml version="1.0" encoding="utf-8"?>
						<ReviseItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
						  <RequesterCredentials>
							<eBayAuthToken>'.$this->token.'</eBayAuthToken>
						  </RequesterCredentials>
						  <Item>
						  	<ItemID>'.$this->item_id.'</ItemID>
							<OutOfStockControl>true</OutOfStockControl>
						  </Item>
						</ReviseItemRequest>';
			break;
			
		endswitch;
		
		return $xml;
	}
	
	/**
	 * Function to parse XML response from eBay
	 *
	 * @param $xml string the response from eBay
	 * @return n/a
	 * saves the parsed response to $this->data
	 */
	public function parse_xml($xml)
	{
		$this->data = @simplexml_load_string($xml);
	}
	
	/**
	 * Function to get curl response
	 *
	 * @param $opcode string the operation code of ebay
	 * @return n/a
	 * saves the response to $this->response
	 */
	public function get_source($opcode)
	{
		$ch=curl_init($this->url);
		curl_setopt($ch,CURLOPT_HEADER,0);	
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);	
		curl_setopt($ch,CURLOPT_BINARYTRANSFER,0);	
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);		
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
		if($this->doPost){
			$headers = array(             
        			"X-EBAY-API-APP-ID: ".EBAY_APP_ID,
					"X-EBAY-API-APP-NAME: ".EBAY_APP_ID,
					"X-EBAY-API-DEV-NAME: ".EBAY_DEV_TOKEN,
					"X-EBAY-API-CERT-NAME: ".EBAY_CERT_ID,
        			"X-EBAY-API-CALL-NAME: ".$opcode, 
        			"X-EBAY-API-VERSION: 863",
					"X-EBAY-API-SITEID: 0",
					"X-EBAY-API-COMPATIBILITY-LEVEL: 863" ,
    			); 
			curl_setopt($ch,CURLOPT_POST,1);
			curl_setopt($ch,CURLOPT_POSTFIELDS,$this->postData);
			curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
		}
		$data=curl_exec($ch);
		$this->response=$data;
		$info=curl_getinfo($ch);
		$this->curl_info=$info;
	}
	
	/**
	 * Function to save log to txt file
	 *
	 * @param $str string the string to write 
	 * @return n/a
	 * saves $str to log file
	 */
	public function log($str)
	{
		global $debug_off;
		$fp=fopen(dirname(dirname(__FILE__))."/logs/ebay-log.txt","a");
		fwrite($fp,date('[d-M-Y H:i:s]')." $str\r\n");
		fclose($fp);
	
		if(!empty($debug_off))return true;
		echo $str."<br/>";
		@flush();
		@ob_flush();
	}
		
}
?>