<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/change_password.php,v 1.1.1.1.2.3 2005/07/26 15:50:30 drewslater Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: change_password.php,v 1.1.1.1.2.3 2005/07/26 15:50:30 drewslater Exp $
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
$gBitSmarty->assign('login', $_REQUEST['login']);
$gBitSmarty->assign('oldpass', $_REQUEST["oldpass"]);
if (isset($_REQUEST["change"])) {

	if ($_REQUEST["pass"] != $_REQUEST["pass2"]) {
		$gBitSystem->fatalError( tra("The passwords didn't match") );
	}
	if ($_REQUEST["pass"] == $_REQUEST["oldpass"]) {
		$gBitSystem->fatalError( tra("You can not use the same password again") );
	}
	if( !$gBitUser->isAdmin() && !$gBitUser->validate($_REQUEST['login'], $_REQUEST["oldpass"], '', '') ) {
		$gBitSystem->fatalError( tra("Invalid old password") );
	}
	//Validate password here
	if (strlen($_REQUEST["pass"]) < $min_pass_length) {
		$gBitSystem->fatalError(  tra("Password should be at least"). ' ' . $min_pass_length . ' ' . tra("characters long") );
	}
	// Check this code
	if ($pass_chr_num == 'y') {
		if (!preg_match_all("/[0-9]+/", $_REQUEST["pass"], $foo) || !preg_match_all("/[A-Za-z]+/", $_REQUEST["pass"], $foo)) {
			$gBitSmarty->assign('msg', tra("Password must contain both letters and numbers"));
			$gBitSystem->display( 'error.tpl' );
			die;
		}
	}
	$gBitUser->change_user_password($_REQUEST['login'], $_REQUEST["pass"]);
	$url = $gBitUser->login( $_REQUEST['login'], $_REQUEST["pass"] );
	header ( "location: ".$url );
}

// Display the template
$gBitSystem->display( 'bitpackage:users/change_password.tpl');
?>
