<?php
// $Header$
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See below for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details.
// Initialization
require_once( '../../kernel/includes/setup_inc.php' );

if( $validatedGroup = $gBitSystem->getConfig( 'users_validate_email_group' ) ) {	
	$gBitUser->verifyTicket();

	$whereSql = '';
	$bindVars = array( $gBitSystem->getConfig('users_validate_email_group') );
	if( !empty( $_REQUEST['start_user_id'] ) ) {
		$whereSql = " AND user_id>?";
		$bindVars[] = $_REQUEST['start_user_id'];
	}

	$selectSql = "SELECT uu.user_id,uu.email  FROM users_users uu WHERE user_id NOT IN (SELECT user_id FROM users_groups_map WHERE group_id = ?) $whereSql ORDER BY uu.user_id";
	$users     = $gBitDb->getAssoc($selectSql, $bindVars );
	$errors;
	foreach ( $users as $id=>$email ){
		print date( "Y-m-d H:i:s" )." Verifying $email ( $id ) .... ";
		flush();
		$emailStatus = $gBitUser->verifyMx($email,$errors);
		if( $emailStatus === true){
			$gBitUser->addUserToGroup( $id , $validatedGroup );
			print "valid";
		} elseif( $emailStatus === -1 )  {
			print "MX connection failed";
		} else {
			print " --INVALID-- ";
		}
		print "<br/>\n";
		flush();
	}
} else {
	
}

