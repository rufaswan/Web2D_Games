<?php
/*
[license]
[/license]
 */
require "common.inc";

//define("DRY_RUN", true);

$gp_clut = "";

function loadtexx( &$texx, $pfx, $id, $sx, $sy, $w, $h )
{
	if ( ! isset( $texx[$id] ) )
	{
		$scfn = "$pfx.$id.sc";
		$file = load_file($scfn);
		if ( empty($file) )
			return php_error("NOT FOUND %s", $scfn);

		$len = strlen($file);
		switch ( $len )
		{
			case 0x2000: // 128x128 , 4-bit
			case 0x8000: // 256x256 , 4-bit
				$texx[$id] = "";
				for ( $i=0; $i < $len; $i++ )
				{
					$b = ord($file[$i]);
					$b1 = ($b >> 0) & BIT4;
					$b2 = ($b >> 4) & BIT4;
					$texx[$id] .= chr($b1) . chr($b2);
				}
				break;
			case 0x4000: // 128x128 , 8-bit
				$texx[$id] = $file;
				break;
			default:
				return php_error("UNKNOWN LEN %s = %x", $scfn, $len);
		} // switch ( $len )
		echo "add TEXX $id\n";
	}

	$len = strlen( $texx[$id] );
	if ( $len == 0x4000 ) // 128x128
		return rippix8($texx[$id], $sx, $sy, $w, $h, 0x80, 0x80);
	else
	if ( $len == 0x10000 ) // 256x256
		return rippix8($texx[$id], $sx, $sy, $w, $h, 0x100, 0x100);
	return "";
}
//////////////////////////////
function abs_canv( &$CANV_S, $int )
{
	$int = abs($int);
	if ( $int > $CANV_S )
		$CANV_S = $int;
	return;
}

function sectpart( &$meta, &$src, $pfx, $id, $num, $off )
{
	printf("=== sectpart( $pfx , $id , $num , %x )\n", $off);

	$data = array();
	$CANV_S = 0;
	$is_mid = false;
	while ( $num > 0 )
	{
		// 0 1  2 3  4 5  6 7  8 9  10 11  12 13 14 15
		// dx-  dy-  sx-  sy-  w--  h----  t  f  c  -
		$num--;
		$p = $off + ($num * 0x10);

		zero_watch("v15", $meta[$p+15]);

		$dx = sint16( $meta[$p+0] . $meta[$p+1] );
		$dy = sint16( $meta[$p+2] . $meta[$p+3] );
		$sx = str2int($meta, $p+ 4, 2);
		$sy = str2int($meta, $p+ 6, 2);
		$w  = str2int($meta, $p+ 8, 2);
		$h  = str2int($meta, $p+10, 2);
		$p12 = ord( $meta[$p+12] );
		$p13 = ord( $meta[$p+13] );
		$p14 = ord( $meta[$p+14] );

		$v = array($dx,$dy,$sx,$sy,$w,$h,$p12,$p13,$p14);
		$data[] = $v;

		// detect origin and canvas size
		abs_canv($CANV_S, $dx);
		abs_canv($CANV_S, $dy);
		abs_canv($CANV_S, $dx + $w);
		abs_canv($CANV_S, $dy + $h);
		if ( ! $is_mid )
		{
			if ( $dx < 0 || $dy < 0 )
				$is_mid = true;
		}

		printf("%4d , %4d , %4d , %4d , %4d , %4d", $dx, $dy, $sx, $sy, $w, $h);
		printf(" , %2x , %2x , %2x\n", $p12, $p13, $p14);
	} // for ( $i=0; $i < $num; $i++ )
	if ( empty($data) )
		return;

	$ceil = ( $is_mid ) ? int_ceil($CANV_S*2, 16) : int_ceil($CANV_S, 16);
	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $ceil;
	$pix['rgba']['h'] = $ceil;
	$pix['rgba']['pix'] = canvpix($ceil,$ceil);

	$origin = ( $is_mid ) ? $ceil / 2 : 0;
	printf("CANV_S  %d\n", $CANV_S);
	printf("ORIGIN  %d\n", $origin);

	global $gp_clut;
	$texx = array();
	foreach ( $data as $v )
	{
		list($dx,$dy,$sx,$sy,$w,$h,$p12,$p13,$p14) = $v;

		$pix['dx'] = $dx + $origin;
		$pix['dy'] = $dy + $origin;

		$tid = $p12;
		$cid = $p14;
		$loadtexx = loadtexx($texx, $pfx, $tid, $sx, $sy, $w, $h);

		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = $loadtexx;
		$pix['src']['pal'] = substr($gp_clut, $cid*0x40, 0x40);
		$pix['bgzero'] = 0;

		// 7654 3210
		// ---- --hv
		$pix['vflip'] = $p13 & 1;
		$pix['hflip'] = $p13 & 2;
		flag_watch("p13", $p13 & 0xfc);

		/////////////////////////////////
		//// original sheet in parts ////
			while ( ($tid+1)*0x100 > $src['rgba']['h'] )
			{
				$src['rgba']['pix'] .= canvpix(0x100,0x100);
				$src['rgba']['h'] += 0x100;
			}
			$src['dx'] = $sx;
			$src['dy'] = $sy + ($tid * 0x100);
			$src['src']['w'] = $w;
			$src['src']['h'] = $h;
			$src['src']['pix'] = $loadtexx;
			$src['src']['pal'] = substr($gp_clut, $cid*0x40, 0x40);
			copypix($src);
		//// original sheet in parts ////
		/////////////////////////////////

		copypix($pix);
	} // for ( $i=0; $i < $num; $i++ )

	$fn = sprintf("$pfx/%04d", $id);
	savepix($fn, $pix, false);
	return;
}

