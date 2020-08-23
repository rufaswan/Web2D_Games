<?php
// cache 1 MB
define("WRITE_S", 0x100000);

function expiso( $fp, $fname, $bksz, $bkhd )
{
	if ( $bksz == 0x800 )
		return;
	printf("== expiso( $fname , %x , %x )\n", $bksz, $bkhd);

	$size = filesize($fname);
	$isop = fopen("$fname.iso", 'wb');
	$cache = "";
	for ( $i=0; $i < $size; $i += $bksz )
	{
		fseek($fp, $i + $bkhd, SEEK_SET);
		$cache .= fread($fp, 0x800);
		if ( strlen($cache) >= WRITE_S )
		{
			fwrite($isop, $cache);
			$cache = "";
		}
	}
	if ( strlen($cache) > 0 )
		fwrite($isop, $cache);
	fclose($isop);
	return;
}

function bin2iso( $fname )
{
	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return;

	$detect = array(
		array("iso/2048", 0x800, 0   ),
		array("psx/2352", 0x930, 0x18), // psx bin
		array("sat/2352", 0x930, 0x10), // saturn bin
		array("bin/2336", 0x920, 0x8 ),
		array("mdf/2448", 0x990, 0x18),
	);

	$head = fread($fp, 0x10000);
	$bkhd = 0;
	$bksz = 0;
	foreach ( $detect as $det )
	{
		$p = ($det[1] * 0x10) + $det[2];
		if ( substr($head, $p+1, 5) == 'CD001' )
		{
			$bkhd = $det[2];
			$bksz = $det[1];
			printf("DETECT %s , %x , %x , %s\n", $det[0], $det[1], $det[2], $fname);
			return expiso($fp, $fname, $bksz, $bkhd);
		}
	}
	fclose($fp);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	bin2iso( $argv[$i] );
