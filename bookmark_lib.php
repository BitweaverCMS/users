<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/Attic/bookmark_lib.php,v 1.1.1.1.2.5 2005/11/16 19:11:57 mej Exp $
 *
 * Lib for user administration, groups and permissions
 * This lib uses pear so the constructor requieres
 * a pear DB object
 
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: bookmark_lib.php,v 1.1.1.1.2.5 2005/11/16 19:11:57 mej Exp $
 * @package users
 */

/**
 * @package users
 * @subpackage BookmarkLib
 */
class BookmarkLib extends BitBase {
	function BookmarkLib() {
		BitBase::BitBase();
	}
	function get_folder_path($folder_id, $user_id) {
		$path = '';
		$info = $this->get_folder($folder_id, $user_id);
		$path = '<a href='.USERS_PKG_URL.'bookmarks.php?parent_id="' . $info["folder_id"] . '">' . $info["name"] . '</a>';
		while ($info["parent_id"] != 0) {
			$info = $this->get_folder($info["parent_id"], $user_id);
			$path
				= $path = '<a href='.USERS_PKG_URL.'bookmarks.php?parent_id="' . $info["folder_id"] . '">' . $info["name"] . '</a>' . '>' . $path;
		}
		return $path;
	}
	function get_folder($folder_id, $user_id) {
		$query = "select * from `".BIT_DB_PREFIX."tiki_user_bookmarks_folders` where `folder_id`=? and `user_id`=?";
		$result = $this->mDb->query($query,array($folder_id,$user_id));
		if (!$result->numRows())
			return false;
		$res = $result->fetchRow();
		return $res;
	}
	function get_url($url_id) {
		$query = "select * from `".BIT_DB_PREFIX."tiki_user_bookmarks_urls` where `url_id`=?";
		$result = $this->mDb->query($query,array($url_id));
		if (!$result->numRows())
			return false;
		$res = $result->fetchRow();
		return $res;
	}
	function remove_url($url_id, $user_id) {
		$query = "delete from `".BIT_DB_PREFIX."tiki_user_bookmarks_urls` where `url_id`=? and `user_id`=?";
		$result = $this->mDb->query($query,array($url_id,$user_id));
		return true;
	}
	function remove_folder($folder_id, $user_id) {
		// Delete the category
		$query = "delete from `".BIT_DB_PREFIX."tiki_user_bookmarks_folders` where `folder_id`=? and `user_id`=?";
		$result = $this->mDb->query($query,array($folder_id,$user_id));
		// Remove objects for this category
		$query = "delete from `".BIT_DB_PREFIX."tiki_user_bookmarks_urls` where `folder_id`=? and `user_id`=?";
		$result = $this->mDb->query($query,array($folder_id,$user_id));
		// SUbfolders
		$query = "select `folder_id` from `".BIT_DB_PREFIX."tiki_user_bookmarks_folders` where `parent_id`=? and `user_id`=?";
		$result = $this->mDb->query($query,array($folder_id,$user_id));
		while ($res = $result->fetchRow()) {
			// Recursively remove the subcategory
			$this->remove_folder($res["folder_id"], $user_id);
		}
		return true;
	}
	function update_folder($folder_id, $name, $user_id) {
		$query = "update `".BIT_DB_PREFIX."tiki_user_bookmarks_folders` set `name`=? where `folder_id`=? and `user_id`=?";
		$result = $this->mDb->query($query,array($name,$folder_id,$user_id));
	}
	function add_folder($parent_id, $name, $user_id) {
		// Don't allow empty/blank folder names.
		if (empty($name))
			return false;
		$query = "insert into `".BIT_DB_PREFIX."tiki_user_bookmarks_folders`(`name`,`parent_id`,`user_id`) values(?,?,?)";
		$result = $this->mDb->query($query,array($name,$parent_id,$user_id));
	}
	function replace_url($url_id, $folder_id, $name, $url, $user_id) {
		$id = NULL;
		if( strlen( $url ) < 250 ) {
			global $gBitSystem;
			$now = $gBitSystem->getUTCTime();
			if ($url_id) {
				$query = "update `".BIT_DB_PREFIX."tiki_user_bookmarks_urls` set `user_id`=?,`last_updated`=?,`folder_id`=?,`name`=?,`url`=? where `url_id`=?";
				$bindvars=array($user_id,(int) $now,$folder_id,$name,$url,$url_id);
			} else {
				$query = " insert into `".BIT_DB_PREFIX."tiki_user_bookmarks_urls`(`name`,`url`,`data`,`last_updated`,`folder_id`,`user_id`)
		  values(?,?,?,?,?,?)";
					$bindvars=array($name,$url,'',(int) $now,$folder_id,$user_id);
			}
			$result = $this->mDb->query($query,$bindvars);
			$id = $this->mDb->getOne("select max(`url_id`) from `".BIT_DB_PREFIX."tiki_user_bookmarks_urls` where `url`=? and `last_updated`=?",array($url,(int) $now));
		}
		return $id;
	}
	function refresh_url($url_id) {
		$info = $this->get_url($url_id);
		if (strstr($info["url"], 'tiki_') || strstr($info["url"], 'messu_'))
			return false;
		@$fp = fopen($info["url"], "r");
		if (!$fp)
			return;
		$data = '';
		while (!feof($fp)) {
			$data .= fread($fp, 4096);
		}
		fclose ($fp);
		global $gBitSystem;
		$now = $gBitSystem->getUTCTime();
		$query = "update `".BIT_DB_PREFIX."tiki_user_bookmarks_urls` set `last_updated`=?, `data`=? where `url_id`=?";
		$result = $this->mDb->query($query,array((int) $now,BitDb::db_byte_encode( $data ),$url_id));
		return true;
	}
	function list_folder($folder_id, $offset, $maxRecords, $sort_mode = 'name_asc', $find, $user_id) {
		if ($find) {
			$findesc = '%' . strtoupper( $find ) . '%';
			$mid = " and UPPER(`name`) like ? or UPPER(`url`) like ?";
			$bindvars=array($folder_id,$user_id,$findesc,$findesc);
		} else {
			$mid = "";
			$bindvars=array($folder_id,$user_id);
		}
		$query = "select * from `".BIT_DB_PREFIX."tiki_user_bookmarks_urls` where `folder_id`=? and `user_id`=? $mid order by ".$this->mDb->convert_sortmode($sort_mode);
		$query_cant = "select count(*) from `".BIT_DB_PREFIX."tiki_user_bookmarks_urls` where `folder_id`=? and `user_id`=? $mid";
		$result = $this->mDb->query($query,$bindvars,$maxRecords,$offset);
		$cant = $this->mDb->getOne($query_cant,$bindvars);
		$ret = array();
		while ($res = $result->fetchRow()) {
			$res["datalen"] = strlen($res["data"]);
			$ret[] = $res;
		}
		$retval = array();
		$retval["data"] = $ret;
		$retval["cant"] = $cant;
		return $retval;
	}
	function get_child_folders($folder_id, $user_id) {
		$ret = array();
		$query = "select * from `".BIT_DB_PREFIX."tiki_user_bookmarks_folders` where `parent_id`=? and `user_id`=?";
		$result = $this->mDb->query($query,array($folder_id,$user_id));
		while ($res = $result->fetchRow()) {
			$cant = $this->mDb->getOne("select count(*) from `".BIT_DB_PREFIX."tiki_user_bookmarks_urls` where `folder_id`=?",array($res["folder_id"]));
			$res["urls"] = $cant;
			$ret[] = $res;
		}
		return $ret;
	}
}
global $bookmarklib;
$bookmarklib = new BookmarkLib();
?>
