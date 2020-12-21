<?php
/*
[license]
[/license]
 */
/*
 * Special Thanks to:
 *   Zone File Technical Documentation by Nyxojaele (Dec 26, 2010)
 *   romhacking.net/documents/528/
 */
require "common.inc";

$gp_clut = array();
//define("DRY_RUN", true);

// map files are loaded to RAM 80180000
// offsets here are RAM pointers
function ramint( &$file, $pos )
{
	$int = str2int($file, $pos, 3);
	if ( $int )
		$int -= 0x180000;
	else
		printf("ERROR ramint zero @ %x\n", $pos);
	return $int;
}

function loadclut( &$file )
{
	global $gp_clut;
	$gp_clut = array();

	// f_xxx.bin is sets of 4-bit 128x128 pix data
	// arranged like this
	//  0  1   4  5   8  9  12 13  16 17  20 21  24 25  28 29
	//  2  3   6  7  10 11  14 15  18 19  22 23  26 27  30 31
	//
	// clut is on 2 3 6 7 10 11 14 15
	// and in left-to-right , then top-to-bottom order
	// 2,0  2,1  3,0  3,1  6,0  6,1  7,0  7,1 ... 14,14  14,15  15,14  15,15
	for ( $c=0; $c < 16; $c++ )
	{
		for ( $s=0; $s < 4; $s++ )
		{
			$pos = ($s * 0x8000) + ($c * 0x40);
			$p1 = $pos + 0x5c00;
			$p2 = $pos + 0x7c00;
			printf("add CLUT @ %x , %x\n", $p1, $p2);

			$gp_clut[] = strpal555($file, $p1+ 0, 16);
			$gp_clut[] = strpal555($file, $p1+32, 16);
			$gp_clut[] = strpal555($file, $p2+ 0, 16);
			$gp_clut[] = strpal555($file, $p2+32, 16);
		}
	}
	var_dump( count($gp_clut) );
	return;
}
//////////////////////////////
function sectmap( &$meta, &$file, &$done, $off, $dir )
{
	printf("=== sectmap( %x , $dir )\n", $off);
	if ( isset( $done[$off] ) )
		return;

	$b1 = ramint($meta, $off+0); // tile layout
	$b2 = ramint($meta, $off+4); // tile def
	if ( $b1 == 0 || $b2 == 0 )
		return;

	$b3 = str2int($meta, $off+8, 3); // map corners
		$x1 = (($b3 >>  0) & 0x3f) * 0x100;
		$y1 = (($b3 >>  6) & 0x3f) * 0x100;
		$x2 = (($b3 >> 12) & 0x3f) * 0x100;
		$y2 = (($b3 >> 18) & 0x3f) * 0x100;
		$map_w = $x2 + 0x100 - $x1;
		$map_h = $y2 + 0x100 - $y1;

	$b4 = str2int($meta, $off+11, 1); // room flag
	$b5 = str2int($meta, $off+12, 1);
	$b6 = str2int($meta, $off+13, 1);
	$b7 = str2int($meta, $off+14, 2); // draw flag
	printf("map : %d x %d ( %d , %d ) = %x\n", $map_w, $map_h, $x1, $y1, $b1);

	$d1 = ramint($meta, $b2+ 0); // tile set
	$d2 = ramint($meta, $b2+ 4); // tile pos
	$d3 = ramint($meta, $b2+ 8); // clut
	$d4 = ramint($meta, $b2+12); // collusion
	printf("tiledef : %x , %x , %x , %x\n", $d1, $d2, $d3, $d4);

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $map_w;
	$pix['rgba']['h'] = $map_h;
	$pix['rgba']['pix'] = canvpix($map_w,$map_h);

	$pix['src']['w'] = 16;
	$pix['src']['h'] = 16;

	global $gp_clut;
	$pos = $b1;
	$map = "";
	for ( $y=0; $y < $map_h; $y += 0x10 )
	{
		for ( $x=0; $x < $map_w; $x += 0x10 )
		{
			$id = str2int($meta, $pos, 2);
				$pos += 2;

			$c1 = ord( $meta[$d1+$id] );
			$c2 = ord( $meta[$d2+$id] );
			$c3 = ord( $meta[$d3+$id] );
			$c4 = ord( $meta[$d4+$id] );

			$c = ($c1 << 24) | ($c2 << 16) | ($c3 << 8) | $c4;
			$map .= sprintf("%8x ", $c);

			$pix['dx'] = $x;
			$pix['dy'] = $y;

			if ( $c1 >> 4 )
				continue;
			$tp = ($c1 & 15) * 0x8000;
			if ( $c2 & 0x80 )  $tp += 0x4000;
			if ( $c2 & 0x08 )  $tp += 0x2000;

			$tx =  ($c2 &  7) * 16;
			$ty = (($c2 >> 4) & 7) * 16;

			$bin = substr($file, $tp, 0x2000);
			$pix['src']['pix'] = rippix4($bin, $tx, $ty, 16, 16, 0x80, 0x80);
			$pix['src']['pal'] = $gp_clut[$c3];
			$pix['bgzero'] = 0;

			copypix($pix);
		} // for ( $x=0; $x < $map_w; $x++ )

		$map .= "\n";
	} // for ( $y=0; $y < $map_h; $y++ )

	echo "$map\n";
	savepix("$dir/map_$off", $pix);
	$done[$off] = 'x';
	return;
}

function map_sel( &$meta, &$file, $dir )
{
	if ( strlen($file) != 0x30000 )
		return;
	echo "=== map_sel( $dir )\n";

	return;
}
//////////////////////////////
function sotn( $dir )
{
	if ( ! is_dir($dir) )
		return;
	if ( ! file_exists("$dir/setup.txt") )
		return;

	$meta = file_get_contents("$dir/st.2");
	$file = file_get_contents("$dir/st.1");

	if ( strlen($file) != 0x40000 )
		return map_sel($meta, $file, $dir);

	//$off1  = ramint($meta, 0x00); // func() entity attack?
	//$off2  = ramint($meta, 0x04); // func() respawn entity
	//$off3  = ramint($meta, 0x08); // func() respawn screen check by frames
	//$off4  = ramint($meta, 0x0c); // func() respawn room check
	$off5  = ramint($meta, 0x10); // zone layout
	$off6  = ramint($meta, 0x14); // entity sprite
	//$off7  = ramint($meta, 0x18);
	//$off8  = ramint($meta, 0x1c);
	$off9  = ramint($meta, 0x20); // zone layout def
	$off10 = ramint($meta, 0x24); // entity sprite def
	//$off11 = ramint($meta, 0x28); // func() entity AI?
	//$off12 = ramint($meta, 0x2c); // <- $off6 can refer here = are/cat/dai.bin ...

	loadclut( $file );
	$st = $off9;
	$done = array();
	while (1)
	{
		$fgoff = ramint($meta, $st+0);
		$bgoff = ramint($meta, $st+4);
		if ( $fgoff == 0 || $bgoff == 0 )
			break;
		sectmap($meta, $file, $done, $fgoff, $dir);
		sectmap($meta, $file, $done, $bgoff, $dir);
		$st += 8;
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	sotn( $argv[$i] );

/*
 * dra.bin       is loaded @ 800a0000
 * f_map.bin     is loaded @ 801e0000
 * st/xxx.bin    is loaded @ 80180000 -> 8003c77c
 * bin/arc_f.bin is loaded @ 8013c000
 * bin/ric.bin   is loaded @
 *
 * lbaini.php -lba  dra.bin 3cac-4a6c cb3c-cd1c  st/sel/sel.bin b0dc-b0fc
 *
 */
