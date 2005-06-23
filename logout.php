<?php
// $Header: /cvsroot/bitweaver/_bit_users/logout.php,v 1.1.1.1.2.1 2005/06/23 10:58:52 wolff_borg Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// Initialization
$bypass_siteclose_check = 'y';
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
header ("location: ".BIT_ROOT_URL.$gBitSystem->getDefaultPage());
exit;
?>
