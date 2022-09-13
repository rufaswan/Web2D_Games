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

function lunar_shift( &$bit, &$file, &$pos, $s )
{
	if ( count($bit) < $s )
		lunar_getbit($bit, $file, $pos, $s);

	while ( $s > 0 )
	{
		array_shift($bit);
		$s--;
	}
	return;
}

function lunar_getbit( &$bit, &$file, &$pos, $c )
{
	while ( count($bit) < $c )
	{
		$b1 = ord( $file[$pos+0] );
		$b2 = ord( $file[$pos+1] );
			$pos += 2;
		$b = $b1 | ($b2 << 8);

		for ( $i=0; $i < 16; $i++ )
		{
			$bit[] = $b & 1;
				$b >>= 1;
		} // for ( $i=0; $i < 16; $i++ )
	} // while ( count($bit) < $c )

	$int = 0;
	for ( $i=0; $i < $c; $i++ )
	{
		$b = $bit[$i];
		$int |= ($b << $i);
	} // for ( $i=0; $i < $c; $i++ )

	trace("%x [%d-bit] = %x\n", $pos, $c, $int);
	return $int;
}

function lunar_trace_bit( &$bit, $st )
{
	if ( defined('NO_TRACE') )
		return;

	$null = '';
	$cnt = count($bit);
	$val = lunar_getbit($bit, $null, $st, $cnt);
	printf("[%x] BIT %x = %x\n", $st, $cnt, $val);
	return;
}

function lunar_trace_dict( &$dict )
{
	if ( defined('NO_TRACE') )
		return;
	if ( empty($dict) )
		return;

	$max = 0;
	foreach ( $dict as $k => $v )
	{
		if ( $max < $k )
			$max = $k;
	}

	echo "DICT\n";
	for ( $i=0; $i <= $max; $i += 4 )
	{
		for ( $j=0; $j < 4; $j++ )
		{
			$k = $i + $j;
			if ( ! isset($dict[$k]) )
				printf('%10s  ', ' ');
			else
			{
				if ( "{$dict[$k][2]}" === $dict[$k][2] )
					$hex = $dict[$k][2];
				else
					$hex = sprintf('%x', $dict[$k][2]);
				printf('%2x %2x %-4s  ', $dict[$k][0], $dict[$k][1], $hex);
			}
		}
		echo "\n";
	} // for ( $k=0; $k <= $max; $k += 4 )
	return;
}
//////////////////////////////
function lunar_seed1( &$bit, &$file, &$st, $len )
{
	trace("== lunar_seed1( %x , %x , %x )\n", count($bit), $st, $len);
	$v0 = array();
	for ( $i=0; $i < $len; $i++ )
	{
		$v1 = lunar_getbit($bit, $file, $st, 3);
		lunar_shift($bit, $file, $st, 3);
		$v0[$i] = $v1;
	}
	while( count($v0) < 0x13 )
		$v0[] = 0;

	// RAM 800ad888
	//   10 11 12 -  8 7 9 6  a 5 b 4  c 3 d 2  e 1 f
	$v1 = array(16,17,18,0 , 8,7,9,6 , 10,5,11,4 , 12,3,13,2 , 14,1,15);
	$seed = array();
	foreach ( $v0 as $k => $v )
		$seed[ $v1[$k] ] = $v;
	ksort($seed);

	//print_r($seed);
	return $seed;
}

