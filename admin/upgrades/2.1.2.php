<?php
/**
 * @version $Header$
 */
global $gBitInstaller;

$infoHash = array(
	'package'      => USERS_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "Clean expiring password values since feature was previously non-functional.",
	'post_upgrade' => NULL,
);

$gBitInstaller->registerPackageUpgrade( $infoHash, array(

array( 'PHP' => '
	// make sure plugins are up to date.
	global $gBitDb;
	$gBitDb->query( "UPDATE `".BIT_DB_PREFIX."users_users` SET `pass_due`=NULL" );
'
)

));
?>
