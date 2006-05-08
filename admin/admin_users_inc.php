<?php
$formFeatures = array(
	'users_preferences' => array(
			'label' => 'User Preferences',
			'note' => 'Users can view and modify their personal preferences.',
			'page' => 'UserPreferences',
	),
	'users_change_language' => array(
			'label' => 'Registered users can change language',
			'note' => 'Allows users to view a translated version of the site.'
	),
	'users_watches' => array(
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
		$gBitSystem->storeConfig( $feature, (isset( $_REQUEST['settings'][$feature][0] ) ? $_REQUEST['settings'][$feature][0] : 'n'), USERS_PKG_NAME );
	}

	if( $customFields = explode( ',', $_REQUEST['settings']['custom_user_fields'] ) ) {
		trim_array( $customFields );
		$customFields = implode( ',', $customFields );
	}
	$gBitSystem->storeConfig( 'custom_user_fields', $customFields, USERS_PKG_NAME );
	$gBitSystem->storeConfig( 'users_display_name', (isset( $_REQUEST['settings']['users_display_name'] ) ? $_REQUEST['settings']['users_display_name'] : 'real_name'), USERS_PKG_NAME );
	$gBitSystem->storeConfig( 'users_themes', (isset( $_REQUEST['settings']['users_themes'][0] ) ? $_REQUEST['settings']['users_themes'][0] : NULL), USERS_PKG_NAME );
	$gBitSystem->storeConfig( 'users_layouts', (isset( $_REQUEST['settings']['users_layouts'][0] ) ? $_REQUEST['settings']['users_layouts'][0] : NULL), USERS_PKG_NAME );
}

?>
