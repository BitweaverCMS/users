<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/modules/mod_user_profile.php,v 1.12 2009/10/01 14:17:06 wjames5 Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See below for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
 *
 * $Id: mod_user_profile.php,v 1.12 2009/10/01 14:17:06 wjames5 Exp $
 * @package users
 * @subpackage modules
 */
global $gQueryUser, $gBitUser, $gBitSmarty, $moduleParams;
extract( $moduleParams );

$userClass = $gBitSystem->getConfig( 'user_class', 'BitPermUser' );
if( !empty( $module_params['user_id'] )) {
	$user = $userClass( $module_params['user_id'] );
	$user->load();
	$userInfo = &$user->mInfo;
	$userPrefs = &$user->mPrefs;
} elseif( !empty( $module_params['login'] )) {
	$user = $userClass();
	$user->load(null,$module_params['login']);
	$userInfo = &$user->mInfo;
	$userPrefs = &$user->mPrefs;
} elseif( !empty( $gQueryUser->mInfo )) {
	$userInfo = &$gQueryUser->mInfo;
	$userPrefs = &$gQueryUser->mPrefs;
} elseif( !empty( $gBitUser->mInfo )) {
	$userInfo = &$gBitUser->mInfo;
	$userPrefs = &$gBitUser->mPrefs;
}
$userInfo['portrait_url']  = liberty_fetch_thumbnail_url( array( 'storage_path' => $userInfo['portrait_storage_path'], 'size' => 'small', 'mime_image' => FALSE ));

$gBitSmarty->assign_by_ref( 'userInfo', $userInfo );
$gBitSmarty->assign_by_ref( 'userPrefs', $userPrefs );
?>
