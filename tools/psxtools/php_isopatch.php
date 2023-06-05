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
require 'common-iso.inc';

function findlist( &$list, $key, $val )
{
	foreach ( $list as $v )
	{
		if ( $v[$key] == $val )
			return $v;
	}
	return '';
}

function isopatch( $iso, $patch )
{
	if ( ! is_file($iso) || ! is_file($patch) )
		return;
	$isop = fopen($iso, 'rb+');
	if ( ! $isop )  exit();

	$list = lsiso_r($isop);
	if ( empty($list) )  exit();

	$data = array(
		'FILE' => '',
		'TYPE' => '',
		'OFF'  => 0,
	);
	foreach ( file($patch) as $line )
	{
		$line = preg_replace('|[\s]+|', '', $line);
		if ( empty($line) )
			continue;

		if ( strpos($line, '=') )
		{
			list($k,$v) = explode('=', $line);
			switch ( $k )
			{
				case 'FILE':
					$k = findlist($list, 'file', $v);
					if ( empty($k) )
						php_error('FILE = %s not found', $v);
					$data['FILE'] = $k;
					$data['OFF' ] = $k['lba'] * 0x800;
					printf("SET FILE %s @ %x\n", $v, $data['OFF']);
					break;
				case 'TYPE':
					$data['TYPE'] = $v;
					printf("SET TYPE %s\n", $v);
					break;
			} // switch ( $k )
			continue;
		}

		if ( strpos($line, ',') )
		{
			list($off,$siz,$nam) = explode(',', $line);
			$k = findlist($list, 'file', $nam);
			if ( empty($k) )
				continue;

			if ( $off[0] !== '-' )
			{
				$off = hexdec($off) + $data['OFF'];
				$s = '';
				switch ( $data['TYPE'] )
				{
					case 'INT':
						$s = chrint($k['lba'], 3);
						printf("PATCH INT lba  @ %x = %x\n", $off, $k['lba']);
						break;
				} // switch ( $data['TYPE'] )

				fseek ($isop, $off, SEEK_SET);
				fwrite($isop, $s);
			}

			if ( $siz[0] !== '-' )
			{
				$siz = hexdec($siz) + $data['OFF'];
				$s = '';
				switch ( $data['TYPE'] )
				{
					case 'INT':
						$s = chrint($k['size'], 4);
						printf("PATCH INT size @ %x = %x\n", $siz, $k['size']);
						break;
				} // switch ( $data['TYPE'] )

				fseek ($isop, $siz, SEEK_SET);
				fwrite($isop, $s);
			}
		}
	} // foreach ( file($patch) as $line )

	fclose($isop);
	return;
}

printf("%s  ISOFILE  PATCHFILE\n", $argv[0]);
if ( $argc !== 3 )  exit();

isopatch( $argv[1], $argv[2] );
