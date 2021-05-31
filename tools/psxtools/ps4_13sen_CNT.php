<?php
/*
[license]
[/license]
 * Special Thanks
 *   pkg_parser lib
 *     n1ghty
 */
require "common.inc";
require "common-guest.inc";

function sect_psf( $fp, &$psf )
{
	// param.sfo
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

function pkgfile( $fname )
{
	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return;

	$head = fp2str($fp, 0, 0x800);
	if ( substr($head,0,4) !== "\x7fCNT" )
		return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$tbl_cnt = str2big($head, 0x10, 4);
	$tbl_pos = str2big($head, 0x18, 4);
	$tbl_siz = $tbl_cnt * 0x20;

	$head = fp2str($fp, $tbl_pos, $tbl_siz);
	$file_pos = 0;
	$file_dat = '';
	for ( $i=0; $i < $tbl_siz; $i += 0x20 )
	{
		$typ = str2big($head, $i+0, 4);
		$nam = str2big($head, $i+4, 4);
		$pos = str2big($head, $i+0x10, 4);
		$siz = str2big($head, $i+0x14, 4);
		printf("%4x , %8x , %8x , %8x\n", $i, $typ, $pos, $siz);

		$sub = fp2str($fp, $pos, $siz);

		if ( $typ == 0x200 )
		{
			$file_pos = $pos;
			$file_dat = $sub;
		}

		if ( $typ === 0x1000 )
			$sub = sect_psf($fp, $sub);

		if ( $nam === 0 )
			$fn = sprintf("%04d.bin", $i/0x20);
		else
			$fn = substr0($file_dat, $nam);

		save_file("$pfx/$fn", $sub);
	} // for ( $i=0; $i < $tbl_cnt; $i++ )

	return;
}

for ( $i=1; $i < $argc; $i++ )
	pkgfile( $argv[$i] );
