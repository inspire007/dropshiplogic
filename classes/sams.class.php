<?php
/**
 * This class is used to handle orders in SamsClub.
 *
 * @package EbaySalesAutomation
 * @subpackage SamsClub
 * @version 1.0
 * @author N/A
 * @copyright 2014
 */
 
/**
 * This the Main Samsclub class
 * Work flow :
 * Step1. Login/Login check
 * Step2. Load cart
 * Step2. Clear cart if any product loaded
 * Step3. Search product by sku
 * Step4. If found add product to cart
 * Step5. Proceed to checkout
 * Step6. Add shipping address
 * Step7. Confirm payment, tax exempt
 * Step8. verify and place order
 */
class SamsClub
{
	/**
 	 * curl related variables
 	 */
	public $url;
	public $referer;
	public $cookie;
	public $curl_info;
	public $doPost;
	public $postData;
	public $response;
	public $last_url;
	public $userAgent;
	
	/**
 	 * order id variables
 	 */
	public $productID;
	public $productSKU;
	public $sales_order_id;
	
	/**
 	 * price quantity cost
 	 */
	public $product_unit_price;
	public $product_unit_paid;
	public $quantity;
	public $total_cost;
	
	/**
 	 * shiping address
 	 */
	public $street1;
	public $buyer_f_name;
	public $city;
	public $state;
	public $post_code;
	
	/**
 	 * post order
 	 */
	public $tracking_no;
	public $tracking_url;
	public $tracking_carrier;
	public $shipment_status;
	public $order_status;
	public $priv_note;
	
	/**
 	 * misc variables
 	 */
	public $mysql_res;
	public $log_file;
	public $error;
	public $ignore_loss;
	public $ignore_price;
	public $ignore_tax;
	
	/**
	 * Class constructor
	 *
	 * @param n/a
	 * @return n/a
	 */
	public function __construct()
	{
		$this->userAgent = "Mozilla/5.0 (Windows NT 6.1; rv:16.0) Gecko/20100101 Firefox/16.0";
		$this->cookie = dirname(dirname(__FILE__))."/logs/cookie.txt";
		$this->log_file = dirname(dirname(__FILE__))."/logs/sams-log.txt";
	}
	
	/**
	 * Function to initialize the bot
	 *
	 * @param $sku string the sku to order from sams
	 * @param $quantity integer the quantity of the product to purchase
	 * @param $shipping array the array of mysql result used to get shipping address
	 * @return true if success and false if failed
	 * Firsts load the cart, clears the cart, search product, adds to cart, initialize checkout
	 * loads shipping address, confirms order, makes final payment, collects order id
	 * Each step is retired
	 */
	public function init_order($sku, $quantity, $shipping)
	{
		$this->error = '';
		$this->sales_order_id = '';
		$this->total_cost = 0;
		$this->ignore_loss = 0;
		$this->ignore_price = 0;
		$this->ignore_tax = 0;
		$this->product_unit_paid = $shipping['paid_price'];
		$this->ignore_loss = $shipping['ignore_loss'];
		$this->ignore_price = $shipping['ignore_price'];
		$this->ignore_tax = $shipping['ignore_tax'];
		
		//---login
		$this->login();
		if(!empty($this->error))return false;
		
		//---clear cart
		//retry if failed
		$this->load_cart();		
		if(!$this->clear_cart()){
			if(!$this->clear_cart()){
				$this->error = 'CART_CLEAR_FAIL';
				$this->log('Could not clear cart');
				return false;	
			}
		}
		
		//---search
		$this->search_product($sku);
		if(!empty($this->error))return false;
		
		$this->quantity = $quantity;
		
		//---add to cart
		//retry if failed
		$this->load_product();
		if(!empty($this->error)){
			$this->error = '';
			$this->log('Retrying load product...');
			$this->load_product();
			if(!empty($this->error))return false;
		}
		
		//---checkout
		//retry if failed
		$this->init_checkout();
		
		//login if needed
		if(preg_match('/account\/signin\/login\.jsp/i', $this->last_url)){
			$this->error = '';
			$this->login(1);	
			if(!empty($this->error))return false;
			$this->init_checkout();
		}
		
		if(!empty($this->error)){
			$this->error = '';
			$this->log('Retrying init checkout...');
			$this->init_checkout();
			if(!empty($this->error))return false;
		}
		
		//---shipping
		//retry if failed
		$this->street1 = $shipping['street1'];
		$this->city = $shipping['city'];
		$this->state = $shipping['state_province'];
		$this->post_code = $shipping['postal_code'];
		
		$shipping['name'] = preg_replace('/[^a-z\.\-]/i', ' ', $shipping['name']);

		list($this->buyer_f_name) = explode(' ', $shipping['name']);
		$this->load_shipping_address($shipping['name'], $shipping['street1'], $shipping['street2'], $shipping['city'], 
									$shipping['state_province'], $shipping['postal_code'], $shipping['phone']);
		
		if(preg_match('/account\/signin\/login\.jsp/i', $this->last_url)){
			$this->error = '';
			$this->login(1);	
			if(!empty($this->error))return false;
			$this->load_shipping_address($shipping['name'], $shipping['street1'], $shipping['street2'], $shipping['city'], 
										$shipping['state_province'], $shipping['postal_code'], $shipping['phone']);
		}
		
		$i = 0;
		if(!empty($this->error)){
			do{
				$this->log('Retrying load shipping address...');
				$this->error = '';
				$this->load_shipping_address($shipping['name'], $shipping['street1'], $shipping['street2'], $shipping['city'], 
										$shipping['state_province'], $shipping['postal_code'], $shipping['phone']);
				
				if(preg_match('/account\/signin\/login\.jsp/i', $this->last_url)){
					$this->error = '';
					$this->login(1);	
					if(!empty($this->error))return false;
					$this->load_shipping_address($shipping['name'], $shipping['street1'], $shipping['street2'], $shipping['city'], 
												$shipping['state_province'], $shipping['postal_code'], $shipping['phone']);
				}
				
				if(empty($this->error))break;
				if($i++ >= 5)break;
			}while(1);
			if(!empty($this->error))return false;
		}
		
		//---confirm
		//retry if failed
		$this->confirm_order();
		if(!empty($this->error)){
			$this->error = '';
			$this->log('Retrying confirm order...');
			$this->confirm_order();
			if(!empty($this->error))return false;
		}
		
		//---final
		//retry if failed
		$this->make_final_order();
		if(!empty($this->error)){
			$this->error = '';
			$this->log('Retrying final order...');
			$this->make_final_order();
			if(!empty($this->error))return false;
		}
		
		return true;
	}
	
