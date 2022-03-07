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
require 'common.inc';

define('CANV_S', 0x300);
define('SCALE', 1.0);
//define('DRY_RUN', true);

$gp_pix  = array();
$gp_clut = array();

function loadtim( &$file, $base )
{
	printf("=== loadtim( %x )\n", $base);
	$dat = substr($file, $base);
	$tim = psxtim($dat);

	global $gp_pix, $gp_clut;
	$gp_clut[] = $tim['pal'];
	$gp_pix[]  = $tim['pix'];
	return;
}

// callback for copypix()
function ana_alp( $fg, $bg )
{
	return alpha_add( $fg, $bg );
}
//////////////////////////////
function secttalk( &$file, $talk, $dir )
{
	$num = str2int($file, $talk, 4);
	if ( $num == 0 )
		return;
	printf("= talk base %x = %d\n", $talk, $num);

	$st = $talk + 4;
	for ( $i=0; $i < $num; $i++ )
	{
		printf("= $dir/talk/$i.clut , %x\n", $st);
		$sz = 0x30 * 0x30 / 2;

		$pal = substr($file, $st+0, 0x20);
		$pix = substr($file, $st+0x20, $sz);
			$st += (0x20 + $sz);

		bpp4to8($pix);
		$img = array(
			'cc'  => 0x10,
			'w'   => 0x30,
			'h'   => 0x30,
			'pal' => pal555($pal),
			'pix' => $pix,
		);

		save_clutfile("$dir/talk/$i.clut", $img);
	} // for ( $i=0; $i < $num; $i++ )
	return;
}

function sectparts( &$meta, $off, $fn, $ids, $m, &$big )
{
	$num = ord( $meta[$off] );
		$off++;
	printf("=== sectparts( %x , $fn , $big ) = $num\n", $off);
	if ( $num == 0 )
		return;

	$data = array();
	while ( $num > 0 )
	{
		$num--;
		$p7 = ord( $meta[$off+7] );
		if ( $p7 & 0x20 )
		{
			if ( $m == 0 && $meta[$off+1] == BYTE && $meta[$off+3] == BYTE )
				$big = 'BIG';
			$n = ( $big ) ? 17 : 9;
			$off += $n;
		}
		else
		{
			if ( ! isset( $ids[ $p7 & 0x0f ] ) )
				return;
			if ( $m == 0 && $meta[$off+9] == BYTE && $meta[$off+10] == BYTE )
				$big = 'BIG';
			$n = ( $big ) ? 11 : 9;
			$s = substr($meta, $off, $n);
			array_unshift($data, $s);
			$off += $n;
		}
	} // while ( $num > 0 )
	if ( empty($data) )
		return;

	$ceil = int_ceil( CANV_S * SCALE, 2 );
	$pix = COPYPIX_DEF($ceil,$ceil);

	global $gp_pix, $gp_clut;
	foreach ( $data as $v )
	{
		// 0   1   2  3  4 5 6 7 8 9   10
		// dx1 dy1 sx sy w h c f r dx2 dy2
		if ( $big )
		{
			$dx = sint16( $v[0] . $v[ 9] );
			$dy = sint16( $v[1] . $v[10] );
		}
		else
		{
			$dx = sint8( $v[0] );
			$dy = sint8( $v[1] );
		}
		$dx = (int)($dx * SCALE);
		$dy = (int)($dy * SCALE);
		$pix['dx'] = $dx + $ceil/2;
		$pix['dy'] = $dy + $ceil/2;

		$sx = ord( $v[2] );
		$sy = ord( $v[3] );
		$w  = ord( $v[4] );
		$h  = ord( $v[5] );
		$cid = ord( $v[6] );

		$p7 = ord( $v[7] );
		$tid = ($p7 & 0x0f);
			$tid = $ids[$tid];
		$pix['vflip'] = $p7 & 0x80;
		$pix['hflip'] = $p7 & 0x40;
		$pix['alpha'] = '';
		//if ( $tid == 2 && $cid == 1 )
		if ( $cid == 11 ) // mask + image
			$pix['alpha'] = 'ana_alp';

		$rippix8 = rippix8($gp_pix[$tid], $sx, $sy, $w, $h, 0x100, 0x100);

		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = $rippix8;
		$pix['src']['pal'] = $gp_clut[$tid][$cid];
		$pix['bgzero'] = 0;
		scalepix($pix, SCALE, SCALE);

		$pix['rotate'] = array(ord($v[8]), 0, 0);

		printf("%4d , %4d , %4d , %4d , %4d , %4d", $dx, $dy, $sx, $sy, $w, $h);
		printf(" , $cid , %08b , %d\n", $p7, $pix['rotate'][0]);
		copypix($pix);
	} // foreach ( $data as $v )

	savepix($fn, $pix, true);
	return;
}

function sectanim( &$meta, $id, $pos, $flg )
{
	$num = ord($meta[$pos]);
		$pos++;
	if ( $num == 0 )
		return '';

	$ret = array();
	for ( $i=0; $i < $num; $i++ )
	{
		$p = $pos + ($i * 2);
		$b1 = ord($meta[$p+0]);
		$b2 = ord($meta[$p+1]);
		$ret[] = "$b1-$b2";
	}
	if ( $flg )
		$ret[] = 'flag';

	$buf = "anim_{$id} = ";
	$buf .= implode(' , ', $ret);
	return "$buf\n";
}

function sectmeta( &$meta, $dir, $ids )
{
	printf("=== sectmeta( $dir )\n");
	$off = str2int($meta, 2, 2);

	// sprite parts data
	$cnt = str2int($meta, $off, 2);
	$big = '';

	for ( $m=0; $m < $cnt; $m++ )
	{
		$pos = $off + 2 + ($m * 2);
		$pos = str2int($meta, $pos, 2);
		$fn  = sprintf('%s/%04d', $dir, $m);
		sectparts($meta, $pos, $fn, $ids, $m, $big);
	}

	// sprite animation sequence
	$ed = $off;
	$st = 6;
	$buf = '';
	$m = 0;
	while ( $st < $ed )
	{
		$b1 = str2int($meta, $st, 2);
		$pos = $b1 & 0x7fff;
		$flg = $b1 >> 15;

		$buf .= sectanim($meta, $m, $pos, $flg);
		$st += 2;
		$m++;
	}
	save_file("$dir/anim.txt", $buf);
	return;
}
//////////////////////////////
function objimg( &$file, $dir )
{
	echo "=== objimg( $dir )\n";
	$hsz = str2int($file, 0, 4);

	$ed = $hsz - 4;
	$st = 0;
	while ( $st < $ed )
	{
		$off = str2int($file, $st, 4);
		loadtim( $file, $off );
		$st += 4;
	}

	$meta = substr($file, str2int($file, $hsz-4, 4));

	sectmeta($meta, $dir, array(0,1,2,3,4,5,6,7,8,9));
	//save_file("$dir/meta", $meta);
	return;
}

function infoimg( &$file, $dir )
{
	echo "=== infoimg( $dir )\n";
	// talking portraits
	$base = str2int($file, 0x20, 4);
	secttalk($file, $base, $dir);

	// load unassembled sprite images
	$base = str2int($file, 0x1c, 4);
	$ed = str2int($file, $base, 4);
	$st = 0;
	while ( $st < $ed )
	{
		$off = str2int($file, $base+$st, 4);
		loadtim($file, $base+$off);
		$st += 4;
	}

	// load sprite assembly meta
	$meta = array();
	$base = str2int($file, 0x18, 4);
	$ed = str2int($file, $base, 4) - 4;
	$st = 0;
	while ( $st < $ed )
	{
		$b1 = str2int($file, $base+$st+0, 4);
		$b2 = str2int($file, $base+$st+4, 4);
		$sz = $b2 - $b1;

		$meta[] = substr($file, $base+$b1, $sz);
		$st += 4;
	}

	// get info on matching meta to sprite image
	$ed = str2int($file, 0x18, 4);
	$st = str2int($file, 0x14, 4);
	$file = substr($file, $st, $ed-$st);

	$num = str2int($file, 6, 2);
	$b1 = count($meta);
	if ( $num != $b1 )
		printf("ERROR p14 num %d != meta[] %d\n", $num, $b1);

	$bak = array();
	for ( $i=0; $i < $num; $i++ )
	{
		$p = 8 + ($i * 4);
		$p = str2int($file, $p, 4) + 2;

		echo "= get tids\n";
		$ids = array();
		while ( $file[$p] != BYTE )
		{
			$b1 = ord( $file[$p] );
			printf("i %d , id[] %d\n", $i, $b1);
			$ids[] = $b1;
			$p++;
		}
		if ( empty($ids) )
			$ids = $bak;

		sectmeta($meta[$i], "$dir/$i", $ids);
		//save_file("$dir/$i/meta", $meta[$i]);
		$bak = $ids;
	}
	return;
}
//////////////////////////////
function mana( $fname )
{
	// for ANA/*/*.IMG only
	if ( stripos($fname, ".img") == false )
		return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);

	global $gp_pix, $gp_clut;
	$gp_pix  = array();
	$gp_clut = array();

	// 08-10 for ANA/*_*OBJ/*.IMG
	// 28    for ANA/*INFO*/*.IMG
	if ( ord($file[0]) & 0x20 )
		infoimg($file, $dir);
	else
		objimg($file, $dir);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	mana( $argv[$i] );

/*
cid 12
	esl_bs00_img
	wal_drm1_img
	end_0001_img
	end_0002_img
	jul_bs32_img
	mhm_gdn2_img
	jgl_e120_img
	rui_e050_img
	lak_bs10_img
	lak_e040_img
	wal_b010_img
	wal_drm0_img
	wal_tmpl_img
	wal_way0_img
cid 13
	mhm_gdn2_img
cid 14
	mhm_gdn2_img
tid ff
	jul_bss4.img
	min_bs00.img
	sea_bs10.img

1-1-3  186,213
m#888888   + i#606040 = r#e8e8c8 (in-game , additive blending)
m#ffffff88 + i#606040 = r#b4b4a5 (test as alpha)
 */
