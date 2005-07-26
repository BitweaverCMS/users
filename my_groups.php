<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/my_groups.php,v 1.1.1.1.2.2 2005/07/26 15:50:30 drewslater Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: my_groups.php,v 1.1.1.1.2.2 2005/07/26 15:50:30 drewslater Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

// PERMISSIONS: registered user required
if( !$gBitUser->isRegistered() ) {
	$gBitSystem->fatalError( 'You must be logged in to edit your groups.' );
}

$successMsg = NULL;
$errorMsg = NULL;

// We need to scan for defaults
global $gBitInstaller;
$gBitInstaller = &$gBitSystem;
$gBitSystem->verifyInstalledPackages();

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
	$mid = 'bitpackage:users/my_group_edit.tpl';
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
			$levels = $gBitUser->get_permission_levels();
			sort($levels);
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
$gBitSmarty->assign('errorMsg',$errorMsg);
$gBitSmarty->assign( (!empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'edit').'TabSelect', 'tdefault' );


// Display the template for group administration
$gBitSystem->display( $mid );
?>
