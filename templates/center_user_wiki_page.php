<?php

global $gQueryUser;

include_once( USERS_PKG_PATH.'lookup_user_inc.php' );

$parsed = $gQueryUser->parseData();
$gBitSmarty->assignByRef( 'parsed', $parsed );

?>
