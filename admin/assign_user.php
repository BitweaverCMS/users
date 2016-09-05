<?php
// $Header$
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See below for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.
// This script is used to assign groups to a particular user
// ASSIGN USER TO GROUPS
// Initialization
require_once( '../../kernel/setup_inc.php' );

$gBitSystem->verifyPermission( 'p_users_admin' );

if (!$gBitUser->userExists( array( 'user_id' => $_REQUEST["assign_user"] ) ) ) {
	$gBitSystem->fatalError( tra( "User doesnt exist" ));
}

$assignUser = new BitPermUser( $_REQUEST["assign_user"] );
$assignUser->setCacheableObject( FALSE );
$assignUser->load( TRUE );

if( $assignUser->isAdmin() && !$gBitUser->isAdmin() ) {
	$gBitSystem->fatalError( tra( 'You cannot modify a system administrator.' ));
}

if( isset( $_REQUEST["action"] ) ) {
	$gBitUser->verifyTicket();
	if ($_REQUEST["action"] == 'assign') {
		$assignUser->addUserToGroup( $assignUser->mUserId, $_REQUEST["group_id"] );
	} elseif ($_REQUEST["action"] == 'removegroup') {
		$assignUser->removeUserFromGroup($_REQUEST["assign_user"], $_REQUEST["group_id"]);
	}
	header( 'Location: '.$_SERVER['SCRIPT_NAME'].'?assign_user='.$assignUser->mUserId );
	die;
}elseif(isset($_REQUEST['set_default'])) {
	$gBitUser->verifyTicket();
	$assignUser->storeUserDefaultGroup( $assignUser->mUserId, $_REQUEST['default_group'] );
	$assignUser->load();
}
$gBitSmarty->assignByRef( 'assignUser', $assignUser );

$listHash = array( 'sort_mode' => 'group_name_asc' );
$gBitSmarty->assign('groups', $gBitUser->getAllGroups( $listHash ));

$gBitSystem->setBrowserTitle( 'Edit User: '.$assignUser->mUsername );

if($gBitSystem->isPackageActive("quota")) {
	include(QUOTA_PKG_PATH.'quota_inc.php');	
}



// Display the template
$gBitSystem->display( 'bitpackage:users/admin_assign_user.tpl', NULL, array( 'display_mode' => 'admin' ));
?>
