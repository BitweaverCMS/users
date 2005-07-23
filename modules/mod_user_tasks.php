<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/modules/Attic/mod_user_tasks.php,v 1.1.1.1.2.2 2005/07/23 04:43:23 wolff_borg Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: mod_user_tasks.php,v 1.1.1.1.2.2 2005/07/23 04:43:23 wolff_borg Exp $
 * @package users
 * @subpackage modules
 */

/**
 * required setup
 */
require_once(USERS_PKG_PATH."task_lib.php");
global $gBitUser, $gBitSystem, $tasklib;

if ($gBitUser->getUserId() > 0 && $gBitSystem->isFeatureActive('feature_tasks') && $gBitUser->hasPermission( 'bit_p_tasks' )) {
	if (isset($_SESSION['thedate'])) {
		$pdate = $_SESSION['thedate'];
	} else {
		$pdate = date("U");
	}
	if (isset($_REQUEST["modTasksDel"])) {
		foreach (array_keys($_REQUEST["modTasks"])as $task) {
			$tasklib->remove_task($gBitUser->getUserId(), $task);
		}
	}
	if (isset($_REQUEST["modTasksCom"])) {
		foreach (array_keys($_REQUEST["modTasks"])as $task) {
			$tasklib->complete_task($gBitUser->getUserId(), $task);
		}
	}
	if (isset($_REQUEST["modTasksSave"])) {
		$tasklib->replace_task($gBitUser->getUserId(), 0, $_REQUEST['modTasksTitle'], $_REQUEST['modTasksTitle'], date("U"), 'o', 3, 0, 0);
	}
	$ownurl =/*httpPrefix().*/ $_SERVER["REQUEST_URI"];
	$smarty->assign('ownurl', $ownurl);
	$tasks_use_dates = $gBitUser->getPreference('tasks_use_dates');
	$modTasks = $tasklib->list_tasks($gBitUser->getUserId(), 0, -1, 'priority_desc', '', $tasks_use_dates, $pdate);
	$smarty->assign('modTasks', $modTasks['data']);
}
?>
