<?php
global $gBitSystem, $gUpgradeFrom, $gUpgradeTo, $gBitDb;

$upgrades = array(


'TIKIWIKI19' => array (
	'TIKIWIKI18' => array (

array( 'DATADICT' => array(
array( 'ALTER' => array(
	'tiki_user_tasks' => array(
		'title' => array( '`title`', 'VARCHAR(250)' ),
		'description' => array( '`description`', 'X' ),
	),
)),
array( 'RENAMECOLUMN' => array(
	'tiki_pages' => array( '`created`' => '`date` I8' ),
)),
)),

array( 'QUERY' =>
	array( 'SQL92' => array(
		"UPDATE `tiki_user_tasks` SET `title`= (SELECT `title` FROM `tiki_user_tasks_history` WHERE `tiki_user_tasks`.`taskId`=`belongs_to`)",
		"UPDATE `tiki_user_tasks` SET `description`= (SELECT `description` FROM `tiki_user_tasks_history` WHERE `tiki_user_tasks`.`taskId`=`belongs_to`)",
	)
)),


/*

alter table tiki_user_modules add parse char(1) default NULL;

CREATE TABLE tiki_user_tasks_history (
  belongs_to integer(14) NOT NULL,                   -- the fist task in a history it has the same id as the task id
  task_version integer(4) NOT NULL DEFAULT 0,        -- version number for the history it starts with 0
  title varchar(250) NOT NULL,                       -- title
  description text DEFAULT NULL,                     -- description
  start integer(14) DEFAULT NULL,                    -- date of the starting, if it is not set than there is not starting date
  end integer(14) DEFAULT NULL,                      -- date of the end, if it is not set than there is not dealine
  lasteditor varchar(200) NOT NULL,                  -- lasteditor: username of last editior
  lastchanges integer(14) NOT NULL,                  -- date of last changes
  priority integer(2) NOT NULL DEFAULT 3,            -- priority
  completed integer(14) DEFAULT NULL,                -- date of the completation if it is null it is not yet completed
  deleted integer(14) DEFAULT NULL,                  -- date of the deleteation it it is null it is not deleted
  status char(1) DEFAULT NULL,                       -- null := waiting,
                                                     -- o := open / in progress,
                                                     -- c := completed -> (percentage = 100)
  percentage int(4) DEFAULT NULL,
  accepted_creator char(1) DEFAULT NULL,             -- y - yes, n - no, null - waiting
  accepted_user char(1) DEFAULT NULL,                -- y - yes, n - no, null - waiting
  PRIMARY KEY (belongs_to, task_version)
) AUTO_INCREMENT=1 ;
UPDATE tiki_user_tasks set title = '-'  where title IS NULL;
INSERT INTO tiki_user_tasks_history (belongs_to, title, start, description, lasteditor, lastchanges, priority, completed, status, percentage) SELECT  taskId, title, date, description, user, date, priority, completed, status, percentage FROM tiki_user_tasks;
ALTER TABLE tiki_user_tasks ADD last_version integer(4) NOT NULL DEFAULT 0 AFTER taskId;
ALTER TABLE tiki_user_tasks MODIFY user varchar(200) NOT NULL DEFAULT '';
ALTER TABLE tiki_user_tasks ADD creator varchar(200) NOT NULL AFTER user;
ALTER TABLE tiki_user_tasks ADD public_for_group varchar(30) DEFAULT NULL AFTER creator;
ALTER TABLE tiki_user_tasks ADD rights_by_creator char(1) DEFAULT NULL AFTER public_for_group;
ALTER TABLE tiki_user_tasks ADD status char(1) default NULL;
ALTER TABLE tiki_user_tasks ADD priority int(2) default NULL;
ALTER TABLE tiki_user_tasks ADD completed int(14) default NULL;
ALTER TABLE tiki_user_tasks ADD percentage int(4) default NULL;
*/

	)
),




'BONNIE' => array(
	'BWR1' => array(

// STEP 1
array( 'QUERY' =>
	array( 'MYSQL' => array(
	"ALTER TABLE `".BIT_DB_PREFIX."tiki_user_preferences` DROP PRIMARY KEY",
	"ALTER TABLE `".BIT_DB_PREFIX."users_usergroups` DROP PRIMARY KEY",
	"ALTER TABLE `".BIT_DB_PREFIX."users_grouppermissions` DROP PRIMARY KEY",
	"ALTER TABLE `".BIT_DB_PREFIX."tiki_group_inclusion` DROP PRIMARY KEY",
	"ALTER TABLE `".BIT_DB_PREFIX."tiki_user_watches` DROP PRIMARY KEY",
	)),
),

// STEP 1
array( 'DATADICT' => array(
array( 'RENAMETABLE' => array(
		'users_usergroups' => 'users_groups_map',
		'tiki_group_inclusion' => 'users_groups_inclusion',
	)
),
array( 'RENAMECOLUMN' => array(
	'users_users' => array(
		'`userId`' => '`user_id` I4',
		'`lastLogin`' => '`last_login` I8',
		'`currentLogin`' => '`current_login` I8',
		'`registrationDate`' => '`registration_date` I8',
//		'`avatarName`' => '`avatar_name`',
//		'`avatarSize`' => '`avatar_size`',
//		'`avatarFileType`' => '`avatar_file_type`',
//		'`avatarData`' => '`avatar_data`',
//		'`avatarLibName`' => '`avatar_lib_name`',
//		'`avatarType`' => '`avatar_type`',
	),
	'users_groups_map' => array(
		'`userId`' => '`user_id` I4'
	),
	'tiki_semaphores' => array( '`semName`' => '`sem_name` C(250)',
							   '`timestamp`' => '`created` I8' ),
	'tiki_user_preferences' => array(
		'`prefName`' => '`pref_name` C(40)'
	),
//	'users_groups_inclusion' => array('`groupName`', '`group_name`'),
//	'users_groups_inclusion' => array('`includeGroup`', '`include_group`'),
	'tiki_user_bookmarks_folders' => array(
		'`folderId`' => '`folder_id` I4',
		'`parentId`' => '`parent_id` I4',
	),
	'tiki_user_bookmarks_urls' => array(
		'`urlId`' => '`url_id` I4',
		'`lastUpdated`' => '`last_updated` I8',
		'`folderId`' => '`folder_id` I4',
	),
	'tiki_user_menus' => array(
		'`menuId`' => '`menu_id` I4',
	),
	'users_grouppermissions' => array(
		'`permName`' => '`perm_name` C(30)',
	),
	'users_groups' => array(
		'`groupName`' => '`group_name` C(30)',
		'`groupDesc`' => '`group_desc` C(255)',
		'`groupHome`' => '`group_home` C(255)',
	),
	'users_groups_map' => array(
		'`userId`' => '`user_id` I4'
	),
	'users_objectpermissions' => array(
		'`permName`' => '`perm_name` C(30)',
		'`objectType`' => '`object_type` C(20)',
	),
	'users_permissions' => array(
		'`permName`' => '`perm_name` C(30)',
		'`permDesc`' => '`perm_desc` C(250)',
		'`type`' => '`package` C(100)',
	),
	'tiki_userfiles' => array(
		'`fileId`' => '`file_id` I4',
		'`isFile`' => '`is_file` C(1)',
	),
	'tiki_user_tasks' => array(
		'`taskId`' => '`task_id` I4' ,
	)
)),

array( 'ALTER' => array(
	'users_users' => array(
		'content_id' => array( '`content_id`', 'I4' ), // , 'NOTNULL' ),
		'default_group_id' => array( '`default_group_id`', 'I4' ), // , 'NOTNULL' ),
		'real_name' => array( '`real_name`', 'VARCHAR(64)'),
		'avatar_attachment_id' => array( '`avatar_attachment_id`', 'I4' ), // , 'NOTNULL' ),
		'portrait_attachment_id' => array( '`portrait_attachment_id`', 'I4' ), // , 'NOTNULL' ),
		'logo_attachment_id' => array( '`logo_attachment_id`', 'I4' ), // , 'NOTNULL' ),
	),
	'tiki_semaphores' => array(
		'user_id' => array( '`user_id`', 'I4' ), // , 'NOTNULL' ),
	),
	'tiki_sessions' => array(
		'user_id' => array( '`user_id`', 'I4' ), // , 'NOTNULL' ),
		'session_id' => array( '`session_id`', 'VARCHAR(32)' ), // , 'NOTNULL' ),
	),
	'tiki_user_bookmarks_folders' => array(
		'user_id' => array( '`user_id`', 'I4' ), // , 'NOTNULL' ),
	),
	'tiki_user_bookmarks_urls' => array(
		'user_id' => array( '`user_id`', 'I4' ), // , 'NOTNULL' ),
	),
	'tiki_user_menus' => array(
		'user_id' => array( '`user_id`', 'I4' ), // , 'NOTNULL' ),
	),
	'tiki_user_preferences' => array(
		'user_id' => array( '`user_id`', 'I4' ), // , 'NOTNULL' ),
	),
	'tiki_user_tasks' => array(
		'user_id' => array( '`user_id`', 'I4' ), // , 'NOTNULL' ),
	),
	'tiki_user_watches' => array(
		'user_id' => array( '`user_id`', 'I4' ), // , 'NOTNULL' ),
	),
	'users_groups' => array(
		'group_id' => array( '`group_id`', 'I4' ), // , 'NOTNULL' ),
		'user_id' => array( '`user_id`', 'I4' ), // , 'NOTNULL' ),
	),
	'users_grouppermissions' => array(
		'group_id' => array( '`group_id`', 'I4' ), // , 'NOTNULL' ),
	),
	'users_objectpermissions' => array(
		'group_id' => array( '`group_id`', 'I4' ), // , 'NOTNULL' ),
		'object_id' => array( '`object_id`', 'I4' ), // , 'NOTNULL' ),
	),
	'users_groups_map' => array(
		'group_id' => array( '`group_id`', 'I4' ), // , 'NOTNULL' ),
	),
	'users_groups_inclusion' => array(
		'group_id' => array( '`group_id`', 'I4' ), // , 'NOTNULL' ),
		'include_group_id' => array( '`include_group_id`', 'I4' ), // , 'NOTNULL' ),
	),
)),

array( 'CREATE' => array (
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

)),

)),

// STEP 2
array( 'PHP' => '
	global $gBitSystem, $gBitDb;
	$max = $gBitDb->GetOne( "SELECT MAX(user_id) FROM `'.BIT_DB_PREFIX.'users_users`" );
	$gBitSystem->mDb->CreateSequence( "users_users_user_id_seq", $max + 1 );
	$gBitSystem->mDb->CreateSequence( "users_groups_id_seq", 1 );
	$gBitDb->query( "UPDATE `'.BIT_DB_PREFIX.'users_groups` SET `group_id`=-1 WHERE group_name=\'Anonymous\'" );
	$gBitDb->query( "INSERT INTO `'.BIT_DB_PREFIX.'users_groups_map` (`group_id`,`user_id`,`groupName`) VALUES ( -1, '.ANONYMOUS_USER_ID.',\'Anonymous\' )" );
	$groupNames = $gBitDb->GetCol( "SELECT `group_name` FROM `'.BIT_DB_PREFIX.'users_groups` WHERE `group_name` != \'Anonymous\'" );
	foreach( $groupNames as $name ) {
		$id = $gBitDb->GenID( "users_groups_id_seq" );
		$gBitDb->query( "UPDATE `'.BIT_DB_PREFIX.'users_groups` SET group_id=? WHERE group_name=?", array( $id, $name ) );
	}
' ),

// STEP 3
array( 'QUERY' =>
	array( 'SQL92' => array(
		"UPDATE `".BIT_DB_PREFIX."users_grouppermissions` SET `perm_name`=replace(`perm_name`,'tiki_','bit_')",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`=replace(`perm_name`,'tiki_','bit_')",
		"UPDATE `".BIT_DB_PREFIX."users_objectpermissions` SET `perm_name`=replace(`perm_name`,'tiki_','bit_')",




		"INSERT INTO `".BIT_DB_PREFIX."users_users` (`real_name`, `login`, `email`, `user_id` ) VALUES ('Anonymous', 'anonymous', 'anonymous@localhost', ".ANONYMOUS_USER_ID.")",
// TikiWiki assigns the creator user foreign key as 'system' even if there is now 'system' user - XOXO spiderr
// In order for all pages to upgrade, there must be at least an 'admin' and 'system' user
 		"INSERT INTO `".BIT_DB_PREFIX."users_users` (`real_name`, `login`, `email`, `user_id` ) VALUES ('Administrator', 'admin', 'root@localhost', ".ROOT_USER_ID.")",
 		"INSERT INTO `".BIT_DB_PREFIX."users_users` (`real_name`, `login`, `email` ) VALUES ('System', 'system', 'system@localhost' )",
		"UPDATE `".BIT_DB_PREFIX."tiki_semaphores` SET `user_id`=(SELECT `user_id` FROM `".BIT_DB_PREFIX."users_users` WHERE `".BIT_DB_PREFIX."users_users`.`login`=`".BIT_DB_PREFIX."tiki_semaphores`.`user`)",
		"UPDATE `".BIT_DB_PREFIX."tiki_semaphores` SET `user_id`=(SELECT `user_id` FROM `".BIT_DB_PREFIX."users_users` WHERE `".BIT_DB_PREFIX."users_users`.`login`=`".BIT_DB_PREFIX."tiki_semaphores`.`user`)",
		"UPDATE `".BIT_DB_PREFIX."tiki_sessions` SET `user_id`=(SELECT `user_id` FROM `".BIT_DB_PREFIX."users_users` WHERE `".BIT_DB_PREFIX."users_users`.`login`=`".BIT_DB_PREFIX."tiki_sessions`.`user`)",
		"UPDATE `".BIT_DB_PREFIX."tiki_user_preferences` SET `user_id`=(SELECT `user_id` FROM `".BIT_DB_PREFIX."users_users` WHERE `".BIT_DB_PREFIX."users_users`.`login`=`".BIT_DB_PREFIX."tiki_user_preferences`.`user`)",
		"UPDATE `".BIT_DB_PREFIX."tiki_user_bookmarks_folders` SET `user_id`=(SELECT `user_id` FROM `".BIT_DB_PREFIX."users_users` WHERE `".BIT_DB_PREFIX."users_users`.`login`=`".BIT_DB_PREFIX."tiki_user_bookmarks_folders`.`user`)",
		"UPDATE `".BIT_DB_PREFIX."tiki_user_bookmarks_urls` SET `user_id`=(SELECT `user_id` FROM `".BIT_DB_PREFIX."users_users` WHERE `".BIT_DB_PREFIX."users_users`.`login`=`".BIT_DB_PREFIX."tiki_user_bookmarks_urls`.`user`)",
		"UPDATE `".BIT_DB_PREFIX."tiki_user_menus` SET `user_id`=(SELECT `user_id` FROM `".BIT_DB_PREFIX."users_users` WHERE `".BIT_DB_PREFIX."users_users`.`login`=`".BIT_DB_PREFIX."tiki_user_menus`.`user`)",
		"UPDATE `".BIT_DB_PREFIX."tiki_user_tasks` SET `user_id`=(SELECT `user_id` FROM `".BIT_DB_PREFIX."users_users` WHERE `".BIT_DB_PREFIX."users_users`.`login`=`".BIT_DB_PREFIX."tiki_user_tasks`.`user`)",
		"UPDATE `".BIT_DB_PREFIX."tiki_user_watches` SET `user_id`=(SELECT `user_id` FROM `".BIT_DB_PREFIX."users_users` WHERE `".BIT_DB_PREFIX."users_users`.`login`=`".BIT_DB_PREFIX."tiki_user_watches`.`user`)",
		"UPDATE `".BIT_DB_PREFIX."users_groups_map` SET `group_id`=(SELECT `group_id` FROM `".BIT_DB_PREFIX."users_groups` WHERE `".BIT_DB_PREFIX."users_groups`.`group_name`=`".BIT_DB_PREFIX."users_groups_map`.`groupName`)",
		"UPDATE `".BIT_DB_PREFIX."users_grouppermissions` SET `group_id`=(SELECT `group_id` FROM `".BIT_DB_PREFIX."users_groups` WHERE `".BIT_DB_PREFIX."users_groups`.`group_name`=`".BIT_DB_PREFIX."users_grouppermissions`.`groupName`)",
		"UPDATE `".BIT_DB_PREFIX."users_objectpermissions` SET `group_id`=(SELECT `group_id` FROM `".BIT_DB_PREFIX."users_groups` WHERE `".BIT_DB_PREFIX."users_groups`.`group_name`=`".BIT_DB_PREFIX."users_objectpermissions`.`groupName`)",
		"UPDATE `".BIT_DB_PREFIX."users_groups_inclusion` SET `group_id`=(SELECT `group_id` FROM `".BIT_DB_PREFIX."users_groups` WHERE `group_name`=`".BIT_DB_PREFIX."users_groups_inclusion`.`groupName`)",
		"UPDATE `".BIT_DB_PREFIX."users_groups_inclusion` SET `include_group_id`=(SELECT `group_id` FROM `".BIT_DB_PREFIX."users_groups` WHERE `group_name`=`includeGroup`)",
		"UPDATE `".BIT_DB_PREFIX."users_groups` SET `user_id`=1",
		"UPDATE `".BIT_DB_PREFIX."users_groups` SET `is_default`='y' WHERE `group_name`='Registered'",
		"alter table `".BIT_DB_PREFIX."tiki_user_watches` add index `user_id` (`user_id`)",
		"update `".BIT_DB_PREFIX."tiki_user_watches` set `type` = 'bitpage' where `type` = 'Wiki page'",
		"update `".BIT_DB_PREFIX."tiki_user_watches` set `type` = 'bitpage' where `type` = 'Wiki-Seite'",


	),
)),

// STEP 4
array( 'DATADICT' => array(
array( 'DROPCOLUMN' => array(
		'tiki_semaphores' => array( '`user`' ),
		'tiki_sessions' => array( '`user`', '`sessionId`' ),
		'users_groups_map' => array( '`groupName`' ),
		'users_grouppermissions' => array( '`groupName`' ),
		'users_objectpermissions' => array( '`groupName`' ),
		'users_groups_inclusion' => array( '`groupName`' ),
		'tiki_user_bookmarks_folders' => array( '`user`' ),
		'tiki_user_bookmarks_urls' => array( '`user`' ),
		'tiki_user_menus' => array( '`user`' ),
		'tiki_user_preferences' => array( '`user`' ),
		'tiki_user_tasks' => array( '`user`' ),
		'tiki_user_watches' => array( '`user`' ),
	)),
)),

// STEP 5
array( 'SQL92' =>
	array( 'QUERY' => array(
		"INSERT INTO `".BIT_DB_PREFIX."users_groups_map` (`group_id`, `user_id` ) VALUES ( -1, ".ANONYMOUS_USER_ID." )",
	),
)),

// STEP 2
array( 'PHP' => '
	global $gBitSystem, $gBitDb;
	$adminGroup = $gBitDb->GetOne( "SELECT `group_id` FROM `'.BIT_DB_PREFIX.'users_grouppermissions` where perm_name=\'bit_p_admin\'" );
	if( empty( $adminGroup ) ) {
		$adminGroup = $gBitDb->GetOne( "SELECT `group_id` FROM `'.BIT_DB_PREFIX.'users_groups` where LOWER(`group_name`) LIKE \'administrator%\'" );
		if( empty( $adminGroup ) ) {
			$adminGroup = $gBitDb->GenID( "users_groups_id_seq" );
			$gBitDb->query( "INSERT INTO `'.BIT_DB_PREFIX.'users_groups` (`group_id`,`group_name`) VALUES ( $adminGroup, \'Administrators\' )" );
		}
		$gBitDb->query( "INSERT INTO `'.BIT_DB_PREFIX.'users_groups_map` (`group_id`,`user_id`) VALUES ( $adminGroup, '.ROOT_USER_ID.' )" );
		$gBitDb->query( "INSERT INTO `'.BIT_DB_PREFIX.'users_grouppermissions` (`perm_name`, `group_id`) VALUES( \'bit_p_admin\', $adminGroup )" );
	}
' ),

// STEP 4
array( 'DATADICT' => array(
array( 'CREATEINDEX' => array(
		'tiki_sema_user_idx' => array( 'tiki_semaphores', '`user_id`', array() ),
		'tiki_user_prefs_idx' => array( 'tiki_user_preferences', '`user_id`', array() ),
		'tiki_user_prefs_un_idx' => array( 'tiki_user_preferences', '`user_id`,`pref_name`', array( 'UNIQUE' ) ),
		'users_groups_map_user_idx' => array( 'users_groups_map', '`user_id`', array() ),
		'users_groups_map_group_idx' => array( 'users_groups_map', '`group_id`', array() ),
		'users_groups_map_ug_idx' => array( 'users_groups_map', '`user_id`,`group_id`', array( 'UNIQUE' ) ),
		'users_groupperms_group_idx' => array( 'users_grouppermissions', '`group_id`', array() ),
		'users_groupperms_group_idx' => array( 'users_grouppermissions', '`group_id`,`perm_name`', array( 'UNIQUE' ) ),
		'users_groups_inc_group_idx' => array( 'users_groups_inclusion', '`group_id`', array() ),
		'users_groups_inc_gi_idx' => array( 'users_groups_inclusion', '`group_id`,`include_group_id`', array( 'UNIQUE' ) ),
	)),
)),



	)
), 

'BWR1' => array(
	'BWR2' => array(
// STEP 1
array( 'DATADICT' => array(
	array( 'RENAMETABLE' => array(
		'tiki_semaphores'             => 'users_semaphores',
		'users_objectpermissions'     => 'users_object_permissions',
		'users_grouppermissions'      => 'users_group_permissions',
		'tiki_user_watches'           => 'users_watches',
	)),
	array( 'RENAMECOLUMN' => array(
		'users_watches' => array(
			'`type`' => '`watch_type` C(200)'
		),
		'users_group_permissions' => array(
			'`value`' => "`perm_value` C(1) default ''"
		),
		'users_users' => array(
			'`password`' => '`user_password` C(32)'
		),
		'users_permissions' => array(
			'`level`' => '`perm_level` C(80)'
		),
	)),
	array( 'DROPTABLE' => array(
		'tiki_sessions'
	)),
)),

array( 'QUERY' =>
	array( 'SQL92' => array(
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_shoutbox_view' WHERE `perm_name`='bit_p_view_shoutbox'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_shoutbox_admin' WHERE `perm_name`='bit_p_admin_shoutbox'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_shoutbox_post' WHERE `perm_name`='bit_p_post_shoutbox'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_xmlrpc_send_content' WHERE `perm_name`='bit_p_send_pages'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_xmlrpc_sendme_content' WHERE `perm_name`='bit_p_sendme_pages'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_xmlrpc_admin_content' WHERE `perm_name`='bit_p_admin_received_pages'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_admin' WHERE `perm_name`='bit_p_admin'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_access_closed_site' WHERE `perm_name`='bit_p_access_closed_site'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_gatekeeper_create' WHERE `perm_name`='bit_p_create_gatekeeper'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_gatekeeper_edit' WHERE `perm_name`='bit_p_gatekeeper_edit'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_gatekeeper_admin' WHERE `perm_name`='bit_p_gatekeeper_admin'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_gatekeeper_read' WHERE `perm_name`='bit_p_read_gatekeeper'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_calendar_view' WHERE `perm_name`='bit_p_view_calendar'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_sample_admin' WHERE `perm_name`='bit_p_admin_sample'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_sample_create' WHERE `perm_name`='bit_p_create_sample'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_sample_edit' WHERE `perm_name`='bit_p_edit_sample'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_sample_read' WHERE `perm_name`='bit_p_read_sample'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_sample_remove' WHERE `perm_name`='bit_p_remove_sample'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_categories_admin' WHERE `perm_name`='bit_p_admin_categories'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_categories_view' WHERE `perm_name`='bit_p_view_categories'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_tidbits_admin_banning' WHERE `perm_name`='bit_p_admin_banning'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_tidbits_upload_userfiles' WHERE `perm_name`='bit_p_userfiles'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_tidbits_create_bookmarks' WHERE `perm_name`='bit_p_create_bookmarks'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_tidbits_cache_bookmarks' WHERE `perm_name`='bit_p_cache_bookmarks'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_tidbits_configure_modules' WHERE `perm_name`='bit_p_configure_modules'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_tidbits_use_usermenu' WHERE `perm_name`='bit_p_usermenu'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_tidbits_use_tasks' WHERE `perm_name`='bit_p_tasks'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_tidbits_custom_home_theme' WHERE `perm_name`='bit_p_custom_home_theme'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_tidbits_custom_home_layout' WHERE `perm_name`='bit_p_custom_home_layout'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_tidbits_use_custom_css' WHERE `perm_name`='bit_p_custom_css'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_tidbits_edit_fortune_cookies' WHERE `perm_name`='bit_p_edit_cookies'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_quota_create' WHERE `perm_name`='bit_p_create_quota'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_quota_edit' WHERE `perm_name`='bit_p_quota_edit'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_quota_admin' WHERE `perm_name`='bit_p_quota_admin'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_quota_read' WHERE `perm_name`='bit_p_read_quota'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_smileys_use' WHERE `perm_name`='bit_p_use_smileys'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_chatterbox_use' WHERE `perm_name`='bit_p_use_chatterbox'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_fisheye_view' WHERE `perm_name`='bit_p_view_fisheye'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_fisheye_create' WHERE `perm_name`='bit_p_create_fisheye'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_fisheye_edit' WHERE `perm_name`='bit_p_edit_fisheye'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_fisheye_upload' WHERE `perm_name`='bit_p_upload_fisheye'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_fisheye_admin' WHERE `perm_name`='bit_p_admin_fisheye'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_fisheye_upload_nonimages' WHERE `perm_name`='bit_p_fisheye_upload_nonimages'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_languages_create' WHERE `perm_name`='bit_p_create_languages'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_languages_edit' WHERE `perm_name`='bit_p_edit_languages'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_languages_delete' WHERE `perm_name`='bit_p_delete_languages'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_languages_edit_master' WHERE `perm_name`='bit_p_edit_master_strings'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_languages_import' WHERE `perm_name`='bit_p_import_languages'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_users_assign_group_perms' WHERE `perm_name`='bit_p_user_group_perms'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_users_assign_group_members' WHERE `perm_name`='bit_p_user_group_members'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_users_group_subgroups' WHERE `perm_name`='bit_p_user_group_subgroups'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_users_admin' WHERE `perm_name`='bit_p_admin_users'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_users_view_icons_and_tools' WHERE `perm_name`='bit_p_view_tabs_and_tools'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_users_create_personal_groups' WHERE `perm_name`='bit_p_create_personal_groups'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_users_view_user_list' WHERE `perm_name`='bit_p_view_user_list'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_users_view_user_homepage' WHERE `perm_name`='bit_p_view_user_homepage'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_users_edit_user_homepage' WHERE `perm_name`='bit_p_edit_user_homepage'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_galaxia_admin' WHERE `perm_name`='bit_p_admin_workflow'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_galaxia_abort_instance' WHERE `perm_name`='bit_p_abort_instance'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_galaxia_use' WHERE `perm_name`='bit_p_use_workflow'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_galaxia_exception_instance' WHERE `perm_name`='bit_p_exception_instance'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_galaxia_send_instance' WHERE `perm_name`='bit_p_send_instance'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_newsletters_admin' WHERE `perm_name`='bit_p_admin_newsletters'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_newsletters_create' WHERE `perm_name`='bit_p_create_newsletters'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_newsletters_create_editions' WHERE `perm_name`='bit_p_create_editions'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_newsletters_subscribe' WHERE `perm_name`='bit_p_subscribe_newsletters'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_newsletters_subscribe_email' WHERE `perm_name`='bit_p_subscribe_email'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_blogs_create' WHERE `perm_name`='bit_p_create_blogs'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_blogs_create_public_blog' WHERE `perm_name`='bit_p_create_public_blog'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_blogs_post' WHERE `perm_name`='bit_p_blog_post'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_blogs_admin' WHERE `perm_name`='bit_p_blog_admin'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_blogs_view' WHERE `perm_name`='bit_p_read_blog'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_pdf_generation' WHERE `perm_name`='bit_p_pdf_generation'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_messages_send' WHERE `perm_name`='bit_p_messages'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_wiki_edit_dynvar' WHERE `perm_name`='bit_p_edit_dynvar'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_wiki_edit_page' WHERE `perm_name`='bit_p_edit'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_wiki_view_page' WHERE `perm_name`='bit_p_view'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_wiki_remove_page' WHERE `perm_name`='bit_p_remove'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_wiki_rollback' WHERE `perm_name`='bit_p_rollback'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_wiki_admin' WHERE `perm_name`='bit_p_admin_wiki'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_wiki_admin_attachments' WHERE `perm_name`='bit_p_wiki_admin_attachments'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_wiki_view_attachments' WHERE `perm_name`='bit_p_wiki_view_attachments'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_wiki_upload_picture' WHERE `perm_name`='bit_p_upload_picture'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_wiki_save_minor' WHERE `perm_name`='bit_p_minor'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_wiki_rename_page' WHERE `perm_name`='bit_p_rename'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_wiki_lock_page' WHERE `perm_name`='bit_p_lock'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_wiki_edit_book' WHERE `perm_name`='bit_p_edit_books'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_wiki_admin_book' WHERE `perm_name`='bit_p_admin_books'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_wiki_edit_copyright' WHERE `perm_name`='bit_p_edit_copyrights'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_pigeonholes_view' WHERE `perm_name`='bit_p_view_pigeonholes'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_pigeonholes_insert_member' WHERE `perm_name`='bit_p_insert_pigeonhole_member'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_pigeonholes_edit' WHERE `perm_name`='bit_p_edit_pigeonholes'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_stickies_edit' WHERE `perm_name`='bit_p_stickies_edit'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_stickies_admin' WHERE `perm_name`='bit_p_stickies_admin'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_stats_view_referer' WHERE `perm_name`='bit_p_view_referer_stats'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_stats_view' WHERE `perm_name`='bit_p_view_stats'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_liberty_edit_html_style' WHERE `perm_name`='bit_p_edit_html_style'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_liberty_post_comments' WHERE `perm_name`='bit_p_post_comments'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_liberty_read_comments' WHERE `perm_name`='bit_p_read_comments'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_liberty_remove_comments' WHERE `perm_name`='bit_p_remove_comments'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_liberty_vote_comments' WHERE `perm_name`='bit_p_vote_comments'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_liberty_edit_comments' WHERE `perm_name`='bit_p_edit_comments'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_liberty_attach_attachments' WHERE `perm_name`='bit_p_content_attachments'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_liberty_detach_attachment' WHERE `perm_name`='bit_p_detach_attachment'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_liberty_print' WHERE `perm_name`='bit_p_print'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_articles_edit' WHERE `perm_name`='bit_p_edit_article'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_articles_remove' WHERE `perm_name`='bit_p_remove_article'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_articles_read' WHERE `perm_name`='bit_p_read_article'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_articles_submit' WHERE `perm_name`='bit_p_submit_article'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_articles_edit_submission' WHERE `perm_name`='bit_p_edit_submission'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_articles_remove_submission' WHERE `perm_name`='bit_p_remove_submission'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_articles_approve_submission' WHERE `perm_name`='bit_p_approve_submission'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_articles_send' WHERE `perm_name`='bit_p_send_articles'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_articles_sendme' WHERE `perm_name`='bit_p_sendme_articles'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_articles_auto_approve' WHERE `perm_name`='bit_p_autoapprove_submission'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_articles_admin' WHERE `perm_name`='bit_p_admin_articles'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_nexus_insert_item' WHERE `perm_name`='bit_p_insert_nexus_item'",
		"UPDATE `".BIT_DB_PREFIX."users_permissions` SET `perm_name`='p_nexus_create_menus' WHERE `perm_name`='bit_p_create_nexus_menus'",



		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_shoutbox_view' WHERE `perm_name`='bit_p_view_shoutbox'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_shoutbox_admin' WHERE `perm_name`='bit_p_admin_shoutbox'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_shoutbox_post' WHERE `perm_name`='bit_p_post_shoutbox'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_xmlrpc_send_content' WHERE `perm_name`='bit_p_send_pages'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_xmlrpc_sendme_content' WHERE `perm_name`='bit_p_sendme_pages'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_xmlrpc_admin_content' WHERE `perm_name`='bit_p_admin_received_pages'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_admin' WHERE `perm_name`='bit_p_admin'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_access_closed_site' WHERE `perm_name`='bit_p_access_closed_site'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_gatekeeper_create' WHERE `perm_name`='bit_p_create_gatekeeper'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_gatekeeper_edit' WHERE `perm_name`='bit_p_gatekeeper_edit'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_gatekeeper_admin' WHERE `perm_name`='bit_p_gatekeeper_admin'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_gatekeeper_read' WHERE `perm_name`='bit_p_read_gatekeeper'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_calendar_view' WHERE `perm_name`='bit_p_view_calendar'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_sample_admin' WHERE `perm_name`='bit_p_admin_sample'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_sample_create' WHERE `perm_name`='bit_p_create_sample'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_sample_edit' WHERE `perm_name`='bit_p_edit_sample'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_sample_read' WHERE `perm_name`='bit_p_read_sample'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_sample_remove' WHERE `perm_name`='bit_p_remove_sample'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_categories_admin' WHERE `perm_name`='bit_p_admin_categories'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_categories_view' WHERE `perm_name`='bit_p_view_categories'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_tidbits_admin_banning' WHERE `perm_name`='bit_p_admin_banning'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_tidbits_upload_userfiles' WHERE `perm_name`='bit_p_userfiles'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_tidbits_create_bookmarks' WHERE `perm_name`='bit_p_create_bookmarks'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_tidbits_cache_bookmarks' WHERE `perm_name`='bit_p_cache_bookmarks'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_tidbits_configure_modules' WHERE `perm_name`='bit_p_configure_modules'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_tidbits_use_usermenu' WHERE `perm_name`='bit_p_usermenu'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_tidbits_use_tasks' WHERE `perm_name`='bit_p_tasks'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_tidbits_custom_home_theme' WHERE `perm_name`='bit_p_custom_home_theme'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_tidbits_custom_home_layout' WHERE `perm_name`='bit_p_custom_home_layout'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_tidbits_use_custom_css' WHERE `perm_name`='bit_p_custom_css'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_tidbits_edit_fortune_cookies' WHERE `perm_name`='bit_p_edit_cookies'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_quota_create' WHERE `perm_name`='bit_p_create_quota'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_quota_edit' WHERE `perm_name`='bit_p_quota_edit'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_quota_admin' WHERE `perm_name`='bit_p_quota_admin'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_quota_read' WHERE `perm_name`='bit_p_read_quota'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_smileys_use' WHERE `perm_name`='bit_p_use_smileys'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_chatterbox_use' WHERE `perm_name`='bit_p_use_chatterbox'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_fisheye_view' WHERE `perm_name`='bit_p_view_fisheye'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_fisheye_create' WHERE `perm_name`='bit_p_create_fisheye'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_fisheye_edit' WHERE `perm_name`='bit_p_edit_fisheye'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_fisheye_upload' WHERE `perm_name`='bit_p_upload_fisheye'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_fisheye_admin' WHERE `perm_name`='bit_p_admin_fisheye'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_fisheye_upload_nonimages' WHERE `perm_name`='bit_p_fisheye_upload_nonimages'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_languages_create' WHERE `perm_name`='bit_p_create_languages'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_languages_edit' WHERE `perm_name`='bit_p_edit_languages'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_languages_delete' WHERE `perm_name`='bit_p_delete_languages'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_languages_edit_master' WHERE `perm_name`='bit_p_edit_master_strings'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_languages_import' WHERE `perm_name`='bit_p_import_languages'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_users_assign_group_perms' WHERE `perm_name`='bit_p_user_group_perms'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_users_assign_group_members' WHERE `perm_name`='bit_p_user_group_members'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_users_group_subgroups' WHERE `perm_name`='bit_p_user_group_subgroups'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_users_admin' WHERE `perm_name`='bit_p_admin_users'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_users_view_icons_and_tools' WHERE `perm_name`='bit_p_view_tabs_and_tools'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_users_create_personal_groups' WHERE `perm_name`='bit_p_create_personal_groups'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_users_view_user_list' WHERE `perm_name`='bit_p_view_user_list'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_users_view_user_homepage' WHERE `perm_name`='bit_p_view_user_homepage'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_users_edit_user_homepage' WHERE `perm_name`='bit_p_edit_user_homepage'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_galaxia_admin' WHERE `perm_name`='bit_p_admin_workflow'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_galaxia_abort_instance' WHERE `perm_name`='bit_p_abort_instance'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_galaxia_use' WHERE `perm_name`='bit_p_use_workflow'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_galaxia_exception_instance' WHERE `perm_name`='bit_p_exception_instance'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_galaxia_send_instance' WHERE `perm_name`='bit_p_send_instance'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_newsletters_admin' WHERE `perm_name`='bit_p_admin_newsletters'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_newsletters_create' WHERE `perm_name`='bit_p_create_newsletters'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_newsletters_create_editions' WHERE `perm_name`='bit_p_create_editions'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_newsletters_subscribe' WHERE `perm_name`='bit_p_subscribe_newsletters'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_newsletters_subscribe_email' WHERE `perm_name`='bit_p_subscribe_email'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_blogs_create' WHERE `perm_name`='bit_p_create_blogs'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_blogs_create_public_blog' WHERE `perm_name`='bit_p_create_public_blog'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_blogs_post' WHERE `perm_name`='bit_p_blog_post'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_blogs_admin' WHERE `perm_name`='bit_p_blog_admin'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_blogs_view' WHERE `perm_name`='bit_p_read_blog'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_pdf_generation' WHERE `perm_name`='bit_p_pdf_generation'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_messages_send' WHERE `perm_name`='bit_p_messages'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_wiki_edit_dynvar' WHERE `perm_name`='bit_p_edit_dynvar'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_wiki_edit_page' WHERE `perm_name`='bit_p_edit'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_wiki_view_page' WHERE `perm_name`='bit_p_view'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_wiki_remove_page' WHERE `perm_name`='bit_p_remove'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_wiki_rollback' WHERE `perm_name`='bit_p_rollback'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_wiki_admin' WHERE `perm_name`='bit_p_admin_wiki'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_wiki_admin_attachments' WHERE `perm_name`='bit_p_wiki_admin_attachments'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_wiki_view_attachments' WHERE `perm_name`='bit_p_wiki_view_attachments'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_wiki_upload_picture' WHERE `perm_name`='bit_p_upload_picture'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_wiki_save_minor' WHERE `perm_name`='bit_p_minor'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_wiki_rename_page' WHERE `perm_name`='bit_p_rename'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_wiki_lock_page' WHERE `perm_name`='bit_p_lock'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_wiki_edit_book' WHERE `perm_name`='bit_p_edit_books'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_wiki_admin_book' WHERE `perm_name`='bit_p_admin_books'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_wiki_edit_copyright' WHERE `perm_name`='bit_p_edit_copyrights'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_pigeonholes_view' WHERE `perm_name`='bit_p_view_pigeonholes'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_pigeonholes_insert_member' WHERE `perm_name`='bit_p_insert_pigeonhole_member'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_pigeonholes_edit' WHERE `perm_name`='bit_p_edit_pigeonholes'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_stickies_edit' WHERE `perm_name`='bit_p_stickies_edit'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_stickies_admin' WHERE `perm_name`='bit_p_stickies_admin'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_stats_view_referer' WHERE `perm_name`='bit_p_view_referer_stats'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_stats_view' WHERE `perm_name`='bit_p_view_stats'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_liberty_edit_html_style' WHERE `perm_name`='bit_p_edit_html_style'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_liberty_post_comments' WHERE `perm_name`='bit_p_post_comments'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_liberty_read_comments' WHERE `perm_name`='bit_p_read_comments'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_liberty_remove_comments' WHERE `perm_name`='bit_p_remove_comments'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_liberty_vote_comments' WHERE `perm_name`='bit_p_vote_comments'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_liberty_edit_comments' WHERE `perm_name`='bit_p_edit_comments'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_liberty_attach_attachments' WHERE `perm_name`='bit_p_content_attachments'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_liberty_detach_attachment' WHERE `perm_name`='bit_p_detach_attachment'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_liberty_print' WHERE `perm_name`='bit_p_print'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_articles_edit' WHERE `perm_name`='bit_p_edit_article'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_articles_remove' WHERE `perm_name`='bit_p_remove_article'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_articles_read' WHERE `perm_name`='bit_p_read_article'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_articles_submit' WHERE `perm_name`='bit_p_submit_article'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_articles_edit_submission' WHERE `perm_name`='bit_p_edit_submission'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_articles_remove_submission' WHERE `perm_name`='bit_p_remove_submission'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_articles_approve_submission' WHERE `perm_name`='bit_p_approve_submission'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_articles_send' WHERE `perm_name`='bit_p_send_articles'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_articles_sendme' WHERE `perm_name`='bit_p_sendme_articles'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_articles_auto_approve' WHERE `perm_name`='bit_p_autoapprove_submission'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_articles_admin' WHERE `perm_name`='bit_p_admin_articles'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_nexus_insert_item' WHERE `perm_name`='bit_p_insert_nexus_item'",
		"UPDATE `".BIT_DB_PREFIX."users_group_permissions` SET `perm_name`='p_nexus_create_menus' WHERE `perm_name`='bit_p_create_nexus_menus'",
	)
)),
array( 'ALTER' => array(
	'users_groups' => array(
		'is_public' => array( '`is_public`', 'VARCHAR(1)' ),
	)
	'users_cnxn' => array(
		'assume_from_user_id' => array( '`assume_from_user_id`', 'INT' ),
	)
)),
array( 'ALTER' => array(
	'users_groups' => array(
			'after_registration_page' => array( '`after_registration_page`', 'VARCHAR(255)' ),
	)
)),

	)
)

);

if( isset( $upgrades[$gUpgradeFrom][$gUpgradeTo] ) ) {
	$gBitSystem->registerUpgrade( USERS_PKG_NAME, $upgrades[$gUpgradeFrom][$gUpgradeTo] );
}


?>
