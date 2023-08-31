<?php

class AWSApp
{
	/**
	 curl related variables
	 */
	public $url;
	public $curl_info;
	public $doPost;
	public $postData;
	public $response;
	public $last_url;
	public $referer;
	public $userAgent;
	public $proxy;
	public $proxy_pwd;
	public $cookie;
	public $curl_multi;
	
	public $data;
	
	public $fromDate;
	public $toDate;
	
	public $username;
	public $password;
	public $AMAZON_AWS_Access_Key_ID;
	public $AMAZON_Marketplace_ID;
	public $AMAZON_Merchant_ID;
	public $AMAZON_Secret_Key;
	public $content_md5;
	
	public $errors;

	public function __construct()
	{
		$this->data = array();
		$this->cookie = dirname(dirname(__FILE__)).'/logs/amazon-cookie.txt';
		$this->userAgent = 'Mozilla/5.0 (Windows NT 6.1; rv:28.0) Gecko/20100101 Firefox/28.0';
	}
	
	public function Amazon($opcode, $params = array())
	{
		$url = 'https://mws.amazonservices.com/?';
		$expires_at = gmdate("Y-m-d\TH:i:s\Z", time() + 60*10);
		
		$this->curl_multi = 0;
		$this->doPost = 0;
		$this->postData = array();
		
		if($opcode == 'ListOrders'){
			$url = 'https://mws.amazonservices.com/Orders/2013-09-01?';
			$q = 'AWSAccessKeyId='.$this->AMAZON_AWS_Access_Key_ID.'&Action=ListOrders&MarketplaceId.Id.1='.$this->AMAZON_Marketplace_ID.'&OrderStatus.Status.1=Unshipped&OrderStatus.Status.2=PartiallyShipped&OrderStatus.Status.3=Shipped&SellerId='.$this->AMAZON_Merchant_ID.'&SignatureVersion=2&SignatureMethod=HmacSHA256&LastUpdatedAfter='.urlencode($params['from']).'&Version=2013-09-01';
			
		}
			
		else if($opcode == 'ListOrdersByNextToken'){
			$url = 'https://mws.amazonservices.com/Orders/2013-09-01?';
			$q = 'AWSAccessKeyId='.$this->AMAZON_AWS_Access_Key_ID.'&Action=ListOrdersByNextToken&SellerId='.$this->AMAZON_Merchant_ID.'&SignatureVersion=2&SignatureMethod=HmacSHA256&NextToken='.urlencode($params['token']).'&Version=2013-09-01';
			
		}
		
		else if($opcode == 'GetOrder'){
			$url = 'https://mws.amazonservices.com/Orders/2013-09-01?';
			$orders = '';
			foreach($params as $i => $id)$orders .= 'AmazonOrderId.Id.'.($i+1).'='.$id.'&';
			$orders = rtrim($orders, '&');
			$q = 'AWSAccessKeyId='.$this->AMAZON_AWS_Access_Key_ID.'&Action=GetOrder&'.$orders.'&SellerId='.$this->AMAZON_Merchant_ID.'&SignatureVersion=2&SignatureMethod=HmacSHA256&Version=2013-09-01';
			
		}
		
		else if($opcode == 'ListOrderItems'){
			$url = 'https://mws.amazonservices.com/Orders/2013-09-01?';
			$q = 'AWSAccessKeyId='.$this->AMAZON_AWS_Access_Key_ID.'&Action=ListOrderItems&SellerId='.$this->AMAZON_Merchant_ID.'&AmazonOrderId='.$params['order_id'].'&SignatureVersion=2&SignatureMethod=HmacSHA256&Version=2013-09-01';
			
		}
		
		else if($opcode == 'RequestReport'){
			$q = 'AWSAccessKeyId='.$this->AMAZON_AWS_Access_Key_ID.'&Action=RequestReport&StartDate='.urlencode($this->fromDate).'&EndDate='.urlencode($this->toDate).'&Marketplace='.$this->AMAZON_Marketplace_ID.'&SellerId='.$this->AMAZON_Merchant_ID.'&ReportType=_GET_CONVERGED_FLAT_FILE_ACTIONABLE_ORDER_DATA_&SignatureVersion=2&SignatureMethod=HmacSHA256&Timestamp='.urlencode($expires_at).'&Version=2009-01-01';
			
		}
		
		else if($opcode == 'RequestSettlementReport'){
			$q = 'AWSAccessKeyId='.$this->AMAZON_AWS_Access_Key_ID.'&Action=RequestReport&StartDate='.urlencode($this->fromDate).'&EndDate='.urlencode($this->toDate).'&Marketplace='.$this->AMAZON_Marketplace_ID.'&SellerId='.$this->AMAZON_Merchant_ID.'&ReportType=_GET_PAYMENT_SETTLEMENT_DATA_&SignatureVersion=2&SignatureMethod=HmacSHA256&Timestamp='.urlencode($expires_at).'&Version=2009-01-01';
			
		}
		
		else if($opcode == 'RequestListingsReport'){
			$q = 'AWSAccessKeyId='.$this->AMAZON_AWS_Access_Key_ID.'&Action=RequestReport&StartDate='.urlencode($this->fromDate).'&Marketplace='.$this->AMAZON_Marketplace_ID.'&SellerId='.$this->AMAZON_Merchant_ID.'&ReportType=_GET_MERCHANT_LISTINGS_DATA_&SignatureVersion=2&SignatureMethod=HmacSHA256&Timestamp='.urlencode($expires_at).'&Version=2009-01-01';
			
		}
		
		else if($opcode == 'RequestFeedBack'){
			$q = 'AWSAccessKeyId='.$this->AMAZON_AWS_Access_Key_ID.'&Action=RequestReport&StartDate='.urlencode($this->fromDate).'&Marketplace='.$this->AMAZON_Marketplace_ID.'&SellerId='.$this->AMAZON_Merchant_ID.'&ReportType=_GET_SELLER_FEEDBACK_DATA_&SignatureVersion=2&SignatureMethod=HmacSHA256&Timestamp='.urlencode($expires_at).'&Version=2009-01-01';	
		}
		
		else if($opcode == 'ReportRequestIdList'){
			$q = 'AWSAccessKeyId='.$this->AMAZON_AWS_Access_Key_ID.'&Action=GetReportRequestList&ReportRequestIdList.Id.1='.$params['ReportRequestID'].'&Marketplace='.$this->AMAZON_Marketplace_ID.'&SellerId='.$this->AMAZON_Merchant_ID.'&SignatureVersion=2&SignatureMethod=HmacSHA256&Timestamp='.urlencode($expires_at).'&Version=2009-01-01';
			
		}
		
		else if($opcode == 'GetReport'){
			$q = 'AWSAccessKeyId='.$this->AMAZON_AWS_Access_Key_ID.'&Action=GetReport&Marketplace='.$this->AMAZON_Marketplace_ID.'&SellerId='.$this->AMAZON_Merchant_ID.'&ReportId='.$params['ReportID'].'&SignatureVersion=2&SignatureMethod=HmacSHA256&Timestamp='.urlencode($expires_at).'&Version=2009-01-01';
			
		}
		
		else if($opcode == 'CancelReportRequests'){
			$q = 'AWSAccessKeyId='.$this->AMAZON_AWS_Access_Key_ID.'&Action=CancelReportRequests&ReportRequestIdList.Id.1='.$params['ReportRequestID'].'&Marketplace='.$this->AMAZON_Marketplace_ID.'&SellerId='.$this->AMAZON_Merchant_ID.'&SignatureVersion=2&SignatureMethod=HmacSHA256&Timestamp='.urlencode($expires_at).'&Version=2009-01-01';
			
		}
		
		else if($opcode == 'ConfirmShipping'){
			
			if(preg_match('/ups|usps|dhl/i', $params['carrier']))$params['carrier'] = strtoupper($params['carrier']);
			else if(preg_match('/fedex/i', $params['carrier']))$params['carrier'] = 'FedEx';
			
			$this->doPost = 1;
			$q = 'AWSAccessKeyId='.$this->AMAZON_AWS_Access_Key_ID.'&Marketplace='.$this->AMAZON_Marketplace_ID.'&Action=SubmitFeed&SellerId='.$this->AMAZON_Merchant_ID.'&FeedType=_POST_ORDER_FULFILLMENT_DATA_&Version=2009-01-01&SignatureVersion=2&SignatureMethod=HmacSHA256';
			//<CarrierName>'.$params['carrier'].'</CarrierName>
			$this->postData = '<?xml version="1.0" encoding="UTF-8"?>
						<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amznenvelope.xsd">
							<Header>
								<DocumentVersion>1.01</DocumentVersion>
								<MerchantIdentifier>My Store</MerchantIdentifier>
							</Header>
							<MessageType>OrderFulfillment</MessageType>
							<Message>
								<MessageID>1</MessageID>
								<OrderFulfillment>
									<AmazonOrderID>'.$params['order_id'].'</AmazonOrderID>
									<FulfillmentDate>'.date('Y-m-d\TH:i:s').'</FulfillmentDate>
									<FulfillmentData>
										<CarrierCode>'.$params['carrier'].'</CarrierCode>
										<ShipperTrackingNumber>'.$params['no'].'</ShipperTrackingNumber>
									</FulfillmentData>
									<Item>
										<AmazonOrderItemCode>'.$params['item_id'].'</AmazonOrderItemCode>
										<Quantity>'.$params['quantity'].'</Quantity>
									</Item>
								</OrderFulfillment> 
							</Message>
						</AmazonEnvelope>';	
						
			$this->content_md5 = base64_encode(md5($this->postData, true));
		}
		
		else if($opcode == 'ConfirmBulkShipping'){
			
			$post = '<?xml version="1.0" encoding="UTF-8"?>
						<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amznenvelope.xsd">
							<Header>
								<DocumentVersion>1.01</DocumentVersion>
								<MerchantIdentifier>My Store</MerchantIdentifier>
							</Header>
							<MessageType>OrderFulfillment</MessageType>
						';
						
			foreach($params as $i => $param){
				
				if(preg_match('/ups|usps|dhl/i', $param['carrier']))$param['carrier'] = strtoupper($param['carrier']);
				else if(preg_match('/fedex/i', $param['carrier']))$param['carrier'] = 'FedEx';
				
					$post .= '<Message>
								<MessageID>'.($i+1).'</MessageID>
								<OrderFulfillment>
									<AmazonOrderID>'.$param['order_id'].'</AmazonOrderID>
									<FulfillmentDate>'.date('Y-m-d\TH:i:s').'</FulfillmentDate>
									<FulfillmentData>
										<CarrierCode>'.$param['carrier'].'</CarrierCode>
										<ShipperTrackingNumber>'.$param['no'].'</ShipperTrackingNumber>
									</FulfillmentData>
									<Item>
										<AmazonOrderItemCode>'.$param['item_id'].'</AmazonOrderItemCode>
										<Quantity>'.$param['quantity'].'</Quantity>
									</Item>
								</OrderFulfillment> 
							</Message>
						';	
			}
			
			$post .= '</AmazonEnvelope>';
			
			//echo '<pre>'.$post;
			
			$this->doPost = 1;
			$q = 'AWSAccessKeyId='.$this->AMAZON_AWS_Access_Key_ID.'&Marketplace='.$this->AMAZON_Marketplace_ID.'&Action=SubmitFeed&SellerId='.$this->AMAZON_Merchant_ID.'&FeedType=_POST_ORDER_FULFILLMENT_DATA_&Version=2009-01-01&SignatureVersion=2&SignatureMethod=HmacSHA256';
			
			//<CarrierName>'.$params['carrier'].'</CarrierName>
			
			$this->postData = $post;			
			$this->content_md5 = base64_encode(md5($this->postData, true));
		}
		
		else if($opcode == 'UpdateInventory'){
			
			$this->doPost = 1;
			$q = 'AWSAccessKeyId='.$this->AMAZON_AWS_Access_Key_ID.'&Marketplace='.$this->AMAZON_Marketplace_ID.'&Action=SubmitFeed&SellerId='.$this->AMAZON_Merchant_ID.'&FeedType=_POST_INVENTORY_AVAILABILITY_DATA_&Version=2009-01-01&SignatureVersion=2&SignatureMethod=HmacSHA256';
			
			$this->postData = '<?xml version="1.0" encoding="utf-8" ?> 
								<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amznenvelope.xsd">
								<Header>
									<DocumentVersion>1.01</DocumentVersion> 
									<MerchantIdentifier>M_SELLER_354577</MerchantIdentifier> 
								</Header>
								<MessageType>Inventory</MessageType>';
			
			$k = 1;					
			foreach($params as $item => $stock)$this->postData .= 
								'<Message>
									<MessageID>'.($k++).'</MessageID> 
									<OperationType>Update</OperationType> 
									<Inventory>
										<SKU>'.$item.'</SKU> 
										<Quantity>'.$stock.'</Quantity> 
									</Inventory>
								</Message>';
								
			$this->postData .= '</AmazonEnvelope>';	
						
			$this->content_md5 = base64_encode(md5($this->postData, true));
		}
		
		else if($opcode == 'GetFeedSubmissionResult'){
			$q = 'AWSAccessKeyId='.$this->AMAZON_AWS_Access_Key_ID.'&Action=GetFeedSubmissionResult&FeedSubmissionId='.$params['feed_id'].'&Marketplace='.$this->AMAZON_Marketplace_ID.'&SellerId='.$this->AMAZON_Merchant_ID.'&SignatureVersion=2&SignatureMethod=HmacSHA256&Timestamp='.urlencode($expires_at).'&Version=2009-01-01';
		}
		
		$url .= $q;	
		
		$this->url = $this->generate_signature($url);
		$this->get_source(0);
		
		$this->content_md5 = '';
		
		$this->parse_xml($this->response);
		
	}
	
