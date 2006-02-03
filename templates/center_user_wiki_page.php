<?php

global $gQueryUser;

include_once( USERS_PKG_PATH.'lookup_user_inc.php' );

$parsed = $gQueryUser->parseData();
$gBitSmarty->assign_by_ref( 'parsed', $parsed );

?>
