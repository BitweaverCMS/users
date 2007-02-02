<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/my_groups.php,v 1.10 2007/02/02 20:51:17 nickpalmer Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: my_groups.php,v 1.10 2007/02/02 20:51:17 nickpalmer Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

// PERMISSIONS: registered user required
if ( !$gBitUser->isRegistered() ) {
	$gBitSmarty->assign('msg', tra("You are not logged in"));
	$gBitSystem->display( 'error.tpl' );
	die;
}

$successMsg = NULL;
$errorMsg = NULL;
if (! empty( $_REQUEST['errorMsg'] ) ) {
	$errorMsg[] = $_REQUEST['errorMsg'];
}

// We need to scan for defaults
global $gBitInstaller;
$gBitInstaller = &$gBitSystem;
$gBitSystem->verifyInstalledPackages();

$mid = 'bitpackage:users/my_groups_list.tpl';

if ( $gBitUser->hasPermission('p_users_create_personal_groups' ) ) {
	if( !empty( $_REQUEST['group_id'] ) ) {
		$allPerms = $gBitUser->getGroupPermissions( NULL, NULL, NULL, !empty( $_REQUEST['sort_mode'] ) ? $_REQUEST['sort_mode'] : NULL );
		// get grouplist separately from the $users stuff to avoid splitting of data due to pagination
		$listHash = array( 'sort_mode' => 'group_name_asc' );
		$groupList = $gBitUser->getAllGroups( $listHash );
	} else {
		// get grouplist separately from the $users stuff to avoid splitting of data due to pagination
		$listHash = array( 'sort_mode' => !empty( $_REQUEST['sort_mode'] ) ? $_REQUEST['sort_mode'] : 'group_name_asc' );
		$groupList = $gBitUser->getAllGroups( $listHash );
	}

	$gBitSmarty->assign( 'package',isset( $_REQUEST['package'] ) ? $_REQUEST['package'] : 'all' );

	if( !empty( $_REQUEST["cancel"] ) ) {
		header( 'Location: '.USERS_PKG_URL.'my_groups.php' );
		die;
	} elseif( isset($_REQUEST["save"] ) ) {
		if( empty($_REQUEST["name"] ) ) {
			$_REQUEST["name"] = $_REQUEST["olgroup"];
		}
		if( $gBitUser->storeGroup( $_REQUEST ) ) {
			$successMsg = "Group changes were saved sucessfully.";
		} else {
			$errorMsg = $gBitUser->mErrors['groups'];
		}
	} elseif (isset($_REQUEST['allper'])) {
		if ($_REQUEST['oper'] == 'assign') {
			$gBitUser->assign_level_permissions($_REQUEST['group_id'], $_REQUEST['level']);
		} else {
			$gBitUser->remove_level_permissions($_REQUEST['group_id'], $_REQUEST['level']);
		}
	} elseif (isset($_REQUEST["createlevel"])) {
		$gBitUser->create_dummy_level($_REQUEST['level']);
	} elseif (isset($_REQUEST['updateperms'])) {

		$updatePerms = $gBitUser->getgroupPermissions( $_REQUEST['group_id'] );
		foreach (array_keys($_REQUEST['level'])as $per) {
			if( $allPerms[$per]['level'] != $_REQUEST['level'][$per] ) {
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
	} elseif (isset($_REQUEST["action"])) {
	// Process a form to remove a group
		if( $_REQUEST["action"] == 'delete' ) {
			if( $gBitUser->getDefaultGroup( $_REQUEST['group_id'] ) ) {
				$errorMsg = "You cannot remove this Group, as it is currently set as your 'Default' group";
			} else {
				$gBitUser->remove_group($_REQUEST['group_id']);
				$successMsg = "The group ".$_REQUEST['group_id']." was deleted.";
				unset( $_REQUEST['group_id'] );
			}
		} elseif ($_REQUEST["action"] == 'remove') {
			$gBitUser->remove_permission_from_group( $_REQUEST["permission"], $_REQUEST['group_id'] );
			$successMsg = "Permission Removed";
		} elseif( $_REQUEST["action"] == 'create' ) {
			$mid = 'bitpackage:users/my_group_edit.tpl';
			$gBitSystem->setBrowserTitle( 'Create New Group' );
		} elseif ($_REQUEST["action"] == 'assign') {
			$gBitUser->assignPermissionToGroup($_REQUEST["perm"], $_REQUEST['group_id']);
		}
	} elseif (!empty($_REQUEST['submitUserSearch'])) {
		$searchParams = array('find' => $_REQUEST['find']);
		$gBitUser->getList($searchParams);
		$foundUsers = $searchParams['data'];
		$gBitSmarty->assign_by_ref('foundUsers', $foundUsers);
	} elseif (!empty($_REQUEST['assignuser'])) {
		if( !empty($_REQUEST['group_id'] ) ) {
			// need some security here people!
			$gBitUser->addUserToGroup( $_REQUEST['assignuser'], $_REQUEST['group_id'] );
		}
	//	$mid = 'bitpackage:users/my_group_edit.tpl';
	}

	// get pagination url
	// get grouplist separately from the $users stuff to avoid splitting of data due to pagination
	$listHash = array( 'sort_mode' => 'group_name_asc' );
	$groupList = $gBitUser->getAllUserGroups();

	if( empty( $groupList ) ) {
		$mid = 'bitpackage:users/my_groups_list.tpl';
	} else {
		$inc = array();
		if( empty( $mid ) ) {
			if( !empty( $_REQUEST['group_id'] ) ) {
				// we don't want our own group listed when editing
				if( !empty( $groupList[$_REQUEST['group_id']] ) ) {
					unset( $groupList[$_REQUEST['group_id']] );
				}
				$groupInfo = $gBitUser->getGroupInfo( $_REQUEST['group_id'] );
				$rs = array();
				$gBitUser->getIncludedGroups( $_REQUEST['group_id'], $rs );
				foreach( array_keys( $groupList ) as $groupId ) {
					$groupList["data"][$groupId]['included'] = isset( $rs[$groupId] ) ? 'y' : 'n';
				}
				$levels = $gBitUser->getPermissionLevels();
				$gBitSmarty->assign('levels', $levels);
				$groupUsers = $gBitUser->get_group_users( $_REQUEST['group_id'] );
				$gBitSmarty->assign_by_ref('groupUsers', $groupUsers);
				$gBitSmarty->assign_by_ref('groupInfo', $groupInfo);
				$gBitSmarty->assign_by_ref( 'allPerms', $allPerms );
				$gBitSystem->setBrowserTitle( 'Admininster Group: '.$groupInfo['group_name'].' '.(isset( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : '') );
				$mid = 'bitpackage:users/my_group_edit.tpl';
			} else {
				$gBitSystem->setBrowserTitle( 'Edit User Groups' );
				$_REQUEST['group_id'] = 0;
				$mid = 'bitpackage:users/my_groups_list.tpl';
			}
		}
		$gBitSmarty->assign('groups', $groupList);
	}
	$gBitSmarty->assign('successMsg',$successMsg);
	$gBitSmarty->assign( (!empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'edit').'TabSelect', 'tdefault' );
}

if ( ( !empty( $_REQUEST['add_public_group'] ) || !empty( $_REQUEST['remove_public_group'] ) ) && !empty( $_REQUEST['public_group_id'] ) ) {
	$groupInfo = $gBitUser->getGroupInfo( $_REQUEST['public_group_id'] );
	if ( empty($groupInfo) || $groupInfo['is_public'] != 'y' ) {
		$errorMsg[] = "You can't use this group";
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
	
$gBitSmarty->assign('errorMsg',$errorMsg);

// Display the template for group administration
$gBitSystem->display( $mid );
?>
