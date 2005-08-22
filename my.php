<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/my.php,v 1.2.2.7 2005/08/22 09:36:15 lsces Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: my.php,v 1.2.2.7 2005/08/22 09:36:15 lsces Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );
include_once( USERS_PKG_PATH.'task_lib.php' );

if( !$gBitUser->isRegistered() ) {
	Header( 'Location: '.USERS_PKG_URL.'login.php' );
	die;
}

// custom userfields
if( !empty( $gBitSystem->mPrefs['custom_user_fields'] ) ) {
	$customFields= explode( ',', $gBitSystem->mPrefs['custom_user_fields']  );
	$gBitSmarty->assign('customFields', $customFields);
}

// some content specific offsets and pagination settings
if( !empty( $_REQUEST['sort_mode'] ) ) {
	$content_sort_mode = $_REQUEST['sort_mode'];
	$gBitSmarty->assign( 'sort_mode', $content_sort_mode );
}

$max_content = $gBitSystem->mPrefs['maxRecords'];
$offset_content = !empty( $_REQUEST['offset'] ) ? $_REQUEST['offset'] : 0;
$gBitSmarty->assign( 'curPage', $page = !empty( $_REQUEST['page'] ) ? $_REQUEST['page'] : 1 );
$offset_content = ( $page - 1 ) * $gBitSystem->mPrefs['maxRecords'];

// set the user_id to only display content viewing user
$_REQUEST['user_id'] = $gBitUser->mUserId;

// now that we have all the offsets, we can get the content list
include_once( LIBERTY_PKG_PATH.'get_content_list_inc.php' );

// calculate page number
$numPages = ceil( $contentList['cant'] / $gBitSystem->mPrefs['maxRecords'] );
$gBitSmarty->assign( 'numPages', $numPages );

//$gBitSmarty->assign_by_ref('offset', $offset);
$gBitSmarty->assign( 'contentSelect', $contentSelect );
$gBitSmarty->assign( 'contentTypes', $contentTypes );
$gBitSmarty->assign( 'contentList', $contentList['data'] );
$gBitSmarty->assign( 'contentCount', $contentList['cant'] );
// end of content listing

$gBitSystem->display( 'bitpackage:users/my_bitweaver.tpl', 'My '.$gBitSystem->getPreference( 'siteTitle' ) );

?>
