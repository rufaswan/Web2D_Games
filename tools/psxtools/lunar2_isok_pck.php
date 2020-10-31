<?php
require "common.inc";
require "common-quad.inc";
require "lunar2.inc";

define("CANV_S", 0x300);
define("SCALE", 1.0);
//define("DRY_RUN", true);

$gp_pix  = "";
$gp_clut = "";

function sectparts( &$meta, $dir )
{
	printf("== sectparts( $dir )\n");

	$ceil = int_ceil(CANV_S * SCALE, 2);
	$pix = COPYPIX_DEF();
	$pix['rgba']['w'] = $ceil;
	$pix['rgba']['h'] = $ceil;
	$pix['rgba']['pix'] = canvpix($ceil,$ceil);

	global $gp_pix, $gp_clut;
	$len = strlen($meta);
	$sw = strlen($gp_pix) / 0x100;
	$sh = 0x100;
	for ( $i=0; $i < $len; $i += 0x16 )
	{
		$b1 = ord( $meta[$i+0] );
		$b2 = ord( $meta[$i+1] );
		$sx = ord( $meta[$i+2] );
		$sy = ord( $meta[$i+3] );
		$w  = ord( $meta[$i+4] );
		$h  = ord( $meta[$i+5] );

		$pix['src']['w'] = $w;
		$pix['src']['h'] = $h;
		$pix['src']['pix'] = rippix8($gp_pix, $b2*0x80+$sx, $sy, $w, $h, $sw, $sh);
		$pix['src']['pal'] = $gp_clut;
		$pix['bgzero'] = 0;

		sectquad($pix, $meta, $i, $ceil/2);
		printf("parts() %02x , %02x\n", $b1, $b2);

		copyquad($pix, 1);
	} // for ( $i=0; $i < $len; $i += 0x16 )

	savpix($dir, $pix, true);
	return;
}

function sectmeta( &$meta, $dir )
{
	printf("== sectmeta( $dir )\n");
	//save_file("$dir/meta", $meta);

	$ed = str2int($meta, 4, 4);
	$st = str2int($meta, 0, 4);
	if ( $st < 8 )
		return;
	for ( $i=$st; $i < $ed; $i += 4 )
	{
		$num = str2int($meta, $i+0, 2);
		$off = str2int($meta, $i+2, 2);

		$pos = $ed + ($off * 0x16);
		$sub = substr ($meta, $pos, $num*0x16);
		printf("meta() %x , %x\n", $pos, $num*0x16);

		$fn = sprintf("$dir/%04d", ($i-$st)/4);
		sectparts($sub, $fn);
	} // for ( $i=$st; $i < $ed; $i += 4 )

	// tileset
		global $gp_pix, $gp_clut;
		$w = strlen($pix) / 0x100;
		$h = 0x100;

		$clut = "CLUT";
		$clut .= chrint(0x100, 4);
		$clut .= chrint($w, 4);
		$clut .= chrint($h, 4);
		$clut .= $gp_clut;
		$clut .= $gp_pix;
		save_file("$dir/pix.clut", $clut);
	return;
}
//////////////////////////////
function loadpck( &$file, $fname )
{
	$num = str2int($file, 0, 4);
	printf("== loadpck( $fname ) = $num\n");

	$ed = strlen($file);
	$st = 4;
	$pck = array();
	while ( $st < $ed )
	{
		$siz = str2int($file, $st, 4);
			$st += 4;
		if ( $siz == 0 )
			break;
		printf("%3d  %6x  %6x\n", count($pck), $st, $siz);
		$pck[] = substr($file, $st, $siz);
			$st += $siz;
	}

	$file = $pck;
	return;
}
//////////////////////////////
function pck_btlbk( $fname )
{
	echo "== pck_btlbk( $fname )\n";
	$pck = load_file($fname);
	if ( empty($pck) )  return;
	loadpck($pck, $fname); // all 2 (pix , clut)

	$w = strlen($pck[0]) / 0x100;
	$h = 0x100;

	$clut = "CLUT";
	$clut .= chrint(0x100, 4);
	$clut .= chrint($w, 4);
	$clut .= chrint($h, 4);
	$clut .= pal555($pck[1]);
	$clut .= $pck[0];

	file_put_contents("$fname.clut", $clut);
	return;
}

function pck_pc( $fname )
{
	echo "== pck_pc( $fname )\n";
	$pck = load_file($fname);
	if ( empty($pck) )  return;
	loadpck($pck, $fname); // 3 (meta , meta , pix) 2 (meta , pix)

	$dir = str_replace('.', '_', $fname);
	$cnt = count($pck);

	// require palette from sysspr.pck
	global $gp_pix, $gp_clut;
	if ( $cnt == 2 )
	{
		$gp_pix  = $pck[1];
		//$gp_clut = grayclut(0x100);
		sectmeta($pck[0], $dir);
	}
	if ( $cnt == 3 )
	{
		$gp_pix  = $pck[2];
		//$gp_clut = grayclut(0x100);
		sectmeta($pck[0], "$dir/0");
		sectmeta($pck[1], "$dir/1");
	}
	return;
}

