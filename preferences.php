<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/preferences.php,v 1.23 2006/03/01 18:35:20 spiderr Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: preferences.php,v 1.23 2006/03/01 18:35:20 spiderr Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
require_once( '../bit_setup_inc.php' );

if( $gBitSystem->isPackageActive( 'wiki' ) ) {
	include_once( WIKI_PKG_PATH.'BitPage.php' );
}
if( $gBitSystem->isPackageActive( 'blogs' ) ) {
	include_once( BLOGS_PKG_PATH.'BitBlog.php' );
}
// User preferences screen
$gBitSystem->verifyFeature( 'users_preferences' );

if( !$gBitUser->isRegistered() ) {
	$gBitSmarty->assign( 'msg', tra( "You are not logged in" ) );
	$gBitSystem->display( 'error.tpl' );
	die;
}

if( !empty( $_REQUEST["view_user"] ) && $_REQUEST["view_user"] <> $gBitUser->mUserId) {
	$gBitSystem->verifyPermission( 'bit_p_admin_users' );
	$editUser = new BitUser( $_REQUEST["view_user"] );
	$editUser->load( TRUE );
	$gBitSmarty->assign('view_user', $_REQUEST["view_user"]);
} else {
	$editUser = &$gBitUser;
}

global $gQueryUserId;
$gQueryUserId = &$editUser->mUserId;

$foo = parse_url($_SERVER["REQUEST_URI"]);
if( $gBitSystem->isPackageActive( 'wiki' ) ) {
	$foo1 = str_replace( USERS_PKG_URL."user_preferences", WIKI_PKG_URL."edit", $foo["path"] );
	$foo2 = str_replace( USERS_PKG_URL."user_preferences", WIKI_PKG_URL."index", $foo["path"] );
	$gBitSmarty->assign('url_edit', httpPrefix(). $foo1);
	$gBitSmarty->assign('url_visit', httpPrefix(). $foo2);
}
if( $gBitSystem->isFeatureActive( 'custom_user_fields' ) ) {
	$customFields= explode( ',', $gBitSystem->getConfig( 'custom_user_fields' )  );
	$gBitSmarty->assign('customFields', $customFields);
}

if( $gBitSystem->isPackageActive( 'calendar' ) && $gBitSystem->isFeatureActive('calendar_user_prefs') ) {
	include_once( CALENDAR_PKG_PATH.'admin/admin_calendar_inc.php' );
	if( !empty( $_REQUEST['calendar_submit'] ) ) {
		foreach( $calendarValues as $item ) {
			if( !empty( $_REQUEST[$item] ) ) {
				$editUser->storePreference( $item, $_REQUEST[$item], 'calendar' );
			}
		}
	}
}

$gBitLanguage->mLanguage = $editUser->getPreference( 'bitlanguage', $gBitLanguage->mLanguage);
$gBitSmarty->assign( 'gBitLanguage', $gBitLanguage );
if (isset($_REQUEST["prefs"])) {
	// setting preferences
	//  if (isset($_REQUEST["email"]))  $gBitUser->change_user_email($userwatch,$_REQUEST["email"]);
	if (isset($_REQUEST["real_name"]))
		$editUser->store( $_REQUEST );
	if (isset($_REQUEST["userbreadCrumb"]))
		$editUser->storePreference( 'userbreadCrumb', $_REQUEST["userbreadCrumb"], 'users');
	if (isset($_REQUEST["homePage"]))
		$editUser->storePreference( 'homePage', $_REQUEST["homePage"], 'users');
	if (isset($change_language) && $change_language == 'y') {
		if (isset($_REQUEST["language"])) {
			$editUser->storePreference( 'bitlanguage', $_REQUEST["language"], 'languages');
		}
	}
	if (isset($_REQUEST["style"]))
		$gBitSmarty->assign('style', $_REQUEST["style"]);
	if (isset($_REQUEST['display_timezone'])) {
		$editUser->storePreference( 'display_timezone', $_REQUEST['display_timezone'], 'users');
		$gBitSmarty->assign_by_ref('display_timezone', $_REQUEST['display_timezone'], 'users');
	}
	$editUser->storePreference( 'country', $_REQUEST["country"], 'users' );
	$editUser->storePreference( 'user_information', $_REQUEST['user_information'], 'users');
	if (isset($_REQUEST['user_dbl']) && $_REQUEST['user_dbl'] == 'on') {
		$editUser->storePreference( 'user_dbl', 'y', 'users');
		$gBitSmarty->assign('user_dbl', 'y');
	} else {
		$editUser->storePreference( 'user_dbl', 'n', 'users');
		$gBitSmarty->assign('user_dbl', 'n');
	}
	if( isset( $customFields ) && is_array( $customFields ) ) {
		foreach( $customFields as $f ) {
			if( isset( $_REQUEST['CUSTOM'][$f] ) ) {
				$editUser->storePreference( trim( $f ), trim( $_REQUEST['CUSTOM'][$f] ), 'users' );
			}
		}
	}

	$email_isPublic = isset($_REQUEST['email_isPublic']) ? $_REQUEST['email_isPublic']: 'n';
	$editUser->storePreference( 'email is public', $email_isPublic, 'users');
	if (isset($_REQUEST['view_user'])) {
		header ("location: ".USERS_PKG_URL."preferences.php?view_user=$editUser->mUserId");
	} else {
		header ("location: ".USERS_PKG_URL."preferences.php");
	}
	die;
}
if (isset($_REQUEST['chgemail'])) {
	// check user's password
	if (!$gBitUser->hasPermission( 'bit_p_admin_users' ) && !$editUser->validate($editUser->mUsername, $_REQUEST['pass'], '', '')) {
		$gBitSmarty->assign('msg', tra("Invalid password.  Your current password is required to change your email address."));
		$gBitSystem->display( 'error.tpl' );
		die;
	}
	if( $editUser->change_user_email( $editUser->mUserId, $editUser->mUsername, $_REQUEST['email'], $_REQUEST['pass'] ) ) {
		$gBitSmarty->assign( 'successMsg', tra( 'Your email address was updated successfully' ) );
	}
}
if (isset($_REQUEST["chgpswd"])) {
	if( $_REQUEST["pass1"] != $_REQUEST["pass2"] ) {
		$gBitSystem->fatalError( tra("The passwords didn't match") );
	}
	if( !$gBitUser->hasPermission( 'bit_p_admin_users' ) && !$editUser->validate( $editUser->mUsername, $_REQUEST["old"], '', '' ) ) {
		$gBitSystem->fatalError( tra( "Invalid old password" ) );
	}
	//Validate password here
	if (strlen($_REQUEST["pass1"]) < $gBitSystem->getConfig( 'min_pass_length', 4 ) ) {
		$gBitSystem->fatalError( tra("Password should be at least"). ' ' . $min_pass_length . ' ' . tra("characters long") );
	}
	// Check this code
	if( $gBitSystem->isFeatureActive( 'pass_chr_num' ) ) {
		if (!preg_match_all("/[0-9]+/", $_REQUEST["pass1"], $foo) || !preg_match_all("/[A-Za-z]+/", $_REQUEST["pass1"], $foo)) {
			$gBitSystem->fatalError(tra("Password must contain both letters and numbers") );
		}
	}
	if( $gBitUser->change_user_password($editUser->mUsername, $_REQUEST["pass1"]) ) {
		$gBitSmarty->assign( 'successMsg', tra( 'The password was updated successfully' ) );
	}
}
if (isset($_REQUEST['messprefs'])) {
	$editUser->storePreference( 'mess_max_records', $_REQUEST['mess_max_records'], 'users' );
	$editUser->storePreference( 'minPrio', $_REQUEST['minPrio'], 'users' );
	$editUser->storePreference( 'message_alert', !empty( $_REQUEST['message_alert'] ) ? 'y' : 'n', 'users' );
	$editUser->storePreference( 'messages_allow_messages', !empty( $_REQUEST['messages_allow_messages'] ) ? 'y' : 'n', 'users' );
}

