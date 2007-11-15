<?php

function users_admin_email_user( &$pParamHash ) {
	global $gBitSmarty, $gBitSystem;

	$ret = FALSE;
	$siteName = $gBitSystem->getConfig('site_title', $_SERVER['HTTP_HOST'] );
	$gBitSmarty->assign('siteName',$_SERVER["SERVER_NAME"]);
	$gBitSmarty->assign('mail_site',$_SERVER["SERVER_NAME"]);
	$gBitSmarty->assign('mail_user',$pParamHash['login']);
	if( !empty( $_REQUEST['admin_verify_user'] ) && !empty($pParamHash['user_store']['provpass']) ) {
		$apass = addslashes(substr(md5($gBitSystem->genPass()),0,25));
		$apass = $pParamHash['user_store']['provpass'];
		$machine = httpPrefix().USERS_PKG_URL.'confirm.php';
		// Send the mail
		$gBitSmarty->assign('mail_machine',$machine);
		$gBitSmarty->assign('mailUserId',$pParamHash['user_store']['user_id']);
		$gBitSmarty->assign('mailProvPass',$apass);
		$mail_data = $gBitSmarty->fetch('bitpackage:users/admin_validation_mail.tpl');
		mail($pParamHash['email'], $siteName.' - '.tra('Your registration information'),$mail_data,"From: ".$gBitSystem->getConfig('site_sender_email')."\r\nContent-type: text/plain;charset=utf-8\r\n");
		$gBitSmarty->assign('showmsg','n');
		
		$ret = array('confirm' => 'Validation email sent to ' . $pParamHash['email'] . '.');
	} elseif( !empty( $pParamHash['password'] ) ) {
		// Send the welcome mail
		$gBitSmarty->assign( 'mailPassword',$pParamHash['password'] );
		$gBitSmarty->assign( 'mailEmail',$pParamHash['email'] );
		$mail_data = $gBitSmarty->fetch('bitpackage:users/admin_welcome_mail.tpl');
		mail($pParamHash["email"], tra( 'Welcome to' ).' '.$siteName,$mail_data,"From: ".$gBitSystem->getConfig('site_sender_email')."\r\nContent-type: text/plain;charset=utf-8\r\n");
		$ret = array('welcome'=>'Welcome email sent to ' . $pParamHash['email'] . '.');
	}
	return $ret;
}
?>