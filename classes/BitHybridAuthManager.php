<?php
/**
 * Class that manages the base autentication method
 *
 * @package users
 * @subpackage auth
 * Copyright (c) 2017 bitweaver.org
 */

require_once( USERS_PKG_PATH.'hauth/Hybrid/Auth.php' );

require_once( EXTERNAL_LIBS_PATH . 'facebook/src/Facebook/autoload.php' );

class BitHybridAuthManager extends BitSingleton {

	private $mEnabledProviders = array();
	
	/**
	 * Constructor
	 * Loads user configuration and strategies.
	 */
	public function __construct(){
		parent::__construct();
		/**
		 * Configurable settings
		 */
		$this->getEnabledProviders();
	}

	public static function isCacheableClass() {
		return true;
	}

	public function __sleep() {
		return array_merge( parent::__sleep(), array( 'mEnabledProviders' ) );
	}

	/**
	* Returns:
	* - FALSE: Authentication Failed
	* - TRUE: Authentication was connected to pUser
	* - INT: users_users.user_id of auth'ed profile
	* - Object: Hybrid_User_Profile of validated profile, but no local user_id was found to match the profile
	**/
	public function authenticate( $pProvider, &$pUser ) {
		$ret = FALSE;
		if( $this->isProviderEnabled( $pProvider ) ) {
			$hybridAuth = $this->getHybridAuth();
			$authedProvider = $hybridAuth->authenticate( $pProvider );
			if( $authProfile = $authedProvider->getUserProfile() ) {
				$ret = $authProfile;
				$this->cacheUserProfile( $pProvider, $authProfile );
				if( ($userId = $this->mDb->getOne( "SELECT `user_id` FROM `".BIT_DB_PREFIX."users_auth_map` uam WHERE uam.`provider`=? AND uam.`provider_identifier`=?", array( $pProvider, $authProfile->identifier ) )) > ROOT_USER_ID ) {
				} elseif( $authProfile->emailVerified && ($userId = $this->mDb->getOne( "SELECT uu.`user_id` FROM `".BIT_DB_PREFIX."users_users` uu WHERE uu.`email`=?", array( $authProfile->emailVerified ) )) > ROOT_USER_ID ) {
				} else {
					$ret = $authProfile;
				}
				if( !empty( $userId ) ) {
					$pUser->mUserId = $userId;
					if( $pUser->load() ) {
						$pUser->loadPermissions( TRUE );
						$pUser->setUserSession();
						$this->mDb->query( "UPDATE `".BIT_DB_PREFIX."users_auth_map` SET `last_login`=? WHERE `user_id`=? AND `provider`=?", array( time(), $pUser->mUserId, $pProvider ) );
						$pUser->clearFromCache();
						$ret = $userId;
					}
				}
				if( $pUser->isRegistered() ) {
					$this->storeUserProfile( $pUser->mUserId, $pProvider, $authProfile->identifier, $authProfile );
				}
			}
		}
		return $ret;
	}

	public function expungeUserProfile( $pUserId, $pProvider ) {
		global $gBitSystem;
		if( $authProfile = $this->getUserProfile( $pProvider, $pUserId ) ) {
			if( $gBitSystem::isCacheActive() ) {
				$cacheKey = $this->getProfileCacheKey( $pProvider, $authProfile->identifier );
				apc_delete( $cacheKey );
			}
			$query = "DELETE FROM `".BIT_DB_PREFIX."users_auth_map` WHERE `user_id`=? AND `provider`=?";
			$result = $this->mDb->query( $query, array( $pUserId, $pProvider ) );
		}
	}

