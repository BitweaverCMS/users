<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/modules/mod_user_profile.php,v 1.6 2006/06/08 20:01:00 hash9 Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: mod_user_profile.php,v 1.6 2006/06/08 20:01:00 hash9 Exp $
 * @package users
 * @subpackage modules
 */
global $gQueryUser, $gBitUser, $gBitSmarty;

if ( !empty($module_params['user_id'])) {
	$user = new BitUser($module_params['user_id']);
	$user->load();
	$gBitSmarty->assign_by_ref('userInfo', $user->mInfo);
} elseif ( !empty($module_params['login']) ) {
	$user = new BitUser();
	$user->load(null,$module_params['login']);
	$gBitSmarty->assign_by_ref('userInfo', $user->mInfo);
} elseif ( !empty( $gQueryUser->mInfo ) ) {
	$gBitSmarty->assign_by_ref('userInfo', $gQueryUser->mInfo );
} elseif( !empty( $gBitUser->mInfo ) ) {
	$gBitSmarty->assign_by_ref('userInfo', $gBitUser->mInfo );
}
?>
