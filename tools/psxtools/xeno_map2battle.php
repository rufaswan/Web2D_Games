<?php
require "common.inc";

define("VRAM_W", 0x400);
define("VRAM_H", 0x200);

function vramcopy( &$vram, &$part, $dx, $dy, $w, $h )
{
	for ( $y=0; $y < $h; $y++ )
	{
		$dyy = ($dy+$y) * VRAM_W * 2;
		$syy =      $y  * $w       * 2;
		$dxx = $dyy + ($dx * 2);

		$b1 = substr($part, $syy, $w*2);
		strupd($vram, $dxx, $b1);
	}
	return;
}

function tex2vram( &$file )
{
	global $gp_clut;
	$vram = str_repeat(ZERO, VRAM_W*2*VRAM_H);
	$len = strlen($file);
	$pos = 0;
	while ( $pos < $len )
	{
		$b1 = str2int($file, $pos, 4);
		$bak = $pos;
		switch ( $b1 )
		{
			case 0x1200:
			case 0x1201:
				$bx = str2int($file, $pos+ 4, 2);
				$by = str2int($file, $pos+ 6, 2);
				$dx = str2int($file, $pos+ 8, 2);
				$dy = str2int($file, $pos+10, 2);
				$w  = str2int($file, $pos+0x0c, 2);
				$no = str2int($file, $pos+0x18, 2);
					$pos += (0x800 + $no * 0x800);

				$data = '';
				$h = 0;
				for ( $i=0; $i < $no; $i++ )
				{
					$p1 = $bak + 0x1c + ($i * 2);
					$p1 = str2int($file, $p1, 2);

					$p2 = $bak + 0x800 + ($i * 0x800);
					$data .= substr($file, $p2, $p1*$w*2);
					$h += $p1;
				}

				printf("%6x  %4x  %4x,%4x  %4x,%4x\n", $bak, $b1, $bx+$dx, $by+$dy, $w, $h);
				vramcopy( $vram, $data, $bx+$dx, $by+$dy, $w, $h );
				break;
		} // switch ( $b1 )
	} // while ( $pos < $len )

	return $vram;
}
//////////////////////////////
function xeno_decode( &$file, $st, $ed )
{
	echo "== begin sub_80032cac\n";

	$lw = str2int($file, $st, 3);
		$st += 4;
	$bycod = 0;
	$bylen = 0;
	$dec = '';
	while ( $st < $ed )
	{
		if ( $bylen == 0 )
		{
			$bycod = ord( $file[$st] ); // t8
				$st++;
			printf("BYTECODE %2x\n", $bycod);
			$bylen = 8; // t9
		}

		$flg = $bycod & 1;
			$bycod >>= 1;
			$bylen--;

		printf("%6x  %6x  ", $st, strlen($dec));
		if ( $flg )
		{
			$b1 = ord( $file[$st+0] ); // t0
			$b2 = ord( $file[$st+1] ); // t4
				$st += 2;
			$pos = ($b2 & 0xf) << 8;
				$pos |= $b1;
			$len = ($b2 >> 4) + 3;
			printf("REF  POS -%d LEN %d\n", $pos, $len);

			for ( $i=0; $i < $len; $i++ )
			{
				$p = strlen($dec) - $pos;
				$dec .= $dec[$p];
			}
		}
		else
		{
			$b1 = $file[$st]; // t0
				$st++;
			printf("COPY %2x\n", ord($b1));
			$dec .= $b1;
		}
	} // while ( $st < $ed )
	echo "== end sub_80032cac\n";

	return $dec;
}

function sectfile1( &$file )
{
	$sect = array();
	//for ( $i=0; $i < 8; $i++ )
	for ( $i=3; $i < 4; $i++ )
	{
		$p = 0x130 + ($i * 4);
		$p1 = str2int($file, $p+0, 3);
		$p2 = str2int($file, $p+4, 3);
		$bin = xeno_decode($file, $p1, $p2);
		$sect[$i] = $bin;
	}
	return $sect;
}
//////////////////////////////
function xeno( $fname1, $fname2 )
{
	$file1 = file_get_contents($fname1); // even
	$file2 = file_get_contents($fname2); // odd
	if ( empty($file1) || empty($file2) )
		return;

	$dir = str_replace('.', '_', $fname1);
	$dec = sectfile1($file1);
	$file2 = tex2vram($file2);

	$meta = $dec[3];
	$cnt = str2int($meta, 0, 3);
	for ( $i=0; $i < $cnt; $i++ )
	{
		$p1 = $i * 8;
		$sx = str2int($file1, $p1+0, 2);
		$sy = str2int($file1, $p1+2, 2);

		$w = VRAM_W - $sx;
		if ( $w > 0x40  )  $w = 0x40;
		$h = VRAM_H - $sy;
		if ( $h > 0x100 )  $h = 0x100;

		$pix = rippix8($file2, $sx*2, $sy, $w*2, $h, VRAM_W*2, VRAM_H);
		if ( trim($pix, ZERO) == "" )
			continue;

		$p1 = 4 + ($i * 4);
		$base = str2int($meta, $p1, 3);
		$c1 = str2int($meta, $base+0, 3);
		$z1 = str2int($meta, $base+4+($c1*4), 3);
		$data = substr($meta, $base, $z1);

		// same format as monster battle sprites
		$p1 = 8 + 12 + strlen($data);
		$btl =  chrint(1, 4);
		$btl .= chrint($p1, 4);

		$btl .= chrint(20, 4);
		$btl .= chrint($p1, 4);
		$btl .= chrint(0, 4);
		$btl .= $data;
		$btl .= chrint(1, 4);
		$btl .= chrint(8, 4);
		$btl .= chrint($w , 2);
		$btl .= chrint($h, 2);
		$btl .= $pix;
		save_file("$dir/$i.bin", $btl);

	} // for ( $i=0; $i < $cnt; $i++ )
	return;
}

for ( $i=1; $i < $argc; $i += 2 )
{
	if ( ! isset( $argv[$i+1] ) )
		continue;
	xeno( $argv[$i+0] , $argv[$i+1] );
}