	/**
	 * Function to parse XML response from Amazon
	 *
	 * @param $xml string the response from Amazon
	 * @return n/a
	 * saves the parsed response to $this->data
	 */
	 
	public function parse_xml($xml)
	{
		$this->data = @simplexml_load_string($xml);
	}
	
	public function generate_signature($url)
	{
		$original_url = $url;
		$url = urldecode($url);
		$urlparts = parse_url($url);
	
		foreach (explode('&', $urlparts['query']) as $part) {
			if(strpos($part, '=')){
				list($name, $value) = explode('=', $part, 2);
			}
			else{
				$name = $part;
				$value = '';
			}
			$params[$name] = $value;
		}
		
		if (empty($params['Timestamp'])) {
			$params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
		}
	
		ksort($params);
	
		$canonical = '';
		foreach ($params as $key => $val) {
			$canonical  .= "$key=".rawurlencode(utf8_encode($val))."&";
		}
		
		$canonical = preg_replace("/&$/", '', $canonical);
		$canonical = str_replace(array(' ', '+', ',', ';'), array('%20', '%20', urlencode(','), urlencode(':')), $canonical);
	
		$string_to_sign = ($this->doPost ? 'POST' : 'GET')."\n{$urlparts['host']}\n{$urlparts['path']}\n$canonical";
		$signature = base64_encode(hash_hmac('sha256', $string_to_sign, $this->AMAZON_Secret_Key, true));
		$url = "{$urlparts['scheme']}://{$urlparts['host']}{$urlparts['path']}?$canonical&Signature=".rawurlencode($signature);
		return $url;
	}
	
