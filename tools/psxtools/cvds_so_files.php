<?php
require "common.inc";

//////////////////////////////
function ramspr( &$ram, $dir, $oset, $ofns, $blk)
{
	printf("=== ramspr( $dir , %x , %x , %x )\n", $oset, $ofns, $blk);
	$sid = 0;
	while(1)
	{
		$bak = $oset;
		$off = str2int($ram, $oset, 3);
			$oset += 4;
		if ( $off == 0 )
			break;

		printf("set_%d @ %x = %x\n", $sid, $bak, $off);
		$set = array();
		while(1)
		{
			// 1 = f_*.dat  t_*.dat
			// 2 = p_*.dat  *.nsbmd  *.nsbtx
			// 3 = palette ptr
			$id = str2int($ram, $off+0, 3);
			$ty = str2int($ram, $off+4, 3);
				$off += 8;
			switch ( $ty )
			{
				case 0:
					break 2;
				case 1:
				case 2:
					$ps = $ofns + ($id * $blk) + 6;
					$fn = substr0($ram, $ps);

					printf("  $ty $fn\n");
					if ( stripos($fn, ".nsb") )
						break;
					if ( stripos($fn, ".jnt") )
						break;
					$set[$ty][] = $fn;
					break;
				case 3:
					$cn = str2int($ram, $id+2, 2);
					printf("  $ty palette %d @ %x\n", $cn, $id+4);
					$set[$ty][] = substr($ram, $id+4, $cn*0x20);
					break;
				default:
					printf("  ERROR unknown $ty @ %x\n", $off-8);
					break;
			}

		}

		$todir = "$dir/cvds/$bak";
		@mkdir($todir, 0755, true);
		$txt = "";
		foreach ( $set as $sk => $sv )
		{
			foreach ( $sv as $svk => $svv )
			{
				if ( $sk == 3 )
				{
					save_file("$todir/$svk.$sk", $svv);
					continue;
				}
				$txt .= "$svk.$sk = $svv\n";
				copy("$dir/data/$svv", "$todir/$svk.$sk");
			}
		} // foreach ( $set as $sk => $sv )
		save_file("$todir/data.txt", $txt);

		$sid++;
	}
	echo "\n";
	return;
}
//////////////////////////////
function cvds_yr9e( &$ram, $dir ) // order of ecclesia
{
	echo "DETECT : CV Order of Ecclesia\n";
	$ofn = 0xd8cec;
	$blk = 0x20;
	ramspr($ram, $dir, 0xf2814, $ofn, $blk);
	ramspr($ram, $dir, 0xf343c, $ofn, $blk);
	return;
}

function cvds_acbe( &$ram, $dir ) // portrait of ruins
{
	echo "DETECT : CV Portrait of Ruins\n";
	$ofn = 0xcdafc;
	$blk = 0x20;
	ramspr($ram, $dir, 0xcd88c, $ofn, $blk);
	ramspr($ram, $dir, 0xe19dc, $ofn, $blk);
	// bg series ref = 0xdfa50
	return;
}

function cvds_acve( &$ram, $dir ) // dawn of sorrow
{
	echo "DETECT : CV Dawn of Sorrow\n";
	$ofn = 0x8cc6c;
	$blk = 0x28;
	ramspr($ram, $dir, 0x8ca90, $ofn, $blk);
	ramspr($ram, $dir, 0x9b890, $ofn, $blk);
	return;
}
//////////////////////////////
function overlay( &$ram, $dir, $oid )
{
	$ofn = sprintf("$dir/overlay/overlay_%04d.bin", $oid);
	$bin = file_get_contents($ofn);
	if ( empty($bin) )  return;

	$y9 = file_get_contents("$dir/y9.bin");
	$p = ($oid * 0x20) + 4;
	$off = str2int($y9, $p, 3);

	strupd($ram, $off, $bin);
	file_put_contents("$dir/nds.ram", $ram);
	return;
}

function ndsram( $dir )
{
	$ram = strpad( 0x400000 );
	$head = file_get_contents("$dir/header.bin");

	$off = str2int($head, 0x28, 3);
	$bin = file_get_contents("$dir/arm9.bin");
	strupd($ram, $off, $bin);

	$off = str2int($head, 0x38, 3);
	$bin = file_get_contents("$dir/arm7.bin");
	strupd($ram, $off, $bin);

	file_put_contents("$dir/nds.ram", $ram);
	return $ram;
}

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

// DS RAM = 4 MB ( 0x2000000-0x2400000 )
