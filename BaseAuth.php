<?php
global $gBitUser;

class BaseAuth {
	var $mLogin;
	var $mConfig;
	var $mInfo;
	var $mCfg;
	var $mErrors =array();

	function &getAuthMethods() {
		static $authMethod = array();
		return $authMethod;
	}

	function getAuthMethod($authId) {
		$authMethod =& BaseAuth::getAuthMethods();
		if (empty($authMethod[$authId])) return null;
		return $authMethod[$authId];
	}

	function setAuthMethod($authId,&$method) {
		$authMethod =& BaseAuth::getAuthMethods();
		$authMethod[$authId]=$method;
	}

	function BaseAuth($authId) {
		global $gBitSystem;
		global $gBitUser;
		$this->mCfg = BaseAuth::getAuthMethod($authId);
		$this->mCfg['auth_id'] = $authId;
		foreach ($this->getSettings() as $op_id => $op) {
			$var_id = substr($op_id,strrpos($op_id,"_")+1);
			$var = $gBitSystem->getConfig($op_id, $op['default']);
			if ($op['type']=="checkbox") {
				$var = ($var== "y");
			}
			$this->mConfig[$var_id]=$var;
		}
	}

	function register($id,$hash) {
		if (!function_exists('preFlightWarning')) {
			function preFlightWarning($str) {
				?><div style="background: white; z-index: 50000; margin: 0em; padding: 1px; color: red; text-align: center;"">
				<h1>
					<img src="<?php echo LIBERTY_PKG_URL; ?>/icons/warning.png" alt="Warning" />
					<?php echo $str; ?>
					<img src="<?php echo LIBERTY_PKG_URL; ?>/icons/warning.png" alt="Warning" />
				</h1>
				</div><?php
			}
		}
		global $gBitSystem;
		$err = false;
		$method = BaseAuth::getAuthMethod($id);
		if (! empty($method)) {
			preFlightWarning("Auth Registration Failed: $id already registered");
			$err = true;
		}
		if (empty($hash['name'])) {
			preFlightWarning("Auth Registration Failed: $id: No Name given");
			$err = true;
		}
		if (empty($hash['file'])) {
			preFlightWarning("Auth Registration Failed: $id: No file given");
			$err = true;
		}elseif(!file_exists($hash['file'])) {
			preFlightWarning("Auth Registration Failed: $id: File (".basename($hash['file']).") doesn't exist");
			$err = true;
		}
		if (empty($hash['class'])) {
			preFlightWarning("Auth Registration Failed: $id: No class given");
			$err = true;
		}

		if (!$err) {
			BaseAuth::setAuthMethod($id,$hash);
		}
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
		global $gBitUser;
		if (empty($package) && !empty($this->mCfg['auth_id'])) {
			$package = $this->mCfg['auth_id'];
		}
		for ($i=0;$i<BaseAuth::getAuthMethodCount();$i++) {
			$default="";
			if ($i==0) {
				$default="bit";
			}
			if ($gBitSystem->getConfig("users_auth_method_$i",$default)== $package) {
				return true;
			}
		}
		return false;
	}

	function init($authId) {
		global $gBitUser;
		global $gBitSystem;
		if (is_numeric($authId)) {
			$default="";
			if ($authId==0) {
				$default="bit";
			}
			$method_name=$gBitSystem->getConfig("users_auth_method_$authId",$default);
			if (!empty($method_name)) {
				return BaseAuth::init($method_name);
			}
		} elseif (!empty($authId)) {
			$method=BaseAuth::getAuthMethod($authId);
			if (file_exists($method['file'])) {
				require_once($method['file']);
				$cl = $method['class'];
				$instance = new $cl();
				if ($instance->isSupported()) {
					return $instance;
				}
			}
		}
		return false;
	}

	function settings() {
		global $gBitSystem;
		global $gBitUser;
		global $gBitSmarty;
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
		$gBitSmarty->assign_by_ref( 'authSettings',  $authSettings);
	}
}
?>
