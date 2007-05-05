<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/index.php,v 1.24 2007/05/05 08:46:51 spiderr Exp $
 *
 * $Id: index.php,v 1.24 2007/05/05 08:46:51 spiderr Exp $
 * @package users
 * @subpackage functions
 */
global $gQueryUserId, $gBitSystem;

/**
 * required setup
 */
// Todo: use a different $_SERVER variable to properly determine the active package
if( !defined( 'ACTIVE_PACKAGE' )) {
	define( 'ACTIVE_PACKAGE', 'users' );
}

require_once( '../bit_setup_inc.php' );
require_once( LIBERTY_PKG_PATH."LibertyStructure.php" );

// custom userfields
if( $gBitSystem->getConfig( 'custom_user_fields' )) {
	$customFields= explode( ',', $gBitSystem->getConfig( 'custom_user_fields' ));
	$gBitSmarty->assign( 'customFields', $customFields );
}

// lookup may be via content_id which will then return user_id for search request
require_once( USERS_PKG_PATH.'lookup_user_inc.php' );

// i think we should always allow looking at yourself - regardless of permissions
if( !empty( $_REQUEST['home'] ) && $gQueryUser->isValid() && (( $gBitUser->hasPermission( 'p_users_view_user_homepage' ) || $gBitUser->hasPermission( 'p_users_admin' )) || $gQueryUser->mUserId == $gBitUser->mUserId )) {
	$gQueryUserId = $gQueryUser->mUserId;
	if( $gQueryUser->isValid() ) {
		$gBitSmarty->assign( 'gQueryUserId', $gQueryUserId );
	}

	if( $gBitSystem->isPackageActive('stars') && $gBitSystem->isFeatureActive('stars_user_ratings')) {
		require( STARS_PKG_PATH."templates/user_ratings.php" );
	}

	if( $gQueryUser->canCustomizeTheme() ) {
		$userHomeStyle = $gQueryUser->getPreference( 'theme' );
		if( !empty( $userHomeStyle )) {
			$gBitThemes->setStyle( $userHomeStyle );
			$gBitThemes->mStyles['styleSheet'] = $gBitThemes->getStyleCss( $userHomeStyle, $gQueryUser->mUserId );
			$gBitSmarty->assign( 'userStyle', $userHomeStyle );
		}
	}

	$userHomeTitle = $gQueryUser->getPreference( 'homepage_title' );
	if( empty( $userHomeTitle )) {
		$userHomeTitle = $gQueryUser->getDisplayName()."'s Homepage";
	}
	$browserTitle = $userHomeTitle;

	// need to load layout now that we can check for center pieces
	$layoutHash['layout'] = 'home';
	$gBitThemes->loadLayout( $layoutHash );
	$centerDisplay = ( count( $gCenterPieces ) ? 'bitpackage:kernel/dynamic.tpl' : 'bitpackage:users/center_user_wiki_page.tpl' );

} else {
	$gBitSystem->verifyPermission( 'p_users_view_user_list' );
	$gQueryUser->getList( $_REQUEST );
	$gBitSmarty->assign_by_ref( 'users', $_REQUEST["data"] );
	$gBitSmarty->assign_by_ref( 'usercount', $_REQUEST["cant"] );
	// display an error message
	if( !empty( $_REQUEST['home'] )) {
		$feedback['error'] = tra( 'The following user could not be found' ).': '.$_REQUEST['home'];
		$gBitSmarty->assign( 'feedback', $feedback );
	}
	$_REQUEST['listInfo']["URL"] = USERS_PKG_URL."index.php";
	$gBitSmarty->assign_by_ref( 'control', $_REQUEST['listInfo'] );
	$gBitSmarty->assign_by_ref( 'listInfo', $_REQUEST['listInfo'] );
	$browserTitle = $gBitSystem->getConfig( 'site_title' ).' '.tra( 'Members' );
	$centerDisplay = 'bitpackage:users/index_list.tpl';
}

$gBitSmarty->assign( 'gBitLanguage', $gBitLanguage );
$gBitSystem->display( $centerDisplay, $browserTitle );
?>
