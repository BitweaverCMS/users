<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/index.php,v 1.1.1.1.2.9 2005/10/01 13:09:34 spiderr Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: index.php,v 1.1.1.1.2.9 2005/10/01 13:09:34 spiderr Exp $
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

// custom userfields
if( !empty( $gBitSystem->mPrefs['custom_user_fields'] ) ) {
	$customFields= explode( ',', $gBitSystem->mPrefs['custom_user_fields']  );
	$gBitSmarty->assign('customFields', $customFields);
}
// lookup may be via content_id which will then return user_id for search request
require_once( USERS_PKG_PATH.'lookup_user_inc.php' );
$search_request = '';
if (!empty($_REQUEST['home'])) {
	$search_request = $_REQUEST['home'];
	}
if( !empty( $_REQUEST['home'] ) ) {
	$gBitSmarty->assign( 'home', $_REQUEST['home'] );
	$gQueryUserId = $_REQUEST['home'];
	if( $gQueryUser->isValid() ) {
		$gBitSmarty->assign( 'gQueryUserId', $gQueryUserId );
	}

	if( $gQueryUser->canCustomizeTheme() ) {
		$userHomeStyle = $gQueryUser->getPreference( 'theme' );
		if( isset( $userHomeStyle ) ) {
			$gBitSystem->setStyle($userHomeStyle );
			$gBitSystem->mStyles['styleSheet'] = $gBitSystem->getStyleCss( $userHomeStyle, $_REQUEST['home'] );
			$gBitSmarty->assign( 'userStyle', $userHomeStyle );
		}
	}
	$userHomeTitle = $gQueryUser->getPreference( 'homepage_title' );
	if (!$userHomeTitle) {
		$userHomeTitle = $gQueryUser->getDisplayName()."'s Homepage";
	}
	$browserTitle = $userHomeTitle;
	//$_REQUEST['page'] = $userHomeTitle; // $_REQUEST['page'] should be used for requesting a page #! - drewslater

// need to loadLayout prematurely (usually happens in modules_inc.php) so we can see if we have any center pieces
	if( $gQueryUser->canCustomizeLayout() ) {
		$homeName = $_REQUEST['home'];
	} else {
		$homeName = ROOT_USER_ID;
	}
	$layout = HOMEPAGE_LAYOUT;
	if( isset( $layout ) ) {
		$gBitSystem->loadLayout( $homeName, $layout, ACTIVE_PACKAGE, TRUE );
	}
	global $gCenterPieces;
	$centerDisplay = ( count( $gCenterPieces ) ? 'bitpackage:kernel/dynamic.tpl' : 'bitpackage:users/center_user_wiki_page.tpl' );
} elseif (empty($search_request)) {
	$gQueryUser->getList( $_REQUEST );
	$gBitSmarty->assign('search_request',$search_request);
	$gBitSmarty->assign_by_ref('users', $_REQUEST["data"]);
	$gBitSmarty->assign_by_ref('usercount', $_REQUEST["cant"]);
	if (isset($_REQUEST["numrows"]))
		$_REQUEST["control"]["numrows"] = $_REQUEST["numrows"];
	else
		$_REQUEST["control"]["numrows"] = 50;
	$_REQUEST["control"]["URL"] = USERS_PKG_URL."index.php";
	$gBitSmarty->assign_by_ref('control', $_REQUEST["control"]);
	$centerDisplay = 'bitpackage:users/index_list.tpl';
	$browserTitle = $siteTitle.' '.tra( 'Members' );
} else {
	$gBitSmarty->assign('msg',tra('User not found'));
	$centerDisplay = 'bitpackage:kernel/error.tpl';
	$browserTitle = $siteTitle.' '.tra( 'Members' );

}

$gBitSystem->display( $centerDisplay, $browserTitle );
?>
