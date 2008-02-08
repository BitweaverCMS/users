<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/lookup_user_inc.php,v 1.12 2008/02/08 07:44:42 spiderr Exp $
 *
 * @package users
 * @subpackage functions
 */
global $gQueryUser;

/**
 * This is a centralized include file to setup $gQueryUser var if you need to display detailed information about an arbitrary user.
 */
// fHomepage stuff is for backwards comability
if( isset( $_REQUEST['fHomepage'] )) {
	$_REQUEST['home'] = $_REQUEST['fHomepage'];
} elseif( isset( $_REQUEST['home'] )) {
	$_REQUEST['fHomepage'] = $_REQUEST['home'];
} elseif( @BitBase::verifyId( $_REQUEST['content_id'] )) {
	// This identifies the user_id associated with the content_id
	$_REQUEST['home'] = $gBitUser->getUserFromContentId( $_REQUEST['content_id'] );
} elseif( @BitBase::verifyId( $_REQUEST['user_id'] )) {
	$userInfo = $gBitUser->getUserInfo( array( 'user_id' => $_REQUEST['user_id'] ));
	$_REQUEST['home'] = !empty( $userInfo['login'] ) ? $userInfo['login'] : NULL;
}

if( isset( $_REQUEST['home'] )) {
	// this allows for a numeric user_id or alpha_numeric user_id
	$queryUserId = $gBitUser->lookupHomepage( $_REQUEST['home'], $gBitSystem->getConfig( 'users_case_sensitive_login', 'y' ) == 'y' );
	$gQueryUser = new BitPermUser( $queryUserId );
	$gQueryUser->load( TRUE );
} elseif( $gBitUser->isValid() ) {
	// We are looking at ourself, use our existing BitUser
	global $gBitUser;
	$gQueryUser = &$gBitUser;
}

if( !$gBitUser->isAdmin() ) {
	if( $gQueryUser->mUserId != $gBitUser->mUserId && $gQueryUser->getPreference( 'users_information' ) == 'private' ) {
		$gBitSystem->setHttpStatus( 403 );
		$gBitSystem->fatalError( tra( "The user has choosen to make his information private" ));
	}
}

if( $gQueryUser->isValid() ) {
	$gQueryUser->sanitizeUserInfo();
	$gBitSmarty->assign_by_ref( 'gQueryUser', $gQueryUser );
	$gBitSmarty->assign_by_ref( 'userInfo', $gQueryUser->mInfo );
	$gBitSmarty->assign_by_ref( 'userPrefs', $gQueryUser->mPrefs );
	$gBitSmarty->assign( 'homepage_header', $gQueryUser->getPreference( 'homepage_header' ) );
}
?>
