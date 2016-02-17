<?php
/**
 * user profile page
 *
 * @copyright (c) 2004-15 bitweaver.org
 *
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */

	// this first version is a bit incomplete, but at least things work now. - spiderr

	include USERS_PKG_PATH.'templates/center_user_wiki_page.php';
	$gBitSystem->display( 'bitpackage:users/center_user_wiki_page.tpl' , NULL, array( 'display_mode' => 'display' ));

?>
