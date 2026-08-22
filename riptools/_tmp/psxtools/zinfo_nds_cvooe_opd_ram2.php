<?php
require 'common.inc';

function ramoff( &$file, $off )
{
	if ( $file[$off+3] !== "\x02" )
		return -1;
	return str2int($file, $off, 3);
}

function info_pose0( &$file, $off )
{
	printf("== nfo_pose0( %x )\n", $off);
	$jnt_file = ramoff($file, $off + 0x00); // jnt file
	$jnt_off = ramoff($file, $off + 0x04); // jnt file - joint
	$pos_off = ramoff($file, $off + 0x08); // jnt file - pose
	//ramoff($file, $off + 0x0c); // jnt file - hitbox
	//ramoff($file, $off + 0x10); // jnt file - point
	//ramoff($file, $off + 0x14); // jnt file - draw
	//ramoff($file, $off + 0x18); // jnt file - anim
	//ramoff($file, $off + 0x1c); //
	//ramoff($file, $off + 0x20); //

	$jpos = substr($file, $jnt_file + 0x22, 0x2d-0x22);
	printf("jnt  head = %s\n", printhex($jpos));

	$cjnt = str2int($file, $jnt_file + 0x26, 1);
	$txt1 = '';
	$txt2 = '';

	$res_off = $off + 0x78;
	$jpos = substr($file, $pos_off, 2);
		$pos_off += 2;
	printf("pose head = %s\n", printhex($jpos));

	for ( $i=0; $i < $cjnt; $i++ )
	{
		// 0123456789abcdef0123456789ab
		// --1-4   4   2 2 2 11--2 ----
		$jres = substr($file, $res_off, 0x1c);
			$res_off += 0x1c;
		$jjnt = substr($file, $jnt_off, 4);
			$jnt_off += 4;
		$jpos = substr($file, $pos_off, 4);
			$pos_off += 4;
		$txt1 .= sprintf("%2x   %s   %s   %s\n", $i, printhex($jjnt), printhex($jpos), printhex($jres));

		$b02 = str2int($jres, 0x02, 1);
		$b04 = str2int($jres, 0x04, 4, true); // x
		$b08 = str2int($jres, 0x08, 4, true); // y
		$b0c = str2int($jres, 0x0c, 2); // rot
		$b0e = str2int($jres, 0x0e, 2); // rot
		$b10 = str2int($jres, 0x10, 2); // rot
		$b12 = str2int($jres, 0x12, 1);
		$b13 = str2int($jres, 0x13, 1);
		$b16 = str2int($jres, 0x16, 2);
			$b04 /= 0x1000;
			$b08 /= 0x1000;
			$b0c = $b0c * 360 / 0x10000;
			$b0e = $b0e * 360 / 0x10000;
			$b10 = $b10 * 360 / 0x10000;

		$txt2 .= sprintf("%2x : %7.2f  %7.2f , %5.1f %5.1f %5.1f\n", $i, $b04, $b08, $b0c, $b0e, $b10);
	} // for ( $i=0; $i < $cjnt; $i++ )

	echo "$txt1\n";
	echo "$txt2\n";
	return;
}

function cvooe( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$pos = 0;
	while (1)
	{
		$pos = strpos($file, '.opd', $pos);
		if ( $pos === false )
			break;

		$off = -1;
		if ( $file[$pos -  5] === "\x08" )
			$off = $pos -  7;
		else
		if ( $file[$pos -  8] === "\x0b" )
			$off = $pos - 10;
		else
		if ( $file[$pos -  9] === "\x0c" )
			$off = $pos - 11;
		else
		if ( $file[$pos - 10] === "\x0d" )
			$off = $pos - 12;
		else
		if ( $file[$pos - 11] === "\x0e" )
			$off = $pos - 13;

		$pos += 4;
		if ( $off < 0 )
			continue;

		$chr = chrint($off + 0x2000030, 4);
		$off = strpos($file, $chr);
		info_pose0($file, $off - 4);
	} // while (1)
	return;
}

cvooe('RAM2');

/*
mon 102/66 , final knight  , pose 0
	cart  2c08400 + ed2 = 2c092d2

id  f  p_rot           dist  +x       +y      dx       dy
 0  -  5b               - =  0    ,   0    = -1    ,  -46
 2  4  += c3e9 = c444   - =  0    ,   0    = -1    ,  -46
 3  -  5b               a =  1.04 ,  -9.94 =  0.04 ,  -55.95
 4  4  += bb60 = bbbb   - =  0    ,   0    =  0.04 ,  -55.95
 5  -  5b              1c = -2.92 , -27.84 = -2.92 ,  -83.79
1f  4  += be38 = be93   - =  0    ,   0    = -2.92 ,  -83.79
20  -  5b              18 = -0.83 , -23.98 = -3.76 , -107.77

 0  -  5b               - =  0    ,  0    =  -1    , -46
14  2  +=[8000]= 805b   5 = -4.99 , -0.04 =  -6    , -46.04
15  4  += 5666 = 5666   - =  0    ,  0    =  -6    , -46.04
16  -  5b               f = -7.83 , 12.78 = -13.83 , -33.24
17  1  +=[4000]= 405b  18 = -0.2  , 23.99 = -14.01 ,  -9.24


mon 118/76 , eligor  , pose 0
	cart  2bfc000 + 68f0 = 2c028f0

*/
