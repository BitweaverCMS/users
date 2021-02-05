<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * smarty_function_captcha
 */
function smarty_function_captcha( $pParams, &$gBitSmarty ) {
	global $gBitSystem, $gBitUser;

	if( $gBitSystem->isFeatureActive('users_register_recaptcha') ) {
		require_once USERS_PKG_INCLUDE_PATH.'recaptcha/autoload.php';
		if( $recapKey = $gBitSystem->getConfig( 'users_register_recaptcha_secret_key' ) ) {	
			$recaptcha = new \ReCaptcha\ReCaptcha( $recapKey );
		}
	}

	if( $gBitSystem->isFeatureActive('users_register_smcaptcha') ) {
		require_once( USERS_PKG_INCLUDE_PATH.'solvemedialib.php' );
		$gBitSmarty->assign( 'solveMediaHtml', solvemedia_get_html( $gBitSystem->getConfig( 'users_register_smcaptcha_c_key'), null, !empty( $_SERVER['HTTPS'] ) ) );
	}

	if( !empty( $pParams['force'] ) || empty( $_SESSION['captcha_verified'] ) && !$gBitUser->hasPermission( 'p_users_bypass_captcha' ) ) {
		$pParams['size'] = !empty( $pParams['size'] ) ? $pParams['size'] : '5';
		$pParams['variant'] = !empty( $pParams['variant'] ) ? $pParams['variant'] : 'condensed';
		if( !empty( $pParams['errors'] ) ) {
			$gBitSmarty->assign( 'errors', $pParams['errors'] );
		}
		if( $gBitSystem->isFeatureActive( 'liberty_use_captcha_freecap' ) ) {
			$pParams['source'] = USERS_PKG_URL."freecap/freecap.php";
		} else {
			$getString = 'size='.$pParams['size'];
			if( @BitBase::verifyId( $pParams['width'] ) ) {
				$getString .= '&width='.$pParams['width'];
			}
			if( @BitBase::verifyId( $pParams['height'] ) ) {
				$getString .= '&height='.$pParams['height'];
			}
			$pParams['source'] = USERS_PKG_URL."captcha_image.php?$getString";
		}
		$gBitSmarty->assign( 'params', $pParams );
		print $gBitSmarty->fetch( "bitpackage:users/captcha.tpl" );
	}
}
?>
