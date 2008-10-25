<?php
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
?>
