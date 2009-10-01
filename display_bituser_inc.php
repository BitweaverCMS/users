<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/display_bituser_inc.php,v 1.5 2009/10/01 14:17:06 wjames5 Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See below for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
 *
 * $Id: display_bituser_inc.php,v 1.5 2009/10/01 14:17:06 wjames5 Exp $
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
