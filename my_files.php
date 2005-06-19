<?php
require_once("../bit_setup_inc.php");
global $gBitSystem;

if (!$gBitUser->mUserId) {
	$smarty->assign('msg', tra("You are not logged in"));
	$gBitSystem->display( 'error.tpl' );
	die;
}

$userFiles = $gBitUser->getUserFiles();
$smarty->assign_by_ref('userFiles', $userFiles['files']);
$smarty->assign('numUserFiles', count($userFiles['files']));
$smarty->assign('diskUsage', $userFiles['diskUsage']);

if (!empty($_REQUEST['deleteAttachment'])) {
	$attachmentId = $_REQUEST['deleteAttachment'];
}

$gBitSystem->display('bitpackage:users/my_files.tpl');
?>
