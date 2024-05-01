<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D Games.
    <https://github.com/rufaswan/Web2D_Games>

Web2D Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 *
 * Special Thanks
 *   SFF Decompiler
 *   https://github.com/PopovEvgeniy/sffdecompiler
 *     Popov Evgeniy Alekseyevich
 *   SFF Extract
 *   https://network.mugenguild.com/winane/software/index.html
 *     Osuna Richert Christophe
 */
require 'common.inc';

$gp_required_sprite = array(
	'5000,0'  => 'Hit high while standing, head back slightly - Feet',
	'5000,10' => 'Hit high while standing, head back more     - Feet',
	'5000,20' => 'Hit high while standing, head back far      - Feet',
	'5001,0'  => 'Hit high while standing, head back slightly - Midsection',
	'5001,10' => 'Hit high while standing, head back more     - Midsection',
	'5001,20' => 'Hit high while standing, head back far      - Midsection',
	'5002,0'  => 'Hit high while standing, head back slightly - Head',
	'5002,10' => 'Hit high while standing, head back more     - Head',
	'5002,20' => 'Hit high while standing, head back far      - Head',

	'5010,0'  => 'Hit low while standing, bent over slightly - Feet',
	'5010,10' => 'Hit low while standing, bent over more     - Feet',
	'5010,20' => 'Hit low while standing, bent over far      - Feet',
	'5011,0'  => 'Hit low while standing, bent over slightly - Midsection',
	'5011,10' => 'Hit low while standing, bent over more     - Midsection',
	'5011,20' => 'Hit low while standing, bent over far      - Midsection',
	'5012,0'  => 'Hit low while standing, bent over slightly - Head',
	'5012,10' => 'Hit low while standing, bent over more     - Head',
	'5012,20' => 'Hit low while standing, bent over far      - Head',

	'5030,0'  => 'Hit back, head back slightly        - Where feet would be if standing',
	'5030,10' => 'Hit back, head back far             - Where feet would be',
	'5030,20' => 'Hit in stomach with body horizontal - Where feet would be',
	'5030,30' => 'Hit back, body horizontal           - Where feet would be',
	'5030,40' => 'Falling, head down slightly         - Where feet would be',
	'5030,50' => 'Falling, head down far              - Where feet would be',
	'5031,0'  => 'Hit back, head back slightly        - Midsection',
	'5031,10' => 'Hit back, head back far             - Midsection',
	'5031,20' => 'Hit in stomach with body horizontal - Midsection',
	'5031,30' => 'Hit back, body horizontal           - Midsection',
	'5031,40' => 'Falling, head down slightly         - Midsection',
	'5031,50' => 'Falling, head down far              - Midsection',
	'5032,0'  => 'Hit back, head back slightly        - Head',
	'5032,10' => 'Hit back, head back far             - Head',
	'5032,20' => 'Hit in stomach with body horizontal - Head',
	'5032,30' => 'Hit back, body horizontal           - Head',
	'5032,40' => 'Falling, head down slightly         - Head',
	'5032,50' => 'Falling, head down far              - Head',

	'5040,0'  => 'Fall and hitting ground   - Ground-level (eg. back)',
	'5040,10' => 'Lying down on ground      - Ground-level',
	'5040,20' => 'Hit while lying on ground - Ground-level',
	'5041,0'  => 'Fall and hitting ground   - Middle of body',
	'5041,10' => 'Lying down on ground      - Middle of body',
	'5041,20' => 'Hit while lying on ground - Middle of body',
	'5042,0'  => 'Fall and hitting ground   - Head',
	'5042,10' => 'Lying down on ground      - Head',
	'5042,20' => 'Hit while lying on ground - Head',

	'5060,0'  => 'Hit up, head pointed up, body vertical - Where feet would be if standing',
	'5060,10' => 'Head pointed down, body vertical       - Where feet would be',
	'5061,0'  => 'Hit up, head pointed up, body vertical - Midsection',
	'5061,10' => 'Head pointed down, body vertical       - Midsection',
	'5062,0'  => 'Hit up, head pointed up, body vertical - Head',
	'5062,10' => 'Head pointed down, body vertical       - Head',

	'5070,0'  => 'Thrown, body tiled forwards slightly   - Where feet would be if standing',
	'5070,10' => 'Thrown, body tiled forwards far        - Where feet would be',
	'5070,20' => 'Thrown, body horizontal, head in front - Where feet would be',
	'5071,0'  => 'Thrown, body tiled forwards slightly   - Midsection',
	'5071,10' => 'Thrown, body tiled forwards far        - Midsection',
	'5071,20' => 'Thrown, body horizontal, head in front - Midsection',
	'5072,0'  => 'Thrown, body tiled forwards slightly   - Head',
	'5072,10' => 'Thrown, body tiled forwards far        - Head',
	'5072,20' => 'Thrown, body horizontal, head in front - Head',

	'5020,0'  => 'Hit while crouching, head back slightly - Feet',
	'5020,10' => 'Hit while crouching, head back more     - Feet',
	'5020,20' => 'Hit while crouching, head back far      - Feet',
);

