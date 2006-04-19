<?php
// $Header: /cvsroot/bitweaver/_bit_users/admin/admin_login_inc.php,v 1.15 2006/04/19 13:48:40 squareing Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.

$loginSettings = array(
	'allow_register' => array(
		'label' => "Users can register",
		'type' => "checkbox",
		'note' => "",
	),
	'send_welcome_email' => array(
		'label' => "Send registration welcome email",
		'type' => "checkbox",
		'note' => "Upon successful registration, this will send the user an email with login information, including their password.",
	),
	'eponymous_groups' => array(
		'label' => "Create a group for each user",
		'type' => "checkbox",
		'note' => "This will create a group for each user with the same name as the user. This might be useful if you want to assign different permission settings to every user.",
	),
	'use_register_passcode' => array(
		'label' => "Request passcode to register",
		'type' => "checkbox",
		'note' => "",
	),
	'register_passcode' => array(
		'label' => "Passcode",
		'type' => "text",
		'note' => "Enter the Passcode that is required for users to register with your site.",
	),
	'rnd_num_reg' => array(
		'label' => "Prevent automatic/robot registration",
		'type' => "checkbox",
		'note' => "This will generate a random number as an image, the user has to confirm during the registration step.",
	),
	'validate_user' => array(
		'label' => "Validate users by email",
		'type' => "checkbox",
		'note' => "Send an email to the user, to validate registration.",
	),
	'validate_email' => array(
		'label' => "Validate email address",
		'type' => "checkbox",
		'link' => "kernel/admin/index.php?page=server/General Settings",
		'note' => "This feature should be used only when you need the maximum security and should be used with discretion. If a visitor's email server is not responding, they will not be able to register. You also must have a valid sender email to use this feature.",
	),
	'forgot_pass' => array(
		'label' => "Remind passwords by email",
		'type' => "checkbox",
		'note' => "This will display a 'forgot password' link on the login page and allow users to have their password sent to their registered email address.",
	),
	'pass_due' => array(
		'label' => "Password invalid after days",
		'type' => "text",
		'note' => "",
	),
	'clear_passwords' => array(
		'label' => "Store plaintext passwords",
		'type' => "checkbox",
		'note' => "Passwords will be visible in the database. If a user requests a password, their password will *not* be reset and simply emailed to them in plain text. This option is less secure, but better suited to sites with a wide variety of users.",
	),
	'case_sensitive_login' => array(
		'label' => 'Case-Sensitive Login',
		'type' => "checkbox",
		'note' => 'This determines whether user login names are case-sensitive.'
	),
	'user_password_generator' => array(
		'label' => "Password generator",
		'type' => "checkbox",
		'note' => "Display password generator on registration page that creates secure passwords.",
	),
	'pass_chr_num' => array(
		'label' => "Force to use characters <strong>and</strong> numbers in passwords",
		'type' => "checkbox",
		'note' => "",
	),
	'min_pass_length' => array(
		'label' => "Minimum password length",
		'type' => "text",
		'note' => "",
	),
	'rememberme' => array(
		'label' => "Remember me feature",
		'type' => "checkbox",
		'note' => "Registered users will stay logged even if they close their browser.",
	),
	'cookie_domain' => array(
		'label' => "Remember me domain",
		'type' => "text",
		'note' => "Remember to use a '.' wildcard prefix if you want domain wide cookies.<br />e.g.: <strong>.mysite.com</strong> for a domain called <strong>www.mysite.com</strong>",
	),
	'cookie_path' => array(
		'label' => "Remember me path",
		'type' => "text",
		'note' => "The path '/foo' would match '/foobar' and '/foo/bar.html'",
	),
);
$gBitSmarty->assign( 'loginSettings', $loginSettings );

if( !function_exists("gd_info" ) ) {
	$gBitSmarty->assign( 'warning', 'PHP GD library is required for this feature (not found on your system)' );
}

if( !empty( $_REQUEST["loginprefs"] ) ) {
	if( !preg_match( "#^/#", $_REQUEST['cookie_path'] ) ) {
		$_REQUEST['cookie_path'] = BIT_ROOT_URL;
	}
	foreach( array_keys( $loginSettings ) as $feature ) {
		if( $loginSettings[$feature]['type'] == 'text' ) {
			simple_set_value( $feature, USERS_PKG_NAME );
		} else {
			simple_set_toggle( $feature, USERS_PKG_NAME );
		}
	}
	simple_set_value( 'remembertime', USERS_PKG_NAME );
	simple_set_value( 'auth_method', USERS_PKG_NAME );

	if ( isset( $_REQUEST['registration_group_choice'] ) ) {
		$listHash = array();
		$groupList = $gBitUser->getAllGroups( $listHash );
		$in = array();
		$out = array();
		foreach ( $groupList['data'] as $gr ) {
			if ($gr['group_id'] == -1)
				continue;
			if ( $gr['is_public'] == 'y' && !in_array( $gr['group_id'], $_REQUEST['registration_group_choice'] ) ) // deselect
				$out[] = $gr['group_id'];
			elseif ( $gr['is_public'] != 'y' && in_array( $gr['group_id'], $_REQUEST['registration_group_choice'] ) ) //select
				$in[] = $gr['group_id'];
		}
		if ( count($in) ) {
			$gBitUser->storeRegistrationChoice( $in, 'y' );
		}
		if ( count($out) ) {
			$gBitUser->storeRegistrationChoice( $out, NULL );
		}
	}
}

