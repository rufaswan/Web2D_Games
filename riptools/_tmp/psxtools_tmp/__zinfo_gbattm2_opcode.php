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

$gp_op_siz = [
//  0  1  2  3    4  5  6  7    8  9  a  b    c  d  e  f
	-1,-1,-1,-1 , -1, 4,-1,-1 ,  2, 2, 2, 2 ,  4, 2, 2, 2 , // 00
	16,12,16,14 , -1,12,-1,-1 ,  2, 2,-1, 2 ,  4, 4, 2, 2 , // 10
	 2, 2, 2, 2 ,  2, 2, 2, 2 ,  2, 2,-1, 2 ,  2, 2, 2, 2 , // 20

	 8,10,10,-1 ,  8,-1,-1,-1 , 12,14,-1,-1 , -1,-1,-1,-1 , // 30
	-1,-1,-1,-1 ,  2, 2,-1, 4 ,  2, 2, 2, 2 ,  2, 2, 2, 2 , // 40
	 2,10, 2,-1 , -1, 2, 2, 2 ,  2, 2,-1,-1 , -1,-1,-1,-1 , // 50
];
$gp_goto = [
];

function gbattm2_opcode( &$oplist, &$file, &$label, $pos, $len )
{
	global $gp_op_siz, $gp_goto;
	while ( $pos < $len )
	{
		$by = str2int($file, $pos, 2);
		if ( $by & 0x8000 )
		{
			$siz = $gp_op_siz[ $by & BIT8 ];
			if ( $siz < 1 )
				$siz = 2;
			else
				$siz += 2;
		}
		else
		{
			$siz = 2;
		}

		$oplist[$pos] = substr($file, $pos, $siz);
		$pos += $siz;
	} // while ( $pos < $len )

	return $pos;
}
//////////////////////////////
function gbattm2_op( &$file, $pos )
{
	$label = [];
	$end = str2int($file, $pos, 2) << 1;
	$id  = 0;
	while ( $pos < $end )
	{
		$goto = str2int($file, $pos, 2) << 1;
		$name = sprintf('spr::act_%03d', $id);
		$label[$name] = $goto;

		$id++;
		$pos += 2;
	} // while ( $pos < $end )

	$oplist = [];
	$len = strlen($file);
	gbattm2_opcode($oplist, $file, $label, $end, $len);;

	asm_trace($label, $oplist);
	return;
}

function gbattm2( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	$pos = str2int($file, 0, 2);

	ob_start();
	gbattm2_op($file, $pos);
	$txt = ob_get_clean();

	save_file("$fname.0.txt", $txt);
	return;
}

for ( $i=1; $i < $argc; $i++ )
	gbattm2( $argv[$i] );

/*
*/
