<?php
require "common.inc";
require "common-guest.inc";

//define("DRY_RUN", true);

$gp_patch = array();
$gp_pix_a = array();
$gp_pix_r = array();
$gp_clut = array();
$gp_game = "";
// MARL = Map Area Room Layer

function layerloop( &$ram, $dir, $MA, $RL, $off, $mp3 )
{
	printf("=== layerloop( $dir , $MA , $RL , %x , $mp3 )\n", $off);
	$map_w = ord( $ram[$off+0] ) * 0x100;
	$map_h = ord( $ram[$off+1] ) * 0xc0;
		zero_watch("ram2", $ram[$off+2]);
		zero_watch("ram3", $ram[$off+3]);
	$off1 = str2int($ram, $off+ 4, 3); // tile def
	$off2 = str2int($ram, $off+ 8, 3); // collusion
	$off3 = str2int($ram, $off+12, 3); // tile layout
		$off += 16;
	echo "map : $map_w x $map_h\n";
	printf("off : %6x , %6x , %6x\n", $off1, $off2, $off3);

	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $map_w;
	$pix['rgba']['h'] = $map_h;
	$pix['rgba']['pix'] = canvpix($map_w,$map_h);

	$pix['src']['w'] = 16;
	$pix['src']['h'] = 16;

	global $gp_pix_r, $gp_clut;
	$pos = $off3;
	$map4  = "";
	$map8a = "";
	//$map8b = "";
	for ( $y=0; $y < $map_h; $y += 0x10 )
	{
		for ( $x=0; $x < $map_w; $x += 0x10 )
		{
			$dat = str2int($ram, $pos, 2);
				$pos += 2;
			$map4 .= sprintf("%4x ", $dat);

			$b1 = ($dat & 0x3ff) - 1;

			$b2 = ( $b1 < 0 ) ? 0 : str2int($ram, $off1 + ($b1*4), 4);;
			$map8a .= sprintf("%8x ", $b2);

			//$b3 = ( $b1 < 0 ) ? 0 : str2int($ram, $off2 + ($b1*4), 4);;
			//$map8b .= sprintf("%8x ", $b3);

			if ( $b1 < 0 )
				continue;

			// flag can be set on both $dat and $b2
			// if both set , cancel out
			$f1 = ($dat & 0x4000 ) ? 1 : 0;
			$f2 = ($b2  & 0x20000) ? 1 : 0;
				$pix['hflip'] = $f1 ^ $f2;
			$f1 = ($dat & 0x8000 ) ? 1 : 0;
			$f2 = ($b2  & 0x40000) ? 1 : 0;
				$pix['vflip'] = $f1 ^ $f2;
			flag_warn("dat", $dat & 0x3c00);

			$sx = (($b2 >> 0) & 0x07) * 0x10;
			$sy = (($b2 >> 4) & 0x0f) * 0x20;
			if ( $b2 & 0x08 )
				$sy += 0x10;
			$tid = (($b2 >> 12) & 0x1f);

			$cid = ($b2 >> 24) & BIT8;
			//$flg3 = $b2 & 0x80000;
			flag_warn("b2", $b2 & 0x80f00);

			$pix['src']['pix'] = "";
			$pix['src']['pal'] = "";
			$pix['dx'] = $x;
			$pix['dy'] = $y;
			if ( $gp_pix_r[$tid][1] == 4 )
			{
				$pix['src']['pix'] = rippix4($gp_pix_r[$tid][0], $sx, $sy, 16, 16, 0x80, 0x80);
				$pix['src']['pal'] = strpal555($gp_clut[$mp3], $cid*0x20, 0x10);
			}
			if ( $gp_pix_r[$tid][1] == 8 )
			{
				$pix['src']['pix'] = rippix8($gp_pix_r[$tid][0], $sx, $sy, 16, 16, 0x80, 0x80);
				$pix['src']['pal'] = strpal555($gp_clut[$mp3], $cid*0x20, 0x100);
			}
			$pix['bgzero'] = 0;

			copypix($pix);
		} // for ( $x=0; $x < $map_w; $x += 0x10 )

		$map4  .= "\n";
		$map8a .= "\n";
		//$map8b .= "\n";
	} // for ( $y=0; $y < $map_h; $y += 0x10 )

	echo "$map4 \n";
	echo "$map8a\n";
	//echo "$map8b\n";
	$fn = sprintf("$dir/cvnds_map/ma_%04d/l_%04d", $MA, $RL);
	savpix($fn, $pix);
	return;
}
//////////////////////////////
function mappos( $dat )
{
	global $gp_game;
	if ( $gp_game == 'por' || $gp_game == 'ooe' )
	{
		// fedc ba98  7654 3210  fedc ba98  7654 3210
		// -555 4444  4--3 3333  3322 2222  2111 1111
		// POR 202e14c (r3 << 0x12) >> 0x19 == (r3 >>  7) & 0x7f
		// POR 202e15c (r3 << 0xb ) >> 0x19 == (r3 >> 14) & 0x7f
		// POR 202e620 (r0 << 0x4 ) >> 0x1b == (r0 >> 23) & 0x1f
		//             r1 + (r0 << 3)
		// POR 202e6d0 (r0 << 0x1 ) >> 0x1d == (r0 >> 28) & 0x7
		// POR 20309dc (r0 << 0x19) >> 0x19 == (r0 >>  0) & 0x7f
		$b1 = ($dat >>  0) & 0x7f; //
		$b2 = ($dat >>  7) & 0x7f; // map left
		$b3 = ($dat >> 14) & 0x7f; // map top
		$b4 = ($dat >> 23) & 0x1f; // palette set
		$b5 = ($dat >> 28) & 0x7;  //
	}
	if ( $gp_game == 'dos' )
	{
		// DOS = 2 x 2-bytes (0x1c + 0x1e)
		// DOS-1e 2026210 (r2 << 9) >> 9 == (r2 >> 0) & 0x7f
		// DOS-1e 2026224 (r2 << 2) >> 9 == (r2 >> 7) & 0x7f
		// DOS-1e 202737c (r3 << 2) >> 9 == (r3 >> 7) & 0x7f
		//                (r3 << 2)
		//        2027398 (r4 << 9) >> 7 == (r4 << 2) & 0x1ff
		$b1 = 0; //
		$b2 = ($dat >> 0x10) & 0x7f; // map left
		$b3 = ($dat >> 0x17) & 0x7f; // map top
		$b4 = 0; //
		$b5 = 0; //
	}

	$b2 = $b2 * 0x100;
	$b3 = $b3 * 0xc0;
	printf("mappos() %8x = %x  %x  %x  %x  %x\n", $dat, $b1, $b2, $b3, $b4, $b5);
	$map = array($b1, $b2, $b3, $b4, $b5);
	return $map;
}

