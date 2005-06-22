<?php
// $Header: /cvsroot/bitweaver/_bit_users/preferences.php,v 1.2.2.1 2005/06/22 20:11:15 spiderr Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// Initialization
require_once( '../bit_setup_inc.php' );
include_once( KERNEL_PKG_PATH.'mod_lib.php' );
if( $gBitSystem->isPackageActive( 'wiki' ) ) {
	include_once( WIKI_PKG_PATH.'BitPage.php' );
}
if( $gBitSystem->isPackageActive( 'blogs' ) ) {
	include_once( BLOGS_PKG_PATH.'BitBlog.php' );
}
// User preferences screen
if ($feature_userPreferences != 'y') {
	$smarty->assign('msg', tra("This feature is disabled").": feature_userPreferences");
	$gBitSystem->display( 'error.tpl' );
	die;
}
if (empty($gBitUser->mUserId)) {
	$smarty->assign('msg', tra("You are not logged in"));
	$gBitSystem->display( 'error.tpl' );
	die;
}
if( !empty( $_REQUEST["view_user"] ) && $_REQUEST["view_user"] <> $gBitUser->mUserId) {
	$gBitSystem->verifyPermission( 'bit_p_admin_users' );
	$editUser = new BitUser( $_REQUEST["view_user"] );
	$editUser->load( TRUE );
	$smarty->assign('view_user', $_REQUEST["view_user"]);
} else {
	$editUser = &$gBitUser;
}
global $gQueryUserId;
$gQueryUserId = &$editUser->mUserId;

$foo = parse_url($_SERVER["REQUEST_URI"]);
if( $gBitSystem->isPackageActive( 'wiki' ) ) {
	$foo1 = str_replace( USERS_PKG_URL."user_preferences", WIKI_PKG_URL."edit", $foo["path"] );
	$foo2 = str_replace( USERS_PKG_URL."user_preferences", WIKI_PKG_URL."index", $foo["path"] );
	$smarty->assign('url_edit', httpPrefix(). $foo1);
	$smarty->assign('url_visit', httpPrefix(). $foo2);
}
if( !empty( $gBitSystem->mPrefs['custom_user_fields'] ) ) {
	$customFields= explode( ',', $gBitSystem->mPrefs['custom_user_fields']  );
	$smarty->assign('customFields', $customFields);
}

