<?php
/*
[license]
[/license]
 */
require "common.inc";

//define("DRY_RUN", true);

$gp_pix  = array();
$gp_clut = array();

// for 3D textures
function loadtex( &$file, $pos, $dir )
{
	global $gp_clut, $gp_pix;
	$gp_clut = array();
	$gp_pix  = array();
	$p1 = str2int($file, $pos+4, 4);
	$p2 = str2int($file, $pos+8, 4);
	$tex = substr($file, $pos+$p1, $p2-$p1);

	$num = str2int($tex, 0, 2);
	printf("= loadtex( %x ) = $num, %x-%x\n", $pos, $p1, $p2);
	for ( $i=0; $i < $num; $i++ )
	{
		$p = 4 + ($i * 4);
		$p1 = str2int($tex, $p+0, 4);
		$ty = str2int($tex, $p1, 4);
		printf("p %x p1 %x\n", $p, $p1);
		switch ( $ty )
		{
			case 0x1101:
				$p2 = str2int($tex, $p+4, 4);
				$cn = ($p2 - $p1 - 0x10) / 0x20;
				//$gp_clut = mstrpal555($tex, $p1+0x10, 16, $cn);
				printf("CLUT , %d , %d [%x]\n", $i, $cn, $p+$pos);
				save_file("$dir/pal", substr($tex, $p1+0x10, $cn*0x20));
				break;
			case 0x1100:
				$w = str2int($tex, $p1+12, 2) * 2;
				$h = str2int($tex, $p1+14, 2);
				$sz = $w * $h;
				printf("PIX  , %d , %d x %d [%x]\n", $i, $w, $h, $p+$pos);

				$p = $p1 + 16;
				$pix = "";
				while ( $sz > 0 )
				{
					$b = ord( $tex[$p] );
					$b1 = ($b >> 0) & BIT4;
					$b2 = ($b >> 4) & BIT4;
					$pix .= chr($b1) . chr($b2);

					$p++;
					$sz--;
				}
				$gp_pix[$i]['p'] = $pix;
				$gp_pix[$i]['w'] = $w * 2;
				$gp_pix[$i]['h'] = $h;
				break;
			default:
				printf("UNK  , %d [%x]\n", $i, $p+$pos);
				break;
		} // switch ( $ty )
	} // for ( $i=0; $i < $num; $i++ )

	if ( empty($gp_clut) )
		$gp_clut[] = grayclut(0x10);
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
			$p1 = str2int($file, $mp+ 4, 4);
			$p2 = str2int($file, $mp+ 8, 4);
			$p3 = str2int($file, $mp+12, 4); // end

			$s1 = substr($file, $mp+$p1, $p2-$p1);
			$s2 = substr($file, $mp+$p2, $p3-$p2);

			save_file("$dir/0.meta", $s1);
			save_file("$dir/1.meta", $s2);
			loadtex($file, $pp, $dir);

			global $gp_clut, $gp_pix;
			foreach ( $gp_pix as $pk => $pv )
			{
				foreach ( $gp_clut as $ck => $cv )
				{
					$clut = "CLUT";
					$clut .= chrint(0x10, 4);
					$clut .= chrint($pv['w'], 4);
					$clut .= chrint($pv['h'], 4);
					$clut .= $cv;
					$clut .= $pv['p'];
					save_file("$dir/$pk-$ck.clut", $clut);
				}
			}
			return;
		case 3:
		case 4:
		case 5:
			echo "SKIP $dir is 2D pixels\n";
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
	xeno jp1 / slps 011.60
		2146-2288  3d models
		2776-2925  3d models
		2928-2988  3d models
		2619-2770  spr1 monsters bosses , 3d models
	xeno jp2 / slps 011.61
		2610-2761  spr1 monsters bosses , 3d models
*/
