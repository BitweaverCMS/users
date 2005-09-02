<?php
// $Header: /cvsroot/bitweaver/_bit_users/admin/index.php,v 1.1.1.1.2.9 2005/09/02 16:08:14 spiderr Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// Initialization
require_once( '../../bit_setup_inc.php' );

function batchImportUsers() {
	global $gBitSmarty;
	$fname = $_FILES['csvlist']['tmp_name'];
	$fhandle = fopen($fname, "r");
	//Get the field names
	$fields = fgetcsv($fhandle, 1000);
	//any?
	if (!$fields[0]) {
		$gBitSmarty->assign('msg', tra("The file is not a CSV file or has not a correct syntax"));
		$gBitSystem->display( 'error.tpl' );
		die;
	}
	//now load the users in a table
	while (!feof($fhandle)) {
		if( $data = fgetcsv($fhandle, 1000) ) {
			for ($i = 0; $i < count($fields); $i++) {
				@$ar[$fields[$i]] = $data[$i];
			}
			$userrecs[] = $ar;
		}
	}
	fclose ($fhandle);
	// any?
	if (!is_array($userrecs)) {
		$gBitSmarty->assign('msg', tra("No records were found. Check the file please!"));
		$gBitSystem->display( 'error.tpl' );
		die;
	}
	// Process user array
	$added = 0;
	$i = 1;
	foreach ($userrecs as $u) {
		$newUser = new BitUser();
		//untested - spiderr
		if( $newUser->store( $u ) ) {
			if( !empty( $u['groups'] ) ) {
				$grps = explode(",", $u['groups']);
				foreach ($grps as $grp) {
					if( $groupId = $newUser->group_exists( $grp, ROOT_USER_ID ) ) {
						$newUser->addUserToGroup( $newUser->mUserId, $groupId );
					}
				}
			}
			$added++;
		} else {
			$discarded[$i] = implode( ',', $newUser->mErrors );
		}
		unset( $newUser );
		$i++;
	}

	$gBitSmarty->assign('added', $added);
	if (@is_array($discarded)) {
		$gBitSmarty->assign('discarded', count($discarded));
	}
	@$gBitSmarty->assign_by_ref('discardlist', $discarded);
}

$gBitSystem->verifyPermission( 'bit_p_admin_users' );

$feedback = array();

// Process the form to add a user here
if (isset($_REQUEST["newuser"])) {
	$newUser = new BitPermUser();
	// Check if the user already exists
	// jht 2005-06-22_23:51:58 flag this user store as coming from admin page -- a kludge
	$_REQUEST['admin_add'] = 1;
	if( $newUser->store( $_REQUEST ) ) {
		$gBitSmarty->assign( 'addSuccess', "User Added Successfully" );
	} else {
		$gBitSmarty->assign_by_ref( 'newUser', $_REQUEST );
		$gBitSmarty->assign( 'errors', $newUser->mErrors );
	}
	// if no user data entered, check if it's a batch upload
} elseif( isset( $_REQUEST["batchimport"]) ) {
	if( $_FILES['csvlist']['size'] && is_uploaded_file($_FILES['csvlist']['tmp_name'] ) ) {
		batchImportUsers();
	}
} elseif( isset( $_REQUEST["assume_user"]) && $gBitUser->hasPermission( 'bit_p_admin_users' ) ) {
	$assume_user = (is_numeric( $_REQUEST["assume_user"] )) ? array( 'user_id' => $_REQUEST["assume_user"] ) : array('login' => $_REQUEST["assume_user"]) ;
	$userInfo = $gBitUser->getUserInfo( $assume_user );
	if( isset( $_REQUEST["confirm"] ) ) {
		$gBitUser->verifyTicket();
		if( $gBitUser->assumeUser( $userInfo["user_id"] ) ) {
			header( 'Location: '.$gBitSystem->getDefaultPage() );
			die;
		}
	} else {
		$gBitSystem->setBrowserTitle( 'Assumer User Identity' );
		$formHash['assume_user'] = $_REQUEST['assume_user'];
		$msgHash = array(
			'confirm_item' => tra( 'This will log you in as the user' )." <strong>$userInfo[real_name] ($userInfo[login])</strong>",
		);
		$gBitSystem->confirmDialog( $formHash,$msgHash );
	}
} elseif( !empty( $_REQUEST['find'] ) ) {
	$title = 'Find Users';
}

// Process actions here
// Remove user or remove user from group
if (isset($_REQUEST["action"])) {
	$formHash['action'] = $_REQUEST['action'];
	if ($_REQUEST["action"] == 'delete') {
		$gBitUser->verifyTicket();
		$formHash['user_id'] = $_REQUEST['user_id'];
		$userInfo = $gBitUser->getUserInfo( array( 'user_id' => $_REQUEST["user_id"] ) );
		if( !empty( $userInfo['user_id'] ) ) {
			if( isset( $_REQUEST["confirm"] ) ) {
				if( $gBitUser->expunge( $_REQUEST["user_id"] ) ) {
					$feedback['success'][] = tra( 'User Deleted' )." <strong>$userInfo[real_name] ($userInfo[login])</strong>";
				}
			} else {
				$gBitSystem->setBrowserTitle( 'Delete user' );
				$msgHash = array(
					'confirm_item' => tra( 'Are you sure you want to remove the user?' ),
					'warning' => tra( 'This will permentally delete the user' )." <strong>$userInfo[real_name] ($userInfo[login])</strong>",
				);
				$gBitSystem->confirmDialog( $formHash,$msgHash );
			}
		} else {
			$feedback['error'][] = tra( 'User not found' );
		}
	}
	if ($_REQUEST["action"] == 'removegroup') {
		$gBitUser->removeUserFromGroup($_REQUEST["user"], $_REQUEST["group"]);
	}
}

// get default group and pass it to tpl
foreach( $gBitUser->getDefaultGroup() as $defaultGroupId => $defaultGroupName ) {
	$gBitSmarty->assign('defaultGroupId', $defaultGroupId );
	$gBitSmarty->assign('defaultGroupName', $defaultGroupName );
}

$_REQUEST['max_records'] = 20;
$gBitUser->getList( $_REQUEST );
$gBitSmarty->assign_by_ref('users', $_REQUEST["data"]);
$gBitSmarty->assign_by_ref('usercount', $_REQUEST["cant"]);
if (isset($_REQUEST["numrows"]))
	$_REQUEST["control"]["numrows"] = $_REQUEST["numrows"];
else
	$_REQUEST["control"]["numrows"] = 10;
$_REQUEST["control"]["URL"] = USERS_PKG_URL."admin/index.php";
$gBitSmarty->assign_by_ref('control', $_REQUEST["control"]);

$gBitUser->invokeServices( 'content_edit_function' );

// Get groups (list of groups)
$grouplist = $gBitUser->getGroups('', '', 'group_name_asc');
$gBitSmarty->assign( 'grouplist', $grouplist );
$gBitSmarty->assign( 'feedback', $feedback );

$gBitSmarty->assign( (!empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'userlist').'TabSelect', 'tdefault' );

// Display the template
$gBitSystem->display( 'bitpackage:users/users_admin.tpl', (!empty( $title ) ? $title : 'Edit Users' ) );
?>