$gBitLanguage->mLanguage = $editUser->getPreference( 'bitlanguage', $gBitLanguage->mLanguage);
$smarty->assign( 'gBitLanguage', $gBitLanguage );
if (isset($_REQUEST["prefs"])) {
	// setting preferences
	//  if (isset($_REQUEST["email"]))  $gBitUser->change_user_email($userwatch,$_REQUEST["email"]);
	if (isset($_REQUEST["real_name"]))
		$editUser->store( $_REQUEST );
	if (isset($_REQUEST["userbreadCrumb"]))
		$editUser->storePreference( 'userbreadCrumb', $_REQUEST["userbreadCrumb"]);
	if (isset($_REQUEST["homePage"]))
		$editUser->storePreference( 'homePage', $_REQUEST["homePage"]);
	if ($change_language == 'y') {
		if (isset($_REQUEST["language"])) {
			$editUser->storePreference( 'bitlanguage', $_REQUEST["language"]);
		}
	}
	if (isset($_REQUEST["style"]))
		$smarty->assign('style', $_REQUEST["style"]);
	if (isset($_REQUEST['display_timezone'])) {
		$editUser->storePreference( 'display_timezone', $_REQUEST['display_timezone']);
		$smarty->assign_by_ref('display_timezone', $_REQUEST['display_timezone']);
	}
	$editUser->storePreference( 'country', $_REQUEST["country"]);
	$editUser->storePreference( 'user_information', $_REQUEST['user_information']);
	if (isset($_REQUEST['user_dbl']) && $_REQUEST['user_dbl'] == 'on') {
		$editUser->storePreference( 'user_dbl', 'y');
		$smarty->assign('user_dbl', 'y');
	} else {
		$editUser->storePreference( 'user_dbl', 'n');
		$smarty->assign('user_dbl', 'n');
	}
	if( isset( $customFields ) && is_array( $customFields ) ) {
		foreach( $customFields as $f ) {
			if( isset( $_REQUEST['CUSTOM'][$f] ) ) {
				$editUser->storePreference( trim( $f ), trim( $_REQUEST['CUSTOM'][$f] ) );
			}
		}
	}

	$email_isPublic = isset($_REQUEST['email_isPublic']) ? $_REQUEST['email_isPublic']: 'n';
	$editUser->storePreference( 'email is public', $email_isPublic);
	if (isset($_REQUEST['view_user'])) {
		header ("location: ".USERS_PKG_URL."preferences.php?view_user=$editUser->mUserId");
	} else {
		header ("location: ".USERS_PKG_URL."preferences.php");
	}
	die;
}
if (isset($_REQUEST['chgemail'])) {

	// check user's password
	if (!$editUser->validate($editUser->mUsername, $_REQUEST['pass'], '', '')) {
		$smarty->assign('msg', tra("Invalid password.  You current password is required to change your email address."));
		$gBitSystem->display( 'error.tpl' );
		die;
	}
	if( $editUser->change_user_email( $editUser->mUserId, $editUser->mUsername, $_REQUEST['email'], $_REQUEST['pass'] ) ) {
		$smarty->assign( 'successMsg', tra( 'Your email address was updated successfully' ) );
	}
}
if (isset($_REQUEST["chgpswd"])) {

	if( $_REQUEST["pass1"] != $_REQUEST["pass2"] ) {
		$gBitSystem->fatalError( tra("The passwords didn't match") );
	}
	if( !$gBitUser->isAdmin() && !$editUser->validate( $editUser->mUsername, $_REQUEST["old"], '', '' ) ) {
		$gBitSystem->fatalError( tra( "Invalid old password" ) );
	}
	//Validate password here
	if (strlen($_REQUEST["pass1"]) < $min_pass_length) {
		$gBitSystem->fatalError( tra("Password should be at least"). ' ' . $min_pass_length . ' ' . tra("characters long") );
	}
	// Check this code
	if ($pass_chr_num == 'y') {
		if (!preg_match_all("/[0-9]+/", $_REQUEST["pass1"], $foo) || !preg_match_all("/[A-Za-z]+/", $_REQUEST["pass1"], $foo)) {
			$gBitSystem->fatalError(tra("Password must contain both letters and numbers") );
		}
	}
	if( $gBitUser->change_user_password($editUser->mUsername, $_REQUEST["pass1"]) ) {
		$smarty->assign( 'successMsg', tra( 'The password was updated successfully' ) );
	}
}
if (isset($_REQUEST['messprefs'])) {

	$editUser->storePreference( 'mess_maxRecords', $_REQUEST['mess_maxRecords']);
	$editUser->storePreference( 'minPrio', $_REQUEST['minPrio']);
	if (isset($_REQUEST['allowMsgs']) && $_REQUEST['allowMsgs'] == 'on') {
		$editUser->storePreference( 'allowMsgs', 'y');
	} else {
		$editUser->storePreference( 'allowMsgs', 'n');
	}
}
if (isset($_REQUEST['mybitweaverprefs'])) {

	if (isset($_REQUEST['mybitweaver_pages']) && $_REQUEST['mybitweaver_pages'] == 'on') {
		$editUser->storePreference( 'mybitweaver_pages', 'y');
	} else {
		$editUser->storePreference( 'mybitweaver_pages', 'n');
	}
	if (isset($_REQUEST['mybitweaver_blogs']) && $_REQUEST['mybitweaver_blogs'] == 'on') {
		$editUser->storePreference( 'mybitweaver_blogs', 'y');
	} else {
		$editUser->storePreference( 'mybitweaver_blogs', 'n');
	}
	if (isset($_REQUEST['mybitweaver_gals']) && $_REQUEST['mybitweaver_gals'] == 'on') {
		$editUser->storePreference( 'mybitweaver_gals', 'y');
	} else {
		$editUser->storePreference( 'mybitweaver_gals', 'n');
	}
	if (isset($_REQUEST['mybitweaver_msgs']) && $_REQUEST['mybitweaver_msgs'] == 'on') {
		$editUser->storePreference( 'mybitweaver_msgs', 'y');
	} else {
		$editUser->storePreference( 'mybitweaver_msgs', 'n');
	}
	if (isset($_REQUEST['mybitweaver_tasks']) && $_REQUEST['mybitweaver_tasks'] == 'on') {
		$editUser->storePreference( 'mybitweaver_tasks', 'y');
	} else {
		$editUser->storePreference( 'mybitweaver_tasks', 'n');
	}
	if (isset($_REQUEST['mybitweaver_items']) && $_REQUEST['mybitweaver_items'] == 'on') {
		$editUser->storePreference( 'mybitweaver_items', 'y');
	} else {
		$editUser->storePreference( 'mybitweaver_items', 'n');
	}
}
if (isset($_REQUEST['tasksprefs'])) {

	$editUser->storePreference( 'tasks_maxRecords', $_REQUEST['tasks_maxRecords']);
	if (isset($_REQUEST['tasks_use_dates']) && $_REQUEST['tasks_use_dates'] == 'on') {
		$editUser->storePreference( 'tasks_use_dates', 'y');
	} else {
		$editUser->storePreference( 'tasks_use_dates', 'n');
	}
}

