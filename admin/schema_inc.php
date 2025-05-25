<?php

$tables = array(

'users_users' => "
  user_id I4 PRIMARY,
  content_id I4,
  email C(200),
  login C(40),
  real_name C(64),
  user_password C(32),
  provpass C(32),
  provpass_expires I8,
  default_group_id I4,
  last_login I8,
  current_login I8,
  registration_date I8 NOTNULL,
  registration_ip C(32) NOTNULL,
  challenge C(32),
  pass_due I8,
  hash C(32),
  created I8,
  avatar_attachment_id I4,
  portrait_attachment_id I4,
  logo_attachment_id I4
  CONSTRAINT	', CONSTRAINT `users_avatar_attach_ref` FOREIGN KEY (`avatar_attachment_id`) REFERENCES `".BIT_DB_PREFIX."liberty_attachments` (`attachment_id`)
				 , CONSTRAINT `users_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content` (`content_id`)
				 , CONSTRAINT `users_portrait_attach_ref` FOREIGN KEY (`portrait_attachment_id`) REFERENCES `".BIT_DB_PREFIX."liberty_attachments` (`attachment_id`)
				 , CONSTRAINT `users_logo_attach_ref` FOREIGN KEY (`logo_attachment_id`) REFERENCES `".BIT_DB_PREFIX."liberty_attachments` (`attachment_id`)'
",

'users_auth_map' => "
  user_id I4 PRIMARY,
  provider C(64) PRIMARY,
  provider_identifier C(64) NOTNULL,
  last_login I8,
  profile_json X
  CONSTRAINT ', CONSTRAINT `users_auth_user_ref` FOREIGN KEY (`user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)'
",

'users_favorites_map' => "
  favorite_content_id I4 PRIMARY,
  user_id I4 PRIMARY,
  map_position I4
  CONSTRAINT ', CONSTRAINT `users_fav_user_ref` FOREIGN KEY (`user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)
			  , CONSTRAINT `users_fav_con_ref` FOREIGN KEY (`favorite_content_id`) REFERENCES `".BIT_DB_PREFIX."liberty_content` (`content_id`)'
			 '
",

'users_permissions' => "
  perm_name C(30) PRIMARY,
  perm_desc C(250),
  perm_level C(80),
  package C(100)
",

'users_groups' => "
  group_id I4 PRIMARY,
  user_id I4 NOTNULL,
  group_name C(30),
  is_default C(1),
  group_desc C(255),
  group_home C(255),
  is_public C(1),
  after_registration_page C(255)
  CONSTRAINT ', CONSTRAINT `users_groups_user_ref` FOREIGN KEY (`user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)'
",

'users_group_permissions' => "
  group_id I4 PRIMARY,
  perm_name C(30) PRIMARY,
  perm_value C(1) default ''
  CONSTRAINT ', CONSTRAINT `users_group_perm_group_ref` FOREIGN KEY (`group_id`) REFERENCES `".BIT_DB_PREFIX."users_groups` (`group_id`)
 				, CONSTRAINT `users_group_perm_perm_ref` FOREIGN KEY (`perm_name`) REFERENCES `".BIT_DB_PREFIX."users_permissions` (`perm_name`)'
",

'users_groups_map' => "
  user_id I4 PRIMARY,
  group_id I4 PRIMARY
  CONSTRAINT ', CONSTRAINT `users_groups_map_group_ref` FOREIGN KEY (`group_id`) REFERENCES `".BIT_DB_PREFIX."users_groups` (`group_id`)
  				, CONSTRAINT `users_groups_map_user_ref` FOREIGN KEY (`user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)'
",

'users_cnxn' => "
  user_id I4,
  cookie C(64),
  ip C(39),
  last_get I8,
  connect_time I8,
  get_count I8,
  user_agent C(128),
  assume_user_id I4,
  current_view X
  CONSTRAINT ', CONSTRAINT `users_cnxn_user_ref` FOREIGN KEY (`user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)'
",

'users_watches' => "
  user_id I4 PRIMARY,
  event C(40) PRIMARY,
  object C(120) PRIMARY,
  hash C(32),
  title C(250),
  watch_type C(200),
  url C(250),
  email C(200)
  CONSTRAINT ', CONSTRAINT `users_watches_user_ref` FOREIGN KEY (`user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)'
",

);

global $gBitInstaller;
foreach( array_keys( $tables ) AS $tableName ) {
	$gBitInstaller->registerSchemaTable( USERS_PKG_NAME, $tableName, $tables[$tableName], TRUE );
}