function lunar_seed2( &$bit, &$file, &$st, &$dict, $len, $sp514 )
{
	trace("== lunar_seed2( %x , %x , %x , %x )\n", count($bit), $st, $len, $sp514);
	$a3 = 0;
	$seed = array();

	while ( $len > 0 )
	{
		// READ X-bits but RIGHT-SHIFT Y-bits only
		$v0 = lunar_getbit($bit, $file, $st, $sp514);

		$dt = $dict[$v0];
		lunar_shift($bit, $file, $st, $dt[1]);

		// copy
		if ( $dt[2] < 0x10 )
		{
			$a3 = $dt[2];
			$seed[] = $dt[2];
			$len--;
			continue;
		}

		// duplicate
		if ( $dt[2] === 0x10 )
		{
			$v0 = lunar_getbit($bit, $file, $st, 2);
			lunar_shift($bit, $file, $st, 2);
			$v1 = $v0 + 2;
			while ( $v1 >= 0 )
			{
				$seed[] = $a3;
				$v1--;
				$len--;
			} // while ( $v1 > 0 )
			continue;
		}

		// dummy
		if ( $dt[2] === 0x11 )
		{
			$v0 = lunar_getbit($bit, $file, $st, 3);
			lunar_shift($bit, $file, $st, 3);
			$v1 = $v0 + 2;
		}
		else // 11+
		{
			$v0 = lunar_getbit($bit, $file, $st, 7);
			lunar_shift($bit, $file, $st, 7);
			$v1 = $v0 + 0xa;
		}

		while ( $v1 >= 0 )
		{
			$seed[] = 0;
			$v1--;
			$len--;
		} // while ( $v1 > 0 )
		$a3 = 0;
	} // while ( $len > 0 )

	//print_r($seed);
	return $seed;
}
//////////////////////////////
function lunar_off( $off, $half )
{
	if ( ($off & $half) === 0 )
		return ($off ^ $half);

	while (1)
	{
		$off   ^= $half;
		$half >>= 1;
		if ( ($off & $half) === 0 )
			return ($off ^ $half);
	}
	return 0;
}

function lunar_val100( $set, $id )
{
	$CONST = array(
		0 => array(
			// RAM 800ad8dc
			0 => array(
				0,0,0,0 , 0, 0, 0,0 ,
				1,1,1,1 , 2, 2, 2,2 ,
				3,3,3,3 , 4, 4, 4,4 ,
				5,5,5,5 , 0,99,99,0 ,
			),
			// RAM 800ad89c
			//   3  +1  +1  +1   +1  +1  +1  +1
			//  +1  +2  +2  +2   +2  +4  +4  +4
			//  +4  +8  +8  +8   +8 +10 +10 +10
			// +10 +20 +20 +20  +20
			4 => array(
				    3,   4,   5,   6 ,     7,   8,   9, 0xa ,
				  0xb, 0xd, 0xf,0x11 ,  0x13,0x17,0x1b,0x1f ,
				 0x23,0x2b,0x33,0x3b ,  0x43,0x53,0x63,0x73 ,
				 0x83,0xa3,0xc3,0xe3 , 0x102,   0,   0,   0 ,
			),
		),
		1 => array(
			// RAM 800ad958
			0 => array(
				 0, 0, 0, 0 ,  1, 1, 2, 2 ,
				 3, 3, 4, 4 ,  5, 5, 6, 6 ,
				 7, 7, 8, 8 ,  9, 9,10,10 ,
				11,11,12,12 , 13,13,
			),
			// RAM 800ad91c
			//    1   +1   +1    +1     +1    +2   +2   +4
			//   +4   +8   +8   +10    +10   +20  +20  +40
			//  +40  +80  +80  +100   +100  +200 +200 +400
			// +400 +800 +800 +1000  +1000 +2000
			4 => array(
				     1,     2,     3,     4 ,      5,     7,    9,  0xd,
				  0x11,  0x19,  0x21,  0x31 ,   0x41,  0x61, 0x81, 0xc1,
				 0x101, 0x181, 0x201, 0x301 ,  0x401, 0x601,0x801,0xc01,
				0x1001,0x1801,0x2001,0x3001 , 0x4001,0x6001,
			),
		),
	);

	if ( ! isset($CONST[$set]) )
		return -1;
	if ( ! isset($CONST[$set][0][$id]) )
		return -1;
	return array( $CONST[$set][0][$id], $CONST[$set][4][$id] );
}

function lunar_dten( $k, $v, $set, $fp )
{
	// Dict 2
	//    00-ff   10,byte
	//   100      0f,*end*
	//   101-11f  bit,LEN pair [bit=0-5,63]
	// Dict 3
	//    00-1f   bit,POS pair [bit=0-d]
	if ( $v < $fp )
	{
		$b0 = ( $v < 0x100 ) ? 0x10 : 0xf;
		$dt = array($b0,$k,$v);
	}
	else
	{
		list($b0,$b4) = lunar_val100($set, $v-$fp);
		$dt = array($b0,$k,$b4);
	}
	return $dt;
}

