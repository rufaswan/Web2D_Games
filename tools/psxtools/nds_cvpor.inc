<?php
/*
[license]
[/license]
 */
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

function nds_ram( $dir )
{
	$head = load_file("$dir/header.bin");
	if ( empty($head) )
		return "";
	$ram = str_repeat(ZERO, 0x400000);

	$bin = load_file("$dir/arm9.bin");
	$off = str2int($head, 0x28, 3);
		str_update($ram, $off, $bin);
	$bin = load_file("$dir/arm7.bin");
	$off = str2int($head, 0x38, 3);
		str_update($ram, $off, $bin);
	return $ram;
}

function nds_patch( $dir, $pfx )
{
	$head = load_file("$dir/header.bin");
	if ( empty($head) )
		return array();
	$NTR = substr($head, 12, 4);
	$ver = ord( $head[0x1e] );

	$txt = sprintf("%s_%s_%d.txt", $pfx, $NTR, $ver);
	return patchfile($txt);
}

function nds_overlay( &$ndsram, $dir, $id )
{
	if ( strpos($id, 'ov-') !== false )
		$id = (int)substr($id, 3);

	$y9   = load_file("$dir/y9.bin");
	if ( ! isset( $y9[$id*0x20] ) )
		return array();
	$off   = str2int($y9, ($id * 0x20) + 0x04, 3);
	$start = str2int($y9, ($id * 0x20) + 0x10, 3);

	$over = sprintf("$dir/overlay/overlay_%04d.bin", $id);
	$bin  = load_file($over);
	trace("load OVERLAY %d[0x%x] @ %x => %x\n", $id, $id, $off, $start);
		str_update($ndsram, $off, $bin);
	return array($off, $start);
}
