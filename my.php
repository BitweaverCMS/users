<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/my.php,v 1.2.2.3 2005/07/13 20:09:17 squareing Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: my.php,v 1.2.2.3 2005/07/13 20:09:17 squareing Exp $
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
	$smarty->assign('customFields', $customFields);
}

// some content specific offsets and pagination settings
if( !empty( $_REQUEST['sort_mode'] ) ) {
	$content_sort_mode = $_REQUEST['sort_mode'];
	$smarty->assign( 'sort_mode', $content_sort_mode );
}

$max_content = $gBitSystem->mPrefs['maxRecords'];
$offset_content = !empty( $_REQUEST['offset'] ) ? $_REQUEST['offset'] : 0;
$smarty->assign( 'curPage', $page = !empty( $_REQUEST['page'] ) ? $_REQUEST['page'] : 1 );
$offset_content = ( $page - 1 ) * $gBitSystem->mPrefs['maxRecords'];

// set the user_id to only display content viewing user
$_REQUEST['user_id'] = $gBitUser->mUserId;

// now that we have all the offsets, we can get the content list
include_once( LIBERTY_PKG_PATH.'get_content_list_inc.php' );

// calculate page number
$numPages = ceil( $contentList['cant'] / $gBitSystem->mPrefs['maxRecords'] );
$smarty->assign( 'numPages', $numPages );

//$smarty->assign_by_ref('offset', $offset);
$smarty->assign( 'contentSelect', $contentSelect );
$smarty->assign( 'contentTypes', $contentTypes );
$smarty->assign( 'contentList', $contentList['data'] );
// end of content listing

$gBitSystem->setBrowserTitle( 'My '.$gBitSystem->getPreference( 'siteTitle' ) );
$gBitSystem->display( 'bitpackage:users/my_bitweaver.tpl');



// none of the below is currently being used by my_bitweaver.tpl - xing

/*
// User preferences screen
if ($feature_userPreferences != 'y') {
	$smarty->assign('msg', tra("This feature is disabled").": feature_userPreferences");
	$gBitSystem->display( 'error.tpl' );
	die;
}
*/