function lunar_addptr( &$dict, $pos, $blk, $off2, $sp514 )
{
	if ( isset($dict[$pos]) )
		return;
	$max = 1 << $sp514;
	if ( $pos >= $max )
		return;

	$bit = 0;
	while ( $blk > 1 )
	{
		$blk >>= 1;
		$bit++;
	}
	$dict[$pos] = array(0x10+$bit , $sp514 , $off2);
	return;
}

function lunar_dict( &$seed, $fp, $set, &$sp514 )
{
	// sub_80024378(a0, a1, a2, a3, sp10, sp14, sp18)
	//   sp+20    , 13     ,  13 ,        0     ,        0     , sp+510 , sp+514=7
	//   sp+20    , s2+101 , 101 , 800ad89c[20] , 800ad8dc[20] , sp+510 , sp+514=gp+468=9
	//   sp+20+s2 , s3+1   ,   0 , 800ad91c[1e] , 800ad958[1e] , sp+518 , sp+51c=gp+46c=6
	// 1 = 800b8328 - 800b8728 = 400 / 8 =  80
	// 2 = 800b8328 - 800b9320 = ff8 / 8 = 1ff
	// 3 = 800b - 800b = 400 / 8 =
	trace("== begin sub_80024378\n");

	$count = array();
	$min = count($seed);
	$max = 0;
	foreach ( $seed as $k => $v )
	{
		if ( ! isset($count[$v]) )
			$count[$v] = array();
		if ( $v === 0 )
			continue;

		$count[$v][] = $k;
		if ( $v < $min )  $min = $v;
		if ( $v > $max )  $max = $v;
	} // foreach ( $seed as $k => $v )

	if ( $sp514 < $min )  $sp514 = $min;
	if ( $sp514 > $max )  $sp514 = $max;
	trace("min %x max %x sp514 %x\n", $min, $max, $sp514);

	$dict = array();
	$max  = (1 << $sp514);

	// Master Dictionary
	$ed   = $max;
	$off1 = 0;
	for ( $i = $min; $i <= $sp514; $i++ )
	{
		if ( ! isset($count[$i]) )
			continue;

		$blk = 1 << $i;
		foreach ( $count[$i] as $v )
		{
			$dt = lunar_dten($i, $v, $set, $fp);

			$st = $off1;
			$off1 = lunar_off($off1, $blk >> 1);

			trace("  %x,%x  %x + %x = %x %x %x\n", $i, $v, $st, $blk, $dt[0], $dt[1], $dt[2]);
			while ( $st < $ed )
			{
				$dict[$st] = $dt;
				$st += $blk;
			}
		} // foreach ( $count[$i] as $v )
	} // for ( $i = $min; $i <= $sp514; $i++ )

	// Mini Dictionaries
	//  flatten the array for looping
	$buf = array();
	for ( $i = ($sp514 + 1); $i <= $max; $i++ )
	{
		if ( ! isset($count[$i]) )
			continue;

		$k = $i - $sp514;
		$blk = 1 << $k;
		foreach ( $count[$i] as $v )
		{
			$dt = lunar_dten($k, $v, $set, $fp);
			$b1 = $off1;
			$off1 = lunar_off($off1, 1 << ($i-1));

			trace("  %x,%x = %x %x %x , %x , %x\n", $i, $v, $dt[0], $dt[1], $dt[2], $b1, $blk);
			$buf[] = array($dt,$b1,$blk);
		} // foreach ( $count[$i] as $v )
	} // for ( $i = ($sp514 + 1); $i <= $max; $i++ )

	// Generate group of Mini-Dictionaries
	// and add pointer to Master Dictionary
	$off2 = $max + 4;
	while ( ! empty($buf) )
	{
		$count = array();

		$en = array_shift($buf);
		$blk = $en[2];

		$count[] = $en;
		$c = 1;

		// get entries for a block
		// when mixed block sizes, use the largest
		// and then fill up accordingly
		while (1)
		{
			if ( empty($buf) )
				break;
			if ( $c === $blk )
				break;
			$en = array_shift($buf);
			while ( $blk !== $en[2] )
			{
				$blk <<= 1;
				$c   <<= 1; // smaller blocks = repeat
			}

			$count[] = $en;
			$c++;
		} // while (1)

		foreach ( $count as $v )
		{
			$st = 0;
			lunar_addptr($dict, $v[1], $blk, $off2, $sp514);
			while ( $st < $blk )
			{
				$p = $off2 + $st + ($v[1] >> $sp514);
				$dict[$p] = $v[0];
				$st += $v[2];
			}
		} // foreach ( $count as $v )

		$off2 += ($blk + 1);
	} // while ( ! empty($buf) )

	lunar_trace_dict($dict);
	trace("== end sub_80024378\n");
	return $dict;
}
//////////////////////////////
function lunar_data( &$dec, &$bit, &$file, &$st, &$dict2, &$dict3, $sp514, $sp51c )
{
	trace("== begin sub_800248a4\n");

	while (1)
	{
		// get entry from dict2
		// 11+   mini/sub-dictionary pointer
		// 10    output byte
		//  f    *end*
		//  0-e  copy output from POS by LEN w/dict3
		$v0 = lunar_getbit($bit, $file, $st, $sp514);
		$dt2 = $dict2[$v0];
		lunar_shift($bit, $file, $st, $dt2[1]);
		trace("dict2[%x] = %x %x %x\n", $v0, $dt2[0], $dt2[1], $dt2[2]);

		while ( $dt2[0] > 0x10 )
		{
			$v0 = lunar_getbit($bit, $file, $st, $dt2[0]-0x10);
			$v1 = $v0 + $dt2[2];

			$dt2 = $dict2[$v1];
			lunar_shift($bit, $file, $st, $dt2[1]);
			trace("dict2[%x] = %x %x %x\n", $v0, $dt2[0], $dt2[1], $dt2[2]);
		} // while ( $dt2[0] > 0x10 )

		if ( $dt2[0] === 0x10 )
		{
			$v1  = $dt2[2] & BIT8;
			trace("  dec[%x] = %x\n", strlen($dec), $v1);
			$dec .= chr($v1);
			continue;
		}

		if ( $dt2[0] === 0xf )
			break;

		// s2 00-0e
		$v0 = lunar_getbit($bit, $file, $st, $dt2[0]);
		lunar_shift($bit, $file, $st, $dt2[0]);
		$dlen = $v0 + $dt2[2];

			// get entry from dict3
			// 11+    mini/sub-dictionary pointer
			//  0-10  copy output from POS by LEN w/dict3
			$v0 = lunar_getbit($bit, $file, $st, $sp51c);
			$dt3 = $dict3[$v0];
			lunar_shift($bit, $file, $st, $dt3[1]);
			trace("dict3[%x] = %x %x %x\n", $v0, $dt3[0], $dt3[1], $dt3[2]);

			while ( $dt3[0] > 0x10 )
			{
				$v0 = lunar_getbit($bit, $file, $st, $dt3[0]-0x10);
				$v1 = $v0 + $dt3[2];

				$dt3 = $dict3[$v1];
				lunar_shift($bit, $file, $st, $dt3[1]);
				trace("dict3[%x] = %x %x %x\n", $v0, $dt3[0], $dt3[1], $dt3[2]);
			} // while ( $dt3[0] > 0x10 )

			// s3 00-10
			$v0 = lunar_getbit($bit, $file, $st, $dt3[0]);
			lunar_shift($bit, $file, $st, $dt3[0]);
			$dpos = $v0 + $dt3[2];

		trace("  dec[%x] = [%x+%x] , %x\n", strlen($dec), $v0, $dt3[2], $dlen);
		$dp = strlen($dec) - $dpos;
		for ( $i=0; $i < $dlen; $i++ )
			$dec .= $dec[$dp+$i];
	} // while (1)

	trace("== end sub_800248a4\n");
	return;
}