function pck_mnsprcha( $sprn, $chan )
{
	echo "== pck_mnsprcha( $sprn, $chan )\n";
	$spr = load_file($sprn);
	$cha = load_file($chan);
	if ( empty($spr) || empty($cha) )
		return;
	loadpck($spr, $sprn); // all 15+ (??? , ??? , clut , ??? , ??? , ??? , meta ...)
	loadpck($cha, $chan); // all  1  (pix)

	$dir = str_replace('.', '_', $sprn);

	global $gp_pix, $gp_clut;
	$gp_pix  = $cha[0];
	$gp_clut = pal555( $spr[2] );

	$cnt = count($spr) - 6;
	for ( $i=0; $i < $cnt; $i++ )
		sectmeta($spr[6+$i], "$dir/$i");
	return;
}

function pck_efsprcha( $sprn, $chan )
{
	echo "== pck_efsprcha( $sprn, $chan )\n";
	$spr = load_file($sprn);
	$cha = load_file($chan);
	if ( empty($spr) || empty($cha) )
		return;
	loadpck($spr, $sprn); // all 2 (??? , meta)
	loadpck($cha, $chan); // all 2 (pix , clut)

	$dir = str_replace('.', '_', $sprn);

	global $gp_pix, $gp_clut;
	$gp_pix  = $cha[0];
	$gp_clut = pal555( $cha[1] );
	sectmeta($spr[1], $dir);
	return;
}

function pck_title( $fname )
{
	echo "== pck_title( $fname )\n";
	$pck = load_file($fname);
	if ( empty($pck) )  return;
	loadpck($pck, $fname); // 9 (pix*3 , clut*3 , meta*3)

	$dir = str_replace('.', '_', $fname);

	global $gp_pix, $gp_clut;
	for ( $i=0; $i < 3; $i++ )
	{
		$gp_pix  = $pck[$i+0];
		$gp_clut = pal555( $pck[$i+3] );
		sectmeta($pck[$i+6], "$dir/$i");
	}
	return;
}

function pck_continue( $fname )
{
	echo "== pck_continue( $fname )\n";
	$pck = load_file($fname);
	if ( empty($pck) )  return;
	loadpck($pck, $fname); // 3 (pix , clut , meta)

	$dir = str_replace('.', '_', $fname);

	global $gp_pix, $gp_clut;
	$gp_pix  = $pck[0];
	$gp_clut = pal555( $pck[1] );
	sectmeta($pck[2], $dir);
	return;
}

function pck_syssprcha( $sprn, $chan )
{
	echo "== pck_syssprcha( $sprn, $chan )\n";
	$spr = load_file($sprn);
	$cha = load_file($chan);
	if ( empty($spr) || empty($cha) )
		return;
	loadpck($spr, $sprn); // 18 (meta , ???*3 , clut , sjis*8 , ???*5)
	loadpck($cha, $chan); // 12 (pix , clut*10, pix)

	$dir = str_replace('.', '_', $sprn);

	global $gp_pix, $gp_clut;
	//$gp_pix  = $cha[0];
	//$gp_clut = pal555( $spr[4] );
	//sectmeta($spr[0], $dir);

	// for _pc*.pck later
	$gp_clut = pal555( $spr[4] );
	return;
}
//////////////////////////////
function lunar2( $fname )
{
	// for *.pck only
	if ( stripos($fname, '.pck') === false )
		return;

	// special effects
	if ( stripos($fname, 'efspr') !== false )
	{
		$fn2 = str_ireplace('efspr', 'efcha', $fname);
		return pck_efsprcha($fname, $fn2);
	}
	if ( stripos($fname, 'efcha') !== false )
	{
		$fn2 = str_ireplace('efcha', 'efspr', $fname);
		return pck_efsprcha($fn2, $fname);
	}

	// monsters + bosses
	if ( stripos($fname, 'sysspr') !== false )
	{
		$fn2 = str_ireplace('sysspr', 'syscha', $fname);
		return pck_syssprcha($fname, $fn2);
	}
	if ( stripos($fname, 'syscha') !== false )
	{
		$fn2 = str_ireplace('syscha', 'sysspr', $fname);
		return pck_syssprcha($fn2, $fname);
	}

	// system
	if ( stripos($fname, 'mnspr') !== false )
	{
		$fn2 = str_ireplace('mnspr', 'mncha', $fname);
		return pck_mnsprcha($fname, $fn2);
	}
	if ( stripos($fname, 'mncha') !== false )
	{
		$fn2 = str_ireplace('mncha', 'mnspr', $fname);
		return pck_mnsprcha($fn2, $fname);
	}

	// battle backgrounds
	if ( stripos($fname, 'btlbk') !== false )
		return pck_btlbk($fname);

	// player characters
	if ( stripos($fname, '_pc') !== false )
		return pck_pc($fname);

	// misc
	if ( stripos($fname, 'continue') !== false )
		return pck_continue($fname);
	if ( stripos($fname, 'title') !== false )
		return pck_title($fname);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	lunar2( $argv[$i] );
