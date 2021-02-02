<?php
// $Header$
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See below for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.
// Initialization
require_once( '../../kernel/setup_inc.php' );

// PERMISSIONS: NEEDS admin
$gBitSystem->verifyPermission( 'p_users_admin' );

$successMsg = NULL;
$errorMsg = NULL;

if( count( $_GET ) > 2 || count( $_POST ) > 2 ) {
	$gBitUser->verifyTicket();
}

if( !empty( $_REQUEST['group_id'] ) ) {
	$permListHash = array(
		'sort_mode' => !empty( $_REQUEST['sort_mode'] ) ? $_REQUEST['sort_mode'] : NULL,
		'package' => !empty( $_REQUEST['package'] ) ? $_REQUEST['package'] : NULL,
	);
	$allPerms = $gBitUser->getGroupPermissions( $permListHash );
}

if( !empty( $_REQUEST["cancel"] ) ) {
	bit_redirect( USERS_PKG_URL.'admin/edit_group.php' );
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
	$gBitSmarty->assignByRef( 'groupInfo', $groupInfo );
	$groupMembers = $gBitUser->getGroupUsers( $_REQUEST["members"] );
	$gBitSmarty->assignByRef( 'groupMembers', $groupMembers );
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
} elseif( isset( $_REQUEST['delete'] ) ) {
	// Process a form to remove a group
	$formHash['group_id'] = $_REQUEST['group_id'];
	$formHash['delete'] = 1;
	$groupInfo = $gBitUser->getGroupInfo( $_REQUEST['group_id'] );
	if( isset( $_REQUEST["confirm"] ) ) {
		$gBitUser->verifyTicket();
		if( $_REQUEST['group_id'] == $gBitSystem->getConfig( 'default_home_group' ) ) {
			$gBitSystem->storeConfig( 'default_home_group', NULL, USERS_PKG_NAME );
		}
		$gBitUser->expungeGroup( $_REQUEST['group_id'] );
		$successMsg = "The group ".$groupInfo['group_name']." was deleted.";
		unset( $_REQUEST['group_id'] );
	} else {
		$gBitSystem->setBrowserTitle( tra('Delete group') );
		$msgHash = array(
			'confirm_item' => tra( 'Are you sure you want to permantly remove the group' )." <strong>$groupInfo[group_name]</strong>".'?',
			'warning' => tra( 'This cannot be undone.' ),
		);
		$gBitSystem->confirmDialog( $formHash,$msgHash );
	}
//	$mid = 'bitpackage:users/admin_groups_list.tpl';
} elseif( isset( $_REQUEST['updateperms'] )) {
	foreach( array_keys( $allPerms ) as $perm ) {
		if( !empty( $_REQUEST['perm'][$perm] )) {
			$gBitUser->assignPermissionToGroup( $perm, $_REQUEST['group_id'] );
		} else {
			// we have a selected perm that is now UNselected
			$gBitUser->removePermissionFromGroup( $perm, $_REQUEST['group_id'] );
		}
	}
	// let's reload just to be safe.
	$allPerms = $gBitUser->getGroupPermissions( $permListHash );
} elseif( isset( $_REQUEST["action"] )) {
	$formHash['action'] = $_REQUEST['action'];
	if ($_REQUEST["action"] == 'remove') {
		$gBitUser->removePermissionFromGroup( $_REQUEST["permission"], $_REQUEST['group_id'] );
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
	$gBitSmarty->assignByRef( 'permPackages', $permPackages );

	// get grouplist separately from the $users stuff to avoid splitting of data due to pagination
	$listHash = array( 'sort_mode' => 'group_name_asc' );

/*
	// get content and pass it on to the template
	include_once( LIBERTY_PKG_INCLUDE_PATH.'get_content_list_inc.php' );
	foreach( $contentList as $cItem ) {
		$cList[$contentTypes[$cItem['content_type_guid']]][$cItem['content_id']] = $cItem['title'].' [id: '.$cItem['content_id'].']';
	}
	$gBitSmarty->assign( 'contentList', $cList );
	$gBitSmarty->assign( 'contentSelect', $contentSelect );
*/
	$contentTypes = array( '' => tra( 'All Content' ) );
	foreach( $gLibertySystem->mContentTypes as $cType ) {
		$contentTypes[$cType['content_type_guid']] = $gLibertySystem->getContentTypeName( $cType['content_type_guid'] );
	}
	$gBitSmarty->assign( 'contentTypes', $contentTypes );
} else {
	// get grouplist separately from the $users stuff to avoid splitting of data due to pagination
	$listHash = array( 'sort_mode' => !empty( $_REQUEST['sort_mode'] ) ? $_REQUEST['sort_mode'] : 'group_name_asc' );
}
$gBitSmarty->assign('groupList', $gBitUser->getAllGroups( $listHash ));

$inc = array();
if( empty( $mid ) ) {
	if( !empty( $_REQUEST['group_id'] ) ) {
		$groupInfo = $gBitUser->getGroupInfo( $_REQUEST['group_id'] );

		$defaultGroupId = $gBitSystem->getConfig( 'default_home_group' );
		$gBitSmarty->assignByRef( 'defaultGroupId', $defaultGroupId );
		$gBitSmarty->assignByRef( 'groupInfo', $groupInfo );
		$gBitSmarty->assignByRef( 'allPerms', $allPerms );

		$gBitSystem->setBrowserTitle( tra( 'Admininster Group' ).': '.$groupInfo['group_name'] );
		$mid = 'bitpackage:users/admin_group_edit.tpl';
	} else {
		$gBitSystem->setBrowserTitle( tra( 'Admin List Groups' ) );
		$_REQUEST['group_id'] = 0;
		$mid = 'bitpackage:users/admin_groups_list.tpl';
	}
}

$gBitSmarty->assign('successMsg',$successMsg);
$gBitSmarty->assign('errorMsg',$errorMsg);

// Display the template for group administration
$gBitSystem->display( $mid , NULL, array( 'display_mode' => 'edit' ));
?>
