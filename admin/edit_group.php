<?php
// $Header: /cvsroot/bitweaver/_bit_users/admin/edit_group.php,v 1.21 2006/12/23 09:29:05 squareing Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// Initialization
require_once( '../../bit_setup_inc.php' );

// PERMISSIONS: NEEDS admin
$gBitSystem->verifyPermission( 'p_users_admin' );

$successMsg = NULL;
$errorMsg = NULL;

$gBitUser->getUnassignedPerms();

$gBitSmarty->assign( 'loadAjax', 'prototype' );
if ( !empty( $gBodyOnload ) ) {
	array_push( $gBodyOnload, 'injectSuggestBehavior();' );
} else {
	$gBodyOnload = 'injectSuggestBehavior();';
}

if( count( $_GET ) > 2 || count( $_POST ) > 2 ) {
	$gBitUser->verifyTicket();
}

if( !empty( $_REQUEST['group_id'] ) ) {
	$allPerms = $gBitUser->getGroupPermissions( NULL, NULL, NULL, !empty( $_REQUEST['sort_mode'] ) ? $_REQUEST['sort_mode'] : NULL );

	// get all the included permissions as well
	$includes = array();
	$gBitUser->getIncludedGroups( $_REQUEST['group_id'], $includes );
	foreach( $includes as $gid => $name ) {
		foreach( $gBitUser->getGroupPermissions( $gid ) as $p ) {
			$incPerms[$p['perm_name']]['group_id'] = $gid;
			$incPerms[$p['perm_name']]['group_name'] = $name;
		}
	}
}

$gBitSmarty->assign( 'package',isset( $_REQUEST['package'] ) ? $_REQUEST['package'] : 'all' );

