<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/BitPermUser.php,v 1.73 2008/11/03 18:24:08 spiderr Exp $
 *
 * Lib for user administration, groups and permissions
 * This lib uses pear so the constructor requieres

 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: BitPermUser.php,v 1.73 2008/11/03 18:24:08 spiderr Exp $
 * @package users
 */

/**
 * required setup
 */
require_once( USERS_PKG_PATH.'/BitUser.php' );

/**
 * Class that holds all information for a given user
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.73 $
 * @package  users
 * @subpackage  BitPermUser
 */
class BitPermUser extends BitUser {
	// change this to an email address to receive debug emails from the LDAP code
	// does this work? - xing - Saturday Oct 18, 2008   09:47:20 CEST
	var $debug = FALSE;

	// we use these to cache data
	var $cUserGroups = array();
	var $cGroupPerms = array( array() );

	/**
	 * BitPermUser Initialise class
	 * 
	 * @param numeric $pUserId User ID of the user we wish to load
	 * @param numeric $pContentId Content ID of the user we wish to load
	 * @access public
	 * @return void
	 */
	function BitPermUser( $pUserId=NULL, $pContentId=NULL ) {
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
		global $gBitUser, $user_cookie_site;
		$ret = FALSE;
		// make double sure the current logged in user has permission, check for p_users_admin, not admin, as that is all you need for assuming another user.
		// this enables creating of a non technical site adminstrators group, eg customer support representatives.
		if( $gBitUser->hasPermission( 'p_users_admin' ) ) {
			$assumeUser = new BitPermUser( $pUserId );
			$assumeUser->loadPermissions();

			if( $assumeUser->isAdmin() ) ) {
				$this->mErrors['assume_user'] = tra( "User administrators cannot be assumed." );
			} else {
				$this->mDb->query( "UPDATE `".BIT_DB_PREFIX."users_cnxn` SET `user_id`=?, `assume_user_id`=? WHERE `cookie`=?", array( $pUserId, $gBitUser->mUserId, $_COOKIE[$user_cookie_site] ) );
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
				$this->loadGroups();
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
			$defaultGroups = $this->getDefaultGroup();
			$this->addUserToGroup( $this->mUserId, $defaultGroups );
			if( $gBitSystem->isFeatureActive( 'users_eponymous_groups' ) ) {
				// Create a group just for this user, for permissions assignment.
				$groupParams = array(
					'user_id' => $this->mUserId,
					'name'    => $pParamHash['user_store']['login'],
					'desc'    => "Personal group for ".( !empty( $pParamHash['user_store']['real_name'] ) ? $pParamHash['user_store']['real_name'] : $pParamHash['user_store']['login'] )
				);
				if( $this->storeGroup( $groupParams ) ) {
					$this->addUserToGroup( $this->mUserId, $groupParams['group_id'] );
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
	 * groupExists work out if a given group exists
	 * 
	 * @param string $pGroupName 
	 * @param numeric $pUserId 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function groupExists( $pGroupName, $pUserId = ROOT_USER_ID ) {
		static $sGroups = array();
		if( !isset( $sGroups[$pUserId][$pGroupName] ) ) {
			$bindVars = array( $pGroupName );
			$whereSql = '';
			if( $pUserId != '*' ) {
				$whereSql = 'AND `user_id`=?';
				$bindVars[] = $pUserId;
			}
			$query = "
				SELECT ug.`group_name`, ug.`group_id`,  ug.`user_id`
				FROM `".BIT_DB_PREFIX."users_groups` ug
				WHERE `group_name`=? $whereSql";
			if( $result = $this->mDb->getAssoc( $query, $bindVars ) ) {
				if( empty( $sGroups[$pUserId] ) ) {
					$sGroups[$pUserId] = array();
				}
				$sGroups[$pUserId][$pGroupName] = $result[$pGroupName];
			} else {
				$sGroups[$pUserId][$pGroupName]['group_id'] = NULL;
			}
		}
		return( $sGroups[$pUserId][$pGroupName]['group_id'] );
	}

	/**
	 * removes user and associated private data
	 *
	 * @access public
	 * @return always FALSE???
	 * TODO: fix return
	 */
	function expunge() {
		global $gBitSystem, $gBitUser;
		if( $this->isValid() ) {
			$this->mDb->StartTrans();
			if( $this->mUserId == $gBitUser->mUserId ) {
				$this->mDb->RollbackTrans();
				$gBitSystem->fatalError( tra( 'You cannot delete yourself' ) );
			} elseif( $this->mUserId != ANONYMOUS_USER_ID ) {
				$userTables = array(
					'users_groups_map',
				);

				foreach( $userTables as $table ) {
					$query = "DELETE FROM `".BIT_DB_PREFIX.$table."` WHERE `user_id` = ?";
					$result = $this->mDb->query( $query, array( $this->mUserId ) );
				}

				if( BitUser::expunge( $this->mUserId ) ) {
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



	// =-=-=-=-=-=-=-=-=-=-=-= Group Functions =-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	/**
	 * loadGroups load groups into $this->mGroups
	 * 
	 * @param boolean $pForceRefresh 
	 * @access public
	 * @return void
	 */
	function loadGroups( $pForceRefresh = FALSE ) {
		if( $this->isValid() ) {
			$this->mGroups = $this->getGroups( NULL, $pForceRefresh );
		}
	}

	/**
	 * isInGroup work out if a given user is in a group
	 * 
	 * @param mixed $pGroupMixed Group ID or Group Name (deprecated)
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function isInGroup( $pGroupMixed ) {
		$ret = FALSE;
		if( $this->isAdmin() ) {
			$ret = TRUE;
		} if( $this->isValid() ) {
			if( empty( $this->mGroups ) ) {
				$this->loadGroups();
			}
			if( preg_match( '/A-Za-z/', $pGroupMixed ) ) {
				// Old style group name passed in
				deprecated( "Please use the Group ID instead of the Group name." );
				$ret = in_array( $pGroupMixed, $this->mGroups );
			} else {
				$ret = isset( $this->mGroups[$pGroupMixed] );
			}
		}
		return $ret;
	}

	/**
	 * getAllGroups Get a list of all Groups
	 * 
	 * @param array $pListHash List Hash
	 * @access public
	 * @return array of groups
	 */
	function getAllGroups( &$pListHash ) {
		if( empty(  $pListHash['sort_mode'] ) || $pListHash['sort_mode'] == 'name_asc' ) {
			$pListHash['sort_mode'] = 'group_name_asc';
		}
		$this->prepGetList( $pListHash );

		$sortMode = $this->mDb->convertSortmode( $pListHash['sort_mode'] );
		if( !empty( $pListHash['find_groups'] ) ) {
			$mid = " WHERE UPPER(`group_name`) like ?";
			$bindvars[] = "%".strtoupper( $pListHash['find_groups'] )."%";
		} elseif( !empty( $pListHash['find'] ) ) {
			$mid = " WHERE UPPER(`group_name`) like ?";
			$bindvars[] = "%".strtoupper( $pListHash['find'] )."%";
		} else {
			$mid = '';
			$bindvars = array();
		}

		if( !empty( $pListHash['hide_root_groups'] )) {
			$mid .= !empty( $mid ) ? ' AND ' : ' WHERE ';
			$mid .= '`user_id` <> '.ROOT_USER_ID;
		} elseif( !empty( $pListHash['only_root_groups'] )) {
			$mid .= !empty( $mid ) ? ' AND ' : ' WHERE ';
			$mid .= '`user_id` = '.ROOT_USER_ID;
		}

		if( !empty( $pListHash['is_public'] ) ) {
			if (strlen($mid) > 0) {
				$mid .= ' AND ';
			} else {
				$mid = 'WHERE ';
			}
			$mid .= '`is_public`= ?';
			$bindvars[] = $pListHash['is_public'];
		}

		$query = "
			SELECT `user_id`, `group_id`, `group_name` , `group_desc`, `group_home`, `is_default`, `is_public`
			FROM `".BIT_DB_PREFIX."users_groups` $mid
			ORDER BY $sortMode";
		$ret = array();
		if( $rs = $this->mDb->query( $query, $bindvars ) ) {
			while( $row = $rs->fetchRow() ) {
				$groupId = $row['group_id'];
				$ret[$groupId] = $row;
				$ret[$groupId]['perms'] = $this->getGroupPermissions( array( 'group_id' => $groupId ));
			}
		}

		$pListHash['cant'] = $this->mDb->getOne( "SELECT COUNT(*) FROM `".BIT_DB_PREFIX."users_groups` $mid", $bindvars );

		return $ret;
	}

	/**
	 * getAllUserGroups 
	 * 
	 * @param numeric $pUserId 
	 * @access public
	 * @return array of groups a user belongs to
	 */
	function getAllUserGroups( $pUserId = NULL ) {
		if( empty( $pUserId ) ) {
			$pUserId = $this->mUserId;
		}

		$sql = "
			SELECT ug.`group_id` AS `hash_key`, ug.* FROM `".BIT_DB_PREFIX."users_groups` ug
			WHERE `user_id`=?
			ORDER BY ug.`group_name` ASC";
		return $this->mDb->getAssoc( $sql, array( $pUserId ));
	}

	/**
	 * expungeGroup remove a group
	 * 
	 * @param numeric $pGroupId 
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function expungeGroup( $pGroupId ) {
		// we cannot remove the anonymous group
		if( $pGroupId != ANONYMOUS_GROUP_ID ) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."users_groups_map` WHERE `group_id` = ?";
			$result = $this->mDb->query( $query, array( $pGroupId ));
			$query = "DELETE FROM `".BIT_DB_PREFIX."users_group_permissions` WHERE `group_id` = ?";
			$result = $this->mDb->query( $query, array( $pGroupId ));
			$query = "DELETE FROM `".BIT_DB_PREFIX."users_groups` WHERE `group_id` = ?";
			$result = $this->mDb->query( $query, array( $pGroupId ));
			return TRUE;
		}
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
	 * getDefaultGroup get the default group of a given user
	 * 
	 * @param array $pGroupId pass in a Group ID to make conditional function
	 * @access public
	 * @return Default Group ID if one is set
	 */
	function getDefaultGroup( $pGroupId = NULL ) {
		$bindvars = NULL;
		$whereSql = '';
		if( @BitBase::verifyId( $pGroupId )) {
			$whereSql = "AND `group_id`=? ";
			$bindvars = array( $pGroupId );
		}
		return( $this->mDb->getAssoc( "SELECT `group_id`, `group_name` FROM `".BIT_DB_PREFIX."users_groups` WHERE `is_default` = 'y' $whereSql ", $bindvars ) );
	}

	/**
	 * getGroupUsers Get a list of users who share a given group id
	 * 
	 * @param array $pGroupId 
	 * @access public
	 * @return list of users who are in the group id
	 */
	function getGroupUsers( $pGroupId ) {
		$ret = array();
		if( @BitBase::verifyId( $pGroupId )) {
			$query = "
				SELECT uu.`user_id` AS hash_key, uu.`login`, uu.`real_name`, uu.`user_id`
				FROM `".BIT_DB_PREFIX."users_users` uu
				INNER JOIN `".BIT_DB_PREFIX."users_groups_map` ug ON (uu.`user_id`=ug.`user_id`)
				WHERE `group_id`=?";
			$ret = $this->mDb->getAssoc( $query, array( $pGroupId ));
		}
		return $ret;
	}

	/**
	 * getGroupHome get the URL where a user of that group should be sent
	 * 
	 * @param array $pGroupId 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function getGroupHome( $pGroupId ) {
		$ret = FALSE;
		if( @BitBase::verifyId( $pGroupId )) {
			$query = "SELECT `group_home` FROM `".BIT_DB_PREFIX."users_groups` WHERE `group_id`=?";
			$ret = $this->mDb->getOne( $query,array( $pGroupId ) );
		}
		return $ret;
	}

	/**
	 * storeUserDefaultGroup 
	 * 
	 * @param array $pUserId 
	 * @param array $pGroupId 
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function storeUserDefaultGroup( $pUserId, $pGroupId ) {
		if( @BitBase::verifyId( $pUserId ) && @BitBase::verifyId( $pGroupId )) {
			$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `default_group_id` = ? WHERE `user_id` = ?";
			return $this->mDb->query( $query, array( $pGroupId, $pUserId ));
		}
	}

	/**
	 * batchAssignUsersToGroup assign all users to a given group
	 * 
	 * @param array $pGroupId 
	 * @access public
	 * @return void
	 */
	function batchAssignUsersToGroup( $pGroupId ) {
		if( @BitBase::verifyId( $pGroupId )) {
			$users = $this->getGroupUsers( $pGroupId );
			$result = $this->mDb->getCol( "SELECT uu.`user_id` FROM `".BIT_DB_PREFIX."users_users` uu" );
			foreach( $result as $userId ) {
				if( empty( $users[$userId] ) && $userId != ANONYMOUS_USER_ID ) {
					$this->addUserToGroup( $userId, $pGroupId );
				}
			}
		}
	}

	/**
	 * batchSetUserDefaultGroup 
	 * 
	 * @param array $pGroupId 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function batchSetUserDefaultGroup( $pGroupId ) {
		if( @BitBase::verifyId( $pGroupId )) {
			$users = $this->getGroupUsers($pGroupId);
			foreach( array_keys( $users ) as $userId ) {
				$this->storeUserDefaultGroup( $userId, $pGroupId );
			}
		}
	}

	/**
	 * getGroupInfo 
	 * 
	 * @param array $pGroupId 
	 * @access public
	 * @return group information
	 */
	function getGroupInfo( $pGroupId ) {
		if( @BitBase::verifyId( $pGroupId )) {
			$sql = "SELECT * FROM `".BIT_DB_PREFIX."users_groups` WHERE `group_id` = ?";
			$ret = $this->mDb->getRow( $sql, array( $pGroupId ));

			$listHash = array(
				'group_id' => $pGroupId,
				'sort_mode' => 'up.perm_name_asc',
			);
			$ret["perms"] = $this->getGroupPermissions( $listHash );

			$sql = "SELECT COUNT(*) FROM `".BIT_DB_PREFIX."users_groups_map` WHERE `group_id` = ?";
			$ret['num_members'] = $this->mDb->getOne( $sql, array( $pGroupId ));

			return $ret;
		}
	}

	/**
	 * addUserToGroup Adds user pUserId to group(s) pGroupMixed.
	 * 
	 * @param numeric $pUserId User ID
	 * @param mixed $pGroupMixed A single group ID or an array of group IDs
	 * @access public
	 * @return Either an ADO RecordSet (success) or FALSE (failure).
	 */
	function addUserToGroup( $pUserId, $pGroupMixed ) {
		$result = FALSE;
		if( @BitBase::verifyId( $pUserId ) && !empty( $pGroupMixed )) {
			$result = TRUE;
			$addGroups = array();
			if( is_array( $pGroupMixed ) ) {
				$addGroups = array_keys( $pGroupMixed );
			} elseif( @BitBase::verifyId($pGroupMixed) ) {
				$addGroups = array( $pGroupMixed );
			}
			$currentUserGroups = $this->getGroups( $pUserId );
			foreach( $addGroups AS $groupId ) {
				$isInGroup = FALSE;
				foreach( $currentUserGroups as $curGroupId => $curGroupInfo ) {
					if( $curGroupId == $groupId ) {
						$isInGroup = TRUE;
					}
				}
				if( !$isInGroup ) {
					$query = "INSERT INTO `".BIT_DB_PREFIX."users_groups_map` (`user_id`,`group_id`) VALUES(?,?)";
					$result = $this->mDb->query( $query, array( $pUserId, $groupId ));
				}
			}
		}
		return $result;
	}

	/**
	 * removeUserFromGroup 
	 * 
	 * @param array $pUserId 
	 * @param array $pGroupId 
	 * @access public
	 * @return void
	 */
	function removeUserFromGroup( $pUserId, $pGroupId ) {
		if( @BitBase::verifyId( $pUserId ) && @BitBase::verifyId( $pGroupId )) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."users_groups_map` WHERE `user_id` = ? AND `group_id` = ?";
			$result = $this->mDb->query( $query, array( $pUserId, $pGroupId ));
			$default = $this->getDefaultGroup();
			if( $pGroupId == key( $default )) {
				$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `default_group_id` = NULL WHERE `user_id` = ?";
				$this->mDb->query( $query, array( $pUserId ));
			}
		}
	}

	/**
	 * verifyGroup 
	 * 
	 * @param array $pParamHash 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function verifyGroup( &$pParamHash ) {
		if( !empty($pParamHash['group_id'] )) {
			if( @$this->verifyId( $pParamHash['group_id'] )) {
				$pParamHash['group_store']['group_id'] = $pParamHash['group_id'];
			} else {
				$this->mErrors['groups'] = 'Unknown Group';
			}
		}

		if( !empty( $pParamHash["name"] )) {
			$pParamHash['group_store']['group_name'] = substr( $pParamHash["name"], 0, 30 );
		}
		if( !empty( $pParamHash["desc"] )) {
			$pParamHash['group_store']['group_desc'] = substr( $pParamHash["desc"], 0, 255 );;
		}
		$pParamHash['group_store']['group_home']              = !empty( $pParamHash["home"] )                    ? $pParamHash["home"]                    : '';
		$pParamHash['group_store']['is_default']              = !empty( $pParamHash["is_default"] )              ? $pParamHash["is_default"]              : NULL;
		$pParamHash['group_store']['user_id']                 = @$this->verifyId( $pParamHash["user_id"] )       ? $pParamHash["user_id"]                 : $this->mUserId;
		$pParamHash['group_store']['is_public']               = !empty( $pParamHash['is_public'] )               ? $pParamHash['is_public']               : NULL;
		$pParamHash['group_store']['after_registration_page'] = !empty( $pParamHash['after_registration_page'] ) ? $pParamHash['after_registration_page'] : '';
		return( count( $this->mErrors ) == 0 );
	}

	/**
	 * storeGroup 
	 * 
	 * @param array $pParamHash 
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
	 */
	function storeGroup( &$pParamHash ) {
		global $gBitSystem;
		if ($this->verifyGroup( $pParamHash)) {
			$this->mDb->StartTrans();
			if( empty( $pParamHash['group_id'] ) ) {
				$pParamHash['group_id'] = $this->mDb->GenID( 'users_groups_id_seq' );
				$pParamHash['group_store']['group_id'] = $pParamHash['group_id'];
				$result = $this->mDb->associateInsert( BIT_DB_PREFIX.'users_groups', $pParamHash['group_store'] );
			} else {
				$sql = "SELECT COUNT(*) FROM `".BIT_DB_PREFIX."users_groups` WHERE `group_id` = ?";
				$groupExists = $this->mDb->getOne($sql, array($pParamHash['group_id']));
				if ($groupExists) {
					$result = $this->mDb->associateUpdate( BIT_DB_PREFIX.'users_groups', $pParamHash['group_store'], array( "group_id" => $pParamHash['group_id'] ) );
				} else {
					// A group_id was specified but that group does not exist yet
					$pParamHash['group_store']['group_id'] = $pParamHash['group_id'];
					$result = $this->mDb->associateInsert(BIT_DB_PREFIX.'users_groups', $pParamHash['group_store']);
				}
			}

			if( isset( $_REQUEST['batch_set_default'] ) and $_REQUEST['batch_set_default'] == 'on' ) {
				$gBitUser->batchSetUserDefaultGroup( $pParamHash['group_id'] );
			}
			$this->mDb->CompleteTrans();
		}
		return ( count( $this->mErrors ) == 0 );
	}

	/**
	 * getGroupUserData 
	 * 
	 * @param array $pGroupId 
	 * @param array $pColumns 
	 * @access public
	 * @return array of group data
	 */
	function getGroupUserData( $pGroupId, $pColumns ) {
		$ret = array();
		if( @$this->verifyId( $pGroupId ) && !empty( $pColumns ) ) {
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
					INNER JOIN `".BIT_DB_PREFIX."users_groups_map` ugm ON (uu.`user_id`=ugm.`user_id`)
				WHERE ugm.`group_id` = ?";
			$ret = $this->mDb->$exec( $query, array( $pGroupId ));
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
	function loadPermissions() {
		if( $this->isValid() && empty( $this->mPerms ) ) {
			$this->mPerms = array();
			// the double up.`perm_name` is intentional - the first is for hash key, the second is for hash value
			$query = "
				SELECT up.`perm_name` AS `hash_key`, up.`perm_name`, up.`perm_desc`, up.`perm_level`, up.`package`
				FROM `".BIT_DB_PREFIX."users_permissions` up
					INNER JOIN `".BIT_DB_PREFIX."users_group_permissions` ugp ON ( ugp.`perm_name`=up.`perm_name` )
					INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON ( ug.`group_id`=ugp.`group_id` )
					LEFT OUTER JOIN `".BIT_DB_PREFIX."users_groups_map` ugm ON ( ugm.`group_id`=ugp.`group_id` AND ugm.`user_id` = ? )
				WHERE ug.`group_id`= ".ANONYMOUS_GROUP_ID." OR ugm.`group_id`=ug.`group_id`";
			$this->mPerms = $this->mDb->getAssoc( $query, array( $this->mUserId ));
		}
		return( count( $this->mPerms ) );
	}

	/**
	 * getUnassignedPerms 
	 * 
	 * @access public
	 * @return array of permissions that have not been assigned to any group yet
	 */
	function getUnassignedPerms() {
		$query = "SELECT up.`perm_name` AS `hash_key`, up.*
			FROM `".BIT_DB_PREFIX."users_permissions` up
				LEFT OUTER JOIN `".BIT_DB_PREFIX."users_group_permissions` ugp ON( up.`perm_name` = ugp.`perm_name` )
			WHERE ugp.`group_id` IS NULL AND up.`perm_name` <> ?
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
	 * getGroupPermissions 
	 * 
	 * @param array $pGroupId Group id, if unset, all groups are returned
	 * @param string $pPackage permissions to give group, if unset, all permissions are returned
	 * @param string $find search for a particular permission
	 * @param array $pSortMode sort mode of return hash
	 * @access public
	 * @return TRUE on success, FALSE on failure
	 */
	function getGroupPermissions( $pParamHash = NULL ) {
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

		if( @BitBase::verifyId( $pParamHash['group_id'] )) {
			$selectSql = ', ugp.`perm_value` AS `hasPerm` ';
			$fromSql = ' INNER JOIN `'.BIT_DB_PREFIX.'users_group_permissions` ugp ON ( ugp.`perm_name`=up.`perm_name` ) ';
			if( $whereSql ) {
				$whereSql .= " AND  ugp.`group_id`=?";
			} else {
				$whereSql .= " WHERE ugp.`group_id`=?";
			}

			$bindVars[] = $pParamHash['group_id'];
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
	 * assignLevelPermissions Assign the permissions of a given level to a given group
	 * 
	 * @param array $pGroupId Group we want to assign permissions to
	 * @param array $pLevel permission level we wish to assign from
	 * @param array $pPackage limit set of permissions to a given package
	 * @access public
	 * @return void
	 */
	function assignLevelPermissions( $pGroupId, $pLevel, $pPackage = NULL) {
		if( @BitBase::verifyId( $pGroupId ) && !empty( $pLevel )) {
			$bindvars = array( $pLevel );
			$whereSql = '';
			if( !empty( $pPackage ) ) {
				$whereSql = ' AND `package`=?';
				array_push( $bindvars, $pPackage );
			}
			$query = "SELECT `perm_name` FROM `".BIT_DB_PREFIX."users_permissions` WHERE `perm_level` = ? $whereSql";
			$result = $this->mDb->query( $query, $bindvars );
			while( $row = $result->fetchRow() ) {
				$this->assignPermissionToGroup( $row['perm_name'], $pGroupId );
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
	 * assignPermissionToGroup 
	 * 
	 * @param array $perm 
	 * @param array $pGroupId 
	 * @access public
	 * @return TRUE on success
	 */
	function assignPermissionToGroup( $pPerm, $pGroupId ) {
		if( @BitBase::verifyId( $pGroupId ) && !empty( $pPerm )) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."users_group_permissions` WHERE `group_id` = ? AND `perm_name` = ?";
			$result = $this->mDb->query( $query, array( $pGroupId, $pPerm ));
			$query = "INSERT INTO `".BIT_DB_PREFIX."users_group_permissions`(`group_id`, `perm_name`) VALUES(?, ?)";
			$result = $this->mDb->query( $query, array( $pGroupId, $pPerm ));
			return TRUE;
		}
	}

	/**
	 * removePermissionFromGroup 
	 * 
	 * @param string $pPerm Perm name
	 * @param numeric $pGroupId Group ID
	 * @access public
	 * @return TRUE on success
	 */
	function removePermissionFromGroup( $pPerm, $pGroupId ) {
		if( @BitBase::verifyId( $pGroupId ) && !empty( $pPerm )) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."users_group_permissions` WHERE `perm_name` = ? AND `group_id` = ?";
			$result = $this->mDb->query($query, array($pPerm, $pGroupId));
			return TRUE;
		}
	}

	/**
	 * storeRegistrationChoice 
	 * 
	 * @param mixed $pGroupMixed A single group ID or an array of group IDs
	 * @param array $pValue Value you wish to store - use NULL to delete a value
	 * @access public
	 * @return ADO record set on success, FALSE on failure
	 */
	function storeRegistrationChoice( $pGroupMixed, $pValue = NULL ) {
		if( !empty( $pGroupMixed )) {
			$bindVars[] = $pValue;
			if( is_array( $pGroupMixed )) {
				$mid = implode( ',', array_fill( 0, count( $pGroupMixed ),'?' ));
				$bindVars = array_merge( $bindVars, $pGroupMixed );
			} else {
				$bindVars[] = $pGroupMixed;
				$mid = 'LIKE ?';
			}
			$query = "UPDATE `".BIT_DB_PREFIX."users_groups` SET `is_public`= ? where `group_id` IN ($mid)";
			return $this->mDb->query( $query, $bindVars );
		}
	}



	// {{{ ==================== deprecated methods - will be removed soon ====================
	//  - xing - Saturday Oct 18, 2008   11:38:05 CEST
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function get_user_id( $pUserName ) {
		deprecated( "This method doesn't seem to be used. Please remove this note if it is actually used." );
		if( !empty( $pUserName ) ) {
			$id = $this->mDb->getOne("select `user_id` from `".BIT_DB_PREFIX."users_users` where `login`=?", array($pUserName));
			$id = ($id === NULL) ? -1 : $id;
			return $id;
		}
	}
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function remove_group($pGroupId) {
		deprecated( 'Method has been renamed to $gBitUser->expungeGroup()' );
		$this->expungeGroup( $pGroupId );
	}
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function get_group_users( $pGroupId ) {
		deprecated( 'Method has been renamed to $gBitUser->getGroupUsers()' );
		$this->getGroupUsers( $pGroupId );
	}
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function batch_set_user_default_group( $pGroupId ) {
		deprecated( 'Method has been renamed to $gBitUser->batchSetUserDefaultGroup()' );
		$this->batchSetUserDefaultGroup( $pGroupId );
	}
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function countGroupUsers( $pGroupId ) {
		deprecated( "This method doesn't seem to be used. Please remove this note if it is actually used." );
		static $sGroupUsers = array();
		if( !isset( $sGroupUsers[$pGroupId] ) ) {
			$query = "SELECT COUNT(`user_id`) from `".BIT_DB_PREFIX."users_groups_map` WHERE `group_id` = ?";
			$sGroupUsers[$pGroupId] = $this->mDb->getOne($query, array( $pGroupId ) );
		}
		return $sGroupUsers[$pGroupId];
	}
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function changePermissionLevel($perm, $pLevel) {
		deprecated( "This method doesn't seem to be used. Please remove this note if it is actually used." );
		$query = "update `".BIT_DB_PREFIX."users_permissions` set `perm_level` = ?
			where `perm_name` = ?";
		$this->mDb->query($query, array($pLevel, $perm));
	}
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function removeLevelPermissions($group, $pLevel) {
		deprecated( "This method doesn't seem to be used. Please remove this note if it is actually used." );
		$query = "select `perm_name` from `".BIT_DB_PREFIX."users_permissions` where `perm_level` = ?";
		$result = $this->mDb->query($query, array($pLevel));
		while ($res = $result->fetchRow()) {
			$this->removePermissionFromGroup($res['perm_name'], $group);
		}
	}
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function createDummyLevel($pLevel) {
		deprecated( "This method doesn't seem to be used. Please remove this note if it is actually used." );
		$query = "delete from `".BIT_DB_PREFIX."users_permissions` where `perm_name` = ?";
		$result = $this->mDb->query($query, array(''));
		$query = "insert into `".BIT_DB_PREFIX."users_permissions` (`perm_name`, `perm_desc`,
			`package`, `perm_level`) VALUES ('','','',?)";
		$this->mDb->query($query, array($pLevel));
	}
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function getPermissionLevels() {
		deprecated( "This method doesn't seem to be used. Please remove this note if it is actually used." );
		return( $this->mDb->getCol( "SELECT DISTINCT(`perm_level`) FROM `".BIT_DB_PREFIX."users_permissions` ORDER BY `perm_level`" ));
	}
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function group_has_permission( $pGroupId, $pPerm ) {
		deprecated( "This method doesn't seem to be used. Please remove this note if it is actually used." );
		if (!isset($pPerm, $this->cGroupPerms[$pGroupId][$pPerm])) {
			$query = "SELECT count(*)
					  FROM `".BIT_DB_PREFIX."users_group_permissions`
					  WHERE `group_id`=? AND `perm_name`=?";
			$result = $this->mDb->getOne( $query, array( $pGroupId, $pPerm ) );
			$this->cGroupPerms[$pGroupId][$pPerm] = $result;
			return $result;
		} else {
			return $this->cGroupPerms[$pGroupId][$pPerm];
		}
	}
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function getContentTypeList( $pUserId ) {
		deprecated( "This method doesn't seem to be used. Please remove this note if it is actually used." );
		foreach( $gLibertySystem->mContentTypes as $contentType ) {
			// TODO: if this method is used somewhere, please fix this to use mViewContentPerm
			$perm = $contentType["content_type_guid"].'_p_view';
			if (!empty( $perm ) and $gBitUser->hasPermission( $perm )) {
				$contentTypes[$contentType["content_type_guid"]] = $contentType["content_description"];
			}
		}
	}
	/**
	 * @deprecated deprecated since version 2.1.0-beta
	 */
	function setPermission( $pPerm, $pValue = NULL ) {
		deprecated( "This method doesn't do what it's supposed to and it seems it's not used." );
		if( $this->isAdmin() ) {
			$this->mPerms[$pPerm] = TRUE;
		} elseif( $this->isValid() ) {
			if( $pValue == 'y' || $pValue == TRUE ) {
				$this->mPerms[$pPerm] = TRUE;
			} else {
				unset( $this->mPerms[$pPerm] );
			}
		}
	}
	// }}}
}

/* vim: :set fdm=marker : */
?>
