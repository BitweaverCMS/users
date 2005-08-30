<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/Attic/task_lib.php,v 1.4 2005/08/30 22:37:36 squareing Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: task_lib.php,v 1.4 2005/08/30 22:37:36 squareing Exp $
 * @package users
 */

/**
 * Task properties:
 *  user, task_id, title, description, date, status, priority, completed, percentage
 * @package users
 * @subpackage TaskLib
 */
class TaskLib extends BitBase {

	function TaskLib() {
		BitBase::BitBase();
	}
	function get_task( $pUserId,  $task_id) {
		$query = "select * from `".BIT_DB_PREFIX."tiki_user_tasks` where `user_id`=? and `task_id`=?";
		$result = $this->mDb->query($query,array( $pUserId, (int)$task_id));
		$res = $result->fetchRow();
		return $res;
	}

	function update_task_percentage( $pUserId,  $task_id, $perc) {
		$query = "update `".BIT_DB_PREFIX."tiki_user_tasks` set `percentage`=? where `user_id`=? and `task_id`=?";
		$this->mDb->query($query,array((int)$perc, $pUserId, (int)$task_id));
	}

	function open_task( $pUserId,  $task_id) {
		$query = "update `".BIT_DB_PREFIX."tiki_user_tasks` set `completed`=?, `status`=?, `percentage`=? where `user_id`=? and `task_id`=?";
		$this->mDb->query($query, array(0,'o',0, $pUserId, (int)$task_id));
	}

	function replace_task( $pUserId,  $task_id, $title, $description, $date, $status, $priority, $completed, $percentage) {
		if ($task_id != 0) {
			$query = "update `".BIT_DB_PREFIX."tiki_user_tasks` set `title` = ?, `description` = ?, `date` = ?, `status` = ?, `priority` = ?, ";
			$query.= "`percentage` = ?, `completed` = ?  where `user_id`=? and `task_id`=?";
			$this->mDb->query($query,array($title,$description,$date,$status,$priority,$percentage,$completed, $pUserId, $task_id));
			return $task_id;
		} else {
			$query = "insert into `".BIT_DB_PREFIX."tiki_user_tasks`(`user_id`,`title`,`description`,`date`,`status`,`priority`,`completed`,`percentage`) ";
			$query.= " values(?,?,?,?,?,?,?,?)";
			$this->mDb->query($query,array($pUserId,$title,$description,$date,$status,$priority,$completed,$percentage));
			$task_id = $this->mDb->getOne( "select  max(`task_id`) from `".BIT_DB_PREFIX."tiki_user_tasks` where `user_id`=? and `title`=? and `date`=?",array( $pUserId, $title,$date));
			return $task_id;
		}
	}

	function complete_task( $pUserId,  $task_id) {
		global $gBitSystem;
		$now = $gBitSystem->getUTCTime();
		$query = "update `".BIT_DB_PREFIX."tiki_user_tasks` set `completed`=?, `status`='c', `percentage`=100 where `user_id`=? and `task_id`=?";
		$this->mDb->query($query,array((int)$now, $pUserId, (int)$task_id));
	}

	function remove_task( $pUserId,  $task_id) {
		$query = "delete from `".BIT_DB_PREFIX."tiki_user_tasks` where `user_id`=? and `task_id`=?";
		$this->mDb->query($query,array( $pUserId, (int)$task_id));
	}

	function list_tasks( $pUserId,  $offset, $maxRecords, $sort_mode, $find, $use_date, $pdate) {
		global $gBitSystem;
		$now = $gBitSystem->getUTCTime();
		$bindvars=array($pUserId);
		if ($use_date == 'y') {
			$prio = " and `date`<=? ";
			$bindvars2=$pdate;
		} 
		else {
			$prio = '';
		}

		if ($find) {
			$findesc = '%' . strtoupper( $find ). '%';
			$mid = " and (UPPER(`title`) like ? or UPPER(`description`) like ?)";
			$bindvars[]=$findesc;
			$bindvars[]=$findesc;
		} else {
			$mid = "" ;
		}

		$mid.=$prio;
		if (isset($bindvars2)) 
			$bindvars[]=$bindvars2;

		$query = "select * from `".BIT_DB_PREFIX."tiki_user_tasks` where `user_id`=? $mid order by ".$this->mDb->convert_sortmode($sort_mode).",`task_id` desc";
		$query_cant = "select count(*) from `".BIT_DB_PREFIX."tiki_user_tasks` where `user_id`=? $mid";
		$result = $this->mDb->query($query,$bindvars,$maxRecords,$offset);
		$cant = $this->mDb->getOne($query_cant,$bindvars);
		$ret = array();

		while ($res = $result->fetchRow()) {
			$ret[] = $res;
		}

		$retval = array();
		$retval["data"] = $ret;
		$retval["cant"] = $cant;
		return $retval;
	}

}
global $tasklib;
$tasklib = new TaskLib();
?>