	/**
	 * Function to login to Samsclub account
	 *
	 * @param int $skip_check whether it should load myaccount to check login
	 * @return true if success and false if failed
	 * used in two places, at start and at init checkout
	 */
	public function login($skip_check = 0)
	{
		$this->log("Logging in...");
		
		if(!$skip_check){
			$this->url = 'https://www.samsclub.com/sams/account/myaccount/account.jsp';
			$this->referer = 'https://www.samsclub.com/sams/homepage.jsp';
			$this->doPost = 0;
			$this->get_source();
			
			if(preg_match('/href="\/sams\/logout\.jsp"/', $this->response, $m)){
				$this->log("Login successful. Already logged in");
				return true;	
			}
		}
		
		$this->postData = $this->getFormFields('loginForm', 'name');
		
		if(!isset($this->postData['/atg/userprofiling/ProfileFormHandler.value.login'])){
			//var_dump( $this->response);
			//var_dump($this->curl_info);
			//var_dump($this->postData);
			$this->log("Login form not found | ".$this->last_url);
			$this->error = 'LOGIN_FORM_NOT_FOUND';
			return false;	
		}
		
		$this->postData['/atg/userprofiling/ProfileFormHandler.value.login'] = SAMS_CLUB_EMAIL;
		$this->postData['/atg/userprofiling/ProfileFormHandler.value.password'] = SAMS_CLUB_PASS;
		
		$this->referer = $this->last_url;
		
		$this->url = $this->last_url.'&&_DARGS='.urlencode($this->postData['_DARGS']);
		//parse_str($this->last_url, $args);
		//$this->url = 'https://www.samsclub.com/sams/account/signin/login.jsp?memberMess=true&redirectURL='.(array_shift($args)).'&&_DARGS='.@$this->postData['_DARGS'];
		$this->doPost = 1;
		$this->get_source();
		
		if(!preg_match('/sams\/account\/signin\/login\.jsp|\/maintenance\/index\.html/i', $this->last_url)){
			$this->log("Login successful");
			return true;	
		}
		
		$this->error = 'LOGIN_FAILED';
		$this->log("Login failed | ".$this->last_url);
		return false;
	}
	
	/**
	 * Function to load the cart
	 *
	 * @param n/a
	 * @return n/a
	 */
	public function load_cart()
	{
		$this->log('Loading cart...');
		$this->doPost = 0;
		$this->referer = 'https://www.samsclub.com/sams/homepage.jsp';
		$this->url = 'https://www.samsclub.com/sams/cart/cart.jsp?eventId=scCheckout';
		$this->get_source();
	}
	
