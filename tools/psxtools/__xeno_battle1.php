<?php
require "common.inc";

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

			save_file("$dir/0.meta", substr($meta, $p1, $p2-$p1));
			save_file("$dir/1.meta", substr($meta, $p2, $p3-$p2));
			return;
		case 3:
		case 4:
		case 5:
			$p1 = str2int($meta,  4, 4);
			$p2 = str2int($meta,  8, 4);
			$p3 = str2int($meta, 12, 4); // palette
			$p4 = str2int($meta, 16, 4); // end  4+,extra

			save_file("$dir/0.meta", substr($meta, $p1, $p2-$p1));
			save_file("$dir/1.meta", substr($meta, $p2, $p3-$p2));
			save_file("$dir/2.meta", substr($meta, $p3, $p4-$p3));
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
