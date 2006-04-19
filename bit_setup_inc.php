<?php
global $gBitSystem, $gBitUser, $gBitSmarty;

$registerHash = array(
	'package_name' => 'users',
	'package_path' => dirname( __FILE__ ).'/',
	'activatable' => FALSE,
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

	$cookie_path = $gBitSystem->getConfig('cookie_path', BIT_ROOT_URL);
	$cookie_path = !empty($cookie_path) ? $cookie_path : BIT_ROOT_URL;
	$gBitSystem->storeConfig( 'cookie_path', $cookie_path, KERNEL_PKG_NAME );

	// set session lifetime
	$site_session_lifetime = $gBitSystem->getConfig( 'site_session_lifetime', '0' );
	if( $site_session_lifetime > 0 ) {
		ini_set( 'session.gc_maxlifetime', $site_session_lifetime );
	}

	// is session data  stored in DB or in filesystem?
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

	session_name( BIT_SESSION_NAME );
	if ($gBitSystem->isFeatureActive('rememberme')) {
		session_set_cookie_params($site_session_lifetime, $cookie_path, $gBitSystem->getConfig('cookie_domain'));
	} else {
		session_set_cookie_params($site_session_lifetime, BIT_ROOT_URL);
	}
	if (ini_get('safe_mode') && ini_get('safe_mode_gid')) {
		umask(0007);
	}
	session_start();
	// in the case of tikis on same domain we have to distinguish the realm
	// changed cookie and session variable name by a name made with site_title
	$cookie_site = strtolower( ereg_replace("[^a-zA-Z0-9]", "", $gBitSystem->getConfig('site_title', 'bitweaver')) );
	global $user_cookie_site;
	$user_cookie_site = 'tiki-user-' . $cookie_site;


	global $gOverrideLoginFunction;
	if( !empty( $gOverrideLoginFunction ) ) {
		$gBitUser->mUserId = $gOverrideLoginFunction();
		if ($gBitUser->mUserId) {
			$gBitUser->load();
			$gBitUser->loadPermissions();
		}

	} else {
		// if remember me is enabled, check for cookie where auth hash is stored
		// user gets logged in as the first user in the db with a matching hash
		if ( $gBitSystem->isFeatureActive('rememberme') ) {
			if (isset($_COOKIE[$user_cookie_site])) {
				if ( !isset( $_SESSION[$user_cookie_site] ) ) {
					$_SESSION[$user_cookie_site] = $gBitUser->getByHash( $_COOKIE[$user_cookie_site] );
				}
			}
		}
		// check what auth metod is selected. default is for the 'tiki' to auth users
		$auth_method = $gBitSystem->getConfig('auth_method', 'tiki');
		// if the auth method is 'web site', look for the username in $_SERVER
		if ($auth_method == 'ws') {
			if (isset($_SERVER['REMOTE_USER'])) {
				if ($gBitUser->userExists($_SERVER['REMOTE_USER'])) {
					$_SESSION[$user_cookie_site] = $_SERVER['REMOTE_USER'];
				}
			}
		}
		if (isset($_SESSION[$user_cookie_site])) {
			$sessionUser = $_SESSION[$user_cookie_site];
		}
	}

	$full = FALSE;
	// if the username is already saved in the session, pull it from there
	if ( isset( $_SESSION[$user_cookie_site] ) ) {
		if( is_numeric( $_SESSION[$user_cookie_site] ) ) {
			// For cases where a login override returns the userId and not the username
			// We will load the BitUser using the user_id rather than their login
			$gBitUser->mUserId = $_SESSION[$user_cookie_site];
			$gBitUser->load( TRUE );
			srand(time());
			$gTicket = substr( md5(rand() . $gBitUser->mUserId ), 0, 20);
		} else {
			// old tiki session used username
			$_SESSION[$user_cookie_site] = NULL;
		}
	}

	if( !$gBitUser->isValid() ) {
		$gBitUser->mUserId = ANONYMOUS_USER_ID;
		$gBitUser->load( TRUE );
	}

	if (isset($_REQUEST[BIT_SESSION_NAME])) {
		$gBitUser->updateSession( $_REQUEST[BIT_SESSION_NAME] );
	} elseif (function_exists("session_id")) {
		$gBitUser->updateSession(session_id());
	}

	$gBitSmarty->assign_by_ref('gBitUser', $gBitUser);
	$gBitSmarty->register_object('gBitUser', $gBitUser, array(), true, array('hasPermission'));

	$allow_register = $gBitSystem->getConfig("allow_register", 'y');
	$validate_user = $gBitSystem->getConfig("validate_user", 'n');
	$forgot_pass = $gBitSystem->getConfig("forgot_pass", 'y');
	$eponymous_groups = $gBitSystem->getConfig("eponymous_groups", 'n');
	$use_register_passcode = $gBitSystem->getConfig("use_register_passcode", 'n');
	$register_passcode = $gBitSystem->getConfig("register_passcode", '');
	$site_url_index = $gBitSystem->getConfig("site_url_index", '');
	$site_use_proxy = $gBitSystem->getConfig("site_use_proxy", 'n');
	$site_proxy_host = $gBitSystem->getConfig("site_proxy_host", '');
	$site_proxy_port = $gBitSystem->getConfig("site_proxy_port", '');
	$site_store_session_db = $gBitSystem->getConfig("site_store_session_db", 'n');
	$site_session_lifetime = $gBitSystem->getConfig("site_session_lifetime", 0);
	$remembertime = $gBitSystem->getConfig('remembertime', 7200);
	$site_https_login = $gBitSystem->getConfig('site_https_login', 'n');
	$site_https_login_required = $gBitSystem->getConfig('site_https_login_required', 'n');
	$change_language = $gBitSystem->getConfig("change_language", 'y');

	$gBitSmarty->assign('allow_register', $allow_register);
	$gBitSmarty->assign('site_url_index', $site_url_index);
	$gBitSmarty->assign('site_use_proxy', $site_use_proxy);
	$gBitSmarty->assign('site_proxy_host', $site_proxy_host);
	$gBitSmarty->assign('site_proxy_port', $site_proxy_port);
	$gBitSmarty->assign('change_language', $change_language);
	$gBitSmarty->assign('eponymous_groups', $eponymous_groups);

	$site_user_assigned_modules = 'n';
	$gBitSmarty->assign('remembertime', $remembertime);
	$gBitSmarty->assign('webserverauth', 'n');
	$gBitSmarty->assign('uf_use_db', 'y');
	$gBitSmarty->assign('uf_use_dir', '');
	$gBitSmarty->assign('userfiles_quota', 30);
	$gBitSmarty->assign('register_passcode', $register_passcode);
	$gBitSmarty->assign('use_register_passcode', $use_register_passcode);
	$gBitSmarty->assign('min_pass_length', 1);
	$gBitSmarty->assign('pass_chr_num', 'n');
	$gBitSmarty->assign('pass_due', 999);
	$gBitSmarty->assign('rnd_num_reg', 'n');
	// PEAR::Auth support
	$gBitSmarty->assign('auth_method', "tiki");
	$gBitSmarty->assign('auth_pear', "tiki");
	$gBitSmarty->assign('auth_create_gBitDbUser', 'n');
	$gBitSmarty->assign('auth_create_user_auth', 'n');
	$gBitSmarty->assign('auth_skip_admin', 'y');
	$gBitSmarty->assign('auth_ldap_host', 'localhost');
	$gBitSmarty->assign('auth_ldap_port', '389');
	$gBitSmarty->assign('auth_ldap_scope', 'sub');
	$gBitSmarty->assign('auth_ldap_basedn', '');
	$gBitSmarty->assign('auth_ldap_userdn', '');
	$gBitSmarty->assign('auth_ldap_userattr', 'uid');
	$gBitSmarty->assign('auth_ldap_useroc', 'inetOrgPerson');
	$gBitSmarty->assign('auth_ldap_groupdn', '');
	$gBitSmarty->assign('auth_ldap_groupattr', 'cn');
	$gBitSmarty->assign('auth_ldap_groupoc', 'groupOfUniqueNames');
	$gBitSmarty->assign('auth_ldap_memberattr', 'uniqueMember');
	$gBitSmarty->assign('auth_ldap_memberisdn', 'y');
	$gBitSmarty->assign('auth_ldap_adminuser', '');
	$gBitSmarty->assign('auth_ldap_adminpass', '');

	// Permissions
	// Get group permissions here

//	======================= HOPEFULLY WE CAN SURVIVE WITHOUT THIS PREFERENCE ASSIGNEMENT STUFF =================
//	if (is_array($gBitUser->mPerms)) {	// This avoids php warning during install
//		foreach( array_keys( $gBitUser->mPerms ) as $perm ) {
//			// print("Asignando permiso global : $perm<br/>");
//			$gBitSmarty->assign("$perm", 'y');
//			$$perm = 'y';
//		}
//	}
//	============================================================================================================

	// If we are processing a login then do not generate the challenge
	// if we are in any other case then yes.
	if( !empty( $_SERVER["REQUEST_URI"] ) && !strstr($_SERVER["REQUEST_URI"], USERS_PKG_URL . 'validate')) {
		if ($gBitSystem->getConfig('feature_challenge') == 'y') {
			$chall = $gBitUser->generateChallenge();

			$_SESSION["challenge"] = $chall;
			$gBitSmarty->assign('challenge', $chall);
		}
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
		$gBitSystem->registerAppMenu( USERS_PKG_NAME, 'My '.$displayTitle, ($gBitSystem->getConfig('users_preferences') == 'y' ? USERS_PKG_URL.'my.php':''), 'bitpackage:users/menu_users.tpl' );
	}
?>
