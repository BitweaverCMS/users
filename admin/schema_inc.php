<?php

$tables = array(

'sessions' => "
  expiry I8 unsigned NOTNULL,
  sesskey C(32) NOTNULL,
  expireref C(64),
  session_data X
",

'users_users' => "
  user_id I4 PRIMARY,
  content_id I4,
  email C(200),
  login C(40),
  real_name C(64),
  password C(32),
  provpass C(32),
  default_group_id I4,
  last_login I8,
  current_login I8,
  registration_date I8 NOTNULL,
  challenge C(32),
  pass_due I8,
  hash C(32),
  created I8,
  avatar_attachment_id I4,
  portrait_attachment_id I4,
  logo_attachment_id I4", /* temporarily removed do to indterminate scan order
  CONSTRAINT	', CONSTRAINT `tiki_avatar_attach_ref` FOREIGN KEY (`avatar_attachment_id`) REFERENCES `".BIT_DB_PREFIX."tiki_attachments` (`attachment_id`)
			 , CONSTRAINT `tiki_users_content_ref` FOREIGN KEY (`content_id`) REFERENCES `".BIT_DB_PREFIX."tiki_content` (`content_id`)
			 , CONSTRAINT `tiki_portrait_attach_ref` FOREIGN KEY (`portrait_attachment_id`) REFERENCES `".BIT_DB_PREFIX."tiki_attachments` (`attachment_id`)
			 , CONSTRAINT `tiki_logo_attach_ref` FOREIGN KEY (`logo_attachment_id`) REFERENCES `".BIT_DB_PREFIX."tiki_attachments` (`attachment_id`)'
",*/

'tiki_sessions' => "
  session_id C(32) PRIMARY,
  user_id I4,
  timestamp I8
",

'tiki_user_bookmarks_urls' => "
  url_id I4 AUTO PRIMARY,
  name C(30),
  url C(250),
  data X,
  last_updated I8,
  folder_id I4 NOTNULL,
  user_id I4 NOTNULL
  CONSTRAINTS	', CONSTRAINT `tiki_user_bookmarks_user_ref` FOREIGN KEY (`user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)'
",

'tiki_user_menus' => "
  menu_id I4 AUTO PRIMARY,
  user_id I4 NOTNULL,
  url C(250),
  name C(40),
  position I4,
  mode C(1)
  CONSTRAINTS	', CONSTRAINT `tiki_user_menus_user_ref` FOREIGN KEY (`user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)'
",

'tiki_user_tasks' => "
  task_id I4 AUTO PRIMARY,
  user_id I4 NOTNULL,
  title C(250),
  description X,
  date I8,
  status C(1),
  priority I4,
  completed I8,
  percentage I4
  CONSTRAINTS	', CONSTRAINT `tiki_user_tasks_user_ref` FOREIGN KEY (`user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)'
",

'tiki_user_watches' => "
  user_id I4 PRIMARY,
  event C(40) PRIMARY,
  object C(120) PRIMARY,
  hash C(32),
  title C(250),
  type C(200),
  url C(250),
  email C(200)
  CONSTRAINTS	', CONSTRAINT `tiki_user_watches_user_ref` FOREIGN KEY (`user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)'
",

'tiki_userfiles' => "
  file_id I4 AUTO PRIMARY,
  user_id I4 NOTNULL,
  name C(200),
  filename C(200),
  filetype C(200),
  filesize C(200),
  data B,
  hits I4,
  is_file C(1),
  path C(255),
  created I8
  CONSTRAINTS	', CONSTRAINT `tiki_userfiles_user_ref` FOREIGN KEY (`user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)'
",

'users_permissions' => "
  perm_name C(30) PRIMARY,
  perm_desc C(250),
  level C(80),
  package C(100)
",

'users_groups' => "
  group_id I4 PRIMARY,
  user_id I4 NOTNULL,
  group_name C(30),
  is_default C(1),
  group_desc C(255),
  group_home C(255)
  CONSTRAINTS	', CONSTRAINT `users_groups_user_ref` FOREIGN KEY (`user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)'
",

'users_groups_inclusion' => "
  group_id I4 PRIMARY,
  include_group_id I4 PRIMARY
",

'users_grouppermissions' => "
  group_id I4 PRIMARY,
  perm_name C(30) PRIMARY,
  value C(1) default ''
  CONSTRAINTS	', CONSTRAINT `users_groupperm_group_ref` FOREIGN KEY (`group_id`) REFERENCES `".BIT_DB_PREFIX."users_groups` (`group_id`)
  				, CONSTRAINT `users_groupperm_perm_ref` FOREIGN KEY (`perm_name`) REFERENCES `".BIT_DB_PREFIX."users_permissions` (`perm_name`)'
",

'users_objectpermissions' => "
  group_id I4 PRIMARY,
  perm_name C(30) PRIMARY,
  object_type C(20) PRIMARY,
  object_id I4 PRIMARY
  CONSTRAINTS   ', CONSTRAINT `users_objectperm_group_ref` FOREIGN KEY (`group_id`) REFERENCES `".BIT_DB_PREFIX."users_groups` (`group_id`)
                , CONSTRAINT `users_objectperm_perm_ref` FOREIGN KEY (`perm_name`) REFERENCES `".BIT_DB_PREFIX."users_permissions` (`perm_name`)'
",

'users_groups_map' => "
  user_id I4 PRIMARY,
  group_id I4 PRIMARY
  CONSTRAINTS	', CONSTRAINT `users_groups_map_group_ref` FOREIGN KEY (`group_id`) REFERENCES `".BIT_DB_PREFIX."users_groups` (`group_id`)
  				, CONSTRAINT `users_groups_map_user_ref` FOREIGN KEY (`user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)'
",

'users_cnxn' => "
  user_id I4,
  cookie C(64),
  ip C(16),
  last_get I8,
  connect_time I8,
  get_count I8,
  user_agent C(128),
  current_view X
  CONSTRAINTS	', CONSTRAINT `users_cnxn_user_ref` FOREIGN KEY (`user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)'
",

'tiki_user_bookmarks_folders' => "
  folder_id I4 AUTO PRIMARY,
  parent_id I4,
  user_id I4 PRIMARY,
  name C(30)
",

'tiki_user_modules' => "
  name C(200) PRIMARY,
  title C(40),
  data X
",

'tiki_user_postings' => "
  user_id I4 PRIMARY,
  posts I8,
  last I8,
  first I8,
  level I4
  CONSTRAINTS	', CONSTRAINT `tiki_user_postings_user_ref` FOREIGN KEY (`user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)'
",

'tiki_user_preferences' => "
  user_id I4 PRIMARY,
  pref_name C(40) PRIMARY,
  value C(250)
  CONSTRAINTS	', CONSTRAINT `tiki_user_preferences_user_ref` FOREIGN KEY (`user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)'
",

'tiki_user_votings' => "
  user_id I4 PRIMARY,
  id C(160) PRIMARY
  CONSTRAINTS	', CONSTRAINT `tiki_user_votings_user_ref` FOREIGN KEY (`user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)'
",

'tiki_userpoints' => "
  user_id I4,
  points decimal(8,2),
  voted I4 DEFAULT NULL
  CONSTRAINTS	', CONSTRAINT `tiki_userpoints_user_ref` FOREIGN KEY (`user_id`) REFERENCES `".BIT_DB_PREFIX."users_users` (`user_id`)'
"
);

