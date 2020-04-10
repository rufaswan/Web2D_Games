<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D_Games. <https://github.com/rufaswan/Web2D_Games>

Web2D_Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D_Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D_Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
$gp_file = array();
$g0_list = array();
//////////////////////////////
function add_g01( $exp, $n1, $n2 )
{
	if ( ! is_numeric($n1) || ! is_numeric($n2) )
	{
		echo "# " . implode(',', $exp) . "\n";
		return;
	}
	global $g0_list;
	if ( isset( $g0_list[$n1] ) )
	{
		if ( $g0_list[$n1] != $n2 )
			printf("# CONFLICT %d -> %d [now=%d]\n", $n1, $n2, $g0_list[$n1]);
		return;
	}
	else
		$g0_list[$n1] = $n2;
	return;
}
//////////////////////////////
function g01fade( $l, $exp, $label )
{
	$func = "{$exp[3]},{$exp[4]}";
	if ( $func != $label )
		return;
	global $gp_file;
	$v1 = explode(',', $gp_file[$l-3]);
	$v2 = explode(',', $gp_file[$l-2]);
	$v3 = explode(',', $gp_file[$l-1]);
	if ( $v1[2] != '!' || $v2[2] != '!' || $v3[2] != '!' )
		return;
	add_g01($exp, $v1[4], -1);
	add_g01($exp, $v2[4], $v3[4]);
	return;
}

function g1var( $l, $exp, $label )
{
	$func = "{$exp[3]},{$exp[4]}";
	if ( $func != $label )
		return;
	global $gp_file;
	$v1 = explode(',', $gp_file[$l-2]);
	$v2 = explode(',', $gp_file[$l-1]);
	if ( $v1[2] != '!' || $v2[2] != '!' )
		return;
	add_g01($exp, $v1[4], $v2[4]);
	return;
}
function g0var( $l, $exp, $label )
{
	$func = "{$exp[3]},{$exp[4]}";
	if ( $func != $label )
		return;
	global $gp_file;
	$v1 = explode(',', $gp_file[$l-1]);
	if ( $v1[2] != '!' )
		return;
	add_g01($exp, $v1[4], -1);
	return;
}

function g1int( $l, $exp, $label )
{
	add_g01($exp, $exp[3], $exp[4]);
	return;
}
function g0int( $l, $exp, $label )
{
	add_g01($exp, $exp[3], -1);
	return;
}
//////////////////////////////
function looplines( $look, $label , $callback )
{
	echo "### look for $look $label\n";
	global $gp_file;
	$ed = count($gp_file);
	for ( $i=1; $i < $ed; $i++ )
	{
		if ( empty($gp_file[$i]) )
			continue;
		if ( $gp_file[$i][0] == '#' )
			continue;

		$exp = explode(',', $gp_file[$i]);
		if ( $exp[2] == $look )
			$callback( $i, $exp, $label );
	}
	return;
}

$gp_file = file("sco_code.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ( empty($gp_file) )  exit();

looplines( "G_0", "", "g0int" );
looplines( "G_1", "", "g1int" );

for ( $i=1; $i < $argc; $i++ )
{
	list($t,$s,$p) = explode('-', $argv[$i]);
	switch ( $t )
	{
		case '0':
			looplines( '~', "$s,$p", "g0var" );
			break;
		case '1':
			looplines( '~', "$s,$p", "g1var" );
			break;
		case '01':
			looplines( '~', "$s,$p", "g01fade" );
			break;
		default:
			exit("UNKNOWN $t\n");
	}
}

ksort($g0_list);
foreach ( $g0_list as $k => $v )
{
	if ( $v == -1 )
		echo "G0 $k\n";
	else
		echo "G1 $k $v\n";
}
