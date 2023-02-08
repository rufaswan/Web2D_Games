<?php
require 'common.inc';
require 'DX_keys.inc';

define('BLKSZ', 12 << 20);

function key2char( $key )
{
	$arr = array();
	foreach ( explode(' ', $key) as $kh )
	{
		if ( empty($kh) )
			continue;
		$arr[] = hexdec($kh);
	}
	return $arr;
}

function xorfile( &$sub, &$key )
{
	$slen = strlen($sub);
	$klen = count ($key);
	$kp = 0;
	for ( $i=0; $i < $slen; $i++ )
	{
		$b = ord( $sub[$i] );
		$b ^= $key[$kp];
		$sub[$i] = chr($b);

		$kp++;
		while( $kp >= $klen )
			$kp -= $klen;
	} // for ( $i=0; $i < $slen; $i++ )
}

function dxdecode( $fname )
{
	$fp1 = fopen($fname, 'rb');
	if ( ! $fp1 )  return;

	$sub = fread($fp1, BLKSZ);
	$b1 = ord($sub[0]) ^ ord('D');
	$b2 = ord($sub[1]) ^ ord('X');

	global $dx_keys;
	$key = array();
	foreach ( $dx_keys as $dx )
	{
		$dx2 = key2char($dx);
		if ( $dx2[0] === $b1 && $dx2[1] === $b2 )
		{
			printf("DETECT key = %s\n", $dx);
			$key = $dx2;
		}
	} // foreach ( $dx_keys as $dx )

	if ( empty($key) )
		return;

	$fp2 = fopen("$fname.dec", 'wb');
	if ( ! $fp1 )  return;

	xorfile($sub, $key);
	fwrite($fp2, $sub);

	$pos = BLKSZ;
	$len = filesize($fname);
	while ( $pos < $len )
	{
		fseek($fp1, $pos, SEEK_SET);
		$sub = fread($fp1, BLKSZ);

		xorfile($sub, $key);
		fwrite($fp2, $sub);

		$pos += BLKSZ;
	} // while ( $pos < $len )

	fclose($fp1);
	fclose($fp2);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	dxdecode( $argv[$i] );
