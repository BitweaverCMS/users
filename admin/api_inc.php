<?php

global $gApi;

$gApi->registerRoute( USERS_PKG_DIR, 'bituser_api_handler' );

function bituser_api_handler( $pMethod, $pRequest ) {
	global $gApi, $gBitSystem, $gBitSmarty, $gBitUser;

	$routeAction = BitBase::getParameter( $pRequest, 'route_action' );

	$pStatus = HttpStatusCodes::HTTP_NOT_FOUND;
	$respData = "Unknown ".$routeAction." method: ".$pMethod;

	if( $routeAction == 'register' ) {
		if( $pMethod == 'POST' ) {
			$newUser = new BitUser();
			if( $newUser->register( $pRequest ) ) {
				$respStatus = HttpStatusCodes::HTTP_OK;
				$respData = $newUser->exportHash();
			} else {
				$respStatus = HTTP_CONFLICT;
				$respData = $newUser->mErrors;
			}
		}
	} else if( $routeAction == 'authenticate' ) {
		if( $pMethod == 'DELETE' ) {
			$gApi->verifyAuthorization();
			if( $gBitUser->isRegistered() ) {
				$gBitUser->logout();
			}
			$respStatus = HttpStatusCodes::HTTP_OK;
		} elseif( $pMethod == 'GET' || $pMethod == 'POST' ) {
			$gApi->verifyAuthorization();
			$gContent = &$gBitUser;
			$respStatus = HttpStatusCodes::HTTP_OK;
			$respData = $gBitUser->exportHash();
		}
	}
//bit_error_log( $respData, $respStatus );
	$gApi->outputJson( $respData, $respStatus );
}
