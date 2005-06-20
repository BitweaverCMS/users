<?php
global $gBitSystem, $gBitUser, $smarty;

$gBitSystem->registerPackage( 'users', dirname( __FILE__).'/', FALSE );

$gBitSystem->registerAppMenu( 'login', 'Login', '', '', 'login');

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
	$gBitLoc['cookie_path'] = $cookie_path;

	// set session lifetime
	$session_lifetime = $gBitSystem->getPreference('session_lifetime', '0');
	if ($session_lifetime > 0)
	{
		ini_set('session.gc_maxlifetime', $session_lifetime * 60);
	}
	// is session data  stored in DB or in filesystem?
	if( $gBitSystem->isFeatureActive( 'y' ) && !empty( $gBitDbType ) )
	{
		include(UTIL_PKG_PATH . 'adodb/session/adodb-session.php');
		ADODB_Session::dataFieldName('session_data');
		ADODB_Session::driver($gBitDbType);
		ADODB_Session::host($gBitDbHost);
		ADODB_Session::user($gBitDbUser);
		ADODB_Session::password($gBitDbPassword);
		ADODB_Session::database($gBitDbName);
		ADODB_Session::table(BIT_DB_PREFIX.'sessions');
		ini_set('session.save_handler', 'user');
	}

	session_name( BIT_SESSION_NAME );
	if ($gBitSystem->isFeatureActive('rememberme')) {
		session_set_cookie_params($session_lifetime, $cookie_path, $gBitSystem->getPreference('cookie_domain'));
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
				if ($gBitUser->exists($_SERVER['REMOTE_USER'])) {
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

	$smarty->assign_by_ref('gBitUser', $gBitUser);
	$smarty->register_object('gBitUser', $gBitUser, array(), true, array('hasPermission'));

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

	$smarty->assign('allowRegister', $allowRegister);
	$smarty->assign('urlIndex', $urlIndex);
	$smarty->assign('use_proxy', $use_proxy);
	$smarty->assign('proxy_host', $proxy_host);
	$smarty->assign('proxy_port', $proxy_port);
	$smarty->assign('change_language', $change_language);
	$smarty->assign('eponymousGroups', $eponymousGroups);

	$user_assigned_modules = 'n';
	$smarty->assign('remembertime', $remembertime);
	$smarty->assign('webserverauth', 'n');
	$smarty->assign('uf_use_db', 'y');
	$smarty->assign('uf_use_dir', '');
	$smarty->assign('userfiles_quota', 30);
	$smarty->assign('registerPasscode', $registerPasscode);
	$smarty->assign('useRegisterPasscode', $useRegisterPasscode);
	$smarty->assign('min_pass_length', 1);
	$smarty->assign('pass_chr_num', 'n');
	$smarty->assign('pass_due', 999);
	$smarty->assign('rnd_num_reg', 'n');
	// PEAR::Auth support
	$smarty->assign('auth_method', "tiki");
	$smarty->assign('auth_pear', "tiki");
	$smarty->assign('auth_create_gBitDbUser', 'n');
	$smarty->assign('auth_create_user_auth', 'n');
	$smarty->assign('auth_skip_admin', 'y');
	$smarty->assign('auth_ldap_host', 'localhost');
	$smarty->assign('auth_ldap_port', '389');
	$smarty->assign('auth_ldap_scope', 'sub');
	$smarty->assign('auth_ldap_basedn', '');
	$smarty->assign('auth_ldap_userdn', '');
	$smarty->assign('auth_ldap_userattr', 'uid');
	$smarty->assign('auth_ldap_useroc', 'inetOrgPerson');
	$smarty->assign('auth_ldap_groupdn', '');
	$smarty->assign('auth_ldap_groupattr', 'cn');
	$smarty->assign('auth_ldap_groupoc', 'groupOfUniqueNames');
	$smarty->assign('auth_ldap_memberattr', 'uniqueMember');
	$smarty->assign('auth_ldap_memberisdn', 'y');
	$smarty->assign('auth_ldap_adminuser', '');
	$smarty->assign('auth_ldap_adminpass', '');

	// Permissions
	// Get group permissions here
	if (is_array($gBitUser->mPerms)) {	// This avoids php warning during install
		foreach( array_keys( $gBitUser->mPerms ) as $perm ) {
			// print("Asignando permiso global : $perm<br/>");
			$smarty->assign("$perm", 'y');
			$$perm = 'y';
		}
	}

	if( $gBitUser->isRegistered() && $gBitSystem->getPreference('feature_usermenu') == 'y' ) {
		if (!isset($_SESSION['usermenu'])) {
			include_once(USERS_PKG_PATH . 'user_menu_lib.php');

			$user_menus = $usermenulib->list_usermenus($gBitUser->mUserId, 0, -1, 'position_asc', '');
			$smarty->assign('usr_user_menus', $user_menus['data']);
			$_SESSION['usermenu'] = $user_menus['data'];
		} else {
			$user_menus = $_SESSION['usermenu'];
			$smarty->assign('usr_user_menus', $user_menus);
		}
	}
	// If we are processing a login then do not generate the challenge
	// if we are in any other case then yes.
	if( !empty( $_SERVER["REQUEST_URI"] ) && !strstr($_SERVER["REQUEST_URI"], USERS_PKG_URL . 'validate')) {
		if ($gBitSystem->getPreference('feature_challenge') == 'y') {
			$chall = $gBitUser->generateChallenge();

			$_SESSION["challenge"] = $chall;
			$smarty->assign('challenge', $chall);
		}
	}

	$smarty->assign('user_dbl', 'y');

	$allowMsgs = 'n';
	if( $gBitUser->isRegistered() ) {
		global $tasks_use_dates, $tasks_maxRecords, $allowMsgs;
		$allowMsgs = $gBitUser->getPreference( 'allowMsgs', 'y');
		$tasks_use_dates = $gBitUser->getPreference( 'tasks_use_dates');
		$tasks_maxRecords = $gBitUser->getPreference( 'tasks_maxRecords');
		$smarty->assign('tasks_use_dates', $tasks_use_dates);
		$smarty->assign('tasks_maxRecords', $tasks_maxRecords);
		$smarty->assign('allowMsgs', $allowMsgs);
	}

?>
