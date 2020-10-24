<?php
require "common.inc";

function tex2clut( &$file, $dir )
{
	$pal = array();

	$len = strlen($file);
	$pos = 0;
	$id = 0;
	while ( $pos < $len )
	{
		$b1 = str2int($file, $pos, 4);
		$bak = $pos;
		switch ( $b1 )
		{
			case 0x1201: // 256 palette
				$w  = str2int($file, $pos+0x0c, 2);
				$no = str2int($file, $pos+0x18, 2);
					$pos += (0x800 + $no * 0x800);

				$cn = $no * 4;
				printf("%6x  %4x  clut @ %d\n", $bak, $b1, $cn);
				for ( $i=0; $i < $cn; $i++ )
				{
					$p1 = $bak + 0x800 + ($i * 0x200);
					$p2 = substr($file, $p1, 0x200);
					if ( trim($p2, ZERO) != "" )
						$pal[] = pal555($p2);
				}
				break;
			case 0x1200: // 8-bpp pixels
				$w  = str2int($file, $pos+0x0c, 2);
				$no = str2int($file, $pos+0x18, 2);
					$pos += (0x800 + $no * 0x800);

				$data = '';
				$h = 0;
				for ( $i=0; $i < $no; $i++ )
				{
					$p1 = $bak + 0x1c + ($i * 2);
					$p1 = str2int($file, $p1, 2);

					$p2 = $bak + 0x800 + ($i * 0x800);
					$data .= substr($file, $p2, $p1*$w*2);
					$h += $p1;
				}

				printf("%6x  %4x  pix @ %dx%d\n", $bak, $b1, $w, $h);
				if ( empty($pal) )
					$pal[] = grayclut(0x100);

				foreach ( $pal as $k => $v )
				{
					$clut = "CLUT";
					$clut .= chrint(0x100, 4);
					$clut .= chrint($w*2, 4);
					$clut .= chrint($h  , 4);
					$clut .= $v;
					$clut .= $data;
					save_file("$dir/$id-$k.clut", $clut);
				}

				$id++;
				break;
		} // switch ( $b1 )
	} // while ( $pos < $len )

	return;
}
//////////////////////////////
function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$b1 = str2int($file, 0, 4);
	if ( $b1 != 0x1201 && $b1 != 0x1200 )
		return;

	echo "=== $fname ===\n";
	$dir  = str_replace('.', '_', $fname);
	tex2clut($file, $dir);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
