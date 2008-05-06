<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/BitUser.php,v 1.177 2008/05/06 08:27:15 squareing Exp $
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
 * $Id: BitUser.php,v 1.177 2008/05/06 08:27:15 squareing Exp $
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
 * @version  $Revision: 1.177 $
 * @package  users
 * @subpackage  BitUser
 */
class BitUser extends LibertyAttachable {
	var $mUserId;
	var $mUsername;
	var $mGroups;
	var $mInfo;
	var $mTicket;
	var $mAuth;

	/**
* Constructor - will automatically load all relevant data if passed a user string
*
* @access public
* @author Christian Fowler <spider@viovio.com>
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
		$this->mUserId = ( @$this->verifyId( $pUserId ) ? $pUserId : NULL);
		$this->mContentId = $pContentId;
	}

	/**
	* load - loads all settings & preferences for this user
	*
	* @access public
	* @author Chrstian Fowler <spider@steelsun.com>
	* @return returnString
	*/
	function load( $pFull=FALSE, $pUserName=NULL ) {
		global $gBitSystem;
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
				$fullSelect = ' , lc.* ';
				$fullJoin = " LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON ( uu.`content_id`=lc.`content_id` )";
				$this->getServicesSql( 'content_load_sql_function', $fullSelect, $fullJoin, $whereSql, $bindVars );
			}
			// uu.`user_id` AS `uu_user_id` is last and aliases to avoid possible column name collisions
			$query = "select uu.*, tf_ava.`storage_path` AS `avatar_storage_path`, tf_por.`storage_path` AS `portrait_storage_path`, tf_logo.`storage_path` AS `logo_storage_path`  $fullSelect, uu.`user_id` AS `uu_user_id`
					  FROM `".BIT_DB_PREFIX."users_users` uu
						LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_attachments` ta_ava ON ( uu.`avatar_attachment_id`=ta_ava.`attachment_id` )
						LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_files` tf_ava ON ( tf_ava.`file_id`=ta_ava.`foreign_id` )
						LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_attachments` ta_por ON ( uu.`portrait_attachment_id`=ta_por.`attachment_id` )
						LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_files` tf_por ON ( tf_por.`file_id`=ta_por.`foreign_id` )
						LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_attachments` ta_logo ON ( uu.`logo_attachment_id`=ta_logo.`attachment_id` )
						LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_files` tf_logo ON ( tf_logo.`file_id`=ta_logo.`foreign_id` )
						$fullJoin
					  $whereSql";