if( !empty( $_REQUEST["cancel"] ) ) {
	header( 'Location: '.USERS_PKG_URL.'admin/edit_group.php' );
	die;
} elseif( isset( $_REQUEST["batch_assign"] ) ) {
	$groupInfo = $gBitUser->getGroupInfo( $_REQUEST['batch_assign'] );
	if( isset( $_REQUEST["confirm"] ) ) {
		$gBitUser->batchAssignUsersToGroup( $_REQUEST['batch_assign'] );
	} else {
		$gBitSystem->setBrowserTitle( tra( 'Confirm Batch Group Assignment' ) );
		$formHash['batch_assign'] = $_REQUEST["batch_assign"];
		$msgHash = array(
			'label' => tra( 'Batch Assign Users to Group' ),
			'confirm_item' => $groupInfo['group_name'],
			'warning' => tra( 'This will assign every user on the site to the group' ).' <strong>'.$groupInfo['group_name'].'</strong>',
		);
		$gBitSystem->confirmDialog( $formHash,$msgHash );
	}

} elseif( isset($_REQUEST["members"] ) ) {
	$groupInfo = $gBitUser->getGroupInfo( $_REQUEST["members"] );
	$gBitSmarty->assign_by_ref( 'groupInfo', $groupInfo );
	$groupMembers = $gBitUser->get_group_users( $_REQUEST["members"] );
	$gBitSmarty->assign_by_ref( 'groupMembers', $groupMembers );
	$mid = "bitpackage:users/group_list_members.tpl";
	$gBitSystem->setBrowserTitle( tra( 'Group Members' ).': '.$groupInfo['group_name'] );
} elseif( isset($_REQUEST["save"] ) ) {
	if( empty($_REQUEST["name"] ) ) {
		$_REQUEST["name"] = $_REQUEST["olgroup"];
	}
	// modification

	$_REQUEST['user_id'] = ROOT_USER_ID;
	if( $gBitUser->storeGroup( $_REQUEST ) ) {
		$successMsg = "Group changes were saved sucessfully.";
	} else {
		$errorMsg = $gBitUser->mErrors['groups'];
	}
	if( !empty( $_REQUEST['default_home_group'] ) ) {
		$gBitSystem->storeConfig( 'default_home_group', $_REQUEST['group_id'], USERS_PKG_NAME );
	} elseif( $_REQUEST['group_id'] == $gBitSystem->getConfig( 'default_home_group' ) ) {
		// the default home group was unchecked.
		$gBitSystem->storeConfig( 'default_home_group', NULL, USERS_PKG_NAME );
	}

//	$mid = 'bitpackage:users/admin_groups_list.tpl';
} elseif (isset($_REQUEST['allper'])) {
	if ($_REQUEST['oper'] == 'assign') {
		$gBitUser->assign_level_permissions($_REQUEST['group_id'], $_REQUEST['perm_level']);
	} else {
		$gBitUser->remove_level_permissions($_REQUEST['group_id'], $_REQUEST['perm_level']);
	}
} elseif (isset($_REQUEST["createlevel"])) {
	$gBitUser->create_dummy_level($_REQUEST['perm_level']);
} elseif (isset($_REQUEST['updateperms'])) {
	$updatePerms = $gBitUser->getgroupPermissions( $_REQUEST['group_id'] );
	if (!empty($_REQUEST['perm_level'])) {
		foreach (array_keys($_REQUEST['perm_level'])as $per) {
			if( $allPerms[$per]['perm_level'] != $_REQUEST['perm_level'][$per] ) {
				// we changed level. perm[] checkbox is not taken into account
				$gBitUser->change_permission_level($per, $_REQUEST['perm_level'][$per]);
			}
			if( isset($_REQUEST['perm'][$per]) && !isset($updatePerms[$per]) ) {
				// we have an unselected perm that is now selected
				// remove it from any inherited group ( we need to shuffle around perms in the users groups as well ) - xing
				if( !empty( $incPerms[$per] ) ) {
					$gBitUser->remove_permission_from_group( $per, $incPerms[$per]['group_id'] );
				}
				$gBitUser->assignPermissionToGroup($per, $_REQUEST['group_id']);
			} elseif( empty($_REQUEST['perm'][$per]) && isset($updatePerms[$per]) ) {
				// we have a selected perm that is now UNselected
				$gBitUser->remove_permission_from_group($per, $_REQUEST['group_id']);
			}
		}
	}
	// let's reload just to be safe.
	$allPerms = $gBitUser->getGroupPermissions();
} elseif (isset($_REQUEST["action"])) {
	$formHash['action'] = $_REQUEST['action'];
// Process a form to remove a group
	if( $_REQUEST["action"] == 'delete' ) {
		$gBitUser->verifyTicket();
		$formHash['group_id'] = $_REQUEST['group_id'];
		$groupInfo = $gBitUser->getGroupInfo( $_REQUEST['group_id'] );
		if( isset( $_REQUEST["confirm"] ) ) {
			if( $_REQUEST['group_id'] == $gBitSystem->getConfig( 'default_home_group' ) ) {
				$gBitSystem->storeConfig( 'default_home_group', NULL, USERS_PKG_NAME );
			}
			$gBitUser->remove_group($_REQUEST['group_id']);
			$successMsg = "The group ".$groupInfo['group_name']." was deleted.";
			unset( $_REQUEST['group_id'] );
		} else {
			$gBitSystem->setBrowserTitle( tra('Delete group') );
			$msgHash = array(
				'confirm_item' => tra( 'Are you sure you want to remove the group?' ),
				'warning' => tra( 'This will permentally delete the group' )." <strong>$groupInfo[group_name]</strong>",
			);
			$gBitSystem->confirmDialog( $formHash,$msgHash );
		}
	} elseif ($_REQUEST["action"] == 'remove') {
		$gBitUser->remove_permission_from_group( $_REQUEST["permission"], $_REQUEST['group_id'] );
		$successMsg = 'The permission '.$_REQUEST['permission'].' was removed successflly. <a href="'.USERS_PKG_URL.'admin/edit_group.php?action=assign&amp;perm='.$_REQUEST['permission'].'&amp;group_id='.$_REQUEST['group_id'].'&amp;pacakge='.$_REQUEST['package'].'">Undo last action.</a>';
	} elseif( $_REQUEST["action"] == 'create' ) {
		$mid = 'bitpackage:users/admin_group_edit.tpl';
		$gBitSystem->setBrowserTitle( tra( 'Create New Group' ) );
	} elseif ($_REQUEST["action"] == 'assign') {
		$gBitUser->assignPermissionToGroup($_REQUEST["perm"], $_REQUEST['group_id']);
	}
}

