<?php
// $Header$
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See below for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.

require_once( USERS_PKG_PATH.'BaseAuth.php' );

$loginSettings = array(
	'users_create_user_auth' => array(
		'label' => "Propagate Users",
		'type' => "checkbox",
		'note' => "Create a User in all lower Authentication Methods.<br />This won't work for methods in Method 1.",
	),
	'users_allow_register' => array(
		'label' => "Users can register",
		'type' => "checkbox",
		'note' => "Registration is attempted for the lowest level supporting the creation of new users.",
	),
	'send_welcome_email' => array(
		'label' => "Send registration welcome email",
		'type' => "checkbox",
		'note' => "Upon successful registration, this will send the user an email with login information, including their password.",
	),
	'after_reg_url' => array(
		'label' => "After registration url",
		'type' => "text",
		'note' => "Set a url users will be directed to after registration. Default is your site's home page.",
	),
	'users_login_homepage' => array(
		'label' => "After login url",
		'type' => "text",
		'note' => "Set a custom url where users will be directed after logging in. It should not include a leading slash or subdirectory. Default is users/my.php",
	),
	'users_eponymous_groups' => array(
		'label' => "Create a group for each user",
		'type' => "checkbox",
		'note' => "This will create a group for each user with the same name as the user. This might be useful if you want to assign different permission settings to every user.",
	),
	'users_validate_user' => array(
		'label' => "Validate users by email",
		'type' => "checkbox",
		'note' => "Send an email to the user, to validate registration.",
	),
	'users_forgot_pass' => array(
		'label' => "Remind passwords by email",
		'type' => "checkbox",
		'note' => "This will display a 'forgot password' link on the login page and allow users to have their password sent to their registered email address.",
	),
	'users_pass_due' => array(
		'label' => "Password invalid after days",
		'type' => "text",
		'note' => "",
	),
	'users_clear_passwords' => array(
		'label' => "Store plaintext passwords",
		'type' => "checkbox",
		'note' => "Passwords will be visible in the database. If a user requests a password, their password will *not* be reset and simply emailed to them in plain text. This option is less secure, but better suited to sites with a wide variety of users.",
	),
	'users_case_sensitive_login' => array(
		'label' => 'Case-Sensitive Login',
		'type' => "checkbox",
		'note' => 'This determines whether user login names are case-sensitive.'
	),
	'user_password_generator' => array(
		'label' => "Password generator",
		'type' => "checkbox",
		'note' => "Display password generator on registration page that creates secure passwords.",
	),
	'users_pass_chr_num' => array(
		'label' => "Force to use characters <strong>and</strong> numbers in passwords",
		'type' => "checkbox",
		'note' => "",
	),
	'users_min_pass_length' => array(
		'label' => "Minimum password length",
		'type' => "text",
		'note' => "",
	),
	'users_remember_me' => array(
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
	'users_validate_email' => array(
		'label' => "Validate email address",
		'type' => "checkbox",
		'link' => "kernel/admin/index.php?page=server/General Settings",
		'note' => "This feature should be used only when you need the maximum security and should be used with discretion. If a visitor's email server is not responding, they will not be placed into the group specified below for verified emails. If a users email is determined to be invalid (meaning, the server does respond, but negatively) they will not be able to register. You also must have a valid sender email to use this feature.",
	),
);
$gBitSmarty->assign( 'loginSettings', $loginSettings );


if (defined ('ROLE_MODEL') ) {
	$listHash = array( 'sort_mode' => 'role_name_asc' );
	$gBitSmarty->assign( 'roleList', $gBitUser->getAllRoles( $listHash ));
} else {
	$listHash = array( 'sort_mode' => 'group_name_asc' );
	$gBitSmarty->assign('groups', $gBitUser->getAllGroups( $listHash ));
}

if( !function_exists("gd_info" ) ) {
	$gBitSmarty->assign( 'warning', 'PHP GD library is required for this feature (not found on your system)' );
}

if( !empty( $_REQUEST["loginprefs"] ) ) {
	if( !preg_match( "#^/#", $_REQUEST['cookie_path'] ) ) {
		$_REQUEST['cookie_path'] = '/'.$_REQUEST['cookie_path'];
	} elseif( $_REQUEST['cookie_path'] == BIT_ROOT_URL ) {
		$_REQUEST['cookie_path'] = '';
	}

	if( $_REQUEST['cookie_domain'] == $_SERVER["SERVER_NAME"] ) {
		$_REQUEST['cookie_domain'] = '';
	}

	foreach( array_keys( $loginSettings ) as $feature ) {
		if( $loginSettings[$feature]['type'] == 'text' ) {
			simple_set_value( $feature, USERS_PKG_NAME );
		} else {
			simple_set_toggle( $feature, USERS_PKG_NAME );
		}
	}
	simple_set_value( 'users_remember_time', USERS_PKG_NAME );
	simple_set_value( 'users_auth_method', USERS_PKG_NAME );
	simple_set_value( 'users_validate_email_group', USERS_PKG_NAME );

	if( isset( $_REQUEST['registration_group_choice'] ) ) {
		$listHash = array();
		$groupList = $gBitUser->getAllGroups( $listHash );
		$in = array();
		$out = array();
		foreach( $groupList as $gr ) {
			if( $gr['group_id'] == ANONYMOUS_GROUP_ID ) {
				continue;
			}

			// work out if someting has been selected or deselected
			if( $gr['is_public'] == 'y' && !in_array( $gr['group_id'], $_REQUEST['registration_group_choice'] )) {
				$out[] = $gr['group_id'];
			} elseif( $gr['is_public'] != 'y' && in_array( $gr['group_id'], $_REQUEST['registration_group_choice'] )) {
				$in[] = $gr['group_id'];
			}
		}
		if( count( $in ) ) {
			$gBitUser->storeRegistrationChoice( $in, 'y' );
		}
		if( count( $out ) ) {
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
		'label' => "Profile Picture",
		'type' => "checkbox",
		'note' => "Allow users to upload a profile picture.",
	),
	'users_register_require_passcode' => array(
		'label' => "Request passcode to register",
		'type' => "checkbox",
		'note' => "",
	),
	'users_register_passcode' => array(
		'label' => "Passcode",
		'type' => "text",
		'note' => "Enter the Passcode that is required for users to register with your site.",
	),
	'users_random_number_reg' => array(
		'label' => "Use basic captcha to prevent automatic/robot registration",
		'type' => "checkbox",
		'note' => "This will generate an image with a word that the user has to confirm during the registration step.",
	),
	'users_register_recaptcha' => array(
		'label' => "Use advanced reCAPTCHA to prevent automatic/robot registration",
		'type' => "checkbox",
		'note' => "To use reCAPTCHA you must get your API keys from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a> and enter them below.",
	),
	'users_register_recaptcha_public_key' => array(
		'label' => "reCAPTCHA Public Key",
		'type' => "text",
		'note' => "This will be given to you after registering your site with Google",
	),
	'users_register_recaptcha_private_key' => array(
		'label' => "reCAPTCHA Private Key",
		'type' => "text",
		'note' => "This will be given to you after registering your site with Google",
	),
	'users_register_smcaptcha' => array(
		'label' => "Use Solve Media CAPTCHA to prevent automatic/robot registration",
		'type' => "checkbox",
		'note' => "To use Solve Media CAPTCHA you must get your API keys from <a href='https://www.solvemedia.com'>https://www.solvemedia.com</a> and enter them below.",
	),
	'users_register_smcaptcha_c_key' => array(
		'label' => "Solve Media Challenge Key (C-key)",
		'type' => "text",
		'note' => "This will be given to you after registering your site with Solve Media",
	),
	'users_register_smcaptcha_v_key' => array(
		'label' => "Solve Media Verification Key (V-key)",
		'type' => "text",
		'note' => "This will be given to you after registering your site with Solve Media",
	),
	'users_register_smcaptcha_h_key' => array(
		'label' => "Solve Media Authentication Hash Key (H-key)",
		'type' => "text",
		'note' => "This will be given to you after registering your site with Solve Media",
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

$listHash = array();

// This needs to be made more generic so that it picks up all plugins
// Could not see where the 'auth_ldap' was defined in the $options['avail'] array
$options = BaseAuth::getConfig();
if( !empty( $_REQUEST["auth_ldap"] ) ) {
	$option_ldap = $options['avail']['ldap']['options'];
	foreach( array_keys( $option_ldap ) as $feature ) {
		if( $option_ldap[$feature]['type'] == 'text' ) {
			simple_set_value( $feature, USERS_PKG_NAME );
		} else {
			simple_set_toggle( $feature, USERS_PKG_NAME );
		}
	}
}

$gBitSmarty->assign( 'authSettings', BaseAuth::getConfig() );
?>
