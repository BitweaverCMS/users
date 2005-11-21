<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/BitUser.php,v 1.2.2.50 2005/11/21 16:18:49 squareing Exp $
 *
 * Lib for user administration, groups and permissions
 * This lib uses pear so the constructor requieres
 * a pear DB object

 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: BitUser.php,v 1.2.2.50 2005/11/21 16:18:49 squareing Exp $
 * @package users
 */

/**
 * required setup
 */
require_once( LIBERTY_PKG_PATH.'LibertyAttachable.php' );
define( 'AVATAR_TYPE_CENTRALIZED', 'c' );
define( 'AVATAR_TYPE_USER_DB', 'u' );
define( 'AVATAR_TYPE_LIBRARY', 'l' );

// Column sizes for users_users table
define('REAL_NAME_COL_SIZE', 64);

define('BITUSER_CONTENT_TYPE_GUID', 'bituser' );

// some definitions for helping with authentication
define("USER_VALID", 2);
define("SERVER_ERROR", -1);
define("PASSWORD_INCORRECT", -3);
define("USER_NOT_FOUND", -5);
define("ACCOUNT_DISABLED", -6);

/**
 * Class that holds all information for a given user
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.2.2.50 $
 * @package  users
 * @subpackage  BitUser
 */
class BitUser extends LibertyAttachable {
/**
* associative hash of all entries from tiki_user_preferences
* @access public
*/
	var $mUserPrefs;
	var $mUserId;
	var $mUsername;
	var $mGroups;
	var $mInfo;
	var $mTicket;
	// used by LDAP to hold email and real_name temporarily
	var $mTmpStore;

/**
* Constructor - will automatically load all relevant data if passed a user string
*
* @access public
* @author Christian Fower<spider@viovio.com>
* @return returnString
*/
	function BitUser( $pUserId=NULL, $pContentId=NULL ) {
		LibertyAttachable::LibertyAttachable();
		$this->registerContentType( BITUSER_CONTENT_TYPE_GUID, array(
				'content_type_guid' => BITUSER_CONTENT_TYPE_GUID,
				'content_description' => 'User Information',
				'handler_class' => 'BitUser',
				'handler_package' => 'users',
				'handler_file' => 'BitUser.php',
				'maintainer_url' => 'http://www.bitweaver.org'
			) );
		$this->mUserId = (is_numeric( $pUserId ) ? $pUserId : NULL);
		$this->mContentId = $pContentId;
	}

	function assumeUser( $pUserId ) {
		global $gBitUser, $user_cookie_site;
		$ret = FALSE;
		// make double sure the current logged in user has permission
		if( $gBitUser->hasPermission( 'bit_p_admin_users' ) ) {
			$_SESSION[$user_cookie_site] = $pUserId;
			$ret = TRUE;
		}
		return $ret;
	}

/**
* load - loads all settings & preferences for this user
*
* @access public
* @author Chrstian Fowler <spider@steelsun.com>
* @return returnString
*/
	function load( $pFull=FALSE, $pUserName=NULL ) {
		$this->mInfo = NULL;
		if( isset( $this->mUserId ) ) {
			$whereSql = "WHERE uu.`user_id`=?";
			$bindVars = array( $this->mUserId );
		} elseif( isset( $this->mContentId ) ) {
			$whereSql = "WHERE uu.`content_id`=?";
			$bindVars = array( $this->mContentId );
		} elseif( !empty( $pUserName ) ) {
			$whereSql = "WHERE uu.`login`=?";
			$bindVars = array( $pUserName );
		}
		if( isset( $whereSql ) ) {
			$fullSelect = '';
			$fullJoin = '';
			if( $pFull ) {
				$fullSelect = ' , tc.* ';
				$fullJoin = " LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON ( uu.`content_id`=tc.`content_id` )";
			}
			// uu.`user_id` AS `uu_user_id` is last and aliases to avoid possible column name collisions
			$query = "select uu.*, uu.`login` AS `user`, tf_ava.`storage_path` AS `avatar_storage_path`, tf_por.`storage_path` AS `portrait_storage_path`, tf_logo.`storage_path` AS `logo_storage_path`  $fullSelect, uu.`user_id` AS `uu_user_id`
					  FROM `".BIT_DB_PREFIX."users_users` uu
						LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_attachments` ta_ava ON ( uu.`avatar_attachment_id`=ta_ava.`attachment_id` )
						LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_files` tf_ava ON ( tf_ava.`file_id`=ta_ava.`foreign_id` )
						LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_attachments` ta_por ON ( uu.`portrait_attachment_id`=ta_por.`attachment_id` )
						LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_files` tf_por ON ( tf_por.`file_id`=ta_por.`foreign_id` )
						LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_attachments` ta_logo ON ( uu.`logo_attachment_id`=ta_logo.`attachment_id` )
						LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_files` tf_logo ON ( tf_logo.`file_id`=ta_logo.`foreign_id` )
						$fullJoin
					  $whereSql";

			if( ($result = $this->mDb->query( $query, $bindVars )) && $result->numRows() ) {
				$this->mInfo = $result->fields;
				$this->mInfo['valid'] = !empty( $result->fields['uu_user_id'] );
				$this->mInfo['user_id'] = $result->fields['uu_user_id'];
				$this->mUserId = $result->fields['uu_user_id'];
				$this->mContentId = $result->fields['content_id'];
				$this->mUsername = $result->fields['login'];
				$this->mInfo['is_registered'] = $this->isRegistered();
				$this->mInfo['avatar_url'] = (!empty($this->mInfo['avatar_storage_path']) ? BIT_ROOT_URL.$this->mInfo['avatar_storage_path'] : NULL);
				$this->mInfo['portrait_url'] = (!empty($this->mInfo['portrait_storage_path']) ? BIT_ROOT_URL.$this->mInfo['portrait_storage_path']: NULL);
				$this->mInfo['logo_url'] = (!empty($this->mInfo['logo_storage_path']) ? BIT_ROOT_URL.$this->mInfo['logo_storage_path'] : NULL);
				$this->mInfo['avatar_path'] = (!empty($this->mInfo['avatar_storage_path']) ? BIT_ROOT_PATH.$this->mInfo['avatar_storage_path'] : NULL);
				$this->mInfo['avatar_path'] = (!empty($this->mInfo['portrait_storage_path']) ? BIT_ROOT_PATH.$this->mInfo['portrait_storage_path']: NULL);
				$this->mInfo['avatar_path'] = (!empty($this->mInfo['logo_storage_path']) ? BIT_ROOT_PATH.$this->mInfo['logo_storage_path'] : NULL);
				// a few random security conscious unset's - SPIDER
				unset( $this->mInfo['password'] );
				unset( $this->mInfo['hash'] );
				if( $pFull ) {
					$query = "SELECT `pref_name`, `value` FROM `".BIT_DB_PREFIX."tiki_user_preferences` WHERE `user_id`=?";
					$this->mUserPrefs = $this->mDb->getAssoc( $query, array( $this->mUserId ) );
					if( isset( $this->mUserPrefs['country'] ) ) {
						$this->mUserPrefs['flag'] = $this->mUserPrefs['country'];
						$this->mUserPrefs['country'] = str_replace( '_', ' ', $this->mUserPrefs['country']);
					}
					$this->mInfo['real_name'] = trim($this->mInfo['real_name']);
					$this->mInfo['display_name'] = ((!empty($this->mInfo['real_name']) ? $this->mInfo['real_name'] :
													(!empty($this->mUsername) ? $this->mUsername :
														(!empty($this->mInfo['email']) ? substr($this->mInfo['email'],0, strpos($this->mInfo['email'],'@')) :
															$this->mUserId))));
					//print("displayName: ".$this->mInfo['display_name']);
					$this->defaults();
					$this->mInfo['publicEmail'] = scrambleEmail( $this->mInfo['email'], (isset($this->mUserPrefs['email is public']) ? $this->mUserPrefs['email is public'] : NULL) );
				}
				$this->mTicket = substr( md5( session_id() . $this->mUserId ), 0, 20 );
			} else {
				$this->mUserId = NULL;
			}
		}
		return( $this->isValid() );
	}


	function storePreference( $pPrefName, $pPrefValue ) {
		$ret = FALSE;
		if( $this->isValid() ) {
			$this->mUserPrefs[$pPrefName] = $pPrefValue;
			$query = "delete from `".BIT_DB_PREFIX."tiki_user_preferences` where `user_id`=? and `pref_name`=?";
			$bindvars=array( $this->mUserId, $pPrefName );
			$result = $this->mDb->query($query, $bindvars);
			$query = "insert into `".BIT_DB_PREFIX."tiki_user_preferences`(`user_id`,`pref_name`,`value`) values(?, ?, ?)";
			$bindvars[]=$pPrefValue;
			$result = $this->mDb->query($query, $bindvars);
			$ret = TRUE;
		}
		return $ret;
	}


	function getPreference( $pPrefName, $pPrefDefault=NULL, $pUserId = NULL ) {
	// ATS - Added ability to query a preference for any user
		$ret = NULL;
		if (!$pUserId) {
			$pUserId = $this->mUserId;
		}

		if ($pUserId && ($pUserId != $this->mUserId) && !empty($pPrefName)) {
			// Get a user preference for an arbitrary user
			$sql = "SELECT `value` FROM `".BIT_DB_PREFIX."tiki_user_preferences` WHERE `pref_name` = ? and `user_id` = ?";

			$rs = $this->mDb->query($sql, array($pPrefName, $pUserId));
			$ret = (!empty($rs->fields['value'])) ? $rs->fields['value'] : $pPrefDefault;
		} else {
			if( isset( $this->mUserPrefs ) && isset( $this->mUserPrefs[$pPrefName] ) ) {
				$ret = $this->mUserPrefs[$pPrefName];
			} else {
				$ret = $pPrefDefault;
			}
		}
		return $ret;
	}


