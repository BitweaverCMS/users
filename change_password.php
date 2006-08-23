<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/change_password.php,v 1.6 2006/08/23 08:29:29 jht001 Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: change_password.php,v 1.6 2006/08/23 08:29:29 jht001 Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
if (!isset($_REQUEST['login']))
	$_REQUEST['login'] = '';
if (!isset($_REQUEST["oldpass"]))
	$_REQUEST["oldpass"] = '';
if (!isset($_REQUEST["provpass"]))
	$_REQUEST["provpass"] = '';
$gBitSmarty->assign('login', $_REQUEST['login']);
$gBitSmarty->assign('oldpass', $_REQUEST["oldpass"]);
$gBitSmarty->assign('provpass', $_REQUEST["provpass"]);
if (isset($_REQUEST["change"])) {

	if ($_REQUEST["pass"] != $_REQUEST["pass2"]) {
		$gBitSystem->fatalError( tra("The passwords didn't match") );
	}
	if ($_REQUEST["pass"] == $_REQUEST["oldpass"]) {
		$gBitSystem->fatalError( tra("You can not use the same password again") );
	}
    $passsword_error_msg = $gBitUser->verifyPasswordFormat( $_REQUEST["pass"] );
    if (strlen($passsword_error_msg)) {
		$gBitSystem->fatalError( $passsword_error_msg );
		}

	if (strlen($_REQUEST["provpass"]) ) {
		if (!$gBitUser->confirmRegistration($_REQUEST['login'], $_REQUEST["provpass"]) ) {
			$gBitSystem->fatalError( tra("Password reset request is invalid or has expired") );
		}
	}		
	elseif( !$gBitUser->isAdmin() && !$gBitUser->validate($_REQUEST['login'], $_REQUEST["oldpass"], '', '') ) {
		$gBitSystem->fatalError( tra("Invalid old password") );
	}

	$gBitUser->storePassword( $_REQUEST["pass"], $_REQUEST['login'] );
	$url = $gBitUser->login( $_REQUEST['login'], $_REQUEST["pass"] );
	header ( "location: ".$url );
}

// Display the template
$gBitSystem->display( 'bitpackage:users/change_password.tpl');
?>
