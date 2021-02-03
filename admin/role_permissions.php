<?php
require_once( '../../kernel/includes/setup_inc.php' );
$gBitSystem->verifyPermission( 'p_admin' );

$feedback = array();

// get a list of all roles and their permissions
$listHash = array(
	'only_root_roles' => TRUE,
	'sort_mode' => !empty( $_REQUEST['sort_mode'] ) ? $_REQUEST['sort_mode'] : 'role_name_asc'
);
$allRoles = $gBitUser->getAllRoles( $listHash );
$allPerms = $gBitUser->getRolePermissions( $_REQUEST );

// deal with assigning permissions to various roles
if( !empty( $_REQUEST['save'] )) {
	$gBitUser->verifyTicket();
	foreach( array_keys( $allRoles ) as $roleId ) {
		foreach( array_keys( $allPerms ) as $perm ) {
			if( !empty( $_REQUEST['perms'][$roleId][$perm] )) {
				$gBitUser->assignPermissionToRole( $perm, $roleId );
			} else {
				$gBitUser->removePermissionFromRole( $perm, $roleId );
			}
		}
	}

	$feedback['success'] = tra( "The permissions were successfully added to the requested roles." );
	// we need to update the roles list
	$allRoles = $gBitUser->getAllRoles( $listHash );
}

// Check to see if we have unassigned permissions
if(( $unassignedPerms = $gBitUser->getUnassignedPerms() )) {
	$feedback['warning'] = tra( 'You have some permissions that are not assigned to any role. You need to assign these to at least one role each.' );
	$gBitSmarty->assign( 'unassignedPerms', $unassignedPerms );
}

$gBitSmarty->assign( 'allPerms', $allPerms );
$gBitSmarty->assign( 'allRoles', $allRoles );
$gBitSmarty->assign( 'permPackages', $gBitUser->getPermissionPackages() );
$gBitSmarty->assign( 'feedback', $feedback );
$gBitSmarty->assign( 'contentWithPermissions', LibertyContent::getContentWithPermissionsList() );

$gBitSystem->display( 'bitpackage:users/admin_role_permissions.tpl', tra( 'Permission Maintenance' ), array( 'display_mode' => 'admin' ));
?>
