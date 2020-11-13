<?php
// cache 80 MB
define("WRITE_S", 80 << 20);

function expiso( $fp, $fname, $bksz, $bkhd, $skip )
{
	if ( $bksz == 0x800 )
		return;
	printf("== expiso( $fname , %x , %x , %x )\n", $bksz, $bkhd, $skip);

	$size = filesize($fname);
	$isop = fopen("$fname.iso", 'wb');
	$cache = "";
	for ( $i=$skip; $i < $size; $i += $bksz )
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
		//    type        s-size  s-head  cd-head
		array("iso/2048",  0x800,      0, 0),
		array("psx/2352",  0x930,   0x18, 0), // psx bin
		array("sat/2352",  0x930,   0x10, 0), // saturn bin
		array("bin/2336",  0x920,   0x08, 0),
		array("bin/2448",  0x990,   0x18, 0),

		array("bin/2352+930"  , 0x930, 0x10, 0x930  ),
		array("cvm/2048+1800" , 0x800,    0, 0x1800 ),
		array("cdi/2048+4b000", 0x800,    0, 0x4b000),
	);

	$bkhd = 0;
	$bksz = 0;
	foreach ( $detect as $det )
	{
		$p = $det[3] + ($det[1] * 0x10) + $det[2];

		fseek($fp, $p, SEEK_SET);
		$head = fread($fp, 0x800);
		if ( substr($head, 1, 5) == 'CD001' )
		{
			$skip = $det[3];
			$bkhd = $det[2];
			$bksz = $det[1];
			printf("DETECT %s , %x , %x , %x , %s\n", $det[0], $det[1], $det[2], $det[3], $fname);
			return expiso($fp, $fname, $bksz, $bkhd, $skip);
		}
	}
	fclose($fp);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	bin2iso( $argv[$i] );