function lunar_decode( &$file )
{
	$last = 0;
	$st = 0;

	$dec = '';
	$bit = array();
	while ( $last === 0 )
	{
		trace("== begin sub_80023e88\n");
		lunar_trace_bit($bit, $st);

		$last = lunar_getbit($bit, $file, $st, 1); // s4
		lunar_shift($bit, $file, $st, 1);
		lunar_shift($bit, $file, $st, 2); // skipped

		$v0 = lunar_getbit($bit, $file, $st, 5);
		lunar_shift($bit, $file, $st, 5);
			$s2 = $v0 + 0x101; // s2
		$v0 = lunar_getbit($bit, $file, $st, 5);
		lunar_shift($bit, $file, $st, 5);
			$s3 = $v0 + 1; // s3
		$v0 = lunar_getbit($bit, $file, $st, 4);
		lunar_shift($bit, $file, $st, 4);
			$a1 = $v0 + 4; // a1

		trace("s4 %x s2 %x s3 %x a1 %x\n", $last, $s2, $s3, $a1);

		$sp514 = 7;
		$seed = lunar_seed1($bit, $file, $st, $a1); // max f + 4 = 13
		$dict = lunar_dict ($seed, 0x13, -1, $sp514);
		lunar_trace_bit($bit, $st);

		$seed = lunar_seed2($bit, $file, $st, $dict, $s2+$s3, $sp514);
		$s2 = array_splice($seed, 0, $s2); // max 1f + 101 = 120
		$s3 = array_splice($seed, 0, $s3); // max 1f +   1 =  20
		trace("s2 %x s3 %x\n", count($s2), count($s3));

		$sp514 = 9;
		$dict2 = lunar_dict($s2, 0x101, 0, $sp514);

		$sp51c = 6;
		$dict3 = lunar_dict($s3, 0, 1, $sp51c);

		lunar_trace_bit($bit, $st);
		lunar_data($dec, $bit, $file, $st, $dict2, $dict3, $sp514, $sp51c);
		trace("DEC %x\n", strlen($dec));

		trace("== end sub_80023e88\n");
	} // while ( $last === 0 )
	return $dec;
}

