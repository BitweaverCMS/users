<?php

require_once(UTIL_PKG_PATH . "PHP_Compat/Compat/Function/scandir.php");

class BaseAuth {
	var $mLogin;
	var $mConfig;
	var $mInfo;
	var $mCfg;
	var $mErrors =array();

	function &getAuthMethods() {
		static $authMethod = array();
		static $scaned = false;
		if (!$scaned) {
			$scaned = true;
			BaseAuth::scanAuthPlugins();
		}
		return $authMethod;
	}

	function getAuthMethod( $pAuthId ) {
		$authMethod =& BaseAuth::getAuthMethods();
		if (empty($authMethod[$pAuthId])) return null;
		return $authMethod[$pAuthId];
	}

	function setAuthMethod($pAuthId,&$method) {
		$authMethod =& BaseAuth::getAuthMethods();
		$authMethod[$pAuthId]=$method;
	}

	function BaseAuth($pAuthId) {
		global $gBitSystem;
		$this->mCfg = BaseAuth::getAuthMethod($pAuthId);
		$this->mCfg['auth_id'] = $pAuthId;
		foreach ($this->getSettings() as $op_id => $op) {
			$var_id = substr($op_id,strrpos($op_id,"_")+1);
			$var = $gBitSystem->getConfig($op_id, $op['default']);
			if ($op['type']=="checkbox") {
				$var = ($var== "y");
			}
			$this->mConfig[$var_id]=$var;
		}
	}

	function scanAuthPlugins() {
		global $gBitSystem;
		
		$authDir = $gBitSystem->getConfig( 'users_auth_plugins_dir', USERS_PKG_PATH.'auth/' );
		if( is_dir( $authDir ) && $authScan = scandir( $authDir ) ) {
			foreach( $authScan as $plugDir ) {
				if( $plugDir != 'CVS' && substr($plugDir,0,1)!='.' && is_dir( $authDir.$plugDir ) ) {
					BaseAuth::register( $plugDir,array(
						'name' => strtoupper( $plugDir ).' Auth',
						'file' => $authDir.$plugDir.'/auth.php',
						'class' => ucfirst( $plugDir ).'Auth',
					) );
				}
			}
		}
	}

	function register($id,$hash) {
		global $gBitSystem;
		$err = false;
		$method = BaseAuth::getAuthMethod($id);
		if (! empty($method)) {
			BaseAuth::authError("Auth Registration Failed: $id already registered");
			$err = true;
		}
		if (empty($hash['name'])) {
			BaseAuth::authError("Auth Registration Failed: $id: No Name given");
			$err = true;
		}
		if (empty($hash['file'])) {
			BaseAuth::authError("Auth Registration Failed: $id: No file given");
			$err = true;
		} elseif(!file_exists($hash['file'])) {
			BaseAuth::authError("Auth Registration Failed: $id: File (".basename($hash['file']).") doesn't exist");
			$err = true;
		}
		if (empty($hash['class'])) {
			BaseAuth::authError("Auth Registration Failed: $id: No class given");
			$err = true;
		}

		if (!$err) {
			BaseAuth::setAuthMethod($id,$hash);
		}
	}

	function authError($str) {
		$warning = '<div class="error">'.$str.'</div>';
		print( $warning );
	}

	function getAuthMethodCount() {
		$methods = BaseAuth::getAuthMethods();
		if (empty($methods)) return 0;
		return count($methods);
	}

	function validate($user,$pass,$challenge,$response) {
		if (!$this->isSupported()) return false;
		$this->mLogin = $user;
		$this->mInfo['login']=$user;
		$this->mInfo['password']=$pass;
	}

	function getUserData() {
		return $this->mInfo;
	}

	function isSupported() {
		$this->mErrors[] = "BaseAuth is not an authentcation method";
		return false;
	}

	function createUser(&$userattr) {
		$this->mErrors[] = "BaseAuth is not an authentcation method";
		return false;
	}

	function getSettings() {
		return array();
	}

