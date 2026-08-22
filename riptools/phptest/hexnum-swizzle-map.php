<?php
declare( strict_types=1 );

require 'tool.inc';
tool::require('func-hexnum');

$w = 8;
$h = 8;

// morton swizzle = right->down->right->down->...
// drdr drdr
$maskx = 0x555555;
$masky = 0xaaaaaa;

$size = $w * $h;
//////////////////////////////
$map = [];
for ( $y=0; $y < $h; $y++ )
{
	for ( $x=0; $x < $w; $x++ )
	{
		$id = hexnum::swizzle_xy2id($x, $y, $maskx, $masky);
		$map[] = $id;
	} // for ( $x=0; $x < $w; $x++ )
} // for ( $y=0; $y < $h; $y++ )
hexnum::swizzle_map($map, $w, $h, true);
//////////////////////////////
$map = [];
for ( $i=0; $i < $size; $i++ )
{
	list($x,$y) = hexnum::swizzle_id2xy($i, $maskx, $masky);
	$dst = ($y * $w) + $x;
	$map[$dst] = $i;
} // for ( $i=0; $i < $size; $i++ )
hexnum::swizzle_map($map, $w, $h, true);
//////////////////////////////
