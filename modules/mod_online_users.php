<?php
global $gBitUser;
$online_users = $gBitUser->get_online_users();
$smarty->assign('online_users', $online_users);
$smarty->assign('logged_users', count( $online_users ) );
?>
