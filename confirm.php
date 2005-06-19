<?php
// $Header: /cvsroot/bitweaver/_bit_users/confirm.php,v 1.1 2005/06/19 05:12:22 bitweaver Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// Initialization
require_once( '../bit_setup_inc.php' );

if( $userInfo = $gBitUser->confirmRegistration( $_REQUEST["user"], $_REQUEST["pass"] ) ) {
	$smarty->assign_by_ref( 'userInfo', $userInfo );
	$gBitSystem->display( 'bitpackage:users/change_password.tpl' );
} else {
	$smarty->assign('msg', tra("Invalid username or password"));
	$gBitSystem->display( 'error.tpl' );
}
?>
