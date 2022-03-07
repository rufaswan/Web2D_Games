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
define('CANV_S', 0x400);
//define('DRY_RUN', true);

function ralph_cex_cpdt( &$cex, &$pdt, &$cdt, $pfx, &$sect )
{
	$len6 = strlen( $cex[6] );
	for ( $i6=0; $i6 < $len6; $i6 += $sect[6] )
	{
		$id7 = str2int($cex[6], $i6+ 0, 2);
		$no7 = str2int($cex[6], $i6+10, 1);

		if ( $no7 == 0 )
			continue;

		$data = array();
		for ( $i7=0; $i7 < $no7; $i7++ )
		{
			$p7 = ($id7 + $i7) * $sect[7];
			$p7 = substr($cex[7], $p7, $sect[7]);

			//$b8 = sint8( $p7[8] );
			$data[] = $p7;
		} // for ( $i7=0; $i7 < $no7; $i7++ )

		$pix = COPYPIX_DEF(CANV_S,CANV_S);
		$pix['src']['w'] = 16;
		$pix['src']['h'] = 16;
		$pix['bgzero'] = 0;

		foreach ( $data as $v )
		{
			$dx  = str2int($v, 0, 2, true);
			$dy  = str2int($v, 2, 2, true);
			$tid = str2int($v, 4, 2);
			$cid = ord( $v[6] );
			$b7  = ord( $v[7] );
			zero_watch('v9' , $v[ 9]);
			zero_watch('v10', $v[10]);
			zero_watch('v11', $v[11]);

			$pix['hflip'] = $b7 & 1;
			$pix['vflip'] = $b7 & 2;

			$pix['dx'] = $dx + CANV_S/2;
			$pix['dy'] = $dy + CANV_S/2;
			$pix['src']['pal'] = substr($cdt, $cid*0x40, 0x40);
			$pix['src']['pix'] = substr($pdt, $tid*0x100, 0x100);

			if ( $v[8] !== ZERO )
				$pix['src']['pal'] = str_repeat("\xff\x00\x00\xff", 16);

			trace("%4d , %4d , %4x , %2x , %2x\n", $dx, $dy, $tid, $cid, $b7);
			copypix($pix);
		} // foreach ( $data as $v )

		$id6 = $i6 / $sect[6];
		$fn6 = sprintf('%s/%04d', $pfx, $id6);

		trace("== %s %x\n", $pfx, $id6);
		savepix($fn6, $pix, true);
	} // for ( $i6=0; $i6 < $len6; $i6 += $sect[6] )

	return;
}

function ralph_tbl_cex( &$cex, $pfx, &$sect )
{
	if ( str2int($cex,0,4) != 0x38 )
		return php_error('TBL-CEX not 0x38 [%s]', $pfx);

	$off = array();
	$off[] = strlen($cex);

	for ( $i=0; $i < 0x38; $i += 4 )
	{
		$b = str2int($cex, $i, 4);
		if ( $b == 0 )
			continue;
		$off[] = $b;
	} // for ( $i=0; $i < 0x38; $i += 4 )
	sort($off);

	$data = array();
	for ( $i=0; $i < 0x38; $i += 4 )
	{
		$b = str2int($cex, $i, 4);
		if ( $b == 0 )
			continue;

		$i4 = $i / 4;
		$id = array_search($b, $off);
		$sz = $off[$id+1] - $off[$id];

		$s = substr($cex, $off[$id], $sz);
		printf("%6x , %6x , %d.meta\n", $off[$id], $sz, $i4);
		$data[$i4] = $s;
	} // for ( $i=0; $i < 0x38; $i += 4 )

	foreach ( $data as $k => $v )
	{
		if ( isset( $sect[$k] ) )
		{
			$v = debug_block($v, $sect[$k]);
			save_file("$pfx/$k.txt", $v);
		}
		else
			save_file("$pfx/$k.meta", $v);
	} // foreach ( $data as $k => $v )

	$cex = $data;
	return;
}
//////////////////////////////
function load_tbltim( $fname, $pfx )
{
	$tbl = load_file($fname);
	if ( empty($tbl) )
		return '';

	$data = array();
	$b1 = str2int($tbl, 4, 4);
	$b2 = str2int($tbl, 8, 4);

	$ed = $b2 + 12;
	$st = 12;
	while ( $st < $ed )
	{
		$bak = $st;
		$st += 8; // TIM header size
		$st += str2int($tbl, $st, 4); // TIM clut size
		$st += str2int($tbl, $st, 4); // TIM pixel size

		printf("%x - %x\n", $bak, $st);
		$sub = substr($tbl, $bak, $st-$bak);
		$data[0][] = psxtim($sub);
	} // while ( $st < $ed )

	$ed = strlen($tbl);
	$st = $b2 + 12;
	while ( $st < $ed )
	{
		$cn = str2int($tbl, $st+8, 4);
			$st += 12;
		$sub = substr($tbl, $st, $cn*0x20);
			$st += ($cn * 0x20);
		$data[] = pal555($sub);
	} // while ( $st < $ed )

	// debug
	foreach ( $data[0] as $k => $v )
	{
		if ( $v['t'] == 'CLUT' )
		{
			if ( ! isset($v['pal']) || empty($v['pal']) )
				$v['pal'] = ( isset($data[2]) ) ? $data[2] : $data[1];

			$fn = sprintf('%s/%04d.clut', $pfx, $k);
			save_clutfile($fn, $v);
		}
		else
		if ( $v['t'] == 'RGBA' )
		{
			$fn = sprintf('%s/%04d.rgba', $pfx, $k);
			save_clutfile($fn, $v);
		}
	} // foreach ( $data[0] as $k => $v )

	return $data;
}

function load_pdt( $fname )
{
	$pdt = load_file($fname);
	if ( empty($pdt) )
		return '';

	bpp4to8($pdt);
	return substr($pdt, 0x100);
}

function load_tbl( $fname )
{
	$tbl = load_file($fname);
	if ( empty($tbl) )
		return '';

	$b1 = str2int($tbl, 0, 4);
	if ( $b1 == 2 || $b1 == 3 )
	{
		$b1  = str2int($tbl, 8, 4);
		$cex = substr ($tbl, $b1+0x14);
		if ( str2int($cex,0,4) == 0x38 )
			return $cex;
	}
	else
	if ( $b1 == 0x38 )
		return $tbl;
	return '';
}
