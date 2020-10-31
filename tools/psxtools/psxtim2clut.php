<?php
require "common.inc";

function psxtimfile( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	// can also be 00
	//$mgc = str2int($file, 0, 4);
	//if ( $mgc != 0x10 )  return;

	$tim = psxtim($file);
	if ( empty( $tim['pix'] ) )
		return;

	$dir = str_replace('.', '_', $fname);

	if ( $tim['t'] == 'RGBA' )
	{
		$data = "RGBA";
		$data .= chrint($tim['w'], 4);
		$data .= chrint($tim['h'], 4);
		$data .= $tim['pix'];
		save_file("$dir.rgba", $data);
		return;
	}

	if ( $tim['t'] == 'CLUT' )
	{
		$pal = array();
		if ( isset( $tim['clut'] ) )
			$pal = $tim['clut'];
		else
			$pal[] = grayclut( $tim['cc'] );

		$data = "CLUT";
		$data .= chrint($tim['cc'], 4);
		$data .= chrint($tim['w'], 4);
		$data .= chrint($tim['h'], 4);

		$cnt = count($pal);
		foreach ( $pal as $ck => $cv )
		{
			// skip black/white only background
			if ( trim($cv, ZERO.BYTE) == "" )
				continue;

			$clut = $data;
			$clut .= $cv;
			$clut .= $tim['pix'];

			if ( $cnt == 1 )
				save_file("$dir.clut", $clut);
			else
				save_file("$dir/$ck.clut", $clut);
		} // foreach ( $pal as $ck => $cv )

		return;
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	psxtimfile( $argv[$i] );

/*
 * Weird TIM files
 * - Legend of Mana /wm/wmap/wm1.tim
 *   - TIM 2 = 8-bpp CLUT gray
 *
*/
