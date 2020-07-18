<?php
require "common.inc";

$gp_patch = array();

function rm_ent( $dir )
{
	if ( empty($dir) || ! is_dir($dir) || is_link($dir) )
		return;
	foreach ( scandir($dir) as $f )
	{
		if ( $f[0] == '.' )
			continue;
		unlink("$dir/$f");
	}
	return;
}

function scdat_ent( &$ram, $ent, $base, $fst, $fbk )
{
	$ent = preg_replace("|[\s]+|", '', $ent);
	list($d,$l) = explode('=', $ent);

	if ( $d == 'reset' )
		return nds_overlay( $ram, $base, substr($l,0,strpos($l,',')) );

	$dir = "$base/cvnds/$d";
	rm_ent($dir);
	@mkdir($dir, 0755, true);

	$cnt = array(0,0,0,0,0);
	$txt = "";
	foreach ( explode(',', $l) as $lv )
	{

		if ( strpos($lv, '-') === false )
			continue;
		$lv = explode('-', $lv);

		switch ( $lv[0] )
		{
			case 'ov':
				$v1 = (int)$lv[1];
				nds_overlay( $ram, $base, $v1 );
				$txt .= "overlay  $v1\n";
				break;
			case '1':
			case '2':
			case '4':
				$v0 = hexdec( $lv[0] );
				$v1 = hexdec( $lv[1] );
				$v2 = $v1;
				if ( isset( $lv[2] ) )
					$v2 = hexdec( $lv[2] );

				for ( $i=$v1; $i <= $v2; $i++ )
				{
					$pos = $fst + ($i * $fbk);
					$fn1 = substr0($ram, $pos + 6);
					$fn2 = sprintf("%d.%d", $cnt[$v0], $v0);

					if ( stripos($fn1, '.jnt') || stripos($fn1, '.nsb') )
						continue;
					if ( ! is_file("$base/data/$fn1") )
						continue;
					copy("$base/data/$fn1", "$dir/$fn2");
					$txt .= "$fn2  $fn1\n";
					$cnt[$v0]++;
				}
				break;
			case '3':
				$v0 = hexdec( $lv[0] );
				$v1 = hexdec( $lv[1] );
				$cn = ord( $ram[$v1+2] );
					$v1 += 4;
				$pal = substr($ram, $v1, $cn*0x20);
				$fn2 = sprintf("%d.%d", $cnt[$v0], $v0);

				save_file("$dir/$fn2", $pal);
				$txt .= sprintf("$fn2  palette  %x  %x\n", $v1, $cn);
				$cnt[$v0]++;
				break;
		} // switch ( $lv[0] )
	} // foreach ( explode(',', $l) as $lv)

	save_file("$dir/files.txt", $txt);
	return;
}
//////////////////////////////
function loop4p( &$ram, $pos, $pfx )
{
	$ent = array();
	while (1)
	{
		$b1 = str2int($ram, $pos, 3);
			$pos += 4;
		if ( $b1 == BIT24 || $b1 == 0 )
			break;
		$ent[] = sprintf("%s%x", $pfx, $b1);
	}
	return $ent;
}
//////////////////////////////
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

function mon_obj_sc( &$ram, $dir, $type, $pfx )
{
	global $gp_patch;
	nds_game( $ram, $dir, $gp_patch['ndsram']['game'] );
	list($st,$ed) = $gp_patch['ndsram'][$type];
	$file_st = $gp_patch['ndsram']['files'][0];
	$file_bk = $gp_patch['ndsram']['files'][2];

	$id = 0;
	while ( $st < $ed )
	{
		$pos = str2int ($ram, $st, 3);
		$ent = file_ent($ram, $pos, $pfx, $id);

		scdat_ent( $ram, $ent, $dir, $file_st, $file_bk );
		echo "$ent\n";
		$st += 4;
		$id++;
	}

	foreach ( $gp_patch[$type] as $mk => $mv )
	{
		$ent = sprintf("%s = %s", $mk, implode(' , ', $mv));
		scdat_ent( $ram, $ent, $dir, $file_st, $file_bk );
		echo "$ent\n";
	}
	return;
}
//////////////////////////////
function listfile( &$ram, $files )
{
	$cnt = ( $files[1] - $files[0] ) / $files[2];
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p = $files[0] + ($i * $files[2]);
		$b1 = str2int($ram, $p+0, 4);
		$b2 = str2int($ram, $p+4, 2);
		$b3 = substr0($ram, $p+6);
		printf("%4x , %8x , %4x , %s\n", $i, $b1, $b2, $b3);
	}
	return;
}

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
	$game = $gp_patch['ndsram']['game'][0];

	# list internal file id
	arrayhex( $gp_patch['ndsram']['files'] );
	listfile( $ram, $gp_patch['ndsram']['files'] );

	arrayhex( $gp_patch['ndsram']['mon_sc'] );
	arrayhex( $gp_patch['ndsram']['obj_sc'] );
	mon_obj_sc($ram, $dir, 'mon_sc', 'mon');
	mon_obj_sc($ram, $dir, 'obj_sc', 'obj');

	// game specific files
	$file_st = $gp_patch['ndsram']['files'][0];
	$file_bk = $gp_patch['ndsram']['files'][2];
	foreach ( $gp_patch[$game] as $gk => $gv )
	{
		switch ( $gk )
		{
			case 'dest_data': // candles
				arrayhex( $gv );
				list($st,$ed) = $gv;
				$id = 0;
				while ( $st < $ed )
				{
					$b2 = str2int($ram, $st+ 0, 2);
					$b1 = str2int($ram, $st+ 4, 2);
					$b3 = str2int($ram, $st+16, 3);
					$ent = sprintf("dest_%d = 2-%x , 1-%x , 3-%x", $id, $b2, $b1, $b3);
					scdat_ent( $ram, $ent, $dir, $file_st, $file_bk );
					echo "$ent\n";
					$id++;
					$st += 0x14;
				}
				break;
			case 'load_data': // loading rooms
				arrayhex( $gv );
				list($st,$ed) = $gv;
				$id = 0;
				while ( $st < $ed )
				{
					$b1 = str2int($ram, $st+0, 3);
						$b1 = loop4p($ram, $b1, '1-');
					$b2 = str2int($ram, $st+4, 2);
					$b3 = str2int($ram, $st+8, 3);
					$ent = sprintf("load_%d = 2-%x , 3-%x , %s", $id, $b2, $b3, implode(' , ', $b1));
					scdat_ent( $ram, $ent, $dir, $file_st, $file_bk );
					echo "$ent\n";
					$id++;
					$st += 0x10;
				}
				break;
		} // switch ( $gk )
	} // foreach ( $gp_patch[$game] as $gk => $gv )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );
