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

$gBitSmarty->assign_by_ref( 'userInfo', $userInfo );
$gBitSmarty->assign_by_ref( 'userPrefs', $userPrefs );
?>
