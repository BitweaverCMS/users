<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/modules/mod_since_last_visit.php,v 1.3 2005/07/17 17:36:44 squareing Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: mod_since_last_visit.php,v 1.3 2005/07/17 17:36:44 squareing Exp $
 * @package users
 * @subpackage modules
 */
if( $gBitSystem->isPackageActive( 'bitforums' ) ) {
	$nvi_info = $gBitSystem->get_news_from_last_visit($user);
	$smarty->assign('nvi_info', $nvi_info);
}

?>
