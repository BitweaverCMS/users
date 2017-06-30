<?php
/**
 * validate user login
 *
 * @copyright (c) 2004-17 bitweaver.org
 *
 * @package users
 * @subpackage functions
 */

/**
 * this is a dirty hack to allow admins to log in when we require a visit to the installer
 * used in kernel/setup_inc.php - xing - Friday Oct 03, 2008   16:44:48 CEST
 */
define( 'LOGIN_VALIDATE', TRUE );

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

global $gBitSystem;

$redirectUrl = FALSE;

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

if( !empty( $_REQUEST['provider'] ) ) {
	require_once( USERS_PKG_PATH.'classes/BitHybridAuthManager.php' );
	BitHybridAuthManager::loadSingleton();
	global $gBitHybridAuthManager;

	if( !empty( $_REQUEST['disconnect'] ) ) {
		if( $gBitUser->isRegistered() ) {
			$gBitHybridAuthManager->expungeUserProfile( $gBitUser->mUserId, $_REQUEST['provider'] );
		}
		bit_redirect( $_SESSION['loginfrom'] );
	} else {
		try {
			$auth = $gBitHybridAuthManager->authenticate( $_REQUEST['provider'], $gBitUser );
			if( $auth === FALSE ) {
				// social auth failed
vd( 'social auth failed' );die;
			} elseif( $auth === TRUE ) {
				// account was connected to current object
vd( 'account was connected to current object' );die;
			} elseif( BitBase::verifyId( $auth ) ) {
				$redirectUrl = $gBitUser->getPostLoginUrl();
			} elseif( is_object( $auth ) && is_a( $auth, 'Hybrid_User_Profile' ) ) {
				// an unconnected authProfile was found
				$gBitSmarty->assign_by_ref( 'authProfile', $auth );
				$tpl = 'bitpackage:users/validate_auth.tpl';
				if( !empty( $_REQUEST['auth_login'] ) ) {
					$user = isset($_REQUEST['user']) ? $_REQUEST['user'] : false;
					$pass = isset($_REQUEST['pass']) ? $_REQUEST['pass'] : false;
					$challenge = isset($_REQUEST['challenge']) ? $_REQUEST['challenge'] : false;
					$response = isset($_REQUEST['response']) ? $_REQUEST['response'] : false;

					// if $referer is set, login() will return the user to whence he came
					$gBitUser->login( $user, $pass, $challenge, $response );
					if( $gBitUser->isRegistered() ) {
						$gBitHybridAuthManager->storeUserProfile( $gBitUser->mUserId, $_REQUEST['provider'], $auth->identifier, $auth );
						$redirectUrl = $gBitUser->getPostLoginUrl();
						$tpl = NULL;
					} else {
						$redirectUrl = NULL;
					}
				} else {
					if( $gBitUser->isRegistered() ) {
						$redirectUrl = $gBitUser->getPostLoginUrl();
						$tpl = NULL;
					} elseif( !empty( $_REQUEST['auth_new'] ) && !$gBitUser->isRegistered() ) {
						$registerHash = array( 'password'=>BitUser::genPass(20), 'novalidation'=>TRUE );
						foreach( array( 'displayName' => 'real_name', 'email'=>'email', 'emailVerified'=>'verified_email', 'gender'=>'customers_gender', 'firstName'=>'customers_firstname', 'lastName'=>'customers_lastname', 'phone'=>'customers_telephone' ) as $member=>$key ) {
							if( $auth->$member ) {
								$registerHash[$key] = $auth->$member;
							}
						}
						if( $auth->birthMonth && $auth->birthDay ) {
							$registerHash['customers_dob'] = ($auth->birthYear ? $auth->birthYear : 1900).'-'.$auth->birthMonth.'-'.$auth->birthDay;
						}

						$prefId = $gBitHybridAuthManager->getConfigName( $_REQUEST['provider'], 'id' );

						include( USERS_PKG_PATH.'register_inc.php' );
					}
				}
			}
		} catch( Exception $e ) {
			// Display the recived error,
			// to know more please refer to Exceptions handling section on the userguide
			switch( $e->getCode() ){
				case 0 : $authError = 'Unspecified error.';
					break;
				case 1 : $authError = 'Hybriauth configuration error.';
					break;
				case 2 : $authError = 'Provider not properly configured.';
					break;
				case 3 : $authError = 'Unknown or disabled provider.';
					break;
				case 4 : $authError = 'Missing provider application credentials.';
					break;
				case 5 : $authError = 'Authentification failed. The user has canceled the authentication or the provider refused the connection.';
					break;
				case 6 : $authError = 'User profile request failed. Most likely the user is not connected to the provider and he should authenticate again.';
					break;
				case 7 : $authError = 'User not connected to the provider.';
					break;
				case 8 : $authError = 'Provider does not support this feature.';
					break;
			}
	 
			$gBitSmarty->assign_by_ref( 'authError', $authError );
			$gBitSmarty->assign_by_ref( 'authExpection', $e );
				$tpl = 'bitpackage:users/validate_auth.tpl';
		}
	}
} else {

	$user = isset($_REQUEST['user']) ? $_REQUEST['user'] : false;
	$pass = isset($_REQUEST['pass']) ? $_REQUEST['pass'] : false;
	$challenge = isset($_REQUEST['challenge']) ? $_REQUEST['challenge'] : false;
	$response = isset($_REQUEST['response']) ? $_REQUEST['response'] : false;

	// if $referer is set, login() will return the user to whence he came
	$redirectUrl = $gBitUser->login( $user, $pass, $challenge, $response );
}

if( !empty( $tpl ) ) {
	$gBitSystem->display( $tpl );
} elseif(( strpos( $redirectUrl, 'login.php?' ) || strpos( $redirectUrl, 'remind_password.php' )) && strpos( $redirectUrl, 'login.php?error=' ) == -1 ) {
// but if we came from a login page, let's go home (except if we got an error when login in)
	$redirectUrl = $gBitUser->getPostLoginUrl();
} else {
	echo "no where to go";
}

if( !empty( $redirectUrl ) ) {
	bit_redirect( $redirectUrl );
}

