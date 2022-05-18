<?php
require 'common.inc';

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$len = strlen($file);
	$pix = '';
	$p = array(ZERO, "\x01");
	for ( $i=0; $i < $len; $i++ )
	{
		$c = ord( $file[$i] );
		$j = 8;
		while ( $j > 0 )
		{
			$j--;
			$b = ($c >> $j) & 1;
			$pix .= $p[$b];
		}
	} // for ( $i=0; $i < $len; $i++ )

	$w = 16;
	$h = strlen($pix) >> 4;

	$img = array(
		'cc' => 2,
		'w' => $w,
		'h' => $h,
		'pal' => grayclut(2),
		'pix' => $pix,
	);
	save_clutfile("$fname.clut", $img);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );
