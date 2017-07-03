<?php
/**
 * @version $Header$
 * @package users
 * @subpackage functions
 */

/**
 * users_admin_email_user 
 * 
 * @param array $pParamHash 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function users_admin_email_user( &$pParamHash ) {
	global $gBitSmarty, $gBitSystem;

	$ret = FALSE;
	$siteName = $gBitSystem->getConfig('site_title', $_SERVER['HTTP_HOST'] );
	$gBitSmarty->assign( 'siteName', $_SERVER["SERVER_NAME"] );
	$gBitSmarty->assign( 'mail_site', $_SERVER["SERVER_NAME"] );
	$gBitSmarty->assign( 'mail_user', $pParamHash['login'] );
	if( !empty( $_REQUEST['admin_verify_user'] ) && !empty( $pParamHash['user_store']['provpass'] )) {
		$apass = addslashes( substr( md5( $gBitSystem->genPass() ), 0, 25 ));
		$apass = $pParamHash['user_store']['provpass'];
		$machine = httpPrefix().USERS_PKG_URL.'confirm.php';
		// Send the mail
		$gBitSmarty->assign( 'mail_machine', $machine );
		$gBitSmarty->assign( 'mailUserId', $pParamHash['user_store']['user_id'] );
		$gBitSmarty->assign( 'mailProvPass', $apass );
		$mail_data = $gBitSmarty->fetch( 'bitpackage:users/admin_validation_mail.tpl' );
		mail( $pParamHash['email'], $siteName.' - '.tra( 'Your registration information' ),$mail_data,"From: ".$gBitSystem->getConfig( 'site_sender_email' )."\r\nContent-type: text/plain;charset=utf-8\r\n" );
		$gBitSmarty->assign( 'showmsg', 'n' );

		$ret = array(
			'confirm' => 'Validation email sent to '.$pParamHash['email'].'.'
		);
	} elseif( !empty( $pParamHash['password'] )) {
		// Send the welcome mail
		$gBitSmarty->assign( 'mailPassword',$pParamHash['password'] );
		$gBitSmarty->assign( 'mailEmail',$pParamHash['email'] );
		$mail_data = $gBitSmarty->fetch( 'bitpackage:users/admin_welcome_mail.tpl' );
		mail( $pParamHash["email"], tra( 'Welcome to' ).' '.$siteName,$mail_data,"From: ".$gBitSystem->getConfig('site_sender_email')."\r\nContent-type: text/plain;charset=utf-8\r\n" );
		$ret = array(
			'welcome' => 'Welcome email sent to '.$pParamHash['email'].'.'
		);
	}
	return $ret;
}

/**
 * scramble_email 
 * 
 * @param array $email 
 * @param string $method 
 * @access public
 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
 */
function scramble_email( $email, $method = 'unicode' ) {
	switch( $method ) {
		case 'strtr':
			$trans = array(	"@" => tra(" AT "),
			"." => tra(" DOT ")
			);
			$ret = strtr($email, $trans);
			break;

		case 'x' :
			$encoded = $email;
			for ($i = strpos($email, "@") + 1; $i < strlen($email); $i++) {
				if ($encoded[$i]  != ".") $encoded[$i] = 'x';
			}
			$ret = $encoded;
			break;

		// for legacy code
		case 'y':
		case 'unicode':
			$encoded = '';
			for( $i = 0; $i < strlen( $email ); $i++) {
				$encoded .= '&#' . ord( $email[$i] ). ';';
			}
			$ret = $encoded;
			break;

		default:
			$ret = NULL;
			break;
	}
	return $ret;
}


function users_httpauth(){
	global $gBitSystem, $gBitUser;
	// require ssl
	$https_mode = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off';
	// no https redirect
	if( !$https_mode ){
		$url = $gBitSystem->getConfig( 'site_https_domain' );
		$site_https_port = $gBitSystem->getConfig('site_https_port', 443);
		if ($site_https_port != 443)
			$url .= ':' . $site_https_port;
		$url .= $gBitSystem->getConfig( 'site_https_prefix' ) . $_SERVER['REQUEST_URI'];
		if (SID)
			$url .= (!empty( $_SERVER['QUERY_STRING'] )?'&':'?') . SID;
		$url = preg_replace('/\/+/', '/', $url);
		header("Location: https://$url");
		exit;
	}

	$user = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : false;
	$pass = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : false;
	$challenge = false;
	$response = false;
	// verify the user is valid first
	if( $gBitUser->validate( $user, $pass, $challenge, $response ) ){
		// log in user - returns a url so can't use it for validation check
		$gBitUser->login( $user, $pass, $challenge, $response );
		return( TRUE );
	}
	// require http auth
	else{
		header('WWW-Authenticate: Basic realm="Test"');
		header('HTTP/1.0 401 Unauthorized');
		$gBitSystem->fatalError( tra('HTTP Authentication Canceled') );
		exit;
	}
}
