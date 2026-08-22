<?php
require 'common.inc';

function dds1( $fname, $st, $ed, $blk )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$id = 0;
	while ( $st < $ed )
	{
		$sub = substr($file, $st, $blk);
		printf("%2x : %s\n", $id, printhex($sub));

		$id++;
		$st += $blk;
	}
	return;
}

dds1('SLPM_655.97', 0x291834, 0x2922b4, 0x1c);
dds1('SLPM_657.95', 0x301384, 0x302c44, 0x24);
