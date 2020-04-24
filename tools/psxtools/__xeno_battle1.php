<?php
require "common.inc";

define("CANV_S", 0x200);
//define("DRY_RUN", true);

$gp_pix  = array();
$gp_clut = array();

function loadtim( &$file, $pos )
{
	global $gp_pix;
	$gp_pix = array();
	$pix = substr($file, $pos);

	$num = str2int($pix, 0, 2);
	for ( $i=0; $i < $num; $i++ )
	{
		$p = 4 + ($i * 4);
		$p = str2int($pix, $p, 4);
		$w = str2int($pix, $p+0, 2) * 2;
		$h = str2int($pix, $p+2, 2);
			$p += 4;
		printf("loadtim %d , %d x %d [%x]\n", $i, $w, $h, $p+$pos);

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
		$gp_pix[$i] = $rip;
	}
	return;
}
//////////////////////////////
function sectparts( &$meta, $off, $fn )
{
	$num = ord( $meta[$off] );
	printf("=== sectparts( %x , $fn ) = $num\n", $off);
	if ( $num == 0 )
		return;

	$pix = COPYPIX_DEF;
	$pix['rgba']['w'] = CANV_S;
	$pix['rgba']['h'] = CANV_S;
	$pix['rgba']['pix'] = canvpix(CANV_S,CANV_S);

	global $gp_pix, $gp_clut;
	$p1 = $off + 4;
	$p2 = $off + 4 + ($num * 2);
	for ( $i=0; $i < $num; $i++ )
	{
		$v1 = $p1;
		$p1 += 2;
		$v3 = str2int($meta, $v1, 2);


/*
		$b1 = ord( $meta[$p2] );
		if ( $b1 & 0x80 )
		{
			while ( $meta[$p2] != chr(0xc4) )
				$p2++;
			$p2++; // skip c4
		}
		while( $meta[$p2] != ZERO )
		{
			if ( $b1 >= 0xe0 )
				$p2 += 3;
			else
			if ( $b1 >= 0xc0 )
				$p2++;
		}
		while (1)
		{
			if ( $b1 & 0x80 )
			{
				$p2 += 2;
				continue;
			}
			if ( $b1 == 0 )
				break;
			$p2++;
		}
*/

		$v2 = $p2;
		$p2 += 3;
		//printf("%x,%x,%x,%x,%x\n", $p1, $p2, $v1, $v2, $v3);

		$dx = sint8( $meta[$v2+1] );
		$dy = sint8( $meta[$v2+2] );
		$pix['dx'] = $dx + (CANV_S / 2);
		$pix['dy'] = $dy + (CANV_S / 2);

		$v20 = ord( $meta[$v2+0] );

		$sx = ord( $meta[$v3+1] );
		$sy = ord( $meta[$v3+2] );
		$w  = ord( $meta[$v3+3] );
		$h  = ord( $meta[$v3+4] );

		$v30 = ord( $meta[$v3+0] );
		$tid = $v30 >> 1;

		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = rippix8($gp_pix[$tid], $sx, $sy, $w, $h, 0x100, 0x100);
		$pix['src']['pal'] = $gp_clut[0];

		printf("%4d , %4d , %4d , %4d , %4d , %4d , %02x , %02x\n",
			$dx, $dy, $sx, $sy, $w, $h, $v20, $v30);
		copypix($pix);
	} // for ( $i=0; $i < $num; $i++ )

	savpix($fn, $pix, true);
	return;
}

function sect1( &$meta, &$file, $dir )
{
	// 2 - 3d (?? , ??)
	// 3 - 2d (?? , ?? , clut)
	// 4 - 2d (?? , ?? , clut , seds) [2312.bin]
	// 5 - 2d (?? , ?? , clut , seds , wds)
	$num = str2int($meta, 0, 2);
	printf("=== sect1( $dir ) = $num\n");

	switch ( $num )
	{
		case 2:
			$p1 = str2int($meta,  4, 4);
			$p2 = str2int($meta,  8, 4);
			$p3 = str2int($meta, 12, 4); // end

			echo "SKIP $dir is 3D model\n";
			return;

			$s1 = substr($meta, $p1, $p2-$p1);
			$s2 = substr($meta, $p2, $p3-$p2);

			save_file("$dir/0.meta", $s1);
			save_file("$dir/1.meta", $s2);
			return;
		case 3:
		case 4:
		case 5:
			$p1 = str2int($meta,  4, 4);
			$p2 = str2int($meta,  8, 4);
			$p3 = str2int($meta, 12, 4); // palette
			$p4 = str2int($meta, 16, 4); // end  4+,extra

			$s1 = substr($meta, $p1, $p2-$p1);
			$s2 = substr($meta, $p2, $p3-$p2);
			$s3 = substr($meta, $p3, $p4-$p3);

			//save_file("$dir/0.meta", $s1);
			save_file("$dir/1.meta", $s2);
			save_file("$dir/pal.dat", substr($s3,4));

			global $gp_clut;
			$cn = (strlen($s3) - 4) / 0x20;
			$gp_clut = mclut2str($s3, 4, 16, $cn);

			$num = ord( $s2[0] );
			for ( $i=0; $i < $num; $i++ )
			{
				$p = 2 + ($i * 2);
				$off = str2int($s2, $p, 2);
				$fn = sprintf("$dir/%04d", $i);
				sectparts( $s2, $off, $fn );
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

	$num = str2int($file, 0, 2);
	$off = str2int($file, 4, 4);
	for ( $i=0; $i < $num; $i++ )
	{
		$p = 8 + ($i * 12);
		$mp = str2int($file, $p+0, 4);
		$pp = str2int($file, $p+4, 4);
		if ( $pp < $off )
			continue;

		$meta = substr($file, $mp, $off-$mp);
		loadtim($file, $pp);
		sect1($meta, $file, "{$dir}_{$i}");
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );

/*
xeno jp1 / slps 011.60
  2619-2770  spr1 monsters bosses
  2989-3018  spr2 party
xeno jp2 / slps 011.61
  2610-2761  spr1 monsters bosses
  2980-3009  spr2 party
*/
