<?php
class BitAuth extends BaseAuth {

	function BitAuth() {
		parent::BaseAuth('bit');
	}

	function validate($user,$pass,$challenge,$response) {
		parent::validate($user,$pass,$challenge,$response);
		global $gBitSystem;
		global $gBitDb;
		$ret = SERVER_ERROR;
		if( empty( $user ) ) {
			$this->mErrors['login'] = 'User not found';
		} elseif( empty( $pass ) ) {
			$this->mErrors['login'] = 'Password incorrect';
		} else {
			$loginVal = strtoupper( $user ); // case insensitive login
			$loginCol = ' UPPER(`'.(strpos( $user, '@' ) ? 'email' : 'login').'`)';
			// first verify that the user exists
			$query = "select `email`, `login`, `user_id`, `user_password` from `".BIT_DB_PREFIX."users_users` where " . $gBitDb->convert_binary(). " $loginCol = ?";
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
					$query = "select `user_id`, `hash` from `".BIT_DB_PREFIX."users_users` where " . $gBitDb->convert_binary(). " $loginCol = ? and (`hash`=? or `hash`=?)";
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
						$ret=USER_VALID;
					} else {
						$ret=PASSWORD_INCORRECT;
						$this->mErrors[] = 'Password incorrect';
					}
				} else {
					// Use challenge-reponse method
					// Compare pass against md5(user,challenge,hash)
					$hash = $gBitDb->getOne("select `hash`  from `".BIT_DB_PREFIX."users_users` where " . $gBitDb->convert_binary(). " $loginCol = ?", array( $user ) );
					if (!isset($_SESSION["challenge"])) {
						$this->mErrors[] = 'Invalid challenge';
						$ret=PASSWORD_INCORRECT;
					}
					//print("pass: $pass user: $user hash: $hash <br/>");
					//print("challenge: ".$_SESSION["challenge"]." challenge: $challenge<br/>");
					//print("response : $response<br/>");
					if ($response == md5( strtolower($user) . $hash . $_SESSION["challenge"]) ) {
						$ret = USER_VALID;
						$this->update_lastlogin( $userId );
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
		return true;
	}

	function isSupported() {
		return true;
	}

	function createUser(&$userattr) {
		//$authUserInfo = array( 'login' => $instance->mInfo['login'], 'password' => $instance->mInfo['password'], 'real_name' => $instance->mInfo['real_name'], 'email' => $instance->mInfo['email'] );
		if (empty($userattr["email"])) {
			$userattr["email"] = $userattr["login"];
		}
		$u = new BitUser();
		$res = $u->store( $userattr );
		$this->mErrors = array_merge($this->mErrors,$u->mErrors);
		return $res;
	}
}