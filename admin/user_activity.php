<?php

	require_once( '../../bit_setup_inc.php' );
	
	$listHash = array();
	
	if( @BitBase::verifyId( $_REQUEST['user_id'] ) ) {
		$listHash['user_id'] = $_REQUEST['user_id'];
	}

	if( !empty( $_REQUEST['user_agent'] ) ) {
		$listHash['user_agent'] = $_REQUEST['user_agent'];
	}

	if( !empty( $_REQUEST['ip'] ) ) {
		$listHash['ip'] = $_REQUEST['ip'];
	}

	if( @BitBase::verifyId( $_REQUEST['user_id'] ) ) {
		$listHash['user_id'] = $_REQUEST['user_id'];
	}

	$gBitSmarty->assign_by_ref( 'userActivity', $gBitUser->getUserActivity( $listHash ) );
	
	$gBitSystem->display( 'bitpackage:users/user_activity.tpl', 'User Activity' );

?>
