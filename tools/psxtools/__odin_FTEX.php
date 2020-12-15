<?php
/*
[license]
[/license]
 */
require "common.inc";

function sectfgst( &$file, $pos, $pfx, $id )
{
	printf("== sectfgst( %x , %s , %d )\n", $pos, $pfx, $id);
	$fgsz = str2int($file, $pos+4, 4);
	$hdsz = str2int($file, $pos+8, 4);

	$w  = str2int($file, $pos+0x14, 2);
	$h  = str2int($file, $pos+0x16, 2);
	$cc = str2int($file, $pos+0x24, 2);
	$cn = str2int($file, $pos+0x26, 2);
	$tm = substr0($file, $pos+0x44);
	printf("%8x , pix %xx%x , %s\n", $pos, $w, $h, $tm);
		$pos += $hdsz;

	$pal = "";
	for ( $i=0; $i < $cn; $i++ )
	{
		$pal .= substr($file, $pos, $cc*4);
		while ( strlen($pal) % 0x40 )
			$pal .= PIX_ALPHA;
		$pos += ($cc * 4);
	}
	printf("add CLUT %dx%d @ %x\n", $cc, $cn, $pos);

	$pix = "";
	$len = $fgsz - $hdsz - ($cc * $cn * 4);
	$part = $len / 0x20;
	$strp = 2;
	$colr = 8;
	$blck = 2;
	for ( $p=0; $p < $part; $p++ )
	{
		$pxx = $p * $blck * $strp * $colr;
		for ( $b=0; $b < $blck; $b++ )
		{
			$bxx = $b * $colr;
			for ( $s=0; $s < $strp; $s++ )
			{
				$sxx = $s * $strp * $colr;
				for ( $c=0; $c < $colr; $c++ )
				{
					$i = $pxx + $bxx + $sxx + $c;
					$pix .= $file[$pos+$i];
				} // for ( $c=0; $c < $colr; $c++ )
			} // for ( $s=0; $s < $strp; $s++ )
		} // for ( $b=0; $b < $blck; $b++ )
	} // for ( $p=0; $p < $part; $p++ )

	$clut = "CLUT";
	$clut .= chrint( strlen($pal) / 4, 4);
	$clut .= chrint( $w, 4);
	$clut .= chrint( $h, 4);
	$clut .= $pal;
	$clut .= $pix;
	save_file("$pfx/$id.clut", $clut);
/*
            int parts = length / 32;
            int stripes=2;
            int colors = 8;
            int blocks = 2;

            int i = 0;
            for (int part = 0; part < parts; part++)
            {
                for (int block = 0; block < blocks; block++)
                {
                    for (int stripe = 0; stripe < stripes; stripe++)
                    {

                        for (int color = 0; color < colors; color++)
                        {
                            newColors[i++] = originalData[
								index
								+ part * colors * stripes * blocks
								+ block * colors
								+ stripe * stripes * colors
								+ color ];
                        }
                    }
                }
            }
*/
	return;
}

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
				sectfgst($file, $st, $pfx, $id);
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
