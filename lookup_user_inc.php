<?php
// $Header: /cvsroot/bitweaver/_bit_users/lookup_user_inc.php,v 1.1 2005/06/19 05:12:21 bitweaver Exp $
// Copyright (c) 2004, bitweaver, Christian Fowler, Brian Todoroff, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
//
// This is a centralized include file to setup $gQueryUser var if you need to display detailed information about any arbitrary user.
//
global $gQueryUser;

// Keep backward compatability
if (isset($_REQUEST['fHomepage'])) {
	$_REQUEST['home'] = $_REQUEST['fHomepage'];
}
if (isset($_REQUEST['home'])) {
	$_REQUEST['fHomepage'] = $_REQUEST['home'];
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
$smarty->assign_by_ref('gQueryUser', $gQueryUser);

if( $gQueryUser->isValid() ) {
	$smarty->assign_by_ref( 'userInfo', $gQueryUser->mInfo );
	$smarty->assign_by_ref( 'userPrefs', $gQueryUser->mUserPrefs );
	$smarty->assign( 'homepage_header', $gQueryUser->getPreference( 'homepage_header' ) );
}
?>
