<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/validate.php,v 1.24 2009/10/01 13:45:52 wjames5 Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
 *
 * $Id: validate.php,v 1.24 2009/10/01 13:45:52 wjames5 Exp $
 * @package users
 * @subpackage functions
 */
$bypass_siteclose_check = 'y';

/**
 * this is a dirty hack to allow admins to log in when we require a visit to the installer
 * used in kernel/setup_inc.php - xing - Friday Oct 03, 2008   16:44:48 CEST
 */
define( 'LOGIN_VALIDATE', TRUE );

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

global $gBitSystem;

//Remember where user is logging in from and send them back later; using session variable for those of us who use WebISO services
//do not use session loginfrom with login.php or register.php - only "inline" login forms display in perm denied fatals, etc.
if( !empty( $_SESSION['returnto'] ) ) {
	// we have been explicitly told where we want to return
	$_SESSION['loginfrom'] = $_SESSION['returnto'];
} elseif( isset( $_SERVER['HTTP_REFERER'] ) && strpos( $_SERVER['HTTP_REFERER'], 'login.php' ) === FALSE && strpos( $_SERVER['HTTP_REFERER'], 'register.php' ) === FALSE ) {
	$from = parse_url( $_SERVER['HTTP_REFERER'] );
	$_SESSION['loginfrom'] = (!empty($from['path']) ? $from['path'] : '').( !empty( $from['query'] ) ? '?'.$from['query'] : '' );
} elseif( !empty( $_SESSION['loginfrom'] ) ) {
	unset( $_SESSION['loginfrom'] );
}

// Added check for IIS $_SERVER['HTTPS'] uses 'off' value - wolff_borg
$https_mode = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
if ($gBitSystem->isFeatureActive( 'site_https_login_required' ) && !$https_mode) {
	$url = $gBitSystem->getConfig( 'site_https_domain' );
	$site_https_port = $gBitSystem->getConfig('site_https_port', $site_https_port);
	if ($site_https_port != 443)
		$url .= ':' . $site_https_port;
	$url .= $gBitSystem->getConfig( 'site_https_prefix' ) . $gBitSystem->getDefaultPage();
	if (SID)
		$url .= '?' . SID;
	$url = preg_replace('/\/+/', '/', $url);
	header("Location: https://$url");
	exit;
}

$user = isset($_REQUEST['user']) ? $_REQUEST['user'] : false;
$pass = isset($_REQUEST['pass']) ? $_REQUEST['pass'] : false;
$challenge = isset($_REQUEST['challenge']) ? $_REQUEST['challenge'] : false;
$response = isset($_REQUEST['response']) ? $_REQUEST['response'] : false;

// if $referer is set, login() will return the user to whence he came
$url = $gBitUser->login( $user, $pass, $challenge, $response );
if (!preg_match('/^\w+:\/{2}/', $url)) {
	$url = httpPrefix() . $url;
}

// but if we came from a login page, let's go home (except if we got an error when login in)
if(( strpos( $url, 'login.php?' ) || strpos( $url, 'remind_password.php' )) && strpos( $url, 'login.php?error=' ) == -1 ) {
	$url = $gBitSystem->getDefaultPage();
}

bit_redirect( $url );
exit;
?>
