<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/validate.php,v 1.1.1.1.2.8 2005/10/10 18:52:24 jht001 Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: validate.php,v 1.1.1.1.2.8 2005/10/10 18:52:24 jht001 Exp $
 * @package users
 * @subpackage functions
 */
$bypass_siteclose_check = 'y';

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
global $gBitSystem;

//Remember where user is logging in from and send them back later; using session variable for those of us who use WebISO services
if( empty( $_SESSION['loginfrom'] ) ) {
	if( isset( $_SERVER['HTTP_REFERER'] ) && !strpos( $_SERVER['HTTP_REFERER'], 'login.php' )  && !strpos( $_SERVER['HTTP_REFERER'], 'register.php' ) ) {
		$from = (parse_url($_SERVER['HTTP_REFERER']));
		$_SESSION['loginfrom'] = $from['path'];
	}
}
/* This appears obsoleted by code in users/admin/index.php (assume_user) - wolff_borg
if ($gBitUser->isAdmin()) {
	if (isset($_REQUEST["su"])) {
		if ($gBitUser->userExists( array( 'login' => $_REQUEST['username'] ) ) ) {
			$_SESSION["$user_cookie_site"] = $_REQUEST["username"];
			$gBitSmarty->assign_by_ref('user', $_REQUEST["username"]);
		}
		$url = $_SESSION['loginfrom'];
		//unset session variable for the next su
		unset($_SESSION['loginfrom']);
		echo("location: $url");
		//header("location: $url");
		die;
	}
}
*/

$https_mode = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on';
$https_login_required = $gBitSystem->getPreference('https_login_required', 'n');
if ($gBitSystem->isFeatureActive( 'https_login_required' ) && !$https_mode) {
	$url = 'https://' . $https_domain;
	if ($https_port != 443)
		$url .= ':' . $https_port;
	$url .= $https_prefix . $gBitSystem->getDefaultPage();
	if (SID)
		$url .= '?' . SID;
	header("Location: " . $url);
	exit;
}

$user = isset($_REQUEST['user']) ? $_REQUEST['user'] : false;
$pass = isset($_REQUEST['pass']) ? $_REQUEST['pass'] : false;
$challenge = isset($_REQUEST['challenge']) ? $_REQUEST['challenge'] : false;
$response = isset($_REQUEST['response']) ? $_REQUEST['response'] : false;

// if $referer is set, login() will return the user to whence he came
$url = httpPrefix() .  $gBitUser->login( $user, $pass, $challenge, $response );
// but if we came from a login page, let's go home (except if we got an error when login in)
if( (strpos( $url, 'login.php?' ) || strpos( $url, 'remind_password.php' )) && strpos( $url, 'login.php?error=') == -1) {
	$url = $gBitSystem->getDefaultPage();
}
header('location: ' . $url);
exit;
?>
