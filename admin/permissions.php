<?php
require_once( '../../bit_setup_inc.php' );
$gBitSystem->verifyPermission( 'p_admin' );
$gBitUser->verifyTicket();

$feedback = array();

// get a list of all groups and their permissions
$listHash = array(
	'only_root_groups' => TRUE,
	'sort_mode' => !empty( $_REQUEST['sort_mode'] ) ? $_REQUEST['sort_mode'] : 'group_name_asc'
);
$allGroups = $gBitUser->getAllGroups( $listHash );
$allPerms = $gBitUser->getGroupPermissions( $_REQUEST );

// deal with assigning permissions to various groups
if( !empty( $_REQUEST['save'] )) {
	foreach( array_keys( $allGroups ) as $groupId ) {
		foreach( array_keys( $allPerms ) as $perm ) {
			if( !empty( $_REQUEST['perms'][$groupId][$perm] )) {
				$gBitUser->assignPermissionToGroup( $perm, $groupId );
			} else {
				$gBitUser->removePermissionFromGroup( $perm, $groupId );
			}
		}
	}

	$feedback['success'] = tra( "The permissions were successfully added to the requested groups." );
	// we need to update the groups list
	$allGroups = $gBitUser->getAllGroups( $listHash );
}

// Check to see if we have unassigned permissions
if(( $unassignedPerms = $gBitUser->getUnassignedPerms() )) {
	$feedback['warning'] = tra( 'You have some permissions that are not assigned to any group. You need to assign these to at least one group each.' );
	$gBitSmarty->assign( 'unassignedPerms', $unassignedPerms );
}

$gBitSmarty->assign( 'allPerms', $allPerms );
$gBitSmarty->assign( 'allGroups', $allGroups );
$gBitSmarty->assign( 'permPackages', $gBitUser->getPermissionPackages() );
$gBitSmarty->assign( 'feedback', $feedback );
$gBitSmarty->assign( 'contentWithPermissions', LibertyContent::getContentWithPermissionsList() );

$gBitSystem->display( 'bitpackage:users/admin_permissions.tpl', tra( 'Permission Maintenance' ), array( 'display_mode' => 'admin' ));
?>
