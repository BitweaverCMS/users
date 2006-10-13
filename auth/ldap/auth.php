<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/auth/ldap/auth.php,v 1.3 2006/10/13 12:47:40 lsces Exp $
 *
 * @package users
 */

/**
 * required setup
 */
if (file_exists(UTIL_PKG_PATH."pear/Auth/Auth.php")) {
	require_once (UTIL_PKG_PATH."pear/Auth/Auth.php");
} else {
	@include_once("Auth.php");
}

/**
 * Class that manages the PEAR:ldap autentication method
 *
 * @package users
 * @subpackage auth
 */
class LDAPAuth extends BaseAuth {
	function LDAPAuth() {
		parent::BaseAuth('ldap');
	}

	function validate($user,$pass,$challenge,$response) {
		parent::validate($user,$pass,$challenge,$response);
		// set the Auth options
		$a = new Auth("LDAP", $this->mConfig, "", false, $user, $pass);
		// check if the login correct
		$a->login();
		$ret = '';
		switch ($a->getStatus()) {
			case AUTH_LOGIN_OK:
				$ret=USER_VALID;
				$ds=ldap_connect($this->mConfig["host"], $this->mConfig["port"]);  // Connects to LDAP Server
				if ($ds) {
					$r=ldap_bind($ds, $this->mConfig["adminuser"], $this->mConfig["adminpass"]);
					if ($r) {
						$attrs = array("cn", "mail");
						$sr=ldap_search($ds, $this->mConfig["basedn"], "(".$this->mConfig["userattr"]."=".$user.")", $attrs);  // Search
						$info = ldap_get_entries($ds, $sr);
						$this->mInfo["real_name"] = $info[0]["cn"][0];
						if(empty($this->mConfig["email"])) {
							if(empty($info[0]["mail"][0])) {
								$this->mInfo["email"] = $info[0][$this->mConfig["userattr"]][0];
							} else {
								$this->mInfo["email"] = $info[0]["mail"][0];
							}
						} else {
							$replace_func = create_function('$matches','$info = '.var_export($info,true).';
								$m = $matches[0];
								$m = substr($m,1,strlen($m)-2);
								if(empty($info[0][$m][0])) return "";
								return strtolower($info[0][$m][0]);');
							$this->mInfo["email"] = preg_replace_callback('/%.*?%/',$replace_func,$this->mConfig["email"]);
						}
					}
					ldap_close($ds);
				}
				break;
			case AUTH_USER_NOT_FOUND:
				$ret=USER_NOT_FOUND;
				break;
			case AUTH_WRONG_LOGIN:
				$ret=PASSWORD_INCORRECT;
				break;
			default:
				$ret=SERVER_ERROR;
				break;
		}
		return $ret;
	}

	function isSupported() {
		$ret = true;
		if (! class_exists("Auth")) {
			$this->mErrors['support']=tra("LDAP Authentication is not supported as PEAR Package Auth is not availible.");
			$ret = false;
		}
		if (!function_exists('ldap_connect')) {
			$this->mErrors['support']=tra("LDAP Authentication is not supported as PHP LDAP Extention not loaded.");
			$ret = false;
		}
		return $ret;
	}

	// create a new user in the Auth directory
	function createUser(&$userattr) {
		global $gBitDb;
		// set additional attributes here
		if (empty($userattr["email"])) {
			$userattr["email"] = $gBitDb->getOne("select `email` from `".BIT_DB_PREFIX."users_users` where `login`=?", array($userattr["login"]));
		}
		// set the Auth options
		$a = new Auth("LDAP", $this->mConfig);
		// check if the login correct
		if ($a->addUser($userattr["login"], $userattr["password"], $userattr) === true) {
			return true;
		} else {
			// otherwise use the error status given back
			$this->mErrors['create'] = $a->getStatus();
			return false;
		}
	}

	function canManageAuth() {
		return true;
	}

	function getSettings() {
		global $gBitUser;
		$listHash = array();
		$groups = $gBitUser->getAllGroups($listHash);
		$groups=$groups['data'];
		$groupsD =array();
		foreach ($groups as $g) {
			$groupsD[$g['group_id']]= "{$g['group_name']} ( {$g['group_desc']} )";
		}
		$groups = $groupsD;
		return array(
		'users_ldap_host' => array(
			'label' => "LDAP Host",
			'type' => "text",
			'note' => "",
			'default' => 'localhost',
		),
		'users_ldap_port' => array(
			'label' => "LDAP Port",
			'type' => "text",
			'note' => "",
			'default' => '389',
		),
		'users_ldap_basedn' => array(
			'label' => "LDAP Base DN",
			'type' => "text",
			'note' => "",
			'default' => '',
		),
		'users_ldap_userdn' => array(
			'label' => "LDAP User DN",
			'type' => "text",
			'note' => "",
		'default' => '',
		),
		'users_ldap_userattr' => array(
			'label' => "LDAP User Attribute",
			'type' => "text",
			'note' => "",
			'default' => 'uid',
		),
		'users_ldap_email' => array(
			'label' => "LDAP User E-Mail Address",
			'type' => "text",
			'note' => "If empty the attribute \"mail\" is used, if it not set for a user, <em>LDAP User Attribute</em> is used instead.<br />Otherwise all %<em>fields</em>% are replaced with the first value from the ldap attribute of the same name, and the result used as the email address.<br />Please remember to include the @ sign",
			'default' => '',
		),
		'users_ldap_useroc' => array(
			'label' => "LDAP User OC",
			'type' => "text",
			'note' => "",
		'default' => 'inetOrgPerson',
		),
		'users_ldap_groupdn' => array(
			'label' => "LDAP Group DN",
			'type' => "text",
			'note' => "",
			'default' => '',
		),
		'users_ldap_groupattr' => array(
			'label' => "LDAP Group Atribute",
			'type' => "text",
			'note' => "",
			'default' => 'cn',
		),
		'users_ldap_groupoc' => array(
			'label' => "LDAP Group OC",
			'type' => "text",
			'note' => "",
			'default' => 'groupOfUniqueNames',
		),
		'users_ldap_memberattr' => array(
			'label' => "LDAP Member Attribute",
			'type' => "text",
			'note' => "",
			'default' => 'uniqueMember',
		),
		'users_ldap_memberisdn' => array(
			'label' => "LDAP Member Is DN",
			'type' => "text",
			'note' => "",
			'default' => '',
		),
		'users_ldap_adminuser' => array(
			'label' => "LDAP Admin User",
			'type' => "text",
			'note' => "",
			'default' => '',
		),
		'users_ldap_adminpass' => array(
			'label' => "LDAP Admin Pwd",
			'type' => "password",
			'note' => "",
			'default' => '',
		),
		'users_ldap_scope' => array(
			'label' => "LDAP Scope",
			'type' => "option",
			'note' => "",
			'default' => 'sub',
			'options' => array(
				'sub' => "Sub",
				'one' => "One",
				'base' => "Base",
			),
		),
		'users_ldap_group' => array(
			'label' => "LDAP Group",
			'type' => "option",
			'note' => "",
			'default' => '3',
			'options' => $groups,
		),
	);
	}
}

?>
