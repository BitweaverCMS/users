<?php
// $Header$
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See below for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.
// Initialization
require_once( '../../kernel/includes/setup_inc.php' );

$gBitSystem->verifyPermission( 'p_users_admin' );

$feedback = array();

if( isset($_REQUEST["newuser"] ) ) {
	$userRecord = $_REQUEST;
	$newUser = new BitPermUser();
	if( $newUser->importUser( $userRecord ) ) {
		$gBitSmarty->assign( 'addSuccess', "User Added Successfully" );
		if( empty( $_REQUEST['admin_noemail_user'] ) ) {
			$ret = users_admin_email_user( $userRecord );
			if( is_array( $ret ) ) {
				list($key, $val) = each($ret);
				$newUser->mLogs[$key] = $val;
			}
			$logHash['action_log']['title'] = $userRecord['login'];
			$newUser->storeActionLog( $logHash );
		}
	} else {
		$gBitSmarty->assignByRef( 'newUser', $_REQUEST );
		$gBitSmarty->assign( 'errors', $newUser->mErrors );
	}
} elseif( isset( $_REQUEST["assume_user"]) && $gBitUser->hasPermission( 'p_users_admin' ) ) {
	$assume_user = (is_numeric( $_REQUEST["assume_user"] )) ? array( 'user_id' => $_REQUEST["assume_user"] ) : array('login' => $_REQUEST["assume_user"]) ;
	$userInfo = $gBitUser->getUserInfo( $assume_user );
	if( isset( $_REQUEST["confirm"] ) ) {
		$gBitUser->verifyTicket();
		if( $gBitUser->assumeUser( $userInfo["user_id"] ) ) {
			header( 'Location: '.$gBitSystem->getDefaultPage() );
			die;
		}elseif( !empty( $gBitUser->mErrors ) ){
			if ( !isset( $feedback['error'] ) ){
				$feedback['error'] = array();
			}
			$feedback['error'] = array_merge( $feedback['error'], $gBitUser->mErrors ); 
		}
	} else {
		$gBitSystem->setBrowserTitle( 'Assume User Identity' );
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
// Remove user or remove user from team
if( isset( $_REQUEST["action"] ) ) {
	$formHash['action'] = $_REQUEST['action'];
	if( !empty( $_REQUEST['batch_user_ids'] ) && is_array( $_REQUEST['batch_user_ids'] ) ) {
		if( $formHash['action'] == 'export' ) {
			$file = tempnam( sys_get_temp_dir(), 'users' );
			$fp = fopen($file, 'w');
			$printHeader = TRUE;
			foreach( $_REQUEST['batch_user_ids'] as $uid ) {
				$listUser = BitUser::getUserObject( $uid );
				$hash = $listUser->exportHash();
				if( $printHeader ) {
					fputcsv( $fp, array_keys( $hash ) );
					$printHeader = FALSE;
				}
				fputcsv( $fp, $hash );
			}
			fclose( $fp );
		    header( "Content-Type: text/csv" );
			header('Content-disposition: attachment;filename='.$gBitSystem->getConfig('site_title', 'Site').'-users-export-'.date('Y-m-d_Hi').'.csv');
			readfile( $file );
			flush();
			unlink( $file );
			exit;
		} elseif( isset( $_REQUEST["confirm"] ) ) {
			$gBitUser->verifyTicket();
			$delUsers = $errDelUsers = "";
			foreach( $_REQUEST['batch_user_ids'] as $uid ) {
				$expungeUser = BitUser::getUserObject( $uid );
				$userInfo = $gBitUser->getUserInfo( array( 'user_id' => $uid ) );
				if( $expungeUser->load() && $expungeUser->expunge( BitBase::getParameter( $_REQUEST, 'delete_user_content' ) ) ) {
					$delUsers .= "<li>{$userInfo['real_name']} ({$userInfo['login']})</li>";
				} else {
					$errDelUsers .= "<li>User $uid could not be deleted:".var_export( $expungeUser->mErrors, TRUE )."</li>";
				}
			}

			if( !empty( $delUsers ) ) {
				$feedback['success'][] = tra( 'Users deleted' ).": <ul>$delUsers</ul>";
			} 
			if( !empty( $errDelUsers ) ) {
				$feedback['error'][] = tra( 'Users not deleted' ).": <ul>$errDelUsers</ul>";
			}
		} else {
			foreach( $_REQUEST['batch_user_ids'] as $uid ) {
				if( $userInfo = $gBitUser->getUserInfo( array( 'user_id' => $uid ) ) ) {
					$formHash['input'][] = '<input type="hidden" name="batch_user_ids[]" value="'.$uid.'"/>'."{$userInfo['real_name']} ({$userInfo['login']})<br/>&lt;{$userInfo['email']}&gt;";
				} else {
					$formHash['input'][] = '<span class="error"/>'.$uid.' '.tra('not found').'</span>';
				}
			}
			$formHash['input'][] = "<input type='checkbox' name='delete_user_content' value='all' checked='checked'/> ".tra( 'Delete all content created by this user' );
			$gBitSystem->setBrowserTitle( 'Delete users' );
			$msgHash = array(
				'confirm_item' => tra( 'Are you sure you want to remove these users?' ),
				'warning' => tra( 'This will permentally delete these users' ),
			);
			$gBitSystem->confirmDialog( $formHash, $msgHash );
		}
	} elseif( $_REQUEST["action"] == 'delete' ||  $_REQUEST["action"] == 'ban' ||  $_REQUEST["action"] == 'unban'  ) {
		$formHash['user_id'] = $_REQUEST['user_id'];
		$userInfo = $gBitUser->getUserInfo( array( 'user_id' => $_REQUEST["user_id"] ) );
		if( !empty( $userInfo['user_id'] ) ) {
				$userClass = $gBitSystem->getConfig( 'user_class', 'BitPermUser' );
				$reqUser = new $userClass( $_REQUEST["user_id"] );
			if( isset( $_REQUEST["confirm"] ) ) {
				$gBitUser->verifyTicket();
				switch(  $_REQUEST["action"] ){
					case 'delete':
						$reqUser->StartTrans();
						if( $reqUser->load(TRUE) && $reqUser->expunge( !empty( $_REQUEST['delete_user_content'] ) ? $_REQUEST['delete_user_content'] : NULL ) ) {
							$feedback['success'][] = tra( 'User deleted' )." <strong>{$userInfo['real_name']} ({$userInfo['login']}) &lt;{$userInfo['email']}&gt;</strong>";
						}
						$reqUser->CompleteTrans();
						break;
					case 'ban':
						if( $reqUser->load() && $reqUser->ban() ) {
							$feedback['success'][] = tra( 'User banned' )." <strong>{$userInfo['real_name']} ({$userInfo['login']})</strong>";
						}
						break;
					case 'unban':
						if( $reqUser->load() && $reqUser->unban() ) {
							$feedback['success'][] = tra( 'User restored' )." <strong>{$userInfo['real_name']} ({$userInfo['login']})</strong>";
						}
						break;
				}
			} else {
				switch( $_REQUEST["action"] ){
					case 'delete':
						$gBitSystem->setBrowserTitle( tra( 'Delete user' ).' '.$reqUser->getDisplayName() );
			$reqUser->invokeServices( 'users_expunge_check_function' );
			if( !empty( $reqUser->mErrors['expunge_check'] ) ) {
				$feedback['error'] = $reqUser->mErrors;
			} else {
						$formHash['input'][] = "<div class='checkbox'><label><input type='checkbox' name='delete_user_content' value='all' checked='checked'/>".tra( 'Delete all content created by this user' ).'</label></div>';
						foreach( $gLibertySystem->mContentTypes as $contentTypeGuid => $contentTypeHash ) {
//							$formHash['input'][] = "<input type='checkbox' name='delete_user_content' checked='checked' value='$contentTypeGuid'/>Delete All User's $gLibertySystem->getContentTypeName($contentTypeHash['content_type_guid'],TRUE)";
						}

						$msgHash = array(
							'confirm_item' => tra( 'Are you sure you want to remove the user?' ),
							'warning' => tra( 'This will permentally delete the user' )." <strong>$userInfo[real_name] ($userInfo[login]) &lt;$userInfo[email]&gt;</strong>",
						);
						$gBitSystem->confirmDialog( $formHash,$msgHash );
			}
						break;
					case 'ban':
						$gBitSystem->setBrowserTitle( tra( 'Disable User' ) );
						$msgHash = array(
							'confirm_item' => tra( 'Are you sure you want to disable this user account?' ),
							'warning' => tra( 'This will suspend access for user' )." <strong>$userInfo[real_name] ($userInfo[login])</strong>",
						);
						$gBitSystem->confirmDialog( $formHash,$msgHash );
						break;
					case 'unban':
						$gBitSystem->setBrowserTitle( tra( 'Re-enable user' ) );
						$msgHash = array(
							'confirm_item' => tra( 'Are you sure you want to re-enable this user?' ),
							'warning' => tra( 'This will restore access for user' )." <strong>$userInfo[real_name] ($userInfo[login])</strong>",
						);
						$gBitSystem->confirmDialog( $formHash,$msgHash );
						break;
				}
			}
		} else {
			$feedback['error'][] = tra( 'User not found' );
		}
	}
	if ($_REQUEST["action"] == 'removerole') {
		$gBitUser->removeUserFromRole($_REQUEST["user"], $_REQUEST["role"]);
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

// override default max_records
$listHash = $_REQUEST;
$listHash['max_records'] = !empty( $_REQUEST['max_records'] ) ? $_REQUEST['max_records'] : $gBitSystem->getConfig('max_records');
$users = $gBitUser->getList( $listHash );
$gBitSmarty->assignByRef('users', $users );
$gBitSmarty->assignByRef('usercount', $listHash["cant"]);
if (isset($listHash["numrows"])) {
	$listHash['listInfo']["numrows"] = $listHash["numrows"];
} else {
	$listHash['listInfo']["numrows"] = 10;
}
$listHash['listInfo']["URL"] = USERS_PKG_URL."admin/index.php";
$gBitSmarty->assignByRef('listInfo', $listHash['listInfo']);

// invoke edit service for the add user feature
$userObj = new BitPermUser();
$userObj->invokeServices( 'content_edit_function' );	// Get groups (list of groups)
$grouplist = $gBitUser->getGroups('', '', 'group_name_asc');
$gBitSmarty->assign( 'grouplist', $grouplist );
$gBitSmarty->assign( 'feedback', $feedback );

$gBitSmarty->assign( (!empty( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : 'userlist').'TabSelect', 'tdefault' );

// Display the template
$gBitSystem->display( 'bitpackage:users/admin_list_users.tpl', (!empty( $title ) ? $title : 'Edit Users' ) , array( 'display_mode' => 'admin' ));
