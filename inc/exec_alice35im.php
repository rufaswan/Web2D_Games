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
require "exec_alice35.php";

function sco35_mouse( $type, $args, &$file, &$st )
{
	// mouse only , skip keyboard input
	global $gp_pc, $gp_input, $gp_key;
	switch ( $type )
	{
		case "IK0":
		case "IK1":
		case "IK2":
		case "IK3":
		case "IK4":
		case "IK5":
		case "IK6":
			if ( sco35_loop_IK0( $file, $st ) )
			{
				$st += 17;
				return true;
			}

			trace("$type skip");
			$gp_pc["var"][0] = 0;
			return true;
		case "IM":
			list($v1,$e1,$v2,$e2) = $args;

			if ( ! empty($gp_input) && $gp_input[0] == "mouse" )
			{
				$mx = $gp_input[1];
				$my = $gp_input[2];
				trace("IM $v1+$e1,$v2+$e2 = %d,%d", $mx,$my);
				$gp_pc["var"][0] = $gp_key["enter"];
				sco35_var_put( $v1, $e1, $mx );
				sco35_var_put( $v2, $e2, $my );
				$gp_input = array();
				return true;
			}

			trace("IM wait = 0");
			$gp_pc["var"][0] = 0;
			sco35_var_put( $v1, $e1, -1 );
			sco35_var_put( $v2, $e2, -1 );
			return false;
	}
	return false;
}

function exec_alice35im()
{
	exec_alice35();
}
