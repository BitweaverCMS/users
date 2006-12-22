<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/BitPermUser.php,v 1.48 2006/12/22 20:29:56 squareing Exp $
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
 * $Id: BitPermUser.php,v 1.48 2006/12/22 20:29:56 squareing Exp $
 * @package users
 */

/**
 * required setup
 */
require_once( dirname( __FILE__ ).'/BitUser.php' );

/**
 * Class that holds all information for a given user
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.48 $
 * @package  users
 * @subpackage  BitPermUser
 */
class BitPermUser extends BitUser {
# var $db;  // The PEAR db object used to access the database
	// change this to an email address to receive debug emails from the LDAP code
	var $debug = false;
	var $usergroups_cache;
	var $groupperm_cache;

	function BitPermUser( $pUserId=NULL, $pContentId=NULL ) {
		BitUser::BitUser( $pUserId, $pContentId );
		// Initialize caches
		$this->usergroups_cache = array();
		$this->groupperm_cache = array(array());
	}

	function assumeUser( $pUserId ) {
		global $gBitUser, $user_cookie_site;
		$ret = FALSE;
		// make double sure the current logged in user has permission
		if( $gBitUser->hasPermission( 'p_users_admin' ) ) {
			$assumeUser = new BitPermUser( $pUserId );
			$assumeUser->loadPermissions();

			if( $assumeUser->hasPermission( 'p_users_admin' ) ) {
				$this->mErrors['assume_user'] = tra( "User administrators cannot be assumed." );
			} else {
				$this->mDb->query( "UPDATE `".BIT_DB_PREFIX."users_cnxn` SET `user_id`=?, `assume_from_user_id`=? WHERE `cookie`=?", array( $pUserId, $gBitUser->mUserId, $_COOKIE[$user_cookie_site] ) );
				$ret = TRUE;
			}
		}
		return $ret;
	}

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

