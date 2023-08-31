<?php
/**
 * Export Results
 *
 * @package EbaySalesAutomation
 * @subpackage Export
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
session_write_close();

if(!empty($_POST['exportOrderIDs'])){
	$orders = mysql_real_escape_string(rtrim($_POST['exportOrderIDs'], ','));
	
	$q = mysql_query("SELECT * FROM ebay_orders WHERE id IN ($orders)");
	if(!mysql_num_rows($q))exit('No data to export for selected search criteria');
	
	$top = 1;
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename=export.csv');
}

else if(!empty($_POST['exportOrderDate_init'])){
	$init = "added_at >= '".mysql_real_escape_string($_POST['exportOrderDate_init'])."'";
	if(!empty($_POST['exportOrderDate_end']))$end = "added_at <= '".mysql_real_escape_string($_POST['exportOrderDate_end'])."'";
	else $end = '1';
	
	$q = mysql_query("SELECT * FROM ebay_orders WHERE $init AND $end");
	if(!mysql_num_rows($q))exit('No data to export today');
	
	$top = 1;
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename=export_'.$_POST['exportOrderDate_init'].'.csv');
}

else{
	$today = date('Y-m-d');
	$q = mysql_query("SELECT * FROM ebay_orders WHERE added_at LIKE '$today%'");
	if(!mysql_num_rows($q))exit('No data to export today');
	
	$top = 1;
	header('Content-Type: text/csv');
	header('Content-Disposition: attachment; filename=export_'.$today.'.csv');
}

$fp = fopen('php://output', 'w');
	
while($res = mysql_fetch_assoc($q)){
	if($top){
		$head = array();
		foreach($res as $k => $v)$head[] = $k;
		fputcsv($fp, $head);
		$top = 0;	
		$head = '';
	}
	fputcsv($fp, $res);	
}
fclose($fp);

?>