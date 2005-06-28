<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/index.php,v 1.2 2005/06/28 07:46:23 spiderr Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: index.php,v 1.2 2005/06/28 07:46:23 spiderr Exp $
 * @package users
 * @subpackage functions
 */
global $gQueryUserId, $gBitSystem;

/**
 * required setup
 */
define('ACTIVE_PACKAGE', 'users');	// Todo: use a different $_SERVER variable to properly determine the active package
require_once( '../bit_setup_inc.php' );
global $gBitSystem;
require_once( LIBERTY_PKG_PATH."LibertyStructure.php" );

require_once( USERS_PKG_PATH.'lookup_user_inc.php' );
if( !empty( $_REQUEST['home'] ) ) {
	$smarty->assign( 'home', $_REQUEST['home'] );
	$gQueryUserId = $_REQUEST['home'];
	if( $gQueryUser->isValid() ) {
		$smarty->assign( 'gQueryUserId', $gQueryUserId );
	}

	if( $gBitSystem->getPreference('feature_user_theme') ) {
		$userHomeStyle = $gQueryUser->getPreference( 'theme' );
		if( isset( $userHomeStyle ) ) {
			$gBitSystem->setStyle($userHomeStyle );
			$gBitLoc['styleSheet'] = $gBitSystem->getStyleCss( $userHomeStyle, $_REQUEST['home'] );
			$smarty->assign( 'userStyle', $userHomeStyle );
		}
	}
	$userHomeTitle = $gQueryUser->getPreference( 'homepage_title' );
	if (!$userHomeTitle) {
		$userHomeTitle = $gQueryUser->getDisplayName()."'s Homepage";
	}
	$browserTitle = $userHomeTitle;
	//$_REQUEST['page'] = $userHomeTitle; // $_REQUEST['page'] should be used for requesting a page #! - drewslater

// need to loadLayout prematurely (usually happens in modules_inc.php) so we can see if we have any center pieces
	if( $gBitSystem->getPreference('feature_user_layout') == 'h' ) {
		$user_name = $_REQUEST['home'];
	} elseif( $gBitSystem->getPreference('feature_user_layout') == 'y' ) {
		$user_name = $gBitUser->mUserId;
	} else {
		$user_name = ROOT_USER_ID;
	}
	$layout = HOMEPAGE_LAYOUT;
	if( isset( $layout ) ) {
		$gBitSystem->loadLayout( $user_name, $layout, ACTIVE_PACKAGE, TRUE );
	}
	global $gCenterPieces;
	$centerDisplay = ( count( $gCenterPieces ) ? 'bitpackage:kernel/dynamic.tpl' : 'bitpackage:users/center_user_wiki_page.tpl' );
} else {
	$gQueryUser->getList( $_REQUEST );
	$smarty->assign_by_ref('users', $_REQUEST["data"]);
	if (isset($_REQUEST["numrows"]))
		$_REQUEST["control"]["numrows"] = $_REQUEST["numrows"];
	else
		$_REQUEST["control"]["numrows"] = 50;
	$_REQUEST["control"]["URL"] = USERS_PKG_URL."index.php";
	$smarty->assign_by_ref('control', $_REQUEST["control"]);
	$centerDisplay = 'bitpackage:users/index_list.tpl';
	$browserTitle = $siteTitle.' '.tra( 'Members' );
}

$gBitSystem->display( $centerDisplay, $browserTitle );
?>
