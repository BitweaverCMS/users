<?php
// $Header: /cvsroot/bitweaver/_bit_users/admin/verify_emails.php,v 1.2 2009/09/01 20:43:06 tylerbello Exp $
// Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
// All Rights Reserved. See copyright.txt for details and a complete list of authors.
// Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details.
// Initialization
require_once( '../../bit_setup_inc.php' );

	
	$gBitUser->verifyTicket();

		$selectSql = 'SELECT group_id from users_groups ug where group_name = \''.$gBitSystem->getConfig('users_validate_email_group').'\'';
		$groupId   = $gBitDb->getOne($selectSql);
		$selectSql = 'SELECT uu.user_id,uu.email  FROM users_users uu INNER JOIN users_groups_map ugm ON ( ugm.user_id = uu.user_id ) INNER JOIN users_groups ug ON ( ug.group_id = ugm.group_id) WHERE group_name !=\''.$groupId.'\'';
		$users     = $gBitDb->getAssoc($selectSql);
		$errors;
		foreach ( $users as $id=>$email ){
			print "Verifying $email ( $id ) .... ";
			$emailStatus = $gBitUser->verifyMx($email,$errors);
			if( $emailStatus === true){
				$gBitUser->addUserToGroup( $id , $groupId );
				print "valid";
			} elseif( $emailStatus === -1 )  {
				print "MX connection failed";
			} else {
				print " --INVALID-- ";
			}
			print "<br/>\n";
			flush();
		}


