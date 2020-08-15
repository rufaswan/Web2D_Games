<?php
require "common.inc";

$gp_pix  = "";
$gp_clut = "";

function loadclut( $pfx )
{
	$rgb = load_file("$pfx.rgb");
	if ( empty($rgb) )  return;
	if ( strlen($rgb) != 0x30 )
		return;
	printf("== loadclut( $pfx ) = 16\n");

	global $gp_clut;
	$gp_clut = "";
	for ( $i=0; $i < 0x30; $i += 3 )
	{
		$r = ord( $rgb[$i+0] );
		$g = ord( $rgb[$i+1] );
		$b = ord( $rgb[$i+2] );
		$r = int_clamp($r * 0x11, 0, BIT8);
		$g = int_clamp($g * 0x11, 0, BIT8);
		$b = int_clamp($b * 0x11, 0, BIT8);
		$gp_clut .=  chr($r) . chr($g) . chr($b) . BYTE;
	}
	return;
}

function loadtexx( $pfx )
{
	$dat = load_file("$pfx.dat");
	if ( empty($dat) )  return;
	if ( strlen($dat) != 0x10000 )
		return;
	printf("== loadtexx( $pfx ) = 256\n");

	global $gp_pix;
	$gp_pix = "";
	for ( $i=0; $i < 0x4000; $i++ )
	{
		$b1 = ord( $dat[$i+0     ] );
		$b2 = ord( $dat[$i+0x4000] );
		$b3 = ord( $dat[$i+0x8000] );
		$b4 = ord( $dat[$i+0xc000] );

		$j = 8;
		while ( $j > 0 )
		{
			$j--;
			$b11 = ($b1 >> $j) & 1;
			$b21 = ($b2 >> $j) & 1;
			$b31 = ($b3 >> $j) & 1;
			$b41 = ($b4 >> $j) & 1;
			$bj = ($b41 << 3) | ($b31 << 2) | ($b21 << 1) | ($b11 << 0);
			$gp_pix .= chr($bj);
		}
	} // for ( $i=0; $i < 0x4000; $i++ )
	return;
}
//////////////////////////////
function sectmap( &$map, $pfx, $map_w, $map_h )
{
	printf("== sectmap( $map_w , $map_h )\n");

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $map_w;
	$pix['rgba']['h'] = $map_h;
	$pix['rgba']['pix'] = canvpix($map_w,$map_h);
	$pix['bgzero'] = true;

	$pix['src']['w'] = 0x20;
	$pix['src']['h'] = 0x10;

	global $gp_clut, $gp_pix;
	$pos = 4;
	$mdt = "";
	for ( $y=0; $y < $map_h; $y += 0x10 )
	{
		for ( $x=0; $x < $map_w; $x += 0x20 )
		{
			$b1 = ord( $map[$pos] );
				$pos++;
			$mdt .= sprintf("%2x ", $b1);

			$pix['src']['pix'] = substr($gp_pix, $b1*0x200, 0x200);
			$pix['src']['pal'] = $gp_clut;
			$pix['dx'] = $x;
			$pix['dy'] = $y;

			copypix($pix);
		} // for ( $x=0; $x < $map_w; $x += 0x20 )
		$mdt .= "\n";

	} // for ( $y=0; $y < $map_h; $y += 0x10 )
	echo "$mdt\n";

	savpix($pfx, $pix);
	return;
}

function rusty( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$map = load_file("$pfx.map");
	if ( empty($map) )  return;

	loadtexx($pfx);
	loadclut($pfx);

	$map_w = str2int($map, 0, 2) * 0x20;
	$map_h = str2int($map, 2, 2) * 0x10;
	echo "map : $map_w x $map_h\n";

	sectmap($map, $pfx, $map_w, $map_h);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	rusty( $argv[$i] );

/*
bg1 be(+2f80)
	0/2          1            3
	ff ff ff fa  -- -- -- --  ff ff ff fa
	ff ff ff f7  -- -- -- 02  ff ff ff f5
	ff ff ff ea  -- -- -- --  ff ff ff ea
	ff ff ff f5  -- -- -- --  ff ff ff f5
	ff ff fa aa  -- -- -- --  ff ff fa aa
	ff ff f7 ff  -- -- 02 aa  ff ff f5 55
	ff ff ea aa  -- -- -- --  ff ff ea aa
	ff ff d5 55  -- -- -- --  ff ff d5 55

	1011 = d  rgb
	1110 = 7
*/
