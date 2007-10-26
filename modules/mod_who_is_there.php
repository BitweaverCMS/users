<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/modules/mod_who_is_there.php,v 1.6 2007/10/26 13:26:51 nickpalmer Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: mod_who_is_there.php,v 1.6 2007/10/26 13:26:51 nickpalmer Exp $
 * @package users
 * @subpackage modules
 */
$logged_users = $gBitUser->count_sessions(true);
$listHash['online'] = true;
$online_users = $gBitUser->getUserActivity($listHash);
$gBitSmarty->assign('online_users', $online_users);
$gBitSmarty->assign('logged_users', $logged_users);
?>
