<?php
/**
 * my groups
 *
 * @copyright (c) 2004-15 bitweaver.org
 *
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/includes/setup_inc.php' );

global $gBitUser, $gBitSystem;

// PERMISSIONS: registered user required
if ( !$gBitUser->isRegistered() ) {
	$gBitSystem->fatalError( tra( "You are not logged in." ));
}

if( !empty( $_REQUEST["cancel"] ) ) {
	bit_redirect( USERS_PKG_URL.'my_groups.php' );
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
	// Update Permissions
	} elseif (isset($_REQUEST['updateperms'])) {
		$listHash = array( 'group_id' => $_REQUEST['group_id'] );
		$updatePerms = $gBitUser->getgroupPermissions( $listHash );
		foreach (array_keys($_REQUEST['perm']) as $per) {
			if( isset($_REQUEST['perm'][$per]) && !isset($updatePerms[$per]) ) {
				// we have an unselected perm that is now selected
				$gBitUser->assignPermissionToGroup($per, $_REQUEST['group_id']);
			} elseif( empty($_REQUEST['perm'][$per]) && isset($updatePerms[$per]) ) {
				// we have a selected perm that is now UNselected
				$gBitUser->removePermissionFromGroup($per, $_REQUEST['group_id']);
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
				$gBitUser->expungeGroup( $_REQUEST['group_id'] );
				$successMsg = tra("The group was deleted.");
				unset( $_REQUEST['group_id'] );
			}
		// remove a permission from a group
		} elseif ($_REQUEST["action"] == 'remove') {
			$gBitUser->removePermissionFromGroup( $_REQUEST["permission"], $_REQUEST['group_id'] );
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
		$gBitSmarty->assignByRef('foundUsers', $foundUsers);
	} elseif (!empty($_REQUEST['assignuser'])) {
		if( !empty($_REQUEST['group_id'] ) ) {
			if ($_REQUEST['group_id'] != -1 && $groupList[$_REQUEST['group_id']]['user_id'] == $gBitUser->mUserId) {
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
		$groupUsers = $gBitUser->getGroupUsers( $_REQUEST['group_id'] );
		$gBitSmarty->assignByRef('groupUsers', $groupUsers);
		$gBitSmarty->assignByRef('groupInfo', $groupInfo);
		$gBitSmarty->assignByRef( 'allPerms', $allPerms );
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
			$url = BitPage::getDisplayUrlFromHash( $groupInfo['after_registration_page'] );
		} else {
			$url = $groupInfo['after_registration_page'];
		}
		bit_redirect( $url );
	}
}

/* Load up public groups and check if the user can join or leave them */
$systemGroups = $gBitUser->getGroups( $gBitUser->mUserId, TRUE );
$gBitSmarty->assignByRef( 'systemGroups', $systemGroups);
$listHash = array(
	'is_public'=>'y',
	'sort_mode' => array( 'is_default_asc', 'group_desc_asc' ),
);
$publicGroups = $gBitUser->getAllGroups( $listHash );
if( count( $publicGroups )) {
	foreach ( $systemGroups as $groupId=>$groupInfo ) {
		foreach ( $publicGroups as $key=>$publicGroup) {
			if ( $publicGroups[$key]['group_id'] == $groupId) {
				if ($publicGroups[$key]['is_default'] != 'y' ) {
					$systemGroups[$groupId]['public'] = 'y';
					$canRemovePublic = 'y';
				}
				$publicGroups[$key]['used'] = 'y';
				break;
			}
		}
	}
	foreach ( $publicGroups as $groupInfo) {
		if ( empty($groupInfo['used'] ) && $groupInfo['is_default'] != 'y' ) {
			$gBitSmarty->assign( 'canAddPublic' , 'y');
			break;
		}
	}
	$gBitSmarty->assignByRef( 'publicGroups', $publicGroups );
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
$gBitSystem->display( $mid , NULL, array( 'display_mode' => 'display' ));
?>
