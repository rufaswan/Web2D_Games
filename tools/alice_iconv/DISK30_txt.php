<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of Web2D_Games. <https://github.com/rufaswan/Web2D_Games>

Web2D_Games is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Web2D_Games is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Web2D_Games.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
require "common.inc";
require "common_sco.inc";
$gp_msg = array();
$gp_mid = 1;

function sco3txt( $rem, $fname )
{
	$file = file_get_contents( $fname );
		if ( empty($file) )   return;

	$base = substr($fname, 0, strrpos($fname, '.'));

	$ed = strlen($file);
	$st = 2;
	$select = false;
	while ( $st < $ed )
	{
		printf("$base,%d,", $st);

		$bak = $st;
		$cmd = $file[$st];
		$arg = array();
		switch ( $cmd )
		{
			case '!':
				$st++;
				$arg[] = sco_varno( $file, $st );
				$arg[] = sco_calli( $file, $st );
				$arg = implode(',', $arg);
				echo "$cmd,$arg";
				break;
			case '{':
				$st++;
				$arg[] = sco_calli( $file, $st );
				$arg[] = sint16( $file, $st );
				$arg = implode(',', $arg);
				echo "$cmd,$arg";
				break;
			case '@':
			case '\\':
				$st++;
				$arg = sint16( $file, $st );
				echo "$cmd,$arg";
				break;
			case '%':
			case '&':
				$st++;
				$arg = sco_calli( $file, $st );
				echo "$cmd,$arg";
				break;
			case '$':
				if ( $select )
				{
					$st++;
					$select = false;
					echo "$cmd,end";
				}
				else
				{
					$st++;
					$select = true;
					$arg = sint16( $file, $st );
					echo "$cmd,$arg";
				}
				break;
			case ']':
			case 'A':
			case 'F':
			case 'R':
				$st++;
				echo "$cmd";
				break;
			case 'L':
			case 'Q':
				$st++;
				$arg = sco_var_args( 1, $file, $st );
				echo "$cmd,$arg";
				break;
			case 'J':
			case 'O':
			case 'T':
			case 'U':
			case 'Y':
			case 'Z':
				$st++;
				$arg = sco_var_args( 2, $file, $st );
				echo "$cmd,$arg";
				break;
			case 'P':
				$st++;
				$arg = sco_var_args( 4, $file, $st );
				echo "$cmd,$arg";
				break;
			case 'E':
			case 'I':
				$st++;
				$arg = sco_var_args( 6, $file, $st );
				echo "$cmd,$arg";
				break;
			case 'K':
			case 'S':
			case 'X':
				$arg = ord( $file[$st+1] );
				$st += 2;
				echo "$cmd,$arg";
				break;
			case 'H':
				$t = ord( $file[$st+1] );
				$st += 2;
				$arg = sco_var_args( 1, $file, $st );
				echo "$cmd,$t,$arg";
				break;
			case 'M':
				$st++;
				$arg = sco_ascii( $file, $st, ':' );
				$id = add_str( $arg );
				echo "$cmd,$id";
				break;
			case 'B':
				$t = ord( $file[$st+1] );
				switch ( $t )
				{
					case 1:
					case 2:
					case 3:
					case 4:
						$st += 2;
						$arg = sco_var_args( 6, $file, $st );
						echo "$cmd,$t,$arg";
						break;
				}
				break;
			case 'G':
				$st++;
				$arg = sco_var_args( 1, $file, $st );
				echo "$cmd,$arg";
				break;
			default:
				$b1 = ord($cmd);
				if ( $b1 == 0x1a )
				{
					echo "SYS_END\n";
					return;
				}
				else
				if ( $b1 == 0x20 || $b1 & 0x80 )
				{
					$arg = sco_sjis($file, $st);
					$id = add_str( $arg );
					echo "MSG,$id";
				}
				break;
		}
		echo "\n";

		if ( $bak == $st )
			return;
	} // while ( $st < $ed )
	return;
}

if ( $argc == 1 )   exit();
for ( $i=1; $i < $argc; $i++ )
	sco3txt( $argc-$i, $argv[$i] );

foreach ( $gp_msg as $k => $v )
	echo "$k,$v\n";
return;
