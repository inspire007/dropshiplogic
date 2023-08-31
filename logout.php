<?php
/**
 * Logout script
 * Logouts users from the system
 *
 * @package EbaySalesAutomation
 * @subpackage Logout
 * @version 1.0
 * @author N/A
 * @copyright 2014
 */
 
include(dirname(__FILE__).'/config.php');

/**
	empty session
 */
$_SESSION = array();

/**
	send to login page
 */

header('location: login.php');
exit();

?>