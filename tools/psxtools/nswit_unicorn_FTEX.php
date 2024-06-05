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
 *
 * Special Thanks
 *   Tegra X1 block linear swizzling algorithm
 *   https://github.com/ScanMountGoat/tegra_swizzle/tegra_swizzle/src/swizzle.rs
 */
require 'common.inc';
require 'common-guest.inc';
require 'class-bptc.inc';
require 'class-s3tc.inc';

//define('DRY_RUN', true);

// Tegra TRM page 1188
// gob_offset
//     ((x % 64) / 32) * 256  | ((x & 3f) >> 5) << 8 = --x. .... << 3 = ---x ---- ----
//   + ((y %  8) /  2) * 64   | ((y &  7) >> 1) << 6 = ---- -yy. << 5 = ---- yy-- ----
//   + ((x % 32) / 16) * 32   | ((x & 1f) >> 4) << 5 = ---x .... << 1 = ---- --x- ----
//   +  (y %  2) * 16         |  (y &  1)       << 4 = ---- ---y << 4 = ---- ---y ----
//   +  (x % 16)              |  (x &  f)                             = ---- ---- xxxx
//
// bit pattern = ---x yyxy xxxx
//   rgba/ 4 bytes = bit      , x/12f y/d0
//    bc4/ 8 bytes = bit >> 3 , x/25  y/1a
//    bc7/16 bytes = bit >> 4 , x/12  y/d

