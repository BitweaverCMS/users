<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/modules/mod_since_last_visit.php,v 1.6 2009/10/01 14:17:06 wjames5 Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See below for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
 *
 * $Id: mod_since_last_visit.php,v 1.6 2009/10/01 14:17:06 wjames5 Exp $
 * @package users
 * @subpackage modules
 */
if( $gBitSystem->isPackageActive( 'bitforums' ) ) {
	$nvi_info = $gBitSystem->get_news_from_last_visit($user);
	$gBitSmarty->assign('nvi_info', $nvi_info);
}

?>
