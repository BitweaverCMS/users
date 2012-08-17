<?php
/**
 * $Header$
 *
 * Lib for user administration, roles and permissions
 * This lib uses pear so the constructor requieres

 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See below for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
 *
 * $Id$
 * @package users
 */

/**
 * required setup
 */
require_once( USERS_PKG_PATH.'/RoleUser.php' );

/**
 * Class that holds all information for a given user
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision$
 * @package  users
 * @subpackage  RolePermUser
 */
class RolePermUser extends BitUser {
	// change this to an email address to receive debug emails from the LDAP code
	// does this work? - xing - Saturday Oct 18, 2008   09:47:20 CEST
	var $debug = FALSE;

	// we use these to cache data
	var $cUserRoles = array();
	var $cRolePerms = array( array() );

	/**
	 * RolePermUser Initialise class
	 *
	 * @param numeric $pUserId User ID of the user we wish to load
	 * @param numeric $pContentId Content ID of the user we wish to load
	 * @access public
	 * @return void
	 */
	function RolePermUser( $pUserId=NULL, $pContentId=NULL ) {
		BitUser::BitUser( $pUserId, $pContentId );

		// Permission setup
		$this->mAdminContentPerm = 'p_users_admin';
	}

	/**
	 * assumeUser Assume the identity of anothre user - Only admins may do this
	 *
	 * @param numeric $pUserId User ID of the user you want to hijack
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function assumeUser( $pUserId ) {
		global $gBitUser;
		$ret = FALSE;
		// make double sure the current logged in user has permission, check for p_users_admin, not admin, as that is all you need for assuming another user.
		// this enables creating of a non technical site adminstrators role, eg customer support representatives.
		if( $gBitUser->hasPermission( 'p_users_admin' ) ) {
			$assumeUser = new RolePermUser( $pUserId );
			$assumeUser->loadPermissions();
			if( $assumeUser->isAdmin() ) {
				$this->mErrors['assume_user'] = tra( "User administrators cannot be assumed." );
			} else {
				$this->mDb->query( "UPDATE `".BIT_DB_PREFIX."users_cnxn` SET `user_id`=?, `assume_user_id`=? WHERE `cookie`=?", array( $pUserId, $gBitUser->mUserId, $_COOKIE[$this->getSiteCookieName()] ) );
				$ret = TRUE;
			}
		}

		return $ret;
	}

	/**
	 * load
	 *
	 * @param boolean $pFull Load all permissions
	 * @param string $pUserName User login name
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function load( $pFull=FALSE, $pUserName=NULL ) {
		if( BitUser::load( $pFull, $pUserName ) ) {
			if( $pFull ) {
				unset( $this->mPerms );
				$this->loadRoles();
				$this->loadPermissions();
			}
		}
		return( $this->mUserId != NULL );
	}

	/**
	 * sanitizeUserInfo Used to remove sensitive information from $this->mInfo when it is unneccessary (i.e. $gQueryUser)
	 *
	 * @access public
	 * @return void
	 */
	function sanitizeUserInfo() {
		if( !empty( $this->mInfo )) {
			$unsanitary = array( 'provpass', 'hash', 'challenge', 'user_password' );
			foreach( array_keys( $this->mInfo ) as $key ) {
				if( in_array( $key, $unsanitary )) {
					unset( $this->mInfo[$key] );
				}
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
		global $gBitSystem;
		// keep track of newUser before calling base class
		$newUser = !$this->isRegistered();
		$this->mDb->StartTrans();
		if( BitUser::store( $pParamHash ) && $newUser ) {
			$defaultRoles = $this->getDefaultRole();
			$this->addUserToRole( $this->mUserId, $defaultRoles );
			if( $gBitSystem->isFeatureActive( 'users_eponymous_roles' ) ) {
				// Create a role just for this user, for permissions assignment.
				$roleParams = array(
					'user_id' => $this->mUserId,
					'name'    => $pParamHash['user_store']['login'],
					'desc'    => "Personal role for ".( !empty( $pParamHash['user_store']['real_name'] ) ? $pParamHash['user_store']['real_name'] : $pParamHash['user_store']['login'] )
				);
				if( $this->storeRole( $roleParams ) ) {
					$this->addUserToRole( $this->mUserId, $roleParams['role_id'] );
				}
			}
			$this->load( TRUE );

			// store any uploaded images, this can stuff mErrors, so we want to do this as the very last thing.
			$pParamHash['upload']['thumbnail'] = FALSE;   // i don't think this does anything - perhaps replace it by setting thumbnail_sizes
			$this->storeImages( $pParamHash );
		}
		$this->mDb->CompleteTrans();
		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * roleExists work out if a given role exists
	 *
	 * @param string $pRoleName
	 * @param numeric $pUserId
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function roleExists( $pRoleName, $pUserId = ROOT_USER_ID ) {
		static $sRoles = array();
		if( !isset( $sRoles[$pUserId][$pRoleName] ) ) {
			$bindVars = array( $pRoleName );
			$whereSql = '';
			if( $pUserId != '*' ) {
				$whereSql = 'AND `user_id`=?';
				$bindVars[] = $pUserId;
			}
			$query = "
				SELECT ur.`role_name`, ur.`role_id`,  ur.`user_id`
				FROM `".BIT_DB_PREFIX."users_roles` ur
				WHERE `role_name`=? $whereSql";
			if( $result = $this->mDb->getAssoc( $query, $bindVars ) ) {
				if( empty( $sRoles[$pUserId] ) ) {
					$sRoles[$pUserId] = array();
				}
				$sRoles[$pUserId][$pRoleName] = $result[$pRoleName];
			} else {
				$sRoles[$pUserId][$pRoleName]['role_id'] = NULL;
			}
		}
		return( $sRoles[$pUserId][$pRoleName]['role_id'] );
	}

	/**
	 * removes user and associated private data
	 *
	 * @access public
	 * @return always FALSE???
	 * TODO: fix return
	 */
	function expunge( $pExpungeContent=NULL) {
		global $gBitSystem, $gBitUser;
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			if( $this->mUserId == $gBitUser->mUserId ) {
				$this->mDb->RollbackTrans();
				$gBitSystem->fatalError( tra( 'You cannot delete yourself' ) );
			} elseif( $this->mUserId != ANONYMOUS_USER_ID ) {
				$userTables = array(
					'users_roles_map',
				);

				foreach( $userTables as $table ) {
					$query = "DELETE FROM `".BIT_DB_PREFIX.$table."` WHERE `user_id` = ?";
					$result = $this->mDb->query( $query, array( $this->mUserId ) );
				}

				if( parent::expunge( $pExpungeContent ) ) {
					$this->mDb->CompleteTrans();
					return TRUE;
				} else {
					$this->mDb->RollbackTrans();
				}
			} else {
				$this->mDb->RollbackTrans();
				$gBitSystem->fatalError( tra( 'The anonymous user cannot be deleted' ) );
			}
		}
		return FALSE;
	}



	// =-=-=-=-=-=-=-=-=-=-=-= Role Functions =-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	/**
	 * loadRoles load roles into $this->mRoles
	 *
	 * @param boolean $pForceRefresh
	 * @access public
	 * @return void
	 */
	function loadRoles( $pForceRefresh = FALSE ) {
		if( $this->isValid() ) {
			$this->mRoles = $this->getRoles( NULL, $pForceRefresh );
		}
	}

	/**
	 * isInRole work out if a given user is assigned to a role
	 *
	 * @param mixed $pRoleMixed Role ID or Role Name (deprecated)
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function isInRole( $pRoleMixed ) {
		$ret = FALSE;
		if( $this->isAdmin() ) {
			$ret = TRUE;
		} if( $this->isValid() ) {
			if( empty( $this->mRoles ) ) {
				$this->loadRoles();
			}
			if( preg_match( '/A-Za-z/', $pRoleMixed ) ) {
				// Old style role name passed in
				deprecated( "Please use the Role ID instead of the Role name." );
				$ret = in_array( $pRoleMixed, $this->mRoles );
			} else {
				$ret = isset( $this->mRoles[$pRoleMixed] );
			}
		}
		return $ret;
	}

	/**
	 * getAllRoless Get a list of all Roles
	 *
	 * @param array $pListHash List Hash
	 * @access public
	 * @return array of roles
	 */
	function getAllRoles( &$pListHash ) {
		if( empty(  $pListHash['sort_mode'] ) || $pListHash['sort_mode'] == 'name_asc' ) {
			$pListHash['sort_mode'] = 'role_name_asc';
		}
		$this->prepGetList( $pListHash );
		$sortMode = $this->mDb->convertSortmode( $pListHash['sort_mode'] );
		if( !empty( $pListHash['find_roles'] ) ) {
			$mid = " AND UPPER(`role_name`) like ?";
			$bindvars[] = "%".strtoupper( $pListHash['find_roles'] )."%";
		} elseif( !empty( $pListHash['find'] ) ) {
			$mid = " AND  UPPER(`role_name`) like ?";
			$bindvars[] = "%".strtoupper( $pListHash['find'] )."%";
		} else {
			$mid = '';
			$bindvars = array();
		}

		if( !empty( $pListHash['hide_root_roles'] )) {
			$mid .= ' AND `user_id` <> '.ROOT_USER_ID;
		} elseif( !empty( $pListHash['only_root_roles'] )) {
			$mid .= ' AND `user_id` = '.ROOT_USER_ID;
		}

		if( !empty( $pListHash['user_id'] ) ){
			$mid .= ' AND `user_id` = ? ';
			$bindvars[] = $pListHash['user_id'];
		}
		if( !empty( $pListHash['is_public'] ) ) {
			$mid .= ' AND `is_public` = ?';
			$bindvars[] = $pListHash['is_public'];
		}
		if( !empty( $pListHash['visible'] ) && !$this->isAdmin() ){
			global $gBitUser;
			$mid .= ' AND `user_id` = ? OR `is_public` = ? ';
			$bindvars[] = $gBitUser->mUserId;
			$bindvars[] = 'y';

		}

		$mid =  preg_replace('/^ AND */',' WHERE ', $mid);

		$query = "
			SELECT `user_id`, `role_id`, `role_name` , `role_desc`, `role_home`, `is_default`, `is_public`
			FROM `".BIT_DB_PREFIX."users_roles` $mid
			ORDER BY $sortMode";
		$ret = array();
		if( $rs = $this->mDb->query( $query, $bindvars ) ) {
			while( $row = $rs->fetchRow() ) {
				$roleId = $row['role_id'];
				$ret[$roleId] = $row;
				$ret[$roleId]['perms'] = $this->getRolePermissions( array( 'role_id' => $roleId ));
			}
		}

		$pListHash['cant'] = $this->mDb->getOne( "SELECT COUNT(*) FROM `".BIT_DB_PREFIX."users_roles` $mid", $bindvars );

		return $ret;
	}

	/**
	 * getAllUserRoles
	 *
	 * @param numeric $pUserId
	 * @access public
	 * @return array of roles a user belongs to
	 */
	function getAllUserRoles( $pUserId = NULL ) {
		if( empty( $pUserId ) ) {
			$pUserId = $this->mUserId;
		}

		$sql = "
			SELECT ur.`role_id` AS `hash_key`, ur.* FROM `".BIT_DB_PREFIX."users_roles` ur
			WHERE `user_id`=?
			ORDER BY ur.`role_name` ASC";
		return $this->mDb->getAssoc( $sql, array( $pUserId ));
	}

	/**
	 * expungeRole remove a role
	 *
	 * @param numeric $pRoleId
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function expungeRole( $pRoleId ) {
		// we cannot remove the anonymous role
		if( $pRoleId != ANONYMOUS_TEAM_ID ) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."users_roles_map` WHERE `role_id` = ?";
			$result = $this->mDb->query( $query, array( $pRoleId ));
			$query = "DELETE FROM `".BIT_DB_PREFIX."users_role_permissions` WHERE `role_id` = ?";
			$result = $this->mDb->query( $query, array( $pRoleId ));
			$query = "DELETE FROM `".BIT_DB_PREFIX."users_roles` WHERE `role_id` = ?";
			$result = $this->mDb->query( $query, array( $pRoleId ));
			return TRUE;
		}
	}

	/**
	 * getDefaultRole get the default role of a given user
	 *
	 * @param array $pRoleId pass in a Role ID to make conditional function
	 * @access public
	 * @return Default Role ID if one is set
	 */
	function getDefaultRole( $pRoleId = NULL ) {
		$bindvars = NULL;
		$whereSql = '';
		if( @BitBase::verifyId( $pRoleId )) {
			$whereSql = "AND `role_id`=? ";
			$bindvars = array( $pRoleId );
		}
		return( $this->mDb->getAssoc( "SELECT `role_id`, `role_name` FROM `".BIT_DB_PREFIX."users_roles` WHERE `is_default` = 'y' $whereSql ", $bindvars ) );
	}

	/**
	 * getRoleUsers Get a list of users who share a given role id
	 *
	 * @param array $pRoleId
	 * @access public
	 * @return list of users who are in the role id
	 */
	function getRoleUsers( $pRoleId ) {
		$ret = array();
		if( @BitBase::verifyId( $pRoleId )) {
			$query = "
				SELECT uu.`user_id` AS hash_key, uu.`login`, uu.`real_name`, uu.`user_id`
				FROM `".BIT_DB_PREFIX."users_users` uu
				INNER JOIN `".BIT_DB_PREFIX."users_roles_map` ur ON (uu.`user_id`=ur.`user_id`)
				WHERE `role_id`=?";
			$ret = $this->mDb->getAssoc( $query, array( $pRoleId ));
		}
		return $ret;
	}

	/**
	 * getHomeRole get the URL where a user of that role should be sent
	 *
	 * @param array $pRoleId
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getHomeRole( $pRoleId ) {
		$ret = FALSE;
		if( @BitBase::verifyId( $pRoleId )) {
			$query = "SELECT `role_home` FROM `".BIT_DB_PREFIX."users_roles` WHERE `role_id`=?";
			$ret = $this->mDb->getOne( $query,array( $pRoleId ) );
		}
		return $ret;
	}

	/**
	 * storeUserDefaultRole
	 *
	 * @param array $pUserId
	 * @param array $pRoleId
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function storeUserDefaultRole( $pUserId, $pRoleId ) {
		if( @BitBase::verifyId( $pUserId ) && @BitBase::verifyId( $pRoleId )) {
			$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `default_role_id` = ? WHERE `user_id` = ?";
			return $this->mDb->query( $query, array( $pRoleId, $pUserId ));
		}
	}

	/**
	 * batchAssignUsersToRole assign all users to a given role
	 *
	 * @param array $pRoleId
	 * @access public
	 * @return void
	 */
	function batchAssignUsersToRole( $pRoleId ) {
		if( @BitBase::verifyId( $pRoleId )) {
			$users = $this->getRoleUsers( $pRoleId );
			$result = $this->mDb->getCol( "SELECT uu.`user_id` FROM `".BIT_DB_PREFIX."users_users` uu" );
			foreach( $result as $userId ) {
				if( empty( $users[$userId] ) && $userId != ANONYMOUS_USER_ID ) {
					$this->addUserToRole( $userId, $pRoleId );
				}
			}
		}
	}

	/**
	 * batchSetUserDefaultRole
	 *
	 * @param array $pRoleId
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function batchSetUserDefaultRole( $pRoleId ) {
		if( @BitBase::verifyId( $pRoleId )) {
			$users = $this->getRoleUsers($pRoleId);
			foreach( array_keys( $users ) as $userId ) {
				$this->storeUserDefaultRole( $userId, $pRoleId );
			}
		}
	}

	/**
	 * getRoleInfo
	 *
	 * @param array $pRoleId
	 * @access public
	 * @return role information
	 */
	function getRoleInfo( $pRoleId ) {
		if( @BitBase::verifyId( $pRoleId )) {
			$sql = "SELECT * FROM `".BIT_DB_PREFIX."users_roles` WHERE `role_id` = ?";
			$ret = $this->mDb->getRow( $sql, array( $pRoleId ));

			$listHash = array(
				'role_id' => $pRoleId,
				'sort_mode' => 'up.perm_name_asc',
			);
			$ret["perms"] = $this->getRolePermissions( $listHash );

			$sql = "SELECT COUNT(*) FROM `".BIT_DB_PREFIX."users_roles_map` WHERE `role_id` = ?";
			$ret['num_members'] = $this->mDb->getOne( $sql, array( $pRoleId ));

			return $ret;
		}
	}

	/**
	 * addUserToRole Adds user pUserId to role(s) pRoleMixed.
	 *
	 * @param numeric $pUserId User ID
	 * @param mixed $pRoleMixed A single role ID or an array of role IDs
	 * @access public
	 * @return Either an ADO RecordSet (success) or FALSE (failure).
	 */
	function addUserToRole( $pUserId, $pRoleMixed ) {
		$result = FALSE;
		if( @BitBase::verifyId( $pUserId ) && !empty( $pRoleMixed )) {
			$result = TRUE;
			$addRoles = array();
			if( is_array( $pRoleMixed ) ) {
				$addRoles = array_keys( $pRoleMixed );
			} elseif( @BitBase::verifyId($pRoleMixed) ) {
				$addRoles = array( $pRoleMixed );
			}
			$currentUserRoles = $this->getRoles( $pUserId );
			foreach( $addRoles AS $roleId ) {
				$isInRole = FALSE;
				foreach( $currentUserRoles as $curRoleId => $curRoleInfo ) {
					if( $curRoleId == $roleId ) {
						$isInRole = TRUE;
					}
				}
				if( !$isInRole ) {
					$query = "INSERT INTO `".BIT_DB_PREFIX."users_roles_map` (`user_id`,`role_id`) VALUES(?,?)";
					$result = $this->mDb->query( $query, array( $pUserId, $roleId ));
				}
			}
		}
		return $result;
	}

	/**
	 * removeUserFromRole
	 *
	 * @param array $pUserId
	 * @param array $pRoleId
	 * @access public
	 * @return void
	 */
	function removeUserFromRole( $pUserId, $pRoleId ) {
		if( @BitBase::verifyId( $pUserId ) && @BitBase::verifyId( $pRoleId )) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."users_roles_map` WHERE `user_id` = ? AND `role_id` = ?";
			$result = $this->mDb->query( $query, array( $pUserId, $pRoleId ));
			$default = $this->getDefaultRole();
			if( $pRoleId == key( $default )) {
				$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `default_role_id` = NULL WHERE `user_id` = ?";
				$this->mDb->query( $query, array( $pUserId ));
			}
		}
	}

	/**
	 * verifyRole
	 *
	 * @param array $pParamHash
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function verifyRole( &$pParamHash ) {
		if( !empty($pParamHash['role_id'] )) {
			if( @$this->verifyId( $pParamHash['role_id'] )) {
				$pParamHash['role_store']['role_id'] = $pParamHash['role_id'];
			} else {
				$this->mErrors['roles'] = 'Unknown Role';
			}
		}

		if( !empty( $pParamHash["name"] )) {
			$pParamHash['role_store']['role_name'] = substr( $pParamHash["name"], 0, 30 );
		}
		if( !empty( $pParamHash["desc"] )) {
			$pParamHash['role_store']['role_desc'] = substr( $pParamHash["desc"], 0, 255 );;
		}
		$pParamHash['role_store']['role_home']              = !empty( $pParamHash["home"] )                    ? $pParamHash["home"]                    : '';
		$pParamHash['role_store']['is_default']              = !empty( $pParamHash["is_default"] )              ? $pParamHash["is_default"]              : NULL;
		$pParamHash['role_store']['user_id']                 = @$this->verifyId( $pParamHash["user_id"] )       ? $pParamHash["user_id"]                 : $this->mUserId;
		$pParamHash['role_store']['is_public']               = !empty( $pParamHash['is_public'] )               ? $pParamHash['is_public']               : NULL;
		$pParamHash['role_store']['after_registration_page'] = !empty( $pParamHash['after_registration_page'] ) ? $pParamHash['after_registration_page'] : '';
		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * storeRole
	 *
	 * @param array $pParamHash
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function storeRole( &$pParamHash ) {
		global $gBitSystem;
		if ($this->verifyRole( $pParamHash)) {
			$this->mDb->StartTrans();
			if( empty( $pParamHash['role_id'] ) ) {
				$pParamHash['role_id'] = $this->mDb->GenID( 'users_roles_id_seq' );
				$pParamHash['role_store']['role_id'] = $pParamHash['role_id'];
				$result = $this->mDb->associateInsert( BIT_DB_PREFIX.'users_roles', $pParamHash['role_store'] );
			} else {
				$sql = "SELECT COUNT(*) FROM `".BIT_DB_PREFIX."users_roles` WHERE `role_id` = ?";
				$roleExists = $this->mDb->getOne($sql, array($pParamHash['role_id']));
				if ($roleExists) {
					$result = $this->mDb->associateUpdate( BIT_DB_PREFIX.'users_roles', $pParamHash['role_store'], array( "role_id" => $pParamHash['role_id'] ) );
				} else {
					// A role_id was specified but that role does not exist yet
					$pParamHash['role_store']['role_id'] = $pParamHash['role_id'];
					$result = $this->mDb->associateInsert(BIT_DB_PREFIX.'users_roles', $pParamHash['role_store']);
				}
			}

			if( isset( $_REQUEST['batch_set_default'] ) and $_REQUEST['batch_set_default'] == 'on' ) {
				$gBitUser->batchSetUserDefaultRole( $pParamHash['role_id'] );
			}
			$this->mDb->CompleteTrans();
		}
		return ( count( $this->mErrors ) == 0 );
	}

	/**
	 * getRoleUserData
	 *
	 * @param array $pRoleId
	 * @param array $pColumns
	 * @access public
	 * @return array of role data
	 */
	function getRoleUserData( $pRoleId, $pColumns ) {
		$ret = array();
		if( @$this->verifyId( $pRoleId ) && !empty( $pColumns ) ) {
			if( is_array( $pColumns ) ) {
				$col = implode( $pColumns, ',' );
				$exec = 'getAssoc';
			} else {
				$col = '`'.$pColumns.'`';
				$exec = 'getArray';
			}
			$query = "
				SELECT $col
				FROM `".BIT_DB_PREFIX."users_users` uu
					INNER JOIN `".BIT_DB_PREFIX."users_roles_map` urm ON (uu.`user_id`=urm.`user_id`)
				WHERE urm.`role_id` = ?";
			$ret = $this->mDb->$exec( $query, array( $pRoleId ));
		}
		return $ret;
	}

	// =-=-=-=-=-=-=-=-=-=-=-= PERMISSION FUNCTIONS =-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	/**
	 * loadPermissions
	 *
	 * @access public
	 * @return TRUE on success, FALSE if no perms were loaded
	 */
	function loadPermissions( $pForceReload=FALSE ) {
		if( $this->isValid() && (empty( $this->mPerms ) || $pForceReload) ) {
			$this->mPerms = array();
			// the double up.`perm_name` is intentional - the first is for hash key, the second is for hash value
			$query = "
				SELECT up.`perm_name` AS `hash_key`, up.`perm_name`, up.`perm_desc`, up.`perm_level`, up.`package`
				FROM `".BIT_DB_PREFIX."users_permissions` up
					INNER JOIN `".BIT_DB_PREFIX."users_role_permissions` urp ON ( urp.`perm_name`=up.`perm_name` )
					INNER JOIN `".BIT_DB_PREFIX."users_roles` ur ON ( ur.`role_id`=urp.`role_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."users_roles_map` urm ON ( urm.`role_id`=urp.`role_id` AND urm.`user_id` = ? )
				WHERE ur.`role_id`= ".ANONYMOUS_TEAM_ID." OR urm.`role_id`=ur.`role_id`";
			$this->mPerms = $this->mDb->getAssoc( $query, array( $this->mUserId ));
			// Add in override permissions
			if( !empty( $this->mPermsOverride ) ) {
				foreach( $this->mPermsOverride as $key => $val ) {
					$this->mPerms[$key] = $val;
				}
			}
		}
		return( count( $this->mPerms ) );
	}

	/**
	 * getUnassignedPerms
	 *
	 * @access public
	 * @return array of permissions that have not been assigned to any role yet
	 */
	function getUnassignedPerms() {
		$query = "SELECT up.`perm_name` AS `hash_key`, up.*
			FROM `".BIT_DB_PREFIX."users_permissions` up
				LEFT OUTER JOIN `".BIT_DB_PREFIX."users_role_permissions` urp ON( up.`perm_name` = urp.`perm_name` )
			WHERE urp.`role_id` IS NULL AND up.`perm_name` <> ?
			ORDER BY `package`, up.`perm_name` ASC";
		return( $this->mDb->getAssoc( $query, array( '' )));
	}

	/**
	 * isAdmin
	 *
	 * @param array $pCheckTicket
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function isAdmin() {
		// we can't use hasPermission here since it turn into an endless loop
		return( !empty( $this->mPerms['p_admin'] ));
	}

	/**
	 * hasPermission check to see if a user has a given permission
	 *
	 * @param array $pPerm Perm name
	 * @access public
	 * @return TRUE if the user has a permission, FALSE if they don't
	 */
	function hasPermission( $pPerm ) {
		$ret = FALSE;
		if( $this->isAdmin() ) {
			$ret = TRUE;
		} elseif( $this->isValid() ) {
			$ret = isset( $this->mPerms[$pPerm] );
		}
		return ( $ret );
	}

	/**
	 * verifyPermission check if a user has a given permission and if not
	 * it will display the error template and die()
	 * @param $pPermission value of a given permission
	 * @return none
	 * @access public
	 */
	function verifyPermission( $pPermission, $pMsg = NULL ) {
		global $gBitSmarty, $gBitSystem, ${$pPermission};
		if( empty( $pPermission ) || $this->hasPermission( $pPermission ) ) {
			return TRUE;
		} else {
			$gBitSystem->fatalPermission( $pPermission, $pMsg );
		}
	}

	/**
	 * getRolePermissions
	 *
	 * @param array $pRoleId Role id, if unset, all roles are returned
	 * @param string $pPackage permissions to give role, if unset, all permissions are returned
	 * @param string $find search for a particular permission
	 * @param array $pSortMode sort mode of return hash
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function getRolePermissions( $pParamHash = NULL ) {
		global $gBitSystem;
		$ret = $bindVars = array();
		$whereSql = $selectSql = $fromSql = '';

		if( !empty( $pParamHash['sort_mode'] )) {
			$sortMode = $this->mDb->convertSortmode( $pParamHash['sort_mode'] );
		} else {
			$sortMode = 'up.`package`, up.`perm_name` ASC';
		}

		if( !empty( $pParamHash['package'] )) {
			$whereSql = ' WHERE `package`= ? ';
			$bindVars[] = $pParamHash['package'];
		}

		if( @BitBase::verifyId( $pParamHash['role_id'] )) {
			$selectSql = ', urp.`perm_value` AS `hasPerm` ';
			$fromSql = ' INNER JOIN `'.BIT_DB_PREFIX.'users_role_permissions` urp ON ( urp.`perm_name`=up.`perm_name` ) ';
			if( $whereSql ) {
				$whereSql .= " AND  urp.`role_id`=?";
			} else {
				$whereSql .= " WHERE urp.`role_id`=?";
			}

			$bindVars[] = $pParamHash['role_id'];
		}

		if( !empty( $pParamHash['find'] )) {
			if( $whereSql ) {
				$whereSql .= " AND `perm_name` like ?";
			} else {
				$whereSql .= " WHERE `perm_name` like ?";
			}
			$bindVars[] = '%'.$pParamHash['find'].'%';
		}

		// the double up.`perm_name` is intentional - the first is for hash key, the second is for hash value
		$query = "
			SELECT up.`perm_name` AS `hash_key`, up.`perm_name`, up.`perm_desc`, up.`perm_level`, up.`package` $selectSql
			FROM `".BIT_DB_PREFIX."users_permissions` up $fromSql $whereSql
			ORDER BY $sortMode";
		$perms = $this->mDb->getAssoc( $query, $bindVars );

		// weed out permissions of inactive packages
		$ret = array();
		foreach( $perms as $key => $perm ) {
			if( $gBitSystem->isPackageActive( $perm['package'] )) {
				$ret[$key] = $perm;
			}
		}

		return $ret;
	}

	/**
	 * assignLevelPermissions Assign the permissions of a given level to a given role
	 *
	 * @param array $pRoleId Role we want to assign permissions to
	 * @param array $pLevel permission level we wish to assign from
	 * @param array $pPackage limit set of permissions to a given package
	 * @access public
	 * @return void
	 */
	function assignLevelPermissions( $pRoleId, $pLevel, $pPackage = NULL) {
		if( @BitBase::verifyId( $pRoleId ) && !empty( $pLevel )) {
			$bindvars = array( $pLevel );
			$whereSql = '';
			if( !empty( $pPackage ) ) {
				$whereSql = ' AND `package`=?';
				array_push( $bindvars, $pPackage );
			}
			$query = "SELECT `perm_name` FROM `".BIT_DB_PREFIX."users_permissions` WHERE `perm_level` = ? $whereSql";
			$result = $this->mDb->query( $query, $bindvars );
			while( $row = $result->fetchRow() ) {
				$this->assignPermissionToRole( $row['perm_name'], $pRoleId );
			}
		}
	}

	/**
	 * getPermissionPackages Get a list of packages that have their own set of permissions
	 *
	 * @access public
	 * @return array of packages
	 */
	function getPermissionPackages() {
		return( $this->mDb->getCol( "SELECT DISTINCT(`package`) FROM `".BIT_DB_PREFIX."users_permissions` ORDER BY `package`" ) );
	}

	/**
	 * assignPermissionToRole
	 *
	 * @param array $perm
	 * @param array $pRoleId
	 * @access public
	 * @return TRUE on success
	 */
	function assignPermissionToRole( $pPerm, $pRoleId ) {
		if( @BitBase::verifyId( $pRoleId ) && !empty( $pPerm )) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."users_role_permissions` WHERE `role_id` = ? AND `perm_name` = ?";
			$result = $this->mDb->query( $query, array( $pRoleId, $pPerm ));
			$query = "INSERT INTO `".BIT_DB_PREFIX."users_role_permissions`(`role_id`, `perm_name`) VALUES(?, ?)";
			$result = $this->mDb->query( $query, array( $pRoleId, $pPerm ));
			return TRUE;
		}
	}

	/**
	 * removePermissionFromRole
	 *
	 * @param string $pPerm Perm name
	 * @param numeric $pRoleId Role ID
	 * @access public
	 * @return TRUE on success
	 */
	function removePermissionFromRole( $pPerm, $pRoleId ) {
		if( @BitBase::verifyId( $pRoleId ) && !empty( $pPerm )) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."users_role_permissions` WHERE `perm_name` = ? AND `role_id` = ?";
			$result = $this->mDb->query($query, array($pPerm, $pRoleId));
			return TRUE;
		}
	}

