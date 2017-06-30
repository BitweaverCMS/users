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
require_once( dirname( __FILE__ ).'/../../kernel/setup_inc.php' );

global $gBitSystem;

//Remember where user is logging in from and send them back later; using session variable for those of us who use WebISO services
//do not use session loginfrom with login.php or register.php - only "inline" login forms display in perm denied fatals, etc.
if( !empty( $_REQUEST['returnto'] ) ) {
	$url = $_REQUEST['returnto'];
} elseif( !empty( $_SESSION['returnto'] ) ) {
	$url = $_SESSION['returnto'];
}

if( $_REQUEST['provider'] ) {
	try {
		require_once( USERS_PKG_PATH.'classes/BitHybridAuthManager.php' );
		BitHybridAuthManager::loadSingleton();
		global $gBitHybridAuthManager;

		$gBitHybridAuthManager->authenticate( $_REQUEST['provider'], $gBitUser );

	} catch( Exception $e ) {
		vd( $e );
		// Display the recived error,
		// to know more please refer to Exceptions handling section on the userguide
		switch( $e->getCode() ){
			case 0 : echo "Unspecified error."; break;
			case 1 : echo "Hybriauth configuration error."; break;
			case 2 : echo "Provider not properly configured."; break;
			case 3 : echo "Unknown or disabled provider."; break;
			case 4 : echo "Missing provider application credentials."; break;
			case 5 : echo "Authentification failed. The user has canceled the authentication or the provider refused the connection.";
							 break;
			case 6 : echo "User profile request failed. Most likely the user is not connected to the provider and he should authenticate again.";
				$authProfile->logout();
				break;
			case 7 : echo "User not connected to the provider.";
				$authProfile->logout();
				break;
			case 8 : echo "Provider does not support this feature."; break;
		}
 
		// well, basically your should not display this to the end user, just give him a hint and move on..
		echo "<br /><br /><b>Original error message:</b> " . $e->getMessage();
	}

}

// but if we came from a login page, let's go home (except if we got an error when login in)
if( empty( $url ) ) {
	$url = BitBase::getParameter( $_SERVER, 'HTTP_REFERER', $gBitSystem->getDefaultPage() );
}

bit_redirect( $url );
