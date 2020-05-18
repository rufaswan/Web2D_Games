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
 */
