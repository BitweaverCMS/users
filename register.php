<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/register.php,v 1.13 2006/04/04 22:23:27 sylvieg Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: register.php,v 1.13 2006/04/04 22:23:27 sylvieg Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
// Avoid user hell
if( isset( $_REQUEST['tk'] ) ) {
	unset( $_REQUEST['tk'] );
}

require_once( '../bit_setup_inc.php' );
require_once( KERNEL_PKG_PATH.'BitBase.php' );
include_once( KERNEL_PKG_PATH.'notification_lib.php' );

// Permission: needs p_register
$gBitSystem->verifyFeature( 'allow_register' );

if( isset( $_REQUEST["register"] ) ) {
	$reg = $_REQUEST;
	// novalidation is set to yes if a user confirms his email is correct after tiki fails to validate it
	if( $gBitSystem->isFeatureActive( 'rnd_num_reg' ) ) {
		if( (empty( $reg['novalidation'] ) || $reg['novalidation'] != 'yes')
			&& (!isset( $_SESSION['random_number'] ) || $_SESSION['random_number']!=$reg['regcode'])) {
			$errors['rnd_num_reg'] = "Wrong registration code";
		}
	}

	// Check the mode
	if( $gBitSystem->isFeatureActive( 'use_register_passcode' ) ) {
		if( $reg["passcode"] != $gBitSystem->getConfig( "register_passcode",md5( $gBitUser->genPass() ) ) ) {
			$errors['passcode'] = 'Wrong passcode! You need to know the passcode to register at this site';
		}
	}

	if( empty( $errors ) ) {
		$newUser = new BitPermUser();
		if( $newUser->register( $reg ) ) {
			if ( !empty( $_REQUEST['group'] ) ) {
				$groupInfo = $gBitUser->getGroupInfo( $_REQUEST['group'] );
				if ( empty($groupInfo) || $groupInfo['is_public'] != 'y' ) {
					$errors[] = "You can't use this group";
					$gBitSmarty->assign_by_ref( 'errors', $errors );
				} else {
					$userId = $newUser->getUserId();
					$gBitUser->addUserToGroup( $userId, $_REQUEST['group'] );
					$gBitUser->storeUserDefaultGroup( $userId, $_REQUEST['group'] );
				}
			}
			if( $gBitSystem->isFeatureActive( 'validate_user' ) ) {
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
	if( $gBitSystem->isFeatureActive( 'custom_user_fields' ) ) {
		$fields= explode( ',', $gBitSystem->getConfig( 'custom_user_fields' )  );
		trim_array( $fields );
		$gBitSmarty->assign('customFields', $fields);
	}
}

$languages = array();
$languages = $gBitLanguage->listLanguages();
$gBitSmarty->assign_by_ref('languages', $languages);
$gBitSmarty->assign_by_ref('gBitLanguage', $gBitLanguage);

// Get flags here
$flags = array();
$h = opendir( USERS_PKG_PATH.'icons/flags/' );
while ($file = readdir($h)) {
	if (strstr($file, ".gif")) {
		$parts = explode('.', $file);
		$flags[] = $parts[0];
	}
}
closedir ($h);
sort ($flags);
$gBitSmarty->assign('flags', $flags);

$listHash = array( 'is_public'=>'y', 'sort_mode'=>'is_default_asc' );
$groupList = $gBitUser->getAllGroups( $listHash );
if ( $groupList['cant'] ) {
	$gBitSmarty->assign_by_ref( 'groupList', $groupList['data'] );
}

$gBitSystem->display('bitpackage:users/register.tpl', 'Register' );

?>
