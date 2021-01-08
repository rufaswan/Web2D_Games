<?php
/*
[license]
[/license]
 */
require "common.inc";

$clut = array(
	'cc' => 0x10,
	'w' => 0,
	'h' => 0,
	'pal' => grayclut(0x10),
	'pix' => str_repeat(ZERO, 0x100*0x100),
);
for ( $i=0; $i < 0x10; $i++ )
	$clut['pix'][$i] = chr($i*0x11);
save_clutfile("gray-16.clut", $clut);

$clut = array(
	'cc' => 0x100,
	'w' => 0,
	'h' => 0,
	'pal' => grayclut(0x100),
	'pix' => str_repeat(ZERO, 0x100*0x100*4),
);
for ( $i=0; $i < 0x100; $i++ )
	$clut['pix'][$i] = chr($i);
save_clutfile("gray-256.clut", $clut);
