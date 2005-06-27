<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/logout.php,v 1.1.1.1.2.3 2005/06/27 17:48:00 lsces Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: logout.php,v 1.1.1.1.2.3 2005/06/27 17:48:00 lsces Exp $
 * @package users
 * @subpackage functions
 */

$bypass_siteclose_check = 'y';

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
global $gBitSystem;
// go offline in Live Support
if ($gBitSystem->isPackageActive( 'LIVE_SUPPORT_PKG_NAME' ) ) {
	include_once( LIVE_SUPPORT_PKG_PATH.'ls_lib.php' );
	if ($lslib->get_operator_status($user) != 'offline') {
		$lslib->set_operator_status($user, 'offline');
	}
}
$gBitUser->logout();
header ("location: ".$gBitSystem->getDefaultPage());
exit;
?>
