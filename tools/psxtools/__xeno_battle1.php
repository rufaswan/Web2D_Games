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

// callback for copypix()
function xeno_alp( $fg, $bg )
{
	if ( $fg == $bg )
		return $fg;
	return alpha_add( $fg, $bg );
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

	$data = array();
	$id = 0;
	$pos = $off + 4 + ($num * 2);
	//debug( substr($meta, $pos, 0x10) );
	while ( $id < $num )
	{
		while(1)
		{
			$b1 = ord( $meta[$pos] ) >> 4;
			if ( $b1 == 0xf )  $pos += 4;
			else
			if ( $b1 == 0xe )  $pos += 3;
			else
			if ( $b1 == 0xd )  $pos += 2;
			else
			if ( $b1 == 0xc )  $pos += 1;
			else
			if ( $b1 == 0x8 )  $pos += 1;
			else
				break;
		}

		$m1 = substr($meta, $pos, 3);
		$pos += 3;

		$p1 = $off + 4 + ($id * 2);
		$p2 = str2int($meta, $p1, 2);
		$m2 = substr($meta, $p2, 5);
			$id++;
		printf("pos %x , part %x %x , id %x\n", $pos-3, $p1, $p2, $id-1);
		array_unshift($data, array($m1,$m2));
	}

	global $gp_pix, $gp_clut;
	foreach ( $data as $v )
	{
		list($m1,$m2) = $v;

		$dx = sint8( $m1[1] );
		$dy = sint8( $m1[2] );
		$pix['dx'] = $dx + (CANV_S / 2);
		$pix['dy'] = $dy + (CANV_S / 2);

		$m10 = ord( $m1[0] );
		$pix['hflip'] = $m10 & 0x40;
		$pix['vflip'] = $m10 & 0x20;
		$cid = $m10 & 0x0f;
		//alpha parts has both sprite + effect
		//$pix['alpha'] = ( $m10 & 0x10 ) ? "xeno_alp": "";

		$sx = ord( $m2[1] );
		$sy = ord( $m2[2] );
		$w  = ord( $m2[3] );
		$h  = ord( $m2[4] );

		$m20 = ord( $m2[0] );
		$tid = $m20 >> 1;
		flag_warn("m20", $m20 & 1);

		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = rippix8($gp_pix[$tid], $sx, $sy, $w, $h, 0x100, 0x100);
		$pix['src']['pal'] = $gp_clut[$cid];

		printf("%4d , %4d , %4d , %4d , %4d , %4d , %02x , %02x\n",
			$dx, $dy, $sx, $sy, $w, $h, $m10, $m20);
		copypix($pix);
	} // foreach ( $data as $v )

	savpix($fn, $pix, true);
	return;
}

function sect1( &$meta, &$file, $dir, $pp )
{
	// 2 - 3d (?? , ??)
	// 3 - 2d (anim , parts , clut)
	// 4 - 2d (anim , parts , clut , seds) [2312.bin]
	// 5 - 2d (anim , parts , clut , seds , wds)
	$num = str2int($meta, 0, 2);
	printf("=== sect1( $dir ) = $num\n");

	switch ( $num )
	{
		case 2:
			echo "SKIP $dir is 3D model\n";
			return;

			$p1 = str2int($meta,  4, 4);
			$p2 = str2int($meta,  8, 4);
			$p3 = str2int($meta, 12, 4); // end

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
			//save_file("$dir/1.meta", $s2);
			save_file("$dir/pal.dat", substr($s3,4));
			loadtim($file, $pp);

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
	if ( str2int($file, 4, 4) != str2int($file, 12, 4) )
		return;

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
		sect1($meta, $file, "{$dir}_{$i}", $pp);
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
