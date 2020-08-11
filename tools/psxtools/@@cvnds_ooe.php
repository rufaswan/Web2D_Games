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
map 0-0 room 0 mon_data
	= 2cfdc0

bcfunc = 48358 -> 48278 -> 3ba58
	obj 67-0 curtain
	obj 2-13 statue
	obj 2-a cendlier
	obj 16 chest

spikes   aflo00.dat  891  36e
candles  cand00.dat  8ad  3c4  3c5
chest    coffer00.dat  8b4  3e2
lamp     dest00-12.dat  8bf-8cb  401-40d

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
