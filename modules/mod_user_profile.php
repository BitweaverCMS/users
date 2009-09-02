<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/modules/mod_user_profile.php,v 1.8 2009/09/02 18:21:58 spiderr Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: mod_user_profile.php,v 1.8 2009/09/02 18:21:58 spiderr Exp $
 * @package users
 * @subpackage modules
 */
global $gQueryUser, $gBitUser, $gBitSmarty, $moduleParams;
extract( $moduleParams );

$userClass = $gBitSystem->getConfig( 'user_class', 'BitPermUser' );
if( !empty( $module_params['user_id'] )) {
	$user = $userClass( $module_params['user_id'] );
	$user->load();
	$gBitSmarty->assign_by_ref( 'userInfo', $user->mInfo );
} elseif( !empty( $module_params['login'] )) {
	$user = $userClass();
	$user->load(null,$module_params['login']);
	$gBitSmarty->assign_by_ref( 'userInfo', $user->mInfo);
} elseif( !empty( $gQueryUser->mInfo )) {
	$gBitSmarty->assign_by_ref( 'userInfo', $gQueryUser->mInfo );
} elseif( !empty( $gBitUser->mInfo )) {
	$gBitSmarty->assign_by_ref( 'userInfo', $gBitUser->mInfo );
}
?>
