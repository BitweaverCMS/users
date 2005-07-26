<?php
// $Header: /cvsroot/bitweaver/_bit_users/admin/admin_userfiles_inc.php,v 1.1.1.1.2.1 2005/07/26 15:50:31 drewslater Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
if (isset($_REQUEST["userfilesprefs"])) {
	
	$gBitSystem->storePreference("uf_use_db", $_REQUEST["uf_use_db"]);
	$gBitSystem->storePreference("uf_use_dir", $_REQUEST["uf_use_dir"]);
	$gBitSystem->storePreference("userfiles_quota", $_REQUEST["userfiles_quota"]);
	$gBitSmarty->assign('uf_use_db', $_REQUEST["uf_use_db"]);
	$gBitSmarty->assign('uf_use_dir', $_REQUEST["uf_use_dir"]);
	$gBitSmarty->assign('userfiles_quota', $_REQUEST['userfiles_quota']);
}

?>