/* Don't think this is needed - could not find Smarty refs to url_edit or url_visit - wolff_borg
$foo = parse_url($_SERVER["REQUEST_URI"]);
$foo1 = str_replace( USERS_PKG_URL."user_preferences", WIKI_PKG_URL."edit", $foo["path"]);
$foo2 = str_replace( USERS_PKG_URL."user_preferences", WIKI_PKG_URL."index", $foo["path"]);
$smarty->assign('url_edit', httpPrefix(). $foo1);
$smarty->assign('url_visit', httpPrefix(). $foo2);
*/
/* none of this is being used in the my_bitweaver.tpl file - xing
if (isset($_REQUEST['messprefs'])) {
	
	$gBitUser->setPreference('mess_maxRecords', $_REQUEST['mess_maxRecords']);
	$gBitUser->setPreference('minPrio', $_REQUEST['minPrio']);
	if (isset($_REQUEST['allowMsgs']) && $_REQUEST['allowMsgs'] == 'on') {
		$gBitUser->setPreference('allowMsgs', 'y');
	} else {
		$gBitUser->setPreference('allowMsgs', 'n');
	}
}
if (isset($_REQUEST['tasksprefs'])) {
	
	$gBitUser->setPreference('tasks_maxRecords', $_REQUEST['tasks_maxRecords']);
	if (isset($_REQUEST['tasks_use_dates']) && $_REQUEST['tasks_use_dates'] == 'on') {
		$gBitUser->setPreference('tasks_use_dates', 'y');
	} else {
		$gBitUser->setPreference('tasks_use_dates', 'n');
	}
}
$tasks_maxRecords = $gBitUser->getPreference('tasks_maxRecords');
$tasks_use_dates = $gBitUser->getPreference('tasks_use_dates');
$smarty->assign('tasks_maxRecords', $tasks_maxRecords);
$smarty->assign('tasks_use_dates', $tasks_use_dates);
$mess_maxRecords = $gBitUser->getPreference('mess_maxRecords', 20);
$smarty->assign('mess_maxRecords', $mess_maxRecords);
$allowMsgs = $gBitUser->getPreference('allowMsgs', 'y');
$smarty->assign('allowMsgs', $allowMsgs);
$minPrio = $gBitUser->getPreference('minPrio', 6);
$smarty->assign('minPrio', $minPrio);
$smarty->assign_by_ref('userinfo', $gBitUser->mInfo);
$styles = array();
$h = opendir( THEMES_PKG_PATH.'styles/' );
while ($file = readdir($h)) {
	if (strstr($file, "css")) {
		$styles[] = $file;
	}
}
closedir ($h);
$smarty->assign_by_ref('styles', $styles);
$languages = array();
$h = opendir( LANGUAGES_PKG_PATH.'lang/' );
while ($file = readdir($h)) {
	if ($file != '.' && $file != '..' && is_dir('lang/' . $file) && strlen($file) == 2) {
		$languages[] = $file;
	}
}
closedir ($h);
$smarty->assign_by_ref('languages', $languages);
// Get user pages
if (isset($_REQUEST["by"]) && ($_REQUEST["by"]=='creator')) $who = 'creator';
*/
/* userwatch isn't working at the moment
if( $gBitSystem->isPackageActive( 'blogs' ) ) {
	require_once( BLOGS_PKG_PATH.'BitBlog.php' );
	$user_blogs = $gBlog->list_user_blogs($userwatch,false);
	$smarty->assign_by_ref('user_blogs', $user_blogs);
}
if( $gBitSystem->isPackageActive( 'wiki') && !empty( $userwatchId ) ) {
	require_once( WIKI_PKG_PATH.'BitPage.php' );
	global $wikilib;
	$user_pages = $wikilib->get_user_pages( $userwatchId, -1 );
	$smarty->assign_by_ref('user_pages', $user_pages);
}
if( $gBitSystem->isPackageActive( 'imagegals' ) ) {
	$user_galleries = $gBitSystem->get_user_galleries($userwatch, -1);
	$smarty->assign_by_ref('user_galleries', $user_galleries);
}
if( $gBitSystem->isPackageActive( 'trackers' ) ) {
	$user_items = $gBitSystem->get_user_items($userwatch);
	$smarty->assign_by_ref('user_items', $user_items);
}
if( $gBitSystem->isPackageActive( 'messu' ) ) {
	require_once( MESSU_PKG_PATH.'messu_lib.php' );
	$msgs = $messulib->list_user_messages($user, 0, -1, 'date_desc', '', 'is_read', 'n');
	$smarty->assign('msgs', $msgs['data']);
}
*/
// Get flags here
/* none of this is being used by my_bitweaver.tpl
$flags = array();
$h = opendir( USERS_PKG_PATH.'icons/flags/' );
while ($file = readdir($h)) {
	if (strstr($file, ".gif")) {
		$parts = explode('.', $file);
		$flags[] = $parts[0];
	}
}
closedir ($h);
$smarty->assign('flags', $flags);
// Get preferences
$gBitLanguage->mLanguage = $gBitUser->getPreference('bitlanguage', $gBitLanguage->mLanguage);
$real_name = $gBitUser->getPreference('real_name', '');
$country = $gBitUser->getPreference('country', 'Other');
$smarty->assign('country', $country);
$anonpref = $gBitUser->getPreference('userbreadCrumb', 4);
$userbreadCrumb = $gBitUser->getPreference('userbreadCrumb', $anonpref);
$smarty->assign_by_ref('real_name', $real_name);
$smarty->assign_by_ref('userbreadCrumb', $userbreadCrumb);
$homePage = $gBitUser->getPreference('homePage', '');
$smarty->assign_by_ref('homePage', $homePage);
//Get tasks
if (isset($_SESSION['thedate'])) {
	$pdate = $_SESSION['thedate'];
} else {
	$pdate = date("U");
}
$tasks_use_dates = $gBitUser->getPreference('tasks_use_dates');
$tasks = $tasklib->list_tasks($gBitUser->mUserId, 0, -1, 'priority_desc', '', $tasks_use_dates, $pdate);
$smarty->assign('tasks', $tasks['data']);
$user_information = $gBitUser->getPreference('user_information', 'public');
$smarty->assign('user_information', $user_information);
$timezone_options = $gBitSystem->get_timezone_list(true);
$smarty->assign_by_ref('timezone_options', $timezone_options);
$server_time = new Date();
$display_timezone = $gBitUser->getPreference('display_timezone', $server_time->tz->getID());
$smarty->assign_by_ref('display_timezone', $display_timezone);
$smarty->assign('mybitweaver_pages', $gBitUser->getPreference('mybitweaver_pages'), 'y');
$smarty->assign('mybitweaver_blogs', $gBitUser->getPreference('mybitweaver_blogs'), 'y');
$smarty->assign('mybitweaver_gals', $gBitUser->getPreference('mybitweaver_gals'), 'y');
$smarty->assign('mybitweaver_items', $gBitUser->getPreference('mybitweaver_items'), 'y');
$smarty->assign('mybitweaver_msgs', $gBitUser->getPreference('mybitweaver_msgs'), 'y');
$smarty->assign('mybitweaver_tasks', $gBitUser->getPreference('mybitweaver_tasks'), 'y');
$section = 'mybitweaver';
include_once ( KERNEL_PKG_PATH.'menu_register_inc.php' );

*/
?>
