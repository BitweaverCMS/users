<?php

	// Register the new user
	$userClass = $gBitSystem->getConfig( 'user_class', 'BitPermUser' );
	$newUser = new $userClass();

	if( $newUser->preRegisterVerify( $registerHash ) && $newUser->register( $registerHash ) ) {
		$gBitUser->mUserId = $newUser->mUserId;

		// add user to user-selected group
		if ( !empty( $_REQUEST['group'] ) ) {
			$groupInfo = $gBitUser->getGroupInfo( $_REQUEST['group'] );
			if ( empty($groupInfo) || $groupInfo['is_public'] != 'y' ) {
				$errors[] = "You can't use this group";
				$gBitSmarty->assignByRef( 'errors', $errors );
			} else {
				$userId = $newUser->getUserId();
				$gBitUser->addUserToGroup( $userId, $_REQUEST['group'] );
				$gBitUser->storeUserDefaultGroup( $userId, $_REQUEST['group'] );
			}
		}

		// set the user to private if necessary. defaults to public
		if(!empty($_REQUEST['users_information']) && $_REQUEST['users_information'] == 'private'){
			$newUser->storePreference('users_information','private');
		}

		// requires validation by email 
		if( $gBitSystem->isFeatureActive( 'users_validate_user' ) ) {
			$gBitSmarty->assign('msg',tra('You will receive an email with information to login for the first time into this site'));
			$gBitSmarty->assign('showmsg','y');
		} else {
			if( !empty( $_SESSION['loginfrom'] ) ) {
				unset( $_SESSION['loginfrom'] );
			}
			// registration login, fake the cookie so the session gets updated properly.
			if( empty($_COOKIE[$gBitUser->getSiteCookieName()] ) ) {
				$_COOKIE[$gBitUser->getSiteCookieName()] = session_id();
			}
			// login with email since login is not technically required in the form, as it can be auto generated during store
			$afterRegDefault = $newUser->login( $registerHash['email'], $registerHash['password'], FALSE, FALSE );
			$url = $gBitSystem->getConfig( 'after_reg_url' )?BIT_ROOT_URI.$gBitSystem->getConfig( 'after_reg_url' ):$afterRegDefault;
			// return to referring page
			if( !empty( $_SESSION['returnto'] ) ) {
				$url = $_SESSION['returnto'];
			// forward to group post-registration page 
			} elseif ( !empty( $_REQUEST['group'] ) && !empty( $groupInfo['after_registration_page'] ) ) {
				if ( $newUser->verifyId( $groupInfo['after_registration_page'] ) ) {
					$url = BIT_ROOT_URI."index.php?content_id=".$groupInfo['after_registration_page'];
				} elseif( strpos( $groupInfo['after_registration_page'], '/' ) === FALSE ) {
					$url = BitPage::getDisplayUrlFromHash( $groupInfo['after_registration_page'] );
				} else {
					$url = $groupInfo['after_registration_page'];
				}
			}
			header( 'Location: '.$url );
			exit;
		}
	} else {
		$gBitSystem->setHttpStatus( HttpStatusCodes::HTTP_BAD_REQUEST );
		$gBitSmarty->assignByRef( 'errors', $newUser->mErrors );
	}
