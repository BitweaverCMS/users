<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/register.php,v 1.32 2007/06/17 13:53:04 squareing Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: register.php,v 1.32 2007/06/17 13:53:04 squareing Exp $
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
$gBitSystem->verifyFeature( 'users_allow_register' );

require_once( USERS_PKG_PATH.'BaseAuth.php' );

if( $gBitUser->isRegistered() ) {
	$url = $gBitSystem->getDefaultPage();
	header( 'Location: '.$url );
	exit;
}

if( isset( $_REQUEST["register"] ) ) {
	$reg = $_REQUEST;
	// novalidation is set to yes if a user confirms his email is correct after tiki fails to validate it
	if( $gBitSystem->isFeatureActive( 'users_random_number_reg' ) ) {
		if( ( empty( $reg['novalidation'] ) || $reg['novalidation'] != 'yes' )
			&&( !isset( $_SESSION['captcha'] ) || $_SESSION['captcha'] != md5( $reg['captcha'] ) ) )
		{
			$errors['captcha'] = "Wrong registration code";
		}
	}

	// Check the mode
	if( $gBitSystem->isFeatureActive( 'users_register_require_passcode' ) ) {
		if( $reg["passcode"] != $gBitSystem->getConfig( "users_register_passcode",md5( $gBitUser->genPass() ) ) ) {
			$errors['passcode'] = 'Wrong passcode! You need to know the passcode to register at this site';
		}
	}
	if( empty( $errors ) ) {
		$newUser = new BitPermUser();
		if( $newUser->register( $reg ) ) {
			$gBitUser->mUserId = $newUser->mUserId;

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

			if( $gBitSystem->isFeatureActive( 'users_validate_user' ) ) {
				$gBitSmarty->assign('msg',tra('You will receive an email with information to login for the first time into this site'));
				$gBitSmarty->assign('showmsg','y');
			} else {
				if( !empty( $_SESSION['loginfrom'] ) ) {
					unset( $_SESSION['loginfrom'] );
				}
				// registration login, fake the cookie so the session gets updated properly.
				if( empty($_COOKIE[$user_cookie_site] ) ) {
					$_COOKIE[$user_cookie_site] = session_id();
				}
				$afterRegDefault = $newUser->login( $reg['login'], $reg['password'], FALSE, FALSE );
				$url = $gBitSystem->getConfig( 'after_reg_url' )?BIT_ROOT_URL.$gBitSystem->getConfig( 'after_reg_url' ):$afterRegDefault;
				if ( !empty( $_REQUEST['group'] ) && !empty( $groupInfo['after_registration_page'] ) ) {
					if ( $newUser->verifyId( $groupInfo['after_registration_page'] ) ) {
						$url = BIT_ROOT_URL."index.php?content_id=".$groupInfo['after_registration_page'];
					} elseif( strpos( $groupInfo['after_registration_page'], '/' ) === FALSE ) {
						$url = BitPage::getDisplayUrl( $groupInfo['after_registration_page'] );
					} else {
						$url = $groupInfo['after_registration_page'];
					}
				}
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



	for ($i=0;$i<BaseAuth::getAuthMethodCount();$i++) {
		$instance = BaseAuth::init($i);
		if ($instance && $instance->canManageAuth()) {
			$auth_reg_fields = $instance->getRegistrationFields();
			foreach (array_keys($auth_reg_fields) as $auth_field) {
				$auth_reg_fields[$auth_field]['value'] = $auth_reg_fields[$auth_field]['default'];
			}
			$gBitSmarty->assign('auth_reg_fields', $auth_reg_fields);
			break;
		}
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

$listHash = array(
	'is_public' => 'y',
	'sort_mode' => array( 'is_default_asc', 'group_desc_asc' ),
);
$groupList = $gBitUser->getAllGroups( $listHash );
$gBitSmarty->assign_by_ref( 'groupList', $groupList );


	// include preferences settings from other packages - these will be included as individual tabs
	$packages = array();
	foreach( $gBitSystem->mPackages as $package ) {
		if( $gBitSystem->isPackageActive( $package['name'] )) {
			$php_file = $package['path'].'user_register_inc.php';
			$tpl_file = $package['path'].'templates/user_register_inc.tpl';
			if( file_exists( $tpl_file )) {
				if( file_exists( $php_file ))  {
					require( $php_file );
				}
				$p=array();
				$p['template'] = $tpl_file;
				$packages[] = $p;
			}
		}
	}
	$gBitSmarty->assign_by_ref('packages',$packages );
	
$gBitSystem->display('bitpackage:users/register.tpl', 'Register' );

?>
