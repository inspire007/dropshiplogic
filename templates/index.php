<?php

$stats = new Stats_Manager();
$salesToday = $stats->getDailySales('today');
$salesYesterday = $stats->getDailySales('yesterday');
$totalSales = $stats->getDailySales('all');
$profitToday =  $stats->getDailyProfit('today');
$profitYesterday =  $stats->getDailyProfit('yesterday');
$profitAll =  $stats->getDailyProfit('all');

?>
<div class="container-fluid">
    <!-- BEGIN PAGE HEADER-->
    <div class="row-fluid">
        <div class="span12">
            <!-- BEGIN PAGE TITLE & BREADCRUMB-->			
            <h3 class="page-title">
                Dashboard				
                <small>latest sales</small>
                <div class="pull-right"><?php echo date('d-M H:i')?></div>
            </h3>
            <ul class="breadcrumb">
                <li>
                    <i class="icon-home"></i>
                    <a href="index.php">Home</a> 
                    <i class="icon-angle-right"></i>
                </li>
                <li><a href="index.php">Dashboard</a></li>
            </ul>
            <!-- END PAGE TITLE & BREADCRUMB-->
        </div>
    </div>
    <!-- END PAGE HEADER-->
    <div id="dashboard">
        <!-- BEGIN DASHBOARD STATS -->
        <div class="row-fluid">
            <div class="span3 responsive" data-tablet="span6" data-desktop="span3">
                <div class="dashboard-stat blue">
                    <div class="visual">
                        <i class="icon-comments"></i>
                    </div>
                    <div class="details">
                        <div class="number">
                            <?php echo $totalSales?>
                        </div>
                        <div class="desc">									
                            Total Sales
                        </div>
                    </div>
                    <a class="more" href="stats.php">
                    View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>						
                </div>
            </div>
            <div class="span3 responsive" data-tablet="span6" data-desktop="span3">
                <div class="dashboard-stat green">
                    <div class="visual">
                        <i class="icon-shopping-cart"></i>
                    </div>
                    <div class="details">
                        <div class="number"><?php echo $salesToday?></div>
                        <div class="desc">New Sales Today</div>
                    </div>
                    <a class="more" href="stats.php">
                    View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>						
                </div>
            </div>
            <div class="span3 responsive" data-tablet="span6  fix-offset" data-desktop="span3">
                <div class="dashboard-stat purple">
                    <div class="visual">
                        <i class="icon-globe"></i>
                    </div>
                    <div class="details">
                        <div class="number"><?php echo $profitToday?>$</div>
                        <div class="desc">Profit Today</div>
                    </div>
                    <a class="more" href="stats.php">
                    View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>						
                </div>
            </div>
            <div class="span3 responsive" data-tablet="span6" data-desktop="span3">
                <div class="dashboard-stat yellow">
                    <div class="visual">
                        <i class="icon-bar-chart"></i>
                    </div>
                    <div class="details">
                        <div class="number"><?php echo $profitAll?>$</div>
                        <div class="desc">Total Profit</div>
                    </div>
                    <a class="more" href="stats.php">
                    View more <i class="m-icon-swapright m-icon-white"></i>
                    </a>						
                </div>
            </div>
        </div>
        <!-- END DASHBOARD STATS -->
        
        <div class="alert alert-success module-stats">
        	<b class="last-up">Last Updates</b> : 
            AmazonOrderSync : <span class="last-up"><?php echo file_exists(dirname(dirname(__FILE__)).'/logs/amazon-log.txt') ? 
								(int)((time() - filemtime(dirname(dirname(__FILE__)).'/logs/amazon-log.txt')) / 60).' min ago' : 'N/A'?></span>
            EbayOrderSync : <span class="last-up"><?php echo file_exists(dirname(dirname(__FILE__)).'/logs/ebay-log.txt') ? 
								(int)((time() - filemtime(dirname(dirname(__FILE__)).'/logs/ebay-log.txt')) / 60).' min ago' : 'N/A'?></span>
            AmazonPostOrderSync : <span class="last-up"><?php echo file_exists(dirname(dirname(__FILE__)).'/logs/amazon-postorder-log.txt') ? 
								(int)((time() - filemtime(dirname(dirname(__FILE__)).'/logs/amazon-postorder-log.txt')) / 60).' min ago' : 'N/A'?></span>
            EbayPostOrderSync : <span class="last-up"><?php echo file_exists(dirname(dirname(__FILE__)).'/logs/ebay-postorder-log.txt') ? 
								(int)((time() - filemtime(dirname(dirname(__FILE__)).'/logs/ebay-postorder-log.txt')) / 60).' min ago' : 'N/A'?></span>
            SamsOrders : <span class="last-up"><?php echo file_exists(dirname(dirname(__FILE__)).'/logs/sams-log.txt') ? 
								(int)((time() - filemtime(dirname(dirname(__FILE__)).'/logs/sams-log.txt')) / 60).' min ago' : 'N/A'?></span>
        </div>
        
        <?php
		//prepare sales entry
		
		if(!empty($_GET['fromDate']) && !preg_match('/[^0-9\-]/', $_GET['fromDate'])){
			$startDate = mysql_real_escape_string($_GET['fromDate']);	
		}
		
		if(!empty($_GET['toDate']) && !preg_match('/[^0-9\-]/', $_GET['toDate'])){
			$endDate = mysql_real_escape_string($_GET['toDate']);	
		}
		
		if(!empty($startDate) && !empty($endDate)){
			$qTime = 'from '.$startDate.' to '.$endDate;
			$qTime_sql = "added_at > '$startDate' AND added_at < '$endDate'";	
		}
		else if(!empty($startDate)){
			$qTime = 'from '.$startDate;
			$qTime_sql = "added_at LIKE '$startDate%'";	
		}
		else{
			$startDate = date('Y-m-d');
			$endDate = '';
			$qTime = 'today';	
			$qTime_sql = "added_at LIKE '$startDate%'";
		}
		
		if(empty($_GET['fromDate'])){
			$qTime = 'today';	
		}
		else if(!preg_match('/[^0-9\-]/', $_GET['fromDate'])){
			$qTime = 'from '.$_GET['fromDate'];
			$qTime_sql = mysql_real_escape_string($_GET['fromDate']);	
		}
		
		//search parameters
		
		if(!empty($_GET['ignoreDate']) || !empty($settings['show_all_orders'])){
			$qTime = 'found';	
			$qTime_sql = "1";
		}
		
		if(!empty($_GET['OrderID']))$OrderID = "order_id LIKE '%".mysql_real_escape_string($_GET['OrderID'])."%'";
		else $OrderID = 1;
		
		if(!empty($_GET['ProductTitle']))$ProductTitle = "item_title LIKE '%".mysql_real_escape_string($_GET['ProductTitle'])."%'";
		else $ProductTitle = 1;
		
		if(!empty($_GET['BuyerName']))$BuyerName = "name LIKE '%".mysql_real_escape_string($_GET['BuyerName'])."%'";
		else $BuyerName = 1;
		
		if(!empty($_GET['BuyerID']))$BuyerID = "buyer_user_id LIKE '%".mysql_real_escape_string($_GET['BuyerID'])."%'";
		else $BuyerID = 1;
		
		if(!empty($_GET['DropShipOrderID']))$DropShipOrderID = "dropship_orderid LIKE '%".mysql_real_escape_string($_GET['DropShipOrderID'])."%'";
		else $DropShipOrderID = 1;
		
		if(!empty($_GET['DropShipSKU']))$DropShipSKU = "item_sku LIKE '%".mysql_real_escape_string($_GET['DropShipSKU'])."%'";
		else $DropShipSKU = 1;
		
		if(!empty($_GET['SalesChannel']))$SalesChannel = "sales_channel = '".mysql_real_escape_string($_GET['SalesChannel'])."'";
		else $SalesChannel = 1;
		
		if(!empty($_GET['Country']))$Country = "(country LIKE '%".mysql_real_escape_string($_GET['Country'])."%' OR country_name LIKE '%".mysql_real_escape_string($_GET['Country'])."%')";
		else $Country = 1;
		
		if(!empty($_GET['OrderStatus'])){
			if($_GET['OrderStatus'] == 'queued')$OrderStatus = "(dropship_done = 0 AND process_at != '0000-00-00 00:00:00')";
			else if($_GET['OrderStatus'] == 'pending')$OrderStatus = "(dropship_done = 0 AND process_at = '0000-00-00 00:00:00')";
			else if($_GET['OrderStatus'] == 'justordered')$OrderStatus = '(dropship_done = 1)';
			else if($_GET['OrderStatus'] == 'ordered')$OrderStatus = '(dropship_done = 2)';
			else if($_GET['OrderStatus'] == 'shipped')$OrderStatus = '(dropship_done = 3)';
			else if($_GET['OrderStatus'] == 'failed')$OrderStatus = '(dropship_done = 100 OR dropship_done = 200)';	
			else $OrderStatus = 1;
		}
		else $OrderStatus = 1;
		
		if(!empty($_GET['SortBy'])){
			switch($_GET['SortBy']):
				case "OrderDateASC":
				$SortBy = 'ORDER BY sales_time ASC';
				break;
				case "OrderDateDESC":
				$SortBy = 'ORDER BY sales_time DESC';
				break;
				default:
				$SortBy = 'ORDER BY id DESC';
			endswitch;	
		}
		else $SortBy = 'ORDER BY id DESC';
		
		if(!empty($_GET['from']))$from = (int)$_GET['from'];
		else $from = 1;
		
		$from--;
		if($from < 0)$from = 0;
		
		if(empty($_GET))$qu = 'view=1';
		else $qu = http_build_query($_GET);
		
		if(!empty($_GET['PerPage']) && is_numeric($_GET['PerPage']) && $_GET['PerPage'] >= 5)$rows = (int)$_GET['PerPage'];
		else $rows = 50;
		
		$q = mysql_query("SELECT NULL FROM ebay_orders WHERE $qTime_sql AND $OrderID AND $ProductTitle AND $BuyerName AND $BuyerID AND $DropShipOrderID AND $DropShipSKU AND $Country AND $OrderStatus AND $SalesChannel AND dropship_done != 500");
		$count = mysql_num_rows($q);
		$q = mysql_query("SELECT * FROM ebay_orders WHERE $qTime_sql AND $OrderID AND $ProductTitle AND $BuyerName AND $BuyerID AND $DropShipOrderID AND $DropShipSKU AND $Country AND $OrderStatus AND $SalesChannel AND dropship_done != 500 $SortBy LIMIT $from, $rows");
		?>
        
        <div class="clearfix"></div>
        <div class="row-fluid" style="display: none" id="main_container">
            <div class="span12">
                <!-- BEGIN PORTLET-->
                <div class="portlet box solid bordered blue">
                    <div class="portlet-title">
                        <h4><i class="icon-money"></i>Latest sales 
                        	<small>Total <?php echo $count?> sales <?php echo $qTime;?>&nbsp;&nbsp;[* marked columns are editable]</small>
                        </h4>
                        	<div class="actions">
                            	<select type="checkbox" id="perPage" style="width:100px" onchange="window.location.href='index.php?PerPage='+this.value">
                                	<option value="">PerPage</option>
                                    <option value="50">50</option>
                                    <option value="75">75</option>
                                    <option value="100">100</option>
                                    <option value="150">150</option>
                                    <option value="200">200</option>
                                </select>
                                <a href="#myModal1" class="btn purple" data-toggle="modal"><i class="icon-search"></i> Search</a>
                                <a href="#myModal2" class="btn yellow" data-toggle="modal"><i class="icon-download"></i> Export</a>
                                <a href="#myModal3" class="btn red" data-toggle="modal"><i class="icon-plus"></i> Add</a>
                                <a href="#myModal4" class="btn black" data-toggle="modal"><i class="icon-plus"></i> Import</a>
                                <div class="btn-group">
                                    <a class="btn green" href="#" data-toggle="dropdown">
                                    <i class="icon-cogs"></i> Options
                                    <i class="icon-angle-down"></i>
                                    </a>
                                    <ul class="dropdown-menu pull-right">
                                        <li><a href="javascript:void(0)" class="export_seller_list"><i class="icon-download"></i> Export to csv</a></li>
                                        <li><a href="javascript:void(0)" class="process_seller_list"><i class="icon-check"></i> Add to queue</a></li>
                                        <li><a href="javascript:void(0)" class="suspend_seller_list"><i class="icon-ban-circle"></i> Suspend orders</a></li>
                                        <li><a href="javascript:void(0)" class="retry_seller_list"><i class="icon-refresh"></i> Retry failed orders</a></li>
                                        <li><a href="javascript:void(0)" class="del_seller_list"><i class="icon-trash"></i> Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                    </div>
                    <div class="portlet-body" id="main-listing" style="color: black; overflow: auto">
                        <table class="table table-hover table-bordered main-listing">
                        	<thead>
                            	<tr>
                                	<th>
                                    	<input type="checkbox" 
                                        onclick="if($(this).is(':checked') == true){$('.sales-record').prop('checked', true);$('.sales-record').parent().addClass('checked');}
                                        else {$('.sales-record').prop('checked', false);$('.sales-record').parent().removeClass('checked');}"/>
                                        #
                                    </th>
                                    <th>Channel</th>
                                    <th>OrderID</th>
                                    <th>ItemName</th>
                                    <th>ItemID</th>
                                    <th>Status</th>
                                    <th>SKU*</th>
                                    <th>Qty*</th>
                                    <th>Sales*</th>
                                    <th>Price*</th>
                                    <th>SalesTax</th>
                                    <th>Fees*</th>
                                    <th>Profit*</th>
                                    <th>OrderedAt</th>
                                    <th>Name*</th>
                                    <th>Email</th>
                                    <th>Username</th>
                                    <th>Street*</th>
                                    <th>City*</th>
                                    <th>State*</th>
                                    <th>PostCode*</th>
                                    <th>Country</th>
                                    <th>Phone*</th>
                                    <th>Vendor*</th>
                                    <th>VendorOrderID*</th>
                                    <th>TrackingNumber*</th>
                                    <th>Notes*</th>
                                 </tr>
                            </thead>
                            <tbody>
                            	<?php
								if(!$count){
									echo '<tr>
											<td colspan="30">
												<div class="alert alert-error"><i class="icon-black icon-remove"></i>&nbsp;&nbsp;&nbsp;<b>No sales record found</b></div>
											</td>
										</tr>';	
								}
								else{
									while($res = mysql_fetch_assoc($q)){
										
										list($inStock) = mysql_fetch_row(mysql_query("SELECT quantity FROM ebay_items WHERE sku = '".mysql_real_escape_string($res['item_sku'])."'"));
										
										if(preg_match('/-/', $res['order_id']))list(,$transid) = explode('-', $res['order_id']);
										else $transid = $res['order_id'];
										
										echo '<tr id="record-'.$res['id'].'" style="overflow: auto">
												<td>
												   	<div style="width:70px">	
													 	<input type="checkbox" class="sales-record" value="'.$res['id'].'" /><br/>'.$res['id'].'<br/>
														'.($inStock == null ? '<span class="label label-inverse"><b>Unavailable</b></span>' : 
															($inStock == 0 ? '<span class="label label-warning"><b>OutOfStock</b></span>' : 
																'<span class="label label-success"><b>InStock</b></span>')).'
												   	</div>
											   </td>
											   <td>
												   	<div style="width:60px">	
													 	'.$res['sales_channel'].'
												   	</div>
											   </td>
											   '.($res['sales_channel'] == 'Ebay' ? '	
												<td>
													<div style="width:100px">
														<a href="http://k2b-bulk'.(EBAY_SANDBOX ? '.sandbox' : '').
														'.ebay.com/ws/eBayISAPI.dll?EditSalesRecord&transid='.$transid.'&itemid='.$res['item_id'].'" 
														target="_blank">'.$transid.'</a>
													</div>
												</td>'
												:
												'<td>
													<div style="width:100px">
														<a href="
															https://sellercentral.amazon.com/gp/orders-v2/details/ref=sm_orddet_cont_myo?ie=UTF8&orderID='.$res['order_id'].'" 
														target="_blank">'.$res['order_id'].'</a>
													</div>
												</td>
												').
												($res['sales_channel'] == 'Ebay' ?
												'<td>
													<div style="width:200px">
													<a href="http://cgi'.(EBAY_SANDBOX ? '.sandbox' : '').
														'.ebay.com/ws/eBayISAPI.dll?ViewItem&item='.$res['item_id'].'" target="_blank">'.$res['item_title'].'</a>
													</div>
												</td>'
												:
												'<td>
													<div style="width:200px">
													<a href="http://www.amazon.com/dp/'.$res['item_id'].'" target="_blank">'.$res['item_title'].'</a>
													</div>
												</td>'
												).
												'<td>
													<div style="width:100px">
													'.$res['item_id'].'
													</div>
												</td>
												<td>
													<div rel="'.$res['id'].'" data-drop-done="'.$res['dropship_done'].'" class="dd-stats">
													  <div class="status-'.$res['id'].'" style="width:200px">
														'.($res['dropship_done'] == 0 ? 
														  ($res['process_at'] == '0000-00-00 00:00:00' ? 
															'
															<div class="btn-group">
																<a class="btn mini black" href="javascript:void(0)" data-toggle="dropdown">
																	<i class="icon icon-time"></i>&nbsp;PENDING <i class="icon-angle-down"></i>
																</a>
																<ul class="dropdown-menu pull-right">
																	<li>
																		<a href="javascript:void(0)" class="queue_order">
																			<i class="icon-black icon-share"></i>Add to queue
																		</a>
																	<li>
																	<li>
																		<a href="javascript:void(0)" class="delete_order">
																			<i class="icon-black icon-trash"></i>Delete order
																		</a>
																	</li>
																</ul>
															</div>
															' : 
															'
															<div class="btn-group">
																<a class="btn mini blue" href="javascript:void(0)" data-toggle="dropdown">
																	<i class="icon-black icon-signin"></i>&nbsp;&nbsp;QUEUED <i class="icon-angle-down"></i>
																</a>
																<ul class="dropdown-menu pull-right">
																	<li>
																		<a href="javascript:void(0)" class="suspend_order">
																			<i class="icon-black icon-ban-circle"></i>Suspend order
																		</a>
																	<li>
																	<li>
																		<a href="javascript:void(0)" class="delete_order">
																			<i class="icon-black icon-trash"></i>Delete order
																		</a>
																	</li>
																</ul>
															</div>
															'
															) : 
															'
															<div class="btn-group">
																<a class="btn mini '.(($res['dropship_done'] == 100 || $res['dropship_done'] == 200) ? 
																'red' : 'green').'" data-toggle="dropdown" href="javascript:void(0)">
																	<i class="icon-black '.(($res['dropship_done'] == 100 || $res['dropship_done'] == 200) ? 
																'icon-ban-circle' : 'icon-ok').'"></i>
																		&nbsp;&nbsp;'.$res['dropship_status'].' <i class="icon-angle-down">
																	</i>
																</a>
																<ul class="dropdown-menu pull-right">
																	'.(($res['dropship_done'] == 100 || $res['dropship_done'] == 200) ? 
																	'<li>
																		<a href="javascript:void(0)" class="retry_order">
																			<i class="icon-black icon-refresh"></i>Retry order
																		</a>
																	<li>' : '').'
																	<li>
																		<a href="javascript:void(0)" class="delete_order">
																			<i class="icon-black icon-trash"></i>Delete order
																		</a>
																	</li>
																</ul>
															</div>
															').'
													   </div>
													   
													   <div>
														   <div class="btn-group">
																<a class="btn mini purple" href="javascript:void(0)" data-toggle="dropdown">
																	<i class="icon-black icon-cog"></i>&nbsp;&nbsp;Options <i class="icon-angle-down"></i>
																</a>
																<ul class="dropdown-menu pull-right">
																	<li>
																		<a href="javascript:void(0)" class="mark_ordered">
																			<i class="icon-black icon-legal"></i>&nbsp;&nbsp;Mark as ordered
																		</a>
																	</li>
																	<li>
																		<a href="javascript:void(0)" class="mark_shipped">
																			<i class="icon-black icon-upload-alt"></i>&nbsp;&nbsp;Mark as shipped
																		</a>
																	</li>
																	<li>
																		<a href="javascript:void(0)" class="mark_cancelled">
																			<i class="icon-black icon-ban-circle"></i>&nbsp;&nbsp;Mark cancelled
																		</a>
																	</li>
																	<li>
																		<a href="javascript:void(0)" class="delete_order">
																			<i class="icon-black icon-trash"></i>&nbsp;&nbsp;Delete order
																		</a>
																	</li>
																	'.(!$res['ignore_loss'] ? 
																	'<li>
																		<a href="javascript:void(0)" class="ignore_loss">
																			<i class="icon-black icon-remove"></i>&nbsp;&nbsp;Ignore loss
																		</a>
																	</li>' 
																	: 
																	'<li>
																		<a href="javascript:void(0)" class="undo_ignore_loss">
																			<i class="icon-black icon-undo"></i>&nbsp;&nbsp;Undo ignore loss
																		</a>
																	</li>').
																	(!$res['ignore_price'] ? 
																	'<li>
																		<a href="javascript:void(0)" class="ignore_price">
																			<i class="icon-black icon-remove"></i>&nbsp;&nbsp;Ignore price
																		</a>
																	</li>' 
																	: 
																	'<li>
																		<a href="javascript:void(0)" class="undo_ignore_price">
																			<i class="icon-black icon-undo"></i>&nbsp;&nbsp;Undo ignore price
																		</a>
																	</li>').
																	(!$res['ignore_tax'] ? 
																	'<li>
																		<a href="javascript:void(0)" class="ignore_tax">
																			<i class="icon-black icon-remove"></i>&nbsp;&nbsp;Ignore tax
																		</a>
																	</li>' 
																	: 
																	'<li>
																		<a href="javascript:void(0)" class="undo_ignore_tax">
																			<i class="icon-black icon-undo"></i>&nbsp;&nbsp;Undo ignore tax
																		</a>
																	</li>').'
																	<li>
																		<a href="javascript:void(0)" class="retry_tracking_up">
																			<i class="icon-black icon-undo"></i>&nbsp;&nbsp;Retry tracking upload
																		</a>
																	</li>
																</ul>
															</div>
													   </div>
													   <span class="ajax-feedback-'.$res['id'].'"></span>
													 </div>
												</td>
												<td>
													<div style="width:70px">
													<font id="item_sku_view_'.$res['id'].'" rel="'.$res['id'].'" class="item_sku_view class_view">
														'.($res['item_sku'] ? $res['item_sku'] : '<a href="javascript:void(0)">Add+</a>').'
													</font>
													<input type="text" rel="'.$res['id'].'" id="item_sku_edit_'.$res['id'].'" class="item_sku_edit class_edit" 
														style="width: 50px; display: none; font-size: 12px" value="'.$res['item_sku'].'" />
													<br/>
													'.($res['item_sku'] ? '<a href="http://www.samsclub.com/sams/search/searchResults.jsp?searchTerm='.$res['item_sku'].'&searchCategoryId=all" target="_blank">View Item</a>' : '').'	
													</div>
												</td>
												<td>
													<div style="width:40px">
														<font class="money2 item_qty_view class_view" id="item_qty_view_'.$res['id'].'" rel="'.$res['id'].'">
															'.$res['item_quantity'].'
														</font>
														<input type="text" rel="'.$res['id'].'" id="item_qty_edit_'.$res['id'].'" class="item_qty_edit class_edit" 
														style="width: 30px; display: none; font-size: 12px" value="'.$res['item_quantity'].'" />
													</div>
												</td>
												<td>
												  <div style="width:50px">
													<font class="money2 item_paid_view class_view" id="item_paid_view_'.$res['id'].'" rel="'.$res['id'].'">
														$'.$res['paid_amount'].'
													</font>
													<input type="text" rel="'.$res['id'].'" id="item_paid_edit_'.$res['id'].'" class="item_paid_edit class_edit" 
														style="width: 40px; display: none; font-size: 12px" value="'.$res['paid_amount'].'" />
												  </div>
												</td>
												<td>
												  <div style="width:50px">
													-<font class="money2 item_purchase_view class_view" id="item_purchase_view_'.$res['id'].'" rel="'.$res['id'].'">
														$'.($res['purchase_price'] ? $res['purchase_price'] : '0.00').'
													</font>
													<input type="text" rel="'.$res['id'].'" id="item_purchase_edit_'.$res['id'].'" class="item_purchase_edit class_edit" 
														style="width: 40px; display: none; font-size: 12px" value="'.$res['purchase_price'].'" />
												  </div>
												</td>
												<td>
												  <div style="width:50px">
												  	-<font class="money2">
														$'.($res['sales_tax'] ? $res['sales_tax'] : '0.00').'
													 </font>
   												  </div>
												</td>
												<td>
												  <div style="width:50px">
													-<font class="money2 item_exp_view class_view" id="item_exp_view_'.$res['id'].'" rel="'.$res['id'].'">
														$'.($res['expense_fee'] ? $res['expense_fee'] : '0.00').'
													</font>
													<input type="text" rel="'.$res['id'].'" id="item_exp_edit_'.$res['id'].'" class="item_exp_edit class_edit" 
														style="width: 40px; display: none; font-size: 12px" value="'.$res['expense_fee'].'" />
												  </div>
												</td>
												<td>
												  <div style="width:50px">
													<font class="money2 doubleUnderline item_profit_view class_view" id="item_profit_view_'.$res['id'].'" rel="'.$res['id'].'">
														$'.($res['amount_profit'] ? $res['amount_profit'] : '0.00').'
													</font>
													<input type="text" rel="'.$res['id'].'" id="item_profit_edit_'.$res['id'].'" class="item_profit_edit class_edit" 
														style="width: 40px; display: none; font-size: 12px" value="'.$res['amount_profit'].'" />
   												  </div>
												</td>
												<td>
													<div style="width:75px">'.
														//date('d-M-Y', strtotime(str_replace(array('T', 'Z', '.000'), array('', ' ', ''), $res['sales_time']))).'
														str_replace(array('T', 'Z', '.000'), array('<br/>', '', ''), $res['sales_time']).'
													</div>
												</td>
												<td>
													<div style="width:100px">
														<font class="name_view class_view" id="name_view_'.$res['id'].'" rel="'.$res['id'].'">
															'.$res['name'].'<br/>
														</font>
														<input type="text" rel="'.$res['id'].'" id="name_edit_'.$res['id'].'" class="name_edit class_edit" 
														style="width: 85px; display: none; font-size: 12px" value="'.htmlentities($res['name']).'" />
													</div>
												</td>
												<td>
													<div style="width:100px">
													'.$res['buyer_email'].'
													</div>
												</td>
												<td>
													<div style="width:100px">
													'.$res['buyer_user_id'].'
													</div>
												</td>
												<td>
													<div style="width:100px">
														<font class="addr_view class_view" id="addr_view_'.$res['id'].'" rel="'.$res['id'].'">
															'.(!empty($res['street1']) ? $res['street1'] : '<a href="javascript:void(0)">Click to Edit street1</a>').'<br/>
														</font>
														<input type="text" rel="'.$res['id'].'" id="addr_edit_'.$res['id'].'" class="addr_edit class_edit" 
														style="width: 85px; display: none; font-size: 12px" value="'.htmlentities($res['street1']).'" />
														<br/><font class="addr2_view class_view" id="addr2_view_'.$res['id'].'" rel="'.$res['id'].'">
															'.(!empty($res['street2']) ? $res['street2'] : '<a href="javascript:void(0)">Click to edit street2</a>').'<br/>
														</font>
														<input type="text" rel="'.$res['id'].'" id="addr2_edit_'.$res['id'].'" class="addr2_edit class_edit" 
														style="width: 85px; display: none; font-size: 12px" value="'.htmlentities($res['street2']).'" />
													</div>
												</td>
												<td>
													<div style="width:100px">
														<font class="city_view class_view" id="city_view_'.$res['id'].'" rel="'.$res['id'].'">
															'.$res['city'].'<br/>
														</font>
														<input type="text" rel="'.$res['id'].'" id="city_edit_'.$res['id'].'" class="city_edit class_edit" 
														style="width: 85px; display: none; font-size: 12px" value="'.htmlentities($res['city']).'" />
													</div>
												</td>
												<td>
													<div style="width:100px">
														<font class="state_view class_view" id="state_view_'.$res['id'].'" rel="'.$res['id'].'">
															'.$res['state_province'].'<br/>
														</font>
														<input type="text" rel="'.$res['id'].'" id="state_edit_'.$res['id'].'" class="state_edit class_edit" 
														style="width: 85px; display: none; font-size: 12px" value="'.htmlentities($res['state_province']).'" />
													</div>
												</td>
												<td>
													<div style="width:100px">
														<font class="pscode_view class_view" id="pscode_view_'.$res['id'].'" rel="'.$res['id'].'">
															'.$res['postal_code'].'<br/>
														</font>
														<input type="text" rel="'.$res['id'].'" id="pscode_edit_'.$res['id'].'" class="pscode_edit class_edit" 
														style="width: 85px; display: none; font-size: 12px" value="'.htmlentities($res['postal_code']).'" />
													</div>
												</td>
												<td>
													<div style="width:100px">
													'.$res['country_name'].'
													</div>
												</td>
												<td>
													<div style="width:100px">
														<font class="phone_view class_view" id="phone_view_'.$res['id'].'" rel="'.$res['id'].'">
															'.$res['phone'].'<br/>
														</font>
														<input type="text" rel="'.$res['id'].'" id="phone_edit_'.$res['id'].'" class="phone_edit class_edit" 
														style="width: 85px; display: none; font-size: 12px" value="'.htmlentities($res['phone']).'" />
													</div>
												</td>
												<td>
													<div style="width:100px">
														<font id="item_vendor_view_'.$res['id'].'" rel="'.$res['id'].'" class="item_vendor_view class_view">
														'.($res['vendor'] ? $res['vendor'] : '<a href="javascript:void(0)">Add+</a>').'
														</font>
														<input type="text" rel="'.$res['id'].'" id="item_vendor_edit_'.$res['id'].'" class="item_vendor_edit class_edit" 
															style="width: 80px; display: none; font-size: 12px" value="'.$res['vendor'].'" />
													</div>
												</td>
												<td>
													<div style="width:100px">
													'.($res['dropship_orderid'] ? 
														$res['dropship_orderid'].'<br/>
														<a target="_blank" href="http://www.samsclub.com/sams/shoppingtools/orderhistory/orderDetailsPage.jsp?&orderId='.$res['dropship_orderid'].'">
														View Order
														</a>'
														: 
														'<font id="item_oid_view_'.$res['id'].'" rel="'.$res['id'].'" class="item_oid_view class_view">
															<a href="javascript:void(0)">Add+</a>
														</font>
														').'
													<input type="text" rel="'.$res['id'].'" id="item_oid_edit_'.$res['id'].'" class="item_oid_edit class_edit" 
														style="width: 80px; display: none; font-size: 12px" value="'.$res['dropship_orderid'].'" /><br/>
													</div>
												</td>
												<td>
													<div style="width:100px">
													'.($res['tracking_number'] ? '<a href="'.$res['tracking_url'].'" target="_blank">'.$res['tracking_number'].'</a>' : 
														'<font id="item_tid_view_'.$res['id'].'" rel="'.$res['id'].'" class="item_tid_view class_view">
															<a href="javascript:void(0)">Add+</a>
														</font>
														').'
														<div class="item_tid_edit class_edit no_blur" id="item_tid_edit_'.$res['id'].'" style="display: none">
															<input type="text" rel="'.$res['id'].'" id="item_tid_edit_id_'.$res['id'].'" class="item_tid_edit_2"
																style="width: 80px; display: ; font-size: 12px" value="'.$res['tracking_number'].'" placeholder="ID"/>
															<input type="text" rel="'.$res['id'].'" id="item_tid_edit_tu_'.$res['id'].'" class="item_tid_edit_2"
																style="width: 80px; display: ; font-size: 12px" value="'.$res['tracking_number'].'" placeholder="Carrier"/>
															<input type="text" rel="'.$res['id'].'" id="item_tid_edit_tcu_'.$res['id'].'" class="item_tid_edit_2"
																style="width: 80px; display: ; font-size: 12px" value="'.$res['tracking_number'].'" placeholder="URL"/>
														</div>
													</div>
												</td>
												<td>
													<div style="width:100px">
													    <font id="note_view_'.$res['id'].'" rel="'.$res['id'].'" class="note_view class_view">
															'.($res['custom_note'] ? htmlentities($res['custom_note']) : '[Add text]').'
														</font>
														
													<input type="text" rel="'.$res['id'].'" id="note_edit_'.$res['id'].'" class="note_edit class_edit" 
														style="width: 90px; display: none; font-size: 12px" value="'.htmlentities($res['custom_note']).'" />
													</div>
												</td>
											 </tr>';
									}
									echo '<tr><td colspan="30">
											Total '.$count.' records. Showing '.($from+1).' to '.(($from+$rows) > $count ? $count : ($from+$rows));
								    echo '<br/>';
									pagination($count,$rows,$from,$qu);
									echo '</td></tr>';	
								}
								?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- END PORTLET-->
								
                <!-- Modal 1 -->
                <div id="myModal1" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel1" aria-hidden="true" style="width:750px">
                	<form id="searchForm">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h3 id="myModalLabel1">Search orders</h3>
                    </div>
                    <div class="modal-body">
                        <p>
                        	<table cellspacing="5" cellpadding="5">
                        	<tr>
                            	<td><label>OrderID</label> 
                            		<input type="text" name="OrderID" />
                                </td>
                                <td><label>Sort by</label> 
                            		<select name="SortBy" class="chosen">
                                    	<option value="">Select One</option>
                                        <option value="OrderDateASC">OrderDateASC</option>
                                        <option value="OrderDateDESC">OrderDateDESC</option>
                                    </select>
                                </td>
                                <td><label>Order Status</label> 
                            		<select name="OrderStatus" class="chosen">
                                    	<option value="">Select One</option>
                                        <option value="queued">Queued</option>
                                        <option value="pending">Pending</option>
                                        <option value="justordered">Just ordered</option>
                                        <option value="ordered">Ordered</option>
                                        <option value="shipped">Shipped</option>
                                        <option value="failed">Failed</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                            	<td><label>SalesChannel</label> 
                            		<select name="SalesChannel" class="chosen">
                                    	<option value="">All</option>
                                        <option value="Ebay">Ebay</option>
                                        <option value="Amazon">Amazon</option>
                                    </select> 
                                </td>
                                <td>
                                	<label>FromDate</label>
                                    <input type="text" class="date-picker" data-date-format="yyyy-mm-dd" name="fromDate"/>
                                </td>
                                <td><label>ToDate</label> 
                            		<input type="text" class="date-picker" data-date-format="yyyy-mm-dd" name="toDate"/>
                                </td>
                           </tr>
                           <tr>
                                <td>
                            		<label>BuyerID</label> 
                            		<input type="text" name="BuyerID" /> 
                                </td>
                                <td><label>DropShipOrderID</label> 
                            		<input type="text" name="DropShipOrderID" /> 
                                </td>
                                <td>
                            		<label>DropShipSKU</label> 
                            		<input type="text" name="DropShipSKU" /> 
                                </td>
                            </tr>
                           <tr>
                            	<td><label>BuyerName</label> 
                            		<input type="text" name="BuyerName" /> 
                                </td>
                               <td> 
                            		<label>ProductTitle</label> 
                            		<input type="text" name="ProductTitle" /> 
                                </td>
                                <td colspan="2">
                                	<input type="checkbox" name="ignoreDate" checked="checked"/>
                                	<font>Search All records ignoring date</font>
                                </td>
                           </tr>
                          </table>
                        </p>
                    </div>
                    </form>
                    <div class="modal-footer">
                        <button class="btn" data-dismiss="modal" aria-hidden="true"><i class="icon-black icon-remove"></i>&nbsp;&nbsp;Close</button>
                        <button class="btn yellow" onclick="$('#searchForm').submit()"><i class="icon-black icon-search"></i>&nbsp;&nbsp;Search</button>
                    </div>
                </div>
                <!-- Modal 1 End -->
                
                <!-- Modal 2 -->
                <div id="myModal2" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel2" aria-hidden="true" style="">
                	<form id="exportForm" action="export.php" method="post">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h3 id="myModalLabel1">Export orders</h3>
                    </div>
                    <div class="modal-body">
                        <p>
                        	<table cellspacing="5" cellpadding="5">
                        	<tr>
                            	<td colspan="2"><label>Select a time range to export</label> </td>
                           </tr>
                           <tr>
                           		<td>
                                	<label>From</label> 
                                	<input type="text" name="exportOrderDate_init" id="exportOrderDate_init" data-date-format="yyyy-mm-dd" class="date-picker"/>
                                </td>
                                <td>
                                	<label>To</label> 
                                	<input type="text" name="exportOrderDate_end" data-date-format="yyyy-mm-dd" class="date-picker"/>
                                </td>
                           </tr>
                          </table>
                        </p>
                    </div>
                    </form>
                    <div class="modal-footer">
                        <button class="btn" data-dismiss="modal" aria-hidden="true"><i class="icon-black icon-remove"></i>&nbsp;&nbsp;Close</button>
                        <button class="btn yellow" onclick="if($('#exportOrderDate_init').val())$('#exportForm').submit()"><i class="icon-black icon-download"></i>&nbsp;&nbsp;Download</button>
                    </div>
                </div>
                <!-- Modal 2 End -->
                
                <!-- Modal 3 -->
                <div id="myModal3" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true" style="width:750px">
                	<form onsubmit="return false" id="manualAddOrderForm">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h3 id="myModalLabel1">Add new order</h3>
                    </div>
                    <div class="modal-body">
                        <p>
                           <table cellspacing="5" cellpadding="5">
                           <tr>
                           		<td>
                                	<label>OrderId</label> 
                                	<input type="text" name="OrderID"/>
                                </td>
                                <td>
                                	<label>Vendor</label> 
                                	<input type="text" name="Vendor" value="SamsClub"/>
                                </td>
                                <td>
                                	<label>VendorOrderID</label> 
                                	<input type="text" name="VendorOrderID"/>
                                </td>
                            </tr>
                            <tr>
                            	<td>
                                	<label>SalesAmount</label> 
                                	<input type="text" name="Sales"/>
                                </td>
                                <td>
                                	<label>ItemSKU</label> 
                                	<input type="text" name="SKU"/>
                                </td>
                                <td>
                                	<label>Qty</label> 
                                	<input type="text" name="Qty"/>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                	<label>Name</label> 
                                	<input type="text" name="Name"/>
                                </td>
                                <td>
                                	<label>StreetAddress</label> 
                                	<input type="text" name="Street"/>
                                </td>
                                <td>
                                	<label>City</label> 
                                	<input type="text" name="City"/>
                                </td>
                           </tr>
                           <tr>
                                <td>
                                	<label>State</label> 
                                	<input type="text" name="State"/>
                                </td>
                                <td>
                                	<label>ZipCode</label> 
                                	<input type="text" name="Zip"/>
                                </td>
                                <td>
                                	<label>Phone</label> 
                                	<input type="text" name="Phone"/>
                                </td>
                           </tr>
                          </table>
                        </p>
                    </div>
                    <input type="hidden" name="addOrder" value="1" />
                    </form>
                    <div class="modal-footer">
                        <button class="btn" data-dismiss="modal" aria-hidden="true"><i class="icon-black icon-remove"></i>&nbsp;&nbsp;Close</button>
                        <button class="btn purple manualAddOrder"><i class="icon-black icon-save"></i>&nbsp;&nbsp;Save</button>
                    </div>
                </div>
                <!-- Modal 3 End -->
                
                <!-- Modal 4 -->
                <div id="myModal4" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel3" aria-hidden="true" style="width:750px">
                	<form onsubmit="return false" id="manualImportForm">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                        <h3 id="myModalLabel1">Manually import order</h3>
                    </div>
                    <div class="modal-body">
                        <p>
                           <table cellspacing="5" cellpadding="5" class="impTab" width="100%">
                           <tr valign="top">
                           		<td>
                                	<label>Ebay</label>
                                    <select id="ebayAcc">
                                    <?php
										$q = mysql_query("SELECT * FROM ebay_tokens");
										while($res = mysql_fetch_assoc($q)){
											echo '<option value="'.$res['id'].'">'.$res['user_id'].'</option>';	
										}
									?>
                                    </select>
                                </td>
                                <td>
                                	<label>OrderIds</label>
                                    <textarea id="ebayOrderIds" placeholder="One per line. Maximum 10"></textarea>
                                </td>
                            </tr>
                            <tr>
                            	<td></td>
                                <td valign="middle">
                                	<button class="btn purple manualImportEbayOrder"><i class="icon-black icon-ok"></i>&nbsp;&nbsp;Process</button>
                                </td>
                            </tr>
                            <tr>
                            	<td colspan="2"><br/></td>
                            </tr>
                            <tr valign="top">
                           		<td>
                                	<label>Amazon</label>
                                    <select id="amazonAcc">
                                    <?php
										$q = mysql_query("SELECT * FROM amazon_tokens");
										while($res = mysql_fetch_assoc($q)){
											echo '<option value="'.$res['id'].'">'.$res['user_id'].'</option>';	
										}
									?>
                                    </select>
                                </td>
                                <td>
                                	<label>OrderIds</label>
                                    <textarea id="amazonOrderIds" placeholder="One per line. Maximum 10"></textarea>
                                </td>
                            </tr>
                            <tr>
                            	<td></td>
                                <td valign="middle">
                                	<button class="btn red manualImportAmazonOrder"><i class="icon-black icon-ok"></i>&nbsp;&nbsp;Process</button>
                                </td>
                            </tr>
                          </table>
                          <div class="impActTab" style="display:none">
                          	<div class="alert alert-info">Importing orders please wait...</div>
                          </div>
                        </p>
                    </div>
                    <input type="hidden" name="addOrder" value="1" />
                    </form>
                    <div class="modal-footer">
                        <button class="btn" data-dismiss="modal" aria-hidden="true"><i class="icon-black icon-remove"></i>&nbsp;&nbsp;Close</button>
                    </div>
                </div>
                <!-- Modal 4 End -->
                
                <form action="export.php" method="post" id="exportOrderIDs"><input type="hidden" id="export_id" name="exportOrderIDs" /></form>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>
  </div>