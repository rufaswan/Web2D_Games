<?php
require "common.inc";

function xeno( $fname, $ofpal, $ofpix )
{
	$file = file_get_contents($fname);
	if ( empty($file) )   return;

	echo "== $fname\n";
	$dir = str_replace('.', '_', $fname);

	foreach ( $ofpal as $pk => $pv )
	{
		$pal = pal555( substr($file, $pv, 0x20) );
		foreach ( $ofpix as $k => $v )
		{
			$w = str2int($file, $v+0, 2) * 2;
			$h = str2int($file, $v+2, 2);
			$pix = substr($file, $v+4, $w*$h);
			bpp4to8($pix);

			printf("%4x , %x x %x\n", $v, $w, $h);
			$img = array(
				'cc'  => 0x10,
				'w'   => $w*2,
				'h'   => $h,
				'pal' => $pal,
				'pix' => $pix,
			);
			save_clutfile("$dir/$pk-$k.clut", $img);
		} // foreach ( $ofpix as $k => $v )
	} // foreach ( $ofpal as $pk => $pv )
	return;
}

xeno('4.bin', array(0x230,0x250), array(0x27c,0x4cc)); // 1890-4 , save/party cube
//xeno('1.bin', array(0xdc), array(0x108)); // 1090-1 , char chest
//xeno('2.bin', array(0xd4,0xf4), array(0x120)); // 1608-2 , gear chest
