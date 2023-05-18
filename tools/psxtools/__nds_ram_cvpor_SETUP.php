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
require 'common-guest.inc';
require 'nds.inc';

function info_filelist( &$nds, &$list )
{
	echo "== FILELIST\n";
	$len = strlen($list[3]);
	for ( $i=0; $i < $len; $i += $list[2] )
	{
		$b1 = str2int($list[3], $i + 0, 4); // fp ram pos
		$b2 = substr0($list[3], $i + 6); // fname
		$id = $i / $list[2];
		printf("%4x  %8x  %s\n", $id, $b1, $b2);
	}
	echo "\n";

	$main = 'arm9/main.bin';
	$arm = $nds->list[$main];

	$p1 = $list[0] - $arm['ram'];
	$p2 = $list[1] - $arm['ram'];
	foreach ( $nds->list as $lk => $lv )
	{
		if ( $lv['ram'] < 0 )
			continue;

		echo "== $lk\n";
		$dat = $nds->loadfile($lk);
		for ( $i=0; $i < $lv['siz']; $i += 4 )
		{
			if ( $dat[$i+3] !== "\x02" )
				continue;
			if ( $lk === $main && $i >= $p1 && $i < $p2 )
				continue;

			$ram = substr($dat, $i, 4);
			$pos = strpos($list[3], $ram);
			if ( $pos === false )
				continue;

			$fid = $pos / $list[2];
			$fst = substr0($list[3], $pos + 6);
			printf("%s + %6x [%8x] = %4x %s\n", $lk, $i, $lv['ram']+$i, $fid, $fst);
		} // for ( $i=0; $i < $lv['siz']; $i += 4 )
		echo "\n";

	} // foreach ( $nds->list as $lk => $lv )
	return;
}

function info_monster( &$ram, $mon_ovid, $mon_sc, &$list )
{
	echo "== MONSTER\n";
	$m_ov = array();
	for ( $pos = $mon_ovid[0]; $pos < $mon_ovid[1]; $pos += 8 )
	{
		$b1 = $pos & BIT24;
		$m  = str2int($ram, $b1 + 0, 2);
		$ov = str2int($ram, $b1 + 4, 2);
		$m_ov[$m] = $ov;
	} // for ( $pos = $mon_ovid[0]; $pos < $mon_ovid[1]; $pos += 8 )

	$type = array(
		1 => 'sc',
		2 => 'so',
		3 => 'pal',
	);
	$id = 0;
	for ( $pos = $mon_sc[0]; $pos < $mon_sc[1]; $pos += 4 )
	{
		printf('mon_%04d_%x  ', $id, $id);
		if ( isset( $m_ov[$id] ) )
			printf('over  %x  ', $m_ov[$id]);

		$id++;
		$off = str2int($ram, $pos & BIT24, 3);

		while (1)
		{
			$b1 = str2int($ram, $off + 0, 4);
			$b2 = str2int($ram, $off + 4, 4);
				$off += 8;
			if ( $b1 === BIT32 )
				break;
			if ( $b2 === 3 ) // pal
				printf('pal  %x  ', $b1);
			else
			{
				$b1 *= $list[2];
				$fn = substr0($list[3], $b1 + 6);
				printf('%s  %s  ', $type[$b2], $fn);
			}
		} // while (1)
		echo "\n";
	} // for ( $pos = $mon_sc[0]; $pos < $mon_sc[1]; $pos += 4 )
	return;
}

function info_stage_area( &$ram, $stg_ovid, $stg_bc, $stg_data, &$list, $mapid )
{
	echo "== MAP\n";
	$areaid = 0;
	while (1)
	{
		$ovid = str2int($ram, $stg_ovid & BIT24, 2);
		$bc   = str2int($ram, $stg_bc   & BIT24, 3);
		$data = str2int($ram, $stg_data & BIT24, 3);
		if ( $ovid === BIT16 || $bc === 0 || $data === 0 )
			break;


	} // while (1)
	echo "\n";
	return;
}

function info_stage( &$ram, $stg_ovid, $stg_bc, $stg_data, &$list, $ntr )
{
	// cvdos
	//   209a41c[0]-e
	//   209a3d4[0]-208b360[0]-fa,2
	//   208ad44[0]-207652c[0]-209ee00[0]-data
	// cvpor
	//   20b3c80[0]-20b3c44[0]-4e
	//   20d424c[0]-20d4288[0]-20bfcbc[0]-f4,2
	//   20d3b78[0]-20d8298[0]-20b48ac[0]-20d937c[0]-data
	// cvooe
	//   20b60fc[0]-20b60c4[0]-40
	//   20d8da0[0]-20d8d68[0]-20ef5fc[0]-1ca,2
	//   20d8fc4[0]-over[12]-2221ee0[0]-221f664[0]-over[15]-22ab630[0]-data
	return;
	if ( stripos($ntr, '_castlevania1') !== false )
		return info_stage_area($ram, $stg_ovid[0], $stg_bc[0], $stg_data[0], $list, 0);

	$mapid = 0;
	$ovid = $stg_ovid[0];
	$bc   = $stg_bc  [0];
	$data = $stg_data[0];
	while (1)
	{
		$m_ovid = str2int($ram, $ovid & BIT24, 4);
		$m_bc   = str2int($ram, $bc   & BIT24, 4);
		$m_data = str2int($ram, $data & BIT24, 4);
		if ( $m_ovid === 0 || $m_bc === 0 || $m_data === 0 )
			break;
		info_stage_area($ram, $m_ovid, $m_bc, $m_data, $list, $mapid);
		$ovid += 4;
		$bc   += 4;
		$data += 4;
		$mapid++;
	} // while (1)
	return;
}
//////////////////////////////
function exportfile( &$nds, $load, $save )
{
	foreach ( $nds->list as $lk => $lv )
	{
		if ( stripos($lk, $load) !== false )
		{
			$sub = $nds->loadfile($lk);
			save_file($save, $sub);
			return;
		}
	}
	return;
}

