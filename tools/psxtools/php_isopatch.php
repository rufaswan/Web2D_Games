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
		if ( $v[$key] === $val )
			return $v;
	}
	return -1;
}

function isopatch( $iso, $patch )
{
	if ( ! is_file($iso) || ! is_file($patch) )
		return;
	$isop = fopen($iso, 'rb+');
	if ( ! $isop )  return;

	$list = lsiso_r($isop);
	if ( empty($list) )  return;

	$data = array(
		'FILE'   => '',
		'POS'    => 0,
		'OFFSET' => '-', // valid = ASM   LBA  MIN
		'SIZE'   => '-', // valid = BYTE  LBA  MIN
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
					if ( $k === -1 )
						return php_error('FILE = %s not found', $v);

					$data['FILE'] = $k['file'];
					$data['POS' ] = $k['lba'] * 0x800;
					printf("SET FILE %s @ %x\n", $v, $data['POS']);
					break;
				case 'OFFSET':
					$data['OFFSET'] = $v;
					printf("SET OFFSET type = %s\n", $v);
					break;
				case 'SIZE':
					$data['SIZE'] = $v;
					printf("SET SIZE type = %s\n", $v);
					break;
			} // switch ( $k )
			continue;
		}


		if ( strpos($line, ',') )
		{
			list($off,$siz,$nam) = explode(',', $line);
			$k = findlist($list, 'file', $nam);
			if ( $k === -1 )
				continue;


			$off = hexdec($off) + $data['POS'];
			switch ( $data['OFFSET'] )
			{
				case 'ASM':
					$s = chrint($k['lba'], 2);
					printf("PATCH OFFSET ASM @ %x = %x\n", $off, $k['lba'] & BIT16);
					fp_update($isop, $off, $s);
					break;
				case 'LBA':
					$s = chrint($k['lba'], 3);
					printf("PATCH OFFSET LBA @ %x = %x\n", $off, $k['lba']);
					fp_update($isop, $off, $s);
					break;
				case 'MIN':
					$s = lba2frame($k['lba']);
					printf("PATCH OFFSET MIN @ %x = %s\n", $off, bin2hex($s));
					fp_update($isop, $off, $s);
					break;
			} // switch ( $data['OFFSET'] )


			$siz = hexdec($siz) + $data['POS'];
			switch ( $data['SIZE'] )
			{
				case 'BYTE':
					$s = chrint($k['size'], 4);
					printf("PATCH SIZE BYTE @ %x = %x\n", $siz, $k['size']);
					fp_update($isop, $siz, $s);
					break;
				case 'LBA':
					$b = int_ceil($k['size'], 0x800) >> 11;
					$s = chrint($b, 3);
					printf("PATCH SIZE LBA @ %x = %x\n", $siz, $b);
					fp_update($isop, $siz, $s);
					break;
				case 'MIN':
					$b = int_ceil($k['size'], 0x800) >> 11;
					$s = lba2frame($k['lba'] + $b);
					printf("PATCH SIZE MIN @ %x = %s\n", $off, bin2hex($s));
					fp_update($isop, $siz, $s);
					break;
			} // switch ( $data['SIZE'] )
		}
	} // foreach ( file($patch) as $line )

	fclose($isop);
	return;
}

printf("%s  ISOFILE  PATCHFILE\n", $argv[0]);
if ( $argc !== 3 )  exit();

isopatch( $argv[1], $argv[2] );
