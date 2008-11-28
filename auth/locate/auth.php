<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/auth/locate/auth.php,v 1.1 2008/11/28 08:03:20 lsces Exp $
 *
 * @package users
 */

/**
 * Class that manages the bitweaver autentication method with additional modifications 
 * for access managed via machine name
 * This is used to idenitfy counter locations in sites where location related activity takes place
 * such as anouncment and direction displays
 *
 * @package users
 * @subpackage auth
 */
class LocateAuth extends BaseAuth {

	function LocateAuth() {
		parent::BaseAuth('locate');
	}

	function validate($user,$pass,$challenge,$response) {
		parent::validate($user,$pass,$challenge,$response);
		global $gBitSystem;
		global $gBitDb;
		global $gMultisites;

		$ret = SERVER_ERROR;
		if( empty( $user ) ) {
			$this->mErrors['login'] = 'User not found';
		} elseif( empty( $pass ) ) {
			$this->mErrors['login'] = 'Password incorrect';
		} else {
			$loginVal = strtoupper( $user ); // case insensitive login
			$loginCol = ' UPPER(`'.(strpos( $user, '@' ) ? 'email' : 'login').'`)';
			// first verify that the user exists
			$query = "select `email`, `login`, `user_id`, `user_password` from `".BIT_DB_PREFIX."users_users` where " . $gBitDb->convertBinary(). " $loginCol = ?";
			$result = $gBitDb->query( $query, array( $loginVal ) );
			if( !$result->numRows() ) {
				$this->mErrors['login'] = 'User not found';
			} else {
				$res = $result->fetchRow();
				$userId = $res['user_id'];
				$user = $res['login'];
				// TikiWiki 1.8+ uses this bizarro conglomeration of fields to get the hash. this sucks for many reasons
				$hash = md5( strtolower($user) . $pass . $res['email']);
				$hash2 = md5($pass);
				// next verify the password with 2 hashes methods, the old one (pass)) and the new one (login.pass;email)
				// TODO - this needs cleaning up - wolff_borg
				if( !$gBitSystem->isFeatureActive( 'feature_challenge' ) || empty($response) ) {
					$query = "select `user_id`, `content_id`, `hash` from `".BIT_DB_PREFIX."users_users` where " . $gBitDb->convertBinary(). " $loginCol = ? and (`hash`=? or `hash`=?)";
					if ( $row = $gBitDb->getRow( $query, array( $loginVal, $hash, $hash2 ) ) ) {
						// auto-update old hashes with simple and standard md5( password )
						$hashUpdate = '';
						if( $row['hash'] == $hash ) {
							$hashUpdate = 'hash=?, ';
							$bindVars[] = $hash2;
						}
						$bindVars[] = $gBitSystem->getUTCTime();
						$bindVars[] = $userId;
						$query = "update `".BIT_DB_PREFIX."users_users` set  $hashUpdate `last_login`=`current_login`, `current_login`=? where `user_id`=?";
						$result = $gBitDb->query($query, $bindVars );
// Modify this to check machine name against managed locations
//						$query = "select `multisite_id` from `".BIT_DB_PREFIX."multisite_content` where `content_id` = ?";
//						$sites = $gBitDb->getAll($query, array( $row['content_id'] ) );
//						if ( !$sites ) {
							$ret=USER_VALID;
/*						} else {
							// This will allow for additional by site checking in future
							// Currently only a single site per user_id is allowed
							$ret=PASSWORD_INCORRECT;
							foreach ( $sites as $id ) {
								if ( $id['multisite_id'] == $gMultisites->mMultisiteId ) {
									$ret=USER_VALID;
								}
							}
							if ( $ret == PASSWORD_INCORRECT ) {
								$this->mErrors[] = 'You are not authorized on this area of the site';
							}
						}
*/
					} else {
						$ret=PASSWORD_INCORRECT;
						$this->mErrors[] = 'Password incorrect';
					}
				} else {
					// Use challenge-reponse method
					// Compare pass against md5(user,challenge,hash)
					$hash = $gBitDb->getOne("select `hash`  from `".BIT_DB_PREFIX."users_users` where " . $gBitDb->convertBinary(). " $loginCol = ?", array( $user ) );
					if (!isset($_SESSION["challenge"])) {
						$this->mErrors[] = 'Invalid challenge';
						$ret=PASSWORD_INCORRECT;
					}
					//print("pass: $pass user: $user hash: $hash <br/>");
					//print("challenge: ".$_SESSION["challenge"]." challenge: $challenge<br/>");
					//print("response : $response<br/>");
					if ($response == md5( strtolower($user) . $hash . $_SESSION["challenge"]) ) {
						$ret = USER_VALID;
						$this->updateLastLogin( $userId );
					} else {
						$this->mErrors[] = 'Invalid challenge';
						$ret=PASSWORD_INCORRECT;
					}
				}
			}
			if (!empty($userId)) {
				$this->mInfo['user_id']=$userId;
			}
		}
		return( $ret );
	}

	function canManageAuth() {
		global $gBitSystem;
		if( $gBitSystem->isPackageActive( 'citizen' ) ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function isSupported() {
		global $gBitSystem;
		if( $gBitSystem->isPackageActive( 'citizen' ) ) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function createUser( &$pUserHash ) {
		//$authUserInfo = array( 'login' => $instance->mInfo['login'], 'password' => $instance->mInfo['password'], 'real_name' => $instance->mInfo['real_name'], 'email' => $instance->mInfo['email'] );
		$u = new BitPermUser();

		if( !$u->store( $pUserHash ) ) {
			$this->mErrors = array_merge($this->mErrors,$u->mErrors);
		}
		return $u->mUserId;
	}
}
