<?php
/**
 * $Header: /cvsroot/bitweaver/_bit_users/captcha_image.php,v 1.3 2006/09/15 21:16:56 spiderr Exp $
 *
 * Copyright (c) 2004 bitweaver.org
 * Copyright (c) 2003 tikwiki.org
 * Copyright (c) 2002-2003, Luis Argerich, Garland Foster, Eduardo Polidor, et. al.
 * All Rights Reserved. See copyright.txt for details and a complete list of authors.
 * Licensed under the GNU LESSER GENERAL PUBLIC LICENSE. See license.txt for details
 *
 * $Id: captcha_image.php,v 1.3 2006/09/15 21:16:56 spiderr Exp $
 * @package users
 * @subpackage functions
 */

/**
 * required setup
 */
// hmm. too many session tweaks in setup_smarty ... we need to call this
require_once( '../bit_setup_inc.php' );

// dimensions
$width  = @BitBase::verifyId( $_REQUEST['width'] ) ? $_REQUEST['width'] : 140;
$height = @BitBase::verifyId( $_REQUEST['height'] ) ? $_REQUEST['height'] : 35;

$img = @imagecreate( $width, $height ) or die( "The GD image library doesn't seem to be available or doesn't have JPG support." );

// colours
$text[] = imagecolorallocate( $img, 0  , 0  , 128 );
$text[] = imagecolorallocate( $img, 0  , 128, 0   );
$text[] = imagecolorallocate( $img, 128, 0  , 0   );
$text[] = imagecolorallocate( $img, 0  , 128, 128 );
$text[] = imagecolorallocate( $img, 128, 128, 0   );
$text[] = imagecolorallocate( $img, 128, 0  , 128 );
$border = imagecolorallocate( $img, 0  , 0  , 0   );
$bg     = imagecolorallocate( $img, 230, 240, 250 );

imagefill( $img, 0, 0, $bg );
imagerectangle( $img, 1, 1, $width - 1, $height - 1, $border );
srand( time() );
$number = rand( 10000, 99999 );
$_SESSION['captcha'] = $number;
for( $i = 0; $i < 5; $i++ ) {
	imagestring( $img, 10, ( $width / 6 ) - 5 + ( $width / 6 ) * $i + rand( 0, 2 ), 2 + rand( 0, $height - 22 ), substr( $number, $i, 1 ), $text[rand( 0, count( $text ) - 1 )] );
}

$gd = gd_info();
if( $gd["PNG Support"] ) {
	header( "Content-type: image/png" );
	imagepng( $img );
} elseif( $gd["GIF Create Support"] ) {
	header( "Content-type: image/gif" );
	imagegif( $img );
} else {
	header( "Content-type: image/jpeg" );
	imagejpeg( $img );
}
?>