/*
gp = 800b598c
468(gp) == 800b5df4 = 9
46c(gp) == 800b5df8 = 6
1304(gp) == 800b6c90 == bits data
1308(gp) == 800b6c94 == bits data count
130c(gp) == 800b6c98
1310(gp) == 800b6c9c == ram data offset
1314(gp) == 800b6ca0 == RAM 800b8320 / dictionary

lu1.bmp
	-   80023e98  80024398[s0,s1]  800248dc
	10  156008  0     -  156012  f  4906  156090  8    c1
	11  158908  8    ac  158910  7    1a  158986  0     -
	12  15b4da  d  1d8c  15b4e2  f  43d5  15b556  d   3ee
	13  15de36  4     d  15de3e  6    18  15debc  e  3ec4

	20  160470  0     -  16047a  f  4491  1604f4  c   bcc
	21  162d5c  b   574  162d64  d  1648  162dd8  b   72c
	22  1659ae  c   d7c  1659b6  e  2948  165a20  a     b
	23  1688b6  b   56d  1688bc  0     -  168902  a   27b

	sp-538 = 801ff920 = psp
	sp-588 = 801ff398 = csp

	psp-510 = Dictionary S2
	psp-514 = 468(gp) = (1 << n) for master , rest minis
	psp-518 = Dictionary S3
	psp-51c = 46c(gp) = (1 << n) for master , rest minis
	//////////////////////////////
	DECODING sub_80023e88
		1+2+5    ac        [1-1- 1] [1-] [-]
		5+4+3*5  9c 0d 70  [-11] [1 --] [-- -] [---] [11-] [1 1--] [1 11--]
		3*8      1b e7 79  [-11] [1 1-] [-1 1] [11-] [-11] [1 --] [-1 1] [-11]
		3*3      e7 0d     [1 11] [1- -] [111]
		-        92        1--1 --1- ---- 11-
			= 4906

		s4  [1-bit]  -     = 0
		[2-bit]      1-    = 2
		s2  [5-bit]  1-1-1 = 15 + 101 = 116 [min 101 max 120]
		s3  [5-bit]  111-- = 1c +   1 =  1d [min   1 max  20]
		a1  [4-bit]  11--  = c  +   4 =  10 [min   4 max  13]
		for a1*[3-bit]
			11- --- --- 1-- -11 -11 -11 1-- -11 11- -11 11- -11 111 1-- 111
			= 6 - - 4  3 3 3 4  3 6 3 6  3 7 4 7  - - -

		re-arrange  800ad888  10 11 12 -  8 7 9 6  a 5 b 4  c 3 d 2  e 1 f
		psp+20 = 4 - 7 7  6 6 4 3  3 3 3 3  3 4 - -  6 - -

		[80024078  80024080]
		jal sub_80024378( psp+20 , 13 , 13 , NULL , NULL , psp+510 , 7 )
			by key
				0  1 e f 11 12 [skip]
				1
				2
				3  7 8 9 a b c
				4  0 6 d
				5
				6  4 5 10
				7  2 3
			psp+510 = 1314(gp) + 8
			min  = 3
			max  = 7
			if ( psp+514 < min )  psp+514 = min
			if ( psp+514 > max )  psp+514 = max
			size = 1 << 7 = 80

			[80024780] t4 + (t2 >> t3) , s2 << (s1-1) = v0,v1
				b8328 + ( 0 >> 0) , 1 << (3-1) = 3,7
				b8328 + ( 4 >> 0) , 1 << (3-1) = 3,8
				b8328 + ( 2 >> 0) , 1 << (3-1) = 3,9
				b8328 + ( 6 >> 0) , 1 << (3-1) = 3,a
				b8328 + ( 1 >> 0) , 1 << (3-1) = 3,b
				b8328 + ( 5 >> 0) , 1 << (3-1) = 3,c
				b8328 + ( 3 >> 0) , 1 << (4-1) = 4,0
				b8328 + ( b >> 0) , 1 << (4-1) = 4,6
				b8328 + ( 7 >> 0) , 1 << (4-1) = 4,d
				b8328 + ( f >> 0) , 1 << (6-1) = 6,4
				b8328 + (2f >> 0) , 1 << (6-1) = 6,5
				b8328 + (1f >> 0) , 1 << (6-1) = 6,10
				b8328 + (3f >> 0) , 1 << (7-1) = 7,2
				b8328 + (7f >> 0) , 1 << (7-1) = 7,3
			3 3 3 4 3 3 3 4   7 b 9 - 8 c a d
			3 3 3 4 3 3 3 6   7 b 9 6 8 c a 4
			3 3 3 4 3 3 3 4   7 b 9 - 8 c a d
			3 3 3 4 3 3 3 6   7 b 9 6 8 c a 10
			3 3 3 4 3 3 3 4   7 b 9 - 8 c a d
			3 3 3 4 3 3 3 6   7 b 9 6 8 c a 5
			3 3 3 4 3 3 3 4   7 b 9 - 8 c a d
			3 3 3 4 3 3 3 7   7 b 9 6 8 c a 2
			3 3 3 4 3 3 3 4   7 b 9 - 8 c a d
			3 3 3 4 3 3 3 6   7 b 9 6 8 c a 4
			3 3 3 4 3 3 3 4   7 b 9 - 8 c a d
			3 3 3 4 3 3 3 6   7 b 9 6 8 c a 10
			3 3 3 4 3 3 3 4   7 b 9 - 8 c a d
			3 3 3 4 3 3 3 6   7 b 9 6 8 c a 5
			3 3 3 4 3 3 3 4   7 b 9 - 8 c a d
			3 3 3 4 3 3 3 7   7 b 9 6 8 c a 3

		len = s2[116] + s3[1d] = 133
		sp514 = 7

		4906   [1--] [1 --] [1- -][---] [11-]
		20 28  --1- [1--] [- --] [1- -] [---]
		...
		c1     11-- 1---
		for len
			[7-bit]  --- -11- =  6 , dict[ 6] = 3,a
			[7-bit]  -1- ---- = 20 , dict[20] = 3,7
			[7-bit]  -1- -1-- = 24 , dict[24] = 3,8
			[7-bit]  -1- -1-- = 24 , dict[24] = 3,8
			[7-bit]  --- -1-- =  4 , dict[ 4] = 3,8
			[7-bit]  -1- ---- = 20 , dict[20] = 3,7
			[7-bit]  --- -1-- =  4 , dict[ 4] = 3,8
			[7-bit]  -1- ---- = 20 , dict[20] = 3,7
			[7-bit]  --1 -1-- = 14 , dict[14] = 3,8
			...
		psp+20 = a 7 8 8 8 7 8 7 8 ...

		[800242d4  800242dc]
		jal sub_80024378( psp+20 , s2 , 101 , k[20] , v[20] , dict_s2 , bit_s2=9 )
			[80024780] t4 + (t2 >> t3) , s2 << (s1-1) = v0,v1
				b8328 + (  0 >> 0) , 1 << (3-1) = 3,101
				b8328 + (  4 >> 0) , 1 << (4-1) = 4,102
				b8328 + (  c >> 0) , 1 << (5-1) = 5,103
				b8328 + ( 1c >> 0) , 1 << (5-1) = 5,104
				b8328 + (  2 >> 0) , 1 << (6-1) = 6,b
				b8328 + ( 22 >> 0) , 1 << (6-1) = 6,e
				b8328 + ( 12 >> 0) , 1 << (6-1) = 6,2b
				b8328 + ( 32 >> 0) , 1 << (6-1) = 6,31
				...
				b8328 + ( 97 >> 0) , 1 << (9-1) = 9,f1
				b8328 + (197 >> 0) , 1 << (9-1) = 9,f9
				b8328 + ( 57 >> 0) , 1 << (9-1) = 9,fa
				b8328 + (157 >> 0) , 1 << (9-1) = 9,fb
				b8328 + ( d7 >> 0) , 1 << (9-1) = 9,10a    <- end main dict
				b9330 + (1d7 >> 9) = 0 , 1 << (a-1) = 1,0
				b9330 + (3d7 >> 9) = 1 , 1 << (a-1) = 1,11
				b9348 + ( 37 >> 9) = 0 , 1 << (a-1) = 1,14
				b9348 + (237 >> 9) = 1 , 1 << (a-1) = 1,1e
				b9360 + (137 >> 9) = 0 , 1 << (a-1) = 1,22
				..
				b9720 + (3bf >> 9) = 1 , 1 << (b-1) = 2,fd
				b9720 + (7bf >> 9) = 3 , 1 << (b-1) = 2,10c
				b9748 + ( 7f >> 9) = 0 , 1 << (b-1) = 2,10d <- mixed 2,3
				b9748 + (47f >> 9) = 2 , 1 << (b-1) = 2,10f
				b9748 + (27f >> 9) = 1 , 1 << (c-1) = 3,15
				b9748 + (a7f >> 9) = 5 , 1 << (c-1) = 3,2d
				b9748 + (67f >> 9) = 3 , 1 << (c-1) = 3,39
				b9748 + (e7f >> 9) = 7 , 1 << (c-1) = 3,42  <- mixed 2,3
				b9790 + (17f >> 9) = 0 , 1 << (c-1) = 3,70
				b9790 + (97f >> 9) = 4 , 1 << (c-1) = 3,7b
				b9790 + (57f >> 9) = 2 , 1 << (c-1) = 3,88
				b9790 + (d7f >> 9) = 6 , 1 << (c-1) = 3,8a
				b9790 + (37f >> 9) = 1 , 1 << (c-1) = 3,8f
				b9790 + (b7f >> 9) = 5 , 1 << (c-1) = 3,a2
				b9790 + (77f >> 9) = 3 , 1 << (c-1) = 3,ba
				b9790 + (f7f >> 9) = 7 , 1 << (c-1) = 3,c1
				b97d8 + ( ff >> 9) = 0 , 1 << (c-1) = 3,ce

		[80024318  80024320]
		jal sub_80024378( psp+20+s2 , s3 , 0 , k[1e] , v[1e] , dict_s3 , bit_s3=6 )
			*refer above*

		[80024330  80024338]
		jal sub_800248a4( dict_s2 , dict_s3 , bit_s2 , bit_s3 )
			sp514/bit_s2 = 9
			sp51c/bit_s3 = 6

			c1 eb           [111- 1-11 1] [1-- ---1]
			ae bb 6e        [111- 1-11 1] [-] [11 1-1- 111] [-]
			03 3e 1b 6b 70  [---] [- -11- 1-1] [1 ---1 1-1] [1 -] [-11 111] [- --] [-- --11 -11] [-]
			b9 ee 77        [-1] [11 -111 111] [-] [111- 1-11 1] --1 -111
			9e              [--1 111-]
			=               1

			[9-bit] = 1 11-- ---1 = 1c1 = 10,7,10        = 10
			[9-bit] = 1 11-1 -111 = 1d7 = 11,9,@b9330/201
				11-10 [1-bit] = - = 0+@201 = 10,1,0      =  0
			[9-bit] = 1 11-1 -111 = 1d7 = 11,9,@b9330/201
				11-10 [1-bit] = - = 0+@201 = 10,1,0      =  0
			[9-bit] = 1 11-1 -111 = 1d7 = 11,9,@b9330/201
				11-10 [1-bit] = - = 0+@201 = 10,1,0      =  0
			[9-bit] = - ---1 1-11 =  1b = 10,9,9         =  9

			[9-bit] = - 1111 1--- = f8 = 0,3,3
				[0-bit] = 0
				[6-bit] = -1 1111 = 1f  = 12,6,@b9b38/4a
					12-10 [2-bit] = 1- = 2+@4a = 0,2,4
						[0-bit] = 0
				LEN 0+3 , POS 0+4                        = pos -4 len 3 = 0 0 0

			[9-bit] = 1 1--- 11-1 = 18d = 10,8,c         =  c
			[9-bit] = - --11 -1-1 =  35 = 10,8,2         =  2

			[111- 1-11 1] [-] [-1 -111] [---]
			[9-bit] = - 1-11 1--- =  b8 = 0,3,3
				[0-bit] = 0
				[6-bit] = -1 -111 = 17 = 1,6,5
					[1-bit] = - = 0
				LEN 0+3 , POS 0+5                        = pos -5 len 3 = 0 0 0

			[9-bit] = 1 11-1 -111 = 1d7 = 11,9,@b9330/201
				11-10 [1-bit] = - = 0+@201 = 10,1,0      =  0
			[9-bit] = 1 1-11 1111 = 1bf = 12,9,@b9720/27f
				12-10 [2-bit] = -1 = 1+@27f = 10,2,fd    = fd
			[9-bit] = 1 1--1 111- = 19e = 10,7,1         =  1
 */
