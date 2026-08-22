<?php
/*
[license]
[/license]
 */
// https://ps2linux.no-ip.info/playstation2-linux.com/project/showfilesb466.html
//   GSTexturePack-1.1/PS2Textures.cpp
//   by Victor Soba

//////////////////////////////
//===== 32 =====
$gp_block32 = [
	 0,  1,  4,  5, 16, 17, 20, 21,
	 2,  3,  6,  7, 18, 19, 22, 23,
	 8,  9, 12, 13, 24, 25, 28, 29,
	10, 11, 14, 15, 26, 27, 30, 31,
];
$gp_columnWord32 = [
	0, 1, 4, 5,  8,  9, 12, 13,
	2, 3, 6, 7, 10, 11, 14, 15,
];

$gp_blockZ32 = [
	24, 25, 28, 29, 8,  9, 12, 13,
	26, 27, 30, 31,10, 11, 14, 15,
	16, 17, 20, 21, 0,  1,  4,  5,
	18, 19, 22, 23, 2,  3,  6,  7,
];
$gp_columnWordZ32 = [
	0, 1, 4, 5,  8,  9, 12, 13,
	2, 3, 6, 7, 10, 11, 14, 15,
];

//===== 16 =====
$gp_block16 = [
	 0,  2,  8, 10,
	 1,  3,  9, 11,
	 4,  6, 12, 14,
	 5,  7, 13, 15,
	16, 18, 24, 26,
	17, 19, 25, 27,
	20, 22, 28, 30,
	21, 23, 29, 31,
];
$gp_columnWord16 = [
	0, 1, 4, 5,  8,  9, 12, 13,  0, 1, 4, 5,  8,  9, 12, 13,
	2, 3, 6, 7, 10, 11, 14, 15,  2, 3, 6, 7, 10, 11, 14, 15,
];
$gp_columnHalf16 = [
	0, 0, 0, 0, 0, 0, 0, 0,  1, 1, 1, 1, 1, 1, 1, 1,
	0, 0, 0, 0, 0, 0, 0, 0,  1, 1, 1, 1, 1, 1, 1, 1,
];

$gp_blockZ16 = [
	24, 26, 16, 18,
	25, 27, 17, 19,
	28, 30, 20, 22,
	29, 31, 21, 23,
	 8, 10,  0,  2,
	 9, 11,  1,  3,
	12, 14,  4,  6,
	13, 15,  5,  7,
];
$gp_columnWordZ16 = [
	0, 1, 4, 5,  8,  9, 12, 13,  0, 1, 4, 5,  8,  9, 12, 13,
	2, 3, 6, 7, 10, 11, 14, 15,  2, 3, 6, 7, 10, 11, 14, 15,
];
$gp_columnHalfZ16 = [
	0, 0, 0, 0, 0, 0, 0, 0,  1, 1, 1, 1, 1, 1, 1, 1,
	0, 0, 0, 0, 0, 0, 0, 0,  1, 1, 1, 1, 1, 1, 1, 1,
];

//===== 16S =====
$gp_block16S = [
	 0,  2, 16, 18,
	 1,  3, 17, 19,
	 8, 10, 24, 26,
	 9, 11, 25, 27,
	 4,  6, 20, 22,
	 5,  7, 21, 23,
	12, 14, 28, 30,
	13, 15, 29, 31,
];
$gp_columnWord16S = [
	0, 1, 4, 5,  8,  9, 12, 13,  0, 1, 4, 5,  8,  9, 12, 13,
	2, 3, 6, 7, 10, 11, 14, 15,  2, 3, 6, 7, 10, 11, 14, 15,
];
$gp_columnHalf16S = [
	0, 0, 0, 0, 0, 0, 0, 0,  1, 1, 1, 1, 1, 1, 1, 1,
	0, 0, 0, 0, 0, 0, 0, 0,  1, 1, 1, 1, 1, 1, 1, 1,
];

$gp_blockZ16S = [
	24, 26,  8, 10,
	25, 27,  9, 11,
	16, 18,  0,  2,
	17, 19,  1,  3,
	28, 30, 12, 14,
	29, 31, 13, 15,
	20, 22,  4,  6,
	21, 23,  5,  7,
];
$gp_columnWordZ16S = [
	0, 1, 4, 5,  8,  9, 12, 13,  0, 1, 4, 5,  8,  9, 12, 13,
	2, 3, 6, 7, 10, 11, 14, 15,  2, 3, 6, 7, 10, 11, 14, 15,
];
$gp_columnHalfZ16S = [
	0, 0, 0, 0, 0, 0, 0, 0,  1, 1, 1, 1, 1, 1, 1, 1,
	0, 0, 0, 0, 0, 0, 0, 0,  1, 1, 1, 1, 1, 1, 1, 1,
];