$tasks_use_dates = $editUser->getPreference( 'tasks_use_dates');
$smarty->assign('tasks_maxRecords', $tasks_maxRecords);
$smarty->assign('tasks_use_dates', $tasks_use_dates);
$mess_maxRecords = $editUser->getPreference( 'mess_maxRecords', 20);
$smarty->assign('mess_maxRecords', $mess_maxRecords);
$allowMsgs = $editUser->getPreference( 'allowMsgs', 'y');
$smarty->assign('allowMsgs', $allowMsgs);
$minPrio = $editUser->getPreference( 'minPrio', 3 );
$smarty->assign('minPrio', $minPrio);
$smarty->assign_by_ref('userInfo', $editUser->mInfo );
$smarty->assign_by_ref('userPrefs', $editUser->mUserPrefs );
$languages = array();
$languages = $gBitLanguage->listLanguages();
$smarty->assign_by_ref('languages', $languages);
// Get user pages
if( $gBitSystem->isPackageActive( 'messu' ) ) {
	$smarty->assign('mybitweaver_msgs', $editUser->getPreference( 'mybitweaver_msgs'), 'y');
}
if( $gBitSystem->isPackageActive( 'wiki' ) ) {
	$smarty->assign('mybitweaver_pages', $editUser->getPreference( 'mybitweaver_pages'), 'y');
	$user_pages = $wikilib->get_user_pages($editUser->mUserId, -1);
	$smarty->assign_by_ref('user_pages', $user_pages);
}
if( $gBitSystem->isPackageActive( 'blogs' ) ) {
	$smarty->assign('mybitweaver_blogs', $editUser->getPreference( 'mybitweaver_blogs'), 'y');
	$user_blogs = $gBlog->list_user_blogs($editUser->mUserId, false);
	$smarty->assign_by_ref('user_blogs', $user_blogs);
}
if( $gBitSystem->isPackageActive( 'imagegals' ) ) {
	$smarty->assign('mybitweaver_gals', $editUser->getPreference( 'mybitweaver_gals'), 'y');
	$user_galleries = $gBitSystem->get_user_galleries($editUser->mUsername, -1);
	$smarty->assign_by_ref('user_galleries', $user_galleries);
}
if( $gBitSystem->isPackageActive( 'trackers' ) ) {
	$smarty->assign('mybitweaver_items', $editUser->getPreference( 'mybitweaver_items'), 'y');
	$user_items = $gBitSystem->get_user_items($editUser->mUsername);
	$smarty->assign_by_ref('user_items', $user_items);
	$smarty->assign('mybitweaver_tasks', $editUser->getPreference( 'mybitweaver_tasks'), 'y');
}

// Get flags here
$flags = array();
$h = opendir( USERS_PKG_PATH.'icons/flags/' );
while ($file = readdir($h)) {
	if (strstr($file, ".gif")) {
		$parts = explode('.', $file);
		$flags[] = $parts[0];
	}
}
closedir ($h);
sort ($flags);

$editUser->mInfo['userbreadCrumb'] = $editUser->getPreference( 'userbreadCrumb', $gBitSystem->getPreference('userbreadCrumb', 4) );
$editUser->mInfo['homePage'] = $editUser->getPreference( 'homePage', '');

$smarty->assign('flags', $flags);
$smarty->assign( 'editUser', $editUser->mInfo );

// Get preferences
//SPIDERKILL $style = $editUser->getPreference( 'theme', $style);
//SPIDERKILL $smarty->assign_by_ref('style', $style);
$real_name = $editUser->mInfo["real_name"];
$country = $editUser->getPreference( 'country', 'Other');
$smarty->assign('country', $country);
$smarty->assign('email_isPublic', $editUser->getPreference( 'email is public', 'n'));
$scramblingMethods = array("n", "strtr", "unicode", "x"); // email_isPublic utilizes 'n'
$smarty->assign_by_ref('scramblingMethods', $scramblingMethods);
$scramblingEmails = array(tra("no"), scrambleEmail($editUser->mInfo['email'], 'strtr'), scrambleEmail($editUser->mInfo['email'], 'unicode')."-".tra("unicode"), scrambleEmail($editUser->mInfo['email'], 'x'));
$smarty->assign_by_ref('scramblingEmails', $scramblingEmails);
$user_information = $editUser->getPreference( 'user_information', 'public');
$smarty->assign('user_information', $user_information);
$user_dbl = $editUser->getPreference( 'user_dbl', 'y');
$smarty->assign('user_dbl', $user_dbl);
//$timezone_options = $gBitSystem->get_timezone_list(true);
//$smarty->assign_by_ref('timezone_options',$timezone_options);
//$server_time = new Date();
$display_timezone = $editUser->getPreference( 'display_timezone', "UTC");
if ($display_timezone != "UTC")
	$display_timezone = "Local";
$smarty->assign_by_ref('display_timezone', $display_timezone);

$gBitSystem->display( 'bitpackage:users/user_preferences.tpl', 'Edit User Preferences');
?>
