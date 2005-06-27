<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/show_user_avatar.php,v 1.1.1.1.2.1 2005/06/27 17:47:59 lsces Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: show_user_avatar.php,v 1.1.1.1.2.1 2005/06/27 17:47:59 lsces Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
include_once( USERS_PKG_PATH.'userprefs_lib.php' );
// application to display an image from the database with
// option to resize the image dynamically creating a thumbnail on the fly.
// you have to check if the user has permission to see this gallery
if (!isset($_REQUEST["user"])) {
	die;
}
$info = $userprefslib->get_user_avatar_img($_REQUEST["user"]);
$type = $info["avatar_file_type"];
$content = $info["avatar_data"];
header ("Content-type: $type");
echo "$content";
?>
