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
 *
 * Special Thanks
 *   DSVania Editor
 *   https://github.com/LagoLunatic/DSVEdit/blob/master/docs/formats/Skeleton%20File%20Format.txt
 *     LagoLunatic
 */
require 'common.inc';

function str2hex( &$str )
{
	$len = strlen($str);
	$hex = '';
	for ( $i=0; $i < $len; $i++ )
	{
		if ( $str[$i] === ZERO )
			$hex .= '-- ';
		else
			$hex .= sprintf('%02x ', ord($str[$i]));
	} // for ( $i=0; $i < $len; $i++ )
	return $hex;
}

function shex( $int )
{
	if ( $int < 0 )
		$s = sprintf('-%x', -$int);
	else
		$s = sprintf('%x' ,  $int);
	return $s;
}
//////////////////////////////
function sect_joint( &$file, $pos, &$inv, $cjnt, $cjnt_inv, $cjnt_vis )
{
	printf("Joints = %x [inv] + %x [vis] = %x\n", $cjnt_inv, $cjnt_vis, $cjnt);
	for ( $i=0; $i < $cjnt; $i++ )
	{
		$s = substr($file, $pos, 4);
			$pos += 4;

		$id = sint8( $s[1] );
		$inv[$i] = shex($id);
		printf("  %2x  %s [%3s]\n", $i, str2hex($s), $inv[$i]);
	} // for ( $i=0; $i < $cjnt; $i++ )
	echo "\n";
	return;
}

function sect_pose( &$file, $pos, &$inv, $cpss, $cjnt )
{
	printf("Poses = %x\n", $cpss);
	for ( $i=0; $i < $cpss; $i++ )
	{
		$s = substr($file, $pos, 2);
			$pos += 2;
		printf("  %02x  %s\n", $i, str2hex($s));

		for ( $j=0; $j < $cjnt; $j++ )
		{
			$s = substr($file, $pos, 4);
				$pos += 4;
			//printf("    %02x-%02x  %s [%s]\n", $i, $j, str2hex($s), $inv[$j]);

			$ang = str2int($s, 0, 2, true) / 0x10000;
			$dis = str2int($s, 2, 1, true);
			$rep = str2int($s, 3, 1, true);

			$deg = $ang * 360;
			printf("    %02x-%02x  %6.1f  %4d  %4d  [%3s]\n", $i, $j, $deg, $dis, $rep, $inv[$j]);
		} // for ( $j=0; $j < $cjnt; $j++ )
	} // for ( $i=0; $i < $cpss; $i++ )
	echo "\n";
	return;
}

function sect_hitbox( &$file, $pos, $chit )
{
	printf("Hitboxes = %x\n", $chit);
	for ( $i=0; $i < $chit; $i++ )
	{
		$s = substr($file, $pos, 8);
			$pos += 8;
		printf("  %02x  %s\n", $i, str2hex($s));
	} // for ( $i=0; $i < $chit; $i++ )
	echo "\n";
	return;
}

function sect_point( &$file, $pos, $cpnt )
{
	printf("Points = %x\n", $cpnt);
	for ( $i=0; $i < $cpnt; $i++ )
	{
		$s = substr($file, $pos, 4);
			$pos += 4;
		printf("  %02x  %s\n", $i, str2hex($s));
	} // for ( $i=0; $i < $chit; $i++ )
	echo "\n";
	return;
}

function sect_draw( &$file, $pos, $cjnt_vis )
{
	$s = substr($file, $pos, $cjnt_vis);
		$pos += $cjnt_vis;
	printf("Draw = %s\n", str2hex($s));
	echo "\n";
	return;
}

