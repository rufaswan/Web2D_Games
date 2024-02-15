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

function cdxaboot( $isop, &$list )
{
	$k = isosearch($list, '__CDXA__/boot.bin');
	if ( $k === -1 )
		return;
	if ( $k['size'] > 0x8000 )
		return;

	$boot = fp2str($isop, $k['lba']*0x800, $k['size']);
	fp_update($isop, 0, $boot);
	return;
}

function cdxapatch( $isop, &$list )
{
	$k = isosearch($list, '__CDXA__/patch.txt');
	if ( $k === -1 )
		return;

	$patch = fp2str($isop, $k['lba']*0x800, $k['size']);
		$patch = str_replace("\r", "\n", $patch);
		$patch = explode("\n", $patch);

	$data = array(
		'FILE'   => '',
		'POS'    => 0,
		'OFFSET' => '-', // valid = ASM   LBA  MIN
		'SIZE'   => '-', // valid = BYTE  LBA  MIN
	);
	foreach ( $patch as $line )
	{
		$line = preg_replace('|[\s]+|', '', $line);
		if ( empty($line) )
			continue;

		if ( strpos($line, '=') )
		{
			list($k,$v) = splitline('=', $line);
			switch ( $k )
			{
				case 'FILE':
					$k = isosearch($list, $v);
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
			list($off,$siz,$nam) = splitline(',', $line);
			$k = isosearch($list, $nam);
			if ( $k === -1 )
				continue;


			if ( $off[0] !== '-' )
			{
				$off = hexdec($off) + $data['POS'];
				switch ( $data['OFFSET'] )
				{
					case 'ASM':
						$s = chrint($k['lba'], 2);
						printf("PATCH OFFSET ASM  @ %6x = %6x [%s]\n", $off, $k['lba'] & BIT16, $nam);
						fp_update($isop, $off, $s);
						break;
					case 'LBA':
						$s = chrint($k['lba'], 3);
						printf("PATCH OFFSET LBA  @ %6x = %6x [%s]\n", $off, $k['lba'], $nam);
						fp_update($isop, $off, $s);
						break;
					case 'MIN':
						$s = lba2frame($k['lba']);
						printf("PATCH OFFSET MIN  @ %6x = %s [%s]\n", $off, bin2hex($s), $nam);
						fp_update($isop, $off, $s);
						break;
				} // switch ( $data['OFFSET'] )
			}


			if ( $siz[0] !== '-' )
			{
				$siz = hexdec($siz) + $data['POS'];
				switch ( $data['SIZE'] )
				{
					case 'BYTE':
						$s = chrint($k['size'], 4);
						printf("PATCH SIZE   BYTE @ %6x = %6x [%s]\n", $siz, $k['size'], $nam);
						fp_update($isop, $siz, $s);
						break;
					case 'LBA':
						$b = int_ceil($k['size'], 0x800) >> 11;
						$s = chrint($b, 3);
						printf("PATCH SIZE   LBA  @ %6x = %6x [%s]\n", $siz, $b, $nam);
						fp_update($isop, $siz, $s);
						break;
					case 'MIN':
						$b = int_ceil($k['size'], 0x800) >> 11;
						$s = lba2frame($k['lba'] + $b);
						printf("PATCH SIZE   MIN  @ %6x = %s [%s]\n", $off, bin2hex($s), $nam);
						fp_update($isop, $siz, $s);
						break;
				} // switch ( $data['SIZE'] )
			}
		}
	} // foreach ( file($patch) as $line )
	return;
}

function isometa_update( $isop, $val, $off, $siz )
{
	while ( strlen($val) < $siz )
		$val .= ' ';
	$val = substr($val, 0, $siz);
	fp_update($isop, $off , $val);
	return;
}

function cdxameta( $isop, $list )
{
	$k = isosearch($list, '__CDXA__/meta.txt');
	if ( $k === -1 )
		return;

	$meta = fp2str($isop, $k['lba']*0x800, $k['size']);
	$off  = 0x8000;
	foreach ( explode("\n",$meta) as $mk => $mv )
	{
		list($k,$v) = splitline('=', $mv);
		printf("PATCH META %18s = %s\n", $k, $v);
		switch ( $k )
		{
			case 'system':              isometa_update($isop, $v, $off +     8, 0x20); break;
			case 'volume':              isometa_update($isop, $v, $off +  0x28, 0x20); break;
			case 'volume set':          isometa_update($isop, $v, $off +  0xbe, 0x80); break;
			case 'publisher':           isometa_update($isop, $v, $off + 0x13e, 0x80); break;
			case 'data preparer':       isometa_update($isop, $v, $off + 0x1be, 0x80); break;
			case 'application':         isometa_update($isop, $v, $off + 0x23e, 0x80); break;
			case 'copyright file':      isometa_update($isop, $v, $off + 0x2be, 0x26); break;
			case 'abstract file':       isometa_update($isop, $v, $off + 0x2e4, 0x24); break;
			case 'bibliographic file':  isometa_update($isop, $v, $off + 0x308, 0x25); break;
		} // switch ( $k )
	} // foreach ( explode("\n",$meta) as $mk => $mv )
	return;
}

function isopatch( $iso )
{
	if ( ! is_file($iso) )
		return;
	$isop = fopen($iso, 'rb+');
	if ( ! $isop )  return;

	$list = lsiso_r($isop);
	if ( empty($list) )  return;

	cdxaboot ($isop, $list);
	cdxapatch($isop, $list);
	cdxameta ($isop, $list);

	fclose($isop);
	return;
}

echo <<<_MSG
patch ISO by __CDXA__ files
  __CDXA__/boot.bin
  __CDXA__/patch.txt

_MSG;
for ( $i=1; $i < $argc; $i++ )
	isopatch( $argv[$i] );
