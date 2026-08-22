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
 */
require 'common.inc';
require 'common-zlib.inc';

function mamecps2( $name, &$file )
{
	while ( strlen($file) & 1 )
		$file .= ZERO;
	$len = strlen($file);
	for ( $i=0; $i < $len; $i += 2 )
	{
		$t = $file[$i+0];
		$file[$i+0] = $file[$i+1];
		$file[$i+1] = $t;
	}
	save_file("cps2-$name.ram", $file);
	return;
}

function mamesave( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( ! substr($file,0,8) === 'MAMESAVE' )
		return;

	// /src/emu/state.c   0.121 - 0.142
	// /src/emu/save.c    0.143 - 0.167
	// /src/emu/save.cpp  0.168+
	//   00  MAMESAVE
	//   08  version (1=0.121 - 0.128 , 2=0.129+)
	//   09  flags (2=MSB)
	//   0a  game name
	//   1c  signature
	//   20  *zlib data*
	$head = substr($file, 0, 0x20);
	$sub  = substr($file, 0x20);

	$ver  = ord($head[8]);
	$flag = ord($head[9]);
	$name = substr($head, 10, 0x12);
		$name = rtrim($name, ZERO);
	//$crc  = str2int($head, 0x1c, 4);

	$sub = zlib_decode($sub);
	switch ( $name )
	{
		case 'cybots':
		case 'spf2t':
			return mamecps2($name, $sub);
		default:
			return save_file($fname.'.dec', $sub);
	} // switch ( $name )
	return;
}

for ( $i=1; $i < $argc; $i++ )
	mamesave( $argv[$i] );

/*
void *memory_manager::allocate_memory(
	device_t &dev,    //
	int spacenum,     //
	std::string name, //
	u8 width,         //
	size_t bytes)     //

void save_manager::save_memory(
	device_t *device,   // &dev
	const char *module, // "memory"
	const char *tag,    // dev.tag()
	u32 index,          // spacenum
	const char *name,   // name.c_str()
	void *val,          // ptr
	u32 valsize,        // width/8
	u32 valcount   = 1, // u32(bytes) / (width/8))
	u32 blockcount = 1, //
	u32 stride     = 0) //
*/
