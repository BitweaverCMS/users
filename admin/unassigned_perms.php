<?php
// $Header: /cvsroot/bitweaver/_bit_users/admin/Attic/unassigned_perms.php,v 1.1.2.1 2006/01/04 14:51:10 squareing Exp $
// Initialization
require_once( '../../bit_setup_inc.php' );

$gBitSystem->verifyPermission( 'bit_p_admin' );

if( !empty( $_REQUEST['assign_permissions'] ) && !empty( $_REQUEST['assign'] ) ) {
	foreach( $_REQUEST['assign'] as $p => $group_id ) {
		$gBitUser->assignPermissionToGroup( $p, $group_id );
	}

}

$unassignedPerms = $gBitUser->getUnassignedPerms();
foreach( $unassignedPerms as $key => $p ) {
	switch( $p['level'] ) {
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
$gBitSmarty->assign( 'unassignedPerms', $unassignedPerms );

$listHash = array( 'sort_mode' => 'group_id_asc' );
$groupList = $gBitUser->getAllGroups( $listHash );
foreach( $groupList['data'] as $group ) {
	$groupDrop[$group['group_id']] = $group['group_name'];
}
$gBitSmarty->assign( 'groupDrop', $groupDrop );

//vd($unassignedPerms);
//vd($groupList['data']);
$gBitSystem->display( "bitpackage:users/admin_unassigned_perms.tpl", tra( "Unassigned Permissions" ) );
?>
