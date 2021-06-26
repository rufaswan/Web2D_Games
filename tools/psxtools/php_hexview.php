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

Linux ONLY , required exec() command
	- stty
	- tput
 */
$gp_opts = array(
	'termx' => 82,
	'termy' => 28,
	'col'   => 32,
	'pos'   =>  0,
	'block' => 0x800,
);

function hexread( $fname )
{
	global $gp_opts;
	$fp = fopen($fname, 'rb');
	if ( ! $fp )  return;

	$done = false;
	$size = filesize($fname);

	// bash title bar
	printf("\033]0;[READ] %s (%x)\007", $fname, $size);
	while ( ! $done )
	{
		$gp_opts['termx'] = exec('tput cols');
		$gp_opts['termy'] = exec('tput lines') - 2;

		ob_start();
		printf("======== %s [%x/%x] (%d%%) ========\n", $fname, $gp_opts['pos'], $size, $gp_opts['pos']*100/($size-1));

		fseek($fp, $gp_opts['pos'], SEEK_SET);
		$sub = fread($fp, $gp_opts['col']*$gp_opts['termy']);

		for ( $y=0; $y < $gp_opts['termy']; $y++ )
		{
			$sy = $y * $gp_opts['col'];
			//if ( ! isset($sub[$sy]) )
				//break;
			printf("%8x :", $gp_opts['pos'] + $sy);

			$x   = 10+3;
			$sep =  0;
			$sx  =  0;
			$buf = '';
			while ( $x < $gp_opts['termx'] )
			{
				// column seperator
				if ( $sep == 0 )
				{
					$buf .= ' ';
					$x++;
				}

				// error check
				if ( ! isset($sub[$sy+$sx]) )
					break;
				if ( $sx >= $gp_opts['col'] )
					break;

				// display a char
				$b = ord( $sub[$sy+$sx] );
				if ( $b == 0 )
					$buf .= '-- ';
				else
					$buf .= sprintf("%2x ", $b);

				$x += 3;
				$sep = ($sep + 1) & 0x03;
				$sx++;
			} // while ( $x < $gp_opts['termx'] )

			echo "$buf\n";
		} // for ( $y=0; $y < $gp_opts['termy']; $y++ )

		echo ob_get_clean();

		// input handling
		// script idle until get input
		$in = fgetc(STDIN);
		switch ( $in )
		{
			case  '':  break;
			case '+':  $gp_opts['col']++; break;
			case '-':  $gp_opts['col']--; break;
			case '*':  $gp_opts['pos'] += $gp_opts['block']; break;
			case '/':  $gp_opts['pos'] -= $gp_opts['block']; break;

			case 'w':  $gp_opts['pos'] -= $gp_opts['col']; break;
			case 's':  $gp_opts['pos'] += $gp_opts['col']; break;
			case 'a':  $gp_opts['pos']--; break;
			case 'd':  $gp_opts['pos']++; break;
			case 'q':
				$done = true;
				break;
			default:
				printf("get %s\n", $in);
				break;
		} // switch ( $in )

		// sanity check
		if ( $gp_opts['col'] < 1 )  $gp_opts['col'] = 1;
		if ( $gp_opts['pos'] < 0 )  $gp_opts['pos'] = 0;
		if ( $gp_opts['pos'] >= $size )  $gp_opts['pos'] = $size - 1;

		//sleep(1);
	} // while ( ! $done )

	fclose($fp);
	return;
}
//////////////////////////////
function get_opt( $argv, $i )
{
	global $gp_opts;
	switch ( $argv[$i] )
	{
		case '--':
			return 1;
		case '-p':
			$gp_opts['pos'] = hexdec( $argv[$i+1] );
			//printf("gp_pos = %x\n", $gp_opts['pos']);
			return 2;
		case '-c':
			$gp_opts['col'] = hexdec( $argv[$i+1] );
			//printf("gp_col = %x\n", $gp_opts['col']);
			return 2;
		case '-c':
			$gp_opts['block'] = hexdec( $argv[$i+1] );
			//printf("gp_block = %x\n", $gp_opts['block']);
			return 2;
		default:
			hexread($argv[$i]);
			return 999;
	}
	return 1;
}

system('stty cbreak -echo');
$i = 1;
while ( $i < $argc )
	$i += get_opt( $argv, $i );
system('stty -cbreak echo');

/*
stty
	 cbreak == -icanon
	-cbreak ==  icanon
 */
