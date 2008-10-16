<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/modules/mod_who_is_there.php,v 1.7 2008/10/16 09:57:58 squareing Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: mod_who_is_there.php,v 1.7 2008/10/16 09:57:58 squareing Exp $
 * @package users
 * @subpackage modules
 */
$listHash['online'] = TRUE;
$gBitSmarty->assign( 'online_users', $gBitUser->getUserActivity( $listHash ));
$gBitSmarty->assign( 'logged_users', $gBitUser->countSessions( TRUE ));
?>
