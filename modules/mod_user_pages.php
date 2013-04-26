<?php
// $Header$
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
		$_template->tpl_vars['contentType'] = new Smarty_variable( $module_params['content_type_guid'] );
	} else {
		$_template->tpl_vars['contentType'] = new Smarty_variable( FALSE );
		$title = tra( "Last Changes" );
	}
	$_template->tpl_vars['moduleTitle'] = new Smarty_variable( $title );
}

if( !empty( $module_params['show_date'] ) ) {
	$_template->tpl_vars['userShowDate'] = new Smarty_variable(  TRUE  );
}

$listHash = array(
	'content_type_guid' => !empty( $module_params['content_type_guid'] ) ? $module_params['content_type_guid'] : NULL,
	'offset' => 0,
	'max_records' => $module_rows,
	'sort_mode' => 'last_modified_desc',
	'user_id' => $userId,
);
$modLastPages = $gBitUser->getContentList( $listHash );
$_template->tpl_vars['modLastPages'] = new Smarty_variable( $modLastPages );
?>