	/**
	 * Function to clear all products from cart
	 *
	 * @param n/a
	 * @return true if cart is clear, false if cart is loaded
	 */
	public function clear_cart()
	{
		$this->log('Clearing cart...');
		$this->postData = $this->getFormFields('cartform', 'name');
		
		if(empty($this->postData['/atg/commerce/order/purchase/CartModifierFormHandler.removalDeliveryCommerceIds'])){
			$this->log('No item loaded in cart');
			return true;	
		}
			
		$html = new simple_html_dom();
		$html->load($this->response);
		$in = $html->find('input[class=removalcommmerceids]');
		
		$this->log(count($in).' items found in cart');
		$this->doPost = 1;
		$this->referer = $this->last_url;
		$this->url = 'https://www.samsclub.com/sams/cart/cart.jsp?_DARGS='.urlencode('/sams/cart/cartInclude.jsp');
		
		foreach($in as $f){
			$oid = trim($f->attr['value']);
			$this->log('Clearing order '.$oid);
			$this->postData['/atg/commerce/order/purchase/CartModifierFormHandler.removalDeliveryCommerceIds'] = $oid;
			$this->get_source();
		}	
		
		$html->clear(); 
		unset($html);
		
		$this->postData = $this->getFormFields('cartform', 'name');
		
		if(empty($this->postData['/atg/commerce/order/purchase/CartModifierFormHandler.removalDeliveryCommerceIds'])){
			$this->log('No item loaded in cart');
			return true;	
		}
		$this->log('Item still loaded in cart');
		return false;
	}
	
	/**
	 * Function to search product from samsclub
	 *
	 * @param int $sku the item sku
	 * @return true if success and false if failed
	 */
	public function search_product($sku)
	{
		$this->productSKU = trim($sku);
		$this->postData = $this->getFormFields('searchForm', 'name');
		if(empty($this->postData)){
			$this->log('Serach form not found | '.$this->last_url);
			$this->error = 'SEARCH_FORM_NOT_FOUND';
			return false;	
		}
		$this->postData['/com/walmart/ecommerce/samsclub/search/SearchFormHandler.searchTerm'] = $sku;
		$this->postData['/com/walmart/ecommerce/samsclub/search/SearchFormHandler.searchCategoryId'] = 'all';
		$this->postData['/com/walmart/ecommerce/samsclub/search/typeahead/TypeaheadDroplet.typeaheadOn'] = true;
		$this->postData['requestSchmem'] = 'http';

		
		$this->log("Searching product...");
		$this->doPost = 1;
		$this->referer = 'http://www.samsclub.com/sams/furniture/1286.cp?navTrack=gnav1_furniture&navAction=jump';
		$this->url = 'http://www.samsclub.com/sams/search/searchResults.jsp?searchTerm='.urlencode($sku).'&searchCategoryId=all';
		$this->get_source();
		$this->log('Returned search url '.$this->last_url);
		return true;
	}
	
	/**
	 * Function to load product in cart
	 *
	 * @param n/a
	 * @return true if success and false if failed
	 */
	public function load_product()
	{
		$this->postData = $this->getFormFields('addToCartSingleForm', 'name');
				
		if(empty($this->postData['/atg/commerce/order/purchase/CartModifierFormHandler.catalogRefIds']) || 
			empty($this->postData['/atg/commerce/order/purchase/CartModifierFormHandler.productIds'])){
			$this->log("Item order form not found | ".$this->last_url);
			$this->error = 'ORDER_FORM_NOT_FOUND';
			return false;	
		}
		
		$this->postData['/atg/commerce/order/purchase/CartModifierFormHandler.deliveryQuantitiesMap.0'] = $this->quantity;
		$this->postData['/atg/commerce/order/purchase/CartModifierFormHandler.pickUpQuantitiesMap.0'] = '0';
		$this->productID = $this->postData['/atg/commerce/order/purchase/CartModifierFormHandler.productIds'];

		$this->extract_sams_price();
		
		if(!empty($this->error))return false;
		
		if(empty($this->product_unit_price) && !$this->ignore_price){
			$this->log('Could not determine product price');
			$this->error = 'FAIL_PRODUCT_PRICE';
			return false;	
		}
							
		$this->doPost = 1;
		$this->referer = $this->last_url;
		$this->url = 'http://www.samsclub.com/sams/shop/product.jsp?productId='.$this->productID.'&_DARGS='.urlencode($this->postData['_DARGS']);
		
		//$this->url = 'http://localhost/is-test.php';
		$this->get_source();
		
		
		/*var_dump($this->postData);
		var_dump($this->curl_info);
		echo $this->response;
		exit;*/
		
		if(preg_match('/cart\/addToCartConfirmPage/siU', $this->last_url)){
			$this->log('Successfully added product to cart');
			return true;	
		}
		
		$this->error = 'CART_FAILED';
		$this->log('Failed to cart product | '.$this->last_url);
		return false;
				
	}
	
