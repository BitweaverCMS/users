<?php

require_once( '../../kernel/includes/setup_inc.php' );

$listHash = $_REQUEST;

if( @BitBase::verifyId( $_REQUEST['user_id'] ) ) {
	$listHash['user_id'] = $_REQUEST['user_id'];
}

if( !empty( $_REQUEST['user_agent'] ) ) {
	$listHash['user_agent'] = $_REQUEST['user_agent'];
}

if( !empty( $_REQUEST['ip'] ) ) {
	$listHash['ip'] = $_REQUEST['ip'];
}

if( $userId = (int)BitBase::getParameter( $_REQUEST, 'user_id' ) ) {
	$listHash['user_id'] = $userId;
	$gBitSmarty->assign( 'userInfo',  $gBitUser->getUserInfo( array( 'user_id' => $userId ) ) );
}

$gBitSmarty->assign( 'userActivity', $gBitUser->getUserActivity( $listHash ));
$gBitSmarty->assignByRef( 'listInfo', $listHash['listInfo'] );
$gBitSystem->display( 'bitpackage:users/user_activity.tpl', 'User Activity' , array( 'display_mode' => 'admin' ));

