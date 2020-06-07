<?php
require "common.inc";

function cvds( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$head = file_get_contents("$dir/header.bin");
	if ( empty($head) )  return;

	$mgc = substr($head, 12, 4);
	$func = "cvds_" . strtolower($mgc);

	if ( ! function_exists($func) )
		return;

	$ram = ndsram($dir);
	$func($ram, $dir);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvds( $argv[$i] );

/*
overlays - 1dcea0 1ffbc0 1ffde0 223b60 2b6f60 2c1ba0
	ecclesia , ruvas forest
		1dcea0 = 0  , 0  <- ALL SAME
		1ffbc0 = -  , -  <- ALL SAME
		1ffde0 = 18 , 18 <- ALL SAME
		223b60 = 21 , 21 <- ALL SAME
		2b6f60 = 38 , 38 <- ALL SAME
		2c1ba0 = 41 , 43 <- DIFF
	2c1ba0
		41 = ecclesia / exit
		42 = training hall / exit
		43 = ruvas forest / exit 1+2
		44 = argile swamp / exit 1+2
		45 = kalidus channel / exit 1+2 (above ground)
		46 = kalidus channel / exit 3+4 (under ground)
		47 = somnus reef / exit 1+2
		49 = minera prison island / exit 1 (west)
		51 = minera prison island / exit 2 (east)
		52 = lighthouse / exit 1+2
		53 = tymeo mountain / exit 1+2+3
		55 = tristis pass / exit 1+2
		57 = large cavern / exit
		58 = giant dwelling / exit 1+2
		59 = mystery manor / exit
		60 = misty forest road / exit 1+2
		62 = oblivion ridge / exit
		63 = skeleton cave / exit
		66 = dracula castle / exit 1
		67 = dracula castle / exit 2 (secret)
		78 = monastery / exit

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

	prefix ?
		eac = minera prison island
		haa = misty forest road
		jaa = skeleton cave
		laa = large cavern
		maa = dracula castle/library
		mba = dracula castle/kitchen
		oaa = dracula castle/clock tower
		qaa = dracula castle/armory
		saa = monastery

saa = (files) 2df-2f6
	= (arm9.bin) eefbc/data , d8cf4/pointer -> d8c98/begin
	= (arm9.bin) d8de8/pointer -> d8da0/begin -> 46380/func
	= d8cf4[0] , d8da0[12][0]
 */
