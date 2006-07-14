<?php

	require_once( '../../bit_setup_inc.php' );
	
	$listHash = array();
	$gBitSmarty->assign_by_ref( 'userActivity', $gBitUser->getUserActivity( $listHash ) );
	
	$gBitSystem->display( 'bitpackage:users/user_activity.tpl', 'User Activity' );

?>
