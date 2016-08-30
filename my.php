<?php
/**
 * my home page
 *
 * @copyright (c) 2004-15 bitweaver.org
 *
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../kernel/setup_inc.php' );

if( !$gBitUser->isRegistered() ) {
	Header( 'Location: '.USERS_PKG_URL.'login.php' );
	die;
}

// custom userfields
if( $gBitSystem->isFeatureActive( 'custom_user_fields' ) ) {
	$customFields= explode( ',', $gBitSystem->getConfig( 'custom_user_fields' )  );
	$gBitSmarty->assign('customFields', $customFields);
}

if( $gBitSystem->isFeatureActive( 'display_users_content_list' ) ) {
	// some content specific offsets and pagination settings
	if( !empty( $_REQUEST['sort_mode'] ) ) {
		$content_sort_mode = $_REQUEST['sort_mode'];
		$gBitSmarty->assign( 'sort_mode', $content_sort_mode );
	}

	$max_content = $gBitSystem->getConfig( 'max_records' );
	$offset_content = !empty( $_REQUEST['offset'] ) ? $_REQUEST['offset'] : 0;
	$gBitSmarty->assign( 'curPage', $page = !empty( $_REQUEST['page'] ) ? $_REQUEST['page'] : 1 );
	$offset_content = ( $page - 1 ) * $gBitSystem->getConfig( 'max_records' );

	// set the user_id to only display content viewing user
	$_REQUEST['user_id'] = $gBitUser->mUserId;

	// now that we have all the offsets, we can get the content list
	include_once( LIBERTY_PKG_PATH.'get_content_list_inc.php' );

	// calculate page number
	$numPages = ceil( $contentListHash['cant'] / $gBitSystem->getConfig( 'max_records' ) );
	$gBitSmarty->assign( 'numPages', $numPages );

	//$gBitSmarty->assignByRef('offset', $offset);
	$gBitSmarty->assign( 'contentSelect', $contentSelect );
	$gBitSmarty->assign( 'contentTypes', $contentTypes );
	$gBitSmarty->assign( 'contentList', $contentList );
	$gBitSmarty->assign( 'contentCount', $contentListHash['cant'] );
	$gBitSmarty->assign( 'listInfo', $contentListHash );
	// end of content listing
}

$gBitSystem->display( 'bitpackage:users/my_bitweaver.tpl', 'My '.$gBitSystem->getConfig( 'site_title' ) , array( 'display_mode' => 'display' ));

?>