function sffv2( &$file, $dir )
{
	return;
}
//////////////////////////////
function pcx2clut( &$file, $pos, $is_prev_pal )
{
	$magic = str2int($file, $pos, 1);
	if ( $magic !== 0xa )  return 0;

	$paintbrush = str2int($file, $pos + 0x01, 1);
	$enc_type   = str2int($file, $pos + 0x02, 1); // 0=none 1=rle
	$bpp_type   = str2int($file, $pos + 0x03, 1); // 1 2 4 8
	$min_x      = str2int($file, $pos + 0x04, 2);
	$min_y      = str2int($file, $pos + 0x06, 2);
	$max_x      = str2int($file, $pos + 0x08, 2);
	$max_y      = str2int($file, $pos + 0x0a, 2);
	$dpi_x      = str2int($file, $pos + 0x0c, 2);
	$dpi_y      = str2int($file, $pos + 0x0e, 2);
	$ega_pal    = substr ($file, $pos + 0x10, 0x30);
	// 40 = blank or comments
	$plane_cnt  = str2int($file, $pos + 0x41, 1); // 1=clut 3=rgb 4=rgba
	$plane_row  = str2int($file, $pos + 0x42, 2);
	$mode_pal   = str2int($file, $pos + 0x44, 2); // 1=color 2=gray
	$screen_x   = str2int($file, $pos + 0x46, 2);
	$screen_y   = str2int($file, $pos + 0x48, 2);
	// 4a-7f = blank or comments
		$pos += 0x80;

	$w = $screen_x;
	$h = $screen_y;
	$pix = '';
	for ( $y=0; $y < $screen_y; $y++ )
	{
		$plane = array('','','','');
		for ( $p=0; $p < $plane_cnt; $p++ )
		{
			$x = $screen_x;
			while ( $x > 0 )
			{
				$be = $file[$pos];
					$pos++;
				$bd = ord($be);
				if ( $bd >= 0xc0 )
				{
					$dlen = $bd & 0x3f;
					if ( $dlen > 0 )
					{
						$be = $file[$pos];
							$pos++;
						$plane[$p] .= str_repeat($be, $dlen);
						$x -= $dlen;
					}
					else
					{
						$plane[$p] .= str_repeat(ZERO, $x);
						$x = 0;
					}
				}
				else
				{
					$plane[$p] .= $be;
					$x--;
				}
			} // while ( $x > 0 )
		} // for ( $p=0; $p < $plane_cnt; $p++ )

		for ( $x=0; $x < $screen_x; $x++ )
		{
			switch ( $plane_cnt )
			{
				case 1:
					$pix .= $plane[0][$x];
					break;
				case 3:
					$pix .= $plane[0][$x]; // r
					$pix .= $plane[1][$x]; // g
					$pix .= $plane[2][$x]; // b
					$pix .= BYTE;
					break;
				case 4:
					$pix .= $plane[0][$x]; // r
					$pix .= $plane[1][$x]; // g
					$pix .= $plane[2][$x]; // b
					$pix .= $plane[3][$x]; // a
					break;
			} // switch ( $plane_cnt )
		} // for ( $x=0; $x < $screen_x; $x++ )
	} // for ( $y=0; $y < $screen_y; $y++ )

	$img = array(
		'w'   => $w,
		'h'   => $h,
		'pix' => $pix,
	);
	if ( $is_prev_pal )
		return $img;

	$pal = '';
	$pos++;
	for ( $i=0; $i < 0x300; $i += 3 )
	{
		$pal .= substr($file, $pos, 3);
		$pal .= BYTE;
			$pos += 3;
	}

	$img['pal'] = $pal;
	$img['cc']  = strlen($pal) >> 2;
	return $img;
}

