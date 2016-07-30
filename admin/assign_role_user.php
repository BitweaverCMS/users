<?php
// $Header$
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See below for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.
// This script is used to assign roles to a particular user
// ASSIGN USER TO ROLES
// Initialization
require_once( '../../kernel/setup_inc.php' );

$gBitSystem->verifyPermission( 'p_users_admin' );

if (!$gBitUser->userExists( array( 'user_id' => $_REQUEST["assign_user"] ) ) ) {
	$gBitSystem->fatalError( tra( "User doesnt exist" ));
}

$assignUser = new RolePermUser( $_REQUEST["assign_user"] );
$assignUser->load( TRUE );

if( $assignUser->isAdmin() && !$gBitUser->isAdmin() ) {
	$gBitSystem->fatalError( tra( 'You cannot modify a system administrator.' ));
}

if( isset( $_REQUEST["action"] ) ) {
	$gBitUser->verifyTicket();
	if ($_REQUEST["action"] == 'assign') {
		$assignUser->addUserToRole( $assignUser->mUserId, $_REQUEST["role_id"] );
	} elseif ($_REQUEST["action"] == 'removerole') {
		$assignUser->removeUserFromRole($_REQUEST["assign_user"], $_REQUEST["role_id"]);
	}
	bit_redirect( 'assign_role_user.php?assign_user='.$assignUser->mUserId );
}elseif(isset($_REQUEST['set_default'])) {
	$gBitUser->verifyTicket();
	$assignUser->storeUserDefaultRole( $assignUser->mUserId, $_REQUEST['default_role'] );
	$assignUser->load();
}
$gBitSmarty->assignByRef( 'assignUser', $assignUser );

$listHash = array( 'sort_mode' => 'role_name_asc' );
$gBitSmarty->assign('roles', $gBitUser->getAllRoles( $listHash ));

$gBitSystem->setBrowserTitle( 'Edit User: '.$assignUser->mUsername );

// Display the template
$gBitSystem->display( 'bitpackage:users/admin_assign_role_user.tpl', NULL, array( 'display_mode' => 'admin' ));
?>