function tegra_x1_swizzled_8_bits( &$pix, $ow, $oh )
{
	printf("== tegra_x1_swizzled_8_bits( %x , %x )\n", $ow, $oh);

	// 1 pixel = 4*4 tiles = 10 bytes
	// unswizzled tiles
	//    0  1  4  5 20 21 24 25 100 101 104 105 120 121 124 125 200 201 204 205 220 221 224 225 300 301 304 305 320 321 324 325
	//    2  3  6  7 22 23 26 27 102 103 106 107 122 123 126 127 202 203 206 207 222 223 226 227 302 303 306 307 322 323 326 327
	//    8  9  c  d 28 29 2c 2d 108 109 10c 10d 128 129 12c 12d 208 209 20c 20d 228 229 22c 22d 308 309 30c 30d 328 329 32c 32d
	//    a  b  e  f 2a 2b 2e 2f 10a 10b 10e 10f 12a 12b 12e 12f 20a 20b 20e 20f 22a 22b 22e 22f 30a 30b 30e 30f 32a 32b 32e 32f
	//   10 11 14 15 30 31 34 35 110 111 114 115 130 131 134 135 210 211 214 215 230 231 234 235 310 311 314 315 330 331 334 335
	//   12 13 16 17 32 33 36 37 112 113 116 117 132 133 136 137 212 213 216 217 232 233 236 237 312 313 316 317 332 333 336 337
	//   18 19 1c 1d 38 39 3c 3d 118 119 11c 11d 138 139 13c 13d 218 219 21c 21d 238 239 23c 23d 318 319 31c 31d 338 339 33c 33d
	//   1a 1b 1e 1f 3a 3b 3e 3f 11a 11b 11e 11f 13a 13b 13e 13f 21a 21b 21e 21f 23a 23b 23e 23f 31a 31b 31e 31f 33a 33b 33e 33f
	//   40 41 44 45 60 61 64 65 140 141 144 145 160 161 164 165 240 241 244 245 260 261 264 265 340 341 344 345 360 361 364 365
	//   42 43 46 47 62 63 66 67 142 143 146 147 162 163 166 167 242 243 246 247 262 263 266 267 342 343 346 347 362 363 366 367
	//   48 49 4c 4d 68 69 6c 6d 148 149 14c 14d 168 169 16c 16d 248 249 24c 24d 268 269 26c 26d 348 349 34c 34d 368 369 36c 36d
	//   4a 4b 4e 4f 6a 6b 6e 6f 14a 14b 14e 14f 16a 16b 16e 16f 24a 24b 24e 24f 26a 26b 26e 26f 34a 34b 34e 34f 36a 36b 36e 36f
	//   50 51 54 55 70 71 74 75 150 151 154 155 170 171 174 175 250 251 254 255 270 271 274 275 350 351 354 355 370 371 374 375
	//   52 53 56 57 72 73 76 77 152 153 156 157 172 173 176 177 252 253 256 257 272 273 276 277 352 353 356 357 372 373 376 377
	//   58 59 5c 5d 78 79 7c 7d 158 159 15c 15d 178 179 17c 17d 258 259 25c 25d 278 279 27c 27d 358 359 35c 35d 378 379 37c 37d
	//   5a 5b 5e 5f 7a 7b 7e 7f 15a 15b 15e 15f 17a 17b 17e 17f 25a 25b 25e 25f 27a 27b 27e 27f 35a 35b 35e 35f 37a 37b 37e 37f
	//   80 81 84 85 a0 a1 a4 a5 180 181 184 185 1a0 1a1 1a4 1a5 280 281 284 285 2a0 2a1 2a4 2a5 380 381 384 385 3a0 3a1 3a4 3a5
	//   82 83 86 87 a2 a3 a6 a7 182 183 186 187 1a2 1a3 1a6 1a7 282 283 286 287 2a2 2a3 2a6 2a7 382 383 386 387 3a2 3a3 3a6 3a7
	//   88 89 8c 8d a8 a9 ac ad 188 189 18c 18d 1a8 1a9 1ac 1ad 288 289 28c 28d 2a8 2a9 2ac 2ad 388 389 38c 38d 3a8 3a9 3ac 3ad
	//   8a 8b 8e 8f aa ab ae af 18a 18b 18e 18f 1aa 1ab 1ae 1af 28a 28b 28e 28f 2aa 2ab 2ae 2af 38a 38b 38e 38f 3aa 3ab 3ae 3af
	//   90 91 94 95 b0 b1 b4 b5 190 191 194 195 1b0 1b1 1b4 1b5 290 291 294 295 2b0 2b1 2b4 2b5 390 391 394 395 3b0 3b1 3b4 3b5
	//   92 93 96 97 b2 b3 b6 b7 192 193 196 197 1b2 1b3 1b6 1b7 292 293 296 297 2b2 2b3 2b6 2b7 392 393 396 397 3b2 3b3 3b6 3b7
	//   98 99 9c 9d b8 b9 bc bd 198 199 19c 19d 1b8 1b9 1bc 1bd 298 299 29c 29d 2b8 2b9 2bc 2bd 398 399 39c 39d 3b8 3b9 3bc 3bd
	//   9a 9b 9e 9f ba bb be bf 19a 19b 19e 19f 1ba 1bb 1be 1bf 29a 29b 29e 29f 2ba 2bb 2be 2bf 39a 39b 39e 39f 3ba 3bb 3be 3bf
	//   c0 c1 c4 c5 e0 e1 e4 e5 1c0 1c1 1c4 1c5 1e0 1e1 1e4 1e5 2c0 2c1 2c4 2c5 2e0 2e1 2e4 2e5 3c0 3c1 3c4 3c5 3e0 3e1 3e4 3e5
	//   c2 c3 c6 c7 e2 e3 e6 e7 1c2 1c3 1c6 1c7 1e2 1e3 1e6 1e7 2c2 2c3 2c6 2c7 2e2 2e3 2e6 2e7 3c2 3c3 3c6 3c7 3e2 3e3 3e6 3e7
	//   c8 c9 cc cd e8 e9 ec ed 1c8 1c9 1cc 1cd 1e8 1e9 1ec 1ed 2c8 2c9 2cc 2cd 2e8 2e9 2ec 2ed 3c8 3c9 3cc 3cd 3e8 3e9 3ec 3ed
	//   ca cb ce cf ea eb ee ef 1ca 1cb 1ce 1cf 1ea 1eb 1ee 1ef 2ca 2cb 2ce 2cf 2ea 2eb 2ee 2ef 3ca 3cb 3ce 3cf 3ea 3eb 3ee 3ef
	//   d0 d1 d4 d5 f0 f1 f4 f5 1d0 1d1 1d4 1d5 1f0 1f1 1f4 1f5 2d0 2d1 2d4 2d5 2f0 2f1 2f4 2f5 3d0 3d1 3d4 3d5 3f0 3f1 3f4 3f5
	//   d2 d3 d6 d7 f2 f3 f6 f7 1d2 1d3 1d6 1d7 1f2 1f3 1f6 1f7 2d2 2d3 2d6 2d7 2f2 2f3 2f6 2f7 3d2 3d3 3d6 3d7 3f2 3f3 3f6 3f7
	//   d8 d9 dc dd f8 f9 fc fd 1d8 1d9 1dc 1dd 1f8 1f9 1fc 1fd 2d8 2d9 2dc 2dd 2f8 2f9 2fc 2fd 3d8 3d9 3dc 3dd 3f8 3f9 3fc 3fd
	//   da db de df fa fb fe ff 1da 1db 1de 1df 1fa 1fb 1fe 1ff 2da 2db 2de 2df 2fa 2fb 2fe 2ff 3da 3db 3de 3df 3fa 3fb 3fe 3ff
	// bitmask
	//         0 -> 1        = right
	//        01 -> 23       = down
	//      0123 -> 4567     = right
	//  01234567 -> 89abcdef = down
	//      0..f -> 10..1f   = down
	//     0..1f -> 20..3f   = right
	//     0..3f -> 40..7f   = down
	//     0..7f -> 80..ff   = down
	//     0..ff -> 100..1ff = right
	//    0..1ff -> 200..3ff = right
	// pattern = --rr ddrd drdr
	//         = x/325  y/da
	$bc = array(
		'pix' => $pix,
		'dec' => str_repeat(ZERO, $ow*$oh),
		'pos' => 0,
		'w'   => $ow >> 2, // div 4
		'h'   => $oh >> 2, // div 4
		'bpp' => 1, // grayscale
	);

	$len_pix = strlen($pix);
	$len_blk = $len_pix >> 4; // div 10
	printf("LEN %x / 10 = %x\n", $len_pix, $len_blk);

	// bit pattern swizzle
	//     64 =                rdrd drdr  x/a5    y/5a
	//    128 =           --rr ddrd drdr  x/325   y/da
	//    256 =           rrrd ddrd drdr  x/e25   y/1da
	//    512 =      --rr rrdd ddrd drdr  x/3c25  y/3da
	//   1024 =      drrr rrdd ddrd drdr  x/7c25  y/13da
	//   2048 = --dd rrrr rrdd ddrd drdr  x/fc25  y/303da
	//   4096 = dddr rrrr rrdd ddrd drdr  x/1fc25 y/e03da

	// same as 2*1 of 16-bits , but with additional r
	$bits = array(
		array(    0x40,    0x32,     0xd), //   7,7
		array(   0x100,    0xd2,    0x2d), //   f,f
		array(   0x400,   0x392,    0x6d), //  1f,1f
		array(  0x1000,   0xf12,    0xed), //  3f,3f
		array(  0x4000,  0x3e12,   0x1ed), //  7f,7f
		array( 0x10000,  0x7e12,  0x81ed), //  ff,ff
		array( 0x40000,  0xfe12, 0x301ed), // 1ff,1ff
		array(0x100000, 0x1fe12, 0xe01ed), // 3ff,3ff
	);
	foreach ( $bits as $bv )
	{
		if ( $len_blk <= $bv[0] )
		{
			printf("bitmask = %x , %x\n", $bv[1], $bv[2]);
			$i = 0;
			while ( $i < $bv[0] && $bc['pos'] < $len_pix )
			{
				$x = swizzle_bitmask($i >> 1, $bv[1]) << 1;
				$y = swizzle_bitmask($i >> 1, $bv[2]);
				pixdec_copy44($bc, $x + 0, $y);
				pixdec_copy44($bc, $x + 1, $y);
				$i += 2;
			} // while ( $i < $bv[0] && $bc['pos'] < $len_pix )
			goto done;
		}
	} // foreach ( $bits as $bv )
	php_notice("SKIP = texture over 400x400 tile / 1000x1000 pixels\n");
	return;

done:
	$pix = $bc['dec'];
	return;
}

