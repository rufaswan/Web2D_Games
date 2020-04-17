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
			$p1 = str2int($meta,  4, 4);
			$p2 = str2int($meta,  8, 4);
			$p3 = str2int($meta, 12, 4);
			$p4 = str2int($meta, 16, 4); // end

			save_file("$dir/0.meta", substr($meta, $p1, $p2-$p1));
			save_file("$dir/1.meta", substr($meta, $p2, $p3-$p2));
			save_file("$dir/2.meta", substr($meta, $p3, $p4-$p3));
			return;
		case 4:
			$p1 = str2int($meta,  4, 4);
			$p2 = str2int($meta,  8, 4);
			$p3 = str2int($meta, 12, 4);
			$p4 = str2int($meta, 16, 4);
			$p5 = str2int($meta, 20, 4); // end

			save_file("$dir/0.meta", substr($meta, $p1, $p2-$p1));
			save_file("$dir/1.meta", substr($meta, $p2, $p3-$p2));
			save_file("$dir/2.meta", substr($meta, $p3, $p4-$p3));
			save_file("$dir/3.meta", substr($meta, $p4, $p5-$p4));
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
sprites models bosses
  2619-2770  xeno jp1 / slps 011.60
*/
