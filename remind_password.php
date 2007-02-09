<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/remind_password.php,v 1.21 2007/02/09 15:28:53 phoenixandy Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: remind_password.php,v 1.21 2007/02/09 15:28:53 phoenixandy Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

$gBitSystem->verifyFeature( 'users_forgot_pass' );

if( $gBitUser->isRegistered() ) {
	header( 'Location: '.BIT_ROOT_URL );
	die;
} elseif (isset($_REQUEST["remind"])) {
	$userInfo = '';
	$pLogin = trim( $_REQUEST["username"] );
    if ( strlen ( $pLogin ) ) {
		$loginCol = strpos( $pLogin, '@' ) ? 'email' : 'login';
		$userInfo = $gBitUser->getUserInfo( array( $loginCol => $pLogin ) );
	}
	if( $userInfo ) {
		if ( $gBitSystem->isFeatureActive( 'users_clear_passwords' ) && !empty($userInfo['user_password']) ) {
			$gBitSmarty->assign( 'userPass', $userInfo['user_password'] );
			$tmp['success'] = tra("A password reminder email has been sent ");
			$pass = $userInfo['user_password'];
		} else {
			$pass = $gBitUser->genPass();
			list($pass,$provpass) = $gBitUser->createTempPassword( $_REQUEST["username"], $pass );
			$gBitSmarty->assign( 'mailProvPass', $provpass );
			$gBitSmarty->assign( 'mailUserId', $userInfo['user_id'] );
			$tmp['success'] = tra("Information to reset your password has been sent ");
		}
		$tmp['success'] .= tra("to the registered email address for")." " . $_REQUEST["username"] . ".";

		$gBitSmarty->assign('mail_user', $userInfo[$loginCol]);
		$gBitSmarty->assign('mail_same', $gBitSystem->isFeatureActive( 'users_clear_passwords' ));
		$gBitSmarty->assign('mail_pass', $pass);
		$mail_data = $gBitSmarty->fetch('bitpackage:users/password_reminder.tpl');
		$subject = tra( "Your password for" ).": ".$gBitSystem->getConfig( 'site_title', $_SERVER['HTTP_HOST'] );
		mail( $userInfo['email'], $subject, $mail_data, "From: ".$gBitSystem->getConfig( 'site_sender_email' )."\r\nContent-type: text/plain;charset=utf-8\r\n");
		// Just show "success" message and no form
	} else {
		// Show error message (and leave form visible so user can fix problem)
		$gBitSmarty->assign('showmsg', 'e');
		$tmp['error'] = tra("Invalid or unknown username").": ".$_REQUEST["username"];
	}
	$gBitSmarty->assign('msg', $tmp);
}
// Display the template
$gBitSystem->display( 'bitpackage:users/remind_password.tpl', 'Request New Password' );
?>
