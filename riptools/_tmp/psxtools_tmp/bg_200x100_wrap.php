<?php

function clutwrap( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file,0,4) !== "CLUT" )
		return;

	$sz = strlen($file);
	$hz = $sz - (0x200*0x100);
	if ( $hz <= 16 )  return;

	$img  = substr($file, 0, $hz);
	$file = substr($file, $hz);

	//$wrap_x = 0x80;
	//$wrap_y = 0x20;
	$wrap_x = 0x180;
	$wrap_y = 0xe0;
	for ( $y=0; $y < 0x100; $y++ )
	{
		$dyy = ( ($y + $wrap_y) % 0x100 ) * 0x200;
		for ( $x=0; $x < 0x200; $x++ )
		{
			$dxx  = $dyy + ( ($x + $wrap_x) % 0x200 );
			$img .= $file[$dxx];

		} // for ( $x=0; $x < 0x200; $x++ )
	} // for ( $y=0; $y < 0x100; $y++ )

	file_put_contents("$fname.w", $img);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	clutwrap( $argv[$i] );
