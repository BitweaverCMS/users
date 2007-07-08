<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_users/users_rss.php,v 1.4 2007/07/08 07:56:40 squareing Exp $
 * @package wiki
 * @subpackage functions
 */

/**
 * Initialization
 */
require_once( "../bit_setup_inc.php" );

$gBitSystem->verifyPackage( 'rss' );
$gBitSystem->verifyFeature( 'users_rss' );

require_once( RSS_PKG_PATH."rss_inc.php" );

$rss->title = $gBitSystem->getConfig( 'users_rss_title', $gBitSystem->getConfig( 'site_title' ).' - '.tra( 'Registrations' ) );
$rss->description = $gBitSystem->getConfig( 'users_rss_description', $gBitSystem->getConfig( 'site_title' ).' - '.tra( 'RSS Feed' ) );

// check permission to view users pages
if( !$gBitUser->hasPermission( 'p_users_view_user_list' ) ) {
	require_once( RSS_PKG_PATH."rss_error.php" );
} else {
	// check if we want to use the cache file - users with users_admin permission use a different cache file
	$cacheFile = TEMP_PKG_PATH.RSS_PKG_NAME.'/'.USERS_PKG_NAME.'/'.$cacheFileTail;
	$rss->useCached( $rss_version_name, $cacheFile, $gBitSystem->getConfig( 'rssfeed_cache_time' ));

	$listHash = array(
		'max_records' => $gBitSystem->getConfig( 'users_rss_max_records' ),
		'sort_mode' => 'registration_date_desc',
	);
	$gBitUser->getList( $listHash );
	$feeds = $listHash['data'];

	// set the rss link
	$rss->link = 'http://'.$_SERVER['HTTP_HOST'].USERS_PKG_URL;

	// get all the data ready for the feed creator
	foreach( $feeds as $feed ) {
		$item = new FeedItem();

		$item->title = tra( "New user registration" ).": ".$feed['login'];
		$item->link = BIT_BASE_URI.$gBitUser->getDisplayUrl( $feed['login'] );

		$item->description = '';

		if( !empty( $feed['thumbnail_url'] ) ) {
			$item->description .= '<img alt="user portrait" title="'.$feed['login'].'" src="'.$feed['thumbnail_url'].'" /><br />';
		}
		if( !empty( $feed['real_name'] ) ) {
			$item->description .= tra( "Real Name" ).": ".$feed['real_name'].'<br />';
		}
		$item->description .= tra( "Login" ).': <a href="'.BIT_BASE_URI.$gBitUser->getDisplayUrl( $feed['login'] ).'">'.$feed['login'].'</a><br />';
		if( $gBitUser->hasPermission( 'p_users_admin' ) ) {
			$item->description .= tra( "Email Address" ).': <a href="mailto:'.$feed['email'].'">'.$feed['email'].'</a><br />';
		}
		require_once $gBitSmarty->_get_plugin_filepath( 'modifier', 'bit_short_datetime' );
		$item->description .= tra( "Member Since" ).": ".smarty_modifier_bit_short_datetime( $feed['registration_date'] ).'<br />';

		$item->date = ( int )$feed['registration_date'];
		$item->source = 'http://'.BIT_BASE_URI;
		$item->author = $_SERVER['HTTP_HOST'];

		$item->descriptionTruncSize = $gBitSystem->getConfig( 'rssfeed_truncate', 5000 );
		$item->descriptionHtmlSyndicated = FALSE;

		// pass the item on to the rss feed creator
		$rss->addItem( $item );
	}

	// finally we are ready to serve the data
	echo $rss->saveFeed( $rss_version_name, $cacheFile );
}
?>
