<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/change_password.php,v 1.11 2007/11/09 10:29:39 joasch Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: change_password.php,v 1.11 2007/11/09 10:29:39 joasch Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
if (!isset($_REQUEST['login']))
	$_REQUEST['login'] = '';
if (!isset($_REQUEST["oldpass"]))
	$_REQUEST["oldpass"] = '';
if (!isset($_REQUEST["provpass"]))
	$_REQUEST["provpass"] = '';

$gBitSmarty->assign('login', $_REQUEST['login']);
$gBitSmarty->assign('oldpass', $_REQUEST["oldpass"]);
$gBitSmarty->assign('provpass', $_REQUEST["provpass"]);
if (isset($_REQUEST["change"])) {

	$userInfo = $gBitUser->getUserInfo( array( 'user_id' => $_REQUEST['user_id'] ) );

	if ($_REQUEST["pass"] == $_REQUEST["oldpass"]) {
		$gBitSystem->fatalError( tra("You can not use the same password again") );
	}
	
    if( $passswordError = $gBitUser->verifyPasswordFormat( $_REQUEST["pass"], $_REQUEST["pass2"] ) ) {
		$gBitSystem->fatalError( tra( $passswordError ));
	}

	$validated = FALSE;
	if( !empty( $_REQUEST["provpass"] ) ) {
		if( $validated = $gBitUser->confirmRegistration( $userInfo['user_id'], $_REQUEST["provpass"] ) ) {
			if( $gBitSystem->isFeatureActive( 'send_welcome_email' ) ) {
	            $siteName = $gBitSystem->getConfig('site_title', $_SERVER['HTTP_HOST'] );
	            $gBitSmarty->assign('siteName',$_SERVER["SERVER_NAME"]);
	            $gBitSmarty->assign('mail_site',$_SERVER["SERVER_NAME"]);
	            $gBitSmarty->assign('mail_user',$userInfo['login']);
				// Send the welcome mail
				$gBitSmarty->assign( 'mailPassword',$_REQUEST['pass'] );
				$gBitSmarty->assign( 'mailEmail',$validated['email'] );
				$mail_data = $gBitSmarty->fetch('bitpackage:users/welcome_mail.tpl');
				mail($validated["email"], tra( 'Welcome to' ).' '.$siteName,$mail_data,"From: ".$gBitSystem->getConfig('site_sender_email')."\r\nContent-type: text/plain;charset=utf-8\r\n");
 			}
		} else	{
				$gBitSystem->fatalError( tra("Password reset request is invalid or has expired") );
		}
	} elseif( $gBitUser->isRegistered() ) {
		if( !( $validated = $gBitUser->validate( $userInfo['login'], $_REQUEST["oldpass"], '', '' )) ) {
			$gBitSystem->fatalError( tra("Invalid old password") );
		}
	}

	if( $validated ) {
		$gBitUser->storePassword( $_REQUEST["pass"], $userInfo['login'] );
		$url = $gBitUser->login( $userInfo['login'], $_REQUEST["pass"] );
	}

	header ( "location: ".$url );
}

// Display the template
$gBitSystem->display( 'bitpackage:users/change_password.tpl', 'Change Password' );

?>
