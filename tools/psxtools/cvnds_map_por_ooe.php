<?php
require "common.inc";
//define("DRY_RUN", true);

$gp_pix  = array();
$gp_clut = array();

function sectmap( &$ram, $dir, $mid, $zid, $off, $pid )
{
	printf("=== sectmap( $dir , $mid , $zid , %x , $pid )\n", $off);
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
	$pix['bgzero'] = false;

	$pix['src']['w'] = 16;
	$pix['src']['h'] = 16;

	global $gp_pix, $gp_clut;
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
			$tid = (($b2 >> 12) & 0x1f) + $pid;

			$cid = ($b2 >> 24) & BIT8;
			//$flg3 = $b2 & 0x80000;
			flag_warn("b2", $b2 & 0x80f00);

			$pix['src']['pix'] = rippix4($gp_pix[$tid], $sx, $sy, 16, 16, 0x40, 0x80);
			$pix['src']['pal'] = $gp_clut[$pid][$cid];
			$pix['dx'] = $x;
			$pix['dy'] = $y;

			copypix($pix);
		} // for ( $x=0; $x < $map_w; $x += 0x10 )

		$map4  .= "\n";
		$map8a .= "\n";
		//$map8b .= "\n";
	} // for ( $y=0; $y < $map_h; $y += 0x10 )

	echo "$map4 \n";
	echo "$map8a\n";
	//echo "$map8b\n";
	$fn = sprintf("$dir/cvnds_map/%d/map_%d", $mid, $zid);
	savpix($fn, $pix);
	return;
}
//////////////////////////////
function mappos( $dat )
{
	// fedc ba98  7654 3210  fedc ba98  7654 3210
	// -555 4444  4--3 3333  3322 2222  2111 1111
	// POR 202e14c (r3 << 0x12) >> 0x19 == (r3 >>  7) & 0x7f
	// POR 202e15c (r3 << 0xb ) >> 0x19 == (r3 >> 14) & 0x7f
	// POR 202e620 (r0 << 0x4 ) >> 0x1b == (r0 >> 23) & 0x1f
	//             r1 + (r0 << 3)
	// POR 202e6d0 (r0 << 0x1 ) >> 0x1d == (r0 >> 28) & 0x7
	// POR 20309dc (r0 << 0x19) >> 0x19 == (r0 >>  0) & 0x7f
	$b1 = ($dat >>  0) & 0x7f; // ?counter?
	$b2 = ($dat >>  7) & 0x7f; // map left
	$b3 = ($dat >> 14) & 0x7f; // map top
	$b4 = ($dat >> 23) & 0x1f; // palette set
	$b5 = ($dat >> 28) & 0x7;  //

	$b2 = $b2 * 0x100;
	$b3 = $b3 * 0xc0;
	printf("mappos() %8x = %x  %x  %x  %x  %x\n", $dat, $b1, $b2, $b3, $b4, $b5);
	$map = array($b1, $b2, $b3, $b4, $b5);
	return $map;
}

function monloop( &$ram, &$zone, $off)
{
	while (1)
	{
		$bak = $off;
		$x = str2int($ram, $off+0, 2);
		$y = str2int($ram, $off+2, 2);
			$off += 12;
		if ( $x == 0x7fff || $y == 0x7fff )
			return;

		debug( substr($ram, $bak, 12) );
		$ty = ord( $ram[$bak+5] );
		$id = str2int($ram, $bak+6, 2);
		switch ( $ty )
		{
			// common in all dos + por + ooe
			case 1: // enemies , boss
				$zone[] = sprintf("mon_%d+%d+%d", $id, $x, $y);
				break;
			case 2: // candles , barrels
				$zone[] = sprintf("obj_%d+%d+%d", $id, $x, $y);
				break;
			case 4: // weapons , armor , relic
				$zone[] = sprintf("item_%d+%d+%d", $id, $x, $y);
				break;

			// different on each
			//case 3: // dos candles
			//case 5:
			//case 6: // dos event , always +0+0
			//case 7: // ooe breakable wall
			//case 8: // por event , always +0+0
			//case 9: // por event , always +0+0
			default:
				$err = sprintf("ty%d_%d+%d+%d", $ty, $id, $x, $y);
				$zone[] = $err;
				trigger_error($err, E_USER_WARNING);
				break;
		}
	}
	return;
}

