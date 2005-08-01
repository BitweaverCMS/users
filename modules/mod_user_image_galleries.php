<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/modules/Attic/mod_user_image_galleries.php,v 1.3 2005/08/01 18:42:03 squareing Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: mod_user_image_galleries.php,v 1.3 2005/08/01 18:42:03 squareing Exp $
 * @package users
 * @subpackage modules
 */
$ranking = $gBitSystem->get_user_galleries($user, $module_rows);
$gBitSmarty->assign('modUserG', $ranking);
?>
