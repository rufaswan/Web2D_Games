<?php
require "common.inc";

define("CANV_S", 0x200);
//define("DRY_RUN", true);

$gp_pix  = array();
$gp_clut = array();

// for 2D pixels - 256 width
function loadpix( &$file, $pos )
{
	global $gp_pix;
	$gp_pix = array();
	$pix = substr($file, $pos);

	$num = str2int($pix, 0, 2);
	printf("= loadpix( %x ) = $num\n", $pos);
	for ( $i=0; $i < $num; $i++ )
	{
		$p = 4 + ($i * 4);
		$p = str2int($pix, $p, 4);
		$w = str2int($pix, $p+0, 2) * 2;
		$h = str2int($pix, $p+2, 2);
			$p += 4;

		$siz = $w * $h;
		$rip = "";
		while ( $siz > 0 )
		{
			$b = ord($pix[$p]);
			$b1 = $b & 0x0f;
			$b2 = $b >> 4;
			$rip .= chr($b1) . chr($b2);

			$siz--;
			$p++;
		}
		$gp_pix[$i]['p'] = $rip;
		$gp_pix[$i]['w'] = $w * 2;
		$gp_pix[$i]['h'] = $h;
	}
	return;
}

// for 2D pixels - variable width
function loadsrc( &$meta, $off, &$pix )
{
	$w = ord( $meta[$off+0] );
	$h = ord( $meta[$off+1] );
		$off += 4;

	$siz = (int)($w / 2 * $h);
	$src = "";
	for ( $i=0; $i < $siz; $i++ )
	{
		$b = ord( $meta[$off+$i] );
		$b1 = $b & 0x0f;
		$b2 = $b >> 4;
		$src .= chr($b1) . chr($b2);
	}

	$pix['src']['w'] = $w;
	$pix['src']['h'] = $h;
	$pix['src']['pix'] = $src;
	return;
}

