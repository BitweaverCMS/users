<?php
/**
 * $Header$
 *
 * @package users
 */

/**
 * required setup
 */
if (file_exists(UTIL_PKG_INCLUDE_PATH."pear/Auth/Auth.php")) {
	require_once (UTIL_PKG_INCLUDE_PATH."pear/Auth/Auth.php");
} else {
// THIS may need changing if a different PEAR installation is used
	include_once("Auth/Auth.php");
}

/**
 * Class that manages the PEAR:ldap autentication method
 *
 * @package users
 * @subpackage auth
 */
class LDAPAuth extends BaseAuth {
	function __construct() {
		parent::__construct('ldap');
	}

	function validate($user,$pass,$challenge,$response) {
		parent::validate($user,$pass,$challenge,$response);
		global $gBitDb;

		if ( empty($user) or empty($pass) ) {
			return USER_NOT_FOUND;
		}

		$this->mInfo["real_name"] = '';  // This needs fixing in the base code - real_name will only exist if a user has been identiied

		// Use V3, which requires UTF-8:
		$this->mConfig['version'] = 3;
		$user_utf8 = utf8_encode( $user );

		if ( $this->mConfig['reqcert'] ) {
			// Skip the SSL certificate check:
			// (This assumes PHP is using the OpenLDAP client library.)
			putenv('LDAPTLS_REQCERT=never');
		}

		if ( $this->mConfig['activedirectory'] ) {
			$this->mConfig['attributes'] = (array) null;
			$this->mConfig['userfilter'] = '(objectClass='.$this->mConfig['useroc'].')';
			$this->mConfig['groupfilter'] = '(objectClass='.$this->mConfig['groupoc'].')';
			$this->mConfig['groupscope'] = $this->mConfig['userscope'];
		} else {
			// Using bitweaver groups with LDAP still needs completing so disable for now
			unset($this->mConfig['group']);
		}

		$a = new Auth('LDAP', $this->mConfig, "", false);
		$a->_loadStorage();  // set up connection to ldap via user details

		// First, try by username.  If that fails, try by email address.
		$success = $a->storage->fetchData($user_utf8, $pass, false);

		if ($success == false) {
			// The user wasn't found.  Try again by email address:
			$this->mConfig['userattrsto'] = $this->mConfig['userattr'];  // Keep this for later
			$this->mConfig['userattr'] = $this->mConfig['email'];  // Tell PEAR::Auth() to look at the 'mail' attribute

			// this needs testing better, should be no need to create second instance of Auth!
			$a = new Auth('LDAP', $this->mConfig, "", false);
			$a->_loadStorage();  // set up connection to ldap via user details

			$success = $a->storage->fetchData($user_utf8, $pass, false);
			if ($success == false) {
				$this->mErrors['login'] = isset($a->storage->options['status']) ? $a->storage->options['status'] : 'Not authenticated';
				return PASSWORD_INCORRECT;
			}
		}

		// At this point, there was a successful ldap_bind() using the
		// user's Distinguished Name (DN) and password for login.
		// The call to ldap_get_attributes() has been saved into $a->getAuthData('attributes')

		if ( $this->mConfig['activedirectory'] ) {
			// Active Directory does some things differently - mainly in the returns
			$attributes = $a->getAuthData();
			// Warning: ldap_get_attributes() uses case-sensitive array keys
			$this->mInfo["login"] = $attributes[ $this->mConfig['userattr'] ];
			$this->mInfo["email"] = $attributes[ $this->mConfig['email'] ];
			$this->mInfo["real_name"] = empty($attributes[$this->mConfig['name']]) ? $this->mInfo["login"] : $attributes[$this->mConfig['name']];
		}
		else {
			$attributes = $a->getAuthData('attributes');
			// Warning: ldap_get_attributes() uses case-sensitive array keys
			$this->mInfo["login"] = $attributes[ $this->mConfig['userattr'] ][0];
			$this->mInfo["email"] = $attributes[ $this->mConfig['email'] ][0];
			$this->mInfo["real_name"] = empty($attributes[$this->mConfig['name']][0]) ? $this->mInfo["login"] : $attributes[$this->mConfig['name']][0];
		}
		// Note, the new (or updated) SQL user will be created by the calling BitUser class.

		return USER_VALID;  // Success!

	}

	function isSupported() {
		$ret = true;
		if (!class_exists("Auth")) {
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

		// Roles are not inteneded to match with ldap groups
		// This area needs a closer look if it needs to be used
		$groups = array();
		$groups = $gBitUser->getAllGroups($listHash);
		$groupsD = array();
		foreach ($groups as $g) {
			$groupsD[$g['group_id']]= "{$g['group_name']} ( {$g['group_desc']} )";
		}
		$groups = $groupsD;
		return array(
		'users_ldap_url' => array(
			'label' => "LDAP Connection URL",
			'type' => "text",
			'note' => "You can specify an LDAP URL, like ldap://localhost/ or ldaps://some-server/.",
			'default' => '',
		),
		'users_ldap_host' => array(
			'label' => "LDAP Host",
			'type' => "text",
			'note' => "Instead of a URL, you can specify a hostname and port explicitly.  Give either a URL, or else a hostname/port (but not both).",
			'default' => 'localhost',
		),
		'users_ldap_port' => array(
			'label' => "LDAP Port",
			'type' => "text",
			'note' => "",
			'default' => '389',
		),
		'users_ldap_start_tls' => array(
			'label' => "Use Start-TLS?",
			'type' => "checkbox",
			'note' => "Please note there is a difference between ldaps:// and Start-TLS for ldap.  Start-TLS uses port 389, while ldaps:// uses port 636.  Both encrypted LDAP (with Start-TLS) and unencrypted LDAP can run on port 389 concurrently.",
			'default' => 'y',
		),
		'users_ldap_reqcert' => array(
			'label' => "Skip the SSL Cert validation?",
			'type' => "checkbox",
			'note' => "If Start-TLS is checked, then your LDAP server needs a trusted SSL cert -- unless you check this option, in which case you can use a self-signed (untrusted) cert.",
			'default' => 'y',
		),
		'users_ldap_referrals' => array(
			'label' => "Use Referrals?",
			'type' => "checkbox",
			'note' => "This should probably be 'yes'.  (Only applies to LDAP V3 servers.)",
			'default' => 'y',
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
			'note' => "The LDAP Attribute to use for the user's login in Bitweaver.  (This is the first attribute searched when the user logs in.)",
			'default' => 'uid',
		),
		'users_ldap_email' => array(
			'label' => "LDAP User E-Mail Address",
			'type' => "text",
			'note' => "The LDAP Attribute to use for the user's email address in Bitweaver.  (This is the second attribute searched when the user logs in.)",
			'default' => 'mail',
		),
		'users_ldap_name' => array(
			'label' => "LDAP User Display Name",
			'type' => "text",
			'note' => "The LDAP Attribute to use for the user's Full Name in Bitweaver.",
			'default' => 'displayName',
		),
		'users_ldap_useroc' => array(
			'label' => "LDAP User OC",
			'type' => "text",
			'note' => "",
		'default' => '(objectClass=inetOrgPerson)',
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
			'default' => '(objectClass=groupOfUniqueNames)',
		),
		'users_ldap_memberattr' => array(
			'label' => "LDAP Member Attribute",
			'type' => "text",
			'note' => "",
			'default' => 'uniqueMember',
		),
		'users_ldap_memberisdn' => array(
			'label' => "LDAP Member Is DN",
			'type' => "checkbox",
			'note' => "",
			'default' => 'n',
		),
		'users_ldap_binddn' => array(
			'label' => "LDAP Bind DN",
			'type' => "text",
			'note' => "This DN will be used to search the LDAP directory for users.  If left blank, 'anonymous bind' is used.",
			'default' => '',
		),
		'users_ldap_bindpw' => array(
			'label' => "LDAP Bind Pwd",
			'type' => "password",
			'note' => "",
			'default' => '',
		),
		'users_ldap_userscope' => array(
			'label' => "LDAP Scope to use when searching for users",
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
			'label' => "LDAP Group Requirement",
			'type' => "text",
			'note' => "If this is specified, then the LDAP user must also be a member of this LDAP group to connect.",
			'default' => ''
		),
		'users_ldap_activedirectory' => array(
			'label' => "Active Directory?",
			'type' => "checkbox",
			'note' => "",
			'default' => 'n'
		),
	);
	}
}

?>
