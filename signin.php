<?php
/**
 * site sign in
 *
 * @copyright (c) 2004-15 bitweaver.org
 *
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
include_once ("../kernel/setup_inc.php");

if( !empty( $_REQUEST['returnto'] ) ) {
	$_SESSION['returnto'] = $_REQUEST['returnto'];
} elseif( !empty( $_SERVER['HTTP_REFERER'] ) && !strpos( $_SERVER['HTTP_REFERER'], 'login.php' )  && !strpos( $_SERVER['HTTP_REFERER'], 'register.php' ) ) {
	$from = parse_url( $_SERVER['HTTP_REFERER'] );
	if( !empty( $from['path'] ) && $from['host'] == $_SERVER['SERVER_NAME'] ) {
		$_SESSION['loginfrom'] = $from['path'].'?'.( !empty( $from['query'] ) ? $from['query'] : '' );
	}
}

if( $gBitUser->isRegistered() ) {
	bit_redirect( $gBitSystem->getConfig( 'users_login_homepage', $gBitSystem->getDefaultPage() ) );
}

if( !empty( $_REQUEST['error'] ) ) {
	$gBitSmarty->assign( 'error', $_REQUEST['error'] );
	$gBitSystem->setHttpStatus( HttpStatusCodes::HTTP_UNAUTHORIZED );
}

$languages = array();
$languages = $gBitLanguage->listLanguages();
$gBitSmarty->assign_by_ref( 'languages', $languages );
$gBitSmarty->assign_by_ref( 'gBitLanguage', $gBitLanguage );

$gBitSmarty->assign( 'metaKeywords', 'Login, Sign in, Registration, Register, Create new account' );
$gBitSystem->display( 'bitpackage:users/signin.tpl', $gBitSystem->getConfig( 'site_title' ).' Login' , array( 'display_mode' => 'display' ));
?>
