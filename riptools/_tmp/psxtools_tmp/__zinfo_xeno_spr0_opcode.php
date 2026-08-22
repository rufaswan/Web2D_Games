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
require 'common-guest.inc';

function spr0_opcode( &$oplist, &$file, &$label, $pos, $len )
{
	while ( $pos < $len )
	{
		$by = ord( $file[$pos] );
/*
		if ( $by & 0x80 )
		else
		if ( $by & 0x40 )
		else
*/

		$siz = 1;
		$oplist[$pos] = substr($file, $pos, $siz);
		$pos += $siz;
	} // while ( $pos < $len )

	return $pos;
}
//////////////////////////////
function xeno_spr0op( &$file )
{
	$label = [];
	$cnt = ord( $file[0] );
	for ( $i=0; $i < $cnt; $i++ )
	{
		$pos  = 2 + ($i * 2);
		$goto = str2int($file, $pos, 2);
		$name = sprintf('spr::act_%02d', $i);
		$label[$name] = $goto;
	}

	$oplist = [];
	$len = strlen ($file);
	$pos = str2int($file, 2, 2);
	spr0_opcode($oplist, $file, $label, $pos, $len);;

	asm_trace($label, $oplist);
	return;
}

function xeno( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$len = strlen ($file);
	$cnt = str2int($file, 0, 4);
	if ( $cnt < 3 )
		return;

	$siz = str2int($file, 4 + ($cnt*4), 4);
	if ( $len < $siz )
		return;

	$b04 = str2int($file, 4, 4);
	$b08 = str2int($file, 8, 4);
	$sub = substr ($file, $b04, $b08-$b04);

	ob_start();
	xeno_spr0op($sub);
	$txt = ob_get_clean();

	save_file("$fname.0.meta", $sub);
	save_file("$fname.0.txt" , $txt);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	xeno( $argv[$i] );

/*
act name
	2992  2993
//////////////////////////////
427.bin = RAM 800ca6e0  fei
429.bin = RAM 800de6e8  citan

spr file 0.meta

800223b0  move  a0, v1
800223b4  if ( a0 < 5 )  else  800223ec
800223bc  sll   v1, a0, 1
800223c4  lw    a2, 58(s1)
800223cc  addu  a0, v1, a2
800223d8  lhu   a0, 4(a0)
//////////////////////////////
*/
