<?php
/**
 * $Header$
 *
 * @package users
 */

/**
 * Class that manages the imap autentication method
 *
 * @package users
 * @subpackage auth
 */
class IMAPAuth extends BaseAuth {

	function __construct() {
		parent::__construct('imap');
	}

	function validate($user,$pass,$challenge,$response) {
		parent::validate($user,$pass,$challenge,$response);
		$mailbox = '{' . $this->mConfig['server'];
		if ($this->mConfig["ssl"]) {
			$mailbox .= "/ssl";
			if ($this->mConfig["sslvalidate"]) {
				$mailbox .= "/validate-cert";
			} else {
				$mailbox .= "/novalidate-cert";
			}
		}
		$mailbox .= ':'.$this->mConfig["port"].'}INBOX';

		$imapauth = @imap_open($mailbox,$user , $pass);
		if (!$imapauth) {
			$this->mErrors['login']=imap_errors();
			$ret=USER_NOT_FOUND;
		} else {
			$ret=USER_VALID;
			$this->mInfo["real_name"] = $user;
			if(empty($this->mConfig["email"])) {
				$this->mInfo["email"] = $user;
			} else {
				$info=array('login'=>$user);
				$replace_func = create_function('$matches','$info = '.var_export($info,true).';
							$m = $matches[0];
							$m = substr($m,1,strlen($m)-2);
							if(empty($info[$m])) return "";
							return strtolower($info[$m]);');
				$this->mInfo["email"] = preg_replace_callback('/%.*?%/',$replace_func,$this->mConfig["email"]);
			}
			imap_close($imapauth);
		}
		return $ret;
	}

	function isSupported() {
		$ret = true;
		if (!function_exists('imap_open')) {
			$this->mErrors['support']=tra("IMAP Authentication is not supported as PHP IMAP Extention not loaded.");
			$ret = false;
		}
		return $ret;
	}

	function createUser(&$userattr) {
		$this->mErrors['create']=tra("Cannot create users in an IMAP Server.");
		return false;
	}

	function canManageAuth() {
		$this->mErrors[]=tra("Cannot create users in an IMAP Server.");
		return false;
	}

	function getSettings() {
		return array(
		'users_imap_server' => array(
			'label' => "IMAP Server",
			'type' => "text",
			'note' => "",
			'default' => '',
		),
		'users_imap_ssl' => array(
			'label' => "Connect Using SSL",
			'type' => "checkbox",
			'note' => "",
			'default' => 'y',
		),
		'users_imap_sslvalidate' => array(
			'label' => "Require SSL Certificate to be valid",
			'type' => "checkbox",
			'note' => "",
			'default' => 'n',
		),
		'users_imap_port' => array(
			'label' => "IMAP Port",
			'type' => "text",
			'note' => "",
			'default' => '993',
		),
		'users_imap_email' => array(
			'label' => "LDAP User E-Mail Address",
			'type' => "text",
			'note' => "If empty the login is used.<br />Otherwise all %login% is replaced with the login name, and the result used as the email address.<br />Please remember to include the @ sign",
			'default' => "%login%@redhat.com",
		),
	);
	}
}
