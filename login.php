<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/login.php,v 1.2 2005/06/28 07:46:23 spiderr Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: login.php,v 1.2 2005/06/28 07:46:23 spiderr Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
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
