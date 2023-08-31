<?php
//exit('Upgrade in progress');
/*@
	If session not required i.e. cron scripts
*/
if(empty($no_session)){
	@session_start();
	@ob_start();
}
include(dirname(__FILE__).'/functions.php');
ini_set('date.timezone', 'UTC');

/*@
	Database connection variables
*/
define('DB_HOST','localhost');
define('DB_USER','temp');
define('DB_PASS','temp');
define('DB_NAME','autodropship');

/*@
	Ebay application
*/

define('EBAY_DEV_TOKEN','');
define('EBAY_APP_ID','');
define('EBAY_CERT_ID','');
define('EBAY_RU_NAME','');
define('EBAY_SANDBOX', 0);

/*@
	SamsClub
*/
define('SAMS_CLUB_EMAIL', '');
define('SAMS_CLUB_PASS', '');
define('SAMS_ORDER_DEFAULT_PHONE', '');

/*@
	Misc
*/
define('PAGE_TITLE', 'Sales Management App');
define('CURL_LIMIT', 10);

/*@
	initiatives
*/
ini_set('display_errors', 1);
set_time_limit(0);
ignore_user_abort(true);
error_reporting(false);

mysql_conn();
$settings = load_settings();

/*@
	auto or manual orders
*/
define('AUTO_ORDER', $settings['auto_order']);
define('ORDER_SAFETY_200', $settings['order_safety_200']);

/*@
	checks if login is required
*/
if(!empty($login_required) && empty($no_session)){
	if(!check_login()){
		header('location: login.php');
		exit();	
	}	
}

?>