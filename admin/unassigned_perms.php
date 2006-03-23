<?php
// $Header: /cvsroot/bitweaver/_bit_users/admin/Attic/unassigned_perms.php,v 1.4 2006/03/23 13:19:46 squareing Exp $
// Initialization
require_once( '../../bit_setup_inc.php' );

$gBitSystem->verifyPermission( 'bit_p_admin' );
$gBitSmarty->assign_by_ref( 'feedback', $feedback = array() );

$listHash = array( 'sort_mode' => 'group_id_asc' );
$groupList = $gBitUser->getAllGroups( $listHash );
foreach( $groupList['data'] as $group ) {
	$groupDrop[$group['group_id']] = $group['group_name'];
}
$gBitSmarty->assign( 'groupDrop', $groupDrop );

if( !empty( $_REQUEST['assign_permissions'] ) && !empty( $_REQUEST['assign'] ) ) {
	$feedback['success'] = tra( "The permissions were successfully added to the requested groups." );
	foreach( $_REQUEST['assign'] as $p => $group_id ) {
		$gBitUser->assignPermissionToGroup( $p, $group_id );
		$assignedPerms[$p] = $groupDrop[$group_id];
	}
	$gBitSmarty->assign( 'assignedPerms', $assignedPerms );
}

$unassignedPerms = $gBitUser->getUnassignedPerms();
foreach( $unassignedPerms as $key => $p ) {
	if( !empty( $p['perm_level'] ) ) {
		switch( $p['perm_level'] ) {
			case "basic":
				$unassignedPerms[$key]['suggestion'] = -1;
				break;
			case "admin":
				$unassignedPerms[$key]['suggestion'] = 1;
				break;
			case "editors":
				$unassignedPerms[$key]['suggestion'] = 2;
				break;
			case "registered":
				$unassignedPerms[$key]['suggestion'] = 3;
				break;
			default:
				$unassignedPerms[$key]['suggestion'] = 0;
				break;
		}
	}
}
$gBitSmarty->assign( 'unassignedPerms', $unassignedPerms );

$gBitSystem->display( "bitpackage:users/admin_unassigned_perms.tpl", tra( "Unassigned Permissions" ) );
?>