	function canManageAuth() {
		$this->mErrors[] = "BaseAuth is not an authentcation method";
		return false;
	}

	function getRegistrationFields() {
		return array();
	}

	function isActive($package = '') {
		global $gBitSystem;
		if (empty($package) && !empty($this->mCfg['auth_id'])) {
			$package = $this->mCfg['auth_id'];
		}
		for ($i=0;$i<BaseAuth::getAuthMethodCount();$i++) {
			$default="";
			if ( $i==0 ) {
				$default="bit";
			}
			if ($gBitSystem->getConfig("users_auth_method_$i",$default)== $package) {
				return true;
			}
		}
		return false;
	}

	function init( $pAuthMixed ) {
		global $gBitSystem;
		if( is_numeric( $pAuthMixed ) ) {
			$default="";
			if ($pAuthMixed==0) {
				$default="bit";
			}
			$authPlugin = $gBitSystem->getConfig("users_auth_method_$pAuthMixed",$default);			
			if (!empty( $authPlugin ) ) {
				return BaseAuth::init( $authPlugin );
			}
		} elseif (!empty($pAuthMixed)) {
			$authPlugin=BaseAuth::getAuthMethod( $pAuthMixed );
			if (file_exists( $authPlugin['file'] )) {
				require_once( $authPlugin['file'] );
				$cl = $authPlugin['class'];
				$instance = new $cl();
				if( $instance->isSupported() ) {
					return $instance;
				}
			}
		}
		return false;
	}

	function getConfig() {
		global $gBitSystem;
		$authSettings = array();
		foreach( BaseAuth::getAuthMethods() as $meth_name => $method ) {
			$instance = BaseAuth::init($meth_name) ;
			if ($instance) {
				foreach ($instance->getSettings() as $op_id => $op) {
					if (!empty($_REQUEST[$op_id])) {
						if( $op['type'] == 'checkbox' ) {
							simple_set_toggle( $op_id, USERS_PKG_NAME );
						} else {
							simple_set_value( $op_id, USERS_PKG_NAME );
						}
					}
					$value = $gBitSystem->getConfig($op_id, $op['default']);
					$op['value']=$value;
					$method['options'][$op_id] = $op;
				}
				$method['canManageAuth'] = $instance->canManageAuth();
				$authSettings['avail'][$meth_name]=$method;
			} elseif( is_object( $instance ) ) {
				$authSettings['err'][$meth_name]=implode("<br />",$instance->mErrors);
			}
		}
		if (!empty($_REQUEST["loginprefs"])) {
			$used =array();
			for ($i=0,$j=0;$i<count($authSettings['avail']);$i++,$j++) {
				$gBitSystem->storeConfig( "users_auth_method_$i",null, USERS_PKG_NAME );
				if (empty($_REQUEST["users_auth_method_$i"])) {
					$j--;
				} elseif(!empty($used[$_REQUEST["users_auth_method_$i"]])) {
					$j--;
				} else {
					$used[$_REQUEST["users_auth_method_$i"]]="stored_$j";
					$gBitSystem->storeConfig( "users_auth_method_$j", $_REQUEST["users_auth_method_$i"], USERS_PKG_NAME );
				}
			}
		}
		$canManageAuth = false;
		for ($i=0;$i<count($authSettings['avail']);$i++) {
			$default="";
			if ($i==0) {
				$default="bit";
			}
			$authSettings['avail_method'][$i]['value']=$gBitSystem->getConfig("users_auth_method_$i",$default);
			if (!$canManageAuth&&!empty($authSettings['avail_method'][$i]['value'])) {
				$canManageAuth = $authSettings['avail'][$authSettings['avail_method'][$i]['value']]['canManageAuth'];
			}
		}
		if (($gBitSystem->getConfig('users_allow_register','y')=='y')&&!$canManageAuth) {
			$authSettings['err']['bit_reg']="Registration is enabled but there are no Auth Methods that support this, Registration won't work!";
		}
		$method['active']=BaseAuth::isActive($meth_name);
		return $authSettings;
	}
}
?>
