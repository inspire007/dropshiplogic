<div class="container-fluid">
    <!-- BEGIN PAGE HEADER-->
    <div class="row-fluid">
        <div class="span12">
        	<h3 class="page-title">
               Listings		
            </h3>
            <ul class="breadcrumb">
                <li>
                    <i class="icon-home"></i>
                    <a href="index.php">Home</a> 
                    <i class="icon-angle-right"></i>
                </li>
                <li><a href="listings.php">Listings</a></li>
            </ul>
            <!-- END PAGE TITLE & BREADCRUMB-->
        </div>
    </div>
    
    <div class="row-fluid">
    
    <?php
	$rows = 50;
	if(!empty($_GET['from']))$from = (int)$_GET['from'];
	else $from = 1;
	
	$from--;
	if($from < 0)$from = 0;
	
	if(empty($_GET))$qu = 'view=1';
	else $qu = http_build_query($_GET);

	$q = mysql_query("SELECT NULL FROM ebay_items");
	$count = mysql_num_rows($q);
	$q = mysql_query("SELECT * FROM ebay_items LIMIT $from, $rows");
	?>
	<div class="clearfix"></div>
        <div class="row-fluid" style="display: none" id="main_container">
            <div class="span12">
                <!-- BEGIN PORTLET-->
                <div class="portlet box solid bordered blue">
                    <div class="portlet-title">
                        <h4><i class="icon-money"></i>Latest sales 
                        	<small>Total <?php echo $count?> listings</small>
                        </h4>
                    </div>
                    <div class="portlet-body" id="main-listing" style="color: black; overflow: auto">
                        <table class="table table-hover table-bordered main-listing">
                        	<thead>
                            	<tr>
                                	<th>#</th>
                                    <th>ItemID</th>
                                    <th>SalesChannel</th>
                                    <th>Title</th>
                                    <th>Vendor</th>
                                    <th>SKU</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Owner</th>
                                    <th>LastUpdate</th>
                                </tr>
                            </thead>
                            <tbody>
                            	<?php
								if(!$count){
									echo '<tr>
											<td colspan="30">
												<div class="alert alert-error"><i class="icon-black icon-remove"></i>&nbsp;&nbsp;&nbsp;<b>No listing found</b></div>
											</td>
										</tr>';	
								}
								else{
									
									while($res = mysql_fetch_assoc($q)){
										echo '<tr style="'.(!$res['quantity'] ? 'background-color: rgba(215, 40, 40, 0.9)' : '').'">
												<td>'.$res['id'].'</td>
												<td>
													'.($res['sales_channel'] == 'Ebay' ? '<a href="http://cgi'.(EBAY_SANDBOX ? '.sandbox' : '').
														'.ebay.com/ws/eBayISAPI.dll?ViewItem&item='.$res['item_id'].'"' : 
														'<a href="http://www.amazon.com/dp/'.$res['item_id'].'"').' target="_blank">
														'.$res['item_id'].'
														</a>
												</td>
												<td>'.$res['sales_channel'].'</td>
												<td>'.$res['item_title'].'</td>
												<td>'.$res['vendor'].'</td>
												<td>
													<a href="http://www.samsclub.com/sams/search/searchResults.jsp?searchTerm='.urlencode($res['sku']).'&searchCategoryId=all" 
														target="_blank">'.$res['sku'].'
													</a>
												</td>
												<td>'.$res['quantity'].'</td>
												<td>'.$res['price'].'</td>
												<td>'.$res['user_id'].'</td>
												<td>'.$res['last_update'].'</td>
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
             </div>     
    	</div>
    </div>
</div>