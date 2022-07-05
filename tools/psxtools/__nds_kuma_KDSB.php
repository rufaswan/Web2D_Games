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

function kuma( $fname )
{
	$file = file_get_contents($fname);
	if ( empty($file) )  return;

	if ( substr($file, 0, 4) !== 'KDSB' )
		return;

	$op_cnt = str2int($file,  8, 4);
	$jp_cnt = str2int($file, 12, 4);

	$op_off = 0x10;
	$jp_off = 0x10 + ($op_cnt * 4);

	echo "== $fname : opcode ==\n";
	for ( $i=0; $i < $op_cnt; $i++ )
	{
		$op = str2int($file, $op_off+0, 2);
		$ar = str2int($file, $op_off+2, 2);
			$op_off += 4;
		printf("%3d : %4x,%4x\n", $i, $op, $ar);

		global $gp_op;
		$gp_op[$op] = 1;
	} // for ( $i=0; $i < $op_cnt; $i++ )

	echo "== $fname : sjis ==\n";
	for ( $i=0; $i < $jp_cnt; $i++ )
	{
		$jp = substr0($file, $jp_off);
			$jp_off += strlen($jp) + 1;
		printf("%3d : %s\n", $i, $jp);
	} // for ( $i=0; $i < $jp_cnt; $i++ )
	return;
}

$gp_op = array();
for ( $i=1; $i < $argc; $i++ )
	kuma( $argv[$i] );

ksort($gp_op);
echo "== op_list ==\n";
foreach ( $gp_op as $k=>$v )
	printf("  %4x\n", $k);

/*
iconv -f cp932 -t utf8

op list
	1   00 10        // end        [arg 414e]
	-
	3   01           // loading slide out [arg ??? 1e/3c/5a]
	4   01           // loading slide in  [arg 5a]
	5   00           // screen  shake     [arg 414e]
	6   10 11 21     // char  set  [arg ID]
	7   10 11 20 21  // talk  anim [arg ID]
	8   10 11 20 21  // walk  anim [arg ID]
	9   11 21        // talk  text [arg -]
	a   10 11 20 21  // pos   set  [arg pixel]
	b   10 11 21     // walk  pos  [arg pixel]
	-
	d   10 11 20 21  // face  set  [arg 1=right -1=left]
	e   01           // bg    set  [arg ID]
	f   11 21        // think text [arg -]
	10  11 21        // navi  text [arg -]
	-
	12  00           // voice set  [arg ID]
	13  00           // music set  [arg ID]
	14  00 20        // title bg   [arg ID]
	15  10           // title text [arg -]
	16  10 20        // bath  text [arg -] *bath*
	17  11 21        // sound set  [arg ID] *bath*
	18  10 20        // var value  [arg value] *bath*
	19  00           // var ID     [arg ID] *bath*
	1a  00           // bg scroll  [arg 0=L2R 1=R2L] *prol_a01 epil_a02*
	1b  00           // [arg 38] *bath*
		xy
			x  0=bg     1=obj_left  2=obj_right
			y  0=cache  1=exec
		pixel  0=left edge  100=right edge
		char ID
			0  kuma-tan
			1  rabbi-tan
			2  neko-kun
			3  tora-neesan
			4  ushi-neesan
			5  saru-jii
			6  maguro
			7  twin monkey
			8  owner
			9  sumomo
		var ID
			1  popularity         [75-72-6f-6c-69-47-44-41-3e-3b]
			2  will               [75-72-6f-6c-69]
			3  talent music       [7e-7b-78-75]
			4  talent strenght    [81-7e-7b-75]
			5  talent skill       [7e-7b-78-75]
			6  talent show art    [81-7e-7b-78-75]
			7  kuma-player like   [a5-a2-9f-9c-99-5b-58-55-52]
			8  show performance   [5b-58-55-52]
			9  hunger             [47-44-41-3e-3b]
			a  sumomo-player like [56-50]
 */
