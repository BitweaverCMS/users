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
$listHash['online'] = TRUE;
$_template->tpl_vars['online_users'] = new Smarty_variable( $gBitUser->getUserActivity( $listHash ) );
$_template->tpl_vars['logged_users'] = new Smarty_variable( $gBitUser->countSessions( TRUE ) );
?>
