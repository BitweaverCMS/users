<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/lookup_user_inc.php,v 1.1.1.1.2.3 2005/07/26 15:50:30 drewslater Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: lookup_user_inc.php,v 1.1.1.1.2.3 2005/07/26 15:50:30 drewslater Exp $
 * @package users
 * @subpackage functions
 */
global $gQueryUser;

/**
 * This is a centralized include file to setup $gQueryUser var if you need to display detailed information about any arbitrary user.
 */
// Keep backward compatability
if (isset($_REQUEST['fHomepage'])) {
	$_REQUEST['home'] = $_REQUEST['fHomepage'];
}
if (isset($_REQUEST['home'])) {
	$_REQUEST['fHomepage'] = $_REQUEST['home'];
}

if (isset($_REQUEST['content_id'])) {
	// This identifies the user_id associated with the contact_id of a record
	// Used to allow access to user records via the generic index.php?content_id=x
	$_REQUEST['home'] = $gBitUser->getUserFromContentId($_REQUEST['content_id']);
}

if (isset($_REQUEST['home'])) {
	// this allows for a numeric user_id or alpha_numeric user_id
	$queryUserId = $gBitUser->lookupHomepage($_REQUEST['home'], $gBitSystem->getPreference('case_sensitive_login', 'y') == 'y');
	$_REQUEST['home'] = $queryUserId;
	$gQueryUser = new BitPermUser( $queryUserId );
	$gQueryUser->load( TRUE );
} elseif( $gBitUser->isValid() ) {
	// We are looking at ourself, use our existing BitUser
	global $gBitUser;
	$gQueryUser = &$gBitUser;
}

if (!$gBitUser->isAdmin()) {
	if( $gQueryUser->mUserPrefs['user_information'] == 'private') {
		$gBitSystem->fatalError( tra("The user has choosen to make his information private") );
		die;
	}
}

$gQueryUser->sanitizeUserInfo();
$gBitSmarty->assign_by_ref('gQueryUser', $gQueryUser);

if( $gQueryUser->isValid() ) {
	$gBitSmarty->assign_by_ref( 'userInfo', $gQueryUser->mInfo );
	$gBitSmarty->assign_by_ref( 'userPrefs', $gQueryUser->mUserPrefs );
	$gBitSmarty->assign( 'homepage_header', $gQueryUser->getPreference( 'homepage_header' ) );
}
?>
