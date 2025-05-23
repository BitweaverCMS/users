<?php
// $Header$
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See below for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.

require_once( USERS_PKG_CLASS_PATH.'BaseAuth.php' );

$loginSettings = array(
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
	'users_forgot_pass' => array(
		'label' => "Remind passwords by email",
		'type' => "checkbox",
		'note' => "This will display a 'forgot password' link on the login page and allow users to have their password sent to their registered email address.",
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
);
$gBitSmarty->assign( 'loginSettings', $loginSettings );

$registerSettings = array(
	'users_validate_user' => array(
		'label' => "Validate users by email",
		'type' => "checkbox",
		'note' => "Send an email to the user, to validate registration.",
	),
	'users_validate_email' => array(
		'label' => "Validate email address",
		'type' => "checkbox",
		'link' => "kernel/admin/index.php?page=server/General Settings",
		'note' => "This feature should be used only when you need the maximum security and should be used with discretion. If a visitor's email server is not responding, they will not be placed into the group specified below for verified emails. If a users email is determined to be invalid (meaning, the server does respond, but negatively) they will not be able to register. You also must have a valid sender email to use this feature.",
	),
	'users_case_sensitive_login' => array(
		'label' => 'Case-Sensitive Login',
		'type' => "checkbox",
		'note' => 'This determines whether user login names are case-sensitive.'
	),
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
	'users_pass_due' => array(
		'label' => "Password invalid after days",
		'type' => "text",
		'note' => "",
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
	'user_password_generator' => array(
		'label' => "Password generator",
		'type' => "checkbox",
		'note' => "Display password generator on registration page that creates secure passwords.",
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
	'users_register_recaptcha_site_key' => array(
		'label' => "reCAPTCHA Site Key",
		'type' => "text",
		'note' => "This will be given to you after registering your site with Google",
	),
	'users_register_recaptcha_secret_key' => array(
		'label' => "reCAPTCHA Secret Key",
		'type' => "text",
		'note' => "This will be given to you after registering your site with Google",
	),
	'users_register_cfturnstile' => array(
		'label' => "Use Cloudflare Turnstile to prevent automatic/robot registration",
		'type' => "checkbox",
		'note' => "To use Cloudflare Turnstile you must get your API keys from <a href='https://www.cloudflare.com/application-services/products/turnstile/'>https://www.solvemedia.com</a> and enter them below.",
	),
	'users_register_cfturnstile_site_key' => array(
		'label' => "Cloudflare Site Key",
		'type' => "text",
		'note' => "This will be given to you after registering your site with Cloudflare",
	),
	'users_register_cfturnstile_secret_key' => array(
		'label' => "Cloudflare Secret Key",
		'type' => "text",
		'note' => "This will be given to you after registering your site with Cloudflare",
	),
);
$gBitSmarty->assign( 'registerSettings', $registerSettings );

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

$listHash = array( 'sort_mode' => 'group_name_asc' );
$gBitSmarty->assign('groups', $gBitUser->getAllGroups( $listHash ));

if( !function_exists("gd_info" ) ) {
	$gBitSmarty->assign( 'warning', 'PHP GD library is required for this feature (not found on your system)' );
}

require_once( USERS_PKG_CLASS_PATH.'BitHybridAuthManager.php' );
BitHybridAuthManager::loadSingleton();
global $gBitHybridAuthManager;

if( !empty( $_POST ) ) {
	// Save all HybridAuth Single Sign On configuration
	if( !empty( $_REQUEST['hybridauth'] ) ) {
		$allAuthProviders = $gBitHybridAuthManager->getAllProviders();
		// make sure all (un)checkboxes stick
		foreach( $allAuthProviders as $providerKey=>$providerConfig ) {
			$enabledConfig = $gBitHybridAuthManager->getEnabledConfigKey( $providerConfig['provider'] );
			$gBitSystem->storeConfig( $enabledConfig, BitBase::getParameter( $_REQUEST['hybridauth'], $enabledConfig, NULL ) );
		}
		foreach( $_REQUEST['hybridauth'] as $prefName=>$prefValue ) {
			if( $prefName == 'users_ha_facebook_scope' ) {
				$prefName = preg_replace('/\s+/', '', $prefName );
			}
			$gBitSystem->storeConfig( $prefName, (!empty( $prefValue ) ? $prefValue : NULL ) );
		}
		$gBitHybridAuthManager->clearFromCache();
	}

	// Save all preferences
	foreach( array( 'loginprefs'=>'loginSettings', 'registerprefs'=>'registerSettings', 'httpprefs'=>'httpSettings' ) as $prefGroup=>$prefHash ) {
		$settings = $$prefHash;
		foreach( array_keys( $settings ) as $feature ) {
			if( $settings[$feature]['type'] == 'text' ) {
				simple_set_value( $feature, USERS_PKG_NAME );
			} else {
				simple_set_toggle( $feature, USERS_PKG_NAME );
			}
		}
	}

	if( !preg_match( "#^/#", $_REQUEST['cookie_path'] ) ) {
		$_REQUEST['cookie_path'] = '/'.$_REQUEST['cookie_path'];
	} elseif( $_REQUEST['cookie_path'] == BIT_ROOT_URL ) {
		$_REQUEST['cookie_path'] = '';
	}

	if( $_REQUEST['cookie_domain'] == $_SERVER["SERVER_NAME"] ) {
		$_REQUEST['cookie_domain'] = '';
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
	$gBitSystem->clearFromCache();
}

$gBitSmarty->assign( 'hybridProviders', $gBitHybridAuthManager->getAllProviders() );


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
