<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_users/bit_setup_inc.php,v 1.48 2008/10/16 09:57:58 squareing Exp $
 * @package users
 */
global $gBitDbType, $gBitDbHost, $gBitDbUser, $gBitDbPassword, $gBitDbName, $gBitThemes;

$registerHash = array(
	'package_name' => 'users',
	'package_path' => dirname( __FILE__ ).'/',
	'activatable' => FALSE,
	'required_package'=> TRUE,
);
$gBitSystem->registerPackage( $registerHash );

$gBitSystem->registerNotifyEvent( array( "user_registers" => tra( "A user registers" )));

if( !defined( 'AVATAR_MAX_DIM' )) {
	define( 'AVATAR_MAX_DIM', 100 );
}
if( !defined( 'PORTRAIT_MAX_DIM' )) {
	define( 'PORTRAIT_MAX_DIM', 300 );
}
if( !defined( 'LOGO_MAX_DIM' )) {
	define( 'LOGO_MAX_DIM', 600 );
}

require_once( USERS_PKG_PATH . 'BitPermUser.php' );
// a package can decide to override the default user class
$userClass = $gBitSystem->getConfig( 'user_class', 'BitPermUser' );
$gBitUser = new $userClass();


// set session lifetime
if( $gBitSystem->isFeatureActive( 'site_session_lifetime' )) {
	ini_set( 'session.gc_maxlifetime', $gBitSystem->isFeatureActive( 'site_session_lifetime' ));
}

// is session data stored in DB or in filesystem?
if( $gBitSystem->isFeatureActive( 'site_store_session_db' ) && !empty( $gBitDbType )) {
	include_once( UTIL_PKG_PATH . 'adodb/session/adodb-session.php' );
	ADODB_Session::dataFieldName( 'session_data' );
	ADODB_Session::driver( $gBitDbType );
	ADODB_Session::host( $gBitDbHost );
	ADODB_Session::user( $gBitDbUser );
	ADODB_Session::password( $gBitDbPassword );
	ADODB_Session::database( $gBitDbName );
	ADODB_Session::table( BIT_DB_PREFIX.'sessions' );
	ini_set( 'session.save_handler', 'user' );
}

session_name( BIT_SESSION_NAME );
if( $gBitSystem->isFeatureActive( 'users_remember_me' )) {
	session_set_cookie_params( $gBitSystem->getConfig( 'site_session_lifetime' ), $gBitSystem->getConfig( 'cookie_path', BIT_ROOT_URL ), $gBitSystem->getConfig( 'cookie_domain', '' ));
} else {
	session_set_cookie_params( $gBitSystem->getConfig( 'site_session_lifetime' ), BIT_ROOT_URL, '' );
}

// just use a simple COOKIE (unique random string) that is linked to the users_cnxn table.
// This way, nuking rows in the users_cnxn table can log people out and is much more reliable than SESSIONS
global $gShellScript;
if( empty( $gShellScript ) ) {
	session_start();
}
$cookie_site = strtolower( ereg_replace( "[^a-zA-Z0-9]", "", $gBitSystem->getConfig( 'site_title', 'bitweaver' )));
global $user_cookie_site;
$user_cookie_site = 'bit-user-'.$cookie_site;


// TODO: Remove this cookie munging business. This is a temporary fix that users don't have to log in again when they visit
// first we check to see if the 'bit' and the 'tiki' version of the cookie are present
if( !empty( $_COOKIE['tiki-user-'.$cookie_site] ) && !empty( $_COOKIE[$user_cookie_site] )) {
	setcookie( 'tiki-user-'.$cookie_site, $_COOKIE['tiki-user-'.$cookie_site], 1, $gBitSystem->getConfig( 'cookie_path', BIT_ROOT_URL ), $gBitSystem->getConfig( 'cookie_domain', '' ));
	unset( $_COOKIE['tiki-user-'.$cookie_site] );
}
// if the 'tiki' version is still set, we make sure that it's copied to the 'bit' version of the cookie
if( !empty( $_COOKIE['tiki-user-'.$cookie_site] )) {
	// here we can't check to see if a user has checked the rme button. this doesn't really matter since there's only _very_ few users who will be logging in exactly now
	if( $gBitSystem->isFeatureActive( 'users_remember_me' )) {
		$cookie_time = (int)(time() + $gBitSystem->getConfig( 'users_remember_time', 86400 ));
	} else {
		$cookie_time = 0;
	}
	// we copy the existing cookie accross
	setcookie( $user_cookie_site, $_COOKIE['tiki-user-'.$cookie_site], $cookie_time, $gBitSystem->getConfig( 'cookie_path', BIT_ROOT_URL ), $gBitSystem->getConfig( 'cookie_domain', '' ));
	$_COOKIE[$user_cookie_site] = $_COOKIE['tiki-user-'.$cookie_site];
}


