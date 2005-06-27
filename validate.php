<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/validate.php,v 1.1.1.1.2.1 2005/06/27 17:48:00 lsces Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: validate.php,v 1.1.1.1.2.1 2005/06/27 17:48:00 lsces Exp $
 * @package users
 * @subpackage functions
 */
$bypass_siteclose_check = 'y';

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
global $gBitSystem;
/*
if (!isset($_REQUEST["login"])) {
  header("location: $HTTP_REFERER");
  die;
}
*/
/* SPIDERKILL - nuked this since it seems to go off at odd times
// Alert user if cookies are switched off
if (ini_get('session.use_cookies') == 1) {
vd( $_COOKIE );
	if(!isset($_COOKIE[BIT_SESSION_NAME])) {
		$url = KERNEL_PKG_URL.'error.php?error=' . urlencode(tra('You have to enable cookies to be able to login to this site'));
		header("location: $url");
		die;
	}
}
*/

//Remember where user is logging in from and send them back later; using session variable for those of us who use WebISO services
if( empty( $_SESSION['loginfrom'] ) ) {
	if( isset( $_SERVER['HTTP_REFERER'] ) && !strpos( $_SERVER['HTTP_REFERER'], 'login.php' )  && !strpos( $_SERVER['HTTP_REFERER'], 'register.php' ) ) {
		$from = (parse_url($_SERVER['HTTP_REFERER']));
		$_SESSION['loginfrom'] = $from['path'];
	}
}
if ($gBitUser->hasPermission( 'bit_p_admin' )) {
	if (isset($_REQUEST["su"])) {
		if ($gBitUser->userExists( array( 'login' => $_REQUEST['username'] ) ) ) {
			$_SESSION["$user_cookie_site"] = $_REQUEST["username"];
			$smarty->assign_by_ref('user', $_REQUEST["username"]);
		}
		$url = $_SESSION['loginfrom'];
		//unset session variable for the next su
		unset($_SESSION['loginfrom']);
		header("location: $url");
		die;
	}
}

$https_mode = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on';
$https_login_required = $gBitSystem->getPreference('https_login_required', 'n');
if ($https_login_required == 'y' && !$https_mode) {
	$url = 'https://' . $https_domain;
	if ($https_port != 443)
		$url .= ':' . $https_port;
	$url .= $https_prefix . $gBitSystem->getDefaultPage();
	if (SID)
		$url .= '?' . SID;
	header("Location " . $url);
	exit;
}

$user = isset($_REQUEST['user']) ? $_REQUEST['user'] : false;
$pass = isset($_REQUEST['pass']) ? $_REQUEST['pass'] : false;
$challenge = isset($_REQUEST['challenge']) ? $_REQUEST['challenge'] : false;
$response = isset($_REQUEST['response']) ? $_REQUEST['response'] : false;

$url = $gBitUser->login( $user, $pass, $challenge, $response );

// if $referer is set, we return the user to whence he came
if( !strpos( $url, 'login.php?' ) ) {
	if( isset( $_REQUEST['referer'] ) ) {
		$url = $_REQUEST['referer'];
	} elseif( !empty( $_SERVER['HTTP_REFERER'] ) ) { 
		$url = $_SERVER['HTTP_REFERER'];
	} else {
		$url = BIT_ROOT_URL;
	}
}

header('location: ' . $url);
exit;
?>
