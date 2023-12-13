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

$gp_op_siz = array(
//   0  1  2  3    4  5  6  7    8  9  a  b    c  d  e  f
	 1,-2,-2, 4 , -1,-2,-2, 3 ,  3, 3,-2, 3 ,  1, 1, 1, 1 , // 00
	-1,-1, 4, 1 ,  1, 1, 3,18 ,  5, 6, 2, 7 ,  4, 7, 1, 2 , // 10
	 3, 3, 1, 1 ,  2, 2, 3, 2 ,  2, 2, 1, 1 ,  2, 8, 3, 3 , // 20
	 3,-2,-2, 1 ,  5, 6, 3, 3 ,  6, 6, 6, 6 ,  3, 3, 6, 6 , // 30

	 6, 5, 5, 3 ,  5, 8, 1, 6 ,  7, 8, 6, 8 ,  8,10, 6, 8 , // 40
	 8,10, 2, 4 ,  5, 7,10,-1 ,  4, 1, 1,-1 ,  3, 2, 1, 2 , // 50
	 1, 8, 2, 8 ,  1, 8, 2, 4 ,  4, 3, 3, 3 ,  3, 8, 8, 2 , // 60
	 2, 3, 3,-1 ,  3, 3, 1, 1 ,  4, 1, 1, 4 ,  4, 4, 4, 3 , // 70

	 5, 5, 5, 5 , -2,-2,-2, 3 ,  3,-2,-2,-2 ,  3, 3,-2, 3 , // 80
	 3,-2,-1, 3 ,  5, 2, 1, 3 ,  5, 1, 3, 5 ,  1, 4, 1, 1 , // 90
	 7, 3, 2, 8 ,  4, 3, 3, 1 ,  5, 2, 2, 1 ,  4, 7, 7, 4 , // a0
	 4, 4, 2, 3 ,  3, 5, 5, 1 ,  1,-2, 2, 2 ,  1, 3, 3, 3 , // b0

	 3, 3, 3, 1 ,  2, 2, 1, 3 ,  3,-2, 8,-2 ,  4, 1, 1, 5 , // c0
	11, 0, 4, 4 , -1, 3, 3, 3 ,  3, 3,17, 5 ,  5, 6, 6, 6 , // d0
	 7,14,-2,-2 ,  0,17, 9, 7 ,  7, 7, 6,20 , 15, 8, 3, 3 , // e0
	 7,11, 9, 7 ,  2, 4, 2, 5 ,  4, 2, 5,-2 , -1, 1,-1, 1 , // f0
);
$gp_opfe_siz = array(
//   0  1  2  3    4  5  6  7    8  9  a  b    c  d  e  f
	 0, 1, 4, 3 ,  3,-2,-2, 2 ,  7, 3, 3, 3 , 13, 3, 5, 6 , // 00
	 5, 6, 3, 5 ,  5, 5, 1, 3 ,  4, 2, 1, 5 ,  8, 8, 2, 1 , // 10
	 2, 3, 3,20 ,  1, 2,15,-1 ,  3, 3, 3, 3 ,  3, 3, 3, 3 , // 20
	-2,-2,-2,-2 , -2,-2,-2,-2 ,  5, 3, 3, 3 ,  5,10,10, 7 , // 30

	 7, 3, 3, 1 ,  1, 2, 2, 3 ,  8, 1, 3, 1 ,  2, 2, 1, 1 , // 40
	 1, 1, 1, 1 ,  1, 1, 3, 1 ,  3, 3, 3, 3 , -1, 7, 3, 8 , // 50
	 9, 1, 5, 5 ,  3, 5, 9,19 ,  6, 5, 3, 5 ,  1, 1, 4, 8 , // 60
	 3, 3,10,12 ,  3, 4,16,-1 ,  0, 0, 0, 0 ,  0, 0, 0, 1 , // 70

	15, 8,25, 3 ,  9, 3, 2, 1 , 18,11, 3, 3 ,  7, 3, 5, 8 , // 80
	 9,14,14,11 , 10,14, 1, 2 ,  3, 2, 9, 3 ,  3, 3, 9, 4 , // 90
	12, 5, 1, 2 ,  1, 7, 5, 9 ,  7, 7, 2, 4 ,  4, 4, 7,18 , // a0
	-1, 1, 4, 4 ,  4, 1, 2, 3 ,  4, 9,10, 3 ,  4, 7, 1,13 , // b0

	 3, 7, 9, 1 ,  2, 5, 5, 5 , 18,18, 2, 1 ,  1, 3, 3, 5 , // c0
	 5, 1, 3,17 , -1, 5, 5, 6 ,  2, 2, 1, 3 ,  5,-1, 5, 3 , // d0
	 2, 5,-1,-1 , -1,-1,-1,-1 , -1,-1,-1,-1 , -1,-1,-1,-1 , // e0
	-1,-1,-1,-1 , -1,-1,-1,-1 , -1,-1,-1,-1 , -1,-1,-1,-1 , // f0
);
$gp_goto = array(
	0x05 => array(1, 3, true), 0x06 => array(1, 5, true),
	0x0a => array(2, 4, true),

	0x01 => array(1, 3, false), 0x02 => array(6, 8, false),
	0x84 => array(3, 5, false), 0x85 => array(3, 5, false), 0x86 => array(3, 5, false),
	0x89 => array(4, 6, false), 0x8a => array(2, 4, false), 0x8b => array(3, 5, false),
	0x8e => array(5, 7, false),
	0x91 => array(2, 4, false),
	0xb9 => array(2, 4, false),
	0xc9 => array(2, 4, false),
	0xcb => array(2, 4, false),
	0xfb => array(3, 5, false),
	0xfe05 => array(4, 6, false), 0xfe06 => array(4, 6, false),

	0x31 => array(3, 5, false), 0x32 => array(3, 5, false),
	0xe2 => array(3, 5, false), 0xe3 => array(3, 5, false),
	0xfe30 => array(3, 5, false), 0xfe31 => array(3, 5, false), 0xfe32 => array(3, 5, false), 0xfe33 => array(3, 5, false),
	0xfe34 => array(4, 6, false), 0xfe35 => array(4, 6, false), 0xfe36 => array(4, 6, false), 0xfe37 => array(4, 6, false),
);

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
	$list = array();
	$len  = strlen($file);
	for ( $i=0; $i < $len; $i += 0x40 )
	{
		for ( $j=0; $j < 0x40; $j += 2 )
		{
			$p = str2int($file, $i + $j, 2);
			if ( $p === 0 && $j > 0 )
				continue;
			$name = sprintf('obj_%d::%s', $i >> 6, $action[$j >> 1]);
			$list[$name] = $p;
		}
	} // for ( $i=0; $i < $len; $i += 0x40 )
	return $list;
}

function map5_loclabel( &$file, &$pos, &$label, &$list, $oppos, $opsiz, $isfunc )
{
	$goto = str2int($file, $pos + $oppos, 2);
	if ( $isfunc )
		$name = sprintf('  func_%x', $goto);
	else
	{
		if ( $goto < $pos )
			$name = sprintf('    loop_%x', $goto);
		else
			$name = sprintf('    goto_%x', $goto);
	}
	$label[$name] = $goto;

	//printf("%4x : add label %s\n", $pos, $name);
	$list[$pos] = substr($file, $pos, $opsiz);
	$pos += $opsiz;
	return;
}

function map5_opcode( &$oplist, &$file, &$label, $pos, $len )
{
	global $gp_op_siz, $gp_opfe_siz, $gp_goto;

	while ( $pos < $len )
	{
		$by = ord( $file[$pos] );
		if ( $by === 0xfe )
		{
			$by = ord( $file[$pos+1] );
			$by |= 0xfe00;
		}

		if ( isset($gp_goto[$by]) )
		{
			list($p,$z,$f) = $gp_goto[$by];
			if ( $by > BIT8 )
				map5_loclabel($file, $pos, $label, $oplist, $p+1, $z+1, $f);
			else
				map5_loclabel($file, $pos, $label, $oplist, $p, $z, $f);
			continue;
		}

		$sz = 1;
		switch ( $by )
		{
			// 10 00 [var/2 var/4 var/6 lbu/8]
			// 11 00 [var/2 var/4 var/6 lbu/8]
			// 10 01
			// 11 01 xx xx
			case 0x10:
			case 0x11:
				$b1 = ord( $file[$pos+1] );
				$sz = 9;
				if ( $b1 !== 0 )
					$sz = ($by === 0x10) ? 2 : 4; // -9+b : -9+d
				break;
			case 0x57:
				$b1 = ord( $file[$pos+1] );
				$sz = 0xd;
				if ( $b1 === 0xf )  $sz = 2;
				break;
			case 0x73:
				$b1 = ord( $file[$pos+1] );
				$sz = 1;
				if ( $b1 === 0 )  $sz = 2;
				if ( $b1 === 1 )  $sz = 8;
				break;
			case 0xd4:
			case 0xfc:
				$b1 = ord( $file[$pos+1] );
				$sz = 1 + 4;
				if ( $b1 === 0xff || $b1 === 0xfe || $b1 === 0xfd || $b1 === 0xfb )
					$sz = 6;
				break;

			case 0xfe27:
				$b1 = ord( $file[$pos+2] );
				$sz = 2;
				if ( $b1 === 0 )  $sz = 4;
				break;
			case 0xfe5c:
				$b1 = ord( $file[$pos+2] );
				$sz = 2;
				if ( $b1 === 2 )  $sz = 4;
				break;
			case 0xfe77:
				$b1 = ord( $file[$pos+2] );
				$sz = 2;
				if ( $b1 === 1 )  $sz = 0xb;
				break;
			case 0xfeb0:
				$b1 = ord( $file[$pos+2] );
				$sz = 2;
				if ( $b1 !== 1 )  $sz = 6;
				break;
			case 0xfed4:
				$b1 = ord( $file[$pos+2] );
				$sz = 2;
				if ( $b1 === 1 )  $sz = 0xa;
				if ( $b1 === 3 )  $sz = 0xa;
				break;
			case 0xfedd:
				$b1 = ord( $file[$pos+2] );
				$sz = 2;
				if ( $b1 === 0 )  $sz = 6;
				if ( $b1 === 1 )  $sz = 6;
				break;

			default:
				if ( $by < 0x100 )
					$sz = $gp_op_siz[$by];
				else
					$sz = $gp_opfe_siz[$by & BIT8];

				if ( $sz < 1 )  $sz = 1;
				break;
		} // switch ( $by )

		if ( $by > BIT8 )
			$sz++;

		$oplist[$pos] = substr($file, $pos, $sz);
		$pos += $sz;
	} // while ( $pos < $len )

	return $pos;
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

function map5_labelrange( &$label )
{
	$min = BIT16;
	$max = 0;
	foreach ( $label as $k => $v )
	{
		if ( $v < $min )  $min = $v;
		if ( $v > $max )  $max = $v;
	}
	return array($min,$max);
}

function xeno_map5op( &$file )
{
	$cnt = str2int($file, 0x80, 2);

	$sub = substr($file, 0x84, $cnt*0x40);
	$pos = str2int($sub, 0, 2);
	$label = map5_label($sub);

	$sub = substr($file, 0x84 + $cnt*0x40);
	$len = strlen($sub);
	$oplist = array();
	map5_opcode($oplist, $sub, $label, $pos, $len);

	// to handle subfunc() before obj_0::init
	$range = map5_labelrange($label);
	if ( $range[0] !== $pos )
	{
		$res = map5_opcode($oplist, $sub, $label, $range[0], $pos);
		if ( $res !== $pos )
			php_warning('subfunc() before obj_0::init do not stop @ obj_0::init !');
		ksort($oplist);
	}

	// check for invalid labels
	foreach ( $label as $k => $v )
	{
		if ( ! isset($oplist[$v]) )
			php_warning('opcode not stop @ %4x [%s]', $v, trim($k));
	}

	// to print constants
	$range = map5_labelrange($label);
	if ( $range[0] > 0 && $sub[0] === "\xff" )
	{
		echo "constant:\n";
		$pos = 1;
		while ( $pos < $range[0] )
		{
			$s = substr($file, $pos, 7);
			printf("  %4x : %s\n", $pos, printhex($s));
			$pos += 7;
		} // while ( $pos < $range[0] )
	} // if ( $range[0] > 0 && $sub[0] === "\xff" )

	// to print opcodes with labels
	foreach ( $oplist as $ok => $ov )
	{
		$lab = map5_haslabels($label, $ok);
		if ( ! empty($lab) )
		{
			foreach ( $lab as $lv )
				printf("%s:\n", $lv);
		}
		printf("      %4x : %s\n", $ok, printhex($ov));
	} // foreach ( $oplist as $ok => $ov )

	return;
}

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	ob_start();
	xeno_map5op($file);
	$txt = ob_get_clean();

	file_put_contents("$fname.txt", $txt);
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
==============================
lahan 0608.5
break = 800a14ec  800a23f4

init_0     4e bc  4f 2a  50 e7  57 75  5a 2  64 a0  6b 9d  6f -
loop_0     70 fe 8e  76 5b

init_1     9a 16  9d fe d  a1 -
loop_1     a2  c
init_2    118 16  11b fe d  11f -
loop_2    120  c
init_3    123 16  126 fe d  12a -
loop_3    12b a7
init_4    12e 16  131 fe d  135 -
loop_4    136 a7
init_5    139 16  13c fe d  140 -
loop_5    141 a7
init_6    144 16  147 fe d  14b -
loop_6    14c a7
init_7    14f 16  152 fe d  156 -
loop_7    157 a7
init_8    15a 16  15d fe d  161 -
loop_8    162 a7
init_9    165 16  168 fe d  16c -
loop_9    16d a7

init_10   170  b   173 20  176 1d  17d 23  17e -
loop_10   17f 26
init_11   1b2  b   1b5 23  1b6 20  1b9 -
loop_11   1ba 26
init_12   1da  b   1dd 20  1e0 1c  1e4 23  1e5 18  1ea -
loop_12   1eb 26
init_13   209  b   20c 1d  213 20  216 18  21b 23  21c -
loop_13   21d 26

init_14   23b bc   23c f8  240 18  245 -
loop_14   246 5b
init_15   306 bc   307  -
loop_15   308 37   30b  2  313 2  31b 3c  31e bf  321 1  324 26  34f 26
init_16   437 bc   438  -
loop_16   439 26   483 26
init_17   56b bc   56c  -
loop_17   56d 26   5ce 26
init_18   69f bc   6a0  -
loop_18   6a1 26
init_19   7d3  b   7d6 1d  7dd 23  7de -
loop_19   7df  2   7e7  2  7ef 1  7f8 26  7fb 1  866 -  868 fe 13  86e 26
init_20   885 bc   886 2a  887 -
loop_20   888 37   88b 37  88e c6  88f a8  894 2  89c 8  89f 2  8a7 3c  8aa c1  8ad 26  8b0 1  8b9  2  8c1 c1  8c4 3c  8c7 5a  8c8 1
init_21   9d6 fe 15  9dc 1a  9de 5f  9e0 -
loop_21   9e1  -
init_22   9ff  b   a02 20  a05 21  a08 -
loop_22   a09  2   a11 fe 1  a13 1  a3e -
init_23   aca  b   acd 20  ad0 21  ad3 -
loop_23   ad4  2   adc 52
init_24   b95 fe 15  b9b 5f  b9d 19  ba3 1a  ba5 20  ba8 -
loop_24   ba9  -
init_25   bfa  b   bfd 21  c00 1f  c02 19  c08 1c  c0c fe 3  c10 23  c11 -
loop_25   c12 a8   c17  2  c1f 35  c25 6e  c2d 6d  c35 38  c3b 38  c41 4b
init_26   d03  b   d06 21  d09 1f  d0b 19  d11 1c  d15 fe 3  d19 23  d1a -
loop_26   d1b a8   d20  2  d28 35  d2e 6e  d36 6d  d3e 38  d44 38  d4a 4b
init_27   d9b  b   d9e 19  da4 20  da7 fe d  dab 17  dbd -
loop_27   dbe 59   dbf  -
init_28   f6d fe 15  f73 20  f76 37  f79 -
loop_28   f7a 59   f7b  -
init_29   fd6 fe 15  fdc 5f  fde -
loop_29   fdf 26
init_30  107d fe 15  1083 5f  1085 -
loop_30  1086 26
init_31  10b5  b  10b8 5f  10ba -
loop_31  10bb 26
init_32  1104 fe 15  110a 5f  110c -
loop_32  110d 26
init_33  119c fe 15  11a2 5f  11a4 37  11a7 -
loop_33  11a8  -
init_34  1202 fe 15  1208 5f  120a -
loop_34  120b  -
init_35  12a0 bc  12a1 18  12a6
loop_35  12a7  -
init_36  12ba bc  12bb 18  12c0 1c  12c4 -
loop_36  12c5  -
init_37  12ce  b  12d1 6a  12d4 1f  12d6 2  12e7 27  12e9 -
loop_37  12ea 26
init_38  1323 fe 15  1329 -
loop_38  132a a8  132f  2  1343 2  134b 4a  1374 26
init_39  13a1 fe 15  13a7 20  13aa 21  13ad -
loop_39  13ae a8  13b3  2  13bb 4a
init_40  14af  b  14b2 20  14b5 21  14b8 -
loop_40  14b9 53  14bd 26
init_41  14d9  b  14dc 5f  14de -
loop_41  14df a8  14e4  2  14ec 4a  14fa 2  1510 2  1526 2  152e 5f  1530 26
init_42  154f  b  1552 69  1555 19  155b -
loop_42  155c 26
init_43  1582  b  1585 69  1588 -
loop_43  1589 26
init_44  159a  b  159d 21  15a0 -
loop_44  15a1 a8  15a6  2  15ba 2  15ce 2  15e2 2  1611 2  1625 2  1648 2  1650 4a
init_45  1722  b  1725 1c  1729 23  172a -
loop_45  172b 26
init_46  173d  b  1740 5f  1742 -
loop_46  1743 26
init_47  1751  b  1754 5f  1756 -
loop_47  1757 26
init_48  1763 bc  1764 2a  1765 -
loop_48  1766 c6  1767 cb  177f 37  1782 cb  179a 37  179d cb  17b5 37  17b8 cb  17d0 37  17d3 -
init_49  17fa e6  1803 2a  1804 -
loop_49  1805  -
init_50  1806 2a  1807  -
loop_50  1808  a  180c  a  1810 a  1814 a  1818 a  181c a  1820 a  1824 a  1828 -

init_51  1859 46  185a -
loop_51  185a  -
init_52  1867 46  1868 -
loop_52  1868  -
init_53  1877 46  1878 -
loop_53  1878  -
init_54  1887 46  1888 -
loop_54  1888  -
init_55  1897 46  1898 -
loop_55  1898  -
init_56  18a7 46  18a8 -
loop_56  18a8  -
init_57  18b7 46  18b8 -
loop_57  18b8  -
init_58  18c7 46  18c8 -
loop_58  18c8  -

init_59  18dd 2a  18de 43  18e1 -
loop_59  18e2  2  18fb  2  1903 38  1909 c6  190a 35  1910 35  1916 38  191c 5  191f -
init_60  1921 2a  1922 43  1925 -
loop_60  1926 35  192c 38  1932 5  1935 -
init_61  1937 43  193a 2a  193b -
loop_61  193c 35  1942 38  1948 5  194b -
init_62  194d 43  1950 2a  1951 -
loop_62  1952 35  1958 38  195e 5  1961 -

sub  1963 35  1969 6d  1971 db  1976 35  197c 39  1982 6d  198a db  198f d

init_63  1990  -
loop_63  1990  -

d9b  27  dan
fd6  29  center merchant
==============================
00+1  01+3  02+8  05+3  08+3  0a+4  0b+3
16+3  17+18  18+5  19+6  1a+2  1c+4  1d+7  1f+2
20+3  21+3  23+1  26+3  27+2  2a+1
35+6  37+3  38+6  39+6  3c+3
43+3  46+1  4a+14
53+4  59+1  5a+1  5f+2
69+3  6a+3  6d+8  6e+8
73+3  75+3
9d+4
a0+7  a8+5
bc+1  bf+3
c1+3  c6+1  cb+24
db+5
e6+9  e7+7
f8+4

fe 01+2  03+4  0d+4
fe 13+6  15+6
fe 8e+6
==============================
== ./0746_340_5.dec.txt ==
     1 : opcode not stop @   16 [    loop_16]
     2 : opcode not stop @ fdda [    goto_fdda]
     3 : opcode not stop @ 1500 [  func_1500]
     4 : opcode not stop @   19 [    loop_19]
     5 : opcode not stop @ fea2 [    goto_fea2]
     6 : opcode not stop @  c00 [    loop_c00]
     7 : opcode not stop @ 18fe [    loop_18fe]
     8 : opcode not stop @  1a4 [    loop_1a4]

== ./1584_3c0_5.dec.txt ==
  1 : opcode not stop @   17 [obj_1::ontalk]
  2 : opcode not stop @   17 [obj_1::onpush]
==============================
*/
