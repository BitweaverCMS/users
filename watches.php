<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/watches.php,v 1.1.1.1.2.2 2005/07/26 15:50:31 drewslater Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: watches.php,v 1.1.1.1.2.2 2005/07/26 15:50:31 drewslater Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
include_once( '../bit_setup_inc.php' );
$user = $gBitUser->mUserId;
if (!$user) {
	$gBitSmarty->assign('msg', tra("You must log in to use this feature"));
	$gBitSystem->display( 'error.tpl' );
	die;
}

$gBitSystem->verifyFeature( 'feature_user_watches' );

if (isset($_REQUEST['hash'])) {
	
	$gBitUser->remove_user_watch_by_hash($_REQUEST['hash']);
}
if (isset($_REQUEST['watch'])) {
	
	foreach (array_keys($_REQUEST["watch"])as $item) {
		$gBitUser->remove_user_watch_by_hash($item);
	}
}
// Get watch events and put them in watch_events
$events = $gBitUser->get_watches_events();
$gBitSmarty->assign('events', $events);
// if not set event type then all
if (!isset($_REQUEST['event']))
	$_REQUEST['event'] = '';
// get all the information for the event
$watches = $gBitUser->getWatches( $_REQUEST['event'] );
$gBitSmarty->assign('watches', $watches);

$gBitSystem->display( 'bitpackage:users/user_watches.tpl');
?>