//===== 8 =====
$gp_block8 = [
	 0,  1,  4,  5, 16, 17, 20, 21,
	 2,  3,  6,  7, 18, 19, 22, 23,
	 8,  9, 12, 13, 24, 25, 28, 29,
	10, 11, 14, 15, 26, 27, 30, 31,
];
$gp_columnWord8 = [
	[
		 0,  1,  4,  5,  8,  9, 12, 13,   0,  1,  4,  5,  8,  9, 12, 13,
		 2,  3,  6,  7, 10, 11, 14, 15,   2,  3,  6,  7, 10, 11, 14, 15,

		 8,  9, 12, 13,  0,  1,  4,  5,   8,  9, 12, 13,  0,  1,  4,  5,
		10, 11, 14, 15,  2,  3,  6,  7,  10, 11, 14, 15,  2,  3,  6,  7,
	],
	[
		 8,  9, 12, 13,  0,  1,  4,  5,   8,  9, 12, 13,  0,  1,  4,  5,
		10, 11, 14, 15,  2,  3,  6,  7,  10, 11, 14, 15,  2,  3,  6,  7,

		 0,  1,  4,  5,  8,  9, 12, 13,   0,  1,  4,  5,  8,  9, 12, 13,
		 2,  3,  6,  7, 10, 11, 14, 15,   2,  3,  6,  7, 10, 11, 14, 15,
	],
];
$gp_columnByte8 = [
	0, 0, 0, 0, 0, 0, 0, 0,  2, 2, 2, 2, 2, 2, 2, 2,
	0, 0, 0, 0, 0, 0, 0, 0,  2, 2, 2, 2, 2, 2, 2, 2,

	1, 1, 1, 1, 1, 1, 1, 1,  3, 3, 3, 3, 3, 3, 3, 3,
	1, 1, 1, 1, 1, 1, 1, 1,  3, 3, 3, 3, 3, 3, 3, 3,
];

