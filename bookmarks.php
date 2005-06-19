<?php
// $Header: /cvsroot/bitweaver/_bit_users/Attic/bookmarks.php,v 1.1 2005/06/19 05:12:22 bitweaver Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// Initialization
require_once( '../bit_setup_inc.php' );
include_once( USERS_PKG_PATH.'bookmark_lib.php' );
if (!$gBitUser->hasPermission( 'bit_p_create_bookmarks' )) {
	$smarty->assign('msg', tra("You dont have permission to use this feature"));
	$gBitSystem->display( 'error.tpl' );
	die;
}
if (!$gBitUser->mUserId) {
	$smarty->assign('msg', tra("You must log in to use this feature"));
	$gBitSystem->display( 'error.tpl' );
	die;
}
if ($feature_user_bookmarks != 'y') {
	$smarty->assign('msg', tra("This feature is disabled").": feature_user_bookmarks");
	$gBitSystem->display( 'error.tpl' );
	die;
}
if (!isset($_REQUEST["parent_id"])) {
	$_REQUEST["parent_id"] = 0;
}
if ($_REQUEST["parent_id"]) {
	$path = $bookmarklib->get_folder_path($_REQUEST["parent_id"], $gBitUser->mUserId);
	$p_info = $bookmarklib->get_folder($_REQUEST["parent_id"], $gBitUser->mUserId);
	$father = $p_info["parent_id"];
} else {
	$path = tra("TOP");
	$father = 0;
}
$smarty->assign('parent_id', $_REQUEST["parent_id"]);
$smarty->assign('path', $path);
//chekck for edit folder
if (isset($_REQUEST["editfolder"])) {
	$folder_info = $bookmarklib->get_folder($_REQUEST["editfolder"], $gBitUser->mUserId);
} else {
	$folder_info["name"] = '';
	$_REQUEST["editfolder"] = 0;
}
$smarty->assign('foldername', $folder_info["name"]);
$smarty->assign('editfolder', $_REQUEST["editfolder"]);
if (isset($_REQUEST["editurl"])) {
	$url_info = $bookmarklib->get_url($_REQUEST["editurl"]);
} else {
	$url_info["name"] = '';
	$url_info["url"] = '';
	$_REQUEST["editurl"] = 0;
}
$smarty->assign('urlname', $url_info["name"]);
$smarty->assign('urlurl', $url_info["url"]);
$smarty->assign('editurl', $_REQUEST["editurl"]);
// Create a folder inside the parentFolder here
if (isset($_REQUEST["addfolder"])) {
	
	if ($_REQUEST["editfolder"]) {
		$bookmarklib->update_folder($_REQUEST["editfolder"], $_REQUEST["foldername"], $gBitUser->mUserId);
		$smarty->assign('editfolder', 0);
		$smarty->assign('foldername', '');
	} else {
		$bookmarklib->add_folder($_REQUEST["parent_id"], $_REQUEST["foldername"], $gBitUser->mUserId);
	}
}
if (isset($_REQUEST["removefolder"])) {
	
	$bookmarklib->remove_folder($_REQUEST["removefolder"], $gBitUser->mUserId);
}
if (isset($_REQUEST["refreshurl"])) {
	
	$bookmarklib->refresh_url($_REQUEST["refreshurl"]);
}
if (isset($_REQUEST["addurl"])) {
	
	if( $urlid = $bookmarklib->replace_url($_REQUEST["editurl"], $_REQUEST["parent_id"], $_REQUEST["urlname"], $_REQUEST["urlurl"], $gBitUser->mUserId) ) {
		if ($_REQUEST["editurl"] == 0 && $gBitUser->hasPermission( 'bit_p_cache_bookmarks' )) {
			$bookmarklib->refresh_url($urlid);
		}
		$smarty->assign('editurl', 0);
		$smarty->assign('urlname', '');
		$smarty->assign('urlurl', '');
	} else {
		$smarty->assign( 'bookmarkError', "URL CANNOT BE MORE THAN 250 characters" );
	}
}
if (isset($_REQUEST["removeurl"])) {
	
	$bookmarklib->remove_url($_REQUEST["removeurl"], $gBitUser->mUserId);
}
$urls = $bookmarklib->list_folder($_REQUEST["parent_id"], 0, -1, 'name_asc', '', $gBitUser->mUserId);
$smarty->assign('urls', $urls["data"]);
$folders = $bookmarklib->get_child_folders($_REQUEST["parent_id"], $gBitUser->mUserId);
$pf = array(
	"name" => "..",
	"folder_id" => $father,
	"parent_id" => 0,
	"user_id" => $gBitUser->mUserId
);
$pfs = array($pf);
if ($_REQUEST["parent_id"]) {
	$folders = array_merge($pfs, $folders);
}
$smarty->assign('folders', $folders);

// Display the template
$gBitSystem->display( 'bitpackage:users/user_bookmarks.tpl');
?>
