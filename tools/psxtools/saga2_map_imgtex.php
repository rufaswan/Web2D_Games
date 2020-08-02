<?php
/*
 * 00 4 HEAD end
 * 04 4
 * 08 4
 * 0c 4 CHR offset(+4)
 * 10 4 CHR end
 * 1c 4 MAP TEX data(+4)
 * 20 4 MAP TEX end
 * 28 4
 * 2c 4
 * 30 4 MOVE data
 * 34 4 MOVE end
 *
 * MAP TEX data
 * 00 4 [loop] data head start(+4)
 *   [loop]
 *   00 4 [loop] bg start/data head end
 *     [loop]
 *     00 2 img_tex 16*16 offsets (in 16s)
 *     ...
 *   ...
 * 31 1 map width (in tiles)
 *
 * bg data
 * = 00 0e 1c 2a ... 01 0f 1d 2b ... 02 10 1e 2c ...
 *
 * tex in 16*16 tile
 * 00 0e 1c 2a ...
 * 01 0f 1d 2b ...
 * 02 10 1e 2c ...
 * ...
 *
 * ffff = dummy
 * 00   =    c in map%03x.tex (size = 0x100)
 * 01   =  10c in map%03x.tex (size = 0x100)
 * 10   = 100c in map%03x.tex (size = 0x100)
 */
require "common.inc";
$gp_pix  = "";
$gp_clut = array();

function sectmap( &$img, $dir, $id, $meta, $base )
{
	printf("=== sectmap( $dir, $id, %x )\n", $base);
	$map_w = ord( $meta[0x25] ) * 0x10;
	$map_h = ord( $meta[0x27] ) * 0x10;
	echo "map : $map_w x $map_h\n";

	$fn = sprintf("$dir/%04d", $id);

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $map_w;
	$pix['rgba']['h'] = $map_h;
	$pix['rgba']['pix'] = canvpix($map_w,$map_h);
	$pix['bgzero'] = true;

	$pix['src']['w'] = 16;
	$pix['src']['h'] = 16;

	global $gp_pix, $gp_clut;
	$cn = ord( $meta[0x2b] ) & 0xf;
	$pix['src']['pal'] = $gp_clut[$cn];

	$pos = $base + ($id * 4);
	$pos = $base + str2int($img, $pos, 4);
	$map = "";
	for ( $y=0; $y < $map_h; $y += 0x10 )
	{
		for ( $x=0; $x < $map_w; $x += 0x10 )
		{
			$dat = str2int($img, $pos, 2);
				$pos += 2;
			$map .= sprintf("%4x ", $dat);

			if ( $dat == BIT16 )
				continue;

			$pix['src']['pix'] = substr($gp_pix, $dat*0x100, 0x100);
			$pix['dx'] = $x;
			$pix['dy'] = $y;

			copypix($pix);
		} // for ( $x=0; $x < $map_w; $x += 0x10 )

		$map .= "\n";
	} // for ( $y=0; $y < $map_h; $y += 0x10 )

	echo "$map\n";
	savpix($fn, $pix);
	return;
}

function savemap( &$img, $dir )
{
	$off = str2int($img, 0x1c, 4);
	$siz = str2int($img, $off + 4, 4);

	$ed = $off + $siz;
	$st = $off + 0x18;
	$id = 0;
	while ( $st < $ed )
	{
		$meta = substr($img, $st, 0x54);

		sectmap( $img, $dir, $id, $meta, $off + $siz );
		$st += 0x54;
		$id++;
	}
	return;
}

function saga2( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$img = load_file("$pfx.img");
	$tex = load_file("$pfx.tex");
	if ( empty($img) )  return;
	if ( empty($tex) )  return;

	$dir = "{$pfx}_imgtex";

	global $gp_pix, $gp_clut;
	$off = str2int($tex, 4, 4);
	$gp_pix = substr($tex, 12, $off-12);

	$cc = str2int($tex, $off+0, 2);
	$cn = str2int($tex, $off+2, 2);
	$gp_clut = mstrpal555($tex, $off+4, $cc, $cn);

	$tex = "";
	savemap($img, $dir);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	saga2( $argv[$i] );

/*
	/mout/map.out is loaded to 800ac000
	data is loaded to 801a0000
 */