	function defaults() {
		global $gBitSystem;
		if( empty( $this->mUserPrefs['user_information'] ) ) { $this->mUserPrefs['user_information'] = 'public'; }
		if( empty( $this->mUserPrefs['allowMsgs'] ) ) { $this->mUserPrefs['allowMsgs'] = 'y'; }
		if( empty( $this->mUserPrefs['display_timezone'] ) ) {
			$server_time = new Date();
			$this->mUserPrefs['display_timezone'] = $server_time->tz->getID();
		}
		if( empty( $this->mUserPrefs['userbreadCrumb'] ) ) {
			$this->mUserPrefs['userbreadCrumb'] = $gBitSystem->getPreference('userbreadCrumb',4);
		}
		if( empty( $this->mUserPrefs['bitlanguage'] ) ) {
			global $gBitLanguage;
			$this->mUserPrefs['bitlanguage'] = $gBitLanguage->mLanguage;
		}
		if( empty( $this->mUserPrefs['theme'] ) ) {
			global $site_style;
			$this->mUserPrefs['theme'] = $site_style;
		}
	}


	// =-=-=-=-=-=-=-=-=-=-=-=-=-= Session & Authentication Related Functions


	function updateSession( $pSessionId ) {
		if ( !$this->isDatabaseValid() ) return true;
		global $gBitSystem;
		$now = $gBitSystem->getUTCTime();
		$oldy = $now - (5 * 60);
		$bindVars = array( $now, $pSessionId );
		$userDelSql = '';

		if( $this->isRegistered() ) {
			array_push( $bindVars, $this->mUserId );
			$userDelSql = ' OR `user_id`=?';
		}
		$this->mDb->StartTrans();
		$hasSession = $this->mDb->getOne( "SELECT `timestamp` FROM `".BIT_DB_PREFIX."tiki_sessions` WHERE `session_id`=? ", array( $pSessionId ) );
		if( $hasSession ) {
			$ret = $this->mDb->query( "UPDATE `".BIT_DB_PREFIX."tiki_sessions` SET `timestamp`=? WHERE `session_id`=? $userDelSql", $bindVars );
		} else {
			if( $this->isRegistered() ) {
				$query = "insert into `".BIT_DB_PREFIX."tiki_sessions`(`timestamp`,`session_id`,`user_id`) values(?,?,?)";
				$result = $this->mDb->query($query, $bindVars);
			}
		}
		$query = "DELETE from `".BIT_DB_PREFIX."tiki_sessions` where `timestamp`<?";
		$result = $this->mDb->query($query, array($oldy));
		$this->mDb->CompleteTrans();

		return true;
	}

	function count_sessions() {
		$query = "select count(*) from `".BIT_DB_PREFIX."tiki_sessions`";
		$cant = $this->mDb->getOne($query,array());
		return $cant;
	}

	function logout() {
		global $user_cookie_site, $gBitSystem;
		setcookie($user_cookie_site, '', -3600, $gBitSystem->getPreference('cookie_path'), $gBitSystem->getPreference('cookie_domain') );
		//session_unregister ('user');
		unset ($_SESSION[$user_cookie_site]);
		session_destroy();
		$this->mUserId = NULL;
	}

	function isRegistered() {
		return ( $this->mUserId > ANONYMOUS_USER_ID );
	}

	function isValid() {
		return ( !empty( $this->mUserId ) );
	}

	function isAdmin() {
//		print "PURE VIRTUAL BASE FUNCTION";
//		die;
		return FALSE;
	}

	function verifyTicket( $pFatalOnError=TRUE ) {
		global $gBitSystem;
		$ret = FALSE;
		if( !empty( $_REQUEST['tk'] ) ) {
			if( !($ret = $_REQUEST['tk'] == $this->mTicket ) && $pFatalOnError ) {
				$gBitSystem->fatalError( "Security Violation" );
			}
		}
		return $ret;
	}

	function verify( &$pParamHash ) {
		global $gBitSystem;

		trim_array( $pParamHash );

		// perhaps someone is importing users and *knows* what they are doing
		if( !empty( $pParamHash['user_id'] ) && is_numeric( $pParamHash['user_id'] ) ) {
			$pParamHash['user_store']['user_id'] = $pParamHash['user_id'];
		}
		if( !empty( $pParamHash['login'] ) ) {
			if( $this->userExists( array( 'login' => $pParamHash['login'] ) ) ) {
				$this->mErrors['login'] = 'The username "'.$pParamHash['login'].'" is already in use';
			} elseif( preg_match( '/[^A-Za-z0-9_.]/', $pParamHash["login"] ) ) {
				$this->mErrors['login'] = tra( "Your username can only contain numbers, characters, and underscores." );
			} else {
				// LOWER CASE all logins
				$pParamHash['login'] = $pParamHash['login'];
				$pParamHash['user_store']['login'] = $pParamHash['login'];
			}
		}
		if( !empty( $pParamHash['real_name'] ) ) {
			$pParamHash['user_store']['real_name'] = substr( $pParamHash['real_name'], 0, 64 );
		}
		if( !empty( $pParamHash['email'] ) ) {
			// LOWER CASE all emails
			$pParamHash['email'] = strtolower( $pParamHash['email'] );
			if( $this->verifyEmail( $pParamHash['email'] ) ) {
				$pParamHash['user_store']['email'] = strtolower( substr( $pParamHash['email'], 0, 200 ) );
			}
		}
		// check some new user requirements
		if( !$this->isRegistered() ) {
			/*if( empty( $pParamHash['login'] ) ) {
				$this->mErrors['login'] = 'You must enter a username';
			}*/
			if( empty( $pParamHash['registration_date'] ) ) {
				$pParamHash['registration_date'] = date( "U" );
			}
			$pParamHash['user_store']['registration_date'] = $pParamHash['registration_date'];
			if( empty( $pParamHash['email'] ) ) {
				$this->mErrors['email'] = tra( 'You must enter your email address' );
			}
			// jht 2005-06-22_23:51:58 $pParamHash['admin_add'] is set on adds from admin page - a kludge
			// that should be fixed in some better way.
			if(empty($pParamHash['admin_add']) && $gBitSystem->isFeatureActive( 'validateUsers' ) ) {
				$pParamHash['password'] = $this->genPass();
				$pParamHash['user_store']['provpass'] = substr( md5( $pParamHash['password'] ), 0, 30 );
				$pParamHash['pass_due'] = 0;
			} elseif( empty( $pParamHash['password'] ) ) {
				$this->mErrors['password'] = tra( 'Your password should be at least '.$gBitSystem->getPreference( 'min_pass_length', 4 ).' characters long' );
			}
		} elseif( $this->isValid() ) {
			// Prevent loosing user info on save
			if( empty( $pParamHash['edit'] ) ) {
				$pParamHash['edit'] = $this->mInfo['data'];
			}
		}


		//Validate password here
		if( !empty( $pParamHash['password'] ) ) {
			$minPassword = $gBitSystem->getPreference( 'min_pass_length', 4 );
			if(strlen( $pParamHash['password'] ) < $minPassword ) {
				$this->mErrors['password'] = tra( 'Your password should be at least '.$minPassword.' characters long' );
			} elseif( !empty( $pParamHash['password2'] ) && ($pParamHash['password'] != $pParamHash['password2']) ) {
				$this->mErrors['password'] = tra( 'The passwords do not match' );
			} elseif( $gBitSystem->isFeatureActive( 'pass_chr_num' ) &&
				(!preg_match_all( "/[0-9]+/",$pParamHash["password"],$foo ) || !preg_match_all("/[A-Za-z]+/",$pParamHash["password"],$foo)) ) {
				$this->mErrors['password'] = tra( 'Password must contain both letters and numbers' );
			} else {
				// Generate a unique hash
				$pParamHash['user_store']['hash'] = md5( strtolower( (!empty($pParamHash['login'])?$pParamHash['login']:'') ).$pParamHash['password'].$pParamHash['email'] );
				$now = $gBitSystem->getUTCTime();
				if( !isset( $pParamHash['pass_due'] ) && $gBitSystem->getPreference('pass_due') ) {
					$pParamHash['user_store']['pass_due'] = $now + (60 * 60 * 24 * $gBitSystem->getPreference('pass_due') );
				} elseif( isset( $pParamHash['pass_due'] ) ) {
					// renew password only next half year ;)
					$pParamHash['user_store']['pass_due'] = $now + (60 * 60 * 24 * $pParamHash['pass_due']);
				}
				if( $gBitSystem->isFeatureActive( 'feature_clear_passwords' ) || !empty( $pParamHash['user_store']['provpass'] ) ) {
					$pParamHash['user_store']['password'] = $pParamHash['password'];
				}
			}
		}
		return( count( $this->mErrors ) == 0 );
	}

   function get_SMTP_response ( &$pConnect ) {

		$Out = "";
		while (1) {
			$work = fgets ( $pConnect, 1024 );
			$Out .= $work;
			if (!preg_match('/^\d\d\d-/',$work)) {
				break;
				}
			}	
   
        return $Out;
   }


