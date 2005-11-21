<?php
global $gBitSystem, $gBitUser, $gBitSmarty;

$gBitSystem->registerPackage( 'users', dirname( __FILE__).'/', FALSE );

$gBitSystem->registerNotifyEvent( array( "user_registers" => tra("A user registers") ) );

if( $gBitSystem->isFeatureActive( 'feature_userfiles' ) ) {
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
	$gBitUser = new BitPermUser();

	$cookie_path = $gBitSystem->getPreference('cookie_path', BIT_ROOT_URL);
	$cookie_path = ($cookie_path == '') ? $cookie_path : BIT_ROOT_URL;
	$gBitSystem->storePreference( 'cookie_path', $cookie_path );

	// set session lifetime
	$session_lifetime = $gBitSystem->getPreference( 'session_lifetime', '0' );
	if( $session_lifetime > 0 ) {
		ini_set( 'session.gc_maxlifetime', $session_lifetime );
	}

	// is session data  stored in DB or in filesystem?
	if( $gBitSystem->isFeatureActive( 'y' ) && !empty( $gBitDbType ) ) {
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
		session_set_cookie_params($session_lifetime, $cookie_path, $gBitSystem->getPreference('cookie_domain'));
	} else {
		session_set_cookie_params($session_lifetime, BIT_ROOT_URL);
	}
	if (ini_get('safe_mode') && ini_get('safe_mode_gid')) {
		umask(0007);
	}
	session_start();

	// in the case of tikis on same domain we have to distinguish the realm
	// changed cookie and session variable name by a name made with siteTitle
	$cookie_site = strtolower( ereg_replace("[^a-zA-Z0-9]", "", $gBitSystem->getPreference('siteTitle', 'bitweaver')) );
	global $user_cookie_site;
	$user_cookie_site = 'tiki-user-' . $cookie_site;


	if( function_exists( 'tiki_login_override' ) ) {
		$gBitUser->mUserId = tiki_login_override();
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
		$auth_method = $gBitSystem->getPreference('auth_method', 'tiki');
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

	$allowRegister = $gBitSystem->getPreference("allowRegister", 'y');
	$validateUsers = $gBitSystem->getPreference("validateUsers", 'n');
	$forgotPass = $gBitSystem->getPreference("forgotPass", 'y');
	$eponymousGroups = $gBitSystem->getPreference("eponymousGroups", 'n');
	$useRegisterPasscode = $gBitSystem->getPreference("useRegisterPasscode", 'n');
	$registerPasscode = $gBitSystem->getPreference("registerPasscode", '');
	$urlIndex = $gBitSystem->getPreference("urlIndex", '');
	$use_proxy = $gBitSystem->getPreference("use_proxy", 'n');
	$proxy_host = $gBitSystem->getPreference("proxy_host", '');
	$proxy_port = $gBitSystem->getPreference("proxy_port", '');
	$session_db = $gBitSystem->getPreference("session_db", 'n');
	$session_lifetime = $gBitSystem->getPreference("session_lifetime", 0);
	$remembertime = $gBitSystem->getPreference('remembertime', 7200);
	$https_login = $gBitSystem->getPreference('https_login', 'n');
	$https_login_required = $gBitSystem->getPreference('https_login_required', 'n');
	$change_language = $gBitSystem->getPreference("change_language", 'y');

	$gBitSmarty->assign('allowRegister', $allowRegister);
	$gBitSmarty->assign('urlIndex', $urlIndex);
	$gBitSmarty->assign('use_proxy', $use_proxy);
	$gBitSmarty->assign('proxy_host', $proxy_host);
	$gBitSmarty->assign('proxy_port', $proxy_port);
	$gBitSmarty->assign('change_language', $change_language);
	$gBitSmarty->assign('eponymousGroups', $eponymousGroups);

	$user_assigned_modules = 'n';
	$gBitSmarty->assign('remembertime', $remembertime);
	$gBitSmarty->assign('webserverauth', 'n');
	$gBitSmarty->assign('uf_use_db', 'y');
	$gBitSmarty->assign('uf_use_dir', '');
	$gBitSmarty->assign('userfiles_quota', 30);
	$gBitSmarty->assign('registerPasscode', $registerPasscode);
	$gBitSmarty->assign('useRegisterPasscode', $useRegisterPasscode);
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
	if (is_array($gBitUser->mPerms)) {	// This avoids php warning during install
		foreach( array_keys( $gBitUser->mPerms ) as $perm ) {
			// print("Asignando permiso global : $perm<br/>");
			$gBitSmarty->assign("$perm", 'y');
			$$perm = 'y';
		}
	}

	if( $gBitUser->isRegistered() && $gBitSystem->getPreference('feature_usermenu') == 'y' ) {
		if (!isset($_SESSION['usermenu'])) {
			include_once(USERS_PKG_PATH . 'user_menu_lib.php');

			$user_menus = $usermenulib->list_usermenus($gBitUser->mUserId, 0, -1, 'position_asc', '');
			$gBitSmarty->assign('usr_user_menus', $user_menus['data']);
			$_SESSION['usermenu'] = $user_menus['data'];
		} else {
			$user_menus = $_SESSION['usermenu'];
			$gBitSmarty->assign('usr_user_menus', $user_menus);
		}
	}
	// If we are processing a login then do not generate the challenge
	// if we are in any other case then yes.
	if( !empty( $_SERVER["REQUEST_URI"] ) && !strstr($_SERVER["REQUEST_URI"], USERS_PKG_URL . 'validate')) {
		if ($gBitSystem->getPreference('feature_challenge') == 'y') {
			$chall = $gBitUser->generateChallenge();

			$_SESSION["challenge"] = $chall;
			$gBitSmarty->assign('challenge', $chall);
		}
	}

	$gBitSmarty->assign('user_dbl', 'y');

	$allowMsgs = 'n';
	if( $gBitUser->isRegistered() ) {
		global $tasks_use_dates, $tasks_maxRecords, $allowMsgs;
		$user_dbl = $gBitUser->getPreference( 'user_dbl', 'y');
		$gBitSmarty->assign('user_dbl', $user_dbl);
		$allowMsgs = $gBitUser->getPreference( 'allowMsgs', 'y');
		$tasks_use_dates = $gBitUser->getPreference( 'tasks_use_dates');
		$tasks_maxRecords = $gBitUser->getPreference( 'tasks_maxRecords');
		$gBitSmarty->assign('tasks_use_dates', $tasks_use_dates);
		$gBitSmarty->assign('tasks_maxRecords', $tasks_maxRecords);
		$gBitSmarty->assign('allowMsgs', $allowMsgs);
	}

	// register 'my' menu
	if( $gBitUser->isValid() && ( $gBitUser->isRegistered() || !$gBitSystem->isFeatureActive( 'hide_my_top_bar_link' ) ) ) {
		$displayTitle = !empty( $gBitSystem->mPrefs['site_menu_title'] ) ? $gBitSystem->mPrefs['site_menu_title'] : $gBitSystem->getPreference( 'siteTitle', 'Site' );
		$gBitSystem->registerAppMenu( 'users', 'My '.$displayTitle, ($gBitSystem->getPreference('feature_userPreferences') == 'y' ? USERS_PKG_URL.'my.php':''), 'bitpackage:users/menu_users.tpl' );
	}
?>
