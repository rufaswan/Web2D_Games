<?php
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-clutfile');

// 3080  [PAK]
function xeno( string $fname ) : void
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$cnt = tool::ordstr($file, 0, 1);
	if ( $cnt !== 0xb )
		return;

	$pal8 = '';
	$pal4 = '';

	$pos = tool::ordstr($file,  4, 3);
	if ( tool::ordstr($file, $pos +  0, 2) !== 0x1101  )
		return;
	if ( tool::ordstr($file, $pos + 12, 3) !== 0x20100 )
		return;
	$sub = tool::substr($file, $pos + 16, 0x400);
	$pal8 .= psx::pal555($sub);

	$pos = tool::ordstr($file,  8, 3);
	if ( tool::ordstr($file, $pos +  0, 2) !== 0x1101  )
		return;
	if ( tool::ordstr($file, $pos + 12, 3) !== 0x10100 )
		return;
	$sub = tool::substr($file, $pos + 16, 0x200);
	$pal4 .= psx::pal555($sub);

	$pos = tool::ordstr($file, 12, 3);
	if ( tool::ordstr($file, $pos +  0, 2) !== 0x1101  )
		return;
	if ( tool::ordstr($file, $pos + 12, 3) !== 0x10040 )
		return;
	$sub = tool::substr($file, $pos + 16, 0x80);
	$pal4 .= psx::pal555($sub);

	$len8 = strlen($pal8);
	$len4 = strlen($pal4);
	$cnt -= 3;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$pos = tool::ordstr($file, 16+$i*4, 3);

		if ( tool::ordstr($file,$pos,2) !== 0x1100 )
			return;
		//$x = tool::ordstr($file, $pos +  4, 2);
		//$y = tool::ordstr($file, $pos +  6, 2);
		$w = tool::ordstr($file, $pos + 12, 2);
		$h = tool::ordstr($file, $pos + 14, 2);

		$pix = tool::substr($file, $pos + 16, $w*$h);
		$img = new clutdata;
		$img->w = $w << 1;
		$img->h = $h;
		$img->pix = $pix;

		$id = 0;
		for ( $j=0; $j < $len8; $j += 0x400 )
		{
			$img->pal = tool::substr($pal8, $j, 0x400);
			$fn = sprintf('%s/%02d-8-%d.clut', $dir, $i, $id);
			clutfile::save($fn, $img);
			$id++;
		}

		psx::bpp4to8($pix);
		$img = new clutdata;
		$img->w = $w << 2;
		$img->h = $h;
		$img->pix = $pix;

		$id = 0;
		for ( $j=0; $j < $len4; $j += 0x40 )
		{
			$img->pal = tool::substr($pal4, $j, 0x40);
			$fn = sprintf('%s/%02d-4-%d.clut', $dir, $i, $id);
			clutfile::save($fn, $img);
			$id++;
		}
	} // for ( $i=0; $i < $cnt; $i++ )

/*
	$len = strlen($file);

	for ( $i=0; $i < $len; $i += 0x1000 )
	{
		$pal = tool::substr($file, $i+0,     0x100);
		$pix = tool::substr($file, $i+0x100, 60*64);

		$img = [
			'cc'  => 0x80,
			'w'   => 60,
			'h'   => 64,
			'pal' => psx::pal555($pal),
			'pix' => $pix,
		];

		$fn = sprintf('%s/%04d.clut', $dir, $i/0x1000);
		clutfile::save($fn, $img);
	} // for ( $i=0; $i < $len; $i += 0x1000 )
*/

	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
