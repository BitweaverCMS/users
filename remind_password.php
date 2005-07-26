<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/remind_password.php,v 1.1.1.1.2.3 2005/07/26 15:50:30 drewslater Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: remind_password.php,v 1.1.1.1.2.3 2005/07/26 15:50:30 drewslater Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
if ($forgotPass != 'y') {
	$gBitSmarty->assign('msg', tra("This feature is disabled").": forgotPass");
	$gBitSystem->display( 'error.tpl' );
	die;
} elseif( $gBitUser->isRegistered() ) {
	header( 'Location: '.BIT_ROOT_URL );
	die;
} elseif (isset($_REQUEST["remind"])) {
	if( $userInfo = $gBitUser->getUserInfo( array( 'login' => $_REQUEST["username"] ) ) ) {
		if ( $gBitSystem->isFeatureActive( 'feature_clear_passwords' ) && !empty($userInfo['password']) ) {
			$pass = $userInfo['password'];
			$tmp['success'] = tra("A password reminder email has been sent ");
		} else {
			$pass = $gBitUser->renew_user_password($_REQUEST["username"]);
			$tmp['success'] = tra("A new password has been sent ");
		}
		$tmp['success'] .= tra("to the registered email address for")." " . $_REQUEST["username"] . ".";

		$gBitSmarty->assign('mail_site', $_SERVER["SERVER_NAME"]);
		$gBitSmarty->assign('mail_user', $_REQUEST["username"]);
		$gBitSmarty->assign('mail_same', $gBitSystem->isFeatureActive( 'feature_clear_passwords' ));
		$gBitSmarty->assign('mail_pass', $pass);
		$mail_data = $gBitSmarty->fetch('bitpackage:users/password_reminder.tpl');
		$subject = "Your password for ".$gBitSystem->getPreference( 'siteTitle', $_SERVER['HTTP_HOST'] );
		mail( $userInfo['email'], $subject, $mail_data, "From: ".$gBitSystem->getPreference( 'sender_email' )."\r\nContent-type: text/plain;charset=utf-8\r\n");
		// Just show "success" message and no form
	} else {
		// Show error message (and leave form visible so user can fix problem)
		$gBitSmarty->assign('showmsg', 'e');
		$tmp['error'] = tra("Invalid or unknown username").": ".$_REQUEST["username"];
	}
	$gBitSmarty->assign('msg', $tmp);
}
// Display the template
$gBitSystem->display( 'bitpackage:users/remind_password.tpl');
?>
