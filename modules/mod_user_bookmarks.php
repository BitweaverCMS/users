<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/modules/Attic/mod_user_bookmarks.php,v 1.1.1.1.2.3 2005/11/16 17:51:19 mej Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: mod_user_bookmarks.php,v 1.1.1.1.2.3 2005/11/16 17:51:19 mej Exp $
 * @package users
 * @subpackage modules
 */

if( $gBitSystem->isFeatureActive( 'feature_user_bookmarks' ) && $gBitUser->isRegistered() && $gBitUser->hasPermission( 'bit_p_create_bookmarks' ) ) {
	/**
	 * required setup
	 */
	include_once( USERS_PKG_PATH.'bookmark_lib.php' );
	global $bookmarklib;

	$setup_parsed_uri = parse_url($_SERVER["REQUEST_URI"]);
	if (isset($setup_parsed_uri["query"])) {
		parse_str($setup_parsed_uri["query"], $setup_query_data);
	} else {
		$setup_query_data = array();
	}

	// check the session to get the parent or create parent =0
	$gBitSmarty->assign('ownurl', httpPrefix(). $_SERVER["REQUEST_URI"]);
	if (!isset($_SESSION["bookmarks_parent"])) {
		$_SESSION["bookmarks_parent"] = 0;
	}
	if (isset($_REQUEST["bookmarks_parent"])) {
		$_SESSION["bookmarks_parent"] = $_REQUEST["bookmarks_parent"];
	}
	$ownurl = httpPrefix(). $_SERVER["REQUEST_URI"];
	// Now build urls
	if (strstr($ownurl, '?')) {
		$modb_sep = '&amp;';
	} else {
		$modb_sep = '?';
	}
	$gBitSmarty->assign('modb_sep', $modb_sep);
	if (isset($_REQUEST["bookmark_removeurl"])) {
		$bookmarklib->remove_url($_REQUEST["bookmark_removeurl"], $gBitUser->mUserId );
		header( 'Location: '.$_SERVER['HTTP_REFERER'] );
		die;
	} elseif (isset($_REQUEST["bookmark_create_folder"])) {
		$bookmarklib->add_folder($_SESSION["bookmarks_parent"], $_REQUEST['bookmark_urlname'], $gBitUser->mUserId );
	} elseif (isset($_REQUEST["bookmark_mark"])) {
		if (empty($_REQUEST["bookmark_urlname"])) {
			global $gContent, $gBitSystem;
			if( $gContent && $gContent->getTitle() ) {
				$_REQUEST["bookmark_urlname"] = $gContent->getTitle();
			} elseif( $gBitSystem->getBrowserTitle() ) {
				$_REQUEST["bookmark_urlname"] = $gBitSystem->getBrowserTitle();
			} else {
				$_REQUEST["bookmark_urlname"] = basename( $_SERVER['REQUEST_URI'] );
			}
		}
		if (!empty($_REQUEST["bookmark_urlname"])) {
			$bookmarklib->replace_url(0, $_SESSION["bookmarks_parent"], $_REQUEST["bookmark_urlname"], $ownurl, $gBitUser->mUserId );
		}
	}
	$modb_p_info = $bookmarklib->get_folder($_SESSION["bookmarks_parent"], $gBitUser->mUserId );
	$modb_father = $modb_p_info["parent_id"];
	// get folders for the parent
	$modb_urls = $bookmarklib->list_folder($_SESSION["bookmarks_parent"], 0, -1, 'name_asc', '', $gBitUser->mUserId );
	$gBitSmarty->assign('modb_urls', $modb_urls["data"]);
	$modb_folders = $bookmarklib->get_child_folders($_SESSION["bookmarks_parent"], $gBitUser->mUserId );
	$modb_pf = array(
		"name" => "..",
		"folder_id" => $modb_father,
		"parent_id" => 0,
		"user_id" => $gBitUser->mUserId
	);
	$modb_pfs = array($modb_pf);
	if ($_SESSION["bookmarks_parent"]) {
		$modb_folders = array_merge($modb_pfs, $modb_folders);
	}
	$gBitSmarty->assign('modb_folders', $modb_folders);
// get urls for the parent
}
?>
