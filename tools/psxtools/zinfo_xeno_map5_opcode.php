<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D Games.
    <https://github.com/rufaswan/Web2D_Games>

Web2D Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
require 'common.inc';

function map5_label( &$file )
{
	$action = array(
		'oninit' , 'onloop' , 'ontalk' , 'onpush' ,
		'ev_00'  , 'ev_01'  , 'ev_02'  , 'ev_03'  ,
		'ev_04'  , 'ev_05'  , 'ev_06'  , 'ev_07'  ,
		'ev_08'  , 'ev_09'  , 'ev_10'  , 'ev_11'  ,

		'ev_12'  , 'ev_13'  , 'ev_14'  , 'ev_15'  ,
		'ev_16'  , 'ev_17'  , 'ev_18'  , 'ev_19'  ,
		'ev_20'  , 'ev_21'  , 'ev_22'  , 'ev_23'  ,
		'ev_24'  , 'ev_25'  , 'ev_26'  , 'ev_27'  ,
	);
	$list = array('default' => 0);
	$len  = strlen($file);
	for ( $i=0; $i < $len; $i += 0x40 )
	{
		for ( $j=0; $j < 0x40; $j += 2 )
		{
			$p = str2int($file, $i + $j, 2);
			if ( $p === 0 )
				continue;
			$name = sprintf('obj_%d::%s', $i >> 6, $action[$j >> 1]);
			$list[$name] = $p;
		}
	} // for ( $i=0; $i < $len; $i += 0x40 )
	return $list;
}

function map5_opcode( &$file, &$label )
{
	$op_siz = array(
		-1,-9,-9, 4 , -1, 3, 5, 3 ,  3, 3, 4, 3 ,  1,-1, 1, 1 , // 00
		-1,-1, 9, 1 ,  1, 1, 3,18 ,  5, 6, 2, 7 ,  4, 7, 1, 2 , // 10
		 3, 3, 1, 1 ,  2, 2, 3, 2 ,  2, 2, 1, 1 ,  2, 8, 3, 3 , // 20
		 3, 5, 5, 1 ,  5, 6, 3, 3 ,  6, 6, 6, 6 ,  3, 3, 6, 6 , // 30

		 6, 5, 5, 3 ,  5, 8, 1, 6 ,  7, 8, 6, 7 ,  8,10, 6, 8 , // 40
		 8,10, 2, 4 ,  5, 7,10,13 ,  4, 1, 1,-1 ,  3, 2, 1, 2 , // 50
		 1, 8, 2, 8 ,  1, 8, 2, 4 ,  4, 3, 3, 3 ,  3, 8, 8, 2 , // 60
		 2, 3, 3, 8 ,  3, 3, 1, 1 ,  4, 1, 1, 4 ,  4, 4, 4, 3 , // 70

		 5, 5, 5, 5 ,  5, 5, 5, 3 ,  3, 6, 4, 5 ,  3, 3, 7, 3 , // 80
		 3, 4,-1, 3 ,  5, 2, 1, 3 ,  5, 1, 3, 5 ,  1, 4, 1, 1 , // 90
		 7, 3, 1, 8 ,  4, 3,-1, 1 ,  5, 2, 2, 1 ,  4, 7, 7, 4 , // a0
		 4, 4, 2, 3 ,  3, 5, 5, 1 ,  1, 4, 2, 2 ,  1, 3, 3, 3 , // b0

		 3, 3, 3, 1 ,  2, 2, 1, 3 ,  3, 4, 8, 4 ,  4, 1, 1, 5 , // c0
		11, 0, 4,-1 ,  6, 3, 3, 3 ,  3, 3,17, 5 ,  5, 6, 6, 6 , // d0
		 7,14,-1,-1 ,  0,17, 9, 7 ,  7, 7,-1,20 , 15, 8, 3, 3 , // e0
		 7,11, 9, 7 ,  2, 4, 2, 5 ,  4, 2,-1, 5 ,  6, 1,-9, 1 , // f0
	);
	$opfe_siz = array(
		 0, 1, 4, 3 ,  3, 6, 6, 2 ,  7, 3, 3, 3 , 13, 3, 5, 6 , // 00
		 5, 6, 3, 5 ,  5, 5, 1, 3 ,  4, 2, 1, 5 ,  8, 8, 2, 1 , // 10
		 2, 3, 3,20 ,  1, 2,15, 4 ,  3, 3, 3, 3 ,  3, 3, 3, 3 , // 20
		 5, 5, 5, 5 ,  6, 6, 6, 6 ,  5, 3, 3, 3 ,  5,10,10, 7 , // 30

		 7, 3, 3, 1 ,  1, 2, 2, 3 ,  8, 1, 3, 1 ,  2, 2, 1, 1 , // 40
		 1, 1, 1, 1 ,  1, 1, 3, 1 ,  3, 3, 3, 3 ,  4, 7, 3, 8 , // 50
		 9, 1, 5, 5 ,  3, 5, 9,19 , -1, 5, 3, 5 ,  1, 1, 4, 8 , // 60
		 3, 3,10,12 ,  3, 4,16,11 ,  0, 0, 0, 0 ,  0, 0, 0, 1 , // 70

		15, 8,25, 3 ,  9, 3, 2, 1 , 18,11, 3, 3 ,  7, 3, 5, 8 , // 80
		 9,14,14,11 , 10,14, 1, 2 ,  3, 2, 9, 3 ,  3, 3, 9, 4 , // 90
		 2, 5, 1, 2 ,  1, 7, 5, 9 ,  7, 7, 2, 4 ,  4, 4, 7,18 , // a0
		 6, 1, 4, 4 ,  4, 1, 2, 3 ,  4, 9,10, 3 ,  4, 7, 1,13 , // b0

		 3, 7, 9, 1 ,  2, 5, 5, 5 , 18,18, 2, 1 ,  1, 3, 3, 5 , // c0
		 5, 1, 3,17 , 10, 5, 5, 6 ,  2, 2, 1, 3 ,  5, 2, 5, 3 , // d0
		 2, 5,-1,-1 , -1,-1,-1,-1 , -1,-1,-1,-1 , -1,-1,-1,-1 , // e0
		-1,-1,-1,-1 , -1,-1,-1,-1 , -1,-1,-1,-1 , -1,-1,-1,-1 , // f0
	);
	$list = array();
	$len  = strlen($file);
	$pos  = 0;
	while ( $pos < $len )
	{
		$by = ord( $file[$pos] );
		switch ( $by )
		{
			case 0x01: // goto
				$goto = str2int($file, $pos+1, 2);
				$name = sprintf('loc_%x', $goto);
				$label[$name] = $goto;

				printf("%4x : add %2x label %s\n", $pos, $by, $name);
				$list[$pos] = substr($file, $pos, 3);
				$pos += 3;
				break;
			case 0x02: // if else
				$goto = str2int($file, $pos+6, 2);
				$name = sprintf('loc_%x', $goto);
				$label[$name] = $goto;

				printf("%4x : add %2x label %s\n", $pos, $by, $name);
				$list[$pos] = substr($file, $pos, 8);
				$pos += 8;
				break;
			case 0xfe:
				$by = ord( $file[$pos+1] );
				switch( $by )
				{
					default:
						$sz = $opfe_siz[$by];
						if ( $sz < 1 )
							$sz = 1;
						$list[$pos] = substr($file, $pos, 1+$sz);
						$pos += (1 + $sz);
						break;
				} // switch( $by )
				break;
			default:
				$sz = $op_siz[$by];
				if ( $sz < 1 )
					$sz = 1;
				$list[$pos] = substr($file, $pos, $sz);
				$pos += $sz;
				break;
		} // switch ( $by )
	} // while ( $pos < $len )
	return $list;
}
//////////////////////////////
function map5_haslabels( &$label, $p )
{
	$list = array();
	foreach ( $label as $k => $v )
	{
		if ( $v === $p )
			$list[] = $k;
	}
	return $list;
}

