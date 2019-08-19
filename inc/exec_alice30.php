<?php
/*
[license]
Copyright (C) 2019 by Rufas Wan

This file is part of web2D_game. <https://github.com/rufaswan/web2D_game>

web2D_game is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

web_2D_game is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with web2D_game.  If not, see <http://www.gnu.org/licenses/>.
[/license]
 */
/*
 * 00  2  file size
 * 02  ...  bytecode
 *
 * Tested OK
 * Ambivalenz 1.03w  -  98 (iconv 98-ca6)
 * Ayumi-chan 1.11w  -  89 (iconv 4-1b,4-86)
 * FunnyBee   1.03wb -  78 (iconv *lot*)
 * Mugen      1.00   - 126 OK
 * Rance 4.1   .100  -  47 (iconv 2-*lot*,4-*lot*)
 * Rance 4.2  1.01   -  49 (iconv 2-*lot*,4-*lot*,7-12b,10-14c7,22-*lot*)
 * Yakata 3          -  99 OK
 */
require "cmd_alice30.php";

function sco30_html( &$run )
{
	$run = false;
	global $gp_init, $gp_pc, $ajax_html;
	$ajax_html = "";
	ob_start();

/// CSS ///

/// CSS ///

/// WINDOW ///

/// WINDOW ///

/// AUDIO ///

/// AUDIO ///

	$ajax_html = ob_get_clean();
}

function sco30_text_add( $jp )
{
	global $gp_pc;
}

function sco30_load_sco( $id )
{
	global $sco_file, $gp_init;
	if ( ! isset( $sco_file[$id] ) )
	{
		$sco = sprintf( $gp_init["path_sco"], $id );
		$sco_file[$id] = file_get_contents( ROOT . "/$sco" );
		trace("load $sco");
	}
}

function exec_alice30( $id, &$st, &$run )
{
	global $sco_file;
	if ( $id == 0 )  $id = 1;
	if ( $st == 0 )  $st = 2;
	sco35_load_sco( $id );
	trace("= sco_%d_%x : ", $id, $st);

	global $gp_pc;
	$now = $st;
	$b1 = ord( $sco_file[$id][$st] );
	if ( $b1 & 0x80 )
	{
		$jp = sco30_sjis($sco_file[$id], $st);
		trace("text %s", $jp);
		sco30_text_add( $jp );
	}
	else
		sco30_cmd( $sco_file[$id], $st, $run );

	if ( $st == $now )
		sco30_html( $run );
}