if (isset($_REQUEST['tasksprefs'])) {
	$editUser->storePreference( 'tasks_max_records', $_REQUEST['tasks_max_records'], 'users');
	if (isset($_REQUEST['tasks_use_dates']) && $_REQUEST['tasks_use_dates'] == 'on') {
		$editUser->storePreference( 'tasks_use_dates', 'y', 'users');
	} else {
		$editUser->storePreference( 'tasks_use_dates', 'n', 'users');
	}
}

$gBitSmarty->assign_by_ref('userInfo', $editUser->mInfo );
$gBitSmarty->assign_by_ref('userPrefs', $editUser->mPrefs );
$languages = array();
$languages = $gBitLanguage->listLanguages();
$gBitSmarty->assign_by_ref('languages', $languages);

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
$gBitSmarty->assign('flags', $flags);

$editUser->mInfo['userbreadCrumb'] = $editUser->getPreference( 'userbreadCrumb', $gBitSystem->getConfig('userbreadCrumb', 4) );
$editUser->mInfo['homePage'] = $editUser->getPreference( 'homePage', '');

$gBitSmarty->assign( 'editUser', $editUser->mInfo );

// Get preferences
//SPIDERKILL $style = $editUser->getPreference( 'theme', $style);
//SPIDERKILL $gBitSmarty->assign_by_ref('style', $style);
$real_name = $editUser->mInfo["real_name"];
$gBitSmarty->assign('email_isPublic', $editUser->getPreference( 'email is public', 'n'));
$scramblingMethods = array("n", "strtr", "unicode", "x"); // email_isPublic utilizes 'n'
$gBitSmarty->assign_by_ref('scramblingMethods', $scramblingMethods);
$scramblingEmails = array(tra("no"), scrambleEmail($editUser->mInfo['email'], 'strtr'), scrambleEmail($editUser->mInfo['email'], 'unicode')."-".tra("unicode"), scrambleEmail($editUser->mInfo['email'], 'x'));
$gBitSmarty->assign_by_ref('scramblingEmails', $scramblingEmails);
$user_information = $editUser->getPreference( 'user_information', 'public');
$gBitSmarty->assign('user_information', $user_information);
$user_dbl = $editUser->getPreference( 'user_dbl', 'y');
$gBitSmarty->assign('user_dbl', $user_dbl);
//$timezone_options = $gBitSystem->get_timezone_list(true);
//$gBitSmarty->assign_by_ref('timezone_options',$timezone_options);
//$server_time = new Date();
$display_timezone = $editUser->getPreference( 'display_timezone', "UTC");
if ($display_timezone != "UTC")
	$display_timezone = "Local";
$gBitSmarty->assign_by_ref('display_timezone', $display_timezone);

$gBitSystem->display( 'bitpackage:users/user_preferences.tpl', 'Edit User Preferences');
?>
