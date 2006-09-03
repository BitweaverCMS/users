<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/preferences.php,v 1.37 2006/09/03 08:36:04 jht001 Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: preferences.php,v 1.37 2006/09/03 08:36:04 jht001 Exp $
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
	$gBitSystem->verifyPermission( 'p_users_admin' );
	$userClass = $gBitSystem->getConfig( 'user_class', 'BitPermUser' );
	$editUser = new $userClass( $_REQUEST["view_user"] );
	$editUser->load( TRUE );
	$gBitSmarty->assign('view_user', $_REQUEST["view_user"]);
    $watches = $editUser->getWatches();
    $gBitSmarty->assign('watches', $watches);
} else {
	$gBitUser->load( TRUE );
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

$packages = array();
foreach ($gBitSystem->mPackages as $package) {
	if ($gBitSystem->isPackageActive( $package['name'] )) {
		$php_file = $package['path'].'user_preferences_inc.php';
		$tpl_file = $package['path'].'templates/user_preferences_inc.tpl';
		if (file_exists($tpl_file)) {
			if (file_exists($php_file))  {
				require($php_file);
			}
			$p=array();
			$p['template'] = $tpl_file;
			$packages[] = $p;
		}
	}
}
$gBitSmarty->assign_by_ref('packages',$packages);

$gBitLanguage->mLanguage = $editUser->getPreference( 'bitlanguage', $gBitLanguage->mLanguage);
$gBitSmarty->assign( 'gBitLanguage', $gBitLanguage );
if (isset($_REQUEST["prefs"])) {
	if (isset($_REQUEST["real_name"])) {
		$editUser->store( $_REQUEST );
	}
	if (isset($_REQUEST["users_bread_crumb"])) {
		$editUser->storePreference( 'users_bread_crumb', $_REQUEST["users_bread_crumb"], 'users');
	}
	if (isset($_REQUEST["users_homepage"])) {
		$editUser->storePreference( 'users_homepage', $_REQUEST["users_homepage"], 'users');
	}
	if( $gBitSystem->isFeatureActive( 'users_change_language' ) ) {
		if (isset($_REQUEST["language"])) {
			$editUser->storePreference( 'bitlanguage', $_REQUEST["language"], 'languages');
		}
	}
	if( isset( $_REQUEST["style"] ) ) {
		$gBitSmarty->assign('style', $_REQUEST["style"]);
	}
	if( isset( $_REQUEST['site_display_timezone'] ) ) {
		$editUser->storePreference( 'site_display_timezone', $_REQUEST['site_display_timezone'], 'users');
		$gBitSmarty->assign_by_ref('site_display_timezone', $_REQUEST['site_display_timezone'], 'users');
	}
	$editUser->storePreference( 'users_country', $_REQUEST["users_country"], 'users' );
	$editUser->storePreference( 'users_information', $_REQUEST['users_information'], 'users');
	if( isset($_REQUEST['users_double_click']) && $_REQUEST['users_double_click'] == 'on' ) {
		$editUser->storePreference( 'users_double_click', 'y', 'users');
		$gBitSmarty->assign('users_double_click', 'y');
	} else {
		$editUser->storePreference( 'users_double_click', 'n', 'users');
		$gBitSmarty->assign('users_double_click', 'n');
	}
	if( isset( $customFields ) && is_array( $customFields ) ) {
		foreach( $customFields as $f ) {
			if( isset( $_REQUEST['CUSTOM'][$f] ) ) {
				$editUser->storePreference( trim( $f ), trim( $_REQUEST['CUSTOM'][$f] ), 'users' );
			}
		}
	}

	$users_email_display = isset($_REQUEST['users_email_display']) ? $_REQUEST['users_email_display']: 'n';
	$editUser->storePreference( 'users_email_display', $users_email_display, 'users');
	if (isset($_REQUEST['view_user'])) {
		header ("location: ".USERS_PKG_URL."preferences.php?view_user=$editUser->mUserId");
	} else {
		header ("location: ".USERS_PKG_URL."preferences.php");
	}
	die;
}

if (isset($_REQUEST['chgemail'])) {
	// check user's password
	if( !$gBitUser->hasPermission( 'p_users_admin' ) && !$editUser->validate( $editUser->mUsername, $_REQUEST['pass'], '', '' ) ) {
		$gBitSmarty->assign('msg', tra("Invalid password.  Your current password is required to change your email address."));
		$gBitSystem->display( 'error.tpl' );
		die;
	}
	if( $editUser->change_user_email( $editUser->mUserId, $editUser->mUsername, $_REQUEST['email'], $_REQUEST['pass'] ) ) {
		$gBitSmarty->assign( 'successMsg', tra( 'Your email address was updated successfully' ) );
		#make sure udpated value appears on screen repaint
		$editUser->mInfo['email'] = $_REQUEST['email'];
	}
}
if (isset($_REQUEST["chgpswd"])) {
	if( $_REQUEST["pass1"] != $_REQUEST["pass2"] ) {
		$gBitSystem->fatalError( tra("The passwords didn't match") );
	}
	if( !$gBitUser->hasPermission( 'p_users_admin' ) && !$editUser->validate( $editUser->mUsername, $_REQUEST["old"], '', '' ) ) {
		$gBitSystem->fatalError( tra( "Invalid old password" ) );
	}
	//Validate password here
	$users_min_pass_length = $gBitSystem->getConfig( 'users_min_pass_length', 4 );
	if (strlen($_REQUEST["pass1"]) < $users_min_pass_length ) {
		$gBitSystem->fatalError( tra("Password should be at least"). ' ' . $users_min_pass_length . ' ' . tra("characters long") );
	}
	// Check this code
	if( $gBitSystem->isFeatureActive( 'users_pass_chr_num' ) ) {
		if (!preg_match_all("/[0-9]+/", $_REQUEST["pass1"], $foo) || !preg_match_all("/[A-Za-z]+/", $_REQUEST["pass1"], $foo)) {
			$gBitSystem->fatalError(tra("Password must contain both letters and numbers") );
		}
	}
	if( $editUser->storePassword( $_REQUEST["pass1"] ) ) {
		$gBitSmarty->assign( 'successMsg', tra( 'The password was updated successfully' ) );
	}
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

$editUser->mInfo['users_bread_crumb'] = $editUser->getPreference( 'users_bread_crumb', $gBitSystem->getConfig('users_bread_crumb', 4) );
$editUser->mInfo['users_homepage'] = $editUser->getPreference( 'users_homepage', '');

$gBitSmarty->assign( 'editUser', $editUser->mInfo );

// Get preferences
//SPIDERKILL $style = $editUser->getPreference( 'theme', $style);
//SPIDERKILL $gBitSmarty->assign_by_ref('style', $style);
$real_name = $editUser->mInfo["real_name"];
$gBitSmarty->assign('users_email_display', $editUser->getPreference( 'users_email_display', 'n'));
$scramblingMethods = array("n", "strtr", "unicode", "x"); // users_email_display utilizes 'n'
$gBitSmarty->assign_by_ref('scramblingMethods', $scramblingMethods);
$scramblingEmails = array(tra("no"), scrambleEmail($editUser->mInfo['email'], 'strtr'), scrambleEmail($editUser->mInfo['email'], 'unicode')."-".tra("unicode"), scrambleEmail($editUser->mInfo['email'], 'x'));
$gBitSmarty->assign_by_ref('scramblingEmails', $scramblingEmails);
//$timezone_options = $gBitSystem->get_timezone_list(true);
//$gBitSmarty->assign_by_ref('timezone_options',$timezone_options);
//$server_time = new Date();

$site_display_timezone = $editUser->getPreference( 'site_display_timezone', "UTC");
if ($site_display_timezone != "UTC") {
	$site_display_timezone = "Local";
}

$gBitSmarty->assign_by_ref('site_display_timezone', $site_display_timezone);

$gBitSystem->display( 'bitpackage:users/user_preferences.tpl', 'Edit User Preferences');
?>
