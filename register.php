<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/register.php,v 1.1.1.1.2.4 2005/07/26 15:50:30 drewslater Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: register.php,v 1.1.1.1.2.4 2005/07/26 15:50:30 drewslater Exp $
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
		if( $newUser->register( $reg ) ) {
			if( $gBitSystem->isFeatureActive( 'validateUsers' ) ) {
				$gBitSmarty->assign('msg',tra('You will receive an email with information to login for the first time into this site'));
				$gBitSmarty->assign('showmsg','y');
			} else {
				$url = $newUser->login( $reg['login'], $reg['password'], FALSE, FALSE );
				header( 'Location: '.$url );
				exit;
			}
		} else {
			$gBitSmarty->assign_by_ref( 'errors', $newUser->mErrors );
		}
	} else {
		$gBitSmarty->assign_by_ref( 'errors', $errors );
	}
	$gBitSmarty->assign_by_ref( 'reg', $reg );

} else {
	if( !empty( $gBitSystem->mPrefs['custom_user_fields'] ) ) {
		$fields= explode( ',', $gBitSystem->mPrefs['custom_user_fields']  );
		trim_array( $fields );
		$gBitSmarty->assign('customFields', $fields);
	}
}

$gBitSystem->display('bitpackage:users/register.tpl', 'Register' );

?>