//===== 4 =====
$gp_block4 = [
	 0,  2,  8, 10,
	 1,  3,  9, 11,
	 4,  6, 12, 14,
	 5,  7, 13, 15,
	16, 18, 24, 26,
	17, 19, 25, 27,
	20, 22, 28, 30,
	21, 23, 29, 31,
];
$gp_columnWord4 = [
	[
		 0,  1,  4,  5,  8,  9, 12, 13,   0,  1,  4,  5,  8,  9, 12, 13,   0,  1,  4,  5,  8,  9, 12, 13,   0,  1,  4,  5,  8,  9, 12, 13,
		 2,  3,  6,  7, 10, 11, 14, 15,   2,  3,  6,  7, 10, 11, 14, 15,   2,  3,  6,  7, 10, 11, 14, 15,   2,  3,  6,  7, 10, 11, 14, 15,

		 8,  9, 12, 13,  0,  1,  4,  5,   8,  9, 12, 13,  0,  1,  4,  5,   8,  9, 12, 13,  0,  1,  4,  5,   8,  9, 12, 13,  0,  1,  4,  5,
		10, 11, 14, 15,  2,  3,  6,  7,  10, 11, 14, 15,  2,  3,  6,  7,  10, 11, 14, 15,  2,  3,  6,  7,  10, 11, 14, 15,  2,  3,  6,  7,
	],
	[
		 8,  9, 12, 13,  0,  1,  4,  5,   8,  9, 12, 13,  0,  1,  4,  5,   8,  9, 12, 13,  0,  1,  4,  5,   8,  9, 12, 13,  0,  1,  4,  5,
		10, 11, 14, 15,  2,  3,  6,  7,  10, 11, 14, 15,  2,  3,  6,  7,  10, 11, 14, 15,  2,  3,  6,  7,  10, 11, 14, 15,  2,  3,  6,  7,

		 0,  1,  4,  5,  8,  9, 12, 13,   0,  1,  4,  5,  8,  9, 12, 13,   0,  1,  4,  5,  8,  9, 12, 13,   0,  1,  4,  5,  8,  9, 12, 13,
		 2,  3,  6,  7, 10, 11, 14, 15,   2,  3,  6,  7, 10, 11, 14, 15,   2,  3,  6,  7, 10, 11, 14, 15,   2,  3,  6,  7, 10, 11, 14, 15,
	],
];
$gp_columnByte4 = [
	0, 0, 0, 0, 0, 0, 0, 0,  2, 2, 2, 2, 2, 2, 2, 2,  4, 4, 4, 4, 4, 4, 4, 4,  6, 6, 6, 6, 6, 6, 6, 6,
	0, 0, 0, 0, 0, 0, 0, 0,  2, 2, 2, 2, 2, 2, 2, 2,  4, 4, 4, 4, 4, 4, 4, 4,  6, 6, 6, 6, 6, 6, 6, 6,

	1, 1, 1, 1, 1, 1, 1, 1,  3, 3, 3, 3, 3, 3, 3, 3,  5, 5, 5, 5, 5, 5, 5, 5,  7, 7, 7, 7, 7, 7, 7, 7,
	1, 1, 1, 1, 1, 1, 1, 1,  3, 3, 3, 3, 3, 3, 3, 3,  5, 5, 5, 5, 5, 5, 5, 5,  7, 7, 7, 7, 7, 7, 7, 7,
];
//////////////////////////////
//===== 32 =====
function writeTexPSMCT32($dbw, $rrw, $rrh)
{
	printf("== %s( %x , %x , %x )\n", __FUNCTION__, $dbw, $rrw, $rrh);
	global $gp_block32, $gp_columnWord32;
	$src = 0;
	for ( $y=0; $y < $rrh; $y++ )
	{
		for ( $x=0; $x < $rrw; $x++ )
		{
			$pageX = (int)($x / 64);
			$pageY = (int)($y / 32);
			$page  = $pageX + $pageY * $dbw;

			$px = $x % 64;
			$py = $y % 32;

			$blockX = (int)($px / 8);
			$blockY = (int)($py / 8);
			$block  = $gp_block32[$blockX + $blockY * 8];

			$bx = $px % 8;
			$by = $py % 8;

			$column = (int)($by / 2);

			$cx = $bx;
			$cy = $by % 2;
			$cw = $gp_columnWord32[$cx + $cy * 8];

			$startBlockPos = 0;
			$startBlockPos += $page * 2048;
			$startBlockPos += $block * 64;
			$startBlockPos += $column * 16;
			$startBlockPos += $cw;
			printf("gsmem[%x] = %x\n", $startBlockPos, $src);
			$src++;
		}
	}
	return;
}

function readTexPSMCT32($dbw, $rrw, $rrh)
{
	printf("== %s( %x , %x , %x )\n", __FUNCTION__, $dbw, $rrw, $rrh);
	global $gp_block32, $gp_columnWord32;
	$src = 0;
	for ( $y=0; $y < $rrh; $y++ )
	{
		for ( $x=0; $x < $rrw; $x++ )
		{
			$pageX = (int)($x / 64);
			$pageY = (int)($y / 32);
			$page  = $pageX + $pageY * $dbw;

			$px = $x % 64;
			$py = $y % 32;

			$blockX = (int)($px / 8);
			$blockY = (int)($py / 8);
			$block  = $gp_block32[$blockX + $blockY * 8];

			$bx = $px % 8;
			$by = $py % 8;

			$column = (int)($by / 2);

			$cx = $bx;
			$cy = $by % 2;
			$cw = $gp_columnWord32[$cx + $cy * 8];

			$startBlockPos = 0;
			$startBlockPos += $page * 2048;
			$startBlockPos += $block * 64;
			$startBlockPos += $column * 16;
			$startBlockPos += $cw;
			printf("%x = gsmem[%x]\n", $src, $startBlockPos);
			$src++;
		}
	}
	return;
}

