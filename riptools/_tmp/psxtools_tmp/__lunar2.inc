<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D Games.
    <https://github.com/rufaswan/Web2D_Games>

Web2D Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */

function sectquad( &$pix, &$meta, $p, $ceil )
{
	$qax = sint16( $meta[$p+ 6] . $meta[$p+ 7] );
	$qay = sint16( $meta[$p+ 8] . $meta[$p+ 9] );
	$qbx = sint16( $meta[$p+10] . $meta[$p+11] );
	$qby = sint16( $meta[$p+12] . $meta[$p+13] );
	$qcx = sint16( $meta[$p+14] . $meta[$p+15] );
	$qcy = sint16( $meta[$p+16] . $meta[$p+17] );
	$qdx = sint16( $meta[$p+18] . $meta[$p+19] );
	$qdy = sint16( $meta[$p+20] . $meta[$p+21] );
		$qax = (int)($qax * SCALE);
		$qay = (int)($qay * SCALE);
		$qbx = (int)($qbx * SCALE);
		$qby = (int)($qby * SCALE);
		$qcx = (int)($qcx * SCALE);
		$qcy = (int)($qcy * SCALE);
		$qdx = (int)($qdx * SCALE);
		$qdy = (int)($qdy * SCALE);

	$pix['quad'] = [
		$qax+$ceil , $qay+$ceil ,
		$qbx+$ceil , $qby+$ceil ,
		$qcx+$ceil , $qcy+$ceil ,
		$qdx+$ceil , $qdy+$ceil ,
	];

	$pix['src']['quad'] = [
		                 0 ,                  0 ,
		$pix['src']['w']-1 ,                  0 ,
		$pix['src']['w']-1 , $pix['src']['h']-1 ,
		                 0 , $pix['src']['h']-1 ,
	];

	$des = [
		$qax , $qay ,
		$qbx , $qby ,
		$qcx , $qcy ,
		$qdx , $qdy ,
	];

	quad_dump($pix['src']['quad'] , "src quad");
	quad_dump($des                , "des quad");
	return;
}

function sectparts( &$meta, $dir )
{
	trace("== sectparts( $dir )\n");

	$ceil = int_ceil(CANV_S * SCALE, 2);
	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $ceil;
	$pix['rgba']['h'] = $ceil;
	$pix['rgba']['pix'] = canvpix($ceil,$ceil);

	global $gp_pix, $gp_clut;
	$len = strlen($meta);
	$sw = strlen($gp_pix) / 0x100;
	$sh = 0x100;
	for ( $i=0; $i < $len; $i += 0x16 )
	{
		$b1 = ord( $meta[$i+0] );
		$b2 = ord( $meta[$i+1] );
		$sx = ord( $meta[$i+2] );
		$sy = ord( $meta[$i+3] );
		$w  = ord( $meta[$i+4] );
		$h  = ord( $meta[$i+5] );
			$sx = $b2 * 0x80 + $sx;

		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = rippix8($gp_pix, $sx, $sy, $w, $h, $sw, $sh);
		$pix['src']['pal'] = $gp_clut;
		$pix['bgzero'] = 0;

		sectquad($pix, $meta, $i, $ceil/2);
		trace("%4d , %4d , %4d , %4d", $sx, $sy, $w, $h);
		trace(" , %02x\n", $b1);

		copyquad($pix, 1);
	} // for ( $i=0; $i < $len; $i += 0x16 )

	savepix($dir, $pix, true);
	return;
}

