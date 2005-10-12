<?php
$formFeatures = array(
	'feature_tasks' => array(
		'label' => 'User Tasks',
		'note' => 'Make notes of tasks with due date and priority.',
		'page' => 'UserTasks',
	),
	'feature_user_bookmarks' => array(
		'label' => 'User Bookmarks',
		'note' => 'Users can create their own list of private bookmarks.',
		'page' => 'UserBookmarks',
	),
	'feature_userfiles' => array(
		'label' => 'User Files',
		'note' => 'Users can upload private user files.',
		'page' => 'UserFiles',
	),
	'feature_usermenu' => array(
		'label' => 'User menu',
		'note' => 'Users can customise their own menus.',
		'page' => 'UserMenu',
	),
	'feature_userPreferences' => array(
		'label' => 'User Preferences',
		'note' => 'Users can view and modify their personal preferences.',
		'page' => 'UserPreferences',
	),
	'change_language' => array(
		'label' => 'Registered users can change language',
		'note' => 'Allows users to view a translated version of the site.'
	),
	'feature_user_watches' => array(
		'label' => 'User Watches',
		'note' => 'Users can mark pages to be watched. If a watched page is modified, the user is informed.',
		'page' => 'UserWatches',
	),
	'case_sensitive_login' => array(
		'label' => 'Case-Sensitive Login',
		'note' => 'This determines whether user login names are case-sensitive.'
	),
);

if( $gBitSystem->isPackageActive( 'notepad' ) ) {
	$formFeatures['feature_notepad'] = array(
		'label' => 'User Notepad',
		'note' => 'Allow users to make notes.',
		'page' => 'UserNotepad'
	);
}

$gBitSmarty->assign( 'formFeatures', $formFeatures );

if( isset( $_REQUEST['fTiki'] ) ) {
	foreach ( array_keys( $formFeatures ) as $feature) {
		$gBitSystem->storePreference( $feature, (isset( $_REQUEST['fTiki'][$feature][0] ) ? $_REQUEST['fTiki'][$feature][0] : 'n') );
	}

	if( $customFields = explode( ',', $_REQUEST['fTiki']['custom_user_fields'] ) ) {
		trim_array( $customFields );
		$customFields = implode( ',', $customFields );
	}
	$gBitSystem->storePreference( 'custom_user_fields', $customFields );
	$gBitSystem->storePreference( 'display_name', (isset( $_REQUEST['fTiki']['display_name'] ) ? $_REQUEST['fTiki']['display_name'] : 'real_name') );
	$gBitSystem->storePreference( 'feature_user_theme', (isset( $_REQUEST['fTiki']['feature_user_theme'][0] ) ? $_REQUEST['fTiki']['feature_user_theme'][0] : NULL) );
	$gBitSystem->storePreference( 'feature_user_layout', (isset( $_REQUEST['fTiki']['feature_user_layout'][0] ) ? $_REQUEST['fTiki']['feature_user_layout'][0] : NULL) );
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
