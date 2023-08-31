<?php
/**
 * This class is used to fetch sales stats from database.
 *
 * @package EbaySalesAutomation
 * @subpackage SalesStats
 * @version 1.0
 * @author N/A
 * @copyright 2014
 */
 
 /**
 * This the Main Stats
 * Work flow :
 * Initialize it and today and yesterday will auto set
 * call getDailySales() with date to get daily sales of that date
 * call getDailyProfit() with date to get daily profit of that date 
 * call forecastSales() to forecast sales of today
 * call salesTrend() to get sales trend of last 30 days
 * call profitTrend() to get profit trend of last 30 days
 */
class Stats_Manager
{
	/**
 	 * class variables
 	 */
	public $today;
	public $yesterday;
	
	/**
	 * Class constructor
	 *
	 * @param n/a
	 * @return n/a
	 */
	public function __construct()
	{
		$this->today = date('Y-m-d');
		$this->yesterday = date('Y-m-d', time() - 3600*24);
	}
	
	/**
	 * Function to get daily sales
	 *
	 * @param $time string target date, accepted params : all, today, yesterday, alluptodate or date in the format yyyy-mm-dd
	 * @param $time2 string date in the format yyyy-mm-dd when alluptodate is selected
	 * @return int the number of sales
	 * alluptodate is used to sum total upto $time2
	 */
	public function getDailySales($time, $time2 = '')
	{
		$sales = 0;
		$time = mysql_real_escape_string($time);
		$time2 = mysql_real_escape_string($time2);
		
		switch($time){
			
			case "all":
				list($sales) =  mysql_fetch_row(mysql_query("SELECT SUM(sales_count) FROM sales_stats"));
			break;
			
			case "alluptodate":
				list($sales) =  mysql_fetch_row(mysql_query("SELECT SUM(sales_count) FROM sales_stats WHERE added_at < '$time2'"));
			break;
			
			case "today":
				$sales =  mysql_num_rows(mysql_query("SELECT NULL FROM ebay_orders WHERE added_at LIKE '$this->today%'"));
			break;
			
			case "yesterday":
				list($sales) =  mysql_fetch_row(mysql_query("SELECT sales_count FROM sales_stats WHERE added_at LIKE '$this->yesterday'"));
			break;
			
			default:
				list($sales) =  mysql_fetch_row(mysql_query("SELECT sales_count FROM sales_stats WHERE added_at LIKE '$time'"));
		}
		
		return custom_number_format((int)$sales, 0);
	}
	
	/**
	 * Function to get daily profit
	 *
	 * @param $time string target date, accepted params : all, today, yesterday, alluptodate or date in the format yyyy-mm-dd
	 * @param $time2 string date in the format yyyy-mm-dd when alluptodate is selected
	 * @return float the amount of profit
	 * alluptodate is used to sum total upto $time2
	 */
	public function getDailyProfit($time, $time2 = '')
	{
		$profit = 0;
		$time = mysql_real_escape_string($time);
		$time2 = mysql_real_escape_string($time2);
		
		switch($time){
			
			case "all":
				list($profit) = mysql_fetch_row(mysql_query("SELECT SUM(total_profit) FROM sales_stats"));
			break;
			
			case "alluptodate":
				list($profit) = mysql_fetch_row(mysql_query("SELECT SUM(total_profit) FROM sales_stats WHERE added_at < '$time2'"));
			break;
			
			case "today":
				list($profit) = mysql_fetch_row(mysql_query("SELECT SUM(amount_profit) FROM ebay_orders WHERE added_at LIKE '$this->today%'"));
			break;
			
			case "yesterday":
				list($profit) = mysql_fetch_row(mysql_query("SELECT total_profit FROM sales_stats WHERE added_at LIKE '$this->yesterday'"));
			break;
			
			default:
				list($profit) = mysql_fetch_row(mysql_query("SELECT total_profit FROM sales_stats WHERE added_at LIKE '$time'"));

		}
		
		return custom_number_format((float)$profit, 2);
	}
	
	/**
	 * Function to forecast sales
	 *
	 * @param $date date any date to compute forecast
	 * @return int forecast sales
	 * calculates using MA method. Uses last 3 days performance
	 */
	public function forecastSales($date = '')
	{
		$a = $b = $c = 0;
		
		if(empty($date))$date = 'NOW()';
		else $date = "'".mysql_real_escape_string($date)."'";
		
		list($a) = mysql_fetch_row(mysql_query("SELECT sales_count FROM sales_stats WHERE added_at < DATE_SUB($date, INTERVAL 1 DAY) AND added_at >= DATE_SUB($date, INTERVAL 2 DAY)"));
		list($b) = mysql_fetch_row(mysql_query("SELECT sales_count FROM sales_stats WHERE added_at < DATE_SUB($date, INTERVAL 2 DAY) AND added_at >= DATE_SUB($date, INTERVAL 3 DAY)"));
		list($c) = mysql_fetch_row(mysql_query("SELECT sales_count FROM sales_stats WHERE added_at < DATE_SUB($date, INTERVAL 3 DAY) AND added_at >= DATE_SUB($date, INTERVAL 4 DAY)"));
		
		return (int)(($a+$b+$c)/3);
	}
	
	/**
	 * Function to forecast profit
	 *
	 * @param $date date any date to compute forecast
	 * @return float forecast profit
	 * calculates using MA method. Uses last 3 days performance
	 */
	public function forecastProfit($date = '')
	{
		$a = $b = $c = 0;
		
		if(empty($date))$date = 'NOW()';
		else $date = "'".mysql_real_escape_string($date)."'";
		
		list($a) = mysql_fetch_row(mysql_query("SELECT total_profit FROM sales_stats WHERE added_at < DATE_SUB($date, INTERVAL 1 DAY) AND added_at >= DATE_SUB($date, INTERVAL 2 DAY)"));
		list($b) = mysql_fetch_row(mysql_query("SELECT total_profit FROM sales_stats WHERE added_at < DATE_SUB($date, INTERVAL 2 DAY) AND added_at >= DATE_SUB($date, INTERVAL 3 DAY)"));
		list($c) = mysql_fetch_row(mysql_query("SELECT total_profit FROM sales_stats WHERE added_at < DATE_SUB($date, INTERVAL 3 DAY) AND added_at >= DATE_SUB($date, INTERVAL 4 DAY)"));
		
		return custom_number_format(($a+$b+$c)/3, 2);
	}
	
	/**
	 * Function to get last 30days sales trend
	 *
	 * @param n/a
	 * @return array containing 30 days sales record with respect to date
	 */
	public function salesTrend()
	{
		$sales = array();
		$q = mysql_query("SELECT sales_count, UNIX_TIMESTAMP(added_at)*1000 AS added_at FROM sales_stats WHERE added_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
		while($res = mysql_fetch_assoc($q)){
			$sales[] = array($res['added_at'], $res['sales_count']);
		}
		return $sales;	
	}
	
	/**
	 * Function to get last 30days sales profit
	 *
	 * @param n/a
	 * @return array containing 30 days profit record with respect to date
	 */
	public function profitTrend()
	{
		$profit = array();
		$q = mysql_query("SELECT total_profit,UNIX_TIMESTAMP(added_at)*1000 AS added_at FROM sales_stats WHERE added_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
		while($res = mysql_fetch_assoc($q)){
			$profit[] = array($res['added_at'], $res['total_profit']);
		}
		return $profit;	
	}
}

?>