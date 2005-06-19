<?php
// $Header: /cvsroot/bitweaver/_bit_users/admin/admin_login_inc.php,v 1.1 2005/06/19 05:12:24 bitweaver Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
if (isset($_REQUEST["loginprefs"])) {
	
	if (isset($_REQUEST["eponymousGroups"]) && $_REQUEST["eponymousGroups"] == "on") {
		$gBitSystem->storePreference("eponymousGroups", 'y');
		$smarty->assign('eponymousGroups', 'y');
	} else {
		$gBitSystem->storePreference("eponymousGroups", 'n');
		$smarty->assign('eponymousGroups', 'n');
	}
	if (isset($_REQUEST["allowRegister"]) && $_REQUEST["allowRegister"] == "on") {
		$gBitSystem->storePreference("allowRegister", 'y');
		$smarty->assign('allowRegister', 'y');
	} else {
		$gBitSystem->storePreference("allowRegister", 'n');
		$smarty->assign('allowRegister', 'n');
	}
	if (isset($_REQUEST["webserverauth"]) && $_REQUEST["webserverauth"] == "on") {
		$gBitSystem->storePreference("webserverauth", 'y');
		$smarty->assign('webserverauth', 'y');
	} else {
		$gBitSystem->storePreference("webserverauth", 'n');
		$smarty->assign('webserverauth', 'n');
	}
	if (isset($_REQUEST["useRegisterPasscode"]) && $_REQUEST["useRegisterPasscode"] == "on") {
		$gBitSystem->storePreference("useRegisterPasscode", 'y');
		$smarty->assign('useRegisterPasscode', 'y');
	} else {
		$gBitSystem->storePreference("useRegisterPasscode", 'n');
		$smarty->assign('useRegisterPasscode', 'n');
	}
	$gBitSystem->storePreference("registerPasscode", $_REQUEST["registerPasscode"]);
	$smarty->assign('registerPasscode', $_REQUEST["registerPasscode"]);
	$gBitSystem->storePreference("min_pass_length", $_REQUEST["min_pass_length"]);
	$smarty->assign('min_pass_length', $_REQUEST["min_pass_length"]);
	if (isset($_REQUEST["user_password_generator"]) && $_REQUEST["user_password_generator"] == "on") {
		$gBitSystem->storePreference("user_password_generator", 'y');
		$smarty->assign('user_password_generator', 'y');
	} else {
		$gBitSystem->storePreference("user_password_generator", 'n');
		$smarty->assign('user_password_generator', 'n');
	}
	if (isset($_REQUEST["validateUsers"]) && $_REQUEST["validateUsers"] == "on") {
		$gBitSystem->storePreference("validateUsers", 'y');
		$smarty->assign('validateUsers', 'y');
	} else {
		$gBitSystem->storePreference("validateUsers", 'n');
		$smarty->assign('validateUsers', 'n');
	}
	if (isset($_REQUEST["validateEmail"]) && $_REQUEST["validateEmail"] == "on" && $gBitSystem->hasValidSenderEmail() ) {
		$gBitSystem->storePreference("validateEmail", 'y');
		$smarty->assign('validateEmail', 'y');
	} else {
		$gBitSystem->storePreference("validateEmail", 'n');
		$smarty->assign('validateEmail', 'n');
	}
	if (isset($_REQUEST["rnd_num_reg"]) && $_REQUEST["rnd_num_reg"] == "on") {
		$gBitSystem->storePreference("rnd_num_reg", 'y');
		$smarty->assign('rnd_num_reg', 'y');
	} else {
		$gBitSystem->storePreference("rnd_num_reg", 'n');
		$smarty->assign('rnd_num_reg', 'n');
	}
	if (isset($_REQUEST["pass_chr_num"]) && $_REQUEST["pass_chr_num"] == "on") {
		$gBitSystem->storePreference("pass_chr_num", 'y');
		$smarty->assign('pass_chr_num', 'y');
	} else {
		$gBitSystem->storePreference("pass_chr_num", 'n');
		$smarty->assign('pass_chr_num', 'n');
	}
	if (isset($_REQUEST["feature_challenge"]) && $_REQUEST["feature_challenge"] == "on") {
		$gBitSystem->storePreference("feature_challenge", 'y');
		$smarty->assign('feature_challenge', 'y');
	} else {
		$gBitSystem->storePreference("feature_challenge", 'n');
		$smarty->assign('feature_challenge', 'n');
	}
	if (isset($_REQUEST["feature_clear_passwords"]) && $_REQUEST["feature_clear_passwords"] == "on") {
		$gBitSystem->storePreference("feature_clear_passwords", 'y');
		$smarty->assign('feature_clear_passwords', 'y');
	} else {
		$gBitSystem->storePreference("feature_clear_passwords", 'n');
		$smarty->assign('feature_clear_passwords', 'n');
	}
	if (isset($_REQUEST["forgotPass"]) && $_REQUEST["forgotPass"] == "on") {
		$gBitSystem->storePreference("forgotPass", 'y');
		$smarty->assign('forgotPass', 'y');
	} else {
		$gBitSystem->storePreference("forgotPass", 'n');
		$smarty->assign('forgotPass', 'n');
	}
	if (isset($_REQUEST["rememberme"]) && $_REQUEST["rememberme"] == "on") {
		$gBitSystem->storePreference("rememberme", 'y');
		$smarty->assign('rememberme', 'y');
	} else {
		$gBitSystem->storePreference("rememberme", 'n');
		$smarty->assign('rememberme', 'n');
	}
	$gBitSystem->storePreference("pass_due", $_REQUEST["pass_due"]);
	$smarty->assign('pass_due', $_REQUEST["pass_due"]);
	$gBitSystem->storePreference('remembertime', $_REQUEST['remembertime']);
	$smarty->assign('remembertime', $_REQUEST['remembertime']);
	$v = isset($_REQUEST['cookie_domain']) ? $_REQUEST['cookie_domain'] : $_SERVER['SERVER_NAME'];
	$gBitSystem->storePreference('cookie_domain', $v);
	$smarty->assign('cookie_domain', $v);
	if ( isset($_REQUEST['cookie_path']) ) {
		$v = $_REQUEST['cookie_path'];
		$v = ( preg_match( "/^\//", $v ) ) ? $v : "/" . $v;
	} else {
		$v = BIT_ROOT_URL;
	}
	$gBitSystem->storePreference('cookie_path', $v);
	$smarty->assign('cookie_path', $v);
	if (isset($_REQUEST["auth_method"])) {
		$gBitSystem->storePreference('auth_method', $_REQUEST['auth_method']);
		$smarty->assign('auth_method', $_REQUEST['auth_method']);
	}
	$b = (isset($_REQUEST['feature_ticketlib']) && $_REQUEST['feature_ticketlib'] == 'on') ? 'y' : 'n';
	$gBitSystem->storePreference('feature_ticketlib', $b);
	$smarty->assign('feature_ticketlib', $b);
}
if (isset($_REQUEST["httpprefs"])) {
	$b = (isset($_REQUEST['https_login']) && $_REQUEST['https_login'] == 'on') ? 'y' : 'n';
	$gBitSystem->storePreference('https_login', $b);
	$smarty->assign('https_login', $b);
	$b = (isset($_REQUEST['https_login_required']) && $_REQUEST['https_login_required'] == 'on') ? 'y' : 'n';
	$gBitSystem->storePreference('https_login_required', $b);
	$smarty->assign('https_login_required', $b);
	/* # not implemented
	   $b = isset($_REQUEST['http_basic_auth']) && $_REQUEST['http_basic_auth'] == 'on';
	   $gBitSystem->storePreference('http_basic_auth', $b);
	   $smarty->assign('http_basic_auth', $b);
	*/
	$v = isset($_REQUEST['http_domain']) ? $_REQUEST['http_domain'] : '';
	$gBitSystem->storePreference('http_domain', $v);
	$smarty->assign('http_domain', $v);
	$v = isset($_REQUEST['http_port']) ? $_REQUEST['http_port'] : 80;
	$gBitSystem->storePreference('http_port', $v);
	$smarty->assign('http_port', $v);
	$v = isset($_REQUEST['http_prefix']) ? $_REQUEST['http_prefix'] : BIT_ROOT_URL;
	$gBitSystem->storePreference('http_prefix', $v);
	$smarty->assign('http_prefix', $v);
	$v = isset($_REQUEST['https_domain']) ? $_REQUEST['https_domain'] : '';
	$gBitSystem->storePreference('https_domain', $v);
	$smarty->assign('https_domain', $v);
	$v = isset($_REQUEST['https_port']) ? $_REQUEST['https_port'] : 443;
	$gBitSystem->storePreference('https_port', $v);
	$smarty->assign('https_port', $v);
	$v = isset($_REQUEST['https_prefix']) ? $_REQUEST['https_prefix'] : BIT_ROOT_URL;
	$gBitSystem->storePreference('https_prefix', $v);
	$smarty->assign('https_prefix', $v);
}
if (isset($_REQUEST["auth_pear"])) {
	
	if (isset($_REQUEST["auth_create_gBitDbUser"]) && $_REQUEST["auth_create_gBitDbUser"] == "on") {
	$gBitSystem->storePreference("auth_create_gBitDbUser", 'y');
	$smarty->assign("auth_create_gBitDbUser", 'y');
	} else {
	$gBitSystem->storePreference("auth_create_gBitDbUser", 'n');
	$smarty->assign("auth_create_gBitDbUser", 'n');
	}
	if (isset($_REQUEST["auth_create_user_auth"]) && $_REQUEST["auth_create_user_auth"] == "on") {
	$gBitSystem->storePreference("auth_create_user_auth", 'y');
	$smarty->assign("auth_create_user_auth", 'y');
	} else {
	$gBitSystem->storePreference("auth_create_user_auth", 'n');
	$smarty->assign("auth_create_user_auth", 'n');
	}
	if (isset($_REQUEST["auth_skip_admin"]) && $_REQUEST["auth_skip_admin"] == "on") {
	$gBitSystem->storePreference("auth_skip_admin", 'y');
	$smarty->assign("auth_skip_admin", 'y');
	} else {
	$gBitSystem->storePreference("auth_skip_admin", 'n');
	$smarty->assign("auth_skip_admin", 'n');
	}
	if (isset($_REQUEST["auth_ldap_host"])) {
	$gBitSystem->storePreference("auth_ldap_host", $_REQUEST["auth_ldap_host"]);
	$smarty->assign('auth_ldap_host', $_REQUEST["auth_ldap_host"]);
	}
	if (isset($_REQUEST["auth_ldap_port"])) {
	$gBitSystem->storePreference("auth_ldap_port", $_REQUEST["auth_ldap_port"]);
	$smarty->assign('auth_ldap_port', $_REQUEST["auth_ldap_port"]);
	}
	if (isset($_REQUEST["auth_ldap_scope"])) {
	$gBitSystem->storePreference("auth_ldap_scope", $_REQUEST["auth_ldap_scope"]);
	$smarty->assign('auth_ldap_scope', $_REQUEST["auth_ldap_scope"]);
	}
	if (isset($_REQUEST["auth_ldap_basedn"])) {
	$gBitSystem->storePreference("auth_ldap_basedn", $_REQUEST["auth_ldap_basedn"]);
	$smarty->assign('auth_ldap_basedn', $_REQUEST["auth_ldap_basedn"]);
	}
	if (isset($_REQUEST["auth_ldap_userdn"])) {
	$gBitSystem->storePreference("auth_ldap_userdn", $_REQUEST["auth_ldap_userdn"]);
	$smarty->assign('auth_ldap_userdn', $_REQUEST["auth_ldap_userdn"]);
	}
	if (isset($_REQUEST["auth_ldap_userattr"])) {
	$gBitSystem->storePreference("auth_ldap_userattr", $_REQUEST["auth_ldap_userattr"]);
	$smarty->assign('auth_ldap_userattr', $_REQUEST["auth_ldap_userattr"]);
	}
	if (isset($_REQUEST["auth_ldap_useroc"])) {
	$gBitSystem->storePreference("auth_ldap_useroc", $_REQUEST["auth_ldap_useroc"]);
	$smarty->assign('auth_ldap_useroc', $_REQUEST["auth_ldap_useroc"]);
	}
	if (isset($_REQUEST["auth_ldap_groupdn"])) {
	$gBitSystem->storePreference("auth_ldap_groupdn", $_REQUEST["auth_ldap_groupdn"]);
	$smarty->assign('auth_ldap_groupdn', $_REQUEST["auth_ldap_groupdn"]);
	}
	if (isset($_REQUEST["auth_ldap_groupattr"])) {
	$gBitSystem->storePreference("auth_ldap_groupattr", $_REQUEST["auth_ldap_groupattr"]);
	$smarty->assign('auth_ldap_groupattr', $_REQUEST["auth_ldap_groupattr"]);
	}
	if (isset($_REQUEST["auth_ldap_groupoc"])) {
	$gBitSystem->storePreference("auth_ldap_groupoc", $_REQUEST["auth_ldap_groupoc"]);
	$smarty->assign('auth_ldap_groupoc', $_REQUEST["auth_ldap_groupoc"]);
	}
	if (isset($_REQUEST["auth_ldap_memberattr"])) {
	$gBitSystem->storePreference("auth_ldap_memberattr", $_REQUEST["auth_ldap_memberattr"]);
	$smarty->assign('auth_ldap_ldap_memberattr', $_REQUEST["auth_ldap_memberattr"]);
	}
	if (isset($_REQUEST["auth_ldap_memberisdn"]) && $_REQUEST["auth_ldap_memberisdn"] == "on") {
	$gBitSystem->storePreference("auth_ldap_memberisdn", 'y');
	$smarty->assign("auth_ldap_memberisdn", 'y');
	} else {
	$gBitSystem->storePreference("auth_ldap_memberisdn", 'n');
	$smarty->assign("auth_ldap_memberisdn", 'n');
	}
	if (isset($_REQUEST["auth_ldap_adminuser"])) {
	$gBitSystem->storePreference("auth_ldap_adminuser", $_REQUEST["auth_ldap_adminuser"]);
	$smarty->assign('auth_ldap_adminuser', $_REQUEST["auth_ldap_adminuser"]);
	}
	if (isset($_REQUEST["auth_ldap_adminpass"])) {
	$gBitSystem->storePreference("auth_ldap_adminpass", $_REQUEST["auth_ldap_adminpass"]);
	$smarty->assign('auth_ldap_adminpass', $_REQUEST["auth_ldap_adminpass"]);
	}
}
$smarty->assign("change_theme", $gBitSystem->getPreference("change_theme", "n"));
$smarty->assign("change_language", $gBitSystem->getPreference("change_language", "n"));
$smarty->assign("eponymousGroups", $gBitSystem->getPreference("eponymousGroups", 'n'));
$smarty->assign("allowRegister", $gBitSystem->getPreference("allowRegister", 'n'));
$smarty->assign("webserverauth", $gBitSystem->getPreference("webserverauth", 'n'));
$smarty->assign("useRegisterPasscode", $gBitSystem->getPreference("useRegisterPasscode", 'n'));
$smarty->assign("registerPasscode", $gBitSystem->getPreference("registerPasscode", ''));
$smarty->assign("min_pass_length", $gBitSystem->getPreference("min_pass_length", '1'));
$smarty->assign("pass_due", $gBitSystem->getPreference("pass_due", '999'));
$smarty->assign("validateUsers", $gBitSystem->getPreference("validateUsers", 'n'));
$smarty->assign("validateEmail", $gBitSystem->getPreference("validateEmail", 'n'));
$smarty->assign("rnd_num_reg", $gBitSystem->getPreference("rnd_num_reg", 'n'));
$smarty->assign("pass_chr_num", $gBitSystem->getPreference("pass_chr_num", 'n'));
$smarty->assign("feature_challenge", $gBitSystem->getPreference("feature_challenge", 'n'));
$smarty->assign("feature_clear_passwords", $gBitSystem->getPreference("feature_clear_passwords", 'n'));
$smarty->assign("forgotPass", $gBitSystem->getPreference("forgotPass", 'n'));
$smarty->assign("https_login", $gBitSystem->getPreference("https_login", 'n'));
$smarty->assign("https_login_required", $gBitSystem->getPreference("https_login_required", 'n'));
$smarty->assign("http_domain", $gBitSystem->getPreference("http_domain", ''));
$smarty->assign("http_port", $gBitSystem->getPreference("http_port", '80'));
$smarty->assign("http_prefix", $gBitSystem->getPreference("http_prefix", BIT_ROOT_URL));
$smarty->assign("https_domain", $gBitSystem->getPreference("https_domain", ''));
$smarty->assign("https_port", $gBitSystem->getPreference("https_port", '443'));
$smarty->assign("https_prefix", $gBitSystem->getPreference("https_prefix", BIT_ROOT_URL));
$smarty->assign("remembertime", $gBitSystem->getPreference("remembertime", 2592000));
$smarty->assign("cookie_domain", $gBitSystem->getPreference("cookie_domain", $_SERVER['SERVER_NAME']));
$smarty->assign("cookie_path", $gBitSystem->getPreference("cookie_path", BIT_ROOT_URL));
$smarty->assign("auth_method", $gBitSystem->getPreference("auth_method", 'tiki'));
$smarty->assign("feature_ticketlib", $gBitSystem->getPreference("feature_ticketlib", 'n'));
$smarty->assign("auth_create_gBitDbUser", $gBitSystem->getPreference("auth_create_gBitDbUser", 'n'));
$smarty->assign("auth_create_user_auth", $gBitSystem->getPreference("auth_create_user_auth", 'n'));
$smarty->assign("auth_skip_admin", $gBitSystem->getPreference("auth_skip_admin", 'y'));
$smarty->assign("auth_ldap_host", $gBitSystem->getPreference("auth_ldap_host", 'localhost'));
$smarty->assign("auth_ldap_port", $gBitSystem->getPreference("auth_ldap_port", '389'));
$smarty->assign("auth_ldap_scope", $gBitSystem->getPreference("auth_ldap_scope", 'sub'));
$smarty->assign("auth_ldap_basedn", $gBitSystem->getPreference("auth_ldap_basedn", ''));
$smarty->assign("auth_ldap_userdn", $gBitSystem->getPreference("auth_ldap_userdn", ''));
$smarty->assign("auth_ldap_userattr", $gBitSystem->getPreference("auth_ldap_userattr", 'uid'));
$smarty->assign("auth_ldap_useroc", $gBitSystem->getPreference("auth_ldap_useroc", 'inetOrgPerson'));
$smarty->assign("auth_ldap_groupdn", $gBitSystem->getPreference("auth_ldap_groupdn", ''));
$smarty->assign("auth_ldap_groupattr", $gBitSystem->getPreference("auth_ldap_groupattr", 'cn'));
$smarty->assign("auth_ldap_groupoc", $gBitSystem->getPreference("auth_ldap_groupoc", 'groupOfUniqueNames'));
$smarty->assign("auth_ldap_ldap_memberattr", $gBitSystem->getPreference("auth_ldap_ldap_memberattr", 'uniqueMember'));
$smarty->assign("auth_ldap_memberisdn", $gBitSystem->getPreference("auth_ldap_memberisdn", 'y'));
$smarty->assign("auth_ldap_adminuser", $gBitSystem->getPreference("auth_ldap_adminuser", ''));
$smarty->assign("auth_ldap_adminpass", $gBitSystem->getPreference("auth_ldap_adminpass", ''));

?>
