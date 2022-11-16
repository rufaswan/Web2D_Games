<?php
/*
 * Special Thanks
 *   DSVania Editor
 *   https://github.com/LagoLunatic/DSVEdit/blob/master/docs/formats/Skeleton%20File%20Format.txt
 *     LagoLunatic
 */
require 'common.inc';

function loop_opd( &$c_mod, &$file, &$pos )
{
	$sz = str2int($file, $pos+2, 1);
	$fn = substr ($file, $pos+3, $sz);

	$dx = str2int($file, $pos+0x22, 2, true);
	$dy = str2int($file, $pos+0x24, 2, true);
	printf("%8x  %8x  %d,%d  %s\n", $c_mod, $pos, $dx, $dy, $fn);

	$cjnt = str2int($file, $pos+0x26, 1);
	$cjnt_inv = str2int($file, $pos+0x27, 1);
	$cjnt_vis = str2int($file, $pos+0x28, 1);
	$chit = str2int($file, $pos+0x29, 1);
	$cpss = str2int($file, $pos+0x2a, 1);
	$cpnt = str2int($file, $pos+0x2b, 1);
	$canm = str2int($file, $pos+0x2c, 1);

	$pjnt = $pos  + 0x30;
	$ppss = $pjnt + ($cjnt * 4);
	$phit = $ppss + ($cpss * (2 + ($cjnt * 4)));
	$ppnt = $phit + ($chit * 8);
	$pvis = $ppnt + ($cpnt * 4);
	$pamn = $pvis + $cjnt_vis;

	//////////////////////////////
	for ( $i=0; $i < $camn; $i++ )
	{
		$p = $pamn + ($i * 4);

		$file[$p+0] = "\x01";
		$file[$p+1] = "\x00";
		$file[$p+2] = "\x00";
		$file[$p+3] = "\x00";
		$c_mod++;
	} // for ( $i=0; $i < $camn; $i++ )
	//////////////////////////////

	$pos = $pamn;
	return;
}

function ndsooe( $fname )
{
	$file = load_bakfile($fname);
	if ( empty($file) )  return;

	$t = substr($file, 0, 18);
		$t = str_replace(ZERO, '', $t);
	if ( $t !== 'CASTLEVANIA3YR9JA4' )
		return;

	$c_mod = 0;
	$pos   = 0;
	while (1)
	{
		$pos = strpos($file, '.opd', $pos);
		if ( $pos === false )
			break;

		$pos -= ($pos & 0xff);
		loop_opd($c_mod, $file, $pos);
	} // while (1)

	if ( $c_mod > 0 )
		save_file($fname, $file);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	ndsooe( $argv[$i] );
