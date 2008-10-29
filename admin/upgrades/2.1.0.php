<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_users/admin/upgrades/2.1.0.php,v 1.2 2008/10/29 22:05:19 squareing Exp $
 */
global $gBitInstaller;

$infoHash = array(
	'package'      => USERS_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "Remove the unused <em>users_semaphores</em> table from your database. If you need a semaphores feature, there is a <a class='external' href='http://www.bitweaver.org/wiki/SemaphorePackage'>SemaphorePackage</a> now.",
	'post_upgrade' => NULL,
);

$gBitInstaller->registerPackageUpgrade( $infoHash, array(

array( 'DATADICT' => array(
	array( 'DROPTABLE' => array(
		'users_semaphores',
	)),
)),

));

$gBitInstaller->registerPackageDependencies( $infoHash, array(
	'liberty'   => array( 'min' => '2.1.0' ),
	'kernel'    => array( 'min' => '2.0.0' ),
	'themes'    => array( 'min' => '2.0.0' ),
	'languages' => array( 'min' => '2.0.0' ),
));
?>
