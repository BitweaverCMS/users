<?php
/**
 * change password
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
if( !isset( $_REQUEST['login'] )) {
	$_REQUEST['login'] = '';
}
if( !isset( $_REQUEST['user_id'] )) {
	$_REQUEST['user_id'] = '';
}
if( !isset( $_REQUEST["oldpass"] )) {
	$_REQUEST["oldpass"] = '';
}
if( !isset( $_REQUEST["provpass"] )) {
	$_REQUEST["provpass"] = '';
}

$gBitSmarty->assign( 'login', $_REQUEST['login'] );
$gBitSmarty->assign( 'oldpass', $_REQUEST["oldpass"] );
$gBitSmarty->assign( 'provpass', $_REQUEST["provpass"] );

$userInfo = $gBitUser->getUserInfo( array( 'user_id' => $_REQUEST['user_id'] ));
$gBitSmarty->assign_by_ref( 'userInfo', $userInfo );

if( isset( $_REQUEST["change"] )) {

	if( $_REQUEST["pass"] == $_REQUEST["oldpass"] ) {
		$gBitSystem->fatalError( tra( "You can not use the same password again" ));
	}

	if( $passswordError = $gBitUser->verifyPasswordFormat( $_REQUEST["pass"], $_REQUEST["pass2"] )) {
		$gBitSystem->fatalError( tra( $passswordError ));
	}

	$validated = FALSE;
	if( !empty( $_REQUEST["provpass"] ) ) {
		if( $validated = $gBitUser->confirmRegistration( $userInfo['user_id'], $_REQUEST["provpass"] ) ) {
			if( $gBitSystem->isFeatureActive( 'send_welcome_email' ) ) {
				$siteName = $gBitSystem->getConfig( 'site_title', $_SERVER['HTTP_HOST'] );
				// Send the welcome mail
				$gBitSmarty->assign( 'siteName', $_SERVER["SERVER_NAME"] );
				$gBitSmarty->assign( 'mail_site', $_SERVER["SERVER_NAME"] );
				$gBitSmarty->assign( 'mail_user', $userInfo['login'] );
				$gBitSmarty->assign( 'mailPassword',$_REQUEST['pass'] );
				$gBitSmarty->assign( 'mailEmail',$validated['email'] );
				$mail_data = $gBitSmarty->fetch('bitpackage:users/welcome_mail.tpl');
				mail($validated["email"], tra( 'Welcome to' ).' '.$siteName,$mail_data,"From: ".$gBitSystem->getConfig('site_sender_email')."\r\nContent-type: text/plain;charset=utf-8\r\n");
			}
		} else	{
				$gBitSystem->fatalError( tra("Password reset request is invalid or has expired") );
		}
	} elseif( !( $validated = $gBitUser->validate( $userInfo['email'], $_REQUEST["oldpass"], '', '' )) ) {
		$gBitSystem->fatalError( tra("Invalid old password") );
	}

	if( $validated ) {
		$gBitUser->storePassword( $_REQUEST["pass"], (!empty( $userInfo['login'] )?$userInfo['login']:$userInfo['email']) );
		$url = $gBitUser->login( (!empty( $userInfo['login'] )?$userInfo['login']:$userInfo['email']), $_REQUEST["pass"] );
	}

	bit_redirect( $url );
}

// Display the template
$gBitSystem->display( 'bitpackage:users/change_password.tpl', 'Change Password' , array( 'display_mode' => 'display' ));

?>
