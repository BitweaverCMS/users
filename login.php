<?php
// $Header: /cvsroot/bitweaver/_bit_users/login.php,v 1.1 2005/06/19 05:12:22 bitweaver Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
# $Header: /cvsroot/bitweaver/_bit_users/login.php,v 1.1 2005/06/19 05:12:22 bitweaver Exp $
include_once ("../bit_setup_inc.php");

if( $gBitUser->isRegistered() ) {
	header( 'Location: '.USERS_PKG_URL.'my.php' );
	die;
}

if( !empty( $_REQUEST['error'] ) ) {
	$smarty->assign( 'error', $_REQUEST['error'] );
}

$gBitSystem->display( 'bitpackage:users/login.tpl');

$gBitSystem->setBrowserTitle( $gBitSystem->getPreference( 'siteTitle' ).' Login' );
?>
