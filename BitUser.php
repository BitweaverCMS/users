<?php
/**
 * Lib for user administration, groups and permissions
 * This lib uses pear so the constructor requieres
 * a pear DB object

 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See below for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
 *
 * @package users
 */

/**
 * required setup
 */
require_once( LIBERTY_PKG_PATH.'LibertyMime.php' );
require_once( USERS_PKG_PATH.'users_lib.php' );

define( 'AVATAR_TYPE_CENTRALIZED', 'c' );
define( 'AVATAR_TYPE_USER_DB', 'u' );
define( 'AVATAR_TYPE_LIBRARY', 'l' );

// Column sizes for users_users table
define( 'REAL_NAME_COL_SIZE', 64 );

define( 'BITUSER_CONTENT_TYPE_GUID', 'bituser' );

// some  definitions for helping with authentication
define( "USER_VALID", 2 );
define( "SERVER_ERROR", -1 );
define( "PASSWORD_INCORRECT", -3 );
define( "USER_NOT_FOUND", -5 );
define( "ACCOUNT_DISABLED", -6 );

/**
 * Class that holds all information for a given user
 *
 * @author   spider <spider@steelsun.com>
 * @package  users
 * @subpackage  BitUser
 */
class BitUser extends LibertyMime {
	public $mUserId;
	public $mUsername;
	public $mGroups;
	public $mTicket;
	public $mAuth;

	/**
	 * Constructor - will automatically load all relevant data if passed a user string
	 *
	 * @access public
	 * @author Christian Fowler <spider@viovio.com>
	 * @return returnString
	 */
	function __construct( $pUserId=NULL, $pContentId=NULL ) {
		parent::__construct();
		$this->mContentTypeGuid = BITUSER_CONTENT_TYPE_GUID;
		$this->registerContentType(
			BITUSER_CONTENT_TYPE_GUID, array(
				'content_type_guid'   => BITUSER_CONTENT_TYPE_GUID,
				'content_name'        => 'User Information',
				'content_name_plural' => 'User Information',
				'handler_class'       => 'BitUser',
				'handler_package'     => 'users',
				'handler_file'        => 'BitUser.php',
				'maintainer_url'      => 'http://www.bitweaver.org'
			)
		);
		$this->mUserId = ( @$this->verifyId( $pUserId ) ? $pUserId : NULL);
		$this->mContentId = $pContentId;
	}

	public function __sleep() {
		return array_merge( parent::__sleep(), array( 'mUserId', 'mUsername', 'mGroups', 'mTicket', 'mAuth' ) );
	}

	public function getCacheKey() {
		$siteCookie = static::getSiteCookieName();
		if( $this->isRegistered() && !empty( $_COOKIE[$siteCookie] ) ) { 
			return $_COOKIE[$siteCookie];
		} else {
			return ANONYMOUS_USER_ID;
		}
	}

	public static function isCacheableClass() {
		global $gBitSystem;
		return !$gBitSystem->isLive(); // only cache user objects in test mode for now
	}

	/**
	 * Determines if a user object is cacheable. Out of paranoia, admin's are never cached.
	 * @return boolean if object can be cached
	 */
	public function isCacheableObject() {
		global $gBitSystem;
		return parent::isCacheableObject() && (!$this->isAdmin() || $gBitSystem->isLive()); // Do not cache admin object for live sites per paranoia
	}

	/**
	 * Validate inbound sort_mode parameter
	 * @return array of fields which are valid sorts
	 */
	public static function getSortModeFields() {
		$fields = parent::getSortModeFields();
		$fields[] = 'map_position';
		return $fields;
	}

	/**
	 * load - loads all settings & preferences for this user
	 *
	 * @param boolean $pFull Load additional user data like
	 * @param string $pUserName User login name
	 * @access public
	 * @author Chrstian Fowler <spider@steelsun.com>
	 * @return returnString
	 */
	function load( $pFull=TRUE, $pUserName=NULL ) {
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
			$query = "
				SELECT uu.*,
						lf_ava.`file_name` AS `avatar_file_name`,  la_ava.`attachment_id` AS `avatar_attachment_id`, lf_ava.`mime_type` AS `avatar_mime_type`,
						lf_por.`file_name` AS `portrait_file_name`, ta_por.`attachment_id` AS `portrait_attachment_id`, lf_por.`mime_type` AS `portrait_mime_type`,
						lf_logo.`file_name` AS `logo_file_name`, ta_logo.`attachment_id` AS `logo_attachment_id`, lf_logo.`mime_type` AS `logo_mime_type`
					  $fullSelect, uu.`user_id` AS `uu_user_id`
				FROM `".BIT_DB_PREFIX."users_users` uu
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_attachments` la_ava ON ( uu.`avatar_attachment_id`=la_ava.`attachment_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_files` lf_ava ON ( lf_ava.`file_id`=la_ava.`foreign_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_attachments` ta_por ON ( uu.`portrait_attachment_id`=ta_por.`attachment_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_files` lf_por ON ( lf_por.`file_id`=ta_por.`foreign_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_attachments` ta_logo ON ( uu.`logo_attachment_id`=ta_logo.`attachment_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_files` lf_logo ON ( lf_logo.`file_id`=ta_logo.`foreign_id` )
					$fullJoin
				$whereSql";
			if(( $result = $this->mDb->query( $query, $bindVars )) && $result->numRows() ) {
				$this->mInfo = $result->fetchRow();
				$this->mInfo['user']          = $this->mInfo['login'];
				$this->mInfo['valid']         = @$this->verifyId( $this->mInfo['uu_user_id'] );
				$this->mInfo['user_id']       = $this->mInfo['uu_user_id'];
				$this->mInfo['is_registered'] = $this->isRegistered();
				foreach( array( 'portrait', 'avatar', 'logo' ) as $img ) {
					$this->mInfo[$img.'_path']   = $this->getSourceFile( array( 'user_id'=>$this->getField( 'user_id' ), 'package'=>liberty_mime_get_storage_sub_dir_name( array( 'mime_type' => $this->getField( $img.'_mime_type' ), 'name' =>  $this->getField( $img.'_file_name' ) ) ), 'file_name' => basename( $this->mInfo[$img.'_file_name'] ), 'sub_dir' =>  $this->getField( $img.'_attachment_id' ), 'mime_type' => $this->getField( $img.'_mime_type' ) ) );
					$this->mInfo[$img.'_url']    = liberty_fetch_thumbnail_url( array( 'source_file'=>$this->mInfo[$img.'_path'], 'size' => 'small', 'mime_image' => FALSE ));
				}

				// break the real name into first and last name using the last space as the beginning of the last name
				// for people who really want to use first and last name fields
				if( preg_match( '/ /', $this->mInfo['real_name'] ) ) {
					$this->mInfo['first_name'] = substr( $this->mInfo['real_name'], 0, strrpos($this->mInfo['real_name'], ' ') );
					$this->mInfo['last_name'] = substr( $this->mInfo['real_name'], strrpos($this->mInfo['real_name'], ' ')+1 );
				}else{
					// no spaces assign the real name to the first name
					$this->mInfo['first_name'] = $this->mInfo['real_name'];
				}

				$this->mUserId    = $this->mInfo['uu_user_id'];
				$this->mContentId = $this->mInfo['content_id'];
				$this->mUsername  = $this->mInfo['login'];
				// a few random security conscious unset's - SPIDER
				unset( $this->mInfo['user_password'] );
				unset( $this->mInfo['hash'] );

				$this->loadPreferences();
				// Load attachments
				LibertyMime::load();
				if( $this->getPreference( 'users_country' ) ) {
					$this->setPreference( 'flag', $this->getPreference( 'users_country' ) );
					$this->setPreference( 'users_country', str_replace( '_', ' ', $this->getPreference( 'users_country' ) ) );
				}
				if( $pFull ) {
					$this->mInfo['real_name'] = trim( $this->mInfo['real_name'] );
					$this->mInfo['display_name'] = (
						( !empty( $this->mInfo['real_name'] ) ? $this->mInfo['real_name'] :
						( !empty( $this->mUsername) ? $this->mUsername :
						( !empty( $this->mInfo['email'] ) ? substr( $this->mInfo['email'], 0, strpos( $this->mInfo['email'],'@' )) :
						$this->mUserId )))
					);
					//print("displayName: ".$this->mInfo['display_name']);
					$this->defaults();
					$this->mInfo['publicEmail'] = scramble_email( $this->mInfo['email'], ( $this->getPreference( 'users_email_display' ) ? $this->getPreference( 'users_email_display' ) : NULL ) );
				}
				$this->mTicket = substr( md5( session_id() . $this->mUserId ), 0, 20 );
			} else {
				$this->mUserId = NULL;
			}
		}
		if( !$gBitSystem->isFeatureActive( 'i18n_browser_languages' ) ) {
			global $gBitLanguage, $gBitUser;
			//change language only if if logged user is this user
			//otherwise it's just logged user (lang A) watching other user's page (lang B) and don't change
			if( $this->mUserId && $this->mUserId != ANONYMOUS_USER_ID && $gBitUser === $this) {
				$gBitLanguage->mLanguage = $this->getPreference( 'bitlanguage', $gBitLanguage->mLanguage );
			} elseif( isset( $_SESSION['bitlanguage'] )) {
				// users not logged that change the preference
				$gBitLanguage->mLanguage = $_SESSION['bitlanguage'];
			}
		}
		return( $this->isValid() );
	}

	/**
	 * defaults set a default set of preferences in mPrefs for new users
	 *
	 * @access public
	 * @return void
	 */
	function defaults() {
		global $gBitSystem, $gBitThemes, $gBitLanguage;
		if( !$this->getPreference( 'users_information' ) ) {
			$this->setPreference( 'users_information', 'public' );
		}
		if( !$this->getPreference( 'messages_allow_messages' ) ) {
			$this->setPreference( 'messages_allow_messages', 'y' );
		}
		if( !$this->getPreference( 'site_display_utc' ) ) {
			$this->setPreference( 'site_display_utc', 'Local' );
		}
/*
 * site_display_timezone is not used for 'Local' time display so daylight saving offset is not available
 * both of these should pick up the 'site default' values
		if( !$this->getPreference( 'site_display_timezone' ) ) {
			$this->setPreference( 'site_display_timezone', 'UTC' );
		}
 */
 		if( !$this->getPreference( 'bitlanguage' ) ) {
			$this->setPreference( 'bitlanguage', $gBitLanguage->mLanguage );
		}
		if( !$this->getPreference( 'theme' ) ) {
			$this->setPreference( 'theme', $gBitThemes->getStyle() );
		}
	}

	/**
	 * verify store hash
	 *
	 * @param array $pParamHash Data to be verified
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function verify( &$pParamHash ) {
		global $gBitSystem;

		trim_array( $pParamHash );

		// DO NOT REMOVE - to allow specific setting of the user_id during the first store.
		// used by ROOT_USER_ID or ANONYMOUS_USER_ID during install.
		if( @$this->verifyId( $pParamHash['user_id'] ) ) {
			$pParamHash['user_store']['user_id'] = $pParamHash['user_id'];
		}
		// require login
		if( !empty( $pParamHash['login'] ) && $pParamHash['login'] != $this->getField( 'login' ) ) {
			$pParamHash['login'] = strip_tags($pParamHash['login']);
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
		// some people really like using first and last names
		// push them into real_name
		if( !empty( $pParamHash['first_name'] ) ) {
			$pParamHash['real_name'] = $pParamHash['first_name'];
		}
		if( !empty( $pParamHash['last_name'] ) ) {
			$pParamHash['real_name'] = !empty( $pParamHash['real_name'] )?$pParamHash['real_name']." ":'';
			$pParamHash['real_name'] .= $pParamHash['last_name'];
		}
		// real_name
		if( !empty( $pParamHash['real_name'] ) ) {
			$pParamHash['user_store']['real_name'] = substr( strip_tags($pParamHash['real_name']), 0, 64 );
		}
		// require email
		if( !empty( $pParamHash['email'] ) ) {
			// LOWER CASE all emails
			$pParamHash['email'] = strtolower( $pParamHash['email'] );
			if( $emailResult = $this->verifyEmail( $pParamHash['email'] , $this->mErrors) ) {
				$pParamHash['verified_email'] = ($emailResult === true);
			}
		}
		// check some new user requirements
		if( !$this->isRegistered() ) {
			if( empty( $pParamHash['login'] ) ) {
				// choose a login based on the username in the email
				if( empty($pParamHash['email']) ){
					// obviously if they didnt enter an email address we cant help them out
					$this->mErrors['email'] = tra( 'You must enter your email address' );
				}else{
					$loginBase = preg_replace( '/[^A-Za-z0-9_]/', '', substr( $pParamHash['email'], 0, strpos( $pParamHash['email'], '@' ) ) );
					$login = $loginBase;
					do {
						if( $loginTaken = $this->userExists( array( 'login' => $login ) ) ) {
							$login = $loginBase.rand(100,999);
						}
					} while( $loginTaken );
					$pParamHash['login'] = $login;
				}
			}
			if( empty( $pParamHash['registration_date'] ) ) {
				$pParamHash['registration_date'] = date( "U" );
			}
			$pParamHash['user_store']['registration_date'] = $pParamHash['registration_date'];

			if( !empty( $pParamHash['email'] ) && empty($this->mErrors['email']) ) {
				$pParamHash['user_store']['email'] = substr( $pParamHash['email'], 0, 200 ) ;
			}elseif( empty($pParamHash['email']) ){
				$this->mErrors['email'] = tra( 'You must enter your email address' );
			}

			if( $gBitSystem->isFeatureActive( 'users_validate_user' ) ) {
				$pParamHash['user_store']['provpass'] = md5(BitSystem::genPass());
				$pParamHash['pass_due'] = 0;
			} elseif( empty( $pParamHash['password'] ) ) {
				$this->mErrors['password'] = tra( 'Your password should be at least '.$gBitSystem->getConfig( 'users_min_pass_length', 4 ).' characters long' );
			}
		} elseif( $this->isValid() ) {
			// Prevent losing user info on save
			if( empty( $pParamHash['edit'] ) ) {
				$pParamHash['edit'] = $this->mInfo['data'];
			}
		}

		if( isset( $pParamHash['password'] ) ) {
            if( isset( $pParamHash["password2"] ) && $pParamHash["password"] != $pParamHash["password2"] ) {
                $passwordErrors['password2'] = tra("The passwords didn't match");
            }
			if( ( !$this->isValid() || isset( $pParamHash['password'] ) ) && $error = $this->verifyPasswordFormat( $pParamHash['password'] ) ) {
				$passwordErrors['password'] = $error;
			}
			if( !empty( $passwordErrors ) ) {
				$this->mErrors = array_merge( $this->mErrors,$passwordErrors );
			} else {
				// Generate a unique hash
				//$pParamHash['user_store']['hash'] = md5( strtolower( (!empty($pParamHash['login'])?$pParamHash['login']:'') ).$pPassword.$pParamHash['email'] );
				$pParamHash['user_store']['hash'] = md5( $pParamHash['password'] );
				$now = $gBitSystem->getUTCTime();
				// set password due date
				// if no pass_due and no user_pass_due value user will never have to update the password
				if( empty( $pParamHash['pass_due'] ) && $gBitSystem->getConfig('users_pass_due') ) {
					// renew password according to config value
					$pParamHash['user_store']['pass_due'] = $now + (60 * 60 * 24 * $gBitSystem->getConfig('users_pass_due') );
				} elseif( !empty( $pParamHash['pass_due'] ) ) {
					// renew password only next half year ;)
					$pParamHash['user_store']['pass_due'] = $now + (60 * 60 * 24 * $pParamHash['pass_due']);
				}
				if( $gBitSystem->isFeatureActive( 'users_clear_passwords' ) || !empty( $pParamHash['user_store']['provpass'] ) ) {
					$pParamHash['user_store']['user_password'] = $pParamHash['password'];
				}
			}
		}

		// if we have an error we get them all by checking parent classes for additional errors
		if( count( $this->mErrors ) > 0 ){
			parent::verify( $pParamHash );
		}

		return ( count($this->mErrors) == 0 );
	}

	/**
	 * answerCaptcha
	 *
	 * Determine if the submitted answer for the captcha is valid
	 */
	function answerCaptcha( $pParamHash ) {
		global $gBitSystem;
		// require catpcha
		// novalidation is set to yes if a user confirms his email is correct after tiki fails to validate it
		if( $gBitSystem->isFeatureActive( 'users_random_number_reg' ) ) {
			if( ( empty( $pParamHash['novalidation'] ) || $pParamHash['novalidation'] != 'yes' )
				&&( !isset( $_SESSION['captcha'] ) || $_SESSION['captcha'] != md5( $pParamHash['captcha'] ) ) )
			{
				$this->mErrors['captcha'] = "Wrong Answer";
			}
		}

		if( $gBitSystem->isFeatureActive( 'users_register_recaptcha' ) && (empty( $pParamHash['novalidation'] ) || $pParamHash['novalidation'] != 'yes') ) {
			require_once( USERS_PKG_PATH.'classes/recaptchalib.php' );
			if( !empty( $pParamHash["recaptcha_challenge_field"] ) && !empty( $pParamHash["recaptcha_response_field"] ) ) {
				$resp = recaptcha_check_answer ( $gBitSystem->getConfig( 'users_register_recaptcha_private_key' ), $_SERVER["REMOTE_ADDR"], $pParamHash["recaptcha_challenge_field"], $pParamHash["recaptcha_response_field"] );
				if( !$resp->is_valid ) {
					$this->mErrors['recaptcha'] = $resp->error;
				}
			} else {
				$this->mErrors['recaptcha'] = 'Wrong Answer';
			}
		}

		if( $gBitSystem->isFeatureActive( 'users_register_smcaptcha' ) && (empty( $pParamHash['novalidation'] ) || $pParamHash['novalidation'] != 'yes') ) {
			require_once( USERS_PKG_PATH.'classes/solvemedialib.php' );
			if( !empty( $pParamHash['adcopy_challenge'] ) && !empty( $pParamHash['adcopy_response'] ) ) {
				$solvemediaResponse = solvemedia_check_answer($gBitSystem->getConfig( 'users_register_smcaptcha_v_key' ), $_SERVER["REMOTE_ADDR"], $pParamHash["adcopy_challenge"], $pParamHash["adcopy_response"], $gBitSystem->getConfig( 'users_register_smcaptcha_h_key' ) );
				if( !$solvemediaResponse->is_valid ) {
					$this->mErrors['smcaptcha'] = $solvemediaResponse->error;
				}
			} else {
				$this->mErrors['smcaptcha'] = 'Wrong Answer';
			}
		}

		return ( count($this->mErrors) == 0 );
	}

	/**
	 * preRegisterVerify
	 *
	 * A collection of values to verify before a user can register
	 * Separated from BitUser::verify so that import verification can
	 * be processed with less rigor than user submitted requests
	 */
	function preRegisterVerify( &$pParamHash ) {
		global $gBitSystem;

		$this->answerCaptcha( $pParamHash );

		// require passcode
		if( $gBitSystem->isFeatureActive( 'users_register_require_passcode' ) ) {
			if( $pParamHash["passcode"] != $gBitSystem->getConfig( "users_register_passcode",md5( $this->genPass() ) ) ) {
				$this->mErrors['passcode'] = 'Wrong passcode! You need to know the passcode to register at this site';
			}
		}
		return ( count($this->mErrors) == 0 );
	}

	/**
	 * verifyPasswordFormat
	 *
	 * @param array $pPassword
	 * @param array $pPassword2
	 * @access public
	 * @return FALSE on success, Error string on failure
	 */
	function verifyPasswordFormat( $pPassword, $pPassword2=NULL ) {
		global $gBitSystem;

		$minPassword = $gBitSystem->getConfig( 'users_min_pass_length', 4 );
		if( strlen( $pPassword ) < $minPassword ) {
			return ( tra( 'Your password should be at least '.$minPassword.' characters long' ));
		}
		if( !empty( $pPassword2 ) && $pPassword != $pPassword2 ) {
			return( tra( 'The passwords do not match' ));
		}
		if( $gBitSystem->isFeatureActive( 'users_pass_chr_num' ) && ( !preg_match_all( "/[0-9]+/",$pPassword,$foo ) || !preg_match_all( "/[A-Za-z]+/",$pPassword,$foo ))) {
			return ( tra( 'Password must contain both letters and numbers' ));
		}

		return FALSE;
	}

	/**
	 * getSmtpResponse
	 *
	 * @param array $pConnect
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getSmtpResponse( &$pConnect ) {
		$out = "";
		while( 1 ) {
			$work = fgets( $pConnect, 1024 );
			$out .= $work;
			if( !preg_match( '/^\d\d\d-/',$work )) {
				break;
			}
		}
		return $out;
	}

	/**
	 * verifyEmail
	 *
	 * @param array $pEmail
	 * @return TRUE on success, FALSE on failure, or -1 if verifyMX had a connection failure - mErrors will contain reason for failure
	 */
	public function verifyEmail( $pEmail , &$pErrors ) {
		global $gBitSystem;

		// check for existing user first, so root@localhost doesn't get attempted to re-register
		if( !empty( $this ) && is_object( $this ) && $this->userExists( array( 'email' => $pEmail ) ) ) {
			$pErrors['email'] = 'The email address "'.$pEmail.'" has already been registered.';
		// during install we have some <user>@localhost as email address. we won't cause problems on those
		} elseif( $pEmail == 'root@localhost' || $pEmail == 'guest@localhost' ) {
			// nothing to do
		} elseif( !validate_email_syntax( $pEmail ) ) {
			$pErrors['email'] = 'The email address "'.$pEmail.'" is invalid.';
		} elseif( $gBitSystem->isFeatureActive( 'users_validate_email' ) ) {
			$mxErrors;
			$ret = $this->verifyMX( $pEmail, $mxErrors ) ;
			if ($ret === false)	{
				bit_error_log('INVALID EMAIL : '.$pEmail.' by '. $_SERVER['REMOTE_ADDR'] .' for '. $mxErrors['email']);
				$pErrors = array_merge( $pErrors, $mxErrors );
			}
		}

		if( !isset( $ret ) ) {
			$ret = ( count( $pErrors ) == 0 )  ;
		}

		return $ret;
	}

	/**
	 * verifyAnonEmail
	 *
	 * @param array $pEmail
	 * @return TRUE on success, FALSE on failure, or -1 if verifyMX had a connection failure - mErrors will contain reason for failure
	 */
	public static function verifyAnonEmail( $pEmail , &$pErrors ) {
		global $gBitSystem;

		// check for existing user first, so root@localhost doesn't get attempted to re-register
		if( $pEmail == 'root@localhost' || $pEmail == 'guest@localhost' ) {
			// nothing to do
		} elseif( !validate_email_syntax( $pEmail ) ) {
			$pErrors['email'] = 'The email address "'.$pEmail.'" is invalid.';
		}

		if( !isset( $ret ) ) {
			$ret = ( count( $pErrors ) == 0 )  ;
		}

		return $ret;
	}

	/**
	 * verifyMX
	 *
	 * @param array $pEmail
	 * @param array $pValidate
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function verifyMX( $pEmail, &$pErrors ) {

		global $gBitSystem, $gDebug;

		$HTTP_HOST=$_SERVER['SERVER_NAME'];
		$ret = false;

		if( validate_email_syntax( $pEmail ) ){
			list ( $Username, $domain ) = preg_split ("/@/",$pEmail);
			//checkdnsrr will check to see if there are any MX records for the domain
			if( !is_windows() and checkdnsrr ( $domain, "MX" ) )  {
				bitdebug( "Confirmation : MX record for {$domain} exists." );

				$MXWeights = array();

				getmxrr ( $domain, $MXHost, $MXWeights );
				$hosts = array();

				//create an array that combines the MXWeights with their associated hosts
				for( $i = 0; $i < count( $MXHost ); $i++ ) {
					$hosts[$MXHost[$i]] = $MXWeights[$i];
				}

				//sorts the hosts by weight
				asort($hosts);

				if( !empty($hosts)) {	//hosts shouldn't be empty here, since we passed the checkdnsrr check, but the server COULD have died between the first and second check.
					$Connect = '' ;
					foreach ($hosts as $host=>$priority){

						$Connect = @fsockopen ( $host, 25, $errNo, $errStr, 10 ); // 10 second timeout to open each MX server, seems adequate to me, increase as necessary
						// Success in fsockopen

						if( $Connect ) {
							bitdebug( "Connection succeeded to {$host} SMTP." );


							stream_set_timeout( $Connect, 30 );
							$out = $this->getSmtpResponse( $Connect );

							// Judgment is that a service preparing to begin a transaction will send a 220 string after a succesful handshake
							if( preg_match ( "/^220/", $out ) ) {
								// Inform client's reaching to server who connect.
								if( $gBitSystem->hasValidSenderEmail() ) {
									$senderEmail = $gBitSystem->getConfig( 'site_sender_email' );
									fputs( $Connect, "HELO $HTTP_HOST\r\n" );
									bitdebug( "Run : HELO $HTTP_HOST" );
									// Receive server's answering cord.
									$out = $this->getSmtpResponse( $Connect );

									// Inform sender's address to server.
									fputs ( $Connect, "MAIL FROM: <{$senderEmail}>\r\n" );
									bitdebug( "Run : MAIL FROM: &lt;{$senderEmail}&gt;" );
									// Receive server's answering cord.
									$from = $this->getSmtpResponse( $Connect );

									// Inform listener's address to server.
									fputs ( $Connect, "RCPT TO: <{$pEmail}>\r\n" );
									bitdebug( "Run : RCPT TO: &lt;{$pEmail}&gt;" );
									// Receive server's answering cord.
									$to = $this->getSmtpResponse( $Connect );

									// Finish connection.
									fputs( $Connect, "QUIT\r\n" );
									bitdebug( "Run : QUIT" );
									fclose( $Connect );

									//Checks if we received a 250 OK from the server. If we did not, the server is telling us that this address is not a valid mailbox.
									if( !preg_match ( "/^250/", $from ) || ( !preg_match ( "/^250/", $to ) && !preg_match( "/Please use your ISP relay/", $to ))) {
										$pErrors['email']   = $pEmail." is not recognized by the mail server. Try double checking the address for typos." ;
										bit_error_log("INVALID EMAIL : ".$pEmail." SMTP FROM : ".$from." SMTP TO: ".$to);
										$ret = false;
										break; //break out of foreach and fall through to the end of function
									}else{
										$ret = true;//address has been verified by the server, no more checking necessary
										break;
									}
								}
							} elseif( preg_match ( "/^420/", $out ) ) {
								// Yahoo has a bad, bad habit of issuing 420's
								bit_error_log("UNKNOWN EMAIL : ".$pEmail." SMTP response: ".$out);
								$ret = true;
							} else {
								$pErrors['email'] = 'Connection rejected by MX server';
								bit_error_log("INVALID EMAIL : ".$pEmail." SMTP response: ".$out);
								$ret = false;
							}
						} else {
							//fsockopen failed
							if(!$gBitSystem->getConfig('users_validate_email_group')){ //will ONLY stuff mErrors if you have not set a default group for verifiable emails, otherwise this is not a game breaking case
								$pErrors['email'] = "One or more mail servers not responding";
							}
							$ret = -1; //-1 implies ambiguity, MX servers found, but unable to be reached.
						}
					}
				}else{
					$pErrors['email'] = "Mail server not found";
					$ret = false;
				}
			} else {
				$pErrors['email'] = "Mail server not found";
				$ret = false;
			}
		} else {
			$pErrors['email'] = "Invalid email syntax";
			$ret = false;
		}
		return $ret;
	}

	/**
	 * register - will handle everything necessary for registering a user and sending appropriate emails, etc.
	 *
	 * @access public
	 * @author Christian Fowler<spider@viovio.com>
	 * @return TRUE on success, FALSE on failure
	 */
	function register( &$pParamHash, $pNotifyRegistrant=TRUE ) {
		global $notificationlib, $gBitSmarty, $gBitSystem;
		$ret = FALSE;
		if( !empty( $_FILES['user_portrait_file'] ) && empty( $_FILES['user_avatar_file'] ) ) {
			$pParamHash['user_auto_avatar'] = TRUE;
		}
		if( $this->verify( $pParamHash )) {
			for( $i = 0; $i < BaseAuth::getAuthMethodCount(); $i++ ) {
				$instance = BaseAuth::init( $i );
				if( $instance && $instance->canManageAuth() ) {
					if( $userId = $instance->createUser( $pParamHash )) {
						$this->mUserId = $userId;
						break;
					} else {
						$this->mErrors = array_merge( $this->mErrors, $instance->mErrors );
						return FALSE;
					}
				}
			}

			if( !empty( $pParamHash['verified_email'] ) && $pParamHash['verified_email'] && $gBitSystem->getConfig('users_validate_email_group') ) {
				BitPermUser::addUserToGroup( $this->mUserId, $gBitSystem->getConfig('users_validate_email_group') );
			}

			$this->mLogs['register'] = 'New user registered.';
			$ret = TRUE;

			$this->load( FALSE, $pParamHash['login'] );

			require_once( KERNEL_PKG_PATH.'notification_lib.php' );
			$notificationlib->post_new_user_event( $pParamHash['login'] );

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

			// Send notification
			if( $pNotifyRegistrant ) {
				$siteName = $gBitSystem->getConfig('site_title', $_SERVER['HTTP_HOST'] );
				$gBitSmarty->assign( 'siteName',$_SERVER["SERVER_NAME"] );
				$gBitSmarty->assign( 'mail_site',$_SERVER["SERVER_NAME"] );
				$gBitSmarty->assign( 'mail_user',$pParamHash['login'] );
				if( $gBitSystem->isFeatureActive( 'users_validate_user' ) ) {
					// $apass = addslashes(substr(md5($gBitSystem->genPass()),0,25));
					$apass = $pParamHash['user_store']['provpass'];
					$foo  = parse_url( $_SERVER["REQUEST_URI"] );
					$foo1 = str_replace( "register", "confirm", $foo["path"] );
					$machine = httpPrefix().$foo1;

					// Send the mail
					$gBitSmarty->assign( 'msg',tra( 'You will receive an email with information to login for the first time into this site' ));
					$gBitSmarty->assign( 'mail_machine',$machine );
					$gBitSmarty->assign( 'mailUserId',$this->mUserId );
					$gBitSmarty->assign( 'mailProvPass',$apass );
					$mail_data = $gBitSmarty->fetch( 'bitpackage:users/user_validation_mail.tpl' );
					mail( $pParamHash["email"], $siteName.' - '.tra( 'Your registration information' ), $mail_data, "From: ".$gBitSystem->getConfig('site_sender_email')."\nContent-type: text/plain;charset=utf-8\n" );
					$gBitSmarty->assign( 'showmsg', 'y' );

					$this->mLogs['confirm'] = 'Validation email sent.';
				} elseif( $gBitSystem->isFeatureActive( 'send_welcome_email' ) ) {
					// Send the welcome mail
					$gBitSmarty->assign( 'mailPassword',$pParamHash['password'] );
					$gBitSmarty->assign( 'mailEmail',$pParamHash['email'] );
					$mail_data = $gBitSmarty->fetch( 'bitpackage:users/welcome_mail.tpl' );
					mail( $pParamHash["email"], tra( 'Welcome to' ).' '.$siteName, $mail_data, "From: ".$gBitSystem->getConfig( 'site_sender_email' )."\nContent-type: text/plain;charset=utf-8\n" );

					$this->mLogs['welcome'] = 'Welcome email sent.';
				}
			}
			$logHash['action_log']['title'] = $pParamHash['login'];
			$this->storeActionLog( $logHash );
		}
		return( $ret );
	}

	/**
	 * verifyCaptcha
	 *
	 * @param array $pCaptcha
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
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


	/**
	 * store
	 *
	 * @param array $pParamHash
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function store( &$pParamHash ) {
		if( $this->verify( $pParamHash ) ) {
			$this->StartTrans();
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
			if( LibertyMime::store( $pParamHash ) ) {

				if( empty( $this->mInfo['content_id'] ) || ($pParamHash['content_id'] != $this->mInfo['content_id']) ) {
					$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `content_id`=? WHERE `user_id`=?";
					$result = $this->mDb->query( $query, array( $pParamHash['content_id'], $this->mUserId ) );
					$this->mInfo['content_id'] = $pParamHash['content_id'];
				}
			}

			$this->CompleteTrans();

			$this->load( TRUE );
		}
		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * Imports a user record from csv file
	 * This is a admin specific function
	 *
	 * @param $pParamHash an array with user data
	 * @return TRUE if import succeed
	 **/
	function importUser( &$pParamHash ) {
		global $gBitUser;

		if( ! $gBitUser->hasPermission( 'p_users_admin' ) ) {
			return FALSE;
		}
		if( $this->verifyUserImport( $pParamHash ) ) {
			$this->StartTrans();
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
			if( LibertyContent::store( $pParamHash )) {
				if( empty( $this->mInfo['content_id'] ) || $pParamHash['content_id'] != $this->mInfo['content_id'] )  {
					$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `content_id`=? WHERE `user_id`=?";
					$result = $this->mDb->query( $query, array( $pParamHash['content_id'], $this->mUserId ) );
					$this->mInfo['content_id'] = $pParamHash['content_id'];
				}
			}

			$this->CompleteTrans();

			// store any uploaded images
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
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
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
						$this->mErrors['email'] = 'Cannot find a valid mail server';
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
				if( strlen( $pParamHash['hash'] ) <> 32 ) {
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
				$pParamHash['user_store']['user_password'] = '';
			}
		} elseif( !empty( $pParamHash['hash'] )) {
			$pParamHash['user_store']['hash'] = $pParamHash['hash'];
			$now = $gBitSystem->getUTCTime();
			if( !isset( $pParamHash['pass_due'] ) && $gBitSystem->getConfig( 'users_pass_due' )) {
				$pParamHash['user_store']['pass_due'] = $now + ( 60 * 60 * 24 * $gBitSystem->getConfig( 'users_pass_due' ));
			} elseif( isset( $pParamHash['pass_due'] ) ) {
				// renew password only next half year ;)
				$pParamHash['user_store']['pass_due'] = $now + ( 60 * 60 * 24 * $pParamHash['pass_due'] );
			}
		}
		return ( count( $this->mErrors ) == 0 );
	}

	/**
	 * expunge removes user and associated private data
	 *
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function expunge( $pExpungeContent = NULL ) {
		global $gBitSystem;
		$this->invokeServices( 'users_expunge_check_function' );
		if( !empty( $this->mErrors['expunge_check'] ) ) {
			$this->mDb->RollbackTrans();
		} else {
			$this->StartTrans();

			if( !empty( $pExpungeContent ) ) {
				if( $pExpungeContent == 'all' ) {
					if( $userContent = $this->mDb->getAssoc( "SELECT content_id, content_type_guid FROM `".BIT_DB_PREFIX."liberty_content` WHERE `user_id`=? AND `content_type_guid` != 'bituser'", array( $this->mUserId ) ) ) {
						foreach( $userContent as $contentId=>$contentTypeGuid ) {
							if( $delContent = static::getLibertyObject( $contentId, $contentTypeGuid ) ) {
								$delContent->expunge();
							}
						}
					}
				}
			}

			if( $this->mUserId != ANONYMOUS_USER_ID ) {
				$this->purgeImage( 'avatar' );
				$this->purgeImage( 'portrait' );
				$this->purgeImage( 'logo' );
				$this->invokeServices( 'users_expunge_function' );
				$userTables = array(
					'users_cnxn',
					'users_watches',
					'users_favorites_map',
					'users_users',
				);
				foreach( $userTables as $table ) {
					$query = "DELETE FROM `".BIT_DB_PREFIX.$table."` WHERE `user_id` = ?";
					$result = $this->mDb->query( $query, array( $this->mUserId ) );
				}

				parent::expunge();

				$logHash['action_log']['title'] = $this->mInfo['login'];
				$this->mLogs['user_del'] = 'User deleted';
				$this->storeActionLog( $logHash );
				$this->CompleteTrans();
				return TRUE;
			} else {
				$this->mDb->RollbackTrans();
				$gBitSystem->fatalError( tra( 'The anonymous user cannot be deleted' ) );
			}
		}
		return count( $this->mErrors ) === 0;
	}

	// {{{ ==================== Sessions and logging in and out methods ====================
	/**
	 * updateSession
	 *
	 * @param array $pSessionId
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	protected function updateSession( $pSessionId ) {
		global $gLightWeightScan;
		if ( !$this->isDatabaseValid() ) return true;
		global $gBitSystem, $gBitUser;
		$update['last_get'] = $gBitSystem->getUTCTime();
		$update['current_view'] = $_SERVER['SCRIPT_NAME'];

		if( empty( $gLightWeightScan ) ) {
			$row = $this->mDb->getRow( "SELECT `last_get`, `connect_time`, `get_count`, `user_agent`, `current_view` FROM `".BIT_DB_PREFIX."users_cnxn` WHERE `cookie`=? ", array( $pSessionId ) );
			if( $gBitUser->isRegistered() ) {
				$update['user_id'] = $gBitUser->mUserId;
			}
			if( $row ) {
				if( empty( $row['ip'] ) || $row['ip'] != $_SERVER['REMOTE_ADDR'] ) {
					$update['ip'] = $_SERVER['REMOTE_ADDR'];
				}
				if( !empty( $_SERVER['HTTP_USER_AGENT'] ) && (empty( $row['user_agent'] ) || $row['user_agent'] != $_SERVER['HTTP_USER_AGENT']) ) {
					$update['user_agent'] = (string)substr( $_SERVER['HTTP_USER_AGENT'], 0, 128 );
				}
				$update['get_count'] = $row['get_count'] + 1;
				$ret = $this->mDb->associateUpdate( BIT_DB_PREFIX.'users_cnxn', $update, array( 'cookie' => $pSessionId ) );
			} else {
				if( $this->isRegistered() ) {
					$update['user_id'] = $this->mUserId;
					$update['ip'] = $_SERVER['REMOTE_ADDR'];
					// truncate length & cast substr to (string) to prevent insert fatals if substr returns false
					$update['user_agent'] = (string)substr( $_SERVER['HTTP_USER_AGENT'], 0, 128 );
					$update['get_count'] = 1;
					$update['connect_time'] = $update['last_get'];
					$update['cookie'] = $pSessionId;
					$result = $this->mDb->associateInsert( BIT_DB_PREFIX.'users_cnxn', $update );
				}
			}
			// Delete old connections nightly during the hour of 3 am
			// This needs moving to an event that is known to happen
			if( date( 'H' ) == '03' && date( 'i' ) > 0 &&  date( 'i' ) < 2 ) {
				// Default to 30 days history
				$oldy = $update['last_get'] - ($gBitSystem->getConfig( 'users_cnxn_history_days', 30 ) * 24 * 60 * 60);
				$query = "DELETE from `".BIT_DB_PREFIX."users_cnxn` where `connect_time` < ?";
				$result = $this->mDb->query($query, array($oldy));
			}
		}
		return true;
	}

	/**
	 * countSessions
	 *
	 * @param array $pActive
	 * @access public
	 * @return count of sessions
	 */
	function countSessions( $pActive = FALSE ) {
		$query = "SELECT COUNT(*) FROM `".BIT_DB_PREFIX."users_cnxn`";
		if( $pActive ) {
			$query .=" WHERE `cookie` IS NOT NULL";
		}
		return $this->mDb->getOne( $query,array() );
	}

	/**
	 * logout
	 *
	 * @access public
	 * @return void
	 */
	function logout() {
		// This must come first
		$this->clearFromCache();

		$this->sendSessionCookie( FALSE );

		session_destroy();
		$this->mUserId = NULL;
		// ensure Guest default page is loaded if required
		$this->mInfo['default_group_id'] = -1;
	}

	protected function sendSessionCookie( $pCookie=TRUE ) {
		global $gBitSystem;

		$siteCookie = static::getSiteCookieName();
		$cookieTime = 0;
		$cookiePath = BIT_ROOT_URL;
		$cookieDomain = '';

		if( $pCookie === TRUE ) {
			$pCookie = session_id();
		} elseif( $pCookie==FALSE ) {
			$pCookie = ''; // unset the cookie, eg logout
			if( !empty( $_COOKIE[$siteCookie] ) ) {
				$this->mDb->query( "UPDATE `".BIT_DB_PREFIX."users_cnxn` SET `cookie`=NULL WHERE `cookie`=?", array( $_COOKIE[$siteCookie] ) );
				unset( $_COOKIE[$siteCookie] );
			}
		}

		if( !empty( $pCookie ) ) {
			// Now if the remember me feature is on and the user checked the user_remember_me checkbox then ...
			if( $gBitSystem->isFeatureActive( 'users_remember_me' ) && isset( $_REQUEST['rme'] ) && $_REQUEST['rme'] == 'on' ) {
				$cookieTime = (int)( time() + (int)$gBitSystem->getConfig( 'users_remember_time', 86400 ));
				$cookiePath = $gBitSystem->getConfig( 'cookie_path', $cookiePath );
				$cookieDomain = $gBitSystem->getConfig( 'cookie_domain', $cookieDomain );
			}
		}
		setcookie( $siteCookie, $pCookie, $cookieTime , $cookiePath, $cookieDomain );
		$_COOKIE[$siteCookie] = $pCookie;
	}

	public static function getSiteCookieName() {
		global $gBitSystem;

		$cookie_site = strtolower( preg_replace( "/[^a-zA-Z0-9]/", "", $gBitSystem->getConfig( 'site_title', 'bitweaver' )));
		return( 'bit-user-'.$cookie_site );
	}

	/**
	 * verifyTicket
	 *
	 * @param array $pFatalOnError
	 * @param array $pForceCheck
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function verifyTicket( $pFatalOnError=TRUE, $pForceCheck=TRUE ) {
		global $gBitSystem, $gBitUser;
		$ret = FALSE;
		if( $pForceCheck == TRUE || !empty( $_REQUEST['tk'] ) ) {
			if( empty( $_REQUEST['tk'] ) || (!($ret = $_REQUEST['tk'] == $this->mTicket ) && $pFatalOnError) ) {
				$userString = $gBitUser->isRegistered() ? "\nUSER ID: ".$gBitUser->mUserId.' ( '.$gBitUser->getField( 'email' ).' ) ' : '';
				error_log( tra( "Security Violation" )."$userString ".$_SERVER['REMOTE_ADDR']."\nURI: $_SERVER[REQUEST_URI] \nREFERER: $_SERVER[HTTP_REFERER] " );
				$gBitSystem->fatalError( tra( "Security Violation" ));
			}
		}
		return $ret;
	}
	// }}}

	// {{{ ==================== Banning ====================
	/**
	 * ban sets the user account status to -201 suspended
	 *
	 * @access public
	 * @return TRUE on success, Display error message on failure
	 */
	function ban(){
		global $gBitSystem;
		if( $this->mUserId == ANONYMOUS_USER_ID || $this->mUserId == ROOT_USER_ID || $this->isAdmin()) {
			$gBitSystem->fatalError( tra( 'You cannot ban the user' )." ".$this->mInfo['login'] );
		} else {
			$this->storeStatus( -201 );
			return TRUE;
		}
	}

	/**
	 * ban unban the user
	 *
	 * @access public
	 * @return TRUE on success
	 */
	function unban(){
		global $gBitSystem;
		$this->storeStatus( 50 );
		return TRUE;
	}
	// }}}

	/**
	 * genPass generate random password
	 *
	 * @param array $pLength Length of final password
	 * @access public
	 * @return password
	 */
	public static function genPass( $pLength=NULL ) {
		global $gBitSystem;
		$vocales = "AaEeIiOoUu13580";
		$consonantes = "BbCcDdFfGgHhJjKkLlMmNnPpQqRrSsTtVvWwXxYyZz24679";
		$ret = '';
		if( empty( $pLength ) || !is_numeric( $pLength ) ) {
			$pLength = $gBitSystem->getConfig( 'users_min_pass_length', 16 );
		}
		for( $i = 0; $i < $pLength; $i++ ) {
			if( $i % 2 ) {
				$ret .= $vocales{rand( 0, strlen( $vocales ) - 1 )};
			} else {
				$ret .= $consonantes{rand( 0, strlen( $consonantes ) - 1 )};
			}
		}
		return $ret;
	}

	/**
	 * generateChallenge
	 *
	 * @access public
	 * @return md5 string
	 */
	function generateChallenge() {
		return( md5( BitSystem::genPass() ));
	}

	/**
	 * login
	 *
	 * @param array $pLogin
	 * @param array $pPassword
	 * @param array $pChallenge
	 * @param array $pResponse
	 * @access public
	 * @return URL the user should be sent to after login
	 */
	function login( $pLogin, $pPassword, $pChallenge=NULL, $pResponse=NULL ) {
		global $gBitSystem;
		$isvalid = false;

		$loginCol = strpos( $pLogin, '@' ) ? 'email' : 'login';

		$this->StartTrans();
		// Verify user is valid
		if( $this->validate( $pLogin, $pPassword, $pChallenge, $pResponse )) {
			$userInfo = $this->getUserInfo( array( $loginCol => $pLogin ));

			// If the password is valid but it is due then force the user to change the password by
			// sending the user to the new password change screen without letting him use
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
				$this->loadPermissions( TRUE );

				$url = $this->getPostLoginUrl();

				$this->setUserSession();
			}
		} else {
			// before we give up lets see if the user exists and if the password is expired
			$query = "select `email`, `user_id`, `user_password` from `".BIT_DB_PREFIX."users_users` where " . $this->mDb->convertBinary(). " $loginCol = ?";
			$result = $this->mDb->getRow( $query, array( $pLogin ) );
			if( !empty( $result['user_id'] ) && $this->isPasswordDue( $result['user_id'] ) ) {
				// user needs email password reset so send it and let them know
				$url = USERS_PKG_URL.'remind_password.php?remind=y&required=y&username='.$pLogin;
			}else{
				$this->mUserId = ANONYMOUS_USER_ID;
				$this->mInfo = array();
				$this->clearFromCache();
				$this->mErrors['login'] = tra( 'Invalid username or password' );
				$url = USERS_PKG_URL.'login.php?error=' . urlencode( $this->mErrors['login'] );
			}
		}
		$this->CompleteTrans();

		// check for HTTPS mode and redirect back to non-ssl when not requested, or a  SSL login was forced
		if( isset( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS'] ) == 'on' ) {
			$refererSsl = isset( $_SERVER['HTTP_REFERER'] ) && substr( $_SERVER['HTTP_REFERER'], 0, 5 ) == 'https';
			if( ($gBitSystem->getConfig( 'site_https_login_required' ) && !$refererSsl) ) {
				// start setting up the URL redirect without SSL
				$prefix = 'http://' . $gBitSystem->getConfig( 'site_http_domain', $_SERVER['HTTP_HOST'] );

				// add port to prefix if needed
				$port   = $gBitSystem->getConfig( 'site_http_port', 80 );
				if( $port != 80 ) {
					$prefix .= ':'.$port;
				}
				$prefix .= $gBitSystem->getConfig( 'site_http_prefix', BIT_ROOT_URL );
				if( strrpos( $prefix, '/' ) == (strlen( $prefix )  - 1) ) {
					$prefix = substr( $prefix, 0, strlen( $prefix ) - 1 );
				}
				// join prefix and URL
				$url = $prefix.$url;
			}
		}
		return( $url );
	}

	public function getPostLoginUrl() {
		global $gBitSystem;
		$url = BIT_ROOT_URL;
		if( $this->isRegistered() ) {
			// set post-login url
			// if group home is set for this user we get that
			// default to general post-login
			// @see BitSystem::getIndexPage
			$indexType = 'my_page';
			// getGroupHome is BitPermUser method
			if( method_exists( $this, 'getGroupHome' ) &&
				(( @$this->verifyId( $this->mInfo['default_group_id'] ) && ( $group_home = $this->getGroupHome( $this->mInfo['default_group_id'] ) ) ) ||
				( $gBitSystem->getConfig( 'default_home_group' ) && ( $group_home = $this->getGroupHome( $gBitSystem->getConfig( 'default_home_group' ) ) ) )) ){
				$indexType = 'group_home';
			}

			$url = isset($_SESSION['loginfrom']) ? $_SESSION['loginfrom'] : $gBitSystem->getIndexPage( $indexType );
			unset( $_SESSION['loginfrom'] );
		}
		return $url;
	}
	public function setUserSession() {
		if( $this->isRegistered() ) {
			$sessionId = session_id();
			$this->sendSessionCookie( $sessionId );
			$this->updateSession( $sessionId );
		}
	}

	/**
	 * validate
	 *
	 * @param array $pUser
	 * @param array $pPass
	 * @param array $pChallenge
	 * @param array $pResponse
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 * @todo rewrite this mess. this is horrible stuff. - xing - Thursday Oct 16, 2008   09:47:20 CEST
	 */
	function validate( $pUser, $pPass, $pChallenge, $pResponse ) {
		global $gBitSystem;
		// these will help us keep tabs of what is going on
		$authValid = $authPresent = FALSE;
		$createAuth = ( $gBitSystem->getConfig( "users_create_user_auth", "n" ) == "y" );

		for( $i = 0; $i < BaseAuth::getAuthMethodCount(); $i++ ) {
			$instance = BaseAuth::init( $i );
			if( $instance ) {
				$result = $instance->validate( $pUser, $pPass, $pChallenge, $pResponse );
				switch( $result ) {
					case USER_VALID:
						unset($this->mErrors['login']);
						$authPresent = TRUE;
						$authValid = TRUE;
						break;
					case PASSWORD_INCORRECT:
						// this mErrors assignment is CRUCIAL so that bit auth fails properly. DO NOT FUCK WITH THIS unless you know what you are doing and have checked with me first. XOXOX - spiderr
						// This might have broken other auth, but at this point, bw auth was TOTALLY busted. If you need to fix, please come find me.
						$this->mErrors['login'] = 'Password incorrect';
						$authPresent = TRUE;
						break;
					case USER_NOT_FOUND:
						break;
				}

				if( $authValid ) {
					if( empty( $instance->mInfo['email'] )) {
						$instance->mInfo['email'] = $pUser;
					}

					//If we're given a user_id then the user is already in the database:
					if( !empty( $instance->mInfo['user_id'] )) {
						$this->mUserId = $instance->mInfo['user_id'];

					//Is the user already in the database:
					} elseif( $this->mDb->getOne( "SELECT COUNT(*) FROM `".BIT_DB_PREFIX."users_users` WHERE `login` = ?", array( $instance->mLogin )) > 0 ) {
						// Update Details
						$authUserInfo = array(
							'login'     => $instance->mInfo['login'],
							'password'  => $instance->mInfo['password'],
							'real_name' => $instance->mInfo['real_name'],
							'email'     => $instance->mInfo['email']
						);
						$userInfo = $this->getUserInfo( array( 'login' => $pUser ));
						$this->mUserId = $userInfo['user_id'];
						$this->store( $authUserInfo );
						$this->mErrors = array();

					} else {
						$authUserInfo = array(
							'login'     => $instance->mInfo['login'],
							'password'  => $instance->mInfo['password'],
							'real_name' => $instance->mInfo['real_name'],
							'email'     => $instance->mInfo['email']
						);
						// TODO somehow, mUserId gets set to -1 at this point - no idea how
						// set to NULL to prevent overwriting Guest user - wolff_borg
						$this->mUserId = NULL;
						$this->store( $authUserInfo );
					}

					if( $createAuth && $i > 0 ) {
						// if the user was logged into this system and we should progate users down other auth methods
						for( $j = $i; $i >= 0; $j-- ) {
							$probMethodName = $gBitSystem->getConfig( "users_auth_method_$j", $default );
							if( !empty( $probMethodName )) {
								$probInstance = BaseAuth::init( $probMethodName );
								if( $probInstance && $probInstance->canManageAuth() ) {
									$result = $probInstance->validate( $pUser, $pPass, $pChallenge, $pResponse );
									if( $result == USER_VALID || $result == PASSWORD_INCORRECT ) {
										// see if we can create a new account
										$userattr = $instance->getUserData();
										if( empty( $userattr['login'] )) {
											$userattr['login'] = $pUser;
										}
										if( empty( $userattr['password'] )) {
											$userattr['password'] = $pPass;
										}
										$probInstance->createUser( $userattr );
									}
								}
								$this->mErrors = array_merge( $this->mErrors, $probInstance->mErrors );
							}
						}
					}
					$this->mAuth = $instance;
					break;
				}
				$this->mErrors = array_merge( $this->mErrors,$instance->mErrors );
			}
		}
		if( $this->mUserId != ANONYMOUS_USER_ID ) {
			$this->load();
			//on first time login we run the users registation service
			if( $this->mInfo['last_login'] == NULL ) {
				$this->invokeServices( 'users_register_function' );
			}
			$this->updateLastLogin( $this->mUserId );
		}
		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * updateLastLogin
	 *
	 * @param array $pUserId
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function updateLastLogin( $pUserId ) {
		$ret = FALSE;
		if( @$this->verifyId( $pUserId ) ) {
			global $gBitSystem;
			$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `last_login` = `current_login`, `current_login` = ? WHERE `user_id` = ?";
			$result = $this->mDb->query( $query, array( $gBitSystem->getUTCTime(), $pUserId ));
			$ret = TRUE;
		}
		return $ret;
	}

	/**
	 * confirmRegistration
	 *
	 * @param array $pUserId
	 * @param array $pProvpass
	 * @access public
	 * @return registered user, empty array on failure
	 */
	function confirmRegistration( $pUserId, $pProvpass ) {
		global $gBitSystem;
		$query = "
			SELECT `user_id`, `provpass`, `user_password`, `login`, `email` FROM `".BIT_DB_PREFIX."users_users`
			WHERE `user_id`=? AND `provpass`=? AND ( `provpass_expires` IS NULL OR `provpass_expires` > ?)";
		return( $this->mDb->getRow( $query, array( (int)$pUserId, $pProvpass, $gBitSystem->getUTCTime() )));
	}

	/**
	 * changeUserEmail
	 *
	 * @param array $pUserId
	 * @param array $pEmail
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function changeUserEmail( $pUserId, $pEmail ) {
		if( !validate_email_syntax( $pEmail ) ) {
			$this->mErrors['bad_mail'] = tra( "The email address provided does not have recognised valid syntax." );
		} elseif( $this->userExists( array( 'email' => $pEmail ))) {
			$this->mErrors['duplicate_mail'] = tra( "The email address you selected already exists." );
		} else {
			$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `email`=? WHERE `user_id`=?";
			$result = $this->mDb->query( $query, array( $pEmail, $pUserId ) );
			$query = "UPDATE `".BIT_DB_PREFIX."users_watches` SET `email`=? WHERE `user_id`=?";
			$result = $this->mDb->query( $query, array( $pEmail, $pUserId ) );

			// update value in hash
			$this->mInfo['email'] = $pEmail;
		}
		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * lookupHomepage
	 *
	 * @param array $iHomepage
	 * @access public
	 * @return user_id that can be used to point to users homepage
	 */
	function lookupHomepage( $iHomepage ) {
		$ret = NULL;
		if( @$this->verifyId( $iHomepage )) {
			// iHomepage is the user_id for the user...
			$key = 'user_id';
			// force to proper integer to get things like "007." to properly query
			$iHomepage = (integer)$iHomepage;
		} elseif( substr( $iHomepage, 0, 7 ) == 'mailto:' ) {
			// iHomepage is the email address of the user...
			$key = 'email';
		} else {
			// iHomepage is the 'login' of the user...
			$key = 'login';
		}
		$tmpUser = $this->getUserInfo( array( $key => $iHomepage ));
		if( @$this->verifyId( $tmpUser['user_id'] )) {
			$ret = $tmpUser['user_id'];
		}
		return $ret;
	}

	/**
	 * getUserPreference
	 *
	 * @param array $pPrefName
	 * @param array $pPrefDefault
	 * @param array $pUserId
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	static function getUserPreference( $pPrefName, $pPrefDefault, $pUserId ) {
		// Alternate to LibertyContent::getPreference when all you have is a user_id and a pref_name, and you need a value...
		global $gBitDb;
		$ret = NULL;

		if( BitBase::verifyId( $pUserId ) ) {
			$query = "
				SELECT lcp.`pref_value` FROM `".BIT_DB_PREFIX."liberty_content_prefs` lcp INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (lcp.`content_id`=uu.`content_id`)
				WHERE uu.`user_id` = ? AND lcp.`pref_name` = ?";
			if( !$ret = $gBitDb->getOne( $query, array( $pUserId, $pPrefName ))) {
				$ret = $pPrefDefault;
			}
		}
		return $ret;
	}

	/**
	 * getUserInfo will fetch the user info of a given user
	 *
	 * @param array $pUserMixed hash key can be any column in users_users table e.g.: 'login', 'user_id', 'email', 'content_id'
	 * @access public
	 * @return user info on success, NULL on failure
	 */
	function getUserInfo( $pUserMixed ) {
		$ret = NULL;
		if( is_array( $pUserMixed ) ) {
			if( $val =  current( $pUserMixed ) ) {
				$key = $this->mDb->sanitizeColumnString( key( $pUserMixed ) );
				if( preg_match( '/_id$/', $key ) ) {
					$col = " uu.`".$key."` ";
					$val = (int)$val;
					if( $val > 0x1FFFFFFF ) {
						// 32 bit overflow, set to zero to avoid fatal error in databases with 32 bit signed integer columns
						$val = 0;
					}
				} elseif( is_numeric( $val ) ) {
					$col = " uu.`".$key."` ";
					$val = $val;
				} else {
					$col = "UPPER( uu.`".$key."` ) ";
					$val = strtoupper( $val );
				}
				if( !empty( $col ) ) {
					$query = "SELECT  uu.* FROM `".BIT_DB_PREFIX."users_users` uu LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (lc.`content_id`=uu.`content_id`) WHERE $col = ?";
					$ret = $this->mDb->getRow( $query, array( $val ), 600 );
				}
			}
		}
		return $ret;
	}

	/**
	 * isUserPublic Determine if an arbitrary user can be viewed by non-permissioned users.
	 *
	 * @param array $pUserId user_id of user to query visibility, if NULL will use this object
	 * @access public
	 * @return boolean if user is publically visible
	 */
	function isUserPrivate( $pUserId=NULL ) {
		$infoPref = NULL;
		if( BitBase::verifyId( $pUserId ) ) {
			$infoPref = BitUser::getUserPreference( 'users_information', NULL, $pUserId );
		} elseif( isset( $this ) && $this->isValid() ) {
			$infoPref = $this->getPreference( 'users_information' );
		}

		return $infoPref == 'private';
	}

	/**
	 * getByHash get user from cookie hash
	 *
	 * @param array $pHash
	 * @access public
	 * @return user info
	 */
	function getUserIdFromCookieHash( $pHash ) {
		$query = "SELECT `user_id` FROM `".BIT_DB_PREFIX."users_cnxn` WHERE `cookie` = ?";
		return $this->mDb->getOne( $query, array( $pHash ));
	}

	/**
	 * isPasswordDue work out if a user has to change their password
	 *
	 * @access public
	 * @return TRUE when the password is due, FALSE if it isn't, NULL when no due time is set
	 * @note NULL password due means *no* expiration
	 */
	function isPasswordDue( $pUserId = NULL ) {
		global $gBitSystem;
		$ret = FALSE;
		if( empty( $pUserId) && $this->isRegistered() ) {
			$pUserId = $this->mUserId;
		}
		if( !empty( $pUserId ) ){
			// get user_id to avoid NULL and zero confusion
			$query = "
				SELECT `user_id`, `pass_due`
				FROM `".BIT_DB_PREFIX."users_users`
				WHERE `pass_due` IS NOT NULL AND `user_id`=? ";
			$due = $this->mDb->getRow( $query, array( $pUserId ) );
			if( @$this->verifyId( $due['user_id'] ) && !empty( $due['pass_due'] ) ) {
				$ret = $due['pass_due'] <= $gBitSystem->getUTCTime();
			}
		}
		return $ret;
	}

	/**
	 * createTempPassword
	 *
	 * @param array $pLogin
	 * @param array $pPass
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function createTempPassword( $pLogin, $pPass ) {
		global $gBitSystem;
		$ret = array( '', '' );

		if( empty( $pLogin ) ) {
			$pLogin = $this->getField( 'email' );
		}

		if( !empty( $pLogin )) {
			$pass = BitSystem::genPass();
			$provpass = md5( $pass );
			$loginCol = strpos( $pLogin, '@' ) ? 'email' : 'login';

			#temp passwords good for 3 days -- prob should be an config option
			$passDue = $gBitSystem->getUTCTime() + ( 60 * 60 * 24 * 3 );
			$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `provpass` = ?, `provpass_expires` = ? WHERE `".$loginCol."` = ?";
			$result = $this->mDb->query( $query, array( $provpass, $passDue, $pLogin ));
			$ret = array( $pass, $provpass );
		}
		return $ret;
	}

	/**
	 * storePassword
	 *
	 * @param array $pPass
	 * @param array $pLogin
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function storePassword( $pPass, $pLogin=NULL ) {
		global $gBitSystem;
		$ret = FALSE;

		if( empty( $pLogin ) ) {
			$pLogin = $this->getField( 'email' );
		}

		if( !empty( $pLogin )) {
			$ret = TRUE;
			$hash = md5( $pPass );
			// if renew password config is set then set - otherwise set null to respect no pass due
			$passDue = NULL;
			if( $gBitSystem->getConfig('users_pass_due') ) {
				$now = $gBitSystem->getUTCTime();;
				// renew password according to config value
				$passDue = $now + ( 60 * 60 * 24 * $gBitSystem->getConfig( 'users_pass_due' ));
			}
			$pPass = NULL;
			$loginCol = strpos( $pLogin, '@' ) ? 'email' : 'login';
			$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `provpass`= NULL, `provpass_expires` = NULL,`hash`=? ,`user_password`=? ,`pass_due`=? WHERE `".$loginCol."`=?";
			$result = $this->mDb->query( $query, array( $hash, $pPass, $passDue, $pLogin ));
		}
		return $ret;
	}

	/**
	 * getUserActivity
	 *
	 * @param array $pListHash
	 * @access public
	 * @return array of users and what they have been up to
	 */
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
			$ips = split( ',', $pListHash['ip'] );
			$whereSql .= ' AND ( ';
			do {
				$ip = array_pop( $ips );
				$whereSql .= ' uc.`ip` = ? ';
				$bindVars[] = $ip;
				if( !empty( $ips ) ) {
					$whereSql .= ' OR ';
				}
			} while( $ips );
			$whereSql .= ' ) ';
		}

		if( !empty( $pListHash['online'] ) ) {
			$whereSql .= ' AND uc.`cookie` IS NOT NULL ';
		}

		$query = "
			SELECT DISTINCT uc.`user_id`, `login`, `real_name`, `connect_time`, `ip`, `user_agent`, `last_get`, uu.`content_id`
			FROM `".BIT_DB_PREFIX."users_cnxn` uc
				INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (uc.`user_id` = uu.`user_id`)
			WHERE uc.`user_id` IS NOT NULL $whereSql
			ORDER BY ".$this->mDb->convertSortmode( $pListHash['sort_mode'] );
		$result = $this->mDb->query( $query, $bindVars, $pListHash['max_records'], $pListHash['offset'] );
		$ret = array();
		while( $res = $result->fetchRow() ) {
			$res['users_information'] = 	$this->getPreference( 'users_information', 'public', $res['content_id'] );
			$ret[] = $res;
		}

		$countSql = "
			SELECT COUNT( uc.`user_id` )
			FROM `".BIT_DB_PREFIX."users_cnxn` uc
				INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON (uc.`user_id` = uu.`user_id`)
			WHERE uc.`user_id` IS NOT NULL $whereSql";
		$pListHash['cant'] = $this->mDb->GetOne( $countSql, $bindVars );
		$this->postGetList( $pListHash );
		return $ret;
	}

	/**
	 * getUserDomain
	 *
	 * @param array $pLogin
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getUserDomain( $pLogin ) {
		$ret = array();
		if( $pLogin == $this->getField( 'login' ) && $this->getPreference( 'domain_style' ) ) {
			$ret = $this->mInfo;
			$ret['style'] = $this->getPreference( 'domain_style' );
		} else {
			$sql = "
				SELECT uu.*, lcp.`pref_value` AS `style`
				FROM `".BIT_DB_PREFIX."users_users` uu
					INNER JOIN `".BIT_DB_PREFIX."liberty_content_prefs` lcp ON( uu.`content_id` = lcp.`content_id` )
				WHERE uu.`login` = ? AND lcp.`pref_name` = ?";
			$ret = $this->mDb->getRow( $sql,  array( $pLogin, 'domain_style' ) );
		}
		return( $ret );
	}

	/**
	 * getDomain
	 *
	 * @param array $pContentId
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getDomain( $pContentId ) {
		$ret = array();
		if( $this->verifyId( $pContentId ) ) {
			$ret['content_id'] = $pContentId;
			$ret['style'] = $this->mDb->getOne( "SELECT `pref_value` FROM `".BIT_DB_PREFIX."liberty_content_prefs` WHERE `content_id`=? AND `pref_name`=?", array( $pContentId, 'domain_style' ));
		}
		return( $ret );
	}

	/**
	 * canCustomizeTheme check if a user can customise their theme
	 *
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function canCustomizeTheme() {
		global $gBitSystem;
		return( $this->hasPermission( 'p_tidbits_custom_home_theme' ) || $gBitSystem->getConfig( 'users_themes' ) == 'y' || $gBitSystem->getConfig( 'users_themes' ) == 'h' || $gBitSystem->getConfig( 'users_themes' ) == 'u' );
	}

	/**
	 * canCustomizeLayout  check if a user can customise their layout
	 *
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function canCustomizeLayout() {
		global $gBitSystem;
		return( $this->hasPermission( 'p_tidbits_custom_home_layout' ) || $gBitSystem->getConfig( 'users_layouts' ) == 'y' || $gBitSystem->getConfig( 'users_layouts' ) == 'h' || $gBitSystem->getConfig( 'users_layouts' ) == 'u' );
	}



	// {{{ ==================== image and file functions ====================
	/**
	 * getThumbnailUrl
	 *
	 * @param string $pSize
	 * @param array $pInfoHash
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getThumbnailUrl( $pSize = 'small', $pInfoHash = NULL, $pSecondaryId = NULL, $pDefault=TRUE ) {
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
	 *     $_FILES['user_portrait_file']
	 *     $_FILES['user_auto_avatar']
	 *     $_FILES['user_logo_file']
	 *
	 * @param array $pParamHash array of options
	 * @param boolean $pParamHash['user_auto_avatar'] automatically create avatar from portrait
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function storeImages( $pParamHash ) {
		if( isset( $_FILES['user_portrait_file'] ) && is_uploaded_file( $_FILES['user_portrait_file']['tmp_name'] ) && $_FILES['user_portrait_file']['size'] > 0 ) {
			$portraitHash                          = $pParamHash;
			$portraitHash['user_id']               = $this->mUserId;
			$portraitHash['content_id']            = $this->mContentId;
			$portraitHash['upload']                = $_FILES['user_portrait_file'];
			$portraitHash['upload']['source_file'] = $_FILES['user_portrait_file']['tmp_name'];
			$this->storePortrait( $portraitHash, !empty( $portraitHash['user_auto_avatar'] ));
		}

		if( isset( $_FILES['user_avatar_file'] ) && is_uploaded_file( $_FILES['user_avatar_file']['tmp_name'] ) && $_FILES['user_avatar_file']['size'] > 0 ) {
			$avatarHash                          = $pParamHash;
			$avatarHash['user_id']               = $this->mUserId;
			$avatarHash['content_id']            = $this->mContentId;
			$avatarHash['upload']                = $_FILES['user_avatar_file'];
			$avatarHash['upload']['source_file'] = $_FILES['user_avatar_file']['tmp_name'];
			$this->storeAvatar( $avatarHash );
		}

		if( isset( $_FILES['user_logo_file'] ) && is_uploaded_file( $_FILES['user_logo_file']['tmp_name'] ) && $_FILES['user_logo_file']['size'] > 0 ) {
			$logoHash                          = $pParamHash;
			$logoHash['user_id']               = $this->mUserId;
			$logoHash['content_id']            = $this->mContentId;
			$logoHash['upload']                = $_FILES['user_logo_file'];
			$logoHash['upload']['source_file'] = $_FILES['user_logo_file']['tmp_name'];
			$this->storeLogo( $logoHash );
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
		if( $this->isValid() && count( $pStorageHash )) {
			// make a copy before the uploaded file disappears
			if( $pGenerateAvatar ) {
				$avatarHash = $pStorageHash;
				$avatarHash['upload']['tmp_name'] = $pStorageHash['upload']['tmp_name'].'.av';
				copy( $pStorageHash['upload']['tmp_name'], $pStorageHash['upload']['tmp_name'].'.av' );
			}

			if( $this->storeUserImage( $pStorageHash, 'portrait' ) && $pGenerateAvatar ) {
				$this->storeAvatar( $avatarHash );
				// nuke copy of image
				@unlink( $pStorageHash['upload']['tmp_name'].'.av' );
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
		return( $this->storeUserImage( $pStorageHash, 'avatar' ));
	}

	/**
	 * storeLogo
	 *
	 * @param array $pStorageHash
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function storeLogo( &$pStorageHash ) {
		return( $this->storeUserImage( $pStorageHash, 'logo' ));
	}

	/**
	 * storeUserImage
	 *
	 * @param array $pStorageHash
	 * @param string $pType
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function storeUserImage( &$pStorageHash, $pType = 'portrait' ) {
		if( $this->isValid() && count( $pStorageHash ) ) {
			// don't do the content thing
			$pStorageHash['skip_content_store'] = TRUE;

			// setup the hash for central storage functions
			$pStorageHash['no_perm_check'] = TRUE;
			$pStorageHash['_files_override'][$pType] = $pStorageHash['upload'];
			$pStorageHash['_files_override'][$pType]['max_width']     = constant( strtoupper( $pType )."_MAX_DIM" );
			$pStorageHash['_files_override'][$pType]['max_height']    = constant( strtoupper( $pType )."_MAX_DIM" );
			$pStorageHash['_files_override'][$pType]['attachment_id'] = !empty( $this->mInfo["{$pType}_attachment_id"] ) ? $this->mInfo["{$pType}_attachment_id"] : NULL;
			$pStorageHash['_files_override'][$pType]['user_id']       = $this->mUserId;
			if( LibertyMime::store( $pStorageHash )) {
				$file = $pStorageHash['upload_store']['files'][$pType];
				if( empty( $this->mInfo["{$pType}_attachment_id"] ) || $this->mInfo["{$pType}_attachment_id"] != $file['attachment_id'] ) {
					$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `{$pType}_attachment_id` = ? WHERE `user_id`=?";
					$result = $this->mDb->query( $query, array( $file['attachment_id'], $this->mUserId ) );
					$this->mInfo["{$pType}_attachment_id"] = $file['attachment_id'];
					$pStorageHash["{$pType}_file_name"] = $file['upload']['dest_branch'];
				}
			} else {
				$this->mErrors["{$pType}_file"] = 'File '.$pStorageHash['upload_store']['files'][$pType]['name'].' could not be stored.';
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
			$this->StartTrans();
			$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `".$pType."_attachment_id` = NULL WHERE `user_id`=?";
			$result = $this->mDb->query( $query, array( $this->mUserId ) );
			if( $this->expungeAttachment( $this->getField( $pType.'_attachment_id' ) ) ) {
				unset( $this->mInfo[$pType.'_file_name'] );
				unset( $this->mInfo[$pType.'_attachment_id'] );
				unset( $this->mInfo[$pType.'_url'] );
			}
			$this->CompleteTrans();
			return TRUE;
		}
	}

	/**
	 * purgePortrait
	 *
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function purgePortrait() {
		return $this->purgeImage( 'portrait' );
	}


	/**
	 * purgeAvatar
	 *
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function purgeAvatar() {
		return $this->purgeImage( 'avatar' );
	}


	/**
	 * purgeLogo
	 *
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function purgeLogo() {
		return $this->purgeImage( 'logo' );
	}
	// }}}

	// {{{ ==================== Watches ====================
	// TODO: clean up all watch functions. these are old and messy. - xing - Thursday Oct 16, 2008   11:07:55 CEST
	/**
	 * storeWatch
	 *
	 * @param array $pEvent
	 * @param array $pObject
	 * @param array $pType
	 * @param array $pTitle
	 * @param array $pUrl
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function storeWatch( $pEvent, $pObject, $pType, $pTitle, $pUrl ) {
		global $userlib;
		if( $this->isValid() ) {
			$hash = md5( uniqid( '.' ));
			$query = "DELETE FROM `".BIT_DB_PREFIX."users_watches` WHERE `user_id`=? AND `event`=? AND `object`=?";
			$this->mDb->query($query,array( $this->mUserId, $pEvent, $pObject ) );
			$query = "INSERT INTO `".BIT_DB_PREFIX."users_watches`(`user_id` ,`event` ,`object` , `email`, `hash`, `watch_type`, `title`, `url`) VALUES(?,?,?,?,?,?,?,?)";
			$this->mDb->query( $query, array( $this->mUserId, $pEvent, $pObject, $this->mInfo['email'], $hash, $pType, $pTitle, $pUrl ) );
			return TRUE;
		}
	}

	/**
	 * getWatches
	 *
	 * @param string $pEvent
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
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

	/**
	 * getEventWatches
	 *
	 * @param array $pEvent
	 * @param array $object
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function getEventWatches( $pEvent, $pObject ) {
		$ret = NULL;
		if( $this->isValid() ) {
			$query = "SELECT * FROM `".BIT_DB_PREFIX."users_watches` WHERE `user_id`=? AND `event`=? AND `object`=?";
			$result = $this->mDb->query($query,array( $this->mUserId, $pEvent, $pObject ) );
			if ( $result->numRows() ) {
				$ret = $result->fetchRow();
			}
		}
		return $ret;
	}

	/**
	 * get_event_watches
	 *
	 * @param array $pEvent
	 * @param array $pObject
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function get_event_watches( $pEvent, $pObject ) {
		$ret = array();

		$query = "select * from `".BIT_DB_PREFIX."users_watches` tw INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON ( tw.`user_id`=uu.`user_id` )  where `event`=? and `object`=?";
		$result = $this->mDb->query( $query,array( $pEvent,$pObject ));

		if( !$result->numRows() ) {
			return $ret;
		}

		while ($res = $result->fetchRow()) {
			$ret[] = $res;
		}

		return $ret;
	}

	/**
	 * remove_user_watch_by_hash
	 *
	 * @param array $pHash
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function remove_user_watch_by_hash( $pHash ) {
		$query = "DELETE FROM `".BIT_DB_PREFIX."users_watches` WHERE `hash`=?";
		$this->mDb->query( $query,array( $pHash ));
	}

	/**
	 * expungeWatch
	 *
	 * @param array $pEvent
	 * @param array $pObject
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function expungeWatch( $pEvent, $pObject ) {
		if( $this->isValid() ) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."users_watches` WHERE `user_id`=? AND `event`=? AND `object`=?";
			$this->mDb->query( $query, array( $this->mUserId, $pEvent, $pObject ));
		}
	}

	/**
	 * get_watches_events
	 *
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function get_watches_events() {
		$query = "select distinct `event` from `".BIT_DB_PREFIX."users_watches`";
		$result = $this->mDb->query($query,array());
		$ret = array();
		while ($res = $result->fetchRow()) {
			$ret[] = $res['event'];
		}
		return $ret;
	}
	// }}}

	/**
	 * getUserAttachments
	 *
	 * @param array $pListHash
	 * @access public
	 * @return list of attachments
	 */
	function getUserAttachments( &$pListHash ) {
		$pListHash['user_id'] = $this->mUserId;
		$mime = new LibertyMime();
		return $mime->getAttachmentList( $pListHash );
	}

	// {{{ ==================== Favorites ====================
	/**
	 * storeFavorite
	 *
	 * @param array $pContentId
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function storeFavorite( $pContentId ) {
		$ret = FALSE;
		if( $this->isValid() && $this->verifyId( $pContentId )) {
			if( !$this->hasFavorite( $pContentId ) ){
				$this->mDb->query( "INSERT INTO `".BIT_DB_PREFIX."users_favorites_map` ( `user_id`, `favorite_content_id` ) VALUES (?,?)", array( $this->mUserId, $pContentId ) );
			}
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

	function hasFavorite( $pContentId ) {
		$ret = FALSE;
		$rslt = $this->mDb->getOne( "SELECT `favorite_content_id` FROM `".BIT_DB_PREFIX."users_favorites_map` WHERE `user_id`=? AND `favorite_content_id`=?", array( $this->mUserId, $pContentId ) );
		if( !is_null( $rslt ) ){
			$ret = TRUE;
		}
		return $ret;
	}

	/**
	 * getFavorites
	 *
	 * @see LibertyContent::getContentList
	 * @return Array of content data
	 */
	function getFavorites(){
		$ret = NULL;
		if( $this->isRegistered() ){
			$listHash['user_favs'] = TRUE;
			$listHash['order_table'] = 'ufm.';
			$listHash['sort_mode'] = 'map_position_desc';
			$ret = $this->getContentList( $listHash );
		}
		return $ret;
	}
	// }}}

	/**
	 * getUserId
	 *
	 * @access public
	 * @return user id of currently loaded user
	 */
	function getUserId() {
		return( $this->isValid() ? $this->mUserId : ANONYMOUS_USER_ID );
	}

	/**
	 * getDisplayUrl
	 *
	 * @param array $pUserName
	 * @param array $pMixed
	 * @access public
	 * @return URL to users homepage
	 */
	public static function getDisplayUrlFromHash( &$pParamHash ) {
		if( function_exists( 'override_user_url' ) ) {
			$ret = override_user_url( $pParamHash );
		} else {
			global $gBitSystem;

			$rewrite_tag = $gBitSystem->isFeatureActive( 'pretty_urls_extended' ) ? 'view/':'';

			if ($gBitSystem->isFeatureActive( 'pretty_urls' )
			|| $gBitSystem->isFeatureActive( 'pretty_urls_extended' ) ) {
				$ret =  USERS_PKG_URL . $rewrite_tag;
				$ret .= urlencode( $pParamHash['login'] );
			} else {
				$ret =  USERS_PKG_URL . 'index.php?home=';
				$ret .= urlencode( $pParamHash['login'] );
			}
		}
		return $ret;
	}

	/**
	 * getDisplayLink
	 *
	 * @param array $pUserName
	 * @param array $pDisplayHash
	 * @access public
	 * @return get a link to the the users homepage
	 */
	public function getDisplayLink( $pLinkText=NULL, $pMixed=NULL, $pAnchor=NULL ) {
		return BitUser::getDisplayNameFromHash( TRUE, $pMixed );
	}

	/**
	 * getTitle
	 *
	 * @param array $pHash
	 * @access public
	 * @return get the users display name
	 */
	public static function getTitleFromHash( &$pHash, $pDefault=TRUE ) {
		return BitUser::getDisplayNameFromHash( FALSE, $pHash );
	}

	/**
	 * Get user information for a particular user
	 *
	 * @param pUseLink return the information in the form of a url that links to the users information page
	 * @param pHash todo - need explanation on how to use this...
	 * @return display name or link to user information page
	 **/
	public static function getDisplayNameFromHash( $pUseLink=FALSE, $pHash ) {
		global $gBitSystem, $gBitUser;
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

			if( empty( $pHash['users_information'] ) && !empty( $pHash['login'] ) ) {
				$pHash['users_information'] = $gBitSystem->mDb->getOne( "SELECT pref_value FROM liberty_content_prefs lcp INNER JOIN users_users uu ON (lcp.content_id=uu.content_id) WHERE uu.login=? AND pref_name='users_information'", array( $pHash['login'] ), 1, NULL, 86400 );
			}

			if( $pUseLink && $gBitUser->hasPermission( 'p_users_view_user_homepage' ) && (empty( $pHash['users_information'] ) || $pHash['users_information'] == 'public') ) {
				$ret = '<a class="username" title="'.( !empty( $pHash['link_title'] ) ? $pHash['link_title'] : tra( 'Profile for' ).' '.htmlspecialchars( $displayName ))
					.'" href="'.BitUser::getDisplayUrlFromHash( $pHash ).'">'
					. htmlspecialchars( isset( $pHash['link_label'] ) ? $pHash['link_label'] : $displayName )
					.'</a>';
			} else {
				$ret = htmlspecialchars( $displayName );
			}
		} else {
			$ret = tra( "Anonymous" );
		}

		return $ret;
	}

	/**
	 * Get user information for a particular user
	 *
	 * @param pUseLink return the information in the form of a url that links to the users information page
	 * @param pHash todo - need explanation on how to use this...
	 * @return display name or link to user information page
	 **/
	function getDisplayName( $pUseLink=FALSE, $pHash=NULL ) {
		$ret = NULL;
		if( empty( $pHash ) && !empty( $this ) && !empty( $this->mInfo )) {
			$pHash = &$this->mInfo;
		}
		return static::getDisplayNameFromHash( $pUseLink, $pHash );
	}

	/**
	 * getRenderFile Returns include file that will
	 *
	 * @access public
	 * @return the fully specified path to file to be included
	 */
	function getRenderFile() {
		return USERS_PKG_PATH."display_bituser_inc.php";
	}

	/**
	 * getSelectionList get a list of users that can be used in dropdown lists in forms to choose from
	 *
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getSelectionList() {
		$query = "
			SELECT uu.`user_id`, uu.`login`, uu.`real_name`
			FROM `".BIT_DB_PREFIX."users_users` uu
			ORDER BY uu.`login`";
		$result = $this->mDb->query( $query );
		$ret = array();
		while( $res = $result->fetchRow()) {
			$ret[$res['user_id']] = $res['login'].(( !empty( $res['real_name'] ) && $res['real_name'] != $res['login'] ) ? ' - '.$res['real_name'] : '' );
		}

		return $ret;
	}

	/**
	 * getList get a list of users
	 *
	 * @param array $pParamHash
	 * @access public
	 * @return array of users
	 */
	function getList( &$pParamHash ) {
		global $gBitSystem, $gBitUser;
		if( empty( $pParamHash['sort_mode'] )) {
			$pParamHash['sort_mode'] = 'registration_date_desc';
		}

		LibertyContent::prepGetList( $pParamHash );

		$selectSql = $joinSql = $whereSql = '';
		$bindVars = array();
		array_push( $bindVars, 'bituser' );
		$this->getServicesSql( 'content_list_sql_function', $selectSql, $joinSql, $whereSql, $bindVars, NULL, $pParamHash );

		// limit search to users with a specific language
		if( !empty( $pParamHash['lang_code'] ) ) {
			$joinSql .= " INNER JOIN `".BIT_DB_PREFIX."liberty_content_prefs` lcp ON ( lcp.`content_id`=uu.`content_id` AND lcp.`pref_name`='bitlanguage' )";
			$whereSql .= " AND lcp.`pref_value`=? ";
			$bindVars[] = $pParamHash['lang_code'];
		}

		if( !$gBitUser->hasPermission( 'p_users_admin' ) ) {
			$joinSql .= " LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_prefs` lcp2 ON ( lcp2.`content_id`=uu.`content_id` AND lcp2.`pref_name`='users_information' )";
			$whereSql .= " AND (lcp2.`pref_value` IS NULL OR lcp2.`pref_value`='public')";
		}

		// limit search to users with a specific IP
		if( !empty( $pParamHash['ip'] ) ) {
			$joinSql .= " LEFT OUTER JOIN `".BIT_DB_PREFIX."users_cnxn` uc ON ( uu.`user_id`=uc.`user_id`) ";
			$ips = explode( ',', $pParamHash['ip'] );
			$whereSql .= ' AND ( ';
			do {
				$ip = array_pop( $ips );
				if( strpos( $ip, '%' ) ) {
					$whereSql .= " uc.`ip` LIKE ? ";
				} else {
					$whereSql .= " uc.`ip`=? ";
				}
				$bindVars[] = $ip;
				if( !empty( $ips ) ) {
					$whereSql .= ' OR ';
				}
			} while( $ips );
			$whereSql .= ' ) ';
		}

		// limit to registrations over a time period like 'YYYY-MM-DD' or 'Y \Week W' or anything convertible by SQLDate
		if( !empty( $pParamHash['period'] ) ) {
			$sqlPeriod = $this->mDb->SQLDate( $this->mDb->getPeriodFormat( $pParamHash['period'] ), $this->mDb->SQLIntToTimestamp( 'registration_date' ));
			$whereSql .= ' AND '.$sqlPeriod.'=?';
			$bindVars[] = $pParamHash['timeframe'];
		}

		// lets search for a user
		if ( $pParamHash['find'] ) {
			$whereSql .= " AND ( UPPER( uu.`login` ) LIKE ? OR UPPER( uu.`real_name` ) LIKE ? OR UPPER( uu.`email` ) LIKE ? ) ";
			$bindVars[] = '%'.strtoupper( $pParamHash['find'] ).'%';
			$bindVars[] = '%'.strtoupper( $pParamHash['find'] ).'%';
			$bindVars[] = '%'.strtoupper( $pParamHash['find'] ).'%';
		}

		if( $gBitSystem->isPackageActive( 'stats' ) ) {
			$joinSql .= " LEFT OUTER JOIN `".BIT_DB_PREFIX."stats_referer_users_map` srum ON (srum.`user_id`=uu.`user_id`)
						  LEFT OUTER JOIN `".BIT_DB_PREFIX."stats_referer_urls` sru ON (srum.`referer_url_id`=sru.`referer_url_id`)";
			$selectSql .= ", sru.`referer_url`";
			if( !empty( $pParamHash['referer'] ) ) {
				if( $pParamHash['referer'] == 'none' ) {
					$whereSql .= " AND `referer_url` IS NULL";
				} else {
					$whereSql .= " AND `referer_url` LIKE ?";
					$bindVars[] = '%'.strtolower( $pParamHash['find'] ).'%';
				}
			}
		}

		// Return an array of users indicating name, email, last changed pages, versions, last_login
		$query = "
			SELECT uu.*, lc.`content_status_id`, lf_ava.`file_name` AS `avatar_file_name`, lf_ava.`mime_type` AS `avatar_mime_type`, la_ava.`attachment_id` AS `avatar_attachment_id` $selectSql
			FROM `".BIT_DB_PREFIX."users_users` uu
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (uu.`content_id`=lc.`content_id`)
				$joinSql
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_content_hits` lch ON ( lc.`content_id` = lch.`content_id` )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_attachments` la_ava ON ( uu.`avatar_attachment_id`=la_ava.`attachment_id` )
				LEFT OUTER JOIN `".BIT_DB_PREFIX."liberty_files` lf_ava ON ( lf_ava.`file_id`=la_ava.`foreign_id` )
			WHERE lc.`content_type_guid` = ? $whereSql ORDER BY ".$this->mDb->convertSortmode( $pParamHash['sort_mode'] );
		$result = $this->mDb->query( $query, $bindVars, $pParamHash['max_records'], $pParamHash['offset'] );

		$ret = array();
		while( $res = $result->fetchRow() ) {
			// Used for pulling out dead/empty/spam accounts
			if( isset( $pParamHash['max_content_count'] ) && is_numeric( $pParamHash['max_content_count'] ) ) {
				$contentCount = $this->mDb->getOne( "SELECT COUNT(*) FROM  `".BIT_DB_PREFIX."liberty_content` lc INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON ( lc.`user_id`=uu.`user_id` ) WHERE uu.`user_id`=? AND `content_type_guid` != 'bituser'", array( $res['user_id'] ) );
				if( $contentCount >  $pParamHash['max_content_count'] ) {
					continue;
				}
			}

			// Used for pulling out non-idle accounts or pigs
			if( isset( $pParamHash['min_content_count'] ) && is_numeric( $pParamHash['min_content_count'] ) ) {
				$contentCount = $this->mDb->getOne( "SELECT COUNT(*) FROM  `".BIT_DB_PREFIX."liberty_content` lc INNER JOIN `".BIT_DB_PREFIX."users_users` uu ON ( lc.`user_id`=uu.`user_id` ) WHERE uu.`user_id`=? AND `content_type_guid` != 'bituser'", array( $res['user_id'] ) );
				if( $contentCount <  $pParamHash['min_content_count'] ) {
					continue;
				}
			}

			if( !empty( $res['referer_url'] )) {
				if ( $gBitSystem->isPackageActive('stats') ) {
					$res['short_referer_url'] = stats_referer_display_short($res['referer_url']);
				}
			}
			if( !empty( $res['avatar_file_name'] )) {
				$res['avatar_url'] = $this->getSourceUrl( array( 'attachment_id'=>$res['avatar_attachment_id'], 'mime_type'=>$res['avatar_mime_type'], 'file_name'=>$res['avatar_file_name'] ) );
				$res['thumbnail_url'] = liberty_fetch_thumbnail_url( array(
					'source_file' => $this->getSourceFile( array( 'sub_dir'=>$res['avatar_attachment_id'], 'user_id' => $res['user_id'], 'file_name'=>$res['avatar_file_name'], 'mime_type'=>$res['avatar_mime_type'], 'package'=>liberty_mime_get_storage_sub_dir_name( array( 'mime_type'=>$res['avatar_mime_type'], 'name'=>$res['avatar_file_name'] ) ) ) ),
					'file_name' => $res['avatar_url'],
					// TODO: Make this a preference
					'size'         => 'avatar'
				));
			}
			$res["groups"] = $this->getGroups( $res['user_id'] );
			$ret[$res['user_id']] = $res;
		}
		$retval = array();

		$query = "
			SELECT COUNT(*) FROM `".BIT_DB_PREFIX."users_users` uu
				INNER JOIN `".BIT_DB_PREFIX."liberty_content` lc ON (uu.`content_id`=lc.`content_id`) $joinSql
			WHERE lc.`content_type_guid` = ? $whereSql";
		$pParamHash["cant"] = $this->mDb->getOne( $query, $bindVars );

		LibertyContent::postGetList( $pParamHash );

		return $ret;
	}

	/**
	 * getGroups
	 *
	 * @param array $pUserId
	 * @param array $pForceRefresh
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getGroups( $pUserId=NULL, $pForceRefresh = FALSE ) {
		$pUserId = !empty( $pUserId ) ? $pUserId : $this->mUserId;
		if( !isset( $this->cUserGroups[$pUserId] ) || $pForceRefresh ) {
			$query = "
				SELECT ug.`group_id`, ug.`group_name`, ug.`user_id` as group_owner_user_id
				FROM `".BIT_DB_PREFIX."users_groups_map` ugm INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON (ug.`group_id`=ugm.`group_id`)
				WHERE ugm.`user_id`=? OR ugm.`group_id`=".ANONYMOUS_GROUP_ID;
			$ret = $this->mDb->getAssoc( $query, array(( int )$pUserId ));
			if( $ret ) {
				foreach( array_keys( $ret ) as $groupId ) {
					$res = array();
					foreach( $res as $key=>$val) {
						$ret[$key] = array( 'group_name' => $val );
					}
				}
			}
			// cache it
			$this->cUserGroups[$pUserId] = $ret;
			return $ret;
		} else {
			return $this->cUserGroups[$pUserId];
		}
	}

	/**
	 * isValid
	 *
	 * @access public
	 * @return TRUE if user is loaded
	 */
	function isValid() {
		return ( $this->verifyId( $this->mUserId ) );
	}

	/**
	 * isAdmin "PURE VIRTUAL BASE FUNCTION";
	 *
	 * @access public
	 * @return FALSE
	 */
	function isAdmin() {
		return FALSE;
	}

	/**
	 * isRegistered
	 *
	 * @access public
	 * @return TRUE if user is registered, FALSE otherwise
	 */
	function isRegistered() {
		return ( $this->mUserId > ANONYMOUS_USER_ID );
	}

	/**
	 * verifyRegistered
	 *
	 * @access public
	 * @return TRUE if user is registered, otherwise a login dialog is displayed
	 */
	function verifyRegistered( $pMsg = "" ) {
		global $gBitSystem;
		if( !$this->isRegistered() ) {
			$gBitSystem->fatalPermission( "", $pMsg );
		}
		return TRUE;
	}

	/**
	 * userExists
	 *
	 * @param array $pUserMixed
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function userExists( $pUserMixed ) {
		$ret = FALSE;
		if ( is_array( $pUserMixed ) ) {
			if( $cur = current( $pUserMixed ) ) {
				if( is_numeric( $cur ) ) {
					$conditionSql = " `".key( $pUserMixed )."` ";
				} else {
					$conditionSql = " UPPER(`".key( $pUserMixed )."`)";
				}
				$query = "SELECT `user_id` FROM `".BIT_DB_PREFIX."users_users` WHERE $conditionSql = ?";
				$ret = $this->mDb->getOne( $query, array( strtoupper( $cur ) ) );
			}
		}
		return $ret;
	}

	/**
	 * Create an export hash from the data
	 *
	 * @access public
	 * @return export data
	 */
	function exportHash() {
		global $gBitSystem;
		$ret = array();
		if( $this->isValid() ) {
			$ret = array(
				'user_id' 		=> $this->mUserId,
				'content_id' 	=> $this->mContentId,
				'real_name' 	=> $this->getField( 'real_name' ),
				'email'	 		=> $this->getField( 'email' ),
				'uri'        	=> $this->getDisplayUri(),
				'registration_date' 	=> date( DateTime::W3C, $this->getField('registration_date') ),
				'last_login' => date( DateTime::W3C, $this->getField('last_login') ),
			);
			$ret['content_count'] = get_user_content_count( $this->mUserId );
			if( $gBitSystem->isPackageActive( 'stats' ) ) {
				$ret['referer'] = $this->mDb->getOne( "SELECT sru.`referer_url` FROM `".BIT_DB_PREFIX."stats_referer_urls` sru INNER JOIN `".BIT_DB_PREFIX."stats_referer_users_map` srum ON (srum.`referer_url_id`=sru.`referer_url_id`) WHERE `user_id`=?", $this->mUserId );
			}
			$ret['ips'] = implode( ',', $this->mDb->getCol( "SELECT DISTINCT(`ip`) FROM `".BIT_DB_PREFIX."users_cnxn` uc WHERE `user_id`=?", $this->mUserId ) );
		}
		return $ret;
	}

	/**
	 * userCollection
	 *
	 * @param array $pInput
	 * @param array $pReturn
	 * @access public
	 * @return $pReturn
	 */
	public static function userCollection( $pInput, &$pReturn ) {
		global $gQueryUserId;

		if( empty( $pReturn['user_id'] )) {
			if( !empty( $gQueryUserId )) {
				$pReturn['user_id'] = $gQueryUserId;
			} elseif( isset( $pInput['user_id'] ) ) {
				$pReturn['user_id'] = $pInput['user_id'];
			}
		}
		if( @BitBase::verifyId( $pInput['group_id'] ) ) {
			$pReturn['group_id'] = $pInput['group_id'];
		}
		return;
	}

	public static function getUserObject( $pUserId ) {
		global $gBitSystem;
		$userClass = $gBitSystem->getConfig( 'user_class', 'BitPermUser' );
		if( $ret = new $userClass( $pUserId ) ) {
			$ret->load();
		}
		return $ret;
	}
}