global $gBitInstaller;

foreach( array_keys( $tables ) AS $tableName ) {
	$gBitInstaller->registerSchemaTable( USERS_PKG_NAME, $tableName, $tables[$tableName], TRUE );
}

$indices = array (
	'users_users_email_idx' => array( 'table' => 'users_users', 'cols' => 'email', 'opts' => array('UNIQUE') ),
	'users_users_login_idx' => array( 'table' => 'users_users', 'cols' => 'login', 'opts' => array('UNIQUE') ),
	'users_permissions_name_idx' => array( 'table' => 'users_permissions', 'cols' => 'perm_name', 'opts' => array('UNIQUE') ),
	'users_users_avatar_atment_idx' => array( 'table' => 'users_users', 'cols' => 'avatar_attachment_id', 'opts' => NULL ),
	'users_groups_user_idx' => array( 'table' => 'users_groups', 'cols' => 'user_id', 'opts' => NULL ),
	'users_groups_user_name_idx' => array( 'table' => 'users_groups', 'cols' => 'user_id,group_name', 'opts' => array('UNIQUE') ),
	'users_groupperm_group_idx' => array( 'table' => 'users_grouppermissions', 'cols' => 'group_id', 'opts' => NULL ),
	'users_groupperm_perm_idx' => array( 'table' => 'users_grouppermissions', 'cols' => 'perm_name', 'opts' => NULL ),
	'users_objectperm_group_idx' =>  array( 'table' => 'users_objectpermissions', 'cols' => 'group_id', 'opts' => NULL ),
	'users_objectperm_perm_idx' => array( 'table' => 'users_objectpermissions', 'cols' => 'perm_name', 'opts' => NULL ),
	'users_objectperm_object_idx' => array( 'table' => 'users_objectpermissions', 'cols' => 'object_id', 'opts' => NULL ),
	'users_permissions_perm_idx' => array( 'table' => 'users_permissions', 'cols' => 'perm_name', 'opts' => NULL ),
	'users_groups_map_user_idx' => array( 'table' => 'users_groups_map', 'cols' => 'user_id', 'opts' => NULL ),
	'users_groups_map_group_idx' => array( 'table' => 'users_groups_map', 'cols' => 'group_id', 'opts' => NULL )
);