if( !empty( $_REQUEST['group_id'] ) || (!empty( $_REQUEST["action"] ) && $_REQUEST["action"] == 'create' ) ) {
	$permPackages = $gBitUser->getPermissionPackages();
	$gBitSmarty->assign_by_ref( 'permPackages', $permPackages );

	// get grouplist separately from the $users stuff to avoid splitting of data due to pagination
	$listHash = array( 'sort_mode' => 'group_name_asc' );

/*
	// get content and pass it on to the template
	include_once( LIBERTY_PKG_PATH.'get_content_list_inc.php' );
	foreach( $contentList['data'] as $cItem ) {
		$cList[$contentTypes[$cItem['content_type_guid']]][$cItem['content_id']] = $cItem['title'].' [id: '.$cItem['content_id'].']';
	}
	$gBitSmarty->assign( 'contentList', $cList );
	$gBitSmarty->assign( 'contentSelect', $contentSelect );
*/
	$contentTypes = array( '' => 'All Content' );
	foreach( $gLibertySystem->mContentTypes as $cType ) {
		$contentTypes[$cType['content_type_guid']] = $cType['content_description'];
	}
	$gBitSmarty->assign( 'contentTypes', $contentTypes );
} else {
	// get grouplist separately from the $users stuff to avoid splitting of data due to pagination
	$listHash = array( 'sort_mode' => !empty( $_REQUEST['sort_mode'] ) ? $_REQUEST['sort_mode'] : 'group_name_asc' );
}
$groupList = $gBitUser->getAllGroups( $listHash );

$inc = array();
if( empty( $mid ) ) {
	if( !empty( $_REQUEST['group_id'] ) ) {
		$groupInfo = $gBitUser->getGroupInfo( $_REQUEST['group_id'] );

		// refresh inherited permissions list
		$incPerms = array();
		if( !empty( $includes ) ) {
			foreach( $includes as $gid => $name ) {
				foreach( $gBitUser->getGroupPermissions( $gid ) as $p ) {
					$incPerms[$p['perm_name']]['group_id'] = $gid;
					$incPerms[$p['perm_name']]['group_name'] = $name;
				}
			}
		}
		$gBitSmarty->assign( 'incPerms', $incPerms );

		$rs = array();
		$gBitUser->getIncludedGroups( $_REQUEST['group_id'], $rs );
		foreach( array_keys( $groupList["data"] ) as $groupId ) {
			$groupList["data"][$groupId]['included'] = isset( $rs[$groupId] ) ? 'y' : 'n';
		}
		$levels = $gBitUser->getPermissionLevels();
		$gBitSmarty->assign('levels', $levels);

		$defaultGroupId = $gBitSystem->getConfig( 'default_home_group' );
		$gBitSmarty->assign_by_ref('defaultGroupId', $defaultGroupId );
		$gBitSmarty->assign_by_ref('groupInfo', $groupInfo);
		$gBitSmarty->assign_by_ref( 'allPerms', $allPerms );

		$gBitSystem->setBrowserTitle( tra( 'Admininster Group' ).': '.$groupInfo['group_name'].' '.(isset( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : '') );
		$mid = 'bitpackage:users/admin_group_edit.tpl';
	} else {
		$gBitSystem->setBrowserTitle( tra( 'Admin List Groups' ) );
		$_REQUEST['group_id'] = 0;
		$mid = 'bitpackage:users/admin_groups_list.tpl';
	}
}

// $gBitSmarty->assign( 'loadDebug', TRUE ); // Ajax / prototype debug

$gBitSmarty->assign('groupList', $groupList['data']);
$gBitSmarty->assign('successMsg',$successMsg);
$gBitSmarty->assign('errorMsg',$errorMsg);

// Display the template for group administration
$gBitSystem->display( $mid );
?>
