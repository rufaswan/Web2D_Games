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
//require 'asm_mips.inc';

function mipsexe( &$file, $st, $ed )
{
	if ( empty($file) )  return;
	if ( $st & 3 )  return;
	if ( $ed & 3 )  return;

	$len = strlen($file);
	if ( $ed > $len )
		$ed = $len;

	printf("# %x - %x = %x\n", $st, $ed, $ed-$st);
	printf('func_%x =', $st);
	while ( $st < $ed )
	{
		$b3 = ord( $file[$st+3] );
		$op = $b3 >> 2;
		switch ( $op )
		{
			case 0: // r-type
				$b0 = ord( $file[$st+0] );
				$func = $b0 & 0x3f;

				// 8=jr  9=jalr
				printf('  %x,%x', $op, $func);
				break;

			case 2: // j-type , j
				$imm = str2int($file, $st, 2, true);
				printf('  %x,%x', $op, $imm);
				break;

			case 3: // j-type , jal
				printf('  %x', $op);
				break;

			default: // i-type
				printf('  %x', $op);
				break;
		} // switch ( $op )

		$st += 4;
	} // while ( $st < $ed )

	echo "\n\n";
	return;
}

$file = '';
for ( $i=1; $i < $argc; $i++ )
{
	if ( is_file($argv[$i]) )
		$file = file_get_contents($argv[$i]);
	else
	{
		$t = explode('-', $argv[$i]);
		if ( empty($t) )
			continue;
		$st = hexdec($t[0]);
		$ed = hexdec($t[1]);
		mipsexe($file, $st, $ed);
	}
} // for ( $i=1; $i < $argc; $i++ )
