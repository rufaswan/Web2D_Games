<?php
require "common.inc";
require "common-guest.inc";

function png_chunk( &$png )
{
	$chunk = array();
	$chunk['PNG'] = substr($png, 0, 8);

	$ed = strlen($png);
	$st = 8;
	while ( $st < $ed )
	{
		//   uppercase     lowercase
		// 1 is critical / optional
		// 2 is public   / private
		// 3 *reserved*  / *invalid*
		// 4 is unsafe   / safe to copy by editor
		$mgc = substr($png, $st+4, 4);
		$len = str2big($png, $st+0, 4);
		printf("%8x , %8x , $mgc\n", $st, $len);

		$dat = substr($png, $st+8, $len);
		if ( ! isset( $chunk[$mgc] ) )
			$chunk[$mgc] = "";
		$chunk[$mgc] .= $dat;

		$st += (8 + $len + 4);
	} // while ( $st < $ed )

	return $chunk;
}

// to strip off any optional PNG chunks (tIME,gAMA,iCCP,etc)
function pngstrip( $fname )
{
	$png = file_get_contents($fname);
	if ( empty($png) )  return;

	if ( substr($png, 1, 3) != "PNG" )
		return;

	$chunk = png_chunk($png);

	// chunks to keep
	$tag = array("IHDR", "PLTE", "tRNS", "IDAT", "IEND");
	$strip = $chunk['PNG'];
	foreach ( $tag as $t )
	{
		if ( ! isset( $chunk[$t] ) )
			continue;
		$len = strlen($chunk[$t]);
		$crc = crc32( $t . $chunk[$t] );

		$strip .= chrbig($len, 4);
		$strip .= $t . $chunk[$t];
		$strip .= chrbig($crc, 4);
	}

	file_put_contents($fname, $strip);
	return;
}

for ( $i=0; $i < $argc; $i++ )
	pngstrip( $argv[$i] );
