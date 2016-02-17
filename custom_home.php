<?php
/**
 * custom home page
 *
 * @copyright (c) 2004-15 bitweaver.org
 *
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );
//ini_set('include_path','.;pear/');
//include('foobar.php');
/*
hfd
require_once "lib/NNTP.php";
$nntp = new Net_NNTP;
$ret = $nntp->connect("news.php.net");
$groups = $nntp->getGroups();
//print_r($groups);
$z = $nntp->selectGroup('php.announce');
print_r($z);
$h = $nntp->splitHeaders(1);
print_r($h);
$b = $nntp->getBody(1);
print_r($b);
*/
$gBitSystem->verifyFeature( 'users_custom_home' );
// Display the template
$gBitSystem->display( 'bitpackage:users/custom_home_2.tpl', NULL, array( 'display_mode' => 'display' ));
?>
