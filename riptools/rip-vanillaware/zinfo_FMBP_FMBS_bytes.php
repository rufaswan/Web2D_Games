<?php
declare( strict_types=1 );

require 'tool.inc';
tool::require('class-ieee754');
tool::require('func-hexnum');
tool::require('vanillaware');

function detect_tag( string &$file ) : string
{
	switch ( substr($file,0,4) )
	{
		case 'FMBP':
			$ver = tool::ordstr($file, 0x14, 2);
			switch ( $ver )
			{
				case 0xc9:  return 'ps2_grim';
				case 0x55:  return 'ps2_odin';
			} // switch ( $ver )
			return '';
		case 'FMBS':
			// big endian test
			$ver = ieee754::ordstr($file, 0x14, 2);
			switch ( $ver )
			{
				case 0x66:  return 'wii_mura';
				case 0x6e:  return 'ps3_drag';
				case 0x72:  return 'ps3_odin';
			} // switch ( $ver )

			// little endian test
			$ver = tool::ordstr($file, 0x14, 2);
			switch ( $ver )
			{
				case 0x66:  return 'nds_kuma';
				case 0x6b:  return 'psp_gran';
				case 0x6d:  return 'vit_mura';
				case 0x6e:  return 'vit_drag';
				case 0x72:
					// s0 test
					if ( tool::ordstr($file,0xc8,4) === 0x120 || str2int($file,0xe0,4) === 0x120 )
						return 'vit_odin';
					if ( tool::ordstr($file,0xb0,4) === 0x120 )
					{
						// 'ps4_odin' , 'ps4_sent'
						return 'ps4_odin';
					}
					return '';
				case 0x76:  return 'ps4_sent';
				case 0x77:
					// 'swi_sent' , 'swi_grim' , 'swi_unic' ,
					// 'ps4_grim' , 'ps4_unic' ,
					// 'ps5_grim' , 'ps5_unic' ,
					// 'xbx_unic'
					return 'ps4_grim';
			} // switch ( $ver )

			// test failed
			return '';
	} // switch ( substr($file,0,4) )
	return '';
}

function addinfo( array &$info, int $sid, int $bid, int $val ) : void
{
	// [
	//   s0 : [
	//     0 : 38 ba
	//     1 : 00
	//     2 : 00
	//     3 : 00
	//   ] ,
	//   s1 : [
	//     ...
	//   ]
	// ]
	while ( ! isset( $info[$sid] ) )
		$info[] = [];
	while ( ! isset( $info[$sid][$bid] ) )
		$info[$sid][] = [];
	if ( isset( $info[$sid][$bid][$val] ) )
		return;

	$info[$sid][$bid][$val] = 1;
	ksort($info[$sid][$bid]);
}

function dumpinfo( array &$info, string $ent ) : void
{
	$txt = '';
	foreach ( $info as $sid => $sv )
	{
		foreach ( $sv as $bid => $bv )
		{
			$txt .= sprintf('s%x[%2x] = ', $sid, $bid);
			if ( count($bv) === 0x100 )
				$txt .= '[00-ff]';
			else
			{
				foreach ( $bv as $bvk => $bvv )
					$txt .= sprintf('%x ', $bvk);
			}
			$txt .= "\n";
		} // foreach ( $row as $rk => $rv )

		$txt .= "\n";
	} // foreach ( $info as $ik => $iv )

	tool::save($fname.'.txt', $txt);
}
//////////////////////////////
function mbsinfo( array &$info, string $fname ) : void
{
	tool::trace(__FUNCTION__, $fname);
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$tag = detect_tag($file);
	if ( empty($tag) )  return;

	global $gp_data;
	if ( ! isset($gp_data[$tag]) )
		return;
	$mbs = &$gp_data[$tag];

	$info['tag'][$tag] = 1;
	if ( count($info['tag']) > 1 )
	{
		tool::warning('cannot analyze mbs from 2 different ver');
		return;
	}

	global $gp_big;
	$gp_big = $mbs['bigend'];

	foreach ( $mbs['sect'] as $sk => $sv )
	{
		$pos = van_int($file, $sv['p'] , 4);
		$cnt = van_int($file, $sv['c'][0], $sv['c'][1]);
		if ( $cnt < 1 )
			continue;

		for ( $c=0; $c < $cnt; $c++ )
		{
			$sub = tool::substr($file, $pos, $sv['k']);
				$pos += $sv['k'];
			for ( $i=0; $i < $sv['k']; $i++ )
			{
				$b = ord($sub[$i]);
				addinfo($info['info'], $sk, $i, $b);
			} // for ( $i=0; $i < $sv['k']; $i++ )
		} // for ( $c=0; $c < $cnt; $c++ )

	} // foreach ( $mbs['sect'] as $sk => $sv )
}

function vanilla( string $ent ) : void
{
	$info = [
		'tag'  => [],
		'info' => [],
	];
	if ( is_file($ent) )
	{
		mbsinfo($info, $ent);
		dumpinfo($info['info'], $ent);
		return;
	}

	if ( is_dir($ent) )
	{
		$list = [];
		tool::scan($list, $ent);
		foreach ( $list as $fn )
			mbsinfo($info, $fn);

		dumpinfo($info['info'], $ent);
		return;
	}
}

tool::argv_callback($argv, 'vanilla');
