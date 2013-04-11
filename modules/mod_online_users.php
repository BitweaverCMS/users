<?php
/**
 * $Header$
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See below for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
 *
 * $Id$
 * @package users
 * @subpackage modules
 */
 global $gBitUser, $module_params;
$listHash['online' ] = true; 
$listHash['last_get'] = !empty( $module_params['time_buffer'] ) ? $module_params['time_buffer'] : 900;
$online_users = $gBitUser->getUserActivity( $listHash );
$_template->tpl_vars['online_users'] = new Smarty_variable( $online_users);
$_template->tpl_vars['logged_users'] = new Smarty_variable( count( $online_users ) );
?>
