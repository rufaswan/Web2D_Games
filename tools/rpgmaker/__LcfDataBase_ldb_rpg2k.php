<?php
// Derived from Source:
//   https://github.com/EasyRPG/liblcf/blob/master/src/generated/ldb_chunks.h
// Original License:
//   MIT License
require "common.inc";

$gp_fname = array();
////////////////////////////////////////
function intv( &$file, &$st )
{
	$ret = 0;
	while(1)
	{
		$b1 = ord( $file[$st] );
			$st++;

		$ret <<= 7;
		$ret += ($b1 & 0x7f);

		if ( $b1 & 0x80 )
			continue;
		return $ret;
	}
}

function lmu_int_var( $name, &$file, &$st )
{
	$vsz = intv( $file, $st );
	$val = intv( $file, $st );
	printf("%s = %x [+=%x]\n", $name, $val, $vsz);
}
function lmu_int_array( $name, &$file, &$st )
{
	$vsz = intv( $file, $st );
	$st += $vsz;
	printf("%s += %x\n", $name, $vsz);
}
////////////////////////////////////////
function rpg2k( $fname )
{
	$file = file_get_contents($fname);
		if ( empty($file) )  return;

	if ( substr($file, 1, 11) != "LcfDataBase" )
		return;

	global $gp_fname;
	$ed = strlen($file);
	$st = 0xc;
	printf("=== %s / %x ===\n", $fname, $ed );
	while ( $st < $ed )
	{
		$b1 = ord( $file[$st] );
			$st++;
		printf("%8x  %2x ", $st-1, $b1);
		switch ( $b1 )
		{
			case 0x20:  lmu_int_array("rpg::btl_anim", $file, $st ); break; // rpg2k3
			case 0x1f:  lmu_int_array("unk 1f", $file, $st );  break; // rpg2k3
			case 0x1e:  lmu_int_array("rpg::class", $file, $st );    break; // rpg2k3
			case 0x1d:  lmu_int_array("rpg::btl_cmd", $file, $st );  break; // rpg2k3
			case 0x1c:  lmu_int_array("unk 1c", $file, $st );  break; // rpg2k3
			case 0x1b:  lmu_int_array("unk 1b", $file, $st );  break; // rpg2k3

			case 0x1a:  lmu_int_array("rpg::version", $file, $st );   break;
			case 0x19:  lmu_int_array("rpg::com_event", $file, $st ); break;
			case 0x18:  lmu_int_array("rpg::variable", $file, $st );  break;
			case 0x17:  lmu_int_array("rpg::switchs", $file, $st );   break;
			case 0x16:
				$vsz = intv( $file, $st );
				$st += $vsz;
				printf("rpg::system += %x\n", $vsz);
				break;
			case 0x15:  lmu_int_array("rpg::terms", $file, $st );     break;
			case 0x14:
				$vsz = intv( $file, $st );
				$st += $vsz;
				printf("rpg::chipset += %x\n", $vsz);
				break;
			case 0x13:
				$vsz = intv( $file, $st );
				$st += $vsz;
				printf("rpg::animation += %x\n", $vsz);
				break;
			case 0x12:  lmu_int_array("rpg::state", $file, $st );     break;
			case 0x11:  lmu_int_array("rpg::attribute", $file, $st ); break;
			case 0x10:
				$vsz = intv( $file, $st );
				$st += $vsz;
				printf("rpg::terrain += %x\n", $vsz);
				break;
			case 0xf:   lmu_int_array("rpg::troop", $file, $st );     break;
			case 0xe:
				$vsz = intv( $file, $st );
				$st += $vsz;
				printf("rpg::enemy += %x\n", $vsz);
				break;
			case 0xd:   lmu_int_array("rpg::item", $file, $st );      break;
			case 0xc:   lmu_int_array("rpg::skill", $file, $st );     break;
			case 0xb:
				$vsz = intv( $file, $st );
				$st += $vsz;
				printf("rpg::actor += %x\n", $vsz);
				break;
			case 0:
				printf("END\n");
				return;
			default:
				exit("UNKNONW\n");
		} // switch ( $b1 )
	} // while ( $st < $ed )
	return;
}
////////////////////////////////////////
if ( $argc == 1 ) exit();
for ( $i=1; $i < $argc; $i++ )
	rpg2k( $argv[$i] );

//ksort($gp_fname);
print_r($gp_fname);