	/*
	public function get_orders($from = 0)
	{
		$data = array();
		$this->curl_multi = 1;
		$this->doPost = 0;
		$this->url = array();
		
		
		//get first 50 orders
		for($i = $from; $i <= 10 ; $i++){
			$this->url[] = 'https://sellercentral.amazon.com/gp/orders-v2/list/ref=sm_myo_apsearch_myosearch?searchType=OrderID&searchKeyword=&searchDateOption=preSelected&preSelectedRange=1&statusFilter=Default&sortBy=OrderDateDescending&isDebug=&isAdvancedSearch=1&ignoreSearchType=1&searchLanguage=en_US&itemsPerPage=5&currentPage='.$i;
		}
		
		$this->get_source();
		$stop = 0;
		
		for($i = 0; $i < count($this->url); $i++){
			
			//echo $this->response[$i];exit;
			if(preg_match('/System Error/i', $this->response[$i]))$stop = 1;
			
			$html = new simple_html_dom();
			$html->load($this->response[$i]);
			$tr = $html->find('tr[class=order-row]');
			$count = count($tr);
			$this->log($count.' orders found on page '.$i);
			
			/*if($count == 0){
				echo $this->response[$i];
				exit;
			}
			
			foreach($tr as $t){
				$a = $t->find('a', 0);
				$order_id = strip_tags($a->text());
				$data[] = trim($order_id); 	
			}
			
			$html->clear(); 
			unset($html);
				
		}
		
		$this->url = array();
		
		if(!$stop){
			
			//get next 50 orders
			for($i = 10; $i <= 20 ; $i++){
				$this->url[] = 'https://sellercentral.amazon.com/gp/orders-v2/list/ref=sm_myo_apsearch_myosearch?searchType=OrderID&searchKeyword=&searchDateOption=preSelected&preSelectedRange=1&statusFilter=Default&sortBy=OrderDateDescending&isDebug=&isAdvancedSearch=1&ignoreSearchType=1&searchLanguage=en_US&itemsPerPage=5&currentPage='.$i;
			}
			
			$this->get_source();
			
			for($i = 0; $i < count($this->url); $i++){
				
				//echo $this->response[$i];exit;
				
				$html = new simple_html_dom();
				$html->load($this->response[$i]);
				$tr = $html->find('tr[class=order-row]');
				$count = count($tr);
				$this->log($count.' orders found on page '.$i);
				
				foreach($tr as $t){
					$a = $t->find('a', 0);
					$order_id = strip_tags($a->text());
					$data[] = trim($order_id); 	
				}
				
				$html->clear(); 
				unset($html);
					
			}
		}
		$orders = array();
		$data = array_chunk($data, 25);
		
		foreach($data as $order_ids){
			$this->Amazon('GetOrder', $order_ids);
			foreach($this->data->GetOrderResult->Orders->Order as $o)$orders[] = $o;
		}
		
		return $orders;
	}*/
	
	public function get_orders()
	{
		$orders = array();
		$this->Amazon('ListOrders', array('from' => date('Y-m-d\TH:i:s', time() - 3600*3)));
		
		foreach($this->data->ListOrdersResult->Orders->Order as $order)$orders[] = $order;
		
		return $orders;
	}
	
