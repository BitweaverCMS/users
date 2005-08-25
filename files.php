<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/Attic/files.php,v 1.1.1.1.2.3 2005/08/25 21:10:31 lsces Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: files.php,v 1.1.1.1.2.3 2005/08/25 21:10:31 lsces Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
include_once ( USERS_PKG_PATH.'userfiles_lib.php');
if ($feature_userfiles != 'y') {
	$gBitSmarty->assign('msg', tra("This feature is disabled").": feature_userfiles");
	$gBitSystem->display( 'error.tpl' );
	die;
}
if ( !$gBitUser->isValid() ) {
	$gBitSmarty->assign('msg', tra("Must be logged to use this feature"));
	$gBitSystem->display( 'error.tpl' );
	die;
}
if (!$gBitUser->hasPermission( 'bit_p_userfiles' )) {
	$gBitSmarty->assign('msg', tra("Permission denied to use this feature"));
	$gBitSystem->display( 'error.tpl' );
	die;
}
$quota = $userfileslib->userfiles_quota( $gBitUser->mUserId );
$limit = $userfiles_quota * 1024 * 1000;
if ($limit == 0)
	$limit = 999999999;
$percentage = ($quota / $limit) * 100;
$cellsize = round($percentage / 100 * 200);
$percentage = round($percentage);
$gBitSmarty->assign('cellsize', $cellsize);
$gBitSmarty->assign('percentage', $percentage);
// Process upload here
for ($i = 0; $i < 5; $i++) {
	if (isset($_FILES["userfile$i"]) && is_uploaded_file($_FILES["userfile$i"]['tmp_name'])) {
		
		$fp = fopen($_FILES["userfile$i"]['tmp_name'], "rb");
		$data = '';
		$fhash = '';
		$name = $_FILES["userfile$i"]['name'];
		if ($uf_use_db == 'n') {
			$fhash = md5(uniqid('.'));
			$fw = fopen($uf_use_dir . $fhash, "wb");
			if (!$fw) {
				$gBitSmarty->assign('msg', tra('Cannot write to this file:'). $fhash);
				$gBitSystem->display( 'error.tpl' );
				die;
			}
		}
		while (!feof($fp)) {
			if ($uf_use_db == 'y') {
				$data .= fread($fp, 8192 * 16);
			} else {
				$data = fread($fp, 8192 * 16);
				fwrite($fw, $data);
			}
		}
		fclose ($fp);
		if ($uf_use_db == 'n') {
			fclose ($fw);
			$data = '';
		}
		$size = $_FILES["userfile$i"]['size'];
		$name = $_FILES["userfile$i"]['name'];
		$type = $_FILES["userfile$i"]['type'];
		if ($quota + $size > $limit) {
			$gBitSmarty->assign('msg', tra('Cannot upload this file not enough quota'));
			$gBitSystem->display( 'error.tpl' );
			die;
		}
		$userfileslib->upload_userfile($gBitUser->mUserId, '', $name, $type, $size, $data, $fhash);
	}
}
// Process removal here
if (isset($_REQUEST["delete"]) && isset($_REQUEST["userfile"])) {
	
	foreach (array_keys($_REQUEST["userfile"])as $file) {
		$userfileslib->remove_userfile($gBitUser->mUserId, $file);
	}
}
$quota = $userfileslib->userfiles_quota($gBitUser->mUserId);
$limit = $userfiles_quota * 1024 * 1000;
if ($limit == 0)
	$limit = 999999999;
$percentage = $quota / $limit * 100;
$cellsize = round($percentage / 100 * 200);
$percentage = round($percentage);
if ($cellsize == 0)
	$cellsize = 1;
$gBitSmarty->assign('cellsize', $cellsize);
$gBitSmarty->assign('percentage', $percentage);
if ( empty( $_REQUEST["sort_mode"] ) ) {
	$sort_mode = 'created_desc';
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
$gBitSmarty->assign_by_ref('offset', $offset);
if (isset($_REQUEST["find"])) {
	$find = $_REQUEST["find"];
} else {
	$find = '';
}
$gBitSmarty->assign('find', $find);
$gBitSmarty->assign_by_ref('sort_mode', $sort_mode);
if (isset($_SESSION['thedate'])) {
	$pdate = $_SESSION['thedate'];
} else {
	$pdate = $gBitSystem->getUTCTime();
}
$channels = $userfileslib->list_userfiles($gBitUser->mUserId, $offset, $maxRecords, $sort_mode, $find);
$cant_pages = ceil($channels["cant"] / $maxRecords);
$gBitSmarty->assign_by_ref('cant_pages', $cant_pages);
$gBitSmarty->assign('actual_page', 1 + ($offset / $maxRecords));
if ($channels["cant"] > ($offset + $maxRecords)) {
	$gBitSmarty->assign('next_offset', $offset + $maxRecords);
} else {
	$gBitSmarty->assign('next_offset', -1);
}
// If offset is > 0 then prev_offset
if ($offset > 0) {
	$gBitSmarty->assign('prev_offset', $offset - $maxRecords);
} else {
	$gBitSmarty->assign('prev_offset', -1);
}
$gBitSmarty->assign_by_ref('channels', $channels["data"]);
$gBitSmarty->assign('tasks_use_dates', $tasks_use_dates);

$gBitSystem->display( 'bitpackage:users/userfiles.tpl');
?>
