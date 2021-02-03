<?php
/**
 * user watches
 *
 * @copyright (c) 2004-15 bitweaver.org
 *
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
include_once( '../kernel/includes/setup_inc.php' );
$user = $gBitUser->mUserId;
if (!$user) {
	$gBitSmarty->assign('msg', tra("You must log in to use this feature"));
	$gBitSystem->display( 'error.tpl' , NULL, array( 'display_mode' => 'display' ));
	die;
}

$gBitSystem->verifyFeature( 'users_watches' );

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

$gBitSystem->display( 'bitpackage:users/user_watches.tpl', NULL, array( 'display_mode' => 'display' ));
?>
