<?php
// $Header: /cvsroot/bitweaver/_bit_users/admin/admin_login_inc.php,v 1.1.1.1.2.5 2005/10/01 13:09:34 spiderr Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
if (isset($_REQUEST["loginprefs"])) {

	if (isset($_REQUEST["eponymousGroups"]) && $_REQUEST["eponymousGroups"] == "on") {
		$gBitSystem->storePreference("eponymousGroups", 'y');
		$gBitSmarty->assign('eponymousGroups', 'y');
	} else {
		$gBitSystem->storePreference("eponymousGroups", 'n');
		$gBitSmarty->assign('eponymousGroups', 'n');
	}
	if (isset($_REQUEST["allowRegister"]) && $_REQUEST["allowRegister"] == "on") {
		$gBitSystem->storePreference("allowRegister", 'y');
		$gBitSmarty->assign('allowRegister', 'y');
	} else {
		$gBitSystem->storePreference("allowRegister", 'n');
		$gBitSmarty->assign('allowRegister', 'n');
	}
	if (isset($_REQUEST["send_welcome_email"]) && $_REQUEST["send_welcome_email"] == "on") {
		$gBitSystem->storePreference("send_welcome_email", 'y');
		$gBitSmarty->assign('send_welcome_email', 'y');
	} else {
		$gBitSystem->storePreference("send_welcome_email", 'n');
		$gBitSmarty->assign('send_welcome_email', 'n');
	}
	if (isset($_REQUEST["webserverauth"]) && $_REQUEST["webserverauth"] == "on") {
		$gBitSystem->storePreference("webserverauth", 'y');
		$gBitSmarty->assign('webserverauth', 'y');
	} else {
		$gBitSystem->storePreference("webserverauth", 'n');
		$gBitSmarty->assign('webserverauth', 'n');
	}
	if (isset($_REQUEST["useRegisterPasscode"]) && $_REQUEST["useRegisterPasscode"] == "on") {
		$gBitSystem->storePreference("useRegisterPasscode", 'y');
		$gBitSmarty->assign('useRegisterPasscode', 'y');
	} else {
		$gBitSystem->storePreference("useRegisterPasscode", 'n');
		$gBitSmarty->assign('useRegisterPasscode', 'n');
	}
	$gBitSystem->storePreference("registerPasscode", $_REQUEST["registerPasscode"]);
	$gBitSmarty->assign('registerPasscode', $_REQUEST["registerPasscode"]);
	$gBitSystem->storePreference("min_pass_length", $_REQUEST["min_pass_length"]);
	$gBitSmarty->assign('min_pass_length', $_REQUEST["min_pass_length"]);
	if (isset($_REQUEST["user_password_generator"]) && $_REQUEST["user_password_generator"] == "on") {
		$gBitSystem->storePreference("user_password_generator", 'y');
		$gBitSmarty->assign('user_password_generator', 'y');
	} else {
		$gBitSystem->storePreference("user_password_generator", 'n');
		$gBitSmarty->assign('user_password_generator', 'n');
	}
	if (isset($_REQUEST["validateUsers"]) && $_REQUEST["validateUsers"] == "on") {
		$gBitSystem->storePreference("validateUsers", 'y');
		$gBitSmarty->assign('validateUsers', 'y');
	} else {
		$gBitSystem->storePreference("validateUsers", 'n');
		$gBitSmarty->assign('validateUsers', 'n');
	}
	if (isset($_REQUEST["validateEmail"]) && $_REQUEST["validateEmail"] == "on" && $gBitSystem->hasValidSenderEmail() ) {
		$gBitSystem->storePreference("validateEmail", 'y');
		$gBitSmarty->assign('validateEmail', 'y');
	} else {
		$gBitSystem->storePreference("validateEmail", 'n');
		$gBitSmarty->assign('validateEmail', 'n');
	}
	if (isset($_REQUEST["rnd_num_reg"]) && $_REQUEST["rnd_num_reg"] == "on") {
		$gBitSystem->storePreference("rnd_num_reg", 'y');
		$gBitSmarty->assign('rnd_num_reg', 'y');
	} else {
		$gBitSystem->storePreference("rnd_num_reg", 'n');
		$gBitSmarty->assign('rnd_num_reg', 'n');
	}
	if (isset($_REQUEST["pass_chr_num"]) && $_REQUEST["pass_chr_num"] == "on") {
		$gBitSystem->storePreference("pass_chr_num", 'y');
		$gBitSmarty->assign('pass_chr_num', 'y');
	} else {
		$gBitSystem->storePreference("pass_chr_num", 'n');
		$gBitSmarty->assign('pass_chr_num', 'n');
	}
	if (isset($_REQUEST["feature_clear_passwords"]) && $_REQUEST["feature_clear_passwords"] == "on") {
		$gBitSystem->storePreference("feature_clear_passwords", 'y');
		$gBitSmarty->assign('feature_clear_passwords', 'y');
	} else {
		$gBitSystem->storePreference("feature_clear_passwords", 'n');
		$gBitSmarty->assign('feature_clear_passwords', 'n');
	}
	if (isset($_REQUEST["forgotPass"]) && $_REQUEST["forgotPass"] == "on") {
		$gBitSystem->storePreference("forgotPass", 'y');
		$gBitSmarty->assign('forgotPass', 'y');
	} else {
		$gBitSystem->storePreference("forgotPass", 'n');
		$gBitSmarty->assign('forgotPass', 'n');
	}
	if (isset($_REQUEST["rememberme"]) && $_REQUEST["rememberme"] == "on") {
		$gBitSystem->storePreference("rememberme", 'y');
		$gBitSmarty->assign('rememberme', 'y');
	} else {
		$gBitSystem->storePreference("rememberme", 'n');
		$gBitSmarty->assign('rememberme', 'n');
	}
	$gBitSystem->storePreference("pass_due", $_REQUEST["pass_due"]);
	$gBitSmarty->assign('pass_due', $_REQUEST["pass_due"]);
	$gBitSystem->storePreference('remembertime', $_REQUEST['remembertime']);
	$gBitSmarty->assign('remembertime', $_REQUEST['remembertime']);
	$v = isset($_REQUEST['cookie_domain']) ? $_REQUEST['cookie_domain'] : $_SERVER['SERVER_NAME'];
	$gBitSystem->storePreference('cookie_domain', $v);
	$gBitSmarty->assign('cookie_domain', $v);
	if ( isset($_REQUEST['cookie_path']) ) {
		$v = $_REQUEST['cookie_path'];
		$v = ( preg_match( "/^\//", $v ) ) ? $v : "/" . $v;
	} else {
		$v = BIT_ROOT_URL;
	}
	$gBitSystem->storePreference('cookie_path', $v);
	$gBitSmarty->assign('cookie_path', $v);
	if (isset($_REQUEST["auth_method"])) {
		$gBitSystem->storePreference('auth_method', $_REQUEST['auth_method']);
		$gBitSmarty->assign('auth_method', $_REQUEST['auth_method']);
	}
	$b = (isset($_REQUEST['feature_ticketlib']) && $_REQUEST['feature_ticketlib'] == 'on') ? 'y' : 'n';
	$gBitSystem->storePreference('feature_ticketlib', $b);
	$gBitSmarty->assign('feature_ticketlib', $b);
}
if (isset($_REQUEST["httpprefs"])) {
	$b = (isset($_REQUEST['https_login']) && $_REQUEST['https_login'] == 'on') ? 'y' : 'n';
	$gBitSystem->storePreference('https_login', $b);
	$gBitSmarty->assign('https_login', $b);
	$b = (isset($_REQUEST['https_login_required']) && $_REQUEST['https_login_required'] == 'on') ? 'y' : 'n';
	$gBitSystem->storePreference('https_login_required', $b);
	$gBitSmarty->assign('https_login_required', $b);
	/* # not implemented
	   $b = isset($_REQUEST['http_basic_auth']) && $_REQUEST['http_basic_auth'] == 'on';
	   $gBitSystem->storePreference('http_basic_auth', $b);
	   $gBitSmarty->assign('http_basic_auth', $b);
	*/
	$v = isset($_REQUEST['http_domain']) ? $_REQUEST['http_domain'] : '';
	$gBitSystem->storePreference('http_domain', $v);
	$gBitSmarty->assign('http_domain', $v);
	$v = isset($_REQUEST['http_port']) ? $_REQUEST['http_port'] : 80;
	$gBitSystem->storePreference('http_port', $v);
	$gBitSmarty->assign('http_port', $v);
	$v = isset($_REQUEST['http_prefix']) ? $_REQUEST['http_prefix'] : BIT_ROOT_URL;
	$gBitSystem->storePreference('http_prefix', $v);
	$gBitSmarty->assign('http_prefix', $v);
	$v = isset($_REQUEST['https_domain']) ? $_REQUEST['https_domain'] : '';
	$gBitSystem->storePreference('https_domain', $v);
	$gBitSmarty->assign('https_domain', $v);
	$v = isset($_REQUEST['https_port']) ? $_REQUEST['https_port'] : 443;
	$gBitSystem->storePreference('https_port', $v);
	$gBitSmarty->assign('https_port', $v);
	$v = isset($_REQUEST['https_prefix']) ? $_REQUEST['https_prefix'] : BIT_ROOT_URL;
	$gBitSystem->storePreference('https_prefix', $v);
	$gBitSmarty->assign('https_prefix', $v);
}
if (isset($_REQUEST["auth_pear"])) {

	if (isset($_REQUEST["auth_create_gBitDbUser"]) && $_REQUEST["auth_create_gBitDbUser"] == "on") {
	$gBitSystem->storePreference("auth_create_gBitDbUser", 'y');
	$gBitSmarty->assign("auth_create_gBitDbUser", 'y');
	} else {
	$gBitSystem->storePreference("auth_create_gBitDbUser", 'n');
	$gBitSmarty->assign("auth_create_gBitDbUser", 'n');
	}
	if (isset($_REQUEST["auth_create_user_auth"]) && $_REQUEST["auth_create_user_auth"] == "on") {
	$gBitSystem->storePreference("auth_create_user_auth", 'y');
	$gBitSmarty->assign("auth_create_user_auth", 'y');
	} else {
	$gBitSystem->storePreference("auth_create_user_auth", 'n');
	$gBitSmarty->assign("auth_create_user_auth", 'n');
	}
	if (isset($_REQUEST["auth_skip_admin"]) && $_REQUEST["auth_skip_admin"] == "on") {
	$gBitSystem->storePreference("auth_skip_admin", 'y');
	$gBitSmarty->assign("auth_skip_admin", 'y');
	} else {
	$gBitSystem->storePreference("auth_skip_admin", 'n');
	$gBitSmarty->assign("auth_skip_admin", 'n');
	}
	if (isset($_REQUEST["auth_ldap_host"])) {
	$gBitSystem->storePreference("auth_ldap_host", $_REQUEST["auth_ldap_host"]);
	$gBitSmarty->assign('auth_ldap_host', $_REQUEST["auth_ldap_host"]);
	}
	if (isset($_REQUEST["auth_ldap_port"])) {
	$gBitSystem->storePreference("auth_ldap_port", $_REQUEST["auth_ldap_port"]);
	$gBitSmarty->assign('auth_ldap_port', $_REQUEST["auth_ldap_port"]);
	}
	if (isset($_REQUEST["auth_ldap_scope"])) {
	$gBitSystem->storePreference("auth_ldap_scope", $_REQUEST["auth_ldap_scope"]);
	$gBitSmarty->assign('auth_ldap_scope', $_REQUEST["auth_ldap_scope"]);
	}
	if (isset($_REQUEST["auth_ldap_basedn"])) {
	$gBitSystem->storePreference("auth_ldap_basedn", $_REQUEST["auth_ldap_basedn"]);
	$gBitSmarty->assign('auth_ldap_basedn', $_REQUEST["auth_ldap_basedn"]);
	}
	if (isset($_REQUEST["auth_ldap_userdn"])) {
	$gBitSystem->storePreference("auth_ldap_userdn", $_REQUEST["auth_ldap_userdn"]);
	$gBitSmarty->assign('auth_ldap_userdn', $_REQUEST["auth_ldap_userdn"]);
	}
	if (isset($_REQUEST["auth_ldap_userattr"])) {
	$gBitSystem->storePreference("auth_ldap_userattr", $_REQUEST["auth_ldap_userattr"]);
	$gBitSmarty->assign('auth_ldap_userattr', $_REQUEST["auth_ldap_userattr"]);
	}
	if (isset($_REQUEST["auth_ldap_useroc"])) {
	$gBitSystem->storePreference("auth_ldap_useroc", $_REQUEST["auth_ldap_useroc"]);
	$gBitSmarty->assign('auth_ldap_useroc', $_REQUEST["auth_ldap_useroc"]);
	}
	if (isset($_REQUEST["auth_ldap_groupdn"])) {
	$gBitSystem->storePreference("auth_ldap_groupdn", $_REQUEST["auth_ldap_groupdn"]);
	$gBitSmarty->assign('auth_ldap_groupdn', $_REQUEST["auth_ldap_groupdn"]);
	}
	if (isset($_REQUEST["auth_ldap_groupattr"])) {
	$gBitSystem->storePreference("auth_ldap_groupattr", $_REQUEST["auth_ldap_groupattr"]);
	$gBitSmarty->assign('auth_ldap_groupattr', $_REQUEST["auth_ldap_groupattr"]);
	}
	if (isset($_REQUEST["auth_ldap_groupoc"])) {
	$gBitSystem->storePreference("auth_ldap_groupoc", $_REQUEST["auth_ldap_groupoc"]);
	$gBitSmarty->assign('auth_ldap_groupoc', $_REQUEST["auth_ldap_groupoc"]);
	}
	if (isset($_REQUEST["auth_ldap_memberattr"])) {
	$gBitSystem->storePreference("auth_ldap_memberattr", $_REQUEST["auth_ldap_memberattr"]);
	$gBitSmarty->assign('auth_ldap_ldap_memberattr', $_REQUEST["auth_ldap_memberattr"]);
	}
	if (isset($_REQUEST["auth_ldap_memberisdn"]) && $_REQUEST["auth_ldap_memberisdn"] == "on") {
	$gBitSystem->storePreference("auth_ldap_memberisdn", 'y');
	$gBitSmarty->assign("auth_ldap_memberisdn", 'y');
	} else {
	$gBitSystem->storePreference("auth_ldap_memberisdn", 'n');
	$gBitSmarty->assign("auth_ldap_memberisdn", 'n');
	}
	if (isset($_REQUEST["auth_ldap_adminuser"])) {
	$gBitSystem->storePreference("auth_ldap_adminuser", $_REQUEST["auth_ldap_adminuser"]);
	$gBitSmarty->assign('auth_ldap_adminuser', $_REQUEST["auth_ldap_adminuser"]);
	}
	if (isset($_REQUEST["auth_ldap_adminpass"])) {
	$gBitSystem->storePreference("auth_ldap_adminpass", $_REQUEST["auth_ldap_adminpass"]);
	$gBitSmarty->assign('auth_ldap_adminpass', $_REQUEST["auth_ldap_adminpass"]);
	}
}
$gBitSmarty->assign("change_language", $gBitSystem->getPreference("change_language", "n"));
$gBitSmarty->assign("eponymousGroups", $gBitSystem->getPreference("eponymousGroups", 'n'));
$gBitSmarty->assign("allowRegister", $gBitSystem->getPreference("allowRegister", 'n'));
$gBitSmarty->assign("webserverauth", $gBitSystem->getPreference("webserverauth", 'n'));
$gBitSmarty->assign("useRegisterPasscode", $gBitSystem->getPreference("useRegisterPasscode", 'n'));
$gBitSmarty->assign("registerPasscode", $gBitSystem->getPreference("registerPasscode", ''));
$gBitSmarty->assign("min_pass_length", $gBitSystem->getPreference("min_pass_length", '1'));
$gBitSmarty->assign("pass_due", $gBitSystem->getPreference("pass_due", '999'));
$gBitSmarty->assign("validateUsers", $gBitSystem->getPreference("validateUsers", 'n'));
$gBitSmarty->assign("validateEmail", $gBitSystem->getPreference("validateEmail", 'n'));
$gBitSmarty->assign("rnd_num_reg", $gBitSystem->getPreference("rnd_num_reg", 'n'));
$gBitSmarty->assign("pass_chr_num", $gBitSystem->getPreference("pass_chr_num", 'n'));
$gBitSmarty->assign("feature_clear_passwords", $gBitSystem->getPreference("feature_clear_passwords", 'n'));
$gBitSmarty->assign("forgotPass", $gBitSystem->getPreference("forgotPass", 'n'));
$gBitSmarty->assign("https_login", $gBitSystem->getPreference("https_login", 'n'));
$gBitSmarty->assign("https_login_required", $gBitSystem->getPreference("https_login_required", 'n'));
$gBitSmarty->assign("http_domain", $gBitSystem->getPreference("http_domain", ''));
$gBitSmarty->assign("http_port", $gBitSystem->getPreference("http_port", '80'));
$gBitSmarty->assign("http_prefix", $gBitSystem->getPreference("http_prefix", BIT_ROOT_URL));
$gBitSmarty->assign("https_domain", $gBitSystem->getPreference("https_domain", ''));
$gBitSmarty->assign("https_port", $gBitSystem->getPreference("https_port", '443'));
$gBitSmarty->assign("https_prefix", $gBitSystem->getPreference("https_prefix", BIT_ROOT_URL));
$gBitSmarty->assign("remembertime", $gBitSystem->getPreference("remembertime", 2592000));
$gBitSmarty->assign("cookie_domain", $gBitSystem->getPreference("cookie_domain", $_SERVER['SERVER_NAME']));
$gBitSmarty->assign("cookie_path", $gBitSystem->getPreference("cookie_path", BIT_ROOT_URL));
$gBitSmarty->assign("auth_method", $gBitSystem->getPreference("auth_method", 'tiki'));
$gBitSmarty->assign("feature_ticketlib", $gBitSystem->getPreference("feature_ticketlib", 'n'));
$gBitSmarty->assign("auth_create_gBitDbUser", $gBitSystem->getPreference("auth_create_gBitDbUser", 'n'));
$gBitSmarty->assign("auth_create_user_auth", $gBitSystem->getPreference("auth_create_user_auth", 'n'));
$gBitSmarty->assign("auth_skip_admin", $gBitSystem->getPreference("auth_skip_admin", 'y'));
$gBitSmarty->assign("auth_ldap_host", $gBitSystem->getPreference("auth_ldap_host", 'localhost'));
$gBitSmarty->assign("auth_ldap_port", $gBitSystem->getPreference("auth_ldap_port", '389'));
$gBitSmarty->assign("auth_ldap_scope", $gBitSystem->getPreference("auth_ldap_scope", 'sub'));
$gBitSmarty->assign("auth_ldap_basedn", $gBitSystem->getPreference("auth_ldap_basedn", ''));
$gBitSmarty->assign("auth_ldap_userdn", $gBitSystem->getPreference("auth_ldap_userdn", ''));
$gBitSmarty->assign("auth_ldap_userattr", $gBitSystem->getPreference("auth_ldap_userattr", 'uid'));
$gBitSmarty->assign("auth_ldap_useroc", $gBitSystem->getPreference("auth_ldap_useroc", 'inetOrgPerson'));
$gBitSmarty->assign("auth_ldap_groupdn", $gBitSystem->getPreference("auth_ldap_groupdn", ''));
$gBitSmarty->assign("auth_ldap_groupattr", $gBitSystem->getPreference("auth_ldap_groupattr", 'cn'));
$gBitSmarty->assign("auth_ldap_groupoc", $gBitSystem->getPreference("auth_ldap_groupoc", 'groupOfUniqueNames'));
$gBitSmarty->assign("auth_ldap_ldap_memberattr", $gBitSystem->getPreference("auth_ldap_ldap_memberattr", 'uniqueMember'));
$gBitSmarty->assign("auth_ldap_memberisdn", $gBitSystem->getPreference("auth_ldap_memberisdn", 'y'));
$gBitSmarty->assign("auth_ldap_adminuser", $gBitSystem->getPreference("auth_ldap_adminuser", ''));
$gBitSmarty->assign("auth_ldap_adminpass", $gBitSystem->getPreference("auth_ldap_adminpass", ''));

?>
