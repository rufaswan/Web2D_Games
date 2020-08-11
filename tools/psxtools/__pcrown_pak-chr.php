<?php
require "common.inc";
require "common-guest.inc";
require "common-3d.inc";

define("CANV_S", 0x200);
define("SCALE", 1);
//define("DRY_RUN", true);

$gp_pix  = array();
$gp_clut = array();

function vertdata( $x1, $y1, $x2, $y2, $mx, $my, $hf, $vf )
{
	$dx = $x1;
	$dy = $y1;

	if ( $mx )
	{
		$dx = $x2 * -1;
		$hf = !$hf;
	}
	if ( $my )
	{
		$dy = $y2 * -1;
		$vf = !$vf;
	}

	return array($dx,$dy,$hf,$vf);
}

function verttype( &$pix, $dat )
{
	//    -| 12  43  |- 21  34  || 14  41  -- 23  32
	//    -| 43  12  |- 34  21  -- 23  32  || 14  41
	// flip  -   y      x   xy     xr  l      r   xl
	// flag  7    6  5    4   3  2   1  0
	//       45l  -  45r  mx  -  cut -  -
	//       80+20 = mirror-y
	$len = strlen($dat);
	$xy = array();
	for ( $i=0; $i < $len; $i++ )
		$xy[] = ord( $dat[$i] );

	$mx = ( ($xy[11] & 0xa0) == 0xa0 );
	$my = ($xy[11] & 0x10);
	zero_watch("xy10", $dat[10]);
	//flag_warn ("xy11", $xy [11] & 0x4f);

	$dx = 0;
	$dy = 0;
	$vf = false;
	$hf = false;

	// 0 1  2  3   4  5   6  7   8  9   a  b
	// pid  x1 y1  x2 y2  x3 y3  x4 y4  -  flag
	$vert = vertex_type( $xy[2], $xy[3], $xy[4], $xy[5], $xy[6], $xy[7], $xy[8], $xy[9] );
	if ( ! empty($vert) )
	{
		echo "DETECT verttype {$vert[0]}\n";
		switch ( $vert[0] )
		{
			case "1243":
				list($dx,$dy,$hf,$vf) = vertdata( $vert[1], $vert[2], $vert[3], $vert[4], $mx, $my, $hf, $vf );
				break;
			case "4312":
				list($dx,$dy,$hf,$vf) = vertdata( $vert[1], $vert[2], $vert[3], $vert[4], $mx, $my, $hf, $vf );
				$vf = !$vf;
				break;
			case "2134":
				list($dx,$dy,$hf,$vf) = vertdata( $vert[1], $vert[2], $vert[3], $vert[4], $mx, $my, $hf, $vf );
				$hf = !$hf;
				break;
			case "3421":
				list($dx,$dy,$hf,$vf) = vertdata( $vert[1], $vert[2], $vert[3], $vert[4], $mx, $my, $hf, $vf );
				$hf = !$hf;
				$vf = !$vf;
				break;
			default:
				break;
		} // switch ( $vert[0] )
	}

	$pix['dx'] = $dx;
	$pix['dy'] = $dy;
	$pix['vflip'] = $vf;
	$pix['hflip'] = $hf;
	return;
}