	function verifyEmail( $pEmail, $pValidate = FALSE ) {
		global $gBitSystem, $gDebug;
		$HTTP_HOST=$_SERVER['SERVER_NAME'];
		$ret = FALSE;
		if( !empty( $this ) ) {
			$errors = &$this->mErrors;
		} else {
			$errors = array();
		}
		if( !eregi (
			  '^[-!#$%&\`*+\\./0-9=?A-Z^_`a-z{|}~]+'.'@'.
			   '(localhost|[-!$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.'.
			   '[-!$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+)$'
				, $pEmail ) ) {
			$errors['email'] = 'The email address "'.$pEmail.'" is invalid.';
		} elseif( !empty( $this ) && is_object( $this ) && $this->userExists( array( 'email' => $pEmail ) ) ) {
			$errors['email'] = 'The email address "'.$pEmail.'" has already been registered.';
		} elseif( $gBitSystem->isFeatureActive( 'validateUsers' ) ) {
			list ( $Username, $domain ) = split ("@",$pEmail);
			// That MX(mail exchanger) record exists in domain check .
			// checkdnsrr function reference : http://www.php.net/manual/en/function.checkdnsrr.php
			if ( checkdnsrr ( $domain, "MX" ) )  {
				if($gDebug) echo "Confirmation : MX record about {$domain} exists.<br>";
				// If MX record exists, save MX record address.
				// getmxrr function reference : http://www.php.net/manual/en/function.getmxrr.php
				if ( getmxrr ($domain, $MXHost))  {
					if($gDebug) {
						echo "Confirmation : Is confirming address by MX LOOKUP.<br>";
						for ( $i = 0,$j = 1; $i < count ( $MXHost ); $i++,$j++ ) {
							echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Result($j) - $MXHost[$i]<BR>";
						}
					}
				}
				// Getmxrr function does to store MX record address about $domain in arrangement form to $MXHost.
				// $ConnectAddress socket connection address.
				$ConnectAddress = $MXHost[0];
			} else {
				// If there is no MX record simply @ to next time address socket connection do .
				$ConnectAddress = $domain;
				if ($gDebug) echo "Confirmation : MX record about {$domain} does not exist.<br>";
			}
			if( !$pValidate ) {	// Skip the connecting test if it didn't work the first time
				// fsockopen function reference : http://www.php.net/manual/en/function.fsockopen.php
				$Connect = @fsockopen ( $ConnectAddress, 25 );
				// Success in socket connection
				if ($Connect) {
					if ($gDebug) echo "Connection succeeded to {$ConnectAddress} SMTP.<br>";
					// Judgment is that service is preparing though begin by 220 getting string after connection .
					// fgets function reference : http://www.php.net/manual/en/function.fgets.php
					// A "Real domain name required for sender address"

				$Out = $this->get_SMTP_response( $Connect );
				if ( ereg ( "^220", $Out ) ) {
						// Inform client's reaching to server who connect.
						if( $gBitSystem->hasValidSenderEmail() ) {
							$senderEmail = $gBitSystem->getPreference( 'sender_email' );
							fputs ( $Connect, "HELO $HTTP_HOST\r\n" );
if ($gDebug) echo "Run : HELO $HTTP_HOST<br>";
							$Out = $this->get_SMTP_response ( $Connect ); // Receive server's answering cord.
							// Inform sender's address to server.
							fputs ( $Connect, "MAIL FROM: <{$senderEmail}>\r\n" );
if ($gDebug) echo "Run : MAIL FROM: &lt;{$senderEmail}&gt;<br>";
							$From = $this->get_SMTP_response ( $Connect ); // Receive server's answering cord.
							// Inform listener's address to server.
							fputs ( $Connect, "RCPT TO: <{$pEmail}>\r\n" );
if ($gDebug) echo "Run : RCPT TO: &lt;{$pEmail}&gt;<br>";
							$To = $this->get_SMTP_response ( $Connect ); // Receive server's answering cord.
							// Finish connection.
							fputs ( $Connect, "QUIT\r\n");
if ($gDebug) echo "Run : QUIT<br>";
							fclose($Connect);
							// Server's answering cord about MAIL and TO command checks.
							// Server about listener's address reacts to 550 codes if there does not exist
							// checking that mailbox is in own E-Mail account.
							if ( !ereg ( "^250", $From ) || !ereg ( "^250", $To )) {
								$errors['email'] = $pEmail." is not recognized by the mail server";
							}
						}
					}
				} else {
					$errors['email'] = "Cannot connect to mail server ({$ConnectAddress}).";
				}
			}
		}
		return( count( $errors ) == 0 );
	}


/**
* register - will handle everything necessary for registering a user and sending appropriate emails, etc.
*
* @access public
* @author Christian Fowler<spider@viovio.com>
* @return returnString
*/
	function register( &$pParamHash ) {
		global $notificationlib, $gBitSmarty, $gBitSystem;
		$ret = FALSE;
		if( $this->store( $pParamHash ) ) {
			require_once( KERNEL_PKG_PATH.'notification_lib.php' );
			$notificationlib->post_new_user_event( $pParamHash['login'] );
			$ret = TRUE;

			// set local time zone as default when registering
			$this->storePreference( 'display_timezone', 'Local' );

			if( !empty( $_REQUEST['CUSTOM'] ) ) {
				foreach( $_REQUEST['CUSTOM'] as $field=>$value ) {
					$this->storePreference( $field, $value );
				}
			}
			$siteName = $gBitSystem->getPreference('siteTitle', $_SERVER['HTTP_HOST'] );
			$gBitSmarty->assign('siteName',$_SERVER["SERVER_NAME"]);
			$gBitSmarty->assign('mail_site',$_SERVER["SERVER_NAME"]);
			$gBitSmarty->assign('mail_user',$pParamHash['login']);
			if( $gBitSystem->isFeatureActive( 'validateUsers' ) ) {
				// $apass = addslashes(substr(md5($gBitSystem->genPass()),0,25));
				$apass = $pParamHash['user_store']['provpass'];
				$foo = parse_url($_SERVER["REQUEST_URI"]);
				$foo1=str_replace("register","confirm",$foo["path"]);
				$machine = httpPrefix().$foo1;

				// Send the mail
				$gBitSmarty->assign('msg',tra('You will receive an email with information to login for the first time into this site'));
				$gBitSmarty->assign('mail_machine',$machine);
				$gBitSmarty->assign('mail_apass',$apass);
				$mail_data = $gBitSmarty->fetch('bitpackage:users/user_validation_mail.tpl');
				mail($pParamHash["email"], $siteName.' - '.tra('Your registration information'),$mail_data,"From: ".$gBitSystem->getPreference('sender_email')."\r\nContent-type: text/plain;charset=utf-8\r\n");
				$gBitSmarty->assign('showmsg','y');
			}
			if( $gBitSystem->isFeatureActive( 'send_welcome_email' ) ) {
				// Send the welcome mail
				$gBitSmarty->assign( 'mailPassword',$pParamHash['password'] );
				$gBitSmarty->assign( 'mailEmail',$pParamHash['email'] );
				$mail_data = $gBitSmarty->fetch('bitpackage:users/welcome_mail.tpl');
				mail($pParamHash["email"], tra( 'Welcome to' ).' '.$siteName,$mail_data,"From: ".$gBitSystem->getPreference('sender_email')."\r\nContent-type: text/plain;charset=utf-8\r\n");
			}
		}
		return( $ret );
	}


	function store( &$pParamHash ) {
		if( $this->verify( $pParamHash ) ) {
			$this->mDb->StartTrans();
			$pParamHash['content_type_guid'] = BITUSER_CONTENT_TYPE_GUID;

			if( !empty( $pParamHash['user_store'] ) && count( $pParamHash['user_store'] ) ) {
				if( $this->isValid() ) {
					$userId = array ( "name" => "user_id", "value" => $this->mUserId );
					$result = $this->mDb->associateUpdate( BIT_DB_PREFIX.'users_users', $pParamHash['user_store'], $userId );
				} else {
					if( empty( $pParamHash['user_store']['user_id'] ) ) {
						$pParamHash['user_store']['user_id'] = $this->mDb->GenID( 'users_users_user_id_seq' );
					}
					$this->mUserId = $pParamHash['user_store']['user_id'];
					$result = $this->mDb->associateInsert( BIT_DB_PREFIX.'users_users', $pParamHash['user_store'] );
				}
			}
			// Prevent liberty from assuming ANONYMOUS_USER_ID while storing
			$pParamHash['user_id'] = $this->mUserId;

			if( LibertyContent::store( $pParamHash ) ) {
				if( empty( $this->mInfo['content_id'] ) || ($pParamHash['content_id'] != $this->mInfo['content_id']) ) {
					$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `content_id`=? WHERE `user_id`=?";
					$result = $this->mDb->query( $query, array( $pParamHash['content_id'], $this->mUserId ) );
					$this->mInfo['content_id'] = $pParamHash['content_id'];
				}
			}
			$pParamHash['upload']['thumbnail'] = FALSE;
			if( isset($_FILES['fPortraitFile']) && is_uploaded_file( $_FILES['fPortraitFile']['tmp_name'] ) ) {
				$pParamHash['upload'] = $_FILES['fPortraitFile'];
				if( !$this->storePortrait( $pParamHash, (!empty( $pParamHash['fAutoAvatar'] ) ? TRUE : FALSE) ) ) {
				}
			}

			if( isset($_FILES['fAvatarFile']) && is_uploaded_file($_FILES['fAvatarFile']['tmp_name']) && $_FILES['fAvatarFile']['size'] > 0 ) {
				$pParamHash['upload'] = $_FILES['fAvatarFile'];
				$pParamHash['upload']['source_file'] = $_FILES['fAvatarFile']['tmp_name'];
				if( !$this->storeAvatar( $pParamHash ) ) {
				}
			}

			if( isset($_FILES['fLogoFile']) && is_uploaded_file($_FILES['fLogoFile']['tmp_name']) && $_FILES['fLogoFile']['size'] > 0 ) {
				$pParamHash['upload'] = $_FILES['fLogoFile'];
				$pParamHash['upload']['source_file'] = $_FILES['fLogoFile']['tmp_name'];
				if( !$this->storeLogo( $pParamHash ) ) {
				}
			}

			$this->mDb->CompleteTrans();
			$this->load( TRUE );
		}
		return( count( $this->mErrors ) == 0 );
	}



	// removes user and associated private data
	function expunge( $pUserId ) {
		global $gBitSystem;
		$this->mDb->StartTrans();
		if( $_REQUEST["user_id"] != ANONYMOUS_USER_ID ) {
			$userTables = array(
				'tiki_semaphores',
				'tiki_user_bookmarks_urls',
				'tiki_user_bookmarks_folders',
				'tiki_user_menus',
				'tiki_user_tasks',
				'tiki_user_preferences',
				'tiki_user_watches',
				'users_users',
				'tiki_content',
			);
			foreach( $userTables as $table ) {
				$query = "delete from `".BIT_DB_PREFIX.$table."` where `user_id` = ?";
				$result = $this->mDb->query($query, array( $pUserId ) );
			}
			$this->mDb->CompleteTrans();
			return TRUE;
		} else {
			$this->mDb->RollbackTrans();
			$gBitSystem->fatalError( tra( 'The anonymous user cannot be deleted' ) );
		}
	}

	function genPass( $pLength=NULL ) {
		global $gBitSystem;
		// AWC: enable mixed case and digits, don't return too short password
		global $min_pass_length;
		$vocales = "AaEeIiOoUu13580";
		$consonantes = "BbCcDdFfGgHhJjKkLlMmNnPpQqRrSsTtVvWwXxYyZz24679";
		$r = '';
		if( empty( $pLength ) || !is_numeric( $pLength ) ) {
			$pLength = $gBitSystem->getPreference( 'min_pass_length', 4 );
		}
		for ($i = 0; $i < $pLength; $i++) {
			if ($i % 2) {
				$r .= $vocales{rand(0, strlen($vocales) - 1)};
			} else {
				$r .= $consonantes{rand(0, strlen($consonantes) - 1)};
			}
		}
		return $r;
	}

	function generateChallenge() {
		return( md5(BitSystem::genPass()) );
	}

	function login( $pLogin, $pPassword, $pChallenge=NULL, $pResponse=NULL ) {
		global $gBitSystem, $user_cookie_site;
		$isvalid = false;
		
		// Make sure cookies are enabled
		if ( !isset($_COOKIE['BWSESSION']) ) {
			$url = USERS_PKG_URL.'login.php?error=' . urlencode(tra('no cookie found, please enable cookies and try again.'));
			return ( $url );
			}
		
		// Verify user is valid
		$validate_result = $this->validate($pLogin, $pPassword, $pChallenge, $pResponse);
		if( $validate_result ) {
			$loginCol = strpos( $pLogin, '@' ) ? 'email' : 'login';
			$userInfo = $this->getUserInfo( array( $loginCol => $pLogin ) );
			// If the password is valid but it is due then force the user to change the password by
			// sending the user to the new password change screen without letting him use tiki
			// The user must re-nter the old password so no secutiry risk here
			if( $this->isPasswordDue() ) {
				// Redirect the user to the screen where he must change his password.
				// Note that the user is not logged in he's just validated to change his password
				// The user must re-enter his old password so no secutiry risk involved
				$url = USERS_PKG_URL.'change_password.php?user_id='.$userInfo['user_id']. '&oldpass=' . urlencode($pPassword);
			} elseif( $userInfo['user_id'] != ANONYMOUS_USER_ID ) {
				// User is valid and not due to change pass.. start session
				//session_register('user',$user);
				$_SESSION[$user_cookie_site] = $userInfo['user_id'];	// ATS - It appears this should be user_id here (instead of $pUserName like it was)
				$url = isset($_SESSION['loginfrom']) ? $_SESSION['loginfrom'] : $gBitSystem->getDefaultPage();
				//unset session variable in case user su's
				unset($_SESSION['loginfrom']);
				// Now if the remember me feature is on and the user checked the rememberme checkbox then ...
				if ($gBitSystem->isFeatureActive( 'rememberme' )&& isset($_REQUEST['rme']) && $_REQUEST['rme'] == 'on') {
					setcookie($user_cookie_site, $userInfo['hash'], (int)(time() + $gBitSystem->getPreference( 'remembertime' )), $gBitSystem->getPreference('cookie_path', BIT_ROOT_URL ), $gBitSystem->getPreference('cookie_domain') );
				} else {
					setcookie($user_cookie_site, $userInfo['hash'], 0, BIT_ROOT_URL);
				}
			}
		} else {
			$url = USERS_PKG_URL.'login.php?error=' . urlencode(tra('Invalid username or password'));
		}
		$https_mode = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on';
		if ($https_mode) {
			$stay_in_ssl_mode = isset($_REQUEST['stay_in_ssl_mode']) && $_REQUEST['stay_in_ssl_mode'] == 'on';
			if (!$stay_in_ssl_mode) {
				$http_domain = $gBitSystem->getPreference('http_domain', false);
				$http_port = $gBitSystem->getPreference('http_port', 80);
				$http_prefix = $gBitSystem->getPreference('http_prefix', '/');
				if ($http_domain) {
					$prefix = 'http://' . $http_domain;
					if ($http_port != 80)
						$prefix .= ':' . $http_port;
					$prefix .= $https_prefix;
					$url = $prefix . $url;
					if (SID)
						$url .= '?' . SID;
				}
			}
		}
		return( $url );
	}

	function validate($user, $pass, $challenge, $response) {
		global $gBitSystem;
		// these will help us keep tabs of what is going on
		$userTikiValid = false;
		$userTikiPresent = false;
		$userAuthValid = false;
		$userAuthPresent = false;
		// see if we are to use PEAR::Auth
		$auth_pear = ($gBitSystem->getPreference("auth_method", "tiki") == "auth");
		$create_tiki = ($gBitSystem->getPreference("auth_create_gBitDbUser", "n") == "y");
		$create_auth = ($gBitSystem->getPreference("auth_create_user_auth", "n") == "y");
		$skip_admin = ($gBitSystem->getPreference("auth_skip_admin", "n") == "y");
		// first attempt a login via the standard Tiki system
		$userId = $this->validateBitUser($user, $pass, $challenge, $response);
		if ($userId) {
			$userTikiValid = true;
			$userTikiPresent = true;
		} elseif ($this->mErrors['login'] == 'Password incorrect') {
			$userTikiPresent = true;
		} elseif ($this->mErrors['login'] == 'User not found') {
		}
		// if we aren't using LDAP this will be quick
		if ( !$auth_pear || ($user == "admin" && $skip_admin) ) {
			// TODO nothing here yet, as skip_admin is broken - wolff_borg
		} elseif ( $auth_pear ) {
			// next see if we need to check LDAP
			// check the user account
			$result = $this->validateAuth($user, $pass);
			switch ($result) {
				case USER_VALID:
					unset($this->mErrors['login']);
					$userAuthValid = true;
					$userAuthPresent = true;
					break;
				case PASSWORD_INCORRECT:
					$this->mErrors['login'] = 'Password incorrect';
					$userAuthPresent = true;
					break;
				case USER_NOT_FOUND:
					// disable this error as user may have an account in Tiki only - wolff_borg
					//$this->mErrors['login'] = 'User not found';
					break;

			}
		}
/*
echo "userId: $userId<br>";
echo "auth_pear: $auth_pear<br>";
echo "create_tiki: $create_tiki<br>";
echo "create_auth: $create_auth<br>";
echo "skip_admin: $skip_admin<br>";
echo "userTikiValid: $userTikiValid<br>";
echo "userAuthValid: $userAuthValid<br>";
echo "userTikiPresent: $userTikiPresent<br>";
echo "userAuthPresent: $userAuthPresent<br>";
*/
		// start off easy
		// if the user verified in Tiki and Auth, or
		// was not present in either, than skip all this
		if ( $auth_pear ) {
//echo "1<br>";
			// if the user was logged into Tiki but not found in Auth
			// see if we can create a new account
			if ( $create_auth && $userTikiPresent && !$userAuthPresent ) {
//echo "2<br>";
				// need to make this better! *********************************************************
				$result = $this->create_user_auth($user, $pass);
				// if the server didn't work, do something!
				if ($result == SERVER_ERROR || $result != USER_VALID) {
					$this->mErrors['login'] = 'Auth server error creating user';
				}
			}
			// if the user was logged into Auth but not found in Tiki
			// see if we can create a new account
			elseif( $create_tiki && $userAuthValid && !$userTikiPresent ) {
//echo "3<br>";
//echo "user: $user<br>";
//echo "pass: $pass<br>";
				// need to make this better! *********************************************************
				// if it worked ok, just log in
				$authUserInfo = array( 'login' => $user, 'password' => $pass, 'real_name' => $this->mTmpStore['real_name'], 'email' => $this->mTmpStore['email'] );
				// TODO somehow, mUserId gets set to -1 at this point - no idea how
				// set to NULL to prevent overwriting Guest user - wolff_borg
				$this->mUserId = NULL;
//echo "mUserId: ".$this->mUserId."<br>";
				if ( $this->store( $authUserInfo ) ) {
					$userId = $this->mUserId;
				}
			}
			// if the user was logged into Auth but not found in Tiki
			// see if we can create a new account
			elseif( $userAuthValid && $userTikiPresent ) {
//echo "4<br>";
//echo "user: $user<br>";
				$real_name = $this->mTmpStore['real_name'];
				$email = $this->mTmpStore['email'];
				$userInfo = $this->getUserInfo(array('login' => $user ));
//vd($userInfo);
				$this->mUserId = $userInfo['user_id'];
				$authUserInfo = array( 'login' => $user, 'password' => $pass, 'real_name' => $real_name, 'email' => $email );
				$this->store( $authUserInfo );
				# TODO: Fix this - if user is an LDAP user, with a TIKI user already created,
				# storing user info causes errors. NEED TO FIX - wolff_borg
				$this->mErrors = array();
			}
		}
		if( $userId ) {
//echo "5<br>";
			$this->update_lastlogin( $userId );
			$this->mUserId = $userId;
			$this->load();
		}
//echo "6<br>";
//vd($this->mErrors);
		return( count( $this->mErrors ) == 0 );
	}
	// validate the user in the PEAR::Auth system
	function validateAuth($user, $pass) {
		global $gBitSystem;
		require_once (UTIL_PKG_PATH."pear/Auth/Auth.php");
		// just make sure we're supposed to be here
		if ($gBitSystem->getPreference("auth_method", "tiki") != "auth")
			return false;
		// get all of the LDAP options from the database
		$options["host"] = $gBitSystem->getPreference("auth_ldap_host", "localhost");
		$options["port"] = $gBitSystem->getPreference("auth_ldap_port", "389");
		$options["scope"] = $gBitSystem->getPreference("auth_ldap_scope", "sub");
		$options["basedn"] = $gBitSystem->getPreference("auth_ldap_basedn", "");
		$options["userdn"] = $gBitSystem->getPreference("auth_ldap_userdn", "");
		$options["userattr"] = $gBitSystem->getPreference("auth_ldap_userattr", "uid");
		$options["useroc"] = $gBitSystem->getPreference("auth_ldap_useroc", "posixAccount");
		$options["groupdn"] = $gBitSystem->getPreference("auth_ldap_groupdn", "");
		$options["groupattr"] = $gBitSystem->getPreference("auth_ldap_groupattr", "cn");
		$options["groupoc"] = $gBitSystem->getPreference("auth_ldap_groupoc", "groupOfUniqueNames");
		$options["memberattr"] = $gBitSystem->getPreference("auth_ldap_memberattr", "uniqueMember");
		$options["memberisdn"] = ($gBitSystem->getPreference("auth_ldap_memberisdn", "y") == "y");
		$options["adminuser"] = $gBitSystem->getPreference("auth_ldap_adminuser", "");
		$options["adminpass"] = $gBitSystem->getPreference("auth_ldap_adminpass", "");

		// set the Auth options
		$a = new Auth("LDAP", $options, "", false, $user, $pass);
		// check if the login correct
		$a->login();
		$ret = '';
		switch ($a->getStatus()) {
			case AUTH_LOGIN_OK:
				$ret=USER_VALID;
				$ds=ldap_connect($options["host"], $options["port"]);  // Connects to LDAP Server
				if ($ds) {
					$r=ldap_bind($ds, $options["adminuser"], $options["adminpass"]);
					$attrs = array("cn", "mail");
					$sr=ldap_search($ds, $options["basedn"], "(".$options["userattr"]."=".$user.")", $attrs);  // Search
					$info = ldap_get_entries($ds, $sr);
					$this->mTmpStore["real_name"] = $info[0]["cn"][0];
					$this->mTmpStore["email"] = $info[0]["mail"][0];
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

	// validate the user in the bitweaver database - validation is case insensitive, and we like it that way!
	function validateBitUser( $pLogin, $pass, $challenge, $response ) {
		global $gBitSystem;
		$ret = NULL;
		if( empty( $pLogin ) ) {
			$this->mErrors['login'] = 'User not found';
		} elseif( empty( $pass ) ) {
			$this->mErrors['login'] = 'Password incorrect';
		} else {
			$loginVal = strtoupper( $pLogin ); // case insensitive login
			$loginCol = ' UPPER(`'.(strpos( $pLogin, '@' ) ? 'email' : 'login').'`)';
			// first verify that the user exists
			$query = "select `email`, `login`, `user_id`, `password` from `".BIT_DB_PREFIX."users_users` where " . $this->mDb->convert_binary(). " $loginCol = ?";
			$result = $this->mDb->query( $query, array( $loginVal ) );
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
					$query = "select `user_id` from `".BIT_DB_PREFIX."users_users` where " . $this->mDb->convert_binary(). " $loginCol = ? and (`hash`=? or `hash`=?)";
					$result = $this->mDb->query( $query, array( $loginVal, $hash, $hash2 ) );
					if ($result->numRows()) {
						$query = "update `".BIT_DB_PREFIX."users_users` set `last_login`=`current_login`, `current_login`=? where `user_id`=?";
						$result = $this->mDb->query($query, array( $gBitSystem->getUTCTime(), $userId ));
						$ret = $userId;
					} else {
						$this->mErrors['login'] = 'Password incorrect';
					}
				} else {
					// Use challenge-reponse method
					// Compare pass against md5(user,challenge,hash)
					$hash = $this->mDb->getOne("select `hash`  from `".BIT_DB_PREFIX."users_users` where " . $this->mDb->convert_binary(). " $loginCol = ?", array( $pLogin ) );
					if (!isset($_SESSION["challenge"])) {
						$this->mErrors['login'] = 'Invalid challenge';
					}
					//print("pass: $pass user: $user hash: $hash <br/>");
					//print("challenge: ".$_SESSION["challenge"]." challenge: $challenge<br/>");
					//print("response : $response<br/>");
					if ($response == md5( strtolower($user) . $hash . $_SESSION["challenge"]) ) {
						$ret = $userId;
						$this->update_lastlogin( $userId );
					} else {
						$this->mErrors['login'] = 'Invalid challenge';
					}
				}
			}
		}
		return( $ret );
	}
	// update the lastlogin status on this user
	function update_lastlogin( $pUserId ) {
		$ret = FALSE;
		if( is_numeric( $pUserId ) ) {
			global $gBitSystem;
			$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `last_login`=`current_login`, `current_login`=?
					  WHERE `user_id`=?";
			$result = $this->mDb->query( $query, array( $gBitSystem->getUTCTime(), $pUserId ) );
			$ret = TRUE;
		}
		return $ret;
	}
	// create a new user in the Auth directory
	function create_user_auth($user, $pass) {
		global $gBitSystem;
		$options = array();
		$options["host"] = $gBitSystem->getPreference("auth_ldap_host", "localhost");
		$options["port"] = $gBitSystem->getPreference("auth_ldap_port", "389");
		$options["scope"] = $gBitSystem->getPreference("auth_ldap_scope", "sub");
		$options["basedn"] = $gBitSystem->getPreference("auth_ldap_basedn", "");
		$options["userdn"] = $gBitSystem->getPreference("auth_ldap_userdn", "");
		$options["userattr"] = $gBitSystem->getPreference("auth_ldap_userattr", "uid");
		$options["useroc"] = $gBitSystem->getPreference("auth_ldap_useroc", "posixAccount");
		$options["groupdn"] = $gBitSystem->getPreference("auth_ldap_groupdn", "");
		$options["groupattr"] = $gBitSystem->getPreference("auth_ldap_groupattr", "cn");
		$options["groupoc"] = $gBitSystem->getPreference("auth_ldap_groupoc", "groupOfUniqueNames");
		$options["memberattr"] = $gBitSystem->getPreference("auth_ldap_memberattr", "uniqueMember");
		$options["memberisdn"] = ($gBitSystem->getPreference("auth_ldap_memberisdn", "y") == "y");
		$options["adminuser"] = $gBitSystem->getPreference("auth_ldap_adminuser", "");
		$options["adminpass"] = $gBitSystem->getPreference("auth_ldap_adminpass", "");
		// set additional attributes here
		$userattr = array();
		$userattr["email"] = $this->mDb->getOne("select `email` from `".BIT_DB_PREFIX."users_users`
				where `login`=?", array($user));
		// set the Auth options
		$a = new Auth("LDAP", $options);
		// check if the login correct
		if ($a->addUser($user, $pass, $userattr) === true)
			$status = USER_VALID;
		// otherwise use the error status given back
		else
			$status = $a->getStatus();
		return $status;
	}

	function get_users_names($offset = 0, $maxRecords = -1, $sort_mode = 'login_desc', $find = '') {
		// Return an array of users indicating name, email, last changed pages, versions, last_login
		if ($find) {
			$findesc = '%' . strtoupper( $find ) . '%';
			$mid = " where UPPER(`login`) like ?";
			$bindvars = array($findesc);
		} else {
			$mid = '';
			$bindvars=array();
		}
		$query = "select `login` from `".BIT_DB_PREFIX."users_users` $mid order by ".$this->mDb->convert_sortmode($sort_mode);
		$result = $this->mDb->query($query,$bindvars,$maxRecords,$offset);
		$ret = array();
		while ($res = $result->fetchRow()) {
			$ret[] = $res["login"];
		}
		return ($ret);
	}

	function confirmRegistration( $pUser, $pProvpass ) {
		$query = "select `user_id`, `provpass`, `password`, `login`, `email` FROM `".BIT_DB_PREFIX."users_users`
				  WHERE `login`=? AND `provpass`=?";
		return( $this->mDb->getRow($query, array( $pUser, $pProvpass ) ) );
	}



	function change_user_email( $pUserId, $pUsername, $pEmail, $pPass ) {
		$hash = md5( strtolower($pUsername) . $pPass . $pEmail );
		$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `email`=?, `hash`=? WHERE " . $this->mDb->convert_binary(). " `user_id`=?";
		$result = $this->mDb->query( $query, array( $pEmail, $hash, $pUserId ) );
		$query = "UPDATE `".BIT_DB_PREFIX."tiki_user_watches` SET `email`=? WHERE " . $this->mDb->convert_binary(). " `user_id`=?";
		$result = $this->mDb->query( $query, array( $pEmail, $pUserId ) );
		return TRUE;
	}


	function lookupHomepage( $iHomepage ) {
		$ret = NULL;
		if (is_numeric($iHomepage)) {
		   	// iHomepage is the user_id for the user...
			$key = 'user_id';
		} elseif (substr($iHomepage,0,7) == 'mailto:') {
			// iHomepage is the email address of the user...
			$key = 'email';
		} else {
			// iHomepage is the 'login' of the user...
			$key = 'login';
		}
		$tmpUser = $this->getUserInfo( array( $key => $iHomepage ) );
		if (!empty($tmpUser['user_id'])) {
			$ret = $tmpUser['user_id'];
		}
		return $ret;
	}

	// specify lookup where by hash key lik 'login' or 'user_id' or 'email'
	function getUserInfo( $pUserMixed ) {
		$ret = NULL;
		if( is_array( $pUserMixed ) ) {
			$query = "SELECT  uu.* FROM `".BIT_DB_PREFIX."users_users` uu LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_content` tc ON (tc.`content_id`=uu.`content_id`)
					  WHERE UPPER( uu.`".key( $pUserMixed )."` ) = ?";
			$ret = $this->mDb->getRow( $query, array( strtoupper( current( $pUserMixed ) ) ) );
		}
		return $ret;
	}

	// specify lookup where by hash key lik 'login' or 'user_id' or 'email'
	function getUserFromContentId( $content_id ) {
		$ret = NULL;
		if( !empty( $content_id ) ) {
			$query = "SELECT  `user_id` FROM `".BIT_DB_PREFIX."users_users`
					  WHERE `content_id` = ?";
			$tmpUser = $this->mDb->getRow( $query, array( $content_id ) );
			if (!empty($tmpUser['user_id'])) {
				$ret = $tmpUser['user_id'];
			}
		}
		return $ret;
	}
/*
	// all of these methods have been replaced by the single getUserInfo method
	function get_user_info($user, $iCaseSensitive = TRUE) {
		if (!$iCaseSensitive) {
			$query = "SELECT * FROM `".BIT_DB_PREFIX."users_users` where LOWER(`login`) = ?";
		} else {
			$query = "select * from `".BIT_DB_PREFIX."users_users` where `login`=?";
		}
		$result = $this->mDb->query($query,array($iCaseSensitive ? $user : strtolower($user)));
		$res = $result->fetchRow();
		$groups = $this->getGroups( $res['user_id'] );
		$res["groups"] = $groups;
		return $res;
	}
	function get_user_info_from_email($email) {
		$query = "select * from `".BIT_DB_PREFIX."users_users` where `email`=?";
		$result = $this->mDb->query($query,array($email));
		$res = $result->fetchRow();
		return $res;
	}
	function get_user_password($user) {
		$query = "select `password`  from `".BIT_DB_PREFIX."users_users` where " . $this->mDb->convert_binary(). " `login`=?";
		$pass = $this->mDb->getOne($query, array($user));
		return $pass;
	}
	function get_user_hash($user) {
		$query = "select `hash`  from `".BIT_DB_PREFIX."users_users` where " .
		$this->mDb->convert_binary(). " `login` = ?";
		$pass = $this->mDb->getOne($query, array($user));
		return $pass;
	}
*/
	function getByHash( $hash ) {
		$query = "select `user_id` from `".BIT_DB_PREFIX."users_users` where `hash`=?";
		return $this->mDb->getOne( $query, array($hash) );
	}

	// NULL password due means *no* expiration
	function isPasswordDue() {
		$ret = FALSE;
		if( $this->isRegistered() ) {
			// get user_id to avoid NULL and zero confusion
			$query = "SELECT `user_id`, `pass_due`
					  FROM `".BIT_DB_PREFIX."users_users`
					  WHERE `pass_due` IS NOT NULL AND `user_id`=? ";
			$due = $this->mDb->getAssoc( $query, array( $this->mUserId ) );
			if( !empty( $due['user_id'] ) ) {
				global $gBitSystem;
				$ret = $due['pass_due'] <= $gBitSystem->getUTCTime();
			}
		}
		return $ret;
	}
	function renew_user_password($user) {
		global $gBitSystem;
		$pass = BitSystem::genPass();
		$query = "select `email` from `".BIT_DB_PREFIX."users_users` where `login` = ?";
		$email = $this->mDb->getOne($query, array($user));
		$hash = md5(strtolower($user) . $pass . $email);
		// Note that tiki-generated passwords are due inmediatley
		$now = $gBitSystem->getUTCTime();
		$query = "update `".BIT_DB_PREFIX."users_users` set `password` = ?, `hash` = ?, `pass_due` = ? where ".$this->mDb->convert_binary()." `login` = ?";
		$result = $this->mDb->query($query, array($pass, $hash, $now, $user));
		return $pass;
	}

	function change_user_password( $user, $pass ) {
		global $gBitSystem;
		$query = "select `email` from `".BIT_DB_PREFIX."users_users` where `login` = ?";
		$email = $this->mDb->getOne($query, array($user));
		$email=trim($email);
		$hash = md5(strtolower($user) . $pass . $email);
		$now = $gBitSystem->getUTCTime();;
		$new_pass_due = $now + (60 * 60 * 24 * $gBitSystem->getPreference( 'pass_due' ) );
		if( !$gBitSystem->isFeatureActive( 'feature_clear_passwords' ) ) {
			$pass = '';
		}
		$query = "update `".BIT_DB_PREFIX."users_users` set `hash`=? ,`password`=? ,`pass_due`=? where " . $this->mDb->convert_binary(). " `login`=?";
		$result = $this->mDb->query($query, array($hash,$pass,$new_pass_due,$user));
		return TRUE;
	}

	function get_users($offset = 0, $maxRecords = -1, $sort_mode = 'login_desc', $find = '') {
		$sort_mode = $this->mDb->convert_sortmode($sort_mode);
		// Return an array of users indicating name, email, last changed pages, versions, last_login
		if ($find) {
			$findesc = '%' . strtoupper( $find ) . '%';
			$mid = " where UPPER(`login`) like ?";
			$bindvars = array($findesc);
		} else {
			$mid = '';
			$bindvars = array();
		}
		$query = "select * from `".BIT_DB_PREFIX."users_users` $mid order by $sort_mode";
		$query_cant = "select count(*) from `".BIT_DB_PREFIX."users_users`";
		$result = $this->mDb->query($query, $bindvars, $maxRecords, $offset);
		$cant = $this->mDb->getOne($query_cant, array());
		$ret = array();
		while( $res = $result->fetchRow() ) {
			//$res["groups"] = $this->get_user_groups( $res['login'] );
			$res["groups"] = $this->getGroups( $res['user_id'] );
			array_push( $ret, $res );
		}
		$retval = array();
		$retval["data"] = $ret;
		$retval["cant"] = $cant;
		return $retval;
	}
	/*shared*/
	function get_online_users() {
		global $gBitSystem;
		$query = "select ts.`user_id`, `login` AS `user`, `real_name` ,`timestamp`
				  FROM `".BIT_DB_PREFIX."tiki_sessions` ts INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (ts.`user_id`=uu.`user_id`)
				  WHERE ts.`user_id` IS NOT NULL";
		$result = $this->mDb->query($query);
		$ret = array();
		while ($res = $result->fetchRow()) {
			$res['user_information'] = 	$this->getPreference( 'user_information', 'public', $res['user_id'] );
			$ret[] = $res;
		}
		return $ret;
	}



	function canCustomizeTheme() {
		global $gBitSystem;
		return( $this->hasPermission( 'bit_p_custom_home_theme' ) || $gBitSystem->getPreference('feature_user_theme') == 'y' || $gBitSystem->getPreference('feature_user_theme') == 'h' );

	}



	function canCustomizeLayout() {
		global $gBitSystem;
		return( $this->hasPermission( 'bit_p_custom_home_layout' ) || $gBitSystem->getPreference('feature_user_layout') == 'y' || $gBitSystem->getPreference('feature_user_layout') == 'h' );
	}



	// ============= image and file functions

	function storePortrait( &$pStorageHash, $pGenerateAvatar=FALSE ) {
		if( !empty( $this->mUserId ) && count( $pStorageHash ) ) {
			// setup the hash for central storage functions
			$pStorageHash['upload']['max_width'] = PORTRAIT_MAX_DIM;
			$pStorageHash['upload']['max_height'] = PORTRAIT_MAX_DIM;
//			$pStorageHash['upload']['dest_base_name'] = 'portrait';
			$pStorageHash['upload']['dest_path'] = $this->getStorageBranch( 'self',$this->mUserId );
			$pStorageHash['storage_type'] = STORAGE_IMAGE;
			$pStorageHash['content_type_guid'] = BITUSER_CONTENT_TYPE_GUID;

			$pStorageHash['attachment_id'] = $this->mInfo['portrait_attachment_id'];
			if( $pGenerateAvatar ) {
				copy($pStorageHash['upload']['tmp_name'],$pStorageHash['upload']['tmp_name'].'.av');
			}

			if( LibertyAttachable::store( $pStorageHash ) ) {
				if($this->mInfo['portrait_attachment_id'] != $pStorageHash['attachment_id'] ) {
					$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `portrait_attachment_id` = ? WHERE `user_id`=?";
					$result = $this->mDb->query( $query, array( $pStorageHash['attachment_id'], $this->mUserId ) );
					$this->mInfo['portrait_attachment_id'] = $pStorageHash['attachment_id'];
					$pStorageHash['portrait_storage_path'] = $pStorageHash['upload']['dest_path'];
				}
				if( $pGenerateAvatar ) {
					$pStorageHash['upload']['tmp_name'] = $pStorageHash['upload']['tmp_name'].'.av';
					$this->storeAvatar( $pStorageHash );
				}
			} else {
				$this->mErrors['file'] = 'File '.$pStorageHash['name'].' could not be stored.';
			}
		}
		return( count( $this->mErrors ) == 0 );
	}


	function storeAvatar( &$pStorageHash ) {
		if( !empty( $this->mUserId ) && count( $pStorageHash ) ) {
			// setup the hash for central storage functions
			$pStorageHash['upload']['max_width'] = AVATAR_MAX_DIM;
			$pStorageHash['upload']['max_height'] = AVATAR_MAX_DIM;
//			$pStorageHash['upload']['dest_base_name'] = 'avatar';
			$pStorageHash['upload']['dest_path'] = $this->getStorageBranch( 'self',$this->mUserId );
			$pStorageHash['storage_type'] = STORAGE_IMAGE;
			$pStorageHash['content_type_guid'] = BITUSER_CONTENT_TYPE_GUID;

			$pStorageHash['attachment_id'] = $this->mInfo['avatar_attachment_id'];
			if( LibertyAttachable::store( $pStorageHash ) ) {
				if( $this->mInfo['avatar_attachment_id'] != $pStorageHash['attachment_id'] ) {
					$this->mInfo['avatar_storage_path'] = $pStorageHash['upload']['dest_path'];
					$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `avatar_attachment_id` = ? WHERE `user_id`=?";
					$result = $this->mDb->query( $query, array( $pStorageHash['attachment_id'], $this->mUserId ) );
					$this->mInfo['avatar_attachment_id'] = $pStorageHash['attachment_id'];
				}
			} else {
				$this->mErrors['file'] = 'File '.$pStorageHash['upload']['name'].' could not be stored.';
			}
		}
		return( count( $this->mErrors ) == 0 );
	}


	function storeLogo( &$pStorageHash ) {
	if( !empty( $this->mUserId ) && count( $pStorageHash ) ) {
			// setup the hash for central storage functions
			$pStorageHash['upload']['max_width'] = LOGO_MAX_DIM;
			$pStorageHash['upload']['max_height'] = LOGO_MAX_DIM;
			$pStorageHash['upload']['dest_path'] = $this->getStorageBranch( 'self',$this->mUserId );
			$pStorageHash['storage_type'] = STORAGE_IMAGE;
			$pStorageHash['content_type_guid'] = BITUSER_CONTENT_TYPE_GUID;

			$pStorageHash['attachment_id'] = $this->mInfo['logo_attachment_id'];

			if( LibertyAttachable::store( $pStorageHash ) ) {
				if($this->mInfo['logo_attachment_id'] != $pStorageHash['attachment_id'] ) {
					$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `logo_attachment_id` = ? WHERE `user_id`=?";
					$result = $this->mDb->query( $query, array( $pStorageHash['attachment_id'], $this->mUserId ) );
					$this->mInfo['logo_attachment_id'] = $pStorageHash['attachment_id'];
				}
			} else {
				$this->mErrors['file'] = 'File '.$pStorageHash['name'].' could not be stored.';
			}
		}
		return( count( $this->mErrors ) == 0 );
	}

	function purgeImage( $pType ) {
		if( !empty( $this->mUserId ) && !empty( $this->mInfo[$pType.'_attachment_id'] ) ) {
			$this->mDb->StartTrans();
			$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `".$pType."_attachment_id` = NULL WHERE `user_id`=?";
			$result = $this->mDb->query( $query, array( $this->mUserId ) );
			if( file_exists( $this->mInfo[$pType.'_storage_path'] ) ) {
				unlink( $this->mInfo[$pType.'_storage_path'] );
			}
			unset( $this->mInfo[$pType.'_storage_path'] );
			unset( $this->mInfo[$pType.'_attachment_id'] );
			unset( $this->mInfo[$pType.'_url'] );
			$this->mDb->CompleteTrans();
		}
	}


	function purgePortrait() {
		$this->purgeImage( 'portrait' );
	}


	function purgeAvatar() {
		$this->purgeImage( 'avatar' );
	}


	function purgeLogo() {
		$this->purgeImage( 'logo' );
	}

	// Get a list of attachments this user owns
	function getUserFiles() {
		global $gLibertySystem;
		$ret = array();
		$ret['files'] = NULL;
		$ret['diskUsage'] = 0;

		if ($this->mUserId) {
			$query = "SELECT ta.`attachment_id`, ta.`foreign_id`
					  FROM `".BIT_DB_PREFIX."tiki_attachments` ta
					  WHERE ta.`user_id` = ? AND ta.`attachment_plugin_guid` = 'tiki_files'";
			$result = $this->mDb->query($query, array($this->mUserId));
			$attachmentIds = $result->getRows();

			$bit_files_load_func = $gLibertySystem->getPluginFunction( 'bitfile', 'load_function'  );
			if ($bit_files_load_func && count($attachmentIds) > 0) {
				$files = array();
				foreach ($attachmentIds as $attachmentId) {
					if ($attachmentId != $this->mInfo['portrait_attachment_id'] && $attachmentId != $this->mInfo['avatar_attachment_id'] && $attachmentId != $this->mInfo['logo_attachment_id']) {
						$fileInfo = $bit_files_load_func($attachmentId);
						$ret['diskUsage'] += $fileInfo['size'];
						$files[] = $fileInfo;
					}
				}
				$ret['files'] = $files;
			}
		}
		return $ret;
	}

	function getUserAttachments( &$pListHash ) {
		global $gLibertySystem;
		LibertyContent::prepGetList( $pListHash );

		$ret = NULL;
		$bindVars[] = $this->mUserId;
		$mid = '';

		if( $this->mUserId ) {
			$query = "SELECT DISTINCT ON( ta.foreign_id, ta.attachment_plugin_guid ) ta.*
				FROM `".BIT_DB_PREFIX."tiki_attachments` ta
				WHERE ta.`user_id` = ? $mid";
			$result = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] );
			$attachments = $result->getRows();
			$data = array();
			foreach( $attachments as $attachment ) {
				$loadFunc = $gLibertySystem->getPluginFunction( $attachment['attachment_plugin_guid'], 'load_function' );
				$data[] = $loadFunc( $attachment );
			}
			$ret['data'] = $data;

			// count all entries
			$queryc = "SELECT DISTINCT ON( ta.foreign_id, ta.attachment_plugin_guid ) ta.*
				FROM `".BIT_DB_PREFIX."tiki_attachments` ta
				WHERE ta.`user_id` = ? $mid";
			$result = $this->mDb->query( $queryc, $bindVars );
			$ret['cant'] = count( $result->getRows() );
		}
		return $ret;
	}

	// ============= watch functions
	/*shared*/
	function storeWatch( $event, $object, $type, $title, $url ) {
		global $userlib;
		if( $this->isValid() ) {
			$hash = md5(uniqid('.'));
			$query = "delete from `".BIT_DB_PREFIX."tiki_user_watches` where `user_id`=? and `event`=? and `object`=?";
			$this->mDb->query($query,array( $this->mUserId, $event, $object ) );
			$query = "insert into `".BIT_DB_PREFIX."tiki_user_watches`(`user_id` ,`event` ,`object` , `email`, `hash`, `type`, `title`, `url`) ";
			$query.= "values(?,?,?,?,?,?,?,?)";
			$this->mDb->query( $query, array( $this->mUserId, $event, $object, $this->mInfo['email'], $hash, $type, $title, $url ) );
			return true;
		}
	}

	function getWatches( $pEvent = '' ) {
		$ret = NULL;
		if( !empty( $this->mUserId ) ) {
			$mid = '';
			$bindvars=array( $this->mUserId );
			if ($pEvent) {
				$mid = " and `event`=? ";
				$bindvars[]=$pEvent;
			}

			$query = "select * from `".BIT_DB_PREFIX."tiki_user_watches` where `user_id`=? $mid";
			$result = $this->mDb->query($query,$bindvars);
			$ret = array();

			while ($res = $result->fetchRow()) {
				$ret[] = $res;
			}
		}
		return $ret;
	}

