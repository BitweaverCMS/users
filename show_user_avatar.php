<?php
/**
 * show user avatar
 *
 * @copyright (c) 2004-15 bitweaver.org
 *
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
include_once( USERS_PKG_INCLUDE_PATH.'userprefs_lib.php' );
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
