<?php
require "common.inc";

define("CANV_S", 0x200);
//define("DRY_RUN", true);

$gp_clut = array();

function loadsrc( &$meta, $off, &$pix )
{
	$w = ord( $meta[$off+0] );
	$h = ord( $meta[$off+1] );
		$off += 4;

	$sz = $w / 2 * $h;
	$src = "";
	for ( $i=0; $i < $sz; $i++ )
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
	$pos = $off + 6 + ($num * 4);
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

		$p1 = $off + 6 + ($id * 4);
		$m2 = substr($meta, $p1, 4);
		$p2 = str2int($m2, 0, 2) * 4;
			$id++;
		printf("pos %x , part %x %x , id %x\n", $pos-3, $p1, $p2, $id-1);
		array_unshift($data, array($m1,$m2));
	}

	global $gp_clut;
	$sx = 0;
	$sy = 0;
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

		$v1 = str2int($m2, 0, 2);
		$v2 = str2int($m2, 2, 2);

		loadsrc($meta, $v1*4, $pix);
		$w = $pix['src']['w'];
		$h = $pix['src']['h'];
		$pix['src']['pal'] = $gp_clut[$cid];

		printf("%4d , %4d , %4d , %4d , %4d , %4d , %02x\n",
			$dx, $dy, $sx, $sy, $w, $h, $m10);
		copypix($pix);
	} // foreach ( $data as $v )

	savpix($fn, $pix, true);
	return;
}

function sect1( &$file, $dir )
{
	$p1 = str2int($file, 0x04, 4);
	$p2 = str2int($file, 0x08, 4);
	$p3 = str2int($file, 0x0c, 4); // palette
	$p4 = str2int($file, 0x10, 4); // 3,end  4,seds  5,seds+wds

	$s1 = substr($file, $p1, $p2-$p1);
	$s2 = substr($file, $p2, $p3-$p2);
	$s3 = substr($file, $p3, $p4-$p3);

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

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$len = strlen($file);

	$mgc = str2int($file, 0, 4);
	$end = str2int($file, 4 + ($mgc*4), 4);
	if ( $end != $len )
		return;
	sect1($file, $dir);
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
