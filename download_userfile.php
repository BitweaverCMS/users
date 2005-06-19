<?php
// $Header: /cvsroot/bitweaver/_bit_users/Attic/download_userfile.php,v 1.1 2005/06/19 05:12:22 bitweaver Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// Initialization
require_once( '../bit_setup_inc.php' );
include_once (USERS_PKG_PATH.'userfiles_lib.php');
if (!isset($_REQUEST["file_id"])) {
	die;
}
$uf_use_db = $gBitSystem->getPreference('uf_use_db', 'y');
$uf_use_dir = $gBitSystem->getPreference('uf_use_dir', '');
$info = $userfileslib->get_userfile($gBitUser->mUserId, $_REQUEST["file_id"]);
$type = &$info["filetype"];
$file = &$info["filename"];
$content = &$info["data"];
header ("Content-type: $type");
header ("Content-Disposition: inline; filename=\"$file\"");
if ($info["path"]) {
	readfile ($uf_use_dir . $info["path"]);
} else {
	echo "$content";
}
?>