	/**
	 * Function to extract price from sams club item page
	 *
	 * @param n/a
	 * @return n/a
	 */
		public function extract_sams_price()
	{
		if(preg_match('/<span class="price">([0-9]+)<\/span>\s<span class="superscript">([0-9]+)<\/span>/siU', $this->response, $m)){
			$this->product_unit_price = (float)($m[1].'.'.$m[2]);
			$this->log('Product price of item # '.$this->productSKU.' $'.$this->product_unit_price);
		}
		else if(preg_match('/<span class=price>([0-9]+)<\/span>\s<span class=superscript>([0-9]+)<\/span>/siU', $this->response, $m)){
			$this->product_unit_price = (float)($m[1].'.'.$m[2]);
			$this->log('Product price of item # '.$this->productSKU.' $'.$this->product_unit_price);
		}
		else if(preg_match('/"onlinePrice">\$([0-9\.]+)<\/span>/siU', $this->response, $m)){
			$this->log('Product price of item # '.$this->productSKU.' $'.$m[1]);
			$this->product_unit_price = (float)$m[1];	
		}
		else{
			$this->product_unit_price = 0;	
			$this->log('Product price of item # '.$this->productSKU.' could not be extracted');
		}
		
		if($this->product_unit_paid - $this->product_unit_price < 0){
			if(!$this->ignore_loss){
				$this->log('Item price is higher than selling price. Probably wrong item chosen. Please retry order.');
				$this->error = 'HIGH_PRO_PRICE';
				return false;	
			}
			else $this->log('Item price is higher than selling price. Losses ignored by config.');
		}
	}
	/**
	 * Function to initiate a checkout request
	 *
	 * @param n/a
	 * @return true if success and false if failed
	 */
	public function init_checkout()
	{
		$this->log('Checking out...');
		$this->load_cart();
		$this->postData = $this->getFormFields('checkoutNowForm', 'name');
		
		//var_dump($this->postData);
		
		//echo $this->response;
		if(empty($this->postData)){
			$this->log('No checkout form found | '.$this->url);
			$this->error = 'CHECKOUT_FORM_NOT_FOUND';
			return false;	
		}
		$this->doPost = 1;
		$this->referer = $this->last_url;
		$this->url = 'https://www.samsclub.com/sams/cart/cart.jsp?_DARGS='.urlencode('/sams/cart/checkoutAllItems.jsp');
		$this->get_source();
		
		$this->postData = $this->getFormFields('addNewAddress', 'name');
		
		if(empty($this->postData)){
			$this->log('No add shipping address form found | '.$this->last_url);
			$this->error = 'ADD_SHIPPING_FORM_NOT_FOUND';
			return false;	
		}
		return false;
	}
	