function sectanim( &$grps, &$meta, $pfx )
{
	$len = strlen($grps);
	$buf = "";
	for ( $i=0; $i < $len; $i++ )
	{
		$num = str2int($grps, $i+0, 1);
		$off = str2int($grps, $i+4, 2);
		if ( $num == 0 )
			continue;

		$ret = array();
		for ( $j=0; $j < $num; $j++ )
		{
			$p = $off + ($j*8);
			$b1 = str2int($meta, $p+0, 2);
			$b2 = str2int($meta, $p+2, 2);
			$ret[] = "$b1-$b2";
		}

		$buf .= sprintf("anim_%d = %s\n", $i/8, implode(' , ', $ret) );
	} // for ( $i=0; $i < $len; $i++ )

	save_file("$pfx/anim.txt", $buf);
}
//////////////////////////////
function cvnds( $fname )
{
	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$so  = load_file("$pfx.so");
	$pal = load_file("$pfx.pal");
	if ( empty($so) || empty($pal) )
		return;

	global $gp_clut;
	$gp_clut = $pal;

	$o1 = str2int($so, 0x04, 4);
	$o2 = str2int($so, 0x08, 4);
	$o3 = str2int($so, 0x0c, 4);
	$o4 = str2int($so, 0x10, 4);
	$o5 = str2int($so, 0x14, 4);
	$o6 = str2int($so, 0x20, 4);
	// 0x24 = total sprite
	// 0x28 = total animation
	if ( $o5 == 0 )  $o5 = $o6;
	if ( $o4 == 0 )  $o4 = $o5;
	if ( $o3 == 0 )  $o3 = $o4;
	if ( $o2 == 0 )  $o2 = $o3;
	if ( $o1 == 0 )  $o1 = $o2;

	// sprite parts data
	$meta = substr($so, $o1, $o2-$o1);
	$grps = substr($so, $o3, $o4-$o3);

	$src = COPYPIX_DEF();
	$src['rgba']['w'] = 0x100;
	$src['rgba']['h'] = 0x100;
	$src['rgba']['pix'] = canvpix(0x100,0x100);

	$len = strlen($grps);
	for ( $i=0; $i < $len; $i += 12 )
	{
		$num = str2int($grps, $i+3, 1);
		$off = str2int($grps, $i+8, 2);
		sectpart($meta, $src, $pfx, $i/12, $num, $off);
	} // for ( $i=0; $i < $len; $i += 12 )

	savepix("$pfx/src", $src);

	// sprite animation sequence
	$meta = substr($so, $o4, $o5-$o4);
	$grps = substr($so, $o5, $o6-$o5);
	sectanim($grps, $meta, $pfx);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );
