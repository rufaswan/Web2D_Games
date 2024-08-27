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
 * http://www.csclub.uwaterloo.ca:11068/mymc/ps2mcfs.html
 *   Ross Ridge
 * https://www.oocities.org/siliconvalley/station/8269/sma02/sma02.html#ECC
 */
require 'common.inc';
require 'common-isofs.inc';

$gp_txt = '';
$gp_ps2 = new ps2mc_ecc;
$gp_cardflag = array(
	 0x1 => 'CF_USE_ECC',
	 0x2 => 'CF_2',
	 0x4 => 'CF_4',
	 0x8 => 'CF_BAD_BLOCK',
	0x10 => 'CF_ERASE_ZERO',
	0x20 => 'CF_20',
	0x40 => 'CF_40',
	0x80 => 'CF_80',
);
$gp_dirmode = array(
	   0x1 => 'DF_READ',
	   0x2 => 'DF_WRITE',
	   0x4 => 'DF_EXECUTE',
	   0x8 => 'DF_PROTECTED',
	  0x10 => 'DF_FILE',
	  0x20 => 'DF_DIR',
	  0x40 => 'DF_40',
	  0x80 => 'DF_80',
	 0x100 => 'DF_100',
	 0x200 => 'O_CREATE',
	 0x400 => 'DF_400',
	 0x800 => 'DF_POCKETST',
	0x1000 => 'DF_PSX',
	0x2000 => 'DF_HIDDEN',
	0x4000 => 'DF_4000',
	0x8000 => 'DF_EXISTS',
);
//////////////////////////////
function bitint_convert( $bit, &$enum )
{
	if ( (int)$bit === $bit )
	{
		$arr = array();
		foreach ( $enum as $k => $v )
		{
			if ( $bit & $k )
				$arr[] = $v;
		}
		return $arr;
	}
	if ( is_array($bit) )
	{
		$int = 0;
		foreach ( $bit as $bv )
		{
			$id = array_search($bv, $enum);
			if ( $id !== false )
				$int |= $id;
		}
		return $int;
	}
	return 0;
}

function bit_cardflag( $flag )
{
	global $gp_cardflag;
	return bitint_convert($flag, $gp_cardflag);
}
function bit_dirmode( $mode )
{
	global $gp_dirmode;
	return bitint_convert($mode, $gp_dirmode);
}
//////////////////////////////
function ps2_header_verify( &$head, $key, $val )
{
	if ( $head[$key] !== $val )
		return php_error('head[%s] %x !== %x', $key, $head[$key], $val);
}

function ps2_header_read( &$sub, $ver )
{
	$head = array(
		'ver'              => $ver,
		'page_len'         => str2int($sub, 0x28, 2),
		'page_per_cluster' => str2int($sub, 0x2a, 2),
		'page_per_block'   => str2int($sub, 0x2c, 2),
		'cluster_per_card' => str2int($sub, 0x30, 4),
		'alloc_offset'     => str2int($sub, 0x34, 4),
		'rootdir_cluster'  => str2int($sub, 0x3c, 4),
		'backup_block1'    => str2int($sub, 0x40, 4),
		'backup_block2'    => str2int($sub, 0x44, 4),
		'ifc_list'         => array(), // indirect fat cluster
		'bad_block_list'   => array(),
		'card_type'        => str2int($sub, 0x150, 1),
		'card_flag'        => str2int($sub, 0x151, 1),
	);
	$or_ifc = 0;
	for ( $i=0; $i < 0x20; $i++ )
	{
		$head['ifc_list'      ][$i] = str2int($sub, 0x50 + ($i << 2), 4);
		$head['bad_block_list'][$i] = str2int($sub, 0xd0 + ($i << 2), 4, true);
			$or_ifc |= $head['ifc_list'][$i];
	}
	ps2_header_verify($head, 'page_len'        , 0x200 );
	ps2_header_verify($head, 'page_per_cluster', 2     );
	ps2_header_verify($head, 'page_per_block'  , 0x10  );
	ps2_header_verify($head, 'cluster_per_card', 0x2000);
	ps2_header_verify($head, 'alloc_offset'    , 0x29  );
	ps2_header_verify($head, 'rootdir_cluster' , 0     );
	ps2_header_verify($head, 'backup_block1'   , 0x3ff );
	ps2_header_verify($head, 'backup_block2'   , 0x3fe );
	ps2_header_verify($head, 'card_type'       , 2     );
	if ( $or_ifc !== 8 )  return;

	$head['card_flag'] = bit_cardflag($head['card_flag']);
	if ( array_search('CF_USE_ECC', $head['card_flag']) === false )
		return php_error('cardflag no CF_USE_ECC');
	return $head;
}

function ps2_cluster_read( &$fat, &$clst, &$file, &$head, $fat_id )
{
	$bin = '';
	while ( $fat_id >= 0 )
	{
		$bin .= $clst->str_read_block($file, $head['alloc_offset'] + $fat_id, 1, 'ps2_card_read');

		$b1 = str2int($fat, ($fat_id << 2) + 0, 2);
		$b2 = str2int($fat, ($fat_id << 2) + 2, 2);
		printf("> %4x , %x\n", $b1, $b2);
		if ( ($b2 & 0x8000) === 0 )
			return php_error('FAT free but not EOF [%x = %x]', $fat_id, $fat_id << 2);

		if ( $b2 === 0xffff )
		{
			$fat_id = -1;
			continue;
		}
		$fat_id = $b1;
	} // while ( $fat_id >= 0 )
	return $bin;
}

