<?php
/**
 * my roles
 *
 * @copyright (c) 2004-15 bitweaver.org
 *
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

global $gBitUser, $gBitSystem;

// PERMISSIONS: registered user required
if ( !$gBitUser->isRegistered() ) {
	$gBitSystem->fatalError( tra( "You are not logged in." ));
}

if( !empty( $_REQUEST["cancel"] ) ) {
	bit_redirect( USERS_PKG_URL.'my_roles.php' );
}

if ( $gBitUser->hasPermission('p_users_create_personal_roles' ) ) {
	if( !empty( $_REQUEST['role_id'] ) ) {
		$allPerms = $gBitUser->getRolePermissions( array( 'sort_mode' => !empty( $_REQUEST['sort_mode'] ) ? $_REQUEST['sort_mode'] : NULL ));
		// get rolelist separately from the $users stuff to avoid splitting of data due to pagination
		$listHash = array( 'sort_mode' => 'role_name_asc' );
		$roleList = $gBitUser->getAllRoles( $listHash );
	} else {
		// get rolelist separately from the $users stuff to avoid splitting of data due to pagination
		$listHash = array( 'sort_mode' => !empty( $_REQUEST['sort_mode'] ) ? $_REQUEST['sort_mode'] : 'role_name_asc' );
		$roleList = $gBitUser->getAllRoles( $listHash );
	}
	
	// Remember a package limit if it is set.
	$gBitSmarty->assign( 'package',isset( $_REQUEST['package'] ) ? $_REQUEST['package'] : 'all' );

	// Save the join
	if( isset($_REQUEST["save"] ) ) {
		if( empty($_REQUEST["name"] ) ) {
			$_REQUEST["name"] = $_REQUEST["olrole"];
		}
		if( $gBitUser->storeRole( $_REQUEST ) ) {
			$successMsg = tra("Role changes were saved sucessfully.");
		} else {
			$errorMsg = $gBitUser->mErrors['roles'];
		}
	// Update Permissions
	} elseif (isset($_REQUEST['updateperms'])) {
		$listHash = array( 'role_id' => $_REQUEST['role_id'] );
		$updatePerms = $gBitUser->getrolePermissions( $listHash );
		foreach (array_keys($_REQUEST['perm']) as $per) {
			if( isset($_REQUEST['perm'][$per]) && !isset($updatePerms[$per]) ) {
				// we have an unselected perm that is now selected
				$gBitUser->assignPermissionToRole($per, $_REQUEST['role_id']);
			} elseif( empty($_REQUEST['perm'][$per]) && isset($updatePerms[$per]) ) {
				// we have a selected perm that is now UNselected
				$gBitUser->removePermissionFromRole($per, $_REQUEST['role_id']);
			}
		}
		// let's reload just to be safe.
		$allPerms = $gBitUser->getRolePermissions();
	// Do some action
	} elseif (isset($_REQUEST["action"])) {
		// Process a form to remove a role
		if( $_REQUEST["action"] == 'delete' ) {
			if( $gBitUser->getDefaultRole( $_REQUEST['role_id'] ) ) {
				$errorMsg = tra("You cannot remove this role, as it is currently set as your 'Default' role");
			} else {
				$gBitUser->expungeRole( $_REQUEST['role_id'] );
				$successMsg = tra("The role was deleted.");
				unset( $_REQUEST['role_id'] );
			}
		// remove a permission from a role
		} elseif ($_REQUEST["action"] == 'remove') {
			$gBitUser->removePermissionFromRole( $_REQUEST["permission"], $_REQUEST['role_id'] );
			$successMsg = tra("Permission Removed");
			$mid = 'bitpackage:users/my_role_edit.tpl';
		// Create a new role
		} elseif( $_REQUEST["action"] == 'create' ) {
			$gBitSystem->setBrowserTitle( tra('Create New Role') );
			$mid = 'bitpackage:users/my_role_edit.tpl';
		// Assign a permission to a role
		} elseif ($_REQUEST["action"] == 'assign') {
			$gBitUser->assignPermissionToRole($_REQUEST["perm"], $_REQUEST['role_id']);
			$successMsg = tra("Permission Assigned");
			$mid = 'bitpackage:users/my_role_edit.tpl';
		}
	// Search for users to add
	} elseif (!empty($_REQUEST['submitUserSearch'])) {
		$searchParams = array('find' => $_REQUEST['find']);
		$gBitUser->getList($searchParams);
		$foundUsers = $searchParams['data'];
		$mid = 'bitpackage:users/my_role_edit.tpl';
		$gBitSmarty->assign_by_ref('foundUsers', $foundUsers);
	} elseif (!empty($_REQUEST['assignuser'])) {
		if( !empty($_REQUEST['role_id'] ) ) {
			if ($_REQUEST['role_id'] != -1 && $roleList[$_REQUEST['role_id']]['user_id'] == $gBitUser->mUserId) {
				$gBitUser->addUserToRole( $_REQUEST['assignuser'], $_REQUEST['role_id'] );
			}
			else {
				$errorMsg = tra("You can not assign users to this role.");
			}
		}
		$mid = 'bitpackage:users/my_role_edit.tpl';
	}

	// get pagination url
	// get rolelist separately from the $users stuff to avoid splitting of data due to pagination
	$listHash = array( 'sort_mode' => 'role_name_asc' );
	$roleList = $gBitUser->getAllUserRoles();
	
	if( !empty( $_REQUEST['role_id'] ) ) {
		// we don't want our own role listed when editing
		if( !empty( $roleList[$_REQUEST['role_id']] ) ) {
			unset( $roleList[$_REQUEST['role_id']] );
		}
		$roleInfo = $gBitUser->getRoleInfo( $_REQUEST['role_id'] );
		$roleUsers = $gBitUser->getRoleUsers( $_REQUEST['role_id'] );
		$gBitSmarty->assign_by_ref('roleUsers', $roleUsers);
		$gBitSmarty->assign_by_ref('roleInfo', $roleInfo);
		$gBitSmarty->assign_by_ref( 'allPerms', $allPerms );
		$gBitSystem->setBrowserTitle( 'Admininster Role: '.$roleInfo['role_name'].' '.(isset( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : '') );
		$mid = 'bitpackage:users/my_role_edit.tpl';
	} 

	$gBitSmarty->assign('roles', $roleList);
	//	$gBitSmarty->assign( (!empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'edit').'TabSelect', 'tdefault' );
}

/* join or leave a public role. */
if ( ( !empty( $_REQUEST['add_public_role'] ) || !empty( $_REQUEST['remove_public_role'] ) ) && !empty( $_REQUEST['public_role_id'] ) ) {
	$roleInfo = $gBitUser->getRoleInfo( $_REQUEST['public_role_id'] );
	if ( empty($roleInfo) || $roleInfo['is_public'] != 'y' ) {
		if (empty($_REQUEST['add_public_role'])) {
			$errorMsg[] = tra("You can't join this role.");
		}
		else {
			$errorMsg[] = tra("You can't leave this role.");
		}			
	} elseif ( !empty( $_REQUEST['add_public_role'] ) ) {
		$gBitUser->addUserToRole( $gBitUser->mUserId, $_REQUEST['public_role_id'] );
	} elseif ( !empty( $_REQUEST['remove_public_role'] ) ) {
		$gBitUser->removeUserFromRole( $gBitUser->mUserId, $_REQUEST['public_role_id'] );
	}
	$gBitUser->loadPermissions();
	if ( !empty( $_REQUEST['add_public_role'] ) && !empty( $roleInfo['after_registration_page'] ) ) {
		if ( $gBitUser->verifyId( $roleInfo['after_registration_page'] ) ) {
			$url = BIT_ROOT_URL."index.php?content_id=".$roleInfo['after_registration_page'];
		} elseif( strpos( $roleInfo['after_registration_page'], '/' ) === FALSE ) {
			$url = BitPage::getDisplayUrl( $roleInfo['after_registration_page'] );
		} else {
			$url = $roleInfo['after_registration_page'];
		}
		header( 'Location: '.$url );
		exit;
	}
}

