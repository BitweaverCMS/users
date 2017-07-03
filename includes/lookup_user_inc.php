<?php
/**
 * $Header$
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
	$userInfo = $gBitUser->getUserInfo( array( 'content_id' => $_REQUEST['content_id'] ));
	$_REQUEST['home'] = !empty( $userInfo['login'] ) ? $userInfo['login'] : NULL;
} elseif( @BitBase::verifyId( $_REQUEST['user_id'] )) {
	$userInfo = $gBitUser->getUserInfo( array( 'user_id' => $_REQUEST['user_id'] ));
	$_REQUEST['home'] = !empty( $userInfo['login'] ) ? $userInfo['login'] : NULL;
}

if( isset( $_REQUEST['home'] )) {
	// this allows for a numeric user_id or alpha_numeric user_id
	$queryUserId = $gBitUser->lookupHomepage( $_REQUEST['home'], $gBitSystem->getConfig( 'users_case_sensitive_login', 'y' ) == 'y' );
	$userClass = $gBitSystem->getConfig( 'user_class', (defined('ROLE_MODEL') ) ?  'RolePermUser' : 'BitPermUser' );
	require_once( USERS_PKG_PATH.'includes/' . $userClass .'.php' );
	$gQueryUser = new $userClass( $queryUserId );
	$gQueryUser->load( TRUE );
	$gQueryUser->setCacheableObject( FALSE );
} elseif( $gBitUser->isValid() ) {
	// We are looking at ourself, use our existing BitUser
	global $gBitUser;
	$gQueryUser = &$gBitUser;
}

if( !$gBitUser->hasPermission( 'p_users_admin' ) ) {
	if( $gQueryUser->mUserId != $gBitUser->mUserId && $gQueryUser->getPreference( 'users_information' ) == 'private' ) {
		// don't spit error for SEO reasons
		$gBitSmarty->assign( 'metaNoIndex', TRUE );
		$gBitSystem->fatalError( tra( "This information is private" ) , NULL, NULL, HttpStatusCodes::HTTP_NOT_FOUND );
	}
}

if( $gQueryUser->isValid() ) {
	$gQueryUser->sanitizeUserInfo();
	$gBitSmarty->assignByRef( 'gQueryUser', $gQueryUser );
	$gBitSmarty->assignByRef( 'userInfo', $gQueryUser->mInfo );
	$gBitSmarty->assignByRef( 'userPrefs', $gQueryUser->mPrefs );
	$gBitSmarty->assign( 'homepage_header', $gQueryUser->getPreference( 'homepage_header' ) );
}
?>