	public function get_next_orders($token)
	{
		$orders = array();
		$this->Amazon('ListOrdersByNextToken', array('token' => $token));
		
		foreach($this->data->ListOrdersByNextTokenResult->Orders->Order as $order)$orders[] = $order;
		
		return $orders;
	}
	
	public function get_listings($from)
	{
		//$this->Amazon('CancelReportRequests', array('ReportRequestID' => '10035908212'));
		
		$this->fromDate = date('Y-m-d\TH:i:s',strtotime($from));
		$this->Amazon('RequestListingsReport');
		
		$report_req_id = @$this->data->RequestReportResult->ReportRequestInfo->ReportRequestId;
		
		if(empty($report_req_id)){
			if(preg_match('/RequestThrottled/i', $this->response))return 'REQUEST_THROTTOLED';
			return 'EMPTY_REQ_ID';
		}
		
		$k = 0;
		do{
			if(!$k)sleep(180);
			else sleep(60);
			$this->Amazon('ReportRequestIdList', array('ReportRequestID' => $report_req_id));
			
			if($k++ >= 30){
				$this->Amazon('CancelReportRequests', array('ReportRequestID' => $report_req_id));
				break;
			}
			
			/*echo "<br/>===========<br/>";
			echo $this->response;
			echo "<br/>===========<br/>";*/
			
			if(empty($this->response))continue;
			if(end($this->data->GetReportRequestListResult->ReportRequestInfo->ReportProcessingStatus) == '_IN_PROGRESS_'
				|| end($this->data->GetReportRequestListResult->ReportRequestInfo->ReportProcessingStatus) == '_SUBMITTED_'
				|| preg_match('/RequestThrottled|Request signature is for too far/i', $this->response)
			)continue;
			
			else break;
			
		}while(1);
		
		$report_id = end($this->data->GetReportRequestListResult->ReportRequestInfo->GeneratedReportId);
		
		if(!empty($report_id)){
			$this->Amazon('GetReport', array('ReportID' => $report_id));
			return $this->response;
		}
		else $this->Amazon('CancelReportRequests', array('ReportRequestID' => $report_req_id));
		
		return false;
	}
	
	public function get_feedback($from)
	{
		//$this->Amazon('CancelReportRequests', array('ReportRequestID' => '10035908212'));
		
		$this->fromDate = date('Y-m-d\TH:i:s',strtotime($from));
		$this->Amazon('RequestFeedBack');
		
		$report_req_id = @$this->data->RequestReportResult->ReportRequestInfo->ReportRequestId;
		
		if(empty($report_req_id)){
			if(preg_match('/RequestThrottled/i', $this->response))return 'REQUEST_THROTTOLED';
			return 'EMPTY_REQ_ID';
		}
		
		$k = 0;
		do{
			if(!$k)sleep(60);
			else sleep(60);
			$this->Amazon('ReportRequestIdList', array('ReportRequestID' => $report_req_id));
			
			if($k++ >= 30){
				$this->Amazon('CancelReportRequests', array('ReportRequestID' => $report_req_id));
				break;
			}
			
			/*echo "<br/>===========<br/>";
			echo $this->response;
			echo "<br/>===========<br/>";*/
			
			if(empty($this->response))continue;
			if(end($this->data->GetReportRequestListResult->ReportRequestInfo->ReportProcessingStatus) == '_IN_PROGRESS_'
				|| end($this->data->GetReportRequestListResult->ReportRequestInfo->ReportProcessingStatus) == '_SUBMITTED_'
				|| preg_match('/RequestThrottled|Request signature is for too far/i', $this->response)
			)continue;
			
			else break;
			
		}while(1);
		
		$report_id = end($this->data->GetReportRequestListResult->ReportRequestInfo->GeneratedReportId);
		
		if(!empty($report_id)){
			$this->Amazon('GetReport', array('ReportID' => $report_id));
			return $this->response;
		}
		else $this->Amazon('CancelReportRequests', array('ReportRequestID' => $report_req_id));
		
		return false;
	}
	
	public function get_order_items($order_id)
	{
		$item = array();
		$this->Amazon('ListOrderItems', array('order_id' => $order_id));
			
		$aaa = $this->data->ListOrderItemsResult->OrderItems->OrderItem;
		
		foreach($aaa as $aa){
			$i = array();
			$i['item_id'] = trim($aa->OrderItemId);
			$i['item_title'] = trim($aa->Title);
			$i['item_condition'] = trim($aa->ConditionId);
			$i['item_sku'] = trim($aa->SellerSKU);
			$i['item_quantity'] = trim($aa->QuantityOrdered);
			$i['paid_amount'] = (float)($aa->ShippingPrice->Amount) + (float)($aa->ItemPrice->Amount);
			$i['sales_tax'] = (float)($aa->ShippingTax->Amount) + (float)($aa->ItemTax->Amount);
			$item[] = $i;	
		}		
		
		return $item;
	}
	
