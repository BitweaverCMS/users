<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/modules/Attic/mod_user_tasks.php,v 1.1.1.1.2.1 2005/06/27 17:47:26 lsces Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: mod_user_tasks.php,v 1.1.1.1.2.1 2005/06/27 17:47:26 lsces Exp $
 * @package users
 * @subpackage modules
 */

/**
 * required setup
 */
require_once(USERS_PKG_PATH."task_lib.php");
global $user, $feature_tasks, $bit_p_tasks, $tasklib;

if ($user && isset($feature_tasks) && $feature_tasks == 'y' && isset($bit_p_tasks) && $gBitUser->hasPermission( 'bit_p_tasks' )) {
	if (isset($_SESSION['thedate'])) {
		$pdate = $_SESSION['thedate'];
	} else {
		$pdate = date("U");
	}
	if (isset($_REQUEST["modTasksDel"])) {
		foreach (array_keys($_REQUEST["modTasks"])as $task) {
			$tasklib->remove_task($user, $task);
		}
	}
	if (isset($_REQUEST["modTasksCom"])) {
		foreach (array_keys($_REQUEST["modTasks"])as $task) {
			$tasklib->complete_task($user, $task);
		}
	}
	if (isset($_REQUEST["modTasksSave"])) {
		$tasklib->replace_task($user, 0, $_REQUEST['modTasksTitle'], $_REQUEST['modTasksTitle'], date("U"), 'o', 3, 0, 0);
	}
	$ownurl =/*httpPrefix().*/ $_SERVER["REQUEST_URI"];
	$smarty->assign('ownurl', $ownurl);
	$tasks_use_dates = $gBitSystem->get_user_preference($user, 'tasks_use_dates');
	$modTasks = $gBitSystem->list_tasks($user, 0, -1, 'priority_desc', '', $tasks_use_dates, $pdate);
	$smarty->assign('modTasks', $modTasks['data']);
}
?>