	/**
	 * Function to convert 2 letter state code to full state
	 * 
	 * @param state letters
	 * @return state after processing
	 * Use "states.txt" for 2 letter state names
	 * Use "states_full_state_name_for_partial_states.txt" for full state names on specific states
	 */
	 public function convert_state($state)
	 {
		$states = array();
		$state_up = strtoupper($state);
		$f = dirname(dirname(__FILE__)).'/logs/states.txt';
		if(file_exists($f)){
			$ll = file($f);
			foreach($ll as $l){
				$l = trim(strtoupper($l));
				$l = explode('|', $l);
				if($l[0] == $state_up || $l[1] == $state_up)return $l[2];	
			} 
		}
		return $state; 	 
	 }
	
	
	/**
	 * Function to login to Samsclub account
	 *
	 * @param string $name full name of the buyer
	 * @param string $street1 street1 address
	 * @param string $street2 street2 address
	 * @param string $city city name
	 * @param string $state state code i.e. OH, NC
	 * @param string $zip zip code i.e. 28719
	 * @param string $phone phone number of the buyer i.e. 123 456 7890 must be separated by spaces
	 * @return true if success and false if failed
	 */
	public function load_shipping_address($name, $street1, $street2, $city, $state, $zip, $phone)
	{
		$this->postData = $this->getFormFields('addNewAddress', 'name');
		$state = $this->convert_state($state);
		$state = preg_replace('/[^a-z\s]/i', '', $state);
		$this->state = $state;
		$this->log('Formatted state name '.$state);
		
		$city = preg_replace('/St\./i', 'Saint', $city);
		$city = preg_replace('/[^a-z\s]/i', '', $city);
		$this->city = $city;
		$this->log('Formatted city name '.$city);
		
		$street1 = preg_replace('/\&/i', 'and', $street1);
		$this->log('Formatted street1 name '.$street1);
		$this->street1 = $street1;
		
		$street2 = preg_replace('/\&/i', 'and', $street2);
		$this->log('Formatted street2 name '.$street2);
		
		if(empty($this->postData)){
			$this->log('No add shipping address form found | '.$this->last_url);
			$this->error = 'ADD_SHIPPING_FORM_NOT_FOUND';
			return false;	
		}
		
		$this->log('Formatted buyer name '.$name);

		$name = explode(' ', $name);
		$first_name = trim($name[0]);
		
		if(count($name) > 1){
			$tmp = $name;
			array_shift($tmp);
			$last_name = implode(' ', $tmp);
		}
		else $last_name = $first_name;
		
		$this->log('Formatted buyer last name '.$last_name);
		
		$middle_name = '';
		/*if(!empty($name[1])){
			if(strtolower($name[1]) != strtolower($last_name) && strtolower($name[1]) != strtolower($first_name)){
				$mn = substr(trim($name[1]), 0, 1);
				if(!preg_match('/[^a-z]/i', $mn))$middle_name = strtoupper($mn);
			}
		}*/
		
		if(empty($phone) || strlen($phone) < 10){
			$phone = SAMS_ORDER_DEFAULT_PHONE;
		}
		//$phone = preg_replace('/\s{2,}/', ' ', $phone);
		$phone = trim(preg_replace('/\s/', '', $phone));
		$this->log('Using buyer phone '.$phone);
		
		if(preg_match('/\s/', $phone))$phone = explode(' ', $phone);
		else{
			$p = array();
			$p[0] = substr($phone, 0, 3);
			$p[1] = substr($phone, 3, 3);
			$p[2] = substr($phone, 6, 4);
			if(empty($p[0]) || empty($p[1]) || empty($p[2])){
				$phone = explode(' ', SAMS_ORDER_DEFAULT_PHONE);	
			}
			else $phone = $p;	
		}
		
		//var_dump($phone);
		
		$this->postData['type'] = 'Residential';
		$this->postData['fName'] = $first_name;
		$this->postData['mName'] = $middle_name;
		$this->postData['lName'] = $last_name;
		$this->postData['stAdd'] = $street1;
		$this->postData['add2'] = $street2;
		$this->postData['city'] = trim($city);
		$this->postData['state'] = trim($state);
		$this->postData['zip'] = trim($zip);
		$this->postData['p_num'] = trim($phone[0]);
		$this->postData['p_num2'] = trim($phone[1]);
		$this->postData['p_num3'] = trim($phone[2]);
		
		$this->postData['/atg/commerce/order/purchase/ShippingGroupFormHandler.addAddressToAllItemsFlag'] = 'Y';
		$this->postData['/atg/commerce/order/purchase/ShippingGroupFormHandler.formValues.suffix'] = '';
		$this->postData['/atg/commerce/order/purchase/ShippingGroupFormHandler.addShippingAddress'] = 'submit';
		
		/*$this->postData['/atg/commerce/order/purchase/ShippingGroupFormHandler.applyShippingGroupToItemSuccessURL'] = '/sams/checkout/shipping/shipping.jsp';
		$this->postData['/atg/commerce/order/purchase/ShippingGroupFormHandler.applyShippingGroupToItemErrorURL'] = '/sams/checkout/shipping/shipping.jsp';*/
		
		$this->postData['/atg/commerce/order/purchase/ShippingGroupFormHandler.addShippingAddressErrorURL'] = '/sams/checkout/shipping/shipping.jsp';
		$this->postData['/atg/commerce/order/purchase/ShippingGroupFormHandler.addShippingAddressSuccessURL'] = '/sams/checkout/shipping/shipping.jsp';
		$this->postData['/atg/commerce/order/purchase/ShippingGroupFormHandler.addShippingAddressSuccessURL'] = '/sams/checkout/shipping/shipping.jsp';
		
		unset($this->postData['/atg/commerce/order/purchase/ShippingGroupFormHandler.applyDefaultShippingGroup']);
		unset($this->postData['/atg/commerce/order/purchase/ShippingGroupFormHandler.saveShippingAddress']);
		unset($this->postData['dockDoorPresentCheckBox']);		
		
		//var_dump($this->postData);
		//exit;
		//file_put_contents('b.txt', $this->response);
		//echo '<pre>';
		//var_dump($this->postData);

		$this->doPost = 1;
		$this->referer = $this->last_url;
		$this->url = 'https://www.samsclub.com/sams/checkout/shipping/shipping.jsp?_DARGS='.urlencode('/sams/checkout/shipping/add_shipping_address_overlay.jsp');
		$this->get_source();
		
		//echo $this->response;
		//exit;
	
		$html = new simple_html_dom();
		$html->load($this->response);
		$alt = $this->getFormFields('shippingSaveAnyWayForm', 'name');
		
		if(!empty($alt)){
			$this->log('Saving shipping address anyway...');
			$this->postData = $alt;
			$this->postData['succUrl'] 
				= $this->postData['/atg/commerce/order/purchase/ShippingGroupFormHandler.addShippingAddressSuccessURL'] 
				= '/sams/checkout/shipping/shipping.jsp';
			$this->postData['/atg/commerce/order/purchase/ShippingGroupFormHandler.saveAnyway'] = 'true';
			
			foreach($this->postData as &$d){
				$d = trim($d);
				if($d == 1)$d = 'true';
			}
			
			$this->doPost = 1;
			$this->referer = $this->last_url;
			$this->url = 'https://www.samsclub.com/sams/checkout/shipping/shipping_address_save_any_way.jsp?_DARGS='.urlencode('/sams/checkout/shipping/shipping_address_save_any_way.jsp');
			$this->get_source();
			
			$html->clear(); 
			unset($html);
			
			$html = new simple_html_dom();
			$html->load($this->response);		
		}
		
		$opt = $html->find('select[id=defaultAddrsId]',0);
		if(!empty($opt))$opt = $opt->find('option[selected=selected]', 0);
		
		if(empty($opt)){
			$msg = '';
			$err = $html->find('div[class=shipInerr]', 0);
			if(!empty($err))$msg = $err->text();
			$this->log('Failed to load shipping address | '.$this->last_url.' | options not found | Error message: '.$msg);
			$this->error = 'FAIL_LOAD_SHIP_ADDR';
			$html->clear(); 
			unset($html);
			return false;
		}
		
		$opt = trim(preg_replace('/[^a-zA-Z0-9\s]/i', ' ', trim($opt->text())));
		$optm = trim(preg_replace('/[^a-zA-Z0-9\s]/i', ' ', $street1));
		
		$opt = preg_replace('/\s{2,}/', ' ', $opt);
		$optm = preg_replace('/\s{2,}/', ' ', $optm);
		
		$html->clear(); 
		unset($html);
		
		$this->log("OPT: '{$opt}' OPTM: '{$optm}'");
		
		if(preg_match('/Ship to '.$optm.'/siU', $opt)){
			$this->log('Successfully loaded shipping address');
			return true;	
		}
		
		$this->log('Failed to load shipping address | '.$this->last_url.' | could not verify');
		$this->error = 'FAIL_LOAD_SHIP_ADDR';
		return false;
	}
	
