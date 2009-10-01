<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/modules/mod_online_users.php,v 1.8 2009/10/01 13:45:52 wjames5 Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
 *
 * $Id: mod_online_users.php,v 1.8 2009/10/01 13:45:52 wjames5 Exp $
 * @package users
 * @subpackage modules
 */
 global $gBitUser, $module_params;
$listHash['online' ] = true; 
$listHash['last_get'] = !empty( $module_params['time_buffer'] ) ? $module_params['time_buffer'] : 900;
$online_users = $gBitUser->getUserActivity( $listHash );
$gBitSmarty->assign('online_users', $online_users);
$gBitSmarty->assign('logged_users', count( $online_users ) );
?>
