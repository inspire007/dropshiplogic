<?php
/**
 * Listings file
 *
 * @package EbaySalesAutomation
 * @subpackage Listing
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

$page_title = 'Listings | '.PAGE_TITLE;

include(dirname(__FILE__).'/classes/stats.class.php');
include(dirname(__FILE__).'/templates/header.php');
include(dirname(__FILE__).'/templates/listings.php');
include(dirname(__FILE__).'/templates/footer.php');

?>