function sect_anim( &$file, $pos, $canm )
{
	printf("Anims = %x\n", $canm);
	for ( $i=0; $i < $canm; $i++ )
	{
		$cnt = ord( $file[$pos] );
			$pos++;
		printf("  %02x  %02x\n", $i, $cnt);

		for ( $j=0; $j < $cnt; $j++ )
		{
			$s = substr($file, $pos, 3);
				$pos += 3;
			printf("    %02x-%02x  %s\n", $i, $j, str2hex($s));
		} // for ( $j=0; $j < $cnt; $j++ )
	} // for ( $i=0; $i < $chit; $i++ )
	echo "\n";
	return;
}
//////////////////////////////
function parse_jntfile( &$file )
{
	$dx = str2int($file, 0x22, 2, true);
	$dy = str2int($file, 0x24, 2, true);
	printf("XY = %s , %s\n", shex($dx), shex($dy));
	echo "\n";

	$cjnt = ord( $file[0x26] );
	$cjnt_inv = ord( $file[0x27] );
	$cjnt_vis = ord( $file[0x28] );
	$chit = ord( $file[0x29] );
	$cpss = ord( $file[0x2a] );
	$cpnt = ord( $file[0x2b] );
	$canm = ord( $file[0x2c] );

	$inv = array();
	$pos = 0x30;

	sect_joint($file, $pos, $inv, $cjnt, $cjnt_inv, $cjnt_vis);
	$pos += ($cjnt * 4);

	sect_pose($file, $pos, $inv, $cpss, $cjnt);
	$pos += ($cpss * (2 + ($cjnt * 4)));

	sect_hitbox($file, $pos, $chit);
	$pos += ($chit * 8);

	sect_point($file, $pos, $cpnt);
	$pos += ($cpnt * 4);

	sect_draw($file, $pos, $cjnt_vis);
	$pos += $cjnt_vis;

	sect_anim($file, $pos, $canm);
	return;
}

function jntfile( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 2) !== "\x01\x23" )
		return;

	$opd = substr0($file, 3);
	if ( strpos($opd, '.opd') === false )
		return;

	ob_start();
	parse_jntfile($file);
	$txt = ob_get_clean();

	save_file("$opd-$fname.txt", $txt);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	jntfile( $argv[$i] );

/*
joint 3
	00 01 02 03 04 05 06 07

	00 *all*
	01
		j_alessi00.jnt  j_alessibak.jnt
		j_armag2.jnt
		j_bigskl00.jnt  j_bigskl01.jnt  j_bigskl02.jnt
		j_cent00.jnt
		j_dhum00.jnt
		j_dino00.jnt
		j_fk.jnt
		j_fran00.jnt  j_fran01.jnt  j_fran03.jnt
		j_geva0.jnt  j_geva_orz.jnt
		j_gk.jnt  j_gk_b.jnt
		j_golem0.jnt
		j_guru00.jnt
		j_hums00.jnt
		j_kani00.jnt
		j_red00.jnt
		j_run.jnt
		j_wpnm00.jnt
	02
		j_bigskl00.jnt  j_bigskl01.jnt  j_bigskl02.jnt
		j_dino00.jnt
		j_dra00.jnt
		j_fk.jnt
		j_fran02.jnt
		j_geva0.jnt  j_geva_orz.jnt
		j_gk.jnt  j_gk_b.jnt
		j_golem0.jnt
		j_grav00.jnt
	03
		j_armag2.jnt
		j_bigskl00.jnt  j_bigskl01.jnt  j_bigskl02.jnt
		j_cent00.jnt
		j_dem.jnt  j_frdm.jnt  j_lddm.jnt  j_sedm.jnt  j_thdm.jnt
		j_dhum00.jnt
		j_grav00.jnt
		j_guru00.jnt
		j_hums00.jnt
		j_kani00.jnt
		j_red00.jnt
		j_wpnm00.jnt
	04 *all*
	05
		j_bigskl00.jnt  j_bigskl01.jnt  j_bigskl02.jnt
		j_cent00.jnt
		j_fk.jnt
		j_fran02.jnt
		j_gk.jnt  j_gk_b.jnt
	06
		j_bigskl00.jnt  j_bigskl01.jnt  j_bigskl02.jnt
		j_cent00.jnt
		j_fran00.jnt  j_fran01.jnt  j_fran02.jnt  j_fran03.jnt
		j_run.jnt
	07
		j_fran00.jnt  j_fran01.jnt  j_fran02.jnt  j_fran03.jnt
		j_geva0.jnt  j_geva_orz.jnt
		j_golem0.jnt
		j_kani00.jnt
		j_run.jnt
 */