function monobj( &$ram, &$room, $off)
{
	while (1)
	{
		$bak = $off;
		$x = sint16( substr($ram, $off+0, 2) );
		$y = sint16( substr($ram, $off+2, 2) );
			$off += 12;
		if ( $x == 0x7fff || $y == 0x7fff )
			return;

		debug( substr($ram, $bak, 12) );
		$ty = ord( $ram[$bak+5] );
		$id = str2int($ram, $bak+ 6, 2);
		$v1 = str2int($ram, $bak+ 8, 2);
		$v2 = str2int($ram, $bak+10, 2);

		// type
		// 1  (common) monster , boss
		// 2  (common) candles
		// 3  candles/dos
		// 4  (common) items , weapons , glyphs
		// 5
		// 6  events/dos , always +0+0
		// 7  secret/ooe
		// 8  events/por , always +0+0
		// 9  events/por , always +0+0
		$room[] = sprintf("en%d/%d_%d_%d+%d+%d", $ty, $id, $v1, $v2, $x, $y);
	}
	return;
}

function roomloop( &$ram, $dir, $MA, $off )
{
	global $gp_clut, $gp_pix_a, $gp_pix_r;
	$id = 0;
	$layout = "";
	$rlst = array();
	// rooms , hallways ...
	while (1)
	{
		$R = $id;
			$id++;
		$p = $R * 4;
		printf("=== roomloop( $dir , $MA , %x )\n", $off+$p);
		$sps = str2int($ram, $off + $p, 3); // 2ab630
		if ( $sps == 0 )
			break;
		$room = array();

		$off1 = str2int($ram, $sps+ 8, 3); // ptr layout (fg + bg1 + bg2 + bg3)
		$off2 = str2int($ram, $sps+12, 3); // ptr bc/f_xxx.dat
		$off3 = str2int($ram, $sps+16, 3); // ptr palettes
		$off4 = str2int($ram, $sps+20, 3); // meta
		$off5 = str2int($ram, $sps+24, 3); // prev/next

		$mappos = str2int($ram, $sps+28, 4);
		$mappos = mappos( $mappos );

		// pix data for a room
		$gp_pix_r = array();
		while (1)
		{
			$b1 = str2int($ram, $off2, 3);
			if ( $b1 == 0 )
				break;

			$b1 = file_get_contents( $gp_pix_a[$b1] );
			$psz = strlen($b1);
			if ( $psz == 0x4000 )
				$gp_pix_r[] = array($b1, 8);
			else
			if ( $psz == 0x2000 )
				$gp_pix_r[] = array($b1, 4);

			$off2 += 8;
		}

		// clut data for a room
		$gp_clut = array();
		while (1)
		{
			$cps = str2int($ram, $off3, 3);
			if ( $cps == 0 )
				break;
			$b1 = str2int($ram, $cps+0, 2);
			$b2 = str2int($ram, $cps+2, 2);
			printf("add CLUT %d @ %x\n", $b2, $cps+4);

			$gp_clut[] = substr($ram, $cps+4, $b2*0x20);
			$off3 += 8;
		}

		// room set = fg , bg1 , bg2 , bg3
		$L = 4;
		while ( $L > 0 )
		{
			$L--;
			$p = $off1 + ($L * 0x10) + 12;
			$cps = str2int($ram, $p, 3);
			if ( $cps == 0 || $ram[$p+3] != chr(2) )
				continue;
			$RL = ($R * 10) + $L;
			$room[] = sprintf("l_%04d+0+0", $RL);
			layerloop($ram, $dir, $MA, $RL, $cps, $mappos[3]);
		}
		// layer on top of bg + fg
		monobj($ram, $room, $off4);

		$rlst[] = sprintf("r_%04d+%d+%d", $R, $mappos[1], $mappos[2]);
		$layout .= sprintf("r_%04d = %s\n", $R, implode(' , ', $room));
		//return;
	}
	$layout .= sprintf("main = %s\n", implode(' , ', $rlst));
	$fn = sprintf("$dir/cvnds_map/ma_%04d/layout.txt", $MA);
	save_file($fn, $layout);
	return;
}
//////////////////////////////
function arealoop( &$ram, $dir, $M, $ovid, $bc, $data )
{
	global $gp_pix_a, $gp_patch;
	$id = 0;
	$fst = $gp_patch['ndsram']['files'][0];
	$fbk = $gp_patch['ndsram']['files'][2];
	// entrance , library , clock tower ...
	while (1)
	{
		$A = $id;
			$id++;
		$p = $A * 4;
		printf("=== arealoop( $dir , $M , %x , %x , %x )\n", $ovid+$p, $bc+$p, $data+$p);
		$off1 = str2int($ram, $ovid + $p, 3); // 40
		$off2 = str2int($ram, $bc   + $p, 3); // ef5fc
		$off3 = str2int($ram, $data + $p, 3); // 21f664
		if ( $off1 == BIT24 || $off2 == 0 || $off3 == 0 )
			break;
		nds_overlay($ram, $dir, $off1);

		// pix data for an area
		$gp_pix_a = array();
		while (1)
		{
			$fid = str2int($ram, $off2+0, 3); // 1ca...
			$fty = str2int($ram, $off2+4, 3); // 2...
				$off2 += 8;
			if ( $fty == 0 )
				break;
			$fps = $fst + ($fid * $fbk);
			$fp = str2int($ram, $fps+0, 3);
			$fn = substr0($ram, $fps+6);
			echo "add PIX @ $fn\n";
			$gp_pix_a[$fp] = "$dir/data/$fn";
		}

		$MA = ($M * 100) + $A;
		roomloop( $ram, $dir, $MA, $off3 );
		//return;
	}
	return;
}

