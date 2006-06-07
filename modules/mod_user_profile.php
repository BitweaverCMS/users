<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/modules/mod_user_profile.php,v 1.5 2006/06/07 23:14:01 hash9 Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: mod_user_profile.php,v 1.5 2006/06/07 23:14:01 hash9 Exp $
 * @package users
 * @subpackage modules
 */
global $gQueryUser, $gBitUser, $gBitSmarty;

if ( isset($module_params['user_id']) && !empty($module_params['user_id'])) {
	$info = $gBitUser->getUserInfo(array("user_id"=>$module_params['user_id']));
	$gBitSmarty->assign_by_ref('userInfo', $info);
} elseif ( isset($module_params['login']) && !empty($module_params['login']) ) {
	$info = $gBitUser->getUserInfo(array("login"=>$module_params['login']));
	$gBitSmarty->assign_by_ref('userInfo', $info);
} elseif ( !empty( $gQueryUser->mInfo ) ) {
	$gBitSmarty->assign_by_ref('userInfo', $gQueryUser->mInfo );
} elseif( !empty( $gBitUser->mInfo ) ) {
	$gBitSmarty->assign_by_ref('userInfo', $gBitUser->mInfo );
}
?>
