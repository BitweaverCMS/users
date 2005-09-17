<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/my_images.php,v 1.1.1.1.2.4 2005/09/17 16:38:24 squareing Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: my_images.php,v 1.1.1.1.2.4 2005/09/17 16:38:24 squareing Exp $
 * @package users
 * @subpackage functions
 */
global $gEditMode;
$gEditMode = 'images';

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

// User preferences screen
if ($feature_userPreferences != 'y') {
	$gBitSmarty->assign('msg', tra("This feature is disabled").": feature_userPreferences");
	$gBitSystem->display( 'error.tpl' );
	die;
}
if (!$gBitUser->isRegistered()) {
	$gBitSmarty->assign('msg', tra("You are not logged in"));
	$gBitSystem->display( 'error.tpl' );
	die;
}
if (!isset($_REQUEST["showall"])) {
	$_REQUEST["showall"] = 'n';
}


include_once(USERS_PKG_PATH.'lookup_user_inc.php');

if ($gQueryUser->mUserId != $gBitUser->mUserId && !$gBitUser->object_has_permission($gBitUser->mUserId, $gQueryUser->mInfo['content_id'], 'bituser', 'bit_p_admin_user')) {
	$gBitSmarty->assign('msg', tra('You do not have permission to edit this user\'s images'));
	$gBitSystem->display('error.tpl');
	die;
}

$gBitSmarty->assign('showall', $_REQUEST["showall"]);
$userwatch = $gQueryUser->mUsername;
$gBitSmarty->assign('userwatch', $userwatch);
$_REQUEST["user_id"]=$gQueryUser->mUserId;

// Upload avatar is processed here
if( !empty( $_REQUEST['fSubmitBio'] ) ) {
	$gQueryUser->store( $_REQUEST );
	$url = $gQueryUser->getDisplayUrl( $gQueryUser->mInfo['login'] );
	header( "Location: ".$url );
} elseif( isset( $_REQUEST['fSubmitDeletePortait'] ) ) {
	$gQueryUser->purgePortrait();
} elseif( isset( $_REQUEST['fSubmitDeleteAvatar'] ) ) {
	$gQueryUser->purgeAvatar();
} elseif( isset( $_REQUEST['fSubmitDeleteLogo'] ) ) {
	$gQueryUser->purgeLogo();
}

if (isset($_REQUEST["uselib"])) {
	$avatarHash['type'] = AVATAR_TYPE_LIBRARY;
	$avatarHash['avatar_lib_name'] = $_REQUEST["avatar"];
	$avatarHash['avatar_name'] = NULL;
	$avatarHash['avatar_size'] = NULL;
	$avatarHash['avatar_type'] = NULL;
	$avatarHash['avatar_data'] = NULL;
	$gQueryUser->storeAvatar( $avatarHash );
}

// For some reason, we have to reassign here to make our changes to gBitUser->mInfo present in smarty.
// dunno why, but this fixes the bug. XOXO spiderr
$gBitSmarty->assign_by_ref('gQueryUser', $gQueryUser);

$gBitSystem->display( 'bitpackage:users/my_images.tpl');

?>
