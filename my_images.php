<?php
/**
 * my images
 *
 * @copyright (c) 2004-15 bitweaver.org
 *
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

// User preferences screen
$gBitSystem->verifyFeature( 'users_preferences' );

if( !$gBitUser->isRegistered() ) {
	$gBitSystem->fatalError( tra( "You are not logged in" ));
}

include_once( USERS_PKG_PATH.'lookup_user_inc.php' );

if( $gQueryUser->mUserId != $gBitUser->mUserId && !$gBitUser->hasPermission( 'p_users_admin' ) ) {
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