//////////////////////////////
function lunar_getbit2( &$bit, &$file, &$st )
{
	if ( empty($bit) )
	{
		$b = ord( $file[$st] );
			$st++;

		$i = 8;
		while ( $i > 0 )
		{
			$bit[] = $b & 1;
			$b >>= 1;
			$i--;
		}
	}
	return array_shift($bit);
}

function lunar_decode2( &$file )
{
	trace("== begin sub_80024c60\n");
	$dec = '';

	$bit = array();

	$ed = strlen($file);
	$st = 0;
	while ( $st < $ed )
	{
		$flg = lunar_getbit2($bit, $file, $st);
		if ( $flg )
		{
			$b = $file[$st];
				$st++;
			$dec .= $b;
		}
		else
		{
			$flg = lunar_getbit2($bit, $file, $st);
			if ( $flg )
			{
				// 7654 3210
				// llpp pppp
				$b = ord( $file[$st] );
					$st++;
				$dlen = ($b >> 6);
				$dpos = ($b & 0x3f) - 0x40;
			}
			else
			{
				$b1 = ord( $file[$st+0] );
				$b2 = ord( $file[$st+1] );
					$st += 2;

				// fedc ba98 7654 3210
				// llll pppp pppp pppp
				$b = ($b1 << 8) | $b2;
				$dlen = ($b >> 12);
				$dpos = ($b & 0xfff) - 0x1000;

				if ( $dlen === 0 )
				{
					$dlen = ord( $file[$st] );
						$st++;
					if ( $dlen === 0 )
						goto done;
				}
			}
			$dlen += 2;

			$dp = strlen($dec) + $dpos;
			for ( $i=0; $i < $dlen; $i++ )
				$dec .= $dec[$dp+$i];
		}
	} // while ( $st < $ed )

done:
	trace("== end sub_80024c60\n");
	return $dec;
}
//////////////////////////////