	/**
	 * Function to confirm order
	 *
	 * confirms shipping address, tax exempt and payment method
	 * @param n/a
	 * @return true if success and false if failed
	 */
	public function confirm_order()
	{
		//confirming shipping address
		$this->log('Confirming shipping address...');
		$this->postData = $this->getFormFields('continueForm', 'name');
		
		if(empty($this->postData)){
			$this->log('No confirm shipping address form found | '.$this->url);
			$this->error = 'CONFIRM_SHIPPING_FORM_NOT_FOUND';
			return false;	
		}
		
		$html = new simple_html_dom();
		$html->load($this->response);
		$form = $html->find('input[class=check-exempt]', 0);
		
		$taxExempt = '';
		if(!empty($form))$taxExempt = trim($form->attr['value']);	
		
		$html->clear(); 
		unset($html);
		
		if(empty($taxExempt)){
			$this->log('Tax exempt rel id not found | '.$this->last_url);
			$this->error = 'TAX_EXEMPT_ID_NOT_FOUND';
			return false;
		}
		
		$this->postData['/atg/commerce/order/purchase/ShippingGroupFormHandler.userTaxExempt'] = 'true';
		$this->postData['taxExemptRequired'] = $taxExempt;
		$this->postData['taxExempt'] = 'agree';
		$this->postData['_D:taxExempt'] = '';
		$this->postData['_DARGS'] = '/sams/checkout/shipping/shipping_include.jsp.4';
				
		$this->referer = $this->last_url;
		$this->doPost = 1;
		$this->url = 'https://www.samsclub.com/sams/checkout/payment/payment.jsp';
		$this->get_source();
		
		//var_dump($this->postData);
		//echo $this->response;
		//exit;
		
		//confirming visa payment method
		$this->log('Confirming order...');
		$this->postData = $this->getFormFields('singlePayForm', 'name');

		if(empty($this->postData)){
			$this->log('No confirm order form found | '.$this->url);
			$this->error = 'CONFIRM_ORDER_FORM_NOT_FOUND';
			return false;	
		}
		
		$this->referer = $this->last_url;
		$this->doPost = 1;
		$this->url = 'https://www.samsclub.com/sams/checkout/orderconfirm/orderConfirmation.jsp?_DARGS='.urlencode($this->postData['_DARGS']);
		$this->get_source();

	}
	
