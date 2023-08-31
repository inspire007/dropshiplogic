<?php
/**
 * Various functions used in the system
 *
 * @package EbaySalesAutomation
 * @subpackage Home
 * @version 1.0
 * @author N/A
 * @copyright 2014
 */

function mysql_conn()
{
	@mysql_close();
	mysql_connect(DB_HOST,DB_USER,DB_PASS) or die('Mysql connection failed');
	mysql_select_db(DB_NAME) or die('Database selection failed');
}

function load_settings()
{
	$settings = array();
	$settings = mysql_fetch_assoc(mysql_query("SELECT * FROM global_config"));
	return $settings;
}

function update_profit_sales($today)
{
	$sales =  mysql_num_rows(mysql_query("SELECT NULL FROM ebay_orders WHERE added_at LIKE '$today%'"));
	list($profit) = mysql_fetch_row(mysql_query("SELECT SUM(amount_profit) FROM ebay_orders WHERE added_at LIKE '$today%'"));
	
	mysql_query("INSERT INTO sales_stats VALUES ('$today', '$sales', '$profit')");
	//if we have error this is usually record exists
	if(mysql_error()){
		mysql_query("UPDATE sales_stats SET sales_count = '$sales', total_profit = '$profit' WHERE added_at = '$today'");	
	}
}

function aasort(&$array, $key) {
    $sorter = array();
    $ret = array();
    reset($array);
    foreach($array as $ii => $va) {
        $sorter[$ii] = $va[$key];
    }
    asort($sorter);
    foreach($sorter as $ii => $va) {
        $ret[$ii] = $array[$ii];
    }
    $array = $ret;
}

function check_login()
{
	if(empty($_SESSION['user_logged_in'])){
		return false;	
	}
	$email = mysql_real_escape_string($_SESSION['user_logged_in']);
	if(!mysql_num_rows(mysql_query("SELECT NULL FROM users WHERE email = '$email' AND status = 1"))){
		unset($_SESSION['user_logged_in']);
		return false;	
	}
	return true;
}

function custom_number_format($n, $precision = 2) 
{
	if($n < 1000000) {
		$n_format = number_format($n, $precision);
	} 
	else if($n < 1000000000) {
		$n_format = number_format($n / 1000000, $precision) . 'M';
	} 
	else {
		$n_format = number_format($n / 1000000000, $precision) . 'B';
	}
	
	return $n_format;
}

function pagination($count,$rows,$from,$qu)
{
	//this is from zero
	//$from--; from ->1
	$from++;
	$qu=preg_replace("/from=(\d+)/","",$qu);
	$qu=preg_replace("/^\&/","",$qu);
	echo '<div class="dataTables_paginate paging_bootstrap pagination"><ul>';
	if(($from-$rows) >= 0)echo '<li class="prev"><a href="'.$_SERVER['PHP_SELF'].'?from='.($from-$rows).'&'.$qu.'">← Prev</a></li>';
	else echo '<li class="active"><span>← Prev</span></li>';
	$index=(int)($count/$rows);
	if($count%$rows)$index++;
	for($i=1;$i<=$index;$i++){
		$current_page=(int)($from/$rows)+1;
		if($index>20 && $current_page>10 && $i==3){echo "<li><span class='dots'>...</span></li>";$i=$current_page-5;}
		if($index>20 && ($index-$current_page)>10 && $i==($current_page+5)){echo "<li><span class='dots'>...</span></li>";$i=$index-2;}
		if($i==(int)($from/$rows)+1)echo '<li class="active"><span>'.$i.'</span></li>';
		else echo '<li><a href="'.$_SERVER['PHP_SELF'].'?from='.(($i-1)*$rows+1).'&'.$qu.'">'.$i.'</a></li>';
	}
	if(($from+$rows) <= $count)echo '<li class="next"><a href="'.$_SERVER['PHP_SELF'].'?from='.($from+$rows).'&'.$qu.'">Next → </a></li>';
	else echo '<li class="active"><span>Next →</span></li>';
	echo '</ul></div>';
	$from--;
}

/**
 * Function to check if the script is already running or not
 * @param $set string ebay or sams
 * @return bool the run status
 */
function check_running($set)
{
	$running=0;
	if(file_exists(dirname(__FILE__)."/logs/lock-".$set.".dat")){
		$fp=fopen(dirname(__FILE__)."/logs/lock-".$set.".dat","r");
		if(!flock($fp,LOCK_EX | LOCK_NB))$running=1;
		fclose($fp);
	}
	return $running;
}

/**
 * Function to lock a process
 * @param $set string ebay or sams
 * @return $fp resource file pointer
 */
function lock_process($set)
{
	$fp = fopen(dirname(__FILE__)."/logs/lock-".$set.".dat","w");
	flock($fp, LOCK_EX);
	return $fp;
}

/**
 * Function to unlock a process
 * @param $fp resource file pointer
 * @return n/a
 */
function unlock_process($fp)
{
	flock($fp, LOCK_UN);
	fclose($fp);
}

/**
 * Function to backup logs for later user
 * @param $set string ebay or sams
 */
function save_logs($set)
{
	$f = dirname(__FILE__)."/logs/".$set."-log.txt";
	if(file_exists($f)){
		if(!is_dir(dirname(__FILE__)."/logs/old/"))mkdir(dirname(__FILE__)."/logs/old/");
		copy($f, dirname(__FILE__)."/logs/old/".time().'_'.basename($f));	
	}
}

/**
 * Function to clear backup logs
 * @param $set string ebay or sams
 */
function clear_logs($set)
{
	if(file_exists(dirname(__FILE__)."/logs/".$set."-log.txt"))unlink(dirname(__FILE__)."/logs/".$set."-log.txt");
	$d = dirname(__FILE__).'/logs/old/';
	if(!is_dir($d))return false;
	//clear tmp directory
	$files = scandir($d);
	foreach($files as $file){
		if($file == '..' || $file == '.' || $file == '.htaccess')continue;
		$t = @filectime($d.'/'.$file);
		if((time() - $t) > 3600*24 && $t)@unlink($d.'/'.$file);	
	}
}

/**
 * Function to log debug strings
 *
 * @param string $str the string to log
 * @param $set string ebay or sams to be globally set
 * @return n/a
 */
function do_log($str)
{
	global $set, $debug_off;
	$fp=fopen(dirname(__FILE__)."/logs/".$set."-log.txt", "a");
	fwrite($fp, date('[d-M-Y H:i:s]')." $str\r\n");
	fclose($fp);
	if(!empty($debug_off))return true;
	echo $str."<br/>";
	@flush();
	@ob_flush();
	
}


?>