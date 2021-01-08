<?php
/*
[license]
[/license]
 */
require "common.inc";
require "common-guest.inc";
require "common-64bit.inc";

//////////////////////////////
function cpk_decrypt( &$str )
{
	if ( substr($str, 0, 4) == "@UTF" )
		return;

	// CriPakTools-20190920/LibCPK/CPK.cs
	$len = strlen($str);
	$m = 0x655f;
	$t = 0x4115;
	for ( $i=0; $i < $len; $i++ )
	{
		$b = ord( $str[$i] );
		$b = ($b ^ $m) & BIT8;
		$str[$i] = chr($b);
		$m = ($m * $t) & BIT16;
	}
	return;
}

function cpklist( &$meta )
{
	$list = array();
	if ( substr($meta, 0, 4) != "@UTF" )
		return $list;

	$off1 = str2big($meta,  8, 4) + 8; // row
	$off2 = str2big($meta, 12, 4) + 8; // str
	$off3 = str2big($meta, 16, 4) + 8; // data

	$tbl1 = str2big($meta, 20, 4); // name
	$tbl2 = str2big($meta, 24, 2); // no col
	$tbl3 = str2big($meta, 26, 2); // row len
	$tbl4 = str2big($meta, 28, 4); // no row

	$cols = array();
	$pos = 32;
	for ( $c=0; $c < $tbl2; $c++ )
	{
		$b1 = str2big($meta, $pos+0, 1);
		$b2 = str2big($meta, $pos+1, 4);
			$pos += 5;
		$s = substr0($meta, $off2+$b2);
		$cols[$c] = array($s, $b1);
	}

	for ( $r=0; $r < $tbl4; $r++ )
	{
		$pos = $off1 + ($r * $tbl3);
		for ( $c=0; $c < $tbl2; $c++ )
		{
			list($cnam,$cflg) = $cols[$c];
			$stor = ($cflg >> 4) & BIT4;
			$type = ($cflg >> 0) & BIT4;

			switch ( $stor )
			{
				case 0:
				case 1:
					$list[$r][$cnam] = 0;
					break;
				case 3:
				case 5:
					switch ( $type )
					{
						case 0:
						case 1:
							$v = str2big($meta, $pos, 1);
							$pos++;
							$list[$r][$cnam] = $v;
							break;
						case 2:
						case 3:
							$v = str2big($meta, $pos, 2);
							$pos += 2;
							$list[$r][$cnam] = $v;
							break;
						case 4:
						case 5:
							$v = str2big($meta, $pos, 4);
							$pos += 4;
							$list[$r][$cnam] = $v;
							break;
						case 6:
						case 7:
							$v = str2big($meta, $pos, 8);
							$pos += 8;
							$list[$r][$cnam] = $v;
							break;
						case 8:
							$v = substrrev($meta, $pos, 4);
							$v = float32($v);
							$list[$r][$cnam] = $v;
							break;
						case 9:
							break;
						case 10:
							$v1 = str2big($meta, $pos, 4);
							$v2 = substr0($meta, $off2+$v1);
							$pos += 4;
							$list[$r][$cnam] = $v2;
							break;
						case 11:
							$v1 = str2big($meta, $pos+0, 4);
							$v2 = str2big($meta, $pos+4, 4);
							$pos += 8;
							$list[$r][$cnam] = substr($meta, $off3+$v1, $v2);
							break;
						default:
							return php_error("unknown cflg %x", $cflg);
					} // switch ( $type )
					break;
				default:
					return php_error("unknown cflg %x", $cflg);
			} // switch ( $stor )

		} // for ( $c=0; $c < $tbl2; $c++ )
	} // for ( $r=0; $r < $tbl4; $r++ )

	return $list;
}
//////////////////////////////
function cpkfiles( $fp, $dir, $off, $siz )
{
	return;
}
//////////////////////////////
function sect_toc( $fp, &$meta, $dir )
{
	echo "== sect_toc( $dir )\n";
	cpk_decrypt($meta);
	save_file("$dir/toc.bin", $meta);
	$list = cpklist($meta);
	if ( empty($list) )
		return;
	print_r($list);

	foreach ( $list as $lv )
	{
		$fn = sprintf("%s/%s", $lv['DirName'], $lv['FileName']);
		printf("%8x , %8x , %s\n", $lv['FileOffset'], $lv['FileSize'], $fn);
	} // foreach ( $list as $lk => $lv )
	return;
}

function sect_etoc( $fp, &$meta, $dir )
{
	echo "== sect_etoc( $dir )\n";
	cpk_decrypt($meta);
	save_file("$dir/etoc.bin", $meta);
	$list = cpklist($meta);
	if ( empty($list) )
		return;
	print_r($list);
	return;
}

function sect_itoc( $fp, &$meta, $dir )
{
	echo "== sect_itoc( $dir )\n";
	cpk_decrypt($meta);
	save_file("$dir/itoc.bin", $meta);
	$list = cpklist($meta);
	if ( empty($list) )
		return;
	print_r($list);
	// OPTIONAL
	return;
}

function sect_gtoc( $fp, &$meta, $dir )
{
	echo "== sect_gtoc( $dir )\n";
	cpk_decrypt($meta);
	save_file("$dir/gtoc.bin", $meta);
	$list = cpklist($meta);
	if ( empty($list) )
		return;
	print_r($list);
	// OPTIONAL
	return;
}

function sect_cpk( $fp, &$meta, $dir )
{
	echo "== sect_cpk( $dir )\n";
	cpk_decrypt($meta);
	save_file("$dir/cpk.bin", $meta);
	$list = cpklist($meta);
	if ( empty($list) )
		return;
	print_r($list);

	if ( $list[0]['TocOffset'] != 0 && $list[0]['TocSize'] != 0 )
	{
		$sub = fp2str($fp, $list[0]['TocOffset']+0x10, $list[0]['TocSize']);
		sect_toc($fp, $sub, $dir);
	}
	if ( $list[0]['EtocOffset'] != 0 && $list[0]['EtocSize'] != 0 )
	{
		$sub = fp2str($fp, $list[0]['EtocOffset']+0x10, $list[0]['EtocSize']);
		sect_etoc($fp, $sub, $dir);
	}
	if ( $list[0]['ItocOffset'] != 0 && $list[0]['ItocSize'] != 0 )
	{
		$sub = fp2str($fp, $list[0]['ItocOffset']+0x10, $list[0]['ItocSize']);
		sect_itoc($fp, $sub, $dir);
	}
	if ( $list[0]['GtocOffset'] != 0 && $list[0]['GtocSize'] != 0 )
	{
		$sub = fp2str($fp, $list[0]['GtocOffset']+0x10, $list[0]['GtocSize']);
		sect_gtoc($fp, $sub, $dir);
	}
	return;
}
//////////////////////////////
function muravita( $fname )
{
	$fp = fopen($fname, "rb");
	if ( ! $fp )  return;

	$dir = str_replace('.', '_', $fname);

	$head = fp2str($fp, 0, 0x10);
	$siz = str2int($head, 8, 4);
	if ( substr($head, 0, 4) != 'CPK ' )
		return;

	$meta = fp2str($fp, 0x10, $siz);
	sect_cpk($fp, $meta, $dir);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	muravita( $argv[$i] );
