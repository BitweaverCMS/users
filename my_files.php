<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/Attic/my_files.php,v 1.1.1.1.2.1 2005/06/27 17:47:58 lsces Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: my_files.php,v 1.1.1.1.2.1 2005/06/27 17:47:58 lsces Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once("../bit_setup_inc.php");
global $gBitSystem;

if (!$gBitUser->mUserId) {
	$smarty->assign('msg', tra("You are not logged in"));
	$gBitSystem->display( 'error.tpl' );
	die;
}

$userFiles = $gBitUser->getUserFiles();
$smarty->assign_by_ref('userFiles', $userFiles['files']);
$smarty->assign('numUserFiles', count($userFiles['files']));
$smarty->assign('diskUsage', $userFiles['diskUsage']);

if (!empty($_REQUEST['deleteAttachment'])) {
	$attachmentId = $_REQUEST['deleteAttachment'];
}

$gBitSystem->display('bitpackage:users/my_files.tpl');
?>