// load the user
global $gOverrideLoginFunction;
if( !empty( $gOverrideLoginFunction )) {
	$gBitUser->mUserId = $gOverrideLoginFunction();
	if( $gBitUser->mUserId ) {
		$gBitUser->load();
		$gBitUser->loadPermissions();
	}
} elseif( !empty( $_COOKIE[$user_cookie_site] ) && ( $gBitUser->mUserId = $gBitUser->getUserIdFromCookieHash( $_COOKIE[$user_cookie_site] ))) {
	$gBitUser->load( TRUE );
} else {
	// Now if the remember me feature is on and the user checked the user_remember_me checkbox then ...
	if( $gBitSystem->isFeatureActive( 'users_remember_me' ) && isset( $_REQUEST['rme'] ) && $_REQUEST['rme'] == 'on' ) {
		$cookie_time = (int)(time() + $gBitSystem->getConfig( 'users_remember_time', 86400 ));
	} else {
		$cookie_time = 0;
	}
	setcookie( $user_cookie_site, session_id(), $cookie_time, $gBitSystem->getConfig( 'cookie_path', BIT_ROOT_URL ), $gBitSystem->getConfig( 'cookie_domain', '' ));

	// if the auth method is 'web site', look for the username in $_SERVER
	if( $gBitSystem->getConfig( 'users_auth_method', 'tiki' ) == 'ws' ) {
		if( isset( $_SERVER['REMOTE_USER'] )) {
			if( $gBitUser->userExists( $_SERVER['REMOTE_USER'] )) {
				$gBitUser->mUserId = $_SERVER['REMOTE_USER'];
				$gBitUser->load( TRUE );
			}
		}
	}
}

// if we still don't have a user loaded, we'll load the anonymous user
if( !$gBitUser->isValid() ) {
	$gBitUser->mUserId = ANONYMOUS_USER_ID;
	$gBitUser->load( TRUE );
}

if( isset( $_COOKIE[$user_cookie_site] )) {
	$gBitUser->updateSession( $_COOKIE[$user_cookie_site] );
} elseif( function_exists( "session_id" )) {
	$gBitUser->updateSession( session_id() );
}

$gBitSmarty->assign_by_ref( 'gBitUser', $gBitUser );

// If we are processing a login then do not generate the challenge
// if we are in any other case then yes.
if( !empty( $_SERVER["REQUEST_URI"] ) && !strstr( $_SERVER["REQUEST_URI"], USERS_PKG_URL.'validate' )) {
	if( $gBitSystem->isFeatureActive( 'feature_challenge' )) {
		$_SESSION["challenge"] = $gBitUser->generateChallenge();
	}
}

if( $gBitSystem->isFeatureActive( 'users_domains' )) {
	$domain = substr( $_SERVER['HTTP_HOST'], 0, strpos( $_SERVER['HTTP_HOST'], '.' ));
	if( $domain && $domain != $gBitSystem->getConfig( 'users_default_domain', 'www' )) {
		if( $gBitSystem->mDomainInfo = $gBitUser->getUserDomain( $domain )) {
			if( empty( $_REQUEST['user_id'] )) {
				$_REQUEST['user_id'] = $gBitSystem->mDomainInfo['user_id'];
			} elseif( empty( $_REQUEST['home'] )) {
				$_REQUEST['home'] = $gBitSystem->mDomainInfo['login'];
			}

			if( !empty( $_REQUEST['lookup_user_id'] )) {
				$_REQUEST['lookup_user_id'] = $gBitSystem->mDomainInfo['user_id'];
			}
		}
	}
}

// users_themes='y' is for the entire site, 'h' is just for users homepage and is dealt with on users/index.php
if( !empty( $gBitSystem->mDomainInfo['style'] )) {
	$theme = $gBitSystem->mDomainInfo['style'];
} elseif( $gBitSystem->getConfig( 'users_themes' ) == 'y' ) {
	if( $gBitUser->isRegistered() && $gBitSystem->isFeatureActive( 'users_preferences' )) {
		if( $userStyle = $gBitUser->getPreference( 'theme' )) {
			$theme = $userStyle;
		}
	}
	if( isset( $_COOKIE['bit-theme'] )) {
		$theme = $_COOKIE['bit-theme'];
	}
}

if( !empty( $theme )) {
	$gBitThemes->setStyle( $theme );
}

// register 'my' menu
if( $gBitUser->isValid() && ( $gBitUser->isRegistered() || !$gBitSystem->isFeatureActive( 'site_hide_my_top_bar_link' ))) {
	$menuHash = array(
		'package_name'  => USERS_PKG_NAME,
		'index_url'     => ( $gBitSystem->isFeatureActive( 'users_preferences' ) ? USERS_PKG_URL.'my.php' : '' ),
		'menu_title'    => 'My '.$gBitSystem->getConfig( 'site_menu_title', $gBitSystem->getConfig( 'site_title', 'Site' )),
		'menu_template' => 'bitpackage:users/menu_users.tpl',
	);
	$gBitSystem->registerAppMenu( $menuHash );
}

require_once( USERS_PKG_PATH.'BaseAuth.php' );

?>