$gBitInstaller->registerSchemaIndexes( USERS_PKG_NAME, $indices );

$gBitInstaller->registerPackageInfo( USERS_PKG_NAME, array(
	'description' => "The users package contains all user information and gives you the possiblity to assign permissions to groups of users.",
	'license' => '<a href="http://www.gnu.org/licenses/licenses.html#LGPL">LGPL</a>',
	'version' => '0.1',
	'state' => 'experimental',
	'dependencies' => '',
) );

// ### Sequences
$sequences = array (
	'users_users_user_id_seq' => array( 'start' => 2 ),
	'users_groups_id_seq' => array( 'start' => 4 )
);
$gBitInstaller->registerSchemaSequences( USERS_PKG_NAME, $sequences );

// ### Default MenuOptions
$gBitInstaller->registerMenuOptions( USERS_PKG_NAME, array(
	array(42,'o','My files', USERS_PKG_NAME.'userfiles.php',95,'feature_userfiles','bit_p_userfiles','Registered')
) );

// ### Default Preferences
$gBitInstaller->registerPreferences( USERS_PKG_NAME, array(
	array(USERS_PKG_NAME,'webserverauth','n'),
	array(USERS_PKG_NAME,'auth_create_user_auth','n'),
	array(USERS_PKG_NAME,'auth_create_gBitDbUser','n'),
	array(USERS_PKG_NAME,'auth_ldap_adminpass',''),
	array(USERS_PKG_NAME,'auth_ldap_adminuser',''),
	array(USERS_PKG_NAME,'auth_ldap_basedn',''),
	array(USERS_PKG_NAME,'auth_ldap_groupattr','cn'),
	array(USERS_PKG_NAME,'auth_ldap_groupdn',''),
	array(USERS_PKG_NAME,'auth_ldap_groupoc','groupOfUniqueNames'),
	array(USERS_PKG_NAME,'auth_ldap_host','localhost'),
	array(USERS_PKG_NAME,'auth_ldap_memberattr','uniqueMember'),
	array(USERS_PKG_NAME,'auth_ldap_memberisdn','n'),
	array(USERS_PKG_NAME,'auth_ldap_port','389'),
	array(USERS_PKG_NAME,'auth_ldap_scope','sub'),
	array(USERS_PKG_NAME,'auth_ldap_userattr','uid'),
	array(USERS_PKG_NAME,'auth_ldap_userdn',''),
	array(USERS_PKG_NAME,'auth_ldap_useroc','inetOrgPerson'),
	array(USERS_PKG_NAME,'auth_method','tiki'),
	array(USERS_PKG_NAME,'auth_skip_admin','y'),
	array(USERS_PKG_NAME,'allowRegister','y'),
	array(USERS_PKG_NAME,'feature_userfiles','n'),
	array(USERS_PKG_NAME,'forgotPass','y'),
	array(USERS_PKG_NAME,'eponymousGroups','n'),
	array(USERS_PKG_NAME,'modallgroups','y'),
	array(USERS_PKG_NAME,'pass_chr_num','n'),
	array(USERS_PKG_NAME,'pass_due','999'),
	array(USERS_PKG_NAME,'registerPasscode',''),
	array(USERS_PKG_NAME,'rememberme','disabled'),
	array(USERS_PKG_NAME,'remembertime','7200'),
	array(USERS_PKG_NAME,'rnd_num_reg','n'),
	array(USERS_PKG_NAME,'userfiles_quota','30'),
	array(USERS_PKG_NAME,'uf_use_db','y'),
	array(USERS_PKG_NAME,'uf_use_dir',''),
	array(USERS_PKG_NAME,'useRegisterPasscode','n'),
	array(USERS_PKG_NAME,'validateUsers','n'),
	array(USERS_PKG_NAME,'validateEmail','n'),
	array(USERS_PKG_NAME,'min_pass_length','4'),
	array(USERS_PKG_NAME,'feature_clear_passwords','n'),
	array(USERS_PKG_NAME,'feature_custom_home','n'),
	array(USERS_PKG_NAME,'feature_user_bookmarks','n'),
	array(USERS_PKG_NAME,'feature_tasks','n'),
	array(USERS_PKG_NAME,'feature_usermenu','n'),
	array(USERS_PKG_NAME,'feature_userPreferences','y'),
	array(USERS_PKG_NAME,'display_name','real_name'),
	array(USERS_PKG_NAME,'change_language','y'),
	array(USERS_PKG_NAME,'case_sensitive_login','y'),
	array('common', 'feature_user_watches','n'),
) );