function sffv1( &$file, $dir )
{
	$cnt_group = str2int($file, 0x10, 4);
	$cnt_image = str2int($file, 0x14, 4);
	$hdsz_main = str2int($file, 0x18, 4);
	$hdsz_sub  = str2int($file, 0x1c, 4);
	$type_pal  = str2int($file, 0x20, 1);
	// 21-1ff = blank or comments

	global $gp_required_sprite;
	$def  = ";Sprite\r\n";
	$def .= ";group,itemno, fname, axisx, axisy\r\n";
	$def .= "[Sprite]\r\n";

	$pos = $hdsz_main;
	$len = strlen($file);
	$pal = '';
	$id  = 0;
	while ( $pos < $len )
	{
		$off_link    = str2int($file, $pos + 0x00, 4);
		$len_sub     = str2int($file, $pos + 0x04, 4);
		$image_x     = str2int($file, $pos + 0x08, 2, true);
		$image_y     = str2int($file, $pos + 0x0a, 2, true);
		$group_id    = str2int($file, $pos + 0x0c, 2, true);
		$image_id    = str2int($file, $pos + 0x0e, 2, true);
		$prev_img_id = str2int($file, $pos + 0x10, 2, true);
		$is_prev_pal = str2int($file, $pos + 0x12, 1);
		// 13-1f = blank or comments

		// normal sprite
		if ( $len_sub > 0 )
		{
			$fn   = sprintf('%s/%06d', $dir, $id);
			$def .= sprintf('%6d , %6d , %06d.clut , %6d , %6d', $group_id, $image_id, $id, $image_x, $image_y);
			printf("%8x  %8x  %s\n", $pos, $len_sub, $fn);

			$sub = substr($file, $pos + $hdsz_sub, $len_sub);
			$img = pcx2clut($sub, 0, $is_prev_pal);
			if ( $img === 0 )
				save_file("$fn.bin", $sub);
			else
			{
				if ( $is_prev_pal )
				{
					$img['pal'] = $pal;
					$img['cc']  = strlen($pal) >> 2;
				}
				else
					$pal = $img['pal'];
				save_clutfile("$fn.clut", $img);
			}
		}
		// linked or duplicate sprites
		else {
			$fn   = sprintf('%s/%06d', $dir, $prev_img_id);
			$def .= sprintf('%6d , %6d , %06d.clut , %6d , %6d', $group_id, $image_id, $prev_img_id, $image_x, $image_y);
			printf("%8x  %8x  @%s\n", $pos, $len_sub, $fn);
		}

		$t = sprintf('%d,%d', $group_id, $image_id);
		if ( isset($gp_required_sprite[$t]) )
			$def .= sprintf(' ; %s', $gp_required_sprite[$t]);
		$def .= "\r\n";

		$pos = $off_link;
		$id++;
	} // while ( $pos < $len )

	save_file("$dir/sffv1.def", $def);
	return;
}
//////////////////////////////
function mugensff( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 11) !== 'ElecbyteSpr' )
		return;

	$ver = array( ord($file[15]) , ord($file[14]) , ord($file[13]) , ord($file[12]) );
	printf("SFF version = %s\n", implode('.',$ver));

	$dir = str_replace('.', '_', $fname);
	switch ( $ver[0] )
	{
		case 1:  return sffv1($file, $dir);
		case 2:  return sffv2($file, $dir);
	} // switch ( $ver[0] )
	return;
}

argv_loopfile($argv, 'mugensff');