$registerSettings = array(
	'reg_real_name' => array(
		'label' => "Real Name",
		'type' => "checkbox",
		'note' => "Allow users to supply their real name.",
	),
	'reg_country' => array(
		'label' => "Country",
		'type' => "checkbox",
		'note' => "Allow users to pick country of residency.",
	),
	'reg_language' => array(
		'label' => "Language",
		'type' => "checkbox",
		'note' => "Allow users to select their preferred language.",
	),
	'reg_homepage' => array(
		'label' => "Homepage URL",
		'type' => "checkbox",
		'note' => "Allow users to enter the url to their own homepage.",
	),
	'reg_portrait' => array(
		'label' => "Self Portrait",
		'type' => "checkbox",
		'note' => "Allow users to upload a self portrait.",
	),
);
$gBitSmarty->assign( 'registerSettings', $registerSettings );

if( !empty( $_REQUEST["registerprefs"] ) ) {
	foreach( array_keys( $registerSettings ) as $feature ) {
		if( $registerSettings[$feature]['type'] == 'text' ) {
			simple_set_value( $feature, USERS_PKG_NAME );
		} else {
			simple_set_toggle( $feature, USERS_PKG_NAME );
		}
	}
}

$httpSettings = array(
	'site_https_login' => array(
		'label' => "Allow secure (https) login",
		'type' => "checkbox",
		'note' => "",
	),
	'site_https_login_required' => array(
		'label' => "Require secure (https) login",
		'type' => "checkbox",
		'note' => "",
	),
	'site_http_domain' => array(
		'label' => "HTTP server name",
		'type' => "text",
		'note' => "",
	),
	'site_http_port' => array(
		'label' => "HTTP port",
		'type' => "text",
		'note' => "",
	),
	'site_http_prefix' => array(
		'label' => "HTTP URL prefix",
		'type' => "text",
		'note' => "",
	),
	'site_https_domain' => array(
		'label' => "HTTPS server name",
		'type' => "text",
		'note' => "",
	),
	'site_https_port' => array(
		'label' => "HTTPS port",
		'type' => "text",
		'note' => "",
	),
	'site_https_prefix' => array(
		'label' => "HTTPS URL prefix",
		'type' => "text",
		'note' => "",
	),
);
$gBitSmarty->assign( 'httpSettings', $httpSettings );

if( !empty( $_REQUEST["httpprefs"] ) ) {
	foreach( array_keys( $httpSettings ) as $feature ) {
		if( $httpSettings[$feature]['type'] == 'text' ) {
			simple_set_value( $feature, USERS_PKG_NAME );
		} else {
			simple_set_toggle( $feature, USERS_PKG_NAME );
		}
	}
}

$ldapSettings = array(
	'auth_create_gBitDbUser' => array(
		'label' => "Create user if not in bitweaver",
		'type' => "checkbox",
		'note' => "",
	),
	'auth_create_user_auth' => array(
		'label' => "Create user if not in Auth",
		'type' => "checkbox",
		'note' => "",
	),
	'auth_skip_admin' => array(
		'label' => "Just use bitweaver auth for admin",
		'type' => "checkbox",
		'note' => "",
	),
	'auth_ldap_host' => array(
		'label' => "LDAP Host",
		'type' => "text",
		'note' => "",
	),
	'auth_ldap_port' => array(
		'label' => "LDAP Port",
		'type' => "text",
		'note' => "",
	),
	'auth_ldap_basedn' => array(
		'label' => "LDAP Base DN",
		'type' => "text",
		'note' => "",
	),
	'auth_ldap_userdn' => array(
		'label' => "LDAP User DN",
		'type' => "text",
		'note' => "",
	),
	'auth_ldap_userattr' => array(
		'label' => "LDAP User Attribute",
		'type' => "text",
		'note' => "",
	),
	'auth_ldap_useroc' => array(
		'label' => "LDAP User OC",
		'type' => "text",
		'note' => "",
	),
	'auth_ldap_groupdn' => array(
		'label' => "LDAP Group DN",
		'type' => "text",
		'note' => "",
	),
	'auth_ldap_groupattr' => array(
		'label' => "LDAP Group Atribute",
		'type' => "text",
		'note' => "",
	),
	'auth_ldap_groupoc' => array(
		'label' => "LDAP Group OC",
		'type' => "text",
		'note' => "",
	),
	'auth_ldap_memberattr' => array(
		'label' => "LDAP Member Attribute",
		'type' => "text",
		'note' => "",
	),
	'auth_ldap_memberisdn' => array(
		'label' => "LDAP Member Is DN",
		'type' => "text",
		'note' => "",
	),
	'auth_ldap_adminuser' => array(
		'label' => "LDAP Admin User",
		'type' => "text",
		'note' => "",
	),
	'auth_ldap_adminpass' => array(
		'label' => "LDAP Admin Pwd",
		'type' => "password",
		'note' => "",
	),
);
$gBitSmarty->assign( 'ldapSettings', $ldapSettings );

if( !empty( $_REQUEST["auth_pear"] ) ) {
	foreach( array_keys( $ldapSettings ) as $feature ) {
		if( $ldapSettings[$feature]['type'] == 'text' ) {
			simple_set_value( $feature, USERS_PKG_NAME );
		} else {
			simple_set_toggle( $feature, USERS_PKG_NAME );
		}
	}
}
$listHash = array();
$groupList = $gBitUser->getAllGroups($listHash);
$gBitSmarty->assign_by_ref('groupList', $groupList['data']);

?>
