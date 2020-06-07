<?php
require "common.inc";

function monster_ent( &$ram, &$y9, $ent, $dir, $fst, $fbk )
{
	$ent = preg_replace("|[\s]+|", '', $ent);
	list($d,$l) = explode('=', $ent);
	@mkdir("$dir/cvds/$d", 0755, true);

	$cnt = array(0,0,0,0);
	$txt = "";
	foreach ( explode(',', $l) as $lv)
	{
		$lv = explode('-', $lv);
		if ( $lv[0] === 'ov' )
		{
			$v1 = (int)$lv[1];
			nds_overlay( $ram, $y9, $dir, $v1 );
			$txt .= "overlay  $v1\n";
			continue;
		}

		$v0 = hexdec( $lv[0] );
		$v1 = hexdec( $lv[1] );
		if ( $v0 == 1 || $v0 == 2 )
		{
			$v2 = $v1;
			if ( isset( $lv[2] ) )
				$v2 = hexdec( $lv[2] );

			for ( $i=$v1; $i <= $v2; $i++ )
			{
				$pos = $fst + ($i * $fbk);
				$fn1 = substr0($ram, $pos + 6);
				$fn2 = sprintf("%d.%d", $cnt[$v0], $v0);

				copy("$dir/data/$fn1", "$dir/cvds/$d/$fn2");
				$txt .= "$fn2  $fn1\n";
				$cnt[$v0]++;
			}
			continue;
		}

		if ( $v0 == 3 )
		{
			$cn = ord( $ram[$v1+2] );
			$v1 += 4;
			$pal = substr($ram, $v1, $cn*0x20);
			$fn2 = sprintf("%d.%d", $cnt[$v0], $v0);

			save_file("$dir/cvds/$d/$fn2", $pal);
			$txt .= sprintf("$fn2  palette  %x  %x\n", $v1, $cn);
			$cnt[$v0]++;
			continue;
		}
	}
	save_file("$dir/cvds/$d/files.txt", $txt);
	return;
}

function file_ent( &$ram, $pos, $pfx, $id)
{
	$ent = array();
	while (1)
	{
		$b1 = str2int($ram, $pos+0, 3);
		$b2 = str2int($ram, $pos+4, 3);
			$pos += 8;
		if ( $b1 == BIT24 || $b2 == 0 )
			break;
		$ent[] = sprintf("%x-%x", $b2, $b1);
	}
	$txt = sprintf("%s_%s = %s", $pfx, $id, implode(' , ', $ent));
	return $txt;
}
//////////////////////////////
function cvds( $dir )
{
	if ( ! is_dir($dir) )
		return;
	$ram = str_pad('', 0x400000, ZERO);

	$file = file_get_contents("$dir/header.bin");
	$NTR = substr($file, 12, 4);
	$off = str2int($file, 0x28, 3);

	$pat = patchfile("cvds_$NTR.txt");
	if ( empty($pat) )
		return;

	$y9   = file_get_contents("$dir/y9.bin");
	$file = file_get_contents("$dir/arm9.bin");
		strupd($ram, $off, $file);

	$mon_st = hexdec( $pat['arm9.bin']['monster'][0] );
	$mon_ed = hexdec( $pat['arm9.bin']['monster'][1] );
	$file_st = hexdec( $pat['arm9.bin']['files'][0] );
	$file_bk = hexdec( $pat['arm9.bin']['files'][2] );

	$id = 0;
	while ( $mon_st < $mon_ed )
	{
		$pos = str2int ($ram, $mon_st, 3);
		$ent = file_ent($ram, $pos, "mon", $id);

		monster_ent( $ram, $y9, $ent, $dir, $file_st, $file_bk );
		echo "$ent\n";
		$mon_st += 4;
		$id++;
	}

	if ( isset( $pat['monster'] ) )
	{
		foreach ( $pat['monster'] as $mk => $mv )
		{
			$ent = sprintf("%s = %s", $mk, implode(' , ', $mv));
			monster_ent( $ram, $y9, $ent, $dir, $file_st, $file_bk );
			echo "$ent\n";
		}
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvds( $argv[$i] );

// DS RAM = 4 MB ( 0x2000000-0x2400000 )
