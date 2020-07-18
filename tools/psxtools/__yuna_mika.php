<?php
require "common.inc";

function yuna( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$rgba = "";
	$ed = strlen($file);
	$st = 0;
	while ( $st < $ed )
	{
		$b = ord( $file[$st] );
		if ( $b == 0 || $b & 0x80 )
		{
			$rgba .= rgb555( $file[$st+1] . $file[$st+0] );
			$st += 2;
		}
		else
		{
			$cnt = $b;
			$b = rgb555( $file[$st+2] . $file[$st+1] );
			while ( $cnt > 0 )
			{
				$rgba .= $b;
				$cnt--;
			}
			$st += 3;
		}
	}
	file_put_contents("$fname.tmp", $rgba);

	$ed = strlen($rgba) / 4;
	$w = 8 * 0x28;
	//$w = 8;
	$h = floor($ed / $w);
	printf("$fname , %x , $w , $h\n", $ed*4);

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $w;
	$pix['rgba']['h'] = $h;
	$pix['rgba']['pix'] = canvpix($w,$h);
	$pix['bgzero'] = true;

	$pix['src']['w'] = 8;
	$pix['src']['h'] = 8;

	$st = 0;
	for ( $y=0; $y < $h; $y += 8 )
	{
		for ( $x=0; $x < $w; $x += 8 )
		{
			$b = substr($rgba, $st, 8*8*4);
				$st += 0x100;
			list($c,$p) = rgba2clut($b, '');

			$pix['src']['pix'] = $p;
			$pix['src']['pal'] = $c;
			$pix['dx'] = $x;
			$pix['dy'] = $y;

			copypix($pix);
		} // for ( $x=0; $x < $w; $x += 8 )
	} // for ( $y=0; $y < $h; $y += 8 )

	savpix($fname, $pix);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	yuna( $argv[$i] );
