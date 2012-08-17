<?php
// $Header$
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See below for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.
// Initialization
require_once( '../../kernel/setup_inc.php' );

$gBitSystem->verifyPermission( 'p_users_admin' );

$feedback = array();

if( isset( $_REQUEST["batchimport"])) {
	// check if it's a batch upload
	if( $_FILES['csvlist']['size'] && is_uploaded_file( $_FILES['csvlist']['tmp_name'] ) ) {
		global $gBitSmarty, $gBitUser, $gBitSystem;

		// get the delimiter if it's set - use comma if it not
		$delimiter = !empty( $_REQUEST['delimiter'] ) ? $_REQUEST['delimiter'] : ",";
		$fname = $_FILES['csvlist']['tmp_name'];
		$fhandle = fopen( $fname, "r" );

		//Get the field names
		$fields = fgetcsv( $fhandle, 1000, $delimiter );

		// is the file a valid CSV file?
		if( empty( $fields[0] ) ) {
			$gBitSystem->fatalError( tra( "The file is not a CSV file or has not a correct syntax" ));
		}

		//now load the users in a table
		while( !feof( $fhandle ) ) {
			if( $data = fgetcsv( $fhandle, 1000, $delimiter ) ) {
				for( $i = 0; $i < count( $fields ); $i++ ) {
					@$ar[$fields[$i]] = $data[$i];
				}
				$userRecords[] = $ar;
			}
		}
		fclose( $fhandle );

		// were there any users in the list?
		if( !is_array( $userRecords ) ) {
			$gBitSystem->fatalError( tra( "No records were found. Check the file please!" ));
		}
		// Process user array
		$added = 0;
		$i = 1;
		foreach( $userRecords as $userRecord ) {
			$newUser = new BitPermUser();
			if( $newUser->importUser( $userRecord ) ) {
				if( !empty( $userRecord['groups'] ) ) {
					// groups need to be separated by spaces since this is a csv file
					$groups = explode( " ", $userRecord['groups'] );
					foreach( $groups as $group ) {
						if( $groupId = $gBitUser->groupExists( $group, ROOT_USER_ID ) ) {
							$newUser->addUserToGroup( $newUser->mUserId, $groupId );
						}
					}
				}
				if( !empty( $userRecord['roles'] ) ) {
					// roles need to be separated by spaces since this is a csv file
					$roles = explode( " ", $userRecord['roles'] );
					foreach( $roles as $role ) {
						if( $roleId = $gBitUser->roleExists( $role, ROOT_USER_ID ) ) {
							$newUser->addUserToRole( $newUser->mUserId, $roleId );
						}
					}
				}
				if( empty( $_REQUEST['admin_noemail_user'] ) ) {
					$ret = users_admin_email_user( $userRecord );
					if( is_array( $ret ) ) {
						list($key, $val) = each($ret);
						$newUser->mLogs[$key] = $val;
					}
					$logHash['action_log']['title'] = $userRecord['login'];
					$newUser->storeActionLog( $logHash );
				}

				$added++;
			} else {
				$discarded[$i] = implode( ',', $newUser->mErrors );
			}
			unset( $newUser );
			$i++;
		}

		$gBitSmarty->assign( 'added', $added );
		if( @is_array( $discarded ) ) {
			$gBitSmarty->assign( 'discarded', count( $discarded ) );
			$gBitSmarty->assign_by_ref( 'discardlist', $discarded );
		}
	}
}

if ( defined( 'ROLE_MODEL' ) ) {
	// get default role and pass it to tpl
	foreach( $gBitUser->getDefaultRole() as $defaultRoleId => $defaultRoleName ) {
		$gBitSmarty->assign('defaultRoleId', $defaultRoleId );
		$gBitSmarty->assign('defaultRoleName', $defaultRoleName );
	}
} else {
	// get default group and pass it to tpl
	foreach( $gBitUser->getDefaultGroup() as $defaultGroupId => $defaultGroupName ) {
		$gBitSmarty->assign('defaultGroupId', $defaultGroupId );
		$gBitSmarty->assign('defaultGroupName', $defaultGroupName );
	}
}

// Display the template
$gBitSystem->display( 'bitpackage:users/users_import.tpl', (!empty( $title ) ? $title : 'Import Users' ) , array( 'display_mode' => 'admin' ));
?>
