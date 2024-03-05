<?php
require 'common.inc';
require 'common-zlib.inc';

function zlib0( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$zlib = array(
		'raw'     => ZLIB_ENCODING_RAW,
		'gzip'    => ZLIB_ENCODING_GZIP,
		'deflate' => ZLIB_ENCODING_DEFLATE,
	);
	foreach ( $zlib as $zk => $zv )
	{
		for ( $i=0; $i <= 9; $i++ )
		{
			$enc = zlib_encode($file, $zv, $i);
			$fn  = sprintf('%s.%s.%d', $fname, $zk, $i);
			save_file($fn, $enc);
		} // for ( $i=0; $i <= 9; $i++ )
	} // foreach ( $zlib as $zk => $zv )

	return;
}

for ( $i=1; $i < $argc; $i++ )
	zlib0( $argv[$i] );

/*
DATA = a\n == "\x61\x0a" == -11- ---1   ---- 1-1-
ZLIB_ENCODING_RAW
	0 =  1  2 -- fd   ff 61  a
	1 = 4b e4  2 --
	2 = 4b e4  2 --
	3 = 4b e4  2 --
	4 = 4b e4  2 --
	5 = 4b e4  2 --
	6 = 4b e4  2 --
	7 = 4b e4  2 --
	8 = 4b e4  2 --
	9 = 4b e4  2 --
ZLIB_ENCODING_DEFLATE
	0 = 78  1  1  2   -- fd ff 61    a -- ce --   6c
	1 = 78  1 4b e4    2 -- -- ce   -- 6c
	2 = 78 5e 4b e4    2 -- -- ce   -- 6c
	3 = 78 5e 4b e4    2 -- -- ce   -- 6c
	4 = 78 5e 4b e4    2 -- -- ce   -- 6c
	5 = 78 5e 4b e4    2 -- -- ce   -- 6c
	6 = 78 9c 4b e4    2 -- -- ce   -- 6c
	7 = 78 da 4b e4    2 -- -- ce   -- 6c
	8 = 78 da 4b e4    2 -- -- ce   -- 6c
	9 = 78 da 4b e4    2 -- -- ce   -- 6c
ZLIB_ENCODING_GZIP
	0 = 1f 8b  8 --   -- -- -- --    4  3  1  2   -- fd ff 61    a  7 a1 ea   dd  2 -- --   --
	1 = 1f 8b  8 --   -- -- -- --    4  3 4b e4    2 --  7 a1   ea dd  2 --   -- --
	2 = 1f 8b  8 --   -- -- -- --   --  3 4b e4    2 --  7 a1   ea dd  2 --   -- --
	3 = 1f 8b  8 --   -- -- -- --   --  3 4b e4    2 --  7 a1   ea dd  2 --   -- --
	4 = 1f 8b  8 --   -- -- -- --   --  3 4b e4    2 --  7 a1   ea dd  2 --   -- --
	5 = 1f 8b  8 --   -- -- -- --   --  3 4b e4    2 --  7 a1   ea dd  2 --   -- --
	6 = 1f 8b  8 --   -- -- -- --   --  3 4b e4    2 --  7 a1   ea dd  2 --   -- --
	7 = 1f 8b  8 --   -- -- -- --   --  3 4b e4    2 --  7 a1   ea dd  2 --   -- --
	8 = 1f 8b  8 --   -- -- -- --   --  3 4b e4    2 --  7 a1   ea dd  2 --   -- --
	9 = 1f 8b  8 --   -- -- -- --    2  3 4b e4    2 --  7 a1   ea dd  2 --   -- --

zlib-1.2.11/inflate.c
HEADER
	if ( lhu/0 == 1f8b ) // is gzip
		if ( lbu/2 !== 08 ) // 08 = z_deflated
			return -1

		// https://www.rfc-editor.org/rfc/rfc1952
		// 01  is ascii text
		// 02  header crc
		// 04  extra field
		// 08  original file name
		// 10  comment
		// 20 40 80 = reserved
		flags = lbu/3
		if ( flags & e0 )
			return -1
		mtime = lw/4
		os_flag = lbu/8  // 2=slowest compression , 4=fastest compression , 0=default
		os_os   = lbu/9  // 0=dos , 3=unix , 7=mac , b=ntfs , ff=unknown
		p += a

		// optional fields
			if ( flags & 04 ) // extra
				exlen = lhu/0
				ex    = sub/2, exlen
				p += 2 + exlen
			if ( flags & 08 ) // name
				name = sub0/0
				p += len(name) + 1
			if ( flags & 10 ) // comment
				comm = sub0/0
				p += len(comm) + 1
			if ( flags & 02 ) // header crc
				crc = lhu/0
				p += 2

	else // is zlib
		flag = lbu/0
		if ( (flag & f) !== 8 ) // 8 = z_deflated
			return -1
		zlen = (flag >> 4) + 8
		if ( zlen > 15 ) // dictionary window size
			return -1

		// https://www.rfc-editor.org/rfc/rfc1950
		//  & 1f  MSB check , ( ((lbu/1 << 8) | lbu/0) % 1f ) == 0
		//                    % 20 SAME AS & 1f , NOT SAME AS % 1f
		//  & 20  has dictionary
		//  & c0  0=fastest , 40=fast , 80=default , c0=slowest
		flag = lbu/1

DECODE
	last = getbit 1
	type = getbit 2
	switch type
		case 0 // STORED
			alignbit
			if ( lhu/0 != (lhu/2 ^ ffff) )
				return -1
			out .= sub/4, lhu/0
			p += 4 + lhu/0
			break
		case 1 // FIXED
			break
		case 2 // TABLE
			nlen  = getbit 5 + 257
			ndist = getbit 5 + 1
			ncode = getbit 4 + 4
			if ( nlen > 286 || ndist > 30 )
				return -1
			break
		case 3 // INVALID
			return -1


DATA = 61  a
	-11- ---1
	---- 1-1-

ENC  = 4b e4  2 --
	-1-- 1  [-1=type]  [1=last]
	111- -1--
	---- --1-
	---- ----
 */