function get_user_content_count( $pUserId ) {
	global $gBitDb;
	if( BitBase::verifyId( $pUserId ) ) {
		return $gBitDb->getOne( "SELECT COUNT(`content_id`) FROM `".BIT_DB_PREFIX."liberty_content` lc WHERE lc.`content_type_guid`!='bituser' AND lc.`user_id`=?", array( $pUserId ) );
	}
}


// {{{ ==================== Services ====================
function users_favs_content_list_sql( &$pObject, $pParamHash=NULL ){
    $ret = array();
	if( !empty( $pParamHash['user_favs'] ) ){
		// $ret['select_sql'] = "";
		$ret['join_sql'] = " INNER JOIN `".BIT_DB_PREFIX."users_favorites_map` ufm ON ( ufm.`favorite_content_id`=lc.`content_id` )";
		$ret['where_sql'] = " AND ufm.`user_id` = ?";
		$ret['bind_vars'][] = $pObject->mUserId;
	}
	return $ret;
}

function users_collection_sql( &$pObject, $pParamHash=NULL ){
    $ret = array();
	if( !empty( $pParamHash['group_id'] ) and BitBase::verifyId( $pParamHash['group_id'] ) ){
		// $ret['select_sql'] = "";
		$ret['join_sql'] = " INNER JOIN `".BIT_DB_PREFIX."users_groups_map` ugm ON (ugm.`user_id`=uu.`user_id`)";
		$ret['where_sql'] = ' AND ugm.`group_id` = ? ';
		$ret['bind_vars'][] = $pParamHash['group_id'];
	}
	return $ret;
}
// }}}

/* vim: :set fdm=marker : */
