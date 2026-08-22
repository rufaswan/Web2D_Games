<?php
define('ZERO2', "\x00\x00");
define('ZERO3', "\x00\x00\x00");

//////////////////////////////
// 4-bpp / 16 colors
$img = 'CLUT';
$img .= "\x10" . ZERO3; // cc
$img .= "\x10" . ZERO3; // w
$img .= "\x01" . ZERO3; // h

// pal
for ( $i=0; $i < 0x100; $i += 0x11 )
{
	$c = chr($i);
	$img .= $c . $c . $c . "\xff";
}

// pix
for ( $i=0; $i < 0x10; $i++ )
	$img .= chr($i);

file_put_contents('gray-16.clut', $img);

//////////////////////////////
// 8-bpp / 256 colors
$img = 'CLUT';
$img .= "\x00\x01" . ZERO2; // cc
$img .= "\x00\x01" . ZERO2; // w
$img .= "\x01" . ZERO3; // h

// pal
for ( $i=0; $i < 0x100; $i++ )
{
	$c = chr($i);
	$img .= $c . $c . $c . "\xff";
}

// pix
for ( $i=0; $i < 0x100; $i++ )
	$img .= chr($i);

file_put_contents('gray-256.clut', $img);

//////////////////////////////
/*
 cc = color color
  w = image width
  h = image height
pal = palette data
pix = pixel data
 */
