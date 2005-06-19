<?php
// $Header: /cvsroot/bitweaver/_bit_users/change_password.php,v 1.1 2005/06/19 05:12:22 bitweaver Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// Initialization
require_once( '../bit_setup_inc.php' );
if (!isset($_REQUEST['login']))
	$_REQUEST['login'] = '';
if (!isset($_REQUEST["oldpass"]))
	$_REQUEST["oldpass"] = '';
$smarty->assign('login', $_REQUEST['login']);
$smarty->assign('oldpass', $_REQUEST["oldpass"]);
if (isset($_REQUEST["change"])) {
	
	if ($_REQUEST["pass"] != $_REQUEST["pass2"]) {
		$smarty->assign('msg', tra("The passwords didn't match"));
		$gBitSystem->display( 'error.tpl' );
		die;
	}
	if ($_REQUEST["pass"] == $_REQUEST["oldpass"]) {
		$smarty->assign('msg', tra("You can not use the same password again"));
		$gBitSystem->display( 'error.tpl' );
		die;
	}
	if (!$gBitUser->validate_user($_REQUEST['login'], $_REQUEST["oldpass"], '', '')) {
		if(!$gBitUser->validate_user("admin",substr($_REQUEST["oldpass"],6,200),'','') or (!$gBitUser->isAdmin())) {
			$smarty->assign('msg', tra("Invalid old password"));
			$gBitSystem->display( 'error.tpl' );
		die;
		}
	}
	//Validate password here
	if (strlen($_REQUEST["pass"]) < $min_pass_length) {
		$smarty->assign('msg', tra("Password should be at least"). ' ' . $min_pass_length . ' ' . tra("characters long"));
		$gBitSystem->display( 'error.tpl' );
		die;
	}
	// Check this code
	if ($pass_chr_num == 'y') {
		if (!preg_match_all("/[0-9]+/", $_REQUEST["pass"], $foo) || !preg_match_all("/[A-Za-z]+/", $_REQUEST["pass"], $foo)) {
			$smarty->assign('msg', tra("Password must contain both letters and numbers"));
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