	/**
	 * Function to make final order
	 *
	 * verifies order status
	 * makes final order and pays for the product
	 * @param n/a
	 * @return true if success and false if failed
	 */
	public function make_final_order()
	{
		//final place order
		$html = new simple_html_dom();
		$html->load($this->response);
		//echo $this->response;exit;
		$data = $html->find('span[class=item_no]', 0);
		//		echo "data=";
		//		print_r($data);
		if(method_exists($data, 'text')){
			$item_no = trim($data->text());
		}
		else{
			file_put_contents(dirname(dirname(__FILE__)).'/logs/fnorder.html', $this->response);
			$this->log('HTML not recognized');
			return false;	
		}
		if($item_no != $this->productSKU){
			$this->log('Failed to verify ordered product SKU '.$item_no.'|'.$this->productSKU);	
			//$this->error = 'FAIL_EXT_ORDER_SKU';
			//return false;
		}
		else $this->log('Product SKU verified # '.$item_no);
		
		$data = $html->find('div[id=qty1]', 0);
		$qty = (int)trim($data->text());
		if($qty != $this->quantity){
			$this->log('Failed to verify ordered product quantity # '.$qty.'|'.$this->quantity);	
			$this->error = 'FAIL_EXT_ORDER_QTY';
			return false;
		}
		else $this->log('Product qunatity verified # '.$qty);
		
		$data = $html->find('dl[class=address]', 0);
		
		//address extracted from order
		$addr = strip_tags(html_entity_decode(trim($data->text())));
		$addr = preg_replace('/Change$/', '', $addr);
		$addr2 = $addr;
		$addr2 = preg_replace('/\broad\b|\brd\b/i', '', $addr2);
		$addr2 = preg_replace('/[^a-z0-9\s\/\\\]/i', '', $addr2);
		$addr2 = trim(preg_replace('/\s{2,}/', ' ', $addr2));
		//$addr2 = preg_replace('/(?<=\d)\s+(?=\d)/', '', $addr2); //replace spaces between two numbers
		
		//to match street address
		$addr3 = preg_replace('/\s/', '', $addr2);
		
		//main shipping
		$s1 = preg_replace('/\broad\b|\brd\b/i', '', $this->street1);
		$s1 = preg_replace('/[^a-z0-9\/\\\]/i', '', $s1);
		
		$s1 = preg_quote($s1, '/');
		$n = preg_quote(preg_replace('/[^a-z0-9]/i', '',$this->buyer_f_name), '/');
		$c = preg_quote($this->city, '/');
		$s = preg_quote($this->state, '/');
		$p = preg_quote($this->post_code, '/');
		
		if( !preg_match("/$s1/siU", $addr3) || 
			!preg_match("/$n/siU", $addr2) || 
			!preg_match("/$c/siU", $addr2) || 
			!preg_match("/$s/siU", $addr2) || 
			!preg_match("/$p/siU", $addr2)){
			$this->log('Failed to verify ordered product shipping address [ EXTRACTED # "'.$addr.'"]|[ FORMATTED # "'.$addr2.'" AND "'.$addr3.'"]|[ FROM DB # "'.$s1.'"][ NAME # "'.$n.'"][ CITY # "'.$c.'"][ STATE # "'.$s.'"][ PC # "'.$p.'"]');	
			$this->error = 'FAIL_EXT_ORDER_ADDR';
			return false;
		}
		else $this->log('Product shipping address verified [ EXTRACTED # "'.$addr.'"]|[ FORMATTED # "'.$addr2.'" AND "'.$addr3.'"]|[ FROM DB # "'.$s1.'"][ NAME # "'.$n.'"][ CITY # "'.$c.'"][ STATE # "'.$s.'"][ PC # "'.$p.'"]');
		
		if(!preg_match("/>no tax</siU", $this->response) && !$this->ignore_tax){
			$this->log('Failed to verify ordered product sales tax exempt');	
			$this->error = 'FAIL_EXT_ORDER_TAX';
			return false;
		}
		else $this->log('Product exempted from sales tax');
				
		$data = $html->find('div[class=price]', 1);
		$price = (float)str_replace('$','',trim($data->text()));
		$eprice = $this->product_unit_price*$this->quantity;
		if(abs($price - $eprice) > 0.5 && !$this->ignore_loss && !$this->ignore_price){
			$this->log('Failed to verify ordered product price # '.$price.'|'.$eprice);	
			$this->error = 'FAIL_EXT_ORDER_PRICE';
			return false;
		}
		else $this->log('Product price verified # '.$price.'|'.$eprice);
		
		$data = $html->find('span[class=total]', 0);
		if(!empty($data)){
			$total = (float)preg_replace('/[^0-9\.]/siU', '' ,trim($data->text()));
			$this->log('Total order cost # '.$total);
			$this->total_cost = $total;
		}
		
		$html->clear(); 
		unset($html);
		
		$this->postData = $this->getFormFields('confirmOrder', 'name');
		
		if(empty($this->postData['/atg/commerce/order/purchase/CommitOrderFormHandler.orderId'])){
			$this->log('Order placement form not found | '.$this->last_url);	
			$this->error = 'ORDER_PLACE_FORM_NOT_FOUND';
			return false;	
		}
		
		$this->log('Pre-Sales order id '.$this->postData['/atg/commerce/order/purchase/CommitOrderFormHandler.orderId']);
		$this->doPost = 1;
		$this->referer = $this->last_url;
		$this->url = 'https://www.samsclub.com/sams/checkout/orderconfirm/orderConfirmation.jsp?_DARGS='.urlencode($this->postData['_DARGS']);
		
		//var_dump($this->postData);
		//echo $this->response;exit;
		/*WARNING: set get_source() will place order*/
		$this->get_source();
		//$this->response = file_get_contents(dirname(__FILE__).'/a.txt');
		if(preg_match('/Thank you, your order is complete/siU', $this->response)){
			$this->log('Order complete');
			$this->sales_order_id = $this->postData['/atg/commerce/order/purchase/CommitOrderFormHandler.orderId'];
			$this->log('Sales order id '.$this->sales_order_id);
			return true;	
		}
		else{
			$this->log('Could not verify order item');
			$this->error = 'ORDER_VRY_ERR_'.$this->postData['/atg/commerce/order/purchase/CommitOrderFormHandler.orderId'];
			return false;	
		}
	}
	
