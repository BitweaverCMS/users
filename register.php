<?php
/**
 * $Header$
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See below for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
 *
 * $Id$
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

require_once( '../kernel/setup_inc.php' );
require_once( KERNEL_PKG_PATH.'BitBase.php' );
include_once( KERNEL_PKG_PATH.'notification_lib.php' );
require_once( USERS_PKG_PATH.'classes/recaptchalib.php' );

// Permission: needs p_register
$gBitSystem->verifyFeature( 'users_allow_register' );

require_once( USERS_PKG_PATH.'BaseAuth.php' );

if( isset( $_REQUEST['returnto'] ) ) {
	$_SESSION['returnto'] = $_REQUEST['returnto']; 
}

if( $gBitUser->isRegistered() ) {
	bit_redirect( $gBitSystem->getDefaultPage() );
}
if( isset( $_REQUEST["register"] ) ) {

	$reg = $_REQUEST;

	// Register the new user
	$userClass = $gBitSystem->getConfig( 'user_class', 'BitPermUser' );
	$newUser = new $userClass();
	if( $newUser->preRegisterVerify( $reg ) && $newUser->register( $reg ) ) {
		$gBitUser->mUserId = $newUser->mUserId;

		// add user to user-selected group
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

		// set the user to private if necessary. defaults to public
		if(!empty($_REQUEST['users_information']) && $_REQUEST['users_information'] == 'private'){
			$newUser->storePreference('users_information','private');
		}

		// requires validation by email 
		if( $gBitSystem->isFeatureActive( 'users_validate_user' ) ) {
			$gBitSmarty->assign('msg',tra('You will receive an email with information to login for the first time into this site'));
			$gBitSmarty->assign('showmsg','y');
		} else {
			if( !empty( $_SESSION['loginfrom'] ) ) {
				unset( $_SESSION['loginfrom'] );
			}
			// registration login, fake the cookie so the session gets updated properly.
			if( empty($_COOKIE[$gBitUser->getSiteCookieName()] ) ) {
				$_COOKIE[$gBitUser->getSiteCookieName()] = session_id();
			}
			// login with email since login is not technically required in the form, as it can be auto generated during store
			$afterRegDefault = $newUser->login( $reg['email'], $reg['password'], FALSE, FALSE );
			$url = $gBitSystem->getConfig( 'after_reg_url' )?BIT_ROOT_URI.$gBitSystem->getConfig( 'after_reg_url' ):$afterRegDefault;
			// return to referring page
			if( !empty( $_SESSION['returnto'] ) ) {
				$url = $_SESSION['returnto'];
			// forward to group post-registration page 
			} elseif ( !empty( $_REQUEST['group'] ) && !empty( $groupInfo['after_registration_page'] ) ) {
				if ( $newUser->verifyId( $groupInfo['after_registration_page'] ) ) {
					$url = BIT_ROOT_URI."index.php?content_id=".$groupInfo['after_registration_page'];
				} elseif( strpos( $groupInfo['after_registration_page'], '/' ) === FALSE ) {
					$url = BitPage::getDisplayUrlFromHash( $groupInfo['after_registration_page'] );
				} else {
					$url = $groupInfo['after_registration_page'];
				}
			}
			header( 'Location: '.$url );
			exit;
		}
	} else {
		$gBitSystem->setHttpStatus( HttpStatusCodes::HTTP_BAD_REQUEST );
		$gBitSmarty->assign_by_ref( 'errors', $newUser->mErrors );
	}

	$gBitSmarty->assign_by_ref( 'reg', $reg );

} else {
	if( $gBitSystem->isFeatureActive( 'custom_user_fields' ) ) {
		$fields= explode( ',', $gBitSystem->getConfig( 'custom_user_fields' )  );
		trim_array( $fields );
		$gBitSmarty->assign('customFields', $fields);
	}

	for( $i=0; $i < BaseAuth::getAuthMethodCount(); $i++ ) {
		$instance = BaseAuth::init( $i );
		if( $instance && $instance->canManageAuth() ) {
			$auth_reg_fields = $instance->getRegistrationFields();
			foreach( array_keys( $auth_reg_fields ) as $auth_field ) {
				$auth_reg_fields[$auth_field]['value'] = $auth_reg_fields[$auth_field]['default'];
			}
			$gBitSmarty->assign( 'auth_reg_fields', $auth_reg_fields );
			break;
		}
	}
}

$languages = array();
$languages = $gBitLanguage->listLanguages();
$gBitSmarty->assign_by_ref( 'languages', $languages );
$gBitSmarty->assign_by_ref( 'gBitLanguage', $gBitLanguage );

// Get flags here
$flags = array();
$h = opendir( USERS_PKG_PATH.'icons/flags/' );
while( $file = readdir( $h )) {
	if( strstr( $file, ".gif" )) {
		$parts = explode( '.', $file );
		$flags[] = $parts[0];
	}
}
closedir( $h );
sort( $flags );
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

$gBitSystem->display('bitpackage:users/register.tpl', 'Register' , array( 'display_mode' => 'display' ));
?>