function cvexport( &$ram, &$nds, &$cmd, $dir )
{
	$cnt = count($cmd);
	$prev = '';
	$num = array(
		'so'  => 0,
		'sc'  => 0,
		'pal' => 0,
		'jnt' => 0,
		'nsbmd' => 0,
		'nsbtx' => 0,
	);

	foreach ( $cmd as $cval )
	{
		if ( $cval === 'over' || $cval === 'sc' || $cval === 'so' || $cval === 'pal' )
		{
			$prev = $cval;
			continue;
		}

		switch ( $prev )
		{
			case 'over':
				$nds->ndsram_over($ram, $cval);
				break;

			case 'sc':
				$fn = sprintf('%s/sc.%03d', $dir, $num['sc']);
					$num['sc']++;
				exportfile($nds, $cval, $fn);
				break;

			case 'so':
				if ( stripos($cval, '.nsbmd') !== false )
				{
					$fn = sprintf('%s/nsbmd.%03d', $dir, $num['nsbmd']);
						$num['nsbmd']++;
					exportfile($nds, $cval, $fn);
					break;
				}

				if ( stripos($cval, '/so/') !== false )
					$sotype = 'so';
				else
				if ( stripos($cval, '/jnt/') !== false )
					$sotype = 'jnt';
				else
				if ( stripos($cval, '.nsbtx') !== false )
					$sotype = 'nsbtx';
				else
					return php_error('unknown  key %s  val %s', $prev, $cval);

				if ( $num[$sotype] !== 0 )
					return php_error('unknown  %s > 1  val %s', $sotype, $cval);
				$fn = sprintf('%s/%s.0', $dir, $sotype);
					$num[$sotype]++;
				exportfile($nds, $cval, $fn);
				break;

			case 'pal':
				if ( $num['pal'] !== 0 )
					return php_error('unknown  pal > 1  val %x', $cval);

				$cval &= BIT24;
				$cc = str2int($ram, $cval + 2, 2);
					$cval += 4;
				$pal = substr($ram, $cval, $cc * 0x20);

				$fn = sprintf('%s/pal.0', $dir);
					$num['pal']++;
				save_file($fn, $pal);
				break;

			default:
				return php_error('unknown  key %s  val %s', $prev, $cval);
		} // switch ( $prev )
	} // foreach ( $cmd as $cv )

	return;
}
//////////////////////////////
function setup_monster( &$ram, &$nds, $monster, $dir, $game )
{
	foreach ( $monster as $mk => $mv )
	{
		cvexport($ram, $nds, $mv, "$dir/$mk");
		save_file("$dir/$mk/game.0", $game);
	} // foreach ( $monster as $mk => $mv )
	return;
}
//////////////////////////////
function cvpor( $fname )
{
	$nds = new NDSList;
	$ntr = $nds->load($fname);
	if ( $ntr === -1 )
		return;

	if ( count($nds->list) < 2 ) // head.bin
		return;

	$patch = load_patchfile($ntr);
	if ( empty($patch) )
		return;
	$game = implode(' ', $patch['info']['game']);

	$ndsram = $nds->ndsram_new(NDS_RAM);
	cvexport($ndsram, $nds, $patch['info']['init'], "$ntr/setup");

	$filelist = $patch['info']['filelist'];
	$filelist[3] = substr($ndsram, $filelist[0] & BIT24, $filelist[1]-$filelist[0]);
	//info_filelist($nds, $filelist);

	info_monster($ndsram, $patch['info']['mon_ovid'], $patch['info']['mon_sc'], $filelist);
	if ( isset( $patch['monster'] ) )
		setup_monster($ndsram, $nds, $patch['monster'], "$ntr/setup/monster", $game);

	info_stage($ndsram, $patch['info']['stg_ovid'], $patch['info']['stg_bc'], $patch['info']['stg_data'], $filelist, $ntr);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvpor( $argv[$i] );

/*
cvooe , monster[0].func1 , over 12
	221122c  e59f115c  ldr  r1, =21dcc10
		[221122c + 8 + 15c] = [2211390]
	2211230  e59f215c  ldr  r2, =20b8aa4
		[2211230 + 8 + 15c] = [2211394]
	2211234  e5911000  ldr  r1, [r1]
	2211238  e59f3158  ldr  r3, =20ca080
		[2211238 + 8 + 158] = [2211398]
	221123c  e1a04000  mov  r4, r0
	2211240  ebf8dc44  bl   =2048358
		f8dc44 = -723bc
		2211240 + 8 - (723bc * 4)
		= 2211248 - 1c8ef0
		= 2048358
	...
	2211390  =21dcc10  =ram
	2211394  =20b8aa4  =fptr , =/sc/f_armc02.dat
	2211398  =20ca080  =palette

	r0-r7  unbanked register
	r8-rc  banked register
	rd  stack pointer
	re  link register
	rf  program counter
 */
