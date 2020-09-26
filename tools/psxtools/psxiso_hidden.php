<?php
require "common.inc";
//define("DRY_RUN", true);

function fp2str( $fp, $pos, $byte )
{
	if ( $byte == 0 )
		return "";
	fseek($fp, $pos, SEEK_SET);
	return fread($fp, $byte);
}

function fp2int( $fp, $pos, $byte )
{
	$str = fp2str( $fp, $pos, $byte );
	return ordint($str);
}

function chrbase10( $chr )
{
	$b = ord( $chr );
	$b1 = ($b >> 0) & BIT4;
	$b2 = ($b >> 4) & BIT4;
	return ($b2 * 10) + $b1;
}

function cdpos2int( $min , $sec , $frame )
{
	$m = chrbase10($min);
	$s = chrbase10($sec);
	$f = chrbase10($frame);

	$s -= 2;
	$s += ($m * 60);
	$f += ($s * 75);
	if ( $f < 0 )
		return 0;
	return $f * 0x800;
}

//////////////////////////////
function valkyrie_decrypt( &$str, &$dic, $key )
{
	printf("valkyrie TOC key : %8x\n", $key);
	$toc = "";
	$ed = strlen($dic);
	$st = 0;
	$k = $key;
	while ( $st < $ed )
	{
		$w = str2int($str, $st*4, 4);
		$b = ord( $dic[$st] );
			$st++;

		$w ^= $k;
		$k ^= ($k << 1);
		$toc .= chrint($w,4);

		$b ^= $k;
		$k  = ~($k) ^ $key;
	}
	return $toc;
}
function valkyrie_toc( $fp, $dir, &$toc )
{
	$list = array();
	$ed = strlen($toc);
	$st = 4;
	while ( $st < $ed )
	{
		$no = $st / 4;
		$lba = cdpos2int($toc[$st+0], $toc[$st+1], $toc[$st+2]);
			$st += 4;

		if ( $lba != 0 )
			$list[] = array($no, $lba);
	}

	$txt = "";
	$ed = count($list);
	$st = 0;
	while ( $st < $ed )
	{
		list($no,$lba) = $list[$st];
		$txt .= sprintf("%4x , %8x\n", $no, $lba);

		$fn = sprintf("$dir/%06d.bin", $no);
		if ( isset( $list[$st+1] ) )
			$sz = $list[$st+1][1] - $lba;
		else
		{
			fseek($fp, 0, SEEK_END);
			$sz = ftell($fp) - $lba;
		}

		save_file($fn, fp2str($fp, $lba, $sz));
		$st++;
	}

	echo "$txt\n";
	save_file("$dir/toc.bin", $toc);
	save_file("$dir/toc.txt", $txt);
	return;
}
//////////////////////////////
function iso_valkyrie($fp, $dir)
{
	printf("%s [%s]\n", $dir, __FUNCTION__);

	// sub_80011d38 , SLPM_863.79
	$str = fp2str($fp, 0x4b000, 0x5000);
	$dic = fp2str($fp, 0x50000, 0x1400);
	$key = 0x64283921;

	$toc = valkyrie_decrypt( $str, $dic, $key );
	valkyrie_toc($fp, $dir, $toc);
	return;
}

function iso_starocean2nd1($fp, $dir)
{
	printf("%s [%s]\n", $dir, __FUNCTION__);

	// sub_80011c20 , SLPM_861.05
	$str = fp2str($fp, 0x96000, 0x4800);
	$dic = fp2str($fp, 0x9a800, 0x1200);
	$key = 0x13578642;

	$toc = valkyrie_decrypt( $str, $dic, $key );
	valkyrie_toc($fp, $dir, $toc);
	return;
}
function iso_starocean2nd2($fp, $dir)  { return iso_starocean2nd1($fp, $dir); }

function iso_xenogears($fp, $dir)
{
	printf("%s [%s]\n", $dir, __FUNCTION__);
	$str = fp2str($fp, 0xc000, 0x8000);

	$txt = "";
	$dn = "";
	for ( $i=0; $i < 0x8000; $i += 7 )
	{
		$no = $i / 7;
		$lba = sint24( substr($str, $i+0, 3) );
		$siz = sint32( substr($str, $i+3, 4) );

		if ( $lba == -1 )
			break;
		if ( $lba == 0 || $siz == 0 )
			continue;

		if ( $siz < 0 )
		{
			$dn = $no;
			$siz *= -1;
			$txt .= sprintf("%8x , DIR  , %8x , %s\n", $lba*0x800, $siz, $dn);
		}
		else
		{
			$fn = sprintf("$dn/%06d.bin", $no);
			$txt .= sprintf("%8x , FILE , %8x , %s\n", $lba*0x800, $siz, $fn);
			save_file("$dir/$fn", fp2str($fp, $lba*0x800, $siz));
		}
	} // for ( $i=0; $i < 0x8000; $i += 7 )

	echo "$txt\n";
	save_file("$dir/toc.bin", $str);
	save_file("$dir/toc.txt", $txt);
	return;
}

function iso_dewprism($fp, $dir)
{
	printf("%s [%s]\n", $dir, __FUNCTION__);
	$str = fp2str($fp, 0xc000, 0x4cd8);

	$txt = "";
	$ed = strlen($str) - 4;
	$st = 0;
	$dn = "";
	while ( $st < $ed )
	{
		$no = $st / 4;
		$lba1 = str2int($str, $st+0, 3) & 0x7fffff;
		$lba2 = str2int($str, $st+4, 3) & 0x7fffff;
			$st += 4;

		$sz = $lba2 - $lba1;
		if ( $sz > 0 )
		{
			$fn = sprintf("$dn/%06d.bin", $no);
			$txt .= sprintf("%8x , FILE , %8x , %s\n", $lba1*0x800, $sz*0x800, $fn);
			save_file("$dir/$fn", fp2str($fp, $lba1*0x800, $sz*0x800));
		}
		else
		{
			$dn = $no;
			$txt .= sprintf("%8x , DIR  , %8x , %s\n", $lba1*0x800, 0, $dn);
		}
	} // while ( $st < $ed )

	echo "$txt\n";
	save_file("$dir/toc.bin", $str);
	save_file("$dir/toc.txt", $txt);
	return;
}
//////////////////////////////
function isofile( $fname )
{
	$fp = fopen($fname, "rb");
	if ( ! $fp )  return;

	$mgc = fp2str($fp, 0x8001, 5);
	if ( $mgc != "CD001" )
		return printf("%s is not an ISO 2048/sector file\n", $fname);

	$dir = str_replace('.', '_', $fname);

	$mgc = fp2str($fp, 0x8028, 0x20);
	$mgc = strtolower( trim($mgc, " ".ZERO) );

	$func = "iso_" . $mgc;
	if ( ! function_exists($func) )
		return printf("%s [%s] is not supported (yet)\n", $fname, $func);

	$func($fp, $dir);
	fclose($fp);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	isofile( $argv[$i] );