	public function storeUserProfile( $pUserId, $pProvider, $pIdentifier, $pAuthProfile ) {
		if( BitBase::verifyId( $pUserId ) && !empty( $pProvider ) && !empty( $pIdentifier ) ) {
			$this->StartTrans();
			$query    = "DELETE FROM `".BIT_DB_PREFIX."users_auth_map` WHERE `user_id`=? AND `provider`=?";
			$result   = $this->mDb->query( $query, array( $pUserId, $pProvider ) );
			if( !is_null( $pIdentifier ) ) {
				$profileHash = get_object_vars( $pAuthProfile );
				ksort( $profileHash );
				$query      = "INSERT INTO `".BIT_DB_PREFIX."users_auth_map` (`user_id`,`provider`,`provider_identifier`,`last_login`,`profile_json`) VALUES(?, ?, ?, ?, ?)";
				$result     = $this->mDb->query( $query, array( $pUserId, $pProvider, $pIdentifier, time(), json_encode( $profileHash ) ) );
			}
			$this->CompleteTrans();
		}
	}

	private function getProfileCacheKey( $pProvider, $pId ) {
		return 'users_ha_'.strtolower( $pProvider ).'_'.$pId;
	}

	private function cacheUserProfile( $pProvider, $pProfile ) {
		$ret = FALSE;
		global $gBitSystem;
		if( $gBitSystem::isCacheActive() ) {
			$cacheKey = $this->getProfileCacheKey( $pProvider, $pProfile->identifier );
			apc_store( $cacheKey, $pProfile );
		}
		return $ret;
	}

	public function getUserProfile( $pProvider, $pUserId=NULL ) {
		try {
			if( empty( $pUserId ) ) {
				global $gBitUser;
				$pUserId = $gBitUser->mUserId;
			}
			if( $profileId = $this->mDb->getOne( "SELECT `provider_identifier` FROM `".BIT_DB_PREFIX."users_auth_map` WHERE `user_id`=? AND `provider`=?", array( $pUserId, $pProvider ) ) ) {
				$cacheKey = $this->getProfileCacheKey( $pProvider, $profileId );
				global $gBitSystem;
				if( $gBitSystem::isCacheActive() ) {
					$hybridAuth = $this->getHybridAuth();
					$hybridAuth->setup( $pProvider );
					if( $ret = apc_fetch( $cacheKey ) ) {
						return $ret;
					}
				}

				$hybridAuth = $this->getHybridAuth();
				$authedProvider = $hybridAuth->authenticate( $pProvider );
				if( $ret = $authedProvider->getUserProfile() ) {
					$this->cacheUserProfile( $pProvider, $ret );
					return $ret;
				}
			}
		} catch( Exception $e ) {
		}
	}

	public function getHybridAuth() {
		$config = array(
			// "base_url" the url that point to HybridAuth Endpoint (where the index.php and config.php are found)
			"base_url" => USERS_PKG_URI.'hauth/',
			"debug_mode" => TRUE,
			"debug_file" => sys_get_temp_dir().'/hybridauth_log',
		);

		foreach( $this->mEnabledProviders as $providerKey => $providerHash ) {
			$config['providers'][$providerHash['provider']] = array ( "enabled" => true );
			foreach( array_keys( $providerHash['keys'] ) as $configKey ) {
				$config['providers'][$providerHash['provider']]['keys'][$configKey] = $this->getProviderConfig( $providerKey, $configKey );
			}
			if( !empty( $providerHash['options'] ) ) {
				foreach( array_keys( $providerHash['options'] ) as $optionKey ) {
					$config['providers'][$providerHash['provider']][$optionKey] = $this->getProviderConfig( $providerKey, $optionKey );
				}
			}
		}
		return new Hybrid_Auth( $config );
	}

	public function isProviderEnabled( $pProvider ) {
		return isset( $this->mEnabledProviders[strtolower( $pProvider )] );
	}

	public function getConnectUri( $pProviderKey ) {
		return USERS_PKG_URI.'hauth/connect?provider='.$this->mEnabledProviders[$pProviderKey]['provider'];
	}

	public function getProviderConfig( $pProviderKey, $pConfigKey ) {
		return $this->getConfig( $this->getConfigName( $pProviderKey, $pConfigKey ) );
	}

	public function getConfigName( $pProviderKey, $pConfigKey ) {
		return 'users_ha_'.strtolower( $pProviderKey ).'_'.$pConfigKey;
	}

