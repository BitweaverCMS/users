<?php

global $gApi;

$gApi->registerRoute( USERS_PKG_DIR, 'bituser_api_handler' );

function bituser_api_handler( $pMethod, $pRequest ) {
	global $gApi, $gBitSystem, $gBitSmarty, $gBitUser;

	switch( $pMethod ) {
		case 'POST':
//		case 'PUT':
			$newUser = new BitUser();
			if( $newUser->register( $pRequest ) ) {
				$gApi->outputJson( $newUser->exportHash(), 200 );
			} else {
				$gApi->outputJson( $newUser->mErrors, HttpStatusCodes::HTTP_CONFLICT );
			}
			break;

		case 'GET':
			$gApi->verifyAuthorization();
			$gContent = &$gBitUser;
			$gContent->verifyViewPermission();
			$gApi->outputJson( $gContent->exportHash(), HttpStatusCodes::HTTP_OK );
			break;
		
		case 'DELETE':
			$gApi->verifyAuthorization();
			if( $gContent->hasAdminPermission() ) {
//	if( is_a( $gContent, 'BitUser' ) && $gContent->isValid() ) {
				$gContent->verifyExpungePermission();
//  }
			}
			break;
	}

	$gBitSystem->display( 'bitpackage:api/api_json_output.tpl' );
}