	public function get_postshipping_info($order_ids)
	{
		$ship = array();
		$order_ids = array_chunk($order_ids, CURL_LIMIT);
		$this->curl_multi = 1;
		$this->doPost = 0;
		
		
		foreach($order_ids as $ords){
			$this->url = array();
			foreach($ords as $od)$this->url[] = 'https://sellercentral.amazon.com/gp/orders-v2/details/ref=sm_orddet_cont_myo?ie=UTF8&orderID='.$od;
			$this->get_source();
			
			for($i = 0; $i < count($this->url); $i++){
				//echo $this->response[$i];exit;
				if(preg_match('/orderID: "([0-9\-]+)"/siU', $this->response[$i], $m))$od = trim($m[1]);
				else if(preg_match('/orderID: \'([0-9\-]+)\'/siU', $this->response[$i], $m))$od = trim($m[1]);
				else{
					do_log('No order id found on shipping confirmation page');
					continue;
				}
				
				$ship[$od] = array();
			
				//echo $this->response[$i];exit;
				
				$html = new simple_html_dom();
				$html->load($this->response[$i]);
				
				$vendor_id = '';
				$t_carrier = '';
				$t_id = '';
				$note = '';
				
				$kk = 0;
				$item_ids = array();
				
				
				$elem = $html->find('td[class=tiny-example]');
				if(empty($elem))return false;
				foreach($elem as $e){
					if(method_exists($e, 'text')){
						$text = trim(strip_tags($e->text()));
						if($kk == 1 && is_numeric($text)){
							$item_ids[] = $text;
							$kk = 0;
						}
						if(preg_match('/Order Item ID/i', $text))$kk = 1;	
					}
				}
				
				if(empty($item_ids)){
					do_log('No item id found on shipping confirmation page');
					continue;
				}
				
				$item_ids = array_slice($item_ids, count($item_ids)/2);
				
				$elem = $html->find('textarea[id=_myoPN_note]', 0);
				if(!empty($elem)){
					$vendor_id = strip_tags($elem->text());
					$note = $vendor_id;
					if(preg_match('/(\d+)/', $vendor_id, $m)){
						$vendor_id = $m[1];
					}	
				}
				
				$kk = 0;
				$kkk = 0;
				$elem = $html->find('td[class=data-display-field]');
				if(!empty($elem)){
					foreach($elem as $e){
						if(method_exists($e, 'text')){
							$t = strip_tags($e->text());
							if(preg_match('/Carrier:/siU', $t)){
								$t_carrier = substr($t, strrpos($t, ';')+1, strlen($t));
								$ship[$od][$item_ids[$kk]]['tracking_carrier'] = $t_carrier;
								$kk++;	
							}
							else if(preg_match('/Tracking ID:/siU', $t)){
								$t_id = substr($t, strrpos($t, ';')+1, strlen($t));
								if($t_id == 'None entered')$t_id = '';	
								$ship[$od][$item_ids[$kkk]]['tracking_id'] = $t_id;
								$kkk++;
							}	
						}	
					}
				}
				
				$html->clear(); 
				unset($html);
				
				$ship[$od]['vendor_orderid'] = $vendor_id;
				$ship[$od]['sales_note'] = $note;
			}
		}
		
		return $ship;
	}
	
	public function update_sales_note($order_id, $sales_note)
	{	
		$this->curl_multi = 0;
		$this->doPost = 1;
		$this->postData = array(
								'action' => 'update-private-note',
								'orderID' => $order_id,
								'privateNote' => $sales_note,
								'applicationPath' => '/gp/orders-v2'
								);
								
		$this->url = 'https://sellercentral.amazon.com/gp/orders-v2/remote-actions/action.html';
		$this->get_source();
		
		if(preg_match('/<status>success<\/status>/siU', $this->response))return true;
		return false;
	}
	
	public function set_tracking_number($order_id, $no, $carrier, $quantity, $item_id)
	{
		$params = array(
						'order_id' => $order_id,
						'carrier' => $carrier,
						'no' => $no,
						'quantity' => $quantity,
						'item_id' => $item_id
						);
						
		$this->Amazon('ConfirmShipping', $params);
		
		$feed_id = $this->data->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
		
		if(empty($feed_id)){
			if(preg_match('/RequestThrottled/i', $this->response))return 'REQUEST_THROTTOLED';
			do_log($this->response);
			//exit;
			return 'EMPTY_FEED_ID';
		}
		$k = 0;
		do{
			if(!$k)sleep(60);
			else sleep(15);
			$this->Amazon('GetFeedSubmissionResult', array('feed_id' => $feed_id));
			
			if($k++ >= 20)break;
			
			if(@$this->data->Error->Code == 'FeedProcessingResultNotReady')continue;
			else if(@$this->data->Error->Code == 'RequestThrottled')continue;
			else break;
		}while(1);
		
		//echo $this->response;
		
		if($this->data->Message->ProcessingReport->ProcessingSummary->MessagesSuccessful == 1)return 'SUCCESS';
		
		$message = $this->data->Message->ProcessingReport->Result->ResultDescription;
		if(empty($message))$message = 'FAIL';
		return $message;
		
	}
	
	public function set_bulk_tracking_number($data)
	{
		$this->errors = array();
		$this->Amazon('ConfirmBulkShipping', $data);
		
		$feed_id = $this->data->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
		
		if(empty($feed_id)){
			if(preg_match('/RequestThrottled/i', $this->response))return 'REQUEST_THROTTOLED';
			do_log($this->response);
			//exit;
			return 'EMPTY_FEED_ID';
		}
		$k = 0;
		do{
			if(!$k)sleep(60);
			else sleep(30);
			$this->Amazon('GetFeedSubmissionResult', array('feed_id' => $feed_id));
			
			if($k++ >= 100)break;
			
			if(@$this->data->Error->Code == 'FeedProcessingResultNotReady')continue;
			else if(@$this->data->Error->Code == 'RequestThrottled')continue;
			else break;
		}while(1);
		
		//echo $this->response;
		
		if(!empty($this->data->Message->ProcessingReport->ProcessingSummary->MessagesSuccessful)){
			$total = (int)$this->data->Message->ProcessingReport->ProcessingSummary->MessagesProcessed;
			$success = (int)$this->data->Message->ProcessingReport->ProcessingSummary->MessagesSuccessful;
			
			do_log($total.' feed submitted. '.$success.' successful');
			
			if($total != $success){
				do_log('Error with feed submission');
				foreach($this->data->Message->ProcessingReport->Result as $s){
					$mid = (int)$s->MessageID;
					$msg = (string)$s->ResultDescription;
					do_log('Error with message #'.$mid.' # '.$msg);
					if(!empty($data[$mid-1]['ref']))$this->errors[$data[$mid-1]['ref']] = $msg;	
				}
			}
			return 'SUCCESS';
		}
		
		$message = $this->data->Message->ProcessingReport->Result->ResultDescription;
		if(empty($message))$message = 'FAIL';
		return $message;
		
	}
	
	/*
	 * @note: items count must not be greater than 500
	 */
	public function UpdateInventory($items)
	{
		$this->Amazon('UpdateInventory', $items);
		
		$feed_id = $this->data->SubmitFeedResult->FeedSubmissionInfo->FeedSubmissionId;
	
		if(empty($feed_id)){
			if(preg_match('/RequestThrottled/i', $this->response))return 'REQUEST_THROTTOLED';
			return 'EMPTY_FEED_ID';
		}
		$k = 0;
		do{
			if(!$k)sleep(60);
			else sleep(15);
			$this->Amazon('GetFeedSubmissionResult', array('feed_id' => $feed_id));
			
			if($k++ >= 10)break;
			
			if(@$this->data->Error->Code == 'FeedProcessingResultNotReady')continue;
			else break;
		}while(1);
		
		if($this->data->Message->ProcessingReport->Summary->ProcessingSummary->MessagesSuccessful == 1)return 'SUCCESS';
		$message = $this->data->Message->ProcessingReport->Result->ResultDescription;
		return $message;
		
	}
	
	public function get_commissions()
	{
		$this->curl_multi = 0;
		$this->doPost = 0;
		
		$this->url = 'https://sellercentral.amazon.com/gp/payments-account//export-transactions.html?ie=UTF8&endDate='.urlencode(date('m/d/y')).'&eventType=&mostRecentLast=0&pageSize=DownloadSize&startDate='.urlencode(date('m/d/y', time() - 3600*24*1)).'&subview=dateRange&view=filter';
		
		$this->get_source(0);
		
		return $this->response;
	}
	