// ### Default Permissions
$gBitInstaller->registerUserPermissions( USERS_PKG_NAME, array(
	array('bit_p_userfiles', 'Can upload personal files', 'registered', USERS_PKG_NAME),
	array('bit_p_user_group_perms', 'Can assign permissions to personal groups', 'editors', USERS_PKG_NAME),
	array('bit_p_user_group_members', 'Can assign users to personal groups', 'registered', USERS_PKG_NAME),
	array('bit_p_user_group_subgroups', 'Can include other groups in groups', 'editors', USERS_PKG_NAME),
	array('bit_p_create_bookmarks', 'Can create user bookmarksche user bookmarks', 'registered', USERS_PKG_NAME),
	array('bit_p_configure_modules', 'Can configure modules', 'registered', USERS_PKG_NAME),
	array('bit_p_cache_bookmarks', 'Can cache user bookmarks', 'admin', USERS_PKG_NAME),
	array('bit_p_usermenu', 'Can create items in personal menu', 'registered', USERS_PKG_NAME),
	array('bit_p_tasks', 'Can use tasks', 'registered', USERS_PKG_NAME),
	array('bit_p_admin_users', 'Can edit the information for other users', 'admin', USERS_PKG_NAME),
	array('bit_p_view_tabs_and_tools', 'Can view tab and tool links', 'basic', USERS_PKG_NAME),
	array('bit_p_custom_home_theme', 'Can modify user homepage theme', 'editors', USERS_PKG_NAME),
	array('bit_p_custom_home_layout', 'Can modify user homepage layout', 'editors', USERS_PKG_NAME),
	array('bit_p_custom_css', 'Can create custom style sheets', 'editors', USERS_PKG_NAME),
) );


$gBitInstaller->registerSchemaDefault( USERS_PKG_NAME, array(
	"INSERT INTO `".BIT_DB_PREFIX."users_groups` (`user_id`, `group_id`, `group_name`,`group_desc`) VALUES ( ".ROOT_USER_ID.", 1, 'Administrators','Site operators')",
	"INSERT INTO `".BIT_DB_PREFIX."users_groups` (`user_id`, `group_id`, `group_name`,`group_desc`) VALUES ( ".ROOT_USER_ID.", -1, 'Anonymous','Public users not logged')",
	"INSERT INTO `".BIT_DB_PREFIX."users_groups` (`user_id`, `group_id`, `group_name`,`group_desc`) VALUES ( ".ROOT_USER_ID.", 2, 'Editors','Site  Editors')",
	"INSERT INTO `".BIT_DB_PREFIX."users_groups` (`user_id`, `group_id`, `group_name`,`group_desc`,`is_default`) VALUES ( ".ROOT_USER_ID.", 3, 'Registered', 'Users logged into the system', 'y')",
) );

?>
