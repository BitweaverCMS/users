<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/Attic/menu.php,v 1.2 2005/06/28 07:46:23 spiderr Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: menu.php,v 1.2 2005/06/28 07:46:23 spiderr Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
include_once( USERS_PKG_PATH.'user_menu_lib.php' );
if ($feature_usermenu != 'y') {
	$smarty->assign('msg', tra("This feature is disabled").": feature_usermenu");
	$gBitSystem->display( 'error.tpl' );
	die;
}
if (!$gBitUser->mUserId) {
	$smarty->assign('msg', tra("Must be logged to use this feature"));
	$gBitSystem->display( 'error.tpl' );
	die;
}
if (!$gBitUser->hasPermission( 'bit_p_usermenu' )) {
	$smarty->assign('msg', tra("Permission denied to use this feature"));
	$gBitSystem->display( 'error.tpl' );
	die;
}
if (!isset($_REQUEST["menu_id"]))
	$_REQUEST["menu_id"] = 0;
if (isset($_REQUEST["delete"]) && isset($_REQUEST["menu"])) {
	
	foreach (array_keys($_REQUEST["menu"])as $men) {
		$usermenulib->remove_usermenu($gBitUser->mUserId, $men);
	}
	if (isset($_SESSION['usermenu']))
		unset ($_SESSION['usermenu']);
}
if (isset($_REQUEST['addbk'])) {
	
	$usermenulib->add_bk($gBitUser->mUserId);
	if (isset($_SESSION['usermenu']))
		unset ($_SESSION['usermenu']);
}
if ($_REQUEST["menu_id"]) {
	$info = $usermenulib->get_usermenu($gBitUser->mUserId, $_REQUEST["menu_id"]);
} else {
	$info = array();
	$info['name'] = '';
	$info['url'] = isset($_REQUEST['url']) ? $_REQUEST['url'] : '';
	$info['mode'] = 'w';
	$info['position'] = $usermenulib->get_max_position($gBitUser->mUserId) + 1;
}
if (isset($_REQUEST['save'])) {
	
	$usermenulib->replace_usermenu(
		$gBitUser->mUserId, $_REQUEST["menu_id"], $_REQUEST["name"], $_REQUEST["url"], $_REQUEST['position'], $_REQUEST['mode']);
	$info = array();
	$info['name'] = '';
	$info['url'] = '';
	$info['position'] = 1;
	$_REQUEST["menu_id"] = 0;
	unset ($_SESSION['usermenu']);
}
$smarty->assign('menu_id', $_REQUEST["menu_id"]);
$smarty->assign('info', $info);
if ( empty( $_REQUEST["sort_mode"] ) ) {
	$sort_mode = 'position_asc';
} else {
	$sort_mode = $_REQUEST["sort_mode"];
}
if (!isset($_REQUEST["offset"])) {
	$offset = 0;
} else {
	$offset = $_REQUEST["offset"];
}
if (isset($_REQUEST['page'])) {
	$page = &$_REQUEST['page'];
	$offset = ($page - 1) * $maxRecords;
}
$smarty->assign_by_ref('offset', $offset);
if (isset($_REQUEST["find"])) {
	$find = $_REQUEST["find"];
} else {
	$find = '';
}
$smarty->assign('find', $find);
$smarty->assign_by_ref('sort_mode', $sort_mode);
if (isset($_SESSION['thedate'])) {
	$pdate = $_SESSION['thedate'];
} else {
	$pdate = date("U");
}
$channels = $usermenulib->list_usermenus($gBitUser->mUserId, $offset, $maxRecords, $sort_mode, $find);
$cant_pages = ceil($channels["cant"] / $maxRecords);
$smarty->assign_by_ref('cant_pages', $cant_pages);
$smarty->assign('actual_page', 1 + ($offset / $maxRecords));
if ($channels["cant"] > ($offset + $maxRecords)) {
	$smarty->assign('next_offset', $offset + $maxRecords);
} else {
	$smarty->assign('next_offset', -1);
}
// If offset is > 0 then prev_offset
if ($offset > 0) {
	$smarty->assign('prev_offset', $offset - $maxRecords);
} else {
	$smarty->assign('prev_offset', -1);
}
$smarty->assign_by_ref('channels', $channels["data"]);

$gBitSystem->display( 'bitpackage:users/usermenu.tpl');
?>
