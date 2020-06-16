<?php
require "common.inc";

define("CANV_S", 0x200);

function loadclut( &$clut, $dir, $id )
{
	if ( ! isset( $clut[$id] ) )
	{
		$file = file_get_contents("$dir/0.3");
		if ( empty($file) )  return "";

		while ( strlen($file) % 0x20 )
			$file .= ZERO;

		$cn = strlen($file) / 0x20;
		$clut = mclut2str($file, 0, 16, $cn);
		echo "ADDED clut $id = $cn\n";
	}
	return $clut[$id];
}

function loadtexx( &$texx, $dir, $id, $sx, $sy, $w, $h )
{
	if ( ! isset( $texx[$id] ) )
	{
		$file = file_get_contents("$dir/$id.1");
		if ( empty($file) )  return "";

		$len = strlen($file);
		switch ( $len )
		{
			case 0x2000: // 128x128 , 4-bit
			case 0x8000: // 256x256 , 4-bit
				$texx[$id] = "";
				for ( $i=0; $i < $len; $i++ )
				{
					$b = ord($file[$i]);
					$b1 = $b & 0x0f;
					$b2 = $b >> 4;
					$texx[$id] .= chr($b1) . chr($b2);
				}
				break;
			case 0x4000: // 128x128 , 8-bit
				$texx[$id] = $file;
				break;
			default:
				trigger_error("$texx $id = $len\n", E_USER_WARNING);
				break;
		} // switch ( $len )
		echo "ADDED texx $id\n";
	}

	$len = strlen( $texx[$id] );
	switch ( $len )
	{
		case 0x4000: // 128x128
			return rippix8($texx[$id], $sx, $sy, $w, $h, 0x80, 0x80);
		case 0x10000: // 256x256
			return rippix8($texx[$id], $sx, $sy, $w, $h, 0x100, 0x100);
	}
	return "";
}
//////////////////////////////
function loadsodat( $dir )
{
	// loading so/p_xxxx.dat
	// avoid [OOE] jnt/j_xxx.jnt
	// avoid [DOS/POR] sm/xxx.nsbmd
	// avoid [POR] sm/xxx.nsbtx
	$id = 0;
	while (1)
	{
		$file = file_get_contents( "$dir/$id.2" );
		$b = ord( $file[3] );
		if ( $b & 0x80 )
			return $file;
		$id++;
	}
	return '';
}

function sectpart( &$meta, &$src, $dir, $id, $num, $off )
{
	printf("=== sectpart( $dir , $id , $num , %x )\n", $off);

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = CANV_S;
	$pix['rgba']['h'] = CANV_S;
	$pix['rgba']['pix'] = canvpix(CANV_S,CANV_S);

	$clut = array();
	$texx = array();

	while ( $num > 0 )
	{
		// 0 1  2 3  4 5  6 7  8 9  10 11  12 13 14 15
		// dx-  dy-  sx-  sy-  w--  h----  t  f  c  -
		$num--;
		$p = $off + ($num * 0x10);

		zero_watch("v15", $meta[$p+15]);

		$dx = sint16( $meta[$p+0] . $meta[$p+1] );
		$dy = sint16( $meta[$p+2] . $meta[$p+3] );
		$pix['dx'] = $dx + (CANV_S / 2);
		$pix['dy'] = $dy + (CANV_S / 2);

		$sx = str2int($meta, $p+ 4, 2);
		$sy = str2int($meta, $p+ 6, 2);
		$w  = str2int($meta, $p+ 8, 2);
		$h  = str2int($meta, $p+10, 2);
		$tid = ord( $meta[$p+12] );

		$p14 = ord( $meta[$p+14] );
		$cid = $p14;

		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = loadtexx($texx, $dir, $tid, $sx, $sy, $w, $h);
		$pix['src']['pal'] = loadclut($clut, $dir, $cid);

		$p13 = ord( $meta[$p+13] );
		$pix['vflip'] = $p13 & 1;
		$pix['hflip'] = $p13 & 2;
		flag_warn("p13", $p13 & 0xfc);

		while ( ($tid+1)*0x100 > $src['rgba']['h'] )
		{
			$src['rgba']['pix'] .= canvpix(0x100,0x100);
			$src['rgba']['h'] += 0x100;
		}
		$src['dx'] = $sx;
		$src['dy'] = $sy + ($tid * 0x100);
		$src['src']['w'] = $w;
		$src['src']['h'] = $h;
		$src['src']['pix'] = loadtexx($texx, $dir, $tid, $sx, $sy, $w, $h);
		$src['src']['pal'] = loadclut($clut, $dir, $cid);

		printf("%4d , %4d , %4d , %4d , %4d , %4d", $dx, $dy, $sx, $sy, $w, $h);
		printf(" , $tid , %02x , %02x [$cid]\n", $p13, $p14);
		copypix($pix);
		copypix($src);
	} // for ( $i=0; $i < $num; $i++ )

	$fn = sprintf("$dir/%04d", $id);
	savpix($fn, $pix, true);
	return;
}

function sectanim( &$meta, $id, $num, $off )
{
	if ( $num < 1 )
		return "";

	$ret = array();
	for ( $i=0; $i < $num; $i++ )
	{
		$p = $off + ($i*8);
		$b1 = str2int($meta, $p+0, 2);
		$b2 = str2int($meta, $p+2, 2);
		$ret[] = "$b1-$b2";
	}

	$buf = "anim_{$id} = ";
	$buf .= implode(' , ', $ret);
	return "$buf\n";
}
//////////////////////////////
function cvnds( $dir )
{
	if ( ! file_exists("$dir/0.1") )  return; // texture
	if ( ! file_exists("$dir/0.2") )  return; // metadata
	if ( ! file_exists("$dir/0.3") )  return; // palette

	$file = loadsodat( $dir );
	$o1 = str2int($file, 0x04, 4);
	$o2 = str2int($file, 0x08, 4);
	$o3 = str2int($file, 0x0c, 4);
	$o4 = str2int($file, 0x10, 4);
	$o5 = str2int($file, 0x14, 4);
	$o6 = str2int($file, 0x20, 4);
	// 0x24 = total sprite
	// 0x28 = total animation

	// sprite parts data
	$meta = substr($file, $o1, $o2-$o1);
	$grps = substr($file, $o3, $o4-$o3);

	$src = COPYPIX_DEF();
	$src['rgba']['w'] = 0x100;
	$src['rgba']['h'] = 0x100;
	$src['rgba']['pix'] = canvpix(0x100,0x100);
	$src['bgzero'] = true;

	$ed = strlen($grps);
	$st = 0;
	$id = 0;
	while ( $st < $ed )
	{
		$num = ord( $grps[$st+3] );
		$off = str2int($grps, $st+8, 2);
		sectpart($meta, $src, $dir, $id, $num, $off);

		$id++;
		$st += 12;
	} // while ( $st < $ed )

	savpix("$dir/src", $src);

	// sprite animation sequence
	$meta = substr($file, $o4, $o5-$o4);
	$grps = substr($file, $o5, $o6-$o5);

	$ed = strlen($grps);
	$st = 0;
	$id = 0;
	$buf = "";
	while ( $st < $ed )
	{
		$num = ord( $grps[$st+0] );
		$off = str2int($grps, $st+4, 2);
		$buf .= sectanim($meta, $id, $num, $off);

		$id++;
		$st += 8;
	} // while ( $st < $ed )
	save_file("$dir/anim.txt", $buf);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );
