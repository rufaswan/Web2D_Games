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
require "common.inc";
require "common-guest.inc";

function sect_image( $fp, $base, $pfx )
{
	// https://www.psdevwiki.com/ps4/PFS
	$sub = fp2str($fp, $base, 0x800);
	if ( str2int($sub,8,4) !== 0x01332a0b ) // 20130315
		return;

	$blksz  = str2int($sub, 0x20, 4);
	$ninode = str2int($sub, 0x30, 4);
	$ndata  = str2int($sub, 0x38, 4);
	$ninblk = str2int($sub, 0x40, 4);

	return;
}
//////////////////////////////
function sect_PSF( $fp, &$psf )
{
	// https://www.psdevwiki.com/ps4/Param.sfo
	if ( substr($psf,0,4) !== "\x00PSF" )
		return;

	$pkey = str2int($psf,  8, 4);
	$pval = str2int($psf, 12, 4);
	$pcnt = str2int($psf, 16, 4);

	$pos = 0x14;
	$buf = '';
	for ( $i=0; $i < $pcnt; $i++ )
	{
		$typ = str2int($psf, $pos+ 3, 1);
		$no1 = str2int($psf, $pos+ 4, 4);
		$no2 = str2int($psf, $pos+ 8, 4); // int_ceil($no1, 4);
		$no3 = str2int($psf, $pos+12, 4);
			$pos += 0x10;

		$key = substr0($psf, $pkey);
			$pkey += strlen($key) + 1;

		switch ( $typ )
		{
			case 2: // str
				$val = substr($psf, $pval, $no1-1);
					$pval += $no2;
				$buf .= sprintf("%s = '%s'\n", $key, $val);
				break;
			case 4: // int32
				$val = str2int($psf, $pval, 4);
					$pval += $no2;
				$buf .= sprintf("%s = 0x%x\n", $key, $val);
				break;
			default:
				php_error("UNK type %x [%x,%x,%x]", $typ, $pos, $pkey, $pval);
				break;
		} // switch ( $typ )

	} // for ( $i=0; $i < $pcnt; $i++ )

	return $buf;
}

function sect_CNT( $fp, $pfx )
{
	$head = fp2str($fp, 0, 0x800);
	if ( substr($head,0,4) !== "\x7fCNT" )
		return false;

	$tbl_cnt = str2big($head, 0x10, 4);
	$tbl_pos = str2big($head, 0x18, 4);
	$tbl_siz = $tbl_cnt * 0x20;

	$head = fp2str($fp, $tbl_pos, $tbl_siz);
	$file_pos = 0;
	$file_dat = '';
	for ( $i=0; $i < $tbl_siz; $i += 0x20 )
	{
		$typ = str2big($head, $i+0x00, 4);
		$nam = str2big($head, $i+0x04, 4);
		$pos = str2big($head, $i+0x10, 4);
		$siz = str2big($head, $i+0x14, 4);

		$flg1 = ord( $head[$i+0x08] );
		$flg2 = str2big($head, $i+0x0c, 4);

		$sub = fp2str($fp, $pos, $siz);

		if ( $typ == 0x200 )
		{
			$file_pos = $pos;
			$file_dat = $sub;
		}

		if ( $typ === 0x1000 )
			$sub = sect_PSF($fp, $sub);

		if ( $nam === 0 )
			$fn = sprintf("%04d.bin", $i/0x20);
		else
			$fn = substr0($file_dat, $nam);

		printf("%8x , %8x , %8x , %s\n", $typ, $pos, $siz, $fn);
		save_file("$pfx/$fn", $sub);
	} // for ( $i=0; $i < $tbl_cnt; $i++ )

	return true;
}
//////////////////////////////
function pkgfile( $fname )
{
	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));

	// https://www.psdevwiki.com/ps4/Package_Files
	$r = sect_CNT($fp, "$pfx/cnt");
	if ( ! $r )
		return;

	$sub = fp2str($fp, 0x400, 0x800);
	$off = str2big($sub, 0x14, 4);
	$siz = str2big($sub, 0x1c, 4);
	if ( filesize($fname) !== ($off+$siz) )
		return;

	sect_image($fp, $off, "$pfx/image");
	return;
}

for ( $i=1; $i < $argc; $i++ )
	pkgfile( $argv[$i] );
