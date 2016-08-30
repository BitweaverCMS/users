<?php
/**
 * @version $Header$
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

/* ---- services ----- */
define( 'CONTENT_SERVICE_USERS_FAVS', 'users_favorites' );
$gLibertySystem->registerService( CONTENT_SERVICE_USERS_FAVS,
	USERS_PKG_NAME,
	array(
		'content_icon_tpl' => 'bitpackage:users/user_favs_service_icon_inc.tpl',
		'content_list_sql_function' => 'users_favs_content_list_sql',
		'content_user_collection_function' => 'users_collection_sql',
	),
	array(
		'description' => tra( 'Provides a ajax service enabling users to bookmark any content as a favorite.' ),
	)
);

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

// a package can decide to override the default user class
$userClass = $gBitSystem->getConfig( 'user_class', (defined('ROLE_MODEL') ) ?  'RolePermUser' : 'BitPermUser' );
require_once( USERS_PKG_PATH . $userClass .'.php' );

// set session lifetime
if( $gBitSystem->isFeatureActive( 'site_session_lifetime' )) {
	ini_set( 'session.gc_maxlifetime', $gBitSystem->isFeatureActive( 'site_session_lifetime' ));
}

// is session data stored in DB or in filesystem?
if( $gBitSystem->isFeatureActive( 'site_store_session_db' ) && !empty( $gBitDbType )) {
	if( file_exists( EXTERNAL_LIBS_PATH.'adodb/session/adodb-session.php' )) {
		include_once( EXTERNAL_LIBS_PATH . 'adodb/session/adodb-session.php' );
	} elseif( file_exists( UTIL_PKG_PATH.'adodb/session/adodb-session.php' )) {
		include_once( UTIL_PKG_PATH.'adodb/session/adodb-session.php' );
	}
	if ( class_exists( 'ADODB_Session' ) ) {
		ADODB_Session::dataFieldName( 'session_data' );
		ADODB_Session::driver( $gBitDbType );
		ADODB_Session::host( $gBitDbHost );
		ADODB_Session::user( $gBitDbUser );
		ADODB_Session::password( $gBitDbPassword );
		ADODB_Session::database( $gBitDbName );
		ADODB_Session::table( BIT_DB_PREFIX.'sessions' );
		ini_set( 'session.save_handler', 'user' );
	}
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
	if (session_status() == PHP_SESSION_NONE) {
		session_start();
	}
}

// Init USER AGENT if empty so reliant methods don't need gobs of empty checking
if( !isset( $_SERVER['HTTP_USER_AGENT'] )) {
	$_SERVER['HTTP_USER_AGENT'] = "";
}

// load the user
global $gOverrideLoginFunction;
$siteCookie = $userClass::getSiteCookieName();

if( !empty( $gOverrideLoginFunction )) {
	$gBitUser = new $userClass();
	$gBitUser->mUserId = $gOverrideLoginFunction();
	if( $gBitUser->mUserId ) {
		$gBitUser->load();
		$gBitUser->loadPermissions();
	}
} elseif( !empty( $_COOKIE[$siteCookie] ) ) {
	if( $gBitUser = $userClass::loadFromCache( $_COOKIE[$siteCookie] ) ) {
//		var_dump( 'load from cache' ); die;
	} else {
		$gBitUser = new $userClass();
		if( $gBitUser->mUserId = $gBitUser->getUserIdFromCookieHash( $_COOKIE[$siteCookie] ) ) {
			// we have user with this cookie.
			if( $gBitUser->load( TRUE ) ) {
				// maybe do something...
			}
		}
	}
}

// if we still don't have a user loaded, we'll load the anonymous user
if( empty( $gBitUser ) || !$gBitUser->isValid() ) {
	if( !($gBitUser = $userClass::loadFromCache( ANONYMOUS_USER_ID ) ) ) {
		$gBitUser = new $userClass( ANONYMOUS_USER_ID );
		if( $gBitUser->load( TRUE ) ) {
			// maybe do something...
		}
	}
}

$gBitSmarty->assignByRef( 'gBitUser', $gBitUser );

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
if( $gBitUser->isValid() && $gBitUser->isRegistered() ) {
	$menuHash = array(
		'package_name'  => USERS_PKG_NAME,
		'index_url'     => ( $gBitSystem->isFeatureActive( 'users_preferences' ) ? $gBitSystem->getConfig( 'users_login_homepage', USERS_PKG_URL.'my.php' ) : '' ),
		'menu_title'    => 'My '.$gBitSystem->getConfig( 'site_menu_title', $gBitSystem->getConfig( 'site_title', 'Site' )),
		'menu_template' => 'bitpackage:users/menu_users.tpl',
	);
	$gBitSystem->registerAppMenu( $menuHash );
}

require_once( USERS_PKG_PATH.'BaseAuth.php' );

?>