function sectpart( &$pak, $dir, $id, $off, $no )
{
	printf("== sectpart( $dir , $id , %x , $no )\n", $off);

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = CANV_S * SCALE;
	$pix['rgba']['h'] = CANV_S * SCALE;
	$pix['rgba']['pix'] = canvpix(CANV_S * SCALE , CANV_S * SCALE);
	$pix['bgzero'] = false;

	global $gp_pix, $gp_clut;
	for ( $i=0; $i < $no; $i++ )
	{
		$p = $off + ($i * 12);
		$dat = substr($pak, $p, 12);
		debug($dat);

		// distort def
		// 0 1  2  3   4  5   6  7   8  9   a  b
		// pid  x1 y1  x2 y2  x3 y3  x4 y4  -  flag
		$b1 = ordint( $dat[1] . $dat[0] );
		$tid = $b1 & 0x0fff;

		verttype($pix, $dat);
		$dx = $pix['dx'];
		$dy = $pix['dy'];
		//$pix['dx'] = ($dx + (CANV_S/2)) * SCALE;
		//$pix['dy'] = ($dy + (CANV_S/2)) * SCALE;
		$pix['dx'] = ((CANV_S/2) - $dx) * SCALE;
		$pix['dy'] = ((CANV_S/2) - $dy) * SCALE;

		$pix['src']['w'] = $gp_pix[$tid][1];
		$pix['src']['h'] = $gp_pix[$tid][2];
		$pix['src']['pix'] = $gp_pix[$tid][0];
		$pix['src']['pal'] = $gp_clut[0];

		$p11 = ord( $dat[11] );

		printf("%4d , %4d , 0 , 0 , %4d , %4d", $dx, $dy, $gp_pix[$tid][1], $gp_pix[$tid][2]);
		printf(" , $tid , %02x\n", $p11);
		copypix($pix);
	}

	$fn = sprintf("$dir/%04d", $id);
	savpix($fn, $pix, true);
	return;
}
//////////////////////////////
function sectanim( &$pak, $off )
{
	// anim def
	// 0 1  2  3  4 5  6  7
	// sid  -  -  ms   -  rep
	$anim = array();
	while (1)
	{
		$bak = $off;
			$off += 8;
		if ( $pak[$bak+3] == BYTE && $pak[$bak+2] == BYTE )
			continue;
		if ( $pak[$bak+7] != ZERO )
			return implode(' , ', $anim);

		$b1 = ordint( $pak[$bak+1] . $pak[$bak+0] );
		$b2 = ordint( $pak[$bak+5] . $pak[$bak+4] );
		$anim[] = sprintf("%d-%d", $b1 & 0x0fff, $b2);
	}
	return implode(' , ', $anim);
}
//////////////////////////////
function load_clut( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	global $gp_clut;
	$gp_clut = array();

	$ed = strlen($file);
	$st = 0;
	while ( $st < $ed )
	{
		$pal = "";
		for ( $i=0; $i < 0x20; $i += 2 )
			$pal .= rgb555( $file[$st+$i+1] . $file[$st+$i+0] );

		$gp_clut[] = $pal;
		$st += 0x20;
	}
	printf("add CLUT @ %d\n", count($gp_clut));
	return;
}