	/**
	 * Function to find the order info including tracking number
	 *
	 * Finds the post order information from SamsClub
	 * Requires the order id to be set in class
	 * @param n/a
	 * @return n/a
	 */
	public function collect_order_info()
	{
		$this->tracking_no = '';
		$this->tracking_carrier = '';
		$this->tracking_url = '';
		
		$this->error = '';
		$this->url = 'https://www.samsclub.com/sams/shoppingtools/orderhistory/orderDetailsPage.jsp?orderId='.$this->sales_order_id;
		$this->doPost = 0;
		$this->referer = 'https://www.samsclub.com/sams/shoppingtools/orderhistory/orderHistory.jsp';
		$this->get_source();
		
		if(preg_match('/account\/signin\/login\.jsp/i', $this->last_url)){
			$this->error = '';
			$this->login(1);	
			if(!empty($this->error))return false;
			
			$this->url = 'https://www.samsclub.com/sams/shoppingtools/orderhistory/orderDetailsPage.jsp?orderId='.$this->sales_order_id;
			$this->doPost = 0;
			$this->referer = 'https://www.samsclub.com/sams/shoppingtools/orderhistory/orderHistory.jsp';
			$this->get_source();
		}
		
		
		$html = new simple_html_dom();
		
		if(!preg_match('/Tracking Number/siU', $this->response, $m))$this->log('Tracking number not added yet');
		else{
			if(preg_match('/<strong>Tracking Number(.*)<strong>/siU', $this->response, $m)){
				$html->load($m[1]);
				$a = $html->find('a[class=blueLinks2]', 0);
				if(!empty($a)){
					$this->tracking_url = $a->attr['href'];
					$this->tracking_no = $a->text();
					if(preg_match('/([a-z0-9]+)\.(.*)\.com/siU', $this->tracking_url, $m))$this->tracking_carrier = ucwords($m[2]);
					else $this->tracking_carrier = 'Undefined';
					$this->log('Tracking number found for order id #'.$this->sales_order_id.', tracking no #'.$this->tracking_no.', tracking url : '.$this->tracking_url.', tracking carrier : '.$this->tracking_carrier);
				}
				else{
					$this->error = 'TRACKING_ID_NOT_FOUND';
					$this->log('Failed to find tracking number');	
				}
			}	
		}
		
		$html->load($this->response);
		$td = $html->find('td[class=brdr]');
		$count = count($td);
		$data = array();
		foreach($td as $i => $t){
			$t = trim(preg_replace('/\s{2,}|\&([a-z]+);/', ' ', strip_tags($t->text())));
			$data[$i] = $t; 	
		}
		$tmp = array_slice($data, 0, $count/2);
		$tmp2 = array_slice($data, $count/2, 100);
		
		$priv_note = 'ORDER ID # '.$this->sales_order_id."\r\n";
		foreach($tmp as $i=>$v){
			$priv_note .= $v.' # '.$tmp2[$i]."\r\n";
			if(preg_match('/STATUS/i', $v)){
				$this->order_status = $tmp2[$i];
				$this->log('ORDER STATUS: '.$this->order_status);
			}
			else if(preg_match('/SHIPPING /i', $v)){
				$this->shipment_status = $tmp2[$i];
				$this->log('SHIP STATUS: '.$this->shipment_status);
			}
		}
		$this->priv_note = $priv_note;
		$this->log('Notes: '.$this->priv_note);
		
		$html->clear(); 
		unset($html);
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
	 * Function to get page source
	 *
	 * depens on loaded url, cookie file, postData and doPost
	 * saves response to response
	 * @param n/a
	 * @return n/a
	 */	
	public function get_source()
	{
		$this->log("Requesting ".$this->url.' '.($this->doPost ? 'Request type: POST' : 'Request type: GET'));
		$ch=curl_init($this->url);
		curl_setopt($ch, CURLOPT_HEADER, 0);	
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);	
		curl_setopt($ch, CURLOPT_REFERER, $this->referer);	
		curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);	
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);	
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);	
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie);
		if($this->doPost){
			if(is_array($this->postData))$post = http_build_query($this->postData, '', '&');
			else $post = $this->postData;
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}
		$data=curl_exec($ch);
		$data=preg_replace('/\s{2,}/',' ',$data);
		$this->response=$data;
		$info=curl_getinfo($ch);
		$this->curl_info=$info;
		$this->last_url=$info['url'];
		curl_close($ch);
		$this->log('Last request url : '.$this->last_url.'. HTTP response: '.$this->curl_info['http_code']);
	}
	
	/**
	 * Function to clear cookie
	 *
	 * @param n/a
	 * @return n/a
	 */
	public function clear_cookie()
	{
		@unlink($this->cookie);
		
	}
	
	/**
	 * Function to clear log
	 *
	 * @param n/a
	 * @return n/a
	 */
	public function clear_log()
	{
		@unlink($this->log_file);
	}
	
	/**
	 * Function to log debug strings
	 *
	 * @param string $str the string to log
	 * @return n/a
	 */
	public function log($str)
	{
		$fp=fopen($this->log_file, "a");
		fwrite($fp, date('[d-M-Y H:i:s]')." $str\r\n");
		fclose($fp);
		
		echo $str."<br/>";
		@flush();
		@ob_flush();
		
	}
	
}

?>