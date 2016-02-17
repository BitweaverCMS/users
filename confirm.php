<?php
/**
 * confirm password change
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

if( !empty( $_REQUEST["v"] ) && strpos( $_REQUEST["v"], ':' ) ) {
	list( $userId, $provPass ) = explode( ':', $_REQUEST["v"] );
}

if( !empty( $userId ) && !empty( $provPass ) && $userInfo = $gBitUser->confirmRegistration( $userId, $provPass ) ) {
	$gBitSmarty->assignByRef( 'userInfo', $userInfo );
	$gBitSystem->display( 'bitpackage:users/change_password.tpl', 'Confirm Password Change' , array( 'display_mode' => 'display' ));
} else {
	$gBitSystem->fatalError( tra("This confirmation link is no longer valid.  Please Login or <a href=\"".USERS_PKG_URL."remind_password.php\">request a new password change</a>"), NULL, NULL, HttpStatusCodes::HTTP_GONE );
}
?>
