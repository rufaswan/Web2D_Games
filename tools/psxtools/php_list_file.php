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

function list_by_ext( &$list )
{
	$sum = 0;
	$data = array();
	foreach ( $list as $lv )
	{
		$sz = filesize($lv);

		$p = strrpos($lv, '/');
		$fn = substr($lv, $p+1);

		$p = strrpos($fn, '.');
		if ( $p === false )
			$ext = ' ';
		else
			$ext = substr($fn, $p+1);

		data_add($data, $ext, $sz);
		$sum += $sz;
	} // foreach ( $list as $fn )
	return array($sum,$data);
}

function list_by_fname( &$list )
{
	$sum = 0;
	$data = array();
	foreach ( $list as $lv )
	{
		$p = strrpos($lv, '/');
		$fn = substr($lv, $p+1);

		$sz = strlen($fn);
		$data[] = array($lv,$sz);
		$sum += $sz;
	} // foreach ( $list as $fn )
	return array($sum,$data);
}

function list_by_fsize( &$list )
{
	$sum  = 0;
	$data = array();
	foreach ( $list as $lv )
	{
		$sz = filesize($lv);
		$data[] = array($lv,$sz);
		$sum += $sz;
	} // foreach ( $list as $fn )
	return array($sum,$data);
}

function list_by_dirn( &$list )
{
	$sum  = 0;
	$data = array();
	foreach ( $list as $lv )
	{
		$sz = filesize($lv);

		$p = strrpos($lv, '/');
		$dn = substr($lv, 0, $p);
		data_add($data, $dn, $sz);
		$sum += $sz;
	} // foreach ( $list as $fn )
	return array($sum,$data);
}
//////////////////////////////
function data_add( &$data, $k, $v )
{
	foreach ( $data as $dk => $dv )
	{
		if ( $dv[0] === $k )
		{
			$data[$dk][1] += $v;
			return;
		}
	} // foreach ( $data as $dk => $dv )

	$data[] = array($k,$v);
	return;
}

function listfiles( &$list, $func )
{
	if ( empty($list) || empty($func) )
		return;
	if ( ! function_exists($func) )
		return;

	list($sum,$data) = $func($list);
	usort($data, function($a,$b){
		return ($a[1] > $b[1]); // ASC
	});

	foreach ( $data as $dv )
		printf("[%4.1f%%]  %8x  %s\n", $dv[1]*100/$sum, $dv[1], $dv[0]);

	$sra = 0;
	while ( $sum > (1 << $sra) )
		$sra++;
	printf("SUM  %x  (1 << %d)\n", $sum, $sra);
	return;
}
//////////////////////////////
$MSG = <<<_MSG
{$argv[0]}  [option]
option
  h  : show this help message
  e  : list files by extension [default]
  fs : list files by file size
  fn : list files by file name length
  dn : list dirs  by dir size

_MSG;

$list = array();
lsfile_r('.', $list);

$func = 'list_by_ext';
for ( $i=1; $i < $argc; $i++ )
{
	switch ( $argv[$i] )
	{
		case 'h':  exit($MSG);
		case 'e' :  $func = 'list_by_ext';   break;
		case 'fs':  $func = 'list_by_fsize'; break;
		case 'fn':  $func = 'list_by_fname'; break;
		case 'dn':  $func = 'list_by_dirn' ; break;
	} // switch ( $argv[$i] )
} // for ( $i=1; $i < $argc; $i++ )

listfiles($list, $func);
echo "type h for help\n";