// callback for copypix()
function xeno_alp( $fg, $bg )
{
	if ( $fg == $bg )
		return $fg;
	return alpha_add( $fg, $bg );
}
//////////////////////////////
function sectparts( &$meta, $off, $fn, $p256, $phdz, $pofz )
{
	$p = ord( $meta[$off] );
	$num = $p & 0x7f;
	$big = $p >> 7;
	printf("=== sectparts( %x , $fn , %d , $phdz , $pofz ) = $num & %d\n", $off, $p256, $big);
	if ( $num == 0 )
		return;

	$data = array();
	$id = 0;
	$pos = $off + $phdz + ($num * $pofz);
	$rx = 0;
	$ry = 0;
	$rot = 0;
	while ( $id < $num )
	{
		$vflip = false;
		while (1)
		{
			$b1 = ord( $meta[$pos] );
			switch ( $b1 >> 4 )
			{
				case 0xf:
					$rx = sint8( $meta[$pos+1] );
					$ry = sint8( $meta[$pos+2] );
					$rot = ord( $meta[$pos+3] );
					//debug( substr($meta, $pos, 4) );;
					printf("%2x , %4d , %4d , %4d\n", $b1, $rx, $ry, $rot);
					$pos += 4;
					break;
				case 0xe:
					$rx = sint8( $meta[$pos+1] );
					$ry = sint8( $meta[$pos+2] );
					$rot = 0;
					//debug( substr($meta, $pos, 3) );;
					printf("%2x , %4d , %4d\n", $b1, $rx, $ry);
					$pos += 3;
					break;
				case 0xc:
					$rx = 0;
					$ry = 0;
					$rot = 0;
					//debug( substr($meta, $pos, 1) );;
					printf("%2x\n", $b1);
					$pos += 1;
					break;
				case 0x8:
					$vflip = true;
					//debug( substr($meta, $pos, 1) );;
					printf("%2x\n", $b1);
					$pos += 1;
					break;
				default:
					if ( ($b1 & 0x80) == 0 )
						break 2;
					debug( substr($meta, $pos, 1) );;
					$pos += 1;
					break;
			}
		} // while (1)

		$bak = $pos;
		if ( $big )
		{
			$dx = sint16( $meta[$bak+1].$meta[$bak+2] );
			$dy = sint16( $meta[$bak+3].$meta[$bak+4] );
			$pos += 5;
		}
		else
		{
			$dx = sint8( $meta[$bak+1] );
			$dy = sint8( $meta[$bak+2] );
			$pos += 3;
		}
		$m1 = array($meta[$bak+0], $dx, $dy, $rot, $rx, $ry, $vflip);

		$p1 = $off + $phdz + ($id * $pofz);
		if ( $p256 )
		{
			$p2 = str2int($meta, $p1, 2);
			$m2 = substr($meta, $p2, 5);
		}
		else
			$m2 = substr($meta, $p1, 4);
		printf("pos %x , part %x , id %x\n", $bak, $p1, $id);
		array_unshift($data, array($m1,$m2));
		$id++;
	} // while ( $id < $num )

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = CANV_S;
	$pix['rgba']['h'] = CANV_S;
	$pix['rgba']['pix'] = canvpix(CANV_S,CANV_S);

	global $gp_pix, $gp_clut;
	foreach ( $data as $v )
	{
		list($b1,$dx,$dy,$rot,$rx,$ry,$vflip) = $v[0];
		$m2 = $v[1];

		if ( $rot == 0 )
		{
			$pix['dx'] = $dx + $rx + (CANV_S / 2);
			$pix['dy'] = $dy + $ry + (CANV_S / 2);
			$pix['rotate'] = array(0,0,0);
		}
		else
		{
			$pix['dx'] = $rx + (CANV_S / 2);
			$pix['dy'] = $ry + (CANV_S / 2);
			$pix['rotate'] = array($rot, $dx, $dy);
		}

		$m10 = ord( $b1[0] );
		$pix['hflip'] = $m10 & 0x40;
		$pix['vflip'] = $vflip;
		$cid = $m10 & 0x0f;
		//alpha parts has both sprite + effect
		//$pix['alpha'] = ( $m10 & 0x20 ) ? "xeno_alp": "";

		if ( $p256 )
		{
			$sx = ord( $m2[1] );
			$sy = ord( $m2[2] );
			$w  = ord( $m2[3] );
			$h  = ord( $m2[4] );

			$m20 = ord( $m2[0] );
			$tid = $m20 >> 1;
			flag_warn("m20", $m20 & 1);

			$pix['src']['w'] = $w;
			$pix['src']['h'] = $h;
			$pix['src']['pix'] = rippix8($gp_pix[$tid]['p'], $sx, $sy, $w, $h, $gp_pix[$tid]['w'], $gp_pix[$tid]['h']);
			$pix['src']['pal'] = $gp_clut[$cid];
		}
		else
		{
			$m20 = str2int($m2, 0, 2) * 4;
			$m22 = str2int($m2, 2, 2);

			loadsrc($meta, $m20, $pix);
			$sx = 0;
			$sy = 0;
			$w = $pix['src']['w'];
			$h = $pix['src']['h'];
			$pix['src']['pal'] = $gp_clut[$cid];
		}

		printf("%4d , %4d , %4d , %4d , %4d , %4d", $dx, $dy, $sx, $sy, $w, $h);
		printf(" , %02x , %02x\n", $m10, $m20);
		copypix($pix);
	} // foreach ( $data as $v )

	savpix($fn, $pix, true);
	return;
}
//////////////////////////////
function sect1( &$file, $dir, $mp, $pp )
{
	// 2 - 3d (data , seds)
	//     4 - data (clut + texture , ??? , ??? , ???)
	// 3 - 2d (anim , parts , clut)
	// 4 - 2d (anim , parts , clut , seds)
	// 5 - 2d (anim , parts , clut , seds , wds)
	$num = str2int($file, $mp+0, 2);
	printf("=== sect1( $dir , %x , %x ) = $num\n", $mp, $pp);

	switch ( $num )
	{
		case 2:
			echo "SKIP $dir is 3D model\n";
			return;
		case 3:
		case 4:
		case 5:
			$p1 = str2int($file, $mp+ 4, 4);
			$p2 = str2int($file, $mp+ 8, 4);
			$p3 = str2int($file, $mp+12, 4); // palette
			$p4 = str2int($file, $mp+16, 4); // end  4+,extra

			$s1 = substr($file, $mp+$p1, $p2-$p1);
			$s2 = substr($file, $mp+$p2, $p3-$p2);
			$s3 = substr($file, $mp+$p3, $p4-$p3);

			//save_file("$dir/0.meta", $s1);
			//save_file("$dir/1.meta", $s2);
			save_file("$dir/pal", substr($s3,4));

			global $gp_clut;
			$cn = (strlen($s3) - 4) / 0x20;
			$gp_clut = mclut2str($s3, 4, 16, $cn);

			$p256 = ord( $s2[1] ) >> 7;
			if ( $p256 )
			{
				echo "DETECT fixed 256 width pixels\n";
				loadpix($file, $pp);
				$phdz = 4;
				$pofz = 2;
			}
			else
			{
				echo "DETECT variable width pixels\n";
				$phdz = 6;
				$pofz = 4;
			}

			$num = ord( $s2[0] );
			for ( $i=0; $i < $num; $i++ )
			{
				$p = 2 + ($i * 2);
				$off = str2int($s2, $p, 2);
				$fn = sprintf("$dir/%04d", $i);
				sectparts( $s2, $off, $fn, $p256, $phdz, $pofz );
			}
			return;
	}
	return;
}

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$num = str2int($file, 0, 4);

	// sprite 2
	$end = str2int($file, 4 + ($num*4), 4);
	if ( $end == strlen($file) )
	{
		echo "DETECT sprite 2 = $fname\n";
		sect1($file, $dir, 0, 0);
		return;
	}

	// sprite 1
	if ( str2int($file, 4, 4) == str2int($file, 12, 4) )
	{
		echo "DETECT sprite 1 = $fname\n";
		$off = str2int($file, 4, 4);
		for ( $i=0; $i < $num; $i++ )
		{
			$p = 8 + ($i * 12);
			$mp = str2int($file, $p+0, 4);
			$pp = str2int($file, $p+4, 4);
			if ( $pp < $off )
				continue;
			sect1($file, "$dir/$i", $mp, $pp);
		}
		return;
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );

/*
	spr1 data loaded to 801a52e0
		then appended to 8010791c
	spr2 data loaded + appended to 80109db4

	xeno jp1 / slps 011.60
		2619-2770  spr1 monsters bosses
		2989-3018  spr2 party
			2989 3006  fei
			2990 3007  elly
			2991 3008  bart
			2992 3009  citan    , 2998 3015 (sword)
			2993 3010  billy
			2994 3011  rico
			2995 3012  emeralda , 2999 3016 (adult)
			2996 3013  chuchu
			2997 3014  maria
	xeno jp2 / slps 011.61
		2610-2761  spr1 monsters bosses
		2980-3009  spr2 party

	2626 2702  > 256x256 canvas
	2710  mixed spr1 + spr2 / ramsus fight (+fei)


	DEBUG 2998,0111.png , 1-1ac , 2-2528
	  ( 898 + 2528 + 6 + (a*4) = 2dee )
*/
