<?php
/**
 * assigned_modules
 *
 * @author   spider <spider@steelsun.com>
 * @version  $Revision: 1.11 $
 * @package  users
 * @subpackage  functions
 * @copyright Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * @license Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
 */
// $Header: /cvsroot/bitweaver/_bit_users/assigned_modules.php,v 1.11 2007/04/02 18:55:02 squareing Exp $
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Initialization

global $gEditMode;
$gEditMode = 'layout';

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

vd( 'This page is not functional and will be fixed asap' );

$gBitSystem->verifyPermission('p_tidbits_configure_modules');

if( !$gBitUser->canCustomizeLayout() && !$gBitUser->canCustomizeTheme() ) {
	$gBitSmarty->assign('msg', tra("This feature is disabled").": user layout");
	$gBitSystem->display( 'error.tpl' );
	die;
}

if (!$gBitUser->isRegistered()) {
	$gBitSmarty->assign('msg', tra("Permission denied: You are not logged in"));
	$gBitSystem->display( 'error.tpl' );
	die;
}

include_once(USERS_PKG_PATH.'lookup_user_inc.php');

if ($gQueryUser->mUserId != $gBitUser->mUserId && !$gBitUser->object_has_permission($gBitUser->mUserId, $gQueryUser->mInfo['content_id'], 'bituser', 'p_users_admin')) {
	$gBitSmarty->assign('msg', tra('You do not have permission to edit this user\'s theme'));
	$gBitSystem->display('error.tpl');
	die;
}

$_REQUEST['fLayout'] = HOMEPAGE_LAYOUT; //we hardcode to a single layout for all users.... for now >:-)
if (isset($_REQUEST['fSubmitSetTheme'] ) ) {
	if( $gBitUser->canCustomizeTheme() ) {
		$gQueryUser->storePreference( 'theme', !empty( $_REQUEST["style"] ) ? $_REQUEST["style"] : NULL );
		$assignStyle = $_REQUEST["style"];
	}
} elseif (isset($_REQUEST['fSubmitSetHeading'] ) ) {

	$homeHeader = substr( trim( $_REQUEST['homeHeaderData']), 0, 250 );
	$gQueryUser->storePreference( 'homepage_header', $homeHeader );
} elseif( isset( $_REQUEST["fSubmitAssign"] ) ) {

	$fAssign = &$_REQUEST['fAssign'];
	$fAssign['user_id'] = $gQueryUser->mUserId;
	$fAssign['layout'] = $_REQUEST['fLayout'];
	$gBitThemes->storeLayout( $fAssign );
	$gBitSmarty->assign_by_ref( 'fAssign', $fAssign );
} elseif (isset($_REQUEST["fMove"])) {

	if( isset( $_REQUEST["fMove"] ) && isset( $_REQUEST["fModule"] ) ) {
		switch( $_REQUEST["fMove"] ) {
			case "unassign":
				$gBitThemes->unassignModule( $_REQUEST['fModule'], $gQueryUser->mUserId, $_REQUEST['fLayout'] );
				break;
			case "up":
				$gBitThemes->moduleUp( $_REQUEST['fModule'], $gQueryUser->mUserId, $_REQUEST['fLayout'] );
				break;
			case "down":
				$gBitThemes->moduleDown( $_REQUEST['fModule'], $gQueryUser->mUserId, $_REQUEST['fLayout'] );
				break;
			case "left":
				$gBitThemes->modulePosition( $_REQUEST['fModule'], $gQueryUser->mUserId, $_REQUEST['fLayout'], 'l' );
				break;
			case "right":
				$gBitThemes->modulePosition( $_REQUEST['fModule'], $gQueryUser->mUserId, $_REQUEST['fLayout'], 'r' );
				break;
		}
	}
}
$orders = array();
for ($i = 1; $i < 20; $i++) {
	$orders[] = $i;
}
$gBitSmarty->assign_by_ref('orders', $orders);
$gBitSmarty->assign( 'homeHeaderData', $gQueryUser->getPreference( 'homepage_header' ) );
// get styles
if( $gBitUser->canCustomizeTheme() ) {
	$styles = $gBitThemes->getStyles( NULL, TRUE, TRUE );
	$gBitSmarty->assign_by_ref( 'styles', $styles );
	if(!isset($_REQUEST["style"])){
		$assignStyle = $gQueryUser->getPreference( 'theme' );
	}
	$gBitSmarty->assign( 'assignStyle', $assignStyle );
}
$assignables = $gBitThemes->getAssignableModules();
if (count($assignables) > 0) {
	$gBitSmarty->assign('canassign', 'y');
} else {
	$gBitSmarty->assign('canassign', 'n');
}
$modules = $gBitSystem->getLayout( $gQueryUser->mUserId, HOMEPAGE_LAYOUT, FALSE );
$gBitThemes->generateModuleNames( $modules );
//print_r($modules);
$gBitSmarty->assign_by_ref('assignables', $assignables);
$layoutAreas = array( 'left'=>'l', 'center'=>'c', 'right'=>'r' );
$gBitSmarty->assign_by_ref( 'layoutAreas', $layoutAreas );
$gBitSmarty->assign_by_ref('modules', $modules);
//print_r($modules);

$gBitSystem->display( 'bitpackage:users/user_assigned_modules.tpl', 'Edit Layout');

?>
