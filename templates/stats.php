<?php

$today = 'today';
$yesterday = 'yesterday';

if(!empty($_GET['range']) && !preg_match('/[^0-9\-]/', $_GET['range'])){
	$date = $_GET['range'];
	$today = $date;
	$yesterday = date('Y-m-d', strtotime($date) - 1600*24);	
}

$stats = new Stats_Manager();
$salesToday = $stats->getDailySales($today);
$salesYesterday = $stats->getDailySales($yesterday);
$profitToday =  $stats->getDailyProfit($today);
$profitYesterday =  $stats->getDailyProfit($yesterday);
$salesTrend = $stats->salesTrend();
$profitTrend = $stats->profitTrend();

$st = (float) str_replace(',', '', $salesToday);
$sy = (float) str_replace(',', '', $salesYesterday);

$pt = (float) str_replace(',', '', $profitToday);
$py = (float) str_replace(',', '', $profitYesterday);

if(!$salesYesterday)$salseChange = 100;
else $salseChange = @round((($st - $sy)/$sy)*100, 2);
if(!$profitYesterday)$profitChange = 100;
else $profitChange = @round((($pt - $py)/$py)*100, 2);

if($today == 'today'){
	$totalSales = $stats->getDailySales('all');
	$profitAll =  $stats->getDailyProfit('all');
	$estimatedSales = $stats->forecastSales();
	$estimatedProfit = $stats->forecastProfit();
}
else{
	$totalSales = $stats->getDailySales('alluptodate', $today);
	$profitAll =  $stats->getDailyProfit('alluptodate', $today);
	$estimatedSales = $stats->forecastSales($today);
	$estimatedProfit = $stats->forecastProfit($today);	
}
?>
<div class="container-fluid">
    <!-- BEGIN PAGE HEADER-->
    <div class="row-fluid">
        <div class="span12">
        	<h3 class="page-title">
               Sales Statistics		
            </h3>
            <ul class="breadcrumb">
                <li>
                    <i class="icon-home"></i>
                    <a href="index.php">Home</a> 
                    <i class="icon-angle-right"></i>
                </li>
                <li><a href="stats.php">Sales Statistics</a></li>
            </ul>
            <!-- END PAGE TITLE & BREADCRUMB-->
        </div>
    </div>
    
    <form action="" method="get">
    <div class="row-fluid">
    	<div class="span12">
        	<div><i class="icon-black icon-calendar"></i> &nbsp; DATE : < <?php echo $today?> >
            	 <input type="text" class="pull-right custom-date-picker" name="range" data-date-format="yyyy-mm-dd" data-onrender='alert(1)' value="<?php echo $today?>"/> 
            </div>
        </div>
    </div>
    </form>
    <div class="row-fluid">
    	
        <div class="span4">
        	<!-- BEGIN SOLID PORTLET-->
        	<div class="portlet solid yellow">
                <div class="portlet-title">
                    <h4><i class="icon-bar-chart"></i>General Sales Statistics</h4>
                    <div class="tools">
                        <a href="javascript:void(0);" class="collapse"></a>                    
                   </div>
                </div>
                <div class="portlet-body">
                	<div class="row-fluid">
                    	<div class="span6 stats-headline"><h4>Total Sales</h4> <font class="money"><?php echo $totalSales?></font></div>
                    	<div class="span6 stats-headline"><h4>Sales Today</h4> <font class="money"><?php echo $salesToday?></font></div>
                    </div>
                    
                    <div class="row-fluid">
                    	<div class="span6 stats-headline"><h4>Sales Yesterday</h4> <font class="money"><?php echo $salesYesterday?></font></div>
                        <div><h2><strong><?php echo $salseChange.'% '.($salseChange <= 0 ?  '<span style="background:url(assets/data-tables/images/sort_desc.png) no-repeat right">&nbsp;&nbsp;&nbsp;</span>' : '<span style="background:url(assets/data-tables/images/sort_asc.png) no-repeat right">&nbsp;&nbsp;&nbsp;</span>')?></strong></h2></div>
                    </div>		
                </div>
            </div>
			<!-- END SOLID PORTLET-->
        </div>
        
        <div class="span4">
        	<!-- BEGIN SOLID PORTLET-->
        	<div class="portlet solid red">
                <div class="portlet-title">
                    <h4><i class="icon-bar-chart"></i>General Profit Statistics</h4>
                    <div class="tools">
                        <a href="javascript:void(0);" class="collapse"></a>                    
                   </div>
                </div>
                <div class="portlet-body">
                	<div class="row-fluid">
                    	<div class="span6 stats-headline"><h4>Total Profit</h4> <font class="money">$<?php echo $profitAll?></font></div>
                    	<div class="span6 stats-headline"><h4>Profit Today</h4> <font class="money">$<?php echo $profitToday?></font></div>
                    </div>
                    
                    <div class="row-fluid">
                    	<div class="span6 stats-headline"><h4>Profit Yesterday</h4> <font class="money">$<?php echo $profitYesterday?></font></div>
                        <div><h2><strong><?php echo $profitChange.'% '.($profitChange <= 0 ?  '<span style="background:url(assets/data-tables/images/sort_desc.png) no-repeat right">&nbsp;&nbsp;&nbsp;</span>' : '<span style="background:url(assets/data-tables/images/sort_asc.png) no-repeat right">&nbsp;&nbsp;&nbsp;</span>')?></strong></h2></div>
                    </div>		
                </div>
            </div>
			<!-- END SOLID PORTLET-->
        </div>
         
        
        <div class="span4">
        	<!-- BEGIN SOLID PORTLET-->
        	<div class="portlet solid blue">
                <div class="portlet-title">
                    <h4><i class="icon-bar-chart"></i>Sales Forecast</h4>
                    <div class="tools">
                        <a href="javascript:void(0);" class="collapse"></a>
                    </div>
                </div>
                <div class="portlet-body">
                	<div class="row-fluid">
                        <div class="span12 stats-headline"><h4>Estimated Sales Today</h4> <font class="money"><?php echo $estimatedSales?></font></div>
                    </div>
                    <div class="row-fluid">
                        <div class="span12 stats-headline"><h4>Estimated Profit Today</h4> <font class="money">$<?php echo $estimatedProfit?></font></div>
                    </div>
                </div>
            </div>
			<!-- END SOLID PORTLET-->
        </div>
    
    </div>
    
    <div class="row-fluid">
    	<div class="span12">
        
        	<!-- BEGIN INTERACTIVE CHART PORTLET-->
            <div class="portlet box purple">
                <div class="portlet-title">
                    <h4><i class="icon-reorder"></i>Sales Trend in Last 30 days</h4>
                    <div class="tools">
                        <a href="javascript:void(0);" class="collapse"></a>
                    </div>
                </div>
                <div class="portlet-body">
                    <div id="chart_sales" class="chart" style="width:92%; margin-left: 4%"></div>
                </div>
            </div>
            <!-- END INTERACTIVE CHART PORTLET-->
            
            <!-- BEGIN TRACKING CURVES PORTLET-->
            <div class="portlet box green">
                <div class="portlet-title">
                    <h4><i class="icon-reorder"></i>Profit Trend in Last 30 days</h4>
                    <div class="tools">
                        <a href="javascript:void(0);" class="collapse"></a>
                    </div>
                </div>
                <div class="portlet-body">
                    <div id="chart_profit" class="chart" style="width:92%; margin-left: 4%"></div>
                </div>
            </div>
            <!-- END TRACKING CURVES PORTLET-->
            
        </div>
    </div>
</div>
<?php
	$sales= '[';
	foreach($salesTrend as $i => $d)$sales .= "[".$d[0].",".$d[1]."],";
	$sales = rtrim($sales, ',').']';
	
	$profit= '[';
	foreach($profitTrend as $i => $d)$profit .= "[".$d[0].",".$d[1]."],";
	$profit = rtrim($profit, ',').']';
?>
<script type="text/javascript">
var daily_sales = <?php echo $sales;?>;
var daily_profit = <?php echo $profit?>
</script>
<script src="assets/flot/jquery.flot.js"></script>