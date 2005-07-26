<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/Attic/versions.php,v 1.1.1.1.2.2 2005/07/26 15:50:31 drewslater Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: versions.php,v 1.1.1.1.2.2 2005/07/26 15:50:31 drewslater Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
include_once( WIKI_PKG_PATH.'hist_lib.php');
if ($feature_wiki != 'y') {
	$gBitSmarty->assign('msg', tra("This feature is disabled").": feature_wiki");
	$gBitSystem->display( 'error.tpl' );
	die;
}
// Only an admin can use this script
if (!$gBitUser->isAdmin()) {
	$gBitSmarty->assign('msg', tra("You dont have permission to use this feature"));
	$gBitSystem->display( 'error.tpl' );
	die;
}
// We have to get the variable ruser as the user to check
if (!isset($_REQUEST["ruser"])) {
	$gBitSmarty->assign('msg', tra("No user indicated"));
	$gBitSystem->display( 'error.tpl' );
	die;
}
if (!$gBitUser->userExists( array( 'login' => $_REQUEST["ruser"] ) ) ) {
	$gBitSmarty->assign('msg', tra("Unexistant user"));
	$gBitSystem->display( 'error.tpl' );
	die;
}
$gBitSmarty->assign_by_ref('ruser', $_REQUEST["ruser"]);
$gBitSmarty->assign('preview', false);
if (isset($_REQUEST["preview"])) {
	$version = $histlib->get_version($_REQUEST["page"], $_REQUEST["version"]);
	$version["data"] = $gBitSystem->parseData($version["data"]);
	if ($version) {
		$gBitSmarty->assign_by_ref('preview', $version);
		$gBitSmarty->assign_by_ref('version', $_REQUEST["version"]);
	}
}
$history = $histlib->get_user_versions($_REQUEST["ruser"]);
$gBitSmarty->assign_by_ref('history', $history);

$gBitSystem->display( 'bitpackage:users/userversions.tpl');
?>
