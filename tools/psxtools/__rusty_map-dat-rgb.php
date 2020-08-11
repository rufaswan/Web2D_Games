<?php
require "common.inc";

$gp_clut = "";

function loadsrc( &$dat, $pos )
{
	//printf("== secttile( %x )\n", $pos);
	$rip = "";
	for ( $i=0; $i < 0x40; $i++ )
	{
		$b = ord( $dat[$pos+$i] );
		$b1 = ($b >> 6) & 0x03;
		$b2 = ($b >> 4) & 0x03;
		$b3 = ($b >> 2) & 0x03;
		$b4 = ($b >> 0) & 0x03;
		$rip .= chr($b1) . chr($b2) . chr($b3) . chr($b4);
	}
	return $rip;
}
//////////////////////////////
function sectmap( &$map, &$dat, $pfx, $id, $map_w, $map_h )
{
	printf("== sectmap( $id , $map_w , $map_h )\n");

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $map_w;
	$pix['rgba']['h'] = $map_h;
	$pix['rgba']['pix'] = canvpix($map_w,$map_h);
	$pix['bgzero'] = true;

	$pix['src']['w'] = 16;
	$pix['src']['h'] = 16;

	global $gp_clut;
	$pos = 4;
	$mdt = "";
	for ( $y=0; $y < $map_h; $y += 0x10 )
	{
		for ( $x=0; $x < $map_w; $x += 0x10 )
		{
			$b1 = ord( $map[$pos] );
				$pos++;
			$mdt .= sprintf("%2x ", $b1);

			$p = ($id * 0x4000) + ($b1 * 0x40);
			$pix['src']['pix'] = loadsrc($dat, $p);
			$pix['src']['pal'] = $gp_clut;
			$pix['dx'] = $x;
			$pix['dy'] = $y;

			copypix($pix);
		} // for ( $x=0; $x < $map_w; $x += 0x10 )
		$mdt .= "\n";

	} // for ( $y=0; $y < $map_h; $y += 0x10 )
	echo "$mdt\n";

	savpix("$pfx/$id", $pix);
	return;
}

function rusty( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$map = load_file("$pfx.map");
	$dat = load_file("$pfx.dat");
	$rgb = load_file("$pfx.rgb");
	if ( empty($map) || empty($dat) || empty($rgb) )
		return;

	global $gp_clut;
	$gp_clut = "";
	for ( $i=0; $i < 0x30; $i += 3 )
	{
		$r = ord( $rgb[$i+2] );
		$g = ord( $rgb[$i+1] );
		$b = ord( $rgb[$i+0] );
		$r = int_clamp($r * 0x11, 0, BIT8);
		$g = int_clamp($g * 0x11, 0, BIT8);
		$b = int_clamp($b * 0x11, 0, BIT8);
		$gp_clut .=  chr($r) . chr($g) . chr($b) . BYTE;
	}

	$map_w = str2int($map, 0, 2) * 0x10;
	$map_h = str2int($map, 2, 2) * 0x10;
	echo "map : $map_w x $map_h\n";
	$s = $map_w * $map_h;

	for ( $i=0; $i < 4; $i++ )
		sectmap($map, $dat, $pfx, $i, $map_w, $map_h);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	rusty( $argv[$i] );
