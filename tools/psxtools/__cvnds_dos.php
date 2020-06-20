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
overlays

	dos alru une
		19e460 = 0
		22faa0 = 1
		2999c0 = 2
		29d6a0 = 3
		2b89a0 = 4
		2d5ec0 = 5
		2d94e0 = 17
		2fea00 = -

0 = 1 x
1 = 1 ok
2 = 3 x
3 = 1 x
4 = 5 x
5 = 2 x
6 = 2 x
7 = 3 ok
8 = 2 x
9 = 2 x
10 = 1 x
11 = 2 ok
12 = 4 x
13 = 4 x
14 = 4 ok
15 = 2 x
16 = 2 ok

map_0 (ram 20f6e90)
13             ########-xx
14             ########
15    ##-##### ########    ########### ##### #####
16 #####-#####-########-##-###########-#####-#####-xx
17    tt-#####-##-#####-##-ss          #####
18    ##-#####-## #####-#####-#####-##-#####-xx
19 ss-##-##### ##-##### #####
1a    ##-#####-## #####-#####-#####
1b       ##       ##
1c       ##       ##
1d       ##-#####-##
1e                ##
1f                ##
20                ##-xx
21                ##
22                ##
23                ##
24                ##-xx
-- 01 02 03 04 05 06 07 08 09 0a 0b 0c 0d 0e 0f 10 11
 */
