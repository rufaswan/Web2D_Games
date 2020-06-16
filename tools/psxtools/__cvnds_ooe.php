<?php
require "common.inc";

function cvnds( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$head = file_get_contents("$dir/header.bin");
	if ( empty($head) )  return;

	$mgc = substr($head, 12, 4);
	$func = "cvnds_" . strtolower($mgc);

	if ( ! function_exists($func) )
		return;

	$ram = ndsram($dir);
	$func($ram, $dir);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );

/*
overlays - 1dcea0 1ffbc0 1ffde0 223b60 2b6f60 2c1ba0
	ecclesia , ruvas forest
		1dcea0 = 0  , 0  <- ALL SAME
		1ffbc0 = -  , -  <- ALL SAME
		1ffde0 = 18 , 18 <- ALL SAME
		223b60 = 21 , 21 <- ALL SAME
		2b6f60 = 38 , 38 <- ALL SAME
		2c1ba0 = 41 , 43 <- DIFF
	d8a3c       = stage        = 27-54
	d8c90,d8c88 = map (id,sub) = 00-13,??

	total stages = 46
		13,2,1,1
		 1,1,2,2
		 3,1,2,2
		 1,1,1,1
		 2,1,2,6

	map id -> overlay (world map selected)
		00  40-4c  dracula castle
		01  27-28  wygol village
		02  29     ecclesia
		03  2a     training hall
		04  2b     ruvas forest
		05  2c     argile swamp
		06  2d-2e  kalidus channel
		07  2f     somnus reef
		08  31-33  minera prison island
		09  34     lighthouse
		0a  35-36  tymeo mountain
		0b  37-38  tristis pass
		0c  39     large cavern
		0d  3a     giant dwelling
		0e  3b     mystery manor
		0f  3c     misty forest road
		10  3d-3e  oblivion ridge
		11  3f     skeleton cave
		12  4d-4e  monastery
		13  4f-54


ov-4d = 2d7160 -> 2af3b8 , 2d76a0 -> 2af3e8
	0  ptr layout
	1  ptr flags
	2  ptr clut
	3  ?position?
	4  ?back?
-> 2218b4-22190c

clut-2c1ba0
	map = 43 44 49 53 55 60 72 73 78 80 81 82 84
	ptr = f414c f426c f4270 f427c f4284 f4290 f42d4

2c1ba0/ruvas - f690
2c1ba0/monastery - f984
	2d5114  2d5918  2d611c  2d6920




	monastery / shanoa , albus
		1dcea0 = 0  , 0  <- ALL SAME
		1ffbc0 = 4  , -  <- DIFF
		1ffde0 = 18 , 18 <- ALL SAME
		223b60 = 21 , 21 <- ALL SAME
		2b6f60 = 38 , 38 <- ALL SAME
		2c1ba0 = 78 , 78 <- ALL SAME

	incomplete monster data
		needle , no tex     ,  17/needles     , ov/?20?
		kyon   , no pal     , 104/jiang shi   ,
		armag  , no tex+pal , 109/arthroverta , ov/23
		kani   , no pal     , 110/brachyura   ,
		man    , no tex+pal , 111/man-eater   , ov/25
		mael   , no tex+pal , 112/rusalka     , ov/26
		fran   , no pal     , 113/goliath     ,
		grav   , no pal     , 114/gravedorcus ,
		albus  , no tex     , 115/albus       , ov/35/boss , ov/21/play
		bar    , no tex+pal , 116/barlowe     , ov/36
		wallm  , no tex+pal , 117/wallman     , ov/27
		alessi , no tex+pal , 118/blackmore   , ov/34
		cent   , no pal     , 119/eligor      ,
		sgami  , no tex+pal , 120/death       , ov/24
		dra    , no tex+pal , 121/dracula     , ov/33
 */