function zoneloop( &$ram, $dir, $mid, $off )
{
	global $gp_clut;
	$id = 0;
	$layout = "";
	$zlst = array();
	// rooms , hallways ...
	while (1)
	{
		$bak = $id;
		$p = $id * 4;
		printf("=== zoneloop( $dir , $mid , %x )\n", $off+$p);
		$sps = str2int($ram, $off + $p, 3); // 2ab630
			$id++;
		if ( $sps == 0 )
			break;
		$zone = array();

		$off1 = str2int($ram, $sps+ 8, 3); // ptr layout (fg + bg1 + bg2 + bg3)
		$off2 = str2int($ram, $sps+12, 3); // ptr flags
		$off3 = str2int($ram, $sps+16, 3); // ptr palettes
		$off4 = str2int($ram, $sps+20, 3); // meta
		$off5 = str2int($ram, $sps+24, 3); // prev/next

		$mappos = str2int($ram, $sps+28, 4);
		$mappos = mappos( $mappos );

		$gp_clut = array();
		while (1)
		{
			$cps = str2int($ram, $off3, 3);
			if ( $cps == 0 )
				break;
			$b1 = str2int($ram, $cps+0, 2);
			$b2 = str2int($ram, $cps+2, 2);
			printf("add CLUT %d @ %x\n", $b2, $cps+4);

			$gp_clut[] = mclut2str($ram, $cps+4, 16, $b2);
			$off3 += 8;
		}

		// room set = fg , bg1 , bg2 , bg3
		$i = 4;
		while ( $i > 0 )
		{
			$i--;
			$p = $off1 + ($i * 0x10) + 12;
			$cps = str2int($ram, $p, 3);
			if ( $cps == 0 || $ram[$p+3] != chr(2) )
				continue;
			$nid = ($bak * 10) + $i;
			$zone[] = "map_{$nid}+0+0";
			sectmap($ram, $dir, $mid, $nid, $cps, $mappos[3]);
		}
		// layer on top of bg + fg
		monloop($ram, $zone, $off4);

		$zlst[] = sprintf("zone_%s+%d+%d", $bak, $mappos[1], $mappos[2]);
		$layout .= sprintf("zone_%d = %s\n", $bak, implode(' , ', $zone));
		//return;
	}
	$layout .= sprintf("main = %s\n", implode(' , ', $zlst));
	$fn = sprintf("$dir/cvnds_map/%d/layout.txt", $mid);
	save_file($fn, $layout);
	return;
}
//////////////////////////////
function arealoop( &$ram, $dir, $mid, $ovid, $bc, $data, $fst, $fbk )
{
	global $gp_pix;
	$id = 0;
	// entrance , underground , library ...
	while (1)
	{
		$bak = $id;
		$p = $id * 4;
		printf("=== arealoop( $dir , $mid , %x , %x , %x , %x , %x )\n", $ovid+$p, $bc+$p, $data+$p, $fst, $fbk);
		$off1 = str2int($ram, $ovid + $p, 3); // 40
		$off2 = str2int($ram, $bc   + $p, 3); // ef5fc
		$off3 = str2int($ram, $data + $p, 3); // 21f664
			$id++;
		if ( $off1 == BIT24 || $off2 == 0 || $off3 == 3 )
			break;
		nds_overlay($ram, $dir, $off1);

		$gp_pix = array();
		while (1)
		{
			$fid = str2int($ram, $off2+0, 3); // 1ca...
			$fty = str2int($ram, $off2+4, 3); // 2...
			if ( $fty == 0 )
				break;
			$fps = $fst + ($fid * $fbk);
			$fn = substr0($ram, $fps+6);

			echo "add PIX @ $fn\n";
			$gp_pix[] = file_get_contents("$dir/data/$fn");
			$off2 += 8;
		}

		$nid = ($mid * 100) + $bak;
		zoneloop( $ram, $dir, $nid, $off3 );
		return;
	}
	return;
}

function maploop( &$ram, $dir, $ovid, $bc, $data, $fst, $fbk )
{
	// dracula castle , village , ecclesia ...
	$id = 0;
	while (1)
	{
		$bak = $id;
		$p = $id * 4;
		printf("=== maploop( $dir , %x , %x , %x , %x , %x )\n", $ovid+$p, $bc+$p, $data+$p, $fst, $fbk);
		$off1 = str2int($ram, $ovid + $p, 3); // b60c4
		$off2 = str2int($ram, $bc   + $p, 3); // d8d68
		$off3 = str2int($ram, $data + $p, 3); // 221ee0
			$id++;
		if ( $off1 == 0 || $off2 == 0 || $off3 == 3 )
			break;
		arealoop( $ram, $dir, $bak, $off1, $off2, $off3, $fst, $fbk );
		return;
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

	$pat = nds_patch($dir, 'cvnds');
	if ( empty($pat) )
		return;
	$ram = nds_ram($dir);
	nds_game( $ram, $dir, $pat['arm9.bin']['game'] );

	arrayhex( $pat['arm9.bin']['files'] );
	arrayhex( $pat['arm9.bin']['stg_ovid'] );
	arrayhex( $pat['arm9.bin']['stg_bc'] );
	arrayhex( $pat['arm9.bin']['stg_data'] );

	$ovid = $pat['arm9.bin']['stg_ovid'][0]; // b60fc
	$bc   = $pat['arm9.bin']['stg_bc'][0];   // d8da0
	$data = $pat['arm9.bin']['stg_data'][0]; // d8fc4

	$fst = $pat['arm9.bin']['files'][0];
	$fbk = $pat['arm9.bin']['files'][2];

	$game = $pat['arm9.bin']['game'][0];
	if ( $game == 'por' || $game == 'ooe' )
		maploop( $ram, $dir, $ovid, $bc, $data, $fst, $fbk );
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );

/*
	POR map 0-0 pos (ram 2106320)
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

	OOE map 0-0 pos (ram 2106a74)
	12    ##-###########-xx
	13    ##
	14    ##-#####-#####
	15             #####-##-#####-xx
	16                   ##
	17             #####-##
	18 xx-########-#####-ss
	-- 08 09 0a 0b 0c 0d 0e 0f 10 11



	DOS map_0 (ram 20f6e90)
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

	// DOS = 2 x 2-bytes (0x1c + 0x1e)
	// DOS-1e 2026210 (r2 << 9) >> 9 == (r2 >> 0) & 0x7f
	// DOS-1e 2026224 (r2 << 2) >> 9 == (r2 >> 7) & 0x7f
	// DOS-1e 202737c (r3 << 2) >> 9 == (r3 >> 7) & 0x7f
	//                (r3 << 2 )
	//        2027398 (r4 << 9) >> 7 == (r4 << 2) & 0x1ff
	$b1 = 0; //
	$b2 = ($dat >> 0x10) & 0x7f; // map left
	$b3 = ($dat >> 0x17) & 0x7f; // map top
	$b4 = 0; //
	$b5 = 0; //
*/