	/**
	 * storeRegistrationChoice
	 *
	 * @param mixed $pRoleMixed A single role ID or an array of role IDs
	 * @param array $pValue Value you wish to store - use NULL to delete a value
	 * @access public
	 * @return ADO record set on success, FALSE on failure
	 */
	function storeRegistrationChoice( $pRoleMixed, $pValue = NULL ) {
		if( !empty( $pRoleMixed )) {
			$bindVars[] = $pValue;
			if( is_array( $pRoleMixed )) {
				$mid = implode( ',', array_fill( 0, count( $pRoleMixed ),'?' ));
				$bindVars = array_merge( $bindVars, $pRoleMixed );
			} else {
				$bindVars[] = $pRoleMixed;
				$mid = 'LIKE ?';
			}
			$query = "UPDATE `".BIT_DB_PREFIX."users_roles` SET `is_public`= ? where `role_id` IN ($mid)";
			return $this->mDb->query( $query, $bindVars );
		}
	}

	/**
	 * Grant a single permission to a given value
	 */
	function setPermissionOverride( $pPerm, $pValue = NULL ) {
		if( $this->isAdmin() ) {
			$this->mPerms[$pPerm] = TRUE;
			$this->mPermsOverride[$pPerm] = TRUE;
		} elseif( $this->isValid() ) {
			if( $pValue == 'y' || $pValue == TRUE ) {
				$this->mPermsOverride[$pPerm] = TRUE;
				$this->mPerms[$pPerm] = TRUE;
			} else {
				unset( $this->mPermsOverride[$pPerm] );
				unset( $this->mPerms[$pPerm] );
			}
		}
	}
}

/* vim: :set fdm=marker : */
?>