	/*shared*/
	function getEventWatches( $event, $object ) {
		$ret = NULL;
		if( $this->isValid() ) {
			$query = "select * from `".BIT_DB_PREFIX."tiki_user_watches` WHERE `user_id`=? and `event`=? and `object`=?";
			$result = $this->mDb->query($query,array( $this->mUserId, $event, $object ) );
			if ( $result->numRows() ) {
				$ret = $result->fetchRow();
			}
		}
		return $ret;
	}

	/*shared*/
	function get_event_watches($event, $object) {
		$ret = array();

		$query = "select * from `".BIT_DB_PREFIX."tiki_user_watches` tw INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON ( tw.`user_id`=uu.`user_id` )  where `event`=? and `object`=?";
		$result = $this->mDb->query($query,array($event,$object));

		if (!$result->numRows())
		return $ret;

		while ($res = $result->fetchRow()) {
		$ret[] = $res;
		}

		return $ret;
	}

	/*shared*/
	function remove_user_watch_by_hash($hash) {
		$query = "delete from `".BIT_DB_PREFIX."tiki_user_watches` where `hash`=?";
		$this->mDb->query($query,array($hash));
	}

	/*shared*/
	function expungeWatch( $event, $object ) {
		if( $this->isValid() ) {
			$query = "delete from `".BIT_DB_PREFIX."tiki_user_watches` where `user_id`=? and `event`=? and `object`=?";
			$this->mDb->query( $query, array( $this->mUserId, $event, $object ) );
		}
	}

	/*shared*/
	function get_watches_events() {
		$query = "select distinct `event` from `".BIT_DB_PREFIX."tiki_user_watches`";
		$result = $this->mDb->query($query,array());
		$ret = array();
		while ($res = $result->fetchRow()) {
		$ret[] = $res['event'];
		}
		return $ret;
	}


	function getUserId() {
		return( !empty( $this->mUserId ) ? $this->mUserId : ANONYMOUS_USER_ID );
	}

	function getDisplayUrl( $pUserName=NULL, $pMixed=NULL ) {
		if( empty( $pUserName ) && !empty( $this ) ) {
			$pUserName = $this->mUsername;
		}
		if( function_exists( 'override_user_url' ) ) {
		    $ret = override_user_url( $pUserName );
		} else {
			global $gBitSystem;

			$rewrite_tag = $gBitSystem->isFeatureActive( 'feature_pretty_urls_extended' ) ? 'view/':'';

			if ($gBitSystem->isFeatureActive( 'pretty_urls' )
			|| $gBitSystem->isFeatureActive( 'feature_pretty_urls_extended' ) ) {
				$ret =  USERS_PKG_URL . $rewrite_tag;
				$ret .= urlencode( $pUserName );
			}
			else {
				$ret =  USERS_PKG_URL . 'index.php?home=';
				$ret .= urlencode( $pUserName );
			}
		}
		return $ret;
	}

