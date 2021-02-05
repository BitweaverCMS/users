<?php
/**
 * logout
 *
 * @copyright (c) 2004-15 bitweaver.org
 *
 * @package users
 * @subpackage functions
 */

$bypass_siteclose_check = 'y';

/**
 * required setup
 */
require_once( '../kernel/includes/setup_inc.php' );
global $gBitSystem;
// go offline in Live Support
if ($gBitSystem->isPackageActive( 'LIVE_SUPPORT_PKG_NAME' ) ) {
	include_once( LIVE_SUPPORT_PKG_INCLUDE_PATH.'ls_lib.php' );
	if ($lslib->get_operator_status($user) != 'offline') {
		$lslib->set_operator_status($user, 'offline');
	}
}
$gBitUser->logout();
header ("location: ".$gBitSystem->getDefaultPage());
exit;