function sectmeta( &$meta, $dir )
{
	trace("== sectmeta( $dir )\n");
	//save_file("$dir/meta", $meta);

	$ed = str2int($meta, 4, 4);
	$st = str2int($meta, 0, 4);
	if ( $st < 8 )
		return;
	for ( $i=$st; $i < $ed; $i += 4 )
	{
		$num = str2int($meta, $i+0, 2);
		$off = str2int($meta, $i+2, 2);

		$pos = $ed + ($off * 0x16);
		$sub = substr ($meta, $pos, $num*0x16);
		trace("meta() %x , %x\n", $pos, $num*0x16);

		$fn = sprintf("$dir/%04d", ($i-$st)/4);
		sectparts($sub, $fn);
	} // for ( $i=$st; $i < $ed; $i += 4 )

	// tileset
		global $gp_pix, $gp_clut;
		$w = strlen($gp_pix) / 0x100;
		$h = 0x100;

		$clut = "CLUT";
		$clut .= chrint(0x100, 4);
		$clut .= chrint($w, 4);
		$clut .= chrint($h, 4);
		$clut .= $gp_clut;
		$clut .= $gp_pix;
		save_file("$dir/pix.clut", $clut);
	return;
}
//////////////////////////////
function pck_mnsprcha( $sprn, $chan )
{
	echo "== pck_mnsprcha( $sprn, $chan )\n";
	$spr = load_file($sprn);
	$cha = load_file($chan);
	if ( empty($spr) || empty($cha) )
		return;
	loadpck($spr, $sprn); // all 15+ (??? , ??? , clut , ??? , ??? , ??? , meta ...)
	loadpck($cha, $chan); // all  1  (pix)

	$dir = str_replace('.', '_', $sprn);

	global $gp_pix, $gp_clut;
	$gp_pix  = $cha[0];
	$gp_clut = pal555( $spr[2] );

	$cnt = count($spr) - 6;
	for ( $i=0; $i < $cnt; $i++ )
		sectmeta($spr[6+$i], "$dir/$i");
	return;
}

function pck_efsprcha( $sprn, $chan )
{
	echo "== pck_efsprcha( $sprn, $chan )\n";
	$spr = load_file($sprn);
	$cha = load_file($chan);
	if ( empty($spr) || empty($cha) )
		return;
	loadpck($spr, $sprn); // all 2 (??? , meta)
	loadpck($cha, $chan); // all 2 (pix , clut)

	$dir = str_replace('.', '_', $sprn);

	global $gp_pix, $gp_clut;
	$gp_pix  = $cha[0];
	$gp_clut = pal555( $cha[1] );
	sectmeta($spr[1], $dir);
	return;
}

function pck_title( $fname )
{
	echo "== pck_title( $fname )\n";
	$pck = load_file($fname);
	if ( empty($pck) )  return;
	loadpck($pck, $fname); // 9 (pix*3 , clut*3 , meta*3)

	$dir = str_replace('.', '_', $fname);

	global $gp_pix, $gp_clut;
	for ( $i=0; $i < 3; $i++ )
	{
		$gp_pix  = $pck[$i+0];
		$gp_clut = pal555( $pck[$i+3] );
		sectmeta($pck[$i+6], "$dir/$i");
	}
	return;
}

function pck_continue( $fname )
{
	echo "== pck_continue( $fname )\n";
	$pck = load_file($fname);
	if ( empty($pck) )  return;
	loadpck($pck, $fname); // 3 (pix , clut , meta)

	$dir = str_replace('.', '_', $fname);

	global $gp_pix, $gp_clut;
	$gp_pix  = $pck[0];
	$gp_clut = pal555( $pck[1] );
	sectmeta($pck[2], $dir);
	return;
}

function pck_pc( $fname )
{
	echo "== pck_pc( $fname )\n";
	$pck = load_file($fname);
	if ( empty($pck) )  return;
	loadpck($pck, $fname); // 3 (meta , meta , pix) 2 (meta , pix)

	$dir = str_replace('.', '_', $fname);
	$cnt = count($pck);

	// require palette from sysspr.pck
	global $gp_pix, $gp_clut;
	if ( empty($gp_clut) )
		$gp_clut = grayclut(0x100);

	if ( $cnt == 2 )
	{
		$gp_pix  = $pck[1];
		sectmeta($pck[0], $dir);
	}
	if ( $cnt == 3 )
	{
		$gp_pix  = $pck[2];
		sectmeta($pck[0], "$dir/0");
		sectmeta($pck[1], "$dir/1");
	}
	return;
}

