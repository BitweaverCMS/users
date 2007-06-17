<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/my_groups.php,v 1.14 2007/06/17 12:42:47 squareing Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: my_groups.php,v 1.14 2007/06/17 12:42:47 squareing Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

global $gBitUser, $gBitSystem;

// PERMISSIONS: registered user required
if ( !$gBitUser->isRegistered() ) {
	$gBitSystem->fatalError( tra( "You are not logged in." ));	
}

if( !empty( $_REQUEST["cancel"] ) ) {
	header( 'Location: '.USERS_PKG_URL.'my_groups.php' );
	die;
}

if ( $gBitUser->hasPermission('p_users_create_personal_groups' ) ) {
	if( !empty( $_REQUEST['group_id'] ) ) {
		$allPerms = $gBitUser->getGroupPermissions( array( 'sort_mode' => !empty( $_REQUEST['sort_mode'] ) ? $_REQUEST['sort_mode'] : NULL ));
		// get grouplist separately from the $users stuff to avoid splitting of data due to pagination
		$listHash = array( 'sort_mode' => 'group_name_asc' );
		$groupList = $gBitUser->getAllGroups( $listHash );
	} else {
		// get grouplist separately from the $users stuff to avoid splitting of data due to pagination
		$listHash = array( 'sort_mode' => !empty( $_REQUEST['sort_mode'] ) ? $_REQUEST['sort_mode'] : 'group_name_asc' );
		$groupList = $gBitUser->getAllGroups( $listHash );
	}
	
	// Remember a package limit if it is set.
	$gBitSmarty->assign( 'package',isset( $_REQUEST['package'] ) ? $_REQUEST['package'] : 'all' );

	// Save the join
	if( isset($_REQUEST["save"] ) ) {
		if( empty($_REQUEST["name"] ) ) {
			$_REQUEST["name"] = $_REQUEST["olgroup"];
		}
		if( $gBitUser->storeGroup( $_REQUEST ) ) {
			$successMsg = tra("Group changes were saved sucessfully.");
		} else {
			$errorMsg = $gBitUser->mErrors['groups'];
		}
	// Save a level join
	} elseif (isset($_REQUEST['allper'])) {
		if ($_REQUEST['oper'] == 'assign') {
			$gBitUser->assignLevelPermissions($_REQUEST['group_id'], $_REQUEST['level']);
		} else {
			$gBitUser->removeLevelPermissions($_REQUEST['group_id'], $_REQUEST['level']);
		}
	// Create a level
	} elseif (isset($_REQUEST["createlevel"])) {
		$gBitUser->create_dummy_level($_REQUEST['level']);
	// Update Permissions
	} elseif (isset($_REQUEST['updateperms'])) {
		$updatePerms = $gBitUser->getgroupPermissions( $_REQUEST['group_id'] );
		foreach (array_keys($_REQUEST['level'])as $per) {
			if( $allPerms[$per]['perm_level'] != $_REQUEST['level'][$per] ) {
				// we changed level. perm[] checkbox is not taken into account
				$gBitUser->change_permission_level($per, $_REQUEST['level'][$per]);
			}
			if( isset($_REQUEST['perm'][$per]) && !isset($updatePerms[$per]) ) {
				// we have an unselected perm that is now selected
				$gBitUser->assignPermissionToGroup($per, $_REQUEST['group_id']);
			} elseif( empty($_REQUEST['perm'][$per]) && isset($updatePerms[$per]) ) {
				// we have a selected perm that is now UNselected
				$gBitUser->remove_permission_from_group($per, $_REQUEST['group_id']);
			}
		}
		// let's reload just to be safe.
		$allPerms = $gBitUser->getGroupPermissions();
	// Do some action
	} elseif (isset($_REQUEST["action"])) {
		// Process a form to remove a group
		if( $_REQUEST["action"] == 'delete' ) {
			if( $gBitUser->getDefaultGroup( $_REQUEST['group_id'] ) ) {
				$errorMsg = tra("You cannot remove this group, as it is currently set as your 'Default' group");
			} else {
				$gBitUser->remove_group($_REQUEST['group_id']);
				$successMsg = tra("The group was deleted.");
				unset( $_REQUEST['group_id'] );
			}
		// remove a permission from a group
		} elseif ($_REQUEST["action"] == 'remove') {
			$gBitUser->remove_permission_from_group( $_REQUEST["permission"], $_REQUEST['group_id'] );
			$successMsg = tra("Permission Removed");
			$mid = 'bitpackage:users/my_group_edit.tpl';
		// Create a new group
		} elseif( $_REQUEST["action"] == 'create' ) {
			$gBitSystem->setBrowserTitle( tra('Create New Group') );
			$mid = 'bitpackage:users/my_group_edit.tpl';
		// Assign a permission to a group
		} elseif ($_REQUEST["action"] == 'assign') {
			$gBitUser->assignPermissionToGroup($_REQUEST["perm"], $_REQUEST['group_id']);
			$successMsg = tra("Permission Assigned");
			$mid = 'bitpackage:users/my_group_edit.tpl';
		}
	// Search for users to add
	} elseif (!empty($_REQUEST['submitUserSearch'])) {
		$searchParams = array('find' => $_REQUEST['find']);
		$gBitUser->getList($searchParams);
		$foundUsers = $searchParams['data'];
		$mid = 'bitpackage:users/my_group_edit.tpl';
		$gBitSmarty->assign_by_ref('foundUsers', $foundUsers);
	} elseif (!empty($_REQUEST['assignuser'])) {
		if( !empty($_REQUEST['group_id'] ) ) {
			if ($_REQUEST['group_id'] != -1 && $groupList['data'][$_REQUEST['group_id']]['user_id'] == $gBitUser->mUserId) {
				$gBitUser->addUserToGroup( $_REQUEST['assignuser'], $_REQUEST['group_id'] );
			}
			else {
				$errorMsg = tra("You can not assign users to this group.");
			}
		}
		$mid = 'bitpackage:users/my_group_edit.tpl';
	}

	// get pagination url
	// get grouplist separately from the $users stuff to avoid splitting of data due to pagination
	$listHash = array( 'sort_mode' => 'group_name_asc' );
	$groupList = $gBitUser->getAllUserGroups();
	
	if( !empty( $_REQUEST['group_id'] ) ) {
		// we don't want our own group listed when editing
		if( !empty( $groupList[$_REQUEST['group_id']] ) ) {
			unset( $groupList[$_REQUEST['group_id']] );
		}
		$groupInfo = $gBitUser->getGroupInfo( $_REQUEST['group_id'] );
		$groupUsers = $gBitUser->get_group_users( $_REQUEST['group_id'] );
		$gBitSmarty->assign_by_ref('groupUsers', $groupUsers);
		$gBitSmarty->assign_by_ref('groupInfo', $groupInfo);
		$gBitSmarty->assign_by_ref( 'allPerms', $allPerms );
		$gBitSystem->setBrowserTitle( 'Admininster Group: '.$groupInfo['group_name'].' '.(isset( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : '') );
		$mid = 'bitpackage:users/my_group_edit.tpl';
	} 

	$gBitSmarty->assign('groups', $groupList);
	//	$gBitSmarty->assign( (!empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'edit').'TabSelect', 'tdefault' );
}

/* join or leave a public group. */
if ( ( !empty( $_REQUEST['add_public_group'] ) || !empty( $_REQUEST['remove_public_group'] ) ) && !empty( $_REQUEST['public_group_id'] ) ) {
	$groupInfo = $gBitUser->getGroupInfo( $_REQUEST['public_group_id'] );
	if ( empty($groupInfo) || $groupInfo['is_public'] != 'y' ) {
		if (empty($_REQUEST['add_public_group'])) {
			$errorMsg[] = tra("You can't join this group.");
		}
		else {
			$errorMsg[] = tra("You can't leave this group.");
		}			
	} elseif ( !empty( $_REQUEST['add_public_group'] ) ) {
		$gBitUser->addUserToGroup( $gBitUser->mUserId, $_REQUEST['public_group_id'] );
	} elseif ( !empty( $_REQUEST['remove_public_group'] ) ) {
		$gBitUser->removeUserFromGroup( $gBitUser->mUserId, $_REQUEST['public_group_id'] );
	}
	$gBitUser->loadPermissions();
	if ( !empty( $_REQUEST['add_public_group'] ) && !empty( $groupInfo['after_registration_page'] ) ) {
		if ( $gBitUser->verifyId( $groupInfo['after_registration_page'] ) ) {
			$url = BIT_ROOT_URL."index.php?content_id=".$groupInfo['after_registration_page'];
		} elseif( strpos( $groupInfo['after_registration_page'], '/' ) === FALSE ) {
			$url = BitPage::getDisplayUrl( $groupInfo['after_registration_page'] );
		} else {
			$url = $groupInfo['after_registration_page'];
		}
		header( 'Location: '.$url );
		exit;
	}
}

/* Load up public groups and check if the user can join or leave them */
$systemGroups = $gBitUser->getGroups( $gBitUser->mUserId, TRUE );
$gBitSmarty->assign_by_ref( 'systemGroups', $systemGroups);
$listHash = array( 'is_public'=>'y', 'sort_mode'=>array('is_default_asc' , 'group_desc_asc') );
$publicGroups = $gBitUser->getAllGroups( $listHash );	
if ( $publicGroups['cant'] ) {
	foreach ( $systemGroups as $groupId=>$groupInfo ) {
		foreach ( $publicGroups['data'] as $key=>$publicGroup) {
			if ( $publicGroups['data'][$key]['group_id'] == $groupId) {
				if ($publicGroups['data'][$key]['is_default'] != 'y' ) {
					$systemGroups[$groupId]['public'] = 'y';
					$canRemovePublic = 'y';
				}
				$publicGroups['data'][$key]['used'] = 'y';
				break;
			}
		}
	}
	foreach ( $publicGroups['data'] as $groupInfo) {
		if ( empty($groupInfo['used'] ) && $groupInfo['is_default'] != 'y' ) {
			$gBitSmarty->assign( 'canAddPublic' , 'y');
			break;
		}
	}
	$gBitSmarty->assign_by_ref( 'publicGroups', $publicGroups['data'] );
	if (isset($canRemovePublic)) {
		$gBitSmarty->assign( 'canRemovePublic' , 'y');
	}
}

// Remember error and success messages.	
if (!empty($errorMsg)) {
	$gBitSmarty->assign('errorMsg',$errorMsg);
}
if (!empty($successMsg)) {
	$gBitSmarty->assign('successMsg',$successMsg);
}

// Default the template if we aren't doing an edit.
if (empty($mid)) {
	$mid = 'bitpackage:users/my_groups_list.tpl';
}

// Display the template for group administration
$gBitSystem->display( $mid );
?>
