<?php
/**
 * password reminder
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

$gBitSystem->verifyFeature( 'users_forgot_pass' );

$pageTitle = tra( 'Request Password Reminder' );

if( $gBitUser->isRegistered() ) {
	header( 'Location: '.BIT_ROOT_URL );
	die;
} elseif (isset($_REQUEST["remind"])) {
	$userInfo = '';
	$_REQUEST["username"] = strip_tags( urldecode( $_REQUEST["username"]) );
	$pLogin = trim( $_REQUEST["username"] );
    if ( strlen ( $pLogin ) ) {
		$loginCol = strpos( $pLogin, '@' ) ? 'email' : 'login';
		$userInfo = $gBitUser->getUserInfo( array( $loginCol => $pLogin ) );
	}
	if( $userInfo ) {
		$pass = $gBitUser->genPass();
		list($pass,$provpass) = $gBitUser->createTempPassword( $_REQUEST["username"], $pass );
		$gBitSmarty->assign( 'mailProvPass', $provpass );
		$gBitSmarty->assign( 'mailUserId', $userInfo['user_id'] );
		$pageTitle = tra( 'Change Your Password' );
		$tmp['success'] = tra("Information to change your password has been sent to the registered email address for")." " . $_REQUEST["username"] . ".";

		$gBitSmarty->assign('mail_user', $userInfo[$loginCol]);
		$gBitSmarty->assign('mail_pass', $pass);
		$gBitSmarty->assign('linkUri', $gBitSystem->isFeatureActive("site_https_login_required") ? 'https://'.$_SERVER['HTTP_HOST'].USERS_PKG_URL : USERS_PKG_URI );
		$mail_data = $gBitSmarty->fetch('bitpackage:users/password_reminder.tpl');
		$subject = tra( "Your password for" ).": ".$gBitSystem->getConfig( 'site_title', $_SERVER['HTTP_HOST'] );
		mail( $userInfo['email'], $subject, $mail_data, "From: ".$gBitSystem->getConfig( 'site_sender_email' )."\nContent-type: text/plain;charset=utf-8\n");
		// Just show "success" message and no form
	} else {
		// Show error message (and leave form visible so user can fix problem)
		$gBitSmarty->assign('showmsg', 'e');
		$tmp['error'] = tra("Invalid or unknown username").": ".$_REQUEST["username"];
	}
	$gBitSmarty->assign('msg', $tmp);
}
// Display the template
$gBitSystem->display( 'bitpackage:users/remind_password.tpl', $pageTitle, array( 'display_mode' => 'display' ));
