<?php
/*
[license]
[/license]
 */
require "common.inc";

//define("NO_TRACE", true);

function mana_decode( &$file , $st )
{
	// sub_80014448-80014888 , SLPS_021.70
	// data loaded to 8001dc003 , decode to 8004fdd8
	trace("=== begin sub_80014448 ===\n");
	$dec = "";
	$ed = strlen($file);
	while ( $st < $ed )
	{
		trace("%6x  %6x  ", $st, strlen($dec));
		$b0 = ord( $file[$st+0] );
		switch ( $b0 - 0xf0 )
		{
			case 0:
				$b1 = ord( $file[$st+1] );
					$st += 2;
				$len = ($b1 & 0x0f) + 3;
				$s0 = chr($b1 >> 4);
				trace("F0 DUPL %2x [%3d]\n", $b1>>4, $len);
				for ( $i=0; $i < $len; $i++ )
					$dec .= $s0;
				break;
			case 1:
				$b1 = ord( $file[$st+1] );
				$b2 = ord( $file[$st+2] );
					$st += 3;
				$len = $b1 + 4;
				$s0 = chr($b2);
				trace("F1 DUPL %2x [%3d]\n", $b2, $len);
				for ( $i=0; $i < $len; $i++ )
					$dec .= $s0;
				break;
			case 2:
				$b1 = ord( $file[$st+1] );
				$b2 = ord( $file[$st+2] );
					$st += 3;
				$len = $b1 + 2;
				$s0 = chr($b2 & 0x0f);
				$s1 = chr($b2 >> 4);
				trace("F2 DUPL %2x %2x [%3d]\n", $b2&0xf, $b2>>4, $len);
				for ( $i=0; $i < $len; $i++ )
				{
					$dec .= $s0;
					$dec .= $s1;
				}
				break;
			case 3:
				$b1 = ord( $file[$st+1] );
				$b2 = ord( $file[$st+2] );
				$b3 = ord( $file[$st+3] );
					$st += 4;
				$len = $b1 + 2;
				$s0 = chr($b2);
				$s1 = chr($b3);
				trace("F3 DUPL %2x %2x [%3d]\n", $b2, $b3, $len);
				for ( $i=0; $i < $len; $i++ )
				{
					$dec .= $s0;
					$dec .= $s1;
				}
				break;
			case 4:
				$b1 = ord( $file[$st+1] );
				$b2 = ord( $file[$st+2] );
				$b3 = ord( $file[$st+3] );
				$b4 = ord( $file[$st+4] );
					$st += 5;
				$len = $b1 + 2;
				$s0 = chr($b2);
				$s1 = chr($b3);
				$s2 = chr($b4);
				trace("F4 DUPL %2x %2x %2x [%3d]\n", $b2, $b3, $b4, $len);
				for ( $i=0; $i < $len; $i++ )
				{
					$dec .= $s0;
					$dec .= $s1;
					$dec .= $s2;
				}
				break;
			case 5:
				$b1 = ord( $file[$st+1] );
				$b2 = ord( $file[$st+2] );
					$st += 3;
				$len = $b1 + 4;
				$s0 = chr($b2);
				trace("F5 REF  %2x st [%3d]\n", $b2, $len);
				for ( $i=0; $i < $len; $i++ )
				{
					$dec .= $s0;
					$dec .= $file[$st];
						$st++;
				}
				break;
			case 6:
				$b1 = ord( $file[$st+1] );
				$b2 = ord( $file[$st+2] );
				$b3 = ord( $file[$st+3] );
					$st += 4;
				$len = $b1 + 3;
				$s0 = chr($b2);
				$s1 = chr($b3);
				trace("F6 REF  %2x %2x st [%3d]\n", $b2, $b3, $len);
				for ( $i=0; $i < $len; $i++ )
				{
					$dec .= $s0;
					$dec .= $s1;
					$dec .= $file[$st];
						$st++;
				}
				break;
			case 7:
				$b1 = ord( $file[$st+1] );
				$b2 = ord( $file[$st+2] );
				$b3 = ord( $file[$st+3] );
				$b4 = ord( $file[$st+4] );
					$st += 5;
				$len = $b1 + 2;
				$s0 = chr($b2);
				$s1 = chr($b3);
				$s2 = chr($b4);
				trace("F7 REF  %2x %2x %2x st [%3d]\n", $b2, $b3, $b4, $len);
				for ( $i=0; $i < $len; $i++ )
				{
					$dec .= $s0;
					$dec .= $s1;
					$dec .= $s2;
					$dec .= $file[$st];
						$st++;
				}
				break;
			case 8:
				$b1 = ord( $file[$st+1] );
				$b2 = ord( $file[$st+2] );
					$st += 3;
				$len = $b1 + 4;
				$s0 = $b2;
				trace("F8 INC  %2x++ [%3d]\n", $b2, $len);
				for ( $i=0; $i < $len; $i++ )
				{
					$dec .= chr($s0);
					$s0++;
				}
				break;
			case 9:
				$b1 = ord( $file[$st+1] );
				$b2 = ord( $file[$st+2] );
					$st += 3;
				$len = $b1 + 4;
				$s0 = $b2;
				trace("F9 DEC  %2x-- [%3d]\n", $b2, $len);
				for ( $i=0; $i < $len; $i++ )
				{
					$dec .= chr($s0);
					$s0--;
				}
				break;
			case 10:
				$b1 = ord( $file[$st+1] );
				$b2 = ord( $file[$st+2] );
				$b3 = ord( $file[$st+3] );
					$st += 4;
				$len = $b1 + 5;
				$s0 = $b2;
				trace("FA INC  %2x += %2x [%3d]\n", $b2, $b3, $len);
				for ( $i=0; $i < $len; $i++ )
				{
					$dec .= chr($s0);
					$s0 += $b3;
				}
				break;
			case 11:
				$b1 = ord( $file[$st+1] );
				$b2 = ord( $file[$st+2] );
				$b3 = ord( $file[$st+3] );
				$b4 = ord( $file[$st+4] );
					$st += 5;
				$len = $b1 + 3;
				$s0 = $b2;
				$s1 = $b3;
				$s2 = ($b4 & 0x80) ? $b4 - 0x100 : $b4;
				trace("FB INC2 %2x %2x += %3d [%3d]\n", $b2, $b3, $s2, $len);
				for ( $i=0; $i < $len; $i++ )
				{
					$dec .= chr($s0);
					$dec .= chr($s1);
					$t0 = ($s1 << 8) | $s0;
					$t0 += $s2;
					$s0 =  ($t0 & BIT8);
					$s1 = (($t0 >> 8) & BIT8);
				}
				break;
			case 12:
				$b1 = ord( $file[$st+1] );
				$b2 = ord( $file[$st+2] );
					$st += 3;
				$len =  ($b2 >> 4) + 4;
				$pos = (($b2 & 0x0f) << 8 ) | $b1;
				trace("FC POS  %3d [%3d]\n", $pos+1, $len);
				for ( $i=0; $i < $len; $i++ )
				{
					$cur = strlen($dec) - $pos - 1;
					$dec .= $dec[$cur];
				}
				break;
			case 13:
				$b1 = ord( $file[$st+1] );
				$b2 = ord( $file[$st+2] );
					$st += 3;
				$len = $b2 + 20;
				$pos = $b1;
				trace("FD POS  %3d [%3d]\n", $pos+1, $len);
				for ( $i=0; $i < $len; $i++ )
				{
					$cur = strlen($dec) - $pos - 1;
					$dec .= $dec[$cur];
				}
				break;
			case 14:
				$b1 = ord( $file[$st+1] );
					$st += 2;
				$len = ($b1 & 0x0f) + 3;
				$pos = ($b1 & 0xf0) >> 1;
				trace("FE POS  %3d [%3d]\n", $pos+8, $len);
				for ( $i=0; $i < $len; $i++ )
				{
					$cur = strlen($dec) - $pos - 8;
					$dec .= $dec[$cur];
				}
				break;
			case 15:
				trace("FF done\n");
				break 2;
			default:
				$st++;
				$len = $b0 + 1;
				trace("-- COPY [%3d]\n", $len);
				for ( $i=0; $i < $len; $i++ )
				{
					$dec .= $file[$st];
					$st++;
				}
				break;
		} // switch ( $b1 - 0xf0 )
	} // while ( $st < $ed )

	trace("=== end sub_80014448 ===\n");
	return $dec;
}

function mana( $fname )
{
	// for /bin/*.bin
	// for /ana/etc_etc/*.prs
	// for /map/*/*.prs
	// for /wm/wmap/*.pim
	// for /wm/wmtim/wmapt*/wm_*.pim
	$bak = file_exists("$fname.bak");
	if ( $bak )
		$file = file_get_contents("$fname.bak");
	else
		$file = file_get_contents($fname);

	if ( empty($file) )
		return;
	// file must starts with 01 and ends with FF
	$ed = strlen($file);
	if ( $file[0] != chr(1) || $file[$ed-1] != BYTE )
		return;

	if ( ! $bak )
		file_put_contents("$fname.bak", $file);

	$dec = mana_decode($file, 1);
	file_put_contents($fname, $dec);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mana( $argv[$i] );
