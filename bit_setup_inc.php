<?php
$registerHash = array(
	'package_name' => 'users',
	'package_path' => dirname( __FILE__ ).'/',
	'activatable' => FALSE,
	'required_package'=> TRUE,
);
$gBitSystem->registerPackage( $registerHash );

$gBitSystem->registerNotifyEvent( array( "user_registers" => tra("A user registers") ) );

if( $gBitSystem->isFeatureActive( 'user_files' ) ) {
	$gBitSystem->registerAppMenu( 'userfiles', 'User Files', '', '', 'userfiles');
}

if( !defined( 'AVATAR_MAX_DIM' ) ) {
	define( 'AVATAR_MAX_DIM', 100 );
}
if( !defined( 'PORTRAIT_MAX_DIM' ) ) {
	define( 'PORTRAIT_MAX_DIM', 300 );
}
if( !defined( 'LOGO_MAX_DIM' ) ) {
	define( 'LOGO_MAX_DIM', 600 );
}


	// **********  USER  ************
	require_once(USERS_PKG_PATH . 'BitPermUser.php');
	// a package can decide to override the default user class
	$userClass = $gBitSystem->getConfig( 'user_class', 'BitPermUser' );
	$gBitUser = new $userClass();


	// set session lifetime
	$site_session_lifetime = $gBitSystem->getConfig( 'site_session_lifetime', '0' );
	if( $site_session_lifetime > 0 ) {
		ini_set( 'session.gc_maxlifetime', $site_session_lifetime );
	}

	// is session data  stored in DB or in filesystem?
	global $gBitDbType, $gBitDbHost, $gBitDbUser, $gBitDbPassword, $gBitDbName;
	if( $gBitSystem->isFeatureActive( 'site_store_session_db' ) && !empty( $gBitDbType ) ) {
		include(UTIL_PKG_PATH . 'adodb/session/adodb-session.php');
		ADODB_Session::dataFieldName('session_data');
		ADODB_Session::driver($gBitDbType);
		ADODB_Session::host($gBitDbHost);
		ADODB_Session::user($gBitDbUser);
		ADODB_Session::password($gBitDbPassword);
		ADODB_Session::database($gBitDbName);
		ADODB_Session::table(BIT_DB_PREFIX.'sessions');
		ini_set( 'session.save_handler', 'user' );
	}

	$cookie_path = BIT_ROOT_URL;
	$cookie_domain = "";
	session_name( BIT_SESSION_NAME );
	if ($gBitSystem->isFeatureActive('users_remember_me')) {
		$cookie_path = $gBitSystem->getConfig('cookie_path', $cookie_path);
		$cookie_domain = $gBitSystem->getConfig('cookie_domain', $cookie_domain);
	}
	session_set_cookie_params($site_session_lifetime, $cookie_path, $cookie_domain);

	if (ini_get('safe_mode') && ini_get('safe_mode_gid')) {
		umask(0007);
	}
	session_start();
	// just use a simple COOKIE (unique random string) that is linked to the users_cnxn table.
	// This way, nuking rows in the users_cnxn table can log people out and is much more reliable than SESSIONS
	$cookie_site = strtolower( ereg_replace("[^a-zA-Z0-9]", "", $gBitSystem->getConfig('site_title', 'bitweaver')) );
	global $user_cookie_site;
	$user_cookie_site = 'bit-user-' . $cookie_site;

	global $gOverrideLoginFunction;
	if( !empty( $gOverrideLoginFunction ) ) {
		$gBitUser->mUserId = $gOverrideLoginFunction();
		if ($gBitUser->mUserId) {
			$gBitUser->load();
			$gBitUser->loadPermissions();
		}
	} elseif( isset($_COOKIE[$user_cookie_site]) &&	($gBitUser->mUserId = $gBitUser->getByHash( $_COOKIE[$user_cookie_site] )) ) {
		$gBitUser->load( TRUE );
	} else {
		// Now if the remember me feature is on and the user checked the user_remember_me checkbox then ...
		if ($gBitSystem->isFeatureActive( 'users_remember_me' ) && isset($_REQUEST['rme']) && $_REQUEST['rme'] == 'on') {
			$cookie_time = (int)(time() + $gBitSystem->getConfig( 'users_remember_time', 86400 ));
		} else {
			$cookie_time = 0;
		}
		setcookie($user_cookie_site, session_id(), $cookie_time, $cookie_path, $cookie_domain);
		// check what auth metod is selected. default is for the 'tiki' to auth users
		$users_auth_method = $gBitSystem->getConfig('users_auth_method', 'tiki');
		// if the auth method is 'web site', look for the username in $_SERVER
		if ($users_auth_method == 'ws') {
			if (isset($_SERVER['REMOTE_USER'])) {
				if ($gBitUser->userExists($_SERVER['REMOTE_USER'])) {
					$gBitUser->mUserId = $_SERVER['REMOTE_USER'];
					$gBitUser->load( TRUE );
				}
			}
		}
	}

	if( !$gBitUser->isValid() ) {
		$gBitUser->mUserId = ANONYMOUS_USER_ID;
		$gBitUser->load( TRUE );
	}

	if (isset($_COOKIE[$user_cookie_site])) {
		$gBitUser->updateSession( $_COOKIE[$user_cookie_site] );
	} elseif (function_exists("session_id")) {
		$gBitUser->updateSession(session_id());
	}

	$gBitSmarty->assign_by_ref('gBitUser', $gBitUser);

	$users_allow_register = $gBitSystem->getConfig("users_allow_register", 'y');
	$users_validate_user = $gBitSystem->getConfig("users_validate_user", 'n');
	$users_forgot_pass = $gBitSystem->getConfig("users_forgot_pass", 'y');
	$users_eponymous_groups = $gBitSystem->getConfig("users_eponymous_groups", 'n');
	$users_register_passcode = $gBitSystem->getConfig("users_register_passcode", 'n');
	$users_register_passcode = $gBitSystem->getConfig("users_register_passcode", '');
	$site_url_index = $gBitSystem->getConfig("site_url_index", '');
	$site_use_proxy = $gBitSystem->getConfig("site_use_proxy", 'n');
	$site_proxy_host = $gBitSystem->getConfig("site_proxy_host", '');
	$site_proxy_port = $gBitSystem->getConfig("site_proxy_port", '');
	$site_store_session_db = $gBitSystem->getConfig("site_store_session_db", 'n');
	$site_session_lifetime = $gBitSystem->getConfig("site_session_lifetime", 0);
	$users_remember_time = $gBitSystem->getConfig('users_remember_time', 7200);
	$site_https_login = $gBitSystem->getConfig('site_https_login', 'n');
	$site_https_login_required = $gBitSystem->getConfig('site_https_login_required', 'n');
	$users_change_language = $gBitSystem->getConfig("users_change_language", 'y');

	// If we are processing a login then do not generate the challenge
	// if we are in any other case then yes.
	if( !empty( $_SERVER["REQUEST_URI"] ) && !strstr($_SERVER["REQUEST_URI"], USERS_PKG_URL . 'validate')) {
		if ($gBitSystem->getConfig('feature_challenge') == 'y') {
			$chall = $gBitUser->generateChallenge();

			$_SESSION["challenge"] = $chall;
			$gBitSmarty->assign('challenge', $chall);
		}
	}

	if( $gBitSystem->isFeatureActive( 'users_domains' ) ) {
		$domain = substr( $_SERVER['HTTP_HOST'], 0, strpos( $_SERVER['HTTP_HOST'], '.' ) );
		if( $domain && $domain != $gBitSystem->getConfig( 'users_default_domain', 'www' ) ) {
			if( $gBitSystem->mDomainInfo = $gBitUser->getUserDomain( $domain ) ) {
				if( empty( $_REQUEST['user_id'] ) ) {
					$_REQUEST['user_id'] = $gBitSystem->mDomainInfo['user_id'];
				} elseif( empty( $_REQUEST['home'] ) ) {
					$_REQUEST['home'] = $gBitSystem->mDomainInfo['login'];
				}
				if( !empty( $_REQUEST['lookup_user_id'] ) ) {
					$_REQUEST['lookup_user_id'] = $gBitSystem->mDomainInfo['user_id'];
				}
			}
		}
	}

	// users_themes='y' is for the entire site, 'h' is just for users homepage and is dealt with on users/index.php
	if( !empty( $gBitSystem->mDomainInfo['style'] ) ) {
		$theme = $gBitSystem->mDomainInfo['style'];
	} elseif( $gBitSystem->getConfig('users_themes') == 'y' ) {
		if ( $gBitUser->isRegistered() && $gBitSystem->isFeatureActive( 'users_preferences' ) ) {
			if( $userStyle = $gBitUser->getPreference('theme') ) {
				$theme = $userStyle;
			}
		}
		if( isset( $_COOKIE['tiki-theme'] )) {
			$theme = $_COOKIE['tiki-theme'];
		}
	}
	if( !empty( $theme ) ) {
		$gBitThemes->setStyle( $theme );
	}

	$messages_allow_messages = 'n';
	if( $gBitUser->isRegistered() ) {
		global $tasks_use_dates, $tasks_max_records, $messages_allow_messages;
		$messages_allow_messages = $gBitUser->getPreference( 'messages_allow_messages', 'y');
		$tasks_use_dates = $gBitUser->getPreference( 'tasks_use_dates');
		$tasks_max_records = $gBitUser->getPreference( 'tasks_max_records');
		$gBitSmarty->assign('tasks_use_dates', $tasks_use_dates);
		$gBitSmarty->assign('tasks_max_records', $tasks_max_records);
		$gBitSmarty->assign('messages_allow_messages', $messages_allow_messages);
	}

	// register 'my' menu
	if( $gBitUser->isValid() && ( $gBitUser->isRegistered() || !$gBitSystem->isFeatureActive( 'site_hide_my_top_bar_link' ) ) ) {
		$site_menu_title = $gBitSystem->getConfig( 'site_menu_title' );
		$displayTitle = !empty( $site_menu_title ) ? $site_menu_title : $gBitSystem->getConfig( 'site_title', 'Site' );
		$menuHash = array(
			'package_name'  => USERS_PKG_NAME,
			'index_url'     => ( $gBitSystem->getConfig( 'users_preferences' ) == 'y' ? USERS_PKG_URL.'my.php':'' ),
			'menu_title'    => 'My '.$displayTitle,
			'menu_template' => 'bitpackage:users/menu_users.tpl',
		);
		$gBitSystem->registerAppMenu( $menuHash );
	}

require_once( USERS_PKG_PATH.'BaseAuth.php' );

?>