	private function getProviderPath() {
		return USERS_PKG_PATH.'hauth/Hybrid/Providers/';
	}

	private function getProviderClass( $pProvider ) {
		return 'Hybrid_Providers_'.$pProvider;
	}

	private function getProviderFile( $pProvider ) {
		return $this->getProviderPath().'/'.$pProvider.'.php';
	}

	private function getProviderIcon( $pProvider ) {
		$ret = 'icon-user';
		$allProviders = $this->getAllProviders();
		if( !empty( $allProviders[$pProvider]['icon'] ) ) {
			$ret = $allProviders[$pProvider]['icon'];
		}
		return $ret;
	}

	public function getEnabledConfigKey( $pProvider ) {
		return strtolower( 'users_ha_'.$pProvider.'_enabled' );
	}

	public function getEnabledProviders() {
		if( empty( $this->mEnabledProviders ) ) {
			$allProviders = $this->getAllProviders();
			foreach( $allProviders as $providerKey=>$providerHash ) {
				if( $this->getConfig( $this->getEnabledConfigKey( $providerHash['provider'] ) ) ) {
					$active = TRUE;
					foreach( array_keys( $providerHash['keys'] ) as $providerConfig ) {
						$configValue = $this->getProviderConfig( $providerKey, $providerConfig );
						$active &= !empty( $configValue );
					}
					if( $active ) {
						$this->mEnabledProviders[$providerKey] = $providerHash;
					}
				}
			}
		}
		return $this->mEnabledProviders;
	}

	public function scanProviders() {
		$ret = array();
		if( $providerFiles = array_diff(scandir( $this->getProviderPath() ), array('..', '.')) ) {
			foreach( $providerFiles as $providerFile ) {
				require_once $this->getProviderFile( $provider );
			}
		}
	}


	public function getAllProviders() {
		return array (
			'amazon' => array( 'provider' => 'Amazon', 'icon' => 'icon-user', 'keys' => array( 'id'=>'', 'secret'=> '' ) ),
			'aol' => array( 'provider' => 'AOL', 'icon' => 'icon-user', 'keys' => array( 'id'=>'', 'secret'=> '' ) ),
			'facebook' => array( 'provider' => 'Facebook', 'icon' => 'icon-facebook-sign', 'keys' => array( 'id'=>'', 'secret'=> '' ), 'options' => array( 'scope'=>'Comma separated list of requested permissions. Default are: email, user_about_me, user_birthday, user_hometown, user_location, user_website, publish_actions, read_custom_friendlists' ) ),
			'foursquare' => array( 'provider' => 'Foursquare', 'icon' => 'icon-foursquare', 'keys' => array( 'id'=>'', 'secret'=> '' ) ),
			'google' => array( 'provider' => 'Google', 'icon' => 'icon-google-plus-sign', 'keys' => array( 'id'=>'', 'secret'=> '' ) ),
			'instagram' => array( 'provider' => 'Instagram', 'icon' => 'icon-instagram', 'keys' => array( 'id'=>'', 'secret'=> '' ) ),
			'linkedin' => array( 'provider' => 'LinkedIn', 'icon' => 'icon-linkedin', 'keys' => array( 'key'=>'', 'secret'=> '' ) ),
			'live' => array( 'provider' => 'Live', 'icon' => 'icon-windows', 'keys' => array( 'id'=>'', 'secret'=> '' ) ),
			'openid' => array( 'provider' => 'OpenID', 'icon' => 'icon-user', 'keys' => array( 'id'=>'', 'secret'=> '' ) ),
			'paypal' => array( 'provider' => 'Paypal', 'icon' => 'icon-user', 'keys' => array( 'id'=>'', 'secret'=> '' ) ),
			'twitter' => array( 'provider' => 'Twitter', 'icon' => 'icon-twitter', 'keys' => array( 'key'=>'', 'secret'=> '' ) ),
			'yahoo' => array( 'provider' => 'Yahoo', 'icon' => 'icon-user', 'keys' => array( 'id'=>'', 'secret'=> '' ) ),
		);
	}
            
}