	public function process_orders($orders, $user_id)
	{
		$myorders = array();
		if(!empty($orders)){
		
			foreach($orders as $order){
				
				$o = array();
				do_log('Processing order id '.$order->AmazonOrderId);
				
				$o['order_id'] = trim($order->AmazonOrderId);
				if(empty($o['order_id']))continue;
				
				if(mysql_num_rows(mysql_query("SELECT NULL FROM ebay_orders WHERE order_id = '".mysql_real_escape_string($o['order_id'])."' AND dropship_done = 3")))continue;
				
				$ship = array();
				$ship_to = $order->ShippingAddress;
				$ship['buyer_user_id'] = trim($order->BuyerName);	
				$ship['name'] = trim($ship_to->Name);	
				$ship['street1'] = trim($ship_to->AddressLine1);
				$ship['street2'] = @trim($ship_to->AddressLine2);
				if(empty($ship['street1']) && !empty($ship['street2'])){
					$ship['street1'] = $ship['street2'];
					$ship['street2'] = '';
				}
				$ship['city'] = trim($ship_to->City);
				$ship['state_province'] = trim(strtoupper($ship_to->StateOrRegion));
				$ship['country'] = trim($ship_to->CountryCode);
				$ship['country_name'] = trim($ship_to->CountryCode);
				$ship['postal_code'] = substr(trim($ship_to->PostalCode), 0, 5);
				$ship['phone'] = preg_replace('/[^0-9\s]/', '', trim($ship_to->Phone));
				//$ship['sales_tax'] = $order->ShippingDetails->SalesTax->SalesTaxAmount;
				
				$o['buyer'] = mysql_real_escape_string(trim($order->BuyerName));
				$o['shipping']  = $ship;
				$o['buyer_email'] = trim($order->BuyerEmail);
				$o['order_status'] = trim($order->OrderStatus);
				$o['sales_time'] = str_replace(array('T', 'Z'), ' ' ,$order->PurchaseDate);
				$o['mod_time'] = str_replace(array('T', 'Z'), ' ' ,$order->LastUpdateDate);
				$o['payment_method'] = $order->PaymentMethod;
				$o['tracking_carrier'] = '';
				$o['tracking_number'] = '';
				$o['dropship_orderid'] = '';
				$o['ebay_sales_note'] = '';
				$o['index'] = strtotime($o['mod_time']); 
				
				$myorders[$o['order_id']] = $o;
				//var_dump($o['order_id']);
			}	
		}
		
		
		//var_dump($myorders);
		
		if(!empty($myorders)){
			$kk = 0;
			foreach($myorders as $order_id => $order){
				$items = $this->get_order_items($order_id);	
				$myorders[$order_id]['item'] = $items;
				if($kk++ >= 7){
					$kk = 0;
					do_log('Sleeping for 20 seconds');
					sleep(20);
				}
			}
		}
		
		
		if(!empty($myorders)){
			$ord = array();
			foreach($myorders as $order_id => $order){
				foreach($order['item'] as $item){
					$item_id = $item['item_id'];
					if(mysql_num_rows(mysql_query("SELECT NULL FROM ebay_orders WHERE order_id = '".mysql_real_escape_string($order_id)."' AND dropship_done = 3 AND item_id = '$item_id'")))continue;
					if($order['order_status'] == 'Unshipped')continue; 
					$ord[] = $order_id;
				}
			}	
			$shipping = $this->get_postshipping_info($ord);
			
			if(!empty($shipping)){
				foreach($shipping as $order_id => $info){
					foreach($info as $jj => $ii){
						if(is_numeric($jj)){
							foreach($myorders[$order_id]['item'] as $jjj => $iii){
								if($iii['item_id'] == $jj){
									$myorders[$order_id]['item'][$jjj]['tracking_carrier'] = $info[$jj]['tracking_carrier'];
									$myorders[$order_id]['item'][$jjj]['tracking_number'] = $info[$jj]['tracking_id'];
								}
							}
						}
					}
					
					$myorders[$order_id]['dropship_orderid'] = $info['vendor_orderid'];
					$myorders[$order_id]['ebay_sales_note'] = $info['sales_note']; 	
				}	
			}	
		}
		
		aasort($myorders, 'index');
		//var_dump($myorders);
		
		if(!empty($myorders)):
		
			foreach($myorders as $order):
				
				if(empty($order['order_id']))continue;
				
				$orderID = mysql_real_escape_string($order['order_id']);	
				$salesTime = mysql_real_escape_string($order['sales_time']);
				$modTime = mysql_real_escape_string($order['mod_time']);
				$buyerEmail = mysql_real_escape_string($order['buyer_email']);
				$paymentMethod = mysql_real_escape_string($order['payment_method']);
				$ebay_sales_notes = mysql_real_escape_string($order['ebay_sales_note']);
				$dropship_orderid = mysql_real_escape_string($order['dropship_orderid']);
				
				$ship = $order['shipping'];
				$items = $order['item'];
				
				
				foreach($items as $item){
					$m_done = 0;
					$status = 0;
					$new = 0;
					$dropship_status = '';
					
					$itemId = $item['item_id'];
					$tracking_carrier = mysql_real_escape_string(@$item['tracking_carrier']);
					$tracking_number = mysql_real_escape_string(@$item['tracking_number']);
					
					if($order['order_status'] == 'Shipped'){
						if(!empty($order['tracking_number'])){
							$status = 3;
							$dropship_status = 'ITEM_TRACKED_M';
						}
						else if(!empty($order['dropship_orderid'])){
							$status = 2;
							$dropship_status = 'ITEM_NOTED_M';
						}
						else{
							$status = 1;
							$dropship_status = 'ITEM_ORDERED_M';
						}
					}
					
					$sql = mysql_query("SELECT id,mod_time,tracking_number,dropship_done FROM ebay_orders WHERE order_id = '$orderID' AND item_id = '$itemId'");
					
					if(!mysql_num_rows($sql)){
						$new = 1;
						mysql_query("INSERT INTO ebay_orders (sales_channel, order_id, item_id, seller_id, vendor, buyer_email, expense_fee ,sales_time, mod_time, payment_method, added_at) VALUES('Amazon','$orderID', '$itemId' ,'".mysql_real_escape_string($user_id)."', 'SamsClub' , '$buyerEmail' , '0', '$salesTime', '$modTime', '$paymentMethod', NOW())");
						$id = mysql_insert_id();
					}
					else list($id, $mod_time, $tc, $m_done) = mysql_fetch_row($sql); 	
					
					if(!empty($id)){
						
						//if modification time is old
						/*if(!$new)if($modTime == $mod_time){
							do_log('Mod time is same for '.$orderID);
							continue;
						}*/
						
						//var_dump($m_done);
						
						mysql_query("UPDATE ebay_orders SET mod_time = '$modTime', ebay_sales_notes = '$ebay_sales_notes' WHERE id = '$id'");
						
						if($m_done != 500){
							if(!empty($m_done)){
								if($status && $status > $m_done)mysql_query("UPDATE ebay_orders SET dropship_done = '$status', dropship_status = '$dropship_status' WHERE id = '$id'");
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
						
						if((!empty($tracking_number) || !empty($tracking_carrier)) && empty($tc)){	
							mysql_query("UPDATE ebay_orders SET tracking_number = '$tracking_number', tracking_carrier = '$tracking_carrier' WHERE id = '$id'");
						}
						
						if(!empty($dropship_orderid)){
							//here dropship_orderid must be null
							mysql_query("UPDATE ebay_orders SET dropship_orderid = '$dropship_orderid' WHERE id = '$id' AND dropship_orderid = ''");	
						}
						
						/**
						 * Set a time only if the listing is pending
						 */
						if(AUTO_ORDER){
							if(ORDER_SAFETY_200 && $item['paid_amount'] >= 200){
								do_log("Paid amount ".$item['paid_amount']." is above safety limit to auto order");
							}
							else mysql_query("UPDATE ebay_orders SET process_at = NOW() WHERE id = '$id' AND dropship_done = 0");
						}
					}
					
					mysql_query("UPDATE ebay_orders SET dropship_done = 1, dropship_status = 'ITEM_ORDERED_M' 
									WHERE dropship_orderid != '' AND dropship_done = 0 AND tracking_number = ''");
									
					mysql_query("UPDATE ebay_orders SET dropship_done = 3, dropship_status = 'ITEM_SHIPPED_M' 
									WHERE dropship_done != 3 AND tracking_number != ''");
									
				}
			endforeach;
		endif;
	}
	
	public function process_commission()
	{
		do_log('Getting Amazon commissions...');
		if($this->login()){
			$data = $this->get_commissions();
			$f = dirname(dirname(__FILE__)).'/logs/amazon-tmp.txt';
			file_put_contents($f, $data);
			
			$fp = fopen($f, 'r');
			for($i = 0; $i < 4; $i++){
				$line = fgets($fp, 4096);
			}
			
			$orders = array();
			
			while($line = fgetcsv($fp, 4096, "\t")){
				$id = $line[1];
				$sku = $line[2];
				if(empty($orders[$id])){
					$orders[$id] = array();
					$orders[$id][$sku] = 0;
				}
				foreach($line as $v)if(preg_match('/\$\-/', $v))$orders[$id][$sku] += (float)preg_replace('/\$\-/', '' , $v);	
			}
			
			foreach($orders as $id => $v){
				foreach($v as $sku => $val){
					do_log("Fees for order id: ".$id." sku: ".$sku." fee: ".$val);
					mysql_query("UPDATE ebay_orders SET expense_fee = '$val' WHERE order_id = '$id' AND item_sku = '$sku'");
					mysql_query("UPDATE ebay_orders SET amount_profit = ROUND(paid_amount - purchase_price - expense_fee, 2) WHERE order_id = '$id' AND purchase_price != ''");
				}
			}	
		}
	}
	
	/*
	public function parse_order_data()
	{
		for($i = 0; $i < count($url) ; $i++){
			$html = new simple_html_dom();
			$html->load($this->response[$i]);
			$tr = $html->find('tr[class=order-row]');
			$count = count($tr);
			$this->log($count.' orders found on page '.$i);
			$data = array();
			foreach($tr as $t){
				$a = $t->find('a', 0);
				$order_id = strip_tags($a->text());
				$data[] = trim($order_id); 	
			}
			
			$html->clear(); 
			unset($html);
		}
	}
	*/
	
	public function login()
	{
		//@unlink($this->cookie);
		$this->curl_multi = 0;
		$this->doPost = 0;
		$this->url = 'https://sellercentral.amazon.com/gp/homepage.html?';
		$this->get_source();
		
		if(preg_match('/Logout/siU', $this->response)){
			$this->log('Login successful. Already logged in.');
			return true;	
		}
		
		$this->log('Logging in...');
		$this->postData = $this->getFormFields('signinWidget', 'name');
		
		$this->postData['username'] = $this->username;
		$this->postData['password'] = $this->password;
		$this->postData['rememberMe'] = 'true';
		
		$this->doPost = 1;
		$this->url = 'https://sellercentral.amazon.com/ap/widget';
		$this->get_source();
		
		if(preg_match('/Logout/siU', $this->response)){
			$this->log('Login successful');
			return true;	
		}
		//echo $this->response;
		$this->log('Login failed');
		return false;
	}
	
	public function get_source($trim = 1)
	{
		//single curl
		if(!$this->curl_multi){
			$this->log("Requesting $this->url [single-curl]");
			$ch=curl_init($this->url);
			curl_setopt($ch,CURLOPT_HEADER,0);	
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);	
			curl_setopt($ch,CURLOPT_BINARYTRANSFER,0);	
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
			curl_setopt($ch,CURLOPT_REFERER,$this->referer);	
			curl_setopt($ch,CURLOPT_TIMEOUT, 60);
			curl_setopt($ch,CURLOPT_USERAGENT,$this->userAgent);	
			curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
			if(!empty($this->cookie)){
				curl_setopt($ch,CURLOPT_COOKIE,1);
				curl_setopt($ch,CURLOPT_COOKIEFILE,$this->cookie);	
				curl_setopt($ch,CURLOPT_COOKIEJAR,$this->cookie);
			}
			if(!empty($this->content_md5))curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-MD5: '.$this->content_md5));
			if($this->doPost){
				curl_setopt($ch,CURLOPT_POST,1);
				curl_setopt($ch,CURLOPT_POSTFIELDS,$this->postData);
			}
			if(!empty($this->proxy)){
				$this->log("Requesting from proxy ".$this->proxy." with u/p ".$this->proxy_pwd);
				curl_setopt($ch, CURLOPT_PROXY,$this->proxy);
				if(!empty($this->proxy_pwd))curl_setopt($ch, CURLOPT_PROXYUSERPWD,$this->proxy_pwd);
			}
			$data=curl_exec($ch);
			if($trim)$data=preg_replace('/\s{2,}/',' ',$data);
			$this->response=$data;
			$info=curl_getinfo($ch);
			$this->curl_info=$info;
			$this->last_url=$info['url'];
		}
		
		//curl multi
		else{
			$mh=curl_multi_init();
			$ch=array();
			
			for($i=0;$i<count($this->url);$i++):
				$this->log("Requesting ".$this->url[$i]." [multi-curl]");
				$ch[$i]=curl_init($this->url[$i]);
				curl_setopt($ch[$i],CURLOPT_HEADER,0);	
				curl_setopt($ch[$i],CURLOPT_RETURNTRANSFER,1);	
				curl_setopt($ch[$i],CURLOPT_BINARYTRANSFER,0);	
				curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION,1);
				curl_setopt($ch[$i],CURLOPT_REFERER,$this->referer);	
				curl_setopt($ch[$i],CURLOPT_TIMEOUT, 60);
				curl_setopt($ch[$i],CURLOPT_USERAGENT,$this->userAgent);	
				curl_setopt($ch[$i],CURLOPT_SSL_VERIFYPEER,0);
				if(!empty($this->cookie)){
					curl_setopt($ch[$i],CURLOPT_COOKIE,1);
					curl_setopt($ch[$i],CURLOPT_COOKIEFILE,$this->cookie);	
					curl_setopt($ch[$i],CURLOPT_COOKIEJAR,$this->cookie);
				}
				if(!empty($this->content_md5))curl_setopt($ch[$i], CURLOPT_HTTPHEADER, array('Content-MD5: '.$this->content_md5));
				if($this->doPost){
					curl_setopt($ch[$i],CURLOPT_POST,1);
					curl_setopt($ch[$i],CURLOPT_POSTFIELDS,$this->postData);
				}
				if(!empty($this->proxy)){
					$this->log("Requesting from proxy ".$this->proxy." with u/p ".$this->proxy_pwd);
					curl_setopt($ch[$i], CURLOPT_PROXY,$this->proxy);
					if(!empty($this->proxy_pwd))curl_setopt($ch[$i], CURLOPT_PROXYUSERPWD,$this->proxy_pwd);
				}
				curl_multi_add_handle($mh,$ch[$i]);
				
			endfor;
			
			if(empty($ch))return;
			
			$running=0;
		
			do{
				curl_multi_exec($mh,$running);
				usleep(50);
			}while($running>0);
			
			$this->response = array();
			$this->curl_info = array();
			$this->last_url = array();
			
			for($i=0;$i< count($this->url); $i++){
				$cr = curl_multi_getcontent($ch[$i]);
				$data = preg_replace('/\s{2,}/',' ',$cr);
				$this->response[$i] = $data;
				$info = curl_getinfo($ch[$i]);
				$this->curl_info[$i] = $info;
				$this->last_url[$i] = $info['url'];
				curl_multi_remove_handle($mh, $ch[$i]);
			}
			curl_multi_close($mh);	
		}
	}
	
	/**
	 * Function to parse form fields from page source
	 *
	 * using simplehtmldom
	 * @param string $id ID or name of the form
	 * @param string $elem element tag of the passed id
	 * @return array with form fields if success and null array if failed
	 */
	public function getFormFields($id, $elem)
	{
		$data = array();
		$html = new simple_html_dom();
		$html->load($this->response);
		$form = $html->find('form['.$elem.'^='.$id.']', 0);
		if(!empty($form)){
			$inputs = $form->find("input");
			if(!empty($inputs)){
				foreach($inputs as $input) {
					if(!empty($input->name))$data[$input->name] = $this->decode_entities($input->value);
				}
			}
		}
		$html->clear(); 
		unset($html);
		return $data;
	}
	
	/**
	 * Function to decode text entries
	 *
	 * @param string $text the text to decode
	 * @return string $text
	 */
	public function decode_entities($text)
	{
		$text = trim($text);
		$text = html_entity_decode($text, ENT_QUOTES, "ISO-8859-1");
		$text = preg_replace('/&#(\d+);/me', "chr(\\1)", $text);
		$text = preg_replace('/&#x([a-f0-9]+);/mei', "chr(0x\\1)", $text);
		if($text == 'true')$text = true;
		else if($text == 'false')$text = false;
		return $text;
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
		global $set, $debug_off;
		$fp = fopen(dirname(dirname(__FILE__))."/logs/".(empty($set) ? 'amazon': $set)."-log.txt","a");
		fwrite($fp, date('[d-M-Y H:i:s]')." $str\r\n");
		fclose($fp);
	
		if(!empty($debug_off))return true;
		echo $str."<br/>";
		@flush();
		@ob_flush();
	}
}

?>