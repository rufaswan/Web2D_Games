<?php
/*
[license]
[/license]
 */
require "common.inc";

function odin( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) != "FTEX" )
		return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));

	$ed = strlen($file);
	$st = str2int($file, 8, 4);
	$id = 0;
	while ( $st < $ed )
	{
		$mgc = substr($file, $st, 4);
		switch ( $mgc )
		{
			case "FGST":
				$siz = str2int($file, $st+0x04, 4);
				$nam = substr0($file, $st+0x44);
				$w = str2int($file, $st+0x14, 2);
				$h = str2int($file, $st+0x16, 2);
				printf("%8x , %8x , %4x , %4x , %s\n", $st, $siz, $w, $h, $nam);

				$cc = str2int($file, $st+0x24, 2);
				$cn = str2int($file, $st+0x26, 2);
				$pos = $st + 0x80;
				$pal = "";
				for ( $i=0; $i < $cn; $i++ )
				{
					$pal .= substr($file, $pos, $cc*4);
					while ( strlen($pal) % 0x40 )
						$pal .= PIX_ALPHA;
					$pos += ($cc * 4);
				}

				$pos = $st + 0x80 + ($cc * $cn * 4);


				$st = int_ceil($st+0x80+$siz, 0x10);
				$id++;
				break;
			case "FEOC":
				return;
			default:
				printf("%8x , UNKNOWN\n", $st);
				return;
		} // switch ( $mgc )
	} // while ( $st < $ed )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	odin( $argv[$i] );