function pck_syssprcha( $sprn, $chan )
{
	echo "== pck_syssprcha( $sprn, $chan )\n";
	$spr = load_file($sprn);
	$cha = load_file($chan);
	if ( empty($spr) || empty($cha) )
		return;
	loadpck($spr, $sprn); // 18 (meta , ???*3 , clut , sjis*8 , ???*5)
	loadpck($cha, $chan); // 12 (pix , clut*10, pix)
		$pal = [];
		$pal[] = pal555( $spr[ 4] ); // _pc*
		$pal[] = pal555( $cha[ 1] ); // menu active
		$pal[] = pal555( $cha[ 2] ); // menu inactive
		$pal[] = pal555( $cha[ 3] ); //
		$pal[] = pal555( $cha[ 4] ); // inactive
		$pal[] = pal555( $cha[ 5] ); //
		$pal[] = pal555( $cha[ 6] ); // inactive
		$pal[] = pal555( $cha[ 7] ); //
		$pal[] = pal555( $cha[ 8] ); //
		$pal[] = pal555( $cha[ 9] ); // inactive
		$pal[] = pal555( $cha[10] ); //

	$dir = str_replace('.', '_', $sprn);

	global $gp_pix, $gp_clut;
	$gp_pix  = $cha[0];
	$gp_clut = $pal[1];
	sectmeta($spr[0], $dir);

	foreach ( $pal as $ck => $cv )
		save_file("$dir/$ck.pal", $cv);

	// for _pc*.pck later
	$gp_clut = $pal[0];
	return;
}
//////////////////////////////
/*
	// special effects
	if ( stripos($fname, 'efspr') !== false )
	{
		$fn2 = str_ireplace('efspr', 'efcha', $fname);
		return pck_efsprcha($fname, $fn2);
	}
	if ( stripos($fname, 'efcha') !== false )
	{
		$fn2 = str_ireplace('efcha', 'efspr', $fname);
		return pck_efsprcha($fn2, $fname);
	}

	// monsters + bosses
	if ( stripos($fname, 'mnspr') !== false )
	{
		$fn2 = str_ireplace('mnspr', 'mncha', $fname);
		return pck_mnsprcha($fname, $fn2);
	}
	if ( stripos($fname, 'mncha') !== false )
	{
		$fn2 = str_ireplace('mncha', 'mnspr', $fname);
		return pck_mnsprcha($fn2, $fname);
	}

	// system
	if ( stripos($fname, 'sysspr') !== false )
	{
		$fn2 = str_ireplace('sysspr', 'syscha', $fname);
		return pck_syssprcha($fname, $fn2);
	}
	if ( stripos($fname, 'syscha') !== false )
	{
		$fn2 = str_ireplace('syscha', 'sysspr', $fname);
		return pck_syssprcha($fn2, $fname);
	}

	// player characters
	if ( stripos($fname, '_pc') !== false )
		return pck_pc($fname);

	// misc
	if ( stripos($fname, 'continue') !== false )
		return pck_continue($fname);
	if ( stripos($fname, 'title') !== false )
		return pck_title($fname);

require "common-quad.inc";
require "lunar2.inc";

define("CANV_S", 0x300);
define("SCALE", 1.0);
//define("DRY_RUN", true);

$gp_pix  = "";
$gp_clut = "";

function loadpck( &$file, $fname )
{
	$num = str2int($file, 0, 4);
	printf("== loadpck( $fname ) = $num\n");

	$ed = strlen($file);
	$st = 4;
	$pck = [];
	while ( $st < $ed )
	{
		$siz = str2int($file, $st, 4);
			$st += 4;
		if ( $siz == 0 )
			break;
		printf("%3d  %6x  %6x\n", count($pck), $st, $siz);
		$pck[] = substr($file, $st, $siz);
			$st += $siz;
	}

	$file = $pck;
	return;
}
//////////////////////////////
function pck_btlbk( $fname )
{
	echo "== pck_btlbk( $fname )\n";
	$pck = load_file($fname);
	if ( empty($pck) )  return;
	loadpck($pck, $fname); // all 2 (pix , clut)

	$w = strlen($pck[0]) / 0x100;
	$h = 0x100;

	$clut = "CLUT";
	$clut .= chrint(0x100, 4);
	$clut .= chrint($w, 4);
	$clut .= chrint($h, 4);
	$clut .= pal555($pck[1]);
	$clut .= $pck[0];

	file_put_contents("$fname.clut", $clut);
	return;
}
//////////////////////////////
 */

