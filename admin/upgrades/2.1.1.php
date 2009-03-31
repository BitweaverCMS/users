<?php
/**
 * @version $Header: /cvsroot/bitweaver/_bit_users/admin/upgrades/2.1.1.php,v 1.3 2009/03/31 16:05:43 dansut Exp $
 */
global $gBitInstaller;

$infoHash = array(
	'package'      => USERS_PKG_NAME,
	'version'      => str_replace( '.php', '', basename( __FILE__ )),
	'description'  => "Minor fix to ip columns to support IPv6",
	'post_upgrade' => NULL,
);

// Increase the size of the IP column to cope with IPv6
$gBitInstaller->registerPackageUpgrade( $infoHash, array(

array( 'QUERY' =>
	array(
		'PGSQL' => array( "ALTER TABLE `".BIT_DB_PREFIX."users_cnxn` ALTER `ip` TYPE VARCHAR(39)" ,),
		'OCI'   => array( "ALTER TABLE `".BIT_DB_PREFIX."users_cnxn` MODIFY (`ip` TYPE VARCHAR2(39))" ,),
		'MYSQL' => array( "ALTER TABLE `".BIT_DB_PREFIX."users_cnxn` MODIFY `ip` VARCHAR(39)" ,),
	),
),

));
?>
