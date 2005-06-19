<?php
global $userlib;
$logged_users = $gBitUser->count_sessions();
$online_users = $gBitUser->get_online_users();
$smarty->assign('online_users', $online_users);
$smarty->assign('logged_users', $logged_users);
?>
