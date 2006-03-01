<?php
// $Header: /cvsroot/bitweaver/_bit_users/admin/admin_userfiles_inc.php,v 1.4 2006/03/01 20:16:36 spiderr Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
if (isset($_REQUEST["userfilesprefs"])) {
	$gBitSystem->storeConfig("uf_use_db", $_REQUEST["uf_use_db"], USERS_PKG_NAME);
	$gBitSystem->storeConfig("uf_use_dir", $_REQUEST["uf_use_dir"], USERS_PKG_NAME);
	$gBitSystem->storeConfig("userfiles_quota", $_REQUEST["userfiles_quota"], USERS_PKG_NAME);
	$gBitSmarty->assign('uf_use_db', $_REQUEST["uf_use_db"]);
	$gBitSmarty->assign('uf_use_dir', $_REQUEST["uf_use_dir"]);
	$gBitSmarty->assign('userfiles_quota', $_REQUEST['userfiles_quota']);
}

?>
