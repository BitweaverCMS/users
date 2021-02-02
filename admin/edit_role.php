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

if( !empty( $_REQUEST['role_id'] ) ) {
	$permListHash = array(
		'sort_mode' => !empty( $_REQUEST['sort_mode'] ) ? $_REQUEST['sort_mode'] : NULL,
		'package' => !empty( $_REQUEST['package'] ) ? $_REQUEST['package'] : NULL,
	);
	$allPerms = $gBitUser->getRolePermissions( $permListHash );
}

if( !empty( $_REQUEST["cancel"] ) ) {
	bit_redirect( USERS_PKG_URL.'admin/edit_role.php' );
} elseif( isset( $_REQUEST["batch_assign"] ) ) {
	$roleInfo = $gBitUser->getRoleInfo( $_REQUEST['batch_assign'] );
	if( isset( $_REQUEST["confirm"] ) ) {
		$gBitUser->batchAssignUsersToRole( $_REQUEST['batch_assign'] );
	} else {
		$gBitSystem->setBrowserTitle( tra( 'Confirm Batch Role Assignment' ) );
		$formHash['batch_assign'] = $_REQUEST["batch_assign"];
		$msgHash = array(
			'label' => tra( 'Batch Assign Users to Role' ),
			'confirm_item' => $roleInfo['role_name'],
			'warning' => tra( 'This will assign every user on the site to the role' ).' <strong>'.$roleInfo['role_name'].'</strong>',
		);
		$gBitSystem->confirmDialog( $formHash,$msgHash );
	}
} elseif( isset($_REQUEST["members"] ) ) {
	$roleInfo = $gBitUser->getRoleInfo( $_REQUEST["members"] );
	$gBitSmarty->assignByRef( 'roleInfo', $roleInfo );
	$roleMembers = $gBitUser->getRoleUsers( $_REQUEST["members"] );
	$gBitSmarty->assignByRef( 'roleMembers', $roleMembers );
	$mid = "bitpackage:users/role_list_members.tpl";
	$gBitSystem->setBrowserTitle( tra( 'Role Members' ).': '.$roleInfo['role_name'] );
} elseif( isset($_REQUEST["save"] ) ) {
	if( empty($_REQUEST["name"] ) ) {
		$_REQUEST["name"] = $_REQUEST["olrole"];
	}
	// modification

	$_REQUEST['user_id'] = ROOT_USER_ID;
	if( $gBitUser->storeRole( $_REQUEST ) ) {
		$successMsg = "Role changes were saved sucessfully.";
	} else {
		$errorMsg = $gBitUser->mErrors['roles'];
	}
	if( !empty( $_REQUEST['default_home_role'] ) ) {
		$gBitSystem->storeConfig( 'default_home_role', $_REQUEST['role_id'], USERS_PKG_NAME );
	} elseif( $_REQUEST['role_id'] == $gBitSystem->getConfig( 'default_home_role' ) ) {
		// the default home role was unchecked.
		$gBitSystem->storeConfig( 'default_home_role', NULL, USERS_PKG_NAME );
	}

//	$mid = 'bitpackage:users/admin_troles_list.tpl';
} elseif( isset( $_REQUEST['updateperms'] )) {
	foreach( array_keys( $allPerms ) as $perm ) {
		if( !empty( $_REQUEST['perm'][$perm] )) {
			$gBitUser->assignPermissionToRole( $perm, $_REQUEST['role_id'] );
		} else {
			// we have a selected perm that is now UNselected
			$gBitUser->removePermissionFromRole( $perm, $_REQUEST['role_id'] );
		}
	}
	// let's reload just to be safe.
	$allPerms = $gBitUser->getRolePermissions( $permListHash );
} elseif( isset( $_REQUEST["action"] )) {
	$formHash['action'] = $_REQUEST['action'];
	// Process a form to remove a role
	if( $_REQUEST["action"] == 'delete' ) {
		$formHash['role_id'] = $_REQUEST['role_id'];
		$roleInfo = $gBitUser->getRoleInfo( $_REQUEST['role_id'] );
		if( isset( $_REQUEST["confirm"] ) ) {
			$gBitUser->verifyTicket();
			if( $_REQUEST['role_id'] == $gBitSystem->getConfig( 'default_home_role' ) ) {
				$gBitSystem->storeConfig( 'default_home_role', NULL, USERS_PKG_NAME );
			}
			$gBitUser->expungeRole( $_REQUEST['role_id'] );
			$successMsg = "The role ".$roleInfo['role_name']." was deleted.";
			unset( $_REQUEST['role_id'] );
		} else {
			$gBitSystem->setBrowserTitle( tra('Delete role') );
			$msgHash = array(
				'confirm_item' => tra( 'Are you sure you want to remove the role?' ),
				'warning' => tra( 'This will permentally delete the role' )." <strong>$roleInfo[role_name]</strong>",
			);
			$gBitSystem->confirmDialog( $formHash,$msgHash );
		}
	} elseif ($_REQUEST["action"] == 'remove') {
		$gBitUser->removePermissionFromRole( $_REQUEST["permission"], $_REQUEST['role_id'] );
		$successMsg = 'The permission '.$_REQUEST['permission'].' was removed successflly. <a href="'.USERS_PKG_URL.'admin/edit_role.php?action=assign&amp;perm='.$_REQUEST['permission'].'&amp;role_id='.$_REQUEST['role_id'].'&amp;pacakge='.$_REQUEST['package'].'">Undo last action.</a>';
	} elseif( $_REQUEST["action"] == 'create' ) {
		$mid = 'bitpackage:users/admin_role_edit.tpl';
		$gBitSystem->setBrowserTitle( tra( 'Create New Role' ) );
	} elseif ($_REQUEST["action"] == 'assign') {
		$gBitUser->assignPermissionToRole($_REQUEST["perm"], $_REQUEST['role_id']);
	}
}