function load_texx( &$pak, $pfx, $off, $no )
{
	$chr = load_file("$pfx.chr");
	if ( empty($chr) )  return;

	global $gp_pix;
	$gp_pix = array();

	global $gp_clut;
	$pos = 0;
	for ( $i=0; $i < $no; $i++ )
	{
		// aligned to 8x8 tile
		while ( ($pos % 0x20) != 0 )
			$pos++;
		$p = $off + ($i * 8);

		// 0  1 2 3  4  5  6  7
		// -  chr    w  h  id
		$id = ordint( $pak[$p+7] . $pak[$p+6] );
		$w = ord( $pak[$p+4] );
		$h = ord( $pak[$p+5] );
		$siz = ($w/2 * $h);
		printf("%4x , %6x , %3d x %3d = %4x\n", $i, $pos, $w, $h, $siz);

		$b1 = substr($chr, $pos, $siz);
		$pix = "";
		for ( $s=0; $s < $siz; $s++ )
		{
			$b2 = ord( $b1[$s] );
			$b3 = ($b2 >> 4) & BIT4;
			$b4 = ($b2 >> 0) & BIT4;
			$pix .= chr($b3) . chr($b4);
		}
		$gp_pix[$i] = array($pix, $w, $h);


		$clut = "CLUT";
		$clut .= chrint(16, 4);
		$clut .= chrint($w, 4);
		$clut .= chrint($h, 4);
		$clut .= $gp_clut[0];
		$clut .= $pix;
		save_file("{$pfx}_tmp/$i.clut", $clut);

		$pos += $siz;
	} // for ( $i=0; $i < $no; $i++ )
	return;
}
//////////////////////////////
function pcrown( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname, '.'));

	$pak = load_file("$pfx.pak");
	if ( empty($pak) )  return;

	if ( substr($pak,0,4) != "unkn" )
		return;

	$dir = "{$pfx}_chrpak";
	load_clut("pcrown.pal");

	str_endian($pak, 0x20, 2); // no1 *  8
	str_endian($pak, 0x22, 2); // no2 * 12
	str_endian($pak, 0x24, 2); // no4 *  8
	str_endian($pak, 0x26, 2); // no5 * 12

	$num1 = str2int($pak, 0x20, 2); // no parts
	$num2 = str2int($pak, 0x22, 2); // no distort set def
	$num3 = str2int($pak, 0x24, 2); // no anim
	$num4 = str2int($pak, 0x26, 2); // no anim set def

	str_endian($pak, 0x08, 4);
	str_endian($pak, 0x0c, 4);
	str_endian($pak, 0x10, 4);
	str_endian($pak, 0x14, 4);
	str_endian($pak, 0x18, 4);
	str_endian($pak, 0x2c, 4);
	str_endian($pak, 0x30, 4);

	$off1 = str2int($pak, 0x08, 3); // parts def * 8
	$off2 = str2int($pak, 0x0c, 3); // distort def * 12
	$off3 = str2int($pak, 0x10, 3); // distort set def
	$off4 = str2int($pak, 0x14, 3); // anim def * 8
	$off5 = str2int($pak, 0x18, 3); // anim set def * 12
	$off6 = str2int($pak, 0x2c, 3);
	$off7 = str2int($pak, 0x30, 3);

	load_texx($pak, $pfx, $off1, $num1);

	for ( $i=0; $i < $num2; $i++ )
	{
		$p = $off3 + ($i * 12);

		// distort set def
		// 0 1 2 3 4 5 6 7  8 9  a b
		// - - - - - - - -  st   no
		$st = ordint( $pak[$p+ 9] . $pak[$p+ 8] );
		$no = ordint( $pak[$p+11] . $pak[$p+10] );

		printf("SPR $i = %x , %x , %x\n", $p, $st, $no);
		sectpart($pak, $dir, $i, $off2 + $st*12, $no);
	}

	$anim = "";
	for ( $i=0; $i < $num4; $i++ )
	{
		$p = $off5 + ($i * 12);

		str_endian($pak, $p, 4);
		$st = str2int($pak, $p, 3);

		printf("ANIM %d = %x , %x\n", $i, $p, $st);
		$anim .= "anim_$i = ";
		$anim .= sectanim($pak, $st);
		$anim .= "\n";
	}
	save_file("$dir/anim.txt", $anim);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	pcrown( $argv[$i] );

/*
grad palette
	1  f8f8f0  7bff
	2  f8e0c0  639f
	3  f0c0a0  531e
	4  d89088  465b
	5  a07880  41f4
	6  804050  2910
	7  a898f0  7a75
	8  281860  3065
	9  503068  34ca
	a  d898c0  627b
	b  9070b8  5dd2
	c  9050a0  5152
	d  684098  4d0d
	f  c8d0f8  7f59
	=> RAM 9ca8e = 0.bin + 98a8e
		size 5000 (640 set of 16 color palettes)

	113/71  215/d7  /  199/c7  183/b7  113/71  145/91  131/83  67/43  1/1
	240/f0  239/ef  96/60

book select
	VORE  item.pak
	VORE  comm.pak
	VORE  arel.pak
	VORE  slct.pak
	VORE  chap.pak
	VORE  obaa.pak
*/
