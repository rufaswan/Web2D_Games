<?php
require 'common.inc';

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	echo "== $fname\n";
	$dir = str_replace('.', '_', $fname);

	if ( str2int($file,0,4) !== 2 )
		return;

	$p1 = str2int($file, 4, 4) + 12;
	$p2 = str2int($file, 8, 4) + 12;

		$w1 = str2int($file, $p1+0, 2);
		$h1 = str2int($file, $p1+2, 2);
		$s1 = $w1 * 2 * $h1;

		$w2 = str2int($file, $p2+0, 2);
		$h2 = str2int($file, $p2+2, 2);
		$s2 = $w2 * 2 * $h2;

		$p1 += 4;
		$p2 += 4;

	// 4-bpp
	$pal = substr($file, $p1, $s1);
	$pix = substr($file, $p2, $s2);
		$pal = pal555($pal);
		bpp4to8($pix);

	$len = strlen($pal);
	for ( $i=0; $i < $len; $i += 0x40 )
	{
		$s = substr($pal, $i, 0x40);
		if ( trim($s,ZERO.BYTE) === '' )
			continue;

		$img = array(
			'cc'  => 0x10,
			'w'   => $w2*4,
			'h'   => $h2,
			'pal' => $s,
			'pix' => $pix,
		);
		$fn = sprintf('%s/4/%04d.clut', $dir, $i>>6);
		save_clutfile($fn, $img);
	} // for ( $i=0; $i < $len; $i += 0x40 )

	// 8-bpp
	$pal = substr($file, $p1, $s1);
	$pix = substr($file, $p2, $s2);
		$pal = pal555($pal);

	$len = strlen($pal);
	for ( $i=0; $i < $len; $i += 0x400 )
	{
		$s = substr($pal, $i, 0x400);
		if ( trim($s,ZERO.BYTE) === '' )
			continue;

		$img = array(
			'cc'  => 0x100,
			'w'   => $w2*2,
			'h'   => $h2,
			'pal' => $s,
			'pix' => $pix,
		);
		$fn = sprintf('%s/8/%04d.clut', $dir, $i>>10);
		save_clutfile($fn, $img);
	} // for ( $i=0; $i < $len; $i += 0x400 )

	return;
}

xeno('1.bin');
