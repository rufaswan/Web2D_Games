<?php
define("ZERO", chr(  0));
define("BYTE", chr(255));
define('BIT8', 0xff);

function ordint( $str )
{
	if ( (int)$str === $str ) // already int
		return $str;
	$str = rtrim("$str", ZERO);
	$len = strlen($str);
	$int = 0;
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $str[$i] );
		$int += ($b << ($i*8));
	}
	return $int;
}

function chrint( $int, $byte = 0 )
{
	if ( "$int" === $int ) // already str
		return $int;
	$int = (int)$int;
	$str = "";
	for ( $i=0; $i < $byte; $i++ )
	{
		$b = $int & BIT8;
		$str .= chr($b);
		$int >>= 8;
	}
	while ( strlen($str) < $byte )
		$str .= ZERO;
	return $str;
}

function rgb555( $str )
{
	// 15-bit ABGR
	// RGB = c6         52         52
	//     = 1100 0110  0101 0010  0101 0010
	//     = 11000      01010      01010
	// 15-bit = 0(A) 01010(B) 01010(G) 11000(R)
	//        = 0010 1001 0101 1000 = 2958
	//        = 58 29
	$pal = ordint($str);

	$b = ($pal >> 7) & 0xf8; // <<  1 >> 8 == >> 7
	$g = ($pal >> 2) & 0xf8; // <<  6 >> 8 == >> 2
	$r = ($pal << 3) & 0xf8; // << 11 >> 8 == << 3
	$a = BYTE;

	$str = chr($r) . chr($g) . chr($b) . $a;
	return $str;
}

function pal555( $str )
{
	$clut = "";
	$siz = strlen($str);
	for ( $i=0; $i < $siz; $i += 2 )
		$clut .= rgb555( $str[$i+0] . $str[$i+1] );
	return $clut;
}
//////////////////////////////
$file = file_get_contents("quicksave_LUNAR2.EXE_4.psv");

$cpos = [
	0x31a1e4,0x31a9e4,
	0x31b1e4,0x31b9e4,
	0x31c1e4,0x31c9e4,
	0x31d1e4,0x31d9e4,
	0x31e1e4,0x31e9e4,
	0x31f1e4,0x31f9e4,
	0x3201e4,0x3209e4,
	0x3a09e4,0x3a11e4,
	0x3a19e4,0x3a21e4,
];
$cpix = 0x2a21e4;

$pix = substr($file, $cpix, 0x100000);
	$clut = "CLUT";
	$clut .= chrint(0x100, 4);
	$clut .= chrint(0x800, 4);
	$clut .= chrint(0x200, 4);
foreach ( $cpos as $ck => $cv  )
{
	$dat = $clut;
	$dat .= pal555( substr($file, $cv, 0x200) );
	$dat .= $pix;
	file_put_contents("LUNAR-$ck.clut", $dat);
}