if( !empty( $_REQUEST['role_id'] ) || (!empty( $_REQUEST["action"] ) && $_REQUEST["action"] == 'create' ) ) {
	$permPackages = $gBitUser->getPermissionPackages();
	$gBitSmarty->assignByRef( 'permPackages', $permPackages );

	// get role list separately from the $users stuff to avoid splitting of data due to pagination
	$listHash = array( 'sort_mode' => 'role_name_asc' );

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
	// get rolelist separately from the $users stuff to avoid splitting of data due to pagination
	$listHash = array( 'sort_mode' => !empty( $_REQUEST['sort_mode'] ) ? $_REQUEST['sort_mode'] : 'role_name_asc' );
}
$gBitSmarty->assign('roleList', $gBitUser->getAllRoles( $listHash ));

$inc = array();
if( empty( $mid ) ) {
	if( !empty( $_REQUEST['role_id'] ) ) {
		$roleInfo = $gBitUser->getRoleInfo( $_REQUEST['role_id'] );

		$defaultRoleId = $gBitSystem->getConfig( 'default_home_role' );
		$gBitSmarty->assignByRef( 'defaultRoleId', $defaultRoleId );
		$gBitSmarty->assignByRef( 'roleInfo', $roleInfo );
		$gBitSmarty->assignByRef( 'allPerms', $allPerms );

		$gBitSystem->setBrowserTitle( tra( 'Admininster Role' ).': '.$roleInfo['role_name'] );
		$mid = 'bitpackage:users/admin_role_edit.tpl';
	} else {
		$gBitSystem->setBrowserTitle( tra( 'Admin List Roles' ) );
		$_REQUEST['role_id'] = 0;
		$mid = 'bitpackage:users/admin_roles_list.tpl';
	}
}

$gBitSmarty->assign('successMsg',$successMsg);
$gBitSmarty->assign('errorMsg',$errorMsg);

// Display the template for role administration
$gBitSystem->display( $mid , NULL, array( 'display_mode' => 'edit' ));
?>
