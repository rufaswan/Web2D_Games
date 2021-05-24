<?php
/*
[license]
[/license]
 */
require "common.inc";

$gp_pix = array();

function sect_BIT( &$sub )
{
	if ( substr($sub,0,3) !== 'BIT' )
		return php_error('not BIT');
	if ( substr($sub,8,8) !== 'TEXTURES' )
		return php_error('BIT no TEXTURES');

	$cnt = str2int($sub, 4, 4);

	global $gp_pix;
	$gp_pix = array();

	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = 0x10 + ($i * 0x30);
		if ( substr($sub,$p+8,8) !== 'PALETTES' )
			return php_error('BIT[%x] no PALETTES', $i);

		$gp_pix[$i]['cc']  = 0x10;
		$gp_pix[$i]['w']   = str2int($sub, $p+0, 2);
		$gp_pix[$i]['h']   = str2int($sub, $p+2, 2);
		$gp_pix[$i]['pal'] = pal555( substr($sub, $p+16, 32) );
	}
	return;
}
//////////////////////////////
function kuma( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) != "FTEX" )
		return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$pos = str2int($file,  8, 4);
	$cnt = str2int($file, 12, 2);

	global $gp_pix;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$sub = substr ($file, $pos+0, 4);
		$siz = str2int($file, $pos+4, 4);
		$hdz = str2int($file, $pos+8, 4);
		if ( $sub != 'FTX0' )
			return php_error('UNKNOWN %s @ %x', $sub, $pos);
		printf("%6x , %6x , %6x , %s\n", $pos, $siz, $hdz, $sub);

		$sub = substr($file, $pos+$hdz, $siz);
		if ( $i === 0 )
			sect_BIT($sub);
		else
		{
			$id = $i - 1;
			bpp4to8($sub);
			$gp_pix[$id]['pix'] = $sub;

			$fn = sprintf("%s.%d.tpl", $pfx, $id);
			save_clutfile($fn, $gp_pix[$id]);
		}

		$pos += ($siz + $hdz);

	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	kuma( $argv[$i] );
