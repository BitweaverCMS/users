<?php
/**
 * A universal helper to bookmark any content object for a user
 * Currently only accessible through ajax/json
 * Those wishing for a non-js implementation feel free to modify
 *
 * @package users
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

$statusCode = 205;
$error = TRUE;
$msg = "";

if( $gBitUser->isRegistered() ){
	if( !empty( $_REQUEST['content_id'] ) && $gContent = LibertyBase::getLibertyObject( $_REQUEST['content_id'] ) ) {
		// verify user has access to view this content
		$gContent->load();
		if( $gContent->hasViewPermission() ){
			if( $gContent->hasService( CONTENT_SERVICE_USERS_FAVS ) ){
				// default action is to add the favorite
				$_REQUEST['action'] = empty( $_REQUEST['action'] )?'add':$_REQUEST['action']; 
				// add or remove 
				switch( $_REQUEST['action'] ){
					case 'add':
						$gBitUser->storeFavorite( $_REQUEST['content_id'] );
						$bookmarkState = 1;
						$msg = tra( 'This content has been added to your favorites' );
						break;
					case 'remove':
						$gBitUser->expungeFavorite( $_REQUEST['content_id'] );
						$bookmarkState = 0;
						$msg = tra( 'This content has been removed from your favorites' );
						break;
				}
				$gBitSmarty->assign( 'bookmarkState', $bookmarkState );
				$gBitSmarty->assign( 'contentId', $_REQUEST['content_id'] );
				$error = FALSE;
			}else{
				$statusCode = 401;
				$msg = tra( 'You can not bookmark this type of content, bookmarking denied' );
			}
		}else{
			$statusCode = 401;
			$msg = tra( 'You do not have permission to view this content, bookmarking denied' );
		}
	}else{
		$statusCode = 400;
		$msg = tra( 'No content was specified to bookmark' );
	}
}else{
	$msg = tra( 'You must be a registered user to bookmark content' );
}

$gBitSmarty->assign( 'statusCode', $statusCode );
$gBitSmarty->assign( 'error', $error );
$gBitSmarty->assign( 'msg', $msg );
$gBitThemes->setFormatHeader( 'json' );
$gBitSystem->display('bitpackage:users/edit_user_fav_json.tpl', null, array( 'format' => 'center_only', 'display_mode' => 'edit' ));
