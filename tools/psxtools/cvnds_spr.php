<?php
require "common.inc";

function monster_ent( &$ram, $ent, $dir, $fst, $fbk )
{
	$ent = preg_replace("|[\s]+|", '', $ent);
	list($d,$l) = explode('=', $ent);
	if ( $d == 'reset' )
		return nds_overlay( $ram, $dir, $l );

	@mkdir("$dir/cvnds/$d", 0755, true);

	$cnt = array(0,0,0,0);
	$txt = "";
	foreach ( explode(',', $l) as $lv)
	{
		if ( strpos($lv, '-') === false )
			continue;
		$lv = explode('-', $lv);

		switch ( $lv[0] )
		{
			case 'ov':
				$v1 = (int)$lv[1];
				nds_overlay( $ram, $dir, $v1 );
				$txt .= "overlay  $v1\n";
				break;
			case '1':
			case '2':
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

					copy("$dir/data/$fn1", "$dir/cvnds/$d/$fn2");
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

				save_file("$dir/cvnds/$d/$fn2", $pal);
				$txt .= sprintf("$fn2  palette  %x  %x\n", $v1, $cn);
				$cnt[$v0]++;
				break;
		} // switch ( $lv[0] )
	} // foreach ( explode(',', $l) as $lv)

	save_file("$dir/cvnds/$d/files.txt", $txt);
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
function cvnds( $dir )
{
	if ( ! is_dir($dir) )
		return;

	$pat = nds_patch($dir, 'cvnds');
	if ( empty($pat) )
		return;
	$ram = nds_ram($dir);

	arrayhex( $pat['arm9.bin']['files'] );
	arrayhex( $pat['arm9.bin']['mon_sc'] );

	$mon_st  = $pat['arm9.bin']['mon_sc'][0];
	$mon_ed  = $pat['arm9.bin']['mon_sc'][1];
	$file_st = $pat['arm9.bin']['files'][0];
	$file_bk = $pat['arm9.bin']['files'][2];

	$id = 0;
	while ( $mon_st < $mon_ed )
	{
		$pos = str2int ($ram, $mon_st, 3);
		$ent = file_ent($ram, $pos, "mon", $id);

		monster_ent( $ram, $ent, $dir, $file_st, $file_bk );
		echo "$ent\n";
		$mon_st += 4;
		$id++;
	}

	if ( isset( $pat['monster'] ) )
	{
		foreach ( $pat['monster'] as $mk => $mv )
		{
			$ent = sprintf("%s = %s", $mk, implode(' , ', $mv));
			monster_ent( $ram, $ent, $dir, $file_st, $file_bk );
			echo "$ent\n";
		}
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	cvnds( $argv[$i] );
