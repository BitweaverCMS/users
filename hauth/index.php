<?php
/**
* HybridAuth
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2015, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
*/

// ------------------------------------------------------------------------
//	HybridAuth End Point
// ------------------------------------------------------------------------
require_once( '../../kernel/includes/setup_inc.php' );

$providerDone = isset( $_REQUEST['hauth_done'] ) ? $_REQUEST['hauth_done'] : '';
if( $providerDone === '' ) {
	bit_redirect( USERS_PKG_URI.'signin.php' );
}
try {
	require_once( USERS_PKG_CLASS_PATH.'BitHybridAuthManager.php' );
	BitHybridAuthManager::loadSingleton();
	global $gBitHybridAuthManager;
	if( !$gBitHybridAuthManager->isProviderEnabled( $providerDone ) ) {
		$_SESSION['users_ha_error'] = tra( 'Sign-in is not available.' );
	} else {
		$gBitHybridAuthManager->authenticateProvider( $providerDone );
	}
	bit_redirect( USERS_PKG_URI.'validate?provider='.rawurlencode( $providerDone ) );
} catch( Exception $e ) {
	$haMessage = $e->getMessage();
	$haQuiet = ( stripos( $haMessage, 'access_denied' ) !== FALSE )
		|| ( stripos( $haMessage, 'already been consumed' ) !== FALSE )
		|| ( stripos( $haMessage, 'authorization state' ) !== FALSE );
	if( !$haQuiet ) {
		bit_error_log( 'Sign-in failed for '.$providerDone.': '.$haMessage );
	}
	$_SESSION['users_ha_error'] = tra( 'Sign-in failed.' );
	bit_redirect( USERS_PKG_URI.'validate?provider='.rawurlencode( $providerDone ) );
}
