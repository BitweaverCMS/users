<?php
$formFeatures = array(
	'users_preferences' => array(
			'label' => 'User Preferences',
			'note' => 'Users can view and modify their personal preferences.',
			'page' => 'UserPreferences',
	),
	'change_language' => array(
			'label' => 'Registered users can change language',
			'note' => 'Allows users to view a translated version of the site.'
	),
	'user_watches' => array(
			'label' => 'User Watches',
			'note' => 'Users can mark pages to be watched. If a watched page is modified, the user is informed.',
			'page' => 'UserWatches',
	),
	'display_users_content_list' => array(
			'label' => 'Display User\'s Content',
			'note' => 'Display listing of all content owned by this user on the user My page.',
			'page' => 'UserContentList',
	),
);


$gBitSmarty->assign( 'formFeatures', $formFeatures );

if( isset( $_REQUEST['settings'] ) ) {
	foreach ( array_keys( $formFeatures ) as $feature) {
		$gBitSystem->storePreference( $feature, (isset( $_REQUEST['settings'][$feature][0] ) ? $_REQUEST['settings'][$feature][0] : 'n'), USERS_PKG_NAME );
	}

	if( $customFields = explode( ',', $_REQUEST['settings']['custom_user_fields'] ) ) {
		trim_array( $customFields );
		$customFields = implode( ',', $customFields );
	}
	$gBitSystem->storePreference( 'custom_user_fields', $customFields, USERS_PKG_NAME );
	$gBitSystem->storePreference( 'display_name', (isset( $_REQUEST['settings']['display_name'] ) ? $_REQUEST['settings']['display_name'] : 'real_name'), USERS_PKG_NAME );
	$gBitSystem->storePreference( 'users_themes', (isset( $_REQUEST['settings']['users_themes'][0] ) ? $_REQUEST['settings']['users_themes'][0] : NULL), USERS_PKG_NAME );
	$gBitSystem->storePreference( 'users_layouts', (isset( $_REQUEST['settings']['users_layouts'][0] ) ? $_REQUEST['settings']['users_layouts'][0] : NULL), USERS_PKG_NAME );
}

// Handle Admin Password Change Request
// doesn't seem to be working at the moment
if (isset($_REQUEST["newadminpass"]) ) {
	if ($_REQUEST["adminpass"] <> $_REQUEST["again"]) {
		$gBitSmarty->assign("msg", tra("The passwords don't match"));

		$gBitSystem->display( 'error.tpl' );
		die;
	}

	// Validate password here
	if (strlen($_REQUEST["adminpass"]) < $min_pass_length) {
		$text = tra("Password should be at least");

		$text .= " " . $min_pass_length . " ";
		$text .= tra("characters long");
		$gBitSmarty->assign("msg", $text);
		$gBitSystem->display( 'error.tpl' );
		die;
	}

	$gBitUser->change_user_password("admin", $_REQUEST["adminpass"]);
	$gBitSmarty->assign('pagetop_msg', tra("Your admin password has been changed"));
}


?>