	function getDisplayLink( $pUserName, $pDisplayHash ) {
		return BitUser::getDisplayName( TRUE, $pDisplayHash );
	}

	function getTitle( $pHash = NULL ) {
		return $this->getDisplayName( FALSE, $pHash );
	}

    /**
* Get user information for a particular user
*
* @param pUseLink return the information in the form of a url that links to the users information page
* @param pHash todo - need explanation on how to use this...
* @return display name or link to user information page
**/
	function getDisplayName($pUseLink = FALSE, $pHash=NULL) {
		global $gBitSystem;
		$ret = NULL;
		if( empty( $pHash ) && !empty( $this->mInfo ) ) {
			$pHash = &$this->mInfo;
		}
		if( !empty( $pHash ) ) {
			$displayName = (((!empty($pHash['real_name']) && $gBitSystem->getPreference( 'display_name', 'real_name' ) == 'real_name') ? $pHash['real_name'] :
							(!empty($pHash['user']) ? $pHash['user'] :
							(!empty($pHash['login']) ? $pHash['login'] :
							(!empty($pHash['email']) ? substr($pHash['email'],0, strpos($pHash['email'],'@')) : $pHash['user_id'])))));
			if (!empty($pHash['user'])) {
				$iHomepage = $pHash['user'];
			} elseif (!empty($pHash['login'])) {
				// user of 'login' is deprecated and eventually should go away!
				$iHomepage = $pHash['login'];
			} elseif (!empty($pHash['user_id'])) {
				$iHomepage = $pHash['user_id'];
			} elseif (!empty($pHash['email'])) {
				$iHomepage = $pHash['email'];
			} else {
				// this won't work right now, we need to alter userslib::interpret_home() to interpret a real name
				$iHomepage = $pHash['real_name'];
			}

			if( $pUseLink ) {
				$ret = '<a class="username" title="'.tra( 'Visit the userpage of' ).': '.$displayName.'" href="'.BitUser::getDisplayUrl( $iHomepage ).'">'.$displayName.'</a>';
			} else {
				$ret = $displayName;
			}
		} else {
			$ret = "Anonymous";
		}
		return $ret;

	}

    /**
    * Returns include file that will
    * @return the fully specified path to file to be included
    */
	function getRenderFile() {
		return USERS_PKG_PATH."display_bituser_inc.php";
	}

	function storeRealName($newRealName) {
		if (strlen($newRealName) > REAL_NAME_COL_SIZE) {
			$newRealName = substr($newRealName,0,REAL_NAME_COL_SIZE);
		}
		if ($this->mUserId) {
			$sql = "UPDATE `".BIT_DB_PREFIX."users_users` SET `real_name` = ? WHERE `user_id` = ?";
			$this->mDb->query($sql, array($newRealName, $this->mUserId));
		}
	}

	function storeLogin($newLogin) {
		$newLogin = substr($newLogin,0,40);
		if ($this->userExists(array('login' => $newLogin))) {
			$this->mErrors[] = "The username '$newLogin' is already taken";
		} elseif ($this->mUserId) {
			$sql = "UPDATE `".BIT_DB_PREFIX."users_users` SET `login` = ? WHERE `user_id` = ?";
			$rs = $this->mDb->query($sql, array($newLogin, $this->mUserId));
		} else {
			$this->mErrors[] = "Invalid user";
		}

		return (count($this->mErrors) == 0);
	}

	function getList( &$pParamHash ) {

		if ( !isset( $pParamHash['sort_mode']) or $pParamHash['sort_mode'] == '' ) $pParamHash['sort_mode'] = 'registration_date_desc';
		$pParamHash['max_records'] = 20;
		LibertyContent::prepGetList( $pParamHash );
		$sort_mode = $this->mDb->convert_sortmode($pParamHash['sort_mode']);
		// Return an array of users indicating name, email, last changed pages, versions, last_login
		if ( $pParamHash['find'] ) {
			$mid = " where UPPER(uu.`login`) LIKE ? OR UPPER(uu.real_name) LIKE ? OR UPPER(uu.email) LIKE ? ";
			$bindvars = array('%'.strtoupper( $pParamHash['find'] ).'%', '%'.strtoupper( $pParamHash['find'] ).'%', '%'.strtoupper( $pParamHash['find'] ).'%');
		} else {
			$mid = '';
			$bindvars = array();
		}
		$query = "SELECT uu.*, tf_ava.`storage_path` AS `avatar_storage_path`
				  FROM `".BIT_DB_PREFIX."users_users` uu
					LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_attachments` ta_ava ON ( uu.`avatar_attachment_id`=ta_ava.`attachment_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."tiki_files` tf_ava ON ( tf_ava.`file_id`=ta_ava.`foreign_id` )
				 $mid order by $sort_mode";
		$query_cant = "select count(*) from `".BIT_DB_PREFIX."users_users` uu $mid";
		$result = $this->mDb->query($query, $bindvars, $pParamHash['max_records'], $pParamHash['offset']);

		$ret = array();
		while( $res = $result->fetchRow() ) {
			if( !empty($res['avatar_storage_path'] ) ) {
				$res['avatar_url'] = BIT_ROOT_URL.$res['avatar_storage_path'];
				$res['thumbnail_url'] = dirname( $res['avatar_url'] ).'/small.jpg';
			}
			$res["groups"] = $this->getGroups( $res['user_id'] );
			array_push( $ret, $res );
		}
		$retval = array();
		$pParamHash["data"] = $ret;

		$pParamHash["cant"] = $this->mDb->getOne($query_cant,$bindvars);

		LibertyContent::postGetList( $pParamHash );
	}

	function isSemaphoreSet( $pSemName, $pLimit ) {
		global $gBitSystem;
		$now = $gBitSystem->getUTCTime();
		$lim = $now - $pLimit;
		$query = "delete from `".BIT_DB_PREFIX."tiki_semaphores` where `sem_name`=? and `created`<?";
		$result = $this->mDb->query($query,array( $pSemName, (int)$lim) );
		$query = "select `sem_name`  from `".BIT_DB_PREFIX."tiki_semaphores` where `sem_name`=?";
		$result = $this->mDb->query($query,array($pSemName));
		return $result->numRows();
	}

	function hasSemaphoreConflict( $pSemName, $pLimit ) {
		global $gBitSystem;
		$ret = NULL;
		$userId = $this->isValid() ? $this->mUserId : ANONYMOUS_USER_ID;
		$now = $gBitSystem->getUTCTime();
		$lim = $now - $pLimit;
		$query = "delete from `".BIT_DB_PREFIX."tiki_semaphores` where `sem_name`=? and `created`<?";
		$result = $this->mDb->query($query,array( $pSemName, (int)$lim) );
		$query = "SELECT uu.`login`, uu.`real_name`, uu.`email`, uu.`user_id`
				  FROM `".BIT_DB_PREFIX."tiki_semaphores` ts INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON( uu.`user_id`=ts.`user_id`)
				  WHERE `sem_name`=? AND ts.`user_id`!='?'";
		$result = $this->mDb->query( $query, array( $pSemName, (int)$userId ) );
		if( $result->fields ) {
			$ret = $result->fields;
			$ret['nolink'] = TRUE;
		}
		return( $ret );
	}

	function storeSemaphore( $pSemName ) {
		if( !empty( $pSemName ) ) {
			global $gBitSystem;
			$userId = $this->isValid() ? $this->mUserId : ANONYMOUS_USER_ID;
			$now = $gBitSystem->getUTCTime();
			//	$cant=$this->mDb->getOne("select count(*) from `".BIT_DB_PREFIX."tiki_semaphores` where `sem_name`='$pSemName'");
			$query = "delete from `".BIT_DB_PREFIX."tiki_semaphores` where `sem_name`=?";
			$this->mDb->query($query,array($pSemName));
			$query = "insert into `".BIT_DB_PREFIX."tiki_semaphores`(`sem_name`,`created`,`user_id`) values(?,?,?)";
			$result = $this->mDb->query($query,array($pSemName, (int)$now, $userId));
			return $now;
		}
	}

	// PURE VIRTUAL FUNCTIONS
	function getGroups () {
		print "CALL TO PURE VIRTUAL FUNCTIONS"; bt(); die;
	}

	function userExists( $pUserMixed ) {
		$ret = FALSE;
		if ( is_array( $pUserMixed ) ) {
			if( $cur = current( $pUserMixed ) ) {
				$query = "SELECT `user_id` FROM `".BIT_DB_PREFIX."users_users` WHERE UPPER(`".key( $pUserMixed )."`) = ?";
				$ret = $this->mDb->getOne( $query, array( strtoupper( $cur ) ) );
			}
		}
		return $ret;
	}


}

function scrambleEmail($email, $method='unicode') {
	switch ($method) {
		case 'strtr':
			$trans = array(	"@" => tra(" AT "),
							"." => tra(" DOT ")
			);
			$ret = strtr($email, $trans);
			break;
		case 'x' :
			$encoded = $email;
			for ($i = strpos($email, "@") + 1; $i < strlen($email); $i++) {
				if ($encoded[$i]  != ".") $encoded[$i] = 'x';
			}
			$ret = $encoded;
			break;
		case 'unicode':
		case 'y':// for previous compatibility
			$encoded = '';
			for ($i = 0; $i < strlen($email); $i++) {
				$encoded .= '&#' . ord($email[$i]). ';';
			}
			$ret = $encoded;
			break;
		default:
			$ret = NULL;
			break;
	}
	return $ret;
}

?>
