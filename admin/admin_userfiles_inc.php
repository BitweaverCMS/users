<?php
// $Header: /cvsroot/bitweaver/_bit_users/admin/admin_userfiles_inc.php,v 1.3 2006/02/08 21:51:15 squareing Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
if (isset($_REQUEST["userfilesprefs"])) {
	$gBitSystem->storePreference("uf_use_db", $_REQUEST["uf_use_db"], USERS_PKG_NAME);
	$gBitSystem->storePreference("uf_use_dir", $_REQUEST["uf_use_dir"], USERS_PKG_NAME);
	$gBitSystem->storePreference("userfiles_quota", $_REQUEST["userfiles_quota"], USERS_PKG_NAME);
	$gBitSmarty->assign('uf_use_db', $_REQUEST["uf_use_db"]);
	$gBitSmarty->assign('uf_use_dir', $_REQUEST["uf_use_dir"]);
	$gBitSmarty->assign('userfiles_quota', $_REQUEST['userfiles_quota']);
}

?>
