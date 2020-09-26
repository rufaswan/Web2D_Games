<?php
require "common.inc";

define("VRAM_W", 0x400);
define("VRAM_H", 0x200);

$gp_clut = array();

function overrect( &$rect, $dx, $dy, $w, $h )
{
	$x1 = $dx;
	$y1 = $dy;
	$x2 = $dx + $w;
	$y2 = $dy + $h;
	$id = count($rect);
	foreach ( $rect as $k => $v )
	{
		if ( $x1 >= $v[2] )
			continue;
		if ( $y1 >= $v[3] )
			continue;
		if ( $x2 <= $v[0] )
			continue;
		if ( $y2 <= $v[1] )
			continue;
		echo "  OVER $id x $k\n";
	}
	$rect[] = array($x1,$y1,$x2,$y2);
	return;
}

function vramcopy( &$vram, &$part, $dx, $dy, $w, $h )
{
	for ( $y=0; $y < $h; $y++ )
	{
		$dyy = ($dy+$y) * VRAM_W * 2;
		$syy =      $y  * $w       * 2;
		$dxx = $dyy + ($dx * 2);

		$b1 = substr($part, $syy, $w*2);
		strupd($vram, $dxx, $b1);
	}
	return;
}
//////////////////////////////
function tex2vram( &$file )
{
	global $gp_clut;
	$vram = str_repeat(ZERO, VRAM_W*2*VRAM_H);
	$len = strlen($file);
	$pos = 0;
	$rect = array();
	$gp_clut = array();
	while ( $pos < $len )
	{
		$b1 = str2int($file, $pos, 4);
		$bak = $pos;
		switch ( $b1 )
		{
			case 0x1200:
			case 0x1201:
				$bx = str2int($file, $pos+ 4, 2);
				$by = str2int($file, $pos+ 6, 2);
				$dx = str2int($file, $pos+ 8, 2);
				$dy = str2int($file, $pos+10, 2);
				$w  = str2int($file, $pos+0x0c, 2);
				$no = str2int($file, $pos+0x18, 2);
					$pos += (0x800 + $no * 0x800);

				$data = '';
				$h = 0;
				for ( $i=0; $i < $no; $i++ )
				{
					$p1 = $bak + 0x1c + ($i * 2);
					$p1 = str2int($file, $p1, 2);

					$p2 = $bak + 0x800 + ($i * 0x800);
					$data .= substr($file, $p2, $p1*$w*2);
					$h += $p1;
				}

				printf("%6x  %4x  %4x,%4x  %4x,%4x\n", $bak, $b1, $bx+$dx, $by+$dy, $w, $h);
				vramcopy( $vram, $data, $bx+$dx, $by+$dy, $w, $h );
				overrect( $rect, $bx+$dx, $by+$dy, $w, $h );

				//if ( $b1 == 0x1201 ) // palettes
					//$gp_clut = mstrpal555($data, 0, $w, $h);
				break;
		} // switch ( $b1 )
	} // while ( $pos < $len )

	if ( empty($gp_clut) )
		$gp_clut[] = grayclut(0x100);
	return $vram;
}
//////////////////////////////
function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$b1 = str2int($file, 0, 4);
	if ( $b1 != 0x1201 && $b1 != 0x1200 )
		return;

	echo "=== $fname ===\n";
	$dir  = str_replace('.', '_', $fname);
	$file = tex2vram($file);

	global $gp_clut;
	foreach ( $gp_clut as $k => $v )
	{
		$clut = "CLUT";
		$clut .= chrint(0x100  , 4);
		$clut .= chrint(0x400*2, 4);
		$clut .= chrint(0x200  , 4);
		$clut .= $v;
		$clut .= $file;
		save_file("$dir/$k.clut", $clut);
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