/* Load up public roles and check if the user can join or leave them */
$systemRoles = $gBitUser->getRoles( $gBitUser->mUserId, TRUE );
$gBitSmarty->assign_by_ref( 'systemRoles', $systemRoles);
$listHash = array(
	'is_public'=>'y',
	'sort_mode' => array( 'is_default_asc', 'role_desc_asc' ),
);
$publicRoles = $gBitUser->getAllRoles( $listHash );	
if( count( $publicRoles )) {
	foreach ( $systemRoles as $roleId=>$roleInfo ) {
		foreach ( $publicRoles as $key=>$publicRole) {
			if ( $publicRoles[$key]['role_id'] == $roleId) {
				if ($publicRoles[$key]['is_default'] != 'y' ) {
					$systemRoles[$roleId]['public'] = 'y';
					$canRemovePublic = 'y';
				}
				$publicRoles[$key]['used'] = 'y';
				break;
			}
		}
	}
	foreach ( $publicRoles as $roleInfo) {
		if ( empty($roleInfo['used'] ) && $roleInfo['is_default'] != 'y' ) {
			$gBitSmarty->assign( 'canAddPublic' , 'y');
			break;
		}
	}
	$gBitSmarty->assign_by_ref( 'publicRoles', $publicRoles );
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
	$mid = 'bitpackage:users/my_roles_list.tpl';
}

// Display the template for role administration
$gBitSystem->display( $mid , NULL, array( 'display_mode' => 'display' ));
?>