function maploop( &$ram, $dir, $ovid, $bc, $data )
{
	// dracula castle , 13th street , monastery ...
	$id = 0;
	while (1)
	{
		$M = $id;
			$id++;
		$p = $M * 4;
		printf("=== maploop( $dir , %x , %x , %x )\n", $ovid+$p, $bc+$p, $data+$p);
		$off1 = str2int($ram, $ovid + $p, 3); // b60c4
		$off2 = str2int($ram, $bc   + $p, 3); // d8d68
		$off3 = str2int($ram, $data + $p, 3); // 221ee0
		if ( $off1 == 0 || $off2 == 0 || $off3 == 0 )
			break;
		arealoop( $ram, $dir, $M, $off1, $off2, $off3 );
		//return;
	}
	return;
}
//////////////////////////////
function nds_game( &$ram, $dir, $game )
{
	foreach ( $game as $g )
	{
		if ( strpos($g, 'ov-') === false )
			continue;
		nds_overlay( $ram, $dir, $g );
	}
	return;
}

function cvnds( $dir )
{
	if ( ! is_dir($dir) )
		return;

	global $gp_patch;
	$gp_patch = nds_patch($dir, 'cvnds');
	if ( empty($gp_patch) )
		return;
	$ram = nds_ram($dir);
	nds_game( $ram, $dir, $gp_patch['ndsram']['game'] );

	arrayhex( $gp_patch['ndsram']['files'] );
	arrayhex( $gp_patch['ndsram']['stg_ovid'] );
	arrayhex( $gp_patch['ndsram']['stg_bc'] );
	arrayhex( $gp_patch['ndsram']['stg_data'] );

	$ovid = $gp_patch['ndsram']['stg_ovid'][0]; // b60fc
	$bc   = $gp_patch['ndsram']['stg_bc'][0];   // d8da0
	$data = $gp_patch['ndsram']['stg_data'][0]; // d8fc4

	global $gp_game;
	$gp_game = $gp_patch['ndsram']['game'][0];
	if ( $gp_game == 'por' || $gp_game == 'ooe' )
		maploop( $ram, $dir, $ovid, $bc, $data );
	if ( $gp_game == 'dos' )
		arealoop( $ram, $dir, 0, $ovid, $bc, $data );
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );

/*
	POR map 0-0 pos (ram 2106320 , overlay 78 , dracula castle entrance)
	1b          #####
	1c ########-#####-#####-xx
	1d          #####
	1e             ##
	1f             ##
	20             ##
	21             ##
	22             ##-tt
	23          aa-##-ss
	-- 01 02 03 04 05 06 07 08


	OOE map 0-0 pos (ram 2106a74 , overlay 64 , dracula castle entrance)
	12    ##-###########-xx
	13    ##
	14    ##-#####-#####
	15             #####-##-#####-xx
	16                   ##
	17             #####-##
	18 xx-########-#####-ss
	-- 08 09 0a 0b 0c 0d 0e 0f 10 11


	DOS map_0 pos (ram 20f6e90 , overlay 14 , lost village)
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