// callback
function ps2_card_read( $block, $byte )
{
	global $gp_ps2;
	$data = $gp_ps2->read($block);
	return substr($data, 0, $byte);
}
//////////////////////////////
function ps2_loopdir( &$ps2, $fat_id, $cnt, $path, $is_join=false )
{
	global $gp_txt;
	$func = __FUNCTION__;

	$data = array();
	$bin  = ps2_cluster_read($ps2['fat'], $ps2['clst'], $ps2['file'], $ps2['head'], $fat_id);

	for ( $i=0; $i < $cnt; $i++ )
	{
		$sub = substr($bin, $i * 0x200, 0x200);

		$mode = str2int($sub, 0, 2);
		$name = substr0($sub, 0x40);

		$dirmode = bit_dirmode($mode);
		if ( array_search('DF_EXISTS',$dirmode) === false )
			continue;

		if ( $name[0] === '.' )
		{
			$data[] = $sub;
			continue;
		}

		$size = str2int($sub,  4, 3);
		$offs = str2int($sub, 16, 3);

		if ( array_search('DF_FILE',$dirmode) !== false )
		{
			$txt = sprintf("%6x , %6x , DF_FILE = %s/%s\n", $offs, $size, $path, $name);
			echo $txt;
			$gp_txt .= $txt;
			if ( $size === 0xffffff ) // empty file
				continue;

			$read = ps2_cluster_read($ps2['fat'], $ps2['clst'], $ps2['file'], $ps2['head'], $offs);
			$data[] = $sub . $read;
			continue;
		}

		if ( array_search('DF_DIR',$dirmode) !== false )
		{
			$txt = sprintf("%6x , %6x , DF_DIR  = %s/%s\n", $offs, $size, $path, $name);
			echo $txt;
			$gp_txt .= $txt;
			$data[] = $sub . $func($ps2, $offs, $size, "$path/$name", true);
			continue;
		}
	} // for ( $i=0; $i < $cnt; $i++ )

	if ( $is_join )
		$data = implode('', $data);
	return $data;
}

function ps2card( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file,0,0x1c) !== 'Sony PS2 Memory Card Format ' )
		return;
	if ( strlen($file) !== 0x840000 )
		return;

	$dir = str_replace('.', '_', $fname);

	$sub = substr0($file, 0x1c);
	$ver = (int)str_replace('.', '', $sub);
	if ( $ver < 1 )
		return;

	// page                     =    200 byte
	// cluster =        2 * 200 =    400 byte
	// block   =       10 * 200 =   2000 byte
	// card    = 2000 * 2 * 200 = 800000 byte
	$page = new filesystem_handler(0x210 , 0x200 , 0);
	$clst = new filesystem_handler(0x420 , 0x400 , 0);

	$sub  = $page->str_read($file, 0, 0x154, 'ps2_card_read');
	$head = ps2_header_read($sub, $ver);
	//print_r($head);

	// page  clst
	//    0     0  superblock
	//    1     0  unused
	//   10     8  indirect fat table
	//   12     9  fat table
	//   52    29  allocatable clusters
	// 3ed2  1f69  reserved clusters
	// 3fe0  1ff0  backup block 2
	// 3ff0  1ff8  backup block 1
	// 4000  2000  end
	$ifat = $clst->str_read_block($file, $head['ifc_list'][0], 1, 'ps2_card_read');
	$fat  = '';
	for ( $i=0; $i < 0x20; $i++ )
	{
		$id  =  str2int($ifat, $i << 2, 4);
		$fat .= $clst->str_read_block($file, $id, 1, 'ps2_card_read');
	}
	save_file("$fname.fat" , $fat);

	// data -> backup block 1
	// lba  -> backup block 2
	// write new data to lba
	// if error
	//    write backup block 1 to lba
	// erase backup block 2
	$root = $clst->str_read_block($file, $head['alloc_offset'], 1, 'ps2_card_read');
	$cnt = str2int($root, 4, 3);

	global $gp_txt;
	$gp_txt = '';
	$ps2 = array(
		'fat'   => &$fat,
		'clst'  => &$clst,
		'file'  => &$file,
		'head'  => &$head,
		'alloc' => $head['alloc_offset'],
	);
	$root = ps2_loopdir($ps2, 0, $cnt, '', false);

	foreach ( $root as $rk => $rv )
	{
		$name = substr0($rv, 0x40);
		if ( $name[0] === '.' )
			continue;

		save_file("$dir/$name.psu", $rv);
	} // foreach ( $root as $rk => $rv )

	save_file("$dir/list.txt", $gp_txt);
	return;
}

argv_loopfile($argv, 'ps2card');

/*
.ps2     page  psu                 page
.  ad40    54      0  8427      6  1     BISLP-25771
.  b160    56    200  8427      -  1     .
.  b370    57    400  8427      -  1     ..
.  b580    58    600  8497   2220  1+12  BISLPS-25771
.  b790    59   2c00  8497  12f18  1+98  view.ico
. 21840   104  15e00  8497  12f18  1+98  del.ico
. 21a50   105  29000  8497    3c4  1+ 2  icon.sys
.840000  4000  29600  end

psu page cnt
	= 200 + int_ceil(data_size, 200)

	200 + int_ceil(2220, 200)
	= 200 + 2400
	= 13 pages
	200 + int_ceil(3c4, 200)
	= 200 + 400
	= 3 pages
*/
