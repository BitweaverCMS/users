<?php
/**
 * register new user
 *
 * @copyright (c) 2004-15 bitweaver.org
 *
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
require_once( KERNEL_PKG_CLASS_PATH.'BitBase.php' );
include_once( KERNEL_PKG_INCLUDE_PATH.'notification_lib.php' );

$gBitSystem->verifyFeature( 'users_allow_register' );

require_once( USERS_PKG_CLASS_PATH.'BitHybridAuthManager.php' );
BitHybridAuthManager::loadSingleton();
global $gBitHybridAuthManager;
$gBitSmarty->assign( 'hybridProviders', $gBitHybridAuthManager->getEnabledProviders() );

// Everything below here is needed for registration

require_once( USERS_PKG_PATH.'includes/BaseAuth.php' );

if( !empty( $_REQUEST['returnto'] ) ) {
	$_SESSION['returnto'] = $_REQUEST['returnto'];
} elseif( !empty( $_SERVER['HTTP_REFERER'] ) && !strpos( $_SERVER['HTTP_REFERER'], 'signin.php' )  && !strpos( $_SERVER['HTTP_REFERER'], 'register.php' ) ) {
	$from = parse_url( $_SERVER['HTTP_REFERER'] );
	if( !empty( $from['path'] ) && $from['host'] == $_SERVER['SERVER_NAME'] ) {
		$_SESSION['loginfrom'] = $from['path'].'?'.( !empty( $from['query'] ) ? $from['query'] : '' );
	}
}

if( $gBitUser->isRegistered() ) {
	bit_redirect( $gBitSystem->getDefaultPage() );
}
if( isset( $_REQUEST["register"] ) ) {

	$registerHash = $_REQUEST;

	include( USERS_PKG_INCLUDE_PATH.'register_inc.php' );

	$gBitSmarty->assignByRef( 'reg', $registerHash );

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
$gBitSmarty->assignByRef( 'languages', $languages );
$gBitSmarty->assignByRef( 'gBitLanguage', $gBitLanguage );

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
$gBitSmarty->assignByRef( 'groupList', $groupList );

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
$gBitSmarty->assignByRef('packages',$packages );

if( !empty( $_REQUEST['error'] ) ) {
	$gBitSmarty->assign( 'error', $_REQUEST['error'] );
	$gBitSystem->setHttpStatus( HttpStatusCodes::HTTP_UNAUTHORIZED );
}

$gBitSmarty->assign( 'metaKeywords', 'Login, Sign in, Registration, Register, Create new account' );
$gBitSystem->display('bitpackage:users/register.tpl', 'Register' , array( 'display_mode' => 'display' ));
?>
