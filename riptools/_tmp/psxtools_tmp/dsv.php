<?php
require "/opt/rfslib.php";

function dsv( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$ed = strlen($file);
	$st = 0x69c;
	while ( $st < $ed )
	{
		$mgc = substr($file, $st, 4);
		switch ( $mgc )
		{
			case "ITCM":
			case "DTCM":
			case "WRAM":
			case "WRAX":
			case "9REG":
			case "VMEM":
			case "OAMS":
			case "LCDM":
				$siz = str2int($file, $st+8, 4);
				printf("%8x , %8x , $mgc\n", $st, $siz);
				file_put_contents("$fname.$mgc", substr($file, $st+12, $siz));
				$st += ($siz + 12);
				break;
			default:
				printf("UNKNOWN %x\n", $st);
				break 2;
		}
	}
	return;
}

for ( $i=1; $i < $argc; $i++ )
	dsv( $argv[$i] );