				if( ($result = $this->mDb->query( $query, $bindVars )) && $result->numRows() ) {
				$this->mInfo = $result->fetchRow();
				$this->mInfo['user'] = $this->mInfo['login'];
				$this->mInfo['valid'] = @$this->verifyId( $this->mInfo['uu_user_id'] );
				$this->mInfo['user_id'] = $this->mInfo['uu_user_id'];
				$this->mUserId = $this->mInfo['uu_user_id'];
				$this->mContentId = $this->mInfo['content_id'];
				$this->mUsername = $this->mInfo['login'];
				$this->mInfo['is_registered'] = $this->isRegistered();
				$this->mInfo['avatar_url'] = liberty_fetch_thumbnail_url( $this->mInfo['avatar_storage_path'], 'avatar');
				$this->mInfo['portrait_url'] = liberty_fetch_thumbnail_url( $this->mInfo['portrait_storage_path'], 'medium');
				$this->mInfo['logo_url'] = (!empty($this->mInfo['logo_storage_path']) ? BIT_ROOT_URL.$this->mInfo['logo_storage_path'] : NULL);
				$this->mInfo['avatar_path'] = (!empty($this->mInfo['avatar_storage_path']) ? BIT_ROOT_PATH.$this->mInfo['avatar_storage_path'] : NULL);
				$this->mInfo['avatar_path'] = (!empty($this->mInfo['portrait_storage_path']) ? BIT_ROOT_PATH.$this->mInfo['portrait_storage_path']: NULL);
				$this->mInfo['avatar_path'] = (!empty($this->mInfo['logo_storage_path']) ? BIT_ROOT_PATH.$this->mInfo['logo_storage_path'] : NULL);
				// a few random security conscious unset's - SPIDER
				unset( $this->mInfo['user_password'] );
				unset( $this->mInfo['hash'] );
				$this->loadPreferences();
				// Load attachments
				LibertyAttachable::load();
				if( $this->getPreference( 'users_country' ) ) {
					$this->setPreference( 'flag', $this->getPreference( 'users_country' ) );
					$this->setPreference( 'users_country', str_replace( '_', ' ', $this->getPreference( 'users_country' ) ) );
				}
				if( $pFull ) {
					$this->mInfo['real_name'] = trim($this->mInfo['real_name']);
					$this->mInfo['display_name'] = ((!empty($this->mInfo['real_name']) ? $this->mInfo['real_name'] :
					(!empty($this->mUsername) ? $this->mUsername :
					(!empty($this->mInfo['email']) ? substr($this->mInfo['email'],0, strpos($this->mInfo['email'],'@')) :
					$this->mUserId))));
					//print("displayName: ".$this->mInfo['display_name']);
					$this->defaults();
					$this->mInfo['publicEmail'] = scrambleEmail( $this->mInfo['email'], ( $this->getPreference( 'users_email_display' ) ? $this->getPreference( 'users_email_display' ) : NULL ) );
				}
				$this->mTicket = substr( md5( session_id() . $this->mUserId ), 0, 20 );
			} else {
				$this->mUserId = NULL;
			}
		}
		if ( !$gBitSystem->isFeatureActive( 'i18n_browser_languages' ) ) {
			global $gBitLanguage;
			if ( $this->mUserId && $this->mUserId != -1 )
				$gBitLanguage->mLanguage = $this->getPreference( 'bitlanguage', $gBitLanguage->mLanguage );
			else if (isset($_SESSION['bitlanguage'])) {
					// users not logged that change the preference
					$gBitLanguage->mLanguage = $_SESSION['bitlanguage'];
				}  
		}
		return( $this->isValid() );
	}

	function defaults() {
		global $gBitSystem, $gBitThemes;
		if( !$this->getPreference( 'users_information' ) ) { $this->setPreference( 'users_information', 'public' ); }
		if( !$this->getPreference( 'messages_allow_messages' ) ) { $this->setPreference( 'messages_allow_messages', 'y' ); }
		if( !$this->getPreference( 'site_display_utc' ) ) {
			$this->setPreference( 'site_display_utc', 'Local' );
		}
		if( !$this->getPreference( 'site_display_timezone' ) ) {
			$server_time = new BitDate();
			$this->setPreference( 'site_display_timezone', $server_time->display_offset );
		}
		if( !$this->getPreference( 'bitlanguage' ) ) {
			global $gBitLanguage;
			$this->setPreference( 'bitlanguage', $gBitLanguage->mLanguage );
		}
		if( !$this->getPreference( 'theme' ) ) {
			global $site_style;
			$this->setPreference( 'theme', $gBitThemes->getStyle() );
		}
	}


	// =-=-=-=-=-=-=-=-=-=-=-=-=-= Session & Authentication Related Functions


	function updateSession( $pSessionId ) {
		global $gLightWeightScan;
		if ( !$this->isDatabaseValid() ) return true;
		global $gBitSystem, $gBitUser;
		$update['last_get'] = $gBitSystem->getUTCTime();
		$update['current_view'] = $_SERVER['PHP_SELF'];

		if( empty( $gLightWeightScan ) ) {
			$this->mDb->StartTrans();
			$row = $this->mDb->getRow( "SELECT `last_get`, `connect_time`, `get_count`, `user_agent`, `current_view` FROM `".BIT_DB_PREFIX."users_cnxn` WHERE `cookie`=? ", array( $pSessionId ) );
			if( $gBitUser->isRegistered() ) {
				$update['user_id'] = $gBitUser->mUserId;
			}
			if( $row ) {
				if( empty( $row['ip'] ) || $row['ip'] != $_SERVER['REMOTE_ADDR'] ) {
					$update['ip'] = $_SERVER['REMOTE_ADDR'];
				}
				if( !empty( $_SERVER['HTTP_USER_AGENT'] ) && (empty( $row['user_agent'] ) || $row['user_agent'] != $_SERVER['HTTP_USER_AGENT']) ) {
					$update['user_agent'] = substr( $_SERVER['HTTP_USER_AGENT'], 0, 128 );
				}
				$update['get_count'] = $row['get_count'] + 1;
				$ret = $this->mDb->associateUpdate( BIT_DB_PREFIX.'users_cnxn', $update, array( 'cookie' => $pSessionId ) );
			} else {
				if( $this->isRegistered() ) {
					$update['user_id'] = $this->mUserId;
					$update['ip'] = $_SERVER['REMOTE_ADDR'];
					$update['user_agent'] = (string)substr( $_SERVER['HTTP_USER_AGENT'], 0, 128 );
					$update['get_count'] = 1;
					$update['cookie'] = $pSessionId;
					$result = $this->mDb->associateInsert( BIT_DB_PREFIX.'users_cnxn', $update );
				}
			}
			// Delete old connections nightly during the hour of 3 am
			if( date( 'H' ) == '03' && date( 'i' ) > 0 &&  date( 'i' ) < 2 ) {
				// Default to 30 days history
				$oldy = $update['last_get'] - ($gBitSystem->getConfig( 'users_cnxn_history_days', 30 ) * 24 * 60);
				$query = "DELETE from `".BIT_DB_PREFIX."users_cnxn` where `connect_time`<?";
				$result = $this->mDb->query($query, array($oldy));
			}
			$this->mDb->CompleteTrans();
		}
		return true;
	}

	function count_sessions($pActive = FALSE) {
		$query = "select count(*) from `".BIT_DB_PREFIX."users_cnxn`";
		if ($pActive) {
			$query .=" WHERE `cookie` IS NOT NULL";
		}
		$cant = $this->mDb->getOne($query,array());
		return $cant;
	}

	function logout() {
		global $user_cookie_site, $gBitSystem;

		if( !empty( $_COOKIE[$user_cookie_site] ) ) {
			$this->mDb->query( "UPDATE `".BIT_DB_PREFIX."users_cnxn` SET `cookie`=NULL WHERE `cookie`=?", array( $_COOKIE[$user_cookie_site] ) );
		}
		$cookie_time = time() - 3600;
		$cookie_path = BIT_ROOT_URL;
		$cookie_domain = "";
		// Now if the remember me feature is on and the user checked the user_remember_me checkbox then ...
		if ($gBitSystem->isFeatureActive( 'users_remember_me' ) && isset($_REQUEST['rme']) && $_REQUEST['rme'] == 'on') {
			$cookie_time = (int)(time() + $gBitSystem->getConfig( 'users_remember_time', 86400 ));
			$cookie_path = $gBitSystem->getConfig('cookie_path', $cookie_path);
			$cookie_domain = $gBitSystem->getConfig('cookie_domain', $cookie_domain);
		}
		setcookie( $user_cookie_site, '', $cookie_time , $cookie_path, $cookie_domain );
		//session_unregister ('user');
		session_destroy();
		$this->mUserId = NULL;
		// ensure Guest default page is loaded if required
		$this->mInfo['default_group_id'] = -1;
	}

	function isRegistered() {
		return ( $this->mUserId > ANONYMOUS_USER_ID );
	}

	function isValid() {
		return ( $this->verifyId( $this->mUserId ) );
	}

	function isAdmin() {
		//	print "PURE VIRTUAL BASE FUNCTION";
		return FALSE;
	}

	function verifyTicket( $pFatalOnError=TRUE ) {
		global $gBitSystem, $gBitUser;
		$ret = FALSE;
		if( !empty( $_REQUEST['tk'] ) ) {
			if( !($ret = $_REQUEST['tk'] == $this->mTicket ) && $pFatalOnError ) {
				$userString = $gBitUser->isRegistered() ? "\nUSER ID: ".$gBitUser->mUserId.' ( '.$gBitUser->getField( 'email' ).' ) ' : '';
				@error_log( tra( "Security Violation" )."$userString ".$_SERVER['REMOTE_ADDR']."\nURI: $_SERVER[REQUEST_URI] \nREFERER: $_SERVER[HTTP_REFERER] " );
				$gBitSystem->fatalError( tra( "Security Violation" ));
			}
		}
		return $ret;
	}

	function verify( &$pParamHash ) {
		global $gBitSystem;

		trim_array( $pParamHash );

		// DO NOT REMOVE - to allow specific setting of the user_id during the first store.
		// used by ROOT_USER_ID or ANONYMOUS_USER_ID during install. 	 
		if( @$this->verifyId( $pParamHash['user_id'] ) ) { 	 
			$pParamHash['user_store']['user_id'] = $pParamHash['user_id']; 	 
		} 	 
		if( !empty( $pParamHash['login'] ) ) {
			if( $this->userExists( array( 'login' => $pParamHash['login'] ) ) ) {
				$this->mErrors['login'] = 'The username "'.$pParamHash['login'].'" is already in use';
			} elseif( preg_match( '/[^A-Za-z0-9_.-]/', $pParamHash["login"] ) ) {
				$this->mErrors['login'] = tra( "Your username can only contain numbers, characters, underscores and hyphens." );
			} else {
				// LOWER CASE all logins
				$pParamHash['login'] = strtolower( $pParamHash['login'] );
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
			if( $gBitSystem->isFeatureActive( 'users_validate_user' ) ) {
				$pParamHash['user_store']['provpass'] = md5(BitSystem::genPass());
				$pParamHash['pass_due'] = 0;
			} elseif( empty( $pParamHash['password'] ) ) {
				$this->mErrors['password'] = tra( 'Your password should be at least '.$gBitSystem->getConfig( 'users_min_pass_length', 4 ).' characters long' );
			}
		} elseif( $this->isValid() ) {
			// Prevent loosing user info on save
			if( empty( $pParamHash['edit'] ) ) {
				$pParamHash['edit'] = $this->mInfo['data'];
			}
		}

		if( isset( $pParamHash['password'] ) ) {
			if (!$this->isValid() || isset($pParamHash['password']) ) {
				$passswordError = $this->verifyPasswordFormat( $pParamHash['password'] );
			}
			if( !empty( $passswordError ) ) {
				$this->mErrors['password'] = $passswordError;
			} else {
				// Generate a unique hash
				//$pParamHash['user_store']['hash'] = md5( strtolower( (!empty($pParamHash['login'])?$pParamHash['login']:'') ).$pPassword.$pParamHash['email'] );
				$pParamHash['user_store']['hash'] = md5( $pParamHash['password'] );
				$now = $gBitSystem->getUTCTime();
				if( !isset( $pParamHash['pass_due'] ) && $gBitSystem->getConfig('users_pass_due') ) {
					$pParamHash['user_store']['pass_due'] = $now + (60 * 60 * 24 * $gBitSystem->getConfig('users_pass_due') );
				} elseif( isset( $pParamHash['pass_due'] ) ) {
					// renew password only next half year ;)
					$pParamHash['user_store']['pass_due'] = $now + (60 * 60 * 24 * $pParamHash['pass_due']);
				}
				if( $gBitSystem->isFeatureActive( 'users_clear_passwords' ) || !empty( $pParamHash['user_store']['provpass'] ) ) {
					$pParamHash['user_store']['user_password'] = $pParamHash['password'];
				}
			}
		}
		return ( count($this->mErrors) == 0 );
	}

	function verifyPasswordFormat( $pPassword, $pPassword2=NULL ) {
		global $gBitSystem;

		$minPassword = $gBitSystem->getConfig( 'users_min_pass_length', 4 );
		if( strlen( $pPassword ) < $minPassword ) {
			return ( tra( 'Your password should be at least '.$minPassword.' characters long' ) );
			}
		if( !empty( $pPassword2 ) && ($pPassword != $pPassword2) ) {
			return( tra( 'The passwords do not match' ) );
			}
		if( $gBitSystem->isFeatureActive( 'users_pass_chr_num' ) &&
		(!preg_match_all( "/[0-9]+/",$pPassword,$foo ) || !preg_match_all("/[A-Za-z]+/",$pPassword,$foo)) ) {
			return ( tra( 'Password must contain both letters and numbers' ) );
			}

		return FALSE;
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

		if( !empty( $this ) ) {
			$errors = &$this->mErrors;
		} else {
			$errors = array();
		}
		if( !validate_email_syntax ( $pEmail ) ) {
			$errors['email'] = 'The email address "'.$pEmail.'" is invalid.';
		} elseif( !empty( $this ) && is_object( $this ) && $this->userExists( array( 'email' => $pEmail ) ) ) {
			$errors['email'] = 'The email address "'.$pEmail.'" has already been registered.';
		} elseif( $gBitSystem->isFeatureActive( 'users_validate_email' ) ) {
			if( !$this->verifyMX( $pEmail, $pValidate ) ) {
				$errors['email'] = 'Cannot find a valid MX host';
			}
		}
		return( count( $errors ) == 0 );
	}

	function verifyMX( $pEmail, $pValidate = FALSE ) {
		global $gBitSystem, $gDebug;
		$HTTP_HOST=$_SERVER['SERVER_NAME'];

		if( !empty( $this ) ) {
			$errors = &$this->mErrors;
		} else {
			$errors = array();
		}

		list ( $Username, $domain ) = split ("@",$pEmail);
		// That MX(mail exchanger) record exists in domain check .
		// checkdnsrr function reference : http://www.php.net/manual/en/function.checkdnsrr.php
		if ( !is_windows() and checkdnsrr ( $domain, "MX" ) )  {
			if($gDebug) echo "Confirmation : MX record about {$domain} exists.<br>";
			// If MX record exists, save MX record address.
			// getmxrr function reference : http://www.php.net/manual/en/function.getmxrr.php

			// Sometimes only the highest priority MX are active
			$MXWeights = array();
			$lowest_weight = 99999;
			$lowest_weight_index = 0;
			if ( getmxrr ($domain, $MXHost, $MXWeights) )  {
				for ($i = 0; $i < count( $MXHost ); $i++ ) {
					if ( $MXWeights[$i] < $lowest_weight ) {
						$lowest_weight = $MXWeights[$i];
						$lowest_weight_index = $i;
					}
				}
				if($gDebug) {
					echo "Confirmation : Is confirming address by MX LOOKUP.<br>";
					for ( $i = 0,$j = 1; $i < count ( $MXHost ); $i++,$j++ ) {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Result($j) - $MXHost[$i]<BR>";
					}
				}
			}
			// Getmxrr function does to store MX record address about $domain in arrangement form to $MXHost.
			// $ConnectAddress socket connection address.
			$ConnectAddress = $MXHost[$lowest_weight_index];
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
				stream_set_timeout($Connect, 90);
				$Out = $this->get_SMTP_response( $Connect );
				if ( ereg ( "^220", $Out ) ) {
					// Inform client's reaching to server who connect.
					if( $gBitSystem->hasValidSenderEmail() ) {
						$senderEmail = $gBitSystem->getConfig( 'site_sender_email' );
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
						if ( !ereg ( "^250", $From )
						|| ( !ereg ( "^250", $To )
							&& !ereg( "Please use your ISP relay", $To) )

						) {
							$errors['email'] = $pEmail." is not recognized by the mail server to=$To= from=$From= out=$Out=";
						}
					}
				}
			} else {
				if (!defined($Out)) { $Out = 'n/a'; }
				$errors['email'] = "Cannot connect to mail server ({$ConnectAddress}). response='$Out'";
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
		global $notificationlib, $gBitSmarty, $gBitSystem, $gBitUser;
		$ret = FALSE;
		if( !empty( $_FILES['fPortraitFile'] ) && empty( $_FILES['fAvatarFile'] ) ) {
			$pParamHash['fAutoAvatar'] = TRUE;
		}
		if ($this->verify($pParamHash)) {
			for ( $i=0; $i<BaseAuth::getAuthMethodCount(); $i++ ) {
				$instance = BaseAuth::init($i);
				if ($instance && $instance->canManageAuth()) {
					if( $userId = $instance->createUser($pParamHash) ) {
						$this->mUserId = $userId;
						break;
					} else {
						$this->mErrors = array_merge( $this->mErrors, $instance->mErrors);
						return FALSE;
					}
				}
			}
			
			$this->load( FALSE, $pParamHash['login'] );

			require_once( KERNEL_PKG_PATH.'notification_lib.php' );
			$notificationlib->post_new_user_event( $pParamHash['login'] );
			$this->mLogs['register'] = 'New user registered.';
			$ret = TRUE;

			// set local time zone as default when registering
			$this->storePreference( 'site_display_utc', 'Local' );

			if( !empty( $_REQUEST['CUSTOM'] ) ) {
				foreach( $_REQUEST['CUSTOM'] as $field=>$value ) {
					$this->storePreference( $field, $value );
				}
			}

			// Handle optional user preferences that may be collected during registration
			if( !empty( $pParamHash['prefs'] ) ) {
				foreach( array_keys( $pParamHash['prefs'] ) as $key ) {
					$this->storePreference( $key, $pParamHash['prefs'][$key] );
				}
			}

			$siteName = $gBitSystem->getConfig('site_title', $_SERVER['HTTP_HOST'] );
			$gBitSmarty->assign('siteName',$_SERVER["SERVER_NAME"]);
			$gBitSmarty->assign('mail_site',$_SERVER["SERVER_NAME"]);
			$gBitSmarty->assign('mail_user',$pParamHash['login']);
			if( $gBitSystem->isFeatureActive( 'users_validate_user' ) ) {
				// $apass = addslashes(substr(md5($gBitSystem->genPass()),0,25));
				$apass = $pParamHash['user_store']['provpass'];
				$foo = parse_url($_SERVER["REQUEST_URI"]);
				$foo1=str_replace("register","confirm",$foo["path"]);
				$machine = httpPrefix().$foo1;

				// Send the mail
				$gBitSmarty->assign('msg',tra('You will receive an email with information to login for the first time into this site'));
				$gBitSmarty->assign('mail_machine',$machine);
				$gBitSmarty->assign('mailUserId',$this->mUserId);
				$gBitSmarty->assign('mailProvPass',$apass);
				$mail_data = $gBitSmarty->fetch('bitpackage:users/user_validation_mail.tpl');
				mail($pParamHash["email"], $siteName.' - '.tra('Your registration information'),$mail_data,"From: ".$gBitSystem->getConfig('site_sender_email')."\r\nContent-type: text/plain;charset=utf-8\r\n");
				$gBitSmarty->assign('showmsg','y');

				$this->mLogs['confirm'] = 'Validation email sent.';
			}
			else if( $gBitSystem->isFeatureActive( 'send_welcome_email' ) ) {
				// Send the welcome mail
				$gBitSmarty->assign( 'mailPassword',$pParamHash['password'] );
				$gBitSmarty->assign( 'mailEmail',$pParamHash['email'] );
				$mail_data = $gBitSmarty->fetch('bitpackage:users/welcome_mail.tpl');
				mail($pParamHash["email"], tra( 'Welcome to' ).' '.$siteName,$mail_data,"From: ".$gBitSystem->getConfig('site_sender_email')."\r\nContent-type: text/plain;charset=utf-8\r\n");

				$this->mLogs['welcome'] = 'Welcome email sent.';
			}
			$logHash['action_log']['title'] = $pParamHash['login'];
			$this->storeActionLog( $logHash );
		}
		return( $ret );
	}

	function verifyCaptcha( $pCaptcha = NULL ) {
		if( $this->hasPermission( 'p_users_bypass_captcha' ) || ( !empty( $_SESSION['captcha_verified'] ) && $_SESSION['captcha_verified'] === TRUE ) ) {
			return TRUE;
		} else {
			if( empty( $pCaptcha ) || empty( $_SESSION['captcha'] ) || $_SESSION['captcha'] != md5( $pCaptcha ) ) {
				return FALSE;
			} else {
				$_SESSION['captcha_verified'] = TRUE;
				return TRUE;
			}
		}
	}


	function store( &$pParamHash ) {
		if( $this->verify( $pParamHash ) ) {
			$this->mDb->StartTrans();
			$pParamHash['content_type_guid'] = BITUSER_CONTENT_TYPE_GUID;

			if( !empty( $pParamHash['user_store'] ) && count( $pParamHash['user_store'] ) ) {
				if( $this->isValid() ) {
					$userId = array ( "user_id" => $this->mUserId );
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
			// Don't let LA snarf these now so we can do extra things.
			$pParamHash['_files_override'] = array();
			if( LibertyAttachable::store( $pParamHash ) ) {
				if( empty( $this->mInfo['content_id'] ) || ($pParamHash['content_id'] != $this->mInfo['content_id']) ) {
					$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `content_id`=? WHERE `user_id`=?";
					$result = $this->mDb->query( $query, array( $pParamHash['content_id'], $this->mUserId ) );
					$this->mInfo['content_id'] = $pParamHash['content_id'];
				}
			}

			$this->mDb->CompleteTrans();

			// store any uploaded images
			$pParamHash['upload']['thumbnail'] = FALSE;   // i don't think this does anything - perhaps replace it by setting thumbnail_sizes
			$this->storeImages( $pParamHash );

			$this->load( TRUE );
		}
		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * Imports a user record from csv file
	 * This is a admin specific function
	 *
	 * @param $pParamHash an array with user data
	 * @return true if import succeed
	 **/
	function importUser( &$pParamHash ) {
		global $gBitUser;

		if( ! $gBitUser->hasPermission( 'p_users_admin' ) ) {
			return FALSE;
		}
		if( $this->verifyUserImport( $pParamHash ) ) {
			$this->mDb->StartTrans();
			$pParamHash['content_type_guid'] = BITUSER_CONTENT_TYPE_GUID;
			if( !empty( $pParamHash['user_store'] ) && count( $pParamHash['user_store'] ) ) {
				// lookup and asign the default group for user
				$defaultGroups = BitPermUser::getDefaultGroup();
				if( !empty( $defaultGroups ) ) {
					$pParamHash['user_store']['default_group_id'] = key( $defaultGroups );
				}
				if( $this->isValid() ) {
					$userId = array ( "user_id" => $this->mUserId );
					$result = $this->mDb->associateUpdate( BIT_DB_PREFIX.'users_users', $pParamHash['user_store'], $userId );
				} else {
					if( empty( $pParamHash['user_store']['user_id'] ) ) {
						$pParamHash['user_store']['user_id'] = $this->mDb->GenID( 'users_users_user_id_seq' );
					}
					$this->mUserId = $pParamHash['user_store']['user_id'];
					$result = $this->mDb->associateInsert( BIT_DB_PREFIX.'users_users', $pParamHash['user_store'] );
				}
				// make sure user is added into the default group map
				if( !empty( $pParamHash['user_store']['default_group_id'] ) ) {
					BitPermUser::addUserToGroup( $pParamHash['user_store']['user_id'],$pParamHash['user_store']['default_group_id'] );
				}

			}
			// Prevent liberty from assuming ANONYMOUS_USER_ID while storing
			$pParamHash['user_id'] = $this->mUserId;
			if( LibertyAttachable::store( $pParamHash ) ) {
				if( empty( $this->mInfo['content_id'] ) || ($pParamHash['content_id'] != $this->mInfo['content_id']) ) {
					$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `content_id`=? WHERE `user_id`=?";
					$result = $this->mDb->query( $query, array( $pParamHash['content_id'], $this->mUserId ) );
					$this->mInfo['content_id'] = $pParamHash['content_id'];
				}
			}

			$this->mDb->CompleteTrans();

			// store any uploaded images
			$pParamHash['upload']['thumbnail'] = FALSE;   // i don't think this does anything - perhaps replace it by setting thumbnail_sizes
			$this->storeImages( $pParamHash );

			$this->load( TRUE );
		}
		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * Verify and validate the data when 
	 * importing a user record from csv file
	 * This is a admin specific function
	 *
	 * @param $pParamHash an array with user data
	 * @return true if validation succeed
	 **/
	function verifyUserImport( &$pParamHash ) {
		global $gBitSystem, $gBitUser;

		if( ! $gBitUser->hasPermission( 'p_users_admin' ) ) {
			return FALSE;
		}

		trim_array( $pParamHash );
        
		// perhaps someone is importing users and *knows* what they are doing
		if( @$this->verifyId( $pParamHash['user_id'] ) ) {
			// only import user_id if it doesn't exist or overwrite is set.
			if( !$this->userExists( array( 'user_id' => $pParamHash['user_id'] ) ) || !empty( $_REQUEST['overwrite'] ) ) {
				$pParamHash['user_store']['user_id'] = $pParamHash['user_id'];
			} else {
				unset( $pParamHash['user_id'] );
			}
		}
		if( !empty( $pParamHash['login'] ) ) {
			$ret = $this->userExists( array( 'login' => $pParamHash['login'] ) );
			if( !empty( $ret ) ) {
				// On batch import admin can overwrite existing user, so don't error if set
				// however, prevent overwrite of a mix of user records
				if( !empty( $_REQUEST['overwrite'] ) && (!isset($pParamHash['user_store']['user_id']) || $pParamHash['user_store']['user_id'] == $ret ) ) {
					$pParamHash['user_id'] = $ret;
					$pParamHash['user_store']['user_id'] = $pParamHash['user_id'];
				} else {
				    $this->mErrors['login'] = 'The username "'.$pParamHash['login'].'" is already in use';
				}
			} elseif( preg_match( '/[^A-Za-z0-9_.-]/', $pParamHash["login"] ) ) {
				$this->mErrors['login'] = tra( "Your username can only contain numbers, characters, underscores and hyphens." );
			} 
			
			if( !isset($this->mErrors['login']) ) {
				// LOWER CASE all logins
				$pParamHash['login'] = strtolower($pParamHash['login']);
				$pParamHash['user_store']['login'] = $pParamHash['login'];
			}
		} else {
            $this->mErrors['login'] = 'Value for username is missing';
		}
		if( !empty( $pParamHash['real_name'] ) ) {
			$pParamHash['user_store']['real_name'] = substr( $pParamHash['real_name'], 0, 64 );
		}
		if( !empty( $pParamHash['email'] ) ) {
			// LOWER CASE all emails admin_verify_email
			$pParamHash['email'] = strtolower( $pParamHash['email'] );
			if( validate_email_syntax( $pParamHash['email'] ) ) {
				$ret = $this->userExists( array( 'email' => $pParamHash['email'] ) );
				if( !empty($ret) ) {
					if( !empty( $_REQUEST['overwrite'] ) && (!isset($pParamHash['user_store']['user_id']) || $pParamHash['user_store']['user_id'] == $ret ) ) {
						$pParamHash['user_id'] = $ret;
						$pParamHash['user_store']['user_id'] = $pParamHash['user_id'];
					} else {
						$this->mErrors['email'] = 'The email address "'.$pParamHash['email'].'" has already been registered.';
					}
				}
				if( !empty( $_REQUEST['admin_verify_email'] ) ) {
					if( !$this->verifyMX( $pParamHash['email'] ) ) {
						$this->mErrors['email'] = 'Cannot find a valid MX host';
					}
				}
				if( !isset($this->mErrors['email']) ) {
					$pParamHash['user_store']['email'] = strtolower( substr( $pParamHash['email'], 0, 200 ) );
				}
			} else {
				$this->mErrors['email'] = 'The email address "'.$pParamHash['email'].'" has an invalid syntax.';
			}
		} else {
		    $this->mErrors['email'] = tra( 'You must enter your email address' );
		}
		
		// check some new user requirements
		if( !$this->isRegistered() ) {
			if( isset($pParamHash['user_store']['user_id']) && !empty( $_REQUEST['overwrite'] ) ) {
				$this->mUserId = $this->userExists( array( 'user_id' => $pParamHash['user_store']['user_id'] ) );
			}
			if( empty( $pParamHash['registration_date'] ) ) {
				$pParamHash['registration_date'] = date( "U" );
			}
			$pParamHash['user_store']['registration_date'] = $pParamHash['registration_date'];

            if( !empty($pParamHash['hash'] ) ) {
                unset( $pParamHash['password'] );
				if($gBitSystem->isFeatureActive( 'users_clear_passwords' ) ) {
                    $this->mErrors['password'] = tra( 'You cannot import a password hash when setting to stor password in plan text is set.' );
				} 
				elseif( strlen( $pParamHash['hash'] ) <> 32 ) {
                    $this->mErrors['password'] = tra( 'When importing a MD5 password hash it needto have a length of 32 bytes.' );
				}
            } else {
			    if( !empty( $_REQUEST['admin_verify_user'] ) ) {
                    $pParamHash['user_store']['provpass'] = md5(BitSystem::genPass());
					$pParamHash['user_store']['hash'] = '';
                    $pParamHash['pass_due'] = 0;
					unset( $pParamHash['password'] );
				} elseif( empty($pParamHash['password'] ) ) {
				   $pParamHash['password'] = $gBitSystem->genPass();
				}
			}
		} elseif( $this->isValid() ) {
			// Prevent loosing user info on save
			if( empty( $pParamHash['edit'] ) ) {
				$pParamHash['edit'] = $this->mInfo['data'];
			}
		}

		if( isset( $pParamHash['password'] ) ) {
			if (!$this->isValid() || isset($pParamHash['password']) ) {
				$passswordError = $this->verifyPasswordFormat( $pParamHash['password'] );
			}
			if( !empty( $passswordError ) ) {
				$this->mErrors['password'] = $passswordError;
			} else {
				// Generate a unique hash
				//$pParamHash['user_store']['hash'] = md5( strtolower( (!empty($pParamHash['login'])?$pParamHash['login']:'') ).$pPassword.$pParamHash['email'] );
				$pParamHash['user_store']['hash'] = md5( $pParamHash['password'] );
				$now = $gBitSystem->getUTCTime();
				if( !isset( $pParamHash['pass_due'] ) && $gBitSystem->getConfig('users_pass_due') ) {
					$pParamHash['user_store']['pass_due'] = $now + (60 * 60 * 24 * $gBitSystem->getConfig('users_pass_due') );
				} elseif( isset( $pParamHash['pass_due'] ) ) {
					// renew password only next half year ;)
					$pParamHash['user_store']['pass_due'] = $now + (60 * 60 * 24 * $pParamHash['pass_due']);
				}
				if( $gBitSystem->isFeatureActive( 'users_clear_passwords' ) ) {
					$pParamHash['user_store']['user_password'] = $pParamHash['password'];
				} else {
					$pParamHash['user_store']['user_password'] = '';
				}
			}
		}
		elseif( !empty($pParamHash['hash']) ) {
            $pParamHash['user_store']['hash'] = $pParamHash['hash'];
            $now = $gBitSystem->getUTCTime();
            if( !isset( $pParamHash['pass_due'] ) && $gBitSystem->getConfig('users_pass_due') ) {
                $pParamHash['user_store']['pass_due'] = $now + (60 * 60 * 24 * $gBitSystem->getConfig('users_pass_due') );
            } elseif( isset( $pParamHash['pass_due'] ) ) {
                // renew password only next half year ;)
                $pParamHash['user_store']['pass_due'] = $now + (60 * 60 * 24 * $pParamHash['pass_due']);
            }
		}
		return ( count($this->mErrors) == 0 );
	}


	// removes user and associated private data
	function expunge() {
		global $gBitSystem;
		$this->mDb->StartTrans();
		if( $this->mUserId != ANONYMOUS_USER_ID ) {
			$this->purgeImage( 'avatar' );
			$this->purgeImage( 'portrait' );
			$this->purgeImage( 'logo' );
			$this->invokeServices( 'users_expunge_function' );	
			$userTables = array(
			'users_semaphores',
			// these have to be dealt with functions in there own packages
			//'stars_history',
			//'tidbits_user_bookmarks_urls',
			//'tidbits_user_bookmarks_folders',
			//'tidbits_user_menus',
			//'tidbits_user_tasks',
			'users_cnxn',
			'users_watches',
			'users_favorites_map',
			'users_users',
			//'liberty_content', you can't delete a content without deleting the associated object - and it is not because a user dissapears that all his production must dissapear - other users can have work on it
			);
			foreach( $userTables as $table ) {
				$query = "DELETE FROM `".BIT_DB_PREFIX.$table."` WHERE `user_id` = ?";
				$result = $this->mDb->query( $query, array( $this->mUserId ) );
			}

			parent::expunge();

			$logHash['action_log']['title'] = $this->mInfo['login'];
			$this->mLogs['user_del'] = 'User deleted';
			$this->storeActionLog( $logHash );
			$this->mDb->CompleteTrans();
			return TRUE;
		} else {
			$this->mDb->RollbackTrans();
			$gBitSystem->fatalError( tra( 'The anonymous user cannot be deleted' ) );
		}
	}

	// sets the user account status to -201 suspended
	function ban(){
		global $gBitSystem;
		if( $this->mUserId == ANONYMOUS_USER_ID || $this->mUserId == ROOT_USER_ID || $this->isAdmin()) {
			$gBitSystem->fatalError( tra( 'You cannot ban the user' )." ".$this->mInfo['login'] );
		}else{
			$this->storeStatus( -201 );
			return TRUE;
		}
	}

	// unban the user
	function unban(){
		global $gBitSystem;
		$this->storeStatus( 50 );
		return TRUE;
	}

	function genPass( $pLength=NULL ) {
		global $gBitSystem;
		// AWC: enable mixed case and digits, don't return too short password
		global $users_min_pass_length;
		$vocales = "AaEeIiOoUu13580";
		$consonantes = "BbCcDdFfGgHhJjKkLlMmNnPpQqRrSsTtVvWwXxYyZz24679";
		$r = '';
		if( empty( $pLength ) || !is_numeric( $pLength ) ) {
			$pLength = $gBitSystem->getConfig( 'users_min_pass_length', 4 );
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
		if ( !isset($_COOKIE[$user_cookie_site]) ) {
			$url = USERS_PKG_URL.'login.php?error=' . urlencode(tra('No cookie found. Please enable cookies and try again.'));
			return ( $url );
		}

		// Verify user is valid
		if( $this->validate($pLogin, $pPassword, $pChallenge, $pResponse) ) {
			$loginCol = strpos( $pLogin, '@' ) ? 'email' : 'login';
			$userInfo = $this->getUserInfo( array( $loginCol => $pLogin ) );
			// If the password is valid but it is due then force the user to change the password by
			// sending the user to the new password change screen without letting him use tiki
			// The user must re-nter the old password so no secutiry risk here
			if( $this->isPasswordDue() ) {
				// Redirect the user to the screen where he must change his password.
				// Note that the user is not logged in he's just validated to change his password
				// The user must re-enter his old password so no secutiry risk involved
				$url = USERS_PKG_URL.'change_password.php?user_id='.$userInfo['user_id'];
			} elseif( $userInfo['user_id'] != ANONYMOUS_USER_ID ) {
				// User is valid and not due to change pass.. 
				$this->mUserId = $userInfo['user_id'];
				$this->load();
				$this->loadPermissions();
				$url = isset($_SESSION['loginfrom']) ? $_SESSION['loginfrom'] : $gBitSystem->getDefaultPage();
				unset($_SESSION['loginfrom']);

				$userInfo['cookie'] = md5( time().$userInfo['email'] );
				$cookie_time = 0;
				$cookie_path = BIT_ROOT_URL;
				$cookie_domain = "";
				// Now if the remember me feature is on and the user checked the user_remember_me checkbox then ...
				if ($gBitSystem->isFeatureActive( 'users_remember_me' ) && isset($_REQUEST['rme']) && $_REQUEST['rme'] == 'on') {
					$cookie_time = (int)(time() + $gBitSystem->getConfig( 'users_remember_time', 86400 ));
					$cookie_path = $gBitSystem->getConfig('cookie_path', $cookie_path);
					$cookie_domain = $gBitSystem->getConfig('cookie_domain', $cookie_domain);
				}
				$session_id = session_id();
				setcookie( $user_cookie_site, $session_id, $cookie_time, $cookie_path, $cookie_domain );
				$this->updateSession( $session_id );
			}
		} else {
			$this->mUserId = ANONYMOUS_USER_ID;
			unset( $this->mInfo );
			$url = USERS_PKG_URL.'login.php?error=' . urlencode(tra('Invalid username or password'));
		}
		$https_mode = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on';
		if ($https_mode) {
			$stay_in_ssl_mode = ((isset($_SERVER['HTTP_REFERER']) && (substr($_SERVER['HTTP_REFERER'], 0, 5) == 'https'))
			|| (isset($_REQUEST['stay_in_ssl_mode']) && $_REQUEST['stay_in_ssl_mode'] == 'on'));
			if (!$stay_in_ssl_mode) {
				$site_http_domain = $gBitSystem->getConfig('site_http_domain', false);
				$site_http_port = $gBitSystem->getConfig('site_http_port', 80);
				$site_http_prefix = $gBitSystem->getConfig('site_http_prefix', '/');
				if ($site_http_domain) {
					$prefix = 'http://' . $site_http_domain;
					if ($site_http_port != 80)
					$prefix .= ':' . $site_http_port;
					$prefix .= $site_http_prefix;
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
		$authValid = false;
		$authPresent = false;

		$createAuth = ($gBitSystem->getConfig("users_create_user_auth", "n") == "y");

		for ($i=0;$i<BaseAuth::getAuthMethodCount();$i++) {
			$instance = BaseAuth::init($i);
			if ($instance) {
				$result = $instance->validate($user, $pass, $challenge, $response);
				switch ($result) {
					case USER_VALID:
						unset($this->mErrors['login']);
						$authPresent = true;
						$authValid = true;
						break;
					case PASSWORD_INCORRECT:
						// this mErrors assignment is CRUCIAL so that bit auth fails properly. DO NOT FUCK WITH THIS unless you know what you are doing and have checked with me first. XOXOX - spiderr
						// This might have broken other auth, but at this point, bw auth was TOTALLY busted. If you need to fix, please come find me.
						$this->mErrors['login'] = 'Password incorrect';
						$authPresent = true;
						break;
					case USER_NOT_FOUND:
						break;
				}
				if ($authPresent) {
					if (empty($instance->mInfo['email'])) {
						$instance->mInfo['email']=$user;
					}
					//If we're given a user_id then the user is already in the tiki list:
					if(!empty($instance->mInfo['user_id'])) {
						$this->mUserId = $instance->mInfo['user_id'];
						//Is the user already in the tiki list:
					} elseif ($this->mDb->getOne("SELECT COUNT(*) FROM `".BIT_DB_PREFIX."users_users` WHERE `login`=?", array($instance->mLogin))>0) {
						// Update Details
						$authUserInfo = array( 'login' => $instance->mInfo['login'], 'password' => $instance->mInfo['password'], 'real_name' => $instance->mInfo['real_name'], 'email' => $instance->mInfo['email'] );
						$userInfo = $this->getUserInfo(array('login' => $user ));
						$this->mUserId = $userInfo['user_id'];
						$this->store( $authUserInfo );
						# TODO: Fix this - if user is an LDAP user, with a TIKI user already created,
						# storing user info causes errors. NEED TO FIX - wolff_borg
						$this->mErrors = array();
					} else {
						//Add the user to the tiki list:
						// need to make this better! *********************************************************
						// if it worked ok, just log in
						$authUserInfo = array( 'login' => $instance->mInfo['login'], 'password' => $instance->mInfo['password'], 'real_name' => $instance->mInfo['real_name'], 'email' => $instance->mInfo['email'] );
						// TODO somehow, mUserId gets set to -1 at this point - no idea how
						// set to NULL to prevent overwriting Guest user - wolff_borg
						$this->mUserId = NULL;
						//echo "mUserId: ".$this->mUserId."<br>";
						$this->store( $authUserInfo );
					}
					if ( $createAuth && $i > 0 ) {
						// if the user was logged into this system and we should progate users down other auth methods
						for ( $j=$i; $i>=0; $j-- ) {
							$probMethodName=$gBitSystem->getConfig("users_auth_method_$j",$default);
							if (! empty($prob_method_name)) {
								$pInstance = BaseAuth::init( $probMethodName );
								if ($pInstance && $pInstance->canManageAuth()) {
									$result = $pInstance->validate($user, $pass, $challenge, $response);
									if ($result == USER_VALID || $result ==PASSWORD_INCORRECT) {
										// see if we can create a new account
										$userattr = $instance->getUserData();
										if (empty($userattr['login'])) {
											$userattr['login'] = $user;
										}
										if (empty($userattr['password'])) {
											$userattr['password'] = $pass;
										}
										$pInstance->createUser($userattr);
									}
								}
								$this->mErrors = array_merge($this->mErrors,$pInstance->mErrors);
							}
						}
					}
					$this->mAuth = $instance;
					break;
				}
				$this->mErrors = array_merge($this->mErrors,$instance->mErrors);
			}
		}
		if( $this->mUserId != ANONYMOUS_USER_ID ) {
			$this->load();
			//on first time login we run the users registation service
			if ($this->mInfo['last_login'] == NULL){
				$this->invokeServices( 'users_register_function' );
			}
			$this->update_lastlogin( $this->mUserId );
		}
		return( count( $this->mErrors ) == 0 );
	}

	// update the lastlogin status on this user
	function update_lastlogin( $pUserId ) {
		$ret = FALSE;
		if( @$this->verifyId( $pUserId ) ) {
			global $gBitSystem;
			$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `last_login`=`current_login`, `current_login`=?
					  WHERE `user_id`=?";
			$result = $this->mDb->query( $query, array( $gBitSystem->getUTCTime(), $pUserId ) );
			$ret = TRUE;
		}
		return $ret;
	}

	function get_users_names($offset = 0, $max_records = -1, $sort_mode = 'login_desc', $find = '') {
		// Return an array of users indicating name, email, last changed pages, versions, last_login
		if ($find) {
			$findesc = '%' . strtoupper( $find ) . '%';
			$mid = " where UPPER(`login`) like ?";
			$bindvars = array($findesc);
		} else {
			$mid = '';
			$bindvars=array();
		}
		$query = "select `login` from `".BIT_DB_PREFIX."users_users` $mid order by ".$this->mDb->convertSortmode($sort_mode);
		$result = $this->mDb->query($query,$bindvars,$max_records,$offset);
		$ret = array();
		while ($res = $result->fetchRow()) {
			$ret[] = $res["login"];
		}
		return ($ret);
	}

	function confirmRegistration( $pUserId, $pProvpass ) {
		global $gBitSystem;
		$now = $gBitSystem->getUTCTime();
		$query = "select `user_id`, `provpass`, `user_password`, `login`, `email` FROM `".BIT_DB_PREFIX."users_users`
				  WHERE `user_id`=? AND `provpass`=? AND ( `provpass_expires` is NULL or `provpass_expires` > ?)";
		$user_found = $this->mDb->getRow($query, array( $pUserId, $pProvpass, $now ) ) ;
		return ($user_found);
	}

	function changeUserEmail( $pUserId, $pEmail ) {
		if( $this->userExists( array( 'email' => $pEmail ))) {
			$this->mErrors['duplicate_mail'] = tra( "The email address you selected already exists." );
		} else {
			$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `email`=? WHERE `user_id`=?";
			$result = $this->mDb->query( $query, array( $pEmail, $pUserId ) );
			$query = "UPDATE `".BIT_DB_PREFIX."users_watches` SET `email`=? WHERE `user_id`=?";
			$result = $this->mDb->query( $query, array( $pEmail, $pUserId ) );

			// update value in hash
			$this->mInfo['email'] = $_REQUEST['email'];
		}
		return( count( $this->mErrors ) == 0 );
	}


	function lookupHomepage( $iHomepage ) {
		$ret = NULL;
		if ( @$this->verifyId($iHomepage)) {
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
		if (@$this->verifyId($tmpUser['user_id'])) {
			$ret = $tmpUser['user_id'];
		}
		return $ret;
	}

	// Alternate to LibertyContent::getPreference when all you have is a user_id and a pref_name, and you need a value...
	function getUserPreference( $pPrefName, $pPrefDefault, $pUserId ) {
		global $gBitDb;
		$ret = NULL;

		if( BitBase::verifyId( $pUserId ) ) {
			$query = "SELECT lcp.`pref_value` FROM `".BIT_DB_PREFIX."liberty_content_prefs` lcp INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lcp.`content_id`=uu.`content_id`)
					  WHERE uu.`user_id`=? AND lcp.`pref_name` = ?";
			if( !$ret = $gBitDb->getOne( $query, array( $pUserId, $pPrefName ) ) ) {
				$ret = $pPrefDefault;
			}
		}
		return $ret;
	}

	// specify lookup where by hash key lik 'login' or 'user_id' or 'email'
	function getUserInfo( $pUserMixed ) {
		$ret = NULL;
		if( is_array( $pUserMixed ) ) {
			$query = "SELECT  uu.* FROM `".BIT_DB_PREFIX."users_users` uu LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lc.`content_id`=uu.`content_id`)
					  WHERE UPPER( uu.`".key( $pUserMixed )."` ) = ?";
			$ret = $this->mDb->getRow( $query, array( strtoupper( current( $pUserMixed ) ) ) );
		}
		return $ret;
	}

	// specify lookup where by hash key lik 'login' or 'user_id' or 'email'
	function getUserFromContentId( $content_id ) {
		$ret = NULL;
		if( @$this->verifyId( $content_id ) ) {
			$query = "SELECT  `user_id` FROM `".BIT_DB_PREFIX."users_users`
					  WHERE `content_id` = ?";
			$tmpUser = $this->mDb->getRow( $query, array( $content_id ) );
			if ( @$this->verifyId($tmpUser['user_id'])) {
				$ret = $tmpUser['user_id'];
			}
		}
		return $ret;
	}

	function getByHash( $hash ) {
		$query = "select `user_id` from `".BIT_DB_PREFIX."users_cnxn` where `cookie`=?";
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
			if( @$this->verifyId( $due['user_id'] ) ) {
				global $gBitSystem;
				$ret = $due['pass_due'] <= $gBitSystem->getUTCTime();
			}
		}
		return $ret;
	}
	function renewPassword( $pLogin ) {
		global $gBitSystem;
		$pass = BitSystem::genPass();
		$this->storePassword( $pass, $pLogin );
		return $pass;
	}

	function createTempPassword( $pLogin, $pPass ) {
		global $gBitSystem;
		if( empty( $pLogin ) ) {
			$pLogin = $this->getField( 'email' );
		}
		if( $pLogin ) {
			$pass = BitSystem::genPass();
			$provpass = md5( $pass );
			$loginCol = strpos( $pLogin, '@' ) ? 'email' : 'login';
			$now = $gBitSystem->getUTCTime();;
			#temp passwords good for 3 days -- prob should be an config option
			$passDue = $now + (60 * 60 * 24 * 3 );
			$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `provpass`= ?, `provpass_expires`=? WHERE `".$loginCol."`=?";
			$result = $this->mDb->query($query, array( $provpass, $passDue, $pLogin ) );
			return array($pass,$provpass);
		}
		return array('','');
	}


	function storePassword( $pPass, $pLogin=NULL ) {
		global $gBitSystem;
		$ret = FALSE;
		if( empty( $pLogin ) ) {
			$pLogin = $this->getField( 'email' );
		}
		if( $pLogin ) {
			$ret = TRUE;
			$hash = md5( $pPass );
			$now = $gBitSystem->getUTCTime();;
			$passDue = $now + (60 * 60 * 24 * $gBitSystem->getConfig( 'users_pass_due' ) );
			if( !$gBitSystem->isFeatureActive( 'users_clear_passwords' ) ) {
				$pPass = NULL;
			}
			$loginCol = strpos( $pLogin, '@' ) ? 'email' : 'login';
			$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `provpass`= NULL, `provpass_expires` = NULL,`hash`=? ,`user_password`=? ,`pass_due`=? WHERE `".$loginCol."`=?";
			$result = $this->mDb->query($query, array( $hash, $pPass, $passDue, $pLogin ) );
		}
		return $ret;
	}

	function get_users($offset = 0, $max_records = -1, $sort_mode = 'login_desc', $find = '') {
		$sort_mode = $this->mDb->convertSortmode($sort_mode);
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
		$result = $this->mDb->query($query, $bindvars, $max_records, $offset);
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
	function getUserActivity( &$pListHash ) {
		$bindVars = array();
		if( empty( $pListHash['sort_mode'] ) ) {
			$pListHash['sort_mode'] = 'last_get_desc';
		}
		LibertyContent::prepGetList( $pListHash );

		$whereSql = '';
		if( !empty( $pListHash['last_get'] ) ) {
			$whereSql .= ' AND uc.`last_get` > ? ';
			$bindVars[] = time() - $pListHash['last_get'];
		}

		if( @BitBase::verifyId( $pListHash['user_id'] ) ) {
			$whereSql .= ' AND uc.`user_id` = ? ';
			$bindVars[] = $pListHash['user_id'];
		}

		if( !empty( $pListHash['ip'] ) ) {
			$whereSql .= ' AND uc.`ip` = ? ';
			$bindVars[] = $pListHash['ip'];
		}

		if( !empty( $pListHash['online'] ) ) {
			$whereSql .= ' AND uc.`cookie` IS NOT NULL ';
		}

		$query = "select DISTINCT uc.`user_id`, `login`, `real_name`,`connect_time`, `ip`, `user_agent`, `last_get`, uu.`content_id`
				  FROM `".BIT_DB_PREFIX."users_cnxn` uc INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (uc.`user_id`=uu.`user_id`)
				  WHERE uc.`user_id` IS NOT NULL $whereSql
				  ORDER BY ".$this->mDb->convertSortmode( $pListHash['sort_mode'] );
		$result = $this->mDb->query($query, $bindVars, $pListHash['max_records'], $pListHash['offset'] );
		$ret = array();
		while ($res = $result->fetchRow()) {
			$res['users_information'] = 	$this->getPreference( 'users_information', 'public', $res['content_id'] );
			$ret[] = $res;
		}
		
		$countSql = "SELECT COUNT( DISTINCT uc.`user_id` )
				     FROM `".BIT_DB_PREFIX."users_cnxn` uc INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (uc.`user_id`=uu.`user_id`)
				     WHERE uc.`user_id` IS NOT NULL $whereSql";
		$pListHash['cant'] = $this->mDb->GetOne( $countSql, $bindVars );
		$this->postGetList( $pListHash );
		return $ret;
	}

	function getUserDomain( $pLogin ) {
		$ret = array();
		if( $pLogin == $this->getField( 'login' ) && $this->getPreference( 'domain_style' ) ) {
			$ret = $this->mInfo;
			$ret['style'] = $this->getPreference( 'domain_style' );
		} else {
			$sql = "SELECT uu.*, lcp.`pref_value` AS `style` FROM `".BIT_DB_PREFIX."users_users` uu 
						INNER JOIN `".BIT_DB_PREFIX."liberty_content_prefs` lcp ON(uu.`content_id`=lcp.`content_id`) 
					WHERE uu.`login`=? AND lcp.`pref_name`=?";
			$ret = $this->mDb->getRow( $sql,  array( $pLogin, 'domain_style' ) );
		}
		return( $ret );
	}

	function getDomain( $pContentId ) {
		$ret = array();
		if( $this->verifyId( $pContentId ) ) {
			$ret['content_id'] = $pContentId;
			$ret['style'] = $this->mDb->getOne( "SELECT `pref_value` FROM `".BIT_DB_PREFIX."liberty_content_prefs` WHERE `content_id`=? AND `pref_name`=?", array( $pContentId, 'domain_style' ) );
		}
		return( $ret );
	}


	function canCustomizeTheme() {
		global $gBitSystem;
		return( $this->hasPermission( 'p_tidbits_custom_home_theme' ) || $gBitSystem->getConfig('users_themes') == 'y' || $gBitSystem->getConfig('users_themes') == 'h' || $gBitSystem->getConfig('users_themes') == 'u' );

	}



	function canCustomizeLayout() {
		global $gBitSystem;
		return( $this->hasPermission( 'p_tidbits_custom_home_layout' ) || $gBitSystem->getConfig('users_layouts') == 'y' || $gBitSystem->getConfig('users_layouts') == 'h' || $gBitSystem->getConfig('users_layouts') == 'u' );
	}



	// ============= image and file functions
	/**
	 * getThumbnailUrl 
	 * 
	 * @param string $pSize 
	 * @param array $pInfoHash 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getThumbnailUrl( $pSize='small', $pInfoHash=NULL ) {
		$ret = '';
		if( $pInfoHash ) {
			// do some stuff if we are passed a hash-o-crap, not implemented currently
		} elseif( $this->isValid() ) {
			$ret = $this->getField( 'avatar_url' );
		}
		return $ret;
	}

	/**
	 * storeImages will store any user images - please note that uploaded files have to be in predefined keys in $_FILES
	 *     $_FILES['fPortraitFile']
	 *     $_FILES['fAutoAvatar']
	 *     $_FILES['fLogoFile']
	 * 
	 * @param array $pParamHash array of options
	 * @param boolean $pParamHash['fAutoAvatar'] automatically create avatar from portrait
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function storeImages( $pParamHash ) {
		if( isset( $_FILES['fPortraitFile'] ) && is_uploaded_file( $_FILES['fPortraitFile']['tmp_name'] ) && $_FILES['fPortraitFile']['size'] > 0 ) {
			$portraitHash = $pParamHash;
			$portraitHash['upload'] = $_FILES['fPortraitFile'];
			$portraitHash['user_id'] = $this->mUserId;
			if( !$this->storePortrait( $portraitHash, ( !empty( $portraitHash['fAutoAvatar'] ) ? TRUE : FALSE ))) {
			}
		}

		if( isset( $_FILES['fAvatarFile'] ) && is_uploaded_file( $_FILES['fAvatarFile']['tmp_name'] ) && $_FILES['fAvatarFile']['size'] > 0 ) {
			$avatarHash = $pParamHash;
			$avatarHash['upload'] = $_FILES['fAvatarFile'];
			$avatarHash['upload']['source_file'] = $_FILES['fAvatarFile']['tmp_name'];
			$avatarHash['user_id'] = $this->mUserId;
			if( !$this->storeAvatar( $avatarHash )) {
			}
		}

		if( isset( $_FILES['fLogoFile'] ) && is_uploaded_file( $_FILES['fLogoFile']['tmp_name'] ) && $_FILES['fLogoFile']['size'] > 0 ) {
			$logoHash = $pParamHash;
			$logoHash['upload'] = $_FILES['fLogoFile'];
			$logoHash['upload']['source_file'] = $_FILES['fLogoFile']['tmp_name'];
			$logoHash['user_id'] = $this->mUserId;
			if( !$this->storeLogo( $logoHash )) {
			}
		}
	}

	/**
	 * storePortrait 
	 * 
	 * @param array $pStorageHash 
	 * @param array $pGenerateAvatar 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function storePortrait( &$pStorageHash, $pGenerateAvatar = FALSE ) {
		if( $this->isValid() && count( $pStorageHash ) ) {
			// make a copy before the uploaded file disappears
			if( $pGenerateAvatar ) {
				$avatarHash = $pStorageHash;
				$avatarHash['upload']['tmp_name'] = $pStorageHash['upload']['tmp_name'].'.av';
				copy( $pStorageHash['upload']['tmp_name'], $pStorageHash['upload']['tmp_name'].'.av' );
			}

			// setup the hash for central storage functions
			$pStorageHash['upload']['max_width'] = PORTRAIT_MAX_DIM;
			$pStorageHash['upload']['max_height'] = PORTRAIT_MAX_DIM;
			$pStorageHash['upload']['dest_path'] = $this->getStorageBranch( 'self',$this->mUserId );
			$pStorageHash['storage_type'] = STORAGE_IMAGE;
			$pStorageHash['content_type_guid'] = BITUSER_CONTENT_TYPE_GUID;
			$pStorageHash['attachment_id'] = !empty( $this->mInfo['portrait_attachment_id'] ) ? $this->mInfo['portrait_attachment_id'] : NULL;
			$pStorageHash['_files_override']['portrait'] = $pStorageHash['upload'];
			// don't do the content thing
			$pStorageHash['skip_content_store'] = TRUE;
			if( LibertyAttachable::store( $pStorageHash ) ) {
				$attachmentId = $pStorageHash['STORAGE']['bitfile']['portrait']['upload']['attachment_id'];
				if( empty( $this->mInfo['portrait_attachment_id'] ) || $this->mInfo['portrait_attachment_id'] != $attachmentId ) {
					$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `portrait_attachment_id` = ? WHERE `user_id`=?";
					$result = $this->mDb->query( $query, array( $attachmentId, $this->mUserId ) );
					$this->mInfo['portrait_attachment_id'] = $attachmentId;
					$pStorageHash['portrait_storage_path'] = $pStorageHash['upload']['dest_path'];
				}

				if( $pGenerateAvatar ) {
					$this->storeAvatar( $avatarHash );

					// nuke copy of image
					@unlink( $pStorageHash['upload']['tmp_name'].'.av' );
				}
			} else {
				$this->mErrors['file'] = 'File '.$pStorageHash['upload']['name'].' could not be stored.';
			}
		}
		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * storeAvatar 
	 * 
	 * @param array $pStorageHash 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function storeAvatar( &$pStorageHash ) {
		if( $this->isValid() && count( $pStorageHash ) ) {
			// setup the hash for central storage functions
			$pStorageHash['upload']['max_width'] = AVATAR_MAX_DIM;
			$pStorageHash['upload']['max_height'] = AVATAR_MAX_DIM;
			$pStorageHash['upload']['dest_path'] = $this->getStorageBranch( 'self',$this->mUserId );
			$pStorageHash['storage_type'] = STORAGE_IMAGE;
			$pStorageHash['content_type_guid'] = BITUSER_CONTENT_TYPE_GUID;
			$pStorageHash['attachment_id'] = !empty( $this->mInfo['avatar_attachment_id'] ) ? $this->mInfo['avatar_attachment_id'] : NULL;
			$pStorageHash['_files_override']['avatar'] = $pStorageHash['upload'];
			// don't do the content thing
			$pStorageHash['skip_content_store'] = TRUE;
			if( LibertyAttachable::store( $pStorageHash ) ) {
				$attachmentId = $pStorageHash['STORAGE']['bitfile']['avatar']['upload']['attachment_id'];
				if( empty( $this->mInfo['avatar_attachment_id'] ) || $this->mInfo['avatar_attachment_id'] != $attachmentId ) {
					$this->mInfo['avatar_storage_path'] = $pStorageHash['upload']['dest_path'];
					$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `avatar_attachment_id` = ? WHERE `user_id`=?";
					$result = $this->mDb->query( $query, array( $attachmentId, $this->mUserId ) );
					$this->mInfo['avatar_attachment_id'] = $attachmentId;
				}
			} else {
				$this->mErrors['avatar'] = 'File '.$pStorageHash['upload']['name'].' could not be stored.';
			}
		}
		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * storeLogo 
	 * 
	 * @param array $pStorageHash 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function storeLogo( &$pStorageHash ) {
		if( $this->isValid() && count( $pStorageHash ) ) {
			// setup the hash for central storage functions
			$pStorageHash['upload']['max_width'] = LOGO_MAX_DIM;
			$pStorageHash['upload']['max_height'] = LOGO_MAX_DIM;
			$pStorageHash['upload']['dest_path'] = $this->getStorageBranch( 'self',$this->mUserId );
			$pStorageHash['storage_type'] = STORAGE_IMAGE;
			$pStorageHash['content_type_guid'] = BITUSER_CONTENT_TYPE_GUID;
			$pStorageHash['attachment_id'] = $this->mInfo['logo_attachment_id'];
			$pStorageHash['_files_override']['logo'] = $pStorageHash['upload'];
			// don't do the content thing
			$pStorageHash['skip_content_store'] = TRUE;
			if( LibertyAttachable::store( $pStorageHash ) ) {
				$attachmentId = $pStorageHash['STORAGE']['bitfile']['logo']['upload']['attachment_id'];
				if($this->mInfo['logo_attachment_id'] != $attachmentId ) {
					$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `logo_attachment_id` = ? WHERE `user_id`=?";
					$result = $this->mDb->query( $query, array( $attachmentId, $this->mUserId ) );
					$this->mInfo['logo_attachment_id'] = $attachmentId;
				}
			} else {
				$this->mErrors['file'] = 'File '.$pStorageHash['name'].' could not be stored.';
			}
		}
		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * purgeImage 
	 * 
	 * @param array $pType 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function purgeImage( $pType ) {
		if( $this->isValid() && @$this->verifyId( $this->mInfo[$pType.'_attachment_id'] ) ) {
			$this->mDb->StartTrans();
			$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `".$pType."_attachment_id` = NULL WHERE `user_id`=?";
			$result = $this->mDb->query( $query, array( $this->mUserId ) );
			if( $this->expungeAttachment( $this->getField( $pType.'_attachment_id' ) ) ) {
				unset( $this->mInfo[$pType.'_storage_path'] );
				unset( $this->mInfo[$pType.'_attachment_id'] );
				unset( $this->mInfo[$pType.'_url'] );
			}
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
			$query = "SELECT a.`attachment_id`, a.`foreign_id`
					  FROM `".BIT_DB_PREFIX."liberty_attachments` a
					  WHERE a.`user_id` = ? AND a.`attachment_plugin_guid` = 'liberty_files'";
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
		$pListHash['user_id'] = $this->mUserId;
		$libertyAttachable = new LibertyAttachable();
		return $libertyAttachable->getAttachmentList( $pListHash );
	}

	function storeFavorite( $pContentId ) {
		$ret = FALSE;
		if( $this->isValid() && $this->verifyId( $pContentId ) ) {
			$this->mDb->query( "INSERT INTO `".BIT_DB_PREFIX."users_favorites_map` ( `user_id`, `favorite_content_id` ) VALUES (?,?)", array( $this->mUserId, $pContentId ) );
			$ret = TRUE;
		}
		return( $ret );
	}

	function expungeFavorite( $pContentId ) {
		$ret = FALSE;
		if( $this->isValid() && $this->verifyId( $pContentId ) ) {
			$this->mDb->query( "DELETE FROM `".BIT_DB_PREFIX."users_favorites_map` WHERE `user_id`=? AND `favorite_content_id`=?", array( $this->mUserId, $pContentId ) );
			$ret = TRUE;
		}
		return( $ret );
	}

	// ============= watch functions
	/*shared*/
	function storeWatch( $event, $object, $type, $title, $url ) {
		global $userlib;
		if( $this->isValid() ) {
			$hash = md5(uniqid('.'));
			$query = "delete from `".BIT_DB_PREFIX."users_watches` where `user_id`=? and `event`=? and `object`=?";
			$this->mDb->query($query,array( $this->mUserId, $event, $object ) );
			$query = "insert into `".BIT_DB_PREFIX."users_watches`(`user_id` ,`event` ,`object` , `email`, `hash`, `watch_type`, `title`, `url`) ";
			$query.= "values(?,?,?,?,?,?,?,?)";
			$this->mDb->query( $query, array( $this->mUserId, $event, $object, $this->mInfo['email'], $hash, $type, $title, $url ) );
			return true;
		}
	}

	function getWatches( $pEvent = '' ) {
		$ret = NULL;
		if( $this->isValid() ) {
			$mid = '';
			$bindvars=array( $this->mUserId );
			if ($pEvent) {
				$mid = " and `event`=? ";
				$bindvars[]=$pEvent;
			}

			$query = "select * from `".BIT_DB_PREFIX."users_watches` where `user_id`=? $mid";
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
			$query = "select * from `".BIT_DB_PREFIX."users_watches` WHERE `user_id`=? and `event`=? and `object`=?";
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

		$query = "select * from `".BIT_DB_PREFIX."users_watches` tw INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON ( tw.`user_id`=uu.`user_id` )  where `event`=? and `object`=?";
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
		$query = "delete from `".BIT_DB_PREFIX."users_watches` where `hash`=?";
		$this->mDb->query($query,array($hash));
	}

	/*shared*/
	function expungeWatch( $event, $object ) {
		if( $this->isValid() ) {
			$query = "delete from `".BIT_DB_PREFIX."users_watches` where `user_id`=? and `event`=? and `object`=?";
			$this->mDb->query( $query, array( $this->mUserId, $event, $object ) );
		}
	}

	/*shared*/
	function get_watches_events() {
		$query = "select distinct `event` from `".BIT_DB_PREFIX."users_watches`";
		$result = $this->mDb->query($query,array());
		$ret = array();
		while ($res = $result->fetchRow()) {
			$ret[] = $res['event'];
		}
		return $ret;
	}


	function getUserId() {
		return( $this->isValid() ? $this->mUserId : ANONYMOUS_USER_ID );
	}

	function getDisplayUrl( $pUserName=NULL, $pMixed=NULL ) {
		if( empty( $pUserName ) && !empty( $this ) && $this->isValid() ) {
			$pUserName = $this->mUsername;
		}
		if( function_exists( 'override_user_url' ) ) {
			$ret = override_user_url( $pUserName );
		} else {
			global $gBitSystem;

			$rewrite_tag = $gBitSystem->isFeatureActive( 'pretty_urls_extended' ) ? 'view/':'';

			if ($gBitSystem->isFeatureActive( 'pretty_urls' )
			|| $gBitSystem->isFeatureActive( 'pretty_urls_extended' ) ) {
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
		return BitUser::getDisplayName( FALSE, $pHash );
	}

	/**
	* Get user information for a particular user
	*
	* @param pUseLink return the information in the form of a url that links to the users information page
	* @param pHash todo - need explanation on how to use this...
	* @return display name or link to user information page
	**/
	function getDisplayName( $pUseLink = FALSE, $pHash=NULL ) {
		global $gBitSystem, $gBitUser;
		$ret = NULL;
		if( empty( $pHash ) && !empty( $this ) && !empty( $this->mInfo )) {
			$pHash = &$this->mInfo;
		}

		if( !empty( $pHash )) {
			if( !empty( $pHash['real_name'] ) && $gBitSystem->getConfig( 'users_display_name', 'real_name' ) == 'real_name' ) {
				$displayName = $pHash['real_name'];
			} elseif( !empty( $pHash['user'] )) {
				$displayName = $pHash['user'];
			} elseif( !empty( $pHash['login'] )) {
				$displayName = $pHash['login'];
			} elseif( !empty( $pHash['email'] )) {
				$displayName = substr( $pHash['email'], 0, strpos( $pHash['email'], '@' ));
			} else {
				$displayName = $pHash['user_id'];
			}

			if( !empty( $pHash['user'] )) {
				$iHomepage = $pHash['user'];
			} elseif( !empty( $pHash['login'] )) {
				// user of 'login' is deprecated and eventually should go away!
				$iHomepage = $pHash['login'];
			} elseif( BitBase::verifyId( $pHash['user_id'] )) {
				$iHomepage = $pHash['user_id'];
			} elseif( !empty( $pHash['email'] )) {
				$iHomepage = $pHash['email'];
			} else {
				// this won't work right now, we need to alter userslib::interpret_home() to interpret a real name
				$iHomepage = $pHash['real_name'];
			}

			if( $pUseLink && $gBitUser->hasPermission( 'p_users_view_user_homepage' )) {
				$ret = '<a class="username" title="'.( !empty( $pHash['link_title'] ) ? $pHash['link_title'] : tra( 'Visit the userpage of' ).': '.htmlspecialchars($displayName) )
					.'" href="'.BitUser::getDisplayUrl( $iHomepage ).'">'
					. htmlspecialchars((( isset( $pHash['link_label'] )) ? ( $pHash['link_label'] ) : ( $displayName )))
					.'</a>';
			} else {
				$ret = htmlspecialchars($displayName);
			}
		} else {
			$ret = tra( "Anonymous" );
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

  	function getSelectionList() {
		$query = "SELECT uu.`user_id`, uu.`login`, uu.`real_name`
				FROM `".BIT_DB_PREFIX."users_users` uu
				ORDER BY uu.`login`";
		
		$result = $this->mDb->query($query);
		$ret = array();
		while( $res = $result->fetchRow()) {
			$ret[$res['user_id']] = $res['login'] .' - '. $res['real_name'];
		}
		
		return $ret;
	}

	function getList( &$pParamHash ) {
		if ( empty( $pParamHash['sort_mode'] ) ) {
			$pParamHash['sort_mode'] = 'registration_date_desc';
		}

		LibertyContent::prepGetList( $pParamHash );
		$sort_mode = $this->mDb->convertSortmode($pParamHash['sort_mode']);
		// Return an array of users indicating name, email, last changed pages, versions, last_login
		if ( $pParamHash['find'] ) {
			$mid = " where UPPER(uu.`login`) LIKE ? OR UPPER(uu.real_name) LIKE ? OR UPPER(uu.email) LIKE ? ";
			$bindvars = array('%'.strtoupper( $pParamHash['find'] ).'%', '%'.strtoupper( $pParamHash['find'] ).'%', '%'.strtoupper( $pParamHash['find'] ).'%');
		} else {
			$mid = '';
			$bindvars = array();
		}
		$query = "SELECT uu.*, lc.`content_status_id`, tf_ava.`storage_path` AS `avatar_storage_path`
				FROM `".BIT_DB_PREFIX."users_users` uu
					INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (uu.`content_id`=lc.`content_id`)
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON ( lc.`content_id` = lch.`content_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_attachments` ta_ava ON ( uu.`avatar_attachment_id`=ta_ava.`attachment_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_files` tf_ava ON ( tf_ava.`file_id`=ta_ava.`foreign_id` )
				$mid ORDER BY $sort_mode";
		$query_cant = "select count(*) from `".BIT_DB_PREFIX."users_users` uu $mid";
		$result = $this->mDb->query($query, $bindvars, $pParamHash['max_records'], $pParamHash['offset']);

		$ret = array();
		global $gBitSystem;
		while( $res = $result->fetchRow() ) {
			if( !empty($res['avatar_storage_path'] ) ) {
				$res['avatar_url'] = $res['avatar_storage_path'];
				/* TODO: Make this a preference in the package */
				$res['thumbnail_url'] = liberty_fetch_thumbnail_url( $res['avatar_url'], 'avatar' );
			}
			$res["groups"] = $this->getGroups( $res['user_id'] );
			array_push( $ret, $res );
		}
		$retval = array();
		$pParamHash["data"] = $ret;

		$pParamHash["cant"] = $this->mDb->getOne($query_cant,$bindvars);

		LibertyContent::postGetList( $pParamHash );

		return $ret;
	}

	function isSemaphoreSet( $pSemName, $pLimit ) {
		global $gBitSystem;
		$now = $gBitSystem->getUTCTime();
		$lim = $now - $pLimit;
		$query = "delete from `".BIT_DB_PREFIX."users_semaphores` where `sem_name`=? and `created`<?";
		$result = $this->mDb->query($query,array( $pSemName, (int)$lim) );
		$query = "select `sem_name`  from `".BIT_DB_PREFIX."users_semaphores` where `sem_name`=?";
		$result = $this->mDb->query($query,array($pSemName));
		return $result->numRows();
	}

	function hasSemaphoreConflict( $pSemName, $pLimit ) {
		global $gBitSystem;
		$ret = NULL;
		$userId = $this->isValid() ? $this->mUserId : ANONYMOUS_USER_ID;
		$now = $gBitSystem->getUTCTime();
		$lim = $now - $pLimit;
		$query = "delete from `".BIT_DB_PREFIX."users_semaphores` where `sem_name`=? and `created`<?";
		$result = $this->mDb->query($query,array( $pSemName, (int)$lim) );
		$query = "SELECT uu.`login`, uu.`real_name`, uu.`email`, uu.`user_id`
				  FROM `".BIT_DB_PREFIX."users_semaphores` ls INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON( uu.`user_id`=ls.`user_id`)
				  WHERE `sem_name`=? AND ls.`user_id` <> ?";
		if( $ret = $this->mDb->getRow( $query, array( $pSemName, (int)$userId ) ) ) {
			$ret['nolink'] = TRUE;
		}
		return( $ret );
	}

	function storeSemaphore( $pSemName ) {
		if( !empty( $pSemName ) ) {
			global $gBitSystem;
			$userId = $this->isValid() ? $this->mUserId : ANONYMOUS_USER_ID;
			$now = $gBitSystem->getUTCTime();
			//	$cant=$this->mDb->getOne("select count(*) from `".BIT_DB_PREFIX."users_semaphores` where `sem_name`='$pSemName'");
			$query = "delete from `".BIT_DB_PREFIX."users_semaphores` where `sem_name`=?";
			$this->mDb->query($query,array($pSemName));
			$query = "insert into `".BIT_DB_PREFIX."users_semaphores`(`sem_name`,`created`,`user_id`) values(?,?,?)";
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