	// Used to hide sensitive information when it is unneccessary (i.e. $gQueryUser)
	function sanitizeUserInfo() {
		if (!empty($this->mInfo)) {
			if (!empty($this->mInfo['provpass'])) {
				unset($this->mInfo['provpass']);
			}
			if (!empty($this->mInfo['hash'])) {
				unset($this->mInfo['hash']);
			}
			if (!empty($this->mInfo['challenge'])) {
				unset($this->mInfo['challenge']);
			}
			if (!empty($this->mInfo['user_password'])) {
				unset($this->mInfo['user_password']);
			}
		}
	}

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
					'name' => $pParamHash['user_store']['login'],
					'desc' => "Personal group for ".(!empty( $pParamHash['user_store']['real_name'] ) ? $pParamHash['user_store']['real_name'] : $pParamHash['user_store']['login'])
				);
				if( $this->storeGroup( $groupParams ) ) {
					$this->addUserToGroup( $this->mUserId, $groupParams['group_id'] );
				}
			}
			$this->load( TRUE );
		}
		$this->mDb->CompleteTrans();
		return( count( $this->mErrors ) == 0 );
	}

	function groupExists( $pGroupName, $pUserId=ROOT_USER_ID ) {
		static $rv = array();
		if( !isset( $rv[$pUserId][$pGroupName] ) ) {
			$bindVars = array( $pGroupName );
			$whereSql = '';
			if( $pUserId != '*' ) {
				$whereSql = 'AND `user_id`=?';
				$bindVars[] = $pUserId;
			}
			$query = "SELECT ug.`group_name`, ug.`group_id`,  ug.`user_id`
					  FROM `".BIT_DB_PREFIX."users_groups` ug
					  WHERE `group_name`=? $whereSql";
			if( $result = $this->mDb->getAssoc( $query, $bindVars ) ) {
				if( empty( $rv[$pUserId] ) ) {
					$rv[$pUserId] = array();
				}
				$rv[$pUserId][$pGroupName] = $result[$pGroupName];
			} else {
				$rv[$pUserId][$pGroupName]['group_id'] = NULL;
			}
		}
		return( $rv[$pUserId][$pGroupName]['group_id'] );
	}

	/**
	 * removes user and associated private data
	 *
	 * @access public
	 * @return TRUE on success, FALSE on failure - mErrors will contain reason for failure
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
	// =-=-=-=-=-=-=-=-=-=-=-= GROUP FUNCTIONS =-=-=-=-=-=-=-=-=-=-=-=-=-=-=

	function loadGroups( $pForceRefresh = FALSE ) {
		if( $this->isValid() ) {
			$this->mGroups = $this->getGroups( NULL, $pForceRefresh );
		}
	}

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
				$ret = in_array( $pGroupMixed, $this->mGroups );
			} else {
				$ret = isset( $this->mGroups[$pGroupMixed] );
			}
		}
		return $ret;
	}

	function getAllGroups( &$pListHash ) {
		if( empty(  $pListHash['sort_mode'] ) || $pListHash['sort_mode'] == 'name_asc' ) {
 			$pListHash['sort_mode'] = 'group_name_asc';
		}
		$this->prepGetList( $pListHash );

		$sortMode = $this->mDb->convert_sortmode( $pListHash['sort_mode'] );
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

		if (!empty($pListHash['hide_root_groups'])) {
			if (strlen($mid) > 0) {
				$mid .= ' AND `user_id` <> '.ROOT_USER_ID;
			} else {
				$mid = " WHERE `user_id` <> ".ROOT_USER_ID;
			}
		}
		if ( !empty( $pListHash['is_public'] ) ) {
			if (strlen($mid) > 0) {
				$mid .= ' AND ';
			} else {
				$mid = 'WHERE ';
			}
			$mid .= '`is_public`= ?';
			$bindvars[] = $pListHash['is_public'];
		}

		$query = "SELECT `user_id`, `group_id`, `group_name` , `group_desc`, `group_home`, `is_default`, `is_public`
				  FROM `".BIT_DB_PREFIX."users_groups` $mid
				  ORDER BY $sortMode";
		$ret = array();
		if( $rs = $this->mDb->query( $query, $bindvars ) ) {
			while( $row = $rs->fetchRow() ) {
				$groupId = $row['group_id'];
				$ret[$groupId] = $row;
				$ret[$groupId]['perms'] = $this->getGroupPermissions( $groupId );
				$inc = array();
				$this->getIncludedGroups( $groupId, $inc );
				$ret[$groupId]['included'] = $inc;
			}
		}
		$query_cant = "select count(*) from `".BIT_DB_PREFIX."users_groups` $mid";
		$cant = $this->mDb->getOne($query_cant, $bindvars);
		$retval = array();
		$retval["data"] = $ret;
		$retval["cant"] = $cant;
		return $retval;
	}

	function getAllUserGroups( $pUserId=NULL ) {
		if( empty( $pUserId ) ) {
			$pUserId = $this->mUserId;
		}

		$sql = "SELECT ug.`group_id`, ug.* FROM `".BIT_DB_PREFIX."users_groups` ug
				WHERE `user_id`=?
				ORDER BY ug.`group_name` ASC";
		return $this->mDb->getAssoc($sql, array( $pUserId ) );
	}

	function get_user_id( $pUserName ) {
		if( !empty( $pUserName ) ) {
			$id = $this->mDb->getOne("select `user_id` from `".BIT_DB_PREFIX."users_users` where `login`=?", array($pUserName));
			$id = ($id === NULL) ? -1 : $id;
			return $id;
		}
	}
/*
	function get_included_groups($pGroupId) {
		$query = "SELECT `include_group_id`, ug.`group_name`
				  FROM `".BIT_DB_PREFIX."users_groups_inclusion` ugi INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON (ug.`group_id`=ugi.`group_id`)
				  WHERE ugi.`group_id`=?";
		return( $this->mDb->getAssoc( $query, array($pGroupId) ) );
	}

	function get_user_groups( $pUserId ) {
		if (!@$this->verifyId($pUserId)) {
			// For legacy calls still using $user as the parameter
			$pUserId = $this->get_user_id($pUserId);
		}
		if (!isset($this->usergroups_cache[$pUserId])) {
			//$userid = $this->get_user_id($user);
			$query = "SELECT ug.`group_id`, ug.`group_name`
					  FROM `".BIT_DB_PREFIX."users_groups_map` ugm INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON (ug.`group_id`=ugm.`group_id`)
					  WHERE ugm.`user_id`=? OR ug.`group_name`='Anonymous'";
			$ret = $this->mDb->getAssoc($query, array((int)$pUserId));
			// cache it
			$this->usergroups_cache[$pUserId] = $ret;
			return $ret;
		} else {
			return $this->usergroups_cache[$pUserId];
		}
	}
*/

	// we cannot remove the anonymous group
	function remove_group($pGroupId) {
		if( $pGroupId != ANONYMOUS_GROUP_ID ) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."users_groups_inclusion`
					  WHERE `group_id` = ? OR `include_group_id` = ?";
			$result = $this->mDb->query($query, array($pGroupId, $pGroupId));
			$query = "delete from `".BIT_DB_PREFIX."users_group_permissions` where `group_id` = ?";
			$result = $this->mDb->query($query, array($pGroupId));
			$query = "delete from `".BIT_DB_PREFIX."users_groups` where `group_id` = ?";
			$result = $this->mDb->query($query, array($pGroupId));
			return true;
		}
	}

	function getGroups( $pUserId=NULL, $pForceRefresh = FALSE ) {
		$pUserId = !empty( $pUserId ) ? $pUserId : $this->mUserId;
		if (!isset($this->usergroups_cache[$pUserId]) || $pForceRefresh) {
			$query = "SELECT ug.`group_id`, ug.`group_name`, ug.`user_id` as group_owner_user_id
					  FROM `".BIT_DB_PREFIX."users_groups_map` ugm INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON (ug.`group_id`=ugm.`group_id`)
					  WHERE ugm.`user_id`=? OR ugm.`group_id`=".ANONYMOUS_GROUP_ID;
			$ret = $this->mDb->getAssoc($query, array((int)$pUserId));
			if( $ret ) {
				foreach( array_keys( $ret ) as $groupId ) {
					$res = array();
					$this->getIncludedGroups( $groupId, $res );
					foreach( $res as $key=>$val) {
						$ret[$key] = array('group_name' => $val);
					}
				}
			}
			// cache it
			$this->usergroups_cache[$pUserId] = $ret;
			return $ret;
		} else {
			return $this->usergroups_cache[$pUserId];
		}
	}

	function getIncludedGroups( $pGroupId, &$pIncludes ) {
		$query = "SELECT ugi.`include_group_id`, ug.`group_name`
				  FROM `".BIT_DB_PREFIX."users_groups_inclusion` ugi
				  	INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON ( ugi.`include_group_id`=ug.`group_id` )
				  WHERE ugi.`group_id`=?";
		$ret = $this->mDb->getAssoc($query, array($pGroupId));
		if( $ret ) {
			foreach( array_keys( $ret ) as $groupId ) {
				if( empty( $pIncludes[$groupId] ) ) {
					$pIncludes[$groupId] = $ret[$groupId];
					$this->getIncludedGroups( $groupId, $pIncludes );
				}
			}
		}
    }

	function addGroupInclusion( $pGroupId, $pIncludeId ) {
		if( @BitBase::verifyId( $pGroupId ) && @BitBase::verifyId( $pIncludeId )  ) {
			$query = "INSERT INTO `".BIT_DB_PREFIX."users_groups_inclusion` (`group_id`,`include_group_id`)
					  VALUES(?,?)";
			$this->mDb->query($query, array($pGroupId, $pIncludeId));
		}
	}

	function removeGroupInclusions( $pGroupId ) {
		if( @BitBase::verifyId( $pGroupId ) ) {
			$query = "DELETE FROM `".BIT_DB_PREFIX."users_groups_inclusion` where `group_id` = ?";
			$result = $this->mDb->query($query, array($pGroupId));
		}
		return true;
	}


	// pass in pGroupId to make conditional function
	function getDefaultGroup( $pGroupId=NULL ) {
		$bindvars = NULL;
		$whereSql = '';
		if( !empty( $pGroupId ) ) {
			$whereSql = "AND `group_id`=? ";
			$bindvars = array( $pGroupId );
		}
		return( $this->mDb->getAssoc("select `group_id`, `group_name` from `".BIT_DB_PREFIX."users_groups` where `is_default` = 'y' $whereSql ", $bindvars ) );
	}

	function get_group_users( $pGroupId ) {
		$query = "SELECT uu.`user_id` AS hash_key, uu.`login`, uu.`real_name`, uu.`user_id` FROM `".BIT_DB_PREFIX."users_users` uu INNER JOIN `".BIT_DB_PREFIX."users_groups_map` ug ON (uu.`user_id`=ug.`user_id`) WHERE `group_id`=?";
		return( $this->mDb->getAssoc( $query, array( $pGroupId ) ) );
	}

	function getGroupHome( $pGroupId ) {
		$ret = FALSE;
		$query = "SELECT `group_home` FROM `".BIT_DB_PREFIX."users_groups` WHERE `group_id`=?";
		$result = $this->mDb->query( $query,array( $pGroupId ) );
		while($res = $result->fetchRow()) {
			$ret = $res['group_home'];
		}
		return $ret;
	}

	function storeUserDefaultGroup( $pUserId, $pGroupId ) {
		$query = "UPDATE `".BIT_DB_PREFIX."users_users` SET `default_group_id` = ?
				  WHERE `user_id` = ?";
		$this->mDb->query($query, array( $pGroupId, $pUserId ) );
	}

	function batchAssignUsersToGroup( $pGroupId ) {
		$users = $this->get_group_users($pGroupId);
		$rs = $this->mDb->getCol( "SELECT uu.`user_id` FROM `".BIT_DB_PREFIX."users_users` uu" );
		foreach( $rs as $userId ) {
			if( empty( $users[$userId] ) && ($userId != ANONYMOUS_USER_ID) ) {
				$this->addUserToGroup( $userId, $pGroupId );
			}
		}
	}

	function batch_set_user_default_group( $pGroupId ) {
		$users = $this->get_group_users($pGroupId);
		foreach( array_keys( $users ) as $userId ) {
			$this->storeUserDefaultGroup( $userId,$pGroupId );
		}
	}

	function getGroupInfo( $pGroupId ) {
		$query = "select * from `".BIT_DB_PREFIX."users_groups` where `group_id`=?";
		$result = $this->mDb->query($query, array($pGroupId));
		$res = $result->fetchRow();
		$perms = $this->getGroupPermissions($pGroupId, NULL, NULL, 'up.perm_name_asc');
		$sql = "SELECT COUNT(*) FROM `".BIT_DB_PREFIX."users_groups_map` WHERE `group_id` = ?";
		$res['num_members'] = $this->mDb->getOne($sql, array($pGroupId));
		$res["perms"] = $perms;
		return $res;
	}
	function addUserToGroup( $pUserId, $pGroupMixed ) {
		$result = TRUE;
		$addGroups = array();
		if( is_array( $pGroupMixed ) ) {
			$addGroups = array_keys( $pGroupMixed );
		} elseif( @BitBase::verifyId($pGroupMixed) ) {
			$addGroups = array( $pGroupMixed );
		}
		$currentUserGroups = $this->getGroups($pUserId);
		foreach( $addGroups AS $groupId ) {
			$isInGroup = FALSE;
			foreach ($currentUserGroups as $curGroupId => $curGroupInfo) {
				if ($curGroupId == $groupId) {
					$isInGroup = TRUE;
				}
			}
			if ( !$isInGroup ) {
				$query = "insert into `".BIT_DB_PREFIX."users_groups_map`(`user_id`,`group_id`) values(?,?)";
				$result = $this->mDb->query($query, array( $pUserId, $groupId ), -1, -1);
			}
		}
		return $result;
	}

    function removeUserFromGroup( $pUserId, $pGroupId ) {
		$query = "delete from `".BIT_DB_PREFIX."users_groups_map` where `user_id` = ? and
			`group_id` = ?";
		$result = $this->mDb->query($query, array($pUserId, $pGroupId));
		$keyarray = $this->getDefaultGroup();
		if( $pGroupId == key( $keyarray ) ) {
			$query = "update `".BIT_DB_PREFIX."users_users` set `default_group_id` = NULL where `user_id` = ?";
			$this->mDb->query($query, array( $pUserId ) );
		}
	}

	function verifyGroup( &$pParamHash ) {
		if (!empty($pParamHash['group_id'])) {
			if( @$this->verifyId( $pParamHash['group_id'] ) ) {
				$pParamHash['group_store']['group_id'] = $pParamHash['group_id'];
			} else {
				$this->mErrors['groups'] = 'Unknown Group';
			}
		}

		if( !empty( $pParamHash["name"] ) ) {
			$pParamHash['group_store']['group_name'] = substr( $pParamHash["name"], 0, 30 );
		}
		if( !empty( $pParamHash["desc"] ) ) {
			$pParamHash['group_store']['group_desc'] = $pParamHash["desc"];
		}
		$pParamHash['group_store']['group_home'] = !empty( $pParamHash["home"] ) ? $pParamHash["home"] : '';
		$pParamHash['group_store']['is_default'] = !empty( $pParamHash["is_default"] ) ? $pParamHash["is_default"] : NULL;
		$pParamHash['group_store']['user_id'] = @$this->verifyId( $pParamHash["user_id"] ) ? $pParamHash["user_id"] : $this->mUserId;
		$pParamHash['group_store']['is_public'] = !empty( $pParamHash['is_public'] ) ? $pParamHash['is_public'] : NULL;
		$pParamHash['group_store']['after_registration_page'] = !empty( $pParamHash['after_registration_page'] ) ? $pParamHash['after_registration_page'] : '';
		return( count( $this->mErrors ) == 0 );
	}

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

			$this->removeGroupInclusions( $pParamHash['group_id'] );
			if (isset($pParamHash["include_groups"])) {
				foreach( $pParamHash["include_groups"] as $includeId ) {
					if( $pParamHash["group_id"] != $includeId ) {
						$this->addGroupInclusion( $pParamHash['group_id'], $includeId );
					}
				}
			}
			if (isset($_REQUEST['batch_set_default']) and $_REQUEST['batch_set_default'] == 'on') {
				$gBitUser->batch_set_user_default_group( $pParamHash['group_id'] );
			}
			$this->mDb->CompleteTrans();
		}
		return ( count( $this->mErrors ) == 0 );
	}


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
			$query = "SELECT $col
					  FROM `".BIT_DB_PREFIX."users_users` uu
					  	INNER JOIN `".BIT_DB_PREFIX."users_groups_map` ugm ON (uu.`user_id`=ugm.`user_id`)
					  WHERE ugm.`group_id` = ?";
			$ret = $this->mDb->$exec($query, array( $pGroupId ) );
		}
		return $ret;
	}


	function countGroupUsers($pGroupId) {
		static $rv = array();
		if( !isset( $rv[$pGroupId] ) ) {
			$query = "select count(`user_id`) from `".BIT_DB_PREFIX."users_groups_map` where `group_id` = ?";
			$rv[$pGroupId] = $this->mDb->getOne($query, array( $pGroupId ) );
		}
		return $rv[$pGroupId];
	}

	// =-=-=-=-=-=-=-=-=-=-=-= PERMISSION FUNCTIONS =-=-=-=-=-=-=-=-=-=-=-=-=-=-=
	function loadPermissions() {
		if( $this->isValid() && empty( $this->mPerms ) ) {
			$this->mPerms = array();
			/* **** NOTICE **** This query is dog slow! I get much better performance with the alternative method below - drewslater
			*/
		// the double up.`perm_name` is intentional - the first is for hash key, the second is for hash value
			$query = "SELECT up.`perm_name` AS `hash_key`, up.`perm_name`, up.`perm_desc`, up.`perm_level`, up.`package`
					  FROM `".BIT_DB_PREFIX."users_permissions` up
						INNER JOIN `".BIT_DB_PREFIX."users_group_permissions` ugp ON ( ugp.`perm_name`=up.`perm_name` )
						INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON ( ug.`group_id`=ugp.`group_id` )
					    LEFT OUTER JOIN `".BIT_DB_PREFIX."users_groups_map` ugm ON ( ugm.`group_id`=ugp.`group_id` AND ugm.`user_id` = ?)
					  WHERE ug.`group_id`= ".ANONYMOUS_GROUP_ID." OR ugm.`group_id`=ug.`group_id`";
			$this->mPerms = $this->mDb->getAssoc( $query, array( $this->mUserId ) );
/*
			// This is uglier, but much faster!
			$this->loadGroups();
			$groupIdsString = '';
			$groupCount = 1;
			foreach ($this->mGroups as $groupId => $groupName) {
				$groupIdsString .= $groupId.($groupCount++ >= count($this->mGroups) ? '' : ', ');
			}
			if ( $groupCount > 1)
			{	$sql = "SELECT up.`perm_name`, up.`perm_desc`, up.`perm_level`, up.`package` FROM `".BIT_DB_PREFIX."users_permissions` up
						INNER JOIN `".BIT_DB_PREFIX."users_group_permissions` ugp ON (ugp.`perm_name` = up.`perm_name`)
						WHERE ugp.`group_id` IN ($groupIdsString)";
				$this->mPerms = $this->mDb->getAssoc( $sql );
			}
*/
		}
		return( count( $this->mPerms ) );
	}


	function getUnassignedPerms() {
		$query = "SELECT up.`perm_name` AS `hash_key`, up.*
			FROM `".BIT_DB_PREFIX."users_permissions` up
			LEFT JOIN `".BIT_DB_PREFIX."users_group_permissions` ugp ON( up.`perm_name` = ugp.`perm_name` )
			WHERE ugp.`group_id` IS NULL
			ORDER BY `package`, up.`perm_name` ASC";
		return( $this->mDb->getAssoc( $query ) );
	}

	// If the request has a ticket, some form action is being processed, and we need to validate we have a matched ticket to avoid XSS
	function isAdmin( $pCheckTicket=FALSE ) {
		$ret = !empty( $this->mPerms['p_admin'] );
		return( $ret );
	}

	function hasPermission( $pPerm ) {
		$ret = FALSE;
		if( $this->isAdmin() ) {
			$ret = TRUE;
		} elseif( $this->isValid() ) {
			$ret = isset( $this->mPerms[$pPerm] );
		}
		return ( $ret );
	}

	// temporarily set the permission for the active user
	// does NOT store the permission
	function setPermission( $pPerm, $pValue = NULL ) {
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
	function getGroupPermissions( $pGroupId=NULL, $pPackage = '', $find = '', $pSortMode = NULL ) {
		global $gBitSystem;
		$values = array();
		$mid = $selectSql = $fromSql = '';

		if( !empty( $pSortMode ) ) {
			$sortMode = $this->mDb->convert_sortmode( $pSortMode );
		} else {
			$sortMode = 'up.`package`, up.`perm_name` ASC';
		}

		if( $pPackage ) {
			$mid = ' WHERE `package`= ? ';
			$values[] = $pPackage;
		}

		if( @$this->verifyId( $pGroupId ) ) {
			$selectSql = ', ugp.`perm_value` AS `hasPerm` ';
			$fromSql = ' INNER JOIN `'.BIT_DB_PREFIX.'users_group_permissions` ugp ON ( ugp.`perm_name`=up.`perm_name` ) ';
			if( $mid ) {
				$mid .= " AND  ugp.`group_id`=?";
			} else {
				$mid .= " WHERE ugp.`group_id`=?";
			}
			$values[] = $pGroupId;
		}

		if( $find ) {
			if( $mid ) {
				$mid .= " AND `perm_name` like ?";
			} else {
				$mid .= " WHERE `perm_name` like ?";
			}
			$values[] = '%'.$find.'%';
		}
		// the double up.`perm_name` is intentional - the first is for hash key, the second is for hash value
		$query = "SELECT up.`perm_name` AS `hash_key`, up.`perm_name`, up.`perm_desc`, up.`perm_level`, up.`package` $selectSql
				  FROM `".BIT_DB_PREFIX."users_permissions` up $fromSql $mid
				  ORDER BY $sortMode";
		$perms = $this->mDb->getAssoc( $query, $values );

		// weed out permissions of inactive packages
		$ret = array();
		foreach( $perms as $key => $perm ) {
			if( $gBitSystem->isPackageActive( $perm['package'] ) ) {
				$ret[$key] = $perm;
			}
		}
		return $ret;
	}


	function assign_object_permission($pGroupId, $object_id, $object_type, $perm_name) {
		//$object_id = md5($object_type . $object_id);
		$query = "DELETE FROM `".BIT_DB_PREFIX."users_object_permissions`
				  WHERE `group_id` = ? AND `perm_name` = ? AND `object_id` = ?";
		$result = $this->mDb->query($query, array($pGroupId, $perm_name, $object_id), -1, -1);
		$query = "insert into `".BIT_DB_PREFIX."users_object_permissions`
				  (`group_id`,`object_id`, `object_type`, `perm_name`)
				  VALUES ( ?, ?, ?, ? )";
		$result = $this->mDb->query($query, array($pGroupId, $object_id,$object_type, $perm_name));
		return true;
	}

	function object_has_permission( $pUserId = NULL, $object_id, $object_type, $perm_name, $pForceRefresh = FALSE ) {
		$ret = FALSE;
		$groups = $this->getGroups($pUserId, $pForceRefresh);

		foreach ( $groups as $groupId => $group_name ) {
			$query = "SELECT count(*)
					  FROM `".BIT_DB_PREFIX."users_object_permissions`
					  WHERE `group_id` = ? and `object_id` = ? and `object_type` = ? and `perm_name` = ?";
					  //pvd($query);pvd($sd="groupid: $groupId | object_id: $object_id | object_type: $object_type | permname: $perm_name");
			$bindvars = array($groupId, $object_id, $object_type, $perm_name);
			$result = $this->mDb->getOne( $query, $bindvars );
			if ($result>0) {
				$ret = true;
			}
		}
		return $ret;
	}


	function remove_object_permission($pGroupId, $object_id, $object_type, $perm_name) {
		//$object_id = md5($object_type . $object_id);
		$query = "delete from `".BIT_DB_PREFIX."users_object_permissions`
			where `group_id` = ? and `object_id` = ?
			and `object_type` = ? and `perm_name` = ?";
		$bindvars = array($pGroupId, $object_id, $object_type, $perm_name);
		$result = $this->mDb->query($query, $bindvars);
		return true;
	}


	function copy_object_permissions($object_id,$destinationObjectId,$object_type) {
		//$object_id = md5($object_type.$object_id);
		$query = "select `perm_name`, `group_name`
			from `".BIT_DB_PREFIX."users_object_permissions`
			where `object_id` =? and
			`object_type` = ?";
		$bindvars = array($object_id, $object_type);
		$result = $this->mDb->query($query, $bindvars);
		while($res = $result->fetchRow()) {
			$this->assign_object_permission($res["group_name"],$destinationObjectId,$object_type,$res["perm_name"]);
		}
		return true;
	}


	function get_object_permissions($object_id, $object_type) {
		//$object_id = md5($object_type . $object_id);
		$query = "select ug.`group_id`, ug.`group_name`, uop.`perm_name`
				  FROM `".BIT_DB_PREFIX."users_object_permissions` uop
					INNER JOIN `".BIT_DB_PREFIX."users_groups` ug ON( uop.`group_id`=ug.`group_id` )
				  WHERE uop.`object_id` = ? AND uop.`object_type` = ?";
		$bindvars = array($object_id, $object_type);
		$result = $this->mDb->query($query, $bindvars);
		$ret = array();
		while ($res = $result->fetchRow()) {
			$ret[] = $res;
		}
		return $ret;
	}


	function object_has_one_permission( $object_id, $object_type ) {
		$ret = NULL;
		if( @$this->verifyId( $object_id ) && !empty( $object_type )  ) {
			//$object_id = md5($object_type . $object_id);
			$query = "select count(*) from `".BIT_DB_PREFIX."users_object_permissions` where `object_id`=? and `object_type`=?";
			$ret = $this->mDb->getOne($query, array( $object_id, $object_type	));
		}
		return $ret;
	}


	function change_permission_level($perm, $level) {
		$gBitCache = new BitCache();
		$gBitCache->removeCached("allperms");
		$query = "update `".BIT_DB_PREFIX."users_permissions` set `perm_level` = ?
			where `perm_name` = ?";
		$this->mDb->query($query, array($level, $perm));
	}


	function assign_level_permissions( $pGroupId, $level, $pPackage=NULL) {
		$gBitCache = new BitCache();
		$gBitCache->removeCached("allperms");
		$bindvars = array($level);
		$whereSql = '';
		if( !empty( $pPackage ) ) {
			$whereSql = ' AND `package`=?';
			array_push( $bindvars, $pPackage );
		}
		$query = "SELECT `perm_name` FROM `".BIT_DB_PREFIX."users_permissions` WHERE `perm_level` = ? $whereSql";
		$result = $this->mDb->query($query, $bindvars);
		$ret = array();
		if( $result ) {
			while ($row = $result->fetchRow()) {
				$this->assignPermissionToGroup($row['perm_name'], $pGroupId );
			}
		}
	}


	function remove_level_permissions($group, $level) {
		$gBitCache = new BitCache();
		$gBitCache->removeCached("allperms");
		$query = "select `perm_name` from `".BIT_DB_PREFIX."users_permissions` where `perm_level` = ?";
		$result = $this->mDb->query($query, array($level));
		$ret = array();
		while ($res = $result->fetchRow()) {
			$this->remove_permission_from_group($res['perm_name'], $group);
		}
	}


	function create_dummy_level($level) {
		$gBitCache = new BitCache();
		$gBitCache->removeCached("allperms");
		$query = "delete from `".BIT_DB_PREFIX."users_permissions` where `perm_name` = ?";
		$result = $this->mDb->query($query, array(''));
		$query = "insert into `".BIT_DB_PREFIX."users_permissions` (`perm_name`, `perm_desc`,
			`package`, `perm_level`) VALUES ('','','',?)";
		$this->mDb->query($query, array($level));
	}


	function getPermissionLevels() {
		return ( $this->mDb->getCol( "select distinct(`perm_level`) from `".BIT_DB_PREFIX."users_permissions` ORDER BY `perm_level`" ) );
	}


	function getPermissionPackages() {
		return( $this->mDb->getCol( "select distinct(`package`) from `".BIT_DB_PREFIX."users_permissions` ORDER BY `package`" ) );
	}


	function assignPermissionToGroup( $perm, $pGroupId ) {
		$gBitCache = new BitCache();
		$gBitCache->removeCached("allperms");
		$query = "DELETE FROM `".BIT_DB_PREFIX."users_group_permissions` WHERE `group_id` = ? AND `perm_name` = ?";
		$result = $this->mDb->query($query, array($pGroupId, $perm));
		$query = "INSERT INTO `".BIT_DB_PREFIX."users_group_permissions`(`group_id`, `perm_name`) VALUES(?, ?)";
		$result = $this->mDb->query($query, array($pGroupId, $perm));
		return TRUE;
	}


	function group_has_permission( $pGroupId, $perm ) {
		if (!isset($perm, $this->groupperm_cache[$pGroupId][$perm])) {
			$query = "SELECT count(*)
					  FROM `".BIT_DB_PREFIX."users_group_permissions`
					  WHERE `group_id`=? AND `perm_name`=?";
			$result = $this->mDb->getOne( $query, array( $pGroupId, $perm ) );
			$this->groupperm_cache[$pGroupId][$perm] = $result;
			return $result;
		} else {
			return $this->groupperm_cache[$pGroupId][$perm];
		}
	}


	function remove_permission_from_group($perm, $pGroupId) {
		$gBitCache = new BitCache();
		$gBitCache->removeCached("allperms");
		$query = "delete from `".BIT_DB_PREFIX."users_group_permissions` where `perm_name` = ?	and `group_id` = ?";
		$result = $this->mDb->query($query, array($perm, $pGroupId));
		return true;
	}

	/**
	 * Return a list of packages that the user has permission to access
	 */
	function getContentTypeList($pUserId) {
		foreach( $gLibertySystem->mContentTypes as $contentType ) {
			$perm = $contentType["content_type_guid"].'_p_view';
			if (!empty( $perm ) and $gBitUser->hasPermission( $perm )) {
				$contentTypes[$contentType["content_type_guid"]] = $contentType["content_description"];
			}
		}
	}
	function storeRegistrationChoice( $groupList, $flag ) {
		$bindVars = array();
		$bindVars[] = $flag;
		if (is_array( $groupList )) {
			$mid = implode(',',array_fill( 0, count( $groupList ),'?' ) );
			$bindVars = array_merge( $bindVars, $groupList );
		} else {
			$bindVars[] = $groupList;
			$mid = 'like ?';
		}
		$query = "update `".BIT_DB_PREFIX."users_groups` set `is_public`= ? where `group_id` in ($mid)";
		$result = $this->mDb->query( $query, $bindVars );
	}


}

?>
