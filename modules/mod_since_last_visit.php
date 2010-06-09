<?php
/**
 * $Header$
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See below for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See http://www.gnu.org/copyleft/lesser.html for details
 *
 * $Id$
 * @package users
 * @subpackage modules
 */
if( $gBitSystem->isPackageActive( 'bitforums' ) ) {
	$nvi_info = $gBitSystem->get_news_from_last_visit($user);
	$gBitSmarty->assign('nvi_info', $nvi_info);
}

?>
