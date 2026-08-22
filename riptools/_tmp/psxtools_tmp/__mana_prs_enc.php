<?php
require "common.inc";

function manaenc( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$data = chr(1);

	$ed = strlen($file);
	$st = 0;
	while ( $st < $ed )
	{
		if ( $file[$st] == ZERO )
		{
			printf("= %x pack ZERO , %x\n", $st, strlen($data));
			$cnt = 0;
			while ( $st < $ed && $file[$st] == ZERO )
			{
				$cnt++;
				$st++;
			}
			echo "  ZERO $cnt\n";

			while ( $cnt > 0xf4 )
			{
				$cnt -= 0xf4;
				$data .= chr(0xf1) . chr(0xf0) . ZERO;
				echo "  F4 ZERO rem $cnt\n";
			}
			while ( $cnt > 4 )
			{
				$d = $cnt - 4;
				$cnt -= ($d + 4);
				$data .= chr(0xf1) . chr($d) . ZERO;
				echo "  04 ZERO rem $cnt\n";
			}
			if ( $cnt )
			{
				$d = $cnt - 1;
				$data .= chr($d);
				while ( $cnt )
				{
					$cnt--;
					$data .= ZERO;
				}
				echo "  ZERO rem $cnt\n";
			}
		}
		else
		{
			printf("= %x pack DATA , %x\n", $st, strlen($data));
			$cnt = 0;
			while ( $st < $ed && $file[$st+$cnt] != ZERO )
				$cnt++;
			echo "  DATA $cnt\n";

			while ( $cnt > 0xe0 )
			{
				$data .= chr(0xdf) . substr($file, $st, 0xe0);
				$st  += 0xe0;
				$cnt -= 0xe0;
				echo "  E0 DATA rem $cnt\n";
			}
			if ( $cnt )
			{
				$d = $cnt - 1;
				$data .= chr($d) . substr($file, $st, $cnt);
				$st += $cnt;
				echo "  DATA rem $cnt\n";
			}
		}
	} // while ( $st < $ed )

	$data .= BYTE;

	file_put_contents("$fname.enc", $data);
}

for ( $i=1; $i < $argc; $i++ )
	manaenc( $argv[$i] );
