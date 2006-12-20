<?php
// $Header: /cvsroot/bitweaver/_bit_users/admin/assign_user.php,v 1.9 2006/12/20 14:59:57 squareing Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// This script is used to assign groups to a particular user
// ASSIGN USER TO GROUPS
// Initialization
require_once( '../../bit_setup_inc.php' );

$gBitSystem->verifyPermission( 'p_users_admin' );

if (!$gBitUser->userExists( array( 'user_id' => $_REQUEST["assign_user"] ) ) ) {
	$gBitSystem->fatalError( "User doesnt exist" );
}

$assignUser = new BitPermUser( $_REQUEST["assign_user"] );
$assignUser->load( TRUE );

if( $assignUser->isAdmin() && !$gBitUser->isAdmin() ) {
	$gBitSystem->fatalError( 'You cannot modify a system administrator.' );
}

if( isset( $_REQUEST["action"] ) ) {
	$gBitUser->verifyTicket();
	if ($_REQUEST["action"] == 'assign') {
		$assignUser->addUserToGroup( $assignUser->mUserId, $_REQUEST["group_id"] );
	} elseif ($_REQUEST["action"] == 'removegroup') {
		$gBitUser->removeUserFromGroup($_REQUEST["assign_user"], $_REQUEST["group_id"]);
	}
	header( 'Location: '.$_SERVER['PHP_SELF'].'?assign_user='.$assignUser->mUserId );
	die;
}elseif(isset($_REQUEST['set_default'])) {
	$gBitUser->verifyTicket();
	$assignUser->storeUserDefaultGroup( $assignUser->mUserId, $_REQUEST['default_group'] );
	$assignUser->load();
}
$gBitSmarty->assign_by_ref( 'assignUser', $assignUser );

$listHash = array( 'sort_mode' => 'group_name_asc' );
$groupList = $gBitUser->getAllGroups( $listHash );

// Get users (list of users)
$gBitSmarty->assign('groups', $groupList['data']);

$gBitSystem->setBrowserTitle( 'Edit User: '.$assignUser->mUsername );

// Display the template
$gBitSystem->display( 'bitpackage:users/admin_assign_user.tpl');
?>
