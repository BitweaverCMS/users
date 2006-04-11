<?php
// $Header: /cvsroot/bitweaver/_bit_users/admin/assign_user.php,v 1.5 2006/04/11 13:10:19 squareing Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// This script is used to assign groups to a particular user
// ASSIGN USER TO GROUPS
// Initialization
require_once( '../../bit_setup_inc.php' );

$gBitSystem->verifyPermission( 'p_admin' );

if (!$gBitUser->userExists( array( 'user_id' => $_REQUEST["assign_user"] ) ) ) {
	$gBitSystem->fatalError( "User doesnt exist" );
}

$assignUser = new BitPermUser( $_REQUEST["assign_user"] );
$assignUser->load( TRUE );

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

/*
// If offset is set use it if not then use offset =0
// use the max_records php variable to set the limit
// if sortMode is not set then use last_modified_desc
if (!isset($_REQUEST["offset"])) {
	$offset = 0;
} else {
	$offset = $_REQUEST["offset"];
}
if (isset($_REQUEST['page'])) {
	$page = &$_REQUEST['page'];
	$offset = ($page - 1) * $max_records;
}
$gBitSmarty->assign_by_ref('offset', $offset);
if (isset($_REQUEST["find"])) {
	$find = $_REQUEST["find"];
} else {
	$find = '';
}
$gBitSmarty->assign('find', $find);
$cant_pages = ceil($users["cant"] / $max_records);
$gBitSmarty->assign_by_ref('cant_pages', $cant_pages);
$gBitSmarty->assign('actual_page', 1 + ($offset / $max_records));
if ($users["cant"] > ($offset + $max_records)) {
	$gBitSmarty->assign('next_offset', $offset + $max_records);
} else {
	$gBitSmarty->assign('next_offset', -1);
}
// If offset is > 0 then prev_offset
if ($offset > 0) {
	$gBitSmarty->assign('prev_offset', $offset - $max_records);
} else {
	$gBitSmarty->assign('prev_offset', -1);
}
*/

// Get users (list of users)
$gBitSmarty->assign('groups', $groupList['data']);

$gBitSystem->setBrowserTitle( 'Edit User: '.$assignUser->mUsername );

// Display the template
$gBitSystem->display( 'bitpackage:users/assignuser.tpl');
?>
