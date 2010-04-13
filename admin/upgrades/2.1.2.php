<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_users/admin/upgrades/2.1.2.php,v 1.1 2010/04/13 13:51:05 spiderr Exp $
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
