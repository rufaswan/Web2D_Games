<?php
require "common.inc";

function dxarip($fp, &$gp_data, $gp_dir)
{
	@mkdir($gp_dir, 0755, true);

	$dir = $gp_dir;
	$mkdir = false;
	foreach( $gp_data as $k => $data )
	{
		printf("%8x , %8x , %8x , %8x , %s\n", $data[0], $data[1], $data[2], $k, $data[3]);
		if ( $data[2] == BIT32 )
		{
			if ( $data[1] == 0 ) // DIR
			{
				if ( $mkdir )
					$dir .= "/{$data[3]}";
				else
				{
					$dir = "$gp_dir/{$data[3]}";
					$mkdir = true;
				}
				@mkdir($dir, 0755, true);
			}
			else // UNCOMPRESSED FILE
			{
				$mkdir = false;
				fseek($fp, $data[0], SEEK_SET);
				file_put_contents("$dir/{$data[3]}", fread($fp, $data[1]));
			}
		}
		else // COMPRESSED FILE
		{
			$mkdir = false;
			fseek($fp, $data[0], SEEK_SET);
			file_put_contents("$dir/{$data[3]}.pack", fread($fp, $data[2]));
		}
	} // foreach( $gp_data as $data )
	return;
}

function dxalib( $fname )
{
	$fp = fopen( $fname, "rb" );
		if ( ! $fp )   return;

	$head = fread($fp, 0x80);
	if ( substr($head, 0, 2) != "DX" )
		return;

	$gp_dir = str_replace('.', '_', $fname);
	$gp_data = array();

	$ver = ord($head[2]);
	$meta_sz  = str2int($head, 4,  4);
	$head_sz  = str2int($head, 8,  4);
	$meta_off = str2int($head, 12, 4) + 4;
	$data_off = str2int($head, 16, 4);

	$fsiz = filesize($fname);
	$ed = $fsiz - $meta_off;
	fseek($fp, $meta_off, SEEK_SET);
	$meta = fread($fp, $ed);

	$st = $data_off + 0x28;
	while ( $st < $ed )
	{
		$fnpos = str2int($meta, $st+0, 4);
		if ( $fnpos == 0 )
			break;

		$upper = substr0($meta, $fnpos);
		$lower = strtolower($upper);

		$fnoff = str2int($meta, $st+0x20, 4);
		$fnsz1 = str2int($meta, $st+0x24, 4);
		$fnsz2 = str2int($meta, $st+0x28, 4);
		//printf("%8x , %8x , %8x , %8x , %s\n", $fnoff, $fnsz1, $fnsz2, $fnpos, $upper);

		$gp_data[$fnpos] = array($head_sz+$fnoff, $fnsz1, $fnsz2, $lower);
		$st += 0x2c;
	}

	ksort($gp_data);
	dxarip($fp, $gp_data, $gp_dir);
	fclose($fp);
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	dxalib( $argv[$i] );
