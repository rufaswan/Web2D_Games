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
 * IE - RA.ald
 * wavLoad sndPlay - WA.ald
 * gaijiSet - gaijin.dat
 */
require "exec_alice35.php";

function sco39_text_add( $jp )
{
	global $gp_pc;
}

function sco39_load_sco( $id )
{
	global $sco_file, $gp_init;
	if ( ! isset( $sco_file[$id] ) )
	{
		$sco = sprintf( $gp_init["path_sco"], $id );
		$sco_file[$id] = file_get_contents( ROOT . "/$sco" );
		trace("load $sco");
	}
}

function exec_alice39( $id, &$st, &$run )
{
	global $sco_file;
	if ( $id == 0 )  $id = 1;
	if ( $st == 0 )  $st = 0x20;
	sco39_load_sco( $id );
	trace("= sco_%d_%x : ", $id, $st);
}
