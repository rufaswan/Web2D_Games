<?php
require "common.inc";
$gp_cc = 0; // 16 or 256

function palette( $fname )
{
	global $gp_cc;
	if ( $gp_cc < 1 )
		return printf("ERROR gp_cc is zero\n");

	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$siz = strlen($file);
	if ( $siz % ($gp_cc*2) )
		return printf("ERROR %s not enough data\n", $fname);

	$cn = $siz / ($gp_cc*2);
	$clut = mstrpal555($file, 0, $gp_cc, $cn);

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $gp_cc * 16;
	$pix['rgba']['h'] = $cn    * 16;
	$pix['rgba']['pix'] = canvpix($gp_cc*16,$cn*16);
	$pix['bgzero'] = true;

	foreach ( $clut as $ck => $cv )
	{
		$pix['dy'] = $ck * 16;
		for ( $i=0; $i < $gp_cc; $i++ )
		{
			$pix['dx'] = $i * 16;

			$pix['src']['w'] = 16;
			$pix['src']['h'] = 16;
			$pix['src']['pix'] = str_repeat(chr($i), 16*16);
			$pix['src']['pal'] = $cv;

			copypix($pix);
		} // for ( $i=0; $i < $gp_cc; $i++ )
	} // foreach ( $clut as $ck => $cv )

	savpix($fname, $pix);
	return;
}

for ( $i=1; $i < $argc; $i++ )
{
	$opt = $argv[$i];
	if ( $opt[0] == '-' )
		$gp_cc = $opt * -1;
	else
		palette( $opt );
}
