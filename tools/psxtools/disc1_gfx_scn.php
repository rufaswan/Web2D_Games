<?php
/*
[license]
[/license]
 */
require "common.inc";

$gp_pix  = array();

function sectscn( &$sub, $typ, $fn )
{
	$len = strlen($sub);

	global $gp_pix;
	switch ( $typ )
	{
		case 1: // subtitle script
			str_update($sub, 0, '01234567');
			$pos = 8;
			while ( $pos < $len )
			{
				$b = ord( $sub[$pos] );
				$sub[$pos] = "\n";
				$pos += ($b + 1);
			}

			save_file("$fn.txt", $sub);
			return;

		case 4: // pixel
			$gp_pix['w'] = 0x140;
			$gp_pix['h'] = 0xc8;
			$gp_pix['pix'] = '';
			$pos = 8;
			for ( $y=0; $y < $gp_pix['h']; $y += 4 )
			{
				$row = array('','','','');
				for ( $x=0; $x < $gp_pix['w']; $x += 4 )
				{
					$row[0] .= substr($sub, $pos+ 0, 4);
					$row[1] .= substr($sub, $pos+ 4, 4);
					$row[2] .= substr($sub, $pos+ 8, 4);
					$row[3] .= substr($sub, $pos+12, 4);
						$pos += 16;
				} // for ( $x=0; $x < $gp_pix['w']; $x += 4 )

				$gp_pix['pix'] .= implode('', $row);
			} // for ( $y=0; $y < $gp_pix['h']; $y += 4 )

			save_clutfile("$fn.clut", $gp_pix);
/*
   title_scn = f9c0 = 140*c8 8-bpp
psygnosi_scn = 55b8 =  9a*bb 4-bpp
*/
			break;
		case 5: // palette
			if ( $len > 0x408 ) // 8 + 256*4
				php_warning("%s palette over 256 colors [%x]", $fn, $len);

			$gp_pix['pal'] = '';
			$gp_pix['cc']  = 0;
			for ( $i=8; $i < $len; $i += 4 )
			{
				$gp_pix['pal'] .= $sub[$i+0];
				$gp_pix['pal'] .= $sub[$i+1];
				$gp_pix['pal'] .= $sub[$i+2];
				$gp_pix['pal'] .= BYTE;
				$gp_pix['cc']++;
			}
			break;
/*
		case 6: // meta
			$gp_pix['size'] = array();
			for ( $i=8; $i < $len; $i += 0x10 )
			{
				$w =
				$gp_clut .= $sub[$i+0];
				$gp_clut .= $sub[$i+1];
				$gp_clut .= $sub[$i+2];
				$gp_clut .= BYTE;
				$gp_pix['cc']++;
			}
			break;
*/
	} // switch ( $type )

	save_file($fn, $sub);
	return;
}

function disc1( $fname )
{
	// for *.scn only
	//if ( stripos($fname, '.scn') === false )
		//return;

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$dir = str_replace('.', '_', $fname);
	$ed = strlen($file);
	$st = 0;
	$id = 0;
	while ( $st < $ed )
	{
		$typ = str2int($file, $st+0, 4);
		$nxt = str2int($file, $st+4, 4);
		$fn  = sprintf("%s/%04d.%d", $dir, $id, $typ & BIT8);

		if ( $nxt === 0 )
			$nxt = $ed;
		$siz = $nxt - $st;
		printf("%8x , %6x , %6x , %s\n", $typ, $st, $siz, $fn);

		$sub = substr($file, $st, $siz);
		sectscn($sub, $typ & BIT8, $fn);
		$st = $nxt;
		$id++;
	} // while ( $st < $ed )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	disc1( $argv[$i] );
