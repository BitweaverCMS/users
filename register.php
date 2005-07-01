<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/register.php,v 1.1.1.1.2.2 2005/07/01 07:24:50 jht001 Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: register.php,v 1.1.1.1.2.2 2005/07/01 07:24:50 jht001 Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
require_once( KERNEL_PKG_PATH.'BitBase.php' );
include_once( KERNEL_PKG_PATH.'notification_lib.php' );

// Permission: needs p_register
$gBitSystem->verifyFeature( 'allowRegister' );

if( isset( $_REQUEST["register"] ) ) {
	$reg = $_REQUEST['REG'];
	// novalidation is set to yes if a user confirms his email is correct after tiki fails to validate it
	if( $gBitSystem->isFeatureActive( 'rnd_num_reg' ) ) {
		if( (empty( $reg['novalidation'] ) || $reg['novalidation'] != 'yes') 
			&& (!isset( $_SESSION['random_number'] ) || $_SESSION['random_number']!=$reg['regcode'])) {
			$errors['rnd_num_reg'] = "Wrong registration code";
		}
	}

	// Check the mode
	if( $gBitSystem->isFeatureActive( 'useRegisterPasscode' ) ) {
		if( $reg["passcode"] != $gBitSystem->getPreference( "registerPasscode",md5( $gBitUser->genPass() ) ) ) {
			$errors['passcode'] = 'Wrong passcode! You need to know the passcode to register at this site';
		}
	}

	if( empty( $errors ) ) {
		$newUser = new BitPermUser();
		if( $newUser->store( $reg ) ) {
			$emails = $notificationlib->get_mail_events('user_registers','*');
			foreach($emails as $email) {
				$smarty->assign('mail_user',$reg['login']);
				$smarty->assign('mail_date',date("U"));
				$smarty->assign('mail_site',$_SERVER["SERVER_NAME"]);
				$mail_data = $smarty->fetch('bitpackage:users/new_user_notification.tpl');
				mail( $reg['email'], tra('New user registration'),$mail_data,"From: ".$gBitSystem->getPreference('sender_email')."\r\nContent-type: text/plain;charset=utf-8\r\n");
			}
			if( !empty( $_REQUEST['CUSTOM'] ) ) {
				foreach( $_REQUEST['CUSTOM'] as $field=>$value ) {
					$newUser->storePreference( $field, $value );	
				}
			}
			if( $gBitSystem->isFeatureActive( 'validateUsers' ) ) {
				// $apass = addslashes(substr(md5($gBitSystem->genPass()),0,25));
				$apass = $reg['user_store']['provpass'];
				$foo = parse_url($_SERVER["REQUEST_URI"]);
				$foo1=str_replace("register","confirm",$foo["path"]);
				$machine = httpPrefix().$foo1;

				// Send the mail
				$smarty->assign('msg',tra('You will receive an email with information to login for the first time into this site'));
				$smarty->assign('mail_machine',$machine);
				$smarty->assign('mail_site',$_SERVER["SERVER_NAME"]);
				$smarty->assign('mail_user',$reg['login']);
				$smarty->assign('mail_apass',$apass);
				$mail_data = $smarty->fetch('bitpackage:users/user_validation_mail.tpl');
				mail($reg["email"], tra('Your bitweaver information registration'),$mail_data,"From: ".$gBitSystem->getPreference('sender_email')."\r\nContent-type: text/plain;charset=utf-8\r\n");
				$smarty->assign('showmsg','y');
			} else {
				$url = $newUser->login( $reg['login'], $reg['password'], FALSE, FALSE );
				header( 'Location: '.$url );
				exit;
			}
		} else {
			$smarty->assign_by_ref( 'errors', $newUser->mErrors );
		}
	} else {
		$smarty->assign_by_ref( 'errors', $errors );
	}
	$smarty->assign_by_ref( 'reg', $reg );

} else {
	if( !empty( $gBitSystem->mPrefs['custom_user_fields'] ) ) {
		$fields= explode( ',', $gBitSystem->mPrefs['custom_user_fields']  );
		trim_array( $fields );
		$smarty->assign('customFields', $fields);
	}
}

$gBitSystem->display('bitpackage:users/register.tpl', 'Register' );

?>
