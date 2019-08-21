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

function G_cmd30( &$file, &$st, &$run )
{
	global $gp_pc;
	$id = ord( $file[$st+1] );
	$st += 2;
	trace("G $id");
	$gp_pc["G"] = $id;
	return;
}

function S_cmd30( &$file, &$st, &$run )
{
	global $gp_pc;
	// SX:　X番の曲を演奏する。 100=pause 101=resume
	$id = ord( $file[$st+1] );
	$st += 2;
	trace("S $id");
	if ( $id > 99 )
	{
		if ( $id == 100 )
			$gp_pc["S"][1] = 0;
		else
		if ( $id == 101 )
			$gp_pc["S"][1] = 1;
	}
	else
		$gp_pc["S"] = array($id,1);
	return;
}

function Y_cmd30( &$file, &$st, &$run )
{
	global $gp_pc;
	$st++;
	$num = sco30_calli($file, $st);
	$val = sco30_calli($file, $st);
	trace("Y $num , $val");
	$gp_pc["Y"][$num][] = $val;
	return;
}

function sco30_cmd( &$file, &$st, &$run, &$select )
{
	$func = __FUNCTION__;
	global $sco_file, $gp_pc, $gp_input;
	$file = &$sco_file[$id];
	switch( $file[$st] )
	{
		case 'G':  return G_cmd30( $file, $st, $run );
		case 'S':  return S_cmd30( $file, $st, $run );
		case 'Y':  return Y_cmd30( $file, $st, $run );
		default:
			$b1 = ord( $sco_file[$id][$st] );
			if ( $b1 & 0x80 )
			{
				$jp = sco30_sjis($sco_file[$id], $st);
				trace("text %s", $jp);
				sco30_text_add( $jp );
				return;
			}
			return;
	}
	return;
}
