<?php
// Derived from Source:
//   https://github.com/EasyRPG/liblcf/blob/master/src/generated/lmu_chunks.h
// Original License:
//   MIT License
require "common.inc";

$gp_fname = array();
////////////////////////////////////////
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

	if ( substr($file, 1, 10) != "LcfMapUnit" )
		return;

	global $gp_fname;
	$ed = strlen($file);
	$st = 0xb;
	printf("=== %s / %x ===\n", $fname, $ed );
	while ( $st < $ed )
	{
		$b1 = ord( $file[$st] );
			$st++;
		printf("%8x  %2x ", $st-1, $b1);
		switch ( $b1 )
		{
			case 0x5b:  lmu_int_var("save_count",  $file, $st );  break;
			case 0x51:
				$vsz = intv( $file, $st );
				$st += $vsz;
				printf("rpg::events += %x\n", $vsz);
				break;

			case 0x48:
				lmu_int_array("upper_layer", $file, $st );
				break;
			case 0x47:
				lmu_int_array("lower_layer", $file, $st );
				break;

			case 0x3e:
				lmu_int_array("generator_tile_id", $file, $st );
				break;
			case 0x3d:
				lmu_int_array("generator_y", $file, $st );
				break;
			case 0x3c:
				lmu_int_array("generator_x", $file, $st );
				break;
			case 0x38:  lmu_int_var("generator_extra_c", $file, $st );  break;
			case 0x37:  lmu_int_var("generator_extra_b", $file, $st );  break;
			case 0x36:  lmu_int_var("generator_floor_c", $file, $st );  break;
			case 0x35:  lmu_int_var("generator_floor_b", $file, $st );  break;
			case 0x34:  lmu_int_var("generator_upper_wall", $file, $st );  break;
			case 0x33:  lmu_int_var("generator_surround",   $file, $st );  break;
			case 0x32:  lmu_int_var("generator_height", $file, $st );  break;
			case 0x31:  lmu_int_var("generator_width",  $file, $st );  break;
			case 0x30:  lmu_int_var("generator_tiles",  $file, $st );  break;
			case 0x29:  lmu_int_var("generator_mode", $file, $st );  break;
			case 0x28:  lmu_int_var("generator_flag", $file, $st );  break;

			case 0x2a:  lmu_int_var("top_level",   $file, $st );  break;

			case 0x26:  lmu_int_var("parallax_sy", $file, $st );  break;
			case 0x25:  lmu_int_var("parallax_auto_loop_y", $file, $st );  break;
			case 0x24:  lmu_int_var("parallax_sx", $file, $st );  break;
			case 0x23:  lmu_int_var("parallax_auto_loop_x", $file, $st );  break;
			case 0x22:  lmu_int_var("parallax_loop_y", $file, $st );  break;
			case 0x21:  lmu_int_var("parallax_loop_x", $file, $st );  break;
			case 0x20:
				$vsz = intv( $file, $st );
				$str = substr($file, $st, $vsz);
				$st += $vsz;
				$gp_fname[ $str ] = array($fname,"parallax_name");
				printf("parallax_name = %s [+=%x]\n", $str, $vsz);
				break;
			case 0x1f:  lmu_int_var("parallax_flag", $file, $st );  break;

			case 0xb:   lmu_int_var("scroll_type", $file, $st );  break;
			case 0x3:   lmu_int_var("height", $file, $st );  break;
			case 0x2:   lmu_int_var("width",  $file, $st );  break;
			case 0x1:   lmu_int_var("chipset_id",  $file, $st );  break;
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
