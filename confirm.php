<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/confirm.php,v 1.5 2006/09/12 19:26:48 spiderr Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: confirm.php,v 1.5 2006/09/12 19:26:48 spiderr Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

if( !empty( $_REQUEST["v"] ) && strpos( $_REQUEST["v"], ':' ) ) {
	list( $userId, $provPass ) = split( ':', $_REQUEST["v"] );
}

if( !empty( $userId ) && !empty( $provPass ) && $userInfo = $gBitUser->confirmRegistration( $userId, $provPass ) ) {
	$gBitSmarty->assign_by_ref( 'userInfo', $userInfo );
	$gBitSystem->display( 'bitpackage:users/change_password.tpl', 'Confrim Password Change' );
} else {
	$gBitSystem->fatalError( tra("This confirmation link is no longer valid.  Please Login or <a href=\"".USERS_PKG_URL."remind_password.php\">request a new password change</a>") );
}
?>
