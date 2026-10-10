<?php
/**
 * PSR-4 autoload for the vendored Hybridauth 3 tree (Hybridauth\ → src/).
 */
spl_autoload_register( function( $class ) {
	$prefix = 'Hybridauth\\';
	$len = strlen( $prefix );
	if( strncmp( $class, $prefix, $len ) !== 0 ) {
		return;
	}
	$relative = str_replace( '\\', '/', substr( $class, $len ) );
	$file = __DIR__.'/src/'.$relative.'.php';
	if( is_file( $file ) ) {
		require $file;
	}
} );