function writeTexPSMZ32($dbw, $rrw, $rrh)
{
	printf("== %s( %x , %x , %x )\n", __FUNCTION__, $dbw, $rrw, $rrh);
	global $gp_blockZ32, $gp_columnWordZ32;
	$src = 0;
	for ( $y=0; $y < $rrh; $y++ )
	{
		for ( $x=0; $x < $rrw; $x++ )
		{
			$pageX = (int)($x / 64);
			$pageY = (int)($y / 32);
			$page  = $pageX + $pageY * $dbw;

			$px = $x % 64;
			$py = $y % 32;

			$blockX = (int)($px / 8);
			$blockY = (int)($py / 8);
			$block  = $gp_blockZ32[$blockX + $blockY * 8];

			$bx = $px % 8;
			$by = $py % 8;

			$column = (int)($by / 2);

			$cx = $bx;
			$cy = $by % 2;
			$cw = $gp_columnWordZ32[$cx + $cy * 8];

			$startBlockPos = 0;
			$startBlockPos += $page * 2048;
			$startBlockPos += $block * 64;
			$startBlockPos += $column * 16;
			$startBlockPos += $cw;
			printf("gsmem[%x] = %x\n", $startBlockPos, $src);
			$src++;
		}
	}
	return;
}

function readTexPSMZ32($dbw, $rrw, $rrh)
{
	printf("== %s( %x , %x , %x )\n", __FUNCTION__, $dbw, $rrw, $rrh);
	global $gp_blockZ32, $gp_columnWordZ32;
	$src = 0;
	for ( $y=0; $y < $rrh; $y++ )
	{
		for ( $x=0; $x < $rrw; $x++ )
		{
			$pageX = (int)($x / 64);
			$pageY = (int)($y / 32);
			$page  = $pageX + $pageY * $dbw;

			$px = $x % 64;
			$py = $y % 32;

			$blockX = (int)($px / 8);
			$blockY = (int)($py / 8);
			$block  = $gp_blockZ32[$blockX + $blockY * 8];

			$bx = $px % 8;
			$by = $py % 8;

			$column = (int)($by / 2);

			$cx = $bx;
			$cy = $by % 2;
			$cw = $gp_columnWordZ32[$cx + $cy * 8];

			$startBlockPos = 0;
			$startBlockPos += $page * 2048;
			$startBlockPos += $block * 64;
			$startBlockPos += $column * 16;
			$startBlockPos += $cw;
			printf("%x = gsmem[%x]\n", $src, $startBlockPos);
			$src++;
		}
	}
	return;
}

//===== 16 =====
function writeTexPSMCT16($dbw, $rrw, $rrh)
{
	printf("== %s( %x , %x , %x )\n", __FUNCTION__, $dbw, $rrw, $rrh);
	global $gp_block16, $gp_columnWord16, $gp_columnHalf16;
	//$dbw >>= 1;
	$src = 0;
	for ( $y=0; $y < $rrh; $y++ )
	{
		for ( $x=0; $x < $rrw; $x++ )
		{
			$pageX = (int)($x / 64);
			$pageY = (int)($y / 64);
			$page  = $pageX + $pageY * $dbw;

			$px = $x % 64;
			$py = $y % 64;

			$blockX = (int)($px / 16);
			$blockY = (int)($py / 8);
			$block  = $gp_block16[$blockX + $blockY * 4];

			$bx = $px % 16;
			$by = $py % 8;

			$column = (int)($by / 2);

			$cx = $bx;
			$cy = $by % 2;
			$cw = $gp_columnWord16[$cx + $cy * 16];
			$ch = $gp_columnHalf16[$cx + $cy * 16];

			$startBlockPos = 0;
			$startBlockPos += $page * 2048;
			$startBlockPos += $block * 64;
			$startBlockPos += $column * 16;
			$startBlockPos += $cw;

			$ch1 = ($startBlockPos * 2) + $ch;
			printf("gsmem[%x] = %x\n", $ch1, $src);
			$src++;
		}
	}
	return;
}

function readTexPSMCT16($dbw, $rrw, $rrh)
{
	printf("== %s( %x , %x , %x )\n", __FUNCTION__, $dbw, $rrw, $rrh);
	global $gp_block16, $gp_columnWord16, $gp_columnHalf16;
	//$dbw >>= 1;
	$src = 0;
	for ( $y=0; $y < $rrh; $y++ )
	{
		for ( $x=0; $x < $rrw; $x++ )
		{
			$pageX = (int)($x / 64);
			$pageY = (int)($y / 64);
			$page  = $pageX + $pageY * $dbw;

			$px = $x % 64;
			$py = $y % 64;

			$blockX = (int)($px / 16);
			$blockY = (int)($py / 8);
			$block  = $gp_block16[$blockX + $blockY * 4];

			$bx = $px % 16;
			$by = $py % 8;

			$column = (int)($by / 2);

			$cx = $bx;
			$cy = $by % 2;
			$cw = $gp_columnWord16[$cx + $cy * 16];
			$ch = $gp_columnHalf16[$cx + $cy * 16];

			$startBlockPos = 0;
			$startBlockPos += $page * 2048;
			$startBlockPos += $block * 64;
			$startBlockPos += $column * 16;
			$startBlockPos += $cw;

			$ch1 = ($startBlockPos * 2) + $ch;
			printf("%x = gsmem[%x]\n", $src, $ch1);
			$src++;
		}
	}
	return;
}

function writeTexPSMZ16($dbw, $rrw, $rrh)
{
	printf("== %s( %x , %x , %x )\n", __FUNCTION__, $dbw, $rrw, $rrh);
	global $gp_blockZ16, $gp_columnWordZ16, $gp_columnHalfZ16;
	//$dbw >>= 1;
	$src = 0;
	for ( $y=0; $y < $rrh; $y++ )
	{
		for ( $x=0; $x < $rrw; $x++ )
		{
			$pageX = (int)($x / 64);
			$pageY = (int)($y / 64);
			$page  = $pageX + $pageY * $dbw;

			$px = $x % 64;
			$py = $y % 64;

			$blockX = (int)($px / 16);
			$blockY = (int)($py / 8);
			$block  = $gp_blockZ16[$blockX + $blockY * 4];

			$bx = $px % 16;
			$by = $py % 8;

			$column = (int)($by / 2);

			$cx = $bx;
			$cy = $by % 2;
			$cw = $gp_columnWordZ16[$cx + $cy * 16];
			$ch = $gp_columnHalfZ16[$cx + $cy * 16];

			$startBlockPos = 0;
			$startBlockPos += $page * 2048;
			$startBlockPos += $block * 64;
			$startBlockPos += $column * 16;
			$startBlockPos += $cw;

			$ch1 = ($startBlockPos * 2) + $ch;
			printf("gsmem[%x] = %x\n", $ch1, $src);
			$src++;
		}
	}
	return;
}

function readTexPSMZ16($dbw, $rrw, $rrh)
{
	printf("== %s( %x , %x , %x )\n", __FUNCTION__, $dbw, $rrw, $rrh);
	global $gp_blockZ16, $gp_columnWordZ16, $gp_columnHalfZ16;
	//$dbw >>= 1;
	$src = 0;
	for ( $y=0; $y < $rrh; $y++ )
	{
		for ( $x=0; $x < $rrw; $x++ )
		{
			$pageX = (int)($x / 64);
			$pageY = (int)($y / 64);
			$page  = $pageX + $pageY * $dbw;

			$px = $x % 64;
			$py = $y % 64;

			$blockX = (int)($px / 16);
			$blockY = (int)($py / 8);
			$block  = $gp_blockZ16[$blockX + $blockY * 4];

			$bx = $px % 16;
			$by = $py % 8;

			$column = (int)($by / 2);

			$cx = $bx;
			$cy = $by % 2;
			$cw = $gp_columnWordZ16[$cx + $cy * 16];
			$ch = $gp_columnHalfZ16[$cx + $cy * 16];

			$startBlockPos = 0;
			$startBlockPos += $page * 2048;
			$startBlockPos += $block * 64;
			$startBlockPos += $column * 16;
			$startBlockPos += $cw;

			$ch1 = ($startBlockPos * 2) + $ch;
			printf("%x = gsmem[%x]\n", $src, $ch1);
			$src++;
		}
	}
	return;
}

//===== 16S =====
function writeTexPSMCT16S($dbw, $rrw, $rrh)
{
	printf("== %s( %x , %x , %x )\n", __FUNCTION__, $dbw, $rrw, $rrh);
	global $gp_block16S, $gp_columnWord16S, $gp_columnHalf16S;
	//$dbw >>= 1;
	$src = 0;
	for ( $y=0; $y < $rrh; $y++ )
	{
		for ( $x=0; $x < $rrw; $x++ )
		{
			$pageX = (int)($x / 64);
			$pageY = (int)($y / 64);
			$page  = $pageX + $pageY * $dbw;

			$px = $x % 64;
			$py = $y % 64;

			$blockX = (int)($px / 16);
			$blockY = (int)($py / 8);
			$block  = $gp_block16S[$blockX + $blockY * 4];

			$bx = $px % 16;
			$by = $py % 8;

			$column = (int)($by / 2);

			$cx = $bx;
			$cy = $by % 2;
			$cw = $gp_columnWord16S[$cx + $cy * 16];
			$ch = $gp_columnHalf16S[$cx + $cy * 16];

			$startBlockPos = 0;
			$startBlockPos += $page * 2048;
			$startBlockPos += $block * 64;
			$startBlockPos += $column * 16;
			$startBlockPos += $cw;

			$ch1 = ($startBlockPos * 2) + $ch;
			printf("gsmem[%x] = %x\n", $ch1, $src);
			$src++;
		}
	}
	return;
}

function readTexPSMCT16S($dbw, $rrw, $rrh)
{
	printf("== %s( %x , %x , %x )\n", __FUNCTION__, $dbw, $rrw, $rrh);
	global $gp_block16S, $gp_columnWord16S, $gp_columnHalf16S;
	//$dbw >>= 1;
	$src = 0;
	for ( $y=0; $y < $rrh; $y++ )
	{
		for ( $x=0; $x < $rrw; $x++ )
		{
			$pageX = (int)($x / 64);
			$pageY = (int)($y / 64);
			$page  = $pageX + $pageY * $dbw;

			$px = $x % 64;
			$py = $y % 64;

			$blockX = (int)($px / 16);
			$blockY = (int)($py / 8);
			$block  = $gp_block16S[$blockX + $blockY * 4];

			$bx = $px % 16;
			$by = $py % 8;

			$column = (int)($by / 2);

			$cx = $bx;
			$cy = $by % 2;
			$cw = $gp_columnWord16S[$cx + $cy * 16];
			$ch = $gp_columnHalf16S[$cx + $cy * 16];

			$startBlockPos = 0;
			$startBlockPos += $page * 2048;
			$startBlockPos += $block * 64;
			$startBlockPos += $column * 16;
			$startBlockPos += $cw;

			$ch1 = ($startBlockPos * 2) + $ch;
			printf("%x = gsmem[%x]\n", $src, $ch1);
			$src++;
		}
	}
	return;
}

function writeTexPSMZ16S($dbw, $rrw, $rrh)
{
	printf("== %s( %x , %x , %x )\n", __FUNCTION__, $dbw, $rrw, $rrh);
	global $gp_blockZ16S, $gp_columnWordZ16S, $gp_columnHalfZ16S;
	//$dbw >>= 1;
	$src = 0;
	for ( $y=0; $y < $rrh; $y++ )
	{
		for ( $x=0; $x < $rrw; $x++ )
		{
			$pageX = (int)($x / 64);
			$pageY = (int)($y / 64);
			$page  = $pageX + $pageY * $dbw;

			$px = $x % 64;
			$py = $y % 64;

			$blockX = (int)($px / 16);
			$blockY = (int)($py / 8);
			$block  = $gp_blockZ16S[$blockX + $blockY * 4];

			$bx = $px % 16;
			$by = $py % 8;

			$column = (int)($by / 2);

			$cx = $bx;
			$cy = $by % 2;
			$cw = $gp_columnWordZ16S[$cx + $cy * 16];
			$ch = $gp_columnHalfZ16S[$cx + $cy * 16];

			$startBlockPos = 0;
			$startBlockPos += $page * 2048;
			$startBlockPos += $block * 64;
			$startBlockPos += $column * 16;
			$startBlockPos += $cw;

			$ch1 = ($startBlockPos * 2) + $ch;
			printf("gsmem[%x] = %x\n", $ch1, $src);
			$src++;
		}
	}
	return;
}

function readTexPSMZ16S($dbw, $rrw, $rrh)
{
	printf("== %s( %x , %x , %x )\n", __FUNCTION__, $dbw, $rrw, $rrh);
	global $gp_blockZ16S, $gp_columnWordZ16S, $gp_columnHalfZ16S;
	//$dbw >>= 1;
	$src = 0;
	for ( $y=0; $y < $rrh; $y++ )
	{
		for ( $x=0; $x < $rrw; $x++ )
		{
			$pageX = (int)($x / 64);
			$pageY = (int)($y / 64);
			$page  = $pageX + $pageY * $dbw;

			$px = $x % 64;
			$py = $y % 64;

			$blockX = (int)($px / 16);
			$blockY = (int)($py / 8);
			$block  = $gp_blockZ16S[$blockX + $blockY * 4];

			$bx = $px % 16;
			$by = $py % 8;

			$column = (int)($by / 2);

			$cx = $bx;
			$cy = $by % 2;
			$cw = $gp_columnWordZ16S[$cx + $cy * 16];
			$ch = $gp_columnHalfZ16S[$cx + $cy * 16];

			$startBlockPos = 0;
			$startBlockPos += $page * 2048;
			$startBlockPos += $block * 64;
			$startBlockPos += $column * 16;
			$startBlockPos += $cw;

			$ch1 = ($startBlockPos * 2) + $ch;
			printf("%x = gsmem[%x]\n", $src, $ch1);
			$src++;
		}
	}
	return;
}