function tegra_x1_swizzled_16_bits( &$pix, $ow, $oh )
{
	printf("== tegra_x1_swizzled_16_bits( %x , %x )\n", $ow, $oh);

	// 1 pixel =  4*4 bc tile
	//         = 10*4 rgba    = 40 bytes
	// unswizzled tiles
	//    0  2 10 12 40 42 50 52 80 82 90 92 c0 c2 d0 d2
	//    1  3 11 13 41 43 51 53 81 83 91 93 c1 c3 d1 d3
	//    4  6 14 16 44 46 54 56 84 86 94 96 c4 c6 d4 d6
	//    5  7 15 17 45 47 55 57 85 87 95 97 c5 c7 d5 d7
	//    8  a 18 1a 48 4a 58 5a 88 8a 98 9a c8 ca d8 da
	//    9  b 19 1b 49 4b 59 5b 89 8b 99 9b c9 cb d9 db
	//    c  e 1c 1e 4c 4e 5c 5e 8c 8e 9c 9e cc ce dc de
	//    d  f 1d 1f 4d 4f 5d 5f 8d 8f 9d 9f cd cf dd df
	//   20 22 30 32 60 62 70 72 a0 a2 b0 b2 e0 e2 f0 f2
	//   21 23 31 33 61 63 71 73 a1 a3 b1 b3 e1 e3 f1 f3
	//   24 26 34 36 64 66 74 76 a4 a6 b4 b6 e4 e6 f4 f6
	//   25 27 35 37 65 67 75 77 a5 a7 b5 b7 e5 e7 f5 f7
	//   28 2a 38 3a 68 6a 78 7a a8 aa b8 ba e8 ea f8 fa
	//   29 2b 39 3b 69 6b 79 7b a9 ab b9 bb e9 eb f9 fb
	//   2c 2e 3c 3e 6c 6e 7c 7e ac ae bc be ec ee fc fe
	//   2d 2f 3d 3f 6d 6f 7d 7f ad af bd bf ed ef fd ff
	// bitmask
	//         0 -> 1        = down
	//        01 -> 23       = right
	//      0123 -> 4567     = down
	//  01234567 -> 89abcdef = down
	//      0..f -> 10..1f   = right
	//     0..1f -> 20..3f   = down
	//     0..3f -> 40..7f   = right
	//     0..7f -> 80..ff   = right
	// pattern = rrdr ddrd
	//         = x/d2  y/2d
	$bc = array(
		'pix' => $pix,
		'dec' => str_repeat(ZERO, $ow*$oh*4),
		'pos' => 0,
		'w'   => $ow >> 2, // div 4
		'h'   => $oh >> 2, // div 4
		'bpp' => 4, // RGBA
	);

	$len_pix = strlen($pix);
	$len_blk = $len_pix >> 6; // div 40
	printf("LEN %x / 40 = %x\n", $len_pix, $len_blk);

	// bit pattern swizzle/*_bc7_deswizzled.bin
	//  id      64     128    256    512    1024   4096
	//       1  down   down   down   down   down   down   w=1 h=2  1:2
	//       2  right  right  right  right  right  right  w=2 h=2  1:1
	//       4  down   down   down   down   down   down   w=2 h=4  1:2
	//       8  down   down   down   down   down   down   w=2 h=8  1:4
	//      10  right  right  right  right  right  right  w=4 h=8  1:2
	//      20  down   down   down   down   down   down   w=4 h=16 1:4
	//      40  right  down   down   down   down   down
	//      80  right  right  down   down   down   down
	//     100  -      right  right  down   down   down
	//     200  -      right  right  right  right  right
	//     400  -      -      right  right  right  right
	//     800  -      -      right  right  right  right
	//    1000  -      -      -      right  right  right
	//    2000  -      -      -      right  right  right
	//    4000  -      -      -      -      right  right
	//    8000  -      -      -      -      down   right
	//   10000  -      -      -      -      -      right
	//   20000  -      -      -      -      -      down
	//   40000  -      -      -      -      -      down
	//   80000  -      -      -      -      -      down
	// pattern
	//     64 =                rrdr ddrd  x/d2    y/2d
	//    128 =           --rr rddr ddrd  x/392   y/6d
	//    256 =           rrrr dddr ddrd  x/f12   y/ed
	//    512 =      --rr rrrd dddr ddrd  x/3e12  y/1ed
	//   1024 =      drrr rrrd dddr ddrd  x/7e12  y/81ed
	//   2048 =   dd rrrr rrrd dddr ddrd  x/fe12  y/301ed
	//   4096 = dddr rrrr rrrd dddr ddrd  x/1fe12 y/e01ed
	$bits = array(
		array(    0x40,    0x32,     0xd), //   7,7
		array(   0x100,    0xd2,    0x2d), //   f,f
		array(   0x400,   0x392,    0x6d), //  1f,1f
		array(  0x1000,   0xf12,    0xed), //  3f,3f
		array(  0x4000,  0x3e12,   0x1ed), //  7f,7f
		array( 0x10000,  0x7e12,  0x81ed), //  ff,ff
		array( 0x40000,  0xfe12, 0x301ed), // 1ff,1ff
		array(0x100000, 0x1fe12, 0xe01ed), // 3ff,3ff
	);

	foreach ( $bits as $bv )
	{
		if ( $len_blk <= $bv[0] )
		{
			printf("bitmask = %x , %x\n", $bv[1], $bv[2]);
			$i = 0;
			while ( $i < $bv[0] && $bc['pos'] < $len_pix )
			{
				$x = swizzle_bitmask($i, $bv[1]);
				$y = swizzle_bitmask($i, $bv[2]);
				pixdec_copy44($bc, $x, $y);
				$i++;
			} // while ( $i < $bv[0] && $bc['pos'] < $len_pix )
			goto done;
		}
	} // foreach ( $bits as $bv )
	php_notice("SKIP = texture over 400x400 tile / 1000x1000 pixels\n");
	return;

done:
	$pix = $bc['dec'];
	return;
}
//////////////////////////////
function im_bc3( &$file, $pos, $w, $h, $size )
{
	printf("== im_bc3( %x , %x , %x )\n", $pos, $w, $h);
	$pix = substr($file, $pos, $size);

	$bc3 = new s3tc_Texture;
	$pix = $bc3->bc3($pix);
	//$pix = $bc4->s3tc_debug($pix, $w, $h);

	$ch = int_ceil_pow2($h);
	tegra_x1_swizzled_16_bits($pix, $w, $ch);
	$img = array(
		'w'   => $w,
		'h'   => $h,
		'pix' => $pix,
	);
	return $img;
}