function xeno_map5op( &$file )
{
	$cnt = str2int($file, 0x80, 2);

	$sub = substr($file, 0x84, $cnt*0x40);
	$label = map5_label($sub);

	$sub = substr($file, 0x84 + $cnt*0x40);
	$oplist = map5_opcode($sub, $label);

	// check for invalid labels
	foreach ( $label as $k => $v )
	{
		if ( ! isset($oplist[$v]) )
			printf("opcode not stop @ %4x [%s]\n", $v, $k);
	}

	$sum = array();
	foreach ( $oplist as $ok => $ov )
	{
		$lab = map5_haslabels($label, $ok);
		if ( ! empty($lab) )
		{
			foreach ( $lab as $lv )
				printf("%s:\n", $lv);
		}
		printf("  %4x : %s\n", $ok, printhex($ov));

		$op = $ov[0];
		if ( $op === "\xfe" )
			$op = substr($ov, 0, 2);

		if ( ! isset($sum[$op]) )
			$sum[$op] = 0;
		$sum[$op]++;
	} // foreach ( $oplist as $ok => $ov )

	ksort($sum);
	foreach ( $sum as $op => $ov )
		printf("> %4s = %4x\n", bin2hex($op), $ov);
	return;
}

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	xeno_map5op($file);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );

/*
map file 5.dec
	current = 800ad0d8
sbyte
ubyte
shalf  800ac274  sub_800ac254
uhalf  800ac2b0  sub_800ac290
word

800a14e8  lbu   v0, 0(v0)
800a14f0  sll   v0, 2
800a14f4  addu  v0, s1  // s1=800ad778
800a14f8  lw    v0, 0(v0)
800a1500  jalr  v0

if ( op === fe )
	80085ffc  lbu   v0, 0(v1)
	80086004  sll   v0, 2
	80086008  lui   at, 800b
	8008600c  addu  at, v0
	80086010  lw    v0, -2488(at)  // at=800adb78
	80086018  jalr  v0

*/