//===== 8 =====
function writeTexPSMT8($dbw, $rrw, $rrh)
{
	printf("== %s( %x , %x , %x )\n", __FUNCTION__, $dbw, $rrw, $rrh);
	global $gp_block8, $gp_columnWord8, $gp_columnByte8;
	$dbw >>= 1;
	$src = 0;

	$buf = [];
	for ( $y=0; $y < $rrh; $y++ )
	{
		for ( $x=0; $x < $rrw; $x++ )
		{
			$pageX = (int)($x / 128);
			$pageY = (int)($y / 64);
			$page  = $pageX + $pageY * $dbw;

			$px = $x % 128;
			$py = $y % 64;

			$blockX = (int)($px / 16);
			$blockY = (int)($py / 16);
			$block  = $gp_block8[$blockX + $blockY * 8];

			$bx = $px % 16;
			$by = $py % 16;

			$column = (int)($by / 4);

			$cx = $bx;
			$cy = $by % 4;
			$cw = $gp_columnWord8[$column & 1][$cx + $cy * 16];
			$cb = $gp_columnByte8[$cx + $cy * 16];

			$startBlockPos = 0;
			$startBlockPos += $page * 2048;
			$startBlockPos += $block * 64;
			$startBlockPos += $column * 16;
			$startBlockPos += $cw;

			$cb1 = ($startBlockPos * 4) + $cb;
			printf("gsmem[%x] = %x\n", $cb1, $src);
			$src++;
		}
	}
	return;
}

function readTexPSMT8($dbw, $rrw, $rrh)
{
	printf("== %s( %x , %x , %x )\n", __FUNCTION__, $dbw, $rrw, $rrh);
	global $gp_block8, $gp_columnWord8, $gp_columnByte8;
	$dbw >>= 1;
	$src = 0;
	for ( $y=0; $y < $rrh; $y++ )
	{
		for ( $x=0; $x < $rrw; $x++ )
		{
			$pageX = (int)($x / 128);
			$pageY = (int)($y / 64);
			$page  = $pageX + $pageY * $dbw;

			$px = $x % 128;
			$py = $y % 64;

			$blockX = (int)($px / 16);
			$blockY = (int)($py / 16);
			$block  = $gp_block8[$blockX + $blockY * 8];

			$bx = $px % 16;
			$by = $py % 16;

			$column = (int)($by / 4);

			$cx = $bx;
			$cy = $by % 4;
			$cw = $gp_columnWord8[$column & 1][$cx + $cy * 16];
			$cb = $gp_columnByte8[$cx + $cy * 16];

			$startBlockPos = 0;
			$startBlockPos += $page * 2048;
			$startBlockPos += $block * 64;
			$startBlockPos += $column * 16;
			$startBlockPos += $cw;

			$cb1 = ($startBlockPos * 4) + $cb;
			printf("%x = gsmem[%x]\n", $src, $cb1);
			$src++;
		}
	}
	return;
}