$indices = array (
	'users_users_email_idx' => array( 'table' => 'users_users', 'cols' => 'email', 'opts' => array('UNIQUE') ),
	'users_auth_user_idx' => array( 'table' => 'users_auth_map', 'cols' => 'user_id', 'opts' => NULL ),
	'users_auth_provider_ident_idx' => array( 'table' => 'users_auth_map', 'cols' => 'provider,provider_identifier', 'opts' => array('UNIQUE') ),
	'users_users_login_idx' => array( 'table' => 'users_users', 'cols' => 'login', 'opts' => array('UNIQUE') ),
	'users_users_avatar_atment_idx' => array( 'table' => 'users_users', 'cols' => 'avatar_attachment_id', 'opts' => NULL ),
	'users_fav_con_idx' => array( 'table' => 'users_favorites_map', 'cols' => 'favorite_content_id', 'opts' => NULL ),
	'users_fav_user_idx' => array( 'table' => 'users_favorites_map', 'cols' => 'user_id', 'opts' => NULL ),
	'users_groups_user_idx' => array( 'table' => 'users_groups', 'cols' => 'user_id', 'opts' => NULL ),
	'users_groups_user_name_idx' => array( 'table' => 'users_groups', 'cols' => 'user_id,group_name', 'opts' => array('UNIQUE') ),
	'users_group_perm_group_idx' => array( 'table' => 'users_group_permissions', 'cols' => 'group_id', 'opts' => NULL ),
	'users_group_perm_perm_idx' => array( 'table' => 'users_group_permissions', 'cols' => 'perm_name', 'opts' => NULL ),
	'users_groups_map_user_idx' => array( 'table' => 'users_groups_map', 'cols' => 'user_id', 'opts' => NULL ),
	'users_groups_map_group_idx' => array( 'table' => 'users_groups_map', 'cols' => 'group_id', 'opts' => NULL )
);
$gBitInstaller->registerSchemaIndexes( USERS_PKG_NAME, $indices );

$gBitInstaller->registerPackageInfo( USERS_PKG_NAME, array(
	'description' => "The users package contains all user information and gives you the possiblity to assign permissions to users.",
	'license' => '<a href="http://www.gnu.org/licenses/licenses.html#LGPL">LGPL</a>',
) );

// ### Sequences
$sequences = array (
	'users_users_user_id_seq' => array( 'start' => 2 ),
	'users_groups_id_seq' => array( 'start' => 4 )
);
$gBitInstaller->registerSchemaSequences( USERS_PKG_NAME, $sequences );

// ### Default Preferences
$gBitInstaller->registerPreferences( USERS_PKG_NAME, array(
	array(USERS_PKG_NAME,'users_auth_method','tiki'),
	array(USERS_PKG_NAME,'users_allow_register','y'),
	array(USERS_PKG_NAME,'users_forgot_pass','y'),
	//array(USERS_PKG_NAME,'users_eponymous_groups','n'),
	//array(USERS_PKG_NAME,'users_pass_chr_num','n'),
	array(USERS_PKG_NAME,'users_pass_due','999'),
	//array(USERS_PKG_NAME,'users_register_passcode',''),
	array(USERS_PKG_NAME,'users_remember_me','y'),
	array(USERS_PKG_NAME,'users_remember_time','86400'),
	//array(USERS_PKG_NAME,'users_random_number_reg','n'),
	//array(USERS_PKG_NAME,'users_register_passcode','n'),
	//array(USERS_PKG_NAME,'users_validate_user','n'),
	//array(USERS_PKG_NAME,'users_validate_email','n'),
	array(USERS_PKG_NAME,'users_min_pass_length','4'),
	//array(USERS_PKG_NAME,'users_custom_home','n'),
	//array(USERS_PKG_NAME,'user_bookmarks','n'),
	array(USERS_PKG_NAME,'users_preferences','y'),
	array(USERS_PKG_NAME,'users_display_name','real_name'),
	array(USERS_PKG_NAME,'users_change_language','y'),
	array(USERS_PKG_NAME,'users_case_sensitive_login','y'),
	//array(USERS_PKG_NAME, 'users_watches','n'),
) );

// ### Default Permissions
$gBitInstaller->registerUserPermissions( USERS_PKG_NAME, array(
	array('p_users_create_bookmarks', 'Can create user bookmarksche user bookmarks', 'registered', USERS_PKG_NAME),
	array('p_users_admin', 'Can edit the information for other users', 'admin', USERS_PKG_NAME),
	array('p_users_view_icons_and_tools', 'Can view tab and tool links', 'basic', USERS_PKG_NAME),
	array('p_users_view_user_list', 'Can view list of registered users', 'editors', USERS_PKG_NAME),
	array('p_users_view_user_homepage', 'Can view personalized homepages', 'basic', USERS_PKG_NAME),
	array('p_users_edit_user_homepage', 'Can create and display a personalized homepage', 'editors', USERS_PKG_NAME),
	array('p_users_bypass_captcha', 'Can bypass spam validation mechanisms', 'registered', USERS_PKG_NAME),
) );

// Package Requirements
$gBitInstaller->registerRequirements( USERS_PKG_NAME, array(
	'liberty'   => array( 'min' => '2.1.4' ),
	'kernel'    => array( 'min' => '2.0.0' ),
	'themes'    => array( 'min' => '2.0.0' ),
	'languages' => array( 'min' => '2.0.0' ),
));
