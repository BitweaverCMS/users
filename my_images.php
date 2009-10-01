<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/my_images.php,v 1.14 2009/10/01 13:45:52 wjames5 Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
 *
 * $Id: my_images.php,v 1.14 2009/10/01 13:45:52 wjames5 Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

// User preferences screen
$gBitSystem->verifyFeature( 'users_preferences' );

if( !$gBitUser->isRegistered() ) {
	$gBitSystem->fatalError( tra( "You are not logged in" ));
}

include_once( USERS_PKG_PATH.'lookup_user_inc.php' );

if( $gQueryUser->mUserId != $gBitUser->mUserId ) {
	$gBitSystem->fatalError( tra( "You do not have permission to edit this user's images" ));
}

$_REQUEST["user_id"] = $gQueryUser->mUserId;

// Upload avatar is processed here
if( !empty( $_REQUEST['store'] )) {
	$gQueryUser->storeImages( $_REQUEST );
	bit_redirect( $gQueryUser->getDisplayUrl( $gQueryUser->mInfo['login'] ));
} elseif( !empty( $_REQUEST['delete_portrait'] )) {
	$gQueryUser->purgePortrait();
	$gQueryUser->load();
} elseif( !empty( $_REQUEST['delete_avatar'] )) {
	$gQueryUser->purgeAvatar();
	$gQueryUser->load();
} elseif( !empty( $_REQUEST['delete_logo'] )) {
	$gQueryUser->purgeLogo();
	$gQueryUser->load();
}

// For some reason, we have to reassign here to make our changes to gBitUser->mInfo present in smarty.
// dunno why, but this fixes the bug. XOXO spiderr
$gBitSmarty->assign_by_ref( 'gQueryUser', $gQueryUser );

$gBitSystem->display( 'bitpackage:users/my_images.tpl', tra( 'Personal Images' ), array( 'display_mode' => 'display' ));
?>