//===== 4 =====
function writeTexPSMT4($dbw, $rrw, $rrh)
{
	printf("== %s( %x , %x , %x )\n", __FUNCTION__, $dbw, $rrw, $rrh);
	global $gp_block4, $gp_columnWord4, $gp_columnByte4;
	$dbw >>= 1;
	$src = 0;
	$odd = false;

	$buf = [];
	for ( $y=0; $y < $rrh; $y++ )
	{
		for ( $x=0; $x < $rrw; $x++ )
		{
			$pageX = (int)($x / 128);
			$pageY = (int)($y / 128);
			$page  = $pageX + $pageY * $dbw;

			$px = $x % 128;
			$py = $y % 128;

			$blockX = (int)($px / 32);
			$blockY = (int)($py / 16);
			$block  = $gp_block4[$blockX + $blockY * 4];

			$bx = $px % 32;
			$by = $py % 16;

			$column = (int)($by / 4);

			$cx = $bx;
			$cy = $by % 4;
			$cw = $gp_columnWord4[$column & 1][$cx + $cy * 32];
			$cb = $gp_columnByte4[$cx + $cy * 32];

			$startBlockPos = 0;
			$startBlockPos += $page * 2048;
			$startBlockPos += $block * 64;
			$startBlockPos += $column * 16;
			$startBlockPos += $cw;

			$cb1 = ($startBlockPos * 4) + ($cb >> 1);
			if ( $cb & 1 )
			{
				if ( $odd )
					$log = sprintf("dst[%x] = gsmem[%x] & 0f | (%x << 0) & f0", $cb1, $cb1, $src);
				else
					$log = sprintf("dst[%x] = gsmem[%x] & 0f | (%x << 4) & f0", $cb1, $cb1, $src);
			}
			else
			{
				if( $odd )
					$log = sprintf("dst[%x] = gsmem[%x] & f0 | (%x >> 4) & 0f", $cb1, $cb1, $src);
				else
					$log = sprintf("dst[%x] = gsmem[%x] & f0 | (%x >> 0) & 0f", $cb1, $cb1, $src);
			}
			echo "$log\n";
			$buf[$cb1][$cb & 1] = $log;

			if($odd)
				$src++;
			$odd = ! $odd;
		}
	}

	echo "== ksort() ==\n";
	ksort($buf);
	foreach ( $buf as $b )
	{
		echo "{$b[0]}\n";
		echo "{$b[1]}\n";
	}
	return;
}

function readTexPSMT4($dbw, $rrw, $rrh)
{
	printf("== %s( %x , %x , %x )\n", __FUNCTION__, $dbw, $rrw, $rrh);
	global $gp_block4, $gp_columnWord4, $gp_columnByte4;
	$dbw >>= 1;
	$src = 0;
	$odd = false;

	for ( $y=0; $y < $rrh; $y++ )
	{
		for ( $x=0; $x < $rrw; $x++ )
		{
			$pageX = (int)($x / 128);
			$pageY = (int)($y / 128);
			$page  = $pageX + $pageY * $dbw;

			$px = $x % 128;
			$py = $y % 128;

			$blockX = (int)($px / 32);
			$blockY = (int)($py / 16);
			$block  = $gp_block4[$blockX + $blockY * 4];

			$bx = $px % 32;
			$by = $py % 16;

			$column = (int)($by / 4);

			$cx = $bx;
			$cy = $by % 4;
			$cw = $gp_columnWord4[$column & 1][$cx + $cy * 32];
			$cb = $gp_columnByte4[$cx + $cy * 32];

			$startBlockPos = 0;
			$startBlockPos += $page * 2048;
			$startBlockPos += $block * 64;
			$startBlockPos += $column * 16;
			$startBlockPos += $cw;

			$cb1 = ($startBlockPos * 4) + ($cb >> 1);
			if ( $cb & 1 )
			{
				if ( $odd )
					printf("%x = %x & 0f | (gsmem[%x] >> 0) & f0\n", $src, $src, $cb1);
				else
					printf("%x = %x & f0 | (gsmem[%x] >> 4) & 0f\n", $src, $src, $cb1);
			}
			else
			{
				if ( $odd )
					printf("%x = %x & 0f | (gsmem[%x] << 4) & f0\n", $src, $src, $cb1);
				else
					printf("%x = %x & f0 | (gsmem[%x] << 0) & 0f\n", $src, $src, $cb1);
			}

			if($odd)
				$src++;
			$odd = ! $odd;
		}
	}
	return;
}
//////////////////////////////
$w = 0x80;
$h = 0x80;
// from src to gsmem = unswizzle
//writeTexPSMCT32($w, $w, $h);
//writeTexPSMCT16($w, $w, $h);
//writeTexPSMCT16S($w, $w, $h);
//writeTexPSMZ32($w, $w, $h);
//writeTexPSMZ16($w, $w, $h);
//writeTexPSMZ16S($w, $w, $h);
writeTexPSMT8($w, $w/2, $h/2);
writeTexPSMT4($w, $w/2, $h/4);

// from gsmem to src = swizzle
//readTexPSMCT32($w, $w, $h);
//readTexPSMCT16($w, $w, $h);
//readTexPSMCT16S($w, $w, $h);
//readTexPSMZ32($w, $w, $h);
//readTexPSMZ16($w, $w, $h);
//readTexPSMZ16S($w, $w, $h);
//readTexPSMT8($w, $w, $h);
//readTexPSMT4($w, $w, $h);
