<?php
require "common.inc";
require "common-guest.inc";

//define("DRY_RUN", true);
$gp_clut = "";

function mag_decode( &$file, $w, $h, $pb1, $pb4, $pc )
{
	printf("== mag_decode( %x , %x , %x , %x , %x )\n", $w, $h, $pb1, $pb4, $pc);
	if ( defined("DRY_RUN") )
		return "";
	// https://github.com/46OkuMen/rusty/blob/master/mag.py
	// https://46okumen.com/projects/rusty/
	$pix = array();
	$bycod = 0;
	$bylen = 0;
	$flgno = array(0 => 0, 0x80 => 0);

	$action = array_fill(0, $w/8, 0);
	$actpos = 0;

	$actdx = array(0,1,2,4, 0,1, 0,1,2, 0,1,2, 0,1,2,  0);
	$actdy = array(0,0,0,0, 1,1, 2,2,2, 4,4,4, 8,8,8, 16);

	$bak = $pb4;
	while ( $pb1 < $bak )
	{
		if ( $bylen == 0 )
		{
			$bycod = ord( $file[$pb1] );
				$pb1++;
			$bylen = 8;
			printf("%6x BYTECODE %2x\n", $pb1-1, $bycod);
		}

		$flg = $bycod & 0x80;
			$bycod <<= 1;
			$bylen--;

		$flgno[$flg]++;
		if ( $flg == 0 )
		{
			$act = ord( $file[$pb4] );
				$pb4++;
			$action[ $actpos ] ^= $act;
			printf("%6x ACT[%d] ^ %2x\n", $pb4-1, $actpos, $act);
		}

		printf("-- ACT %2x\n", $action[$actpos]);
		$by = array();
		$by[] = ($action[$actpos] >> 4) & BIT4;
		$by[] = ($action[$actpos] >> 0) & BIT4;
		$actpos = ($actpos + 1) % ($w/8);

		foreach ( $by as $b )
		{
			if ( $b == 0 )
			{
				printf("---- COPY %x\n", $pc);
				if ( isset( $file[$pc+1] ) )
					$pix[] = substr($file, $pc, 2);
				else
					$pix[] = ZERO . ZERO;
				$pc += 2;
			}
			else
			{
				$p = ($actdy[$b] * $w/4) + $actdx[$b];
				printf("---- REF  %x  [-%d,-%d]\n", $p, $actdx[$b], $actdy[$b]);
				$p = count($pix) - $p;
				if ( isset( $pix[$p] ) )
					$pix[] = $pix[$p];
				else
					$pix[] = ZERO . ZERO;
			}
		} // foreach ( $by as $b )

	} // while ( $pb1 < $bak )

	printf("flags [0]%x , [1]%x\n", $flgno[0], $flgno[0x80]);
	return implode('', $pix);
}

function sectmgx( &$file, $fname, $pos )
{
	printf("== sectmag( $fname , %x )\n", $pos);
	debug( substr($file, $pos+0, 4) );

	$x1 = str2int($file, $pos+ 4, 2);
	$y1 = str2int($file, $pos+ 6, 2);
	$x2 = str2int($file, $pos+ 8, 2);
	$y2 = str2int($file, $pos+10, 2);
	$w = int_ceil($x2-$x1, 8);
	$h = int_ceil($y2-$y1, 8);
	if ( $w == 0 || $h == 0 )
		return;

	$b1 = str2int($file, $pos+12, 4);
	$b2 = str2int($file, $pos+16, 4);
	$b3 = str2int($file, $pos+20, 4); // size
	$b4 = str2int($file, $pos+24, 4);
	$b5 = str2int($file, $pos+28, 4); // size

	global $gp_clut;
	$gp_clut = "";
	for ( $i=0; $i < 0x30; $i += 3 )
	{
		$p = $pos + 32 + $i;
		// in GRB order
		$gp_clut .= $file[$p+1] . $file[$p+0] . $file[$p+2] . BYTE;
	}

	$pix = mag_decode($file, $w, $h, $pos+$b1, $pos+$b2, $pos+$b4 );
	//save_file("$fname.pix", $pix);

	while ( strlen($pix) % 2 )
		$pix .= ZERO;

	$data = "CLUT";
	$data .= chrint(16, 4);
	$data .= chrint($w, 4);
	$data .= chrint($h, 4);
	$data .= $gp_clut;

	$len = strlen($pix);
	for ( $i=0; $i < $len; $i += 2 )
	{
		$b0 = ord( $pix[$i+0] );
		$b1 = ord( $pix[$i+1] );

		$j = 4;
		while ( $j > 0 )
		{
			$j--;
			$b01 = $b0 >> ($j+4);
			$b02 = $b0 >> ($j+0);
			$b11 = $b1 >> ($j+4);
			$b12 = $b1 >> ($j+0);
			$bj = bits8(0,0,0,0, $b11,$b12,$b01,$b02);
			$data .= chr($bj);
		}
	} // for ( $i=0; $i < $len; $i += 2 )
	save_file("$fname.clut", $data);

	return;
}
//////////////////////////////
function rusty( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	// for *.mgx
	$mgc = substr0($file, 0, chr(0x1a));
	if ( substr($mgc, 0, 6) == "MAKI02" )
		return sectmgx($file, $fname, strlen($mgc)+1);

	return;
}

for ( $i=1; $i < $argc; $i++ )
	rusty( $argv[$i] );

/*
op.com
	staff0.mgx
	r_a11.mgx r_a11_1.mgx r_a11_2.mgx r_a11_3.mgx r_a11_4.mgx r_a11_5.mgx r_a11_6.mgx r_a11_7.mgx
	r_a21.mgx r_a21pal.mgx r_a21p_.mgx r_a23.mgx r_a24.mgx r_a26.mgx r_a26a.mgx
	r_a31.mgx r_a32.mgx r_a33.mgx r_a35.mgx r_a36.mgx
	r_b11.mgx r_b12.mgx r_b14.mgx r_b15.mgx r_b16a.mgx r_b16b.mgx
	r_b21a.mgx r_b21b.mgx r_b22.mgx r_b23.mgx r_b24.mgx
	r_b31.mgx r_b32_1.mgx r_b32_2.mgx r_b33.mgx r_b33a.mgx r_b33b.mgx r_b33c.mgx r_b33d.mgx r_b34_1.mgx r_b34_2.mgx
 */
