<?php
declare( strict_types=1 );

require 'tool.inc';
tool::require('func-hexnum');

$w = 0x10;
$h = 0x10;
$map = [];
for ( $y=0; $y < $h; $y++ )
{
	for ( $x=0; $x < $w; $x++ )
	{
		// a = 1-1-
		// 5 = -1-1
		// pattern = xyxy
		//   down -> right -> down -> right
		$id = hexnum::swizzle_xy2id($x, $y, 0xaaaa, 0x5555);
		//printf("%x\n", $id);
		$map[] = $id;
	} // for ( $x=0; $x < $w; $x++ )
} // for ( $y=0; $y < $h; $y++ )

hexnum::swizzle_map($map, $w, $h, true);
