<?php
define('ZERO3', "\x00\x00\x00");

//////////////////////////////
$img = 'CLUT';
$img .= "\xd8" . ZERO3; // cc
$img .= "\xd8" . ZERO3; // w
$img .= "\x01" . ZERO3; // h

// pal
$val = array("\x00" , "\x33" , "\x66" , "\x99" , "\xcc" , "\xff");
foreach ( $val as $r )
{
	foreach ( $val as $g )
	{
		foreach ( $val as $b )
			$img .= $r . $g . $b . "\xff";
	} // foreach ( $val as $g )
} // foreach ( $val as $r )

// pix
for ( $i=0; $i < 0xd8; $i++ )
	$img .= chr($i);

file_put_contents('websave.clut', $img);

//////////////////////////////
/*
cc
	2*2*2 =   8  08
	3*3*3 =  27  1b
	4*4*4 =  64  40
	5*5*5 = 125  7d
	6*6*6 = 216  d8
	7*7*7 = 343 157
*/