function im_bc4( &$file, $pos, $w, $h, $size )
{
	printf("== im_bc4( %x , %x , %x )\n", $pos, $w, $h);
	$pix = substr($file, $pos, $size);

	$bc4 = new s3tc_Texture;
	$pix = $bc4->bc4($pix);
	//$pix = $bc4->bc4_debug($pix, $w, $h);

	$ch = int_ceil_pow2($h);
	tegra_x1_swizzled_8_bits($pix, $w, $ch);
	$img = array(
		'cc'  => 0x100,
		'w'   => $w,
		'h'   => $h,
		'pal' => grayclut(0x100),
		'pix' => $pix,
	);
	return $img;
}

function im_bc7( &$file, $pos, $w, $h, $size )
{
	printf("== im_bc7( %x , %x , %x )\n", $pos, $w, $h);
	$pix = substr($file, $pos, $size);

	$bc7 = new bptc_texture;
	$pix = $bc7->bc7($pix);
	//$pix = $bc7->bptc_debug($pix, $w, $h);

	// Supporter00.ftx   c0 180 ->  c0 200
	// for_Minimap.ftx  780 438 -> 780 800
	$ch = int_ceil_pow2($h);
	tegra_x1_swizzled_16_bits($pix, $w, $ch);
	$img = array(
		'w'   => $w,
		'h'   => $h,
		'pix' => $pix,
	);
	return $img;
}
//////////////////////////////
function switnvt( &$file, $base, $pfx, $id )
{
	printf("== switnvt( %x , %s , %d )\n", $base, $pfx, $id);
	if ( substr($file,$base,4) != '.tex' )
		return;

	$fmt = str2int($file, $base +  4, 2);
	$w   = str2int($file, $base + 12, 4);
	$h   = str2int($file, $base + 16, 4);
	$sz1 = str2int($file, $base + 28, 4);
	$sz2 = str2int($file, $base + 32, 4);

	$list_fmt = array(
		0x44 => 'im_bc3',
		0x49 => 'im_bc4',
		0x4d => 'im_bc7',
	);
	if ( ! isset($list_fmt[$fmt]) )
		return php_error('UNKNOWN im fmt  %x', $fmt);
	printf("DETECT  fmt %s , %x x %x \n", $list_fmt[$fmt], $w, $h);

	if ( defined('DRY_RUN') )
		return;

	$fn = sprintf('%s.%d.nvt', $pfx, $id);
	printf("%4x x %4x , %s\n", $w, $h, $fn);

	$func = $list_fmt[$fmt];
	$img = $func($file, $base+$sz1, $w, $h, $sz2);
	save_clutfile($fn, $img);
	return;
}

function unicorn( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) !== 'FTEX' )
		return;

	$pfx = substr($fname, 0, strrpos($fname, '.'));
	$hdsz = str2int($file,  8, 4);
	$cnt  = str2int($file, 12, 4);

	$st = $hdsz;
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p1 = 0x20 + ($i * 0x30);
		$fn = substr($file, $p1, 0x20);
			$fn = rtrim($fn, ZERO);

		if ( substr($file, $st, 4) !== 'FTX0' )
			return php_error('%s 0x%x not FTX0', $fname, $st);

		$sz1 = str2int($file, $st+4, 4);
		$sz2 = str2int($file, $st+8, 4);
		printf("NVT  %x , %x , %s\n", $st, $sz1, $fn);

		switnvt($file, $st+$sz2, $pfx, $i);
		$st += ($sz1 + $sz2);
	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

argv_loopfile($argv, 'unicorn');

/*
13 sentinels
	44  im_bc3
	49  im_bc4
	4d  im_bc7
grim grimoire once again
	49  im_bc4
	4d  im_bc7
unicorn overlord
	49  im_bc4
	4d  im_bc7

13 sentinels
	44  im_bc3
		SecretFile_000.ftx
		SecretFile_001.ftx
	49  im_bc4
		FontBt.ftx
		FontDigi.ftx
		Font.ftx

grim grimoire once again
	49  im_bc4
		Font.ftx
		Font_Spell.ftx

unicorn overlord
	49  im_bc4
		AiramFontB.ftx
		AiramFont.ftx
		AlbertusNovaFontB.ftx
		AlbertusNovaFont.ftx
		AlbertusNovaFont_LB.ftx
		AlbertusNovaFont_L.ftx
		CongenialFontB.ftx
		CongenialFont.ftx
		KiaroFontB.ftx
		KiaroFont.ftx
		KiaroFont_LB.ftx
		KiaroFont_L.ftx
		KleeFontB.ftx
		KleeFont.ftx
		ManofaFontB.ftx
		ManofaFont.ftx
		MsgFont_CNB.ftx
		MsgFont_CN.ftx
		MsgFont_KOB.ftx
		MsgFont_KO.ftx
		MsgFont_TWB.ftx
		MsgFont_TW.ftx
		SwUserNameFont.ftx
		SysFont_CNB.ftx
		SysFont_CN.ftx
		SysFont_CN_LB.ftx
		SysFont_CN_L.ftx
		SysFont_KOB.ftx
		SysFont_KO.ftx
		SysFont_KO_LB.ftx
		SysFont_KO_L.ftx
		SysFont_TWB.ftx
		SysFont_TW.ftx
		SysFont_TW_LB.ftx
		SysFont_TW_L.ftx
		UnicornFontB.ftx
		UnicornFont.ftx
 */
