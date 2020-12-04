<?php
/*
[license]
[/license]
 */
/*
 * Special Thanks to:
 *   Zone File Technical Documentation by Nyxojaele (Dec 26, 2010)
 *   romhacking.net/documents/528/
 */
require "common.inc";

//define("DRY_RUN", true);

// map files are loaded to RAM 80180000
// offsets here are RAM pointers
function ramint( &$file, $pos )
{
	$int = str2int($file, $pos, 3);
	if ( $int )
		$int -= 0x180000;
	else
		printf("ERROR ramint zero @ %x\n", $pos);
	return $int;
}
//////////////////////////////
function monlist( &$meta, &$data, $off)
{
	while (1)
	{
		$bak = $off;
			$off += 10;
		$sx = sint16( $meta[$bak+0] . $meta[$bak+1] );
		$sy = sint16( $meta[$bak+2] . $meta[$bak+3] );
		if ( $sx == -2 && $sy == -2 )
			continue;
		if ( $sx == -1 && $sy == -1 )
			return;
		$v9 = ord( $meta[$bak+9] );
		//$data[] = "mon_$v9+$sx+$sy";
		$data[] = "mon_0+$sx+$sy";
	}
	return;
}
//////////////////////////////
function sotn( $dir )
{
	if ( ! is_dir($dir) )
		return;
	if ( ! file_exists("$dir/setup.txt") )
		return;

	$setup = array();
	foreach ( file("$dir/setup.txt") as $v )
	{
		$v = preg_replace('|[\s]+|', '', $v);
		if ( empty($v) )
			continue;
		list($k,$v) = explode('=', $v);
		$setup[$k] = $v;
	}
	$meta  = file_get_contents("$dir/st.2");

	//$off1  = ramint($meta, 0x00); // func() entity attack?
	//$off2  = ramint($meta, 0x04); // func() respawn entity
	//$off3  = ramint($meta, 0x08); // func() respawn screen check by frames
	//$off4  = ramint($meta, 0x0c); // func() respawn room check
	$off5  = ramint($meta, 0x10); // zone layout
	$off6  = ramint($meta, 0x14); // entity sprite
	//$off7  = ramint($meta, 0x18);
	//$off8  = ramint($meta, 0x1c);
	$off9  = ramint($meta, 0x20); // zone layout def
	$off10 = ramint($meta, 0x24); // entity sprite def
	//$off11 = ramint($meta, 0x28); // func() entity AI?
	//$off12 = ramint($meta, 0x2c); // <- $off6 can refer here = are/cat/dai.bin ...

	$v = explode(',', $setup['mon_list']);
	$monlist = hexdec( $v[0] );
	ob_start();

	$st = $off5;
	$main = array();
	$id = 0;
	while (1)
	{
		$x1 = ord( $meta[$st+0] );
		$y1 = ord( $meta[$st+1] );
		$x2 = ord( $meta[$st+2] );
		$y2 = ord( $meta[$st+3] );
		if ( $x1 > $x2 || $y1 > $y2 )
			break;

		$b1 = ord( $meta[$st+4] ); // tile layout id
		$b2 = ord( $meta[$st+5] ); // tile gfx id
		$b3 = ord( $meta[$st+6] ); // entity gfx id
		$b4 = ord( $meta[$st+7] ); // entity layout id
		if ( $b2 != BIT8 )
		{
			$zid = "zone_$id";
			$main[] = sprintf("%s+%d+%d", $zid, $x1*0x100 , $y1*0x100);

			$data = array();
			$bg = ramint($meta, $off9 + $b1*8 + 0);
				$data[] = "map_$bg+0+0";
			$bg = ramint($meta, $off9 + $b1*8 + 4);
				$data[] = "map_$bg+0+0";
			$en = ramint($meta, $monlist + $b4*4);
				monlist($meta, $data, $en);

			printf("%s = %s\n", $zid, implode(' , ', $data));
		}
		$id++;
		$st += 8;
	} // while (1)
	printf("main = %s\n", implode(' , ', $main));

	$txt = ob_get_clean();
	echo $txt;
	save_file("$dir/layout.txt", $txt);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	sotn( $argv[$i] );

/*
dre.bin [233f4] - whole file
    0-   40  [----] file header , pointers
   40-   48  [   0] pointers @ fdd8-ff54
   48-   a0  -
   a0-   e4  [  e4] palette data (has ptr @ a858/b1b8)
   e4-   e8  [   0] pointers @ a0-e4
   e8-  118  [ 118] layer data @ 0x10 (has ptr @ b1b8/fdc8)
  118-  128  [   0] pointers @ e8-118 (in 2/set)
  128-  1d0  [ 1d0] sprites data (has ptr @ 1cfc)
  1d0-  220  [   0] pointers @ 128-1d0
  220-  2f4  pointers @ 1620-167c
  2f4-  3c8  pointers @ 167c-16d6
  3c8-  458  pointers @ func()
  458-
     -  f94
  f94- 10d4  [1158] ?string data?
 10d4-
     - 1108
 1108- 1130  pointers @ 115ac-115fc
 1130-
     - 1158
 1158- 11b8  pointers @ f94-10d4
 11b8-
     - 11d8
 11d8- 1270  [1270] ?string data?
 1270- 1284  pointers @ 11d8-1270
 1284-
     - 1424
 1424- 1438  [1438]
 1438- 14b0  ?data? (has ptr @ 1424)
 14b0- 14c4  [   0] room id data @ 0x08
 14c4- 155c  [155c]
 155c- 15a8  pointers @ 14c4-155c
 15a8-
     - 1620
 1620- 167c  [ 220] monster layout by x @ 0x0a
 167c- 16d8  [ 2f4] monster layout by y @ 0x0a
 16d8- 16e0  -
 16e0- 1cfc  talk script + text
 1cfc- 7fd8  [ 128] sprites (4-bit , 128x128 , compressed)
 7fd8- a858  talk portrait (8-bit , 64x54)
 a858- abb8  [  a0] palette  16 colors
 abb8- b1b8  [  a0] palette 256 colors
 b1b8- b9b8  [  e8] room layout (16-bit)
 b9b8-
     - bdc8
 bdc8- cdc8  [fdc8] map sx/sy (in 256x256)
 cdc8- ddc8  [fdc8] map sx/sy (in 16x16)
 ddc8- edc8  [fdc8] map palette
 edc8- fdc8  [fdc8] map collusion
 fdc8- fdd8  [  e8] pointers @ bdc8-fdc8 (in 4/set)
 fdd8- ff54  [  40] pointers @ ff54-1138c
 ff54-1138c  [ffd8] parts assembly data
1138c-1169c  pointers @ switch cases + strings ascii/sjis
1169c-16c54  mips code
16c54-233f4  [   0] mips code

top.bin
	10 -> 1bb4 -> 0,0,2,9
	c -> 2fbe0 -> [asm]740[9]/814[9] -> 2426/29d2
	-2,-2 = start
	-1,-1 = end
	00-01  pos x
	02-03  pos y
	04
	05
	06
	07
	08
	09  object id
 */
