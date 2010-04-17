<?php
// $Header: /cvsroot/bitweaver/_bit_users/modules/mod_user_pages.php,v 1.7 2010/04/17 19:45:49 wjames5 Exp $
/**
 * Params:
 * - content_type_guid : if set, show only those content_type_guid's
 * - show_date : if set, show date of last modification
 * @package liberty
 * @subpackage modules
 */


global $gQueryUser, $gBitUser, $module_rows, $module_params, $gLibertySystem, $module_title;


$userId = $gBitUser->mUserId;
if( !empty( $gQueryUser->mUserId ) ) {
	$userId = $gQueryUser->mUserId;
}

if( empty( $module_title ) ) {
	if( !empty( $module_params['content_type_guid'] ) && !empty( $gLibertySystem->mContentTypes[$module_params['content_type_guid']] ) ) {
		$title = tra( "Last Changes" ).': '.$gLibertySystem->getContentTypeName( $module_params['content_type_guid'], TRUE );
		$gBitSmarty->assign( 'contentType', $module_params['content_type_guid'] );
	} else {
		$gBitSmarty->assign( 'contentType', FALSE );
		$title = tra( "Last Changes" );
	}
	$gBitSmarty->assign( 'moduleTitle', $title );
}

if( !empty( $module_params['show_date'] ) ) {
	$gBitSmarty->assign( 'userShowDate' , TRUE );
}

$listHash = array(
	'content_type_guid' => !empty( $module_params['content_type_guid'] ) ? $module_params['content_type_guid'] : NULL,
	'offset' => 0,
	'max_records' => $module_rows,
	'sort_mode' => 'last_modified_desc',
	'user_id' => $userId,
);
$modLastPages = $gBitUser->getContentList( $listHash );
$gBitSmarty->assign_by_ref( 'modLastPages', $modLastPages );
?>

